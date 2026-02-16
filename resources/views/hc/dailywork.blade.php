@include('partials.header')

{{-- Menggunakan Vite untuk akses offline (Axios & SortableJS) --}}
@vite(['resources/js/app.js'])

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root { 
        --grid-hour-height: 80px; 
        --primary-color: #4e73df;
        --border-color: #e3e6f0;
    }
    
    .hour-sidebar { width: 85px; background: #f8f9fc; flex-shrink: 0; }
    .hour-major { 
        height: 80px; font-size: 14px; color: var(--primary-color); font-weight: bold; 
        display: flex; align-items: flex-start; justify-content: flex-end; 
        padding-right: 15px; padding-top: 10px;
    }

    #daily-grid {
        height: 640px; background-color: #fff; position: relative;
        border-radius: 0 12px 12px 0; border: 1px solid var(--border-color); border-left: none;
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
        transition: top 0.2s ease, height 0.2s ease; border-bottom: 1px solid rgba(0,0,0,0.1);
    }

    /* Kursor khusus: Status 0 (Pending) dan 2 (Revisi) milik sendiri bisa digeser */
    .task-card-scheduled[data-pic="{{ Auth::id() }}"][data-status="0"],
    .task-card-scheduled[data-pic="{{ Auth::id() }}"][data-status="2"] { cursor: grab !important; }
    
    .task-card-scheduled:not([data-pic="{{ Auth::id() }}"]),
    .task-card-scheduled[data-status="1"],
    .task-card-scheduled[data-status="3"],
    .task-card-scheduled[data-status="4"],
    .task-card-scheduled[data-status="5"] { cursor: default !important; }

    .task-title { font-size: 14px !important; font-weight: 800; color: #1a202c; line-height: 1.2; }
    .task-time { font-size: 11px !important; font-weight: 800; color: var(--primary-color); white-space: nowrap; }
    .duration-inline { font-size: 10px; font-weight: bold; color: #4e73df; background: #f0f2f9; padding: 1px 5px; border-radius: 4px; margin-left: 5px; }
    
    .timer-badge { 
        font-size: 10px; background: #fff; color: #e74a3b; padding: 2px 8px; 
        border-radius: 10px; font-weight: 900; border: 1px solid #e74a3b;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: inline-block; margin-left: 5px;
    }

    .action-container { position: absolute; bottom: 8px; right: 8px; display: flex; gap: 4px; }
    .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.7rem; border-radius: 4px; font-weight: bold; }
    .border-dashed { border-style: dashed !important; border-width: 1px !important; }
</style>

<div class="container-fluid">
    {{-- Header & Pool --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 text-gray-800 font-weight-bold mb-0">Daily Work Schedule</h1>
            <small class="text-muted">Manage your 09:00 - 17:00 timeline</small>
        </div>
        <button class="btn btn-sm btn-primary shadow-sm" onclick="location.reload()"><i class="fas fa-sync-alt"></i> Sync Grid</button>
    </div>

    {{-- POOL --}}
    <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px;">
        <div class="card-body bg-light rounded shadow-inner">
            <h6 class="text-gray-600 font-weight-bold mb-3" style="font-size: 13px;">
                <i class="fas fa-list-ul mr-2"></i> TASK POOL (Belum Dijadwalkan)
            </h6>
            
            <div id="unscheduled-pool" class="d-flex flex-wrap" style="gap: 15px; min-height: 80px;">
                @foreach($unscheduled_tasks as $task)
                    <div class="task-card-source card shadow-sm border-left-{{ $priority_colors[$task->priority] ?? 'secondary' }}"
                        data-id="{{ $task->id }}" 
                        data-menit="{{ $task->menit }}" 
                        data-status="{{ $task->status }}" 
                        style="width: 260px; cursor: grab; border-radius: 8px; overflow: hidden;">
                        
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <strong class="task-title" style="font-size: 14px; color: #2d3748; line-height: 1.2;">
                                    {{ $task->nama_task }}
                                </strong>
                                <span class="badge badge-primary shadow-sm" style="font-size: 10px; padding: 4px 8px; border-radius: 5px;">
                                    <i class="fas fa-clock mr-1"></i> {{ $task->menit }}m
                                </span>
                            </div>

                            @if($task->note)
                                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; margin-top: 8px;">
                                    <small class="text-muted d-block mb-1" style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <i class="fas fa-sticky-note text-warning mr-1"></i> Note:
                                    </small>
                                    <p class="mb-0" style="font-size: 11.5px; color: #4a5568; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; font-weight: 500;">
                                        {{ $task->note }}
                                    </p>
                                </div>
                            @endif

                            @if(str_contains($task->nama_task, '(Sisa)'))
                                <div class="mt-2">
                                    <span class="badge badge-light border text-danger" style="font-size: 9px; font-weight: 800;">
                                        <i class="fas fa-cut mr-1"></i> HASIL SPLIT
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- GRID JADWAL --}}
    <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="row no-gutters">
                <div class="col-auto hour-sidebar border-right">
                    @for($i = 9; $i <= 16; $i++)
                        <div class="hour-major">{{ sprintf('%02d:00', $i) }}</div>
                    @endfor
                    <div class="hour-major" style="height: 40px;">17:00</div>
                </div>

                <div class="col position-relative">
                    <div id="daily-grid">
                        @for($i = 0; $i <= 8; $i++)
                            <div class="grid-line" style="top: {{ $i * 80 }}px;"></div>
                        @endfor

                        @foreach($scheduled_tasks as $task)
                            @php
                                $startGrid = \Carbon\Carbon::createFromTime(9, 0, 0);
                                $taskStart = \Carbon\Carbon::parse($task->jam_mulai);
                                $taskEnd = $taskStart->copy()->addMinutes($task->menit);
                                $topPx = ($startGrid->diffInMinutes($taskStart, false) / 60) * 80;
                                $heightPx = ($task->menit / 60) * 80;
                                
                                $isRunning = ($task->status == 1); 
                                $isSplitHistory = ($task->status == 5);
                                $isWaiting = ($task->status == 3); 
                                $isDone = ($task->status == 4);
                                $isMyTask = ($task->pic == Auth::id());
                                $todayStr = \Carbon\Carbon::today()->format('Y-m-d');
                                $fullPlayTime = $todayStr . ' ' . $task->jam_mulai;
                            @endphp

                            <div class="task-card-scheduled card {{ $isRunning ? 'bg-primary text-white running-task' : '' }} border-left-{{ $priority_colors[$task->priority] ?? 'secondary' }}"
                                data-id="{{ $task->id }}" data-status="{{ $task->status }}" data-pic="{{ $task->pic }}"
                                data-menit="{{ $task->menit }}" 
                                style="top: {{ $topPx }}px; height: {{ $heightPx }}px;">

                                <div class="card-body p-2 position-relative h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div class="text-truncate" style="max-width: 65%;">
                                            <strong class="task-title {{ $isRunning ? 'text-white' : '' }}">{{ $task->nama_task }}</strong>
                                        </div>
                                        <div class="task-time-range">
                                            {{ $taskStart->format('H:i') }} - {{ $taskEnd->format('H:i') }}
                                        </div>
                                    </div>

                                    @if($task->note)
                                        <div class="task-note-box"><i class="fas fa-sticky-note mr-1 opacity-50"></i>{{ $task->note }}</div>
                                    @endif

                                    <div class="action-container">
                                        @if($isMyTask)
                                            {{-- LOGIKA UNTUK TASK MILIK SENDIRI --}}
                                            @if($isRunning)
                                                <button onclick="splitTask({{ $task->id }})" class="btn btn-xs btn-outline-light mr-1" title="Split Task"><i class="fas fa-cut"></i></button>
                                                <button onclick="changeStatus({{ $task->id }}, 'stop')" class="btn btn-xs btn-light text-primary shadow-sm border">
                                                    <i class="fas fa-paper-plane mr-1"></i>Kirim Task
                                                </button>
                                            @elseif($task->status == 3)
                                                {{-- Keterangan Menunggu Approval --}}
                                                <span class="badge badge-warning text-dark px-2 py-1" style="font-size: 10px; font-weight: 800;">
                                                    <i class="fas fa-clock mr-1"></i> MENUNGGU APPROVAL
                                                </span>
                                            @elseif($task->status == 0 || $task->status == 2)
                                                <button onclick="returnToPool({{ $task->id }})" class="btn btn-xs btn-light text-danger border" title="Kembalikan ke Pool">
                                                    <i class="fas fa-reply"></i>
                                                </button>
                                                <button onclick="changeStatus({{ $task->id }}, 'play')" class="btn btn-xs btn-success shadow-sm">
                                                    <i class="fas fa-play mr-1"></i>Start
                                                </button>
                                            @elseif($task->status == 4)
                                                <span class="badge badge-success px-2 py-1" style="font-size: 10px;">
                                                    <i class="fas fa-check-double mr-1"></i> SELESAI
                                                </span>
                                            @endif

                                        @else
                                            {{-- LOGIKA UNTUK TASK BAWAHAN --}}
                                            @if($task->status == 3)
                                                <button onclick="changeStatus({{ $task->id }}, 'approve')" class="btn btn-xs btn-success shadow-sm mr-1">
                                                    <i class="fas fa-check mr-1"></i> Approve
                                                </button>
                                                <button onclick="changeStatus({{ $task->id }}, 'revisi')" class="btn btn-xs btn-danger shadow-sm">
                                                    <i class="fas fa-undo mr-1"></i> Revisi
                                                </button>
                                            @else
                                                {{-- Info PIC jika itu task bawahan tapi statusnya belum minta approval --}}
                                                <span class="badge badge-light border text-muted" style="font-size: 9px;">
                                                    Dikerjakan oleh: {{ $task->vic_email }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
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
        const currentUserId = "{{ Auth::id() }}";
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

        // --- SORTABLE LOGIC ---
        new Sortable(document.getElementById('unscheduled-pool'), { group: 'schedule_group', animation: 150 });

        const grid = document.getElementById('daily-grid');
        new Sortable(grid, {
            group: 'schedule_group',
            animation: 150,
            sort: false,
            draggable: ".task-card-source, .task-card-scheduled",
            filter: function (evt, target) {
                const card = target.closest('.task-card-scheduled');
                if (card) {
                    const status = card.getAttribute('data-status');
                    const picId = card.getAttribute('data-pic');
                    if (picId !== currentUserId || ['1', '3', '4', '5'].includes(status)) return true;
                }
                return target.classList.contains('btn');
            },
            onAdd: (evt) => handleTaskMovement(evt, grid, true),
            onEnd: (evt) => { 
                if (evt.to === grid) handleTaskMovement(evt, grid, false); 
                else {
                    // Logic jika ditarik keluar grid (manual drag to pool)
                    const id = evt.item.getAttribute('data-id');
                    returnToPool(id);
                }
            }
        });

        // --- TIMER REAL-TIME ---
        setInterval(() => {
            document.querySelectorAll('.running-task').forEach(card => {
                const startTime = new Date(card.getAttribute('data-start-work')).getTime();
                const diff = Math.floor((new Date().getTime() - startTime) / 60000);
                const timer = document.getElementById(`timer-${card.getAttribute('data-id')}`);
                if(timer) timer.innerText = diff + 'm';
            });
        }, 30000);
    });

    function minutesToClock(totalMinutes) {
        let h = Math.floor(totalMinutes / 60);
        let m = Math.round(totalMinutes % 60);
        return (h < 10 ? '0' + h : h) + ':' + (m < 10 ? '0' + m : m);
    }

    function handleTaskMovement(evt, grid, isNew) {
        const item = evt.item;
        const rect = grid.getBoundingClientRect();
        const clientY = (evt.originalEvent.clientY || (evt.originalEvent.touches ? evt.originalEvent.touches[0].clientY : 0));
        
        let proposedTop = Math.round((clientY - rect.top) / 20) * 20; 
        const duration = parseInt(item.dataset.menit) || 30;
        const heightPx = (duration / 60) * 80;

        // Collision logic
        const otherTasks = Array.from(grid.querySelectorAll('.task-card-scheduled')).filter(el => el !== item)
            .map(el => ({ top: parseFloat(el.style.top), bottom: parseFloat(el.style.top) + parseFloat(el.style.height) }))
            .sort((a, b) => a.top - b.top);

        let finalTop = proposedTop;
        let collision = true;
        while (collision) {
            collision = false;
            for (let other of otherTasks) {
                if (finalTop < other.bottom && (finalTop + heightPx) > other.top) {
                    finalTop = other.bottom;
                    collision = true; break;
                }
            }
        }

        if (finalTop + heightPx > 640) finalTop = 640 - heightPx;
        
        // Update Posisi Card
        item.style.top = finalTop + 'px'; 
        item.style.height = heightPx + 'px'; 
        item.style.left = '0'; 
        item.style.width = '100%';

        // --- UPDATE JAM SECARA OTOMATIS TANPA REFRESH ---
        const startMinutesAt9AM = 9 * 60; // 540 menit
        const additionalMinutes = (finalTop / 80) * 60;
        const startTaskMinutes = startMinutesAt9AM + additionalMinutes;
        const endTaskMinutes = startTaskMinutes + duration;

        const timeRangeElement = item.querySelector('.task-time-range');
        if (timeRangeElement) {
            timeRangeElement.innerText = minutesToClock(startTaskMinutes) + ' - ' + minutesToClock(endTaskMinutes);
        }

        const totalMinStart = (finalTop / 80) * 60;
        axios.post("{{ route('hc.updateSchedule') }}", { id: item.dataset.id, top: totalMinStart })
            .then(() => { if (isNew) location.reload(); });
    }

    function returnToPool(id) {
        if(confirm('Batalkan jadwal dan kembalikan ke Pool?')) {
            axios.post("{{ route('hc.updateSchedule') }}", { id: id, reset: true })
                .then(() => location.reload());
        } else {
            location.reload(); // Refresh untuk kembalikan posisi jika cancel confirm
        }
    }

    function splitTask(id) {
        if(confirm('Split task? Sisa waktu akan kembali ke Pool.')) {
            axios.post("{{ route('hc.cancelRemaining') }}", { id: id }).then(() => location.reload());
        }
    }

    function changeStatus(id, action) {
        let jamMulaiStr = null;
        
        // Konfirmasi untuk aksi sensitif
        if (action === 'approve' && !confirm('Approve tugas ini?')) return;
        if (action === 'revisi' && !confirm('Kembalikan tugas ini untuk direvisi?')) return;

        if (action === 'play') {
            const card = document.querySelector(`.task-card-scheduled[data-id="${id}"]`);
            if (card) {
                const timeRange = card.querySelector('.task-time-range').innerText;
                jamMulaiStr = timeRange.split(' - ')[0]; 
            }
        }

        axios.post("{{ route('hc.updateStatus') }}", { 
            id: id, 
            action: action, 
            jam_mulai: jamMulaiStr 
        })
        .then(() => {
            // Tampilkan notifikasi sukses jika perlu
            location.reload();
        })
        .catch(err => {
            alert('Terjadi kesalahan saat memperbarui status.');
        });
    }
</script>

@include('partials.footer')