<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class MarketingAssetMetadataDatabaseOAuthTest extends ContentPlanDatabaseOAuthTest
{
    public function test_private_pdf_upload_creates_metadata_and_increments_versions_safely(): void
    {
        Storage::fake('local');
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:write']);
        $first = $this->upload($bearer, ['client_id' => $this->allowedClient, 'name' => 'Private Brief', 'file_name' => 'brief.pdf'], $this->pdf('brief.pdf'))
            ->assertCreated()->assertJsonPath('data.mime', 'application/pdf')->assertJsonPath('data.version', 1);
        $id = (int) $first->json('data.document_id');
        $this->assertDatabaseHas('marketing_asset_metadata', ['id' => $id, 'client_id' => $this->allowedClient, 'asset_type' => 'document', 'current_version' => 1]);
        $this->assertDatabaseHas('marketing_asset_versions', ['asset_metadata_id' => $id, 'version_number' => 1, 'upload_status' => 'uploaded']);

        $this->upload($bearer, ['client_id' => $this->allowedClient, 'document_id' => $id, 'file_name' => 'brief-v2.pdf'], $this->pdf('brief-v2.pdf'))
            ->assertCreated()->assertJsonPath('data.version', 2)->assertJsonMissingPath('data.storage_path');
        $this->assertDatabaseHas('marketing_asset_metadata', ['id' => $id, 'current_version' => 2]);

        $this->activityLog->flush();
        $audit = collect($this->firestore->records)->first(fn (array $row): bool => ($row['data']['context']['operation'] ?? null) === 'upload');
        $this->assertNotNull($audit);
        $this->assertSame('marketing_upload_document_file', $audit['data']['context']['tool']);
        $serialized = json_encode($audit, JSON_THROW_ON_ERROR);
        $this->assertDoesNotMatchRegularExpression('/storage_path|confirmation_token|credential|secret|private[_-]?key|%PDF|binary/i', $serialized);
    }

    public function test_pdf_upload_rejects_invalid_empty_cross_client_and_failed_storage_without_metadata(): void
    {
        Storage::fake('local');
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:write']);
        $count = DB::table('marketing_asset_metadata')->count();
        $this->upload($bearer, ['client_id' => $this->allowedClient, 'name' => 'Bad', 'file_name' => 'bad.pdf'], UploadedFile::fake()->createWithContent('bad.pdf', 'not pdf'))->assertStatus(422);
        $this->upload($bearer, ['client_id' => $this->allowedClient, 'name' => 'Empty', 'file_name' => 'empty.pdf'], UploadedFile::fake()->createWithContent('empty.pdf', ''))->assertStatus(422);
        $this->assertSame($count, DB::table('marketing_asset_metadata')->count());

        $foreign = DB::table('marketing_asset_metadata')->insertGetId(['client_id' => $this->otherClient, 'created_by' => $this->authorizedUser->id, 'name' => 'Foreign', 'asset_type' => 'document', 'approval_status' => 'draft', 'current_version' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $this->upload($bearer, ['client_id' => $this->allowedClient, 'document_id' => $foreign, 'file_name' => 'x.pdf'], $this->pdf('x.pdf'))->assertForbidden();

        config(['marketing-mcp.document_storage_disk' => 'missing-testing-disk']);
        $this->upload($bearer, ['client_id' => $this->allowedClient, 'name' => 'Failed', 'file_name' => 'failed.pdf'], $this->pdf('failed.pdf'))->assertStatus(500);
        $this->assertDatabaseMissing('marketing_asset_metadata', ['name' => 'Failed']);

        config(['marketing-mcp.document_storage_disk' => 'local']);
        $collision = DB::table('marketing_asset_metadata')->insertGetId(['client_id' => $this->allowedClient, 'created_by' => $this->authorizedUser->id, 'name' => 'Collision', 'asset_type' => 'document', 'approval_status' => 'draft', 'current_version' => 0, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('marketing_asset_versions')->insert(['asset_metadata_id' => $collision, 'version_number' => 1, 'label' => 'reserved', 'created_by' => $this->authorizedUser->id, 'created_at' => now()]);
        $this->upload($bearer, ['client_id' => $this->allowedClient, 'document_id' => $collision, 'file_name' => 'mid-transaction.pdf'], $this->pdf('mid-transaction.pdf'))->assertStatus(500);
        $this->assertSame(0, DB::table('marketing_asset_metadata')->where('id', $collision)->value('current_version'));
        $this->assertSame(1, DB::table('marketing_asset_versions')->where('asset_metadata_id', $collision)->count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_pdf_upload_rejects_oversize_and_user_without_client_access(): void
    {
        Storage::fake('local');
        config(['marketing-mcp.document_upload_max_kb' => 1]);
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:write']);
        $large = UploadedFile::fake()->createWithContent('large.pdf', "%PDF-1.4\n".str_repeat('A', 2048)."\n%%EOF");
        $this->upload($bearer, ['client_id' => $this->allowedClient, 'name' => 'Large', 'file_name' => 'large.pdf'], $large)->assertStatus(422);
        $this->assertDatabaseMissing('marketing_asset_metadata', ['name' => 'Large']);

        config(['marketing-mcp.document_upload_max_kb' => 20480]);
        $user = $this->makeUser('No Upload Client', 'no-upload-client@example.test', 'Digital Marketing', 'Digital Marketing', false);
        $this->app['auth']->forgetGuards();
        [$restricted] = $this->databaseToken($user, ['marketing:write']);
        $this->upload($restricted, ['client_id' => $this->allowedClient, 'name' => 'Forbidden', 'file_name' => 'forbidden.pdf'], $this->pdf('forbidden.pdf'))->assertForbidden();
        $this->assertDatabaseMissing('marketing_asset_metadata', ['name' => 'Forbidden']);
    }
    public function test_metadata_create_version_update_approval_archive_and_safe_read(): void
    {
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:read', 'marketing:write']);
        $campaign = $this->campaign($this->allowedClient);
        $payload = ['project_id' => $this->allowedClient, 'campaign_id' => $campaign, 'name' => 'Campaign Key Visual', 'asset_type' => 'asset', 'mime_type' => 'image/png', 'description' => 'Metadata only', 'version_label' => 'v1', 'version_note' => 'Initial metadata'];
        $created = $this->assetMutation($bearer, 'create', 0, $payload)->assertCreated()->assertJsonPath('data.current_version', 1)->assertJsonCount(1, 'data.versions');
        $id = (int) $created->json('data.id');
        $this->assertDatabaseHas('marketing_asset_metadata', ['id' => $id, 'client_id' => $this->allowedClient, 'approval_status' => 'draft']);
        $this->assertDatabaseHas('marketing_asset_versions', ['asset_metadata_id' => $id, 'version_number' => 1]);

        $update = ['name' => 'Campaign Key Visual Final', 'description' => 'Approved candidate metadata', 'version_label' => 'v2', 'version_note' => 'Copy and composition revised'];
        $this->assetMutation($bearer, 'update', $id, $update)->assertOk()->assertJsonPath('data.current_version', 2)->assertJsonCount(2, 'data.versions');
        $this->assertDatabaseHas('marketing_asset_versions', ['asset_metadata_id' => $id, 'version_number' => 2, 'note' => 'Copy and composition revised']);

        $confirmation = $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/approvals/confirmation', ['action' => 'submit_for_approval', 'client_id' => $this->allowedClient, 'record_type' => 'asset_metadata', 'record_id' => $id])->assertOk()->json('data.confirmation_token');
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/approvals/submit', ['client_id' => $this->allowedClient, 'record_type' => 'asset_metadata', 'record_id' => $id, 'confirmation_token' => $confirmation])->assertCreated();
        $this->assertDatabaseHas('marketing_asset_metadata', ['id' => $id, 'approval_status' => 'pending']);

        $this->withToken($bearer)->getJson("/api/oauth-marketing-mcp/clients/{$this->allowedClient}/assets")
            ->assertOk()->assertJsonFragment(['id' => $id, 'approval_status' => 'pending', 'current_version' => 2, 'metadata_source' => 'marketing_asset_metadata'])
            ->assertJsonMissingPath('data.0.location')->assertJsonMissing(['private/path'])->assertJsonPath('meta.binary_operations', 'not_supported');

        $this->assetMutation($bearer, 'archive', $id, ['reason' => 'Metadata superseded'])->assertOk()->assertJsonPath('data.archived', true);
        $this->withToken($bearer)->getJson("/api/oauth-marketing-mcp/clients/{$this->allowedClient}/assets")->assertOk()->assertJsonMissing(['id' => $id]);

        $this->activityLog->flush();
        $audits = array_values(array_filter($this->firestore->records, fn (array $row): bool => ($row['data']['module'] ?? null) === 'marketing_asset_metadata'));
        $this->assertCount(3, $audits);
        $serialized = json_encode($audits, JSON_THROW_ON_ERROR);
        $this->assertDoesNotMatchRegularExpression('/confirmation_token|password|credential|secret|private[_-]?key|location|storage[_-]?path|binary/i', $serialized);
    }

    public function test_metadata_permission_division_assignment_relations_and_client_tampering(): void
    {
        $payload = ['project_id' => $this->allowedClient, 'name' => 'Brief', 'asset_type' => 'document', 'mime_type' => 'application/pdf'];
        foreach ([
            $this->makeUser('Asset Viewer', 'asset-viewer@example.test', 'Content Creator', 'Digital Marketing', true),
            $this->makeUser('Asset Finance', 'asset-finance@example.test', 'Manager', 'Finance', true),
            $this->makeUser('Asset Unassigned', 'asset-unassigned@example.test', 'Digital Marketing Manager', 'Digital Marketing', false),
        ] as $user) {
            $this->app['auth']->forgetGuards(); [$token] = $this->databaseToken($user, ['marketing:write']);
            $this->assetConfirmation($token, 'create', 0, $payload)->assertForbidden();
        }
        $this->app['auth']->forgetGuards(); [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:write']);
        $wrongCampaign = $this->campaign($this->otherClient);
        $bad = $payload + ['campaign_id' => $wrongCampaign];
        $token = $this->assetToken($bearer, 'create', 0, $bad);
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/assets', $bad + ['client_id' => $this->allowedClient, 'confirmation_token' => $token])->assertStatus(422);
        $projectMismatch = $payload; $projectMismatch['project_id'] = $this->otherClient;
        $token = $this->assetToken($bearer, 'create', 0, $projectMismatch);
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/assets', $projectMismatch + ['client_id' => $this->allowedClient, 'confirmation_token' => $token])->assertStatus(422);
    }

    public function test_metadata_confirmation_rejects_invalid_expired_reuse_payload_and_client_mismatch(): void
    {
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:write']);
        $payload = ['name' => 'Metadata', 'asset_type' => 'document'];
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/assets', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => str_repeat('x', 64)])->assertStatus(422);
        $expired = $this->assetToken($bearer, 'create', 0, $payload); Cache::forget('marketing_confirmation:'.hash('sha256', $expired));
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/assets', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $expired])->assertStatus(422);
        $valid = $this->assetToken($bearer, 'create', 0, $payload);
        $created = $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/assets', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $valid])->assertCreated();
        $id = (int) $created->json('data.id');
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/assets', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $valid])->assertStatus(422);
        $mismatch = $this->assetToken($bearer, 'update', $id, ['name' => 'Expected']);
        $this->withToken($bearer)->patchJson("/api/oauth-marketing-crud/assets/{$id}", ['name' => 'Tampered', 'client_id' => $this->allowedClient, 'confirmation_token' => $mismatch])->assertStatus(422);
        $client = $this->assetToken($bearer, 'update', $id, ['name' => 'Expected']);
        $this->withToken($bearer)->patchJson("/api/oauth-marketing-crud/assets/{$id}", ['name' => 'Expected', 'client_id' => $this->otherClient, 'confirmation_token' => $client])->assertForbidden();
    }

    private function campaign(int $client): int { return DB::table('marketing_campaigns')->insertGetId(['client_id' => $client, 'owner_user_id' => $this->authorizedUser->id, 'name' => 'Asset campaign '.$client, 'status' => 'planned', 'source' => 'organic', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'created_at' => now(), 'updated_at' => now()]); }
    private function pdf(string $name): UploadedFile { return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF"); }
    private function upload(string $bearer, array $data, UploadedFile $file) { return $this->withToken($bearer)->post('/api/oauth-marketing-crud/assets/upload-document', $data + ['file' => $file], ['Accept' => 'application/json']); }
    private function assetConfirmation(string $bearer,string $action,int $id,array $payload){return $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/assets/confirmation',['action'=>$action,'client_id'=>$this->allowedClient,'record_id'=>$id,'payload'=>$payload]);}
    private function assetToken(string $bearer,string $action,int $id,array $payload):string{return $this->assetConfirmation($bearer,$action,$id,$payload)->assertOk()->json('data.confirmation_token');}
    private function assetMutation(string $bearer,string $action,int $id,array $payload){$token=$this->assetToken($bearer,$action,$id,$payload);$path=$action==='create'?'/api/oauth-marketing-crud/assets':($action==='update'?"/api/oauth-marketing-crud/assets/{$id}":"/api/oauth-marketing-crud/assets/{$id}/archive");$data=$payload+['client_id'=>$this->allowedClient,'confirmation_token'=>$token];return $action==='update'?$this->withToken($bearer)->patchJson($path,$data):$this->withToken($bearer)->postJson($path,$data);}
}
