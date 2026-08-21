<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CRM;
use App\Models\historyChat;
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
            // '62882001850221',
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
            3 => ['label' => 'Meeting Confirmed', 'color' => 'badge-success text-white'],
        ];

        return view('crm.index', compact('title', 'leads', 'stages', 'filter_layanan'));
    }

    public function getHistory($leadId)
    {
        try {
            $lead = CRM::find($leadId);

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead tidak ditemukan.'
                ], 404);
            }

            // Ambil history chat dari Supabase berdasarkan nomor telepon/session_id
            $chats = historyChat::where('session_id', $lead->session_id)
                        ->orderBy('created_at', 'asc')
                        ->get()
                        ->map(function ($chat) use ($lead) {
                            $msgData = $chat->message; // Sudah berbentuk array karena $casts di model
                            $type = $msgData['type'] ?? '';
                            
                            $textContent = '';
                            $isMe = false;

                            if ($type === 'ai') {
                                $isMe = true;
                                // Untuk 'ai', field 'content' berisi string JSON yang harus di-decode lagi
                                $rawContent = $msgData['content'] ?? '';
                                
                                if (is_string($rawContent)) {
                                    $decodedContent = json_decode($rawContent, true);
                                    // Ambil teks pesan dari sub-key "message" di dalam inner JSON
                                    $textContent = $decodedContent['message'] ?? $rawContent;
                                } else {
                                    $textContent = $rawContent;
                                }
                            } else {
                                // Untuk 'human', 'content' langsung berisi teks pesan dari customer
                                $isMe = false;
                                $textContent = $msgData['content'] ?? '';
                            }

                            return [
                                'sender' => $isMe ? 'me' : 'client',
                                'message' => $textContent,
                                'created_at' => $chat->created_at ? $chat->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') : '-'
                            ];
                        });

            return response()->json([
                'success' => true,
                'chats' => $chats
            ]);

        } catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
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
        // Cari data CRM
        $crm = CRM::findOrFail($id);

        // Validasi data
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'nama_usaha' => 'nullable|string|max:255',
            'layanan' => 'nullable|string|max:255',
            'kendala' => 'nullable|string',
            'kebutuhan' => 'nullable|string',
            'sosial_media' => 'nullable|string',
            'kesimpulan' => 'nullable|string',
            'meeting' => 'nullable|date',
            'link_meet' => 'nullable|url|max:500',
            'status' => 'required',
        ]);

        // Update data CRM
        $crm->update($validated);

        // Kembali ke halaman sebelumnya
        return redirect()
            ->back()
            ->with('success', 'Data CRM telah diperbarui.');
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
