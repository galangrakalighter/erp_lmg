<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Division;
use App\Models\userDivision as UserDivision;
use App\Models\ProdukClient;
use App\Support\DashboardAccess;
use App\Models\AuditTrail;
use App\Services\TrialBalanceService;
use Illuminate\Support\Carbon;
use Throwable;

class DashboardController extends Controller
{
    public function index(Request $request){
        $title = "Dashboard";
        $dashboardData = $this->executiveDashboardData($request);

        switch (Auth::user()->jabatan) {
            case "ceo":
                return view('dashboard.ceo', array_merge(compact('title'), $dashboardData));
            case "cfo":
                return view('dashboard.cfo', array_merge(compact('title'), $dashboardData));
            case "coo":
                return view('dashboard.coo', array_merge(compact('title'), $dashboardData));
            default:
                return view('index', compact('title'));
        }
    }

    private function executiveDashboardData(Request $request): array
    {
        $today = Carbon::today();
        $period = $request->string('period')->toString();

        try {
            $selectedPeriod = $period !== ''
                ? Carbon::createFromFormat('Y-m', $period)->startOfMonth()
                : $today->copy()->startOfMonth();
        } catch (Throwable) {
            $selectedPeriod = $today->copy()->startOfMonth();
        }

        $period = $selectedPeriod->format('Y-m');
        $monthStart = $selectedPeriod->toDateString();
        $monthEnd = $selectedPeriod->copy()->endOfMonth()->toDateString();
        $selectedDivisionId = $request->integer('division_id') ?: null;
        $filterDivisions = Division::query()->orderBy('name')->get(['id', 'name']);

        $clients = ProdukClient::query()->external();
        $activeClients = (clone $clients)->where('status', 1)->count();
        $totalClients = (clone $clients)->count();
        $totalEmployees = User::query()->where('isAdmin', false)->count();

        $taskBase = DB::table('task');
        $tasksToday = (clone $taskBase)->when($selectedDivisionId, fn ($query) => $query->where('divisi', $selectedDivisionId))->whereDate('tanggal_mulai', $today)->count();
        $tasksDoneThisMonth = (clone $taskBase)->when($selectedDivisionId, fn ($query) => $query->where('divisi', $selectedDivisionId))
            ->where('status', 4)
            ->whereBetween('tanggal_approve', [$monthStart, $monthEnd])
            ->count();
        $tasksAwaitingApproval = (clone $taskBase)->when($selectedDivisionId, fn ($query) => $query->where('divisi', $selectedDivisionId))->where('status', 3)->count();
        $overdueTasks = (clone $taskBase)->when($selectedDivisionId, fn ($query) => $query->where('divisi', $selectedDivisionId))
            ->where('status', '!=', 4)
            ->whereDate('tanggal_deadline', '<', $today)
            ->count();

        $divisionPerformance = DB::table('divisions as d')
            ->leftJoin('task as t', 't.divisi', '=', 'd.id')
            ->selectRaw("d.id, d.name, COUNT(t.id) as total_tasks, SUM(CASE WHEN t.status = 4 THEN 1 ELSE 0 END) as completed_tasks, SUM(CASE WHEN t.status = 3 THEN 1 ELSE 0 END) as awaiting_approval, SUM(CASE WHEN t.status <> 4 AND t.tanggal_deadline < ? THEN 1 ELSE 0 END) as overdue_tasks", [$today->toDateString()])
            ->groupBy('d.id', 'd.name')
            ->when($selectedDivisionId, fn ($query) => $query->where('d.id', $selectedDivisionId))
            ->orderBy('d.name')
            ->get();

        $clientByDivision = DB::table('client_division as cd')
            ->join('divisions as d', 'd.id', '=', 'cd.division_id')
            ->join('produk_client as pc', 'pc.id', '=', 'cd.client_id')
            ->where('pc.status', 1)
            ->whereRaw("LOWER(TRIM(pc.nama)) <> 'internal'")
            ->when($selectedDivisionId, fn ($query) => $query->where('d.id', $selectedDivisionId))
            ->selectRaw('d.name, COUNT(DISTINCT pc.id) as clients')
            ->groupBy('d.id', 'd.name')
            ->orderByDesc('clients')
            ->get();

        $finance = $this->executiveFinanceSummary($monthStart, $monthEnd, $selectedDivisionId);

        $upcomingTasks = DB::table('task as t')
            ->leftJoin('divisions as d', 'd.id', '=', 't.divisi')
            ->leftJoin('produk_client as pc', 'pc.id', '=', 't.project')
            ->leftJoin('users as u', 'u.id', '=', 't.pic')
            ->where('t.status', '!=', 4)
            ->when($selectedDivisionId, fn ($query) => $query->where('t.divisi', $selectedDivisionId))
            ->orderBy('t.tanggal_deadline')
            ->limit(8)
            ->get(['t.nama_task', 't.status', 't.tanggal_deadline', 'd.name as division_name', 'pc.nama as project_name', 'u.email as pic_email']);

        $outstandingInvoices = DB::table('invoices as i')
            ->join('produk_client as pc', 'pc.id', '=', 'i.customer_id')
            ->whereColumn('i.paid_amount', '<', 'i.total_amount')
            ->when($selectedDivisionId, function ($query) use ($selectedDivisionId) {
                $query->whereExists(function ($subQuery) use ($selectedDivisionId) {
                    $subQuery->selectRaw('1')->from('client_division as cd')
                        ->whereColumn('cd.client_id', 'i.customer_id')
                        ->where('cd.division_id', $selectedDivisionId);
                });
            })
            ->orderBy('i.due_date')
            ->limit(8)
            ->get(['i.no_invoice', 'i.total_amount', 'i.paid_amount', 'i.due_date', 'i.status', 'pc.nama as client_name']);

        $payrollThisMonth = DB::table('payroll')
            ->whereBetween('payroll_date', [$monthStart, $monthEnd])
            ->when($selectedDivisionId, function ($query) use ($selectedDivisionId) {
                $query->whereExists(function ($subQuery) use ($selectedDivisionId) {
                    $subQuery->selectRaw('1')->from('user_division as ud')
                        ->whereColumn('ud.user_id', 'payroll.user_id')
                        ->where('ud.division_id', $selectedDivisionId);
                });
            })
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total, COALESCE(SUM(paid_amount), 0) as paid')
            ->first();

        return compact(
            'today',
            'period',
            'selectedDivisionId',
            'filterDivisions',
            'activeClients',
            'totalClients',
            'totalEmployees',
            'tasksToday',
            'tasksDoneThisMonth',
            'tasksAwaitingApproval',
            'overdueTasks',
            'divisionPerformance',
            'clientByDivision',
            'finance',
            'upcomingTasks',
            'outstandingInvoices',
            'payrollThisMonth',
        );
    }

