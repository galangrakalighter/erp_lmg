<?php

namespace Tests\Feature;

use App\Http\Controllers\CodexConnectionController;
use App\Models\OAuthUser;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use League\OAuth2\Server\ResourceServer;
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
            $mock->shouldReceive('hasRecordedActivity')->zeroOrMoreTimes()->andReturn(false);
            $mock->shouldReceive('hasRecordedCrud')->zeroOrMoreTimes()->andReturn(false);
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
        $this->getJson('/api/marketing-mcp/clients')
            ->assertUnauthorized()
            ->assertJsonStructure(['error' => ['code', 'message'], 'meta' => ['request_id']])
            ->assertJsonPath('error.code', 'unauthenticated')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->getJson('/api/marketing-mcp/clients/1/performance-trend?start_date=2026-10-01&end_date=2026-10-14')->assertUnauthorized();
        $this->getJson('/api/marketing-mcp/clients/1/top-content?start_date=2026-10-01&end_date=2026-10-14&metric=reach')->assertUnauthorized();
        $this->getJson('/api/marketing-mcp/clients/1/paid-performance?start_date=2026-10-01&end_date=2026-10-14')->assertUnauthorized();

        $this->actingAs($this->user)
            ->getJson('/api/marketing-mcp/clients')
            ->assertForbidden();
    }

    public function test_oauth_discovery_describes_pkce_and_read_only_scope(): void
    {
        $this->getJson('/.well-known/oauth-protected-resource')
            ->assertOk()
            ->assertJsonPath('scopes_supported.0', 'marketing:read')
            ->assertJsonPath('scopes_supported.1', 'offline_access')
            ->assertJsonPath('authorization_servers.0', config('marketing-mcp.issuer'));

        $this->getJson('/.well-known/oauth-authorization-server')
            ->assertOk()
            ->assertJsonPath('code_challenge_methods_supported.0', 'S256')
            ->assertJsonPath('token_endpoint_auth_methods_supported.0', 'none')
            ->assertJsonPath('grant_types_supported.0', 'authorization_code')
            ->assertJsonPath('scopes_supported.1', 'offline_access');

        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn ($route) => $route->uri() === 'api/oauth-marketing-mcp/clients');
        $this->assertNotNull($route);
        $this->assertSame(['GET', 'HEAD'], $route->methods());
    }

    public function test_oauth_poc_lists_only_clients_accessible_to_the_connected_user(): void
    {
        $this->mockPassportResourceServer();
        $oauthUser = OAuthUser::query()->findOrFail($this->user->id);
        Passport::actingAs($oauthUser, ['marketing:read'], 'oauth-api');

        $this->getJson('/api/oauth-marketing-mcp/clients')
            ->assertOk()
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.id', $this->allowedClient)
            ->assertJsonMissing(['id' => $this->deniedClient]);
    }

    public function test_oauth_poc_rejects_a_token_without_marketing_scope(): void
    {
        $this->mockPassportResourceServer();
        $oauthUser = OAuthUser::query()->findOrFail($this->user->id);
        Passport::actingAs($oauthUser, [], 'oauth-api');

        $this->getJson('/api/oauth-marketing-mcp/clients')->assertForbidden();
    }

    public function test_oauth_identity_can_use_every_read_only_tool_for_an_authorized_project(): void
    {
        $this->mockPassportResourceServer();
        $oauthUser = OAuthUser::query()->findOrFail($this->user->id);
        Passport::actingAs($oauthUser, ['marketing:read'], 'oauth-api');

        foreach ($this->oauthProjectToolUrls($this->allowedClient) as $url) {
            $this->getJson($url)->assertOk();
        }
    }

    public function test_oauth_identity_cannot_cross_into_another_users_client_or_project(): void
    {
        $otherUser = User::query()->create([
            'name' => 'Other Marketing User',
            'email' => 'other-marketing@example.test',
            'password' => Hash::make('password'),
            'isAdmin' => false,
            'is_locked' => false,
        ]);
        $divisionId = DB::table('divisions')->where('name', 'Digital Marketing')->value('id');
        $roleId = DB::table('roles')->where('division_id', $divisionId)->value('id');
        DB::table('user_division')->insert([
            'user_id' => $otherUser->id,
            'division_id' => $divisionId,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_client')->insert([
            'user_id' => $otherUser->id,
            'client_id' => $this->deniedClient,
            'assigned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->mockPassportResourceServer();
        Passport::actingAs(OAuthUser::query()->findOrFail($otherUser->id), ['marketing:read'], 'oauth-api');

        $this->getJson('/api/oauth-marketing-mcp/clients')
            ->assertOk()
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.id', $this->deniedClient)
            ->assertJsonMissing(['id' => $this->allowedClient]);

        foreach ($this->oauthProjectToolUrls($this->allowedClient) as $url) {
            $this->getJson($url)->assertForbidden();
        }
    }

    public function test_oauth_identity_is_rejected_when_the_account_is_locked(): void
    {
        $this->user->forceFill(['is_locked' => true])->save();
        $this->mockPassportResourceServer();
        Passport::actingAs(OAuthUser::query()->findOrFail($this->user->id), ['marketing:read'], 'oauth-api');

        $this->getJson('/api/oauth-marketing-mcp/clients')->assertStatus(423);
    }

    public function test_role_must_belong_to_the_same_division_as_the_assignment(): void
    {
        $digitalMarketingDivision = DB::table('divisions')->where('name', 'Digital Marketing')->value('id');
        $otherDivision = DB::table('divisions')->insertGetId([
            'name' => 'Other Division',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $foreignHeadRole = DB::table('roles')->insertGetId([
            'division_id' => $otherDivision,
            'role_name' => 'Busines Unit Head',
            'level' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_division')->where('user_id', $this->user->id)->delete();
        DB::table('user_division')->insert([
            'user_id' => $this->user->id,
            'division_id' => $digitalMarketingDivision,
            'role_id' => $foreignHeadRole,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withMarketingToken()->getJson('/api/marketing-mcp/clients')
            ->assertOk()
            ->assertJsonPath('pagination.total', 0);
    }

    public function test_digital_marketing_manager_has_division_wide_project_access(): void
    {
        $divisionId = DB::table('divisions')->where('name', 'Digital Marketing')->value('id');
        $managerRole = DB::table('roles')->insertGetId([
            'division_id' => $divisionId,
            'role_name' => 'Digital Marketing Manager',
            'level' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_division')->where('user_id', $this->user->id)->update(['role_id' => $managerRole]);

        $this->withMarketingToken()
            ->getJson("/api/marketing-mcp/clients/{$this->deniedClient}/data-availability")
            ->assertOk();
    }

    public function test_admin_and_digital_marketing_leader_can_view_division_client_totals(): void
    {
        DB::table('produk_client')->where('id', $this->deniedClient)->update(['status' => 0]);

        $admin = User::query()->create([
            'name' => 'Summary Admin', 'email' => 'summary-admin@example.test',
            'password' => Hash::make('password'), 'isAdmin' => true, 'is_locked' => false,
        ]);
        $adminToken = $admin->createToken('summary-admin', ['marketing:read'])->plainTextToken;
        $this->withToken($adminToken)->getJson('/api/marketing-mcp/division-client-summary')->assertOk()
            ->assertJsonPath('data.division_name', 'Digital Marketing')
            ->assertJsonPath('data.total_clients', 2)
            ->assertJsonPath('data.active_clients', 1)
            ->assertJsonPath('data.inactive_clients', 1)
            ->assertJsonPath('data.accessible_clients', 2)
            ->assertJsonPath('data.scope', 'division');

        $divisionId = DB::table('divisions')->where('name', 'Digital Marketing')->value('id');
        $managerRole = DB::table('roles')->insertGetId([
            'division_id' => $divisionId, 'role_name' => 'Digital Marketing Manager', 'level' => 2,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('user_division')->where('user_id', $this->user->id)->update(['role_id' => $managerRole]);
        $this->withMarketingToken()->getJson('/api/marketing-mcp/division-client-summary')->assertOk()
            ->assertJsonPath('data.total_clients', 2)
            ->assertJsonPath('data.accessible_clients', 2)
            ->assertJsonPath('data.scope', 'division');
    }

    public function test_regular_staff_receives_explicitly_restricted_division_summary_via_oauth(): void
    {
        $this->mockPassportResourceServer();
        Passport::actingAs(OAuthUser::query()->findOrFail($this->user->id), ['marketing:read'], 'oauth-api');

        $this->getJson('/api/oauth-marketing-mcp/division-client-summary')->assertOk()
            ->assertJsonPath('data.total_clients', null)
            ->assertJsonPath('data.active_clients', null)
            ->assertJsonPath('data.inactive_clients', null)
            ->assertJsonPath('data.accessible_clients', 1)
            ->assertJsonPath('data.scope', 'user_access_only')
            ->assertJsonPath('meta.completeness.status', 'restricted')
            ->assertJsonFragment(['access_note' => 'Akses terbatas: total seluruh Divisi Digital Marketing hanya tersedia untuk admin atau pimpinan yang memiliki akses tingkat divisi.']);
    }

    public function test_other_division_user_cannot_read_digital_marketing_division_summary(): void
    {
        $otherDivision = DB::table('divisions')->insertGetId(['name' => 'Finance', 'created_at' => now(), 'updated_at' => now()]);
        $otherRole = DB::table('roles')->insertGetId([
            'division_id' => $otherDivision, 'role_name' => 'Finance Manager', 'level' => 2,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $otherUser = User::query()->create([
            'name' => 'Finance Summary User', 'email' => 'finance-summary@example.test',
            'password' => Hash::make('password'), 'isAdmin' => false, 'is_locked' => false,
        ]);
        DB::table('user_division')->insert([
            'user_id' => $otherUser->id, 'division_id' => $otherDivision, 'role_id' => $otherRole,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $token = $otherUser->createToken('finance-summary', ['marketing:read'])->plainTextToken;

        $this->withToken($token)->getJson('/api/marketing-mcp/division-client-summary')->assertForbidden();
    }

    public function test_division_client_summary_requires_authentication(): void
    {
        $this->getJson('/api/marketing-mcp/division-client-summary')->assertUnauthorized();
        $this->getJson('/api/oauth-marketing-mcp/division-client-summary')->assertUnauthorized();
    }

    public function test_inactive_client_division_assignment_denies_project_access(): void
    {
        DB::table('client_division')->where('client_id', $this->allowedClient)->update(['status' => 0]);

        $this->withMarketingToken()
            ->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/data-availability")
            ->assertForbidden();
    }

    public function test_ai_connection_page_lists_and_revokes_only_the_users_oauth_connection(): void
    {
        $clientId = (string) Str::uuid();
        DB::table('oauth_clients')->insert([
            'id' => $clientId,
            'name' => 'ERP LMG ChatGPT Local',
            'provider' => 'oauth_users',
            'redirect_uris' => json_encode(['http://127.0.0.1:8400/callback']),
            'grant_types' => json_encode(['authorization_code', 'refresh_token']),
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('oauth_access_tokens')->insert([
            'id' => 'access-token-owned',
            'user_id' => $this->user->id,
            'client_id' => $clientId,
            'scopes' => json_encode(['marketing:read', 'offline_access']),
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addHour(),
        ]);
        DB::table('oauth_refresh_tokens')->insert([
            'id' => 'refresh-token-owned',
            'access_token_id' => 'access-token-owned',
            'revoked' => false,
            'expires_at' => now()->addMonth(),
        ]);

        $request = Request::create('/profile/ai-connections');
        $request->setUserResolver(fn () => $this->user);
        $controller = app(CodexConnectionController::class);
        $method = new \ReflectionMethod($controller, 'oauthConnections');
        $connections = $method->invoke($controller, $request);
        $this->assertCount(1, $connections);
        $this->assertSame('ERP LMG ChatGPT Local', $connections->first()->name);

        $this->actingAs($this->user)
            ->delete("/profile/ai-connections/oauth/{$clientId}")
            ->assertRedirect(route('ai-connections.index'));

        $this->assertDatabaseHas('oauth_access_tokens', ['id' => 'access-token-owned', 'revoked' => true]);
        $this->assertDatabaseHas('oauth_refresh_tokens', ['id' => 'refresh-token-owned', 'revoked' => true]);
    }

    public function test_token_requires_marketing_read_ability(): void
    {
        $token = $this->user->createToken('wrong-scope', ['unrelated:read'])->plainTextToken;

        $this->withToken($token)->getJson('/api/marketing-mcp/clients')->assertForbidden();
    }

    public function test_admin_can_read_all_clients_without_division_or_client_assignment(): void
    {
        $admin = User::query()->create([
            'name' => 'Marketing Admin',
            'email' => 'marketing-admin@example.test',
            'password' => Hash::make('password'),
            'isAdmin' => true,
            'is_locked' => false,
        ]);
        $token = $admin->createToken('admin-readiness-test', ['marketing:read'])->plainTextToken;

        $this->withToken($token)->getJson('/api/marketing-mcp/clients')
            ->assertOk()
            ->assertJsonPath('pagination.total', 2)
            ->assertJsonFragment(['id' => $this->allowedClient])
            ->assertJsonFragment(['id' => $this->deniedClient]);
        $this->withToken($token)->getJson("/api/marketing-mcp/clients/{$this->deniedClient}/search?q=launch&start_date=2026-10-01&end_date=2026-10-14")
            ->assertOk();
    }

    public function test_non_admin_from_another_division_cannot_cross_into_digital_marketing_clients(): void
    {
        $otherUser = User::query()->create([
            'name' => 'Other Division User',
            'email' => 'other-division@example.test',
            'password' => Hash::make('password'),
            'isAdmin' => false,
            'is_locked' => false,
        ]);
        $otherDivision = DB::table('divisions')->insertGetId([
            'name' => 'Finance',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherRole = DB::table('roles')->insertGetId([
            'division_id' => $otherDivision,
            'role_name' => 'Finance Manager',
            'level' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_division')->insert([
            'user_id' => $otherUser->id,
            'division_id' => $otherDivision,
            'role_id' => $otherRole,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_client')->insert([
            'user_id' => $otherUser->id,
            'client_id' => $this->allowedClient,
            'assigned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $token = $otherUser->createToken('cross-division-test', ['marketing:read'])->plainTextToken;

        $this->withToken($token)->getJson('/api/marketing-mcp/clients')
            ->assertOk()
            ->assertJsonPath('pagination.total', 0);
        $this->withToken($token)
            ->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/data-availability")
            ->assertForbidden();
        $this->withToken($token)
            ->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/search?q=launch&start_date=2026-10-01&end_date=2026-10-14")
            ->assertForbidden();
    }

    public function test_soft_deleted_account_cannot_reuse_an_existing_token(): void
    {
        $token = $this->user->createToken('before-deactivation', ['marketing:read'])->plainTextToken;
        $this->user->delete();

        $this->withToken($token)->getJson('/api/marketing-mcp/clients')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'unauthenticated');
    }

    public function test_authenticated_user_can_create_a_personal_codex_token(): void
    {
        $response = $this->actingAs($this->user)->postJson('/profile/codex/tokens', [
            'device_name' => 'Laptop Marketing',
            'current_password' => 'password',
        ]);

        $response->assertCreated()
            ->assertJsonPath('device_name', 'Laptop Marketing')
            ->assertJsonPath('abilities.0', 'marketing:read')
            ->assertJsonStructure(['token', 'api_url', 'expires_at']);

        $token = $this->user->tokens()->sole();
        $this->assertSame('codex-erp-lmg: Laptop Marketing', $token->name);
        $this->assertTrue($token->can('marketing:read'));
        $this->assertNotNull($token->expires_at);
    }

    public function test_codex_token_creation_requires_the_current_password(): void
    {
        $this->actingAs($this->user)->from('/profile/codex')->post('/profile/codex/tokens', [
            'device_name' => 'Laptop Marketing',
            'current_password' => 'incorrect-password',
        ])->assertRedirect('/profile/codex')
            ->assertSessionHasErrors('current_password');

        $this->assertCount(0, $this->user->tokens()->get());
    }

    public function test_user_can_only_revoke_their_own_codex_token(): void
    {
        $ownToken = $this->user->createToken('codex-erp-lmg: Own laptop', ['marketing:read'])->accessToken;
        $otherUser = User::query()->create([
            'name' => 'Other User',
            'email' => 'other@example.test',
            'password' => Hash::make('password'),
            'isAdmin' => false,
            'is_locked' => false,
        ]);
        $otherToken = $otherUser->createToken('codex-erp-lmg: Other laptop', ['marketing:read'])->accessToken;

        $this->actingAs($this->user)
            ->delete("/profile/codex/tokens/{$otherToken->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $otherToken->id]);

        $this->actingAs($this->user)
            ->delete("/profile/codex/tokens/{$ownToken->id}")
            ->assertRedirect(route('codex-connection.index'));

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $ownToken->id]);
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

        $this->assertCount(30, $routes);
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
        $this->withToken($token)->getJson('/api/marketing-mcp/clients?access_token=should-never-be-accepted')
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonMissing(['should-never-be-accepted']);
        $this->withToken($token)->getJson("/api/marketing-mcp/clients/{$this->allowedClient}/performance-summary?start_date=2026-10-14&end_date=2026-10-01")
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed');
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

    public function test_all_marketing_mcp_routes_are_read_only_and_use_the_same_success_contract(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'api/marketing-mcp/')
                || str_starts_with($route->uri(), 'api/oauth-marketing-mcp/'));

        $this->assertCount(60, $routes);
        foreach ($routes as $route) {
            $this->assertSame(['GET', 'HEAD'], $route->methods(), $route->uri());
        }

        $this->withMarketingToken()->getJson('/api/marketing-mcp/clients')
            ->assertOk()
            ->assertJsonStructure(['data', 'pagination', 'meta'])
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Request-Id');
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

    public function test_phase_two_tools_return_only_authorized_read_only_digital_marketing_data(): void
    {
        DB::table('analize_4p')->insert([
            'produk_client_id' => $this->allowedClient, 'nama_analisis' => 'Product', 'type' => 'Strength',
            'platform' => 'Instagram', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('target_audience')->insert([
            'produk_client_id' => $this->allowedClient, 'indikator' => 'Primary', 'usia' => '25-34',
            'platform' => 'Instagram', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('gaya_komunikasi')->insert([
            'produk_client_id' => $this->allowedClient, 'gaya_bicara' => 'Friendly', 'gaya_bahasa' => 'Simple',
            'platform' => 'Instagram', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('brand_tagline')->insert([
            'produk_client_id' => $this->allowedClient, 'nama_tagline' => 'Grow together',
            'nama_hashtagline' => '#GrowTogether', 'platform' => 'Instagram', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $refs = $this->contentReferences();
        DB::table('pilar')->where('id', $refs['pilar_id'])->update([
            'kategori_id' => DB::table('produk_client')->where('id', $this->allowedClient)->value('kategori_jasa_id'),
            'status' => 1, 'fungsi_singkat' => 'Educate audiences', 'contoh' => 'Tutorial',
        ]);
        $planId = $this->insertContentPlan($this->allowedClient, $refs, [
            'inspirasi' => 'Customer question', 'note' => 'Draft copy without credentials',
            'tanggal_mulai' => '2026-10-01', 'tanggal_deadline' => '2026-10-04',
        ]);
        $divisionId = DB::table('divisions')->where('name', 'Digital Marketing')->value('id');
        DB::table('task')->insert([
            'nama_task' => 'Review content', 'divisi' => $divisionId, 'pic' => $this->user->id,
            'priority' => 1, 'menit' => 30, 'id_manajement_konten' => $planId,
            'tanggal_mulai' => '2026-10-02', 'tanggal_deadline' => '2026-10-04', 'status' => 2,
            'project' => $this->allowedClient, 'tanggal_approve' => '2026-10-04', 'tanggal_revisi' => '2026-10-03',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $fileId = DB::table('file_pdf')->insertGetId([
            'nama_file' => 'Brand Guide', 'client' => $this->allowedClient, 'division_id' => $divisionId,
            'uploaded_by' => $this->user->id, 'location' => 'private/secret/path.pdf', 'is_pinned' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('file_pdf_versions')->insert([
            'file_pdf_id' => $fileId, 'version_number' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $before = collect(['analize_4p', 'target_audience', 'gaya_komunikasi', 'brand_tagline', 'data_jasa', 'task', 'file_pdf', 'file_pdf_versions'])
            ->mapWithKeys(fn (string $table) => [$table => DB::table($table)->get()->map(fn ($row) => (array) $row)->all()]);

        $base = "/api/marketing-mcp/clients/{$this->allowedClient}";
        $this->withMarketingToken()->getJson($base.'/market-research?platform=instagram')->assertOk()
            ->assertJsonFragment(['section' => 'target_audience', 'usia' => '25-34'])
            ->assertJsonPath('meta.competitor_data.status', 'unavailable');
        $this->withMarketingToken()->getJson($base.'/brand-guidelines?platform=instagram')->assertOk()
            ->assertJsonFragment(['section' => 'tone_of_voice', 'value' => 'Friendly'])
            ->assertJsonFragment(['section' => 'content_pillar', 'value' => 'Education']);
        $period = 'start_date=2026-10-01&end_date=2026-10-14';
        $this->withMarketingToken()->getJson($base.'/content-calendar?'.$period)->assertOk()
            ->assertJsonPath('data.0.hashtags.0', '#GrowTogether')
            ->assertJsonPath('data.0.caption', null)
            ->assertJsonPath('meta.caption_availability.status', 'unavailable');
        $this->withMarketingToken()->getJson($base.'/tasks?'.$period)->assertOk()
            ->assertJsonPath('data.0.pic_name', 'Marketing User')
            ->assertJsonMissingPath('data.0.email');
        $this->withMarketingToken()->getJson($base.'/approvals?'.$period)->assertOk()
            ->assertJsonPath('data.0.approved_at', '2026-10-04')
            ->assertJsonPath('meta.history_availability.status', 'partial');
        $this->withMarketingToken()->getJson($base.'/assets')->assertOk()
            ->assertJsonPath('data.0.version_count', 1)
            ->assertJsonMissingPath('data.0.location')
            ->assertJsonMissing(['private/secret/path.pdf']);

        foreach (['market-research', 'brand-guidelines', 'assets'] as $tool) {
            $this->withMarketingToken()->getJson("/api/marketing-mcp/clients/{$this->deniedClient}/{$tool}")->assertForbidden();
        }
        $this->withMarketingToken()->getJson($base.'/tasks?start_date=2025-01-01&end_date=2026-10-14')->assertUnprocessable();
        $this->withMarketingToken()->getJson($base.'/assets?per_page=101')->assertUnprocessable();
        $this->withMarketingToken()->getJson($base.'/market-research?credential=secret-value')
            ->assertUnprocessable()->assertJsonMissing(['secret-value']);

        foreach ($before as $table => $rows) {
            $this->assertSame($rows, DB::table($table)->get()->map(fn ($row) => (array) $row)->all(), $table);
        }
    }

    public function test_campaign_performance_comparison_and_channel_analysis_use_only_stored_numbers(): void
    {
        $paidCurrent = [
            'id' => 'ad-1', 'campaign_id' => 'campaign-1', 'campaign' => 'Launch', 'title' => 'Paid launch',
            'date' => '2026-10-10', 'budget' => 1500, 'target' => 'lead_generation', 'currency' => 'IDR',
            'classification' => 'TTL',
            'metrics' => ['reach' => 800, 'impressions' => 1000, 'engagement' => 40, 'clicks' => 20, 'spend' => 100, 'leads' => 4, 'conversions' => 2, 'token' => 'never-return'],
        ];
        $organicCurrent = [
            'id' => 'post-1', 'title' => 'Organic launch', 'date' => '2026-10-11', 'classification' => 'BTL',
            'metrics' => ['reach' => 500, 'impressions' => 600, 'engagement' => 60, 'clicks' => 10, 'spend' => 'not-a-number'],
        ];
        $this->insertSnapshot('2026-09-30', ['impressions' => 800, 'clicks' => 8, 'spend' => 80, 'leads' => 2, 'conversions' => 1, 'currency' => 'IDR'], [$paidCurrent], 'paid');
        $this->insertSnapshot('2026-10-10', ['reach' => 800, 'impressions' => 1000, 'engagement' => 40, 'clicks' => 20, 'spend' => 100, 'leads' => 4, 'conversions' => 2, 'currency' => 'IDR'], [$paidCurrent], 'paid');
        $this->insertSnapshot('2026-10-11', ['reach' => 500, 'impressions' => 600, 'engagement' => 60, 'clicks' => 10], [$organicCurrent], 'organic');
        $campaignId = DB::table('marketing_campaigns')->insertGetId([
            'client_id' => $this->allowedClient, 'name' => 'Launch', 'status' => 'active', 'platform' => 'instagram', 'source' => 'paid',
            'start_date' => '2026-10-01', 'end_date' => '2026-10-31', 'budget' => 1500, 'currency' => 'IDR', 'target' => 'lead_generation',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $before = DB::table('analysis_snapshots')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        $base = "/api/marketing-mcp/clients/{$this->allowedClient}";
        $period = 'start_date=2026-10-01&end_date=2026-10-14';

        $this->withMarketingToken()->getJson($base.'/campaigns?'.$period.'&campaign_id='.$campaignId)->assertOk()
            ->assertJsonPath('data.0.campaign', 'Launch')
            ->assertJsonPath('data.0.budget', 1500)
            ->assertJsonPath('data.0.target', 'lead_generation')
            ->assertJsonPath('meta.source', 'marketing_campaigns')
            ->assertJsonMissingPath('data.0.metrics')
            ->assertJsonMissing(['never-return']);
        $this->withMarketingToken()->getJson($base.'/performance-metrics?'.$period)->assertOk()
            ->assertJsonPath('pagination', null)
            ->assertJsonFragment(['ctr' => 2, 'cpc' => 5, 'cpm' => 100])
            ->assertJsonPath('paid_vs_organic.0.platform', 'instagram');
        $this->withMarketingToken()->getJson($base.'/period-comparison?'.$period.'&compare_start_date=2026-09-17&compare_end_date=2026-09-30')->assertOk()
            ->assertJsonPath('pagination', null)
            ->assertJsonPath('data.0.metrics.clicks.current', 20)
            ->assertJsonPath('data.0.metrics.clicks.comparison', 8)
            ->assertJsonPath('data.0.metrics.clicks.change_percent', 150);
        $this->withMarketingToken()->getJson($base.'/channel-analysis?'.$period)->assertOk()
            ->assertJsonPath('pagination', null)
            ->assertJsonFragment(['classification' => 'TTL', 'classification_source' => 'stored'])
            ->assertJsonFragment(['classification' => 'BTL', 'classification_source' => 'stored']);

        foreach (['campaigns', 'performance-metrics', 'period-comparison', 'channel-analysis'] as $tool) {
            $query = $period.($tool === 'period-comparison' ? '&compare_start_date=2026-09-17&compare_end_date=2026-09-30' : '');
            $this->withMarketingToken()->getJson("/api/marketing-mcp/clients/{$this->deniedClient}/{$tool}?{$query}")->assertForbidden();
        }
        $this->withMarketingToken()->getJson($base.'/performance-metrics?'.$period.'&credential=secret')->assertUnprocessable()->assertJsonMissing(['secret']);
        $this->withMarketingToken()->getJson($base.'/period-comparison?'.$period.'&compare_start_date=2026-10-10&compare_end_date=2026-10-01')->assertUnprocessable();
        $this->assertSame($before, DB::table('analysis_snapshots')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all());
    }

    public function test_cross_dataset_search_is_authorized_paginated_safe_and_handles_incomplete_data(): void
    {
        DB::table('analize_4p')->insert([
            'produk_client_id' => $this->allowedClient, 'nama_analisis' => 'Launch market opportunity',
            'type' => 'promotion', 'platform' => 'Instagram', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $refs = $this->contentReferences();
        $this->insertContentPlan($this->allowedClient, $refs, [
            'judul' => 'Launch October', 'tanggal_posting' => '2026-10-10', 'note' => 'Safe campaign copy',
        ]);
        DB::table('file_pdf')->insert([
            'nama_file' => 'Launch Brand Guide', 'client' => $this->allowedClient,
            'location' => 'private/credentials/launch-secret.pdf', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->insertSnapshot('2026-10-10', ['reach' => 100], 'not-json', 'paid');

        $base = "/api/marketing-mcp/clients/{$this->allowedClient}/search";
        $query = 'q=launch&start_date=2026-10-01&end_date=2026-10-14&per_page=2';
        $response = $this->withMarketingToken()->getJson($base.'?'.$query)->assertOk()
            ->assertJsonPath('pagination.page', 1)
            ->assertJsonPath('pagination.per_page', 2)
            ->assertJsonPath('pagination.total', 3)
            ->assertJsonPath('meta.datasets_searched.asset_metadata', 1)
            ->assertJsonMissing(['private/credentials/launch-secret.pdf'])
            ->assertJsonMissing(['not-json']);
        $this->assertCount(2, $response->json('data'));
        $this->withMarketingToken()->getJson($base.'?'.$query.'&page=2')->assertOk()
            ->assertJsonPath('pagination.page', 2)
            ->assertJsonCount(1, 'data');

        $this->withMarketingToken()->getJson("/api/marketing-mcp/clients/{$this->deniedClient}/search?{$query}")->assertForbidden();
        $this->withMarketingToken()->getJson($base.'?q=x&start_date=2026-10-01&end_date=2026-10-14')->assertUnprocessable();
        $this->withMarketingToken()->getJson($base.'?q=launch&start_date=2025-01-01&end_date=2026-10-14')->assertUnprocessable();
        $this->withMarketingToken()->getJson($base.'?q=launch&start_date=2026-10-01&end_date=2026-10-14&per_page=101')->assertUnprocessable();
        $this->withMarketingToken()->getJson($base.'?q=launch&start_date=2026-10-01&end_date=2026-10-14&credential=secret-value')
            ->assertUnprocessable()->assertJsonMissing(['secret-value']);

        $this->auditLog->shouldHaveReceived('record')->withArgs(function (array $activity): bool {
            $filters = data_get($activity, 'context.filters', []);

            return data_get($activity, 'context.tool') === 'marketing_search'
                && ($filters['query_applied'] ?? false) === true
                && ! array_key_exists('q', $filters)
                && ! str_contains(json_encode($activity), 'launch');
        })->atLeast()->once();
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

    /** @return list<string> */
    private function oauthProjectToolUrls(int $clientId): array
    {
        $period = 'start_date=2026-10-01&end_date=2026-10-14';

        return [
            "/api/oauth-marketing-mcp/clients/{$clientId}/data-availability",
            "/api/oauth-marketing-mcp/clients/{$clientId}/performance-summary",
            "/api/oauth-marketing-mcp/clients/{$clientId}/content-plan?{$period}",
            "/api/oauth-marketing-mcp/clients/{$clientId}/content-mix?{$period}",
            "/api/oauth-marketing-mcp/clients/{$clientId}/performance-trend?{$period}",
            "/api/oauth-marketing-mcp/clients/{$clientId}/top-content?{$period}&metric=reach",
            "/api/oauth-marketing-mcp/clients/{$clientId}/paid-performance?{$period}",
            "/api/oauth-marketing-mcp/clients/{$clientId}/market-research",
            "/api/oauth-marketing-mcp/clients/{$clientId}/brand-guidelines",
            "/api/oauth-marketing-mcp/clients/{$clientId}/content-calendar?{$period}",
            "/api/oauth-marketing-mcp/clients/{$clientId}/tasks?{$period}",
            "/api/oauth-marketing-mcp/clients/{$clientId}/approvals?{$period}",
            "/api/oauth-marketing-mcp/clients/{$clientId}/assets",
            "/api/oauth-marketing-mcp/clients/{$clientId}/campaigns?{$period}",
            "/api/oauth-marketing-mcp/clients/{$clientId}/performance-metrics?{$period}",
            "/api/oauth-marketing-mcp/clients/{$clientId}/period-comparison?{$period}",
            "/api/oauth-marketing-mcp/clients/{$clientId}/channel-analysis?{$period}",
            "/api/oauth-marketing-mcp/clients/{$clientId}/search?q=launch&{$period}",
        ];
    }

    private function mockPassportResourceServer(): void
    {
        $this->app->instance(ResourceServer::class, \Mockery::mock(ResourceServer::class));
    }

    private function createContractSchema(): void
    {
        foreach (['marketing_campaigns', 'file_pdf_versions', 'file_pdf', 'task', 'alat_branding', 'moodboard', 'audio_brand', 'komposisi', 'gaya_komunikasi', 'brand_tagline', 'brand_value', 'brand_image', 'brand_identify', 'positioning', 'target_audience', 'segmentasi', 'analize_swot', 'analize_4p', 'oauth_refresh_tokens', 'oauth_access_tokens', 'oauth_clients', 'platform_mirror_copies', 'analysis_snapshots', 'data_jasa', 'jenis_cta', 'jenis_body', 'hooks', 'pilar', 'strategy', 'type_produk', 'status', 'user_client', 'client_division', 'user_division', 'roles', 'divisions', 'produk_client', 'kategori_jasa', 'personal_access_tokens', 'users'] as $table) {
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
        Schema::create('oauth_clients', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->text('redirect_uris');
            $table->text('grant_types');
            $table->boolean('revoked')->default(false);
            $table->timestamps();
        });
        Schema::create('oauth_access_tokens', function (Blueprint $table): void {
            $table->string('id', 80)->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->uuid('client_id');
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked')->default(false);
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });
        Schema::create('oauth_refresh_tokens', function (Blueprint $table): void {
            $table->string('id', 80)->primary();
            $table->string('access_token_id', 80)->index();
            $table->boolean('revoked')->default(false);
            $table->dateTime('expires_at')->nullable();
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
        Schema::table('pilar', function (Blueprint $table): void {
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->integer('status')->default(1);
            $table->string('fungsi_singkat')->nullable();
            $table->string('contoh')->nullable();
            $table->timestamps();
        });
        Schema::create('data_jasa', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('produk_client_id');
            $table->string('judul')->nullable();
            $table->string('inspirasi')->nullable();
            $table->text('note')->nullable();
            $table->string('platform')->nullable();
            $table->string('content_source')->default('organic');
            $table->date('tanggal_posting')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_deadline')->nullable();
            $table->time('jam_posting')->nullable();
            $table->unsignedBigInteger('type_produk_id')->nullable();
            $table->unsignedBigInteger('strategy_id')->nullable();
            $table->unsignedBigInteger('pilar_id')->nullable();
            $table->unsignedBigInteger('hooks_id')->nullable();
            $table->unsignedBigInteger('body_id')->nullable();
            $table->unsignedBigInteger('cta_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->unsignedBigInteger('pic')->nullable();
            $table->unsignedInteger('priority')->nullable();
            $table->unsignedInteger('menit')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->unsignedBigInteger('archived_by')->nullable();
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
        Schema::create('marketing_campaigns', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('client_id'); $table->unsignedBigInteger('owner_user_id')->nullable();
            $table->string('name', 200); $table->string('status', 50); $table->string('platform', 50)->nullable(); $table->string('source', 20)->default('organic');
            $table->date('start_date'); $table->date('end_date'); $table->text('objective')->nullable(); $table->decimal('budget', 18, 2)->nullable();
            $table->string('currency', 3)->nullable(); $table->text('target')->nullable(); $table->timestamp('archived_at')->nullable(); $table->unsignedBigInteger('archived_by')->nullable(); $table->timestamps();
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
        foreach ([
            'analize_4p' => ['nama_analisis', 'type'],
            'analize_swot' => ['strenght', 'weakness', 'oportunity', 'threat'],
            'segmentasi' => ['usia', 'gender', 'negara', 'wilayah', 'gaya_hidup', 'status_sosial', 'minat', 'penggunaan', 'loyalitas', 'manfaat'],
            'target_audience' => ['indikator', 'usia', 'gender', 'negara', 'wilayah', 'pekerjaan', 'gaya_hidup', 'status_sosial', 'penggunaan', 'manfaat'],
            'positioning' => ['indikator', 'deskripsi'],
        ] as $tableName => $fields) {
            Schema::create($tableName, function (Blueprint $table) use ($fields): void {
                $table->id();
                $table->unsignedBigInteger('produk_client_id');
                foreach ($fields as $field) {
                    $table->text($field)->nullable();
                }
                $table->string('platform')->nullable();
                $table->timestamp('archived_at')->nullable();
                $table->unsignedBigInteger('archived_by')->nullable();
                $table->timestamps();
            });
        }
        foreach ([
            'brand_identify' => ['identify'], 'brand_image' => ['nama_image'], 'brand_value' => ['nama_value'],
            'brand_tagline' => ['nama_tagline', 'nama_hashtagline'], 'gaya_komunikasi' => ['gaya_bicara', 'gaya_bahasa'],
            'komposisi' => ['type_komposisi', 'komposisi'], 'audio_brand' => ['nama_audio'],
            'moodboard' => ['moodboard'], 'alat_branding' => ['nama_alat'],
        ] as $tableName => $fields) {
            Schema::create($tableName, function (Blueprint $table) use ($fields, $tableName): void {
                $table->id();
                $table->unsignedBigInteger('produk_client_id');
                foreach ($fields as $field) {
                    $table->text($field)->nullable();
                }
                $table->string('platform')->nullable();
                if ($tableName === 'brand_identify') {
                    $table->timestamp('archived_at')->nullable();
                    $table->unsignedBigInteger('archived_by')->nullable();
                }
                $table->timestamps();
            });
        }
        Schema::create('task', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_task');
            $table->string('jira')->nullable();
            $table->unsignedBigInteger('divisi');
            $table->unsignedBigInteger('pic');
            $table->integer('priority')->default(0);
            $table->integer('menit')->default(0);
            $table->unsignedBigInteger('id_manajement_konten')->nullable();
            $table->unsignedBigInteger('id_kpi')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_deadline');
            $table->integer('status')->default(0);
            $table->unsignedBigInteger('project');
            $table->time('jam_mulai')->nullable();
            $table->date('tanggal_approve')->nullable();
            $table->date('tanggal_revisi')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('archived_by')->nullable();
            $table->timestamps();
        });
        Schema::create('file_pdf', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_file');
            $table->unsignedBigInteger('client');
            $table->unsignedBigInteger('division_id')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('location');
            $table->boolean('is_generated_document')->default(false);
            $table->boolean('is_system_template')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->integer('pin_order')->nullable();
            $table->timestamps();
        });
        Schema::create('file_pdf_versions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('file_pdf_id');
            $table->integer('version_number');
            $table->timestamps();
        });
    }
}
