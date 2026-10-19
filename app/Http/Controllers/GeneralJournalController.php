<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GeneralJournalService;
use App\Models\ChartOfAccount; // Untuk me-load data select COA di modal
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Services\AuditService;

class GeneralJournalController extends Controller
{
    protected $journalService;

    public function __construct(GeneralJournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * Menampilkan Halaman Utama Jurnal Umum
     */
    public function index($id)
    {
        $title = "General Journal";

        // Ambil data COA yang aktif untuk dilempar ke modal tambah jurnal
        $accounts = ChartOfAccount::where('is_active', true)->where('perusahaan', $id)->orderBy('kode', 'asc')->get();

        return view('finance.general_journals', compact('accounts', 'title', 'id'));
    }

    public function getGeneralJournalData(Request $request, $id)
    {
        // Ambil periode dari request (asumsi format YYYY-MM)
        $periode = date('Y-m', strtotime($request->start_date ?? date('Y-m-d')));
        
        // Cek status lock
        $isLocked = DB::table('period_locks')
            ->where('periode', $periode)
            ->where('perusahaan', $id)
            ->where('is_locked', true)
            ->exists();

        $journals = $this->journalService->getAllJournals($request, $id);

        return response()->json([
            'journals' => $journals,
            'isLocked' => $isLocked // Kirim status lock ke frontend
        ]);
    }

    /**
     * Menyimpan Data Transaksi Jurnal Baru
     */
    public function store(Request $request)
    {
        $periode = date('Y-m', strtotime($request->tgl_transaksi));
        $isLocked = DB::table('period_locks')
            ->where('periode', $periode)
            ->where('perusahaan', $request->perusahaan)
            ->where('is_locked', true)
            ->exists();

        if ($isLocked) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan! Periode ' . $periode . ' sudah ditutup dan tidak dapat diubah.');
        }
        try {
            // 1. Validasi Input Header & Array Detail Item
            $validated = $request->validate([
                'tgl_transaksi' => 'required|date',
                'no_invoice'    => 'nullable|string|max:100',
                'keterangan'    => 'required|string',
                'cash_flow_activity' => 'required',
                'status'        => 'required|in:draft,posted', // [BARU] Menangkap status pilihan user (draft/posted)
                'items'         => 'required|array|min:2',
                'items.*.chart_of_account_id' => 'required|integer',
                'items.*.keterangan_item'     => 'nullable|string|max:255',
                'items.*.debit'               => 'required|numeric|min:0',
                'items.*.kredit'              => 'required|numeric|min:0',
                'perusahaan'                  => 'required'
            ]);

            // 2. Kirim ke Service Layer
            try {
                $journalId = $this->journalService->storeJournal($validated);

                AuditService::log(
                    'CREATED',
                    'Jurnal Umum',
                    'Membuat jurnal transaksi baru pada tanggal ' . $validated['tgl_transaksi'] . ' (Status: ' . $validated['status'] . ')',
                    null,
                    ['header_id' => $journalId, 'data' => $validated]
                );

                return redirect()->back()->with('success', 'Data Jurnal Berhasil Disimpan');
            } catch (Exception $e) {
                return redirect()->back()->with('error', 'Gagal Menyimpan Data: ' . $e->getMessage());
            }

        } catch (ValidationException $e) {
            return redirect()->route('general_journal.index')
                ->withInput()
                ->withErrors($e->validator)
                ->with('error', 'Gagal memproses jurnal! Periksa kembali kelengkapan data form.');

        } catch (\Exception $e) {
            return redirect()->route('general_journal.index')
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            // Mengambil data header beserta rincian itemnya
            $journal = DB::table('general_journals')
                ->where('id', $id)
                ->first();

            if (!$journal) {
                return response()->json(['message' => 'Data jurnal tidak ditemukan'], 404);
            }

            $items = DB::table('general_journal_details')
                ->join('chart_of_account', 'general_journal_details.chart_of_account_id', '=', 'chart_of_account.id')
                ->where('general_journal_id', $id)
                ->select(
                    'general_journal_details.*', 
                    'chart_of_account.type as coa_type'
                )
                ->get();

            return response()->json([
                'header' => $journal,
                'items'  => $items
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal mengambil detail data: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // 1. Validasi Input Data Atas & Array Items
        $validated = $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_invoice'    => 'nullable|string|max:100',
            'keterangan'    => 'required|string',
            'cash_flow_activity' => 'required',
            'status'        => 'required|in:draft,posted', // [BARU] Memastikan input status valid
            'items'         => 'required|array|min:2',
            'items.*.chart_of_account_id' => 'required|integer',
            'items.*.keterangan_item'     => 'nullable|string|max:255',
            'items.*.debit'               => 'required|numeric|min:0',
            'items.*.kredit'              => 'required|numeric|min:0',
            'perusahaan'                  => 'required'
        ]);

        // 2. Kirim ke Service Layer untuk pemrosesan Database Transaction
        try {
            $oldHeader = DB::table('general_journals')->where('id', $id)->first();
            $oldDetails = DB::table('general_journal_details')->where('general_journal_id', $id)->get();
            $oldData = [
                'header' => (array) $oldHeader,
                'details' => $oldDetails->map(function ($item) { return (array) $item; })->toArray()
            ];
            $this->journalService->updateJournal($id, $validated);

            $newHeader = DB::table('general_journals')->where('id', $id)->first();
            $newDetails = DB::table('general_journal_details')->where('general_journal_id', $id)->get();

            $newData = [
                'header' => (array) $newHeader,
                'details' => $newDetails->map(function ($item) { return (array) $item; })->toArray()
            ];

            AuditService::log(
                'UPDATED',
                'Jurnal Umum',
                'Memperbarui jurnal transaksi dengan nomor: ' . ($newHeader->no_jurnal ?? $id),
                $oldData,
                $newData
            );

            return redirect()->back()->with('success', 'Data Jurnal Berhasil Diperbaharui');
        } catch (Exception $e) {
            // [MODIFIKASI] Menampilkan pesan error dinamis dari Service Layer (misal: "Akses Ditolak: Periode dikunci")
            return redirect()->back()->with('error', 'Gagal Memperbarui Data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $oldHeader = DB::table('general_journals')->where('id', $id)->first();
        
            if (!$oldHeader) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data jurnal tidak ditemukan!'
                ], 404);
            }

            $oldDetails = DB::table('general_journal_details')->where('general_journal_id', $id)->get();
            
            $oldData = [
                'header'  => (array) $oldHeader,
                'details' => $oldDetails->map(function ($item) { return (array) $item; })->toArray()
            ];


            $this->journalService->deleteJournal($id);

            AuditService::log(
                'DELETED',
                'Jurnal Umum',
                'Menghapus transaksi jurnal umum dengan nomor: ' . ($oldHeader->no_jurnal ?? $id),
                $oldData,
                null
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi Jurnal Umum berhasil dihapus dari sistem!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
}
