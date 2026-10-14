<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Mockery\MockInterface;
use Tests\TestCase;

class MarketingAnalyticsApiTest extends TestCase
{
    private User $user;
    private int $allowedClient;
    private int $deniedClient;
    private MockInterface $auditLog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createContractSchema();
        $this->auditLog = $this->mock(ActivityLogService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('record')->zeroOrMoreTimes();
            $mock->shouldReceive('recordModelEvent')->zeroOrMoreTimes();
        });

        $this->user = User::query()->create([
            'name' => 'Marketing User',
            'email' => 'marketing@example.test',
            'password' => Hash::make('password'),
            'isAdmin' => false,
            'is_locked' => false,
        ]);
        $divisionId = DB::table('divisions')->insertGetId(['name' => 'Digital Marketing', 'created_at' => now(), 'updated_at' => now()]);
        $roleId = DB::table('roles')->insertGetId(['division_id' => $divisionId, 'role_name' => 'Digital Marketing', 'level' => 3, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('user_division')->insert(['user_id' => $this->user->id, 'division_id' => $divisionId, 'role_id' => $roleId, 'created_at' => now(), 'updated_at' => now()]);

        $categoryId = DB::table('kategori_jasa')->insertGetId(['category' => 'External', 'created_at' => now(), 'updated_at' => now()]);
        $this->allowedClient = DB::table('produk_client')->insertGetId(['kategori_jasa_id' => $categoryId, 'nama' => 'Allowed Client', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $this->deniedClient = DB::table('produk_client')->insertGetId(['kategori_jasa_id' => $categoryId, 'nama' => 'Denied Client', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        foreach ([$this->allowedClient, $this->deniedClient] as $clientId) {
            DB::table('client_division')->insert(['client_id' => $clientId, 'division_id' => $divisionId, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        }
        DB::table('user_client')->insert(['user_id' => $this->user->id, 'client_id' => $this->allowedClient, 'assigned_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_routes_require_a_personal_sanctum_token(): void
    {
        $this->getJson('/api/marketing-mcp/clients')->assertUnauthorized();
        $this->getJson('/api/marketing-mcp/clients/1/performance-trend?start_date=2026-10-01&end_date=2026-10-14')->assertUnauthorized();
        $this->getJson('/api/marketing-mcp/clients/1/top-content?start_date=2026-10-01&end_date=2026-10-14&metric=reach')->assertUnauthorized();
        $this->getJson('/api/marketing-mcp/clients/1/paid-performance?start_date=2026-10-01&end_date=2026-10-14')->assertUnauthorized();

        $this->actingAs($this->user)
            ->getJson('/api/marketing-mcp/clients')
            ->assertForbidden();
    }

    public function test_token_requires_marketing_read_ability(): void
    {
        $token = $this->user->createToken('wrong-scope', ['unrelated:read'])->plainTextToken;

        $this->withToken($token)->getJson('/api/marketing-mcp/clients')->assertForbidden();
    }

    public function test_client_list_is_paginated_and_excludes_unassigned_clients(): void
    {
        $response = $this->withMarketingToken()->getJson('/api/marketing-mcp/clients?per_page=1');

        $response->assertOk()
            ->assertJsonPath('pagination.per_page', 1)
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.id', $this->allowedClient)
            ->assertJsonMissing(['id' => $this->deniedClient]);
    }

    public function test_client_scoped_tools_reject_unassigned_client(): void
    {
        $this->withMarketingToken()
            ->getJson("/api/marketing-mcp/clients/{$this->deniedClient}/data-availability")
            ->assertForbidden();

        $this->withMarketingToken()
            ->getJson("/api/marketing-mcp/clients/{$this->deniedClient}/performance-summary")
            ->assertForbidden();
    }

    public function test_period_is_limited_to_366_days(): void
    {
        $this->withMarketingToken()
            ->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/performance-summary?start_date=2024-01-01&end_date=2025-01-01")
            ->assertUnprocessable();
    }

    public function test_performance_summary_returns_only_stored_metrics_without_secret_fields(): void
    {
        DB::table('analysis_snapshots')->insert([
            ['client_id' => $this->allowedClient, 'platform' => 'instagram', 'source' => 'organic', 'snapshot_date' => '2026-10-01', 'metrics' => json_encode(['reach' => 100, 'engagement' => 10]), 'captured_at' => '2026-10-01 12:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['client_id' => $this->allowedClient, 'platform' => 'instagram', 'source' => 'organic', 'snapshot_date' => '2026-10-10', 'metrics' => json_encode(['reach' => 150, 'engagement' => 18]), 'captured_at' => '2026-10-10 12:00:00', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->withMarketingToken()->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/performance-summary?start_date=2026-10-01&end_date=2026-10-14");

        $response->assertOk()
            ->assertJsonPath('data.0.metrics.reach', 150)
            ->assertJsonPath('data.0.change_since_first_snapshot.reach', 50)
            ->assertJsonMissingPath('data.0.access_token')
            ->assertJsonMissingPath('data.0.items');
    }

    public function test_data_availability_reports_content_and_snapshot_sources(): void
    {
        DB::table('data_jasa')->insert([
            'produk_client_id' => $this->allowedClient,
            'platform' => 'Instagram',
            'content_source' => 'organic',
            'tanggal_posting' => '2026-10-03',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('analysis_snapshots')->insert([
            'client_id' => $this->allowedClient,
            'platform' => 'Instagram',
            'source' => 'organic',
            'snapshot_date' => '2026-10-04',
            'metrics' => json_encode(['reach' => 100]),
            'captured_at' => '2026-10-04 12:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withMarketingToken()
            ->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/data-availability?start_date=2026-10-01&end_date=2026-10-14")
            ->assertOk()
            ->assertJsonFragment(['dataset' => 'content_plan', 'platform' => 'instagram', 'source' => 'organic'])
            ->assertJsonFragment(['dataset' => 'performance_snapshot', 'platform' => 'instagram', 'source' => 'organic']);
    }

    public function test_mcp_api_exposes_get_routes_only(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'api/marketing-mcp/'));

        $this->assertCount(8, $routes);
        foreach ($routes as $route) {
            $this->assertSame(['GET', 'HEAD'], $route->methods());
        }
    }

    public function test_successful_tool_call_is_audited(): void
    {
        $this->withMarketingToken()->getJson('/api/marketing-mcp/clients')->assertOk();

        $this->auditLog->shouldHaveReceived('record')->withArgs(
            fn (array $activity, User $user) => $activity['action'] === 'READ'
                && $activity['module'] === 'marketing_mcp'
                && data_get($activity, 'context.tool') === 'marketing_list_clients'
                && $user->is($this->user)
        )->once();
    }

    public function test_locked_user_cannot_use_an_existing_marketing_token(): void
    {
        $token = $this->user->createToken('before-lock', ['marketing:read'])->plainTextToken;
        $this->user->update(['is_locked' => true]);

        $this->withToken($token)->getJson('/api/marketing-mcp/clients')->assertForbidden();
    }

    public function test_empty_results_keep_a_consistent_metadata_envelope(): void
    {
        $response = $this->withMarketingToken()->getJson(
            "/api/marketing-mcp/clients/{$this->allowedClient}/performance-summary?start_date=2026-10-01&end_date=2026-10-14"
        );

        $response->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('pagination.total', 0)
            ->assertJsonPath('meta.period.start_date', '2026-10-01')
            ->assertJsonPath('meta.period.end_date', '2026-10-14')
            ->assertJsonPath('meta.source', 'analysis_snapshots')
            ->assertJsonPath('meta.freshness.latest_captured_at', null)
            ->assertJsonPath('meta.completeness.status', 'no_data');
    }

    public function test_invalid_filters_are_rejected_and_search_is_parameter_bound(): void
    {
        $token = $this->user->createToken('validation-test', ['marketing:read'])->plainTextToken;

        $this->withToken($token)->getJson('/api/marketing-mcp/clients?status=2')->assertUnprocessable();
        $this->withToken($token)->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/performance-summary?platform=invalid")
            ->assertUnprocessable();
        $this->withToken($token)->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/performance-summary?source=unknown")
            ->assertUnprocessable();
        $this->withToken($token)->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/performance-summary?start_date=not-a-date")
            ->assertUnprocessable();
        $this->withToken($token)->getJson('/api/marketing-mcp/clients?page=0&per_page=101')->assertUnprocessable();
        $this->withToken($token)->getJson("/api/marketing-mcp/clients?search=' OR 1=1 --")
            ->assertOk()
            ->assertJsonPath('pagination.total', 0);
    }

    public function test_malformed_metrics_are_omitted_and_marked_partial_instead_of_becoming_zero(): void
    {
        DB::table('analysis_snapshots')->insert([
            ['client_id' => $this->allowedClient, 'platform' => 'instagram', 'source' => 'organic', 'snapshot_date' => '2026-10-01', 'metrics' => json_encode(['reach' => 10, 'likes' => 2]), 'captured_at' => '2026-10-01 12:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['client_id' => $this->allowedClient, 'platform' => 'instagram', 'source' => 'organic', 'snapshot_date' => '2026-10-10', 'metrics' => json_encode(['reach' => 'invalid', 'likes' => 5, 'access_token' => 12345, 'nested' => ['value' => 4]]), 'captured_at' => '2026-10-10 12:00:00', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->withMarketingToken()->getJson(
            "/api/marketing-mcp/clients/{$this->allowedClient}/performance-summary?start_date=2026-10-01&end_date=2026-10-14"
        );

        $response->assertOk()
            ->assertJsonMissingPath('data.0.metrics.reach')
            ->assertJsonMissingPath('data.0.metrics.access_token')
            ->assertJsonMissingPath('data.0.data_quality.ignored_metric_keys')
            ->assertJsonPath('data.0.metrics.likes', 5)
            ->assertJsonPath('data.0.change_since_first_snapshot.reach', null)
            ->assertJsonPath('data.0.change_since_first_snapshot.likes', 3)
            ->assertJsonPath('data.0.data_quality.status', 'partial')
            ->assertJsonPath('data.0.data_quality.ignored_metric_count', 2)
            ->assertJsonPath('meta.completeness.status', 'partial');
    }

    public function test_platform_mirror_records_are_identified_without_cross_platform_total(): void
    {
        $sourceId = DB::table('data_jasa')->insertGetId([
            'produk_client_id' => $this->allowedClient, 'platform' => 'Instagram', 'content_source' => 'organic',
            'tanggal_posting' => '2026-10-03', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $targetId = DB::table('data_jasa')->insertGetId([
            'produk_client_id' => $this->allowedClient, 'platform' => 'Facebook', 'content_source' => 'organic',
            'tanggal_posting' => '2026-10-03', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('platform_mirror_copies')->insert([
            'client_id' => $this->allowedClient, 'table_name' => 'data_jasa', 'source_id' => $sourceId,
            'source_platform' => 'Instagram', 'target_id' => $targetId, 'target_platform' => 'Facebook',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->withMarketingToken()->getJson(
            "/api/marketing-mcp/clients/{$this->allowedClient}/data-availability?start_date=2026-10-01&end_date=2026-10-14"
        );

        $response->assertOk()
            ->assertJsonFragment(['platform' => 'instagram', 'record_count' => 1, 'direct_record_count' => 1, 'mirrored_record_count' => 0])
            ->assertJsonFragment(['platform' => 'facebook', 'record_count' => 1, 'direct_record_count' => 0, 'mirrored_record_count' => 1])
            ->assertJsonMissingPath('meta.total_record_count');
    }

    public function test_get_operations_do_not_mutate_marketing_domain_tables(): void
    {
        DB::table('analysis_snapshots')->insert([
            'client_id' => $this->allowedClient, 'platform' => 'instagram', 'source' => 'organic',
            'snapshot_date' => '2026-10-10', 'metrics' => json_encode(['reach' => 10]),
            'captured_at' => '2026-10-10 12:00:00', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $before = [
            'clients' => DB::table('produk_client')->get()->map(fn ($row) => (array) $row)->all(),
            'snapshots' => DB::table('analysis_snapshots')->get()->map(fn ($row) => (array) $row)->all(),
            'plans' => DB::table('data_jasa')->get()->map(fn ($row) => (array) $row)->all(),
        ];

        $token = $this->user->createToken('read-only-test', ['marketing:read'])->plainTextToken;
        $this->withToken($token)->getJson('/api/marketing-mcp/clients')->assertOk();
        $this->withToken($token)->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/data-availability")->assertOk();
        $this->withToken($token)->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/performance-summary")->assertOk();
        $this->withToken($token)->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/performance-trend?start_date=2026-10-01&end_date=2026-10-14")->assertOk();
        $this->withToken($token)->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/top-content?start_date=2026-10-01&end_date=2026-10-14&metric=reach")->assertOk();
        $this->withToken($token)->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/paid-performance?start_date=2026-10-01&end_date=2026-10-14")->assertOk();

        $this->assertSame($before['clients'], DB::table('produk_client')->get()->map(fn ($row) => (array) $row)->all());
        $this->assertSame($before['snapshots'], DB::table('analysis_snapshots')->get()->map(fn ($row) => (array) $row)->all());
        $this->assertSame($before['plans'], DB::table('data_jasa')->get()->map(fn ($row) => (array) $row)->all());
    }

    public function test_audit_log_does_not_store_raw_search_input(): void
    {
        $this->withMarketingToken()->getJson('/api/marketing-mcp/clients?search=secret-value')->assertOk();

        $this->auditLog->shouldHaveReceived('record')->withArgs(function (array $activity): bool {
            $filters = data_get($activity, 'context.filters', []);
            return data_get($activity, 'context.tool') === 'marketing_list_clients'
                && ($filters['search_applied'] ?? false) === true
                && ! array_key_exists('search', $filters)
                && ! str_contains(json_encode($activity), 'secret-value');
        })->once();
    }

    public function test_content_plan_is_authorized_paginated_filtered_and_excludes_mirror_targets(): void
    {
        $refs = $this->contentReferences();
        $sourceId = $this->insertContentPlan($this->allowedClient, $refs, ['platform' => 'Instagram', 'content_source' => 'organic']);
        $targetId = $this->insertContentPlan($this->allowedClient, $refs, ['platform' => 'Facebook', 'content_source' => 'organic']);
        DB::table('platform_mirror_copies')->insert([
            'client_id' => $this->allowedClient, 'table_name' => 'data_jasa', 'source_id' => $sourceId,
            'source_platform' => 'Instagram', 'target_id' => $targetId, 'target_platform' => 'Facebook',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $url = "/api/marketing-mcp/clients/{$this->allowedClient}/content-plan?start_date=2026-10-01&end_date=2026-10-14&source=organic&per_page=1";
        $response = $this->withMarketingToken()->getJson($url);

        $response->assertOk()->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.content_plan_id', $sourceId)
            ->assertJsonPath('data.0.platform', 'instagram')
            ->assertJsonPath('data.0.pic_name', 'Marketing User')
            ->assertJsonPath('meta.period.timezone', 'Asia/Jakarta')
            ->assertJsonPath('meta.completeness.status', 'complete')
            ->assertJsonMissingPath('data.0.email')->assertJsonMissingPath('data.0.password')
            ->assertJsonMissingPath('data.0.access_token');

        $this->withMarketingToken()->getJson(
            "/api/marketing-mcp/clients/{$this->deniedClient}/content-plan?start_date=2026-10-01&end_date=2026-10-14"
        )->assertForbidden();
        $this->withMarketingToken()->getJson(
            "/api/marketing-mcp/clients/{$this->deniedClient}/content-mix?start_date=2026-10-01&end_date=2026-10-14"
        )->assertForbidden();
    }

    public function test_content_mix_calculates_percentages_and_does_not_double_count_mirrors(): void
    {
        $refs = $this->contentReferences();
        $organicId = $this->insertContentPlan($this->allowedClient, $refs, ['platform' => 'Instagram', 'content_source' => 'organic']);
        $this->insertContentPlan($this->allowedClient, $refs, ['platform' => 'TikTok', 'content_source' => 'paid']);
        $mirrorId = $this->insertContentPlan($this->allowedClient, $refs, ['platform' => 'Facebook', 'content_source' => 'organic']);
        DB::table('platform_mirror_copies')->insert([
            'client_id' => $this->allowedClient, 'table_name' => 'data_jasa', 'source_id' => $organicId,
            'source_platform' => 'Instagram', 'target_id' => $mirrorId, 'target_platform' => 'Facebook',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->withMarketingToken()->getJson(
            "/api/marketing-mcp/clients/{$this->allowedClient}/content-mix?start_date=2026-10-01&end_date=2026-10-14"
        );
        $response->assertOk()->assertJsonPath('data.total_content_plan', 2)
            ->assertJsonPath('data.source_mix.organic.count', 1)
            ->assertJsonPath('data.source_mix.organic.percentage', 50)
            ->assertJsonPath('data.source_mix.paid.count', 1)
            ->assertJsonPath('data.source_mix.paid.percentage', 50)
            ->assertJsonCount(2, 'data.distributions.platform')
            ->assertJsonPath('meta.completeness.status', 'complete');
    }

    public function test_content_tools_require_valid_period_and_return_safe_empty_mix(): void
    {
        $base = "/api/marketing-mcp/clients/{$this->allowedClient}";
        $this->withMarketingToken()->getJson($base.'/content-plan')->assertUnprocessable();
        $this->withMarketingToken()->getJson($base.'/content-plan?start_date=2026-10-14&end_date=2026-10-01')->assertUnprocessable();
        $this->withMarketingToken()->getJson($base.'/content-plan?start_date=2026-10-01&end_date=2026-10-14&platform=invalid&per_page=101')->assertUnprocessable();
        $this->withMarketingToken()->getJson($base.'/content-mix?start_date=2026-10-01&end_date=2026-10-14&source=unknown')->assertUnprocessable();

        $this->withMarketingToken()->getJson($base.'/content-mix?start_date=2026-10-01&end_date=2026-10-14')
            ->assertOk()->assertJsonPath('data.total_content_plan', 0)
            ->assertJsonPath('data.source_mix.organic.percentage', 0)
            ->assertJsonPath('meta.completeness.status', 'no_data');

        DB::table('data_jasa')->insert([
            'produk_client_id' => $this->allowedClient, 'content_source' => 'organic',
            'tanggal_posting' => '2026-10-05', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->withMarketingToken()->getJson($base.'/content-mix?start_date=2026-10-01&end_date=2026-10-14')
            ->assertOk()->assertJsonPath('data.total_content_plan', 1)
            ->assertJsonPath('data.distributions.platform.0.value', null)
            ->assertJsonPath('meta.completeness.status', 'partial')
            ->assertJsonPath('meta.completeness.missing_record_count', 1);
    }

    public function test_content_tools_are_read_only_and_audit_only_safe_filters(): void
    {
        $refs = $this->contentReferences();
        $this->insertContentPlan($this->allowedClient, $refs);
        $before = DB::table('data_jasa')->get()->map(fn ($row) => (array) $row)->all();
        $url = "/api/marketing-mcp/clients/{$this->allowedClient}/content-plan?start_date=2026-10-01&end_date=2026-10-14&pic_id={$this->user->id}";

        $this->withMarketingToken()->getJson($url)->assertOk();
        $this->withMarketingToken()->getJson(
            "/api/marketing-mcp/clients/{$this->allowedClient}/content-mix?start_date=2026-10-01&end_date=2026-10-14"
        )->assertOk();

        $this->assertSame($before, DB::table('data_jasa')->get()->map(fn ($row) => (array) $row)->all());
        $this->auditLog->shouldHaveReceived('record')->withArgs(fn (array $activity, User $user) => data_get($activity, 'context.tool') === 'marketing_get_content_plan'
            && (int) data_get($activity, 'context.filters.pic_id') === $this->user->id
            && ! str_contains(json_encode($activity), 'marketing@example.test')
            && $user->is($this->user)
        )->once();
    }

    public function test_performance_trend_reports_gaps_partial_json_and_zero_baseline_safely(): void
    {
        $this->insertSnapshot('2026-10-01', ['reach' => 0, 'clicks' => 2]);
        $this->insertSnapshot('2026-10-03', ['reach' => 30, 'clicks' => 'broken']);

        $response = $this->withMarketingToken()->getJson(
            "/api/marketing-mcp/clients/{$this->allowedClient}/performance-trend?start_date=2026-10-01&end_date=2026-10-03&granularity=daily&metrics[]=reach&metrics[]=clicks"
        );

        $response->assertOk()
            ->assertJsonPath('data.0.missing_periods.0', '2026-10-02')
            ->assertJsonPath('data.0.changes.reach.absolute', 30)
            ->assertJsonPath('data.0.changes.reach.percentage', null)
            ->assertJsonPath('data.0.changes.reach.direction', 'up')
            ->assertJsonPath('data.0.changes.clicks.direction', 'insufficient_data')
            ->assertJsonPath('meta.completeness.status', 'partial');
    }

    public function test_new_snapshot_tools_validate_access_dates_and_metric_allowlists(): void
    {
        $denied = "/api/marketing-mcp/clients/{$this->deniedClient}";
        $this->withMarketingToken()->getJson($denied.'/performance-trend?start_date=2026-10-01&end_date=2026-10-14')->assertForbidden();
        $this->withMarketingToken()->getJson($denied.'/top-content?start_date=2026-10-01&end_date=2026-10-14&metric=reach')->assertForbidden();
        $this->withMarketingToken()->getJson($denied.'/paid-performance?start_date=2026-10-01&end_date=2026-10-14')->assertForbidden();

        $base = "/api/marketing-mcp/clients/{$this->allowedClient}";
        $this->withMarketingToken()->getJson($base.'/performance-trend?start_date=bad&end_date=2026-10-14')->assertUnprocessable();
        $this->withMarketingToken()->getJson($base.'/performance-trend?start_date=2026-10-01&end_date=2026-10-14&metrics[]=password')->assertUnprocessable();
        $this->withMarketingToken()->getJson($base.'/top-content?start_date=2026-10-01&end_date=2026-10-14&metric=raw_sql')->assertUnprocessable();
        $this->withMarketingToken()->getJson($base.'/top-content?start_date=2026-10-01&end_date=2026-10-14&metric=reach&limit=51')->assertUnprocessable();
        $this->withMarketingToken()->getJson($base.'/paid-performance?start_date=2026-10-01&end_date=2026-10-14&metric=revenue&per_page=101')->assertUnprocessable();
    }

    public function test_top_content_supports_best_worst_limit_and_safe_plan_matching(): void
    {
        $refs = $this->contentReferences();
        $planId = $this->insertContentPlan($this->allowedClient, $refs, ['judul' => 'Matched post']);
        $items = [
            ['id' => 'post-low', 'title' => 'Low post', 'date' => '2026-10-05', 'metrics' => ['reach' => 10, 'clicks' => 1]],
            ['id' => 'post-high', 'title' => 'Matched post', 'date' => '2026-10-05', 'metrics' => ['reach' => 100, 'clicks' => 5]],
        ];
        $this->insertSnapshot('2026-10-05', ['reach' => 110], $items);

        $base = "/api/marketing-mcp/clients/{$this->allowedClient}/top-content?start_date=2026-10-01&end_date=2026-10-14&metric=reach&limit=1";
        $this->withMarketingToken()->getJson($base.'&direction=best')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.value', 100)
            ->assertJsonPath('data.0.rank', 1)->assertJsonPath('data.0.content_plan_match.content_plan_id', $planId)
            ->assertJsonMissingPath('data.0._raw_id');
        $this->withMarketingToken()->getJson($base.'&direction=worst')->assertOk()
            ->assertJsonPath('data.0.value', 10)->assertJsonPath('data.0.content_plan_match.matched', false);
    }

    public function test_paid_performance_calculates_only_available_attributed_metrics(): void
    {
        $items = [[
            'id' => 'ad-1', 'title' => 'Paid A', 'campaign_id' => 'campaign-1', 'campaign' => 'Campaign 1',
            'currency' => 'IDR', 'metrics' => ['spend' => 100, 'impressions' => 1000, 'reach' => 800, 'clicks' => 20, 'engagement' => 40, 'leads' => 4, 'conversions' => 2, 'revenue' => 300],
        ]];
        $this->insertSnapshot('2026-10-05', ['spend' => 100, 'currency' => 'IDR'], $items, 'paid');

        $response = $this->withMarketingToken()->getJson(
            "/api/marketing-mcp/clients/{$this->allowedClient}/paid-performance?start_date=2026-10-01&end_date=2026-10-14&campaign_id=campaign-1"
        );
        $response->assertOk()->assertJsonPath('data.totals.currency', 'IDR')
            ->assertJsonPath('data.calculated_metrics.ctr', 2)
            ->assertJsonPath('data.calculated_metrics.cpc', 5)
            ->assertJsonPath('data.calculated_metrics.cpm', 100)
            ->assertJsonPath('data.calculated_metrics.cpl', 25)
            ->assertJsonPath('data.calculated_metrics.cpa', 50)
            ->assertJsonPath('data.calculated_metrics.roas', 3)
            ->assertJsonPath('data.identified_campaign_count', 1);
    }

    public function test_paid_performance_marks_missing_spend_attribution_and_currency_without_guessing(): void
    {
        $this->insertSnapshot('2026-10-05', ['reach' => 50], [[
            'id' => 'ad-no-spend', 'title' => 'No attribution', 'metrics' => ['impressions' => 100, 'clicks' => 5],
        ]], 'paid');
        $response = $this->withMarketingToken()->getJson(
            "/api/marketing-mcp/clients/{$this->allowedClient}/paid-performance?start_date=2026-10-01&end_date=2026-10-14"
        );
        $response->assertOk()->assertJsonPath('data.totals.spend', null)
            ->assertJsonPath('data.totals.currency', null)
            ->assertJsonMissingPath('data.calculated_metrics.cpc')
            ->assertJsonMissingPath('data.calculated_metrics.cpl')
            ->assertJsonMissingPath('data.calculated_metrics.cpa')
            ->assertJsonMissingPath('data.calculated_metrics.roas')
            ->assertJsonPath('meta.completeness.status', 'partial');
    }

    public function test_paid_performance_deduplicates_mirrored_ids_and_rejects_mixed_currency_totals(): void
    {
        $item = ['id' => 'same-ad', 'title' => 'Mirror', 'currency' => 'IDR', 'metrics' => ['spend' => 100, 'impressions' => 1000, 'clicks' => 10]];
        $this->insertSnapshot('2026-10-05', ['currency' => 'IDR'], [$item], 'paid', 'instagram');
        $item['currency'] = 'USD';
        $this->insertSnapshot('2026-10-05', ['currency' => 'USD'], [$item], 'paid', 'facebook');

        $response = $this->withMarketingToken()->getJson(
            "/api/marketing-mcp/clients/{$this->allowedClient}/paid-performance?start_date=2026-10-01&end_date=2026-10-14"
        );
        $response->assertOk()->assertJsonPath('data.identified_paid_content_count', 1)
            ->assertJsonPath('data.totals.currency', null)->assertJsonPath('data.totals.spend', null)
            ->assertJsonPath('meta.completeness.status', 'partial');
    }

    private function insertSnapshot(string $date, array|string $metrics, array|string $items = [], string $source = 'organic', string $platform = 'instagram'): void
    {
        DB::table('analysis_snapshots')->insert([
            'client_id' => $this->allowedClient, 'platform' => $platform, 'source' => $source,
            'snapshot_date' => $date, 'metrics' => is_array($metrics) ? json_encode($metrics) : $metrics,
            'items' => is_array($items) ? json_encode($items) : $items,
            'captured_at' => $date.' 12:00:00', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function contentReferences(): array
    {
        return [
            'type_produk_id' => DB::table('type_produk')->insertGetId(['type' => 'Reels']),
            'strategy_id' => DB::table('strategy')->insertGetId(['strategy' => 'Awareness']),
            'pilar_id' => DB::table('pilar')->insertGetId(['pilar' => 'Education']),
            'hooks_id' => DB::table('hooks')->insertGetId(['hooks' => 'Question']),
            'body_id' => DB::table('jenis_body')->insertGetId(['nama_body' => 'Story']),
            'cta_id' => DB::table('jenis_cta')->insertGetId(['nama_cta' => 'Learn more']),
            'status_id' => DB::table('status')->insertGetId(['nama_status' => 'Scheduled']),
        ];
    }

    private function insertContentPlan(int $clientId, array $refs, array $overrides = []): int
    {
        return DB::table('data_jasa')->insertGetId($overrides + $refs + [
            'produk_client_id' => $clientId, 'judul' => 'Safe title', 'platform' => 'Instagram',
            'content_source' => 'organic', 'tanggal_posting' => '2026-10-05', 'jam_posting' => '10:00:00',
            'pic' => $this->user->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function withMarketingToken(): static
    {
        $token = $this->user->createToken('marketing-mcp-test', ['marketing:read'])->plainTextToken;
        return $this->withToken($token);
    }

    private function createContractSchema(): void
    {
        foreach (['platform_mirror_copies', 'analysis_snapshots', 'data_jasa', 'jenis_cta', 'jenis_body', 'hooks', 'pilar', 'strategy', 'type_produk', 'status', 'user_client', 'client_division', 'user_division', 'roles', 'divisions', 'produk_client', 'kategori_jasa', 'personal_access_tokens', 'users'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('isAdmin')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('kategori_jasa', function (Blueprint $table): void {
            $table->id();
            $table->string('category');
            $table->timestamps();
        });
        Schema::create('produk_client', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kategori_jasa_id')->nullable();
            $table->string('nama');
            $table->integer('status');
            $table->json('access_token')->nullable();
            $table->timestamps();
        });
        Schema::create('divisions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('division_id')->nullable();
            $table->string('role_name')->nullable();
            $table->integer('level')->nullable();
            $table->timestamps();
        });
        Schema::create('user_division', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();
        });
        Schema::create('client_division', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('division_id');
            $table->unsignedSmallInteger('status')->default(1);
            $table->timestamps();
        });
        Schema::create('user_client', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('client_id');
            $table->timestamp('assigned_at');
            $table->timestamps();
        });
        foreach ([
            'type_produk' => 'type', 'strategy' => 'strategy', 'pilar' => 'pilar', 'hooks' => 'hooks',
            'jenis_body' => 'nama_body', 'jenis_cta' => 'nama_cta', 'status' => 'nama_status',
        ] as $tableName => $label) {
            Schema::create($tableName, function (Blueprint $table) use ($label): void {
                $table->id();
                $table->string($label);
            });
        }
        Schema::create('data_jasa', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('produk_client_id');
            $table->string('judul')->nullable();
            $table->string('platform')->nullable();
            $table->string('content_source')->default('organic');
            $table->date('tanggal_posting')->nullable();
            $table->time('jam_posting')->nullable();
            $table->unsignedBigInteger('type_produk_id')->nullable();
            $table->unsignedBigInteger('strategy_id')->nullable();
            $table->unsignedBigInteger('pilar_id')->nullable();
            $table->unsignedBigInteger('hooks_id')->nullable();
            $table->unsignedBigInteger('body_id')->nullable();
            $table->unsignedBigInteger('cta_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->unsignedBigInteger('pic')->nullable();
            $table->timestamps();
        });
        Schema::create('analysis_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('platform');
            $table->string('source');
            $table->date('snapshot_date');
            $table->json('metrics');
            $table->json('items')->nullable();
            $table->timestamp('captured_at');
            $table->timestamps();
        });
        Schema::create('platform_mirror_copies', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('table_name');
            $table->unsignedBigInteger('source_id');
            $table->string('source_platform');
            $table->unsignedBigInteger('target_id');
            $table->string('target_platform');
            $table->timestamps();
        });
    }
}
