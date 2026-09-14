<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Division;
use App\Models\UserDivision;
use App\Models\ProdukClient;
use App\Models\AuditTrail;
use App\Services\TrialBalanceService;

class DashboardController extends Controller
{
    public function index(){
        $data = DB::table('kategori_jasa')->get();
        $title = "Dashbard";
        return view('index', compact('title'));
    }

    public function dashboard(){
        $title = 'Dashboard';
        $user = Auth::user();

        $divisi = User::find(Auth::user()->id)->userDivisions;
        $role = $user->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]['role_id'])->value('role_name') : "Super Admin";
        $allUser = UserDivision::join('users', 'users.id', '=', 'user_division.user_id')->where('user_division.parent_id', $user->id)->orWhere('user_division.user_id', $user->id)->select('users.id', 'users.email')->get();
        if($user->isAdmin == true){
            $allUser = User::where('isAdmin', false)->get();
        }
        $divisiId = $user->isAdmin == false ? $divisi[0]->division_id : null;
        $produk =  DB::table('client_division')
            ->join('divisions', 'divisions.id', '=', 'client_division.division_id')
            ->join('produk_client', 'produk_client.id', '=', 'client_division.client_id');
            if($role != "Super Admin"){
                $client = $produk->join('user_client', 'user_client.client_id', '=', 'produk_client.id')
                ->where('user_client.user_id', $user->id);
            }
            $client = $produk->where('produk_client.status', 1)->select('produk_client.id as id_client', 'produk_client.nama as nama_client')
            ->get();
        
        if(Auth::user()->isAdmin == true){
            $divisiList = DB::table('divisions')->get();
        }else{    
            $divisiList = DB::table('divisions')->where('id', $divisiId)->get();
        }

        return view('hc.dashboard_new', compact('title', 'allUser', 'role', 'client', 'divisiId', 'divisiList'));
    }

    public function kpi_dashboard(){
        $title = 'Dashboard KPI';
        // Kita hanya perlu daftar project untuk dropdown
        if(Auth::user()->isAdmin == true){
            $listProject = ProdukClient::all();
        }else{
            $listProject = ProdukClient::whereIn('id', Auth::user()->kpis()->pluck('project'))->get();
        }

        $user = Auth::user();

        $divisi = User::find(Auth::user()->id)->userDivisions;
        $role = $user->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]['role_id'])->value('role_name') : "Super Admin";
        $listBawahan = UserDivision::join('users', 'users.id', '=', 'user_division.user_id')->where('user_division.parent_id', $user->id)->orWhere('user_division.user_id', $user->id)->select('users.id', 'users.email')->get();
        if($user->isAdmin == true){
            $listBawahan = User::where('isAdmin', false)->get();
        }

        if($role == 'Super Admin'){
            $listDivisi = Division::all();
        }else{
            $listDivisi = Division::where('id', $divisi[0]->division_id)->get();
        }
        
        return view('hc.kpi', compact('title', 'listProject', 'listBawahan', 'listDivisi'));
    }

    public function finance_dashboard(Request $request, $id, TrialBalanceService $trialBalanceService){
        $periode = $request->get('periode', date('Y-m'));
        $year    = date('Y', strtotime($periode));
        $month   = date('m', strtotime($periode));

        $title = 'Dashboard Finance';

        $perusahaan_baru = DB::table('perusahaan')->where('id', $id)->first();

        // Gunakan Neraca Saldo sebagai sumber utama agar saldo awal dan hanya
        // jurnal berstatus posted ikut dihitung secara konsisten.
        $trialBalance = collect(
            $trialBalanceService->getTrialBalance($periode, (int) $id)['report_data'] ?? []
        );

        $totalPendapatan = $trialBalance
            ->filter(fn ($row) => strtolower((string) ($row['category'] ?? '')) === 'pendapatan'
                || str_starts_with((string) $row['kode'], '4'))
            ->sum(fn ($row) => (float) $row['saldo_kredit'] - (float) $row['saldo_debit']);

        $totalBeban = $trialBalance
            ->filter(fn ($row) => strtolower((string) ($row['category'] ?? '')) === 'beban'
                || in_array(substr((string) $row['kode'], 0, 1), ['5', '6']))
            ->sum(fn ($row) => (float) $row['saldo_debit'] - (float) $row['saldo_kredit']);

        $totalProfit = $totalPendapatan - $totalBeban;

        // 2. HITUNG CASHFLOW (Arus Kas Masuk & Keluar)
        // Biasannya akun Kas atau Bank masuk ke kategori 'aset' (misal: Kas / Bank)
        // Anda bisa menyaring berdasarkan nama tipe 'aset' atau spesifik nama akun kas/bank
        $cashflowIn = DB::table('general_journal_details as gjd')
            ->join('general_journals as gj', 'gj.id', '=', 'gjd.general_journal_id')
            ->join('chart_of_account as coa', 'coa.id', '=', 'gjd.chart_of_account_id')
            ->where(function ($query) {
                $query->whereRaw("LOWER(COALESCE(coa.category, '')) = ?", ['aset'])
                    ->orWhere('coa.kode', 'like', '1%');
            })
            ->where('gj.status', 'posted')
            ->where('gj.perusahaan', $id)
            ->where('coa.perusahaan', $id)
            ->whereYear('gj.tgl_transaksi', $year)
            ->whereMonth('gj.tgl_transaksi', $month)
            ->sum('gjd.debit');

        $cashflowOut = DB::table('general_journal_details as gjd')
            ->join('general_journals as gj', 'gj.id', '=', 'gjd.general_journal_id')
            ->join('chart_of_account as coa', 'coa.id', '=', 'gjd.chart_of_account_id')
            ->where(function ($query) {
                $query->whereRaw("LOWER(COALESCE(coa.category, '')) = ?", ['aset'])
                    ->orWhere('coa.kode', 'like', '1%');
            })
            ->where('gj.status', 'posted')
            ->where('gj.perusahaan', $id)
            ->where('coa.perusahaan', $id)
            ->whereYear('gj.tgl_transaksi', $year)
            ->whereMonth('gj.tgl_transaksi', $month)
            ->sum('gjd.kredit');

        $netCashflow = $cashflowIn - $cashflowOut;

        // Posisi aset pada periode terpilih, termasuk opening balance.
        $mainBalances = $trialBalance
            ->filter(fn ($row) => strtolower((string) ($row['category'] ?? '')) === 'aset'
                || str_starts_with((string) $row['kode'], '1'))
            ->map(fn ($row) => (object) [
                'kode' => $row['kode'],
                'nama_akun' => $row['nama_akun'],
                'type' => $row['category'] ?: 'aset',
                'balance' => (float) $row['saldo_debit'] - (float) $row['saldo_kredit'],
            ])
            ->values();

        $totalSaldoUtama = $mainBalances->sum('balance');

        return view('finance.dashboard', compact(
            'periode',
            'totalPendapatan',
            'totalBeban',
            'totalProfit',
            'cashflowIn',
            'cashflowOut',
            'netCashflow',
            'mainBalances',
            'totalSaldoUtama',
            'perusahaan_baru',
            'title',
            'id'
        ));
    }

    public function audit(Request $request){
        $query = AuditTrail::with('user')->latest();

        // Filter berdasarkan modul
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filter berdasarkan aksi (CREATED, UPDATED, DELETED)
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter berdasarkan rentang tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        } elseif ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
        } elseif ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        // Ambil data unik modul untuk isi dropdown select option secara dinamis
        $modules = AuditTrail::select('module')->distinct()->whereNotNull('module')->pluck('module');

        $audits = $query->paginate(15)->appends($request->query());

        $title = 'Audit Keuangan';

        return view('finance.audit', compact('audits', 'modules', 'title'));
    }

    public function getDataAudit(Request $request){
        $query = AuditTrail::with('user')->latest();

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $audits = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $audits
        ]);
    }
}
