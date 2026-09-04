<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use App\Services\LedgerService;
use App\Services\TrialBalanceService;
use Illuminate\Support\Facades\DB;
use App\Exports\TrialBalanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;

class LedgerController extends Controller
{
    protected $ledgerService;
    protected $trialBalanceService;

    // Suntikkan LedgerService melalui constructor
    public function __construct(LedgerService $ledgerService, TrialBalanceService $trialBalanceService)
    {
        $this->ledgerService = $ledgerService;
        $this->trialBalanceService = $trialBalanceService;
    }

    public function pilih_akun(Request $request, $id)
    {
        $title = "Pilih Akun Buku Besar";
        $periode = $request->input('periode', date('Y-m'));

        // Ambil data COA + Saldo Awal + Total Mutasi berjalan yang berstatus POSTED
        $accountsData = ChartofAccount::select([
                'public.chart_of_account.id',
                'public.chart_of_account.kode',
                'public.chart_of_account.nama_akun',
                'public.chart_of_account.type',
                'public.chart_of_account.category',
                'ob.saldo as opening_saldo',
                DB::raw(
                    'COALESCE(SUM(CASE WHEN ju.id IS NOT NULL ' .
                    'THEN ji.debit ELSE 0 END), 0) as total_debit'
                ),
                DB::raw(
                    'COALESCE(SUM(CASE WHEN ju.id IS NOT NULL ' .
                    'THEN ji.kredit ELSE 0 END), 0) as total_kredit'
                )
            ])
            ->leftJoin('public.opening_balances as ob', function($join) use ($periode) {
                $join->on('public.chart_of_account.id', '=', 'ob.chart_of_account_id')
                    ->where('ob.periode', '=', $periode);
            })
            ->leftJoin('public.general_journal_details as ji', function($join) {
                $join->on('public.chart_of_account.id', '=', 'ji.chart_of_account_id');
            })
            ->leftJoin('public.general_journals as ju', function($join) use ($periode) {
                $join->on('ji.general_journal_id', '=', 'ju.id')
                    ->where('ju.status', '=', 'posted') // Mengunci hanya jurnal sah
                    ->whereRaw("to_char(ju.tgl_transaksi, 'YYYY-MM') = ?", [$periode]);
            })
            ->where('public.chart_of_account.is_active', true)
            ->where('public.chart_of_account.perusahaan', $id)
            ->groupBy(
                'public.chart_of_account.id', 
                'public.chart_of_account.kode', 
                'public.chart_of_account.nama_akun', 
                'public.chart_of_account.type', 
                'public.chart_of_account.category', 
                'ob.saldo'
            )
            ->orderBy('public.chart_of_account.kode', 'asc')
            ->get();

        // Lakukan mapping php untuk menghitung rumus saldo berjalan real-time per akun
        $accounts = $accountsData->map(function($account) {
            $opening = (float) $account->opening_saldo;
            $debit = (float) $account->total_debit;
            $kredit = (float) $account->total_kredit;

            // Aturan saldo normal akuntansi (D vs K)
            if (strtoupper($account->type) === 'D') {
                $account->saldo_saat_ini = $opening + ($debit - $kredit);
            } else {
                $account->saldo_saat_ini = $opening + ($kredit - $debit);
            }

            return $account;
        });

        return view('finance.bb.pilih_akun', compact('title', 'accounts', 'periode', 'id'));
    }

    public function show(Request $request, $perusahaan, $id)
    {
        $id_perusahaan = (int)$perusahaan;
        $title = "Detail Buku Besar";
        
        // Ambil parameter periode dari URL, default ke bulan berjalan jika kosong
        $periode = $request->input('periode', date('Y-m'));

        // Panggil fungsi hitung dari Service
        $ledgerData = $this->ledgerService->getAccountLedger((int) $id, $periode);

        return view('finance.bb.show', compact('title', 'periode', 'ledgerData', 'id_perusahaan'));
    }

    public function neracaSaldo(Request $request, $id)
    {
        $title = "Neraca Saldo";
        $periode = $request->input('periode', date('Y-m'));

        // Panggil data langsung dari Service
        $data = $this->trialBalanceService->getTrialBalance($periode, $id);
        $reportData = collect($data['report_data'])
            ->filter(function ($row) {
                return round((float) $row['saldo_debit'], 2) > 0
                    || round((float) $row['saldo_kredit'], 2) > 0;
            })
            ->values()
            ->all();

        return view('finance.neraca_saldo', [
            'title'            => $title,
            'periode'          => $periode,
            'reportData'       => $reportData,
            'grandTotalDebit'  => $data['grand_total_debit'],
            'grandTotalKredit' => $data['grand_total_kredit']
        ]);
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        
        // Kirim instance service ke dalam constructor export class
        return Excel::download(
            new TrialBalanceExport($periode, $this->trialBalanceService), 
            "Neraca_Saldo_{$periode}.xlsx"
        );
    }

