<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Social_Creator;
use App\Imports\AffiliatorImport;
class SosialCreatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = "Creator";
        $data = collect();
        $categories = Social_Creator::whereNotNull('category')->pluck('category')
            ->flatMap(fn ($item) => explode(',', $item))->map(fn ($item) => trim($item))
            ->filter()->unique()->sort()->values();
        return view('mcn.creator', compact('title', 'data', 'categories'));
    }

    public function dataApi(Request $request)
    {
        $query = Social_Creator::query();
        if ($request->filled('filterKategori')) {
            $query->whereRaw('LOWER(category) LIKE ?', ['%' . strtolower($request->filterKategori) . '%']);
        }
        if ($request->filled('filterLokasi')) {
            $location = strtolower($request->filterLokasi);
            $query->where(fn ($builder) => $builder
                ->whereRaw('LOWER(provinsi) LIKE ?', ['%' . $location . '%'])
                ->orWhereRaw('LOWER(domisili) LIKE ?', ['%' . $location . '%']));
        }
        if ($request->filled('filterGender')) {
            $query->whereRaw('LOWER(gender) = ?', [strtolower($request->filterGender)]);
        }
        if ($request->integer('filterFollowers') > 0) {
            $minimum = $request->integer('filterFollowers');
            $columns = match ($request->filterPlatform) {
                'tiktok' => ['fol_tt'],
                'instagram' => ['fol_ig'],
                'shopee' => ['fol_shopee'],
                default => ['fol_tt', 'fol_ig', 'fol_shopee'],
            };
            $query->where(function ($builder) use ($columns, $minimum) {
                foreach ($columns as $column) {
                    $builder->orWhere($column, '>=', $minimum);
                }
            });
        }
        if ($request->integer('filterGMV') > 0) {
            $minimum = $request->integer('filterGMV');
            $columns = $request->filterPlatformGMV === 'shopee' ? ['gmv_shopee']
                : ($request->filterPlatformGMV === 'tiktok' ? ['gmv_tt'] : ['gmv_tt', 'gmv_shopee']);
            $query->where(function ($builder) use ($columns, $minimum) {
                foreach ($columns as $column) {
                    $builder->orWhere($column, '>=', $minimum);
                }
            });
        }
        $paginator = $query->orderByDesc('id')->paginate(10)->withQueryString();
        $data = $paginator->getCollection();
        $title = 'Creator';
        $categories = Social_Creator::whereNotNull('category')->pluck('category')
            ->flatMap(fn ($item) => explode(',', $item))->map(fn ($item) => trim($item))
            ->filter()->unique()->sort()->values();
        $html = view('mcn.creator', compact('title', 'data', 'categories'))->render();
        return response()->json([
            'html' => $html,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
        ]);
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
        $data = $request->all();
        if ($request->has('categories_array')) {
            $data['category'] = implode(', ', $request->categories_array);
        } else {
            $data['category'] = null;
        }

        // 4. CEK BLACKLIST
        // Kita cari apakah ada ID creator yang terkait dengan username-username tersebut di tabel blacklist
        $isBlacklisted = Social_Creator::join('black_list', 'black_list.id_creator', '=', 'social_commerce_creator.id')
        ->where(function($query) use ($request) {
                if ($request->user_tt) $query->orWhere('social_commerce_creator.user_tt', $request->user_tt);
                if ($request->user_shopee) $query->orWhere('social_commerce_creator.user_shopee', $request->user_shopee);
                if ($request->user_ig) $query->orWhere('social_commerce_creator.user_ig', $request->user_ig);
            })->exists();

        if ($isBlacklisted) {
            return redirect()->back()->with('Error', 'Salah satu akun social media ini masuk dalam daftar Blacklist!');
        }

        $isDuplicate = Social_Creator::where(function($query) use ($request) {
            if ($request->user_tt) {
                $query->orWhere('user_tt', $request->user_tt);
            }
            if ($request->user_shopee) {
                $query->orWhere('user_shopee', $request->user_shopee);
            }
            if ($request->user_ig) {
                $query->orWhere('user_ig', $request->user_ig);
            }
        })->exists();

        if ($isDuplicate) {
            return redirect()->back()->with('Error', 'Creator sudah terdaftar (Username TikTok/Shopee/IG sudah ada di database)!');
        }

        Social_Creator::create($data);
        return redirect()->back()->with('success', 'Data Creator ' . $request->user_tt . ' berhasil disimpan!');
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
        $creator = Social_Creator::findOrFail($id);
        $data = $request->all();
        
        if ($request->has('categories_array')) {
            $data['category'] = implode(', ', $request->categories_array);
        } else {
            $data['category'] = null;
        }

        $creator->update($data);
        return redirect()->back()->with('success', 'Data @' . $creator->user_tt . ' berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $creator = Social_Creator::findOrFail($id);
            $username = $creator->user_tt;
            $creator->delete();

            return redirect()->back()->with('success', "Data Creator @$username berhasil dihapus!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Gagal menghapus data: " . $e->getMessage());
        }
    }

    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new AffiliatorImport, $request->file('file'));
            return back()->with('success', 'Data berhasil diimport!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
