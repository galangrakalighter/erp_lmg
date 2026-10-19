<?php

namespace Tests\Unit;

use App\Services\FacebookService;
use App\Services\InstagramService;
use App\Services\MetaAdsService;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaContentServiceTest extends TestCase
{
    public function test_instagram_media_uses_selected_business_account_on_meta_graph(): void
    {
        Http::fake([
            'graph.facebook.com/*/ig-123/media*' => Http::response(['data' => [[
                'id' => 'media-1', 'caption' => 'Posting pertama', 'permalink' => 'https://instagram.com/p/1',
                'timestamp' => now()->toIso8601String(), 'media_type' => 'IMAGE', 'like_count' => 4,
                'comments_count' => 2, 'insights' => ['data' => []],
            ]]]),
        ]);

        $posts = app(InstagramService::class)->getMedia(null, null, false, 'page-token', 'ig-123');

        $this->assertCount(1, $posts);
        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/ig-123/media'));
    }

    public function test_facebook_feed_uses_selected_page_id(): void
    {
        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/page-123/feed')) {
                return Http::response(['data' => []]);
            }

            return Http::response([]);
        });

        $result = app(FacebookService::class)->getPost(null, null, 'page-token', false, 'page-123');

        $this->assertSame([], $result['data']);
        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/page-123/feed'));
    }

    public function test_facebook_uses_media_view_as_impressions_and_unique_viewers_as_reach(): void
    {
        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/page-123/feed')) {
                return Http::response(['data' => [[
                    'id' => 'post-1',
                    'message' => 'Konten uji',
                    'created_time' => now()->toIso8601String(),
                    'permalink_url' => 'https://facebook.com/post-1',
                ]]]);
            }

            if (str_contains($request->url(), '/post-1/insights')) {
                return Http::response(['data' => [
                    ['name' => 'post_media_view', 'values' => [['value' => 120]]],
                    ['name' => 'post_total_media_view_unique', 'values' => [['value' => 75]]],
                ]]);
            }

            if (str_contains($request->url(), '/post-1')) {
                return Http::response([
                    'reactions' => ['summary' => ['total_count' => 4]],
                    'comments' => ['summary' => ['total_count' => 2]],
                    'shares' => ['count' => 1],
                ]);
            }

            if (str_contains($request->url(), '/page-123')) {
                return Http::response(['followers_count' => 10]);
            }

            return Http::response([]);
        });

        $result = app(FacebookService::class)->getPost(null, null, 'page-token', false, 'page-123');

        $this->assertSame(120, $result['data'][0]['impresi']);
        $this->assertSame(75, $result['data'][0]['reach']);
        $this->assertSame(7, $result['data'][0]['engagement']);
        Http::assertSent(fn (Request $request) => str_contains((string) data_get($request->data(), 'metric'), 'post_media_view'));
    }

    public function test_paid_posts_are_filtered_by_meta_publisher_platform(): void
    {
        Http::fake(['graph.facebook.com/*/act_123/insights*' => Http::response(['data' => [
            [
                'ad_id' => 'ad-facebook', 'ad_name' => 'Facebook Ad', 'campaign_name' => 'Campaign A',
                'publisher_platform' => 'facebook', 'reach' => '80', 'impressions' => '120',
                'clicks' => '9', 'spend' => '15000', 'date_start' => '2026-09-01',
                'actions' => [['action_type' => 'post_engagement', 'value' => '14']],
            ],
            [
                'ad_id' => 'ad-instagram', 'ad_name' => 'Instagram Ad', 'campaign_name' => 'Campaign A',
                'publisher_platform' => 'instagram', 'reach' => '70', 'impressions' => '100',
                'clicks' => '7', 'spend' => '12000', 'date_start' => '2026-09-01',
            ],
        ]])]);

        $result = app(MetaAdsService::class)->getPaidPosts(
            'act_123',
            'ads-token',
            'Instagram',
            Carbon::parse('2026-09-01'),
            Carbon::parse('2026-09-02'),
        );

        $this->assertNull($result['error']);
        $this->assertCount(1, $result['data']);
        $this->assertSame('ad-instagram', $result['data'][0]['id']);
        $this->assertSame(100, $result['data'][0]['impressions']);
        Http::assertSent(fn (Request $request) => data_get($request->data(), 'breakdowns') === 'publisher_platform');
        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer ads-token'));
        Http::assertSent(fn (Request $request) => ! str_contains($request->url(), 'access_token='));
    }

    public function test_paid_posts_return_safe_error_when_meta_dns_times_out(): void
    {
        Http::fake(fn () => throw new ConnectionException('Resolving timed out: access_token=secret'));

        $result = app(MetaAdsService::class)->getPaidPosts('act_123', 'secret-token', 'Facebook');

        $this->assertSame([], $result['data']);
        $this->assertStringContainsString('DNS server', $result['error']);
        $this->assertStringNotContainsString('secret-token', $result['error']);
    }
}
