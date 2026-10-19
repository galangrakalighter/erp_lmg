<?php

namespace App\Services;

class MarketingSearchService
{
    public function __construct(
        private readonly DigitalMarketingDataService $digitalMarketing,
        private readonly CampaignAnalyticsService $campaignAnalytics,
    ) {}

    public function search(int $clientId, array $filters): array
    {
        $unpaged = array_merge($filters, ['page' => 1, 'per_page' => 10000]);
        $period = array_merge($unpaged, ['start_date' => $filters['start_date'], 'end_date' => $filters['end_date']]);
        $sources = [
            'market_research' => $this->digitalMarketing->marketResearch($clientId, $unpaged),
            'brand_guideline' => $this->digitalMarketing->brandGuidelines($clientId, $unpaged),
            'content_calendar' => $this->digitalMarketing->contentCalendar($clientId, $period),
            'task' => $this->digitalMarketing->tasks($clientId, $period),
            'approval' => $this->digitalMarketing->approvals($clientId, $period),
            'asset_metadata' => $this->digitalMarketing->assets($clientId, $unpaged),
            'campaign_snapshot' => $this->campaignAnalytics->campaigns($clientId, $period),
        ];
        $needle = mb_strtolower(trim((string) $filters['q']));
        $rows = collect();
        $searched = [];

        foreach ($sources as $dataset => $payload) {
            $items = collect($payload['data'] ?? []);
            $searched[$dataset] = $items->count();
            foreach ($items as $item) {
                $item = (array) $item;
                $matchedFields = collect($item)->filter(function ($value, string|int $field) use ($needle): bool {
                    if (in_array((string) $field, ['id', 'pic_id', 'division_id', 'uploaded_by_id', 'content_plan_id', 'task_id'], true)) {
                        return false;
                    }

                    return $this->contains($value, $needle);
                })->keys()->map(fn ($field) => (string) $field)->values()->all();
                if ($matchedFields !== []) {
                    $rows->push(['dataset' => $dataset, 'matched_fields' => $matchedFields, 'record' => $item]);
                }
            }
        }

        $page = (int) $filters['page'];
        $perPage = (int) $filters['per_page'];
        $total = $rows->count();

        return [
            'data' => $rows->forPage($page, $perPage)->values()->all(),
            'pagination' => ['page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => max(1, (int) ceil($total / $perPage))],
            'meta' => [
                'period' => ['start_date' => $filters['start_date'], 'end_date' => $filters['end_date'], 'timezone' => 'Asia/Jakarta'],
                'source' => 'authorized_digital_marketing_datasets',
                'freshness' => ['searched_at' => now('Asia/Jakarta')->toIso8601String()],
                'completeness' => ['status' => $total === 0 ? 'no_data' : 'complete'],
                'datasets_searched' => $searched,
                'excluded_content' => ['document_binary', 'document_location', 'credentials', 'tokens', 'passwords'],
            ],
        ];
    }

    private function contains(mixed $value, string $needle): bool
    {
        if (is_string($value) || is_numeric($value)) {
            return str_contains(mb_strtolower((string) $value), $needle);
        }
        if (! is_array($value)) {
            return false;
        }

        return collect($value)->contains(fn ($nested) => $this->contains($nested, $needle));
    }
}
