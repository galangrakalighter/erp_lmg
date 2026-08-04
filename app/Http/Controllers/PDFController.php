<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PDFExport;
use App\Services\LedgerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\FilePdf;
use App\Models\ChartOfAccount;
use Carbon\Carbon;
class PDFController extends Controller
{
    protected $pdf;
    protected $ledgerService;

    public function __construct(PDFExport $pdf, LedgerService $ledgerService)
    {
        $this->pdf = $pdf;
        $this->ledgerService = $ledgerService;
    }

    public function exportPDF(Request $request, $id, $halaman){
        $produk = DB::table('produk_client')->where('id', (int)$id)->first();
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        switch ($halaman) {
            case "Manajemen Konten":
                $data = $this->pdf->getDataManajemen($produk->id, $startDate, $endDate);
                $kategory = DB::table("kategori_jasa")->where('id', $produk->kategori_jasa_id)->first();
                $pdf = Pdf::loadView('pdf.manajemen', compact('data', 'kategory'))
                ->setPaper('a4', 'landscape');
                break;
            case "Branding":
                $data = $this->pdf->getDataBranding($produk->id);
                $pdf = Pdf::loadView('pdf.branding', compact('data'))
                ->setPaper('a4', 'landscape');
                break;
            case "Market Research":
                $data = $this->pdf->getDataMarket($produk->id);
                $pdf = Pdf::loadView('pdf.market', compact('data'))
                ->setPaper('a4', 'landscape');
                break;
            default:
            break;
        }
        return $pdf->download($halaman.' '. $produk->nama .'.pdf');
    }

    public function exportGeneralJournal(Request $request){
        $query = DB::table('general_journals');

        // Cek apakah user mengisi filter tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $query->whereBetween('tgl_transaksi', [$startDate, $endDate]);
        } else {
            // Default ke bulan ini jika tidak ada filter
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
            $query->whereBetween('tgl_transaksi', [$startDate, $endDate]);
        }

        // Filter status jika ada
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_jurnal', 'like', "%{$search}%")
                ->orWhere('no_invoice', 'like', "%{$search}%")
                ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $journals = $query->orderBy('tgl_transaksi', 'asc')->get();

        foreach ($journals as $journal) {
            $journal->details = DB::table('general_journal_details')
                ->leftJoin('chart_of_account', 'general_journal_details.chart_of_account_id', '=', 'chart_of_account.id')
                ->where('general_journal_details.general_journal_id', $journal->id)
                ->select(
                    'general_journal_details.*', 
                    'chart_of_account.kode as account_code', 
                    'chart_of_account.nama_akun as account_name'
                )
                ->get();
        }

        // --- DINAMIS NAMA FILE PDF ---
        $fileName = 'Laporan-Jurnal-Umum_' . $startDate . '_s_d_' . $endDate;
        
        if ($request->filled('status')) {
            $fileName .= '_' . strtolower($request->status);
        }
        
        $fileName .= '.pdf';
        // ----------------------------

        $pdf = PDF::loadView('pdf.general_journal', [
            'journals' => $journals,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        $pdf->setPaper('a4', 'landscape');

        // Menggunakan variabel $fileName yang sudah dinamis
        return $pdf->stream($fileName);
    }

    public function exportBukuBesar(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));

        $startDate = Carbon::createFromFormat('Y-m', $periode)->startOfMonth()->toDateString();
        $endDate   = Carbon::createFromFormat('Y-m', $periode)->endOfMonth()->toDateString();

        $ledger = [];

        $accounts = ChartOfAccount::orderBy('kode')->get();

        foreach ($accounts as $account) {

            $item = $this->ledgerService->getAccountLedger(
                $account->id,
                $periode
            );

            // Hanya tambahkan jika memiliki transaksi
            if (!empty($item['mutasi'])) {
                $ledger[] = $item;
            }

        }

        $pdf = PDF::loadView('pdf.bb', [
            'ledger'        => $ledger,
            'periodeInput'  => $periode,
            'startDate'     => $startDate,
            'endDate'       => $endDate,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Buku-Besar-'.$periode.'.pdf');
    }

    public function exportAdjustmen(Request $request, $id){
        $periodeInput = $request->input('periode', date('Y-m'));
        $startDate = Carbon::createFromFormat('Y-m', $periodeInput)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromFormat('Y-m', $periodeInput)->endOfMonth()->toDateString();

        // Ambil data jurnal penyesuaian berdasarkan rentang tanggal periode
        $journals = \App\Models\AdjustmentJournal::with(['details.account'])
            ->whereBetween('tgl_transaksi', [$startDate, $endDate])
            ->where('perusahaan', $id)
            ->orderBy('tgl_transaksi', 'asc')
            ->get();

        $fileName = 'Jurnal-Penyesuaian_' . $periodeInput . '.pdf';

        $pdf = PDF::loadView('pdf.adjustment', [
            'journals' => $journals,
            'periodeInput' => $periodeInput,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream($fileName);
    }

    public function index($id){
        $title = "File";
        $clients = FilePDF::where('client', $id)->get();

        return view('pdf.index', compact('clients', 'title', 'id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_file' => 'required|string|max:255',
            'client'    => 'required',
            'pdf'       => 'required|mimes:pdf', // Maksimal 10MB
        ]);

        // Folder berdasarkan ID client
        $folder = 'pdf/' . $request->client;

        // Nama file unik
        $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();

        // Simpan file
        $path = $request->file('pdf')->storeAs(
            $folder,
            $filename,
            'public'
        );

        // Simpan ke database
        FilePDF::create([
            'nama_file' => $request->nama_file,
            'client'    => $request->client,
            'location'  => $path,
        ]);

        return redirect()->back()->with('success', 'File berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $file = FilePDF::findOrFail($id);

        $request->validate([
            'nama_file' => 'required|string|max:255',
            'pdf'       => 'nullable|mimes:pdf|max:10240',
        ]);

        $path = $file->location;

        // Jika upload file baru
        if ($request->hasFile('pdf')) {

            // Hapus file lama
            if (Storage::disk('public')->exists($file->location)) {
                Storage::disk('public')->delete($file->location);
            }

            $folder = 'pdf/' . $file->client;

            $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();

            $path = $request->file('pdf')->storeAs(
                $folder,
                $filename,
                'public'
            );
        }

        $file->update([
            'nama_file' => $request->nama_file,
            'location'  => $path,
        ]);

        return redirect()->back()->with('success', 'File berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $file = FilePDF::findOrFail($id);

        // Hapus file fisik
        if (Storage::disk('public')->exists($file->location)) {
            Storage::disk('public')->delete($file->location);
        }

        // Hapus data database
        $file->delete();

        return redirect()->back()->with('success', 'File berhasil dihapus.');
    }
}