    private function executiveFinanceSummary(string $monthStart, string $monthEnd, ?int $divisionId = null): array
    {
        $invoiceQuery = DB::table('invoices as i');

        if ($divisionId) {
            $invoiceQuery->whereExists(function ($query) use ($divisionId) {
                $query->selectRaw('1')->from('client_division as cd')
                    ->whereColumn('cd.client_id', 'i.customer_id')
                    ->where('cd.division_id', $divisionId);
            });
        }

        $invoice = $invoiceQuery
            ->selectRaw('COALESCE(SUM(total_amount), 0) as billed, COALESCE(SUM(paid_amount), 0) as collected')
            ->first();

        $monthlyPayments = DB::table('invoice_payments as ip')
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->when($divisionId, function ($query) use ($divisionId) {
                $query->whereExists(function ($subQuery) use ($divisionId) {
                    $subQuery->selectRaw('1')->from('invoices as i')
                        ->join('client_division as cd', 'cd.client_id', '=', 'i.customer_id')
                        ->whereColumn('i.id', 'ip.invoice_id')
                        ->where('cd.division_id', $divisionId);
                });
            });
        $monthlyCollected = $monthlyPayments->sum('amount');

        $income = 0.0;
        $expense = 0.0;

        try {
            $journal = DB::table('general_journal_details as gjd')
                ->join('general_journals as gj', 'gj.id', '=', 'gjd.general_journal_id')
                ->join('chart_of_account as coa', 'coa.id', '=', 'gjd.chart_of_account_id')
                ->where('gj.status', 'posted')
                ->whereBetween('gj.tgl_transaksi', [$monthStart, $monthEnd])
                ->selectRaw("COALESCE(SUM(CASE WHEN LOWER(COALESCE(coa.category, '')) = 'pendapatan' OR coa.kode LIKE '4%' THEN gjd.kredit - gjd.debit ELSE 0 END), 0) as income")
                ->selectRaw("COALESCE(SUM(CASE WHEN LOWER(COALESCE(coa.category, '')) = 'beban' OR coa.kode LIKE '5%' OR coa.kode LIKE '6%' THEN gjd.debit - gjd.kredit ELSE 0 END), 0) as expense")
                ->first();

            $income = (float) ($journal->income ?? 0);
            $expense = (float) ($journal->expense ?? 0);
        } catch (Throwable) {
            // Dashboard tetap dapat dipakai pada instalasi yang belum memiliki jurnal lengkap.
        }

        return [
            'billed' => (float) ($invoice->billed ?? 0),
            'collected' => (float) ($invoice->collected ?? 0),
            'outstanding' => max(0, (float) ($invoice->billed ?? 0) - (float) ($invoice->collected ?? 0)),
            'monthly_collected' => (float) $monthlyCollected,
            'income' => $income,
            'expense' => $expense,
            'profit' => $income - $expense,
        ];
    }

