<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OpeningBalanceController extends Controller
{
    public function index(Request $request)
    {
        $title = "Opening Balance";
        // Default periode ke bulan berjalan saat ini jika user belum memilih filter periode
        $periode = $request->get('periode', Carbon::now()->format('Y-m'));

        // Ambil semua akun COA yang aktif, gabungkan dengan tabel opening_balances (jika sudah ada saldonya)
        $coas = DB::table('public.chart_of_account as coa')
            ->leftJoin('public.opening_balances as ob', function($join) use ($periode) {
                $join->on('coa.id', '=', 'ob.chart_of_account_id')
                     ->where('ob.periode', '=', $periode);
            })
            ->select(
                'coa.id as coa_id',
                'coa.kode as kode_akun',
                'coa.nama_akun',
                'coa.type as tipe_akun',
                DB::raw('COALESCE(ob.saldo, 0.00) as saldo_awal')
            )
            ->where('coa.is_active', true)
            ->orderBy('coa.kode', 'asc')
            ->get();

        // Jika request dikirim lewat AJAX (misal saat user mengganti filter bulan di dropdown)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $coas
            ]);
        }

        // Tampilan biasa pas pertama kali buka halaman
        return view('finance.opening_balance', compact('coas', 'periode', 'title'));
    }

    public function save(Request $request)
    {
        // Validasi input data
        $request->validate([
            'periode' => 'required|string|size:7', // Format: "YYYY-MM"
            'balances' => 'required|array',       // Array input dari form text
        ]);

        $periode = $request->input('periode');
        $balances = $request->input('balances'); // Format kiriman array: [coa_id => nominal]

        DB::beginTransaction();
        try {
            foreach ($balances as $coaId => $saldo) {
                // Konversi input kosong atau null menjadi 0.00
                $nominalSaldo = !empty($saldo) ? $saldo : 0.00;

                // Eksekusi Upsert ke PostgreSQL
                DB::table('public.opening_balances')->updateOrInsert(
                    [
                        'periode' => $periode,
                        'chart_of_account_id' => $coaId
                    ],
                    [
                        'saldo' => $nominalSaldo,
                        'updated_at' => Carbon::now(),
                        'created_at' => Carbon::now() // Hanya terisi jika baris data baru dimasukkan
                    ]
                );
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => "Saldo awal untuk periode $periode berhasil disimpan!"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan saldo awal: ' . $e->getMessage()
            ], 500);
        }
    }
}
