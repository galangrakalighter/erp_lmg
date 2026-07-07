<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\ProdukClient;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = "Pembayaran";
        $customers = ProdukClient::all();
        return view('finance.invoice', compact('title', 'customers'));
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

        DB::transaction(function () use ($request, $id, $path) {
            $invoice = Invoice::findOrFail($id);

            InvoicePayment::create([
                'invoice_id'   => $id,
                'amount'       => $request->amount,
                'payment_date' => $request->payment_date,
                'method'       => $request->method,
                'file_path'    => $path, // Simpan path ke database
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
            'no_invoice' => 'required|unique:invoices',
            'total_amount' => 'required|numeric',
            'due_date' => 'required|date'
        ]);
        $data['paid_amount'] = 0;
        $data['status'] = 'unpaid';
        Invoice::create($data);
        return back()->with('success', 'Invoice dibuat');
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
        Invoice::findOrFail($id)->delete();
        return back()->with('success', 'Invoice berhasil dihapus');
    }
}
