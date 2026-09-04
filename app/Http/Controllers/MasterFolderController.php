<?php

namespace App\Http\Controllers;

use App\Models\MasterFolder;
use App\Models\MasterFolderFile;
use App\Models\Division;
use App\Models\Role;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MasterFolderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $roleIds = $user->userDivisions()->pluck('role_id');

        $selectedDivisionId = $request->filled('division_id')
            ? (int) $request->input('division_id')
            : null;

        $folders = MasterFolder::with(['division', 'roles.division'])
            ->where('is_personal', false)
            ->when($selectedDivisionId, fn ($query) => $query->where('division_id', $selectedDivisionId))
            ->when(
                $user->isAdmin,
                fn ($query) => $query->withCount('files'),
                fn ($query) => $query->withCount([
                    'files' => fn ($files) => $files->whereHas(
                        'roles',
                        fn ($roles) => $roles->whereIn('roles.id', $roleIds)
                    ),
                ])
            )
            ->when(! $user->isAdmin, fn ($query) => $query->whereHas(
                'roles',
                fn ($roles) => $roles->whereIn('roles.id', $roleIds)
            ))
            ->latest()
            ->get();

        $roles = Role::query()
            ->orderBy('level')
            ->orderBy('role_name')
            ->get()
            ->groupBy('division_id');

        $divisions = Division::query()->orderBy('name')->get();

        return view('master_folder.index', [
            'title' => 'Folder Master',
            'folders' => $folders,
            'rolesByDivision' => $roles,
            'divisions' => $divisions,
            'selectedDivisionId' => $selectedDivisionId,
            'isSuperAdmin' => (bool) $user->isAdmin,
        ]);
    }

    public function myFolders(Request $request)
    {
        return redirect()->route('master-folder.index');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isAdmin, 403, 'Hanya admin yang dapat membuat folder.');

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:150',
                Rule::unique('master_folders')->where(fn ($query) => $query
                    ->where('is_personal', false)),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
        ], [
            'name.unique' => 'Nama folder sudah digunakan.',
            'role_ids.required' => 'Pilih minimal satu role yang dapat melihat folder.',
        ]);

        DB::transaction(function () use ($validated, $user) {
            $folder = MasterFolder::create([
                'division_id' => $validated['division_id'],
                'created_by' => $user->id,
                'name' => trim($validated['name']),
                'description' => $validated['description'] ?? null,
                'is_personal' => false,
            ]);
            $folder->roles()->sync($validated['role_ids']);
        });

        return redirect()->route('master-folder.index')
            ->with('success', 'Folder berhasil dibuat.');
    }

    public function show(Request $request, MasterFolder $folder)
    {
        $this->authorizeFolder($request, $folder);
        $folder->load(['division', 'roles.division']);

        $files = $folder->files()
            ->with(['uploader', 'roles.division'])
            ->when(
                ($folder->is_personal && (int) $folder->created_by !== (int) $request->user()->id)
                    || (! $request->user()->isAdmin && ! $folder->is_personal),
                function ($query) use ($request) {
                $roleIds = $request->user()->userDivisions()->pluck('role_id');

                $query->whereHas('roles', fn ($roles) => $roles->whereIn('roles.id', $roleIds));
                }
            )
            ->latest()
            ->get();

        $folder->setRelation('files', $files);

        $canManage = $this->canManageFolder($request, $folder);
        $backToMine = $folder->is_personal || $request->query('from') === 'mine' || ! $canManage;

        return view('master_folder.show', [
            'title' => $folder->name,
            'folder' => $folder,
            'canManage' => $canManage,
            'backToMine' => $backToMine,
        ]);
    }

    public function update(Request $request, MasterFolder $folder)
    {
        $this->authorizeManageFolder($request, $folder);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:150',
                Rule::unique('master_folders')->where(fn ($query) => $query->where('is_personal', false))->ignore($folder->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'role_ids' => [$folder->is_personal ? 'nullable' : 'required', 'array'],
            'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
        ], [
            'name.unique' => 'Nama folder sudah digunakan.',
            'role_ids.required' => 'Pilih minimal satu role yang dapat melihat folder.',
        ]);

        DB::transaction(function () use ($folder, $validated) {
            $folder->update([
                'name' => trim($validated['name']),
                'description' => $validated['description'] ?? null,
                'division_id' => $validated['division_id'],
            ]);
            $folder->roles()->sync($validated['role_ids'] ?? []);
        });

        return redirect()->route('master-folder.index')
            ->with('success', 'Folder berhasil diperbarui.');
    }

    public function destroy(Request $request, MasterFolder $folder)
    {
        $this->authorizeManageFolder($request, $folder);

        DB::transaction(fn () => $folder->delete());
        Storage::disk('local')->deleteDirectory('master-folders/'.$folder->id);

        return redirect()->route('master-folder.index')
            ->with('success', 'Folder dan seluruh file di dalamnya berhasil dihapus.');
    }

    public function upload(Request $request, MasterFolder $folder)
    {
        $this->authorizeManageFolder($request, $folder);
        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', 'file', 'max:25600'],
            'role_ids' => [$folder->is_personal ? 'nullable' : 'required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
        ], [
            'files.*.max' => 'Ukuran setiap file maksimal 25 MB.',
            'role_ids.required' => 'Pilih minimal satu role yang dapat melihat file.',
        ]);

        $roleIds = $validated['role_ids'] ?? [];
        $folderRoleIds = $folder->roles()->pluck('roles.id');
        if (collect($roleIds)->diff($folderRoleIds)->isNotEmpty()) {
            return back()->withErrors([
                'role_ids' => 'Akses file hanya dapat diberikan kepada role yang memiliki akses ke folder.',
            ])->withInput();
        }

        foreach ($validated['files'] as $uploadedFile) {
            $storedName = (string) str()->uuid().'.'.$uploadedFile->getClientOriginalExtension();
            $path = $uploadedFile->storeAs('master-folders/'.$folder->id, $storedName, 'local');
            $originalName = $this->uniqueFileName($folder, $uploadedFile->getClientOriginalName());

            $file = MasterFolderFile::create([
                'master_folder_id' => $folder->id,
                'uploaded_by' => $request->user()->id,
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'path' => $path,
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
            ]);
            $file->roles()->sync($roleIds);
        }

        return back()->with('success', count($validated['files']).' file berhasil diunggah.');
    }

    public function download(Request $request, MasterFolder $folder, MasterFolderFile $file)
    {
        $this->authorizeFolder($request, $folder);
        abort_unless($file->master_folder_id === $folder->id, 404);
        $this->authorizeFile($request, $folder, $file);
        $this->restoreGeneratedPdfIfMissing($folder, $file);
        abort_unless(Storage::disk('local')->exists($file->path), 404, 'File tidak ditemukan di penyimpanan.');

        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    public function preview(Request $request, MasterFolder $folder, MasterFolderFile $file)
    {
        $this->authorizeFolder($request, $folder);
        abort_unless($file->master_folder_id === $folder->id, 404);
        $this->authorizeFile($request, $folder, $file);

        $isPdf = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)) === 'pdf'
            || $file->mime_type === 'application/pdf';

        abort_unless($isPdf, 415, 'Preview hanya tersedia untuk file PDF.');
        $this->restoreGeneratedPdfIfMissing($folder, $file);
        abort_unless(Storage::disk('local')->exists($file->path), 404, 'File tidak ditemukan di penyimpanan.');

        return Storage::disk('local')->response($file->path, $file->original_name, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function viewPdf(Request $request, MasterFolder $folder, MasterFolderFile $file)
    {
        $this->authorizeFolder($request, $folder);
        abort_unless($file->master_folder_id === $folder->id, 404);
        $this->authorizeFile($request, $folder, $file);

        $isPdf = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)) === 'pdf'
            || $file->mime_type === 'application/pdf';
        abort_unless($isPdf, 415, 'Preview hanya tersedia untuk file PDF.');

        return view('master_folder.preview', [
            'title' => $file->original_name,
            'folder' => $folder,
            'file' => $file,
        ]);
    }

    public function createDocument(Request $request, MasterFolder $folder)
    {
        $this->authorizeManageFolder($request, $folder);

        if (is_string($request->input('content'))) {
            $request->merge(['content' => $this->sanitizeDocumentHtml($request->input('content'))]);
        }

        $validated = $request->validate([
            'document_name' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:15000000'],
            'role_ids' => [$folder->is_personal ? 'nullable' : 'required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
        ], [
            'document_name.required' => 'Nama dokumen wajib diisi.',
            'content.required' => 'Isi dokumen wajib diisi.',
            'role_ids.required' => 'Pilih minimal satu role yang dapat melihat dokumen.',
        ]);

        $roleIds = $validated['role_ids'] ?? [];
        $folderRoleIds = $folder->roles()->pluck('roles.id');
        if (collect($roleIds)->diff($folderRoleIds)->isNotEmpty()) {
            return back()->withErrors([
                'role_ids' => 'Akses dokumen hanya dapat diberikan kepada role yang memiliki akses ke folder.',
            ])->withInput()->with('document_error', true);
        }

        $content = $this->sanitizeDocumentHtml($validated['content']);
        if (trim(strip_tags($content)) === '') {
            return back()->withErrors(['content' => 'Isi dokumen tidak boleh kosong.'])
                ->withInput()->with('document_error', true);
        }

        $documentName = trim(preg_replace('/\.pdf$/i', '', $validated['document_name']));
        if ($documentName === '') {
            return back()->withErrors(['document_name' => 'Nama dokumen tidak valid.'])
                ->withInput()->with('document_error', true);
        }
        $originalName = $this->uniqueFileName($folder, $documentName.'.pdf');
        $documentName = pathinfo($originalName, PATHINFO_FILENAME);
        $storedName = (string) str()->uuid().'.pdf';
        $path = 'master-folders/'.$folder->id.'/'.$storedName;
        $pdfContent = Pdf::loadView('master_folder.document_pdf', compact('folder', 'documentName', 'content'))
            ->setPaper('a4', 'portrait')
            ->output();

        abort_unless(Storage::disk('local')->put($path, $pdfContent), 500, 'Dokumen gagal disimpan.');

        $file = MasterFolderFile::create([
            'master_folder_id' => $folder->id,
            'uploaded_by' => $request->user()->id,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size' => strlen($pdfContent),
            'editor_content' => $content,
            'is_generated_document' => true,
        ]);
        $file->roles()->sync($roleIds);

        return back()->with('success', 'Dokumen PDF berhasil dibuat.');
    }

    public function updateFile(Request $request, MasterFolder $folder, MasterFolderFile $file)
    {
        $this->authorizeManageFolder($request, $folder);
        abort_unless($file->master_folder_id === $folder->id, 404);
        $this->authorizeFile($request, $folder, $file);

        if ($file->is_generated_document && is_string($request->input('content'))) {
            $request->merge(['content' => $this->sanitizeDocumentHtml($request->input('content'))]);
        }

        $validated = $request->validate([
            'file_name' => ['required', 'string', 'max:150'],
            'content' => [Rule::requiredIf($file->is_generated_document), 'nullable', 'string', 'max:15000000'],
            'role_ids' => [$folder->is_personal ? 'nullable' : 'required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
        ], [
            'file_name.required' => 'Nama file wajib diisi.',
            'content.required' => 'Isi dokumen wajib diisi.',
            'role_ids.required' => 'Pilih minimal satu role yang dapat melihat file.',
        ]);

        $roleIds = $validated['role_ids'] ?? [];
        $folderRoleIds = $folder->roles()->pluck('roles.id');
        if (collect($roleIds)->diff($folderRoleIds)->isNotEmpty()) {
            return back()->withErrors([
                'role_ids' => 'Akses file hanya dapat diberikan kepada role yang memiliki akses ke folder.',
            ])->withInput()->with('edit_file_id', $file->id);
        }

        $originalName = trim($validated['file_name']);
        $newPdfContent = null;
        $editorContent = $file->editor_content;

        if ($file->is_generated_document) {
            $documentName = trim(preg_replace('/\.pdf$/i', '', $originalName));
            if ($documentName === '') {
                return back()->withErrors(['file_name' => 'Nama dokumen tidak valid.'])
                    ->withInput()->with('edit_file_id', $file->id);
            }

            $editorContent = $this->sanitizeDocumentHtml($validated['content']);
            if (trim(strip_tags($editorContent)) === '') {
                return back()->withErrors(['content' => 'Isi dokumen tidak boleh kosong.'])
                    ->withInput()->with('edit_file_id', $file->id);
            }

            $originalName = $this->uniqueFileName($folder, $documentName.'.pdf', $file->id);
            $documentName = pathinfo($originalName, PATHINFO_FILENAME);
            $content = $editorContent;
            $newPdfContent = Pdf::loadView(
                'master_folder.document_pdf',
                compact('folder', 'documentName', 'content')
            )->setPaper('a4', 'portrait')->output();
        } else {
            $originalName = $this->uniqueFileName($folder, $originalName, $file->id);
        }

        DB::transaction(function () use ($file, $validated, $originalName, $editorContent, $newPdfContent, $roleIds) {
            if ($newPdfContent !== null) {
                abort_unless(
                    Storage::disk('local')->put($file->path, $newPdfContent),
                    500,
                    'Dokumen gagal diperbarui.'
                );
            }

            $file->update([
                'original_name' => $originalName,
                'editor_content' => $editorContent,
                'size' => $newPdfContent !== null ? strlen($newPdfContent) : $file->size,
            ]);
            $file->roles()->sync($roleIds);
        });

        return back()->with('success', 'File berhasil diperbarui.');
    }

    private function uniqueFileName(MasterFolder $folder, string $requestedName, ?int $ignoreFileId = null): string
    {
        $requestedName = trim(basename(str_replace('\\', '/', $requestedName)));
        $extension = pathinfo($requestedName, PATHINFO_EXTENSION);
        $baseName = pathinfo($requestedName, PATHINFO_FILENAME);
        $extensionWithDot = $extension !== '' ? '.'.$extension : '';
        $baseName = $baseName !== '' ? $baseName : 'File';
        $number = 0;

        do {
            $suffix = $number > 0 ? ' ('.$number.')' : '';
            $maximumBaseLength = max(1, 255 - mb_strlen($extensionWithDot.$suffix));
            $candidate = mb_substr($baseName, 0, $maximumBaseLength).$suffix.$extensionWithDot;
            $query = $folder->files()
                ->whereRaw('LOWER(original_name) = ?', [mb_strtolower($candidate)]);

            if ($ignoreFileId !== null) {
                $query->whereKeyNot($ignoreFileId);
            }

            $number++;
        } while ($query->exists());

        return $candidate;
    }

    public function destroyFile(Request $request, MasterFolder $folder, MasterFolderFile $file)
    {
        $this->authorizeManageFolder($request, $folder);
        abort_unless($file->master_folder_id === $folder->id, 404);
        $this->authorizeFile($request, $folder, $file);

        $path = $file->path;
        DB::transaction(fn () => $file->delete());
        Storage::disk('local')->delete($path);

        return back()->with('success', 'File berhasil dihapus.');
    }

    private function authorizeFolder(Request $request, MasterFolder $folder): void
    {
        if ($folder->is_personal && (int) $folder->created_by === (int) $request->user()->id) {
            return;
        }

        if ($request->user()->isAdmin && ! $folder->is_personal) {
            return;
        }

        $hasAccess = $request->user()->userDivisions()
            ->whereIn('role_id', $folder->roles()->select('roles.id'))
            ->exists();

        abort_unless($hasAccess, 403, 'Anda tidak memiliki akses ke folder ini.');
    }

    private function authorizeFile(Request $request, MasterFolder $folder, MasterFolderFile $file): void
    {
        if ($folder->is_personal && (int) $folder->created_by === (int) $request->user()->id) {
            return;
        }

        if ($request->user()->isAdmin && ! $folder->is_personal) {
            return;
        }

        $hasAccess = $request->user()->userDivisions()
            ->whereIn('role_id', $file->roles()->select('roles.id'))
            ->exists();

        abort_unless($hasAccess, 403, 'Anda tidak memiliki akses ke file ini.');
    }

    private function authorizeManageFolder(Request $request, MasterFolder $folder): void
    {
        abort_unless(
            $this->canManageFolder($request, $folder),
            403,
            'Akses lintas divisi hanya dapat digunakan untuk melihat folder dan file.'
        );
    }

    private function canManageFolder(Request $request, MasterFolder $folder): bool
    {
        if ($folder->is_personal) {
            return (int) $folder->created_by === (int) $request->user()->id;
        }

        if ($request->user()->isAdmin) {
            return true;
        }

        return false;
    }

    private function sanitizeDocumentHtml(string $html): string
    {
        $allowedTags = '<p><div><span><font><br><hr><h1><h2><h3><h4><h5><h6><strong><b><em><i><u><s><sup><sub><a><img><ul><ol><li><blockquote><table><thead><tbody><tfoot><tr><th><td><colgroup><col>';
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

                if ($element->tagName === 'br' && $attribute === 'clear'
                    && in_array(strtolower($value), ['all', 'left', 'right', 'both', 'none'], true)) {
                    continue;
                }

                if ($element->tagName === 'a' && $attribute === 'href' && $this->isSafeDocumentLink($value)) {
                    continue;
                }

                if ($element->tagName === 'a' && $attribute === 'target' && in_array($value, ['_blank', '_self'], true)) {
                    continue;
                }

                if ($element->tagName === 'img' && $attribute === 'src' && $this->isSafeEmbeddedImage($value)) {
                    continue;
                }

                if ($element->tagName === 'img' && $attribute === 'alt') {
                    continue;
                }

                if ($element->tagName === 'img' && in_array($attribute, ['width', 'height'], true)
                    && preg_match('/^\d+(\.\d+)?$/', $value)) {
                    continue;
                }

                if (in_array($element->tagName, ['table', 'td', 'th', 'col'], true)
                    && in_array($attribute, ['width', 'height'], true)
                    && preg_match('/^\d+(\.\d+)?%?$/', $value)) {
                    continue;
                }

                if (in_array($element->tagName, ['table', 'td', 'th'], true)
                    && $attribute === 'border' && preg_match('/^\d+(\.\d+)?$/', $value)) {
                    continue;
                }

                if ($element->tagName === 'table'
                    && in_array($attribute, ['cellpadding', 'cellspacing'], true)
                    && preg_match('/^\d+(\.\d+)?$/', $value)) {
                    continue;
                }

                if (in_array($element->tagName, ['td', 'th'], true)
                    && in_array($attribute, ['colspan', 'rowspan'], true)
                    && preg_match('/^[1-9]\d*$/', $value)) {
                    continue;
                }

                if (in_array($element->tagName, ['tr', 'td', 'th'], true)
                    && $attribute === 'valign'
                    && in_array(strtolower($value), ['top', 'middle', 'bottom', 'baseline'], true)) {
                    continue;
                }

                if (in_array($element->tagName, ['table', 'tr', 'td', 'th'], true)
                    && $attribute === 'bgcolor'
                    && preg_match('/^(transparent|#[0-9a-f]{3,8}|[a-z]+)$/i', $value)) {
                    continue;
                }

                if ($attribute === 'style') {
                    $safeStyle = $this->sanitizeInlineStyle($value);
                    if (in_array($element->tagName, ['br', 'hr'], true)
                        && (str_contains($safeStyle, 'page-break-before: always') || str_contains($safeStyle, 'break-before: page'))) {
                        $safeStyle = preg_replace('/(^|;)\s*display:\s*none\s*(;|$)/i', '$1', $safeStyle);
                        $safeStyle .= '; border: none; height: 0; margin: 0; padding: 0';
                    }
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

    private function isSafeDocumentLink(string $url): bool
    {
        if (str_starts_with($url, '#')) {
            return preg_match('/^#[A-Za-z0-9_-]+$/', $url) === 1;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https', 'mailto'], true);
    }

    private function isSafeEmbeddedImage(string $source): bool
    {
        return preg_match('#^data:image/(png|jpe?g|gif|webp);base64,[a-z0-9+/=\r\n]+$#i', $source) === 1;
    }

    private function restoreGeneratedPdfIfMissing(MasterFolder $folder, MasterFolderFile $file): void
    {
        if (Storage::disk('local')->exists($file->path) || ! $file->is_generated_document) {
            return;
        }

        abort_if(
            blank($file->editor_content),
            404,
            'Sumber dokumen tidak tersedia untuk membuat ulang PDF.'
        );

        $documentName = trim(preg_replace('/\.pdf$/i', '', $file->original_name));
        $content = $file->editor_content;
        $pdfContent = Pdf::loadView(
            'master_folder.document_pdf',
            compact('folder', 'documentName', 'content')
        )->setPaper('a4', 'portrait')->output();

        abort_unless(
            Storage::disk('local')->put($file->path, $pdfContent),
            500,
            'PDF gagal dibuat ulang.'
        );

        $file->update(['size' => strlen($pdfContent)]);
    }

    private function sanitizeInlineStyle(string $style): string
    {
        $safe = [];
        $cssLength = '(?:0|-?\d+(?:\.\d+)?(?:px|pt|pc|em|rem|%|cm|mm|in)?)';

        foreach (explode(';', $style) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);
            $normalizedValue = strtolower($value);

            if (str_contains($normalizedValue, 'url(') || str_contains($normalizedValue, 'expression(')) {
                continue;
            }

            $isSafe = match ($property) {
                'text-align' => in_array($normalizedValue, ['left', 'center', 'right', 'justify', 'start', 'end'], true),
                'font-weight' => in_array($normalizedValue, ['normal', 'bold', 'bolder', 'lighter', '100', '200', '300', '400', '500', '600', '700', '800', '900'], true),
                'font-style' => in_array($normalizedValue, ['normal', 'italic', 'oblique'], true),
                'text-decoration', 'text-decoration-line' => preg_match('/^(none|underline|line-through|underline line-through)$/', $normalizedValue) === 1,
                'font-family' => preg_match('/^[a-z0-9 ,"\'\-_]+$/i', $value) === 1,
                'font-size', 'line-height', 'letter-spacing', 'word-spacing', 'text-indent',
                'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
                'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
                'width', 'height', 'min-width', 'max-width', 'min-height', 'max-height' => preg_match('/^(normal|auto|0|(-?\d+(\.\d+)?)(px|pt|pc|em|rem|%|cm|mm|in)?)$/', $normalizedValue) === 1,
                'margin', 'padding' => preg_match('/^'.$cssLength.'(?:\s+'.$cssLength.'){0,3}$/', $normalizedValue) === 1,
                'color', 'background', 'background-color', 'border-color' => preg_match('/^(none|transparent|#[0-9a-f]{3,8}|[a-z]+|rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+(?:\s*,\s*[\d.]+)?\s*\))$/i', $normalizedValue) === 1,
                'opacity' => preg_match('/^(0(\.\d+)?|1(\.0+)?)$/', $normalizedValue) === 1,
                'visibility' => in_array($normalizedValue, ['visible', 'hidden', 'collapse'], true),
                'display' => in_array($normalizedValue, ['none', 'block', 'inline', 'inline-block', 'table', 'table-row', 'table-cell'], true),
                'page-break-before', 'page-break-after' => in_array($normalizedValue, ['auto', 'always', 'avoid', 'left', 'right'], true),
                'page-break-inside' => in_array($normalizedValue, ['auto', 'avoid'], true),
                'break-before', 'break-after' => in_array($normalizedValue, ['auto', 'page', 'avoid', 'avoid-page', 'left', 'right'], true),
                'break-inside' => in_array($normalizedValue, ['auto', 'avoid', 'avoid-page'], true),
                'float' => in_array($normalizedValue, ['none', 'left', 'right'], true),
                'clear' => in_array($normalizedValue, ['none', 'left', 'right', 'both'], true),
                'white-space' => in_array($normalizedValue, ['normal', 'nowrap', 'pre', 'pre-wrap', 'pre-line'], true),
                'vertical-align' => preg_match('/^(baseline|sub|super|top|text-top|middle|bottom|text-bottom|(-?\d+(\.\d+)?)(px|pt|em|rem|%)?)$/', $normalizedValue) === 1,
                'list-style-type' => preg_match('/^(none|disc|circle|square|decimal|lower-alpha|upper-alpha|lower-roman|upper-roman)$/', $normalizedValue) === 1,
                'border-collapse' => in_array($normalizedValue, ['collapse', 'separate'], true),
                'border-spacing' => preg_match('/^(0|\d+(\.\d+)?(px|pt|em|rem|cm|mm|in))(\s+(0|\d+(\.\d+)?(px|pt|em|rem|cm|mm|in)))?$/', $normalizedValue) === 1,
                'empty-cells' => in_array($normalizedValue, ['show', 'hide'], true),
                'border-style', 'border-top-style', 'border-right-style', 'border-bottom-style', 'border-left-style' => in_array($normalizedValue, ['none', 'hidden', 'solid', 'dashed', 'dotted', 'double'], true),
                'border-width', 'border-top-width', 'border-right-width', 'border-bottom-width', 'border-left-width' => preg_match('/^(0|thin|medium|thick|\d+(\.\d+)?(px|pt))$/', $normalizedValue) === 1,
                'border-top-color', 'border-right-color', 'border-bottom-color', 'border-left-color' => preg_match('/^(transparent|#[0-9a-f]{3,8}|[a-z]+|rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+(?:\s*,\s*[\d.]+)?\s*\))$/i', $normalizedValue) === 1,
                'border', 'border-top', 'border-right', 'border-bottom', 'border-left' => $normalizedValue === 'none' || preg_match('/^\d+(\.\d+)?(px|pt)\s+(solid|dashed|dotted|double)\s+(transparent|#[0-9a-f]{3,8}|[a-z]+)$/i', $normalizedValue) === 1,
                default => false,
            };

            if ($isSafe) {
                $safe[] = $property.': '.$value;
            }
        }

        return implode('; ', $safe);
    }
}
