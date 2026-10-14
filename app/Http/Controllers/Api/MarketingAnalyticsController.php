<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Services\MarketingAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MarketingAnalyticsController extends Controller
{
    public function __construct(
        private readonly MarketingAnalyticsService $analytics,
        private readonly ActivityLogService $activityLog,
    ) {}

    public function clients(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'integer', Rule::in([0, 1])],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]) + ['page' => 1, 'per_page' => 25];

        $result = $this->analytics->accessibleClients($request->user(), $filters);
        $this->audit($request, 'marketing_list_clients', null, $filters);

        return response()->json([
            'data' => $result->items(),
            'pagination' => [
                'page' => $result->currentPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
                'last_page' => $result->lastPage(),
            ],
            'meta' => [
                'period' => null,
                'source' => 'produk_client_access_scope',
                'freshness' => ['generated_at' => now()->toIso8601String()],
                'completeness' => ['status' => 'complete', 'reasons' => []],
            ],
        ]);
    }

    public function dataAvailability(Request $request, int $client): JsonResponse
    {
        $filters = $this->analyticsFilters($request);
        $this->analytics->assertClientAccess($request->user(), $client);
        $result = $this->analytics->dataAvailability($client, $filters);
        $this->audit($request, 'marketing_get_data_availability', $client, $filters);

        return response()->json(['client_id' => $client] + $result);
    }

    public function performanceSummary(Request $request, int $client): JsonResponse
    {
        $filters = $this->analyticsFilters($request, true);
        $this->analytics->assertClientAccess($request->user(), $client);
        $result = $this->analytics->performanceSummary($client, $filters);
        $this->audit($request, 'marketing_get_performance_summary', $client, $filters);

        return response()->json(['client_id' => $client] + $result);
    }

    private function analyticsFilters(Request $request, bool $withSource = false): array
    {
        $request->merge([
            'platform' => filled($request->input('platform'))
                ? strtolower(trim((string) $request->input('platform')))
                : null,
            'source' => filled($request->input('source'))
                ? strtolower(trim((string) $request->input('source')))
                : null,
        ]);
        $rules = [
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
            'platform' => ['nullable', 'string', Rule::in(['facebook', 'instagram', 'tiktok', 'threads', 'google', 'youtube'])],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
        if ($withSource) {
            $rules['source'] = ['nullable', 'in:organic,paid'];
        }

        return $request->validate($rules) + ['page' => 1, 'per_page' => 25];
    }

    private function audit(Request $request, string $tool, ?int $clientId, array $filters): void
    {
        $this->activityLog->record([
            'action' => 'READ',
            'module' => 'marketing_mcp',
            'description' => 'Menjalankan tool '.$tool,
            'target_id' => $clientId,
            'target_type' => 'marketing_analytics',
            'context' => ['tool' => $tool, 'client_id' => $clientId, 'filters' => $this->auditFilters($filters)],
        ], $request->user());
    }

    private function auditFilters(array $filters): array
    {
        $safe = collect($filters)->only([
            'status', 'start_date', 'end_date', 'platform', 'source', 'page', 'per_page',
        ])->all();
        if (array_key_exists('search', $filters)) {
            $safe['search_applied'] = filled($filters['search']);
        }

        return $safe;
    }
}
