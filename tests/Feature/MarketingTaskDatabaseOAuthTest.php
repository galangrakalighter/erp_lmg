<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class MarketingTaskDatabaseOAuthTest extends ContentPlanDatabaseOAuthTest
{
    public function test_task_full_mutation_flow_and_archive_visibility_with_database_oauth(): void
    {
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:read', 'marketing:write']);
        $payload = $this->createPayloadForTask($this->authorizedUser->id);
        $created = $this->mutate($bearer, 'create', 0, $payload)->assertCreated()->assertJsonPath('data.status', 0);
        $id = (int) $created->json('data.id');

        $this->mutate($bearer, 'update', $id, ['name' => 'Updated MCP task', 'priority' => 3])->assertOk()->assertJsonPath('data.nama_task', 'Updated MCP task');
        $secondPic = $this->makeUser('Second Task PIC', 'task-pic@example.test', 'Digital Marketing', 'Digital Marketing', true);
        $this->mutate($bearer, 'change_pic', $id, ['pic_id' => $secondPic->id])->assertOk()->assertJsonPath('data.pic_id', $secondPic->id);
        $this->mutate($bearer, 'change_deadline', $id, ['deadline' => '2026-07-15'])->assertOk()->assertJsonPath('data.deadline', '2026-07-15');
        $this->mutate($bearer, 'change_status', $id, ['status' => 1])->assertOk()->assertJsonPath('data.status', 1);
        Storage::fake('local');
        Storage::disk('local')->put('testing/proof.pdf', 'safe test proof');
        DB::table('task')->where('id', $id)->update(['completion_proof_path' => 'testing/proof.pdf']);
        $this->mutate($bearer, 'change_status', $id, ['status' => 3])->assertOk()->assertJsonPath('data.status', 3);
        $this->mutate($bearer, 'complete', $id, ['note' => 'Evidence reviewed'])->assertOk()->assertJsonPath('data.status', 4)->assertJsonMissing(['testing/proof.pdf']);
        $this->mutate($bearer, 'archive', $id, ['reason' => 'Task lifecycle completed'])->assertOk()->assertJsonPath('data.archived', true);

        $this->assertDatabaseHas('task', ['id' => $id, 'status' => 4, 'archived_by' => $this->authorizedUser->id]);
        $this->withToken($bearer)->getJson("/api/oauth-marketing-mcp/clients/{$this->allowedClient}/tasks?start_date=2026-01-01&end_date=2026-12-31")
            ->assertOk()->assertJsonPath('pagination.total', 0)->assertJsonMissing(['id' => $id]);

        $this->activityLog->flush();
        $audits = array_values(array_filter($this->firestore->records, fn (array $row): bool => ($row['data']['module'] ?? null) === 'marketing_task'));
        $this->assertCount(8, $audits);
        foreach ($audits as $audit) {
            $this->assertSame((string) $this->authorizedUser->id, $audit['data']['user_id']);
            $this->assertSame($this->allowedClient, $audit['data']['context']['client_id']);
            $this->assertNotEmpty($audit['data']['context']['tool']);
            $this->assertNotEmpty($audit['data']['context']['operation']);
            $this->assertSame('success', $audit['data']['context']['result']);
            $this->assertNotEmpty($audit['data']['context']['occurred_at']);
        }
        $serialized = json_encode($audits, JSON_THROW_ON_ERROR);
        $this->assertDoesNotMatchRegularExpression('/confirmation_token|password|credential|secret|private[_-]?key|completion_proof_path|testing\/proof/i', $serialized);
    }

    public function test_task_policy_enforces_role_division_assignment_pic_and_client(): void
    {
        foreach ([
            $this->makeUser('Task Viewer', 'task-viewer@example.test', 'Content Creator', 'Digital Marketing', true),
            $this->makeUser('Task Finance', 'task-finance@example.test', 'Manager', 'Finance', true),
            $this->makeUser('Task Unassigned', 'task-unassigned@example.test', 'Digital Marketing Manager', 'Digital Marketing', false),
        ] as $user) {
            $this->app['auth']->forgetGuards(); [$token] = $this->databaseToken($user, ['marketing:write']);
            $this->confirmationForTask($token, 'create', 0, $this->createPayloadForTask($this->authorizedUser->id))->assertForbidden();
        }

        $this->app['auth']->forgetGuards(); [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:write']);
        $invalidPic = $this->makeUser('No Client PIC', 'no-client-pic@example.test', 'Digital Marketing', 'Digital Marketing', false);
        $this->app['auth']->forgetGuards();
        $token = $this->tokenForTask($bearer, 'create', 0, $this->createPayloadForTask($invalidPic->id));
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/tasks', $this->createPayloadForTask($invalidPic->id) + ['client_id' => $this->allowedClient, 'confirmation_token' => $token])->assertStatus(422);

        $valid = $this->createPayloadForTask($this->authorizedUser->id); $createToken = $this->tokenForTask($bearer, 'create', 0, $valid);
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/tasks', $valid + ['client_id' => $this->otherClient, 'confirmation_token' => $createToken])->assertForbidden();
    }

    public function test_task_confirmation_rejects_invalid_expired_reuse_and_mismatch(): void
    {
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:write']);
        $payload = $this->createPayloadForTask($this->authorizedUser->id);
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/tasks', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => str_repeat('x', 64)])->assertStatus(422);
        $expired = $this->tokenForTask($bearer, 'create', 0, $payload); Cache::forget('marketing_confirmation:'.hash('sha256', $expired));
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/tasks', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $expired])->assertStatus(422);
        $valid = $this->tokenForTask($bearer, 'create', 0, $payload);
        $created = $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/tasks', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $valid])->assertCreated();
        $id = (int) $created->json('data.id');
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/tasks', $payload + ['client_id' => $this->allowedClient, 'confirmation_token' => $valid])->assertStatus(422);
        $payloadToken = $this->tokenForTask($bearer, 'update', $id, ['name' => 'Expected']);
        $this->withToken($bearer)->patchJson("/api/oauth-marketing-crud/tasks/{$id}", ['name' => 'Tampered', 'client_id' => $this->allowedClient, 'confirmation_token' => $payloadToken])->assertStatus(422);
        $actionToken = $this->tokenForTask($bearer, 'change_status', $id, ['status' => 1]);
        $this->withToken($bearer)->postJson("/api/oauth-marketing-crud/tasks/{$id}/change-deadline", ['deadline' => '2026-07-01', 'client_id' => $this->allowedClient, 'confirmation_token' => $actionToken])->assertStatus(422);
        $clientToken = $this->tokenForTask($bearer, 'update', $id, ['name' => 'Expected']);
        $this->withToken($bearer)->patchJson("/api/oauth-marketing-crud/tasks/{$id}", ['name' => 'Expected', 'client_id' => $this->otherClient, 'confirmation_token' => $clientToken])->assertForbidden();
    }

    private function createPayloadForTask(int $pic): array { return ['name' => 'MCP task', 'pic_id' => $pic, 'priority' => 2, 'estimated_minutes' => 60, 'start_date' => '2026-06-01', 'deadline' => '2026-06-30', 'start_time' => '09:00', 'note' => 'Safe task note']; }
    private function confirmationForTask(string $bearer, string $action, int $id, array $payload) { return $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/tasks/confirmation', ['action' => $action, 'client_id' => $this->allowedClient, 'record_id' => $id, 'payload' => $payload]); }
    private function tokenForTask(string $bearer, string $action, int $id, array $payload): string { return $this->confirmationForTask($bearer, $action, $id, $payload)->assertOk()->json('data.confirmation_token'); }
    private function mutate(string $bearer, string $action, int $id, array $payload) { $token = $this->tokenForTask($bearer, $action, $id, $payload); $path = match($action){'create'=>'/api/oauth-marketing-crud/tasks','update'=>"/api/oauth-marketing-crud/tasks/{$id}",'archive'=>"/api/oauth-marketing-crud/tasks/{$id}/archive",'change_pic'=>"/api/oauth-marketing-crud/tasks/{$id}/change-pic",'change_deadline'=>"/api/oauth-marketing-crud/tasks/{$id}/change-deadline",'change_status'=>"/api/oauth-marketing-crud/tasks/{$id}/change-status",'complete'=>"/api/oauth-marketing-crud/tasks/{$id}/complete"}; $data=$payload+['client_id'=>$this->allowedClient,'confirmation_token'=>$token]; return $action==='update'?$this->withToken($bearer)->patchJson($path,$data):$this->withToken($bearer)->postJson($path,$data); }
}
