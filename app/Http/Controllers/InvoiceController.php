<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Services\GeneralJournalService;
use App\Models\ProdukClient;
use App\Models\ChartOfAccount;
use App\Models\User;
use App\Models\Payrol;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    protected $journalService;

    public function __construct(GeneralJournalService $journalService)
    {
        $this->journalService = $journalService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = "Pembayaran";
        $customers = DB::table('client_division')
            ->join('divisions', 'divisions.id', '=', 'client_division.division_id')
            ->join('produk_client', 'produk_client.id', '=', 'client_division.client_id')
            ->where('client_division.division_id', 6)
            ->where('produk_client.status', 1)
            ->get();
        $accountPendapatan = ChartOfAccount::where('category', 'pendapatan')->where('is_active', true)->get();
        $accountPiutang = ChartOfAccount::where('category', 'beban')->where('is_active', true)->get();
        return view('finance.invoice', compact('title', 'customers', 'accountPendapatan', 'accountPiutang'));
    }

    public function getInvoiceData()
    {
        // Mengambil data dengan relasi
        $invoices = Invoice::with(['payments'])
        ->orderBy('due_date', 'asc')
        ->get()
        ->map(function ($invoice) {
            return [
                'id'            => $invoice->id,
                'no_invoice'    => $invoice->no_invoice,
                'customer_name' => $invoice->customer->nama,
                'customer_id' => $invoice->customer_id,
                'due_date'      => \Carbon\Carbon::parse($invoice->due_date)->format('d M Y'),
                'total_amount'  => number_format($invoice->total_amount, 0, ',', '.'),
                'remaining'     => number_format($invoice->remaining, 0, ',', '.'),
                'status'        => $invoice->status,
                'payment_route' => route('invoice.create', $invoice->id) // Pastikan nama route ini benar!
            ];
        });

        return response()->json(['data' => $invoices]);
    }

    public function generatePdf($id)
    {
        $invoice = Invoice::findOrFail($id);
        
        $pdf = Pdf::loadView('finance.invoice_pdf', [
            'invoice_number' => $invoice->no_invoice,
            'invoice_date'   => \Carbon\Carbon::parse($invoice->created_at)->format('d M Y'),
            'due_date'       => \Carbon\Carbon::parse($invoice->due_date)->format('d M Y'),
            'client_company' => $invoice->customer->nama, // Sesuaikan dengan relasi Anda
            'amount'         => $invoice->total_amount,
            'bank_name'      => 'Bank BCA', // Bisa diambil dari database atau config
            'account_name'   => 'Lighter Media Group',
            'account_number' => '1234567890'
        ]);

        return $pdf->stream('Invoice_' . $invoice->no_invoice . '.pdf');
    }

    public function payment(Request $request, $id){
        
        $request->validate([
            'amount'       => 'required|numeric',
            'payment_date' => 'required|date',
            'file_path'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // Validasi file
        ]);

        $path = null;
        if ($request->hasFile('file_path')) {
            // Simpan file ke folder 'payments' di dalam storage/app/public
            $path = $request->file('file_path')->store('payments', 'public');
        }

        $invoice = Invoice::find($id);

        $customer = ProdukClient::findOrFail($invoice->customer_id);
        $customerCode = $customer->nama; 

        $tanggalFormat = date('Ymd');

        $jumlahInvoiceCustomer = \App\Models\Invoice::where('customer_id', $invoice->customer_id)->count() + 1;

        $urutanInvoice = sprintf('%04d', $jumlahInvoiceCustomer);

        $noInvoice = "INV/{$tanggalFormat}/{$customerCode}/{$urutanInvoice}";

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

        DB::transaction(function () use ($request, $id, $path, $noInvoice) {
            $invoice = Invoice::findOrFail($id);


            InvoicePayment::create([
                'invoice_id'   => $id,
                'amount'       => $request->amount,
                'payment_date' => $request->payment_date,
                'method'       => $request->method,
                'file_path'    => $path,
                'no_invoice'   => $noInvoice
            ]);

            $invoice->paid_amount += $request->amount;
            $invoice->status = ($invoice->paid_amount >= $invoice->total_amount) ? 'paid' : 'partial';
            $invoice->save();
        });

        return back()->with('success', 'Pembayaran dan bukti berhasil disimpan.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required',
            'total_amount' => 'required|numeric',
            'due_date' => 'required|date'
        ]);

        $customer = ProdukClient::findOrFail($request->customer_id);
        $customerCode = $customer->nama; 

        $tanggalFormat = date('Ymd');

        $jumlahInvoiceCustomer = \App\Models\Invoice::where('customer_id', $request->customer_id)->count() + 1;

        $urutanInvoice = sprintf('%04d', $jumlahInvoiceCustomer);

        $noInvoice = "INV/{$tanggalFormat}/{$customerCode}/{$urutanInvoice}";
        $data['no_invoice'] = $noInvoice;
        $data['paid_amount'] = 0;
        $data['status'] = 'unpaid';
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
                    'debit' => 0,
                    'kredit' => (float) $request->total_amount
                ]
            ]
        ];

        $this->journalService->storeJournal($dataBaru);
        Invoice::create($data);

        return back()->with('success', 'Invoice dibuat');
    }

    public function list_client()
    {
        $title = 'Profit Per Client';

        // Hanya mengambil client yang minimal punya 1 invoice
        $clients = ProdukClient::has('invoices')
            ->with('invoices.payments')
            ->get()
            ->map(function ($client) {
                $totalInvoice = $client->invoices->sum('total_amount');
                
                $totalBayar = $client->invoices->sum(function ($invoice) {
                    return $invoice->payments->sum('amount');
                });
                
                $balance = $totalInvoice - $totalBayar;

                $client->total_invoice = $totalInvoice;
                $client->total_bayar = $totalBayar;
                $client->balance = $balance;

                return $client;
            });

        return view('finance.profit_list', compact('title', 'clients'));
    }

    public function detail($id)
    {
        $title = "Detail Profit Client";

        // Ambil data client spesifik berdasarkan ID beserta invoice dan pembayaran di dalamnya
        $client = ProdukClient::with(['invoices.payments'])->findOrFail($id);

        // Hitung total keseluruhan invoice dan pembayaran untuk client ini
        $totalInvoice = $client->invoices->sum('total_amount');
        $totalBayar = $client->invoices->sum(function ($invoice) {
            return $invoice->payments->sum('amount');
        });
        $balance = $totalInvoice - $totalBayar;

        return view('finance.detail_profit', compact('title', 'client', 'totalInvoice', 'totalBayar', 'balance'));
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update($request->all());
        return back()->with('success', 'Invoice berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {

        // Invoice::findOrFail($id)->delete();
        // return back()->with('success', 'Invoice berhasil dihapus');

        $invoice = Invoice::find($id);

        $invoicePayment = InvoicePayment::where('invoice_id', $id)->get();
        foreach($invoicePayment as $pd){
            $jurnalUmum = DB::table('general_journals')->where('no_invoice', $pd->no_invoice)->first();
            if ($jurnalUmum) {
                // Hapus detail jurnal terlebih dahulu (berdasarkan foreign key)
                DB::table('general_journal_details')->where('general_journal_id', $jurnalUmum->id)->delete();
                
                // Hapus header jurnal umum menggunakan query builder DB::table
                DB::table('general_journals')->where('id', $jurnalUmum->id)->delete();
            }
        }

        $jurnalUmumPayrol = DB::table('general_journals')->where('no_invoice', $invoice->no_invoice)->first();

        if($jurnalUmumPayrol){
            DB::table('general_journal_details')->where('general_journal_id', $jurnalUmumPayrol->id)->delete();
            DB::table('general_journals')->where('id', $jurnalUmumPayrol->id)->delete();
        }

        InvoicePayment::where('invoice_id', $id)->delete();

        $invoice->delete();

        return redirect()->back()->with('success', 'Data payroll beserta jurnal terkait berhasil dihapus.');

    }
}
