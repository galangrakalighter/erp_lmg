@include('partials.header')

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            {{-- Menampilkan Nama Client jika tersedia --}}
            <h6 class="m-0 font-weight-bold text-primary">Project Overview: {{ $client->nama }}</h6>
            
            @if(Auth::user()->role != "content_creator")
            <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#tambahModal">
                <i class="fas fa-plus fa-sm mr-1"></i> Tambah Task
            </button>
            @endif
        </div>

        <div class="card-body border-bottom bg-light">
            {{-- Pastikan action mengarah ke route index dengan parameter id_client --}}
            <form action="{{ route('hc_overview.index', $id) }}" method="GET">
                <div class="row">
                    {{-- Filter Baris 1 --}}
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Mulai:</label>
                        <input type="date" name="start_date" class="form-control form-control-sm border-primary" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Sampai:</label>
                        <input type="date" name="end_date" class="form-control form-control-sm border-primary" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Divisi:</label>
                        <select name="divisi" class="form-control form-control-sm border-primary">
                            <option value="">- Semua -</option>
                            @foreach($result as $divId => $nama)
                                <option value="{{ $divId }}" {{ request('divisi') == $divId ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Priority:</label>
                        <select name="priority" class="form-control form-control-sm border-primary">
                            <option value="">- Semua -</option>
                            @foreach($prioritas as $pKey => $pVal)
                                <option value="{{ $pKey }}" {{ request('priority') == $pKey ? 'selected' : '' }}>{{ $pVal }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">PIC:</label>
                        <select name="pic" class="form-control form-control-sm border-primary">
                            <option value="">- Semua PIC -</option>
                            @foreach($vic_list as $user)
                                <option value="{{ $user->id }}" {{ request('pic') == $user->id ? 'selected' : '' }}>{{ $user->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Status:</label>
                        <select name="status" class="form-control form-control-sm border-primary">
                            <option value="">- Semua -</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Not Started</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>In Progress</option>
                            <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Revisi</option>
                            <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>Minta Approval</option>
                            <option value="4" {{ request('status') === '4' ? 'selected' : '' }}>Done</option>
                        </select>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-12 d-flex justify-content-start">
                        <button type="submit" class="btn btn-sm btn-primary px-4 shadow-sm mr-2">
                            <i class="fas fa-filter fa-sm mr-1"></i> Filter
                        </button>
                        <a href="{{ route('hc_overview.index', $id) }}" class="btn btn-sm btn-secondary px-4 shadow-sm">
                            <i class="fas fa-undo fa-sm mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Task</th>
                            <th>Divisi</th>
                            <th>PIC</th>
                            <th>Priority</th>
                            <th>Durasi</th>
                            <th>Mulai</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Note</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $d)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-left">{{ $d->id_manajement_konten ? "Edit Konten: ".$d->nama_task : $d->nama_task }}</td>
                                <td>{{ $result[$d->divisi] ?? '-' }}</td>
                                <td><small>{{ $d->vic_email }}</small></td>
                                <td>
                                    @php
                                        $colors = [1 => 'success', 2 => 'warning', 3 => 'danger'];
                                        $c = $colors[$d->priority] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $c }}">{{ $prioritas[$d->priority] }}</span>
                                </td>
                                <td>{{ $d->menit }}m</td>
                                <td>{{ $d->tanggal_mulai }}</td>
                                <td>{{ $d->tanggal_deadline }}</td>
                                <td>@switch($d->status)
                                        @case(0)
                                            <span class="badge badge-secondary shadow-sm px-2">
                                                <i class="fas fa-clock mr-1"></i> Not Started
                                            </span>
                                            @break
                                        @case(1)
                                            <span class="badge badge-primary shadow-sm px-2">
                                                <i class="fas fa-spinner fa-spin mr-1"></i> In Progress
                                            </span>
                                            @break
                                        @case(2)
                                            <span class="badge badge-warning text-dark shadow-sm px-2">
                                                <i class="fas fa-exclamation-circle mr-1"></i> Revisi
                                            </span>
                                            @break
                                        @case(3)
                                            <span class="badge badge-info shadow-sm px-2">
                                                <i class="fas fa-paper-plane mr-1"></i> Minta Approval
                                            </span>
                                            @break
                                        @case(4)
                                            <span class="badge badge-success shadow-sm px-2">
                                                <i class="fas fa-check-double mr-1"></i> Done
                                            </span>
                                            @break
                                        @case(7)
                                            @if($d->tanggal_approve == null)
                                                <span class="badge badge-danger shadow-sm px-2">
                                                    <i class="fas fa-check-double mr-1"></i> Overdue
                                                </span>
                                            @else
                                                <span class="badge badge-danger shadow-sm px-2">
                                                    <i class="fas fa-check-double mr-1"></i> Done (Overdue)
                                                </span>
                                            @endif
                                            @break
                                        @case(8)
                                            <span class="badge badge-warning shadow-sm px-2">
                                                <i class="fas fa-check-double mr-1"></i> Waktu Habis
                                            </span>
                                            @break
                                        @case(9)
                                            <span class="badge badge-danger shadow-sm px-2">
                                                <i class="fas fa-paper-plane mr-1"></i> Minta Approval (Overdue)
                                            </span>
                                            @break
                                        @default
                                            <span class="badge badge-dark px-2">Unknown</span>
                                    @endswitch
                                </td>
                                <td><small>{{ $d->note }}</small></td>
                                <td>
                                    @if(!isset($d->id_manajement_konten))
                                        @if($d->status == 4)
                                            {{-- Task Done: Hanya boleh Edit (biasanya untuk catatan/lampiran) --}}
                                            <span class="badge badge-light border text-muted">Done</span>
                                        @elseif($d->status > 0 && $d->status < 4)
                                        @elseif($d->status == 7 && $d->tanggal_approve != null)
                                            <span class="badge badge-light border text-muted">Done</span>
                                        @elseif($d->status == 9 && $d->can_approve == true)
                                            {{-- <button class="btn btn-sm btn-circle btn-primary" onclick="changeStatus({{ $d->id }}, 'approve')"><i class="fas fa-check"></i></button> --}}
                                        @else
                                            {{-- Task Belum Selesai: Boleh Edit & Hapus --}}
                                            <button class="btn btn-sm btn-circle btn-warning" data-toggle="modal" onclick="getDataEdit('{{ $d->id }}')" data-target="#editModal_{{ $d->id }}"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-circle btn-danger" data-toggle="modal" data-target="#deleteModal_{{ $d->id }}"><i class="fas fa-trash"></i></button>
                                        @endif
                                    @else
                                        {{-- Jika task berasal dari Manajemen Konten --}}
                                        @if($d->status == 5)
                                            <span class="badge badge-light border text-muted">Task yang Di split</span>
                                        @elseif($d->status == 9 && $d->can_approve == true)
                                            {{-- <button class="btn btn-sm btn-circle btn-primary" onclick="changeStatus({{ $d->id }}, 'approve')"><i class="fas fa-check"></i></button> --}}
                                        @elseif($d->status == 7 && $d->tanggal_approve != null)
                                            <span class="badge badge-light border text-muted">Done</span>
                                        @else
                                            <span class="badge badge-light border text-muted">Hapus Di Manajemen Konten</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
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

            <form action="{{ route('hc_overview.store', $id) }}" method="POST">
                @csrf
                <input type="hidden" name="project" value="{{ $id }}">
                
                <div class="modal-body p-4" style="background-color: #f8f9fc;">
                    
                    <h6 class="text-primary font-weight-bold uppercase small mb-3"><i class="fas fa-info-circle mr-1"></i> Informasi Utama</h6>
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark small">NAMA TASK</label>
                        <div class="input-group shadow-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-tasks text-muted"></i></span>
                            </div>
                            <input type="text" name="nama_task" class="form-control border-left-0 pl-0" placeholder="Apa yang akan dikerjakan?" required>
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
                                @foreach($vic_list as $user)
                                    <option value="{{ $user->id }}">{{ $user->email }}</option>
                                @endforeach
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

@foreach ($data as $d)
    {{-- MODAL EDIT --}}
    <div class="modal fade" id="editModal_{{ $d->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark" style="background: linear-gradient(45deg, #f6e05e, #ecc94b);">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-edit mr-2"></i>Edit Task
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <form action="{{ route('hc_overview.update', [$id, $d->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="modal-body p-4" style="background-color: #fcfaf2;">
                        <h6 class="text-warning font-weight-bold uppercase small mb-3" style="filter: brightness(0.7);">
                            <i class="fas fa-info-circle mr-1"></i> Informasi Utama
                        </h6>
                        
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark small">NAMA TASK</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-tasks text-muted"></i></span>
                                </div>
                                <input type="text" name="nama_task" class="form-control border-left-0 pl-0" value="{{ $d->nama_task }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label class="font-weight-bold text-dark small">DIVISI</label>
                                <select name="divisi" class="form-control custom-select shadow-sm border-0" required>
                                    @foreach($result as $divId => $nama)
                                        <option value="{{ $divId }}" {{ $d->divisi == $divId ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label class="font-weight-bold text-dark small">PIC (PERSON IN CHARGE)</label>
                                <select name="pic" class="form-control custom-select shadow-sm border-0 pic-select-edit" 
                                        data-id="{{ $d->id }}" required>
                                    @foreach($vic_list as $user)
                                        <option value="{{ $user->id }}" {{ $d->pic == $user->id ? 'selected' : '' }}>{{ $user->email }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr class="my-4" style="border-top: 1px dashed #d4d4d4;">

                        <h6 class="text-warning font-weight-bold uppercase small mb-3" style="filter: brightness(0.7);">
                            <i class="fas fa-calendar-alt mr-1"></i> Penjadwalan & Waktu
                        </h6>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-dark small">TANGGAL MULAI</label>
                                <input type="date" name="tanggal_mulai" class="form-control shadow-sm border-0 tanggal-mulai-edit" 
                                    data-id="{{ $d->id }}" value="{{ $d->tanggal_mulai }}" required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-dark small">TANGGAL DEADLINE</label>
                                <input type="date" name="tanggal_deadline" class="form-control shadow-sm border-0" value="{{ $d->tanggal_deadline }}" required>
                            </div>
                        </div>

                        <div id="task-list-container-edit-{{ $d->id }}" class="mb-4 animate__animated animate__fadeIn" style="display: none;">
                            <div class="card border-0 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                <div class="card-header bg-info text-white py-2 d-flex justify-content-between align-items-center" style="background: linear-gradient(45deg, #36b9cc, #258391);">
                                    <span class="small font-weight-bold"><i class="fas fa-user-clock mr-1"></i> Jadwal PIC di Tanggal Ini</span>
                                    <span id="total-durasi-edit-{{ $d->id }}" class="badge badge-light text-info shadow-sm">
                                        Total: 0 Menit
                                    </span>
                                </div>
                                <div class="table-responsive" style="max-height: 150px; overflow-y: auto;">
                                    <table class="table table-sm table-hover mb-0 bg-white" style="font-size: 11px;">
                                        <thead class="text-muted" style="background-color: #f1f3f5;">
                                            <tr>
                                                <th class="border-0 pl-3">Nama Task</th>
                                                <th class="border-0">Project</th>
                                                <th class="border-0">Waktu</th>
                                                <th class="border-0">Durasi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="task-list-body-edit-{{ $d->id }}"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-dark small">JAM MULAI</label>
                                <input type="time" name="jam_mulai" id="jam_mulai_edit_{{ $d->id }}" 
                                    class="form-control shadow-sm border-0" value="{{ $d->jam_mulai }}" 
                                    onchange="hitungMenitEdit({{ $d->id }})" required>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-dark small">JAM SELESAI</label>
                                <input type="time" name="jam_selesai" id="jam_selesai_edit_{{ $d->id }}" 
                                    class="form-control shadow-sm border-0" value="{{ $d->jam_selesai }}" 
                                    onchange="hitungMenitEdit({{ $d->id }})" required>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-dark small">ESTIMASI</label>
                                <div class="input-group shadow-sm">
                                    <input type="number" name="menit" id="menit_edit_{{ $d->id }}" class="form-control bg-white font-weight-bold text-primary border-0" value="{{ $d->menit }}" readonly>
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
                                        <option value="{{ $pId }}" {{ $d->priority == $pId ? 'selected' : '' }}>{{ $pName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark small text-uppercase">Link ke Jira</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0">
                                        <i class="fas fa-link text-muted"></i>
                                    </span>
                                </div>
                                <input type="url" 
                                    name="jira_input" 
                                    class="form-control border-left-0 pl-0" 
                                    placeholder="https://jira.atlassian.com/..." 
                                    pattern="https?://.*" 
                                    title="Harap masukkan format URL yang valid (dimulai dengan http:// atau https://)"
                                    value="{{ $d->jira }}">
                            </div>
                            <small class="form-text text-muted">Pastikan menyertakan http:// atau https://</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark small">CATATAN</label>
                            <textarea name="note" class="form-control shadow-sm border-0" rows="3" style="border-radius: 8px;" placeholder="Tambahkan catatan khusus...">{{ $d->note }}</textarea>
                        </div>
                    </div>

                    <div class="modal-footer bg-white border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light font-weight-bold px-4 text-muted" data-dismiss="modal">BATAL</button>
                        <button type="submit" class="btn btn-warning shadow font-weight-bold px-5" style="border-radius: 8px; color: #333;">
                            <i class="fas fa-save mr-2"></i>PERBARUI TASK
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL DELETE --}}
    <div class="modal fade" id="deleteModal_{{ $d->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {{-- Sesuaikan Route Destroy: Butuh id_client dan id task --}}
                <form action="{{ route('hc_overview.destroy', [$id, $d->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body text-center p-4">
                        <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                        <h5>Hapus Task?</h5>
                        <p class="text-muted small">{{ $d->nama_task }}</p>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
/**
 * Helper: Memformat menit ke format "X Jam X Menit"
 */
// function hitungJamSelesaiManual(jamMulai, durasiMenit) {
//     if (!jamMulai) return "00:00";
//     const [jam, menit] = jamMulai.split(':').map(Number);
//     const totalMenit = (jam * 60) + menit + parseInt(durasiMenit || 0);
//     console.log(totalMenit);
    
//     const h = Math.floor(totalMenit / 60);
//     const m = totalMenit % 60;
//     return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
// }

// function formatDurasi(menit) {
//     if (!menit || menit <= 0) return "0 Menit";
//     let jam = Math.floor(menit / 60);
//     let sisaMenit = menit % 60;
//     if (jam > 0) {
//         return sisaMenit > 0 ? `${jam} Jam ${sisaMenit} Menit` : `${jam} Jam`;
//     } else {
//         return `${menit} Menit`;
//     }
// }

function changeStatus(id, action) {
    let jamMulaiStr = null;
    axios.post("{{ str_replace('http://', 'https://', route('hc.updateStatus')) }}", { id: id, action: action, jam_mulai: jamMulaiStr }).then(() => location.reload());
}

function getDataEdit(id){
    const inputMulai = document.getElementById('jam_mulai_edit_' + id);
    const inputMenit = document.getElementById('menit_edit_' + id);
    const inputSelesai = document.getElementById('jam_selesai_edit_' + id);

    if (inputMulai && inputMenit && inputSelesai) {
        const jamMulaiVal = inputMulai.value; // Contoh: "08:00"
        const menitVal = parseInt(inputMenit.value) || 0;

        if (jamMulaiVal && menitVal > 0) {
            // Pecah jam dan menit secara manual (Lebih aman dari NaN)
            let [hours, minutes] = jamMulaiVal.split(':').map(Number);
            
            let date = new Date();
            date.setHours(hours);
            date.setMinutes(minutes + menitVal);

            // Format kembali ke HH:mm
            const h = String(date.getHours()).padStart(2, '0');
            const m = String(date.getMinutes()).padStart(2, '0');

            inputSelesai.value = `${h}:${m}`;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = window.location.origin;

    // --- LOGIKA MODAL TAMBAH ---
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

    // Event Listeners Modal Tambah
    if(inputPic) inputPic.addEventListener('change', () => { fetchTaskByPic(); calculateMinutes(); });
    if(inputTanggal) inputTanggal.addEventListener('change', fetchTaskByPic);
    if(inputMulai) inputMulai.addEventListener('change', calculateMinutes);
    if(inputSelesai) inputSelesai.addEventListener('change', calculateMinutes);


    // --- LOGIKA MODAL EDIT (Delegasi Event) ---
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('pic-select-edit') || e.target.classList.contains('tanggal-mulai-edit')) {
            
            const id = e.target.getAttribute('data-id'); // ID Row/Task dari database
            const pic = document.querySelector(`.pic-select-edit[data-id="${id}"]`).value;
            const tanggal = document.querySelector(`.tanggal-mulai-edit[data-id="${id}"]`).value;

            if (pic && tanggal) {
                const containerTotal = document.getElementById(`total-durasi-edit-${id}`);
                const tableBody = document.getElementById(`task-list-body-edit-${id}`);
                const cardContainer = document.getElementById(`task-list-container-edit-${id}`);

                tableBody.innerHTML = '<tr><td colspan="3" class="text-center p-2 text-muted italic">Mengecek...</td></tr>';

                fetch(`${baseUrl}/api/get-tasks-by-date?tanggal=${tanggal}&pic=${pic}`)
                    .then(response => response.json())
                    .then(data => {
                        tableBody.innerHTML = '';
                        let totalMenitAkumulasi = 0;

                        if (data.length > 0) {
                            if(cardContainer) cardContainer.style.display = 'block';
                            
                            data.forEach(task => {
                                let durasiTask = parseInt(task.menit || 0);
                                totalMenitAkumulasi += durasiTask;

                                let jamM = task.jam_mulai ? task.jam_mulai.substring(0, 5) : "00:00";
                                let jamS = (task.jam_selesai && task.jam_selesai !== "00:00:00") 
                                           ? task.jam_selesai.substring(0, 5) 
                                           : hitungJamSelesaiManual(jamM, durasiTask);
                                console.log(task);
                                tableBody.innerHTML += `
                                    <tr>
                                        <td>${task.nama_task}</td>
                                        <td>${task.nama}</td>
                                        <td><span class="badge badge-secondary">${jamM} - ${jamS}</span></td>
                                        <td>${formatDurasi(durasiTask)}</td>
                                    </tr>`;
                            });

                            // Update label total durasi di header card modal edit
                            if(containerTotal) {
                                containerTotal.innerText = "Total Jam: " + formatDurasi(totalMenitAkumulasi);
                            }
                        } else {
                            if(cardContainer) cardContainer.style.display = 'none';
                            if(containerTotal) containerTotal.innerText = "Total: 0 Menit";
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        tableBody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Gagal memuat data</td></tr>';
                    });
            }
        }
    });
});

function hitungMenitEdit(id) {
    const pic = document.querySelector(`.pic-select-edit[data-id="${id}"]`).value;
    const inputMulai = document.getElementById('jam_mulai_edit_' + id);
    const inputSelesai = document.getElementById('jam_selesai_edit_' + id);
    const inputMenit = document.getElementById('menit_edit_' + id);

    if (inputMulai.value) {
        const jamM = parseInt(inputMulai.value.split(':')[0]);
        let limitMulai = (pic == "39") ? 8 : 9;
        if (jamM < limitMulai) {
            alert(`PIC ini minimal mulai jam 0${limitMulai}:00`);
            inputMulai.value = ""; return;
        }
    }

    if (inputSelesai.value) {
        const [jamS, menitS] = inputSelesai.value.split(':').map(Number);
        if (jamS > 17 || (jamS === 17 && menitS > 0)) {
            alert("Jam selesai maksimal jam 17:00");
            inputSelesai.value = ""; return;
        }
    }

    if (inputMulai.value && inputSelesai.value) {
        const start = new Date("1970-01-01T" + inputMulai.value);
        const end = new Date("1970-01-01T" + inputSelesai.value);

        if (end <= start) {
            alert("Jam selesai harus lebih besar dari jam mulai!");
            inputSelesai.value = '';
            inputMenit.value = '';
            return;
        }

        const diff = (end - start) / (1000 * 60);
        inputMenit.value = diff;
    }
}
</script>

@include('partials.footer')