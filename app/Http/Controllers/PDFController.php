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
use App\Models\ProdukClient;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
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
        $clients = FilePDF::where('client', $id)
            ->orderByDesc('is_pinned')
            ->orderBy('pin_order')
            ->orderByDesc('created_at')
            ->get();

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

    public function createDocument(Request $request)
    {
        $validated = $request->validate([
            'client' => ['required', 'integer', 'exists:produk_client,id'],
            'nama_file' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:15000000'],
        ], [
            'nama_file.required' => 'Nama dokumen wajib diisi.',
            'content.required' => 'Isi dokumen wajib diisi.',
        ]);

        $content = $this->sanitizeDocumentHtml($validated['content']);
        if (trim(strip_tags($content)) === '') {
            return back()->withErrors(['content' => 'Isi dokumen tidak boleh kosong.'])
                ->withInput();
        }

        $documentName = trim(preg_replace('/\.pdf$/i', '', $validated['nama_file']));
        if ($documentName === '') {
            return back()->withErrors(['nama_file' => 'Nama dokumen tidak valid.'])
                ->withInput();
        }

        $client = ProdukClient::findOrFail($validated['client']);
        $filename = (string) str()->uuid().'.pdf';
        $path = 'pdf/'.$client->id.'/'.$filename;
        $pdfContent = Pdf::loadView(
            'pdf.client_document',
            compact('client', 'documentName', 'content')
        )->setPaper('a4', 'portrait')->output();

        abort_unless(
            Storage::disk('public')->put($path, $pdfContent),
            500,
            'Dokumen gagal disimpan.'
        );

        FilePdf::create([
            'nama_file' => $documentName,
            'client' => $client->id,
            'location' => $path,
            'editor_content' => $content,
            'is_generated_document' => true,
        ]);

        return back()->with('success', 'Dokumen PDF berhasil dibuat.');
    }

    public function previewFile(FilePdf $file)
    {
        if ($file->is_generated_document && filled($file->editor_content)) {
            $client = ProdukClient::findOrFail($file->client);
            $documentName = trim(preg_replace('/\.pdf$/i', '', $file->nama_file));
            $content = $this->sanitizeDocumentHtml($file->editor_content);
            $pdfContent = Pdf::loadView(
                'pdf.client_document',
                compact('client', 'documentName', 'content')
            )->setPaper('a4', 'portrait')->output();

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        abort_unless(Storage::disk('public')->exists($file->location), 404, 'File PDF tidak ditemukan.');

        return Storage::disk('public')->response($file->location, $file->nama_file.'.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function update(Request $request, $id)
    {
        $file = FilePDF::findOrFail($id);

        $request->validate([
            'nama_file' => 'required|string|max:255',
            'pdf'       => 'nullable|mimes:pdf|max:10240',
            'content'   => [Rule::requiredIf($file->is_generated_document), 'nullable', 'string', 'max:15000000'],
        ]);

        $path = $file->location;
        $editorContent = $file->editor_content;
        $isGeneratedDocument = $file->is_generated_document;

        if ($file->is_generated_document) {
            $editorContent = $this->sanitizeDocumentHtml($request->input('content', ''));
            if (trim(strip_tags($editorContent)) === '') {
                return back()->withErrors(['content' => 'Isi dokumen tidak boleh kosong.'])
                    ->withInput();
            }

            $client = ProdukClient::findOrFail($file->client);
            $documentName = trim(preg_replace('/\.pdf$/i', '', $request->nama_file));
            $content = $editorContent;
            $pdfContent = Pdf::loadView(
                'pdf.client_document',
                compact('client', 'documentName', 'content')
            )->setPaper('a4', 'portrait')->output();

            abort_unless(
                Storage::disk('public')->put($path, $pdfContent),
                500,
                'Dokumen gagal diperbarui.'
            );
        }

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
            $editorContent = null;
            $isGeneratedDocument = false;
        }

        $file->update([
            'nama_file' => $request->nama_file,
            'location'  => $path,
            'editor_content' => $editorContent,
            'is_generated_document' => $isGeneratedDocument,
        ]);

        return redirect()->back()->with('success', 'File berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $file = FilePDF::findOrFail($id);
        abort_if($file->is_system_template, 403, 'Dokumen bawaan client tidak dapat dihapus.');

        // Hapus file fisik
        if (Storage::disk('public')->exists($file->location)) {
            Storage::disk('public')->delete($file->location);
        }

        // Hapus data database
        $file->delete();

        return redirect()->back()->with('success', 'File berhasil dihapus.');
    }

    private function sanitizeDocumentHtml(string $html): string
    {
        $allowedTags = '<p><div><span><font><br><hr><h1><h2><h3><strong><b><em><i><u><s><a><img><ul><ol><li><blockquote><table><thead><tbody><tfoot><tr><th><td><colgroup><col>';
        $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html);
        $clean = strip_tags($html, $allowedTags);

        $document = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><body>'.$clean.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        foreach ($document->getElementsByTagName('*') as $element) {
            $attributes = [];
            foreach ($element->attributes ?? [] as $attribute) {
                $attributes[] = $attribute->name;
            }
            foreach ($attributes as $attribute) {
                $value = trim($element->getAttribute($attribute));
                if ($attribute === 'align' && in_array($value, ['left', 'center', 'right', 'justify'], true)) {
                    continue;
                }
                if ($element->tagName === 'a' && $attribute === 'href'
                    && (str_starts_with($value, '#') || filter_var($value, FILTER_VALIDATE_URL))) continue;
                if ($element->tagName === 'a' && $attribute === 'target' && in_array($value, ['_blank', '_self'], true)) continue;
                if ($element->tagName === 'img' && $attribute === 'src'
                    && preg_match('#^data:image/(png|jpe?g|gif|webp);base64,#i', $value)) continue;
                if ($element->tagName === 'img' && in_array($attribute, ['alt', 'width', 'height'], true)) continue;
                if (in_array($element->tagName, ['table', 'td', 'th', 'col'], true)
                    && in_array($attribute, ['width', 'height', 'border', 'cellpadding', 'cellspacing', 'colspan', 'rowspan'], true)) continue;
                if ($attribute === 'style') {
                    $safeStyle = $this->sanitizeInlineStyle($value);
                    if ($safeStyle !== '') {
                        $element->setAttribute('style', $safeStyle);
                        continue;
                    }
                }
                if ($element->tagName === 'font' && $attribute === 'size' && preg_match('/^[1-7]$/', $value)) {
                    continue;
                }
                if ($element->tagName === 'font' && $attribute === 'color' && preg_match('/^(#[0-9a-f]{3,6}|[a-z]+|rgb\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\))$/i', $value)) {
                    continue;
                }
                $element->removeAttribute($attribute);
            }
        }

        $body = $document->getElementsByTagName('body')->item(0);
        $result = '';
        foreach ($body?->childNodes ?? [] as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }

    private function sanitizeInlineStyle(string $style): string
    {
        $safe = [];
        foreach (explode(';', $style) as $declaration) {
            if (! str_contains($declaration, ':')) continue;
            [$property, $value] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);
            $value = strtolower($value);
            $isSafe = match ($property) {
                'text-align' => in_array($value, ['left', 'center', 'right', 'justify'], true),
                'font-weight' => in_array($value, ['normal', 'bold', 'bolder', '100', '200', '300', '400', '500', '600', '700', '800', '900'], true),
                'font-style' => in_array($value, ['normal', 'italic', 'oblique'], true),
                'text-decoration', 'text-decoration-line' => preg_match('/^(none|underline|line-through|underline line-through)$/', $value) === 1,
                'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
                'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
                'font-size', 'line-height', 'width', 'height', 'max-width' => preg_match('/^(auto|0|\d+(\.\d+)?(px|pt|em|rem|%|cm|mm|in)?)$/', $value) === 1,
                'color', 'background-color', 'border-color' => preg_match('/^(transparent|#[0-9a-f]{3,8}|[a-z]+|rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+(?:\s*,\s*[\d.]+)?\s*\))$/i', $value) === 1,
                'border-collapse' => in_array($value, ['collapse', 'separate'], true),
                'border-style', 'border-top-style', 'border-right-style', 'border-bottom-style', 'border-left-style' => in_array($value, ['none', 'hidden', 'solid', 'dashed', 'dotted', 'double'], true),
                'border-width', 'border-top-width', 'border-right-width', 'border-bottom-width', 'border-left-width' => preg_match('/^(0|\d+(\.\d+)?(px|pt))$/', $value) === 1,
                'border', 'border-top', 'border-right', 'border-bottom', 'border-left' => $value === 'none'
                    || preg_match('/^\d+(\.\d+)?(px|pt)\s+(solid|dashed|dotted|double)\s+(transparent|#[0-9a-f]{3,8}|[a-z]+|rgba?\([^)]*\))$/i', $value) === 1,
                'opacity' => preg_match('/^(0(\.\d+)?|1(\.0+)?)$/', $value) === 1,
                default => false,
            };
            if ($isSafe) $safe[] = $property.': '.$value;
        }

        return implode('; ', $safe);
    }
}
