<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class DailyResetScheduleTest extends TestCase
{
    public function test_daily_task_reset_is_scheduled_at_one_am_jakarta_time(): void
    {
        $event = collect($this->app->make(Schedule::class)->events())
            ->first(fn ($event) => str_contains($event->command ?? '', 'task:reset-daily'));

        $this->assertNotNull($event);
        $this->assertSame('0 1 * * *', $event->expression);
        $this->assertSame('Asia/Jakarta', $event->timezone);
    }
}
