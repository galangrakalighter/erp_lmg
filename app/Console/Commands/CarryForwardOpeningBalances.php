<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use App\Services\LedgerService;
use App\Services\TrialBalanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CarryForwardOpeningBalances extends Command
{
    protected $signature = 'finance:carry-forward-opening-balances
                            {--period= : Periode tujuan dalam format YYYY-MM}
                            {--dry-run : Hitung tanpa menyimpan perubahan}';

    protected $description = 'Jadikan saldo akhir bulan sebelumnya sebagai saldo awal bulan baru';

    public function handle(
        LedgerService $ledgerService,
        TrialBalanceService $trialBalanceService
    ): int
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
            ->get(['id', 'kode', 'perusahaan']);

        $now = now();
        $changed = 0;
        $profitLossByCompany = [];

        DB::transaction(function () use (
            $accounts,
            $ledgerService,
            $trialBalanceService,
            $previousPeriod,
            $targetPeriod,
            $now,
            &$profitLossByCompany,
            &$changed
        ) {
            foreach ($accounts as $account) {
                $isClosed = DB::table('account_closings')
                    ->where('perusahaan', $account->perusahaan)
                    ->where('periode', $previousPeriod)
                    ->where('chart_of_account_id', $account->id)
                    ->exists();

                if ($isClosed) {
                    $finalBalance = 0.0;
                } else {
                    $ledger = $ledgerService->getAccountLedger(
                        (int) $account->id,
                        $previousPeriod
                    );

                    $finalBalance = round(
                        (float) $ledger['final_balance'],
                        2
                    );
                }

                // Khusus akun 301 (modal/laba ditahan), saldo awal periode baru
                // juga membawa laba-rugi bersih periode sebelumnya. Nilai laba
                // positif akan menambah saldo, sedangkan rugi negatif mengurangi.
                if ((string) $account->kode === '301') {
                    $companyId = (int) $account->perusahaan;

                    if (!array_key_exists($companyId, $profitLossByCompany)) {
                        $profitLossByCompany[$companyId] = $this->calculateProfitLoss(
                            $trialBalanceService,
                            $companyId,
                            $previousPeriod
                        );
                    }

                    $finalBalance = round(
                        $finalBalance + $profitLossByCompany[$companyId],
                        2
                    );
                }

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

    private function calculateProfitLoss(
        TrialBalanceService $trialBalanceService,
        int $companyId,
        string $period
    ): float {
        $trialBalance = $trialBalanceService->getTrialBalance($period, $companyId);
        $balances = collect($trialBalance['report_data'] ?? [])->keyBy(
            fn (array $row) => (string) $row['kode']
        );

        $adjustments = DB::table('adjustment_journal_details as ajd')
            ->join('adjustment_journals as aj', 'ajd.adjustment_journal_id', '=', 'aj.id')
            ->where('aj.perusahaan', $companyId)
            ->whereRaw("to_char(aj.tgl_transaksi, 'YYYY-MM') = ?", [$period])
            ->groupBy('ajd.chart_of_account_id')
            ->selectRaw('ajd.chart_of_account_id, SUM(ajd.debit) as debit, SUM(ajd.kredit) as kredit')
            ->get()
            ->keyBy('chart_of_account_id');

        $accounts = ChartOfAccount::query()
            ->where('perusahaan', $companyId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('kode', 'like', '4%')
                    ->orWhere('kode', 'like', '5%')
                    ->orWhere('kode', 'like', '6%');
            })
            ->get(['id', 'kode']);

        $profitLoss = 0.0;

        foreach ($accounts as $account) {
            $balance = $balances->get((string) $account->kode);
            $adjustment = $adjustments->get($account->id);
            $debit = (float) ($balance['saldo_debit'] ?? 0)
                + (float) ($adjustment->debit ?? 0);
            $credit = (float) ($balance['saldo_kredit'] ?? 0)
                + (float) ($adjustment->kredit ?? 0);

            $profitLoss += $credit - $debit;
        }

        return round($profitLoss, 2);
    }
}
