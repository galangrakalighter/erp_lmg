<?php

namespace Tests\Unit;

use App\Services\ThreadsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ThreadsServiceTest extends TestCase
{
    public function test_it_normalizes_threads_posts_and_insights_for_analysis(): void
    {
        config()->set('services.threads.graph_version', 'v1.0');
        Http::fake([
            'graph.threads.net/v1.0/123/threads_insights*' => Http::response([
                'data' => [['name' => 'followers_count', 'total_value' => ['value' => 450]]],
            ]),
            'graph.threads.net/v1.0/123/threads*' => Http::response([
                'data' => [[
                    'id' => 'post-1',
                    'text' => "Judul Threads\nIsi postingan",
                    'timestamp' => '2026-09-30T08:00:00+0000',
                    'media_type' => 'TEXT_POST',
                    'permalink' => 'https://www.threads.com/@user/post/1',
                ]],
            ]),
            'graph.threads.net/v1.0/post-1/insights*' => Http::response([
                'data' => [
                    ['name' => 'views', 'values' => [['value' => 120]]],
                    ['name' => 'likes', 'values' => [['value' => 12]]],
                    ['name' => 'replies', 'values' => [['value' => 3]]],
                    ['name' => 'reposts', 'values' => [['value' => 2]]],
                    ['name' => 'quotes', 'values' => [['value' => 1]]],
                ],
            ]),
        ]);

        $result = app(ThreadsService::class)->getAnalysis('token', '123');

        $this->assertNull($result['error']);
        $this->assertSame(450.0, $result['followers']);
        $this->assertSame('Judul Threads', $result['data'][0]['judul']);
        $this->assertSame(120.0, $result['data'][0]['views']);
        $this->assertSame(12.0, $result['data'][0]['likes']);
        $this->assertSame(3.0, $result['data'][0]['replies']);
    }
}
