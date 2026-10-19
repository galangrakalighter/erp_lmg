<?php

namespace App\Console\Commands;

use App\Models\CRM_HUMAN;
use Illuminate\Console\Command;

class ResetCrmHumanFollowUpToday extends Command
{
    protected $signature = 'crm:reset-follow-up-today';

    protected $description = 'Membuka kembali checklist follow up harian CRM Human';

    public function handle(): int
    {
        $updated = CRM_HUMAN::query()
            ->where('today', true)
            ->update(['today' => false]);

        $this->info("Checklist follow up harian direset: {$updated} data.");

        return self::SUCCESS;
    }
}
