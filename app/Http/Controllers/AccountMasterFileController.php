<?php

namespace App\Http\Controllers;

use App\Models\AccountMasterFile;
use App\Models\Division;
use App\Models\Role;
use App\Models\User;
use App\Models\userDivision as UserDivision;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AccountMasterFileController extends Controller
{
    public function index(Request $request)
    {
        $visibleIds = $this->visibleOwnerIds($request->user());
        $users = User::unlocked()->with(['userDivisions.division', 'userDivisions.role'])
            ->withCount('accountMasterFiles')
            ->whereIn('id', $visibleIds)
            ->when($request->filled('division_id'), fn ($query) => $query->whereHas(
                'userDivisions', fn ($item) => $item->where('division_id', $request->integer('division_id'))
            ))
            ->when($request->filled('role_id'), fn ($query) => $query->whereHas(
                'userDivisions', fn ($item) => $item->where('role_id', $request->integer('role_id'))
            ))
            ->orderBy('name')
            ->get();

        return view('account_master_file.index', [
            'title' => 'File Master',
            'users' => $users,
            'divisions' => Division::orderBy('name')->get(),
            'roles' => Role::with('division')->orderBy('level')->orderBy('role_name')->get(),
        ]);
    }

    public function show(Request $request, User $owner)
    {
        $this->authorizeView($request->user(), $owner);
        $owner->load(['userDivisions.division', 'userDivisions.role']);

        return view('account_master_file.show', [
            'title' => 'File Master '.$owner->name,
            'owner' => $owner,
            'files' => $owner->accountMasterFiles()->with('uploader')
                ->when($request->filled('uploaded_by'), fn ($query) => $query->where('uploaded_by', $request->integer('uploaded_by')))
                ->latest()->get(),
            'uploaders' => User::query()
                ->unlocked()
                ->whereIn('id', $owner->accountMasterFiles()->whereNotNull('uploaded_by')->select('uploaded_by'))
                ->orderBy('name')->get(['id', 'name', 'email']),
            'selectedUploaderId' => $request->integer('uploaded_by') ?: null,
            'canManage' => (int) $request->user()->id === (int) $owner->id,
        ]);
    }

    public function upload(Request $request, User $owner)
    {
        $this->authorizeManage($request->user(), $owner);
        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', 'file', 'max:25600'],
        ]);

        foreach ($validated['files'] as $uploadedFile) {
            $originalName = $this->uniqueName($owner, $uploadedFile->getClientOriginalName());
            $storedName = (string) str()->uuid().'.'.$uploadedFile->getClientOriginalExtension();
            $path = $uploadedFile->storeAs('account-master-files/'.$owner->id, $storedName, 'local');
            AccountMasterFile::create([
                'owner_user_id' => $owner->id,
                'uploaded_by' => $request->user()->id,
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'path' => $path,
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
            ]);
        }

        return back()->with('success', count($validated['files']).' file berhasil diunggah.');
    }

    public function createDocument(Request $request, User $owner)
    {
        $this->authorizeManage($request->user(), $owner);
        $validated = $request->validate([
            'document_name' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:15000000'],
        ]);

        $content = $this->sanitizeDocumentHtml($validated['content']);
        abort_if(trim(strip_tags($content)) === '', 422, 'Isi dokumen tidak boleh kosong.');
        $baseName = trim(preg_replace('/\.pdf$/i', '', $validated['document_name']));
        abort_if($baseName === '', 422, 'Nama dokumen tidak valid.');
        $originalName = $this->uniqueName($owner, $baseName.'.pdf');
        $documentName = pathinfo($originalName, PATHINFO_FILENAME);
        $storedName = (string) str()->uuid().'.pdf';
        $path = 'account-master-files/'.$owner->id.'/'.$storedName;
        $pdf = Pdf::loadView('account_master_file.document_pdf', compact('owner', 'documentName', 'content'))
            ->setPaper('a4', 'portrait')->output();
        abort_unless(Storage::disk('local')->put($path, $pdf), 500, 'Dokumen gagal disimpan.');

        AccountMasterFile::create([
            'owner_user_id' => $owner->id,
            'uploaded_by' => $request->user()->id,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size' => strlen($pdf),
            'editor_content' => $content,
            'is_generated_document' => true,
        ]);

        return back()->with('success', 'Dokumen PDF berhasil dibuat.');
    }

    public function view(Request $request, User $owner, string $fileName)
    {
        $file = $this->accessibleFile($request, $owner, $fileName);
        abort_unless($this->isPdf($file), 415, 'Preview hanya tersedia untuk PDF.');
        return view('account_master_file.preview', compact('owner', 'file'));
    }

    public function preview(Request $request, User $owner, string $fileName)
    {
        $file = $this->accessibleFile($request, $owner, $fileName);
        abort_unless($this->isPdf($file), 415, 'Preview hanya tersedia untuk PDF.');
        $this->refreshGeneratedPdf($owner, $file);
        abort_unless(Storage::disk('local')->exists($file->path), 404);
        return Storage::disk('local')->response($file->path, $file->original_name, ['Content-Type' => 'application/pdf']);
    }

    public function download(Request $request, User $owner, string $fileName)
    {
        $file = $this->accessibleFile($request, $owner, $fileName);
        $this->refreshGeneratedPdf($owner, $file);
        abort_unless(Storage::disk('local')->exists($file->path), 404);
        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    public function destroy(Request $request, User $owner, AccountMasterFile $file)
    {
        $this->authorizeManage($request->user(), $owner);
        abort_unless((int) $file->owner_user_id === (int) $owner->id, 404);
        Storage::disk('local')->delete($file->path);
        $file->delete();
        return back()->with('success', 'File berhasil dihapus.');
    }

    private function accessibleFile(Request $request, User $owner, string $fileName): AccountMasterFile
    {
        $this->authorizeView($request->user(), $owner);
        return $owner->accountMasterFiles()->where('original_name', $fileName)->firstOrFail();
    }

    private function authorizeView(User $viewer, User $owner): void
    {
        abort_unless(in_array((int) $owner->id, $this->visibleOwnerIds($viewer), true), 403,
            'Anda tidak memiliki akses ke File Master akun ini.');
    }

    private function authorizeManage(User $viewer, User $owner): void
    {
        abort_unless((int) $viewer->id === (int) $owner->id, 403,
            'File Master hanya dapat diubah oleh pemilik akun.');
    }

    private function visibleOwnerIds(User $viewer): array
    {
        if ($viewer->isAdmin) {
            return User::pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        $rows = UserDivision::with('role')->get();
        $visible = [(int) $viewer->id => true];
        foreach ($rows->where('user_id', $viewer->id) as $root) {
            $rootLevel = (int) ($root->role?->level ?? 0);
            $frontier = [(int) $viewer->id];
            $visitedInDivision = [(int) $viewer->id => true];
            while ($frontier !== []) {
                $children = $rows->filter(fn ($row) => in_array((int) $row->parent_id, $frontier, true)
                    && (int) $row->division_id === (int) $root->division_id
                    && (int) ($row->role?->level ?? 0) > $rootLevel);
                $frontier = [];
                foreach ($children as $child) {
                    $childId = (int) $child->user_id;
                    if (! isset($visitedInDivision[$childId])) {
                        $visitedInDivision[$childId] = true;
                        $frontier[] = $childId;
                    }
                    $visible[$childId] = true;
                }
            }
        }
        return array_map('intval', array_keys($visible));
    }

    private function uniqueName(User $owner, string $requested): string
    {
        $requested = trim(basename(str_replace('\\', '/', $requested)));
        $extension = pathinfo($requested, PATHINFO_EXTENSION);
        $base = pathinfo($requested, PATHINFO_FILENAME) ?: 'File';
        $dotExtension = $extension !== '' ? '.'.$extension : '';
        $number = 0;
        do {
            $suffix = $number ? ' ('.$number.')' : '';
            $candidate = mb_substr($base, 0, max(1, 255 - mb_strlen($suffix.$dotExtension))).$suffix.$dotExtension;
            $exists = $owner->accountMasterFiles()->whereRaw('LOWER(original_name) = ?', [mb_strtolower($candidate)])->exists();
            $number++;
        } while ($exists);
        return $candidate;
    }

    private function isPdf(AccountMasterFile $file): bool
    {
        return strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)) === 'pdf'
            || $file->mime_type === 'application/pdf';
    }

    private function refreshGeneratedPdf(User $owner, AccountMasterFile $file): void
    {
        if (! $file->is_generated_document) {
            return;
        }

        abort_if(blank($file->editor_content), 404, 'Sumber dokumen tidak tersedia untuk membuat ulang PDF.');

        $documentName = trim(preg_replace('/\.pdf$/i', '', $file->original_name));
        $content = $this->sanitizeDocumentHtml($file->editor_content);
        $pdf = Pdf::loadView('account_master_file.document_pdf', compact('owner', 'documentName', 'content'))
            ->setPaper('a4', 'portrait')->output();

        abort_unless(Storage::disk('local')->put($file->path, $pdf), 500, 'PDF gagal dibuat ulang.');
        $file->update(['size' => strlen($pdf)]);
    }

    private function sanitizeDocumentHtml(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html);
        $clean = strip_tags($html, '<p><div><span><font><br><hr><h1><h2><h3><h4><h5><h6><strong><b><em><i><u><s><sup><sub><a><img><ul><ol><li><blockquote><table><thead><tbody><tfoot><tr><th><td><colgroup><col>');
        $document = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><body>'.$clean.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        foreach ($document->getElementsByTagName('*') as $element) {
            $names = [];
            foreach ($element->attributes ?? [] as $attribute) $names[] = $attribute->name;
            foreach ($names as $name) {
                $value = trim($element->getAttribute($name));
                $keep = ($name === 'style' && ! preg_match('/url\s*\(|expression\s*\(|javascript:/i', $value))
                    || ($name === 'align' && in_array($value, ['left', 'center', 'right', 'justify'], true))
                    || ($element->tagName === 'a' && $name === 'href' && (str_starts_with($value, '#') || filter_var($value, FILTER_VALIDATE_URL)))
                    || ($element->tagName === 'a' && $name === 'target' && in_array($value, ['_blank', '_self'], true))
                    || ($element->tagName === 'img' && $name === 'src' && preg_match('#^data:image/(png|jpe?g|gif|webp);base64,#i', $value))
                    || ($element->tagName === 'img' && in_array($name, ['alt', 'width', 'height'], true))
                    || (in_array($element->tagName, ['table', 'td', 'th', 'col'], true) && in_array($name, ['width', 'height', 'border', 'cellpadding', 'cellspacing', 'colspan', 'rowspan'], true));
                if (! $keep) $element->removeAttribute($name);
            }
        }
        $body = $document->getElementsByTagName('body')->item(0);
        $result = '';
        foreach ($body?->childNodes ?? [] as $child) $result .= $document->saveHTML($child);
        return $result;
    }
}
