<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PDFExport;
use App\Services\LedgerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\FilePdf;
use App\Models\FilePdfVersion;
use App\Models\ChartOfAccount;
use App\Models\ProdukClient;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Support\DigitalMarketingPermissions;
use App\Services\PlatformMirroringService;
class PDFController extends Controller
{
    protected $pdf;
    protected $ledgerService;
    protected $mirroring;

    public function __construct(
        PDFExport $pdf,
        LedgerService $ledgerService,
        PlatformMirroringService $mirroring
    )
    {
        $this->pdf = $pdf;
        $this->ledgerService = $ledgerService;
        $this->mirroring = $mirroring;
    }

    public function exportPDF(Request $request, $id, $halaman){
        $produk = ProdukClient::findOrFail((int) $id);
        abort_unless(in_array($halaman, ['Manajemen Konten', 'Branding', 'Market Research'], true), 404);

        $request->validate([
            'platform' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'content_source' => ['nullable', Rule::in(['organic', 'paid'])],
        ]);

        $availablePlatforms = collect(data_get($produk->access_token, 'platform', []))
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn ($value) => trim($value));
        $requestedPlatform = trim((string) $request->query('platform', ''));
        $platform = $requestedPlatform !== ''
            ? $availablePlatforms->first(
                fn ($value) => strcasecmp($value, $requestedPlatform) === 0
            )
            : $availablePlatforms->first();
        abort_if(
            $requestedPlatform !== '' && blank($platform),
            422,
            'Platform export tidak valid.'
        );

        $startDate = $request->get('start_date') ?: now()->startOfMonth()->toDateString();
        $endDate = $request->get('end_date') ?: now()->endOfMonth()->toDateString();
        $platforms = $platform
            ? $this->mirroring->platforms(
                $produk->id,
                $platform,
                match ($halaman) {
                    'Market Research' => 'market-research',
                    'Branding' => 'branding',
                    default => $request->query('content_source') === 'paid'
                        ? 'content-plan-paid'
                        : 'content-plan-organic',
                }
            )
            : [];

        switch ($halaman) {
            case "Manajemen Konten":
                $contentSource = $request->query('content_source') === 'paid' ? 'paid' : 'organic';
                $data = $this->pdf->getDataManajemen(
                    $produk->id,
                    $startDate,
                    $endDate,
                    $platforms,
                    $contentSource,
                    $this->mirroring
                );
                $kategory = DB::table("kategori_jasa")->where('id', $produk->kategori_jasa_id)->first();
                $pdf = Pdf::loadView('pdf.manajemen', compact(
                    'data', 'kategory', 'produk', 'platform', 'startDate', 'endDate', 'contentSource'
                ))
                ->setPaper('a4', 'landscape');
                break;
            case "Branding":
                $data = $this->pdf->getDataBranding($produk->id, $platforms, $this->mirroring);
                $pdf = Pdf::loadView('pdf.branding', compact('data', 'produk', 'platform'))
                ->setPaper('a4', 'landscape');
                break;
            case "Market Research":
                $data = $this->pdf->getDataMarket($produk->id, $platforms, $this->mirroring);
                $pdf = Pdf::loadView('pdf.market', compact('data', 'produk', 'platform'))
                ->setPaper('a4', 'landscape');
                break;
        }

        $fileName = collect([$halaman, $produk->nama, $platform])
            ->filter()
            ->implode(' ');

        return $pdf->download($fileName.'.pdf');
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

