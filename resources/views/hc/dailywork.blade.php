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

    .task-card-scheduled[data-pic="{{ Auth::id() }}"][data-status="0"],
    .task-card-scheduled[data-pic="{{ Auth::id() }}"][data-status="2"] { cursor: grab !important; }
    
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
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 text-gray-800 font-weight-bold mb-0">Daily Work Schedule</h1>
            <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i> Shift: 09:00 - 17:00</small>
        </div>
        <button class="btn btn-sm btn-white border shadow-sm" onclick="location.reload()">
            <i class="fas fa-sync-alt text-primary"></i> Refresh Grid
        </button>
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
                    <div class="task-card-source card shadow-sm border-left-{{ $priority_colors[$task->priority] ?? 'secondary' }}"
                        data-id="{{ $task->id }}" 
                        data-menit="{{ $task->menit }}" 
                        data-status="{{ $task->status }}" 
                        style="width: 240px; cursor: grab; border-radius: 8px;">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="task-title text-dark text-truncate" style="max-width: 160px;">{{ $task->nama_task }}</span>
                                <span class="badge badge-primary">{{ $task->menit }}m</span>
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
                            <div class="grid-line" style="top: {{ $i * 80 }}px;"></div>
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
                            @endphp

                            <div class="task-card-scheduled card {{ $isRunning ? 'bg-primary text-white' : '' }} {{ $task->status == 5 ? 'bg-light' : '' }} {{ $task->status == 7 ? 'bg-danger text-white' : '' }} border-left-{{ $priority_colors[$task->priority] ?? 'secondary' }}"
                                data-id="{{ $task->id }}" 
                                data-status="{{ $task->status }}" 
                                data-pic="{{ $task->pic }}" 
                                data-menit="{{ $task->menit }}" 
                                style="top: {{ $topPx }}px; height: {{ $heightPx }}px; min-height: 30px;">

                                <div class="card-body p-2 position-relative h-100 d-flex flex-column">
                                    <div class="d-flex justify-content-between w-100">
                                        <div class="text-truncate" style="max-width: 65%;">
                                            <strong class="task-title {{ ($isRunning || $task->status == 7) ? 'text-white' : '' }}" style="{{ $task->status == 5 ? 'font-size: 11px !important;' : '' }}">
                                                {{ $task->nama_task }} ({{ Str::upper($task->nama) }})
                                                @if($task->status == 5) <small>(Split)</small> @endif
                                            </strong>

                                            @if($isRunning) 
                                                <div class="running-timer badge badge-light text-primary mt-1" 
                                                    data-id="{{ $task->id }}"
                                                    data-start-time="{{ $taskStart->format('H:i') }}" 
                                                    data-duration="{{ $task->menit }}"
                                                    style="font-size: 10px;">
                                                    <i class="fas fa-spinner fa-spin mr-1"></i> Sync...
                                                </div>
                                            @endif

                                            @if($task->status == 7)
                                                <span class="badge badge-light text-danger animate-pulse mt-1" style="font-size: 10px;">
                                                    <i class="fas fa-exclamation-circle mr-1"></i> OVERDUE
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="text-right">
                                            <span class="task-time-range {{ ($isRunning || $task->status == 7) ? 'text-white' : '' }}" 
                                                @if(!in_array($task->status, [1, 3, 4, 7])) onclick="enableTimeEdit(this)" @endif>
                                                {{ $taskStart->format('H:i') }} - {{ $taskEnd->format('H:i') }}
                                            </span>
                                            <input type="time" class="time-edit-input" onblur="saveManualTime(this)" onkeydown="if(event.key==='Enter') saveManualTime(this)">
                                        </div>
                                    </div>

                                    @if($task->status != 5)
                                        <div class="action-container">
                                            @if($isMyTask)
                                                @if($task->status == 1)
                                                    <button onclick="splitTask({{ $task->id }})" class="btn btn-xs btn-light text-primary border">Split</button>
                                                    <button onclick="changeStatus({{ $task->id }}, 'stop')" class="btn btn-xs btn-light text-success border">Kirim Task</button>
                                                @elseif(in_array($task->status, [0, 2]))
                                                    <button onclick="returnToPool({{ $task->id }})" class="btn btn-xs btn-light text-danger border"><i class="fas fa-undo"></i></button>
                                                    <button onclick="changeStatus({{ $task->id }}, 'play')" class="btn btn-xs btn-success shadow-sm">Start</button>
                                                @elseif($task->status == 3)
                                                    <span class="badge badge-warning text-dark" style="font-size: 9px;">Reviewing...</span>
                                                @elseif($task->status == 4)
                                                    <span class="badge badge-success" style="font-size: 9px;"><i class="fas fa-check"></i> Done</span>
                                                @elseif($task->status == 7)
                                                    <button onclick="changeStatus({{ $task->id }}, 'stop')" class="btn btn-xs btn-light text-success border">Kirim Task</button>
                                                @endif
                                            @else
                                                @if($task->status == 3)
                                                    <button onclick="changeStatus({{ $task->id }}, 'approve')" class="btn btn-xs btn-success">Approve</button>
                                                    <button onclick="changeStatus({{ $task->id }}, 'reject')" class="btn btn-xs btn-danger">Reject</button>
                                                @else
                                                    <small class="text-muted" style="font-size: 9px;">PIC: {{ explode('@', $task->vic_email)[0] }}</small>
                                                @endif
                                            @endif
                                        </div>
                                    @endif

                                    @if($isMyTask && in_array($task->status, [0, 2]))
                                        <div class="resize-handle"></div>
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
            draggable: ".task-card-source, .task-card-scheduled[data-status='0'], .task-card-scheduled[data-status='2']",
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
            el.style.display = 'none';
            input.style.display = 'inline-block';
            input.value = el.innerText.split(' - ')[0].trim();
            input.focus();
        };

        window.saveManualTime = function(input) {
            const card = input.closest('.task-card-scheduled');
            const [h, m] = input.value.split(':').map(Number);
            if (h < 9 || h >= 17) { alert("Di luar jam kerja!"); location.reload(); return; }

            const topPx = ((h - 9) * 80) + (m / 60 * 80);
            saveToDatabase(card.dataset.id, topPx, true);
        };

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
                    autoUpdateToOverdue(timer.dataset.id);
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

        document.addEventListener('mousemove', e => {
            if (!isResizing) return;
            
            // Snap resize setiap 50px (10 menit)
            let newH = Math.round((startH + (e.clientY - startY)) / 50) * 50;
            if (newH < 50) newH = 50; // Minimal 10 menit
            
            // Ganti 640 menjadi 2400
            if (parseFloat(currentCard.style.top) + newH <= 2400) {
                currentCard.style.height = newH + 'px';
                // Gunakan pembagi 300 untuk hitung menit
                updateTimeDisplay(currentCard, parseFloat(currentCard.style.top), (newH / 300) * 60);
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

    window.autoReturnToPool = function(id) {
        axios.post("{{ route('hc.updateSchedule') }}", { id: id, reset: true })
            .then(() => location.reload())
            .catch(() => location.reload());
    };

    // --- AJAX ACTIONS ---
    function autoUpdateToOverdue(id) {
        axios.post("{{ route('hc.updateStatus') }}", { id: id, action: 'set_overdue', status: 7 }).then(() => location.reload());
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