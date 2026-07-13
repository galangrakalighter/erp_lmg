@include('partials.header')

{{-- Menggunakan Vite untuk akses offline (Axios & SortableJS) --}}
@vite(['resources/js/app.js'])

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root { 
        --primary-color: #4e73df;
        --border-color: #e3e6f0;
    }

    /* Container Utama untuk mengontrol Zoom */
    #zoom-container {
        display: flex;
        --hour-height: 300px; 
        /* Variabel ini akan otomatis terupdate saat --hour-height berubah via JS */
        --grid-minor-height: calc(var(--hour-height) / 6);
    }

    .hour-sidebar { 
        width: 85px; 
        background: #f8f9fc; 
        flex-shrink: 0; 
        position: sticky;
        left: 0;
        z-index: 20;
        border-right: 1px solid var(--border-color);
    }

    .hour-major { 
        height: var(--grid-minor-height);
        font-size: 14px; 
        color: var(--primary-color); 
        font-weight: bold; 
        display: flex; 
        align-items: center; 
        justify-content: flex-end; 
        padding-right: 15px;
        transition: height 0.1s ease-out;
    }

    .hour-minor {
        height: var(--grid-minor-height);
        font-size: 11px; 
        color: #a1a1a1;
        display: flex; 
        align-items: center; 
        justify-content: flex-end;
        padding-right: 15px;
        border-bottom: 1px dashed #f0f0f0;
        transition: height 0.1s ease-out;
    }

    #daily-grid {
        flex-grow: 1;
        height: calc(9 * var(--hour-height)); 
        background-color: #fff; 
        position: relative;
        border: 1px solid var(--border-color);
        background-image: linear-gradient(var(--border-color) 1px, transparent 1px);
        background-size: 100% var(--grid-minor-height); 
    }

    .grid-line {
        position: absolute; 
        width: 100%; 
        border-top: 1px solid var(--border-color);
        pointer-events: none; 
        z-index: 1;
        transition: top 0.1s ease-out;
    }

    /* KARTU JADWAL */
    .task-card-scheduled { 
        position: absolute; 
        left: 0; 
        width: 100%; 
        z-index: 10; 
        overflow: hidden;
        border-radius: 8px;
        border-left: 6px solid;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: top 0.1s ease-out, height 0.1s ease-out;
    }

    .task-card-scheduled[data-pic="{{ Auth::id() }}"][data-status="0"],
    .task-card-scheduled[data-pic="{{ Auth::id() }}"][data-status="2"] { cursor: grab !important; }
    .task-card-scheduled[data-pic="{{ Auth::id() }}"][data-status="3"] { cursor: grab !important; }
    .task-card-scheduled[data-pic="{{ Auth::id() }}"][data-status="7"] { cursor: grab !important; }
    .task-card-scheduled:active {
        cursor: grabbing !important;
    }
    .my-task { z-index: 11; }
    .subordinate-task { opacity: 0.95; background-color: #fff9e6; }
    .task-title { font-size: 12px; font-weight: 700; color: #1a202c; }

    /* Status Colors */
    .border-left-primary { border-left-color: #4e73df !important; }
    .border-left-success { border-left-color: #1cc88a !important; }
    .border-left-danger { border-left-color: #e74a3b !important; }
    .border-left-warning { border-left-color: #f6c23e !important; }
    .btn-filter-grid {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        letter-spacing: 0.2px;
    }

    /* Saat Tombol Aktif (Diklik) */
    .btn-filter-grid.active {
        background-color: #4e73df !important; /* Warna Biru Utama */
        border-color: #4e73df !important;
        color: #ffffff !important; /* Teks Putih */
        box-shadow: 0 2px 4px rgba(78, 115, 223, 0.25);
    }

    /* Memastikan Icon di dalam tombol aktif juga Putih */
    .btn-filter-grid.active i {
        color: #ffffff !important;
    }

    /* Efek Hover untuk tombol yang tidak aktif */
    .btn-filter-grid:not(.active):hover {
        background-color: #f8f9fc;
        border-color: #b7b9cc;
        color: #2e59d9;
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
                <div class="d-none d-md-flex align-items-center flex-wrap" style="gap: 5px;">
                    
                    {{-- 1. Tombol 'ME' --}}
                    <button class="btn btn-primary active btn-xs rounded-pill btn-filter-grid" 
                            onclick="filterGrid('{{ Auth::id() }}', this)"
                            style="font-size: 10.5px; height: 26px; padding: 0 12px; font-weight: 700;">
                        <i class="fas fa-user mr-1" style="font-size: 9px;"></i> My Schedule
                    </button>

                    {{-- 2. Tombol Bawahan --}}
                    @foreach($subordinateTasks->unique('user_id') as $sub)
                        @if($sub->user_id !== Auth::id())
                            <button class="btn btn-outline-secondary btn-xs rounded-pill btn-filter-grid" 
                                    onclick="filterGrid('{{ $sub->user_id }}', this)"
                                    style="font-size: 10.5px; height: 26px; padding: 0 12px; border-color: #d1d3e2; color: #5a5c69; font-weight: 600;">
                                <i class="fas fa-circle mr-1" style="font-size: 6px; color: #ffc107;"></i>
                                {{ $sub->email }}
                            </button>
                        @endif
                    @endforeach
                </div>

                {{-- Pembatas vertikal --}}
                <div class="d-none d-md-block border-left mx-2" style="height: 18px; opacity: 0.4; border-left: 1px solid #858796 !important;"></div>
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
                        $isOverdue = ($task->status == 7);
                        $isWaitingApprove = ($task->status == 3);
                        $isWaitingOverdue = ($task->status == 9); // Status Baru

                        // Penentuan Border
                        if ($isOverdue) {
                            $borderClass = 'border-danger';
                        } elseif ($isWaitingOverdue) {
                            $borderClass = 'border-warning';
                        } else {
                            $borderClass = 'border-left-' . ($priority_colors[$task->priority] ?? 'secondary');
                        }

                        // Penentuan Background
                        if ($isOverdue) {
                            $bgClass = 'bg-light-danger';
                        } elseif ($isWaitingOverdue) {
                            $bgClass = 'bg-light-warning'; // Anda bisa definisikan di CSS atau gunakan #fff4e5
                        } else {
                            $bgClass = 'bg-white';
                        }

                        // Logika Drag & Drop
                        $dragClass = ($isOverdue || $isWaitingApprove || $isWaitingOverdue) ? 'no-drag' : 'task-card-source';
                        
                        // Inline Style khusus untuk border tebal di kiri
                        $customBorderStyle = "";
                        if ($isOverdue) {
                            $customBorderStyle = "border-left: 5px solid #e74a3b !important;";
                        } elseif ($isWaitingOverdue) {
                            $customBorderStyle = "border-left: 5px solid #f6c23e !important;";
                        }
                    @endphp

                    <div class="{{ $dragClass }} card shadow-sm {{ $borderClass }} {{ $bgClass }}"
                        data-id="{{ $task->id }}" 
                        data-menit="{{ $task->menit }}" 
                        data-status="{{ $task->status }}" 
                        style="width: 240px; cursor: {{ ($isOverdue || $isWaitingOverdue) ? 'default' : 'grab' }}; border-radius: 8px; {{ $customBorderStyle }}">
                        
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="task-title {{ ($isOverdue || $isWaitingOverdue) ? 'font-weight-bold' : 'text-dark' }} {{ $isOverdue ? 'text-danger' : ($isWaitingOverdue ? 'text-warning' : '') }} text-truncate" style="max-width: 160px;">
                                    {{ $task->id_manajement_konten ? "Edit Konten" : "" }} {{ $task->nama_task }} ({{ Str::upper($task->nama) }})
                                </span>
                                <span class="badge {{ $isOverdue ? 'badge-danger' : ($isWaitingOverdue ? 'badge-warning' : 'badge-primary') }}">
                                    {{ $task->menit }}m
                                </span>
                            </div>

                            <div class="mb-1">
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    <i class="fas fa-user mr-1"></i>
                                    {{ $task->user->email ?? '-' }}
                                </small>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mt-1">
                                {{-- Label Status Khusus --}}
                                @if($isOverdue)
                                    <small class="text-danger font-italic" style="font-size: 0.75rem;">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Overdue
                                    </small>
                                @elseif($isWaitingOverdue)
                                    <small class="text-warning font-italic" style="font-size: 0.75rem;">
                                        <i class="fas fa-clock mr-1"></i> Waiting (Overdue)
                                    </small>
                                @endif

                                {{-- Tombol Kirim untuk Overdue/Waiting Overdue --}}
                                @if($isOverdue)
                                    <button type="button" 
                                        onclick="changeStatus({{ $task->id }}, 'stop', 'cc')" 
                                        class="btn btn-xs btn-primary py-0 px-2" 
                                        style="font-size: 0.7rem; border-radius: 4px;">
                                        <i class="fas fa-paper-plane mr-1"></i> Kirim
                                    </button>
                                @endif
                            </div>

                            {{-- Tombol Approval (Muncul jika status 3 ATAU 9 jika 9 butuh tindakan atasan) --}}
                            @if($isWaitingApprove || $isWaitingOverdue)
                                <div class="d-flex justify-content-end mt-2" style="gap: 5px;">
                                    {{-- APPROVE --}}
                                    <button type="button"
                                        onclick="changeStatus({{ $task->id }}, 'approve', 'cc', null, this)"
                                        class="btn btn-xs btn-success py-0 px-2"
                                        style="font-size: 0.7rem; border-radius: 4px;">
                                        <i class="fas fa-check"></i>
                                    </button>

                                    {{-- REVISI --}}
                                    <button type="button"
                                        onclick="changeStatus({{ $task->id }}, 'revisi', 'cc', null, this)"
                                        class="btn btn-xs btn-danger py-0 px-2"
                                        style="font-size: 0.7rem; border-radius: 4px;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- MAIN GRID SECTION --}}
    <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0" style="overflow-x: auto;"> {{-- Scrollbar horizontal jika layar kecil --}}
            <div class="row no-gutters" id="zoom-container">
                
                {{-- Sidebar Jam --}}
                <div class="col-auto hour-sidebar border-right bg-light">
                    @for($i = 8; $i <= 16; $i++)
                        <div class="hour-major font-weight-bold px-2">{{ sprintf('%02d:00', $i) }}</div>
                        <div class="hour-minor text-muted px-2">{{ sprintf('%02d:10', $i) }}</div>
                        <div class="hour-minor text-muted px-2">{{ sprintf('%02d:20', $i) }}</div>
                        <div class="hour-minor text-muted px-2">{{ sprintf('%02d:30', $i) }}</div>
                        <div class="hour-minor text-muted px-2">{{ sprintf('%02d:40', $i) }}</div>
                        <div class="hour-minor text-muted px-2">{{ sprintf('%02d:50', $i) }}</div>
                    @endfor
                    <div class="hour-major font-weight-bold px-2">17:00</div>
                </div>

                {{-- Area Drop Grid --}}
                <div class="col position-relative" style="overflow: hidden;"> {{-- Cegah garis keluar jalur --}}
                    <div id="daily-grid">
                        {{-- Garis Grid per Jam (Utama) --}}
                        @for($i = 0; $i <= 9; $i++)
                            <div class="grid-line" style="top: calc({{ $i }} * var(--hour-height));"></div>
                        @endfor

                        {{-- Garis Grid per 10 Menit (Dashed) --}}
                        @for($i = 0; $i <= 54; $i++)
                            @if($i % 6 != 0)
                                <div class="grid-line" style="position: absolute; width: 100%; border-top: 1px dashed #ebedf3; opacity: 0.5; top: calc({{ $i }} * var(--grid-minor-height)); z-index: 1;"></div>
                            @endif
                        @endfor

                        {{-- Task Cards (Logika PHP Anda sudah benar) --}}
                        @foreach($scheduled_tasks as $task)
                            @php
                            $gridStart = \Carbon\Carbon::parse($task->tanggal_mulai)->setTime(8, 0, 0);
                            $taskStart = \Carbon\Carbon::parse($task->tanggal_mulai . ' ' . $task->jam_mulai);
                            
                            // Hitung rasio (bukan pixel statis)
                            $diffMinutesFromStart = $gridStart->diffInMinutes($taskStart, false);
                            $topRatio = $diffMinutesFromStart / 60;
                            $heightRatio = $task->menit / 60;

                            $taskEnd = $taskStart->copy()->addMinutes($task->menit);
                            $email = $task->vic_email ?? '';
                            $isMyTask = ($task->pic == Auth::id());
                            $isRunning = ($task->status == 1);
                            $isReview = ($task->status == 3);
                            $rentangWaktu = $taskStart->format('H.i') . ' - ' . $taskEnd->format('H.i');
                            $cardTypeClass = $isMyTask ? 'my-task' : 'subordinate-task d-none';
                            @endphp

                            <div class="task-card-scheduled card {{ $cardTypeClass }} {{ $isRunning ? 'bg-primary text-white' : '' }} {{ $task->status == 5 ? 'bg-light' : '' }} {{ $task->status == 7 ? 'bg-danger text-white' : '' }} {{ $task->status == 8 ? 'bg-warning text-white' : '' }} border-left-{{ $priority_colors[$task->priority] ?? 'secondary' }}"
                            data-id="{{ $task->id }}" 
                            data-email="{{ $email }}"
                            data-status="{{ $task->status }}" 
                            data-pic="{{ $task->pic }}" 
                            data-menit="{{ $task->menit }}"
                            style="top: calc({{ $topRatio }} * var(--hour-height)); 
                                height: calc({{ $heightRatio }} * var(--hour-height)); 
                                min-height: 30px; 
                                position: absolute; width: 100%; z-index: 5; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">

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
                                                                <button onclick="changeStatus({{ $task->id }}, 'approve', 'cc', null, this)" class="btn btn-xs btn-success mr-1 shadow-sm" style="font-size: 9px; padding: 1px 4px;"><i class="fas fa-check"></i></button>
                                                                <button onclick="changeStatus({{ $task->id }}, 'revisi', 'cc', null, this)" class="btn btn-xs btn-danger shadow-sm" style="font-size: 9px; padding: 1px 4px;"><i class="fas fa-times"></i></button>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="d-flex">
                                                            @if($task->status == 1)
                                                                <button onclick="splitTask({{ $task->id }})" class="btn btn-xs btn-outline-light mr-1" style="font-size: 9px; padding: 1px 4px;">Split</button>
                                                                <button onclick="changeStatus({{ $task->id }}, 'stop', 'cc', null, this)" class="btn btn-xs btn-success font-weight-bold shadow-sm" style="font-size: 9px; padding: 1px 6px;">Kirim</button>
                                                            @elseif(in_array($task->status, [0, 2, 7]))
                                                                <button onclick="returnToPool({{ $task->id }})" class="btn btn-xs btn-light border mr-1" style="padding: 1px 3px;"><i class="fas fa-undo text-danger" style="font-size: 9px;"></i></button>
                                                                <button onclick="changeStatus({{ $task->id }}, 'play', 'cc', null, this)" class="btn btn-xs btn-success shadow-sm font-weight-bold" style="font-size: 9px; padding: 1px 8px;">START</button>
                                                                <button onclick="EditData({{ $task->id }}" class="btn btn-xs btn-warning shadow-sm font-weight-bold" style="font-size: 9px; padding: 1px 8px;">Edit</button>
                                                            @elseif($task->status == 8)
                                                                <button onclick="changeStatus({{ $task->id }}, 'stop', 'cc', null, this)" class="btn btn-xs btn-success font-weight-bold shadow-sm" style="font-size: 9px; padding: 1px 6px;">Kirim</button>
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
                                <select name="pic" id="pic_select" class="form-control custom-select shadow-sm border-0" onchange="handleChangePIC(this)" required>
                                    <option value="" selected disabled>-- Pilih PIC --</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark small text-uppercase">KPI</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0">
                                        <i class="fas fa-user-tie text-muted"></i>
                                    </span>
                                </div>
                                <select name="kpi_id" id="selectKPI" class="form-control border-left-0 pl-0">
                                    <option value="" selected disabled>-- Pilih KPI --</option>
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
                                    title="Harap masukkan format URL yang valid (dimulai dengan http:// atau https://)">
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

    <div class="modal fade" id="editModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-white" style="background: linear-gradient(45deg, #f6c23e, #f4b619);">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-edit mr-2"></i>Edit Task
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4" style="background-color: #f8f9fc;">
                        
                        <h6 class="text-warning font-weight-bold uppercase small mb-3"><i class="fas fa-info-circle mr-1"></i> Informasi Utama</h6>
                        
                        <input type="hidden" name="id" id="edit_id">

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark small text-uppercase">Nama Task</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0">
                                        <i class="fas fa-tasks text-muted"></i>
                                    </span>
                                </div>
                                <input type="text" name="nama_task" id="edit_nama_task" class="form-control border-left-0 pl-0" required>
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
                                <select name="client_id" id="edit_selectClient" class="form-control border-left-0 pl-0" onchange="handleClientChange(this)" required>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label class="font-weight-bold text-dark small">DIVISI</label>
                                <select name="divisi" id="edit_divisi" class="form-control custom-select shadow-sm border-0" required>
                                    @foreach($result as $divId => $nama)
                                        <option value="{{ $divId }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label class="font-weight-bold text-dark small">PIC (PERSON IN CHARGE)</label>
                                <select name="pic" id="edit_pic_select" class="form-control custom-select shadow-sm border-0" onchange="handleChangePIC(this)" required>
                                    </select>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark small text-uppercase">KPI</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0">
                                        <i class="fas fa-user-tie text-muted"></i>
                                    </span>
                                </div>
                                <select name="kpi_id" id="edit_selectKPI" class="form-control border-left-0 pl-0">
                                    <option value="" selected disabled>-- Pilih KPI --</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="text-warning font-weight-bold uppercase small mb-3"><i class="fas fa-calendar-alt mr-1"></i> Penjadwalan & Waktu</h6>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-dark small">TANGGAL MULAI</label>
                                <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai" class="form-control shadow-sm border-0" required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-dark small">TANGGAL DEADLINE</label>
                                <input type="date" name="tanggal_deadline" id="edit_tanggal_deadline" class="form-control shadow-sm border-0" required>
                            </div>
                        </div>

                        <div id="task-list-container-edit" class="mb-4 animate__animated animate__fadeIn" style="display: none;">
                            <div class="card border-0 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                <div class="card-header bg-info text-white py-2 d-flex justify-content-between align-items-center">
                                    <span class="small font-weight-bold"><i class="fas fa-user-clock mr-1"></i> Jadwal PIC di Tanggal Ini</span>
                                    <span id="total-durasi-container-edit" class="badge badge-light text-info shadow-sm">
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
                                        <tbody id="task-list-body-edit"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-dark small">JAM MULAI</label>
                                <input type="time" name="jam_mulai" id="edit_jam_mulai" class="form-control shadow-sm border-0" required>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-dark small">JAM SELESAI</label>
                                <input type="time" name="jam_selesai" id="edit_jam_selesai" class="form-control shadow-sm border-0" required>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-dark small">ESTIMASI</label>
                                <div class="input-group shadow-sm">
                                    <input type="number" name="menit" id="edit_menit" class="form-control bg-white font-weight-bold border-0" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-white border-0 small">Min</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark small">PRIORITAS</label>
                            <select name="priority" id="edit_priority" class="form-control custom-select shadow-sm border-0" required>
                                @foreach($prioritas as $pId => $pName)
                                    <option value="{{ $pId }}">{{ $pName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark small text-uppercase">Link ke Jira</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0">
                                        <i class="fas fa-link text-muted"></i>
                                    </span>
                                </div>
                                <input type="url" name="jira_input" id="edit_jira_input" class="form-control border-left-0 pl-0" pattern="https?://.*">
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark small">CATATAN</label>
                            <textarea name="note" id="edit_note" class="form-control shadow-sm border-0" rows="3" style="border-radius: 8px;"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer bg-white border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light font-weight-bold px-4 text-muted" data-dismiss="modal">BATAL</button>
                        <button type="submit" class="btn btn-warning shadow-lg px-5 font-weight-bold text-white" style="border-radius: 8px; letter-spacing: 1px;">
                            <i class="fas fa-sync-alt mr-2"></i>UPDATE TASK
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const zoomContainer = document.getElementById('zoom-container');
        let currentHourHeight = 300; // Tinggi awal jam (300px)
        const minHeight = 150;  // Zoom out minimal
        const maxHeight = 1500; // Zoom in maksimal (untuk melihat detail menit)

        let ticking = false;

        window.addEventListener('wheel', function(e) {
            if (e.shiftKey) {
                e.preventDefault();

                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        if (e.deltaY < 0) {
                            currentHourHeight += 60;
                        } else {
                            currentHourHeight -= 60;
                        }

                        currentHourHeight = Math.min(Math.max(currentHourHeight, minHeight), maxHeight);
                        zoomContainer.style.setProperty('--hour-height', currentHourHeight + 'px');
                        
                        ticking = false;
                    });
                    ticking = true;
                }
            }
        }, { passive: false });

        const grid = document.getElementById('daily-grid');
        const pool = document.getElementById('unscheduled-pool');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        const getRole = @json($myRole);
        const myRole = getRole['level'];
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
            console.log(task);
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
            const getRole = @json($myRole);
            const myRole = getRole['role_name'];
            
            const jamMulai = task.jam_mulai || "09:00";
            const durasiMenit = parseInt(task.menit) || 0;
            
            // 1. Perhitungan Posisi Top & Height
            const [h, m] = jamMulai.split(':').map(Number);
            const validH = isNaN(h) ? 9 : h;
            const validM = isNaN(m) ? 0 : m;

            const topRatio = (validH - startHour) + (validM / 60);
            const heightRatio = durasiMenit / 60;

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
                    style="top: calc(${topRatio} * var(--hour-height)); 
                        height: calc(${heightRatio} * var(--hour-height)); 
                        position: absolute; 
                        width: 100%; 
                        border-radius: 8px; 
                        z-index: ${isRunning ? 10 : 1}; 
                        ${!isMyTask ? 'cursor: default;' : 'cursor: grab;'}">

                    <div class="card-body p-2 d-flex flex-column h-100">
                        <div class="d-flex justify-content-between align-items-start w-100">
                            <div style="flex: 1; min-width: 0; padding-right: 10px;">
                                ${!isMyTask ? `<span class="badge badge-dark mb-1" style="font-size: 8px; opacity: 0.9;"><i class="fas fa-user mr-1"></i>${emailPrefix}</span><br>` : ''}
                                
                                ${isWaitingApproval ? `<span class="badge badge-light text-info mb-1" style="font-size: 7px; text-transform: uppercase;">Menunggu Approval</span><br>` : ''}
                                
                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <strong class="task-title text-truncate" style="font-size: 11px; line-height: 1.2; max-width: 70%;">
                                    ${teks} ${task.nama_task} (${(task.project_name || 'PROJECT').toUpperCase()}) (${task.nama_kpi}) ${statusLabel} 
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
                        <div class="mt-2 d-flex pt-2"
                            style="
                                font-size:10px;
                                line-height:1.3;
                                color:#000;
                                border-top:1px solid #000;
                            ">

                            <!-- NOTE TASK -->
                            <div class="w-50 pr-2"
                                style="
                                    overflow:hidden;
                                    border-right:1px solid #000;
                                ">
                                <div style="font-size:9px; font-weight:700; color:#000; text-transform:uppercase;">
                                    Note Task
                                </div>

                                <div
                                    title="${task.note || '-'}"
                                    style="
                                        font-size:11px;
                                        font-weight:500;
                                        color:#000;
                                        white-space:nowrap;
                                        overflow:hidden;
                                        text-overflow:ellipsis;
                                    ">
                                    ${task.note || '-'}
                                </div>
                            </div>

                            <!-- CATATAN KPI -->
                            <div class="w-50 pl-2" style="overflow:hidden;">
                                <div style="font-size:9px; font-weight:700; color:#000; text-transform:uppercase;">
                                    Catatan KPI
                                </div>

                                <div
                                    title="${task.catatan_kpi || '-'}"
                                    style="
                                        font-size:11px;
                                        font-weight:500;
                                        color:#000;
                                        white-space:nowrap;
                                        overflow:hidden;
                                        text-overflow:ellipsis;
                                    ">
                                    ${task.catatan_kpi || '-'}
                                </div>
                            </div>

                        </div>
                    </div>
                    ${isMyTask ? '<div class="resize-handle" style="position: absolute; bottom: 0; left: 0; right: 0; height: 5px; cursor: ns-resize;"></div>' : ''}
                </div>
            `;
        }

        function renderButtons(task, isMyTask) {
            const status = parseInt(task.status);
            const approve = task.tanggal_approve;
            const btnClass = "btn btn-xs shadow-sm font-weight-bold";

            if (isMyTask) {
                if (status === 1) {
                    return `<div class="d-flex">
                        <button onclick="splitTask(${task.id})" class="btn btn-xs btn-outline-light mr-1" style="font-size: 9px; padding: 1px 4px;">Split</button>
                        <button onclick="changeStatus(${task.id}, 'stop', 'notCC', ${task.pic}, this)" class="${btnClass} btn-success" style="font-size: 9px; padding: 1px 6px;">Kirim</button>
                    </div>`;
                } else if(status === 8 || status === 7){
                    return `<div class="d-flex">
                        <button onclick="changeStatus(${task.id}, 'stop', 'notCC', ${task.pic}, this)" class="${btnClass} btn-success" style="font-size: 9px; padding: 1px 6px;">Kirim</button>
                    </div>`;
                } else if ([0, 2].includes(status)) {
                    return `<div class="d-flex">
                        <button onclick="returnToPool(${task.id})" class="btn btn-xs btn-light border mr-1" style="padding: 1px 4px;"><i class="fas fa-undo text-danger" style="font-size: 9px;"></i></button>
                        <button onclick="changeStatus(${task.id}, 'play', 'notCC', ${task.pic}, this)" class="${btnClass} btn-success" style="font-size: 9px; padding: 1px 8px;">START</button>
                        <button onclick="EditData(${task.id})" class="${btnClass} btn-warning" style="font-size: 9px; padding: 1px 8px;">Edit</button>
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
                    if (myRole === 1 && task.level === 2) canApprove = true;
                    else if (myRole === 2 && task.level === 3) canApprove = true;
                    else if (myRole === 3 && task.vic_role === 3) canApprove = true;
                    else if (myRole === 1) canApprove = true;
                }
                
                if (getRole.role_name === 'Super Admin') canApprove = true;

                if (canApprove) {
                    return `<div class="d-flex">
                        <button onclick="changeStatus(${task.id}, 'approve', 'notCC', ${task.pic}, this)" class="btn btn-xs btn-success mr-1 shadow-sm" style="font-size: 9px; padding: 1px 5px;"><i class="fas fa-check"></i></button>
                        <button onclick="changeStatus(${task.id}, 'revisi', 'notCC', ${task.pic}, this)" class="btn btn-xs btn-danger shadow-sm" style="font-size: 9px; padding: 1px 5px;"><i class="fas fa-times"></i></button>
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
        axios.post("{{ str_replace('http://', 'https://', route('hc.updateStatus')) }}", { id: id, action: 'set_overdue', status: 7 }).then(() => location.reload());
    }
    function autoUpdateToEndsTime(id) {
        axios.post("{{ str_replace('http://', 'https://', route('hc.updateStatus')) }}", { id: id, action: 'habis', status: 8 }).then(() => location.reload());
    }
    function returnToPool(id) {
        if(confirm('Kembalikan ke Pool?')) axios.post("{{ route('hc.updateSchedule') }}", { id: id, reset: true }).then(() => location.reload());
    }
    function splitTask(id) {
        if(confirm('Split task ini? Sisa waktu akan dikembalikan ke Pool.')) axios.post("{{ route('hc.cancelRemaining') }}", { id: id }).then(() => location.reload());
    }
    function changeStatus(id, action, cc, userId, btn) {
        const myId = "{{ Auth::id() }}";
        let jamMulaiStr = null;
        const oldCard = document.querySelector(`[data-id="${id}"]`);

        if (action === 'play' && oldCard) {
            const timeRange = oldCard.querySelector('.task-time-range');
            if (timeRange) {
                jamMulaiStr = timeRange.innerText.split(' - ')[0].trim();
            }
        }

        // 1. Update Status melalui POST
        axios.post("{{ str_replace('http://', 'https://', route('hc.updateStatus')) }}", { 
            id: id, 
            action: action, 
            jam_mulai: jamMulaiStr 
        })
        .then(function(updateRes) {
            if (cc == "cc") {
                location.reload();
            } else {
                filterGrid(userId, btn);
            }
        })
        .catch(function(err) {
            console.error("Error pada proses update/render:", err);
            alert("Terjadi kesalahan saat memperbarui data.");
        });
    }

    function EditData(id){
        fetch(`api/hc-daily/get-data/${id}`).then(response => response.json())
        .then(data => {
            console.log(data);
            document.getElementById('edit_id').value = data.task.id;
            document.getElementById('edit_nama_task').value = data.task.nama_task;
            document.getElementById('edit_tanggal_mulai').value = data.task.tanggal_mulai;
            document.getElementById('edit_tanggal_deadline').value = data.task.tanggal_deadline;
            document.getElementById('edit_jam_mulai').value = data.task.jam_mulai;
            document.getElementById('edit_jam_selesai').value = tambahMenitKeJam(data.task.jam_mulai, data.task.menit);
            document.getElementById('edit_menit').value = data.task.menit;
            document.getElementById('edit_priority').value = data.task.priority;
            
            
            document.getElementById('editForm').action = `/hc_overview/${data.project}/${id}`;

            const clientSelect = document.getElementById('edit_selectClient');
            clientSelect.innerHTML = '<option value="" disabled>-- Pilih Client --</option>';

            data.client.forEach(function(item) {
                let option = document.createElement('option');
                option.value = item.id;
                
                option.text = item.nama; // pastikan 'nama' atau 'name' sesuai API
                
                if (item.id == data.task.project) {
                    option.selected = true;
                }

                clientSelect.appendChild(option);
            });

            
            handleClientChange(clientSelect, data.task.pic, data.task.id_kpi);

            $('#editModal').modal('show');
        })
        .catch(error => console.error('Error:', error));
    }

    function tambahMenitKeJam(jamString, menitTambah) {
        // 1. Pecah jam dan menit (asumsi format "HH:mm" atau "HH:mm:ss")
        let [jam, menit] = jamString.split(':').map(Number);

        // 2. Buat objek Date (tanggal bebas, kita hanya butuh jamnya)
        let date = new Date();
        date.setHours(jam);
        date.setMinutes(menit + parseInt(menitTambah));

        // 3. Format kembali ke HH:mm
        let jamHasil = String(date.getHours()).padStart(2, '0');
        let menitHasil = String(date.getMinutes()).padStart(2, '0');

        return `${jamHasil}:${menitHasil}`;
    }

    const baseUrl = window.location.origin;
    // Tambah
    const inputPic = document.getElementById('pic_select');
    const inputTanggal = document.getElementById('tanggal_mulai');
    const inputMulai = document.getElementById('jam_mulai');
    const inputSelesai = document.getElementById('jam_selesai');
    const inputEstimasi = document.getElementById('menit');
    
    // Edit
    const inputMulaiEdit = document.getElementById('edit_jam_mulai');
    const inputSelesaiEdit = document.getElementById('edit_jam_selesai');
    const inputEstimasiEdit = document.getElementById('edit_menit');
    const inputPicEdit = document.getElementById('edit_pic_select');
    const inputTanggalEdit = document.getElementById('edit_tanggal_mulai');

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

    function fetchTaskByPicEdit() {
        const tanggal = inputTanggalEdit.value;
        const pic = inputPicEdit.value;
        const container = document.getElementById('task-list-container-edit');
        const tableBody = document.getElementById('task-list-body-edit');
        if (tanggal && pic) {
            tableBody.innerHTML = '<tr><td colspan="3" class="text-center p-2 text-muted small italic">Mengecek jadwal...</td></tr>';
            container.style.display = 'block';

            fetch(`${baseUrl}/api/get-tasks-by-date?tanggal=${tanggal}&pic=${pic}`)
                .then(response => response.json())
                .then(data => {
                    const totalContainer = document.getElementById('total-durasi-container-edit');
                    
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

    function calculateMinutesEdit() {
        const picId = inputPicEdit.value;
        const mulai = inputMulaiEdit.value;
        const selesai = inputSelesaiEdit.value;

        if (mulai) {
            const jamM = parseInt(mulai.split(':')[0]);
            let limitMulai = (picId == "39") ? 8 : 9;
            if (jamM < limitMulai) {
                alert(`PIC ini minimal mulai jam 0${limitMulai}:00`);
                inputMulaiEdit.value = ""; return;
            }
        }

        if (selesai) {
            const [jamS, menitS] = selesai.split(':').map(Number);
            if (jamS > 17 || (jamS === 17 && menitS > 0)) {
                alert("Jam selesai tidak boleh lebih dari jam 17:00");
                inputSelesaiEdit.value = ""; return;
            }
        }

        if (mulai && selesai) {
            const [h1, m1] = mulai.split(':').map(Number);
            const [h2, m2] = selesai.split(':').map(Number);
            const diff = ((h2 * 60) + m2) - ((h1 * 60) + m1);
            
            if (diff < 0) {
                alert("Jam selesai tidak boleh sebelum jam mulai!");
                inputSelesaiEdit.value = ""; 
                inputEstimasiEdit.value = "";
            } else {
                inputEstimasiEdit.value = diff;
            }
        }
    }

    // Tambah
    if(inputPic) inputPic.addEventListener('change', () => { fetchTaskByPic(); calculateMinutes(); });
    if(inputTanggal) inputTanggal.addEventListener('change', fetchTaskByPic);
    if(inputMulai) inputMulai.addEventListener('change', calculateMinutes);
    if(inputSelesai) inputSelesai.addEventListener('change', calculateMinutes);
    // Edit
    if(inputPicEdit) inputPicEdit.addEventListener('change', () => { fetchTaskByPicEdit(); calculateMinutesEdit(); });
    if(inputTanggalEdit) inputTanggalEdit.addEventListener('change', fetchTaskByPicEdit);
    if(inputMulaiEdit) inputMulaiEdit.addEventListener('change', calculateMinutesEdit);
    if(inputSelesaiEdit) inputSelesaiEdit.addEventListener('change', calculateMinutesEdit);

    async function handleChangePIC(el){
        let selectClient = document.getElementById('selectClient').value;
        const picId = el.value;
        let kpiSelect = document.getElementById('selectKPI');
        if(el.id == "edit_pic_select"){
            kpiSelect = document.getElementById('edit_selectKPI');
            selectClient = document.getElementById('edit_selectClient').value;
        }

        if (!picId) return;

        kpiSelect.innerHTML = '<option value="" selected disabled>Sedang memuat...</option>';

        try {
            const response = await fetch(`/api/kpi-data-daily/${selectClient}/${picId}`);

            if (!response.ok) throw new Error('Gagal mengambil data PIC');

            const data = await response.json();

            kpiSelect.innerHTML = '<option value="" selected>-- Pilih KPI --</option>';
            
            if (data.data.length > 0) {
                data.data.forEach(pic => {
                    console.log(pic);
                    let option = document.createElement('option');
                    option.value = pic.id; // Sesuaikan dengan key ID dari API
                    option.text = pic.nama_kpi; // Menggunakan email sesuai request script kamu
                    kpiSelect.appendChild(option);
                });
            } else {
                kpiSelect.innerHTML = '<option value="" selected disabled>Tidak ada KPI ditemukan</option>';
            }
        } catch (error) {
            console.error('Error:', error);
            picSelect.innerHTML = '<option value="" selected disabled>Gagal memuat data</option>';
            alert('Terjadi kesalahan saat mengambil data PIC.');
        }
    }
    
    async function handleClientChange(el, id = null, id_task = null) {
        const userId = {{ Auth::user()->id }}
        const clientId = el.value;
        let picSelect = document.getElementById('pic_select');
        if(el.id == "edit_selectClient"){
            picSelect = document.getElementById('edit_pic_select');
        }

        if (!clientId) return;

        // 1. Reset dropdown PIC dan tunjukkan status loading
        picSelect.innerHTML = '<option value="" selected disabled>Sedang memuat...</option>';

        try {
            // 2. Panggil API (Sesuaikan URL API kamu)
            // Contoh: /api/get-pic-by-client/5
            // Ambil PIC
            const response = await fetch(`/api/client-pics/${clientId}/${userId}`);
            
            if (!response.ok) throw new Error('Gagal mengambil data PIC');

            const data = await response.json();

            // 3. Bersihkan dropdown PIC kembali
            picSelect.innerHTML = '<option value="" selected disabled>-- Pilih PIC --</option>';

            // 4. Masukkan data dari API ke dalam Select
            if (data.length > 0) {
                data.forEach(pic => {
                    let option = document.createElement('option');
                    option.value = pic.id; // Sesuaikan dengan key ID dari API
                    option.text = pic.email; // Menggunakan email sesuai request script kamu
                    if(el.id == "edit_selectClient"){
                        option.selected = (pic.id == id) ? true : false;
                    }
                    picSelect.appendChild(option);
                });
            } else {
                picSelect.innerHTML = '<option value="" selected disabled>Tidak ada PIC ditemukan</option>';
            }
            if(el.id == "edit_selectClient"){
                handleChangePIC(picSelect);
            }

        } catch (error) {
            console.error('Error:', error);
            picSelect.innerHTML = '<option value="" selected disabled>Gagal memuat data</option>';
            alert('Terjadi kesalahan saat mengambil data PIC.');
        }
    }
</script>

@include('partials.footer')