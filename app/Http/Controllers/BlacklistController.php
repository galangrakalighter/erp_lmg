<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blacklist;
use App\Models\Social_Creator;
class BlacklistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = "Blacklist";
        $data = collect();
        $months = Blacklist::whereNotNull('bulan')->distinct()->orderBy('bulan')->pluck('bulan');
        $jobs = Blacklist::whereNotNull('job')->distinct()->orderBy('job')->pluck('job');
        $blacklistIds = Blacklist::pluck('id_creator')->toArray();
        $creators = Social_Creator::whereNotIn('id', $blacklistIds)->get();
        $creators_blacklist = collect();
        return view('mcn.blacklist', compact('title', 'data', 'creators', 'creators_blacklist', 'months', 'jobs'));
    }

    public function dataApi(Request $request)
    {
        $query = Blacklist::leftjoin('social_commerce_creator', 'social_commerce_creator.id', '=', 'black_list.id_creator')
        ->select('black_list.*', 'social_commerce_creator.user_tt', 'social_commerce_creator.url_tt', 'social_commerce_creator.user_ig', 'social_commerce_creator.url_ig', 'social_commerce_creator.user_shopee', 'social_commerce_creator.url_shopee', 'social_commerce_creator.alamat', 'social_commerce_creator.contact')
        ->when($request->filled('filterBulan'), fn ($builder) => $builder->where('black_list.bulan', $request->filterBulan))
        ->when($request->filled('filterJob'), fn ($builder) => $builder->where('black_list.job', $request->filterJob))
        ->when($request->filled('filterKet'), fn ($builder) => $builder->where('black_list.keterangan', $request->filterKet));
        $paginator = $query->orderBy('black_list.bulan')->paginate(10)->withQueryString();
        $data = $paginator->getCollection();
        $creators = collect();
        $creators_blacklist = Social_Creator::all();
        $months = collect();
        $jobs = collect();
        $title = 'Blacklist';
        $html = view('mcn.blacklist', compact('title', 'data', 'creators', 'creators_blacklist', 'months', 'jobs'))->render();
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
        try {
            Blacklist::create($request->all());
            return redirect()->back()->with('success', 'Data Blacklist berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
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
            $data = Blacklist::findOrFail($id);

            // 3. Update data
            $data->update([
                'id_creator'  => (int)$request->id_creator,
                'bulan'       => strtoupper($request->bulan), // Paksa ke huruf kapital agar rapi
                'job'         => $request->job,
                'keterangan'  => (int)$request->keterangan,
                'link_report' => $request->link_report,
                'note' => $request->note
            ]);

            // 4. Redirect dengan pesan sukses
            return redirect()->back()->with('success', 'Data affiliator berhasil diperbarui!');
        } catch (\Exception $e) {
            // Jika ada error database
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Blacklist::find($id);
        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan atau sudah dihapus sebelumnya.');
        }

        try {
            $data->delete();

            return redirect()->back()->with('success', "Data berhasil dihapus");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
