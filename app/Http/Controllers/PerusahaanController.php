<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class PerusahaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->ensureCfoCompanyManager();
        $title = 'Perusahaan';
        $user = Auth::user();

        $financeUsers = User::query()
            ->unlocked()
            ->where('isAdmin', false)
            ->whereHas('userDivisions.division', fn ($query) => $query->whereRaw("LOWER(TRIM(name)) = 'finance'"))
            ->orderBy('email')
            ->get(['id', 'name', 'email']);

        return view('finance.perusahaan', compact('title', 'user', 'financeUsers'));
    }

    public function getDataPerusahaan(){
        $this->ensureCfoCompanyManager();
        $currentUser = Auth::user();
        $query = DB::table('perusahaan as p')->select('p.*');

        if (! $currentUser->isAdmin) {
            $query->where('p.status', true)
                ->whereExists(function ($subQuery) use ($currentUser) {
                    $subQuery->selectRaw('1')->from('perusahaan_user as pu')
                        ->whereColumn('pu.perusahaan_id', 'p.id')
                        ->where('pu.user_id', $currentUser->id);
                });
        }

        $data = $query->orderBy('p.nama_perusahaan')->get();
        $accessByCompany = DB::table('perusahaan_user as pu')
            ->join('users as u', 'u.id', '=', 'pu.user_id')
            ->whereIn('pu.perusahaan_id', $data->pluck('id'))
            ->orderBy('u.email')
            ->get(['pu.perusahaan_id', 'u.id as user_id', 'u.email'])
            ->groupBy('perusahaan_id');

        $data->each(function ($company) use ($accessByCompany) {
            $access = $accessByCompany->get($company->id, collect());
            $company->user_ids = $access->pluck('user_id')->values();
            $company->user_emails = $access->pluck('email')->values();
        });

        return response()->json(['data' => $data]);
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
        try {
            $this->ensureCfoCompanyManager();
            // Validasi input
            $request->validate([
                'nama_perusahaan' => 'required|string|max:255|unique:perusahaan,nama_perusahaan',
                'status' => 'required|in:0,1',
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'integer|exists:users,id',
            ]);

            $companyId = DB::transaction(function () use ($request) {
                $companyId = DB::table('perusahaan')->insertGetId([
                    'nama_perusahaan' => $request->nama_perusahaan,
                    'status' => $request->status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->syncCompanyUsers($companyId, $request->input('user_ids', []));

                return $companyId;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data perusahaan berhasil ditambahkan!'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
            ], 500);
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
    public function update(Request $request, string $id)
    {
        try {
            $this->ensureCfoCompanyManager();
            // Cek apakah data ada
            $perusahaan = DB::table('perusahaan')->where('id', $id)->first();
            if (!$perusahaan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data perusahaan tidak ditemukan.'
                ], 404);
            }

            // Validasi input (unique dengan mengecualikan ID saat ini)
            $request->validate([
                'nama_perusahaan' => 'required|string|max:255|unique:perusahaan,nama_perusahaan,' . $id,
                'status' => 'required|in:0,1',
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'integer|exists:users,id',
            ]);

            DB::transaction(function () use ($request, $id) {
                DB::table('perusahaan')->where('id', $id)->update([
                    'nama_perusahaan' => $request->nama_perusahaan,
                    'status' => $request->status,
                    'updated_at' => now(),
                ]);
                $this->syncCompanyUsers((int) $id, $request->input('user_ids', []));
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data perusahaan berhasil diperbarui!'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->ensureCfoCompanyManager();
            $perusahaan = DB::table('perusahaan')->where('id', $id)->first();
            if (!$perusahaan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data perusahaan tidak ditemukan.'
                ], 404);
            }

            DB::table('perusahaan')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data perusahaan berhasil dihapus!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function syncCompanyUsers(int $companyId, array $userIds): void
    {
        $financeUserIds = User::query()
            ->unlocked()
            ->where('isAdmin', false)
            ->whereHas('userDivisions.division', fn ($query) => $query->whereRaw("LOWER(TRIM(name)) = 'finance'"))
            ->whereIn('id', $userIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count(array_unique(array_map('intval', $userIds))) !== count($financeUserIds)) {
            throw ValidationException::withMessages([
                'user_ids' => 'Akses perusahaan hanya dapat diberikan kepada pengguna divisi Finance.',
            ]);
        }

        DB::table('perusahaan_user')->where('perusahaan_id', $companyId)->delete();

        if ($financeUserIds !== []) {
            DB::table('perusahaan_user')->insert(array_map(fn (int $userId) => [
                'perusahaan_id' => $companyId,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ], array_unique($financeUserIds)));
        }
    }

    private function ensureCfoCompanyManager(): void
    {
        $user = Auth::user();

        if ($user?->isAdmin && strtolower(trim((string) $user->jabatan)) === 'cfo') {
            return;
        }

        abort(403, 'Master Perusahaan hanya dapat dikelola oleh CFO.');
    }
}
