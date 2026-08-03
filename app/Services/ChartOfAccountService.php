<?php

namespace App\Services;

use App\Models\ChartOfAccount; // Sesuaikan dengan nama Model COA Anda
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ChartOfAccountService
{
    /**
     * Mengambil daftar akun dengan opsional filter tipe
     */
    public function getAllAccounts(Request $request, $id)
    {
        // 1. Inisialisasi query murni menggunakan nama tabel dari Model (tanpa public.)
        $query = ChartOfAccount::query()
            ->select([
                'chart_of_account.*',
                'ob.saldo as opening_saldo',
                'ob.periode as opening_periode'
            ]);

        // 2. Ambil parameter periode dari request, default ke bulan berjalan jika kosong
        $periode = $request->input('periode', date('Y-m'));

        // 3. Lakukan LEFT JOIN dengan kondisi ganda
        $query->leftJoin('opening_balances as ob', function($join) use ($periode) {
            $join->on('chart_of_account.id', '=', 'ob.chart_of_account_id')
                ->where('ob.periode', '=', $periode);
        });

        // --- FILTER DATA ---
        
        // Filter Berdasarkan Tipe Akun (D/K)
        if ($request->filled('type')) {
            $query->where('chart_of_account.type', strtoupper($request->type));
        }

        // Filter Berdasarkan Status Aktif
        if ($request->filled('status')) {
            $is_active = $request->status === 'aktif' ? true : false;
            $query->where('chart_of_account.is_active', $is_active);
        }

        // Filter Berdasarkan Kategori
        if ($request->filled('category')) {
            $query->where('chart_of_account.category', strtolower($request->category));
        }

        // Filter Berdasarkan Keyword Search (Kode / Nama Akun)
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(chart_of_account.kode::text) like ?', ['%' . $search . '%'])
                ->orWhereRaw('LOWER(chart_of_account.nama_akun) like ?', ['%' . $search . '%']);
            });
        }

        $query->where('chart_of_account.perusahaan', $id);

        // Urutkan berdasarkan kode COA
        return $query->orderBy('chart_of_account.kode', 'asc')->get();
    }

    /**
     * Menyimpan akun baru ke database
     */
    public function storeAccount(array $data)
    {
        DB::beginTransaction();

        try {
            // 1. Simpan data COA menggunakan Eloquent seperti kode awalmu
            $coa = ChartOfAccount::create([
                'kode'      => $data['kode'],
                'nama_akun' => $data['nama_akun'],
                'type'      => $data['type'],
                'category'  => $data['category'],
                'is_active' => $data['is_active'] ?? true,
                'perusahaan' => (int)$data['perusahaan']
            ]);

            // 2. Ambil nilai saldo dan periode dari form modal
            $openingSaldo   = isset($data['opening_saldo']) ? (float) $data['opening_saldo'] : 0.00;
            $openingPeriode = $data['opening_periode'] ?? null;

            // 3. Logika: Jika user mengisi saldo (bukan 0) dan memilih periode bulan
            if ($openingSaldo != 0 && !empty($openingPeriode)) {
                
                // Kamu bisa langsung insert menggunakan DB Builder ke tabel opening_balances
                DB::table('opening_balances')->insert([
                    'periode'             => $openingPeriode,
                    'chart_of_account_id' => $coa->id, // Mengambil ID dari model Eloquent di atas
                    'saldo'               => $openingSaldo,
                    'perusahaan'          => $data['perusahaan'],
                    'created_at'          => now(),
                    'updated_at'          => now()
                ]);
            }

            // Jika semua lancar, simpan permanen ke PostgreSQL
            DB::commit();

            // Kembalikan objek model COA-nya agar Controller bisa membaca data/relasinya jika butuh
            return $coa;

        } catch (\Exception $e) {
            // Jika ada yang gagal, gagalkan semua proses pembuatan COA maupun Saldo Awal
           if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            
            // Log error asli ke laravel.log agar kamu bisa melihat alasan SEBENARNYA mengapa simpan COA gagal
            \Log::error('Gagal menyimpan COA atau Saldo Awal: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Alih-alih melempar exception mentah yang bikin middleware crash, 
            // kembalikan pesan error kustom atau throw berupa HttpResponseException
            abort(500, 'Gagal memproses data: ' . $e->getMessage());
        }
    }

    /**
     * Memperbarui data akun yang sudah ada
     */
    public function updateAccount(ChartOfAccount $coa, array $data)
    {
        // Bungkus dengan database transaction demi keamanan data finansial
        DB::beginTransaction();

        try {
            // 1. Jalankan update data master COA seperti kode awalmu
            $isUpdated = $coa->update([
                'kode'      => $data['kode'],
                'nama_akun' => $data['nama_akun'],
                'type'      => $data['type'],
                'category'  => strtolower($data['category']), // Dipaksa huruf kecil agar konsisten dengan filter getAllAccounts
                'is_active' => $data['is_active'] == '1' ? true : false // Memastikan string "1" dari form dikonversi ke boolean PostgreSQL
            ]);

            // 2. Ambil parameter saldo pembuka dari input request form kamu
            $openingSaldo   = isset($data['opening_saldo']) ? (float) $data['opening_saldo'] : 0.00;
            $openingPeriode = $data['opening_periode'] ?? null;

            // 3. Logika Upsert Saldo Awal
            if (!empty($openingPeriode)) {
                DB::table('opening_balances')->updateOrInsert(
                    [
                        'periode'             => $openingPeriode,
                        'chart_of_account_id' => $coa->id,
                        'perusahaan' => $data['perusahaan'],
                    ],
                    [
                        'saldo'      => $openingSaldo,
                        'updated_at' => now(),
                    ]
                );
            }

            // Jika semua baris eksekusi di atas aman, simpan permanen ke PostgreSQL
            DB::commit();

            // Kembalikan nilai boolean dari hasil update COA agar perilakunya tetap sama di Controller
            return $isUpdated;

        } catch (\Exception $e) {
            // Jika ada satu baris kueri yang gagal, batalkan semua perubahan data
            DB::rollBack();
            
            // Lemparkan error ke luar agar bisa ditangkap oleh try-catch milik Controller
            throw $e;
        }
    }

    /**
     * Menghapus akun dari database
     */
    public function deleteAccount(ChartOfAccount $coa)
    {
        return $coa->delete();
    }
}