    public function neracaLajur(Request $request, TrialBalanceService $trialBalanceService, $id)
    {
        $title = "Neraca Lajur";
        
        // Disamakan menggunakan filter periode bulanan (YYYY-MM) sesuai service Neraca Saldo
        $periode = $request->get('periode', date('Y-m'));

        // 1. Ambil data Neraca Saldo ter-update langsung dari Service terintegrasi
        $trialBalanceData = $trialBalanceService->getTrialBalance($periode, $id);
        $reportData = $trialBalanceData['report_data'] ?? [];

        // Meringkas data Neraca Saldo ke dalam format keyBy 'kode' agar mudah di-lookup
        $trialBalanceKeyed = collect($reportData)->keyBy('kode');

        // 2. Ambil total mutasi Debit & Kredit dari Jurnal Penyesuaian berdasarkan periode bulan-tahun terkait
        $adjustmentJournalSums = DB::table('adjustment_journal_details')
            ->join('adjustment_journals', 'adjustment_journal_details.adjustment_journal_id', '=', 'adjustment_journals.id')
            ->select(
                'chart_of_account_id',
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(kredit) as total_kredit')
            )
            ->whereRaw("to_char(adjustment_journals.tgl_transaksi, 'YYYY-MM') = ?", [$periode])
            ->where('perusahaan', $id)
            ->groupBy('chart_of_account_id')
            ->get()
            ->keyBy('chart_of_account_id');

        // 3. Ambil seluruh data COA aktif (Akun baru tanpa transaksi akan tetap masuk ke sini)
        $coas = DB::table('chart_of_account')
            ->where('is_active', true)
            ->where('perusahaan', $id)
            ->orderBy('kode', 'asc')
            ->get();

        $worksheetData = [];
        
        // Inisialisasi Grand Total Kolom bawah
        $totals = [
            'ns_debit' => 0, 'ns_kredit' => 0,
            'aj_debit' => 0, 'aj_kredit' => 0,
            'nsd_debit' => 0, 'nsd_kredit' => 0,
            'lr_debit' => 0, 'lr_kredit' => 0,
            'n_debit' => 0, 'n_kredit' => 0
        ];

        foreach ($coas as $coa) {
            // Cek apakah akun ini terdaftar di records Neraca Saldo dari service
            $nsRow = $trialBalanceKeyed->get($coa->kode);
            
            // Ambil data mutasi Ayat Jurnal Penyesuaian (AJP)
            $aj = $adjustmentJournalSums->get($coa->id) ?? (object)['total_debit' => 0, 'total_kredit' => 0];

            // --- A. NERACA SALDO (Mengambil data valid dari Service) ---
            $ns_debit = $nsRow ? (float)$nsRow['saldo_debit'] : 0;
            $ns_kredit = $nsRow ? (float)$nsRow['saldo_kredit'] : 0;

            // --- B. AYAT JURNAL PENYESUAIAN ---
            $aj_debit = (float)$aj->total_debit;
            $aj_kredit = (float)$aj->total_kredit;

            // --- C. NERACA SALDO DISESUAIKAN (NSD) ---
            // Aturan: Jika saldo normal akun adalah Debit
            if ($ns_debit > 0 || ($ns_debit == 0 && $ns_kredit == 0 && in_array(substr((string)$coa->kode, 0, 1), ['1', '5', '6']))) {
                $nsd_diff = ($ns_debit + $aj_debit) - $aj_kredit;
                $nsd_debit = $nsd_diff > 0 ? $nsd_diff : 0;
                $nsd_kredit = $nsd_diff < 0 ? abs($nsd_diff) : 0;
            } else { // Jika saldo normal akun adalah Kredit
                $nsd_diff = ($ns_kredit + $aj_kredit) - $aj_debit;
                $nsd_debit = $nsd_diff < 0 ? abs($nsd_diff) : 0;
                $nsd_kredit = $nsd_diff > 0 ? $nsd_diff : 0;
            }

            // --- SKIPPING CONDITION DIHAPUS / NON-AKTIFKAN ---
            // Supaya akun baru yang bernilai 0 tetap tampil di list baris tabel, baris ini dilewati.

            // --- D. LABA RUGI vs NERACA ---
            $lr_debit = 0; $lr_kredit = 0;
            $n_debit = 0; $n_kredit = 0;

            $firstDigit = substr((string)$coa->kode, 0, 1);
            // Akun Nominal (4=Pendapatan, 5 & 6=Beban) masuk ke Laba Rugi
            if (in_array($firstDigit, ['4', '5', '6'])) {
                $lr_debit = $nsd_debit;
                $lr_kredit = $nsd_kredit;
            } else { // Akun Riil (1=Aset, 2=Liabilitas, 3=Ekuitas) masuk ke Neraca
                $n_debit = $nsd_debit;
                $n_kredit = $nsd_kredit;
            }

            // Tambahkan ke array tampungan
            $worksheetData[] = [
                'kode' => $coa->kode,
                'nama_akun' => $coa->nama_akun,
                'ns_debit' => $ns_debit, 'ns_kredit' => $ns_kredit,
                'aj_debit' => $aj_debit, 'aj_kredit' => $aj_kredit,
                'nsd_debit' => $nsd_debit, 'nsd_kredit' => $nsd_kredit,
                'lr_debit' => $lr_debit, 'lr_kredit' => $lr_kredit,
                'n_debit' => $n_debit, 'n_kredit' => $n_kredit,
            ];

            // Akumulasi total kolom bawah bawah
            $totals['ns_debit'] += $ns_debit;   $totals['ns_kredit'] += $ns_kredit;
            $totals['aj_debit'] += $aj_debit;   $totals['aj_kredit'] += $aj_kredit;
            $totals['nsd_debit'] += $nsd_debit; $totals['nsd_kredit'] += $nsd_kredit;
            $totals['lr_debit'] += $lr_debit;   $totals['lr_kredit'] += $lr_kredit;
            $totals['n_debit'] += $n_debit;     $totals['n_kredit'] += $n_kredit;
        }

        // --- HITUNG SELISIH BALANSING LABA RUGI & NERACA ---
        $lr_balance = $totals['lr_kredit'] - $totals['lr_debit'];

        $valueColumns = [
            'ns_debit', 'ns_kredit',
            'aj_debit', 'aj_kredit',
            'nsd_debit', 'nsd_kredit',
            'lr_debit', 'lr_kredit',
            'n_debit', 'n_kredit',
        ];
        $worksheetData = array_values(array_filter(
            $worksheetData,
            function ($row) use ($valueColumns) {
                foreach ($valueColumns as $column) {
                    if (round((float) $row[$column], 2) > 0) {
                        return true;
                    }
                }

                return false;
            }
        ));

        return view('finance.neraca_lajur', compact('worksheetData', 'totals', 'lr_balance', 'title', 'periode'));
    }

