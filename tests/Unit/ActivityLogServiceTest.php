<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class ActivityLogServiceTest extends TestCase
{
    public function test_it_writes_sanitized_user_activity_to_firestore(): void
    {
        config(['firestore.activity_log_collection' => 'logs']);

        $request = Request::create('/clients', 'POST', server: [
            'REMOTE_ADDR' => '192.168.1.10',
            'HTTP_USER_AGENT' => 'Test Browser',
        ]);
        $user = new User;
        $user->forceFill([
            'id' => 123,
            'name' => 'Test User',
            'email' => 'user@example.com',
            'role' => 'admin',
            'divisi' => '[2]',
        ]);

        $firestore = Mockery::mock(FirestoreService::class);
        $firestore->shouldReceive('create')
            ->once()
            ->with('logs', Mockery::on(function (array $log): bool {
                return $log['action'] === 'CREATE'
                    && $log['user_id'] === '123'
                    && $log['divisi'] === 2
                    && $log['ip_address'] === '192.168.1.10'
                    && $log['new_data']['password'] === '[REDACTED]';
            }));

        $service = new ActivityLogService($firestore, $request);
        $service->record([
            'action' => 'CREATE',
            'module' => 'clients',
            'description' => 'Menambahkan client',
            'new_data' => ['name' => 'Client A', 'password' => 'secret-value'],
        ], $user);
        $service->flush();

        $this->addToAssertionCount(1);
    }
}
