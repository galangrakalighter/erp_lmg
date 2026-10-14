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

        $this->assertCount(3, $routes);
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

    private function withMarketingToken(): static
    {
        $token = $this->user->createToken('marketing-mcp-test', ['marketing:read'])->plainTextToken;
        return $this->withToken($token);
    }

    private function createContractSchema(): void
    {
        foreach (['platform_mirror_copies', 'analysis_snapshots', 'data_jasa', 'user_client', 'client_division', 'user_division', 'roles', 'divisions', 'produk_client', 'kategori_jasa', 'personal_access_tokens', 'users'] as $table) {
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
        Schema::create('data_jasa', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('produk_client_id');
            $table->string('platform')->nullable();
            $table->string('content_source')->default('organic');
            $table->date('tanggal_posting')->nullable();
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
