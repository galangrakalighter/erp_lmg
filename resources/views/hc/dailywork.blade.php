@include('partials.header')

{{-- Menggunakan Vite untuk akses offline (Axios & SortableJS) --}}
@vite(['resources/js/app.js'])

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root { 
        /* Karena 10 menit = 50px, maka 1 jam (60 menit) = 300px */
        --grid-hour-height: 300px; 
        --grid-minor-height: 50px; /* Jarak antar .10, .20, dll */
        --primary-color: #4e73df;
        --border-color: #e3e6f0;
    }
    
    .hour-sidebar { width: 85px; background: #f8f9fc; flex-shrink: 0; }
    .hour-major { 
        height: var(--grid-minor-height); /* Tinggi label jam utama */
        font-size: 14px; 
        color: var(--primary-color); 
        font-weight: bold; 
        display: flex; 
        align-items: center; 
        justify-content: flex-end; 
        padding-right: 15px;
    }

    .hour-minor {
        height: var(--grid-minor-height); /* Jarak 50px */
        font-size: 11px; 
        color: #a1a1a1;
        display: flex; 
        align-items: center; 
        justify-content: flex-end;
        padding-right: 15px;
        border-bottom: 1px dashed #f0f0f0;
    }

    #daily-grid {
        /* Total tinggi: 8 jam * 300px = 2400px */
        height: 2700px; 
        background-color: #fff; 
        position: relative;
        border: 1px solid var(--border-color);
        /* Garis pandu visual setiap 50px (10 menit) */
        background-image: linear-gradient(var(--border-color) 1px, transparent 1px);
        background-size: 100% var(--grid-minor-height); 
    }

    .grid-line {
        position: absolute; 
        width: 100%; 
        border-top: 
        1px solid var(--border-color);
        pointer-events: none; z-index: 1;
    }

    /* KARTU JADWAL */
    .task-card-scheduled { 
        position: absolute; left: 0; width: 100%; margin: 0 !important; 
        border-radius: 0; border-width: 0 0 0 6px !important; border-style: solid; 
        background: white; z-index: 10; overflow: hidden;
        transition: top 0.2s ease, height 0.1s ease; border-bottom: 1px solid rgba(0,0,0,0.1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .task-subordinate {
        border-left: 6px double #6c757d !important; /* Garis ganda untuk pembeda */
        background-color: #fcfcfc;
        opacity: 0.9;
    }

    .task-subordinate .task-title {
        color: #4a5568;
        font-style: italic;
    }

    /* Penanda teks Review untuk Bawahan */
    .badge-subordinate-review {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
        font-size: 9px;
    }

    /* Class untuk menyembunyikan task bawahan secara default */
    .subordinate-hidden {
        display: none !important;
    }

    .sortable-dragging * {
        user-select: none !important;
    }

    /* Memberikan bayangan pada kartu yang sedang diangkat */
    .sortable-ghost {
        opacity: 0.4;
        background-color: var(--primary-color) !important;
    }

    .task-card-scheduled[data-pic="{{ Auth::id() }}"][data-status="0"],
    .task-card-scheduled[data-pic="{{ Auth::id() }}"][data-status="2"] { cursor: grab !important; }
    .task-card-scheduled[data-pic="{{ Auth::id() }}"][data-status="7"] { cursor: grab !important; }
    
    .task-card-scheduled:active {
        cursor: grabbing !important;
    }
    .task-title { font-size: 13px !important; font-weight: 800; color: #1a202c; line-height: 1.2; }
    
    .task-time-range { 
        font-size: 11px !important; font-weight: 800; color: var(--primary-color); 
        white-space: nowrap; cursor: pointer; padding: 2px 4px; border-radius: 4px;
    }
    .task-time-range:hover { background: rgba(78, 115, 223, 0.1); }
    
    .time-edit-input { 
        font-size: 10px; width: 70px; border: 1px solid var(--primary-color); 
        border-radius: 4px; display: none; 
    }
    
    .action-container { position: absolute; bottom: 5px; right: 8px; display: flex; gap: 4px; z-index: 20; }
    .btn-xs { padding: 0.1rem 0.4rem; font-size: 0.65rem; border-radius: 4px; font-weight: bold; }

    .resize-handle {
        position: absolute; bottom: 0; left: 0; width: 100%; height: 8px;
        cursor: ns-resize; z-index: 30; background: transparent;
    }
    .resize-handle:hover { background: rgba(0,0,0,0.1); }

    body.resizing { cursor: ns-resize !important; user-select: none; }
    body.resizing .task-card-scheduled { transition: none !important; }

    @keyframes pulse-red {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.02); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-pulse { animation: pulse-red 2s infinite; }
    
    .badge-soft-primary { background: #eef2ff; color: #4e73df; }
    .bg-light-danger {
        background-color: #fff5f5;
    }
    /* Sembunyikan secara default untuk elemen yang tidak aktif */

    /* Gaya khusus untuk membedakan tampilan review agar tidak bingung */
    .type-subordinate {
        border-left: 6px solid #f6c23e !important; /* Warna kuning khas review */
        background-color: #fffaf0;
    }
    .bg-white-transparent {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .gap-1 { gap: 4px; }
    .subordinate-task {
        opacity: 0.95;
        z-index: 5;
        background-color: #fff9e6; /* Sedikit krem agar beda dengan putih */
    }
    .my-task {
        z-index: 10;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 text-gray-800 font-weight-bold mb-0">Daily Work Schedule</h1>
            <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i> Shift: 09:00 - 17:00</small>
        </div>
        
        {{-- Sisi Kanan: Grouping Filter & Tombol Tambah --}}
        <div class="d-flex align-items-center" style="gap: 15px;">
            
            @if(Auth::user()->role != "content_creator")
                {{-- Filter User (Hanya muncul di Desktop) --}}
                <div class="d-none d-md-flex align-items-center flex-wrap" style="gap: 4px;">
                    
                    {{-- 1. Tombol 'My Schedule' --}}
                    <button class="btn btn-primary active btn-xs rounded-pill px-3 btn-filter-grid" 
                            onclick="filterGrid('{{ Auth::id() }}', this)">
                        <i class="fas fa-circle mr-1" style="font-size: 7px; vertical-align: middle; color: #fff;"></i>
                        My Schedule
                    </button>

                    {{-- 2. Tombol Bawahan --}}
                    @foreach($subordinateTasks->unique('user_id') as $sub)
                        @if($sub->user_id !== Auth::id())
                            <button class="btn btn-outline-secondary btn-xs rounded-pill px-3 btn-filter-grid" 
                                    onclick="filterGrid('{{ $sub->user_id }}', this)">
                                <i class="fas fa-circle mr-1" style="font-size: 7px; vertical-align: middle; color: #ffc107;"></i>
                                {{ $sub->email }}
                            </button>
                        @endif
                    @endforeach
                </div>

                {{-- Garis pembatas kecil antara filter dan tombol tambah --}}
                <div class="d-none d-md-block border-left" style="height: 24px; width: 1px;"></div>
            @endif

            {{-- Tombol Tambah Task (Selalu di paling kanan) --}}
            <button type="button" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" data-toggle="modal" data-target="#tambahModal">
                <i class="fas fa-plus fa-sm mr-1"></i> Tambah Task
            </button>
        </div>
    </div>

    {{-- TASK POOL SECTION --}}
    <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px;">
        <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
            <h6 class="font-weight-bold text-primary mb-0">
                <i class="fas fa-layer-group mr-1"></i> Task Pool
            </h6>
            <span class="badge badge-soft-primary p-2" id="pool-total-display">
                <i class="far fa-clock mr-1"></i> Total Estimasi: <strong>{{ $formatTotalPool ?? '0' }}</strong>
            </span>
        </div>

        <div class="card-body bg-light rounded shadow-inner mt-2 m-3">
            <div id="unscheduled-pool" class="d-flex flex-wrap" style="gap: 12px; min-height: 60px;">
                @foreach($unscheduled_tasks as $task)
                    @php
                        // Logika penentuan warna border dan background jika overdue
                        $isOverdue = ($task->status == 7);
                        $borderClass = $isOverdue ? 'border-danger' : 'border-left-' . ($priority_colors[$task->priority] ?? 'secondary');
                        $bgClass = $isOverdue ? 'bg-light-danger' : 'bg-white';
                        $dragClass = $isOverdue ? 'no-drag' : 'task-card-source';
                    @endphp

                    <div class="{{ $dragClass }} card shadow-sm {{ $borderClass }} {{ $bgClass }}"
                        data-id="{{ $task->id }}" 
                        data-menit="{{ $task->menit }}" 
                        data-status="{{ $task->status }}" 
                        style="width: 240px; cursor: {{ $isOverdue ? 'default' : 'grab' }}; border-radius: 8px; {{ $isOverdue ? 'border-left: 5px solid #e74a3b !important;' : '' }}">
                        
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="task-title {{ $isOverdue ? 'text-danger font-weight-bold' : 'text-dark' }} text-truncate" style="max-width: 160px;">
                                    {{ $task->id_manajement_konten ? "Edit Konten" : "" }} {{ $task->nama_task }} ({{ Str::upper($task->nama) }})
                                </span>
                                <span class="badge {{ $isOverdue ? 'badge-danger' : 'badge-primary' }}">
                                    {{ $task->menit }}m
                                </span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mt-1">
                                @if($isOverdue)
                                    <small class="text-danger font-italic" style="font-size: 0.75rem;">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Overdue
                                    </small>
                                    
                                    {{-- TOMBOL KIRIM / SEND --}}
                                    <button type="button" 
                                        onclick="changeStatus({{ $task->id }}, 'stop')" 
                                        class="btn btn-xs btn-primary py-0 px-2" 
                                        style="font-size: 0.7rem; border-radius: 4px; background-color: #4e73df;">
                                        <i class="fas fa-paper-plane mr-1"></i> Kirim
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- MAIN GRID SECTION --}}
    <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="row no-gutters">
                {{-- Sidebar Jam --}}
                <div class="col-auto hour-sidebar border-right">
                    @for($i = 8; $i <= 16; $i++)
                        {{-- Label Jam Utama (09:00, 10:00, dst) --}}
                        <div class="hour-major">{{ sprintf('%02d:00', $i) }}</div>
                        
                        {{-- Label Menit (Jarak 50px per item) --}}
                        <div class="hour-minor">{{ sprintf('%02d:10', $i) }}</div>
                        <div class="hour-minor">{{ sprintf('%02d:20', $i) }}</div>
                        <div class="hour-minor">{{ sprintf('%02d:30', $i) }}</div>
                        <div class="hour-minor">{{ sprintf('%02d:40', $i) }}</div>
                        <div class="hour-minor">{{ sprintf('%02d:50', $i) }}</div>
                    @endfor
                    <div class="hour-major">17:00</div>
                </div>

                {{-- Area Drop Grid --}}
                <div class="col position-relative">
                    <div id="daily-grid">
                        {{-- Garis Grid per Jam --}}
                        @for($i = 0; $i <= 8; $i++)
                            <div class="grid-line" style="top: {{ $i * 300 }}px;"></div>
                        @endfor
                        @for($i = 0; $i <= 48; $i++)
                            @if($i % 6 != 0) {{-- Hindari double line dengan jam utama --}}
                                <div class="grid-line" style="top: {{ $i * 50 }}px; border-top: 1px dashed #ebedf3; opacity: 0.5;"></div>
                            @endif
                        @endfor

                        @foreach($scheduled_tasks as $task)
                            @php
                                $startGrid = \Carbon\Carbon::createFromTime($startHour, 0, 0);
                                $taskStart = \Carbon\Carbon::parse($task->jam_mulai);
                                $taskEnd = $taskStart->copy()->addMinutes($task->menit);
                                $email = $task->vic_email ?? '';
                                
                                // PERBAIKAN: Gunakan 300 (skala baru), bukan 80
                                $topPx = ($startGrid->diffInMinutes($taskStart, false) / 60) * 300;
                                $heightPx = ($task->menit / 60) * 300;
                                
                                $isMyTask = ($task->pic == Auth::id());
                                $isRunning = ($task->status == 1);
                                $isReview = ($task->status == 3);
                                $cardTypeClass = $isMyTask ? 'my-task' : 'subordinate-task d-none';
                                $borderStyle = !$isMyTask ? 'border-left: 4px solid #ffc107;' : '';
                                $rentangWaktu = $taskStart->format('H.i') . ' - ' . $taskEnd->format('H.i');
                            @endphp

                            <div class="task-card-scheduled card {{ $cardTypeClass }} {{ $isRunning ? 'bg-primary text-white' : '' }} {{ $task->status == 5 ? 'bg-light' : '' }} {{ $task->status == 7 ? 'bg-danger text-white' : '' }} {{ $task->status == 8 ? 'bg-warning text-white' : '' }} border-left-{{ $priority_colors[$task->priority] ?? 'secondary' }}"
                                data-id="{{ $task->id }}" 
                                data-email="{{ $email }}"
                                data-status="{{ $task->status }}" 
                                data-pic="{{ $task->pic }}" 
                                data-menit="{{ $task->menit }}" 
                                style="top: {{ $topPx }}px; height: {{ $heightPx }}px; min-height: 50px; border-radius: 8px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: absolute; width: 100%;">

                                <div class="card-body p-2 position-relative h-100 d-flex flex-column justify-content-center">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        
                                        <div class="text-truncate" style="flex-grow: 1; margin-right: 10px;">
                                            {{-- Badge Nama untuk Bawahan --}}
                                            @if(!$isMyTask)
                                                <span class="badge {{ $isRunning || $task->status == 7 ? 'badge-light text-dark' : 'badge-dark' }} mb-1" style="font-size: 8px; opacity: 0.9;">
                                                    <i class="fas fa-user mr-1"></i> {{ strtoupper(explode('@', $email)[0]) }}
                                                </span><br>
                                            @endif

                                            <strong class="task-title {{ ($isRunning || $task->status == 7) ? 'text-white' : 'text-dark' }}" style="font-size: 12px; font-weight: 700; line-height: 1.1; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $task->id_manajement_konten ? "Edit Konten" : "" }} {{ $task->nama_task }} ({{ Str::upper($task->nama ?? $task->project_name) }})  @if($task->status == 1)
                                                    <span class="badge badge-light text-primary border" style="font-size: 7px; padding: 1px 3px;"><i class="fas fa-play mr-1"></i>SEDANG BERJALAN</span>
                                                @elseif($task->status == 3)
                                                    <span class="badge badge-warning" style="font-size: 7px; padding: 1px 3px;"><i class="fas fa-clock mr-1"></i>MENUNGGU APPROVAL</span>
                                                @elseif(in_array($task->status, [4, 5]))
                                                    <span class="badge badge-success" style="font-size: 7px; padding: 1px 3px;"><i class="fas fa-check mr-1"></i>DONE</span>
                                                @elseif($task->status == 7)
                                                    <span class="badge badge-light text-danger" style="font-size: 7px; padding: 1px 3px;"><i class="fas fa-exclamation mr-1"></i>OVERDUE</span>
                                                @elseif($task->status == 8)
                                                    <span class="badge badge-danger" style="font-size: 7px; padding: 1px 3px;"><i class="fas fa-stopwatch mr-1"></i>WAKTU HABIS</span>
                                                @endif
                                            </strong>
                                            
                                            {{-- LABEL STATUS DINAMIS --}}
                                            @if($isRunning)
                                                <span class="running-timer badge badge-light text-primary border shadow-sm ml-1" 
                                                    data-id="{{ $task->id }}" 
                                                    data-start-time="{{ $task->jam_mulai }}" 
                                                    data-duration="{{ $task->menit }}"
                                                    style="font-size: 9px; padding: 2px 4px; font-family: monospace;">
                                                    <i class="fas fa-hourglass-half"></i> --:--
                                                </span>
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-center flex-shrink-0" style="gap: 8px;">
                                            
                                            {{-- AREA TANGGAL --}}
                                            <div class="task-date-info d-flex align-items-center {{ ($isRunning || $task->status == 7) ? 'text-white' : '' }}" 
                                                style="gap: 6px; border-right: 1px solid rgba(0,0,0,0.1); padding-right: 8px;">
                                                <div class="text-center" style="line-height: 1;">
                                                    <small style="font-size: 7px; display: block; opacity: 0.7; font-weight: 600;">START</small>
                                                    <span style="font-size: 11px; font-weight: 800;">{{ \Carbon\Carbon::parse($task->tanggal_mulai)->format('d/m') }}</span>
                                                </div>
                                                <div class="text-center" style="line-height: 1;">
                                                    <small style="font-size: 7px; display: block; opacity: 0.7; font-weight: 600;">END</small>
                                                    <span class="{{ ($isRunning || $task->status == 7) ? 'text-white' : 'text-danger' }}" style="font-size: 11px; font-weight: 800;">{{ \Carbon\Carbon::parse($task->tanggal_deadline)->format('d/m') }}</span>
                                                </div>
                                            </div>

                                            {{-- JAM DAN TOMBOL ACTION (SEJAJAR) --}}
                                            <div class="d-flex align-items-center" style="gap: 5px;">
                                                <span class="task-time-range {{ ($isRunning || $task->status == 7) ? 'text-white' : 'text-primary' }}" 
                                                    style="font-size: 10px; font-weight: 900; background: rgba(0,0,0,0.07); padding: 3px 6px; border-radius: 4px; white-space: nowrap;"
                                                    @if($isMyTask && !in_array($task->status, [1, 3, 4, 7, 8])) onclick="enableTimeEdit(this)" @endif>
                                                    {{ $rentangWaktu }}
                                                </span>
                                                <input type="time" class="form-control form-control-sm edit-time-input shadow-sm" 
                                                    style="display: none; width: 85px; font-size: 11px; height: 26px; padding: 2px 5px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 100; border: 2px solid #4e73df;" 
                                                    onblur="saveManualTime(this)"
                                                    onkeydown="if(event.key === 'Enter') { this.blur(); }">

                                                {{-- PINDAHKAN LOGIKA BUTTON KE SINI --}}
                                                <div class="action-buttons-wrapper">
                                                    @if(!$isMyTask)
                                                        @if($isReview)
                                                            <div class="d-flex">
                                                                <button onclick="changeStatus({{ $task->id }}, 'approve')" class="btn btn-xs btn-success mr-1 shadow-sm" style="font-size: 9px; padding: 1px 4px;"><i class="fas fa-check"></i></button>
                                                                <button onclick="changeStatus({{ $task->id }}, 'revisi')" class="btn btn-xs btn-danger shadow-sm" style="font-size: 9px; padding: 1px 4px;"><i class="fas fa-times"></i></button>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="d-flex">
                                                            @if($task->status == 1)
                                                                <button onclick="splitTask({{ $task->id }})" class="btn btn-xs btn-outline-light mr-1" style="font-size: 9px; padding: 1px 4px;">Split</button>
                                                                <button onclick="changeStatus({{ $task->id }}, 'stop')" class="btn btn-xs btn-success font-weight-bold shadow-sm" style="font-size: 9px; padding: 1px 6px;">Kirim</button>
                                                            @elseif(in_array($task->status, [0, 2, 7]))
                                                                <button onclick="returnToPool({{ $task->id }})" class="btn btn-xs btn-light border mr-1" style="padding: 1px 3px;"><i class="fas fa-undo text-danger" style="font-size: 9px;"></i></button>
                                                                <button onclick="changeStatus({{ $task->id }}, 'play')" class="btn btn-xs btn-success shadow-sm font-weight-bold" style="font-size: 9px; padding: 1px 8px;">START</button>
                                                            @elseif($task->status == 8)
                                                                <button onclick="changeStatus({{ $task->id }}, 'stop')" class="btn btn-xs btn-success font-weight-bold shadow-sm" style="font-size: 9px; padding: 1px 6px;">Kirim</button>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Note dipindahkan ke bawah judul secara ringkas jika ada --}}
                                    @if($task->note)
                                        <div class="task-note mt-1" style="font-size: 9px; opacity: 0.8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; border-top: 1px solid rgba(0,0,0,0.05); pt-1">
                                            <i class="fas fa-sticky-note mr-1"></i>{{ $task->note }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tambahModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white" style="background: linear-gradient(45deg, #4e73df, #224abe);">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Task Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="{{ route('hc_daily.storeDaily') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4" style="background-color: #f8f9fc;">
                        
                        <h6 class="text-primary font-weight-bold uppercase small mb-3"><i class="fas fa-info-circle mr-1"></i> Informasi Utama</h6>
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark small text-uppercase">Nama Task</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0">
                                        <i class="fas fa-tasks text-muted"></i>
                                    </span>
                                </div>
                                <input type="text" 
                                    name="nama_task" 
                                    class="form-control border-left-0 pl-0" 
                                    placeholder="Apa yang akan dikerjakan?" 
                                    required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark small text-uppercase">Client</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0">
                                        <i class="fas fa-user-tie text-muted"></i>
                                    </span>
                                </div>
                                <select name="client_id" id="selectClient" class="form-control border-left-0 pl-0" onchange="handleClientChange(this)" required>
                                    <option value="" selected disabled>-- Pilih Client --</option>
                                    @foreach ($produk as $item)
                                        <option value="{{ $item->id }}" data-nama="{{ $item->nama }}">
                                            {{ $item->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label class="font-weight-bold text-dark small">DIVISI</label>
                                <select name="divisi" class="form-control custom-select shadow-sm border-0" required>
                                    @foreach($result as $divId => $nama)
                                        <option value="{{ $divId }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label class="font-weight-bold text-dark small">PIC (PERSON IN CHARGE)</label>
                                <select name="pic" id="pic_select" class="form-control custom-select shadow-sm border-0" required>
                                    <option value="" selected disabled>-- Pilih PIC --</option>
                                    {{-- @foreach($vic_list as $user)
                                        <option value="{{ $user->id }}">{{ $user->email }}</option>
                                    @endforeach --}}
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="text-primary font-weight-bold uppercase small mb-3"><i class="fas fa-calendar-alt mr-1"></i> Penjadwalan & Waktu</h6>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-dark small">TANGGAL MULAI</label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control shadow-sm border-0" required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-dark small">TANGGAL DEADLINE</label>
                                <input type="date" name="tanggal_deadline" class="form-control shadow-sm border-0" required>
                            </div>
                        </div>

                        <div id="task-list-container" class="mb-4 animate__animated animate__fadeIn" style="display: none;">
                            <div class="card border-0 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                <div class="card-header bg-info text-white py-2 d-flex justify-content-between align-items-center">
                                    <span class="small font-weight-bold"><i class="fas fa-user-clock mr-1"></i> Jadwal PIC di Tanggal Ini</span>
                                    <span id="total-durasi-container" class="badge badge-light text-info shadow-sm">
                                        Total: 0 Menit
                                    </span>
                                </div>
                                <div class="table-responsive" style="max-height: 150px; overflow-y: auto;">
                                    <table class="table table-sm table-hover mb-0 bg-white" style="font-size: 12px;">
                                        <thead class="text-muted" style="background-color: #f1f3f5;">
                                            <tr>
                                                <th class="border-0 pl-3">Nama Task</th>
                                                <th class="border-0">Project</th>
                                                <th class="border-0">Waktu</th>
                                                <th class="border-0">Durasi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="task-list-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-dark small">JAM MULAI</label>
                                <input type="time" name="jam_mulai" id="jam_mulai" class="form-control shadow-sm border-0" required>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-dark small">JAM SELESAI</label>
                                <input type="time" name="jam_selesai" id="jam_selesai" class="form-control shadow-sm border-0" required>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-dark small">ESTIMASI</label>
                                <div class="input-group shadow-sm">
                                    <input type="number" name="menit" id="menit" class="form-control bg-white font-weight-bold text-primary border-0" readonly placeholder="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-white border-0 small">Min</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 form-group mb-4">
                                <label class="font-weight-bold text-dark small">PRIORITAS</label>
                                <select name="priority" class="form-control custom-select shadow-sm border-0" required>
                                    @foreach($prioritas as $pId => $pName)
                                        <option value="{{ $pId }}">{{ $pName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark small text-uppercase">Link ke Jira</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0">
                                        {{-- Mengganti ikon task menjadi ikon link/external --}}
                                        <i class="fas fa-link text-muted"></i>
                                    </span>
                                </div>
                                <input type="url" 
                                    name="jira_input" 
                                    class="form-control border-left-0 pl-0" 
                                    placeholder="https://jira.atlassian.com/..." 
                                    {{-- Pattern ini memastikan input diawali dengan http atau https --}}
                                    pattern="https?://.*" 
                                    title="Harap masukkan format URL yang valid (dimulai dengan http:// atau https://)"
                                    required>
                            </div>
                            <small class="form-text text-muted">Pastikan menyertakan http:// atau https://</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark small">CATATAN</label>
                            <textarea name="note" class="form-control shadow-sm border-0" rows="3" style="border-radius: 8px;" placeholder="Tambahkan rincian atau catatan khusus..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer bg-white border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light font-weight-bold px-4 text-muted" data-dismiss="modal">BATAL</button>
                        <button type="submit" class="btn btn-primary shadow-lg px-5 font-weight-bold" style="border-radius: 8px; letter-spacing: 1px;">
                            <i class="fas fa-save mr-2"></i>SIMPAN TASK
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const grid = document.getElementById('daily-grid');
        const pool = document.getElementById('unscheduled-pool');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        const myRole = "{{ Auth::user()->role }}";
        const myId = "{{ Auth::id() }}";

        // --- SORTABLE JS CONFIG ---
        new Sortable(pool, { 
            group: 'schedule_group', 
            animation: 150,
            onAdd: (evt) => autoReturnToPool(evt.item.getAttribute('data-id'))
        });

        new Sortable(grid, {
            group: 'schedule_group',
            animation: 150,
            sort: false,
            scroll: true,
            forceAutoScrollEdge: true, // Sangat penting: memaksa scroll saat di ujung
            scrollSensitivity: 150,    // Seberapa dekat kursor dengan tepi (dalam pixel)
            scrollSpeed: 30,           // Kecepatan scroll
            bubbleScroll: true,
            draggable: ".task-card-source, .task-card-scheduled[data-status='0'], .task-card-scheduled[data-status='2'], .task-card-scheduled[data-status='8']",
            filter: ".resize-handle, .action-container, button",
            onAdd: (evt) => handleTaskMovement(evt, true),
            onEnd: (evt) => { if (evt.to === grid && evt.from === grid) handleTaskMovement(evt, false); }
        });
        
        window.filterGrid = async function(userId, btn) {
            // Update UI Tombol (Visual feedback)
            document.querySelectorAll('.btn-filter-grid').forEach(b => {
                b.classList.replace('btn-primary', 'btn-outline-secondary');
                b.classList.remove('active');
                if(b.querySelector('.fa-circle')) b.querySelector('.fa-circle').style.color = '#ffc107';
            });

            btn.classList.replace('btn-outline-secondary', 'btn-primary');
            btn.classList.add('active');
            if(btn.querySelector('.fa-circle')) btn.querySelector('.fa-circle').style.color = '#fff';

            // Tampilkan Loading
            grid.innerHTML = '<div class="p-5 text-center text-muted"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</div>';

            try {
                const response = await axios.get(`api/hc/get-tasks-json?user_id=${userId}`);
                const tasks = response.data;
                
                grid.innerHTML = ''; 
                renderGridLines();   

                tasks.forEach(task => {
                    // PERBAIKAN: Gunakan == (dua sama dengan) atau konversi ke String agar aman dibanding dengan myId
                    const isMyTask = (String(userId) === String(myId));
                    
                    // Masukkan parameter isMyTask dengan benar
                    const cardHtml = renderTaskCard(task, isMyTask);
                    grid.insertAdjacentHTML('beforeend', cardHtml);
                });

            } catch (error) {
                console.error("Error fetching tasks:", error);
                grid.innerHTML = '<div class="alert alert-danger m-3">Gagal memuat jadwal.</div>';
            }
        };

        function renderTaskCard(task, isMyTask) {
            // --- TAMBAHKAN LOGIKA FILTER TANGGAL DI SINI ---
            const isWaitingApproval = (task.status == 3);
            
            if (isWaitingApproval) {
                const today = new Date();
                const taskDate = new Date(task.tanggal_mulai);
                
                // Membandingkan Tahun, Bulan, dan Tanggal
                const isToday = today.getFullYear() === taskDate.getFullYear() &&
                                today.getMonth() === taskDate.getMonth() &&
                                today.getDate() === taskDate.getDate();

                // Jika status 3 tapi bukan hari ini, jangan tampilkan (return string kosong)
                if (!isToday) {
                    return; 
                }
            }
            const startHour = 8;
            let teks = "";
            if(task.id_manajement_konten != null){
                teks = "Edit Konten";
            }
            const myRole = "{{ Auth::user()->role }}";
            
            const jamMulai = task.jam_mulai || "09:00";
            const durasiMenit = parseInt(task.menit) || 0;
            
            // 1. Perhitungan Posisi Top & Height
            const [h, m] = jamMulai.split(':').map(Number);
            const validH = isNaN(h) ? 9 : h;
            const validM = isNaN(m) ? 0 : m;

            const topPx = ((validH - startHour) * 300) + (validM / 60 * 300);
            const heightPx = (durasiMenit / 60) * 300;

            // 2. Perhitungan Jam Selesai
            let totalMenitSelesai = (validH * 60) + validM + durasiMenit;
            let jamSelesai = Math.floor(totalMenitSelesai / 60);
            let menitSelesai = totalMenitSelesai % 60;

            const jamSelesaiFmt = jamSelesai.toString().padStart(2, '0') + "." + 
                                menitSelesai.toString().padStart(2, '0');
            
            const jamMulaiFmt = validH.toString().padStart(2, '0') + "." + 
                                validM.toString().padStart(2, '0');

            const rentangWaktu = `${jamMulaiFmt} - ${jamSelesaiFmt}`;
            const dragClass = isMyTask ? 'task-card-scheduled' : 'task-card-locked';
            const canEditTime = isMyTask && (task.status == 0 || task.status == 2 || task.status == 7);

            // 3. Helper Format Tanggal
            const fmtDate = (dateStr) => {
                if (!dateStr) return "-/-";
                const d = new Date(dateStr);
                if (isNaN(d.getTime())) return "-/-"; 
                return `${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}`;
            };

            const isRunning = (task.status == 1);
            // const isWaitingApproval sudah didefinisikan di atas
            const isDone = (task.status == 4 || task.status == 5);
            const isOverdue = (task.status == 7);
            const isEndsTime = (task.status == 8);
            let statusLabel = '';

            if (isRunning) {
                statusLabel = `<span class="badge badge-light text-primary border ml-1" style="font-size: 8px;"><i class="fas fa-play mr-1"></i>SEDANG BERJALAN</span>`;
            } else if (isWaitingApproval) {
                statusLabel = `<span class="badge badge-warning ml-1" style="font-size: 8px;"><i class="fas fa-clock mr-1"></i>MENUNGGU APPROVAL</span>`;
            } else if (isDone) {
                statusLabel = `<span class="badge badge-light text-success ml-1" style="font-size: 8px;"><i class="fas fa-check-circle mr-1"></i>DONE</span>`;
            } else if (isOverdue) {
                statusLabel = `<span class="badge badge-light text-danger ml-1" style="font-size: 8px;"><i class="fas fa-exclamation-triangle mr-1"></i>OVERDUE</span>`;
            } else if (isEndsTime) {
                statusLabel = `<span class="badge badge-danger ml-1" style="font-size: 8px;"><i class="fas fa-stopwatch mr-1"></i>WAKTU HABIS</span>`;
            }

            // FIX: Logika penentuan class
            let statusClass = isRunning ? "bg-primary text-white" : 
                            isWaitingApproval ? "bg-info text-white" : 
                            isOverdue ? "bg-danger text-white" : 
                            isDone ? "bg-success text-white" : 
                            isEndsTime ? "bg-warning text-white" : "";

            const priorityClass = {1: 'danger', 2: 'warning', 3: 'primary'}[task.priority] || 'secondary';
            const emailPrefix = task.vic_email;

            return `
                <div class="${dragClass} card ${isMyTask ? 'my-task' : 'subordinate-task'} ${statusClass} border-left-${priorityClass}"
                    data-id="${task.id}" 
                    data-status="${task.status}" 
                    data-pic="${task.pic}" 
                    data-menit="${durasiMenit}"
                    style="top: ${topPx}px; height: ${heightPx}px; position: absolute; width: 100%; border-radius: 8px; z-index: ${isRunning ? 10 : 1}; ${!isMyTask ? 'cursor: default;' : 'cursor: grab;'}">

                    <div class="card-body p-2 d-flex flex-column h-100">
                        <div class="d-flex justify-content-between align-items-start w-100">
                            <div style="flex: 1; min-width: 0; padding-right: 10px;">
                                ${!isMyTask ? `<span class="badge badge-dark mb-1" style="font-size: 8px; opacity: 0.9;"><i class="fas fa-user mr-1"></i>${emailPrefix}</span><br>` : ''}
                                
                                ${isWaitingApproval ? `<span class="badge badge-light text-info mb-1" style="font-size: 7px; text-transform: uppercase;">Menunggu Approval</span><br>` : ''}
                                
                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <strong class="task-title text-truncate" style="font-size: 11px; line-height: 1.2; max-width: 70%;">
                                    ${teks} ${task.nama_task} (${(task.project_name || 'PROJECT').toUpperCase()}) ${statusLabel}
                                    </strong>

                                    ${isRunning ? `
                                        <span class="running-timer badge badge-light text-primary border shadow-sm" 
                                            id="timer-${task.id}"
                                            data-id="${task.id}" 
                                            data-start-time="${task.jam_mulai}" 
                                            data-duration="${durasiMenit}"
                                            style="font-size: 9px; padding: 2px 5px; font-family: monospace; white-space: nowrap;">
                                            <i class="fas fa-hourglass-half"></i> --:--
                                        </span>
                                    ` : ''}
                                </div>
                            </div>

                            <div class="d-flex align-items-center flex-shrink-0" style="gap: 10px;">
                                <div class="d-flex border-right pr-2" style="gap: 8px; font-size: 9px; line-height: 1;">
                                    <div class="text-center">
                                        <small style="display:block; font-size: 6px; opacity: 0.8;">START</small>
                                        <b>${fmtDate(task.tanggal_mulai)}</b>
                                    </div>
                                    <div class="text-center">
                                        <small style="display:block; font-size: 6px; opacity: 0.8;">END</small>
                                        <b class="${isRunning || isOverdue || isWaitingApproval ? 'text-white' : 'text-danger'}">${fmtDate(task.tanggal_deadline)}</b>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center" style="gap: 6px;">
                                    <div class="time-container" style="position: relative;">
                                        <span class="task-time-range" 
                                            ${canEditTime ? `onclick="enableTimeEdit(this)"` : ''}
                                            style="font-size: 10px; font-weight: 800; background: rgba(0,0,0,0.1); padding: 4px 8px; border-radius: 6px; white-space: nowrap; ${canEditTime ? 'cursor: pointer; border: 1px dashed rgba(255,255,255,0.5);' : ''}">
                                            ${rentangWaktu}
                                        </span>

                                        ${canEditTime ? `
                                            <input type="time" class="manual-time-input shadow-sm" 
                                                style="display: none; font-size: 11px; width: 80px; padding: 2px 5px; height: 26px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); border-radius: 4px; border: 2px solid #4e73df; background: white; color: #333; z-index: 20; outline: none;"
                                                onblur="saveManualTime(this)"
                                                onkeydown="if(event.key==='Enter') this.blur()">
                                        ` : ''}
                                    </div>
                                    
                                    <div class="action-buttons-inline">
                                        ${renderButtons(task, isMyTask, isRunning)}
                                    </div>
                                </div>
                            </div>
                        </div>
                        ${task.note ? `<div class="mt-1" style="font-size: 8.5px; opacity: 0.9; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">${task.note}</div>` : ''}
                    </div>
                    ${isMyTask ? '<div class="resize-handle" style="position: absolute; bottom: 0; left: 0; right: 0; height: 5px; cursor: ns-resize;"></div>' : ''}
                </div>
            `;
        }

        function renderButtons(task, isMyTask) {
            const status = parseInt(task.status);
            const approve = task.tanggal_approve;
            console.log(approve);
            const btnClass = "btn btn-xs shadow-sm font-weight-bold";

            if (isMyTask) {
                if (status === 1) {
                    return `<div class="d-flex">
                        <button onclick="splitTask(${task.id})" class="btn btn-xs btn-outline-light mr-1" style="font-size: 9px; padding: 1px 4px;">Split</button>
                        <button onclick="changeStatus(${task.id}, 'stop')" class="${btnClass} btn-success" style="font-size: 9px; padding: 1px 6px;">Kirim</button>
                    </div>`;
                } else if(status === 8 || status === 7){
                    return `<div class="d-flex">
                        <button onclick="changeStatus(${task.id}, 'stop')" class="${btnClass} btn-success" style="font-size: 9px; padding: 1px 6px;">Kirim</button>
                    </div>`;
                } else if ([0, 2].includes(status)) {
                    return `<div class="d-flex">
                        <button onclick="returnToPool(${task.id})" class="btn btn-xs btn-light border mr-1" style="padding: 1px 4px;"><i class="fas fa-undo text-danger" style="font-size: 9px;"></i></button>
                        <button onclick="changeStatus(${task.id}, 'play')" class="${btnClass} btn-success" style="font-size: 9px; padding: 1px 8px;">START</button>
                    </div>`;
                } else if (status === 3) {
                    return `<span class="badge badge-warning" style="font-size: 9px; padding: 3px 5px;"><i class="fas fa-clock mr-1"></i>Review</span>`;
                } else if (approve != null){
                    return `<span class="badge badge-warning" style="font-size: 9px; padding: 3px 5px;"><i class="fas fa-clock mr-1"></i>Done</span>`;
                }
                return `<span></span>`;
            }

            // Tombol untuk BAWAHAN
            if (status === 3) {
                let canApprove = false;
                if (typeof myRole !== 'undefined') {
                    if (myRole === 'head_of_division' && task.vic_role === 'digital_marketing_manager') canApprove = true;
                    else if (myRole === 'digital_marketing_manager' && task.vic_role === 'digital_marketing') canApprove = true;
                    else if (myRole === 'digital_marketing' && task.vic_role === 'content_creator') canApprove = true;
                    else if (myRole === 'super_admin') canApprove = true;
                }

                if (canApprove) {
                    return `<div class="d-flex">
                        <button onclick="changeStatus(${task.id}, 'approve')" class="btn btn-xs btn-success mr-1 shadow-sm" style="font-size: 9px; padding: 1px 5px;"><i class="fas fa-check"></i></button>
                        <button onclick="changeStatus(${task.id}, 'revisi')" class="btn btn-xs btn-danger shadow-sm" style="font-size: 9px; padding: 1px 5px;"><i class="fas fa-times"></i></button>
                    </div>`;
                }
            }

            return `<small class="${task.status == 1 || task.status == 7 ? 'text-white' : 'text-muted'} d-none d-lg-inline" style="font-size: 8px;">${typeof getStatusName === 'function' ? getStatusName(status) : ''}</small>`;
        }

        function getStatusName(status) {
            const statuses = {0: 'Not Started', 1: 'In Progress', 3: 'Review', 4: 'Done', 5: 'Split', 7: 'Overdue', 8: 'Waktu Habis'};
            return statuses[status] || status;
        }

        function renderGridLines() {
            for(let i=0; i<=9; i++) {
                const line = `<div class="grid-line" style="position:absolute; top:${i*300}px; border-top:1px dashed #eee; width:100%; height:1px;"></div>`;
                grid.insertAdjacentHTML('afterbegin', line);
            }
        }

        // Auto-load jadwal sendiri saat pertama kali buka
        const initialBtn = document.querySelector('.btn-filter-grid.active');
        if(initialBtn) initialBtn.click();

        // --- CORE FUNCTIONS ---
        window.handleTaskMovement = function(evt, isNew) {
            const CONFIG_START_HOUR = 8;
            const CONFIG_END_HOUR = 17;
            const TOTAL_HOURS = CONFIG_END_HOUR - CONFIG_START_HOUR;
            const MAX_GRID_HEIGHT = TOTAL_HOURS * 300;
            const item = evt.item;
            const rect = grid.getBoundingClientRect();
            const taskId = item.getAttribute('data-id');
            const duration = parseInt(item.getAttribute('data-menit')) || 60;
            
            // Ambil koordinat Y dari event end
            const clientY = evt.originalEvent.clientY || (evt.originalEvent.changedTouches ? evt.originalEvent.changedTouches[0].clientY : 0);
            
            let heightPx = isNew ? (duration / 60) * 300 : item.offsetHeight;
            let proposedTop = Math.round((clientY - rect.top) / 50) * 50;

            const minTopAllowed = {{ (Auth::id() == 39) ? 0 : 300 }}; 

            if (proposedTop < minTopAllowed) {
                @if(Auth::id() != 39)
                    alert("Maaf, jadwal kerja Anda dimulai dari jam 09:00!");
                @endif
                proposedTop = minTopAllowed;
            }

            // Batasi area grid (09:00 - 17:00 = 2400px)
            if (proposedTop < 0) proposedTop = 0;
            if (proposedTop + heightPx > MAX_GRID_HEIGHT) proposedTop = MAX_GRID_HEIGHT - heightPx;

            if (checkCollision(proposedTop, heightPx, taskId)) {
                alert("Slot waktu sudah terisi!");
                location.reload();
                return;
            }

            // Terapkan posisi secara permanen
            item.style.position = 'absolute';
            item.style.top = proposedTop + 'px';
            item.style.height = heightPx + 'px';
            item.style.width = '100%';

            // Update tampilan jam seketika
            updateTimeDisplay(item, proposedTop, duration);
            
            // Simpan ke database
            saveToDatabase(taskId, proposedTop, isNew);
        };

        function checkCollision(top, height, currentId) {
            let collision = false;
            grid.querySelectorAll('.task-card-scheduled').forEach(task => {
                if (task.getAttribute('data-id') === currentId) return;
                const tTop = parseFloat(task.style.top);
                const tHeight = parseFloat(task.style.height);
                if (top < (tTop + tHeight) && (top + height) > tTop) collision = true;
            });
            return collision;
        }

        function saveToDatabase(taskId, topPx, shouldRefresh) {
            console.log("masuk save to database");
            // topPx / 300 * 60 menit
            const minutesFromStart = (topPx / 300) * 60; 
            axios.post("{{ route('hc.updateSchedule') }}", { id: taskId, top: minutesFromStart })
                .then(() => { if (shouldRefresh) location.reload(); });
        }

        // --- TIME EDITING ---
        window.enableTimeEdit = function(el) {
            const input = el.nextElementSibling;
            const currentTime = el.innerText.split(' - ')[0].trim();
            
            el.style.display = 'none';
            input.style.display = 'inline-block';
            
            // Simpan jam lama di atribut data untuk backup
            input.setAttribute('data-old-value', currentTime); 
            input.value = currentTime;
            input.focus();
        };

        window.saveManualTime = function(input) {
            const card = input.closest('.task-card-scheduled');
            const displayRange = card.querySelector('.task-time-range');
            const oldTime = input.getAttribute('data-old-value');
            const newValue = input.value;
            const CONFIG_START_HOUR = 8;

            const startHour = CONFIG_START_HOUR;
            const endHour = 17;
            const maxMinutes = (endHour - startHour) * 60; // Jika jam 8, maka 540 menit. Jika jam 9, maka 480 menit.

            if (newValue === oldTime) {
                input.style.display = 'none';
                displayRange.style.display = 'inline-block';
                return;
            }

            const [h, m] = newValue.split(':').map(Number);
            const duration = parseInt(card.getAttribute('data-menit'));

            // 1. HITUNG POSISI BERDASARKAN START HOUR DINAMIS
            const hourDiff = h - startHour;
            const proposedTop = (hourDiff * 300) + (m / 60 * 300);
            
            // Total menit dari titik 0 grid
            const totalMinutesFromStart = (hourDiff * 60) + m;
            const endMinutesFromStart = totalMinutesFromStart + duration;

            // 2. VALIDASI JAM KERJA DINAMIS
            if (h < startHour) {
                alert(`Jam mulai tidak boleh sebelum jam ${String(startHour).padStart(2, '0')}:00!`);
                resetInput();
                return;
            }

            if (endMinutesFromStart > maxMinutes) {
                // Fungsi helper untuk format jam selesai agar alert lebih informatif
                const endH = Math.floor((startHour * 60 + endMinutesFromStart) / 60);
                const endM = Math.round((startHour * 60 + endMinutesFromStart) % 60);
                const formatEnd = `${String(endH).padStart(2, '0')}:${String(endM).padStart(2, '0')}`;

                alert(`Tidak bisa input jam ${newValue}. Karena durasi task ${duration} menit, jam selesai (${formatEnd}) akan melebihi jam 17:00!`);
                resetInput();
                return;
            }

            // 3. VALIDASI TABRAKAN
            if (checkCollision(proposedTop, card.offsetHeight, card.dataset.id)) {
                alert("Slot waktu tersebut sudah terisi task lain!");
                resetInput();
                return;
            }

            // EKSEKUSI JIKA LOLOS VALIDASI
            card.style.top = proposedTop + 'px';
            
            // Pastikan updateTimeDisplay juga menggunakan startHour yang benar di dalamnya
            updateTimeDisplay(card, proposedTop, duration);
            
            input.style.display = 'none';
            displayRange.style.display = 'inline-block';

            // Simpan ke database (Top relatif terhadap grid)
            axios.post("{{ route('hc.updateSchedule') }}", { 
                id: card.dataset.id, 
                top: totalMinutesFromStart 
            }).catch(err => {
                alert("Gagal sinkronisasi. Halaman dimuat ulang.");
                location.reload();
            });

            function resetInput() {
                input.value = oldTime;
                input.style.display = 'none';
                displayRange.style.display = 'inline-block';
            }
        };

        function formatMinutesToTime(totalMinutes) {
            const h = Math.floor(totalMinutes / 60) + 9;
            const m = totalMinutes % 60;
            return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}`;
        }

        function updateTimers() {
            document.querySelectorAll('.running-timer').forEach(timer => {
                // console.log(timer);
                const startTimeStr = timer.dataset.startTime; 
                // console.log(startTimeStr);
                
                const durationLimit = parseInt(timer.dataset.duration);
                const taskId = timer.dataset.id;

                // Jika data tidak lengkap, jangan jalankan
                if (!startTimeStr || startTimeStr === "null" || isNaN(durationLimit)) {
                    timer.innerHTML = "<i class='fas fa-exclamation-triangle'></i> Format Error";
                    return;
                }

                let startTime;
                
                try {
                    // Memecah string tanggal "2024-05-20 14:00:00" menjadi array angka
                    // Regex /[^\d]/ memecah berdasarkan karakter APAPUN yang bukan angka (spasi, strip, titik dua)
                    const p = startTimeStr.split(/[^\d]/); 
                    
                    if (p.length >= 5) {
                        // Format Lengkap: [YYYY, MM, DD, HH, MM, SS]
                        // p[1]-1 karena bulan di JS dimulai dari 0
                        startTime = new Date(parseInt(p[0]), parseInt(p[1])-1, parseInt(p[2]), parseInt(p[3]), parseInt(p[4]), parseInt(p[5]) || 0);
                    } else {
                        // Format Jam Saja: [HH, MM, SS]
                        startTime = new Date();
                        startTime.setHours(parseInt(p[0]), parseInt(p[1]), parseInt(p[2]) || 0, 0);
                    }
                } catch (e) {
                    timer.innerHTML = "Err Parse";
                    return;
                }

                const now = new Date();
                
                
                const diffMs = now.getTime() - startTime.getTime();

                // VALIDASI TERAKHIR: Jika hasil pengurangan bukan angka
                if (isNaN(diffMs)) {
                    timer.innerHTML = "Waktu Invalid";
                    return;
                }

                const detikTerpakai = Math.floor(diffMs / 1000);
                const totalDetikLimit = durationLimit * 60;
                const sisaDetikTotal = totalDetikLimit - detikTerpakai;

                if (sisaDetikTotal <= 0) {
                    timer.innerHTML = `<span class="badge badge-danger">SELESAIKAN!</span>`;
                    if (!timer.dataset.isAutoUpdated) {
                        timer.dataset.isAutoUpdated = "true";
                        if (typeof autoUpdateToEndsTime === "function") autoUpdateToEndsTime(taskId);
                    }
                } else {
                    const m = Math.floor(sisaDetikTotal / 60);
                    const s = sisaDetikTotal % 60;
                    
                    // Mencegah tampilan NaN jika m atau s gagal dihitung
                    const displayM = isNaN(m) ? 0 : m;
                    const displayS = isNaN(s) ? 0 : s;

                    timer.innerHTML = `<i class="fas fa-hourglass-half mr-1"></i>${displayM}:${displayS.toString().padStart(2, '0')}`;
                }
            });
        }

        // Jalankan setiap detik
        setInterval(updateTimers, 1000);

        // --- RESIZE LOGIC ---
        let isResizing = false, currentCard = null, startY, startH;

        grid.addEventListener('mousedown', e => {
            if (e.target.classList.contains('resize-handle')) {
                isResizing = true;
                currentCard = e.target.closest('.task-card-scheduled');
                startY = e.clientY;
                startH = currentCard.offsetHeight;
                document.body.classList.add('resizing');
            }
        });

        document.addEventListener('mouseup', () => {
        if (isResizing) {
            // Gunakan pembagi 300
            const duration = (currentCard.offsetHeight / 300) * 60;
            axios.post("{{ route('hc.updateSchedule') }}", { 
                id: currentCard.dataset.id, 
                menit: duration,
                top: (parseFloat(currentCard.style.top) / 300) * 60 
            });
        }
        isResizing = false;
        document.body.classList.remove('resizing');
    });

        function updateTimeDisplay(item, topPx, duration) {
            const CONFIG_START_HOUR = 8;
            const startHour = CONFIG_START_HOUR;
    
            // Hitung total menit dari titik 0:00 hari tersebut
            // (startHour * 60) adalah offset jam mulai (misal 8 * 60 = 480 menit)
            // (topPx / 300 * 60) adalah posisi menit relatif di dalam grid
            const startTotal = (startHour * 60) + (topPx / 300 * 60); 
            const endTotal = startTotal + duration;

            // Helper untuk format menit ke HH:mm
            const format = (m) => {
                const h = Math.floor(m / 60);
                const min = Math.round(m % 60);
                return `${h.toString().padStart(2, '0')}:${min.toString().padStart(2, '0')}`;
            };

            const display = item.querySelector('.task-time-range');
            if (display) {
                display.innerText = `${format(startTotal)} - ${format(endTotal)}`;
            }
        }
    });

    let scrollInterval;

    document.addEventListener('mousemove', (e) => {
        const draggingElement = document.querySelector('.sortable-chosen');
        
        if (draggingElement) {
            const threshold = 200; 
            const windowHeight = window.innerHeight;
            const mouseY = e.clientY;
        }
    });

    document.addEventListener('mouseup', () => {
        if (scrollInterval) {
            clearInterval(scrollInterval);
            scrollInterval = null;
        }
    });

    window.autoReturnToPool = function(id) {
        axios.post("{{ route('hc.updateSchedule') }}", { id: id, reset: true })
            .then(() => location.reload())
            .catch(() => location.reload());
    };

    // --- AJAX ACTIONS ---
    function autoUpdateToOverdue(id) {
        axios.post("{{ route('hc.updateStatus') }}", { id: id, action: 'set_overdue', status: 7 }).then(() => location.reload());
    }
    function autoUpdateToEndsTime(id) {
        axios.post("{{ route('hc.updateStatus') }}", { id: id, action: 'habis', status: 8 }).then(() => location.reload());
    }
    function returnToPool(id) {
        if(confirm('Kembalikan ke Pool?')) axios.post("{{ route('hc.updateSchedule') }}", { id: id, reset: true }).then(() => location.reload());
    }
    function splitTask(id) {
        if(confirm('Split task ini? Sisa waktu akan dikembalikan ke Pool.')) axios.post("{{ route('hc.cancelRemaining') }}", { id: id }).then(() => location.reload());
    }
    function changeStatus(id, action) {
        let jamMulaiStr = null;
        if (action === 'play') {
            const card = document.querySelector(`.task-card-scheduled[data-id="${id}"]`);
            jamMulaiStr = card.querySelector('.task-time-range').innerText.split(' - ')[0].trim();
        }
        axios.post("{{ route('hc.updateStatus') }}", { id: id, action: action, jam_mulai: jamMulaiStr }).then(() => location.reload());
    }

    const baseUrl = window.location.origin;

    const inputPic = document.getElementById('pic_select');
    const inputTanggal = document.getElementById('tanggal_mulai');
    const inputMulai = document.getElementById('jam_mulai');
    const inputSelesai = document.getElementById('jam_selesai');
    const inputEstimasi = document.getElementById('menit');

    function fetchTaskByPic() {
        const tanggal = inputTanggal.value;
        const pic = inputPic.value;
        const container = document.getElementById('task-list-container');
        const tableBody = document.getElementById('task-list-body');
        if (tanggal && pic) {
            tableBody.innerHTML = '<tr><td colspan="3" class="text-center p-2 text-muted small italic">Mengecek jadwal...</td></tr>';
            container.style.display = 'block';

            fetch(`${baseUrl}/api/get-tasks-by-date?tanggal=${tanggal}&pic=${pic}`)
                .then(response => response.json())
                .then(data => {
                    const totalContainer = document.getElementById('total-durasi-container');
                    
                    tableBody.innerHTML = '';
                    let totalMenitAkumulasi = 0; // Inisialisasi counter

                    if (data.length > 0) {
                        data.forEach(task => {
                            // Tambahkan durasi ke total
                            let durasiTask = parseInt(task.menit || 0);
                            totalMenitAkumulasi += durasiTask;

                            let jamM = task.jam_mulai ? task.jam_mulai.substring(0, 5) : "00:00";
                            let jamS = (task.jam_selesai && task.jam_selesai !== "00:00:00") 
                                    ? task.jam_selesai.substring(0, 5) 
                                    : hitungJamSelesaiManual(jamM, durasiTask);

                            tableBody.innerHTML += `
                                <tr>
                                    <td>${task.nama_task}</td>
                                    <td>${task.nama}</td>
                                    <td><span class="badge badge-secondary">${jamM} - ${jamS}</span></td>
                                    <td>${formatDurasi(durasiTask)}</td>
                                </tr>`;
                        });

                        // Tampilkan total di header
                        if(totalContainer) {
                            totalContainer.innerText = "Total: " + formatDurasi(totalMenitAkumulasi);
                            totalContainer.classList.replace('badge-light', 'badge-warning'); // Ubah warna jadi kuning agar kontras
                        }

                    } else {
                        tableBody.innerHTML = '<tr><td colspan="3" class="text-center p-2 text-success">Jadwal Kosong</td></tr>';
                        if(totalContainer) totalContainer.innerText = "Total: 0 Menit";
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    tableBody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Gagal memuat data</td></tr>';
                });
        } else {
            if(container) container.style.display = 'none';
        }
    }


    function calculateMinutes() {
        const picId = inputPic.value;
        const mulai = inputMulai.value;
        const selesai = inputSelesai.value;

        if (mulai) {
            const jamM = parseInt(mulai.split(':')[0]);
            let limitMulai = (picId == "39") ? 8 : 9;
            if (jamM < limitMulai) {
                alert(`PIC ini minimal mulai jam 0${limitMulai}:00`);
                inputMulai.value = ""; return;
            }
        }

        if (selesai) {
            const [jamS, menitS] = selesai.split(':').map(Number);
            if (jamS > 17 || (jamS === 17 && menitS > 0)) {
                alert("Jam selesai tidak boleh lebih dari jam 17:00");
                inputSelesai.value = ""; return;
            }
        }

        if (mulai && selesai) {
            const [h1, m1] = mulai.split(':').map(Number);
            const [h2, m2] = selesai.split(':').map(Number);
            const diff = ((h2 * 60) + m2) - ((h1 * 60) + m1);
            
            if (diff < 0) {
                alert("Jam selesai tidak boleh sebelum jam mulai!");
                inputSelesai.value = ""; 
                inputEstimasi.value = "";
            } else {
                inputEstimasi.value = diff;
            }
        }
    }

    if(inputPic) inputPic.addEventListener('change', () => { fetchTaskByPic(); calculateMinutes(); });
    if(inputTanggal) inputTanggal.addEventListener('change', fetchTaskByPic);
    if(inputMulai) inputMulai.addEventListener('change', calculateMinutes);
    if(inputSelesai) inputSelesai.addEventListener('change', calculateMinutes);
    
    async function handleClientChange(el) {
        const userId = {{ Auth::user()->id }}
        
        const clientId = el.value;
        const picSelect = document.getElementById('pic_select');

        if (!clientId) return;

        // 1. Reset dropdown PIC dan tunjukkan status loading
        picSelect.innerHTML = '<option value="" selected disabled>Sedang memuat...</option>';

        try {
            // 2. Panggil API (Sesuaikan URL API kamu)
            // Contoh: /api/get-pic-by-client/5
            const response = await fetch(`/api/client-pics/${clientId}/${userId}`);
            
            if (!response.ok) throw new Error('Gagal mengambil data PIC');

            const data = await response.json();

            console.log(data);

            // 3. Bersihkan dropdown PIC kembali
            picSelect.innerHTML = '<option value="" selected disabled>-- Pilih PIC --</option>';

            // 4. Masukkan data dari API ke dalam Select
            if (data.length > 0) {
                data.forEach(pic => {
                    let option = document.createElement('option');
                    option.value = pic.id; // Sesuaikan dengan key ID dari API
                    option.text = pic.email; // Menggunakan email sesuai request script kamu
                    picSelect.appendChild(option);
                });
            } else {
                picSelect.innerHTML = '<option value="" selected disabled>Tidak ada PIC ditemukan</option>';
            }

        } catch (error) {
            console.error('Error:', error);
            picSelect.innerHTML = '<option value="" selected disabled>Gagal memuat data</option>';
            alert('Terjadi kesalahan saat mengambil data PIC.');
        }
    }
</script>

@include('partials.footer')