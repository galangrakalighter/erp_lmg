<?php

namespace Tests\Unit;

use App\Services\FacebookService;
use App\Services\InstagramService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaAudienceBreakdownTest extends TestCase
{
    public function test_it_normalizes_instagram_demographic_breakdowns(): void
    {
        Http::fake(fn (Request $request) => Http::response([
            'data' => [[
                'total_value' => ['breakdowns' => [[
                    'results' => [['dimension_values' => [$request['breakdown']], 'value' => 12]],
                ]]],
            ]],
        ]));

        $result = app(InstagramService::class)->getAudienceBreakdowns('token', 'ig-1');

        $this->assertSame(12.0, $result['gender']['gender']);
        $this->assertSame(12.0, $result['age']['age']);
        $this->assertSame(12.0, $result['country']['country']);
        $this->assertSame(12.0, $result['city']['city']);
    }

    public function test_it_splits_facebook_gender_and_age_and_aggregates_reactions(): void
    {
        Http::fake(function (Request $request) {
            return match ($request['metric']) {
                'page_fans_gender_age' => Http::response(['data' => [['values' => [['value' => ['F.25-34' => 7, 'M.25-34' => 5]]]]]]),
                'page_fans_country' => Http::response(['data' => [['values' => [['value' => ['ID' => 12]]]]]]),
                'page_fans_city' => Http::response(['data' => [['values' => [['value' => ['Jakarta' => 9]]]]]]),
                default => Http::response(['data' => [['values' => [['value' => ['like' => 8, 'love' => 4]]]]]]),
            };
        });

        $result = app(FacebookService::class)->getAudienceBreakdowns('token', 'page-1');

        $this->assertSame(7.0, $result['gender']['F']);
        $this->assertSame(12.0, $result['age']['25-34']);
        $this->assertSame(12.0, $result['country']['ID']);
        $this->assertSame(8.0, $result['reaction_type']['like']);
    }
}
