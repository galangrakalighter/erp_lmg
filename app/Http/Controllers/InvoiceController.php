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
use App\Services\AuditService;

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

    public function payment(Request $request, $id)
    {
        $request->validate([
            'amount'       => 'required|numeric',
            'payment_date' => 'required|date',
            'file_path'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', 
        ]);

        // 1. Proteksi Periode: Cek apakah periode dari tanggal pembayaran terkunci
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

        $path = null;
        if ($request->hasFile('file_path')) {
            $path = $request->file('file_path')->store('payments', 'public');
        }

        // Ambil data invoice lama sebelum diubah untuk log audit
        $invoice = Invoice::findOrFail($id);
        $oldInvoiceData = $invoice->toArray();

        $customer = ProdukClient::findOrFail($invoice->customer_id);
        $customerCode = $customer->nama; 

        $tanggalFormat = date('Ymd');
        $jumlahInvoiceCustomer = \App\Models\Invoice::where('customer_id', $invoice->customer_id)->count() + 1;
        $urutanInvoice = sprintf('%04d', $jumlahInvoiceCustomer);
        $noInvoice = "INV/{$tanggalFormat}/{$customerCode}/{$urutanInvoice}";

        $dataBaru = [
            'tgl_transaksi' => $request->payment_date, // Menggunakan tanggal pembayaran agar sinkron dengan periode
            'no_invoice' => $noInvoice,
            'keterangan' => $request->keterangan ?? 'Pembayaran Invoice #' . $invoice->id,
            'cash_flow_activity' => 'operasional',
            'status' => 'draft',
            'items' => [
                [
                    'chart_of_account_id' => $request->coa,
                    'keterangan_item' => $request->keterangan ?? 'Pembayaran Invoice',
                    'debit' => (float) $request->amount,
                    'kredit' => 0
                ]
            ]
        ];

        // Simpan jurnal via service
        $journalId = $this->journalService->storeJournal($dataBaru);

        $paymentRecord = null;
        DB::transaction(function () use ($request, $id, $path, $noInvoice, &$paymentRecord, &$invoice) {
            $paymentRecord = InvoicePayment::create([
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

        // Ambil data invoice terbaru setelah pembayaran
        $invoice->refresh();
        $newInvoiceData = $invoice->toArray();

        // [BARU] Catat ke Audit Trail
        AuditService::log(
            'CREATED',
            'Pembayaran Invoice',
            'Mencatatkan pembayaran sebesar Rp ' . number_format($request->amount, 0, ',', '.') . ' untuk Invoice ID: ' . $id,
            $oldInvoiceData,
            [
                'invoice_updated' => $newInvoiceData,
                'payment_detail'  => $paymentRecord ? $paymentRecord->toArray() : null,
                'journal_id'      => $journalId
            ]
        );

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
        // 1. Proteksi Periode: Cek apakah periode bulan ini terkunci
        $currentDate = now()->toDateString();
        $periode = date('Y-m', strtotime($currentDate));
        $isLocked = DB::table('period_locks')
            ->where('periode', $periode)
            ->where('is_locked', true)
            ->exists();

        if ($isLocked) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat invoice! Periode ' . $periode . ' sudah ditutup dan terkunci.');
        }

        $data = $request->validate([
            'customer_id'  => 'required',
            'total_amount' => 'required|numeric',
            'due_date'     => 'required|date'
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
            'tgl_transaksi' => $currentDate,
            'no_invoice' => $noInvoice,
            'keterangan' => $request->keterangan ?? 'Pembuatan Invoice #' . $noInvoice,
            'cash_flow_activity' => 'operasional',
            'status' => 'draft',
            'items' => [
                [
                    'chart_of_account_id' => $request->coa,
                    'keterangan_item' => $request->keterangan ?? 'Piutang Usaha / Invoice ' . $noInvoice,
                    'debit' => 0,
                    'kredit' => (float) $request->total_amount
                ]
            ]
        ];

        try {
            // Eksekusi penyimpanan jurnal dan invoice dalam satu database transaction atau berurutan
            DB::beginTransaction();

            $journalId = $this->journalService->storeJournal($dataBaru);
            $invoice = Invoice::create($data);

            DB::commit();

            // [BARU] Catat ke Audit Trail
            AuditService::log(
                'CREATED',
                'Manajemen Invoice',
                'Membuat invoice baru dengan nomor: ' . $noInvoice . ' untuk customer ' . $customerCode,
                null,
                [
                    'invoice'    => $invoice->toArray(),
                    'journal_id' => $journalId
                ]
            );

            return back()->with('success', 'Invoice dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat invoice: ' . $e->getMessage());
        }
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

        // 1. Proteksi Periode: Cek apakah periode tanggal saat ini (atau tanggal invoice) terkunci
        $invoiceDate = $invoice->created_at ? date('Y-m', strtotime($invoice->created_at)) : date('Y-m');
        $isLocked = DB::table('period_locks')
            ->where('periode', $invoiceDate)
            ->where('is_locked', true)
            ->exists();

        if ($isLocked) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui invoice! Periode ' . $invoiceDate . ' sudah ditutup dan terkunci.');
        }

        // Ambil data lama sebelum di-update untuk keperluan audit log
        $oldData = $invoice->toArray();

        // Lakukan update data
        $invoice->update($request->all());

        // Ambil data terbaru setelah di-update
        $invoice->refresh();
        $newData = $invoice->toArray();

        // [BARU] Catat ke Audit Trail
        AuditService::log(
            'UPDATED',
            'Manajemen Invoice',
            'Memperbarui data invoice dengan nomor: ' . ($invoice->no_invoice ?? $id),
            $oldData,
            $newData
        );

        return back()->with('success', 'Invoice berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return redirect()->back()->with('error', 'Data invoice tidak ditemukan!');
        }

        // 1. Proteksi Periode: Cek apakah periode tanggal invoice terkunci
        $invoiceDate = $invoice->created_at ? date('Y-m', strtotime($invoice->created_at)) : date('Y-m');
        $isLocked = DB::table('period_locks')
            ->where('periode', $invoiceDate)
            ->where('is_locked', true)
            ->exists();

        if ($isLocked) {
            return redirect()->back()->with('error', 'Gagal menghapus! Periode ' . $invoiceDate . ' sudah ditutup dan terkunci.');
        }

        // 2. Kumpulkan data lama sebelum dihapus untuk keperluan log audit
        $invoicePayments = InvoicePayment::where('invoice_id', $id)->get();
        
        $associatedJournals = [];
        foreach ($invoicePayments as $pd) {
            $jurnalUmum = DB::table('general_journals')->where('no_invoice', $pd->no_invoice)->first();
            if ($jurnalUmum) {
                $details = DB::table('general_journal_details')->where('general_journal_id', $jurnalUmum->id)->get();
                $associatedJournals[] = [
                    'header'  => (array) $jurnalUmum,
                    'details' => $details->map(function($d) { return (array)$d; })->toArray()
                ];
            }
        }

        $jurnalUmumPayrol = DB::table('general_journals')->where('no_invoice', $invoice->no_invoice)->first();
        if ($jurnalUmumPayrol) {
            $detailsPayroll = DB::table('general_journal_details')->where('general_journal_id', $jurnalUmumPayrol->id)->get();
            $associatedJournals[] = [
                'header'  => (array) $jurnalUmumPayrol,
                'details' => $detailsPayroll->map(function($d) { return (array)$d; })->toArray()
            ];
        }

        $oldData = [
            'invoice'          => $invoice->toArray(),
            'invoice_payments' => $invoicePayments->map(function($p) { return $p->toArray(); })->toArray(),
            'journals'         => $associatedJournals
        ];

        $invoiceNumber = $invoice->no_invoice;

        try {
            DB::beginTransaction();

            // 3. Eksekusi penghapusan relasi pembayaran dan jurnal terkait
            foreach($invoicePayments as $pd){
                $jurnalUmum = DB::table('general_journals')->where('no_invoice', $pd->no_invoice)->first();
                if ($jurnalUmum) {
                    DB::table('general_journal_details')->where('general_journal_id', $jurnalUmum->id)->delete();
                    DB::table('general_journals')->where('id', $jurnalUmum->id)->delete();
                }
            }

            if($jurnalUmumPayrol){
                DB::table('general_journal_details')->where('general_journal_id', $jurnalUmumPayrol->id)->delete();
                DB::table('general_journals')->where('id', $jurnalUmumPayrol->id)->delete();
            }

            InvoicePayment::where('invoice_id', $id)->delete();
            $invoice->delete();

            DB::commit();

            // 4. Catat ke Audit Trail
            AuditService::log(
                'DELETED',
                'Manajemen Invoice',
                'Menghapus invoice beserta seluruh pembayaran dan jurnal terkait dengan nomor: ' . $invoiceNumber,
                $oldData,
                null
            );

            return redirect()->back()->with('success', 'Data invoice beserta jurnal terkait berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
