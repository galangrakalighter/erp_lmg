<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\MarketResearch\MarketResearchService;
class marketResearchController extends Controller
{
    protected $market;
    public function __construct(MarketResearchService $market)
    {
        $this->market = $market;
    }

    public function index($id, $platform){
        $data = $this->market->getMarket((int)$id, $platform);
        $divisi = User::find(Auth::user()->id)->userDivisions;
        $role = Auth::user()->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]->role_id)->value('role_name') : "Super Admin";
        $title = "Market Research";
        // dd($data['data']);
        $arr=[];
        foreach($data['data'][3] as $d){
            array_push($arr, $d);
        }
        $produk = DB::table('produk_client')->where('id', $id)->first();
        $halaman = "Market Research";
        $readOnly = ! Auth::user()->isAdmin && $divisi->contains(
            fn ($row) => strtolower(trim((string) $row->division?->name)) === 'multimedia'
        );
        return view('market.index', compact('data', 'title', 'produk', 'platform', 'halaman', 'role', 'readOnly'));
    }
}
