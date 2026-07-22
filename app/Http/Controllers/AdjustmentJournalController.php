<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use App\Models\AdjustmentJournal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\AuditService;
class AdjustmentJournalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = "Ayat Jurnal Penyesuaian";
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('kode', 'asc')->get();
        
        $journals = AdjustmentJournal::with(['details.account'])
                        ->orderBy('tgl_transaksi', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('finance.adjustment', compact('title', 'accounts', 'journals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $periode = date('Y-m', strtotime($request->date));
        $isLocked = DB::table('period_locks')
            ->where('periode', $periode)
            ->where('is_locked', true)
            ->exists();

        if ($isLocked) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan! Periode ' . $periode . ' sudah ditutup dan tidak dapat diubah.');
        }
        
        $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.chart_of_account_id' => 'required|exists:chart_of_account,id',
            'items.*.debit' => 'required|numeric|min:0',
            'items.*.kredit' => 'required|numeric|min:0',
        ]);

        // Validasi tambahan: Memastikan total Debit dan Kredit balance di sisi server
        $totalDebit = collect($request->items)->sum('debit');
        $totalKredit = collect($request->items)->sum('kredit');

        if ($totalDebit !== $totalKredit || $totalDebit <= 0) {
            return redirect()->back()->withInput()->with('error', 'Transaksi gagal disimpan. Total Debit dan Kredit harus seimbang (balanced) dan lebih dari 0.');
        }

        try {
            DB::beginTransaction();

            // 1. Generate nomor jurnal otomatis (Contoh: AJ-20260619-0001)
            $dateString = date('Ymd', strtotime($request->date));
            $latestJournal = AdjustmentJournal::where('no_jurnal', 'LIKE', "AJ-{$dateString}-%")->latest()->first();
            
            $nextSequence = 1;
            if ($latestJournal) {
                $lastNumber = explode('-', $latestJournal->journal_number);
                $nextSequence = ((int) end($lastNumber)) + 1;
            }
            $journalNumber = "AJ-{$dateString}-" . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

            // 2. Simpan Data Header Jurnal
            $journal = AdjustmentJournal::create([
                'no_jurnal' => $journalNumber,
                'tgl_transaksi'  => $request->date,
                'keterangan'     => $request->description,
                'type'           => 'adjustment', // Penanda jenis jurnal jika digabung dengan umum
            ]);

            // 3. Simpan Rincian / Detail Akun
            $detailData = [];
            foreach ($request->items as $item) {
                $detail = $journal->details()->create([
                    'chart_of_account_id' => $item['chart_of_account_id'],
                    'debit'               => $item['debit'],
                    'kredit'              => $item['kredit'],
                ]);
                $detailData[] = $detail->toArray();
            }

            DB::commit();
            AuditService::log(
                'CREATED',
                'Jurnal Penyesuaian',
                'Membuat jurnal penyesuaian baru dengan nomor: ' . $journalNumber,
                null,
                [
                    'header'  => $journal->toArray(),
                    'details' => $detailData
                ]
            );
            return redirect()->back()->with('success', "Jurnal {$journalNumber} berhasil dibuat!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.chart_of_account_id' => 'required|exists:chart_of_account,id',
            'items.*.debit' => 'required|numeric|min:0',
            'items.*.kredit' => 'required|numeric|min:0',
        ]);

        $totalDebit = collect($request->items)->sum('debit');
        $totalKredit = collect($request->items)->sum('kredit');

        if ($totalDebit !== $totalKredit || $totalDebit <= 0) {
            return redirect()->back()->with('error', 'Perubahan gagal disimpan. Total Debit dan Kredit harus seimbang.');
        }

        try {
            $journal = AdjustmentJournal::findOrFail($id);
            $oldData = [
                'header'  => $journal->toArray(),
                'details' => $journal->details->map(function ($detail) { return $detail->toArray(); })->toArray()
            ];

            DB::beginTransaction();
            
            // 1. Update data Header
            $journal->update([
                'tgl_transaksi' => $request->date,
                'keterangan'    => $request->description,
            ]);

            // 2. Hapus detail lama, lalu ganti dengan rincian yang baru di-submit
            $journal->details()->delete();

            $newDetailsData = [];

            foreach ($request->items as $item) {
                $detail = $journal->details()->create([
                    'chart_of_account_id' => $item['chart_of_account_id'],
                    'debit'               => $item['debit'],
                    'kredit'              => $item['kredit'],
                ]);

                $newDetailsData[] = $detail->toArray();
            }

            DB::commit();
            $journal->refresh();
            $newData = [
                'header'  => $journal->toArray(),
                'details' => $newDetailsData
            ];
            AuditService::log(
                'UPDATED',
                'Jurnal Penyesuaian',
                'Memperbarui jurnal penyesuaian dengan nomor: ' . ($journal->no_jurnal ?? $journal->journal_number),
                $oldData,
                $newData
            );
            return redirect()->back()->with('success', "Jurnal {$journal->journal_number} berhasil diperbarui!");

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $journal = AdjustmentJournal::findOrFail($id);
        
        // Proteksi Backend: Cek apakah periode terkunci sebelum hapus
        if ($journal->is_locked) {
            return redirect()->back()->with('error', 'Data tidak dapat dihapus, periode sudah ditutup.');
        }

        try {
            // Simpan data lama untuk keperluan audit trail
            $oldData = [
                'header'  => $journal->toArray(),
                'details' => $journal->details->map(function ($detail) { return $detail->toArray(); })->toArray()
            ];

            $journalNumber = $journal->no_jurnal ?? $journal->journal_number;

            // Eksekusi hapus (Pastikan relasi detail ikut terhapus jika tidak menggunakan cascading delete di database, 
            // atau hapus detailnya manual terlebih dahulu seperti: $journal->details()->delete();)
            $journal->details()->delete();
            $journal->delete();

            // [BARU] Catat ke Audit Trail
            AuditService::log(
                'DELETED',
                'Jurnal Penyesuaian',
                'Menghapus jurnal penyesuaian dengan nomor: ' . $journalNumber,
                $oldData,
                null
            );

            return redirect()->back()->with('success', 'Jurnal berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus jurnal: ' . $e->getMessage());
        }
    }
}
