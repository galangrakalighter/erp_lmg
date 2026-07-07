<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount; // Sesuaikan dengan nama Model COA Anda
use App\Services\ChartOfAccountService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class COAController extends Controller
{
    protected $coaService;

    // Inject Service ke dalam Controller
    public function __construct(ChartOfAccountService $coaService)
    {
        $this->coaService = $coaService;
    }

    /**
     * Menampilkan halaman utama ATAU merespon request data AJAX Fetch
     */
    public function index()
    {
        $title = "Chart Of Account";
        return view('finance.coa', compact('title')); // Sesuaikan dengan path file blade Anda
    }

    public function getCoaData(Request $request)
    {
        try {
            $accounts = $this->coaService->getAllAccounts($request);
            return response()->json($accounts);
        } catch (\Exception $e) {
            // Jika ada error di service/SQL, kita tangkap dan kirim response JSON agar tidak error 500 polosan
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan data dari modal Tambah Akun
     */
    public function store(Request $request)
    {
        try {
            // Proses Validasi Standar
            $validated = $request->validate([
                'kode' => 'required|unique:chart_of_account,kode', 
                'nama_akun' => 'required|string|max:255',
                'type' => 'required|in:D,K',
                'category' => 'required',
                'is_active' => 'nullable|boolean',
                'opening_periode' => 'nullable|string|size:7', // Format: YYYY-MM
                'opening_saldo'   => 'nullable|numeric'
            ], [
                // Kustomisasi pesan error validasi agar rapi
                'kode.unique' => 'Kode akun ini sudah terdaftar!',
            ]);

            // Jika lolos, simpan data
            $this->coaService->storeAccount($validated);

            // Flash message saat sukses
            return redirect()->route('finance_coa.index')
                ->with('success', 'Akun baru berhasil ditambahkan!');

        } catch (ValidationException $e) {
            // JIKA GAGAL VALIDASI (Misal kode kembar): 
            // Kembalikan ke halaman index dengan membawa input lama dan pesan error kustom
            return redirect()->route('finance_coa.index')
                ->withInput()
                ->withErrors($e->validator)
                ->with('error', 'Gagal menambahkan akun! Kode akun sudah terdaftar atau data tidak valid.');
                
        } catch (\Exception $e) {
            // JIKA GAGAL KARENA ERROR SYSTEM / DATABASE LAINNYA:
            return redirect()->route('finance_coa.index')
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Memperbarui data dari modal Edit Akun
     * Menggunakan Route Model Binding ($finance_coa otomatis mencari id)
     */
    public function update(Request $request, $id)
    {
        try {
            // 1. Cari data COA yang ingin diubah, jika tidak ketemu otomatis melempar 404
            $coa = ChartOfAccount::findOrFail($id);

            // 2. Jalankan Validasi dengan Pengecualian ID saat ini (. $coa->id)
            $validated = $request->validate([
                'kode' => 'required|integer|unique:chart_of_account,kode,' . $coa->id, 
                'nama_akun' => 'required|string|max:255',
                'type' => 'required|in:D,K',
                'category' => 'required',
                'is_active' => 'nullable|boolean',
                'opening_periode' => 'nullable|string|size:7',
                'opening_saldo' => 'nullable|numeric'
            ], [
                'kode.unique' => 'Kode akun ini sudah digunakan oleh akun lain!',
            ]);

            // 3. Kirim data yang lolos validasi ke Service Layer
            $this->coaService->updateAccount($coa, $validated);

            // 4. Flash message sukses jika berhasil update
            return redirect()->route('finance_coa.index')
                ->with('success', 'Data akun berhasil diperbarui!');

        } catch (ValidationException $e) {
            // JIKA GAGAL VALIDASI (Misal kode menabrak kode milik akun lain):
            return redirect()->route('finance_coa.index')
                ->withInput()
                ->withErrors($e->validator)
                ->with('error', 'Gagal memperbarui akun! Kode akun sudah terdaftar atau data tidak valid.')
                ->with('edit_failed_id', $id); // Mengirim ID untuk memberi tahu frontend modal mana yang harus dibuka lagi
                
        } catch (\Exception $e) {
            // JIKA GAGAL KARENA ERROR LAIN (Misal kendala database)
            return redirect()->route('finance_coa.index')
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus data dari modal Hapus Akun
     */
    public function destroy($id)
    {
        $coa = ChartOfAccount::findOrFail($id);
        
        $this->coaService->deleteAccount($coa);

        return redirect()->route('finance_coa.index')
            ->with('success', 'Akun berhasil dihapus!');
    }
}