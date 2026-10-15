<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Read-only, allow-listed client/account projection for MCP. */
class ClientManagementReadService
{
    public function __construct(private readonly MarketingAccessService $access) {}

    public function profile(Authenticatable $user, int $clientId): array
    {
        $this->access->assertClientAccess($user, $clientId);
        $client = DB::table('produk_client as c')
            ->leftJoin('kategori_jasa as k', 'k.id', '=', 'c.kategori_jasa_id')
            ->where('c.id', $clientId)
            ->first(['c.id', 'c.nama', 'c.status', 'k.category as category']);
        abort_unless($client, 404, 'Client tidak ditemukan.');

        $divisions = DB::table('client_division as cd')->join('divisions as d', 'd.id', '=', 'cd.division_id')
            ->where('cd.client_id', $clientId)->where('cd.status', 1)->pluck('d.name')->values();
        $projects = $this->projectSummary($clientId);
        return ['client_id' => (int) $client->id, 'name' => $client->nama, 'status' => (int) $client->status,
            'category' => $client->category, 'divisions' => $divisions, 'project_summary' => $projects,
            'scope' => 'client', 'source' => 'produk_client_client_division', 'as_of' => now('Asia/Jakarta')->toIso8601String()];
    }

    public function contacts(Authenticatable $user, int $clientId): array
    {
        $this->access->assertClientAccess($user, $clientId);
        $rows = DB::table('user_client as uc')->join('users as u', 'u.id', '=', 'uc.user_id')
            ->where('uc.client_id', $clientId)->whereNull('u.deleted_at')
            ->orderBy('u.name')->get(['u.id', 'u.name', 'u.is_locked']);
        return ['client_id' => $clientId, 'contacts' => $rows->map(fn ($row) => [
            'user_id' => (int) $row->id, 'name' => $row->name, 'status' => $row->is_locked ? 'inactive' : 'active',
            // Email and phone are intentionally omitted from the baseline projection.
        ])->values(), 'scope' => 'client_assignment', 'source' => 'user_client_users', 'as_of' => now('Asia/Jakarta')->toIso8601String()];
    }

    public function brief(Authenticatable $user, int $clientId): array
    {
        $this->access->assertClientAccess($user, $clientId);
        $items = Schema::hasTable('data_jasa') ? DB::table('data_jasa')->where('produk_client_id', $clientId)
            ->orderByDesc('updated_at')->limit(100)->get(['id', 'judul', 'inspirasi', 'note', 'platform', 'tanggal_mulai', 'tanggal_deadline']) : collect();
        return ['client_id' => $clientId, 'briefs' => $items->map(fn ($item) => (array) $item)->values(),
            'scope' => 'client', 'source' => 'data_jasa', 'as_of' => now('Asia/Jakarta')->toIso8601String()];
    }

    public function needsAndTargets(Authenticatable $user, int $clientId): array
    {
        $this->access->assertClientAccess($user, $clientId);
        $audience = Schema::hasTable('target_audience') ? DB::table('target_audience')->where('produk_client_id', $clientId)
            ->get(['id', 'indikator', 'usia', 'gender', 'negara', 'wilayah', 'pekerjaan', 'gaya_hidup', 'status_sosial', 'penggunaan', 'manfaat', 'platform']) : collect();
        $projects = $this->projectSummary($clientId);
        return ['client_id' => $clientId, 'needs' => [], 'target_audience' => $audience->map(fn ($item) => (array) $item)->values(),
            'project_summary' => $projects, 'scope' => 'client', 'source' => 'target_audience_data_jasa', 'as_of' => now('Asia/Jakarta')->toIso8601String()];
    }