    public function index(Request $request, $id){
        $divisionId = $this->resolveClientDivision($request, (int) $id);
        $divisionName = DB::table('divisions')->where('id', $divisionId)->value('name');
        $title = "File · ".($divisionName ?: 'Divisi');
        $clients = FilePDF::where('client', $id)
            ->where('division_id', $divisionId)
            ->with(['uploader', 'versions.changedBy'])
            ->when($request->filled('uploaded_by'), fn ($query) => $query->where('uploaded_by', $request->integer('uploaded_by')))
            ->orderByDesc('is_pinned')
            ->orderBy('pin_order')
            ->orderByDesc('created_at')
            ->get();

        $uploaders = User::query()
            ->unlocked()
            ->whereIn('id', FilePdf::where('client', $id)->where('division_id', $divisionId)->whereNotNull('uploaded_by')->select('uploaded_by'))
            ->orderBy('name')->get(['id', 'name', 'email']);
        $selectedUploaderId = $request->integer('uploaded_by') ?: null;
        $readOnly = $request->boolean('readonly');

        return view('pdf.index', compact('clients', 'title', 'id', 'divisionId', 'divisionName', 'uploaders', 'selectedUploaderId', 'readOnly'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_file' => 'required|string|max:255',
            'client'    => 'required',
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'pdf'       => 'required|mimes:pdf', // Maksimal 10MB
        ]);
        $this->authorizeDocumentMutation((int) $request->client, (int) $request->division_id);

        // Folder berdasarkan ID client
        $this->ensureClientDivision((int) $request->client, (int) $request->division_id);
        $folder = 'pdf/'.$request->client.'/'.$request->division_id;

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
            'division_id' => $request->division_id,
            'uploaded_by' => Auth::id(),
            'location'  => $path,
        ]);

        return redirect()->back()->with('success', 'File berhasil ditambahkan.');
    }

    public function createDocument(Request $request)
    {
        $validated = $request->validate([
            'client' => ['required', 'integer', 'exists:produk_client,id'],
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'nama_file' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:15000000'],
        ], [
            'nama_file.required' => 'Nama dokumen wajib diisi.',
            'content.required' => 'Isi dokumen wajib diisi.',
        ]);
        $this->authorizeDocumentMutation((int) $validated['client'], (int) $validated['division_id']);

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
        $this->ensureClientDivision($client->id, (int) $validated['division_id']);
        $filename = (string) str()->uuid().'.pdf';
        $path = 'pdf/'.$client->id.'/'.$validated['division_id'].'/'.$filename;
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
            'division_id' => $validated['division_id'],
            'uploaded_by' => Auth::id(),
            'location' => $path,
            'editor_content' => $content,
            'is_generated_document' => true,
        ]);

        return back()->with('success', 'Dokumen PDF berhasil dibuat.');
    }

    public function previewFile(FilePdf $file)
    {
        $this->ensureFileAccessible($file);

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

    /** Tampilan PDF halaman penuh, terpisah dari daftar dokumen. */
    public function viewFile(FilePdf $file)
    {
        $this->ensureFileAccessible($file);

        return view('pdf.viewer', compact('file'));
    }

    public function previewVersion(FilePdf $file, FilePdfVersion $version)
    {
        $this->ensureFileAccessible($file);

        abort_unless($file->is_pinned && (int) $version->file_pdf_id === (int) $file->id, 404);
        abort_unless(Storage::disk('public')->exists($version->location), 404, 'File versi tidak ditemukan.');

        return Storage::disk('public')->response(
            $version->location,
            pathinfo($version->nama_file, PATHINFO_FILENAME).' - v'.$version->version_number.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function update(Request $request, $id)
    {
        $file = FilePDF::findOrFail($id);
        $this->ensureFileAccessible($file);
        $this->authorizeDocumentMutation((int) $file->client, (int) $file->division_id);

        $request->validate([
            'nama_file' => 'required|string|max:255',
            'pdf'       => 'nullable|mimes:pdf|max:10240',
            'content'   => [Rule::requiredIf($file->is_generated_document), 'nullable', 'string', 'max:15000000'],
        ]);

        $path = $file->location;
        $previousPath = $file->location;
        $editorContent = $file->editor_content;
        $isGeneratedDocument = $file->is_generated_document;

        if ($file->is_generated_document) {
            $editorContent = $this->sanitizeDocumentHtml($request->input('content', ''));
            if (trim(strip_tags($editorContent)) === '') {
                return back()->withErrors(['content' => 'Isi dokumen tidak boleh kosong.'])
                    ->withInput();
            }

            $client = ProdukClient::findOrFail($file->client);
            $documentName = trim(preg_replace('/\.pdf$/i', '', $file->is_pinned ? $file->nama_file : $request->nama_file));
            $content = $editorContent;
            $pdfContent = Pdf::loadView(
                'pdf.client_document',
                compact('client', 'documentName', 'content')
            )->setPaper('a4', 'portrait')->output();

            $newPath = $file->is_pinned
                ? 'pdf/'.$file->client.'/'.$file->division_id.'/'.str()->uuid().'.pdf'
                : $path;

            abort_unless(
                Storage::disk('public')->put($newPath, $pdfContent),
                500,
                'Dokumen gagal diperbarui.'
            );
            $path = $newPath;
        }

        // Jika upload file baru
        if ($request->hasFile('pdf')) {

            // Hapus file lama
            if (! $file->is_pinned && Storage::disk('public')->exists($file->location)) {
                Storage::disk('public')->delete($file->location);
            }

            $folder = 'pdf/'.$file->client.($file->division_id ? '/'.$file->division_id : '');

            $filename = time() . '_' . $request->file('pdf')->getClientOriginalName();

            $path = $request->file('pdf')->storeAs(
                $folder,
                $filename,
                'public'
            );
            $editorContent = null;
            $isGeneratedDocument = false;
        }

        $updateData = [
            'nama_file' => $file->is_pinned ? $file->nama_file : $request->nama_file,
            'location' => $path,
            'editor_content' => $editorContent,
            'is_generated_document' => $isGeneratedDocument,
        ];

        try {
            DB::transaction(function () use ($file, $updateData, $editorContent, $path) {
                $file->update($updateData);

                if ($file->is_pinned) {
                    $nextVersion = ((int) $file->versions()->reorder()->max('version_number')) + 1;
                    $file->versions()->create([
                        'version_number' => $nextVersion,
                        'nama_file' => $file->nama_file,
                        'location' => $path,
                        'editor_content' => $editorContent,
                        'changed_by' => Auth::id(),
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            if ($file->is_pinned && $path !== $previousPath) {
                Storage::disk('public')->delete($path);
            }
            throw $exception;
        }

        return redirect()->back()->with('success', 'File berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $file = FilePDF::findOrFail($id);
        $this->ensureFileAccessible($file);
        $this->authorizeDocumentMutation((int) $file->client, (int) $file->division_id);
        abort_if($file->is_system_template, 403, 'Dokumen bawaan client tidak dapat dihapus.');

        // Hapus file fisik
        if (Storage::disk('public')->exists($file->location)) {
            Storage::disk('public')->delete($file->location);
        }

        // Hapus data database
        $file->delete();

        return redirect()->back()->with('success', 'File berhasil dihapus.');
    }

    private function resolveClientDivision(Request $request, int $clientId): int
    {
        $divisionId = $request->integer('division_id');

        if (! $divisionId) {
            $divisionId = (int) DB::table('client_division')
                ->where('client_id', $clientId)
                ->orderBy('division_id')
                ->value('division_id');
        }

        $this->ensureClientDivision($clientId, $divisionId);

        return $divisionId;
    }

    private function ensureClientDivision(int $clientId, int $divisionId): void
    {
        abort_unless($divisionId > 0 && DB::table('client_division')
            ->where('client_id', $clientId)
            ->where('division_id', $divisionId)
            ->exists(), 404, 'Client tidak tersedia pada divisi tersebut.');

        $this->ensureMultimediaDocumentScope($clientId, $divisionId);
    }

    private function authorizeDocumentMutation(int $clientId, int $divisionId): void
    {
        $isDigitalMarketing = DB::table('divisions')
            ->where('id', $divisionId)
            ->whereRaw("LOWER(TRIM(name)) = 'digital marketing'")
            ->exists();

        if (! $isDigitalMarketing) {
            return;
        }

        $this->ensureClientDivision($clientId, $divisionId);
        abort_unless(
            DigitalMarketingPermissions::isManager(DigitalMarketingPermissions::role(Auth::user())),
            403,
            'File Dokumen hanya dapat diubah oleh Business Unit Head atau Digital Marketing Manager.'
        );
    }

    private function ensureFileAccessible(FilePdf $file): void
    {
        $this->ensureMultimediaDocumentScope((int) $file->client, (int) $file->division_id);
    }

    private function ensureMultimediaDocumentScope(int $clientId, int $divisionId): void
    {
        $user = Auth::user();
        if (! $user || $user->isAdmin) {
            return;
        }

        $multimediaId = DB::table('divisions')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['multimedia'])
            ->value('id');
        $isMultimediaUser = $multimediaId && $user->userDivisions()
            ->where('division_id', $multimediaId)
            ->exists();

        if ($isMultimediaUser) {
            $hasClientAccess = DB::table('user_client')
                ->where('user_id', $user->id)
                ->where('client_id', $clientId)
                ->exists();

            abort_unless((int) $multimediaId === $divisionId && $hasClientAccess, 403);
        }
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
