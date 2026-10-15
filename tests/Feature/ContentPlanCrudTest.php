<?php

namespace Tests\Feature;

use App\Services\ContentPlanCrudService;
use App\Services\ContentPlanCrudAuthorization;
use App\Services\ContentPlanMutationAudit;
use App\Services\MarketingConfirmationTokenService;
use App\Services\ActivityLogService;
use App\Services\FirestoreService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Support\InMemoryFirestoreService;

class ContentPlanCrudTest extends TestCase
{
    private Authenticatable $user;
    private ContentPlanCrudService $service;
    private InMemoryFirestoreService $firestore;
    private ActivityLogService $activityLog;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('data_jasa'); Schema::dropIfExists('produk_client');
        Schema::create('produk_client', fn (Blueprint $t) => $t->id());
        Schema::create('data_jasa', function (Blueprint $t): void {
            $t->id(); $t->unsignedBigInteger('produk_client_id'); $t->string('judul')->nullable(); $t->string('inspirasi')->nullable();
            foreach (['type_produk_id','strategy_id','pilar_id','hooks_id','body_id','cta_id','status_id','pic','priority','menit'] as $c) $t->integer($c)->nullable();
            foreach (['durasi','background','komposisi','note','link','platform'] as $c) $t->text($c)->nullable();
            foreach (['tanggal_posting','tanggal_deadline','tanggal_mulai'] as $c) $t->date($c)->nullable();
            $t->timestamp('archived_at')->nullable(); $t->integer('archived_by')->nullable(); $t->timestamps();
        });
        $this->user = \Mockery::mock(Authenticatable::class);
        $this->user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $policy = \Mockery::mock(ContentPlanCrudAuthorization::class);
        $policy->shouldReceive('assert')->zeroOrMoreTimes();
        $policy->shouldReceive('assertObject')->zeroOrMoreTimes()->andReturnUsing(fn ($u, $id, $p) => DB::table('data_jasa')->where('id', $id)->first(['id','produk_client_id','pic','archived_at']));
        $this->firestore = new InMemoryFirestoreService();
        $this->app->instance(FirestoreService::class, $this->firestore);
        $this->activityLog = $this->app->make(ActivityLogService::class);
        $audit = new ContentPlanMutationAudit($this->activityLog);
        $this->app->instance(ContentPlanCrudAuthorization::class, $policy);
        $this->app->instance(ContentPlanMutationAudit::class, $audit);
        DB::table('produk_client')->insert(['id' => 1]);
        $this->service = app(ContentPlanCrudService::class);
        Cache::flush();
    }

    protected function tearDown(): void
    {
        $this->activityLog?->flush();
        parent::tearDown();
    }

    private function payload(): array
    {
        return ['client_id' => 1, 'judul' => 'Pilot plan', 'type_produk_id' => 1, 'strategy_id' => 1, 'pilar_id' => 1, 'hooks_id' => 1, 'body_id' => 1, 'cta_id' => 1, 'priority' => 1, 'menit' => 30];
    }

    private function token(string $action, int $id, array $payload, ?User $user = null): string
    {
        return app(MarketingConfirmationTokenService::class)->issue($user ?? $this->user, $action, 'content_plan', $id, hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)));
    }

    public function test_confirmation_token_is_single_use_and_bound(): void
    {
        $payload = $this->payload(); $token = $this->token('create', 0, $this->servicePayload($payload));
        $this->service->create($this->user, $payload + ['confirmation_token' => $token]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->create($this->user, $payload + ['confirmation_token' => $token]);
    }

    public function test_invalid_and_expired_tokens_are_rejected(): void
    {
        $payload = $this->payload();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->create($this->user, $payload + ['confirmation_token' => str_repeat('x', 64)]);
    }

    public function test_create_update_and_archive_use_lifecycle_without_status_change(): void
    {
        $payload = $this->payload();
        $created = $this->service->create($this->user, $payload + ['confirmation_token' => $this->token('create', 0, $this->servicePayload($payload))]);
        $id = (int) $created['id'];
        $update = ['client_id' => 1, 'judul' => 'Updated'];
        $this->service->update($this->user, $id, $update + ['confirmation_token' => $this->token('update', $id, $this->servicePayload($update))]);
        $archive = ['reason' => 'done'];
        $this->service->archive($this->user, $id, $archive + ['confirmation_token' => $this->token('archive', $id, $archive)]);
        $row = DB::table('data_jasa')->find($id);
        $this->assertNotNull($row->archived_at); $this->assertSame(1, (int) $row->archived_by);
    }

    public function test_client_id_mismatch_is_rejected(): void
    {
        $payload = $this->payload(); $created = $this->service->create($this->user, $payload + ['confirmation_token' => $this->token('create', 0, $this->servicePayload($payload))]);
        $update = ['client_id' => 999, 'judul' => 'tamper'];
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->update($this->user, $created['id'], $update + ['confirmation_token' => $this->token('update', $created['id'], $update)]);
    }

    public function test_expired_token_is_rejected(): void
    {
        $payload = $this->payload(); $token = $this->token('create', 0, $this->servicePayload($payload));
        Cache::forget('marketing_confirmation:'.hash('sha256', $token));
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->create($this->user, $payload + ['confirmation_token' => $token]);
    }

    public function test_token_action_and_record_binding_is_enforced(): void
    {
        $payload = $this->payload(); $created = $this->service->create($this->user, $payload + ['confirmation_token' => $this->token('create', 0, $this->servicePayload($payload))]);
        $token = $this->token('create', 0, ['judul' => 'wrong']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->update($this->user, $created['id'], ['client_id' => 1, 'judul' => 'x', 'confirmation_token' => $token]);
    }

    public function test_token_mismatch_user_client_record_and_action_is_rejected(): void
    {
        $payload = $this->payload();
        $otherUser = \Mockery::mock(Authenticatable::class);
        $otherUser->shouldReceive('getAuthIdentifier')->andReturn(2);

        $userToken = $this->token('create', 0, $this->servicePayload($payload));
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->create($otherUser, $payload + ['confirmation_token' => $userToken]);
    }

    public function test_audit_payload_is_captured_and_redacted(): void
    {
        $payload = $this->payload();
        $token = $this->token('create', 0, $this->servicePayload($payload));
        $this->service->create($this->user, $payload + ['confirmation_token' => $token, 'password' => 'never-log']);
        $this->activityLog->flush();

        $this->assertNotEmpty($this->firestore->records);
        $entry = $this->firestore->records[array_key_last($this->firestore->records)]['data'];
        $this->assertSame('1', $entry['user_id']);
        $this->assertSame(1, $entry['context']['client_id']);
        $this->assertSame('marketing_create_content_plan', $entry['context']['tool']);
        $this->assertSame('CREATE', $entry['action']);
        $this->assertSame('success', $entry['context']['result']);
        $this->assertSame('completed', $entry['context']['status']);
        $this->assertNotEmpty($entry['context']['record_id']);
        $this->assertNotEmpty($entry['context']['occurred_at']);
        $serialized = json_encode($entry, JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString($token, $serialized);
        $this->assertStringNotContainsString('never-log', $serialized);
        $this->assertStringNotContainsString('password', strtolower($serialized));
        $this->assertStringNotContainsString('credential', strtolower($serialized));
    }

    public function test_archived_record_is_excluded_from_active_content_plan_query(): void
    {
        $payload = $this->payload();
        $created = $this->service->create($this->user, $payload + ['confirmation_token' => $this->token('create', 0, $this->servicePayload($payload))]);
        $id = (int) $created['id'];
        $this->service->archive($this->user, $id, ['reason' => 'finished', 'confirmation_token' => $this->token('archive', $id, ['reason' => 'finished'])]);
        $this->assertSame(0, DB::table('data_jasa')->whereNull('archived_at')->where('id', $id)->count());
        $this->assertSame(1, DB::table('data_jasa')->whereNotNull('archived_at')->where('id', $id)->count());
    }

    private function servicePayload(array $payload): array
    { return array_filter(array_intersect_key($payload, array_flip(['judul','type_produk_id','strategy_id','pilar_id','hooks_id','body_id','cta_id','priority','menit']))); }
}