    public function projectStatus(Authenticatable $user, int $clientId): array
    {
        $this->access->assertClientAccess($user, $clientId);
        $tasks = Schema::hasTable('task') ? DB::table('task as t')->leftJoin('users as u', 'u.id', '=', 't.pic')
            ->where('t.project', $clientId)->orderBy('t.tanggal_deadline')->get([
                't.id', 't.nama_task', 't.status', 't.tanggal_mulai', 't.tanggal_deadline', 't.pic', 'u.name as pic_name', 't.priority'
            ]) : collect();
        $today = now('Asia/Jakarta')->toDateString();
        $completed = $tasks->where('status', 4)->count();
        $total = $tasks->count();
        $overdue = $tasks->filter(fn ($task) => filled($task->tanggal_deadline) && $task->tanggal_deadline < $today && (int) $task->status !== 4)->values();
        return [
            'client_id' => $clientId,
            'project' => ['active' => $total > 0, 'task_total' => $total, 'task_completed' => $completed,
                'progress_percent' => $total > 0 ? round(($completed / $total) * 100, 2) : null,
                'status_source' => 'task.status', 'completed_status' => 4],
            'timeline' => $tasks->map(fn ($task) => $this->taskProjection($task, $today))->values(),
            'overdue' => $overdue->map(fn ($task) => $this->taskProjection($task, $today))->values(),
            'scope' => 'client', 'source' => 'task', 'as_of' => now('Asia/Jakarta')->toIso8601String(),
        ];
    }

    public function milestones(Authenticatable $user, int $clientId): array
    {
        $this->access->assertClientAccess($user, $clientId);
        $items = collect();
        if (Schema::hasTable('kpi')) {
            $items = DB::table('kpi as k')->where('k.project', $clientId)->orderBy('k.tanggal_deadline')->get([
                'k.id', 'k.nama_kpi as name', 'k.status', 'k.point', 'k.tanggal_mulai', 'k.tanggal_deadline', 'k.tanggal_approve'
            ]);
        }
        return ['client_id' => $clientId, 'milestones' => $items->map(fn ($item) => (array) $item)->values(),
            'scope' => 'client', 'source' => 'kpi', 'as_of' => now('Asia/Jakarta')->toIso8601String()];
    }

    public function documentsAndHistory(Authenticatable $user, int $clientId): array
    {
        $this->access->assertClientAccess($user, $clientId);
        $documents = Schema::hasTable('file_pdf') ? DB::table('file_pdf as f')->leftJoin('divisions as d', 'd.id', '=', 'f.division_id')
            ->where('f.client', $clientId)->orderByDesc('f.updated_at')->get([
                'f.id', 'f.nama_file as name', 'f.division_id', 'd.name as division_name', 'f.is_pinned', 'f.is_generated_document', 'f.is_system_template', 'f.created_at', 'f.updated_at'
            ])->map(function ($file) {
                $file->versions = Schema::hasTable('file_pdf_versions') ? DB::table('file_pdf_versions')->where('file_pdf_id', $file->id)->count() : null;
                return (array) $file;
            })->values() : collect();
        $approvals = Schema::hasTable('task') ? DB::table('task')->where('project', $clientId)
            ->where(function ($q) { $q->whereNotNull('tanggal_approve')->orWhereNotNull('tanggal_revisi'); })
            ->orderByDesc('tanggal_approve')->get(['id', 'nama_task', 'status', 'tanggal_approve as approved_at', 'tanggal_revisi as revised_at'])->map(fn ($row) => (array) $row)->values() : collect();
        $contracts = ['available' => false, 'items' => [], 'reason' => 'Tidak ada sumber kontrak kanonik yang terhubung ke client pada scope MCP.'];
        if (Schema::hasTable('contracts') && Schema::hasColumn('contracts', 'client_id')) {
            $contracts['available'] = (bool) data_get($user, 'isAdmin', false);
            $contracts['items'] = $contracts['available'] ? DB::table('contracts')->where('client_id', $clientId)->get(['id', 'status', 'created_at', 'updated_at'])->map(fn ($row) => (array) $row)->values()->all() : [];
            if (! $contracts['available']) $contracts['reason'] = 'Kontrak memerlukan permission khusus.';
        }
        return ['client_id' => $clientId, 'documents' => $documents, 'approval_history' => $approvals,
            'contracts' => $contracts, 'communication_summary' => ['available' => false, 'reason' => 'Meeting notes/komunikasi belum memiliki relasi client kanonik yang aman.'],
            'scope' => 'client', 'source' => 'file_pdf_task_contracts', 'as_of' => now('Asia/Jakarta')->toIso8601String()];
    }

