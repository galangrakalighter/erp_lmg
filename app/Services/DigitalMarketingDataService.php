<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DigitalMarketingDataService
{
    private const MAX_PERIOD_DAYS = 366;

    public function __construct(private readonly MarketingAccessService $access) {}

    public function assertClientAccess(mixed $user, int $clientId): void
    {
        $this->access->assertClientAccess($user, $clientId);
    }

    public function marketResearch(int $clientId, array $filters): array
    {
        $platform = $this->platform($filters);
        $definitions = [
            'four_p' => ['analize_4p', ['id', 'nama_analisis', 'type', 'platform', 'updated_at']],
            'swot' => ['analize_swot', ['id', 'strenght', 'weakness', 'oportunity', 'threat', 'platform', 'updated_at']],
            'segmentation' => ['segmentasi', ['id', 'usia', 'gender', 'negara', 'wilayah', 'gaya_hidup', 'status_sosial', 'minat', 'penggunaan', 'loyalitas', 'manfaat', 'platform', 'updated_at']],
            'target_audience' => ['target_audience', ['id', 'indikator', 'usia', 'gender', 'negara', 'wilayah', 'pekerjaan', 'gaya_hidup', 'status_sosial', 'penggunaan', 'manfaat', 'platform', 'updated_at']],
            'positioning' => ['positioning', ['id', 'indikator', 'deskripsi', 'platform', 'updated_at']],
        ];
        $rows = collect();
        foreach ($definitions as $section => [$table, $columns]) {
            $items = DB::table($table)->where('produk_client_id', $clientId)
                ->when($platform, fn (Builder $query) => $query->whereRaw('LOWER(platform) = ?', [$platform]))
                ->get($columns)->map(fn ($row) => ['section' => $section] + (array) $row);
            $rows = $rows->concat($items);
        }

        return $this->paginate($rows, $filters, 'market_research_tables', [
            'competitor_data' => ['status' => 'unavailable', 'reason' => 'Belum ada tabel atau relasi kompetitor kanonik.'],
        ]);
    }

    public function brandGuidelines(int $clientId, array $filters): array
    {
        $platform = $this->platform($filters);
        $definitions = [
            'identity' => ['brand_identify', ['id', 'identify as value', 'platform', 'updated_at']],
            'visual' => ['brand_image', ['id', 'nama_image as value', 'platform', 'updated_at']],
            'value' => ['brand_value', ['id', 'nama_value as value', 'platform', 'updated_at']],
            'tagline' => ['brand_tagline', ['id', 'nama_tagline as value', 'nama_hashtagline as hashtags', 'platform', 'updated_at']],
            'tone_of_voice' => ['gaya_komunikasi', ['id', 'gaya_bicara as value', 'gaya_bahasa as language_style', 'platform', 'updated_at']],
            'composition' => ['komposisi', ['id', 'type_komposisi as value', 'komposisi as guidance', 'platform', 'updated_at']],
            'audio' => ['audio_brand', ['id', 'nama_audio as value', 'platform', 'updated_at']],
            'moodboard' => ['moodboard', ['id', 'moodboard as value', 'platform', 'updated_at']],
            'tool' => ['alat_branding', ['id', 'nama_alat as value', 'platform', 'updated_at']],
        ];
        $rows = collect();
        foreach ($definitions as $section => [$table, $columns]) {
            $items = DB::table($table)->where('produk_client_id', $clientId)
                ->when($platform, fn (Builder $query) => $query->whereRaw('LOWER(platform) = ?', [$platform]))
                ->get($columns)->map(fn ($row) => ['section' => $section] + (array) $row);
            $rows = $rows->concat($items);
        }

        $categoryId = DB::table('produk_client')->where('id', $clientId)->value('kategori_jasa_id');
        $pillars = DB::table('pilar')->where('kategori_id', $categoryId)->where('status', 1)
            ->get(['id', 'pilar as value', 'fungsi_singkat', 'contoh', 'updated_at'])
            ->map(fn ($row) => ['section' => 'content_pillar', 'platform' => null] + (array) $row);

        return $this->paginate($rows->concat($pillars), $filters, 'branding_and_content_master');
    }

    public function contentCalendar(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $platform = $this->platform($filters);
        $source = $filters['source'] ?? null;
        $rows = DB::table('data_jasa')
            ->leftJoin('type_produk', 'type_produk.id', '=', 'data_jasa.type_produk_id')
            ->leftJoin('strategy', 'strategy.id', '=', 'data_jasa.strategy_id')
            ->leftJoin('pilar', 'pilar.id', '=', 'data_jasa.pilar_id')
            ->leftJoin('hooks', 'hooks.id', '=', 'data_jasa.hooks_id')
            ->leftJoin('jenis_body', 'jenis_body.id', '=', 'data_jasa.body_id')
            ->leftJoin('jenis_cta', 'jenis_cta.id', '=', 'data_jasa.cta_id')
            ->leftJoin('status', 'status.id', '=', 'data_jasa.status_id')
            ->leftJoin('users', 'users.id', '=', 'data_jasa.pic')
            ->leftJoin('platform_mirror_copies as mirror_copy', function ($join): void {
                $join->on('mirror_copy.client_id', '=', 'data_jasa.produk_client_id')
                    ->where('mirror_copy.table_name', '=', 'data_jasa')
                    ->on('mirror_copy.target_id', '=', 'data_jasa.id');
            })
            ->where('data_jasa.produk_client_id', $clientId)->whereNull('mirror_copy.id')
            ->whereBetween('data_jasa.tanggal_posting', [$start->toDateString(), $end->toDateString()])
            ->when($platform, fn (Builder $query) => $query->whereRaw('LOWER(data_jasa.platform) = ?', [$platform]))
            ->when($source, fn (Builder $query) => $query->whereRaw('LOWER(data_jasa.content_source) = ?', [$source]))
            ->get([
                'data_jasa.id', 'data_jasa.judul as title', 'data_jasa.inspirasi as inspiration',
                'data_jasa.note as copy_notes', 'data_jasa.platform', 'data_jasa.content_source as source',
                'data_jasa.tanggal_posting as publish_date', 'data_jasa.jam_posting as publish_time',
                'data_jasa.tanggal_mulai as start_date', 'data_jasa.tanggal_deadline as deadline',
                'type_produk.type as content_type', 'strategy.strategy', 'pilar.pilar as content_pillar',
                'hooks.hooks as hook', 'jenis_body.nama_body as body_format', 'jenis_cta.nama_cta as cta',
                'status.nama_status as status', 'users.id as pic_id', 'users.name as pic_name', 'data_jasa.updated_at',
            ]);
        $hashtags = DB::table('brand_tagline')->where('produk_client_id', $clientId)
            ->get(['platform', 'nama_hashtagline'])->groupBy(fn ($row) => strtolower((string) $row->platform));
        $rows = $rows->map(function ($row) use ($hashtags): array {
            $item = (array) $row;
            $item['platform'] = filled($item['platform']) ? strtolower((string) $item['platform']) : null;
            $item['source'] = filled($item['source']) ? strtolower((string) $item['source']) : null;
            $item['hashtags'] = $item['platform'] ? $hashtags->get($item['platform'], collect())->pluck('nama_hashtagline')->filter()->values()->all() : [];
            $item['caption'] = null;

            return $item;
        });

        return $this->paginate($rows, $filters, 'data_jasa', [
            'period' => $this->periodMeta($start, $end),
            'caption_availability' => ['status' => 'unavailable', 'reason' => 'Skema content plan belum memiliki field caption khusus; copy_notes tidak diperlakukan sebagai caption.'],
        ]);
    }

    public function tasks(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $rows = DB::table('task')->leftJoin('users', 'users.id', '=', 'task.pic')
            ->leftJoin('divisions', 'divisions.id', '=', 'task.divisi')
            ->where('task.project', $clientId)
            ->where(function (Builder $query) use ($start, $end): void {
                $query->whereBetween('task.tanggal_mulai', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('task.tanggal_deadline', [$start->toDateString(), $end->toDateString()]);
            })
            ->when(isset($filters['pic_id']), fn (Builder $query) => $query->where('task.pic', (int) $filters['pic_id']))
            ->when(isset($filters['status']), fn (Builder $query) => $query->where('task.status', (int) $filters['status']))
            ->get([
                'task.id', 'task.nama_task as task', 'task.priority', 'task.menit as estimated_minutes',
                'task.tanggal_mulai as start_date', 'task.tanggal_deadline as deadline', 'task.jam_mulai as start_time',
                'task.status', 'task.id_manajement_konten as content_plan_id', 'task.id_kpi as kpi_id',
                'users.id as pic_id', 'users.name as pic_name', 'divisions.id as division_id',
                'divisions.name as division_name', 'task.updated_at',
            ]);

        return $this->paginate($rows, $filters, 'task', ['period' => $this->periodMeta($start, $end)]);
    }

    public function approvals(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $rows = DB::table('task')->leftJoin('users', 'users.id', '=', 'task.pic')
            ->where('task.project', $clientId)
            ->where(function (Builder $query) use ($start, $end): void {
                $query->whereBetween('task.tanggal_approve', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('task.tanggal_revisi', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('task.tanggal_deadline', [$start->toDateString(), $end->toDateString()]);
            })->get([
                'task.id as task_id', 'task.nama_task as task', 'task.status',
                'task.tanggal_approve as approved_at', 'task.tanggal_revisi as revised_at',
                'task.tanggal_deadline as deadline', 'task.id_manajement_konten as content_plan_id',
                'users.id as pic_id', 'users.name as pic_name', 'task.updated_at',
            ]);

        return $this->paginate($rows, $filters, 'task', [
            'period' => $this->periodMeta($start, $end),
            'history_availability' => ['status' => 'partial', 'reason' => 'Hanya tanggal approval/revisi terakhir yang tersedia; event history dan approver belum tersimpan secara kanonik.'],
        ]);
    }

    public function assets(int $clientId, array $filters): array
    {
        $rows = DB::table('file_pdf')->leftJoin('divisions', 'divisions.id', '=', 'file_pdf.division_id')
            ->leftJoin('users', 'users.id', '=', 'file_pdf.uploaded_by')
            ->where('file_pdf.client', $clientId)
            ->when(isset($filters['division_id']), fn (Builder $query) => $query->where('file_pdf.division_id', (int) $filters['division_id']))
            ->select([
                'file_pdf.id', 'file_pdf.nama_file as name', 'file_pdf.division_id', 'divisions.name as division_name',
                'file_pdf.is_generated_document', 'file_pdf.is_system_template', 'file_pdf.is_pinned',
                'file_pdf.pin_order', 'users.id as uploaded_by_id', 'users.name as uploaded_by_name',
                'file_pdf.created_at', 'file_pdf.updated_at',
            ])->selectSub(function ($query): void {
                $query->from('file_pdf_versions')->selectRaw('COUNT(*)')->whereColumn('file_pdf_versions.file_pdf_id', 'file_pdf.id');
            }, 'version_count')->get();

        return $this->paginate($rows, $filters, 'file_pdf_metadata', [
            'excluded_fields' => ['location', 'editor_content', 'binary_content', 'uploader_email'],
        ]);
    }

    private function paginate(Collection $rows, array $filters, string $source, array $extraMeta = []): array
    {
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 25);
        $total = $rows->count();
        $latest = $rows->max('updated_at');

        return [
            'data' => $rows->forPage($page, $perPage)->values()->all(),
            'pagination' => ['page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => max(1, (int) ceil($total / $perPage))],
            'meta' => [
                'source' => $source,
                'freshness' => ['latest_updated_at' => $latest ? (string) $latest : null],
                'completeness' => ['status' => $total === 0 ? 'no_data' : 'complete'],
            ] + $extraMeta,
        ];
    }

    private function period(array $filters): array
    {
        $start = CarbonImmutable::parse($filters['start_date'], 'Asia/Jakarta')->startOfDay();
        $end = CarbonImmutable::parse($filters['end_date'], 'Asia/Jakarta')->endOfDay();
        abort_if($start->diffInDays($end) + 1 > self::MAX_PERIOD_DAYS, 422, 'Rentang tanggal maksimum adalah '.self::MAX_PERIOD_DAYS.' hari.');

        return [$start, $end];
    }

    private function periodMeta(CarbonImmutable $start, CarbonImmutable $end): array
    {
        return ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString(), 'max_days' => self::MAX_PERIOD_DAYS, 'timezone' => 'Asia/Jakarta'];
    }

    private function platform(array $filters): ?string
    {
        return filled($filters['platform'] ?? null) ? strtolower((string) $filters['platform']) : null;
    }
}
