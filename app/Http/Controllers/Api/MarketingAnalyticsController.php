<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Services\CampaignAnalyticsService;
use App\Services\DigitalMarketingDataService;
use App\Services\MarketingAnalyticsService;
use App\Services\MarketingSearchService;
use App\Services\ClientManagementReadService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MarketingAnalyticsController extends Controller
{
    public function __construct(
        private readonly MarketingAnalyticsService $analytics,
        private readonly DigitalMarketingDataService $digitalMarketing,
        private readonly CampaignAnalyticsService $campaignAnalytics,
        private readonly MarketingSearchService $marketingSearch,
        private readonly ActivityLogService $activityLog,
        private readonly ClientManagementReadService $clientManagement,
    ) {}

    public function clientProfile(Request $request, int $client): JsonResponse
    {
        $this->validateAllowedQueryKeys($request, []);
        $result = $this->clientManagement->profile($request->user(), $client);
        $this->audit($request, 'account_get_client_profile', $client, []);
        return response()->json(['data' => $result, 'pagination' => null, 'meta' => ['source' => $result['source'], 'as_of' => $result['as_of']]]);
    }

    public function clientContacts(Request $request, int $client): JsonResponse
    {
        $this->validateAllowedQueryKeys($request, []);
        $result = $this->clientManagement->contacts($request->user(), $client);
        $this->audit($request, 'account_list_client_contacts', $client, []);
        return response()->json(['data' => $result, 'pagination' => null, 'meta' => ['source' => $result['source'], 'as_of' => $result['as_of']]]);
    }

    public function clientBrief(Request $request, int $client): JsonResponse
    {
        $this->validateAllowedQueryKeys($request, []);
        $result = $this->clientManagement->brief($request->user(), $client);
        $this->audit($request, 'account_get_client_brief', $client, []);
        return response()->json(['data' => $result, 'pagination' => null, 'meta' => ['source' => $result['source'], 'as_of' => $result['as_of']]]);
    }

    public function clientNeeds(Request $request, int $client): JsonResponse
    {
        $this->validateAllowedQueryKeys($request, []);
        $result = $this->clientManagement->needsAndTargets($request->user(), $client);
        $this->audit($request, 'account_get_client_needs', $client, []);
        return response()->json(['data' => $result, 'pagination' => null, 'meta' => ['source' => $result['source'], 'as_of' => $result['as_of']]]);
    }

    public function clientProjectStatus(Request $request, int $client): JsonResponse
    {
        $this->validateAllowedQueryKeys($request, []);
        $result = $this->clientManagement->projectStatus($request->user(), $client);
        $this->audit($request, 'account_get_client_project_status', $client, []);
        return response()->json(['data' => $result, 'pagination' => null, 'meta' => ['source' => $result['source'], 'as_of' => $result['as_of']]]);
    }

    public function clientMilestones(Request $request, int $client): JsonResponse
    {
        $this->validateAllowedQueryKeys($request, []);
        $result = $this->clientManagement->milestones($request->user(), $client);
        $this->audit($request, 'account_get_client_milestones', $client, []);
        return response()->json(['data' => $result, 'pagination' => null, 'meta' => ['source' => $result['source'], 'as_of' => $result['as_of']]]);
    }

    public function clientDocumentsAndHistory(Request $request, int $client): JsonResponse
    {
        $this->validateAllowedQueryKeys($request, []);
        $result = $this->clientManagement->documentsAndHistory($request->user(), $client);
        $this->audit($request, 'account_get_client_documents_history', $client, []);
        return response()->json(['data' => $result, 'pagination' => null, 'meta' => ['source' => $result['source'], 'as_of' => $result['as_of']]]);
    }

    public function clientSummary(Request $request, int $client): JsonResponse
    {
        $this->validateAllowedQueryKeys($request, []);
        $result = $this->clientManagement->summary($request->user(), $client);
        $this->audit($request, 'account_get_client_summary', $client, []);
        return response()->json(['data' => $result, 'pagination' => null, 'meta' => ['source' => $result['source'], 'as_of' => $result['as_of']]]);
    }

    public function clientSearch(Request $request, int $client): JsonResponse
    {
        $this->validateAllowedQueryKeys($request, ['q']);
        $filters = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);
        $result = $this->clientManagement->search($request->user(), $client, $filters['q']);
        $this->audit($request, 'account_search_client_data', $client, []);
        return response()->json(['data' => $result, 'pagination' => null, 'meta' => ['source' => $result['source'], 'as_of' => $result['as_of']]]);
    }

    public function clients(Request $request): JsonResponse
    {
        $this->validateAllowedQueryKeys($request, ['search', 'status', 'page', 'per_page']);
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

    public function divisionClientSummary(Request $request): JsonResponse
    {
        $this->validateAllowedQueryKeys($request, []);
        $result = $this->analytics->divisionClientSummary($request->user());
        $this->audit($request, 'marketing_get_division_client_summary', null, []);

        return response()->json([
            'data' => $result,
            'pagination' => null,
            'meta' => [
                'source' => $result['source'],
                'freshness' => ['as_of' => $result['as_of']],
                'completeness' => ['status' => $result['scope'] === 'division' ? 'complete' : 'restricted'],
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
        $allowed = ['start_date', 'end_date', 'platform'];
        $allowed = match ($tool) {
            'trend' => [...$allowed, 'source', 'granularity', 'metrics'],
            'top_content' => [...$allowed, 'source', 'metric', 'direction', 'limit'],
            default => [...$allowed, 'campaign_id', 'metric', 'page', 'per_page'],
        };
        $this->validateAllowedQueryKeys($request, $allowed);

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

    public function marketResearch(Request $request, int $client): JsonResponse
    {
        $filters = $this->catalogFilters($request);

        return $this->digitalMarketingResponse($request, $client, 'marketing_get_market_research', $filters,
            fn () => $this->digitalMarketing->marketResearch($client, $filters));
    }

    public function brandGuidelines(Request $request, int $client): JsonResponse
    {
        $filters = $this->catalogFilters($request);

        return $this->digitalMarketingResponse($request, $client, 'marketing_get_brand_guidelines', $filters,
            fn () => $this->digitalMarketing->brandGuidelines($client, $filters));
    }

    public function contentCalendar(Request $request, int $client): JsonResponse
    {
        $filters = $this->phaseTwoPeriodFilters($request, ['platform', 'source'], [
            'platform' => ['nullable', 'string', Rule::in(['facebook', 'instagram', 'tiktok', 'threads', 'google', 'youtube'])],
            'source' => ['nullable', Rule::in(['organic', 'paid'])],
        ]);

        return $this->digitalMarketingResponse($request, $client, 'marketing_get_content_calendar', $filters,
            fn () => $this->digitalMarketing->contentCalendar($client, $filters));
    }

    public function tasks(Request $request, int $client): JsonResponse
    {
        $filters = $this->phaseTwoPeriodFilters($request, ['pic_id', 'status'], [
            'pic_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'integer', 'min:0'],
        ]);

        return $this->digitalMarketingResponse($request, $client, 'marketing_get_tasks', $filters,
            fn () => $this->digitalMarketing->tasks($client, $filters));
    }

    public function approvals(Request $request, int $client): JsonResponse
    {
        $filters = $this->phaseTwoPeriodFilters($request);

        return $this->digitalMarketingResponse($request, $client, 'marketing_get_approvals', $filters,
            fn () => $this->digitalMarketing->approvals($client, $filters));
    }

    public function assets(Request $request, int $client): JsonResponse
    {
        $this->validateAllowedQueryKeys($request, ['division_id', 'page', 'per_page']);
        $filters = $request->validate([
            'division_id' => ['nullable', 'integer', 'min:1'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]) + ['page' => 1, 'per_page' => 25];

        return $this->digitalMarketingResponse($request, $client, 'marketing_list_assets', $filters,
            fn () => $this->digitalMarketing->assets($client, $filters));
    }

    public function campaigns(Request $request, int $client): JsonResponse
    {
        $filters = $this->campaignAnalyticsFilters($request, true);

        return $this->campaignAnalyticsResponse($request, $client, 'marketing_list_campaigns', $filters,
            fn () => $this->campaignAnalytics->campaigns($client, $filters));
    }

    public function performanceMetrics(Request $request, int $client): JsonResponse
    {
        $filters = $this->campaignAnalyticsFilters($request);

        return $this->campaignAnalyticsResponse($request, $client, 'marketing_get_performance_metrics', $filters,
            fn () => $this->campaignAnalytics->performance($client, $filters));
    }

    public function periodComparison(Request $request, int $client): JsonResponse
    {
        $filters = $this->campaignAnalyticsFilters($request, false, true);

        return $this->campaignAnalyticsResponse($request, $client, 'marketing_compare_performance_periods', $filters,
            fn () => $this->campaignAnalytics->comparison($client, $filters));
    }

    public function channelAnalysis(Request $request, int $client): JsonResponse
    {
        $filters = $this->campaignAnalyticsFilters($request);

        return $this->campaignAnalyticsResponse($request, $client, 'marketing_analyze_channels', $filters,
            fn () => $this->campaignAnalytics->channelAnalysis($client, $filters));
    }

    public function search(Request $request, int $client): JsonResponse
    {
        $this->validateAllowedQueryKeys($request, ['q', 'start_date', 'end_date', 'platform', 'source', 'page', 'per_page']);
        foreach (['platform', 'source'] as $key) {
            $request->merge([$key => filled($request->input($key)) ? strtolower(trim((string) $request->input($key))) : null]);
        }
        $filters = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'platform' => ['nullable', 'string', Rule::in(['facebook', 'instagram', 'tiktok', 'threads', 'google', 'youtube'])],
            'source' => ['nullable', Rule::in(['organic', 'paid'])],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]) + ['page' => 1, 'per_page' => 25];
        $start = CarbonImmutable::parse($filters['start_date'], 'Asia/Jakarta');
        $end = CarbonImmutable::parse($filters['end_date'], 'Asia/Jakarta');
        abort_if($start->diffInDays($end) + 1 > MarketingAnalyticsService::MAX_PERIOD_DAYS, 422, 'Rentang tanggal maksimum adalah 366 hari.');

        $this->analytics->assertClientAccess($request->user(), $client);
        $result = $this->marketingSearch->search($client, $filters);
        $this->audit($request, 'marketing_search', $client, $filters);

        return response()->json(['client_id' => $client] + $result);
    }

    private function campaignAnalyticsFilters(Request $request, bool $listing = false, bool $comparison = false): array
    {
        $allowed = ['start_date', 'end_date', 'platform', 'source'];
        if ($listing) {
            $allowed = [...$allowed, 'campaign_id', 'page', 'per_page'];
        }
        if ($comparison) {
            $allowed = [...$allowed, 'compare_start_date', 'compare_end_date'];
        }
        $this->validateAllowedQueryKeys($request, $allowed);
        foreach (['platform', 'source'] as $key) {
            $request->merge([$key => filled($request->input($key)) ? strtolower(trim((string) $request->input($key))) : null]);
        }
        $rules = [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'platform' => ['nullable', 'string', Rule::in(['facebook', 'instagram', 'tiktok', 'threads', 'google', 'youtube'])],
            'source' => ['nullable', Rule::in(['organic', 'paid'])],
        ];
        if ($listing) {
            $rules += ['campaign_id' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1'], 'per_page' => ['sometimes', 'integer', 'min:1', 'max:100']];
        }
        if ($comparison) {
            $rules += [
                'compare_start_date' => ['nullable', 'date_format:Y-m-d', 'required_with:compare_end_date'],
                'compare_end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:compare_start_date', 'required_with:compare_start_date'],
            ];
        }
        $filters = $request->validate($rules) + ($listing ? ['page' => 1, 'per_page' => 25] : []);
        $start = CarbonImmutable::parse($filters['start_date'], 'Asia/Jakarta');
        $end = CarbonImmutable::parse($filters['end_date'], 'Asia/Jakarta');
        abort_if($start->diffInDays($end) + 1 > MarketingAnalyticsService::MAX_PERIOD_DAYS, 422, 'Rentang tanggal maksimum adalah 366 hari.');
        if ($comparison && ! isset($filters['compare_start_date'])) {
            $days = $start->diffInDays($end) + 1;
            $filters['compare_end_date'] = $start->subDay()->toDateString();
            $filters['compare_start_date'] = $start->subDays($days)->toDateString();
        }
        if ($comparison) {
            $compareStart = CarbonImmutable::parse($filters['compare_start_date'], 'Asia/Jakarta');
            $compareEnd = CarbonImmutable::parse($filters['compare_end_date'], 'Asia/Jakarta');
            abort_if($compareStart->diffInDays($compareEnd) + 1 > MarketingAnalyticsService::MAX_PERIOD_DAYS, 422, 'Rentang tanggal pembanding maksimum adalah 366 hari.');
        }

        return $filters;
    }

    private function campaignAnalyticsResponse(Request $request, int $client, string $tool, array $filters, \Closure $loader): JsonResponse
    {
        $this->analytics->assertClientAccess($request->user(), $client);
        $result = $loader();
        $this->audit($request, $tool, $client, $filters);

        return response()->json(['client_id' => $client] + $result);
    }

    private function catalogFilters(Request $request): array
    {
        $this->validateAllowedQueryKeys($request, ['platform', 'page', 'per_page']);
        $request->merge(['platform' => filled($request->input('platform')) ? strtolower(trim((string) $request->input('platform'))) : null]);

        return $request->validate([
            'platform' => ['nullable', 'string', Rule::in(['facebook', 'instagram', 'tiktok', 'threads', 'google', 'youtube'])],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]) + ['page' => 1, 'per_page' => 25];
    }

    private function phaseTwoPeriodFilters(Request $request, array $extraKeys = [], array $extraRules = []): array
    {
        $this->validateAllowedQueryKeys($request, ['start_date', 'end_date', 'page', 'per_page', ...$extraKeys]);
        foreach (['platform', 'source'] as $key) {
            if (in_array($key, $extraKeys, true)) {
                $request->merge([$key => filled($request->input($key)) ? strtolower(trim((string) $request->input($key))) : null]);
            }
        }

        return $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            ...$extraRules,
        ]) + ['page' => 1, 'per_page' => 25];
    }

    private function digitalMarketingResponse(Request $request, int $client, string $tool, array $filters, \Closure $loader): JsonResponse
    {
        $this->digitalMarketing->assertClientAccess($request->user(), $client);
        $result = $loader();
        $this->audit($request, $tool, $client, $filters);

        return response()->json(['client_id' => $client] + $result);
    }

    private function contentFilters(Request $request, bool $withListingFilters = false): array
    {
        $allowed = ['start_date', 'end_date', 'platform', 'source'];
        if ($withListingFilters) {
            $allowed = [...$allowed, 'status', 'pic_id', 'page', 'per_page'];
        }
        $this->validateAllowedQueryKeys($request, $allowed);

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
        $allowed = ['start_date', 'end_date', 'platform', 'page', 'per_page'];
        if ($withSource) {
            $allowed[] = 'source';
        }
        $this->validateAllowedQueryKeys($request, $allowed);

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
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'platform' => ['nullable', 'string', Rule::in(['facebook', 'instagram', 'tiktok', 'threads', 'google', 'youtube'])],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
        if ($withSource) {
            $rules['source'] = ['nullable', 'in:organic,paid'];
        }

        return $request->validate($rules) + ['page' => 1, 'per_page' => 25];
    }

    private function validateAllowedQueryKeys(Request $request, array $allowed): void
    {
        $unknown = array_values(array_diff(array_keys($request->query()), $allowed));
        if ($unknown === []) {
            return;
        }

        throw ValidationException::withMessages([
            'query' => ['Parameter tidak dikenal: '.implode(', ', $unknown).'.'],
        ]);
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
            'division_id',
            'compare_start_date', 'compare_end_date',
        ])->all();
        if (array_key_exists('search', $filters)) {
            $safe['search_applied'] = filled($filters['search']);
        }
        if (array_key_exists('q', $filters)) {
            $safe['query_applied'] = filled($filters['q']);
        }

        return $safe;
    }
}