    public function arus_kas(Request $request, $id)
    {
        $title = 'Laporan Keuangan';
        $periode = $request->input('periode', date('Y-m'));

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
            $periode = date('Y-m');
        }

        [$tahun, $bulan] = array_map(
            'intval',
            explode('-', $periode)
        );

        // Laba rugi dan neraca menggunakan sumber perhitungan yang sama dengan
        // Neraca Lajur dan export Excel agar seluruh laporan selalu sinkron.
        $laporanData = $this->getWorksheetData($id, $periode, $this->trialBalanceService);
        $labaRugi = collect($laporanData['worksheetData'])
            ->filter(fn ($row) => in_array(substr((string) $row['kode'], 0, 1), ['4', '5', '6'])
                && ($row['lr_debit'] != 0 || $row['lr_kredit'] != 0))
            ->values();
        $neraca = collect($laporanData['worksheetData'])
            ->filter(fn ($row) => in_array(substr((string) $row['kode'], 0, 1), ['1', '2', '3'])
                && ($row['n_debit'] != 0 || $row['n_kredit'] != 0))
            ->values();
        $worksheetTotals = $laporanData['totals'];
        $labaRugiBersih = $laporanData['lr_balance'];

        /*
        * 1. Ambil akun Kas/Bank.
        *
        * Akun dengan kode 111... atau 113... merupakan akun Kas/Bank.
        * Nama akun digunakan sebagai pengamanan tambahan.
        */
        $cashAccounts = DB::table('chart_of_account')
            ->where('perusahaan', $id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('kode', 'like', '111%')
                    ->orWhere('kode', 'like', '113%')
                    ->orWhereRaw(
                        "LOWER(COALESCE(nama_akun, '')) LIKE ?",
                        ['%kas%']
                    )
                    ->orWhereRaw(
                        "LOWER(COALESCE(nama_akun, '')) LIKE ?",
                        ['%bank%']
                    );
            })
            ->whereRaw(
                "LOWER(COALESCE(nama_akun, '')) NOT LIKE ?",
                ['%brankas%']
            )
            ->select(
                'id',
                'kode',
                'nama_akun'
            )
            ->orderBy('kode')
            ->get();

        if ($cashAccounts->isEmpty()) {
            return view('finance.report_cash_flow', [
                'id'                      => $id,
                'title'                   => $title,
                'periode'                 => $periode,
                'operasional'             => [],
                'investasi'               => [],
                'pendanaan'               => [],
                'totalOperasional'        => 0,
                'totalInvestasi'          => 0,
                'totalPendanaan'          => 0,
                'totalSaldoAwal'          => 0,
                'kenaikanPenurunanBersih' => 0,
                'totalSaldoAkhir'         => 0,
                'labaRugi'                => $labaRugi,
                'neraca'                  => $neraca,
                'worksheetTotals'         => $worksheetTotals,
                'labaRugiBersih'          => $labaRugiBersih,
            ])->with(
                'error',
                'Tidak ada akun Kas atau Bank dengan kode 111 atau 113.'
            );
        }

        $cashCoaIds = $cashAccounts
            ->pluck('id')
            ->map(fn ($accountId) => (int) $accountId)
            ->all();

        /*
        * 2. Saldo awal diambil dari LedgerService agar sama
        * dengan saldo awal pada Buku Besar.
        */
        $totalSaldoAwal = $cashAccounts->sum(
            function ($cashAccount) use ($periode) {
                $ledgerData = $this->ledgerService->getAccountLedger(
                    (int) $cashAccount->id,
                    $periode
                );

                return (float) (
                    $ledgerData['opening_balance'] ?? 0
                );
            }
        );

        /*
        * 3. Ambil akun lawan dari jurnal yang mempunyai akun Kas/Bank.
        *
        * Yang ditampilkan adalah akun lawan, bukan akun 111/113.
        *
        * Dari sisi akun lawan:
        * - kredit = kas masuk;
        * - debit  = kas keluar.
        */
        $journalTransactions = DB::table(
            'general_journal_details as jd_lawan'
        )
            ->join(
                'general_journals as gj',
                'jd_lawan.general_journal_id',
                '=',
                'gj.id'
            )
            ->join(
                'chart_of_account as coa_lawan',
                'jd_lawan.chart_of_account_id',
                '=',
                'coa_lawan.id'
            )
            ->where('gj.perusahaan', $id)
            ->whereYear('gj.tgl_transaksi', $tahun)
            ->whereMonth('gj.tgl_transaksi', $bulan)

            /*
            * Hanya jurnal yang memiliki detail akun Kas/Bank.
            */
            ->whereExists(function ($query) use ($cashCoaIds) {
                $query->select(DB::raw(1))
                    ->from('general_journal_details as jd_kas')
                    ->whereColumn(
                        'jd_kas.general_journal_id',
                        'gj.id'
                    )
                    ->whereIn(
                        'jd_kas.chart_of_account_id',
                        $cashCoaIds
                    );
            })

            /*
            * Jangan jadikan akun Kas/Bank sebagai akun yang ditampilkan.
            */
            ->whereNotIn(
                'jd_lawan.chart_of_account_id',
                $cashCoaIds
            )

            /*
            * Pengamanan tambahan:
            * kode 111 dan 113 tidak boleh ditampilkan.
            */
            ->where('coa_lawan.kode', 'not like', '111%')
            ->where('coa_lawan.kode', 'not like', '113%')

            /*
            * Nama yang mengandung Kas/Bank juga tidak ditampilkan.
            */
            ->whereRaw(
                "LOWER(COALESCE(coa_lawan.nama_akun, '')) NOT LIKE ?",
                ['%kas%']
            )
            ->whereRaw(
                "LOWER(COALESCE(coa_lawan.nama_akun, '')) NOT LIKE ?",
                ['%bank%']
            )
            ->select(
                DB::raw(
                    "LOWER(TRIM(COALESCE(gj.arus, ''))) " .
                    'as jenis_arus_kas'
                ),
                'coa_lawan.kode as kode_lawan',
                'coa_lawan.nama_akun as nama_lawan',

                /*
                * Kredit akun lawan menunjukkan kas masuk.
                */
                DB::raw(
                    'SUM(COALESCE(jd_lawan.kredit, 0)) ' .
                    'as kas_masuk'
                ),

                /*
                * Debit akun lawan menunjukkan kas keluar.
                */
                DB::raw(
                    'SUM(COALESCE(jd_lawan.debit, 0)) ' .
                    'as kas_keluar'
                )
            )
            ->groupBy(
                DB::raw(
                    "LOWER(TRIM(COALESCE(gj.arus, '')))"
                ),
                'coa_lawan.kode',
                'coa_lawan.nama_akun'
            )
            ->orderBy(
                'coa_lawan.kode',
                'asc'
            )
            ->get();

        /*
        * 4. Kelompokkan berdasarkan general_journals.arus.
        */
        $kelompok = [
            'operasional' => [],
            'investasi'   => [],
            'pendanaan'   => [],
        ];

        foreach ($journalTransactions as $trx) {
            $kasMasuk = (float) $trx->kas_masuk;
            $kasKeluar = (float) $trx->kas_keluar;

            $netAmount = $kasMasuk - $kasKeluar;

            if (abs($netAmount) < 0.005) {
                continue;
            }

            $jenisArus = strtolower(trim(
                (string) $trx->jenis_arus_kas
            ));

            /*
            * Hanya klasifikasi valid yang dimasukkan.
            */
            if (!array_key_exists($jenisArus, $kelompok)) {
                continue;
            }

            /*
            * Pengamanan terakhir agar kode 111 dan 113
            * tidak masuk ke hasil.
            */
            $kodeAkun = trim((string) $trx->kode_lawan);

            if (
                str_starts_with($kodeAkun, '111') ||
                str_starts_with($kodeAkun, '113')
            ) {
                continue;
            }

            $kelompok[$jenisArus][] = [
                'kode'       => $kodeAkun,
                'nama_akun'  => $trx->nama_lawan,
                'kas_masuk'  => $kasMasuk,
                'kas_keluar' => $kasKeluar,
                'jumlah'     => $netAmount,
            ];
        }

        $operasional = $kelompok['operasional'];
        $investasi = $kelompok['investasi'];
        $pendanaan = $kelompok['pendanaan'];

        /*
        * 5. Hitung total.
        */
        $totalOperasional = (float) collect($operasional)
            ->sum('jumlah');

        $totalInvestasi = (float) collect($investasi)
            ->sum('jumlah');

        $totalPendanaan = (float) collect($pendanaan)
            ->sum('jumlah');

        $kenaikanPenurunanBersih =
            $totalOperasional +
            $totalInvestasi +
            $totalPendanaan;

        /*
        * Tidak ditambah dengan saldo awal.
        */
        $totalSaldoAkhir = $kenaikanPenurunanBersih;

        return view('finance.report_cash_flow', compact(
            'id',
            'title',
            'periode',
            'operasional',
            'investasi',
            'pendanaan',
            'totalOperasional',
            'totalInvestasi',
            'totalPendanaan',
            'totalSaldoAwal',
            'kenaikanPenurunanBersih',
            'totalSaldoAkhir',
            'labaRugi',
            'neraca',
            'worksheetTotals',
            'labaRugiBersih'
        ));
    }

    // Export Excel
    private function getWorksheetData($id, $periode, TrialBalanceService $trialBalanceService)
    {
        // 1. Ambil data dari Service (Sama persis dengan cara di neracaLajur)
        $trialBalanceData = $trialBalanceService->getTrialBalance($periode, $id);
        $reportData = $trialBalanceData['report_data'] ?? [];
        $trialBalanceKeyed = collect($reportData)->keyBy('kode');

        // 2. Ambil AJP
        $adjustmentJournalSums = DB::table('adjustment_journal_details')
            ->join('adjustment_journals', 'adjustment_journal_details.adjustment_journal_id', '=', 'adjustment_journals.id')
            ->select('chart_of_account_id', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(kredit) as total_kredit'))
            ->whereRaw("to_char(adjustment_journals.tgl_transaksi, 'YYYY-MM') = ?", [$periode])
            ->where('adjustment_journals.perusahaan', $id)
            ->groupBy('chart_of_account_id')
            ->get()
            ->keyBy('chart_of_account_id');

        $coas = DB::table('chart_of_account')->where('is_active', true)->where('perusahaan', $id)->orderBy('kode', 'asc')->get();
        
        $worksheetData = [];
        $totals = ['ns_debit'=>0,'ns_kredit'=>0,'aj_debit'=>0,'aj_kredit'=>0,'nsd_debit'=>0,'nsd_kredit'=>0,'lr_debit'=>0,'lr_kredit'=>0,'n_debit'=>0,'n_kredit'=>0];

        foreach ($coas as $coa) {
            $nsRow = $trialBalanceKeyed->get($coa->kode);
            $aj = $adjustmentJournalSums->get($coa->id) ?? (object)['total_debit' => 0, 'total_kredit' => 0];

            $ns_debit = $nsRow ? (float)$nsRow['saldo_debit'] : 0;
            $ns_kredit = $nsRow ? (float)$nsRow['saldo_kredit'] : 0;
            $aj_debit = (float)$aj->total_debit;
            $aj_kredit = (float)$aj->total_kredit;

            // NSD Logic
            if ($ns_debit > 0 || ($ns_debit == 0 && $ns_kredit == 0 && in_array(substr((string)$coa->kode, 0, 1), ['1', '5', '6']))) {
                $nsd_diff = ($ns_debit + $aj_debit) - $aj_kredit;
                $nsd_debit = $nsd_diff > 0 ? $nsd_diff : 0;
                $nsd_kredit = $nsd_diff < 0 ? abs($nsd_diff) : 0;
            } else {
                $nsd_diff = ($ns_kredit + $aj_kredit) - $aj_debit;
                $nsd_debit = $nsd_diff < 0 ? abs($nsd_diff) : 0;
                $nsd_kredit = $nsd_diff > 0 ? $nsd_diff : 0;
            }

            // Laba Rugi / Neraca
            $firstDigit = substr((string)$coa->kode, 0, 1);
            $lr_debit = in_array($firstDigit, ['4', '5', '6']) ? $nsd_debit : 0;
            $lr_kredit = in_array($firstDigit, ['4', '5', '6']) ? $nsd_kredit : 0;
            $n_debit = !in_array($firstDigit, ['4', '5', '6']) ? $nsd_debit : 0;
            $n_kredit = !in_array($firstDigit, ['4', '5', '6']) ? $nsd_kredit : 0;

            $worksheetData[] = [
                'kode' => $coa->kode, 'nama_akun' => $coa->nama_akun,
                'lr_debit' => $lr_debit, 'lr_kredit' => $lr_kredit,
                'n_debit' => $n_debit, 'n_kredit' => $n_kredit
            ];

            $totals['lr_debit'] += $lr_debit; $totals['lr_kredit'] += $lr_kredit;
            $totals['n_debit'] += $n_debit; $totals['n_kredit'] += $n_kredit;
        }

        return ['worksheetData' => $worksheetData, 'totals' => $totals, 'lr_balance' => $totals['lr_kredit'] - $totals['lr_debit']];
    }

    public function export_laba_rugi(Request $request, TrialBalanceService $trialBalanceService, $id)
    {
        $periode = $request->get('periode', date('Y-m'));
        
        // Memanggil helper yang sama agar data sinkron dengan Web
        $data = $this->getWorksheetData($id, $periode, $trialBalanceService);

        // Filter khusus untuk akun Laba Rugi (kode 4, 5, 6)
        $labaRugiMurni = collect($data['worksheetData'])->filter(function ($row) {
            return in_array(substr((string)$row['kode'], 0, 1), ['4', '5', '6']);
        })->map(function ($row) {
            return [
                'kode'      => $row['kode'],
                'nama_akun' => $row['nama_akun'],
                'debit'     => $row['lr_debit'],
                'kredit'    => $row['lr_kredit']
            ];
        })->toArray();

        $perusahaan = DB::table('perusahaan')->where('id', $id)->first();
        $fileName = 'Laporan_Laba_Rugi_' . Carbon::parse($periode)->format('Y_m') . '.pdf';

        return Pdf::loadView('finance.exports.laba_rugi_pdf', [
            'data' => $labaRugiMurni,
            'periode' => $periode,
            'lr_balance' => $data['lr_balance'],
            'totals' => $data['totals'],
            'namaPerusahaan' => $perusahaan->nama_perusahaan ?? 'Perusahaan',
        ])->setPaper('a4', 'portrait')->download($fileName);
    }

    public function export_neraca(Request $request, TrialBalanceService $trialBalanceService, $id)
    {
        $periode = $request->get('periode', date('Y-m'));
    
        // Panggil helper yang sudah sinkron
        $data = $this->getWorksheetData($id , $periode, $trialBalanceService);

        // Tambahkan filter: hanya ambil akun yang memiliki nilai di n_debit atau n_kredit
        $neracaMurni = collect($data['worksheetData'])
            ->filter(function ($row) {
                // Hanya masukkan akun tipe Neraca (1, 2, 3) yang saldonya tidak nol
                $isTipeNeraca = in_array(substr((string)$row['kode'], 0, 1), ['1', '2', '3']);
                $adaSaldo = ($row['n_debit'] > 0 || $row['n_kredit'] > 0);
                
                return $isTipeNeraca && $adaSaldo;
            })
            ->map(function ($row) {
                return [
                    'kode'      => $row['kode'],
                    'nama_akun' => $row['nama_akun'],
                    'debit'     => $row['n_debit'],
                    'kredit'    => $row['n_kredit']
                ];
            })->toArray();

        $aktiva = collect($neracaMurni)
            ->filter(fn ($row) => str_starts_with((string) $row['kode'], '1'))
            ->map(fn ($row) => array_merge($row, ['jumlah' => $row['debit'] - $row['kredit']]))
            ->values();
        $passiva = collect($neracaMurni)
            ->filter(fn ($row) => in_array(substr((string) $row['kode'], 0, 1), ['2', '3']))
            ->map(fn ($row) => array_merge($row, ['jumlah' => $row['kredit'] - $row['debit']]))
            ->values();

        $passiva->push([
            'kode' => '',
            'nama_akun' => $data['lr_balance'] >= 0 ? 'Laba Bersih' : 'Rugi Bersih',
            'jumlah' => $data['lr_balance'],
        ]);

        $perusahaan = DB::table('perusahaan')->where('id', $id)->first();
        $fileName = 'Laporan_Neraca_' . Carbon::parse($periode)->format('Y_m') . '.pdf';

        return Pdf::loadView('finance.exports.neraca_pdf', [
            'aktiva' => $aktiva,
            'passiva' => $passiva,
            'periode' => $periode,
            'namaPerusahaan' => $perusahaan->nama_perusahaan ?? 'Perusahaan',
        ])->setPaper('a4', 'landscape')->download($fileName);
    }

    public function export_cash_flow(Request $request, $id)
    {
        $periode = $request->get('periode', date('Y-m'));

        // --- LOGIKA DATA (Copy-paste dari arus_kas) ---
        $cashCoaIds = DB::table('chart_of_account')
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereRaw('LOWER(nama_akun) LIKE ?', ['%kas%'])
                    ->orWhereRaw('LOWER(nama_akun) LIKE ?', ['%bank%']);
            })
            ->where('perusahaan', $id)
            ->whereRaw('LOWER(nama_akun) NOT LIKE ?', ['%brankas%'])
            ->pluck('id')->toArray();

        $totalSaldoAwal = DB::table('opening_balances')
            ->where('periode', $periode)
            ->whereIn('chart_of_account_id', $cashCoaIds)
            ->where('perusahaan', $id)
            ->sum('saldo') ?? 0;

        $journalTransactions = DB::table('general_journal_details as jd_kas')
            ->join('general_journals', 'jd_kas.general_journal_id', '=', 'general_journals.id')
            ->join('general_journal_details as jd_lawan', function($join) {
                $join->on('jd_kas.general_journal_id', '=', 'jd_lawan.general_journal_id')
                    ->on('jd_kas.id', '!=', 'jd_lawan.id');
            })
            ->join('chart_of_account as coa_lawan', 'jd_lawan.chart_of_account_id', '=', 'coa_lawan.id')
            ->whereRaw("to_char(general_journals.tgl_transaksi, 'YYYY-MM') = ?", [$periode])
            ->whereIn('jd_kas.chart_of_account_id', $cashCoaIds)
            ->whereNotIn('jd_lawan.chart_of_account_id', $cashCoaIds)
            ->where('general_journals.perusahaan', $id)
            ->select('general_journals.arus', 'coa_lawan.kode', 'coa_lawan.nama_akun', 
                    DB::raw('SUM(jd_kas.debit) as kas_masuk'), DB::raw('SUM(jd_kas.kredit) as kas_keluar'))
            ->groupBy('general_journals.arus', 'coa_lawan.kode', 'coa_lawan.nama_akun')
            ->get();

        $operasional = []; $investasi = []; $pendanaan = [];
        foreach ($journalTransactions as $trx) {
            $netAmount = $trx->kas_masuk - $trx->kas_keluar;
            if (round($netAmount, 2) == 0) continue;
            
            $item = ['kode' => $trx->kode, 'nama_akun' => $trx->nama_akun, 'jumlah' => $netAmount];
            if ($trx->arus === 'operasional') $operasional[] = $item;
            elseif ($trx->arus === 'investasi') $investasi[] = $item;
            elseif ($trx->arus === 'pendanaan') $pendanaan[] = $item;
        }

        $totalOperasional = collect($operasional)->sum('jumlah');
        $totalInvestasi = collect($investasi)->sum('jumlah');
        $totalPendanaan = collect($pendanaan)->sum('jumlah');
        $kenaikanPenurunanBersih = $totalOperasional + $totalInvestasi + $totalPendanaan;
        $totalSaldoAkhir = $kenaikanPenurunanBersih;
        $perusahaan = DB::table('perusahaan')->where('id', $id)->first();
        $fileName = 'Laporan_Arus_Kas_' . Carbon::parse($periode)->format('Y_m') . '.pdf';

        return Pdf::loadView('finance.exports.cash_flow_pdf', compact(
            'operasional',
            'investasi',
            'pendanaan',
            'periode',
            'totalOperasional',
            'totalInvestasi',
            'totalPendanaan',
            'totalSaldoAkhir'
        ) + [
            'namaPerusahaan' => $perusahaan->nama_perusahaan ?? 'Perusahaan',
        ])->setPaper('a4', 'portrait')->download($fileName);
    }

    public function jurnal_penutup(Request $request, TrialBalanceService $trialBalanceService, $id)
    {
        $periode = $request->get('periode', date('Y-m'));
        $lrData = $this->getLabaRugiData($request, $trialBalanceService, $id);
        // Jika tidak ada data, kirim variabel kosong dengan nilai 0
        $title = "Jurnal Penutup";
        $isLocked = DB::table('period_locks')
            ->where('periode', $periode)
            ->where('perusahaan', $id)
            ->where('is_locked', true)
            ->exists();
        
        if (empty($lrData['data'])) {
            return view('finance.jurnal_penutup', [
                'periode' => $periode, 'data' => [], 'modals' => 0, 'pendapatan' => 0, 
                'jumlah' => 0, 'totalDebet' => 0, 'totalKredit' => 0, 'lr_balance' => 0,
                'title' => $title, 'isLocked' => $isLocked, 'id' => $id
            ]);
        }


        $modal = $lrData['totals']['debit'] - $lrData['totals']['kredit'];
        $pendapatans = $lrData['totals']['kredit'];
        $data = $lrData['data'];
        $totalDebetData = collect($data)->sum('debit');
        $totalKreditData = collect($data)->sum('kredit');
        $jumlah = $totalDebetData + $totalKreditData;
        
        // Hitung total keseluruhan
        $totalDebet = abs($modal < 0 ? $modal : 0) + abs($pendapatans) + abs($lrData['balance']) + $totalKreditData;
        $totalKredit = abs($modal >= 0 ? $modal : 0) + abs($pendapatans) + abs($lrData['balance']) + $totalDebetData;
                        
        return view('finance.jurnal_penutup', [
            'periode' => $periode, 'pendapatan' => $pendapatans, 'totals' => $lrData['totals'],
            'lr_balance' => $lrData['balance'], 'modals' => $modal, 'data' => $data,
            'totalKredit' => $totalKredit, 'totalDebet' => $totalDebet, 'jumlah' => $jumlah,
            'title' => $title, 'isLocked' => $isLocked, 'id' => $id
        ]);
    }

    public function lockPeriode(Request $request, $id)
    {
        $request->validate(['periode' => 'required']);

        $periode = $request->periode;

        // Ambil data status lama sebelum di-update (jika sudah ada) untuk keperluan log audit
        $oldLock = DB::table('period_locks')->where('periode', $periode)->first();

        // Lakukan update atau insert status kunci periode
        DB::table('period_locks')->updateOrInsert(
            ['periode' => $periode],
            [
                'is_locked'  => true,
                'locked_at'  => now(),
                'locked_by'  => Auth::id(),
                'updated_at' => now(),
                'created_at' => $oldLock ? $oldLock->created_at : now(),
                'perusahaan' => $id
            ]
        );

        // Ambil data terbaru setelah proses
        $newLock = DB::table('period_locks')->where('periode', $periode)->first();

        // [BARU] Catat ke Audit Trail
        AuditService::log(
            'UPDATED',
            'Periode Akuntansi',
            'Menutup / Mengunci periode akuntansi bulan: ' . $periode,
            $oldLock ? (array) $oldLock : null,
            (array) $newLock
        );

        return redirect()->back()->with('success', 'Periode ' . $periode . ' berhasil ditutup.');
    }

    private function getLabaRugiData($request, $trialBalanceService, $id)
    {
        $periode = $request->get('periode', date('Y-m'));
        $startDate = $periode . '-01';
        $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));
        // 1. Ambil data dari Neraca Saldo
        $reportData = collect($trialBalanceService->getTrialBalance($periode, $id)['report_data'] ?? [])->keyBy('kode');
        
        // 2. Ambil semua akun yang memiliki transaksi di periode ini
        $lastDetails = DB::table('general_journal_details')
            ->join('general_journals', 'general_journal_details.general_journal_id', '=', 'general_journals.id')
            ->selectRaw('DISTINCT ON (chart_of_account_id) chart_of_account_id, keterangan_item')
            ->where('general_journals.tgl_transaksi', '>=', $startDate)
            ->where('general_journals.tgl_transaksi', '<', $endDate)
            ->where('general_journals.perusahaan', $id)
            ->whereNotNull('keterangan_item')
            ->orderBy('chart_of_account_id')
            ->orderBy('general_journals.tgl_transaksi', 'desc')
            ->get()
            ->keyBy('chart_of_account_id');

        // 3. Ambil total AJP
        $adjustmentJournalSums = DB::table('adjustment_journal_details')
            ->join('adjustment_journals', 'adjustment_journal_details.adjustment_journal_id', '=', 'adjustment_journals.id')
            ->select('chart_of_account_id', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(kredit) as total_kredit'))
            ->where('adjustment_journals.tgl_transaksi', '>=', $startDate)
            ->where('adjustment_journals.tgl_transaksi', '<', $endDate)
            ->where('adjustment_journals.perusahaan', $id)
            ->groupBy('chart_of_account_id')
            ->get()
            ->keyBy('chart_of_account_id');

        // Filter COA hanya yang relevan dengan transaksi periode ini
        $activeCoaIds = $reportData->pluck('chart_of_account_id')->merge($lastDetails->keys())->unique();
        $coas = DB::table('chart_of_account')->whereIn('id', $activeCoaIds)->where('is_active', true)->orderBy('kode', 'asc')->get();

        $labaRugiData = [];
        $totalLR = ['debit' => 0, 'kredit' => 0];

        foreach ($coas as $coa) {
            if (!in_array(substr((string)$coa->kode, 0, 1), ['3', '4', '5'])) continue;

            $nsRow = $reportData->get($coa->kode);
            $detailRow = $lastDetails->get($coa->id);

            $ns_debit = $nsRow ? (float)$nsRow['saldo_debit'] : 0;
            $ns_kredit = $nsRow ? (float)$nsRow['saldo_kredit'] : 0;

            $nsd = (float)$ns_debit - (float)$ns_kredit;
            $nsd_debit = $nsd >= 0 ? $nsd : 0; 
            $nsd_kredit = $nsd < 0 ? abs($nsd) : 0;

            if ($nsd_debit != 0 || $nsd_kredit != 0) {
                $labaRugiData[] = [
                    'keterangan' => $detailRow ? $detailRow->keterangan_item : 'Penutupan Buku', 
                    'debit' => $nsd_debit, 
                    'kredit' => $nsd_kredit
                ];
                $totalLR['debit'] += $nsd_debit;
                $totalLR['kredit'] += $nsd_kredit;
            }
        }
        return ['data' => $labaRugiData, 'totals' => $totalLR, 'balance' => $totalLR['kredit'] - $totalLR['debit']];
    }
}
