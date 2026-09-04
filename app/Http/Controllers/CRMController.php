<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CRM;
use App\Models\CRM_HUMAN;
use App\Models\Meeting;
use App\Models\historyChat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class CRMController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('crm.index', [
            'title' => 'CRM',
        ]);
    }

    public function aiData(Request $request)
    {
        try {
            \Log::info('API CRM AI dimulai', [
                'query' => $request->query(),
            ]);

            $startDate = $request->input(
                'start_date',
                now('Asia/Jakarta')
                    ->subDay()
                    ->toDateString()
            );

            $endDate = $request->input(
                'end_date',
                now('Asia/Jakarta')
                    ->toDateString()
            );

            // Pada tabel crm_baru, waktu chat tersimpan di kolom
            // chat_terbaru (padanan tanggal_chat pada CRM Human).
            $query = CRM::query()
                ->whereNotNull('chat_terbaru')
                ->whereDate(
                    'chat_terbaru',
                    '>=',
                    $startDate
                )
                ->whereDate(
                    'chat_terbaru',
                    '<=',
                    $endDate
                );

            if ($request->filled('layanan')) {
                $query->where(
                    'layanan',
                    $request->layanan
                );
            }

            if ($request->filled('stage')) {
                $query->where(
                    'status',
                    $request->stage
                );
            }

            $leads = $query
                ->orderByDesc('chat_terbaru')
                ->get()
                ->map(function ($lead) {
                    return [
                        'id' => $lead->id,
                        'nama' => $lead->nama,
                        'brand' => $lead->nama_usaha,
                        'email' => $lead->email,
                        'phone' => $lead->phone,
                        'kategori' => $lead->kategori,
                        'layanan' => $lead->layanan,
                        'kendala' => $lead->kendala,
                        'kebutuhan' => $lead->kebutuhan,
                        'kesimpulan' => $lead->kesimpulan,
                        'sosial_media' => $lead->sosial_media,
                        'prospek' => $lead->hot_prospek
                            ? 'hot'
                            : 'warm',
                        'status' => $lead->status,
                        'status_label' =>
                            $this->aiStatusLabel(
                                $lead->status
                            ),
                        'chat_terbaru' =>
                            $this->dateWib(
                                $lead->chat_terbaru
                            ),
                        'tanggal_chat' =>
                            $this->dateWib(
                                $lead->chat_terbaru
                            ),
                        'created_at' =>
                            $this->dateWib(
                                $lead->created_at
                            ),
                        'meeting' =>
                            $this->dateWib(
                                $lead->meeting
                            ),
                    ];
                })
                ->values();

            $services = CRM::query()
                ->whereNotNull('layanan')
                ->distinct()
                ->orderBy('layanan')
                ->pluck('layanan')
                ->filter()
                ->values();

            \Log::info('API CRM AI selesai', [
                'total' => $leads->count(),
            ]);

            return response()->json([
                'success' => true,
                'total' => $leads->count(),
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'services' => $services,
                    'statuses' => [
                        [
                            'value' => '1',
                            'label' => 'In Progress',
                        ],
                        [
                            'value' => '2',
                            'label' => 'Meeting Scheduled',
                        ],
                        [
                            'value' => '3',
                            'label' => 'Meeting Confirmed',
                        ],
                    ],
                ],
                'data' => $leads,
            ]);
        } catch (\Throwable $e) {
            \Log::error('API CRM AI gagal', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API data CRM Human.
     */
    public function humanData(Request $request)
    {
        try {
            \Log::info(
                'API CRM Human dimulai',
                [
                    'query' =>
                        $request->query(),
                ]
            );

            $startDate = $request->input(
                'start_date',
                now('Asia/Jakarta')
                    ->subDay()
                    ->toDateString()
            );

            $endDate = $request->input(
                'end_date',
                now('Asia/Jakarta')
                    ->toDateString()
            );

            $query = CRM_HUMAN::query()
                ->whereNotNull(
                    'tanggal_chat'
                )
                ->whereDate(
                    'tanggal_chat',
                    '>=',
                    $startDate
                )
                ->whereDate(
                    'tanggal_chat',
                    '<=',
                    $endDate
                );

            if (
                $request->filled('layanan')
            ) {
                $query->where(
                    'layanan',
                    $request->layanan
                );
            }

            if (
                $request->filled('stage')
            ) {
                $query->where(
                    'status',
                    $request->stage
                );
            }

            /*
            * Ambil data Human terlebih dahulu.
            */
            $humanLeads = $query
                ->orderByDesc(
                    'tanggal_chat'
                )
                ->get();

            /*
            * Ambil seluruh id_proteksi yang valid.
            */
            $idProteksiList = $humanLeads
                ->pluck('id_proteksi')
                ->filter(function (
                    $idProteksi
                ) {
                    return (
                        $idProteksi !== null
                        && trim(
                            (string) $idProteksi
                        ) !== ''
                    );
                })
                ->map(function (
                    $idProteksi
                ) {
                    return trim(
                        (string) $idProteksi
                    );
                })
                ->unique()
                ->values();

            /*
            * Cari session_id AI yang cocok.
            *
            * Hanya mengambil session_id,
            * sehingga query tetap ringan.
            */
            if ($idProteksiList->isEmpty()) {
                $aiSessionIds = collect();
            } else {
                $aiSessionIds = CRM::query()
                    ->whereNotNull(
                        'session_id'
                    )
                    ->whereIn(
                        'session_id',
                        $idProteksiList
                            ->all()
                    )
                    ->pluck('session_id')
                    ->map(function (
                        $sessionId
                    ) {
                        return trim(
                            (string) $sessionId
                        );
                    })
                    ->unique()
                    ->flip();
            }

            /*
            * Susun response Human.
            */
            $leads = $humanLeads
                ->map(function (
                    $lead
                ) use (
                    $aiSessionIds
                ) {
                    $idProteksi = trim(
                        (string) (
                            $lead->id_proteksi
                            ?? ''
                        )
                    );

                    /*
                    * Bernilai true apabila:
                    *
                    * Human.id_proteksi ditemukan
                    * pada AI.session_id.
                    */
                    $sumberAi =
                        $idProteksi !== ''
                        && $aiSessionIds->has(
                            $idProteksi
                        );

                    return [
                        'id' =>
                            $lead->getKey(),

                        'id_proteksi' =>
                            $lead->id_proteksi,

                        'sumber_ai' =>
                            $sumberAi,

                        'nama' =>
                            $lead->name,

                        'brand' =>
                            $lead->brand,

                        'phone' =>
                            $lead->phone,

                        'kategori' =>
                            $lead->kategori,

                        'layanan' =>
                            $lead->layanan,

                        'kebutuhan' =>
                            $lead->kebutuhan,

                        'note' =>
                            $lead->note,

                        'sosial_media' =>
                            $this->socialMedia(
                                $lead
                                    ->sosial_media
                            ),

                        'prospek' =>
                            $lead->prospek,

                        'status' =>
                            $lead->status,
                        
                        'chanelling' => $lead->chanelling,

                        'can_push' =>
                            (bool)
                            $lead->can_push,

                        'tanggal_chat' =>
                            $this->dateWib(
                                $lead
                                    ->tanggal_chat
                            ),

                        'created_at' =>
                            $this->dateWib(
                                $lead
                                    ->created_at
                            ),

                        'service_plan' =>
                            (bool) (
                                $lead->service_plan
                                ?? false
                            ),

                        'need_analysys' =>
                            (bool) (
                                $lead->need_analysys
                                ?? false
                            ),

                        'done_meeting' =>
                            (bool) (
                                $lead->done_meeting
                                ?? false
                            ),

                        'jumlah_follow_up' =>
                            min(6, max(0, (int) (
                                $lead->jumlah_follow_up
                                ?? 0
                            ))),

                        'today' =>
                            (bool) (
                                $lead->today
                                ?? false
                            ),
                    ];
                })
                ->values();

            $services = CRM_HUMAN::query()
                ->whereNotNull('layanan')
                ->distinct()
                ->orderBy('layanan')
                ->pluck('layanan')
                ->filter()
                ->values();

            /*
             * Selalu kirim seluruh pilihan status Human,
             * meskipun belum ada data untuk salah satunya.
             */
            $statuses = collect([
                ['value' => 'meeting confirm', 'label' => 'Meeting Confirm'],
                ['value' => 'meeting schedule', 'label' => 'Meeting Schedule'],
                ['value' => 'on progress', 'label' => 'On Progress'],
                ['value' => 'after meeting', 'label' => 'After Meeting'],
                ['value' => 'administrasi progress', 'label' => 'Administrasi Progress'],
                ['value' => 'ghosting', 'label' => 'Ghosting'],
                ['value' => 'no respon', 'label' => 'No Respon'],
                ['value' => 'deals', 'label' => 'Deals'],
                ['value' => 'no deals', 'label' => 'No Deals'],
            ]);

            $totalSumberAi = $leads
                ->where(
                    'sumber_ai',
                    true
                )
                ->count();

            \Log::info(
                'API CRM Human selesai',
                [
                    'total' =>
                        $leads->count(),

                    'total_sumber_ai' =>
                        $totalSumberAi,
                ]
            );

            return response()->json([
                'success' => true,
                'total' =>
                    $leads->count(),

                'total_sumber_ai' =>
                    $totalSumberAi,

                'filters' => [
                    'start_date' =>
                        $startDate,

                    'end_date' =>
                        $endDate,

                    'services' =>
                        $services,

                    'statuses' =>
                        $statuses,
                ],

                'data' => $leads,
            ]);
        } catch (\Throwable $e) {
            \Log::error(
                'API CRM Human gagal',
                [
                    'message' =>
                        $e->getMessage(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API jadwal meeting yang bersumber dari CRM Human.
     */
    public function meetingData(Request $request)
    {
        try {
            $startDate = $request->input(
                'start_date',
                now('Asia/Jakarta')->toDateString()
            );

            $endDate = $request->input(
                'end_date',
                now('Asia/Jakarta')->addDays(6)->toDateString()
            );

            $query = Meeting::query()
                ->with('crmHuman')
                ->whereDate('waktu_meeting', '>=', $startDate)
                ->whereDate('waktu_meeting', '<=', $endDate);

            if ($request->filled('layanan')) {
                $query->whereHas('crmHuman', function ($humanQuery) use ($request) {
                    $humanQuery->where('layanan', $request->layanan);
                });
            }

            if ($request->filled('stage')) {
                $query->whereHas('crmHuman', function ($humanQuery) use ($request) {
                    $humanQuery->where('status', $request->stage);
                });
            }

            $leads = $query
                ->orderBy('waktu_meeting')
                ->get()
                ->map(function ($meeting) {
                    $lead = $meeting->crmHuman;

                    return [
                        'id' => $meeting->getKey(),
                        'id_crm_human' => $meeting->id_crm_human,
                        'nama' => $lead?->name,
                        'brand' => $lead?->brand,
                        'phone' => $lead?->phone,
                        'layanan' => $lead?->layanan,
                        'status' => $lead?->status,
                        'waktu_meeting' => $this->dateWib(
                            $meeting->waktu_meeting
                        ),
                        'link_meeting' => $meeting->link_meeting,
                    ];
                })
                ->values();

            $services = Meeting::query()
                ->join('crm_human', 'meeting.id_crm_human', '=', 'crm_human.id')
                ->whereNotNull('crm_human.layanan')
                ->distinct()
                ->orderBy('crm_human.layanan')
                ->pluck('crm_human.layanan')
                ->filter()
                ->values();

            $statuses = Meeting::query()
                ->join('crm_human', 'meeting.id_crm_human', '=', 'crm_human.id')
                ->whereNotNull('crm_human.status')
                ->distinct()
                ->orderBy('crm_human.status')
                ->pluck('crm_human.status')
                ->filter()
                ->map(function ($status) {
                    return [
                        'value' => (string) $status,
                        'label' => (string) $status,
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'total' => $leads->count(),
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'services' => $services,
                    'statuses' => $statuses,
                ],
                'data' => $leads,
            ]);
        } catch (\Throwable $e) {
            \Log::error('API jadwal meeting gagal', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function dateWib($date): ?string
    {
        if (!$date) {
            return null;
        }

        return $date
            ->copy()
            ->setTimezone('Asia/Jakarta')
            ->format('d M Y, H:i') . ' WIB';
    }

    private function aiStatusLabel($status): string
    {
        return match ((int) $status) {
            1 => 'In Progress',
            2 => 'Meeting Scheduled',
            3 => 'Meeting Confirmed',
            default => 'Unknown',
        };
    }

     private function socialMedia($value): array
    {
        $result = [
            'instagram' => null,
            'facebook' => null,
            'shopee' => null,
        ];

        if (empty($value)) {
            return $result;
        }

        if (is_array($value)) {
            return array_merge($result, $value);
        }

        if (is_object($value)) {
            return array_merge(
                $result,
                (array) $value
            );
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (
                json_last_error() === JSON_ERROR_NONE &&
                is_array($decoded)
            ) {
                return array_merge(
                    $result,
                    $decoded
                );
            }

            $result['lainnya'] = $value;
        }

        return $result;
    }

    /**
     * Mendukung sosial media JSON maupun string lama.
     */

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
            * Memeriksa apakah suatu array merupakan list,
            * contohnya:
            *
            * [
            *     ["row_number" => 1],
            *     ["row_number" => 2],
            * ]
            */
            $isListArray = function (array $value): bool {
                if (function_exists('array_is_list')) {
                    return array_is_list($value);
                }

                return array_keys($value)
                    === range(0, count($value) - 1);
            };

            /*
            * Memeriksa payload pencarian jadwal.
            */
            $isSearchingSchedule = function (
                $value
            ) use (
                &$isSearchingSchedule
            ): bool {
                if (is_string($value)) {
                    $value = trim($value);

                    if ($value === '') {
                        return false;
                    }

                    if (
                        strtolower($value)
                        === 'sedang mencari jadwal'
                    ) {
                        return true;
                    }

                    $decoded = json_decode(
                        $value,
                        true
                    );

                    if (
                        json_last_error()
                        === JSON_ERROR_NONE
                        && is_array($decoded)
                    ) {
                        return $isSearchingSchedule(
                            $decoded
                        );
                    }

                    return false;
                }

                if (!is_array($value)) {
                    return false;
                }

                $hasRequestedAvailable =
                    array_key_exists(
                        'requested_available',
                        $value
                    );

                $requestedAvailable =
                    $hasRequestedAvailable
                        ? filter_var(
                            $value[
                                'requested_available'
                            ],
                            FILTER_VALIDATE_BOOLEAN,
                            FILTER_NULL_ON_FAILURE
                        )
                        : null;

                if (
                    $hasRequestedAvailable
                    && $requestedAvailable === false
                ) {
                    return true;
                }

                if (
                    array_key_exists(
                        'alternative_slots',
                        $value
                    )
                    && is_array(
                        $value[
                            'alternative_slots'
                        ]
                    )
                ) {
                    return true;
                }

                foreach ($value as $item) {
                    if (
                        $isSearchingSchedule($item)
                    ) {
                        return true;
                    }
                }

                return false;
            };

            /*
            * Mengambil pesan AI yang valid.
            *
            * Format yang diterima:
            * - string biasa
            * - {"message":"Isi pesan"}
            *
            * Format yang ditolak:
            * - JSON array
            * - object tanpa key message
            * - array data tool
            */
            $extractAiMessage = function (
                $content
            ) use (
                $isListArray
            ): ?string {
                if (is_array($content)) {
                    if ($isListArray($content)) {
                        return null;
                    }

                    if (
                        !array_key_exists(
                            'message',
                            $content
                        )
                        || !is_string(
                            $content['message']
                        )
                    ) {
                        return null;
                    }

                    return trim(
                        $content['message']
                    );
                }

                if (!is_string($content)) {
                    return null;
                }

                $content = trim($content);

                if ($content === '') {
                    return null;
                }

                $decoded = json_decode(
                    $content,
                    true
                );

                if (
                    json_last_error()
                    !== JSON_ERROR_NONE
                ) {
                    return $content;
                }

                /*
                * JSON string seperti:
                * "Halo, ada yang bisa dibantu?"
                */
                if (is_string($decoded)) {
                    return trim($decoded);
                }

                if (!is_array($decoded)) {
                    return null;
                }

                /*
                * JSON list berarti biasanya output tool.
                */
                if ($isListArray($decoded)) {
                    return null;
                }

                /*
                * Object AI hanya diterima jika memiliki
                * key message dengan nilai string.
                */
                if (
                    !array_key_exists(
                        'message',
                        $decoded
                    )
                    || !is_string(
                        $decoded['message']
                    )
                ) {
                    return null;
                }

                return trim(
                    $decoded['message']
                );
            };

            /*
            * Mengambil pesan human/client.
            *
            * Hanya string biasa yang diterima.
            * JSON array dan object langsung ditolak.
            */
            $extractHumanMessage = function (
                $content
            ): ?string {
                if (!is_string($content)) {
                    return null;
                }

                $content = trim($content);

                if ($content === '') {
                    return null;
                }

                $decoded = json_decode(
                    $content,
                    true
                );

                if (
                    json_last_error()
                    === JSON_ERROR_NONE
                ) {
                    /*
                    * JSON string masih boleh dianggap
                    * sebagai pesan biasa.
                    */
                    if (is_string($decoded)) {
                        return trim($decoded);
                    }

                    /*
                    * Array, object, angka, boolean,
                    * dan null bukan pesan chat.
                    */
                    return null;
                }

                return $content;
            };

            /*
            * Pemeriksaan akhir isi pesan.
            */
            $isValidPlainMessage = function (
                $message
            ) use (
                $isSearchingSchedule
            ): bool {
                if (!is_string($message)) {
                    return false;
                }

                $message = trim($message);

                if ($message === '') {
                    return false;
                }

                $lowerMessage =
                    strtolower($message);

                $blockedMessages = [
                    'sedang mencari jadwal',
                    '[]',
                    '[ ]',
                    '{}',
                    '{ }',
                    'null',
                    'undefined',
                ];

                if (
                    in_array(
                        $lowerMessage,
                        $blockedMessages,
                        true
                    )
                ) {
                    return false;
                }

                if (
                    $isSearchingSchedule(
                        $message
                    )
                ) {
                    return false;
                }

                /*
                * Tolak JSON array atau object valid.
                */
                $decoded = json_decode(
                    $message,
                    true
                );

                if (
                    json_last_error()
                    === JSON_ERROR_NONE
                    && is_array($decoded)
                ) {
                    return false;
                }

                /*
                * Pengaman untuk JSON rusak atau
                * JSON yang terpotong.
                */
                $firstCharacter =
                    substr($message, 0, 1);

                $lastCharacter =
                    substr($message, -1);

                $looksLikeJsonArray =
                    $firstCharacter === '['
                    && $lastCharacter === ']';

                $looksLikeJsonObject =
                    $firstCharacter === '{'
                    && $lastCharacter === '}';

                if (
                    $looksLikeJsonArray
                    || $looksLikeJsonObject
                ) {
                    return false;
                }

                return true;
            };

            $chats = historyChat::query()
                ->where(
                    'session_id',
                    $lead->session_id
                )
                ->orderBy(
                    'created_at',
                    'asc'
                )
                ->get()
                ->map(function (
                    $chat
                ) use (
                    $extractAiMessage,
                    $extractHumanMessage,
                    $isValidPlainMessage,
                    $isSearchingSchedule
                ) {
                    $msgData =
                        $chat->message;

                    /*
                    * Antisipasi jika cast model belum
                    * mengubah message menjadi array.
                    */
                    if (is_string($msgData)) {
                        $decodedMessage =
                            json_decode(
                                $msgData,
                                true
                            );

                        if (
                            json_last_error()
                            !== JSON_ERROR_NONE
                            || !is_array(
                                $decodedMessage
                            )
                        ) {
                            return null;
                        }

                        $msgData =
                            $decodedMessage;
                    }

                    if (!is_array($msgData)) {
                        return null;
                    }

                    if (
                        $isSearchingSchedule(
                            $msgData
                        )
                    ) {
                        return null;
                    }

                    $type = strtolower(
                        trim(
                            (string) (
                                $msgData['type']
                                ?? ''
                            )
                        )
                    );

                    /*
                    * Ini bagian terpenting.
                    *
                    * Jangan menggunakan else untuk
                    * menganggap semua tipe sebagai client.
                    *
                    * Tipe tool dan system harus ditolak.
                    */
                    if (
                        !in_array(
                            $type,
                            ['ai', 'human'],
                            true
                        )
                    ) {
                        return null;
                    }

                    $rawContent =
                        $msgData['content']
                        ?? null;

                    if (
                        $isSearchingSchedule(
                            $rawContent
                        )
                    ) {
                        return null;
                    }

                    if ($type === 'ai') {
                        $textContent =
                            $extractAiMessage(
                                $rawContent
                            );

                        $sender = 'me';
                    } else {
                        $textContent =
                            $extractHumanMessage(
                                $rawContent
                            );

                        $sender = 'client';
                    }

                    if (
                        !$isValidPlainMessage(
                            $textContent
                        )
                    ) {
                        return null;
                    }

                    return [
                        'sender' => $sender,
                        'message' => trim(
                            $textContent
                        ),
                        'created_at' =>
                            $chat->created_at
                                ? $chat
                                    ->created_at
                                    ->copy()
                                    ->setTimezone(
                                        'Asia/Jakarta'
                                    )
                                    ->format(
                                        'd M Y, H:i'
                                    )
                                : '-',
                    ];
                })
                ->filter(function ($chat) {
                    return $chat !== null;
                })
                ->values();

            return response()->json([
                'success' => true,
                'total' => $chats->count(),
                'chats' => $chats,
            ]);
        } catch (\Throwable $e) {
            \Log::error(
                'Gagal mengambil history CRM',
                [
                    'lead_id' => $leadId,
                    'message' =>
                        $e->getMessage(),
                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Gagal mengambil history chat.',
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
    public function store(Request $request, string $type) 
    {
        try {
            $data = $this->validateCrmData(
                $request,
                $type
            );

            $modelClass =
                $this->crmModel($type);

            $payload =
                $this->prepareCrmPayload(
                    $data,
                    $type
                );

            $lead = $modelClass::create($payload);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil ditambahkan.',
                'data' => $lead,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data belum lengkap atau tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Data gagal ditambahkan.',
            ], 500);
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
    
    public function update(Request $request,string $type,string $id) 
    {
        try {
            $modelClass =
                $this->crmModel($type);

            /** @var Model|null $lead */
            $lead = $modelClass::query()
                ->find($id);

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Data tidak ditemukan.',
                ], 404);
            }

            $data =
                $this->validateCrmData(
                    $request,
                    $type
                );

            /*
            * Payload dasar dari data validasi.
            */
            $preparedPayload =
                $this->prepareCrmPayload(
                    $data,
                    $type
                );

            /*
            * Hanya ambil field yang benar-benar
            * dikirim oleh form edit.
            */
            $payload =
                $this->prepareCrmUpdatePayload(
                    $request,
                    $preparedPayload,
                    $type
                );

            if (empty($payload)) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Tidak ada perubahan yang dikirim.',
                ], 422);
            }

            /*
            * Simpan nilai penting sebelum update
            * untuk memastikan tidak berubah.
            */
            $originalSystemValues = [];

            if ($type === 'ai') {
                $originalSystemValues = [
                    'session_id' =>
                        $lead->session_id,

                    'chat_terbaru' =>
                        $lead->chat_terbaru,

                    'created_at' =>
                        $lead->created_at,
                ];
            }

            $lead->fill($payload);
            $lead->save();

            /*
            * Pengaman tambahan:
            * field sistem AI tidak boleh menjadi null
            * akibat proses edit.
            */
            if ($type === 'ai') {
                $systemChanged = false;

                foreach (
                    $originalSystemValues
                    as $field => $value
                ) {
                    if (
                        $lead->{$field}
                        != $value
                    ) {
                        $lead->{$field} =
                            $value;

                        $systemChanged = true;
                    }
                }

                if ($systemChanged) {
                    $lead->save();
                }
            }

            $lead->refresh();

            \Log::info(
                'Data CRM berhasil diperbarui',
                [
                    'type' => $type,
                    'id' => $id,
                    'updated_fields' =>
                        array_keys($payload),

                    'session_id' =>
                        $type === 'ai'
                            ? $lead->session_id
                            : null,

                    'chat_terbaru' =>
                        $type === 'ai'
                            ? $lead->chat_terbaru
                            : null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' =>
                    'Data berhasil diperbarui.',
                'data' =>
                    $this->formatUpdatedCrmData(
                        $lead,
                        $type
                    ),
            ]);
        } catch (
            \Illuminate\Validation\ValidationException
            $e
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Data belum lengkap atau tidak valid.',
                'errors' =>
                    $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error(
                'Update CRM gagal',
                [
                    'type' => $type,
                    'id' => $id,
                    'message' =>
                        $e->getMessage(),
                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Data gagal diperbarui.',
            ], 500);
        }
    }

    private function formatUpdatedCrmData(
        Model $lead,
        string $type
    ): array {
        if ($type === 'ai') {
            return [
                'id' => $lead->getKey(),

                'nama' =>
                    $lead->nama,

                'brand' =>
                    $lead->nama_usaha,

                'email' =>
                    $lead->email,

                'phone' =>
                    $lead->phone,

                'kategori' =>
                    $lead->kategori,

                'layanan' =>
                    $lead->layanan,

                'kendala' =>
                    $lead->kendala,

                'kebutuhan' =>
                    $lead->kebutuhan,

                'kesimpulan' =>
                    $lead->kesimpulan,

                'sosial_media' =>
                    $lead->sosial_media,

                'prospek' =>
                    $lead->hot_prospek
                        ? 'hot'
                        : 'warm',

                'status' =>
                    $lead->status,

                'status_label' =>
                    $this->aiStatusLabel(
                        $lead->status
                    ),

                'meeting' =>
                    $this->dateWib(
                        $lead->meeting
                    ),

                'chat_terbaru' =>
                    $this->dateWib(
                        $lead->chat_terbaru
                    ),

                'created_at' =>
                    $this->dateWib(
                        $lead->created_at
                    ),
            ];
        }

        return [
            'id' =>
                $lead->getKey(),

            'nama' =>
                $lead->name,

            'brand' =>
                $lead->brand,

            'phone' =>
                $lead->phone,

            'kategori' =>
                $lead->kategori,

            'layanan' =>
                $lead->layanan,

            'chanelling' =>
                $lead->chanelling,

            'sosial_media' =>
                $lead->sosial_media,

            'kebutuhan' =>
                $lead->kebutuhan,

            'prospek' =>
                $lead->prospek,

            'status' =>
                $lead->status,

            'can_push' =>
                (bool) $lead->can_push,

            'service_plan' =>
                (bool)
                $lead->service_plan,

            'need_analysys' =>
                (bool)
                $lead->need_analysys,

            'done_meeting' =>
                (bool)
                $lead->done_meeting,

            'note' =>
                $lead->note,

            'tanggal_chat' =>
                $this->dateWib(
                    $lead->tanggal_chat
                ),

            'created_at' =>
                $this->dateWib(
                    $lead->created_at
                ),
        ];
    }

    private function prepareCrmUpdatePayload(
        Request $request,
        array $preparedPayload,
        string $type
    ): array {
        /*
        * Nama kolom database beserta kemungkinan
        * nama input dari frontend.
        */
        $fieldMap = $type === 'ai'
            ? [
                'nama' => [
                    'nama',
                    'name',
                ],

                'nama_usaha' => [
                    'nama_usaha',
                    'brand',
                ],

                'email' => [
                    'email',
                ],

                'phone' => [
                    'phone',
                ],

                'kategori' => [
                    'kategori',
                ],

                'layanan' => [
                    'layanan',
                ],

                'kendala' => [
                    'kendala',
                ],

                'kebutuhan' => [
                    'kebutuhan',
                ],

                'kesimpulan' => [
                    'kesimpulan',
                ],

                'sosial_media' => [
                    'sosial_media',
                ],

                'website' => [
                    'website',
                ],

                'budget' => [
                    'budget',
                ],

                'hot_prospek' => [
                    'hot_prospek',
                    'prospek',
                ],

                'status' => [
                    'status',
                ],

                'meeting' => [
                    'meeting',
                ],

                'link_meet' => [
                    'link_meet',
                ],
            ]
            : [
                'name' => [
                    'name',
                    'nama',
                ],

                'brand' => [
                    'brand',
                    'nama_usaha',
                ],

                'phone' => [
                    'phone',
                ],

                'kategori' => [
                    'kategori',
                ],

                'layanan' => [
                    'layanan',
                ],

                'chanelling' => [
                    'chanelling',
                ],

                'sosial_media' => [
                    'sosial_media',
                ],

                'kebutuhan' => [
                    'kebutuhan',
                ],

                'prospek' => [
                    'prospek',
                    'hot_prospek',
                ],

                'status' => [
                    'status',
                ],

                'can_push' => [
                    'can_push',
                ],

                'service_plan' => [
                    'service_plan',
                ],

                'need_analysys' => [
                    'need_analysys',
                ],

                'done_meeting' => [
                    'done_meeting',
                ],

                'note' => [
                    'note',
                ],

                'tanggal_chat' => [
                    'tanggal_chat',
                ],
            ];

        $updatePayload = [];

        foreach (
            $fieldMap
            as $databaseField => $requestFields
        ) {
            $wasSubmitted = false;

            foreach (
                $requestFields
                as $requestField
            ) {
                /*
                * exists() tetap true ketika nilainya null.
                * Jadi pengguna tetap bisa mengosongkan
                * field nullable secara sengaja.
                */
                if (
                    $request->exists(
                        $requestField
                    )
                ) {
                    $wasSubmitted = true;
                    break;
                }
            }

            if (
                $wasSubmitted
                && array_key_exists(
                    $databaseField,
                    $preparedPayload
                )
            ) {
                $updatePayload[
                    $databaseField
                ] = $preparedPayload[
                    $databaseField
                ];
            }
        }

        /*
        * Field sistem ini tidak boleh diperbarui
        * dari form edit AI.
        */
        if ($type === 'ai') {
            unset(
                $updatePayload[
                    'session_id'
                ],
                $updatePayload[
                    'chat_terbaru'
                ],
                $updatePayload[
                    'created_at'
                ]
            );
        }

        return $updatePayload;
    }

    public function destroy(string $type,string $id) 
    {
        try {
            $modelClass =
                $this->crmModel($type);

            /** @var Model|null $lead */
            $lead = $modelClass::query()
                ->find($id);

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }

            $lead->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Data gagal dihapus.',
            ], 500);
        }
    }

    private function crmModel(string $type): string
    {
        return match ($type) {
            'ai' => CRM::class,
            'human' => CRM_HUMAN::class,
            default => throw new \InvalidArgumentException(
                'Tipe CRM tidak valid.'
            ),
        };
    }

    private function validateCrmData(Request $request,string $type): array 
    {
        $commonRules = [
            'nama' => [
                'nullable',
                'string',
                'max:255',
            ],
            'brand' => [
                'nullable',
                'string',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'kategori' => [
                'nullable',
                'string',
                'max:255',
            ],
            'layanan' => [
                'nullable',
                'string',
                'max:255',
            ],
            'kebutuhan' => [
                'nullable',
                'string',
            ],
            'sosial_media' => [
                'nullable',
                'array',
            ],
            'status' => [
                'nullable',
            ],
            'prospek' => [
                'nullable',
                Rule::in([
                    'cold',
                    'warm',
                    'hot',
                ]),
            ],
        ];

        if ($type === 'ai') {
            return $request->validate(
                array_merge($commonRules, [
                    'email' => [
                        'nullable',
                        'email',
                        'max:255',
                    ],
                    'kendala' => [
                        'nullable',
                        'string',
                    ],
                    'kesimpulan' => [
                        'nullable',
                        'string',
                    ],
                    'meeting' => [
                        'nullable',
                        'date',
                    ],
                ])
            );
        }

        return $request->validate(
            array_merge($commonRules, [
                'note' => [
                    'nullable',
                    'string',
                ],
                'can_push' => [
                    'nullable',
                    'boolean',
                ],
                'tanggal_chat' => [
                    'nullable',
                    'date',
                ],
            ])
        );
    }

    private function prepareCrmPayload(
        array $data,
        string $type
    ): array {
        if ($type === 'ai') {
            return [
                'nama' =>
                    $data['nama'] ?? null,

                'nama_usaha' =>
                    $data['brand']
                    ?? $data['nama_usaha']
                    ?? null,

                'email' =>
                    $data['email'] ?? null,

                'phone' =>
                    $data['phone'] ?? null,

                'kategori' =>
                    $data['kategori'] ?? null,

                'layanan' =>
                    $data['layanan'] ?? null,

                'kendala' =>
                    $data['kendala'] ?? null,

                'kebutuhan' =>
                    $data['kebutuhan'] ?? null,

                'kesimpulan' =>
                    $data['kesimpulan']
                    ?? null,

                'sosial_media' =>
                    $data['sosial_media']
                    ?? null,

                'hot_prospek' =>
                    $data['hot_prospek']
                    ?? false,

                'status' =>
                    $data['status'] ?? null,

                'meeting' =>
                    $data['meeting'] ?? null,

                'link_meet' =>
                    $data['link_meet']
                    ?? null,

                'chat_terbaru' =>
                    $data['chat_terbaru']
                    ?? null,
            ];
        }

        /*
        * Payload khusus tabel crm_human.
        *
        * Perhatikan:
        * nama dari form disimpan ke kolom name.
        */
        return [
            'name' =>
                $data['nama']
                ?? $data['name']
                ?? null,

            'brand' =>
                $data['brand']
                ?? $data['nama_usaha']
                ?? null,

            'phone' =>
                $data['phone'] ?? null,

            'kategori' =>
                $data['kategori'] ?? null,

            'layanan' =>
                $data['layanan'] ?? null,

            'kebutuhan' =>
                $data['kebutuhan'] ?? null,

            'social_media' =>
                $this->prepareSocialMediaValue(
                    $data['sosial_media']
                    ?? null
                ),

            'prospek' =>
                $data['prospek']
                ?? $data['hot_prospek']
                ?? null,

            'status' =>
                $data['status'] ?? null,

            'can_push' =>
                (bool) (
                    $data['can_push']
                    ?? false
                ),

            'service_plan' =>
                (bool) (
                    $data['service_plan']
                    ?? false
                ),

            'need_analysys' =>
                (bool) (
                    $data['need_analysys']
                    ?? false
                ),

            'done_meeting' =>
                (bool) (
                    $data['done_meeting']
                    ?? false
                ),

            'note' =>
                $data['note'] ?? null,

            'tanggal_chat' =>
                $data['tanggal_chat']
                ?? now('Asia/Jakarta'),
        ];
    }

    private function prepareSocialMediaValue(
        $value
    ) {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return (array) $value;
        }

        if (is_string($value)) {
            $decoded = json_decode(
                $value,
                true
            );

            if (
                json_last_error()
                === JSON_ERROR_NONE
            ) {
                return $decoded;
            }

            /*
            * Jika form mengirim teks biasa,
            * simpan sebagai array.
            */
            return [
                'value' => trim($value),
            ];
        }

        return null;
    }

    /*
        * Letakkan kedua konstanta ini di dalam class CRMController.
        * Gunakan alamat server WAHA, bukan alamat webhook n8n.
    */
        private const WAHA_MEDIA_BASE_URL =
            'https://waha-tvdqpz621wfz.cgk-hero.sumopod.my.id';

        private const HUMAN_HISTORY_WEBHOOK =
            'https://bmglbl3.n8n.bocindonesia.com/webhook/get-data-history';

        private const WAHA_API_KEY =
            'i6cpQScyiUj7qE0kOkDayJbxi5zD6REo';

        public function humanHistory($id)
            {
                try {
                    /*
                    * Cari data Human berdasarkan primary key.
                    */
                    $lead = CRM_HUMAN::find($id);

                    if (!$lead) {
                        return response()->json([
                            'success' => false,
                            'message' =>
                                'Data CRM Human tidak ditemukan.',
                        ], 404);
                    }

                    /*
                    * Ambil nama session.
                    *
                    * Prioritas:
                    * 1. human_name
                    * 2. name
                    *
                    * Tabel CRM Human Anda memakai kolom name,
                    * bukan nama.
                    */
                    $sessionName = trim(
                        (string) (
                            $lead->human_name
                            ?? $lead->name
                            ?? ''
                        )
                    );

                    /*
                    * Samakan dengan kode frontend lama:
                    *
                    * customer.human_name?.toLowerCase()
                    */
                    $sessionName =
                        function_exists(
                            'mb_strtolower'
                        )
                            ? mb_strtolower(
                                $sessionName,
                                'UTF-8'
                            )
                            : strtolower(
                                $sessionName
                            );

                    /*
                    * Payload yang dikirim menuju Webhook n8n.
                    *
                    * Karena request menggunakan JSON POST,
                    * pada n8n nilainya dibaca melalui:
                    *
                    * $json.body.id_proteksi
                    * $json.body.session
                    */
                    $payload = [
                        'id' =>
                            $lead->getKey(),

                        'id_proteksi' =>
                            $lead->id_proteksi,

                        'session' =>
                            $sessionName,

                        'phone' =>
                            $lead->phone,

                        'name' =>
                            $lead->name,
                    ];

                    Log::info(
                        'Request history Human ke n8n',
                        [
                            'lead_id' =>
                                $lead->getKey(),

                            'id_proteksi' =>
                                $lead->id_proteksi,

                            'session' =>
                                $sessionName,

                            'webhook' =>
                                self::HUMAN_HISTORY_WEBHOOK,
                        ]
                    );

                    /*
                    * Panggil webhook n8n menggunakan POST.
                    */
                    $response = Http::acceptJson()
                        ->asJson()
                        ->connectTimeout(10)
                        ->timeout(30)
                        ->retry(
                            2,
                            500,
                            function (
                                \Throwable $exception
                            ) {
                                return $exception instanceof
                                    \Illuminate\Http\Client\ConnectionException;
                            }
                        )
                        ->post(
                            self::HUMAN_HISTORY_WEBHOOK,
                            $payload
                        );

                    /*
                    * Catat informasi response tanpa menulis
                    * seluruh isi percakapan ke log.
                    */
                    Log::info(
                        'Response history Human dari n8n',
                        [
                            'lead_id' =>
                                $lead->getKey(),

                            'status' =>
                                $response->status(),

                            'successful' =>
                                $response->successful(),

                            'content_type' =>
                                $response->header(
                                    'Content-Type'
                                ),

                            'body_length' =>
                                strlen(
                                    $response->body()
                                ),
                        ]
                    );

                    /*
                    * Jika n8n memberikan status error.
                    */
                    if ($response->failed()) {
                        Log::error(
                            'Webhook history Human gagal',
                            [
                                'lead_id' =>
                                    $lead->getKey(),

                                'status' =>
                                    $response->status(),

                                'response' =>
                                    mb_substr(
                                        $response->body(),
                                        0,
                                        1000
                                    ),
                            ]
                        );

                        return response()->json([
                            'success' => false,
                            'message' =>
                                'Webhook n8n gagal memberikan history.',
                        ], 502);
                    }

                    /*
                    * Response 200 tidak selalu berarti
                    * n8n mengirimkan body.
                    */
                    $responseBody = trim(
                        $response->body()
                    );

                    if ($responseBody === '') {
                        Log::warning(
                            'Response webhook Human kosong',
                            [
                                'lead_id' =>
                                    $lead->getKey(),

                                'status' =>
                                    $response->status(),

                                'content_type' =>
                                    $response->header(
                                        'Content-Type'
                                    ),
                            ]
                        );

                        return response()->json([
                            'success' => false,
                            'message' =>
                                'Webhook n8n tidak mengirimkan response. Pastikan Webhook memakai "Using Respond to Webhook Node".',
                        ], 502);
                    }

                    /*
                    * Decode response JSON.
                    */
                    $result = json_decode(
                        $responseBody,
                        true
                    );

                    if (
                        json_last_error()
                        !== JSON_ERROR_NONE
                        || !is_array($result)
                    ) {
                        Log::warning(
                            'Response webhook Human bukan JSON',
                            [
                                'lead_id' =>
                                    $lead->getKey(),

                                'json_error' =>
                                    json_last_error_msg(),

                                'response' =>
                                    mb_substr(
                                        $responseBody,
                                        0,
                                        1000
                                    ),
                            ]
                        );

                        return response()->json([
                            'success' => false,
                            'message' =>
                                'Response webhook n8n bukan JSON yang valid.',
                        ], 502);
                    }

                    /*
                    * Mendukung response:
                    *
                    * [...]
                    *
                    * {"chats":[...]}
                    *
                    * {"history":[...]}
                    *
                    * {"data":[...]}
                    *
                    * {"success":true,"chats":[...]}
                    */
                    $historyItems =
                        $this->extractHumanHistoryList(
                            $result
                        );

                    /*
                    * Normalisasi setiap pesan.
                    *
                    * body     -> message
                    * fromMe  -> sender
                    * timestamp -> created_at
                    */
                    $chats = collect($historyItems)
                        ->map(function ($chat) {
                            return $this
                                ->normalizeHumanHistoryItem(
                                    $chat
                                );
                        })
                        ->filter(function ($chat) {
                            return $chat !== null;
                        })
                        /*
                        * Susun dari pesan terlama
                        * menuju pesan terbaru.
                        */
                        ->sortBy(function ($chat) {
                            return $chat[
                                'timestamp_raw'
                            ] ?? 0;
                        })
                        ->values()
                        ->map(function ($chat) {
                            /*
                            * timestamp_raw hanya diperlukan
                            * untuk sorting.
                            */
                            unset(
                                $chat['timestamp_raw']
                            );

                            return $chat;
                        })
                        ->values();

                    Log::info(
                        'History Human berhasil diambil',
                        [
                            'lead_id' =>
                                $lead->getKey(),

                            'total_raw' =>
                                count($historyItems),

                            'total_filtered' =>
                                $chats->count(),
                        ]
                    );

                    return response()->json([
                        'success' => true,
                        'total' =>
                            $chats->count(),
                        'chats' =>
                            $chats,
                    ]);
                } catch (
                    \Illuminate\Http\Client\ConnectionException
                    $e
                ) {
                    Log::error(
                        'Koneksi webhook history Human gagal',
                        [
                            'lead_id' => $id,
                            'message' =>
                                $e->getMessage(),
                        ]
                    );

                    return response()->json([
                        'success' => false,
                        'message' =>
                            'Tidak dapat terhubung ke server n8n.',
                    ], 504);
                } catch (\Throwable $e) {
                    Log::error(
                        'History CRM Human gagal',
                        [
                            'lead_id' => $id,
                            'message' =>
                                $e->getMessage(),
                            'trace' =>
                                $e->getTraceAsString(),
                        ]
                    );

                    return response()->json([
                        'success' => false,
                        'message' =>
                            'Gagal mengambil history Human.',
                    ], 500);
                }
            }

            /**
             * Mengambil array history dari berbagai
             * kemungkinan format response n8n.
             */
            private function extractHumanHistoryList(
                array $result
            ): array {
                /*
                * Response langsung berupa array item.
                */
                if (array_is_list($result)) {
                    /*
                    * Response mungkin dibungkus:
                    *
                    * [
                    *     {
                    *         "chats": [...]
                    *     }
                    * ]
                    */
                    if (
                        count($result) === 1
                        && isset($result[0])
                        && is_array($result[0])
                    ) {
                        $firstItem = $result[0];

                        if (
                            isset($firstItem['chats'])
                            && is_array(
                                $firstItem['chats']
                            )
                        ) {
                            return
                                $firstItem['chats'];
                        }

                        if (
                            isset($firstItem['history'])
                            && is_array(
                                $firstItem['history']
                            )
                        ) {
                            return
                                $firstItem['history'];
                        }

                        if (
                            isset($firstItem['data'])
                            && is_array(
                                $firstItem['data']
                            )
                        ) {
                            return
                                $firstItem['data'];
                        }
                    }

                    return $result;
                }

                if (
                    isset($result['chats'])
                    && is_array($result['chats'])
                ) {
                    return $result['chats'];
                }

                if (
                    isset($result['history'])
                    && is_array(
                        $result['history']
                    )
                ) {
                    return $result['history'];
                }

                if (
                    isset($result['data'])
                    && is_array($result['data'])
                ) {
                    if (
                        isset(
                            $result['data']['chats']
                        )
                        && is_array(
                            $result['data']['chats']
                        )
                    ) {
                        return
                            $result['data']['chats'];
                    }

                    if (
                        isset(
                            $result['data']['history']
                        )
                        && is_array(
                            $result['data']['history']
                        )
                    ) {
                        return
                            $result['data']['history'];
                    }

                    return $result['data'];
                }

                return [];
            }

            /**
             * Normalisasi satu pesan WhatsApp.
             */
            private function normalizeHumanHistoryItem(
                $chat
            ): ?array {
                if (!is_array($chat)) {
                    return null;
                }

                /*
                * Data internal asli WAHA.
                */
                $data = isset($chat['_data'])
                    && is_array($chat['_data'])
                        ? $chat['_data']
                        : [];

                /*
                * ID pesan.
                */
                $messageId =
                    $data['id']['id']
                    ?? $data['key']['id']
                    ?? $chat['id']
                    ?? null;

                if (is_array($messageId)) {
                    $messageId =
                        $messageId['id']
                        ?? null;
                }

                /*
                * Status pengirim.
                *
                * true  = Admin/CS
                * false = Customer
                */
                $fromMeValue =
                    $data['fromMe']
                    ?? $data['key']['fromMe']
                    ?? $chat['fromMe']
                    ?? false;

                $fromMe = filter_var(
                    $fromMeValue,
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                );

                if ($fromMe === null) {
                    $fromMe = false;
                }

                /*
                * Timestamp dari _data.
                */
                $timestamp =
                    $data['messageTimestamp']
                    ?? $data['timestamp']
                    ?? $chat['timestamp']
                    ?? null;

                $timestampRaw =
                    $this->normalizeHumanTimestamp(
                        $timestamp
                    );

                /*
                * Tipe pesan WAHA.
                *
                * Contoh:
                * chat
                * image
                * video
                * audio
                * ptt
                * document
                * sticker
                * location
                * vcard
                */
                $messageType = strtolower(
                    trim(
                        (string) (
                            $data['type']
                            ?? $chat['type']
                            ?? 'chat'
                        )
                    )
                );

                /*
                * Isi/caption pesan dari _data.
                */
                $message =
                    $data['body']
                    ?? $data['caption']
                    ?? $chat['body']
                    ?? '';

                if (!is_string($message)) {
                    $message = '';
                }

                $message = trim($message);

                /*
                * Status media.
                */
                $hasMediaValue =
                    $data['hasMedia']
                    ?? $chat['hasMedia']
                    ?? false;

                $hasMedia = filter_var(
                    $hasMediaValue,
                    FILTER_VALIDATE_BOOLEAN
                );

                /*
                * Media bisa berada di root atau _data.
                */
                $media =
                    isset($data['media'])
                    && is_array($data['media'])
                        ? $data['media']
                        : (
                            isset($chat['media'])
                            && is_array($chat['media'])
                                ? $chat['media']
                                : []
                        );

                $mediaUrl =
                    $media['url']
                    ?? $data['mediaUrl']
                    ?? $data['deprecatedMms3Url']
                    ?? null;

                $mediaMimeType =
                    $media['mimetype']
                    ?? $media['mimeType']
                    ?? $data['mimetype']
                    ?? $data['mimeType']
                    ?? null;

                $mediaFilename =
                    $media['filename']
                    ?? $media['fileName']
                    ?? $data['filename']
                    ?? $data['fileName']
                    ?? null;

                /*
                * Beberapa engine menyimpan metadata
                * dokumen dalam object documentMessage.
                */
                $documentMessage =
                    $data['message'][
                        'documentMessage'
                    ]
                    ?? null;

                if (is_array($documentMessage)) {
                    $mediaMimeType =
                        $mediaMimeType
                        ?? $documentMessage['mimetype']
                        ?? null;

                    $mediaFilename =
                        $mediaFilename
                        ?? $documentMessage['fileName']
                        ?? null;

                    $message =
                        $message
                        ?: (
                            $documentMessage['caption']
                            ?? ''
                        );

                    $messageType = 'document';
                    $hasMedia = true;
                }

                /*
                * Gambar.
                */
                $imageMessage =
                    $data['message'][
                        'imageMessage'
                    ]
                    ?? null;

                if (is_array($imageMessage)) {
                    $mediaMimeType =
                        $mediaMimeType
                        ?? $imageMessage['mimetype']
                        ?? 'image/jpeg';

                    $message =
                        $message
                        ?: (
                            $imageMessage['caption']
                            ?? ''
                        );

                    $messageType = 'image';
                    $hasMedia = true;
                }

                /*
                * Video.
                */
                $videoMessage =
                    $data['message'][
                        'videoMessage'
                    ]
                    ?? null;

                if (is_array($videoMessage)) {
                    $mediaMimeType =
                        $mediaMimeType
                        ?? $videoMessage['mimetype']
                        ?? 'video/mp4';

                    $message =
                        $message
                        ?: (
                            $videoMessage['caption']
                            ?? ''
                        );

                    $messageType = 'video';
                    $hasMedia = true;
                }

                /*
                * Audio/voice note.
                */
                $audioMessage =
                    $data['message'][
                        'audioMessage'
                    ]
                    ?? null;

                if (is_array($audioMessage)) {
                    $mediaMimeType =
                        $mediaMimeType
                        ?? $audioMessage['mimetype']
                        ?? 'audio/ogg';

                    $messageType =
                        !empty($audioMessage['ptt'])
                            ? 'ptt'
                            : 'audio';

                    $hasMedia = true;
                }

                /*
                * Sticker.
                */
                $stickerMessage =
                    $data['message'][
                        'stickerMessage'
                    ]
                    ?? null;

                if (is_array($stickerMessage)) {
                    $mediaMimeType =
                        $mediaMimeType
                        ?? $stickerMessage['mimetype']
                        ?? 'image/webp';

                    $messageType = 'sticker';
                    $hasMedia = true;
                }

                /*
                * Lokasi.
                */
                $location =
                    $data['location']
                    ?? $chat['location']
                    ?? null;

                $latitude = null;
                $longitude = null;
                $locationName = null;

                if (is_array($location)) {
                    $latitude =
                        $location['latitude']
                        ?? $location['lat']
                        ?? null;

                    $longitude =
                        $location['longitude']
                        ?? $location['lng']
                        ?? null;

                    $locationName =
                        $location['description']
                        ?? $location['name']
                        ?? null;

                    $messageType = 'location';
                }

                /*
                * Lokasi kadang berada di message.locationMessage.
                */
                $locationMessage =
                    $data['message'][
                        'locationMessage'
                    ]
                    ?? null;

                if (is_array($locationMessage)) {
                    $latitude =
                        $latitude
                        ?? $locationMessage[
                            'degreesLatitude'
                        ]
                        ?? null;

                    $longitude =
                        $longitude
                        ?? $locationMessage[
                            'degreesLongitude'
                        ]
                        ?? null;

                    $locationName =
                        $locationName
                        ?? $locationMessage['name']
                        ?? $locationMessage['address']
                        ?? null;

                    $messageType = 'location';
                }

                /*
                * VCard/kontak.
                */
                $vCards =
                    $data['vCards']
                    ?? $chat['vCards']
                    ?? [];

                if (!is_array($vCards)) {
                    $vCards = [];
                }

                if (!empty($vCards)) {
                    $messageType = 'vcard';
                }

                /*
                * Abaikan item internal yang tidak mempunyai
                * teks, media, lokasi, atau kontak.
                */
                $hasLocation =
                    $latitude !== null
                    && $longitude !== null;

                $hasVCard =
                    !empty($vCards);

                if (
                    $message === ''
                    && !$hasMedia
                    && !$hasLocation
                    && !$hasVCard
                ) {
                    return null;
                }

                /*
                * Jangan tampilkan payload JSON sebagai teks.
                * Media tetap boleh tampil meskipun caption
                * berupa JSON atau kosong.
                */
                if ($message !== '') {
                    $decodedMessage = json_decode(
                        $message,
                        true
                    );

                    if (
                        json_last_error()
                        === JSON_ERROR_NONE
                        && is_array($decodedMessage)
                    ) {
                        $message = '';
                    }
                }

                /*
                * Tentukan kategori media dari MIME type
                * jika type dari WAHA kurang spesifik.
                */
                $mediaCategory =
                    $this->humanMediaCategory(
                        $messageType,
                        $mediaMimeType
                    );

                return [
                    'id' =>
                        is_scalar($messageId)
                            ? (string) $messageId
                            : null,

                    'sender' =>
                        $fromMe
                            ? 'me'
                            : 'client',

                    'message' =>
                        $message,

                    'message_type' =>
                        $messageType,

                    'created_at' =>
                        $this->formatHumanChatTimestamp(
                            $timestampRaw
                        ),

                    'timestamp_raw' =>
                        $timestampRaw,

                    'source' =>
                        $data['source']
                        ?? $chat['source']
                        ?? null,

                    'has_media' =>
                        $hasMedia,

                    'media' => [
                        'category' =>
                            $mediaCategory,

                        'url' =>
                            $this->humanMediaProxyUrl(
                                $mediaUrl
                            ),

                        'mimetype' =>
                            is_string($mediaMimeType)
                                ? $mediaMimeType
                                : null,

                        'filename' =>
                            is_string($mediaFilename)
                            && trim($mediaFilename) !== ''
                                ? trim($mediaFilename)
                                : $this
                                    ->defaultHumanMediaFilename(
                                        $mediaCategory,
                                        $mediaMimeType
                                    ),
                    ],

                    'location' =>
                        $hasLocation
                            ? [
                                'latitude' =>
                                    (float) $latitude,

                                'longitude' =>
                                    (float) $longitude,

                                'name' =>
                                    is_string($locationName)
                                        ? $locationName
                                        : null,
                            ]
                            : null,

                    'vcards' =>
                        $vCards,
                ];
            }

            private function humanMediaCategory(
                ?string $messageType,
                ?string $mimeType
            ): string {
                $messageType = strtolower(
                    trim(
                        (string) $messageType
                    )
                );

                $mimeType = strtolower(
                    trim(
                        (string) $mimeType
                    )
                );

                if (
                    in_array(
                        $messageType,
                        ['image', 'sticker'],
                        true
                    )
                    || str_starts_with(
                        $mimeType,
                        'image/'
                    )
                ) {
                    return $messageType === 'sticker'
                        ? 'sticker'
                        : 'image';
                }

                if (
                    $messageType === 'video'
                    || str_starts_with(
                        $mimeType,
                        'video/'
                    )
                ) {
                    return 'video';
                }

                if (
                    in_array(
                        $messageType,
                        ['audio', 'ptt', 'voice'],
                        true
                    )
                    || str_starts_with(
                        $mimeType,
                        'audio/'
                    )
                ) {
                    return 'audio';
                }

                if (
                    in_array(
                        $messageType,
                        [
                            'document',
                            'file',
                            'documentwithcaption',
                        ],
                        true
                    )
                ) {
                    return 'document';
                }

                if ($messageType === 'location') {
                    return 'location';
                }

                if (
                    in_array(
                        $messageType,
                        ['vcard', 'contact'],
                        true
                    )
                ) {
                    return 'vcard';
                }

                if ($mimeType !== '') {
                    return 'document';
                }

                return 'text';
            }

            private function defaultHumanMediaFilename(
                string $category,
                ?string $mimeType
            ): string {
                $extension = null;

                $mimeMap = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    'video/mp4' => 'mp4',
                    'audio/mpeg' => 'mp3',
                    'audio/ogg' => 'ogg',
                    'audio/opus' => 'opus',
                    'application/pdf' => 'pdf',
                    'application/msword' => 'doc',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                        => 'docx',
                    'application/vnd.ms-excel'
                        => 'xls',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        => 'xlsx',
                ];

                if (
                    $mimeType
                    && isset($mimeMap[$mimeType])
                ) {
                    $extension =
                        $mimeMap[$mimeType];
                }

                $baseName = match ($category) {
                    'image' => 'gambar',
                    'sticker' => 'stiker',
                    'video' => 'video',
                    'audio' => 'audio',
                    'document' => 'dokumen',
                    default => 'file',
                };

                return $extension
                    ? "{$baseName}.{$extension}"
                    : $baseName;
            }

            /**
             * Normalisasi timestamp detik/milidetik.
             */
            private function normalizeHumanTimestamp(
                $timestamp
            ): ?int {
                if (
                    $timestamp === null
                    || $timestamp === ''
                    || !is_numeric($timestamp)
                ) {
                    return null;
                }

                $timestamp =
                    (float) $timestamp;

                /*
                * Timestamp 13 digit adalah milidetik.
                */
                if ($timestamp > 9999999999) {
                    $timestamp =
                        $timestamp / 1000;
                }

                return (int) $timestamp;
            }

            /**
             * Format timestamp ke WIB.
             */
            private function formatHumanChatTimestamp(
                ?int $timestamp
            ): string {
                if (
                    $timestamp === null
                    || $timestamp <= 0
                ) {
                    return '-';
                }

                try {
                    return \Carbon\Carbon
                        ::createFromTimestamp(
                            $timestamp,
                            'UTC'
                        )
                        ->setTimezone(
                            'Asia/Jakarta'
                        )
                        ->format(
                            'd M Y, H:i'
                        )
                        . ' WIB';
                } catch (\Throwable $e) {
                    Log::warning(
                        'Timestamp history Human tidak valid',
                        [
                            'timestamp' =>
                                $timestamp,
                            'message' =>
                                $e->getMessage(),
                        ]
                    );

                    return '-';
                }
            }

            /**
             * Mengubah URL WAHA menjadi URL proxy Laravel.
             * API key tidak pernah dikirim ke browser.
             */
            private function humanMediaProxyUrl(
                $mediaUrl
            ): ?string {
                if (
                    !is_string($mediaUrl)
                    || trim($mediaUrl) === ''
                ) {
                    return null;
                }

                return route(
                    'crm.api.human.history-media',
                    [
                        'url' => trim($mediaUrl),
                    ]
                );
            }

            /**
             * Proxy aman untuk gambar, PDF, dokumen,
             * audio, video, dan media WAHA lainnya.
             */
            public function humanHistoryMedia(
                Request $request
            ) {
                try {
                    $validated =
                        $request->validate([
                            'url' => [
                                'required',
                                'string',
                                'url',
                                'max:5000',
                            ],
                        ]);

                    $mediaUrl = trim(
                        $validated['url']
                    );

                    $requestedParts =
                        parse_url($mediaUrl);

                    $allowedParts =
                        parse_url(
                            self::WAHA_MEDIA_BASE_URL
                        );

                    $requestedScheme = strtolower(
                        $requestedParts['scheme']
                        ?? ''
                    );

                    $requestedHost = strtolower(
                        $requestedParts['host']
                        ?? ''
                    );

                    $allowedHost = strtolower(
                        $allowedParts['host']
                        ?? ''
                    );

                    $requestedPort =
                        $requestedParts['port']
                        ?? null;

                    $allowedPort =
                        $allowedParts['port']
                        ?? null;

                    if (
                        !in_array(
                            $requestedScheme,
                            ['http', 'https'],
                            true
                        )
                        || $requestedHost === ''
                        || $allowedHost === ''
                        || $requestedHost
                            !== $allowedHost
                        || $requestedPort
                            !== $allowedPort
                    ) {
                        Log::warning(
                            'URL media Human ditolak',
                            [
                                'url' => $mediaUrl,
                                'host' =>
                                    $requestedHost,
                            ]
                        );

                        return response()->json([
                            'success' => false,
                            'message' =>
                                'URL media tidak diizinkan.',
                        ], 403);
                    }

                    $requestedPath =
                        $requestedParts['path']
                        ?? '';

                    if (
                        !str_starts_with(
                            $requestedPath,
                            '/api/files/'
                        )
                    ) {
                        return response()->json([
                            'success' => false,
                            'message' =>
                                'Path media tidak diizinkan.',
                        ], 403);
                    }

                    $headers = [
                        'X-Api-Key' =>
                            self::WAHA_API_KEY,
                        'Accept' => '*/*',
                    ];

                    if (
                        $request->hasHeader('Range')
                    ) {
                        $headers['Range'] =
                            $request->header('Range');
                    }

                    $wahaResponse =
                        Http::withHeaders($headers)
                            ->connectTimeout(10)
                            ->timeout(120)
                            ->withOptions([
                                'allow_redirects' =>
                                    false,
                            ])
                            ->get($mediaUrl);

                    if (
                        !in_array(
                            $wahaResponse->status(),
                            [200, 206],
                            true
                        )
                    ) {
                        Log::error(
                            'Pengambilan media WAHA gagal',
                            [
                                'url' => $mediaUrl,
                                'status' =>
                                    $wahaResponse
                                        ->status(),
                                'response' =>
                                    mb_substr(
                                        $wahaResponse
                                            ->body(),
                                        0,
                                        500
                                    ),
                            ]
                        );

                        return response()->json([
                            'success' => false,
                            'message' =>
                                $wahaResponse->status()
                                === 401
                                    ? 'API key WAHA tidak valid.'
                                    : 'Media gagal diambil dari WAHA.',
                        ], $wahaResponse->status());
                    }

                    $response = response(
                        $wahaResponse->body(),
                        $wahaResponse->status()
                    );

                    $response->header(
                        'Content-Type',
                        $wahaResponse->header(
                            'Content-Type'
                        ) ?: 'application/octet-stream'
                    );

                    foreach (
                        [
                            'Content-Length',
                            'Content-Range',
                            'Accept-Ranges',
                            'Content-Disposition',
                            'Cache-Control',
                            'ETag',
                            'Last-Modified',
                        ] as $headerName
                    ) {
                        $headerValue =
                            $wahaResponse->header(
                                $headerName
                            );

                        if (
                            $headerValue !== null
                            && $headerValue !== ''
                        ) {
                            $response->header(
                                $headerName,
                                $headerValue
                            );
                        }
                    }

                    if (
                        !$wahaResponse->header(
                            'Content-Disposition'
                        )
                    ) {
                        $filename = preg_replace(
                            '/[^a-zA-Z0-9._-]/',
                            '_',
                            basename($requestedPath)
                        );

                        $response->header(
                            'Content-Disposition',
                            'inline; filename="'
                            . ($filename ?: 'media')
                            . '"'
                        );
                    }

                    $response->header(
                        'X-Content-Type-Options',
                        'nosniff'
                    );

                    return $response;
                } catch (
                    \Illuminate\Validation\ValidationException
                    $e
                ) {
                    return response()->json([
                        'success' => false,
                        'message' =>
                            'URL media tidak valid.',
                        'errors' => $e->errors(),
                    ], 422);
                } catch (
                    \Illuminate\Http\Client\ConnectionException
                    $e
                ) {
                    Log::error(
                        'Koneksi media WAHA gagal',
                        [
                            'message' =>
                                $e->getMessage(),
                        ]
                    );

                    return response()->json([
                        'success' => false,
                        'message' =>
                            'Server WAHA tidak dapat dihubungi.',
                    ], 504);
                } catch (\Throwable $e) {
                    Log::error(
                        'Proxy media Human gagal',
                        [
                            'message' =>
                                $e->getMessage(),
                            'trace' =>
                                $e->getTraceAsString(),
                        ]
                    );

                    return response()->json([
                        'success' => false,
                        'message' =>
                            'Media gagal ditampilkan.',
                    ], 500);
                }
            }


    public function updateHumanChecklist(
        Request $request,
        $id
    ) {
        try {
            $validated =
                $request->validate([
                    'field' => [
                        'required',
                        'string',
                        'in:can_push,service_plan,need_analysys,done_meeting,mini_audit,insight,study_kasus',
                    ],
                    'value' => [
                        'required',
                        'boolean',
                    ],
                ]);

            $lead = CRM_HUMAN::find($id);

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Data CRM Human tidak ditemukan.',
                ], 404);
            }

            $field =
                $validated['field'];

            $value =
                (bool) $validated['value'];

            /*
            * Menggunakan assignment langsung
            * agar tidak bergantung pada fillable.
            */
            $lead->{$field} = $value;
            $lead->save();

            \Log::info(
                'Checklist CRM Human diperbarui',
                [
                    'lead_id' => $lead->getKey(),
                    'field' => $field,
                    'value' => $value,
                ]
            );

            return response()->json([
                'success' => true,
                'message' =>
                    'Checklist berhasil diperbarui.',
                'data' => [
                    'id' => $lead->getKey(),
                    'field' => $field,
                    'value' => (bool) $lead->{$field},
                ],
            ]);
        } catch (
            \Illuminate\Validation\ValidationException
            $e
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Data checklist tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error(
                'Gagal memperbarui checklist CRM Human',
                [
                    'lead_id' => $id,
                    'message' => $e->getMessage(),
                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Gagal menyimpan checklist.',
            ], 500);
        }
    }

    public function incrementHumanFollowUp($id)
    {
        try {
            $result = DB::connection('pgsql_supabase')
                ->transaction(function () use ($id) {
                    $lead = CRM_HUMAN::query()
                        ->lockForUpdate()
                        ->find($id);

                    if (!$lead) {
                        return [
                            'status' => 404,
                            'message' => 'Data CRM Human tidak ditemukan.',
                        ];
                    }

                    $current = min(3, max(
                        0,
                        (int) ($lead->jumlah_follow_up ?? 0)
                    ));

                    if ($current >= 3) {
                        return [
                            'status' => 422,
                            'message' => 'Seluruh tahapan follow up sudah selesai.',
                        ];
                    }

                    if ((bool) ($lead->today ?? false)) {
                        return [
                            'status' => 422,
                            'message' => 'Follow up hari ini sudah dilakukan.',
                        ];
                    }

                    $lead->jumlah_follow_up = $current + 1;
                    $lead->today = true;

                    if ((int) $lead->jumlah_follow_up >= 3) {
                        $lead->status = 'ghosting';
                    }

                    $lead->save();

                    return [
                        'status' => 200,
                        'message' => 'Follow up berhasil dicatat.',
                        'data' => [
                            'id' => $lead->getKey(),
                            'jumlah_follow_up' => (int) $lead->jumlah_follow_up,
                            'today' => (bool) $lead->today,
                            'status' => $lead->status,
                        ],
                    ];
                });

            return response()->json([
                'success' => $result['status'] === 200,
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
            ], $result['status']);
        } catch (\Throwable $e) {
            Log::error('Gagal menambah follow up CRM Human', [
                'lead_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat follow up.',
            ], 500);
        }
    }
}
