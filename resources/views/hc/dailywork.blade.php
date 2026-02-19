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
        transition: top 0.2s ease; border-bottom: 1px solid rgba(0,0,0,0.1);
    }

    .task-card-scheduled[data-pic="{{ Auth::id() }}"][data-status="0"],
    .task-card-scheduled[data-pic="{{ Auth::id() }}"][data-status="2"] { cursor: grab !important; }
    
    .task-title { font-size: 14px !important; font-weight: 800; color: #1a202c; line-height: 1.2; }
    
    .task-time-range { 
        font-size: 11px !important; font-weight: 800; color: var(--primary-color); 
        white-space: nowrap; cursor: pointer; padding: 2px 4px; border-radius: 4px;
    }
    .task-time-range:hover { background: rgba(78, 115, 223, 0.1); }
    .time-edit-input { 
        font-size: 10px; width: 70px; border: 1px solid var(--primary-color); 
        border-radius: 4px; display: none; 
    }
    
    .action-container { position: absolute; bottom: 8px; right: 8px; display: flex; gap: 4px; z-index: 20; }
    .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.7rem; border-radius: 4px; font-weight: bold; }

    .resize-handle {
        position: absolute; bottom: 0; left: 0; width: 100%; height: 10px;
        cursor: ns-resize; z-index: 30; background: transparent;
    }
    .resize-handle:hover { background: rgba(0,0,0,0.05); }

    body.resizing { cursor: ns-resize !important; user-select: none; }
    body.resizing .task-card-scheduled { transition: none !important; }
    @keyframes pulse-red {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-pulse {
        animation: pulse-red 2s infinite;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 text-gray-800 font-weight-bold mb-0">Daily Work Schedule</h1>
            <small class="text-muted">Working Hours: 09:00 - 17:00.</small>
        </div>
        <button class="btn btn-sm btn-primary shadow-sm" onclick="location.reload()"><i class="fas fa-sync-alt"></i> Sync Grid</button>
    </div>

    {{-- POOL --}}
    <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px;">
        <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
            <h6 class="font-weight-bold text-primary mb-0">
                <i class="fas fa-layer-group mr-1"></i> Task Pool
            </h6>
            <div class="text-right">
                <span class="badge badge-soft-primary p-2" 
                    style="background: #eef2ff; color: #4e73df; border-radius: 8px;" 
                    id="pool-total-display">
                    <i class="far fa-clock mr-1"></i> Total: <strong>{{ $formatTotalPool }}</strong>
                </span>
            </div>
        </div>

        <div class="card-body bg-light rounded shadow-inner mt-2">
            <div id="unscheduled-pool" class="d-flex flex-wrap" style="gap: 15px; min-height: 80px;">
                @foreach($unscheduled_tasks as $task)
                    <div class="task-card-source card shadow-sm border-left-{{ $priority_colors[$task->priority] ?? 'secondary' }}"
                        data-id="{{ $task->id }}" 
                        data-menit="{{ $task->menit }}" 
                        data-status="{{ $task->status }}" 
                        style="width: 260px; cursor: grab; border-radius: 8px; overflow: hidden;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="task-title text-dark">{{ $task->nama_task }}</strong>
                                <span class="badge badge-primary">{{ $task->menit }}m</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- GRID --}}
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
                                $isMyTask = ($task->pic == Auth::id());
                                $isRunning = ($task->status == 1);
                            @endphp

                            <div class="task-card-scheduled card {{ $isRunning ? 'bg-primary text-white' : '' }} {{ $task->status == 5 ? 'bg-light' : '' }} {{ $task->status == 7 ? 'bg-danger text-white' : '' }} border-left-{{ $priority_colors[$task->priority] ?? 'secondary' }}"
                                data-id="{{ $task->id }}" 
                                data-status="{{ $task->status }}" 
                                data-pic="{{ $task->pic }}" 
                                data-menit="{{ $task->menit }}" 
                                style="top: {{ $topPx }}px; height: {{ $heightPx }}px; {{ $task->status == 5 ? 'min-height: 30px;' : '' }}">

                                <div class="card-body p-2 position-relative h-100 d-flex flex-column justify-content-start align-items-start">
                                    <div class="d-flex justify-content-between w-100 mb-1">
                                        <div class="text-truncate" style="max-width: 70%;">
                                            <strong class="task-title {{ ($isRunning || $task->status == 7) ? 'text-white' : '' }}" style="display: block; {{ $task->status == 5 ? 'font-size: 11px !important;' : '' }}">
                                                {{ $task->nama_task }} 
                                                @if($task->status == 5) <small class="text-muted">(Split)</small> @endif
                                            </strong>

                                            @if($isRunning) 
                                                {{-- data-duration adalah jatah menit (misal: 5), data-start-time adalah jam mulai grid (misal: 14:43) --}}
                                                <small class="running-timer badge badge-light text-primary" 
                                                    data-id="{{ $task->id }}"
                                                    data-start-time="{{ $taskStart->format('H:i') }}" 
                                                    data-duration="{{ $task->menit }}"
                                                    style="font-size: 10px; font-weight: bold;">
                                                    <i class="fas fa-clock mr-1"></i> Menghitung...
                                                </small>
                                            @endif

                                            @if($task->status == 7)
                                                <span class="badge badge-light text-danger animate-pulse" style="font-size: 10px; font-weight: 900;">
                                                    <i class="fas fa-exclamation-circle mr-1"></i> OVERDUE
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="text-right">
                                            <span class="task-time-range {{ ($isRunning || $task->status == 7) ? 'text-white' : '' }}" 
                                                {{-- Matikan edit waktu jika status 7 --}}
                                                @if($task->status != 7) onclick="enableTimeEdit(this)" @endif
                                                style="{{ $task->status != 7 ? 'cursor: pointer;' : '' }} {{ $task->status == 5 ? 'font-size: 9px !important;' : '' }}">
                                                {{ $taskStart->format('H:i') }} - {{ $taskEnd->format('H:i') }}
                                            </span>
                                            {{-- Input time edit otomatis tersembunyi karena kondisi di atas --}}
                                        </div>
                                    </div>

                                    {{-- Sembunyikan tombol aksi jika status 5 ATAU status 7 --}}
                                    @if(!in_array($task->status, [5, 7]))
                                        <div class="action-container">
                                            @if($isMyTask)
                                                {{-- --- LOGIKA JIKA INI TASK SAYA --- --}}
                                                @if($task->status == 1) {{-- Running --}}
                                                    <button onclick="splitTask({{ $task->id }})" class="btn btn-xs btn-light text-primary border">Split</button>
                                                    <button onclick="changeStatus({{ $task->id }}, 'stop')" class="btn btn-xs btn-light text-primary border">Kirim</button>
                                                
                                                @elseif(in_array($task->status, [0, 2])) {{-- Pending atau Revisi --}}
                                                    <button onclick="returnToPool({{ $task->id }})" class="btn btn-xs btn-light text-danger border"><i class="fas fa-reply"></i></button>
                                                    <button onclick="changeStatus({{ $task->id }}, 'play')" class="btn btn-xs btn-success shadow-sm">Start</button>
                                                
                                                @elseif($task->status == 3) {{-- Sedang di-Review Atasan --}}
                                                    <span class="badge badge-warning text-dark"><i class="fas fa-hourglass-start"></i> Waiting Review</span>
                                                
                                                @elseif($task->status == 4) {{-- Selesai --}}
                                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Selesai</span>
                                                    
                                                @elseif($task->status == 7) {{-- Overdue --}}
                                                    <span class="badge badge-dark">Terkunci (Overdue)</span>
                                                @endif

                                            @else
                                                {{-- --- LOGIKA JIKA INI TASK ORANG LAIN (BAWAHAN) --- --}}
                                                @if($task->status == 3) {{-- Jika statusnya perlu di-approve --}}
                                                    <button onclick="approveTask({{ $task->id }}, 'approve')" class="btn btn-xs btn-success shadow-sm">Approve</button>
                                                    <button onclick="approveTask({{ $task->id }}, 'reject')" class="btn btn-xs btn-danger shadow-sm">Reject</button>
                                                @else
                                                    {{-- Menampilkan info PIC jika bukan task kita --}}
                                                    <small class="badge badge-secondary" style="font-size: 9px; opacity: 0.8;">
                                                        <i class="fas fa-user"></i> {{ $task->vic_email }}
                                                    </small>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                    <div class="resize-handle"></div>
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

        const grid = document.getElementById('daily-grid');
        const pool = document.getElementById('unscheduled-pool');

        // --- SORTABLE SETUP POOL ---
        new Sortable(pool, { 
            group: 'schedule_group', 
            animation: 150,
            onAdd: function (evt) {
                const taskId = evt.item.getAttribute('data-id');
                autoReturnToPool(taskId);
            }
        });

        // --- SORTABLE SETUP GRID ---
        new Sortable(grid, {
            group: 'schedule_group',
            animation: 150,
            sort: false,
            // UBAH BAGIAN INI:
            // Hanya status 0, 2, dan task baru dari pool yang bisa di-drag
            draggable: ".task-card-source, .task-card-scheduled[data-status='0'], .task-card-scheduled[data-status='2']",
            
            filter: (evt, target) => target.classList.contains('resize-handle'),
            onAdd: (evt) => handleTaskMovement(evt, true),
            onEnd: (evt) => { if (evt.to === grid && evt.from === grid) handleTaskMovement(evt, false); }
        });

        window.autoReturnToPool = function(id) {
            axios.post("{{ route('hc.updateSchedule') }}", { id: id, reset: true })
                .then(() => location.reload())
                .catch(() => location.reload());
        };

        function checkCollision(proposedTop, heightPx, currentId) {
            const tasks = grid.querySelectorAll('.task-card-scheduled');
            let collision = false;
            tasks.forEach(task => {
                if (task.getAttribute('data-id') == currentId) return;
                const otherTop = parseFloat(task.style.top);
                const otherHeight = parseFloat(task.style.height);
                if (proposedTop < (otherTop + otherHeight) && (proposedTop + heightPx) > otherTop) {
                    collision = true;
                }
            });
            return collision;
        }

        window.handleTaskMovement = function(evt, shouldRefresh) {
            const item = evt.item;
            const rect = grid.getBoundingClientRect();
            const clientY = (evt.originalEvent.clientY || (evt.originalEvent.touches ? evt.originalEvent.touches[0].clientY : 0));
            const taskId = item.getAttribute('data-id');
            const oldTop = item.style.top;

            // --- KUNCI: AMBIL TINGGI FISIK SAAT INI ---
            // Jika kartu berasal dari Pool (isNew), gunakan durasi default.
            // Jika kartu sudah di Grid, ambil tinggi pixel aslinya (agar hasil resize tidak hilang).
            let heightPx;
            const durationMinutes = parseInt(item.getAttribute('data-menit'));
            if (shouldRefresh) { // Datang dari Pool
                heightPx = (durationMinutes / 60) * 80;
            } else { // Geser antar Grid
                heightPx = item.offsetHeight; 
            }
            
            let proposedTop = Math.round((clientY - rect.top) / 20) * 20; 

            // Validasi batas bawah grid (17:00 / 640px)
            if (proposedTop + heightPx > 640) {
                proposedTop = 640 - heightPx;
            }
            if (proposedTop < 0) proposedTop = 0;

            // Cek tabrakan
            if (checkCollision(proposedTop, heightPx, taskId)) {
                alert("Waktu ini sudah terisi!");
                if (shouldRefresh) { 
                    item.remove(); 
                    location.reload(); 
                } else { 
                    item.style.top = oldTop;
                }
                return;
            }

            // TERAPKAN STYLE: Gunakan heightPx yang sudah dikunci
            item.style.position = 'absolute';
            item.style.top = proposedTop + 'px';
            item.style.height = heightPx + 'px'; 
            item.style.width = '100%';

            // Update data-menit di elemen agar sinkron dengan database
            const newDuration = (heightPx / 80) * 60;
            item.setAttribute('data-menit', durationMinutes);

            updateTimeDisplay(item, proposedTop, durationMinutes);
            
            // Simpan ke database
            saveToDatabase(taskId, proposedTop, shouldRefresh, durationMinutes);
        };



        function saveToDatabase(taskId, topPx, shouldRefresh) {
            const totalMinStart = (topPx / 80) * 60;
            axios.post("{{ route('hc.updateSchedule') }}", { id: taskId, top: totalMinStart })
            .then(() => {
                if (shouldRefresh) {
                    location.reload(); // Refresh hanya jika dari Pool
                }
            });
        }

        // --- SISA FUNGSI LAINNYA ---
        window.enableTimeEdit = function(element) {
            const card = element.closest('.task-card-scheduled');
            const status = card.dataset.status;
            if (['1', '3', '4'].includes(status)) return;
            const input = element.nextElementSibling;
            input.value = element.innerText.split(' - ')[0].trim();
            element.style.display = 'none';
            input.style.display = 'block';
            input.focus();
        };

        window.saveManualTime = function(input) {
            const element = input.previousElementSibling;
            const card = input.closest('.task-card-scheduled');
            const taskId = card.dataset.id;
            const durationMinutes = parseInt(card.getAttribute('data-menit'));
            const timeVal = input.value;
            if (!timeVal) { revert(); return; }
            const [hours, minutes] = timeVal.split(':').map(Number);
            const totalMinutes = (hours * 60) + minutes;
            const baselineMinutes = 9 * 60;

            if (totalMinutes < baselineMinutes || totalMinutes + durationMinutes > 17 * 60) {
                alert("Waktu tidak valid!");
                revert(); return;
            }

            let proposedTop = ((totalMinutes - baselineMinutes) / 60) * 80;
            if (checkCollision(proposedTop, (durationMinutes/60)*80, taskId)) {
                alert("Tabrakan!");
                revert(); return;
            }

            card.style.top = proposedTop + 'px';
            updateTimeDisplay(card, proposedTop, durationMinutes);
            saveToDatabase(taskId, proposedTop, false); // Manual edit tanpa refresh
            revert();
            function revert() { input.style.display = 'none'; element.style.display = 'block'; }
        };

        let isUpdatingStatus = new Set(); // Untuk mencegah request ganda ke server

        function updateRunningTimers() {
            const now = new Date();

            document.querySelectorAll('.running-timer').forEach(timer => {
                const taskId = timer.getAttribute('data-id');
                if (isUpdatingStatus.has(taskId)) return;

                const startTimeStr = timer.getAttribute('data-start-time'); // Misal: "14:43"
                const durationLimit = parseInt(timer.getAttribute('data-duration')); // Misal: 5
                
                // Buat objek Date jam mulai hari ini
                const [h, m] = startTimeStr.split(':').map(Number);
                const startTime = new Date();
                startTime.setHours(h, m, 0, 0);

                // Hitung selisih detik yang sudah berjalan
                const elapsedSeconds = Math.floor((now - startTime) / 1000);
                const totalLimitSeconds = durationLimit * 60;
                
                // SISA WAKTU = Batas Menit - Waktu Berjalan
                const remainingSeconds = totalLimitSeconds - elapsedSeconds;

                if (remainingSeconds <= 0) {
                    // JIKA WAKTU HABIS
                    timer.innerHTML = `<i class="fas fa-times-circle"></i> Waktu Habis!`;
                    isUpdatingStatus.add(taskId);
                    
                    // Kirim perintah ke controller untuk ubah status ke 7
                    autoUpdateToOverdue(taskId);
                } else {
                    // TAMPILKAN COUNTDOWN
                    const mins = Math.floor(remainingSeconds / 60).toString().padStart(2, '0');
                    const secs = (remainingSeconds % 60).toString().padStart(2, '0');
                    
                    timer.innerHTML = `<i class="fas fa-hourglass-half mr-1"></i> Sisa: ${mins}:${secs}`;
                    
                    // Efek visual jika sisa waktu kurang dari 1 menit
                    if (remainingSeconds < 60) {
                        timer.classList.replace('text-primary', 'text-danger');
                    }
                }
            });
        }

        function autoUpdateToOverdue(id) {
            axios.post("{{ route('hc.updateStatus') }}", { 
                id: id, 
                action: 'set_overdue', // Anda perlu menangani action ini di Controller
                status: 7 
            })
            .then(() => {
                location.reload(); // Reload untuk mengunci kartu (karena status sudah 7)
            })
            .catch(err => {
                console.error("Gagal update status overdue", err);
                isUpdatingStatus.delete(id);
            });
        }

        // Jalankan timer setiap detik
        setInterval(updateRunningTimers, 1000);

        // Resize
        let isResizing = false;
        let currentCard = null;
        let startY, startH;

        document.addEventListener('mousedown', function(e) {
            if (e.target.classList.contains('resize-handle')) {
                isResizing = true;
                currentCard = e.target.closest('.task-card-scheduled');
                startY = e.clientY;
                startH = parseFloat(window.getComputedStyle(currentCard).height);
                document.body.classList.add('resizing');
                e.preventDefault();
            }
        });

        document.addEventListener('mousemove', function(e) {
            if (!isResizing) return;
            const deltaY = e.clientY - startY;
            let newH = Math.round((startH + deltaY) / 20) * 20;
            if (newH < 20) newH = 20;
            const topPx = parseFloat(currentCard.style.top);
            if (checkCollision(topPx, newH, currentCard.dataset.id)) return;
            if (topPx + newH > 640) newH = 640 - topPx;
            currentCard.style.height = newH + 'px';
            const newDur = parseInt(currentCard.getAttribute('data-menit'));
            currentCard.setAttribute('data-menit', newDur);
            updateTimeDisplay(currentCard, topPx, newDur);
        });

        document.addEventListener('mouseup', () => { 
            if (isResizing) {
                const taskId = currentCard.dataset.id;
                const topPx = parseFloat(currentCard.style.top);
                // Kita perlu update DB saat resize selesai, tapi durasi juga dikirim
                const totalMinStart = (topPx / 80) * 60;
                const duration = (parseFloat(currentCard.style.height) / 80) * 60;
                axios.post("{{ route('hc.updateSchedule') }}", { id: taskId, top: totalMinStart, menit: duration });
            }
            isResizing = false; 
            document.body.classList.remove('resizing'); 
        });

        function updateTimeDisplay(item, topPx, duration) {
            const durationMinutes = parseInt(duration) || 0;
            const startTotal = (9 * 60) + Math.round((topPx / 80) * 60);
            const endTotal = startTotal + durationMinutes;
            const format = (m) => `${Math.floor(m / 60).toString().padStart(2, '0')}:${Math.round(m % 60).toString().padStart(2, '0')}`;
            const display = item.querySelector('.task-time-range');
            if (display) display.innerText = `${format(startTotal)} - ${format(endTotal)}`;
        }
    });

    function returnToPool(id) {
        if(confirm('Kembalikan ke Pool?')) axios.post("{{ route('hc.updateSchedule') }}", { id: id, reset: true }).then(() => location.reload());
    }
    function splitTask(id) {
        if(confirm('Split task?')) axios.post("{{ route('hc.cancelRemaining') }}", { id: id }).then(() => location.reload());
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