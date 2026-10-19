<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;

final class CampaignDatabaseOAuthTest extends ContentPlanDatabaseOAuthTest
{
    public function test_campaign_create_update_archive_list_and_audit_with_database_oauth(): void
    {
        [$bearer, $tokenId] = $this->databaseToken($this->authorizedUser, ['marketing:read', 'marketing:write']);
        $create = $this->campaignPayload();
        $token = $this->campaignConfirmation($bearer, 'create', 0, $create);
        $created = $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/campaigns', $create + ['client_id' => $this->allowedClient, 'confirmation_token' => $token])
            ->assertCreated()->assertJsonPath('meta.operation', 'create')->assertJsonPath('data.name', 'Canonical Launch');
        $id = (int) $created->json('data.id');
        $this->assertDatabaseHas('oauth_access_tokens', ['id' => $tokenId, 'user_id' => $this->authorizedUser->id, 'revoked' => false]);
        $this->assertDatabaseHas('marketing_campaigns', ['id' => $id, 'client_id' => $this->allowedClient, 'name' => 'Canonical Launch']);

        $update = ['name' => 'Canonical Launch Updated', 'status' => 'active'];
        $updateToken = $this->campaignConfirmation($bearer, 'update', $id, $update);
        $this->withToken($bearer)->patchJson("/api/oauth-marketing-crud/campaigns/{$id}", $update + ['client_id' => $this->allowedClient, 'confirmation_token' => $updateToken])
            ->assertOk()->assertJsonPath('data.name', 'Canonical Launch Updated');

        $this->withToken($bearer)->getJson("/api/oauth-marketing-mcp/clients/{$this->allowedClient}/campaigns?start_date=2026-01-01&end_date=2026-12-31")
            ->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('meta.source', 'marketing_campaigns')->assertJsonPath('data.0.campaign_id', $id);

        $archive = ['reason' => 'Campaign completed'];
        $archiveToken = $this->campaignConfirmation($bearer, 'archive', $id, $archive);
        $this->withToken($bearer)->postJson("/api/oauth-marketing-crud/campaigns/{$id}/archive", $archive + ['client_id' => $this->allowedClient, 'confirmation_token' => $archiveToken])
            ->assertOk()->assertJsonPath('data.archived', true);
        $this->assertDatabaseHas('marketing_campaigns', ['id' => $id, 'archived_by' => $this->authorizedUser->id]);
        $this->withToken($bearer)->getJson("/api/oauth-marketing-mcp/clients/{$this->allowedClient}/campaigns?start_date=2026-01-01&end_date=2026-12-31")
            ->assertOk()->assertJsonPath('pagination.total', 0)->assertJsonMissing(['campaign_id' => $id]);

        $this->activityLog->flush();
        $records = array_values(array_filter($this->firestore->records, fn (array $row): bool => ($row['data']['module'] ?? null) === 'campaign'));
        $this->assertCount(3, $records);
        foreach ($records as $record) {
            $this->assertSame((string) $this->authorizedUser->id, $record['data']['user_id']);
            $this->assertSame($this->allowedClient, $record['data']['context']['client_id']);
            $this->assertNotEmpty($record['data']['context']['tool']);
            $this->assertSame((string) $id, $record['data']['target_id']);
            $this->assertNotEmpty($record['data']['context']['operation']);
            $this->assertSame('success', $record['data']['context']['result']);
            $this->assertSame('completed', $record['data']['context']['status']);
            $this->assertNotEmpty($record['data']['context']['occurred_at']);
        }
        $serialized = json_encode($records, JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString($token, $serialized);
        $this->assertDoesNotMatchRegularExpression('/confirmation_token|password|credential|secret|private[_-]?key/i', $serialized);
    }

    public function test_campaign_policy_rejects_wrong_role_division_and_missing_assignment(): void
    {
        foreach ([
            $this->makeUser('Campaign Viewer', 'campaign-viewer@example.test', 'Content Creator', 'Digital Marketing', true),
            $this->makeUser('Campaign Finance', 'campaign-finance@example.test', 'Manager', 'Finance', true),
            $this->makeUser('Campaign Unassigned', 'campaign-unassigned@example.test', 'Digital Marketing Manager', 'Digital Marketing', false),
        ] as $user) {
            $this->app['auth']->forgetGuards();
            [$bearer] = $this->databaseToken($user, ['marketing:write']);
            $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/campaigns/confirmation', ['action' => 'create', 'client_id' => $this->allowedClient, 'payload' => $this->campaignPayload()])->assertForbidden();
        }
    }

    public function test_campaign_confirmation_rejects_invalid_expired_reuse_and_all_mismatches(): void
    {
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:write']);
        $payload = $this->campaignPayload();
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/campaigns', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => str_repeat('x', 64)])->assertStatus(422);

        $expired = $this->campaignConfirmation($bearer, 'create', 0, $payload);
        Cache::forget('marketing_confirmation:'.hash('sha256', $expired));
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/campaigns', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $expired])->assertStatus(422);

        $valid = $this->campaignConfirmation($bearer, 'create', 0, $payload);
        $created = $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/campaigns', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $valid])->assertCreated();
        $id = (int) $created->json('data.id');
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/campaigns', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $valid])->assertStatus(422);

        $payloadMismatch = $this->campaignConfirmation($bearer, 'update', $id, ['name' => 'Expected']);
        $this->withToken($bearer)->patchJson("/api/oauth-marketing-crud/campaigns/{$id}", ['name' => 'Tampered', 'client_id' => $this->allowedClient, 'confirmation_token' => $payloadMismatch])->assertStatus(422);
        $actionMismatch = $this->campaignConfirmation($bearer, 'update', $id, ['name' => 'Expected']);
        $this->withToken($bearer)->postJson("/api/oauth-marketing-crud/campaigns/{$id}/archive", ['reason' => 'Wrong action', 'client_id' => $this->allowedClient, 'confirmation_token' => $actionMismatch])->assertStatus(422);
        $recordMismatch = $this->campaignConfirmation($bearer, 'update', $id, ['name' => 'Expected']);
        $this->withToken($bearer)->patchJson('/api/oauth-marketing-crud/campaigns/'.($id + 1), ['name' => 'Expected', 'client_id' => $this->allowedClient, 'confirmation_token' => $recordMismatch])->assertNotFound();
        $clientMismatch = $this->campaignConfirmation($bearer, 'update', $id, ['name' => 'Expected']);
        $this->withToken($bearer)->patchJson("/api/oauth-marketing-crud/campaigns/{$id}", ['name' => 'Expected', 'client_id' => $this->otherClient, 'confirmation_token' => $clientMismatch])->assertForbidden();

        $second = $this->makeUser('Second Campaign Manager', 'campaign-second@example.test', 'Digital Marketing Manager', 'Digital Marketing', true);
        [$secondBearer] = $this->databaseToken($second, ['marketing:write']);
        $userMismatch = $this->campaignConfirmation($bearer, 'create', 0, $payload);
        $this->app['auth']->forgetGuards();
        $this->withToken($secondBearer)->postJson('/api/oauth-marketing-crud/campaigns', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $userMismatch])->assertStatus(422);
    }

