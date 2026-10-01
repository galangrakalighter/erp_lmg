<?php

namespace Tests\Unit;

use App\Http\Controllers\CRMController;
use App\Models\CRM_HUMAN;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class CrmPurchaseWebhookTest extends TestCase
{
    public function test_it_sends_a_paid_purchase_for_a_human_lead_with_price(): void
    {
        config([
            'services.crm_purchase_webhook.url' => 'https://n8n.example.test/webhook/purchase',
            'services.crm_purchase_webhook.secret' => 'secret-value',
            'services.crm_purchase_webhook.timeout' => 10,
        ]);

        Http::fake([
            'https://n8n.example.test/webhook/purchase' => Http::response([
                'events_received' => 1,
                'fbtrace_id' => 'trace-id',
            ]),
        ]);

        $lead = new CRM_HUMAN([
            'id_proteksi' => 'PROT-123',
            'phone' => '08123456789',
            'status' => 'on progress',
            'price' => 1500000,
        ]);
        $lead->setAttribute('id', 42);

        $method = new ReflectionMethod(
            CRMController::class,
            'sendHumanPurchaseWebhook'
        );
        $method->invoke(new CRMController(), $lead);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://n8n.example.test/webhook/purchase'
                && $request->hasHeader('X-Webhook-Secret', 'secret-value')
                && $request['invoice_id'] === 'CRM-HUMAN-42'
                && $request['id_proteksi'] === 'PROT-123'
                && $request['phone'] === '08123456789'
                && (float) $request['purchase_price'] === 1500000.0
                && $request['currency'] === 'IDR'
                && $request['status'] === 'paid';
        });
    }

    public function test_it_does_not_send_when_the_price_is_zero(): void
    {
        config([
            'services.crm_purchase_webhook.url' => 'https://n8n.example.test/webhook/purchase',
        ]);

        Http::fake();

        $lead = new CRM_HUMAN([
            'id_proteksi' => 'PROT-123',
            'phone' => '08123456789',
            'status' => 'deals',
            'price' => 0,
        ]);
        $lead->setAttribute('id', 42);

        $method = new ReflectionMethod(
            CRMController::class,
            'sendHumanPurchaseWebhook'
        );
        $method->invoke(new CRMController(), $lead);

        Http::assertNothingSent();
    }

    public function test_price_change_triggers_only_for_a_new_positive_value(): void
    {
        $lead = new CRM_HUMAN([
            'price' => 1500000,
        ]);

        $method = new ReflectionMethod(
            CRMController::class,
            'humanPurchasePriceChanged'
        );
        $controller = new CRMController();

        $this->assertTrue(
            $method->invoke($controller, $lead, null)
        );

        $this->assertFalse(
            $method->invoke($controller, $lead, 1500000)
        );
    }
}
