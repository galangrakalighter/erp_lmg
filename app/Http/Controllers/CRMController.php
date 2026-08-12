<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CRM;
class CRMController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = "CRM";
        $filter_layanan = CRM::whereNotNull('layanan')->distinct()->pluck('layanan');

        // Set default tanggal jika request kosong (2 hari terakhir: kemarin s/d hari ini)
        $startDate = $request->start_date ?? now()->subDay()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $excludedPhones = [
            '62882001850221',
            // '62895428171038',
            '6289637277465',
            '6283820602669',
            '6287837979874',
            '6283829287212',
            '6281252795716',
            '6285158860657',
            '6282142644868',
            '6285793602782',
            '62895365042133',
            '6287794444957'
        ];

        $leads = CRM::whereNotNull('chat_terbaru')
            ->whereNotIn('phone', $excludedPhones)
            ->when(true, function($query) use ($startDate, $endDate, $request) {
                $query->whereDate('chat_terbaru', '>=', $request->start_date ?? $startDate)
                    ->whereDate('chat_terbaru', '<=', $request->end_date ?? $endDate);
            })
            ->when($request->layanan, function($query) use ($request) {
                $query->where('layanan', $request->layanan);
            })
            ->when($request->stage, function($query) use ($request) {
                $query->where('status', $request->stage);
            })
            ->orderBy('chat_terbaru', 'desc')
            ->get();

        $stages = [
            1 => ['label' => 'In Progress', 'color' => 'badge-primary'],
            2 => ['label' => 'Meeting Schedule', 'color' => 'badge-warning text-white'],
        ];

        return view('crm.index', compact('title', 'leads', 'stages', 'filter_layanan'));
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
        //
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
        CRM::where("id", $id)->update([
            "project_value" => $request->project_value,
            "project_stage" => $request->project_stage_id
        ]);

        return redirect()->back()->with('success', "Data CRM Telah Di Perbarui");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = CRM::find($id);
        $data->delete();
        return redirect()->back()->with('success', "Data CRM Telah Di Hapus");
    }
}
