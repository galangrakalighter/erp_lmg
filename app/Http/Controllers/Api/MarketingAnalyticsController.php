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

    public function performanceTrend(Request $request, int $client): JsonResponse
    {
        $filters = $this->snapshotToolFilters($request, 'trend');
        $this->analytics->assertClientAccess($request->user(), $client);
        $result = $this->analytics->performanceTrend($client, $filters);
        $this->audit($request, 'marketing_get_performance_trend', $client, $filters);

        return response()->json(['client_id' => $client] + $result);
    }

    public function topContent(Request $request, int $client): JsonResponse
    {
        $filters = $this->snapshotToolFilters($request, 'top_content');
        $this->analytics->assertClientAccess($request->user(), $client);
        $result = $this->analytics->topContent($client, $filters);
        $this->audit($request, 'marketing_get_top_content', $client, $filters);

        return response()->json(['client_id' => $client] + $result);
    }

    public function paidPerformance(Request $request, int $client): JsonResponse
    {
        $filters = $this->snapshotToolFilters($request, 'paid');
        $this->analytics->assertClientAccess($request->user(), $client);
        $result = $this->analytics->paidPerformance($client, $filters);
        $this->audit($request, 'marketing_get_paid_performance', $client, $filters);

        return response()->json(['client_id' => $client] + $result);
    }

    private function snapshotToolFilters(Request $request, string $tool): array
    {
        $request->merge([
            'platform' => filled($request->input('platform')) ? strtolower(trim((string) $request->input('platform'))) : null,
            'source' => filled($request->input('source')) ? strtolower(trim((string) $request->input('source'))) : null,
            'metric' => filled($request->input('metric')) ? strtolower(trim((string) $request->input('metric'))) : null,
            'direction' => filled($request->input('direction')) ? strtolower(trim((string) $request->input('direction'))) : null,
            'granularity' => filled($request->input('granularity')) ? strtolower(trim((string) $request->input('granularity'))) : null,
        ]);
        $rules = [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'platform' => ['nullable', 'string', Rule::in(['facebook', 'instagram', 'tiktok', 'threads', 'google', 'youtube'])],
        ];
        $defaults = [];
        if ($tool === 'trend') {
            $rules += [
                'source' => ['nullable', Rule::in(['organic', 'paid'])],
                'granularity' => ['nullable', Rule::in(['daily', 'weekly', 'monthly'])],
                'metrics' => ['nullable', 'array', 'max:14'],
                'metrics.*' => ['string', 'distinct', Rule::in(MarketingAnalyticsService::ALLOWED_METRICS)],
            ];
            $defaults = ['granularity' => 'daily'];
        } elseif ($tool === 'top_content') {
            $rules += [
                'source' => ['nullable', Rule::in(['organic', 'paid'])],
                'metric' => ['required', Rule::in(MarketingAnalyticsService::TOP_CONTENT_METRICS)],
                'direction' => ['nullable', Rule::in(['best', 'worst'])],
                'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
            ];
            $defaults = ['direction' => 'best', 'limit' => 10];
        } else {
            $rules += [
                'campaign_id' => ['nullable', 'string', 'max:100'],
                'metric' => ['nullable', Rule::in(MarketingAnalyticsService::PAID_SORT_METRICS)],
                'page' => ['sometimes', 'integer', 'min:1'],
                'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            ];
            $defaults = ['metric' => 'spend', 'page' => 1, 'per_page' => 25];
        }

        $validated = $request->validate($rules);
        foreach ($defaults as $key => $value) {
            $validated[$key] ??= $value;
        }

        return $validated;
    }

    public function contentPlan(Request $request, int $client): JsonResponse
    {
        $filters = $this->contentFilters($request, true);
        $this->analytics->assertClientAccess($request->user(), $client);
        $result = $this->analytics->contentPlan($client, $filters);
        $this->audit($request, 'marketing_get_content_plan', $client, $filters);

        return response()->json(['client_id' => $client] + $result);
    }

    public function contentMix(Request $request, int $client): JsonResponse
    {
        $filters = $this->contentFilters($request);
        $this->analytics->assertClientAccess($request->user(), $client);
        $result = $this->analytics->contentMix($client, $filters);
        $this->audit($request, 'marketing_get_content_mix', $client, $filters);

        return response()->json(['client_id' => $client] + $result);
    }

    private function contentFilters(Request $request, bool $withListingFilters = false): array
    {
        $request->merge([
            'platform' => filled($request->input('platform')) ? strtolower(trim((string) $request->input('platform'))) : null,
            'source' => filled($request->input('source')) ? strtolower(trim((string) $request->input('source'))) : null,
        ]);
        $rules = [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'platform' => ['nullable', 'string', Rule::in(['facebook', 'instagram', 'tiktok', 'threads', 'google', 'youtube'])],
            'source' => ['nullable', Rule::in(['organic', 'paid'])],
        ];
        if ($withListingFilters) {
            $rules += [
                'status' => ['nullable', 'integer', 'min:1'],
                'pic_id' => ['nullable', 'integer', 'min:1'],
                'page' => ['sometimes', 'integer', 'min:1'],
                'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            ];
        }

        return $request->validate($rules) + ($withListingFilters ? ['page' => 1, 'per_page' => 25] : []);
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
            'status', 'pic_id', 'start_date', 'end_date', 'platform', 'source', 'page', 'per_page',
            'granularity', 'metrics', 'metric', 'direction', 'limit', 'campaign_id',
        ])->all();
        if (array_key_exists('search', $filters)) {
            $safe['search_applied'] = filled($filters['search']);
        }

        return $safe;
    }
}
