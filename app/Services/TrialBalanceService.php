<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class TrialBalanceService
{
    /**
     * Mengambil Semua Data Jurnal (Gabungan Header & Detail)
     */
    public function getTrialBalance(string $periode, int $id): array
    {
        // 1. Query gabungan untuk mengambil COA, Saldo Awal, dan akumulasi mutasi POSTED
        $balances = DB::table('public.chart_of_account as coa')
            ->select([
                'coa.id',
                'coa.kode',
                'coa.nama_akun',
                'coa.type',
                'coa.category',
                'ob.saldo as opening_balance',
                DB::raw('COALESCE(SUM(CASE WHEN ju.id IS NOT NULL THEN ji.debit ELSE 0 END), 0) as total_debit'),
                DB::raw('COALESCE(SUM(CASE WHEN ju.id IS NOT NULL THEN ji.kredit ELSE 0 END), 0) as total_kredit')
            ])
            ->leftJoin('public.opening_balances as ob', function($join) use ($periode) {
                $join->on('coa.id', '=', 'ob.chart_of_account_id')
                     ->where('ob.periode', '=', $periode);
            })
            ->leftJoin('public.general_journal_details as ji', function($join) {
                $join->on('coa.id', '=', 'ji.chart_of_account_id');
            })
            ->leftJoin('public.general_journals as ju', function($join) use ($periode) {
                $join->on('ji.general_journal_id', '=', 'ju.id')
                     ->where('ju.status', '=', 'posted')
                     ->whereRaw("to_char(ju.tgl_transaksi, 'YYYY-MM') = ?", [$periode]);
            })
            ->where('coa.is_active', true)
            ->where('coa.perusahaan', $id)
            ->groupBy('coa.id', 'coa.kode', 'coa.nama_akun', 'coa.type', 'coa.category', 'ob.saldo')
            ->orderBy('coa.kode', 'asc')
            ->get();

        // 2. Pemrosesan data sesuai aturan saldo normal akuntansi
        $reportData = [];
        $grandTotalDebit = 0;
        $grandTotalKredit = 0;

        foreach ($balances as $row) {
            $opening = (float) $row->opening_balance;
            $debit = (float) $row->total_debit;
            $kredit = (float) $row->total_kredit;

            if (strtoupper($row->type) === 'D') {
                // Tipe DEBIT: Saldo Awal + Debit - Kredit
                $finalBalance = $opening + ($debit - $kredit);
                $displayDebit = $finalBalance;
                $displayKredit = 0;
            } else {
                // Tipe KREDIT: Saldo Awal + Kredit - Debit
                $finalBalance = $opening + ($kredit - $debit);
                $displayDebit = 0;
                $displayKredit = $finalBalance;
            }

            $grandTotalDebit += $displayDebit;
            $grandTotalKredit += $displayKredit;

            $reportData[] = [
                'kode'         => $row->kode,
                'nama_akun'    => $row->nama_akun,
                'category'     => $row->category,
                'saldo_debit'  => $displayDebit,
                'saldo_kredit' => $displayKredit,
            ];
        }

        // Kembalikan dalam bentuk array terstruktur
        return [
            'report_data'        => $reportData,
            'grand_total_debit'  => $grandTotalDebit,
            'grand_total_kredit' => $grandTotalKredit,
        ];
    }
}
