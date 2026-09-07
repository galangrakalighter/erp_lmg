<?php

namespace Tests\Unit;

use App\Http\Controllers\ActivityLogController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ActivityLogFilterTest extends TestCase
{
    public function test_it_filters_logs_by_division_and_jakarta_date_range(): void
    {
        $documents = [
            ['data' => ['divisi' => 1, 'division_ids' => [1, 2], 'created_at' => '2026-09-07T03:00:00Z']],
            ['data' => ['divisi' => 3, 'division_ids' => [3], 'created_at' => '2026-09-08T03:00:00Z']],
            ['data' => ['divisi' => 2, 'division_ids' => [2], 'created_at' => '2026-09-09T03:00:00Z']],
        ];

        $method = new ReflectionMethod(ActivityLogController::class, 'filterDocuments');
        $result = $method->invoke(new ActivityLogController, $documents, '2', '2026-09-07', '2026-09-08');

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['data']['divisi']);
    }

    public function test_it_uses_email_when_an_old_log_has_no_division_id(): void
    {
        $documents = [
            ['data' => ['email' => 'mcn@example.com', 'divisi' => null, 'division_ids' => [], 'created_at' => '2026-09-07T03:00:00Z']],
            ['data' => ['email' => 'finance@example.com', 'divisi' => null, 'division_ids' => [], 'created_at' => '2026-09-07T03:00:00Z']],
        ];

        $method = new ReflectionMethod(ActivityLogController::class, 'filterDocuments');
        $result = $method->invoke(
            new ActivityLogController,
            $documents,
            '3',
            '',
            '',
            ['mcn@example.com'],
        );

        $this->assertCount(1, $result);
        $this->assertSame('mcn@example.com', $result[0]['data']['email']);
    }
}
