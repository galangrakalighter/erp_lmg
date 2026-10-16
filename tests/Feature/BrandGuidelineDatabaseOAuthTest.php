<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;

final class BrandGuidelineDatabaseOAuthTest extends ContentPlanDatabaseOAuthTest
{
    public function test_brand_guideline_policy_rejects_wrong_permission_division_and_missing_client_assignment(): void
    {
        foreach ([
            $this->makeUser('Brand Viewer', 'brand-viewer@example.test', 'Content Creator', 'Digital Marketing', true),
            $this->makeUser('Other Division', 'brand-finance@example.test', 'Manager', 'Finance', true),
            $this->makeUser('Unassigned Brand', 'brand-unassigned@example.test', 'Digital Marketing', 'Digital Marketing', false),
        ] as $user) {
            [$bearer] = $this->databaseToken($user, ['marketing:write']);
            $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/brand-guidelines/confirmation', [
                'action' => 'create', 'client_id' => $this->allowedClient, 'payload' => ['identify' => 'Denied', 'platform' => 'Instagram'],
            ])->assertForbidden();
        }
    }

    public function test_brand_guideline_create_update_archive_and_audit_with_database_oauth(): void
    {
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:read', 'marketing:write']);
        $create = ['identify' => 'Trusted and practical', 'platform' => 'Instagram'];
        $confirmation = $this->brandConfirmation($bearer, 'create', 0, $create);
        $response = $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/brand-guidelines', $create + ['client_id' => $this->allowedClient, 'confirmation_token' => $confirmation])
            ->assertCreated()->assertJsonPath('meta.operation', 'create');
        $id = (int) $response->json('data.id');

        $update = ['identify' => 'Trusted, clear, and practical', 'platform' => 'Instagram'];
        $updateToken = $this->brandConfirmation($bearer, 'update', $id, $update);
        $this->withToken($bearer)->patchJson("/api/oauth-marketing-crud/brand-guidelines/{$id}", $update + ['client_id' => $this->allowedClient, 'confirmation_token' => $updateToken])
            ->assertOk()->assertJsonPath('data.identify', $update['identify']);

        $archive = ['reason' => 'Guideline superseded'];
        $archiveToken = $this->brandConfirmation($bearer, 'archive', $id, $archive);
        $this->withToken($bearer)->postJson("/api/oauth-marketing-crud/brand-guidelines/{$id}/archive", $archive + ['confirmation_token' => $archiveToken])
            ->assertOk()->assertJsonPath('data.archived', true);
        $this->assertDatabaseHas('brand_identify', ['id' => $id, 'archived_by' => $this->authorizedUser->id]);
        $this->withToken($bearer)->getJson("/api/oauth-marketing-mcp/clients/{$this->allowedClient}/brand-guidelines")
            ->assertOk()->assertJsonMissing(['id' => $id]);

        $this->activityLog->flush();
        $records = array_values(array_filter($this->firestore->records, fn (array $row): bool => ($row['data']['module'] ?? null) === 'brand_guideline'));
        $this->assertCount(3, $records);
        foreach ($records as $record) {
            $this->assertSame((string) $this->authorizedUser->id, $record['data']['user_id']);
            $this->assertSame($this->allowedClient, $record['data']['context']['client_id']);
            $this->assertSame('success', $record['data']['context']['result']);
            $this->assertNotEmpty($record['data']['context']['occurred_at']);
        }
        $serialized = json_encode($records, JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString($confirmation, $serialized);
        $this->assertDoesNotMatchRegularExpression('/password|credential|private[_-]?key/i', $serialized);
    }

    public function test_brand_confirmation_rejects_invalid_expired_reuse_payload_and_client_tampering(): void
    {
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:write']);
        $payload = ['identify' => 'Brand identity', 'platform' => 'Instagram'];
        $token = $this->brandConfirmation($bearer, 'create', 0, $payload);
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/brand-guidelines', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => str_repeat('x', 64)])->assertStatus(422);
        $created = $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/brand-guidelines', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $token])->assertCreated();
        $id = (int) $created->json('data.id');
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/brand-guidelines', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $token])->assertStatus(422);
        $mismatch = $this->brandConfirmation($bearer, 'create', 0, $payload);
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/brand-guidelines', ['identify' => 'Changed', 'platform' => 'Instagram', 'client_id' => $this->allowedClient, 'confirmation_token' => $mismatch])->assertStatus(422);
        $expired = $this->brandConfirmation($bearer, 'create', 0, $payload);
        Cache::forget('marketing_confirmation:'.hash('sha256', $expired));
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/brand-guidelines', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $expired])->assertStatus(422);
        $update = ['identify' => 'Wrong client', 'platform' => 'Instagram'];
        $updateToken = $this->brandConfirmation($bearer, 'update', $id, $update);
        $this->withToken($bearer)->patchJson("/api/oauth-marketing-crud/brand-guidelines/{$id}", $update + ['client_id' => $this->otherClient, 'confirmation_token' => $updateToken])->assertForbidden();
    }

    private function brandConfirmation(string $bearer, string $action, int $recordId, array $payload): string
    {
        return $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/brand-guidelines/confirmation', ['action' => $action, 'client_id' => $this->allowedClient, 'record_id' => $recordId, 'payload' => $payload])
            ->assertOk()->json('data.confirmation_token');
    }
}
