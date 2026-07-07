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

    public function pilih_akun(Request $request)
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
                DB::raw('COALESCE(SUM(ji.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(ji.kredit), 0) as total_kredit')
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

        return view('finance.bb.pilih_akun', compact('title', 'accounts', 'periode'));
    }

    public function show(Request $request, $id)
    {
        $title = "Detail Buku Besar";
        
        // Ambil parameter periode dari URL, default ke bulan berjalan jika kosong
        $periode = $request->input('periode', date('Y-m'));

        // Panggil fungsi hitung dari Service
        $ledgerData = $this->ledgerService->getAccountLedger((int) $id, $periode);

        return view('finance.bb.show', compact('title', 'periode', 'ledgerData'));
    }

    public function neracaSaldo(Request $request)
    {
        $title = "Neraca Saldo";
        $periode = $request->input('periode', date('Y-m'));

        // Panggil data langsung dari Service
        $data = $this->trialBalanceService->getTrialBalance($periode);

        return view('finance.neraca_saldo', [
            'title'            => $title,
            'periode'          => $periode,
            'reportData'       => $data['report_data'],
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

    public function neracaLajur(Request $request, TrialBalanceService $trialBalanceService)
    {
        $title = "Neraca Lajur";
        
        // Disamakan menggunakan filter periode bulanan (YYYY-MM) sesuai service Neraca Saldo
        $periode = $request->get('periode', date('Y-m'));

        // 1. Ambil data Neraca Saldo ter-update langsung dari Service terintegrasi
        $trialBalanceData = $trialBalanceService->getTrialBalance($periode);
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
            ->groupBy('chart_of_account_id')
            ->get()
            ->keyBy('chart_of_account_id');

        // 3. Ambil seluruh data COA aktif (Akun baru tanpa transaksi akan tetap masuk ke sini)
        $coas = DB::table('chart_of_account')
            ->where('is_active', true)
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

        return view('finance.neraca_lajur', compact('worksheetData', 'totals', 'lr_balance', 'title', 'periode'));
    }

    public function arus_kas(Request $request)
    {
        $title = "Laporan Arus Kas (Metode Langsung)";
        
        // Filter periode bulanan (YYYY-MM), default bulan berjalan saat ini
        $periode = $request->get('periode', date('Y-m'));

        // 1. Ambil ID semua akun yang merupakan Kas atau Bank berdasarkan string nama akun
        $cashCoaIds = DB::table('chart_of_account')
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereRaw('LOWER(nama_akun) LIKE ?', ['%kas%'])
                    ->orWhereRaw('LOWER(nama_akun) LIKE ?', ['%bank%']);
            })
            ->whereRaw('LOWER(nama_akun) NOT LIKE ?', ['%brankas%'])
            ->pluck('id')
            ->toArray();

        // Jika tidak ada COA yang memenuhi kriteria, kembalikan ke view dengan data kosong
        if (empty($cashCoaIds)) {
            return view('finance.report_cash_flow', [
                'title' => $title, 'periode' => $periode, 'operasional' => [], 'investasi' => [], 'pendanaan' => [],
                'totalOperasional' => 0, 'totalInvestasi' => 0, 'totalPendanaan' => 0,
                'totalSaldoAwal' => 0, 'kenaikanPenurunanBersih' => 0, 'totalSaldoAkhir' => 0
            ])->with('error', 'Tidak ada akun Kas atau Bank yang terdeteksi dalam sistem.');
        }

        // 2. Hitung Saldo Awal Akumulatif dari tabel opening_balances
        $totalSaldoAwal = DB::table('opening_balances')
            ->where('periode', $periode)
            ->whereIn('chart_of_account_id', $cashCoaIds)
            ->sum('saldo') ?? 0;

        // 3. Query Utama: Ditambahkan pengambilan kolom general_journals.arus
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
            ->select(
                'general_journals.arus as jenis_arus_kas', // Mengambil kolom tipe arus kas dari header jurnal
                'coa_lawan.kode as kode_lawan',
                'coa_lawan.nama_akun as nama_lawan',
                DB::raw('SUM(jd_kas.debit) as kas_masuk'),
                DB::raw('SUM(jd_kas.kredit) as kas_keluar')
            )
            ->groupBy('general_journals.arus', 'coa_lawan.kode', 'coa_lawan.nama_akun')
            ->orderBy('coa_lawan.kode', 'asc')
            ->get();
        
        // 4. Pengelompokkan Data ke dalam 3 Aktivitas Arus Kas
        $operasional = [];
        $investasi = [];
        $pendanaan = [];

        foreach ($journalTransactions as $trx) {
            // Murni pergerakan uang fisik pada Kas/Bank: Debit mengurangi Kredit
            $netAmount = $trx->kas_masuk - $trx->kas_keluar;

            if (round($netAmount, 2) == 0) continue;

            $itemData = [
                'kode' => $trx->kode_lawan,
                'nama_akun' => $trx->nama_lawan,
                'jumlah' => $netAmount
            ];

            // LOGIKA KLASIFIKASI BARU: Murni berdasarkan input kolom 'arus' dari Jurnal Umum
            if ($trx->jenis_arus_kas === 'operasional') {
                $operasional[] = $itemData;
            } 
            elseif ($trx->jenis_arus_kas === 'investasi') {
                $investasi[] = $itemData;
            } 
            elseif ($trx->jenis_arus_kas === 'pendanaan') {
                $pendanaan[] = $itemData;
            }
        }

        // 5. Kalkulasi Total Akhir
        $totalOperasional = collect($operasional)->sum('jumlah');
        $totalInvestasi = collect($investasi)->sum('jumlah');
        $totalPendanaan = collect($pendanaan)->sum('jumlah');

        $kenaikanPenurunanBersih = $totalOperasional + $totalInvestasi + $totalPendanaan;
        $totalSaldoAkhir = $totalSaldoAwal + $kenaikanPenurunanBersih;

        return view('finance.report_cash_flow', compact(
            'title', 'periode', 'operasional', 'investasi', 'pendanaan',
            'totalOperasional', 'totalInvestasi', 'totalPendanaan',
            'totalSaldoAwal', 'kenaikanPenurunanBersih', 'totalSaldoAkhir'
        ));
    }

    // Export Excel
    private function getWorksheetData($periode, TrialBalanceService $trialBalanceService)
    {
        // 1. Ambil data dari Service (Sama persis dengan cara di neracaLajur)
        $trialBalanceData = $trialBalanceService->getTrialBalance($periode);
        $reportData = $trialBalanceData['report_data'] ?? [];
        $trialBalanceKeyed = collect($reportData)->keyBy('kode');

        // 2. Ambil AJP
        $adjustmentJournalSums = DB::table('adjustment_journal_details')
            ->join('adjustment_journals', 'adjustment_journal_details.adjustment_journal_id', '=', 'adjustment_journals.id')
            ->select('chart_of_account_id', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(kredit) as total_kredit'))
            ->whereRaw("to_char(adjustment_journals.tgl_transaksi, 'YYYY-MM') = ?", [$periode])
            ->groupBy('chart_of_account_id')
            ->get()
            ->keyBy('chart_of_account_id');

        $coas = DB::table('chart_of_account')->where('is_active', true)->orderBy('kode', 'asc')->get();
        
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

    public function export_laba_rugi(Request $request, TrialBalanceService $trialBalanceService)
    {
        $periode = $request->get('periode', date('Y-m'));
        
        // Memanggil helper yang sama agar data sinkron dengan Web
        $data = $this->getWorksheetData($periode, $trialBalanceService);

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

        $fileName = 'Laporan_Laba_Rugi_' . Carbon::parse($periode)->format('Y_m') . '.xlsx';

        return Excel::download(new class($labaRugiMurni, $periode, $data['lr_balance'], $data['totals']) implements \Maatwebsite\Excel\Concerns\FromView {
            private $data; private $periode; private $balance; private $totals;

            public function __construct($data, $periode, $balance, $totals) {
                $this->data = $data; $this->periode = $periode; $this->balance = $balance; $this->totals = $totals;
            }

            public function view(): \Illuminate\Contracts\View\View {
                return view('finance.exports.laba_rugi_excel', [
                    'data'       => $this->data,
                    'periode'    => $this->periode,
                    'lr_balance' => $this->balance,
                    'totals'     => $this->totals
                ]);
            }
        }, $fileName);
    }

    public function export_neraca(Request $request, TrialBalanceService $trialBalanceService)
    {
        $periode = $request->get('periode', date('Y-m'));
    
        // Panggil helper yang sudah sinkron
        $data = $this->getWorksheetData($periode, $trialBalanceService);

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

        $fileName = 'Laporan_Neraca_' . Carbon::parse($periode)->format('Y_m') . '.xlsx';

        return Excel::download(new class($neracaMurni, $periode, $data['lr_balance'], $data['totals']) implements \Maatwebsite\Excel\Concerns\FromView {
            private $data; private $periode; private $balance; private $totals;

            public function __construct($data, $periode, $balance, $totals) {
                $this->data = $data; $this->periode = $periode; $this->balance = $balance; $this->totals = $totals;
            }

            public function view(): \Illuminate\Contracts\View\View {
                return view('finance.exports.neraca_excel', [
                    'data'       => $this->data,
                    'periode'    => $this->periode,
                    'lr_balance' => $this->balance,
                    'totals'     => $this->totals
                ]);
            }
        }, $fileName);
    }

    public function export_cash_flow(Request $request)
    {
        $periode = $request->get('periode', date('Y-m'));

        // --- LOGIKA DATA (Copy-paste dari arus_kas) ---
        $cashCoaIds = DB::table('chart_of_account')
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereRaw('LOWER(nama_akun) LIKE ?', ['%kas%'])
                    ->orWhereRaw('LOWER(nama_akun) LIKE ?', ['%bank%']);
            })
            ->whereRaw('LOWER(nama_akun) NOT LIKE ?', ['%brankas%'])
            ->pluck('id')->toArray();

        $totalSaldoAwal = DB::table('opening_balances')
            ->where('periode', $periode)
            ->whereIn('chart_of_account_id', $cashCoaIds)
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
        $totalSaldoAkhir = $totalSaldoAwal + $kenaikanPenurunanBersih;
        // --- DOWNLOAD EXCEL (Pola yang sama dengan export_neraca Anda) ---
        $fileName = 'Laporan_Arus_Kas_' . \Carbon\Carbon::parse($periode)->format('Y_m') . '.xlsx';
        // dd($totalSaldoAkhir);
        return Excel::download(new class($operasional, $investasi, $pendanaan, $periode, $totalOperasional, $totalInvestasi, $totalPendanaan, $totalSaldoAkhir) implements \Maatwebsite\Excel\Concerns\FromView, \Maatwebsite\Excel\Concerns\WithTitle {
            private $op, $inv, $pen, $per, $totOp, $totInv, $totPen, $totAkhir;

            public function __construct($op, $inv, $pen, $per, $totOp, $totInv, $totPen, $totAkhir) {
                $this->op = $op; $this->inv = $inv; $this->pen = $pen; $this->per = $per;
                $this->totOp = $totOp; $this->totInv = $totInv; $this->totPen = $totPen; $this->totAkhir = $totAkhir;
            }

            public function title(): string {
                return 'Arus Kas';
            }

            public function view(): \Illuminate\Contracts\View\View {
                return view('finance.exports.cash_flow_excel', [
                    'operasional' => $this->op, 'investasi' => $this->inv, 'pendanaan' => $this->pen,
                    'periode' => $this->per, 'totalOperasional' => $this->totOp, 
                    'totalInvestasi' => $this->totInv, 'totalPendanaan' => $this->totPen,
                    'totalSaldoAkhir' => $this->totAkhir
                ]);
            }
        }, $fileName);
    }

    public function jurnal_penutup(Request $request, TrialBalanceService $trialBalanceService)
    {
        $periode = $request->get('periode', date('Y-m'));
        $lrData = $this->getLabaRugiData($request, $trialBalanceService);
        // Jika tidak ada data, kirim variabel kosong dengan nilai 0
        $title = "Jurnal Penutup";
        $isLocked = DB::table('period_locks')
            ->where('periode', $periode)
            ->where('is_locked', true)
            ->exists();
        
        if (empty($lrData['data'])) {
            return view('finance.jurnal_penutup', [
                'periode' => $periode, 'data' => [], 'modals' => 0, 'pendapatan' => 0, 
                'jumlah' => 0, 'totalDebet' => 0, 'totalKredit' => 0, 'lr_balance' => 0,
                'title' => $title, 'isLocked' => $isLocked
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
            'title' => $title, 'isLocked' => $isLocked,
        ]);
    }

    public function lockPeriode(Request $request)
    {
        $request->validate(['periode' => 'required']);

        DB::table('period_locks')->updateOrInsert(
            ['periode' => $request->periode],
            [
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by' => Auth::id(),
                'created_at' => now()
            ]
        );

        return redirect()->back()->with('success', 'Periode ' . $request->periode . ' berhasil ditutup.');
    }

    private function getLabaRugiData($request, $trialBalanceService)
    {
        $periode = $request->get('periode', date('Y-m'));
        $startDate = $periode . '-01';
        $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));
        // 1. Ambil data dari Neraca Saldo
        $reportData = collect($trialBalanceService->getTrialBalance($periode)['report_data'] ?? [])->keyBy('kode');
        
        // 2. Ambil semua akun yang memiliki transaksi di periode ini
        $lastDetails = DB::table('general_journal_details')
            ->join('general_journals', 'general_journal_details.general_journal_id', '=', 'general_journals.id')
            ->selectRaw('DISTINCT ON (chart_of_account_id) chart_of_account_id, keterangan_item')
            ->where('general_journals.tgl_transaksi', '>=', $startDate)
            ->where('general_journals.tgl_transaksi', '<', $endDate)
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
