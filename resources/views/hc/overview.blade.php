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
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
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
                                                <i class="fas fa-clock mr-1"></i> Pending
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
                                        @else
                                            {{-- Task Belum Selesai: Boleh Edit & Hapus --}}
                                            <button class="btn btn-sm btn-circle btn-warning" data-toggle="modal" data-target="#editModal_{{ $d->id }}"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-circle btn-danger" data-toggle="modal" data-target="#deleteModal_{{ $d->id }}"><i class="fas fa-trash"></i></button>
                                        @endif
                                    @else
                                        {{-- Jika task berasal dari Manajemen Konten --}}
                                        @if($d->status == 5)
                                            <span class="badge badge-light border text-muted">Task yang Di split</span>
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
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold">Tambah Task Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('hc_overview.store', $id) }}" method="POST">
                @csrf
                {{-- Input hidden agar project ID otomatis terisi sesuai halaman --}}
                <input type="hidden" name="project" value="{{ $id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small">Nama Task</label>
                        <input type="text" name="nama_task" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Divisi</label>
                            <select name="divisi" class="form-control" required>
                                @foreach($result as $divId => $nama)
                                    <option value="{{ $divId }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">PIC</label>
                            <select name="pic" class="form-control" required>
                                @foreach($vic_list as $user)
                                    <option value="{{ $user->id }}">{{ $user->email }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Prioritas</label>
                            <select name="priority" class="form-control" required>
                                @foreach($prioritas as $pId => $pName)
                                    <option value="{{ $pId }}">{{ $pName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Estimasi (Menit)</label>
                            <input type="number" name="menit" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Tgl Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Tgl Deadline</label>
                            <input type="date" name="tanggal_deadline" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Catatan</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary btn-block">Simpan Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach ($data as $d)
    {{-- MODAL EDIT --}}
    <div class="modal fade" id="editModal_{{ $d->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title font-weight-bold">Edit Task</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                {{-- Sesuaikan Route Update: Butuh id_client dan id task --}}
                <form action="{{ route('hc_overview.update', [$id, $d->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="form-group mb-4">
                            <label class="text-dark font-weight-bold small uppercase">Nama Task</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-tasks text-muted"></i></span>
                                </div>
                                <input type="text" name="nama_task" class="form-control border-left-0 bg-light" value="{{ $d->nama_task }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="text-dark font-weight-bold small uppercase">Divisi</label>
                                <select name="divisi" class="form-control custom-select bg-light" required>
                                    @foreach($result as $id => $nama)
                                        <option value="{{ $id }}" {{ $d->divisi == $id ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="text-dark font-weight-bold small uppercase">PIC (Person in Charge)</label>
                                <select name="pic" class="form-control custom-select bg-light" required>
                                    @foreach($vic_list as $user)
                                        <option value="{{ $user->id }}" {{ $d->pic == $user->id ? 'selected' : '' }}>{{ $user->email }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="text-dark font-weight-bold small uppercase">Prioritas</label>
                                <select name="priority" class="form-control custom-select bg-light" required>
                                    @foreach($prioritas as $pId => $pName)
                                        <option value="{{ $pId }}" {{ $d->priority == $pId ? 'selected' : '' }}>{{ $pName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="text-dark font-weight-bold small uppercase">Durasi (Menit)</label>
                                <div class="input-group">
                                    <input type="number" name="menit" class="form-control bg-light" value="{{ $d->menit }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-light text-muted small">min</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="text-dark font-weight-bold small uppercase">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control bg-light" value="{{ $d->tanggal_mulai }}" required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="text-dark font-weight-bold small uppercase">Tanggal Deadline</label>
                                <input type="date" name="tanggal_deadline" class="form-control bg-light" value="{{ $d->tanggal_deadline }}" required>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="text-dark font-weight-bold small uppercase">Catatan Tambahan</label>
                            <textarea name="note" class="form-control bg-light" rows="3">{{ $d->note }}</textarea>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-0 px-4">
                        <button type="button" class="btn btn-link text-muted font-weight-bold" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info px-4 shadow-sm font-weight-bold">
                            <i class="fas fa-save mr-2"></i>Perbarui Task
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

@include('partials.footer')