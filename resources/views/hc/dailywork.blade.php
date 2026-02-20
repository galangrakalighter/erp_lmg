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
        height: 2400px; 
        background-color: #fff; 
        position: relative;
        border: 1px solid var(--border-color);
        /* Garis pandu visual setiap 50px (10 menit) */
        background-image: linear-gradient(var(--border-color) 1px, transparent 1px);
        background-size: 100% var(--grid-minor-height); 
    }

    .grid-line {
        position: absolute; width: 100%; border-top: 1px solid var(--border-color);
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
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 text-gray-800 font-weight-bold mb-0">Daily Work Schedule</h1>
            <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i> Shift: 09:00 - 17:00</small>
        </div>
        
        @if(Auth::user()->role != "content_creator")
            <div class="btn-group shadow-sm">
                <button id="btn-self" class="btn btn-sm btn-primary active" onclick="switchMode('self')">
                    <i class="fas fa-user mr-1"></i> My Schedule
                </button>
                <button id="btn-subordinate" class="btn btn-sm btn-white border" onclick="switchMode('subordinate')">
                    <i class="fas fa-users mr-1"></i> Review Subordinate
                </button>
            </div>
        @endif
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
                    @endphp

                    <div class="task-card-source card shadow-sm {{ $borderClass }} {{ $bgClass }}"
                        data-id="{{ $task->id }}" 
                        data-menit="{{ $task->menit }}" 
                        data-status="{{ $task->status }}" 
                        style="width: 240px; cursor: grab; border-radius: 8px; {{ $isOverdue ? 'border-left: 5px solid #e74a3b !important;' : '' }}">
                        
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="task-title {{ $isOverdue ? 'text-danger font-weight-bold' : 'text-dark' }} text-truncate" style="max-width: 160px;">
                                    {{ $task->id_manajement_konten ? "Edit Konten" : "" }} {{ $task->nama_task }} ({{ Str::upper($task->nama) }})
                                </span>
                                <span class="badge {{ $isOverdue ? 'badge-danger' : 'badge-primary' }}">
                                    {{ $task->menit }}m
                                </span>
                            </div>

                            {{-- Keterangan Tambahan jika Overdue --}}
                            @if($isOverdue)
                                <div class="d-flex align-items-center mt-1">
                                    <small class="text-danger font-italic" style="font-size: 0.75rem;">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Overdue
                                    </small>
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
        <div class="card-body p-0">
            <div class="row no-gutters">
                {{-- Sidebar Jam --}}
                <div class="col-auto hour-sidebar border-right">
                    @for($i = 9; $i <= 16; $i++)
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
                                $startGrid = \Carbon\Carbon::createFromTime(9, 0, 0);
                                $taskStart = \Carbon\Carbon::parse($task->jam_mulai);
                                $taskEnd = $taskStart->copy()->addMinutes($task->menit);
                                
                                // PERBAIKAN: Gunakan 300 (skala baru), bukan 80
                                $topPx = ($startGrid->diffInMinutes($taskStart, false) / 60) * 300;
                                $heightPx = ($task->menit / 60) * 300;
                                
                                $isMyTask = ($task->pic == Auth::id());
                                $isRunning = ($task->status == 1);
                                $isReview = ($task->status == 3);
                                $cardType = '';
                                if ($isMyTask) {
                                    $cardType = 'type-self';
                                } elseif (!$isMyTask && $isReview) {
                                    $cardType = 'type-subordinate';
                                } else {
                                    $cardType = 'type-other'; // Task orang lain yang bukan status 3 (biasanya disembunyikan)
                                }
                            @endphp

                            <div class="task-card-scheduled card {{ $cardType }} {{ (!$isMyTask) ? 'subordinate-hidden' : '' }} {{ $isRunning ? 'bg-primary text-white' : '' }} {{ $task->status == 5 ? 'bg-light' : '' }} {{ $task->status == 7 ? 'bg-danger text-white' : '' }} {{ $task->status == 8 ? 'bg-warning text-white' : '' }} border-left-{{ $priority_colors[$task->priority] ?? 'secondary' }}"
                                data-id="{{ $task->id }}" 
                                data-type="{{ $cardType }}"
                                data-status="{{ $task->status }}" 
                                data-pic="{{ $task->pic }}" 
                                data-menit="{{ $task->menit }}" 
                                style="top: {{ $topPx }}px; height: {{ $heightPx }}px; min-height: 40px; border-radius: 8px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">

                                <div class="card-body p-2 position-relative h-100 d-flex flex-column">
                                    <div class="d-flex justify-content-between w-100 mb-2">
                                        <div class="text-truncate" style="max-width: 60%;">
                                            @if(!$isMyTask && $isReview)
                                                <span class="badge badge-pill badge-subordinate-review mb-1" style="font-size: 10px; letter-spacing: 0.5px;">
                                                    <i class="fas fa-user-check mr-1"></i> REVIEW: {{ $task->vic_email }}
                                                </span><br>
                                            @endif
                                            <strong class="task-title {{ ($isRunning || $task->status == 7) ? 'text-white' : 'text-dark' }}" style="font-size: 14px; font-weight: 700; line-height: 1.2;">
                                                {{ $task->id_manajement_konten ? "Edit Konten" : "" }} {{ $task->nama_task }}
                                                <span style="font-weight: 400; opacity: 0.8;">({{ Str::upper($task->nama) }})</span>
                                            </strong>
                                        </div>
                                        
                                        <div class="text-right" style="min-width: 40%;">
                                            <div class="mb-1">
                                                <span class="task-time-range {{ ($isRunning || $task->status == 7) ? 'text-white' : 'text-primary' }}" 
                                                    style="font-size: 14px; font-weight: 800; cursor: pointer; background: rgba(0,0,0,0.05); padding: 2px 6px; border-radius: 4px;"
                                                    @if(!in_array($task->status, [1, 3, 4, 7])) onclick="enableTimeEdit(this)" @endif>
                                                    {{ $taskStart->format('H:i') }} - {{ $taskEnd->format('H:i') }}
                                                </span>
                                                <input type="time" class="time-edit-input" style="display:none; width: 100%; font-size: 12px; border: 1px solid #ddd; border-radius: 4px;" onblur="saveManualTime(this)" onkeydown="if(event.key==='Enter') saveManualTime(this)">
                                            </div>
                                            
                                            <div class="task-date-info {{ ($isRunning || $task->status == 7) ? 'text-white' : '' }}" style="font-size: 11px; line-height: 1.2;">
                                                <div style="font-weight: 600; opacity: 0.9;">Tgl: {{ \Carbon\Carbon::parse($task->tanggal_mulai)->format('d/m/Y') }}</div>
                                                <div class="{{ ($isRunning || $task->status == 7) ? 'text-white' : 'text-danger' }}" style="font-weight: 800;">
                                                    DL: {{ \Carbon\Carbon::parse($task->tanggal_deadline)->format('d/m/Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($task->note)
                                        <div class="task-note mt-1 mb-2 p-2 {{ ($isRunning || $task->status == 7) ? 'bg-white-transparent text-white' : 'bg-light border' }}" 
                                            style="font-size: 13px; border-radius: 6px; border-left: 4px solid #007bff; position: relative; overflow: hidden;">
                                            <div style="font-weight: 800; text-transform: uppercase; font-size: 10px; margin-bottom: 2px; opacity: 0.7;">
                                                <i class="fas fa-pen-nib mr-1"></i> Note
                                            </div>
                                            <div style="line-height: 1.4; font-weight: 500;">
                                                {{ $task->note }}
                                            </div>
                                        </div>
                                    @endif

                                    @if($task->status != 5)
                                        <div class="action-container mt-auto pt-2 border-top d-flex align-items-center justify-content-between">
                                            @if(!$isMyTask && $isReview)
                                                <div class="btn-group btn-group-sm w-100">
                                                    <button onclick="changeStatus({{ $task->id }}, 'approve')" class="btn btn-success font-weight-bold" style="border-radius: 4px 0 0 4px;">Approve</button>
                                                    <button onclick="changeStatus({{ $task->id }}, 'revisi')" class="btn btn-danger font-weight-bold" style="border-radius: 0 4px 4px 0;">Reject</button>
                                                </div>
                                            @else
                                                @if($isMyTask)
                                                    <div class="d-flex gap-1">
                                                        @if($task->status == 1)
                                                            <button onclick="splitTask({{ $task->id }})" class="btn btn-xs btn-outline-secondary mr-1">Split</button>
                                                            <button onclick="changeStatus({{ $task->id }}, 'stop')" class="btn btn-xs btn-success font-weight-bold shadow-sm">Kirim Task</button>
                                                        @elseif(in_array($task->status, [0, 2]))
                                                            <button onclick="returnToPool({{ $task->id }})" class="btn btn-xs btn-light border mr-1"><i class="fas fa-undo text-danger"></i></button>
                                                            <button onclick="changeStatus({{ $task->id }}, 'play')" class="btn btn-xs btn-success px-3 shadow-sm font-weight-bold">START</button>
                                                        @elseif($task->status == 3)
                                                            <span class="badge badge-warning py-1 px-2" style="border-radius: 4px;"><i class="fas fa-clock mr-1"></i> Reviewing...</span>
                                                        @elseif($task->status == 4)
                                                            <span class="badge badge-success py-1 px-2" style="border-radius: 4px;"><i class="fas fa-check-circle mr-1"></i> Selesai</span>
                                                        @elseif($task->status == 7 || $task->status == 8)
                                                            <button onclick="changeStatus({{ $task->id }}, 'stop')" class="btn btn-xs btn-success font-weight-bold shadow-sm">Kirim Task</button>
                                                        @endif
                                                    </div>
                                                @else
                                                    @if($task->status == 3)
                                                        <div class="btn-group btn-group-sm w-100">
                                                            <button onclick="changeStatus({{ $task->id }}, 'approve')" class="btn btn-success">Approve</button>
                                                            <button onclick="changeStatus({{ $task->id }}, 'revisi')" class="btn btn-danger">Reject</button>
                                                        </div>
                                                    @else
                                                        <div class="d-flex align-items-center text-muted">
                                                            <i class="fas fa-user-circle mr-1" style="font-size: 14px;"></i>
                                                            <small style="font-size: 11px; font-weight: 600;">{{ explode('@', $task->vic_email)[0] }}</small>
                                                        </div>
                                                    @endif
                                                @endif
                                            @endif
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
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const grid = document.getElementById('daily-grid');
        const pool = document.getElementById('unscheduled-pool');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

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
            draggable: ".task-card-source, .task-card-scheduled[data-status='0'], .task-card-scheduled[data-status='2'], .task-card-scheduled[data-status='7']",
            filter: ".resize-handle, .action-container, button",
            onAdd: (evt) => handleTaskMovement(evt, true),
            onEnd: (evt) => { if (evt.to === grid && evt.from === grid) handleTaskMovement(evt, false); }
        });

        // --- CORE FUNCTIONS ---
        window.handleTaskMovement = function(evt, isNew) {
            const item = evt.item;
            const rect = grid.getBoundingClientRect();
            const clientY = (evt.originalEvent.clientY || (evt.originalEvent.touches ? evt.originalEvent.touches[0].clientY : 0));
            const taskId = item.getAttribute('data-id');
            const duration = parseInt(item.getAttribute('data-menit'));
            
            let heightPx = isNew ? (duration / 60) * 300 : item.offsetHeight;
            let proposedTop = Math.round((clientY - rect.top) / 50) * 50;

            if (proposedTop < 0) proposedTop = 0;
            if (proposedTop + heightPx > 2400) proposedTop = 2400 - heightPx;

            if (checkCollision(proposedTop, heightPx, taskId)) {
                alert("Slot waktu sudah terisi!");
                location.reload();
                return;
            }

            item.style.position = 'absolute';
            item.style.top = proposedTop + 'px';
            item.style.height = heightPx + 'px';
            item.style.width = '100%';

            updateTimeDisplay(item, proposedTop, duration);
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

        window.switchMode = function(mode) {
            const selfTasks = document.querySelectorAll('.task-card-scheduled[data-type="type-self"]');
            const subTasks = document.querySelectorAll('.task-card-scheduled[data-type="type-subordinate"]');
            const otherTasks = document.querySelectorAll('.task-card-scheduled[data-type="type-other"]');
            
            const btnSelf = document.getElementById('btn-self');
            const btnSub = document.getElementById('btn-subordinate');

            if (mode === 'subordinate') {
                // Tampilkan Bawahan, Sembunyikan Diri Sendiri
                selfTasks.forEach(t => t.classList.add('subordinate-hidden'));
                otherTasks.forEach(t => t.classList.add('subordinate-hidden'));
                subTasks.forEach(t => t.classList.remove('subordinate-hidden'));

                // Update Tombol
                btnSub.classList.replace('btn-white', 'btn-warning');
                btnSub.classList.add('active', 'text-white');
                btnSelf.classList.replace('btn-primary', 'btn-white');
                btnSelf.classList.remove('active');
            } else {
                // Tampilkan Diri Sendiri, Sembunyikan Bawahan
                subTasks.forEach(t => t.classList.add('subordinate-hidden'));
                otherTasks.forEach(t => t.classList.add('subordinate-hidden'));
                selfTasks.forEach(t => t.classList.remove('subordinate-hidden'));

                // Update Tombol
                btnSelf.classList.replace('btn-white', 'btn-primary');
                btnSelf.classList.add('active');
                btnSub.classList.replace('btn-warning', 'btn-white');
                btnSub.classList.remove('active', 'text-white');
            }
        };

        window.saveManualTime = function(input) {
            const card = input.closest('.task-card-scheduled');
            const displayRange = card.querySelector('.task-time-range');
            const oldTime = input.getAttribute('data-old-value');
            const newValue = input.value;

            if (newValue === oldTime) {
                input.style.display = 'none';
                displayRange.style.display = 'inline-block';
                return;
            }

            const [h, m] = newValue.split(':').map(Number);
            const duration = parseInt(card.getAttribute('data-menit'));

            // 1. HITUNG POSISI & TOTAL MENIT SELESAI
            const hourDiff = h - 9;
            const proposedTop = (hourDiff * 300) + (m / 60 * 300);
            const totalMinutesFromNine = (hourDiff * 60) + m;
            const endMinutesFromNine = totalMinutesFromNine + duration;

            // 2. VALIDASI JAM KERJA (Max 17:00 adalah 480 menit dari 09:00)
            if (h < 9) {
                alert("Jam mulai tidak boleh sebelum jam 09:00!");
                resetInput();
                return;
            }

            if (endMinutesFromNine > 480) {
                alert(`Tidak bisa input jam ${newValue}. Karena durasi task ${duration} menit, jam selesai (${formatMinutesToTime(endMinutesFromNine)}) akan melebihi jam 17:00!`);
                resetInput();
                return;
            }

            // 3. VALIDASI TABRAKAN
            if (checkCollision(proposedTop, card.offsetHeight, card.dataset.id)) {
                alert("Slot waktu tersebut sudah terisi task lain!");
                resetInput();
                return;
            }

            // Eksekusi jika lolos validasi
            card.style.top = proposedTop + 'px';
            updateTimeDisplay(card, proposedTop, duration);
            input.style.display = 'none';
            displayRange.style.display = 'inline-block';

            const minutesFromStart = (proposedTop / 300) * 60; 
            axios.post("{{ route('hc.updateSchedule') }}", { 
                id: card.dataset.id, 
                top: minutesFromStart 
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

        // --- COUNTDOWN TIMER ---
        function updateTimers() {
            const now = new Date();
            document.querySelectorAll('.running-timer').forEach(timer => {
                const [h, m] = timer.dataset.startTime.split(':').map(Number);
                const start = new Date(); start.setHours(h, m, 0);
                const elapsed = Math.floor((now - start) / 1000);
                const total = parseInt(timer.dataset.duration) * 60;
                const remain = total - elapsed;

                if (remain <= 0) {
                    autoUpdateToEndsTime(timer.dataset.id);
                } else {
                    const rm = Math.floor(remain / 60);
                    const rs = remain % 60;
                    timer.innerHTML = `<i class="fas fa-clock"></i> ${rm}:${rs < 10 ? '0' : ''}${rs}`;
                    if (remain < 60) timer.classList.replace('text-primary', 'text-danger');
                }
            });
        }
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
            const startTotal = (9 * 60) + (topPx / 300 * 60); // 300 adalah height per jam sekarang
            const endTotal = startTotal + duration;
            const format = (m) => `${Math.floor(m/60).toString().padStart(2,'0')}:${Math.round(m%60).toString().padStart(2,'0')}`;
            const display = item.querySelector('.task-time-range');
            if (display) display.innerText = `${format(startTotal)} - ${format(endTotal)}`;
        }
    });

    let scrollInterval;

    document.addEventListener('mousemove', (e) => {
        const draggingElement = document.querySelector('.sortable-chosen');
        
        if (draggingElement) {
            const threshold = 200; 
            const windowHeight = window.innerHeight;
            const mouseY = e.clientY;

            if (mouseY > windowHeight - threshold) {
                if (!scrollInterval) {
                    console.warn("LOG: Mengaktifkan Scroll Bawah Otomatis"); // Log peringatan saat scroll mulai
                    scrollInterval = setInterval(() => {
                        window.scrollBy(0, 15);
                    }, 20);
                }
            } 
            else if (mouseY < threshold) {
                if (!scrollInterval) {
                    console.warn("LOG: Mengaktifkan Scroll Atas Otomatis");
                    scrollInterval = setInterval(() => {
                        window.scrollBy(0, -15);
                    }, 20);
                }
            } 
            else {
                if (scrollInterval) {
                    console.log("LOG: Area Aman - Menghentikan Scroll");
                    clearInterval(scrollInterval);
                    scrollInterval = null;
                }
            }
        }
    });

    document.addEventListener('mouseup', () => {
        if (scrollInterval) {
            console.log("LOG: Klik Dilepas - Membersihkan Interval Scroll");
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
        axios.post("{{ route('hc.updateStatus') }}", { id: id, action: 'set_overdue', status: 8 }).then(() => location.reload());
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
</script>

@include('partials.footer')