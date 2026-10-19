<?php

namespace App\Http\Controllers;

use App\Models\KPI;
use App\Models\ProdukClient;
use App\Models\userDivision;
use Illuminate\Http\Request;

class ClientKpiTimelineController extends Controller
{
    public function index(Request $request, int $client)
    {
        $clientModel = ProdukClient::query()->findOrFail($client);
        abort_if($clientModel->is_internal, 404);

        $actor = $request->user();
        $visibleUserIds = collect([$actor->id]);

        if (! $actor->isAdmin) {
            $visibleUserIds = $visibleUserIds
                ->merge(userDivision::query()->where('parent_id', $actor->id)->pluck('user_id'))
                ->unique()
                ->values();
        }

        $query = KPI::query()
            ->with(['pics:id,name,email', 'projectClient:id,nama'])
            ->where('project', $clientModel->id)
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('tanggal_deadline')
            ->orderBy('tanggal_mulai')
            ->orderBy('tanggal_deadline');

        if (! $actor->isAdmin) {
            $query->whereHas('pics', fn ($picQuery) => $picQuery->whereIn('users.id', $visibleUserIds));
        }

        $kpis = $query->get()->map(fn (KPI $kpi) => [
            'id' => $kpi->id,
            'nama_kpi' => $kpi->nama_kpi,
            'tanggal_mulai' => $kpi->tanggal_mulai,
            'tanggal_deadline' => $kpi->tanggal_deadline,
            'tanggal_approve' => $kpi->tanggal_approve,
            'status' => (int) $kpi->status,
            'point' => $kpi->point,
            'note' => $kpi->note,
            'pics' => $kpi->pics->map(fn ($pic) => [
                'id' => $pic->id,
                'name' => $pic->name,
                'email' => $pic->email,
            ])->values(),
        ])->values();

        return view('client.timeline', [
            'title' => 'Timeline KPI · '.$clientModel->nama,
            'client' => $clientModel,
            'kpis' => $kpis,
        ]);
    }
}
