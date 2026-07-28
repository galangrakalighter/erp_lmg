<?php

namespace App\Services;

use App\Models\ChartOfAccount; // Sesuaikan dengan nama Model COA Anda
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class LedgerService
{
    public function getAccountLedger(int $coaId, string $periode)
    {
        // 1. Ambil data master COA untuk mengetahui posisi normalnya (D/K)
        $coa = ChartofAccount::findOrFail($coaId);

        // 2. Ambil Saldo Awal dari tabel opening_balances
        $openingBalance = DB::table('opening_balances')
            ->where('chart_of_account_id', $coaId)
            ->where('periode', $periode)
            ->value('saldo') ?? 0.00;

        // 3. Ambil seluruh mutasi transaksi jurnal yang terikat dengan akun ini
        $mutasi = DB::table('general_journal_details as ji')
            ->join('general_journals as ju', 'ji.general_journal_id', '=', 'ju.id')
            ->select([
                'ju.tgl_transaksi as tanggal',
                'ju.no_jurnal',
                'ji.keterangan_item',
                'ji.debit',
                'ji.kredit',
                'ju.no_invoice',
                'ju.status'
            ])
            ->where('ji.chart_of_account_id', $coaId)
            ->where('ju.status', '=', 'posted')
            ->whereRaw("to_char(ju.tgl_transaksi, 'YYYY-MM') = ?", [$periode])
            ->orderBy('ju.tgl_transaksi', 'asc')
            ->orderBy('ju.created_at', 'asc') // Menjaga urutan jika tanggalnya sama
            ->get();

        // 4. Hitung Saldo Berjalan (Running Balance) baris demi baris
        $currentBalance = (float) $openingBalance;
        $ledgerRows = [];

        foreach ($mutasi as $row) {
            // dd($row);
            $debit = (float) $row->debit;
            $kredit = (float) $row->kredit;

            // Rumus akumulasi saldo berjalan berdasarkan tipe normal akun
            if (strtoupper($coa->type) === 'D') {
                // Jika tipe DEBIT: Debit menambah, Kredit mengurangi
                $currentBalance += ($debit - $kredit);
            } else {
                // Jika tipe KREDIT: Kredit menambah, Debit mengurangi
                $currentBalance += ($kredit - $debit);
            }

            $ledgerRows[] = [
                'tanggal'        => $row->tanggal,
                'nomor_jurnal'   => $row->no_jurnal,
                'keterangan'     => $row->keterangan_item,
                'debit'          => $debit,
                'kredit'         => $kredit,
                'saldo_berjalan' => $currentBalance
            ];
        }

        return [
            'account'         => $coa,
            'opening_balance' => $openingBalance,
            'mutasi'          => $ledgerRows,
            'final_balance'   => $currentBalance
        ];
    }

    public function getDataJurnalPenutup($periode, $kodePrefix)
    {
        return DB::table('general_journal_details')
            ->join('chart_of_account', 'general_journal_details.chart_of_account_id', '=', 'chart_of_account.id')
            ->join('general_journals', 'general_journal_details.general_journal_id', '=', 'general_journals.id')
            ->where('chart_of_account.kode', 'LIKE', $kodePrefix)
            ->where(DB::raw("TO_CHAR(general_journals.tgl_transaksi, 'YYYY-MM')"), $periode)
            ->select(
                'chart_of_account.kode', 
                'chart_of_account.nama_akun', 
                'general_journals.keterangan', 
                'general_journal_details.debit', 
                'general_journal_details.kredit'
            )
            ->get();
    }
}