    public function dashboard(){
        $title = 'Dashboard';
        $user = Auth::user();

        $divisi = User::find(Auth::user()->id)->userDivisions;
        $role = $user->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]['role_id'])->value('role_name') : "Super Admin";
        $selfOnly = DashboardAccess::isSelfOnly($user);
        $allUser = UserDivision::join('users', 'users.id', '=', 'user_division.user_id')->where('user_division.parent_id', $user->id)->orWhere('user_division.user_id', $user->id)->select('users.id', 'users.email')->get();
        if($user->isAdmin == true){
            $allUser = User::unlocked()->where('isAdmin', false)->get();
        } elseif ($selfOnly) {
            $allUser = collect([(object) ['id' => $user->id, 'email' => $user->email]]);
        }
        $divisiId = $user->isAdmin == false ? $divisi[0]->division_id : null;
        $produk =  DB::table('client_division')
            ->join('divisions', 'divisions.id', '=', 'client_division.division_id')
            ->join('produk_client', 'produk_client.id', '=', 'client_division.client_id');
            if($role != "Super Admin"){
                $client = $produk->join('user_client', 'user_client.client_id', '=', 'produk_client.id')
                ->where('user_client.user_id', $user->id);
            }
            $client = $produk->where('client_division.status', 1)->select('produk_client.id as id_client', 'produk_client.nama as nama_client')
            ->get();
            $internalProjects = ProdukClient::query()->visibleTo($user)->internal()->where('status', 1)
                ->select('id as id_client', 'nama as nama_client')->get();
            $client = $client->concat($internalProjects)->unique('id_client')->values();
        
        if(Auth::user()->isAdmin == true){
            $divisiList = DB::table('divisions')->get();
        }else{    
            $divisiList = DB::table('divisions')->where('id', $divisiId)->get();
        }

        return view('hc.dashboard_new', compact('title', 'allUser', 'role', 'client', 'divisiId', 'divisiList', 'selfOnly'));
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
        $selfOnly = DashboardAccess::isSelfOnly($user);
        $listBawahan = UserDivision::join('users', 'users.id', '=', 'user_division.user_id')->where('user_division.parent_id', $user->id)->orWhere('user_division.user_id', $user->id)->select('users.id', 'users.email')->get();
        if($user->isAdmin == true){
            $listBawahan = User::unlocked()->where('isAdmin', false)->get();
        } elseif ($selfOnly) {
            $listBawahan = collect([(object) ['id' => $user->id, 'email' => $user->email]]);
        }

        if($role == 'Super Admin'){
            $listDivisi = Division::all();
        }else{
            $listDivisi = Division::where('id', $divisi[0]->division_id)->get();
        }
        
        return view('hc.kpi', compact('title', 'listProject', 'listBawahan', 'listDivisi', 'selfOnly'));
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
