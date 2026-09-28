<?php

namespace Tests\Unit;

use App\Services\FacebookService;
use App\Services\InstagramService;
use Illuminate\Http\Client\Request;
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
}
