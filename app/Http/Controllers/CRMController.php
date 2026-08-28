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
            2 => ['label' => 'Meeting Confirmed', 'color' => 'badge-success text-white'],
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
                    'message' => 'Lead tidak ditemukan.',
                ], 404);
            }

            /*
            * Memeriksa apakah data merupakan payload pencarian jadwal.
            * Mendukung array, object yang sudah menjadi array,
            * string JSON, dan JSON bertingkat.
            */
            $isSearchingSchedule = function ($value) use (&$isSearchingSchedule) {
                if (is_string($value)) {
                    $value = trim($value);

                    if ($value === '') {
                        return false;
                    }

                    // Jangan tampilkan jika sudah berupa teks ini.
                    if (strtolower($value) === 'sedang mencari jadwal') {
                        return true;
                    }

                    $decoded = json_decode($value, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $isSearchingSchedule($decoded);
                    }

                    return false;
                }

                if (!is_array($value)) {
                    return false;
                }

                /*
                * Contoh payload yang dideteksi:
                *
                * [
                *     [
                *         "requested_available" => false,
                *         "meeting" => null,
                *         "jadwal_wib" => null,
                *         "alternative_slots" => [...]
                *     ]
                * ]
                */

                $hasRequestedAvailable = array_key_exists(
                    'requested_available',
                    $value
                );

                $requestedAvailable = $hasRequestedAvailable
                    ? filter_var(
                        $value['requested_available'],
                        FILTER_VALIDATE_BOOLEAN,
                        FILTER_NULL_ON_FAILURE
                    )
                    : null;

                $requestedNotAvailable = $hasRequestedAvailable
                    && $requestedAvailable === false;

                $hasAlternativeSlots = array_key_exists(
                    'alternative_slots',
                    $value
                ) && is_array($value['alternative_slots']);

                if ($requestedNotAvailable || $hasAlternativeSlots) {
                    return true;
                }

                // Periksa seluruh isi secara rekursif.
                foreach ($value as $item) {
                    if ($isSearchingSchedule($item)) {
                        return true;
                    }
                }

                return false;
            };

            /*
            * Memeriksa apakah isi pesan kosong.
            *
            * Contoh yang dianggap kosong:
            * ""
            * null
            * []
            * "[ ]"
            * "[[]]"
            * {"message":[]}
            * {"message":""}
            * {"message":null}
            */
            $isEmptyMessage = function ($value) use (&$isEmptyMessage) {
                if ($value === null) {
                    return true;
                }

                if (is_string($value)) {
                    $value = trim($value);

                    if ($value === '') {
                        return true;
                    }

                    $decoded = json_decode($value, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $isEmptyMessage($decoded);
                    }

                    return false;
                }

                if (is_array($value)) {
                    if (empty($value)) {
                        return true;
                    }

                    // Jika mempunyai key message, periksa isinya.
                    if (array_key_exists('message', $value)) {
                        return $isEmptyMessage($value['message']);
                    }

                    /*
                    * Array dianggap kosong jika seluruh item
                    * di dalamnya juga kosong.
                    */
                    foreach ($value as $item) {
                        if (!$isEmptyMessage($item)) {
                            return false;
                        }
                    }

                    return true;
                }

                return false;
            };

            $chats = historyChat::where(
                    'session_id',
                    $lead->session_id
                )
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($chat) use (
                    $isSearchingSchedule,
                    $isEmptyMessage
                ) {
                    $msgData = $chat->message;

                    /*
                    * Antisipasi jika message masih berupa string JSON,
                    * meskipun model seharusnya sudah menggunakan cast array.
                    */
                    if (is_string($msgData)) {
                        $decodedMessage = json_decode(
                            $msgData,
                            true
                        );

                        if (json_last_error() === JSON_ERROR_NONE) {
                            $msgData = $decodedMessage;
                        }
                    }

                    // Data message yang tidak valid tidak ditampilkan.
                    if (!is_array($msgData)) {
                        return null;
                    }

                    $type = $msgData['type'] ?? '';
                    $rawContent = $msgData['content'] ?? '';
                    $textContent = '';
                    $isMe = false;

                    if ($type === 'ai') {
                        $isMe = true;

                        /*
                        * Content AI biasanya berbentuk:
                        *
                        * {
                        *     "message": "Isi pesan"
                        * }
                        */
                        if (is_string($rawContent)) {
                            $decodedContent = json_decode(
                                $rawContent,
                                true
                            );

                            if (
                                json_last_error() === JSON_ERROR_NONE
                                && is_array($decodedContent)
                            ) {
                                $textContent = $decodedContent['message']
                                    ?? $decodedContent;
                            } else {
                                $textContent = $rawContent;
                            }
                        } else {
                            $textContent = $rawContent;
                        }
                    } else {
                        // Pesan dari customer.
                        $isMe = false;
                        $textContent = $rawContent;
                    }

                    /*
                    * Jangan tampilkan payload pencarian jadwal.
                    * Pemeriksaan dilakukan pada seluruh kemungkinan lokasi.
                    */
                    if (
                        $isSearchingSchedule($msgData)
                        || $isSearchingSchedule($rawContent)
                        || $isSearchingSchedule($textContent)
                    ) {
                        return null;
                    }

                    /*
                    * Jangan tampilkan pesan kosong seperti:
                    * [], "", null, atau JSON dengan message kosong.
                    */
                    if ($isEmptyMessage($textContent)) {
                        return null;
                    }

                    /*
                    * Jika hasilnya masih berupa array, ambil key message.
                    * Jika tidak mempunyai key message, ubah menjadi JSON.
                    */
                    if (is_array($textContent)) {
                        if (array_key_exists('message', $textContent)) {
                            $textContent = $textContent['message'];
                        } else {
                            $textContent = json_encode(
                                $textContent,
                                JSON_UNESCAPED_UNICODE
                                | JSON_UNESCAPED_SLASHES
                            );
                        }
                    }

                    $textContent = trim((string) $textContent);

                    /*
                    * Pemeriksaan terakhir sebelum dikirim ke frontend.
                    */
                    if (
                        $textContent === ''
                        || $textContent === '[]'
                        || $textContent === '[ ]'
                        || strtolower($textContent)
                            === 'sedang mencari jadwal'
                    ) {
                        return null;
                    }

                    return [
                        'sender' => $isMe ? 'me' : 'client',
                        'message' => $textContent,
                        'created_at' => $chat->created_at
                            ? $chat->created_at
                                ->copy()
                                ->setTimezone('Asia/Jakarta')
                                ->format('d M Y, H:i')
                            : '-',
                    ];
                })

                /*
                * Hapus seluruh chat yang menghasilkan null.
                */
                ->filter(function ($chat) {
                    return $chat !== null;
                })

                /*
                * Susun ulang indeks array agar JSON menghasilkan
                * array biasa, bukan object dengan nomor indeks acak.
                */
                ->values();

            return response()->json([
                'success' => true,
                'chats' => $chats,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: '
                    . $e->getMessage(),
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
