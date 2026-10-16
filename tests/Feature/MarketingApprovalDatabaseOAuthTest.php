<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class MarketingApprovalDatabaseOAuthTest extends ContentPlanDatabaseOAuthTest
{
    public function test_append_only_submit_revision_resubmit_and_approve_with_audit_and_history(): void
    {
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:read', 'marketing:write']);
        $recordId = $this->campaign();

        $submit = $this->execute($bearer, 'submit_for_approval', $recordId, 'Ready for review')->assertCreated()->assertJsonPath('data.status', 'pending');
        $firstEvent = (int) $submit->json('data.event_id');
        $revision = $this->execute($bearer, 'request_revision', $recordId, 'Clarify the audience and target')->assertCreated()->assertJsonPath('data.status', 'revision_requested');
        $this->execute($bearer, 'submit_for_approval', $recordId, 'Revision completed')->assertCreated()->assertJsonPath('data.status', 'pending');
        $approved = $this->execute($bearer, 'approve', $recordId, 'Approved for execution')->assertCreated()->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseCount('marketing_approval_events', 4);
        $this->assertDatabaseHas('marketing_approval_events', ['id' => $firstEvent, 'action' => 'submit_for_approval', 'status' => 'pending', 'note' => 'Ready for review']);
        $this->assertNotSame($firstEvent, (int) $approved->json('data.event_id'));
        $this->withToken($bearer)->getJson("/api/oauth-marketing-mcp/clients/{$this->allowedClient}/approvals?start_date=2026-01-01&end_date=2026-12-31")
            ->assertOk()->assertJsonPath('pagination.total', 4)->assertJsonPath('meta.source', 'marketing_approval_events')->assertJsonPath('meta.history_availability.append_only', true);

        $this->activityLog->flush();
        $audits = array_values(array_filter($this->firestore->records, fn (array $row): bool => ($row['data']['module'] ?? null) === 'marketing_approval_event'));
        $this->assertCount(4, $audits);
        foreach ($audits as $audit) {
            $this->assertSame((string) $this->authorizedUser->id, $audit['data']['user_id']);
            $this->assertSame($this->allowedClient, $audit['data']['context']['client_id']);
            $this->assertSame($recordId, $audit['data']['context']['record_id']);
            $this->assertNotEmpty($audit['data']['context']['tool']);
            $this->assertNotEmpty($audit['data']['context']['operation']);
            $this->assertSame('success', $audit['data']['context']['result']);
            $this->assertNotEmpty($audit['data']['context']['status']);
            $this->assertNotEmpty($audit['data']['context']['audit_reference']);
            $this->assertNotEmpty($audit['data']['context']['occurred_at']);
        }
        $serialized = json_encode($audits, JSON_THROW_ON_ERROR);
        $this->assertDoesNotMatchRegularExpression('/confirmation_token|password|credential|secret|private[_-]?key/i', $serialized);
    }

    public function test_reject_appends_new_event_and_never_updates_submission(): void
    {
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:write']);
        $recordId = $this->campaign();
        $this->execute($bearer, 'submit_for_approval', $recordId)->assertCreated();
        $submitted = (array) DB::table('marketing_approval_events')->first();
        $this->execute($bearer, 'reject', $recordId, 'Objective is not measurable')->assertCreated()->assertJsonPath('data.status', 'rejected');
        $this->assertSame($submitted, (array) DB::table('marketing_approval_events')->where('id', $submitted['id'])->first());
        $this->assertDatabaseCount('marketing_approval_events', 2);
    }

    public function test_approval_permissions_division_and_client_assignment_are_enforced(): void
    {
        $recordId = $this->campaign();
        $staff = $this->makeUser('Approval Staff', 'approval-staff@example.test', 'Digital Marketing', 'Digital Marketing', true);
        [$staffBearer] = $this->databaseToken($staff, ['marketing:write']);
        $this->execute($staffBearer, 'submit_for_approval', $recordId)->assertCreated();
        $this->app['auth']->forgetGuards();
        $this->approvalConfirmation($staffBearer, 'approve', $recordId)->assertForbidden();

        foreach ([
            $this->makeUser('Approval Finance', 'approval-finance@example.test', 'Manager', 'Finance', true),
            $this->makeUser('Approval Unassigned', 'approval-unassigned@example.test', 'Digital Marketing Manager', 'Digital Marketing', false),
        ] as $user) {
            $this->app['auth']->forgetGuards();
            [$token] = $this->databaseToken($user, ['marketing:write']);
            $this->approvalConfirmation($token, 'submit_for_approval', $recordId)->assertForbidden();
        }
    }

    public function test_confirmation_rejects_invalid_expired_reuse_and_user_client_record_action_note_mismatch(): void
    {
        [$bearer] = $this->databaseToken($this->authorizedUser, ['marketing:write']);
        $recordId = $this->campaign();
        $base = ['client_id' => $this->allowedClient, 'record_type' => 'campaign', 'record_id' => $recordId];
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/approvals/submit', $base + ['confirmation_token' => str_repeat('x', 64)])->assertStatus(422);

        $expired = $this->token($bearer, 'submit_for_approval', $recordId);
        Cache::forget('marketing_confirmation:'.hash('sha256', $expired));
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/approvals/submit', $base + ['confirmation_token' => $expired])->assertStatus(422);

        $valid = $this->token($bearer, 'submit_for_approval', $recordId);
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/approvals/submit', $base + ['confirmation_token' => $valid])->assertCreated();
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/approvals/submit', $base + ['confirmation_token' => $valid])->assertStatus(422);

        $noteMismatch = $this->token($bearer, 'request_revision', $recordId, 'Expected note');
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/approvals/request-revision', $base + ['note' => 'Changed note', 'confirmation_token' => $noteMismatch])->assertStatus(422);
        $actionMismatch = $this->token($bearer, 'approve', $recordId);
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/approvals/reject', $base + ['note' => 'Wrong action', 'confirmation_token' => $actionMismatch])->assertStatus(422);

        $otherRecord = $this->campaign('Second campaign');
        $recordMismatch = $this->token($bearer, 'approve', $recordId);
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/approvals/approve', ['client_id' => $this->allowedClient, 'record_type' => 'campaign', 'record_id' => $otherRecord, 'confirmation_token' => $recordMismatch])->assertStatus(422);
        $clientMismatch = $this->token($bearer, 'approve', $recordId);
        $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/approvals/approve', ['client_id' => $this->otherClient, 'record_type' => 'campaign', 'record_id' => $recordId, 'confirmation_token' => $clientMismatch])->assertForbidden();

        $other = $this->makeUser('Other Approval Manager', 'approval-other@example.test', 'Digital Marketing Manager', 'Digital Marketing', true);
        [$otherBearer] = $this->databaseToken($other, ['marketing:write']);
        $userMismatch = $this->token($bearer, 'approve', $recordId);
        $this->app['auth']->forgetGuards();
        $this->withToken($otherBearer)->postJson('/api/oauth-marketing-crud/approvals/approve', $base + ['confirmation_token' => $userMismatch])->assertStatus(422);
    }

    private function campaign(string $name = 'Approval campaign'): int
    {
        return DB::table('marketing_campaigns')->insertGetId(['client_id' => $this->allowedClient, 'owner_user_id' => $this->authorizedUser->id, 'name' => $name, 'status' => 'planned', 'source' => 'organic', 'start_date' => '2026-04-01', 'end_date' => '2026-06-30', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function approvalConfirmation(string $bearer, string $action, int $recordId, ?string $note = null)
    {
        return $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/approvals/confirmation', array_filter(['action' => $action, 'client_id' => $this->allowedClient, 'record_type' => 'campaign', 'record_id' => $recordId, 'note' => $note], fn ($value) => $value !== null));
    }

    private function token(string $bearer, string $action, int $recordId, ?string $note = null): string
    {
        return $this->approvalConfirmation($bearer, $action, $recordId, $note)->assertOk()->json('data.confirmation_token');
    }

    private function execute(string $bearer, string $action, int $recordId, ?string $note = null)
    {
        $token = $this->token($bearer, $action, $recordId, $note);
        $path = match ($action) { 'submit_for_approval' => 'submit', 'request_revision' => 'request-revision', default => $action };

        return $this->withToken($bearer)->postJson('/api/oauth-marketing-crud/approvals/'.$path, array_filter(['client_id' => $this->allowedClient, 'record_type' => 'campaign', 'record_id' => $recordId, 'note' => $note, 'confirmation_token' => $token], fn ($value) => $value !== null));
    }
}
