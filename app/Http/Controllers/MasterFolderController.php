<?php

namespace App\Http\Controllers;

use App\Models\MasterFolder;
use App\Models\MasterFolderFile;
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

        $folders = MasterFolder::with(['roles.division'])
            ->where('is_personal', false)
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

        return view('master_folder.index', [
            'title' => 'Folder Master',
            'folders' => $folders,
            'rolesByDivision' => $roles,
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
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
        ], [
            'name.unique' => 'Nama folder sudah digunakan.',
            'role_ids.required' => 'Pilih minimal satu role yang dapat melihat folder.',
        ]);

        DB::transaction(function () use ($validated, $user) {
            $folder = MasterFolder::create([
                'division_id' => null,
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

            $file = MasterFolderFile::create([
                'master_folder_id' => $folder->id,
                'uploaded_by' => $request->user()->id,
                'original_name' => $uploadedFile->getClientOriginalName(),
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

    public function createDocument(Request $request, MasterFolder $folder)
    {
        $this->authorizeManageFolder($request, $folder);

        if (is_string($request->input('content'))) {
            $request->merge(['content' => $this->sanitizeDocumentHtml($request->input('content'))]);
        }

        $validated = $request->validate([
            'document_name' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:200000'],
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
        $originalName = $documentName.'.pdf';
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
            'content' => [Rule::requiredIf($file->is_generated_document), 'nullable', 'string', 'max:200000'],
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

            $originalName = $documentName.'.pdf';
            $content = $editorContent;
            $newPdfContent = Pdf::loadView(
                'master_folder.document_pdf',
                compact('folder', 'documentName', 'content')
            )->setPaper('a4', 'portrait')->output();
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
        $allowedTags = '<p><div><span><font><br><hr><h1><h2><h3><h4><h5><h6><strong><b><em><i><u><s><sup><sub><ul><ol><li><blockquote><table><thead><tbody><tfoot><tr><th><td><colgroup><col>';
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
                'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
                'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
                'width', 'min-width', 'max-width' => preg_match('/^(normal|auto|0|(-?\d+(\.\d+)?)(px|pt|pc|em|rem|%|cm|mm|in)?)$/', $normalizedValue) === 1,
                'color', 'background', 'background-color' => preg_match('/^(transparent|#[0-9a-f]{3,8}|[a-z]+|rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+(?:\s*,\s*[\d.]+)?\s*\))$/i', $normalizedValue) === 1,
                'white-space' => in_array($normalizedValue, ['normal', 'nowrap', 'pre', 'pre-wrap', 'pre-line'], true),
                'vertical-align' => preg_match('/^(baseline|sub|super|top|text-top|middle|bottom|text-bottom|(-?\d+(\.\d+)?)(px|pt|em|rem|%)?)$/', $normalizedValue) === 1,
                'list-style-type' => preg_match('/^(none|disc|circle|square|decimal|lower-alpha|upper-alpha|lower-roman|upper-roman)$/', $normalizedValue) === 1,
                'border-collapse' => in_array($normalizedValue, ['collapse', 'separate'], true),
                'border', 'border-top', 'border-right', 'border-bottom', 'border-left' => preg_match('/^\d+(\.\d+)?(px|pt)\s+(solid|dashed|dotted|double)\s+(#[0-9a-f]{3,8}|[a-z]+)$/i', $normalizedValue) === 1,
                default => false,
            };

            if ($isSafe) {
                $safe[] = $property.': '.$value;
            }
        }

        return implode('; ', $safe);
    }
}
