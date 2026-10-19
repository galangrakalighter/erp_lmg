<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContentCompositionController extends Controller
{
    public function index(string $name)
    {
        $divisions = User::findOrFail(Auth::id())->userDivisions;
        $role = Auth::user()->isAdmin
            ? 'Super Admin'
            : DB::table('roles')->where('id', $divisions[0]->role_id)->value('role_name');

        $categoryId = $name === 'LGTR' ? 1 : 2;
        $data = DB::table('content_compositions')
            ->where('kategori_id', $categoryId)
            ->orderBy('nama')
            ->get();

        return view('cms.composition.index', [
            'data' => $data,
            'title' => 'Komposisi',
            'role' => $role,
            'activeProductCode' => $name,
        ]);
    }

    public function store(Request $request, string $name)
    {
        $validated = $this->validateRequest($request);
        $validated['kategori_id'] = $name === 'LGTR' ? 1 : 2;
        DB::table('content_compositions')->insert($validated);

        return redirect()->back()->with('success', 'Komposisi berhasil ditambahkan.');
    }

    public function update(int $id, Request $request)
    {
        DB::table('content_compositions')->where('id', $id)->update($this->validateRequest($request));

        return redirect()->back()->with('success', 'Komposisi berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        DB::table('content_compositions')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Komposisi berhasil dihapus.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'fungsi_singkat' => ['nullable', 'string', 'max:1000'],
            'contoh' => ['nullable', 'string', 'max:3000'],
            'status' => ['required', 'integer', 'in:1,2'],
        ]);
    }
}
