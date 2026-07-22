<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payrol;
use App\Models\PayrolDetail;
use App\Models\ChartOfAccount;
use App\Services\GeneralJournalService;
use Illuminate\Support\Facades\DB;
class PayrollController extends Controller
{
    protected $journalService;

    public function __construct(GeneralJournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index(){
        $title = 'Payroll';

        $user = User::where('isAdmin', false)->get();

        $account = ChartOfAccount::whereIn('category', ['kewajiban', 'beban'])->where('is_active', true)->get();
        $accountKewajiban = ChartOfAccount::where('category', 'kewajiban')->where('is_active', true)->get();

        return view('finance.payroll', compact('title', 'user', 'account', 'accountKewajiban'));
    }

    public function store(Request $request){
        $tanggalFormat = date('Ymd');
        $jumlahPayrol = Payrol::where('user_id', $request->user_id)->count() + 1;

        $urutanInvoice = sprintf('%04d', $jumlahPayrol);

        $noInvoice = "PYR/{$tanggalFormat}/{$request->user_id}/{$urutanInvoice}";
        
        $data = $request->validate([
            'user_id' => 'required',
            'total_amount' => 'required',
            'payroll_date' => 'required'
        ]);

        $data['paid_amount'] = (float)$request->paid_amount;
        $data['status'] = $request->status;
        $data['keterangan'] = $request->keterangan;
        $data['invoice'] = $noInvoice;

        $dataBaru = [
            'tgl_transaksi' => now()->toDateString(),
            'no_invoice' => $noInvoice,
            'keterangan' => $request->keterangan,
            'cash_flow_activity' => 'operasional',
            'status' => 'draft',
            'items' => [
                [
                    'chart_of_account_id' => $request->coa,
                    'keterangan_item' => $request->keterangan,
                    'debit' => $request->paid_amount ? (float)$request->paid_amount : 0,
                    'kredit' => (float)$request->total_amount
                ]
            ]
        ];

        $this->journalService->storeJournal($dataBaru);

        PayRol::create($data);

        return back()->with('success', 'Payrol Dibuat');

    }

    public function update(Request $request, $id)
    {
        $payroll = Payrol::findOrFail($id);

        // Buat aturan validasi dinamis berdasarkan status payroll saat ini di database
        $rules = [
            'total_amount' => 'required|numeric|min:0',
        ];

        // Jika statusnya 'unpaid', wajibkan juga field-field lainnya karena form lengkap dimunculkan
        if ($payroll->status === 'unpaid') {
            $rules['coa']          = 'required|exists:charts_of_accounts,id'; // Sesuaikan nama tabel COA
            $rules['user_id']      = 'required|exists:users,id';
            $rules['payroll_date'] = 'required|date';
            $rules['status']       = 'required|in:unpaid,partial,paid';
        }

        $request->validate($rules);

        // Proses penyimpanan data
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

        return redirect()->back()->with('success', 'Data payroll berhasil diperbarui.');
    }

    public function destroy($id){
        $payrol = Payrol::find($id);
        
        $payrolDetail = PayrolDetail::where('payroll_id', $id)->get();

        foreach($payrolDetail as $pd){
            $jurnalUmum = DB::table('general_journals')->where('no_invoice', $pd->invoice)->first();
            if ($jurnalUmum) {
                // Hapus detail jurnal terlebih dahulu (berdasarkan foreign key)
                DB::table('general_journal_details')->where('general_journal_id', $jurnalUmum->id)->delete();
                
                // Hapus header jurnal umum menggunakan query builder DB::table
                DB::table('general_journals')->where('id', $jurnalUmum->id)->delete();
            }
        }

        $jurnalUmumPayrol = DB::table('general_journals')->where('no_invoice', $payrol->invoice)->first();

        if($jurnalUmumPayrol){
            DB::table('general_journal_details')->where('general_journal_id', $jurnalUmumPayrol->id)->delete();
            DB::table('general_journals')->where('id', $jurnalUmumPayrol->id)->delete();
        }

        PayrolDetail::where('payroll_id', $id)->delete();

        $payrol->delete();

        return redirect()->back()->with('success', 'Data payroll beserta jurnal terkait berhasil dihapus.');
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

    public function payment(Request $request){
        $tanggalFormat = date('Ymd');
        $jumlahPayrol = Payrol::where('user_id', $request->user_id)->count() + 1;

        $urutanInvoice = sprintf('%04d', $jumlahPayrol);

        $noInvoice = "PYR/{$tanggalFormat}/{$request->user_id}/{$urutanInvoice}";
        
        $dataBaru = [
            'tgl_transaksi' => now()->toDateString(),
            'no_invoice' => $noInvoice,
            'keterangan' => $request->keterangan,
            'cash_flow_activity' => 'operasional',
            'status' => 'draft',
            'items' => [
                [
                    'chart_of_account_id' => $request->coa,
                    'keterangan_item' => $request->keterangan,
                    'debit' => (float) $request->amount,
                    'kredit' => 0
                ]
            ]
        ];

        $this->journalService->storeJournal($dataBaru);

        DB::transaction(function () use ($request, $noInvoice) {
            $invoice = Payrol::findOrFail($request->payroll_id);

            PayrolDetail::create([
                'payroll_id'   => $request->payroll_id,
                'payment_date' => $request->payment_date,
                'amount'       => $request->amount,
                'invoice'      => $noInvoice
            ]);

            $invoice->paid_amount += $request->amount;
            $invoice->status = ($invoice->paid_amount >= $invoice->total_amount) ? 'paid' : 'partial';
            $invoice->save();
        });

        return redirect()->back()->with('success', 'Pembayaran berhasil dilakukan.');
    }
}