    public function summary(Authenticatable $user, int $clientId): array
    {
        $status = $this->projectStatus($user, $clientId);
        $docs = $this->documentsAndHistory($user, $clientId);
        return ['client_id' => $clientId, 'project' => $status['project'], 'overdue_count' => count($status['overdue']),
            'document_count' => $docs['documents']->count(), 'approval_count' => $docs['approval_history']->count(),
            'communication' => $docs['communication_summary'], 'contracts' => $docs['contracts'],
            'scope' => 'client', 'source' => 'task_file_pdf_approval', 'as_of' => now('Asia/Jakarta')->toIso8601String()];
    }

    public function search(Authenticatable $user, int $clientId, string $query): array
    {
        $this->access->assertClientAccess($user, $clientId);
        $like = '%' . addcslashes($query, '%_\\') . '%';
        $results = collect();
        if (Schema::hasTable('data_jasa')) {
            $results = $results->merge(DB::table('data_jasa')->where('produk_client_id', $clientId)->where(function ($q) use ($like) {
                $q->where('judul', 'like', $like)->orWhere('inspirasi', 'like', $like)->orWhere('note', 'like', $like);
            })->limit(50)->get(['id', 'judul as title', 'platform'])->map(fn ($r) => ['type' => 'brief', 'id' => (int) $r->id, 'title' => $r->title, 'platform' => $r->platform]));
        }
        if (Schema::hasTable('task')) {
            $results = $results->merge(DB::table('task')->where('project', $clientId)->where('nama_task', 'like', $like)->limit(50)->get(['id', 'nama_task as title', 'status'])->map(fn ($r) => ['type' => 'task', 'id' => (int) $r->id, 'title' => $r->title, 'status' => $r->status]));
        }
        if (Schema::hasTable('file_pdf')) {
            $results = $results->merge(DB::table('file_pdf')->where('client', $clientId)->where('nama_file', 'like', $like)->limit(50)->get(['id', 'nama_file as title'])->map(fn ($r) => ['type' => 'document', 'id' => (int) $r->id, 'title' => $r->title]));
        }
        return ['client_id' => $clientId, 'query' => $query, 'results' => $results->values(), 'scope' => 'client', 'source' => 'data_jasa_task_file_pdf', 'as_of' => now('Asia/Jakarta')->toIso8601String()];
    }

    private function taskProjection(object $task, string $today): array
    {
        return ['task_id' => (int) $task->id, 'name' => $task->nama_task, 'status' => $task->status,
            'start_date' => $task->tanggal_mulai, 'deadline' => $task->tanggal_deadline,
            'pic' => $task->pic ? ['user_id' => (int) $task->pic, 'name' => $task->pic_name] : null,
            'priority' => $task->priority, 'overdue' => filled($task->tanggal_deadline) && $task->tanggal_deadline < $today && (int) $task->status !== 4];
    }

    private function projectSummary(int $clientId): array
    {
        $summary = ['content_plans' => 0, 'latest_deadline' => null];
        if (Schema::hasTable('data_jasa')) {
            $summary['content_plans'] = DB::table('data_jasa')->where('produk_client_id', $clientId)->count();
            $summary['latest_deadline'] = DB::table('data_jasa')->where('produk_client_id', $clientId)->max('tanggal_deadline');
        }
        if (Schema::hasTable('task')) {
            $summary['tasks'] = DB::table('task')->where('project', $clientId)->count();
        }
        return $summary;
    }
}
