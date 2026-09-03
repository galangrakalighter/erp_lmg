<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use App\Services\LedgerService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CarryForwardOpeningBalances extends Command
{
    protected $signature = 'finance:carry-forward-opening-balances
                            {--period= : Periode tujuan dalam format YYYY-MM}
                            {--dry-run : Hitung tanpa menyimpan perubahan}';

    protected $description = 'Jadikan saldo akhir bulan sebelumnya sebagai saldo awal bulan baru';

    public function handle(LedgerService $ledgerService): int
    {
        $targetPeriod = (string) ($this->option('period')
            ?: now('Asia/Jakarta')->format('Y-m'));

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $targetPeriod)) {
            $this->error('Periode harus memakai format YYYY-MM.');
            return self::FAILURE;
        }

        $previousPeriod = Carbon::createFromFormat(
            '!Y-m',
            $targetPeriod,
            'Asia/Jakarta'
        )->subMonth()->format('Y-m');

        $accounts = ChartOfAccount::query()
            ->orderBy('id')
            ->get(['id', 'perusahaan']);

        $now = now();
        $changed = 0;

        DB::transaction(function () use (
            $accounts,
            $ledgerService,
            $previousPeriod,
            $targetPeriod,
            $now,
            &$changed
        ) {
            foreach ($accounts as $account) {
                $ledger = $ledgerService->getAccountLedger(
                    (int) $account->id,
                    $previousPeriod
                );

                $finalBalance = round(
                    (float) $ledger['final_balance'],
                    2
                );

                $existing = DB::table('opening_balances')
                    ->where('periode', $targetPeriod)
                    ->where('chart_of_account_id', $account->id)
                    ->first(['saldo', 'perusahaan']);

                if (
                    $existing !== null &&
                    abs((float) $existing->saldo - $finalBalance) < 0.005 &&
                    (int) $existing->perusahaan === (int) $account->perusahaan
                ) {
                    continue;
                }

                $changed++;

                if ($this->option('dry-run')) {
                    continue;
                }

                if ($existing !== null) {
                    DB::table('opening_balances')
                        ->where('periode', $targetPeriod)
                        ->where('chart_of_account_id', $account->id)
                        ->update([
                            'saldo' => $finalBalance,
                            'perusahaan' => $account->perusahaan,
                            'updated_at' => $now,
                        ]);
                } else {
                    DB::table('opening_balances')->insert([
                        'periode' => $targetPeriod,
                        'chart_of_account_id' => $account->id,
                        'saldo' => $finalBalance,
                        'perusahaan' => $account->perusahaan,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        });

        $mode = $this->option('dry-run') ? 'Perlu diperbarui' : 'Diperbarui';
        $this->info(
            "{$mode}: {$changed} akun. Saldo akhir {$previousPeriod} " .
            "menjadi saldo awal {$targetPeriod}."
        );

        return self::SUCCESS;
    }
}
