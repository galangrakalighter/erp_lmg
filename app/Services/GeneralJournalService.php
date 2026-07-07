<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class GeneralJournalService
{
    /**
     * Mengambil Semua Data Jurnal (Gabungan Header & Detail)
     */
    public function getAllJournals(Request $request)
    {
        // Menggunakan Raw SQL Query Builder agar sesuai dengan skema struktur database PostgreSQL kamu
        $query = DB::table('general_journals as gj')
            ->join('general_journal_details as gjd', 'gj.id', '=', 'gjd.general_journal_id')
            ->join('chart_of_account as coa', 'gjd.chart_of_account_id', '=', 'coa.id')
            ->select(
                'gj.id as journal_id',
                'gj.no_jurnal',
                'gj.tgl_transaksi',
                'gj.no_invoice',
                'gj.keterangan as keterangan_header',
                'gj.status', // [BARU] Wajib diambil agar JavaScript di frontend bisa membaca status draft/posted
                'gjd.keterangan_item',
                'coa.kode as kode_akun',
                'coa.nama_akun',
                'gjd.debit',
                'gjd.kredit'
            );

        // Filter Berdasarkan Jangkauan Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('gj.tgl_transaksi', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('status')) {
            $query->where('gj.status', strtolower($request->status));
        }

        // Filter Pencarian Keyword Global
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(gj.no_jurnal) like ?', ['%' . $search . '%'])
                ->orWhereRaw('LOWER(gj.no_invoice) like ?', ['%' . $search . '%'])
                ->orWhereRaw('LOWER(gj.keterangan) like ?', ['%' . $search . '%'])
                ->orWhereRaw('LOWER(coa.nama_akun) like ?', ['%' . $search . '%']);
            });
        }

        // Diurutkan berdasarkan tanggal terbaru dan nomor jurnal terbaru
        return $query->orderBy('gj.tgl_transaksi', 'desc')
                    ->orderBy('gj.no_jurnal', 'desc')
                    ->get();
    }

    private function validatePeriodLock(string $dateString)
    {
        $txDate = Carbon::parse($dateString)->startOfMonth();
        $currentMonth = Carbon::now()->startOfMonth();

        // Jika bulan transaksi lebih kecil dari bulan berjalan saat ini
        if ($txDate->lessThan($currentMonth)) {
            throw new Exception("Akses Ditolak: Transaksi pada periode bulan lalu (" . Carbon::parse($dateString)->format('F Y') . ") telah dikunci oleh sistem.");
        }
    }

    /**
     * Memproses Penyimpanan Transaksi Jurnal (Header + Detail)
     */
    public function storeJournal(array $data)
    {
        // 1. [BARU] PROTEKSI KUNCI PERIODE BULANAN
        // Memastikan user tidak memasukkan transaksi baru bertanggal bulan lalu/lampau
        $this->validatePeriodLock($data['tgl_transaksi']);

        // 2. Ambil array items detail
        $items = $data['items'];

        // 3. Validasi Keseimbangan Akuntansi (Double-Entry Balance Check) di tingkat Backend
        $totalDebit = collect($items)->sum('debit');
        $totalKredit = collect($items)->sum('kredit');

        if (round($totalDebit, 2) !== round($totalKredit, 2)) {
            throw new \Exception("Jumlah total debit (Rp " . number_format($totalDebit) . ") dan kredit (Rp " . number_format($totalKredit) . ") tidak seimbang!");
        }

        if ($totalDebit <= 0) {
            throw new \Exception("Nominal transaksi jurnal tidak boleh nol atau minus.");
        }

        // 4. Eksekusi Database Transaction
        DB::beginTransaction();

        try {
            // Generate Nomor Jurnal Otomatis (Contoh hasil: JV-202605-0001)
            $noJurnal = $this->generateJournalNumber($data['tgl_transaksi']);

            // Simpan Ke Tabel Header (general_journals)
            $journalId = DB::table('general_journals')->insertGetId([
                'no_jurnal'     => $noJurnal,
                'tgl_transaksi' => $data['tgl_transaksi'],
                'no_invoice'    => $data['no_invoice'] ?? null,
                'keterangan'    => $data['keterangan'],
                'arus'          => $data['cash_flow_activity'],
                'status'        => $data['status'], // [BARU] Menyimpan status 'draft' atau 'posted' secara dinamis dari form
                'created_by'    => Auth::id(),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now()
            ]);

            // Simpan Ke Tabel Detail (general_journal_details) looping per baris akun
            foreach ($items as $item) {
                // Abaikan baris kosong yang tidak sengaja terkirim (jika ada)
                if (empty($item['chart_of_account_id'])) continue;

                DB::table('general_journal_details')->insert([
                    'general_journal_id'  => $journalId,
                    'chart_of_account_id' => $item['chart_of_account_id'],
                    'keterangan_item'     => $item['keterangan_item'] ?? null,
                    'debit'               => $item['debit'] ?? 0.00,
                    'kredit'              => $item['kredit'] ?? 0.00,
                    'created_at'          => Carbon::now(),
                    'updated_at'          => Carbon::now()
                ]);
            }

            // Jika semua baris sukses dimasukkan tanpa error, kunci permanen datanya
            DB::commit();
            return $journalId;

        } catch (\Exception $e) {
            // Jika di tengah jalan ada baris yang error, batalkan semua baris yang terlanjur di-insert
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Helper Generator Nomor Urut Jurnal Otomatis Berdasarkan Bulan & Tahun Transaksi
     */
    private function generateJournalNumber($date)
    {
        $carbonDate = Carbon::parse($date);
        $prefix = 'JV-' . $carbonDate->format('Ym') . '-'; // Menghasilkan "JV-202605-"

        // Cari nomor urut terakhir pada bulan dan tahun tersebut di database
        $lastJournal = DB::table('general_journals')
            ->where('no_jurnal', 'like', $prefix . '%')
            ->orderBy('no_jurnal', 'desc')
            ->first();

        if ($lastJournal) {
            // Potong string nomor urut terakhir untuk mengambil angka 4 digit belakang (misal dari 'JV-202605-0012' diambil '0012')
            $lastNumber = intval(substr($lastJournal->no_jurnal, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1; // Jika ini transaksi pertama di bulan tersebut
        }

        // Gabungkan prefix dengan angka baru yang diformat pad-left 4 digit (0001, 0002, dst)
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function updateJournal(int $id, array $data)
    {
        $currentJournal = DB::table('general_journals')->where('id', $id)->first();
    
        if (!$currentJournal) {
            throw new Exception("Data jurnal tidak ditemukan.");
        }

        // 2. [BARU] PROTEKSI STATUS POSTED: Jika di DB sudah 'posted', kunci mutlak
        if ($currentJournal->status === 'posted') {
            throw new Exception("Gagal Update: Transaksi yang sudah berstatus 'Posted' tidak dapat diubah kembali.");
        }

        // 3. [BARU] PROTEKSI KUNCI PERIODE BULANAN
        // Cek apakah tanggal lama (di DB) atau tanggal baru (dari Form) berasal dari bulan lalu
        $this->validatePeriodLock($currentJournal->tgl_transaksi);
        $this->validatePeriodLock($data['tgl_transaksi']);

        // 4. Cek Validasi Logika Akuntansi (Apakah Seimbang?)
        $totalDebit  = 0;
        $totalKredit = 0;

        foreach ($data['items'] as $item) {
            $totalDebit  += floatval($item['debit']);
            $totalKredit += floatval($item['kredit']);
        }

        // Gunakan bccomp atau round untuk menghindari masalah float presisi PHP
        if (round($totalDebit, 2) !== round($totalKredit, 2)) {
            throw new Exception("Gagal Simpan: Jumlah total debit ($totalDebit) dan kredit ($totalKredit) tidak seimbang.");
        }

        if ($totalDebit <= 0) {
            throw new Exception("Gagal Simpan: Nominal total transaksi tidak boleh bernilai 0.");
        }

        // 5. Jalankan DB Transaction
        DB::transaction(function () use ($id, $data) {
            
            // A. Update Data Bagian Atas (Header Jurnal)
            DB::table('general_journals')
                ->where('id', $id)
                ->update([
                    'tgl_transaksi' => $data['tgl_transaksi'],
                    'no_invoice'    => $data['no_invoice'],
                    'keterangan'    => $data['keterangan'],
                    'status'        => $data['status'], // [BARU] Menyimpan perubahan status (draft/posted)
                    'created_by'    => Auth::id(),
                    'updated_at'    => now()
                ]);

            // B. Hapus Seluruh Item Rincian Lama (Sesuai nama tabelmu: general_journal_details)
            DB::table('general_journal_details')
                ->where('general_journal_id', $id)
                ->delete();

            // C. Masukkan Rincian Item yang Baru Diedit
            foreach ($data['items'] as $item) {
                DB::table('general_journal_details')->insert([
                    'general_journal_id'   => $id,
                    'chart_of_account_id' => $item['chart_of_account_id'],
                    'keterangan_item'     => $item['keterangan_item'],
                    'debit'               => $item['debit'],
                    'kredit'              => $item['kredit'],
                    'created_at'          => now(),
                    'updated_at'          => now()
                ]);
            }
        });

        return true;
    }

    public function deleteJournal(int $id)
    {
        // 1. Cek keberadaan data sebelum dieksekusi
        $journalExists = DB::table('general_journals')->where('id', $id)->exists();
        if (!$journalExists) {
            throw new Exception("Data jurnal tidak ditemukan atau sudah dihapus sebelumnya.");
        }

        // 2. Jalankan DB Transaction demi keamanan data pembukuan
        DB::transaction(function () use ($id) {
            
            // A. Hapus semua baris akun rincian debit/kredit terlebih dahulu
            DB::table('general_journal_details')
                ->where('general_journal_id', $id)
                ->delete();

            // B. Hapus data induk / header jurnal
            DB::table('general_journals')
                ->where('id', $id)
                ->delete();
        });

        return true;
    }
}