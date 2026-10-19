<?php

namespace Tests\Unit;

use App\Http\Controllers\CRMController;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CrmHumanRefreshWebhookTest extends TestCase
{
    public function test_it_waits_for_and_returns_the_webhook_summary(): void
    {
        config([
            'services.crm_human_refresh_webhook.url' =>
                'https://n8n.example.test/webhook/get-human-chat',
            'services.crm_human_refresh_webhook.timeout' => 600,
        ]);

        Http::fake([
            'https://n8n.example.test/webhook/get-human-chat' =>
                Http::response([
                    'success' => true,
                    'message' => 'Sinkronisasi selesai.',
                    'processed' => 15,
                    'created' => 3,
                    'updated' => 10,
                    'skipped' => 2,
                    'failed' => 0,
                ]),
        ]);

        $response = (new CRMController())->refreshHumanData();
        $data = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($data['success']);
        $this->assertSame(15, $data['summary']['processed']);
        $this->assertSame(10, $data['summary']['updated']);

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && $request->url() ===
                    'https://n8n.example.test/webhook/get-human-chat'
                && array_key_exists('triggered_at', $request->data());
        });
    }

    public function test_it_reports_a_rejected_human_refresh_webhook(): void
    {
        config([
            'services.crm_human_refresh_webhook.url' =>
                'https://n8n.example.test/webhook/get-human-chat',
        ]);

        Http::fake([
            'https://n8n.example.test/webhook/get-human-chat' =>
                Http::response(['message' => 'failed'], 500),
        ]);

        $response = (new CRMController())->refreshHumanData();

        $this->assertSame(502, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['success']);
    }

    public function test_it_reports_a_workflow_failure_returned_with_http_200(): void
    {
        config([
            'services.crm_human_refresh_webhook.url' =>
                'https://n8n.example.test/webhook/get-human-chat',
        ]);

        Http::fake([
            'https://n8n.example.test/webhook/get-human-chat' =>
                Http::response([
                    'success' => false,
                    'message' => 'Database gagal diperbarui.',
                ]),
        ]);

        $response = (new CRMController())->refreshHumanData();

        $this->assertSame(502, $response->getStatusCode());
        $this->assertSame(
            'Database gagal diperbarui.',
            $response->getData(true)['message']
        );
    }
}