    public function test_campaign_oauth_scope_revocation_expiry_and_response_secrecy(): void
    {
        foreach ([
            [$this->databaseToken($this->authorizedUser, ['marketing:read'])[0], 403],
            [$this->databaseToken($this->authorizedUser, ['marketing:write'], true)[0], 401],
            [$this->databaseToken($this->authorizedUser, ['marketing:write'], false, '-1 minute')[0], 401],
        ] as [$bearer, $expectedStatus]) {
            $this->app['auth']->forgetGuards();
            $response = $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/campaigns/confirmation', ['action' => 'create', 'client_id' => $this->allowedClient, 'payload' => $this->campaignPayload()]);
            $response->assertStatus($expectedStatus);
            $this->assertStringNotContainsString($bearer, $response->getContent());
        }
    }

    private function campaignPayload(): array
    {
        return ['owner_user_id' => $this->authorizedUser->id, 'name' => 'Canonical Launch', 'status' => 'planned', 'platform' => 'instagram', 'source' => 'paid', 'start_date' => '2026-04-01', 'end_date' => '2026-06-30', 'objective' => 'Qualified leads', 'budget' => 15000000, 'currency' => 'IDR', 'target' => '120 leads'];
    }

    private function campaignConfirmation(string $bearer, string $action, int $recordId, array $payload): string
    {
        return $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/campaigns/confirmation', ['action' => $action, 'client_id' => $this->allowedClient, 'record_id' => $recordId, 'payload' => $payload])
            ->assertOk()->json('data.confirmation_token');
    }
}
