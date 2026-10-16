<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\FirestoreService;
use DateTimeImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\Client as PassportClient;
use Laravel\Passport\Bridge\Scope;
use Laravel\Passport\Passport;
use League\OAuth2\Server\CryptKey;
use Tests\Support\InMemoryFirestoreService;
use Tests\TestCase;

class ContentPlanDatabaseOAuthTest extends TestCase
{
    protected string $keyDirectory;
    protected string $clientId;
    protected User $authorizedUser;
    protected int $allowedClient;
    protected int $otherClient;
    protected InMemoryFirestoreService $firestore;
    protected ActivityLogService $activityLog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->createSchema();
        $this->createTestingKeys();
        $this->seedAccessFixtures();
        Cache::flush();

        $this->firestore = new InMemoryFirestoreService();
        $this->app->instance(FirestoreService::class, $this->firestore);
        $this->activityLog = new ActivityLogService($this->firestore, $this->app->make(Request::class));
        $this->app->instance(ActivityLogService::class, $this->activityLog);
    }

    protected function tearDown(): void
    {
        $this->activityLog?->flush();
        Passport::loadKeysFrom(storage_path());
        foreach (['oauth-private.key', 'oauth-public.key'] as $file) {
            if (isset($this->keyDirectory) && is_file($this->keyDirectory.DIRECTORY_SEPARATOR.$file)) {
                unlink($this->keyDirectory.DIRECTORY_SEPARATOR.$file);
            }
        }
        if (isset($this->keyDirectory) && is_dir($this->keyDirectory)) rmdir($this->keyDirectory);
        parent::tearDown();
    }

    public function test_database_oauth_token_runs_create_update_archive_policy_database_and_audit(): void
    {
        [$bearer, $tokenId] = $this->databaseToken($this->authorizedUser, ['marketing:read', 'marketing:write']);
        $createPayload = $this->createPayload();
        $confirmation = $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/content-plans/confirmation', [
            'action' => 'create', 'client_id' => $this->allowedClient, 'object_id' => 0, 'payload' => $createPayload,
        ])->assertOk()->json('data.confirmation_token');

        $created = $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/content-plans', $createPayload + ['client_id' => $this->allowedClient, 'confirmation_token' => $confirmation])
            ->assertCreated()->assertJsonPath('meta.operation', 'create');
        $id = (int) $created->json('data.id');
        $this->assertDatabaseHas('oauth_access_tokens', ['id' => $tokenId, 'user_id' => $this->authorizedUser->id, 'revoked' => false]);
        $this->assertDatabaseHas('data_jasa', ['id' => $id, 'produk_client_id' => $this->allowedClient, 'judul' => 'OAuth database plan']);

        $updatePayload = ['judul' => 'OAuth updated'];
        $updateToken = $this->confirmation($bearer, 'update', $this->allowedClient, $id, $updatePayload);
        $this->withToken($bearer)->patchJson("/api/oauth-marketing-crud/content-plans/{$id}", $updatePayload + ['client_id' => $this->allowedClient, 'confirmation_token' => $updateToken])
            ->assertOk()->assertJsonPath('data.judul', 'OAuth updated');

        $archivePayload = ['reason' => 'Completed safely'];
        $archiveToken = $this->confirmation($bearer, 'archive', $this->allowedClient, $id, $archivePayload);
        $archiveResponse = $this->withToken($bearer)->postJson("/api/oauth-marketing-crud/content-plans/{$id}/archive", $archivePayload + ['confirmation_token' => $archiveToken])
            ->assertOk()->assertJsonPath('data.id', $id);
        $this->assertDatabaseHas('data_jasa', ['id' => $id, 'archived_by' => $this->authorizedUser->id]);
        $this->withToken($bearer)->getJson("/api/oauth-marketing-mcp/clients/{$this->allowedClient}/content-plan?start_date=2026-01-01&end_date=2026-12-31")
            ->assertOk()->assertJsonMissing(['id' => $id]);

        $this->activityLog->flush();
        $mutationRecords = array_values(array_filter($this->firestore->records, fn (array $record): bool => ($record['data']['module'] ?? null) === 'content_plan'));
        $this->assertCount(3, $mutationRecords);
        foreach ($mutationRecords as $record) {
            $audit = $record['data'];
            $this->assertSame((string) $this->authorizedUser->id, $audit['user_id']);
            $this->assertSame($this->allowedClient, $audit['context']['client_id']);
            $this->assertNotEmpty($audit['context']['tool']);
            $this->assertNotEmpty($audit['context']['record_id']);
            $this->assertSame('success', $audit['context']['result']);
            $this->assertSame('completed', $audit['context']['status']);
            $this->assertNotEmpty($audit['context']['occurred_at']);
        }
        $serialized = json_encode([$this->firestore->records, $archiveResponse->json()], JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString($bearer, $serialized);
        $this->assertStringNotContainsString($confirmation, $serialized);
        $this->assertDoesNotMatchRegularExpression('/password|credential|private[_-]?key/i', $serialized);
    }

    public function test_content_plan_master_options_are_scoped_paginated_searchable_and_safe(): void
    {
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:read']);
        DB::table('type_produk')->insert(['id' => 2, 'type' => 'Video']);
        DB::table('strategy')->insert(['id' => 2, 'strategy' => 'Awareness']);
        DB::table('hooks')->insert(['id' => 2, 'hooks' => 'Question']);
        DB::table('jenis_body')->insert(['id' => 2, 'nama_body' => 'Story']);
        DB::table('jenis_cta')->insert(['id' => 2, 'nama_cta' => 'Learn']);
        foreach (['product-types', 'strategies', 'hooks', 'bodies', 'ctas'] as $master) {
            $response = $this->withToken($bearer)->getJson("/api/oauth-marketing-mcp/clients/{$this->allowedClient}/content-plan-masters/{$master}?search=ea&per_page=1");
            $response->assertOk()->assertJsonStructure(['data', 'pagination', 'meta']);
            $this->assertLessThanOrEqual(1, count($response->json('data')));
            foreach ($response->json('data') as $row) {
                $this->assertSame(['id', 'name', 'active'], array_keys($row));
                $this->assertTrue($row['active']);
            }
        }
        $restricted = $this->makeUser('Restricted Master User', 'restricted-master@example.test', 'Manager', 'Finance', false);
        [$restrictedBearer] = $this->databaseToken($restricted, ['marketing:read']);
        $this->withToken($restrictedBearer)->getJson('/api/oauth-marketing-mcp/clients/999999/content-plan-masters/hooks')
            ->assertForbidden();
    }

    public function test_missing_revoked_expired_and_wrong_scope_database_tokens_are_rejected(): void
    {
        $this->withToken('not-a-jwt')->postJson('/api/oauth-marketing-crud/content-plans/confirmation', [])->assertUnauthorized();
        foreach ([
            ['scopes' => ['marketing:write'], 'revoked' => true, 'expires' => '+10 minutes'],
            ['scopes' => ['marketing:write'], 'revoked' => false, 'expires' => '-10 minutes'],
            ['scopes' => ['marketing:read'], 'revoked' => false, 'expires' => '+10 minutes'],
        ] as $case) {
            [$bearer] = $this->databaseToken($this->authorizedUser, $case['scopes'], $case['revoked'], $case['expires']);
            $response = $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/content-plans/confirmation', [
                'action' => 'create', 'client_id' => $this->allowedClient, 'payload' => $this->createPayload(),
            ]);
            $case['scopes'] === ['marketing:read'] ? $response->assertForbidden() : $response->assertUnauthorized();
        }
    }

    public function test_division_assignment_permission_and_client_boundaries_are_enforced_with_database_identity(): void
    {
        $noClient = $this->makeUser('No Client', 'noclient@example.test', 'Content Creator', 'Digital Marketing', false);
        $otherDivision = $this->makeUser('Finance User', 'finance@example.test', 'Manager', 'Finance', false);
        $noPermission = $this->makeUser('DM Viewer', 'viewer@example.test', 'Digital Marketing', 'Digital Marketing', true);

        foreach ([[$noClient, $this->allowedClient], [$otherDivision, $this->allowedClient], [$noPermission, $this->allowedClient]] as [$user, $client]) {
            [$bearer] = $this->databaseToken($user, ['marketing:write']);
            $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/content-plans/confirmation', [
                'action' => 'create', 'client_id' => $client, 'payload' => $this->createPayload(),
            ])->assertForbidden();
        }

        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:write']);
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/content-plans/confirmation', [
            'action' => 'create', 'client_id' => $this->otherClient, 'payload' => $this->createPayload(),
        ])->assertForbidden();
    }

    public function test_locked_database_identity_is_rejected(): void
    {
        $locked = $this->makeUser('Locked', 'locked@example.test', 'Digital Marketing Manager', 'Digital Marketing', true, true);
        [$bearer] = $this->databaseToken($locked, ['marketing:write']);
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/content-plans/confirmation', [
            'action' => 'create', 'client_id' => $this->allowedClient, 'payload' => $this->createPayload(),
        ])->assertStatus(423);
    }

    public function test_confirmation_token_invalid_reuse_and_mismatch_are_rejected_through_oauth_route(): void
    {
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:write']);
        $payload = $this->createPayload();
        $token = $this->confirmation($bearer, 'create', $this->allowedClient, 0, $payload);
        $invalid = $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/content-plans', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => str_repeat('x', 64)]);
        $this->assertSame(422, $invalid->status(), $invalid->getContent());
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/content-plans', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $token])->assertCreated();
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/content-plans', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $token])->assertStatus(422);

        $mismatch = $this->confirmation($bearer, 'create', $this->allowedClient, 0, $payload);
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/content-plans', array_replace($payload, ['judul' => 'Tampered']) + ['client_id' => $this->allowedClient, 'confirmation_token' => $mismatch])->assertStatus(422);
        $expired = $this->confirmation($bearer, 'create', $this->allowedClient, 0, $payload);
        Cache::forget('marketing_confirmation:'.hash('sha256', $expired));
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/content-plans', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $expired])->assertStatus(422);
    }

    protected function confirmation(string $bearer, string $action, int $client, int $object, array $payload): string
    {
        return $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/content-plans/confirmation', compact('action', 'client', 'object', 'payload') + ['client_id' => $client, 'object_id' => $object])
            ->assertOk()->json('data.confirmation_token');
    }

    protected function databaseToken(User $user, array $scopes, bool $revoked = false, string $expiry = '+10 minutes'): array
    {
        $id = (string) Str::uuid();
        $expiresAt = new DateTimeImmutable($expiry);
        DB::table('oauth_access_tokens')->insert(['id' => $id, 'user_id' => $user->id, 'client_id' => $this->clientId, 'name' => 'test', 'scopes' => json_encode($scopes), 'revoked' => $revoked, 'created_at' => now(), 'updated_at' => now(), 'expires_at' => $expiresAt]);
        $client = new PassportClient($this->clientId, 'Content Plan Test', ['https://example.test/callback'], false, 'oauth_users', ['authorization_code']);
        $token = new AccessToken((string) $user->id, array_map(fn (string $scope) => new Scope($scope), $scopes), $client);
        $token->setIdentifier($id);
        $token->setExpiryDateTime($expiresAt);
        $token->setPrivateKey(new CryptKey($this->keyDirectory.DIRECTORY_SEPARATOR.'oauth-private.key', null, false));
        return [$token->toString(), $id];
    }

    private function createPayload(): array
    {
        return ['judul' => 'OAuth database plan', 'type_produk_id' => 1, 'strategy_id' => 1, 'pilar_id' => 1, 'hooks_id' => 1, 'body_id' => 1, 'cta_id' => 1, 'status_id' => 1];
    }

    private function seedAccessFixtures(): void
    {
        $this->clientId = (string) Str::uuid();
        DB::table('oauth_clients')->insert(['id' => $this->clientId, 'name' => 'Testing Client', 'provider' => 'oauth_users', 'redirect_uris' => json_encode(['https://example.test/callback']), 'grant_types' => json_encode(['authorization_code']), 'revoked' => false, 'created_at' => now(), 'updated_at' => now()]);
        $category = DB::table('kategori_jasa')->insertGetId(['category' => 'External', 'created_at' => now(), 'updated_at' => now()]);
        $division = DB::table('divisions')->insertGetId(['name' => 'Digital Marketing', 'created_at' => now(), 'updated_at' => now()]);
        $this->allowedClient = DB::table('produk_client')->insertGetId(['kategori_jasa_id' => $category, 'nama' => 'Allowed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $this->otherClient = DB::table('produk_client')->insertGetId(['kategori_jasa_id' => $category, 'nama' => 'Other', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        foreach ([$this->allowedClient, $this->otherClient] as $client) DB::table('client_division')->insert(['client_id' => $client, 'division_id' => $division, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $this->authorizedUser = $this->makeUser('OAuth Owner', 'owner@example.test', 'Digital Marketing Manager', 'Digital Marketing', true);
        foreach ([['type_produk', 'type'], ['strategy', 'strategy'], ['pilar', 'pilar'], ['hooks', 'hooks'], ['jenis_body', 'nama_body'], ['jenis_cta', 'nama_cta']] as [$table, $column]) DB::table($table)->insert(['id' => 1, $column => 'Default']);
        DB::table('status')->insert(['id' => 1, 'nama_status' => 'Draft']);
    }

    protected function makeUser(string $name, string $email, string $roleName, string $divisionName, bool $assignClient, bool $locked = false): User
    {
        $division = DB::table('divisions')->where('name', $divisionName)->value('id') ?: DB::table('divisions')->insertGetId(['name' => $divisionName, 'created_at' => now(), 'updated_at' => now()]);
        $role = DB::table('roles')->insertGetId(['division_id' => $division, 'role_name' => $roleName, 'level' => 3, 'created_at' => now(), 'updated_at' => now()]);
        $user = User::query()->create(['name' => $name, 'email' => $email, 'password' => Hash::make('password'), 'isAdmin' => false, 'is_locked' => $locked]);
        DB::table('user_division')->insert(['user_id' => $user->id, 'division_id' => $division, 'role_id' => $role, 'created_at' => now(), 'updated_at' => now()]);
        if ($assignClient) DB::table('user_client')->insert(['user_id' => $user->id, 'client_id' => $this->allowedClient, 'assigned_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        return $user;
    }

    private function createTestingKeys(): void
    {
        $this->keyDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'erp-lmg-passport-'.Str::uuid();
        mkdir($this->keyDirectory, 0700, true);
        // Copy into an isolated directory so Passport never writes to or mutates application keys.
        copy(storage_path('oauth-private.key'), $this->keyDirectory.DIRECTORY_SEPARATOR.'oauth-private.key');
        copy(storage_path('oauth-public.key'), $this->keyDirectory.DIRECTORY_SEPARATOR.'oauth-public.key');
        Passport::loadKeysFrom($this->keyDirectory);
        $this->app->forgetInstance(\League\OAuth2\Server\ResourceServer::class);
    }

    private function createSchema(): void
    {
        foreach (['marketing_asset_versions','marketing_asset_metadata','file_pdf_versions','file_pdf','marketing_approval_events','task','marketing_campaigns','positioning','target_audience','segmentasi','analize_swot','analize_4p','alat_branding','moodboard','audio_brand','komposisi','gaya_komunikasi','brand_tagline','brand_value','brand_image','brand_identify','platform_mirror_copies','oauth_access_tokens','oauth_clients','data_jasa','status','jenis_cta','jenis_body','hooks','pilar','strategy','type_produk','user_client','client_division','user_division','roles','divisions','produk_client','kategori_jasa','users'] as $table) Schema::dropIfExists($table);
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('email')->unique(); $t->string('password'); $t->boolean('isAdmin')->default(false); $t->boolean('is_locked')->default(false); $t->rememberToken(); $t->timestamps(); $t->softDeletes(); });
        Schema::create('oauth_clients', function (Blueprint $t) { $t->uuid('id')->primary(); $t->string('name'); $t->string('provider')->nullable(); $t->text('redirect_uris'); $t->text('grant_types'); $t->boolean('revoked')->default(false); $t->timestamps(); });
        Schema::create('oauth_access_tokens', function (Blueprint $t) { $t->string('id',80)->primary(); $t->unsignedBigInteger('user_id')->nullable(); $t->uuid('client_id'); $t->string('name')->nullable(); $t->text('scopes')->nullable(); $t->boolean('revoked')->default(false); $t->timestamps(); $t->dateTime('expires_at')->nullable(); });
        Schema::create('kategori_jasa', function (Blueprint $t) { $t->id(); $t->string('category'); $t->timestamps(); });
        Schema::create('produk_client', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('kategori_jasa_id')->nullable(); $t->string('nama'); $t->integer('status'); $t->timestamps(); });
        Schema::create('divisions', function (Blueprint $t) { $t->id(); $t->string('name'); $t->timestamps(); });
        Schema::create('roles', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('division_id'); $t->string('role_name'); $t->integer('level')->nullable(); $t->timestamps(); });
        Schema::create('user_division', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('division_id'); $t->unsignedBigInteger('role_id'); $t->timestamps(); });
        Schema::create('client_division', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('client_id'); $t->unsignedBigInteger('division_id'); $t->integer('status')->default(1); $t->timestamps(); });
        Schema::create('user_client', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('client_id'); $t->timestamp('assigned_at'); $t->timestamps(); });
        foreach (['type_produk' => 'type', 'strategy' => 'strategy', 'pilar' => 'pilar', 'hooks' => 'hooks', 'jenis_body' => 'nama_body', 'jenis_cta' => 'nama_cta', 'status' => 'nama_status'] as $table => $label) {
            Schema::create($table, function (Blueprint $t) use ($label) { $t->id(); $t->string($label)->nullable(); });
        }
        Schema::table('pilar', function (Blueprint $t) { $t->unsignedBigInteger('kategori_id')->nullable(); $t->integer('status')->default(1); $t->string('fungsi_singkat')->nullable(); $t->string('contoh')->nullable(); $t->timestamps(); });
        Schema::create('platform_mirror_copies', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('client_id'); $t->string('table_name'); $t->unsignedBigInteger('source_id'); $t->string('source_platform'); $t->unsignedBigInteger('target_id'); $t->string('target_platform'); $t->timestamps(); });
        Schema::create('brand_identify', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('produk_client_id'); $t->string('identify'); $t->string('platform'); $t->timestamp('archived_at')->nullable(); $t->unsignedBigInteger('archived_by')->nullable(); $t->timestamps(); });
        foreach (['analize_4p'=>['nama_analisis','type','platform'],'analize_swot'=>['strenght','weakness','oportunity','threat','platform'],'segmentasi'=>['usia','gender','negara','wilayah','gaya_hidup','status_sosial','minat','penggunaan','loyalitas','manfaat','platform'],'target_audience'=>['indikator','usia','gender','negara','wilayah','pekerjaan','gaya_hidup','status_sosial','penggunaan','manfaat','platform'],'positioning'=>['indikator','deskripsi','platform']] as $table=>$columns) Schema::create($table,function(Blueprint $t)use($columns){$t->id();$t->unsignedBigInteger('produk_client_id');foreach($columns as $column)$t->text($column)->nullable();$t->timestamp('archived_at')->nullable();$t->unsignedBigInteger('archived_by')->nullable();$t->timestamps();});
        foreach (['brand_image' => ['nama_image'], 'brand_value' => ['nama_value'], 'brand_tagline' => ['nama_tagline','nama_hashtagline'], 'gaya_komunikasi' => ['gaya_bicara','gaya_bahasa'], 'komposisi' => ['type_komposisi','komposisi'], 'audio_brand' => ['nama_audio'], 'moodboard' => ['moodboard'], 'alat_branding' => ['nama_alat']] as $table => $columns) {
            Schema::create($table, function (Blueprint $t) use ($columns) { $t->id(); $t->unsignedBigInteger('produk_client_id'); foreach ($columns as $column) $t->string($column)->nullable(); $t->string('platform')->nullable(); $t->timestamps(); });
        }
        Schema::create('data_jasa', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('produk_client_id'); $t->string('judul')->nullable(); $t->string('inspirasi')->nullable(); foreach (['type_produk_id','strategy_id','pilar_id','hooks_id','body_id','cta_id','status_id'] as $c) $t->integer($c)->nullable(); foreach (['durasi','background','komposisi','note','link','platform'] as $c) $t->text($c)->nullable(); $t->date('tanggal_posting')->nullable(); $t->string('content_source')->default('organic'); $t->timestamp('archived_at')->nullable(); $t->integer('archived_by')->nullable(); $t->timestamps(); });
        Schema::create('marketing_campaigns', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('client_id'); $t->unsignedBigInteger('owner_user_id')->nullable(); $t->string('name', 200); $t->string('status', 50); $t->string('platform', 50)->nullable(); $t->string('source', 20)->default('organic'); $t->date('start_date'); $t->date('end_date'); $t->text('objective')->nullable(); $t->decimal('budget', 18, 2)->nullable(); $t->string('currency', 3)->nullable(); $t->text('target')->nullable(); $t->timestamp('archived_at')->nullable(); $t->unsignedBigInteger('archived_by')->nullable(); $t->timestamps(); });
        Schema::create('marketing_approval_events', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('client_id'); $t->string('record_type', 50); $t->unsignedBigInteger('record_id'); $t->string('action', 30); $t->string('status', 30); $t->text('note')->nullable(); $t->uuid('audit_reference')->unique(); $t->timestamp('created_at'); });
        Schema::create('task', function (Blueprint $t) { $t->id(); $t->string('nama_task'); $t->string('jira')->nullable(); $t->unsignedBigInteger('divisi'); $t->unsignedBigInteger('pic'); $t->integer('priority'); $t->integer('menit'); $t->unsignedBigInteger('id_manajement_konten')->nullable(); $t->unsignedBigInteger('id_kpi')->nullable(); $t->date('tanggal_mulai'); $t->date('tanggal_deadline')->nullable(); $t->text('note')->nullable(); $t->integer('status'); $t->unsignedBigInteger('project'); $t->time('jam_mulai')->nullable(); $t->date('tanggal_approve')->nullable(); $t->date('tanggal_revisi')->nullable(); $t->date('tanggal_split')->nullable(); $t->string('completion_proof_path')->nullable(); $t->timestamp('archived_at')->nullable(); $t->unsignedBigInteger('archived_by')->nullable(); $t->timestamps(); });
        Schema::create('marketing_asset_metadata', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('client_id'); $t->unsignedBigInteger('project_id')->nullable(); $t->unsignedBigInteger('campaign_id')->nullable(); $t->unsignedBigInteger('content_plan_id')->nullable(); $t->unsignedBigInteger('created_by'); $t->string('name'); $t->string('asset_type'); $t->string('mime_type')->nullable(); $t->text('description')->nullable(); $t->string('approval_status')->default('draft'); $t->unsignedInteger('current_version')->default(1); $t->timestamp('archived_at')->nullable(); $t->unsignedBigInteger('archived_by')->nullable(); $t->timestamps(); });
        Schema::create('marketing_asset_versions', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('asset_metadata_id'); $t->unsignedInteger('version_number'); $t->string('label')->nullable(); $t->text('note')->nullable(); $t->unsignedBigInteger('created_by'); $t->string('original_name')->nullable(); $t->string('mime_type')->nullable(); $t->unsignedBigInteger('file_size')->nullable(); $t->string('storage_disk')->nullable(); $t->string('storage_path')->nullable(); $t->string('upload_status')->default('metadata_only'); $t->timestamp('created_at'); $t->unique(['asset_metadata_id','version_number']); });
        Schema::create('file_pdf', function (Blueprint $t) { $t->id(); $t->string('nama_file'); $t->unsignedBigInteger('client'); $t->unsignedBigInteger('division_id')->nullable(); $t->unsignedBigInteger('uploaded_by')->nullable(); $t->string('location'); $t->boolean('is_generated_document')->default(false); $t->boolean('is_system_template')->default(false); $t->boolean('is_pinned')->default(false); $t->integer('pin_order')->nullable(); $t->timestamps(); });
        Schema::create('file_pdf_versions', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('file_pdf_id'); $t->unsignedInteger('version_number'); $t->timestamps(); });
    }
}
