<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CRM;
class CRMController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = "CRM";
        $stages = [
            1 => ['label' => 'In Progress', 'color' => 'bg-blue-100 text-blue-800'],
            2 => ['label' => 'Follow Up', 'color' => 'bg-yellow-100 text-yellow-800'],
            3 => ['label' => 'Lost', 'color' => 'bg-red-100 text-red-800'],
            4 => ['label' => 'Schedule', 'color' => 'bg-purple-100 text-purple-800'],
            5 => ['label' => 'Completed', 'color' => 'bg-green-100 text-green-800'],
        ];
        $leads = CRM::whereNotNull("tanggal_chat")->orderBy("tanggal_chat", 'DESC')->get();
        // foreach($leads as $lead){
        //     dd($lead->tanggal_chat);
        // }
        return view('crm.index', compact('title', 'leads', 'stages'));
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
