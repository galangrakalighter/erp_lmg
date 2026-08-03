<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payrol;
use App\Models\PayrolDetail;
use App\Models\ChartOfAccount;
use App\Services\GeneralJournalService;
use Illuminate\Support\Facades\DB;
use App\Services\AuditService;
class PayrollController extends Controller
{
    protected $journalService;

    public function __construct(GeneralJournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index($id){
        $title = 'Payroll';

        $user = User::where('isAdmin', false)->get();

        $account = ChartOfAccount::whereIn('category', ['kewajiban', 'beban'])->where('perusahaan', $id)->where('is_active', true)->get();
        $accountKewajiban = ChartOfAccount::where('category', 'kewajiban')->where('perusahaan', $id)->where('is_active', true)->get();

        return view('finance.payroll', compact('title', 'user', 'account', 'accountKewajiban', 'id'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input Data
        $validatedData = $request->validate([
            'user_id'      => 'required',
            'total_amount' => 'required|numeric|min:0',
            'payroll_date' => 'required|date',
            'paid_amount'  => 'nullable|numeric|min:0',
            'status'       => 'required|string',
            'keterangan'   => 'nullable|string',
            'coa'          => 'required|integer|exists:chart_of_account,id',
            'perusahaan'   => 'required'
        ]);

        // 2. Proteksi Periode: Cek apakah periode tanggal payroll terkunci
        $periode = date('Y-m', strtotime($request->payroll_date));
        $isLocked = DB::table('period_locks')
            ->where('periode', $periode)
            ->where('is_locked', true)
            ->exists();

        if ($isLocked) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat payroll! Periode ' . $periode . ' sudah ditutup dan terkunci.');
        }

        $tanggalFormat = date('Ymd', strtotime($request->payroll_date));
        $jumlahPayrol = Payrol::where('user_id', $request->user_id)->where('perusahaan', $request->perusahaan)->count() + 1;
        $urutanInvoice = sprintf('%04d', $jumlahPayrol);

        $noInvoice = "PYR/{$tanggalFormat}/{$request->user_id}/{$urutanInvoice}";
        
        $paidAmount = (float) ($request->paid_amount ?? 0);
        $totalAmount = (float) $request->total_amount;

        $data = [
            'user_id'      => $request->user_id,
            'total_amount' => $totalAmount,
            'payroll_date' => $request->payroll_date,
            'paid_amount'  => $paidAmount,
            'status'       => $request->status,
            'keterangan'   => $request->keterangan,
            'invoice'      => $noInvoice,
            'perusahaan'   => $request->perusahaan 
        ];

        $dataBaru = [
            'tgl_transaksi'      => $request->payroll_date,
            'no_invoice'         => $noInvoice,
            'keterangan'         => $request->keterangan ?? 'Payroll ' . $noInvoice,
            'cash_flow_activity' => 'operasional',
            'status'             => 'draft',
            'perusahaan'         => $request->perusahaan,
            'items'              => [
                [
                    'chart_of_account_id' => $request->coa,
                    'keterangan_item'     => $request->keterangan ?? 'Gaji & Tunjangan Karyawan',
                    'debit'               => $paidAmount,
                    'kredit'              => $totalAmount
                ]
            ]
        ];

        try {
            DB::beginTransaction();

            // 3. Simpan Jurnal & Payroll
            $journalId = $this->journalService->storeJournal($dataBaru);
            $payroll = Payrol::create($data);

            DB::commit();

            // 4. Catat ke Audit Trail
            AuditService::log(
                'CREATED',
                'Manajemen Payroll',
                'Membuat data payroll baru dengan nomor: ' . $noInvoice,
                null,
                [
                    'payroll'    => $payroll->toArray(),
                    'journal_id' => $journalId
                ]
            );

            return back()->with('success', 'Payroll Dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat payroll: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $payroll = Payrol::findOrFail($id);

        // 1. Proteksi Periode: Cek apakah periode tanggal payroll terkunci
        $payrollDate = $payroll->payroll_date ?? ($payroll->created_at ? date('Y-m', strtotime($payroll->created_at)) : date('Y-m'));
        $periode = date('Y-m', strtotime($payrollDate));
        $isLocked = DB::table('period_locks')
            ->where('periode', $periode)
            ->where('is_locked', true)
            ->exists();

        if ($isLocked) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui payroll! Periode ' . $periode . ' sudah ditutup dan terkunci.');
        }

        // Buat aturan validasi dinamis berdasarkan status payroll saat ini di database
        $rules = [
            'total_amount' => 'required|numeric|min:0',
        ];

        // Jika statusnya 'unpaid', wajibkan juga field-field lainnya karena form lengkap dimunculkan
        if ($payroll->status === 'unpaid') {
            $rules['coa']          = 'required|integer'; // Sesuaikan dengan tabel COA Anda
            $rules['user_id']      = 'required';
            $rules['payroll_date'] = 'required|date';
            $rules['status']       = 'required|in:unpaid,partial,paid';
        }

        $request->validate($rules);

        // Ambil data lama sebelum diubah untuk keperluan audit log
        $oldData = $payroll->toArray();
        $payrollNumber = $payroll->invoice ?? ('Payroll ID: ' . $id);

        try {
            // Proses penyimpanan data dalam transaksi
            DB::transaction(function () use ($request, $payroll) {
                
                // Selalu update total amount karena field ini selalu ada di form
                $payroll->total_amount = $request->total_amount;

                // Jika statusnya 'unpaid', update juga field lainnya yang dikirim dari form lengkap
                if ($payroll->status === 'unpaid') {
                    $payroll->chart_of_account_id = $request->coa; // Sesuaikan kolom database
                    $payroll->user_id             = $request->user_id;
                    $payroll->payroll_date        = $request->payroll_date;
                    $payroll->paid_amount         = $request->paid_amount ?? $payroll->paid_amount;
                    $payroll->status              = $request->status;
                    $payroll->keterangan          = $request->keterangan;
                } else {
                    // Jika bukan unpaid, sesuaikan status secara otomatis jika total tagihan berubah menjadi kurang dari paid_amount
                    if ($payroll->paid_amount >= $request->total_amount) {
                        $payroll->status = 'paid';
                    } elseif ($payroll->paid_amount > 0 && $payroll->paid_amount < $request->total_amount) {
                        $payroll->status = 'partial';
                    }
                }

                $payroll->save();
            });

            // Ambil data terbaru setelah update
            $payroll->refresh();
            $newData = $payroll->toArray();

            // [BARU] Catat ke Audit Trail
            AuditService::log(
                'UPDATED',
                'Manajemen Payroll',
                'Memperbarui data payroll dengan nomor/referensi: ' . $payrollNumber,
                $oldData,
                $newData
            );

            return redirect()->back()->with('success', 'Data payroll berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $payrol = Payrol::find($id);

        if (!$payrol) {
            return redirect()->back()->with('error', 'Data payroll tidak ditemukan!');
        }

        // 1. Proteksi Periode: Cek apakah periode tanggal payroll terkunci
        $payrollDate = $payrol->payroll_date ?? ($payrol->created_at ? date('Y-m', strtotime($payrol->created_at)) : date('Y-m'));
        $periode = date('Y-m', strtotime($payrollDate));
        $isLocked = DB::table('period_locks')
            ->where('periode', $periode)
            ->where('is_locked', true)
            ->exists();

        if ($isLocked) {
            return redirect()->back()->with('error', 'Gagal menghapus! Periode ' . $periode . ' sudah ditutup dan terkunci.');
        }

        // 2. Kumpulkan data lama sebelum dihapus untuk keperluan log audit
        $payrolDetails = PayrolDetail::where('payroll_id', $id)->get();
        
        $associatedJournals = [];
        foreach ($payrolDetails as $pd) {
            $jurnalUmum = DB::table('general_journals')->where('no_invoice', $pd->invoice)->first();
            if ($jurnalUmum) {
                $details = DB::table('general_journal_details')->where('general_journal_id', $jurnalUmum->id)->get();
                $associatedJournals[] = [
                    'header'  => (array) $jurnalUmum,
                    'details' => $details->map(function($d) { return (array)$d; })->toArray()
                ];
            }
        }

        $jurnalUmumPayrol = DB::table('general_journals')->where('no_invoice', $payrol->invoice)->first();
        if ($jurnalUmumPayrol) {
            $detailsPayroll = DB::table('general_journal_details')->where('general_journal_id', $jurnalUmumPayrol->id)->get();
            $associatedJournals[] = [
                'header'  => (array) $jurnalUmumPayrol,
                'details' => $detailsPayroll->map(function($d) { return (array)$d; })->toArray()
            ];
        }

        $oldData = [
            'payroll'         => $payrol->toArray(),
            'payroll_details' => $payrolDetails->map(function($p) { return $p->toArray(); })->toArray(),
            'journals'        => $associatedJournals
        ];

        $payrollNumber = $payrol->invoice ?? ('Payroll ID: ' . $id);

        try {
            DB::beginTransaction();

            // 3. Eksekusi penghapusan relasi detail payroll dan jurnal terkait
            foreach($payrolDetails as $pd){
                $jurnalUmum = DB::table('general_journals')->where('no_invoice', $pd->invoice)->first();
                if ($jurnalUmum) {
                    DB::table('general_journal_details')->where('general_journal_id', $jurnalUmum->id)->delete();
                    DB::table('general_journals')->where('id', $jurnalUmum->id)->delete();
                }
            }

            if($jurnalUmumPayrol){
                DB::table('general_journal_details')->where('general_journal_id', $jurnalUmumPayrol->id)->delete();
                DB::table('general_journals')->where('id', $jurnalUmumPayrol->id)->delete();
            }

            PayrolDetail::where('payroll_id', $id)->delete();
            $payrol->delete();

            DB::commit();

            // 4. Catat ke Audit Trail
            AuditService::log(
                'DELETED',
                'Manajemen Payroll',
                'Menghapus data payroll beserta rincian dan jurnal terkait dengan nomor/referensi: ' . $payrollNumber,
                $oldData,
                null
            );

            return redirect()->back()->with('success', 'Data payroll beserta jurnal terkait berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function getDataPayroll(Request $request)
    {
        $query = Payrol::with('user');

        if ($request->filled('tahun')) {
            $query->whereYear('payroll_date', $request->tahun);
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('payroll_date', $request->bulan);
        }

        $payrolls = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $payrolls
        ]);
    }

    public function payment(Request $request, $id)
    {
        // 1. Validasi Input Data
        $request->validate([
            'payroll_id'   => 'required|exists:payroll,id', // Sesuaikan nama tabel jika berbeda
            'amount'       => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'coa'          => 'required|integer|exists:chart_of_account,id',
            'keterangan'   => 'nullable|string',
        ]);

        // 2. Proteksi Periode: Cek apakah periode tanggal pembayaran terkunci
        $periode = date('Y-m', strtotime($request->payment_date));
        $isLocked = DB::table('period_locks')
            ->where('periode', $periode)
            ->where('is_locked', true)
            ->exists();

        if ($isLocked) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memproses pembayaran! Periode ' . $periode . ' sudah ditutup dan terkunci.');
        }

        // Ambil data payroll lama sebelum pembayaran dicatat (untuk keperluan log audit)
        $invoice = Payrol::findOrFail($request->payroll_id);
        $oldPayrollData = $invoice->toArray();

        $tanggalFormat = date('Ymd', strtotime($request->payment_date));
        $jumlahPayrol = PayrolDetail::where('payroll_id', $request->payroll_id)->count() + 1;
        $urutanInvoice = sprintf('%04d', $jumlahPayrol);

        $noInvoice = "PYR/{$tanggalFormat}/{$invoice->user_id}/{$urutanInvoice}";
        
        $amount = (float) $request->amount;
        $keterangan = $request->keterangan ?? 'Pembayaran Payroll ' . $invoice->invoice;
        $paymentDate = $request->payment_date;

        $dataBaru = [
            'tgl_transaksi'      => $paymentDate,
            'no_invoice'         => $noInvoice,
            'keterangan'         => $keterangan,
            'cash_flow_activity' => 'operasional',
            'status'             => 'draft',
            'perusahaan'         => $invoice->perusahaan,
            'items'              => [
                [
                    'chart_of_account_id' => $request->coa,
                    'keterangan_item'     => $keterangan,
                    'debit'               => $amount,
                    'kredit'              => 0
                ]
            ]
        ];

        try {
            // 3. Simpan Jurnal Pembayaran via Service
            $journalId = $this->journalService->storeJournal($dataBaru);

            $payrolDetailRecord = null;
            DB::transaction(function () use ($request, $amount, $noInvoice, $paymentDate, &$payrolDetailRecord, &$invoice) {
                
                $payrolDetailRecord = PayrolDetail::create([
                    'payroll_id'   => $request->payroll_id,
                    'payment_date' => $paymentDate,
                    'amount'       => $amount,
                    'invoice'      => $noInvoice
                ]);

                $invoice->paid_amount += $amount;
                $invoice->status = ($invoice->paid_amount >= $invoice->total_amount) ? 'paid' : 'partial';
                $invoice->save();
            });

            // Ambil data payroll terbaru setelah update
            $invoice->refresh();
            $newPayrollData = $invoice->toArray();

            // 4. Catat ke Audit Trail
            AuditService::log(
                'CREATED',
                'Pembayaran Payroll',
                'Mencatatkan pembayaran payroll sebesar Rp ' . number_format($amount, 0, ',', '.') . ' dengan nomor referensi: ' . $noInvoice,
                $oldPayrollData,
                [
                    'payroll_updated' => $newPayrollData,
                    'payment_detail'  => $payrolDetailRecord ? $payrolDetailRecord->toArray() : null,
                    'journal_id'      => $journalId
                ]
            );

            return redirect()->back()->with('success', 'Pembayaran berhasil dilakukan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }
}
