@include('partials.header')
<div class="container-fluid mt-4">
{{-- NOTIFIKASI --}}
    <div class="card shadow">
        {{-- CARD HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Manajemen Konten {{ $produk->nama }}</h6>
            {{-- <a href="{{ route('create_produk_jasa') }}" class="btn btn-sm btn-primary">
                Tambah
            </a> --}}
            @if(Auth::user()->role != "content_creator")
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#tambahModal">
                Tambah
            </button>
            @endif
        </div>

        <div class="card-body">
            <form action="{{ url()->current() }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small font-weight-bold">Dari Tanggal:</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold">Sampai Tanggal:</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ url()->current() }}" class="btn btn-secondary">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
        {{-- CARD BODY --}}
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center" id="dataTable">
                    <thead class="thead-light text-nowrap">
                        <tr>
                            <th>No</th>
                            <th>PIC</th>
                            <th>Judul</th>
                            <th>Inspirasi</th>
                            {{-- <th>Prioritas</th> --}}
                            <th>Type</th>
                            <th>Strategi</th>
                            <th>Pilar</th>
                            <th>Hook</th>
                            <th>Body</th>
                            <th>CTA</th>
                            {{-- <th>Durasi</th> --}}
                            @if($kategory->id == 2)
                            <th>Warna</th>
                            @endif
                            <th>Komposisi</th>
                            <th>Note</th>
                            <th>Tanggal Posting (Y-M-D)</th>
                            {{-- <th>Tanggal Mulai (Y-M-D)</th>
                            <th>Tanggal Deadline (Y-M-D)</th>
                            <th>Durasi Mengerjakan</th> --}}
                            <th>Link</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr>
                                <th>{{ $loop->iteration }}</th>
                                <th>{{ $item->vic_email }}</th>
                                <th>{{ $item->judul }}</th>
                                <th><a href="{{ $item->inspirasi }}">{{ $item->inspirasi }}</a></th>
                                @php
                                    $prioData = [
                                        1 => ['class' => 'badge-info', 'label' => 'Rendah'],
                                        2 => ['class' => 'badge-warning text-dark', 'label' => 'Sedang'],
                                        3 => ['class' => 'badge-danger', 'label' => 'Tinggi']
                                    ][$item->priority] ?? ['class' => 'badge-light', 'label' => 'N/A'];
                                @endphp
                                {{-- <th><span class="badge {{ $prioData['class'] }} shadow-sm px-3">{{ $prioData['label'] }}</span></th> --}}
                                <th>{{ $item->type }}</th>
                                <th>{{ $item->strategy }}</th>
                                <th>{{ $item->pilar }}</th>
                                <th>{{ $item->hooks }}</th>
                                <th>{{ $item->nama_body }}</th>
                                <th>{{ $item->nama_cta }}</th>
                                {{-- <th>{{ $item->durasi }}</th> --}}
                                @if($kategory->id == 2)
                                <th>{{ $item->background }}</th>
                                @endif
                                <th>{{ $item->komposisi }}</th>
                                <th>{{ $item->note }}</th>
                                <th>{{ $item->tanggal_posting }}</th>
                                {{-- <th>{{ $item->tanggal_mulai }}</th>
                                <th>{{ $item->tanggal_deadline }}</th> --}}
                                {{-- <th>{{ $item->menit ? $item->menit . " Menit" : ""}}</th> --}}
                                <th><a href="{{ $item->link }}">{{ $item->link }}</a></th>
                                <th>{{ $item->nama_status }}</th>
                                <th>
                                    <button type="button" class="btn btn-warning" data-toggle="modal" onclick="getDataEdit('{{ $item->id }}')" data-target="#editModal_{{ $item->id }}"><i class="fas fa-edit"></i></button>
                                    @if(Auth::user()->role != "content_creator")
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal_{{ $item->id }}"><i class="fas fa-trash"></i></button>
                                    @endif
                                </th>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div class="modal fade" id="tambahModal" tabindex="-1" role="dialog" aria-labelledby="tambahModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <form action="{{ route('manajemen_create', $produk->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="platform" value="{{ $platform }}">
                    
                    <style>
                        #tambahModal .form-control {
                            border-radius: 8px;
                            transition: all 0.3s ease;
                        }
                        #tambahModal .form-control:focus {
                            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1);
                            border-color: #4e73df;
                        }
                        #tambahModal .input-group-text {
                            border-radius: 8px 0 0 8px;
                            background-color: #f8f9fc;
                        }
                        #tambahModal .section-title h6 {
                            letter-spacing: 0.5px;
                            text-transform: uppercase;
                            font-size: 0.75rem;
                        }
                        #tambahModal .modal-header {
                            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
                        }
                        #tambahModal .btn-primary {
                            border-radius: 8px;
                            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
                            border: none;
                        }
                    </style>

                    <div class="modal-header text-white py-3 px-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                                <i class="fas fa-plus text-primary"></i>
                            </div>
                            <div>
                                <h5 class="modal-title font-weight-bold" id="tambahModalLabel">Tambah Produk Jasa Baru</h5>
                                <small class="text-white-50">Lengkapi formulir untuk menambahkan konten baru</small>
                            </div>
                        </div>
                        <button type="button" class="close text-white outline-none" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body p-4" style="background-color: #fcfcfd;">
                        
                        <div class="section-title mb-3 d-flex align-items-center">
                            <div class="bg-primary mr-2" style="width: 4px; height: 16px; border-radius: 2px;"></div>
                            <h6 class="text-primary font-weight-bold mb-0">Informasi Dasar</h6>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Judul Konten <span class="text-danger">*</span></label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text border-right-0"><i class="fas fa-heading text-muted"></i></span>
                                        </div>
                                        <input type="text" name="judul" class="form-control border-left-0" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Inspirasi / Referensi</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text border-right-0"><i class="fas fa-link text-muted"></i></span>
                                        </div>
                                        <input type="text" name="inspirasi" class="form-control border-left-0">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">PIC (Penanggung Jawab) <span class="text-danger">*</span></label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text border-right-0"><i class="fas fa-user-tag text-muted"></i></span>
                                        </div>
                                        <select name="pic" id="pic_select" class="form-control border-left-0" required>
                                            <option value="" disabled selected>-- Pilih Penanggung Jawab --</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->email }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Prioritas <span class="text-danger">*</span></label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text border-right-0"><i class="fas fa-exclamation-circle text-muted"></i></span>
                                        </div>
                                        <select name="prioritas" class="form-control border-left-0" required>
                                            <option value="1">🟢 Rendah</option>
                                            <option value="2" selected>🟡 Sedang</option>
                                            <option value="3">🔴 Tinggi</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mb-3 d-flex align-items-center mt-4">
                            <div class="bg-primary mr-2" style="width: 4px; height: 16px; border-radius: 2px;"></div>
                            <h6 class="text-primary font-weight-bold mb-0">Strategi Konten</h6>
                        </div>

                        <div class="card border-0 shadow-sm rounded-lg mb-4" style="background: #ffffff; border: 1px solid #e3e6f0 !important;">
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Tipe Produk <span class="text-danger">*</span></label>
                                            <select name="type" class="form-control custom-select bg-light border-0 shadow-none" required>
                                                <option value="" disabled selected>-- Pilih Tipe --</option>
                                                @foreach ($option['type_produk'] as $type)
                                                    <option value="{{ $type->id }}">{{ $type->type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Strategi <span class="text-danger">*</span></label>
                                            <select name="strategy" class="form-control custom-select bg-light border-0 shadow-none" required>
                                                <option value="" disabled selected>-- Pilih Strategi --</option>
                                                @foreach ($option['strategy'] as $strategy)
                                                    <option value="{{ $strategy->id }}">{{ $strategy->strategy }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Pilar <span class="text-danger">*</span></label>
                                            <select name="pilar" class="form-control custom-select bg-light border-0 shadow-none" required>
                                                <option value="" disabled selected>-- Pilih Pilar --</option>
                                                @foreach ($option['pilar'] as $pilar)
                                                    <option value="{{ $pilar->id }}">{{ $pilar->pilar }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Hook <span class="text-danger">*</span></label>
                                            <select name="hooks" class="form-control custom-select bg-light border-0 shadow-none" required>
                                                <option value="" disabled selected>-- Pilih Hook --</option>
                                                @foreach ($option['hooks'] as $hooks)
                                                    <option value="{{ $hooks->id }}">{{ $hooks->hooks }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Jenis Body <span class="text-danger">*</span></label>
                                            <select name="jenis_body" class="form-control custom-select bg-light border-0 shadow-none" required>
                                                <option value="" disabled selected>-- Pilih Jenis Body --</option>
                                                @foreach ($option['jenis_body'] as $jenis_body)
                                                    <option value="{{ $jenis_body->id }}">{{ $jenis_body->nama_body }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-md-0">
                                            <label class="text-dark font-weight-bold small">Jenis CTA <span class="text-danger">*</span></label>
                                            <select name="jenis_cta" class="form-control custom-select bg-light border-0 shadow-none" required>
                                                <option value="" disabled selected>-- Pilih Jenis CTA --</option>
                                                @foreach ($option['jenis_cta'] as $jenis_cta)
                                                    <option value="{{ $jenis_cta->id }}">{{ $jenis_cta->nama_cta }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="form-group mb-0">
                                            <label class="text-dark font-weight-bold small">Durasi</label>
                                            <input type="text" name="durasi" class="form-control bg-light border-0" placeholder="60s">
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="form-group mb-0">
                                            <label class="text-dark font-weight-bold small">Komposisi</label>
                                            <input type="text" name="komposisi" class="form-control bg-light border-0" placeholder="9:16">
                                        </div>
                                    </div>
                                    @if($kategory->id == 2)
                                    <div class="col-md-6 col-12 mt-3 mt-md-3">
                                        <div class="form-group mb-0">
                                            <label class="text-dark font-weight-bold small">Warna Identitas</label>
                                            <div class="input-group shadow-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-palette text-muted"></i></span>
                                                </div>
                                                <input type="text" name="warna" class="form-control border-left-0" placeholder="#ffffff">
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="section-title mb-3 d-flex align-items-center mt-4">
                            <div class="bg-primary mr-2" style="width: 4px; height: 16px; border-radius: 2px;"></div>
                            <h6 class="text-primary font-weight-bold mb-0">Jadwal & Status</h6>
                        </div>

                        <div class="row">
                            {{-- <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Estimasi Durasi (Menit) <span class="text-danger">*</span></label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-stopwatch text-warning"></i></span>
                                        </div>
                                        <input type="number" name="menit" id="estimasi_menit" class="form-control border-left-0" min="1" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light border-left-0 small font-weight-bold">MENIT</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Jam Mulai</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-clock text-info"></i></span>
                                        </div>
                                        <input type="time" name="jam_mulai" id="jam_mulai" class="form-control border-left-0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Jam Selesai</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-history text-info"></i></span>
                                        </div>
                                        <input type="time" name="jam_selesai" id="jam_selesai" class="form-control border-left-0">
                                    </div>
                                </div>
                            </div> --}}
                            {{-- <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Tanggal Mulai</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-play-circle text-success"></i></span>
                                        </div>
                                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control border-left-0">
                                    </div>
                                </div>
                            </div> --}}
                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Tanggal Posting</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="far fa-calendar-check text-muted"></i></span>
                                        </div>
                                        <input type="date" name="tanggal_posting" class="form-control border-left-0">
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Tanggal Deadline</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="far fa-calendar-times text-danger"></i></span>
                                        </div>
                                        <input type="date" name="tanggal_deadline" class="form-control border-left-0">
                                    </div>
                                </div>
                            </div> --}}
                            
                            {{-- <div id="task-list-container" class="col-md-12 mb-4" style="display: none;">
                                <div class="card border-info shadow-sm">
                                    <div class="card-header bg-info text-white py-2 px-3 d-flex justify-content-between align-items-center">
                                        <small class="font-weight-bold"><i class="fas fa-list-ul mr-1"></i> Jadwal Task PIC</small>
                                        <span id="total-durasi-container" class="badge badge-light text-info px-2 py-1">
                                            Total: 0 Menit
                                        </span>
                                    </div>
                                    <div class="table-responsive" style="max-height: 150px; overflow-y: auto;">
                                        <table class="table table-sm table-hover mb-0" style="font-size: 11px;">
                                            <thead class="bg-light text-muted">
                                                <tr>
                                                    <th class="px-3">NAMA TASK</th>
                                                    <th>PROJECT</th>
                                                    <th>WAKTU</th>
                                                    <th>DURASI</th>
                                                </tr>
                                            </thead>
                                            <tbody id="task-list-body"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Link Upload <span class="text-danger">*</span></label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fab fa-google-drive text-primary"></i></span>
                                        </div>
                                        <input type="text" name="link" class="form-control border-left-0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Status Progress <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control custom-select shadow-sm" style="border-radius: 8px;" required>
                                        <option value="" disabled selected>-- Pilih Status --</option>
                                        @foreach ($option['status'] as $status)
                                            <option value="{{ $status->id }}">{{ $status->nama_status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="text-dark font-weight-bold small">Catatan Tambahan</label>
                                    <textarea name="note" class="form-control shadow-sm" rows="3" placeholder="Tambahkan instruksi khusus di sini..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-0 py-3 px-4">
                        <button type="button" class="btn btn-link text-muted px-4 font-weight-bold" data-dismiss="modal" style="text-decoration: none;">Batal</button>
                        <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow">
                            <i class="fas fa-save mr-2"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    @foreach ($data as $item)
    <div class="modal fade" id="editModal_{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <form action="{{ route('manajemen_update', $item->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_project" value="{{ $produk->id }}">
                    <input type="hidden" name="platform" value="{{ $item->platform }}">

                    <div class="modal-header bg-gradient-warning text-white py-3 border-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                                <i class="fas fa-edit text-warning" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title font-weight-bold mb-0">Edit Konten Jasa</h5>
                                <small class="text-white-50">ID Konten: #{{ $item->id }}</small>
                            </div>
                        </div>
                        <button type="button" class="close text-white outline-none" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body p-4" style="background-color: #fcfcfd; max-height: 75vh; overflow-y: auto;">
                        
                        <div class="section-title mb-3 d-flex align-items-center">
                            <div class="bg-warning mr-2" style="width: 4px; height: 20px; border-radius: 10px;"></div>
                            <h6 class="text-dark font-weight-bold mb-0">Informasi Dasar</h6>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-muted font-weight-bold small uppercase">Judul Konten <span class="text-danger">*</span></label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-heading text-muted"></i></span>
                                        </div>
                                        <input type="text" name="judul" class="form-control border-left-0 pl-0 font-weight-bold" value="{{ $item->judul }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }} required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-muted font-weight-bold small uppercase">Inspirasi / Referensi</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-link text-muted"></i></span>
                                        </div>
                                        <input type="text" name="inspirasi" class="form-control border-left-0 pl-0" value="{{ $item->inspirasi }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }}>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted font-weight-bold small uppercase">PIC (Person In Charge)</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-user-tag text-muted"></i></span>
                                        </div>
                                        <select name="pic" class="form-control border-left-0 pl-0 bg-white pic-select-edit" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }} data-id="{{ $item->id }}">
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $item->pic == $user->id ? 'selected' : '' }}>{{ $user->email }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted font-weight-bold small uppercase">Prioritas <span class="text-danger">*</span></label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-exclamation-circle text-muted"></i></span>
                                        </div>
                                        <select name="prioritas" class="form-control border-left-0 pl-0 bg-white" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }} required>
                                            <option value="1" {{ $item->priority == 1 ? 'selected' : '' }}>🟢 Rendah</option>
                                            <option value="2" {{ $item->priority == 2 ? 'selected' : '' }}>🟡 Sedang</option>
                                            <option value="3" {{ $item->priority == 3 ? 'selected' : '' }}>🔴 Tinggi</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mb-3 d-flex align-items-center mt-4">
                            <div class="bg-warning mr-2" style="width: 4px; height: 20px; border-radius: 10px;"></div>
                            <h6 class="text-dark font-weight-bold mb-0">Strategi Konten</h6>
                        </div>
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #fff;">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="text-muted font-weight-bold small">Tipe Produk</label>
                                        <select name="type" class="form-control custom-select bg-light border-0 shadow-none" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                            @foreach ($option['type_produk'] as $type)
                                                <option value="{{ $type->id }}" {{ $item->type_produk_id == $type->id ? 'selected' : '' }}>{{ $type->type }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="text-muted font-weight-bold small">Strategi</label>
                                        <select name="strategy" class="form-control custom-select bg-light border-0 shadow-none" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                            @foreach ($option['strategy'] as $strategy)
                                                <option value="{{ $strategy->id }}" {{ $item->strategy_id == $strategy->id ? 'selected' : '' }}>{{ $strategy->strategy }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="text-muted font-weight-bold small">Pilar</label>
                                        <select name="pilar" class="form-control custom-select bg-light border-0 shadow-none" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                            @foreach ($option['pilar'] as $pilar)
                                                <option value="{{ $pilar->id }}" {{ $item->pilar_id == $pilar->id ? 'selected' : '' }}>{{ $pilar->pilar }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted font-weight-bold small">Hook</label>
                                        <select name="hooks" class="form-control custom-select bg-light border-0 shadow-none" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                            @foreach ($option['hooks'] as $hooks)
                                                <option value="{{ $hooks->id }}" {{ $item->hooks_id == $hooks->id ? 'selected' : '' }}>{{ $hooks->hooks }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted font-weight-bold small">Jenis Body</label>
                                        <select name="jenis_body" class="form-control custom-select bg-light border-0 shadow-none" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                            @foreach ($option['jenis_body'] as $jenis_body)
                                                <option value="{{ $jenis_body->id }}" {{ $item->body_id == $jenis_body->id ? 'selected' : '' }}>{{ $jenis_body->nama_body }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted font-weight-bold small">Jenis CTA</label>
                                        <select name="jenis_cta" class="form-control custom-select bg-light border-0 shadow-none" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                            @foreach ($option['jenis_cta'] as $jenis_cta)
                                                <option value="{{ $jenis_cta->id }}" {{ $item->cta_id == $jenis_cta->id ? 'selected' : '' }}>{{ $jenis_cta->nama_cta }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="text-muted font-weight-bold small">Durasi</label>
                                        <input type="text" name="durasi" class="form-control bg-light border-0 shadow-none" value="{{ $item->durasi }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }}>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="text-muted font-weight-bold small">Komposisi</label>
                                        <input type="text" name="komposisi" class="form-control bg-light border-0 shadow-none" value="{{ $item->komposisi }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }}>
                                    </div>
                                    
                                    @if($kategory->id == 2)
                                    <div class="col-md-12 mt-3">
                                        <label class="text-muted font-weight-bold small">Warna Identitas</label>
                                        <div class="input-group shadow-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-palette text-muted"></i></span>
                                            </div>
                                            <input type="text" name="warna" class="form-control border-left-0 pl-0" value="{{ $item->background }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }}>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="section-title mb-3 d-flex align-items-center mt-4">
                            <div class="bg-warning mr-2" style="width: 4px; height: 20px; border-radius: 10px;"></div>
                            <h6 class="text-dark font-weight-bold mb-0">Jadwal & Status</h6>
                        </div>
                        
                        <div class="row">
                            {{-- <div class="col-md-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="text-muted font-weight-bold small uppercase">Estimasi Mengerjakan (Menit)</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-stopwatch text-warning"></i></span>
                                        </div>
                                        <input type="number" name="menit" id="menit_edit_{{ $item->id }}" class="form-control border-left-0 border-right-0" value="{{ $item->menit ?? 0 }}" min="1" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-white">Menit</span>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}

                            {{-- <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-muted font-weight-bold small uppercase">Tanggal Mulai</label>
                                    <input type="date" name="tanggal_mulai" class="form-control shadow-sm tanggal-mulai-edit" data-id="{{ $item->id }}" value="{{ $item->tanggal_mulai }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }} required>
                                </div>
                            </div> --}}

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-muted font-weight-bold small uppercase">Tanggal Posting</label>
                                    <input type="date" name="tanggal_posting" class="form-control shadow-sm" value="{{ $item->tanggal_posting }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }}>
                                </div>
                            </div>

                            {{-- <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-muted font-weight-bold small uppercase">Tanggal Deadline</label>
                                    <input type="date" name="tanggal_deadline" class="form-control shadow-sm" value="{{ $item->tanggal_deadline }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }} required>
                                </div>
                            </div> --}}

                            {{-- <div class="col-md-6 mb-3">
                                <label class="text-muted font-weight-bold small uppercase">Jam Mulai</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="far fa-clock text-muted"></i></span>
                                    </div>
                                    <input type="time" name="jam_mulai" id="jam_mulai_edit_{{ $item->id }}" class="form-control border-left-0" value="{{ $item->jam_mulai }}" onchange="hitungMenitEdit({{ $item->id }})" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted font-weight-bold small uppercase">Jam Selesai (Otomatis/Manual)</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-flag-checkered text-muted"></i></span>
                                    </div>
                                    <input type="time" name="jam_selesai" id="jam_selesai_edit_{{ $item->id }}" 
                                        class="form-control border-left-0" 
                                        onchange="hitungMenitEdit({{ $item->id }})" required>
                                </div>
                            </div> --}}

                            <div class="col-md-12">
                                {{-- <div id="task-list-container-edit-{{ $item->id }}" class="mb-4" style="display: none;">
                                    <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 10px;">
                                        <div class="card-header bg-info text-white py-2 px-3 d-flex justify-content-between align-items-center">
                                            <small class="font-weight-bold"><i class="fas fa-calendar-alt mr-1"></i> Jadwal PIC di Tanggal Ini</small>
                                            <span id="total-durasi-edit-{{ $item->id }}" class="badge badge-warning text-dark px-2">Total: 0 Menit</span>
                                        </div>
                                        <div class="table-responsive" style="max-height: 150px;">
                                            <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Task</th><th>Project</th><th>Waktu</th><th>Durasi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="task-list-body-edit-{{ $item->id }}"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>

                            <div class="col-md-12 mt-2">
                                <div class="form-group">
                                    <label class="text-muted font-weight-bold small uppercase">Link Lampiran (Output)</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-external-link-alt text-muted"></i></span>
                                        </div>
                                        <input type="text" name="link" class="form-control border-left-0 pl-0" placeholder="https://..." value="{{ $item->link }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-muted font-weight-bold small uppercase">Status Pengerjaan <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control shadow-sm" style="border: 2px solid #ffc107; border-radius: 8px;" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                        @foreach ($option['status'] as $status)
                                            <option value="{{ $status->id }}" {{ $item->status_id == $status->id ? 'selected' : '' }}>{{ $status->nama_status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="text-muted font-weight-bold small uppercase">Catatan Revisi / Tambahan</label>
                                    <textarea name="note" class="form-control shadow-sm" rows="3" placeholder="Tulis catatan di sini..." style="border-radius: 10px;" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }}>{{ $item->note }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-0 py-3">
                        <button type="button" class="btn btn-link text-muted font-weight-bold shadow-none" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-warning text-white px-5 font-weight-bold shadow" style="border-radius: 8px;">
                            <i class="fas fa-save mr-2"></i> Update Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Hapus --}}
    <div class="modal fade" id="deleteModal_{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <form action="{{ route('manajemen_delete', $item->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-5 text-center">
                        <!-- Icon Peringatan Besar -->
                        <div class="mb-4">
                            <div class="bg-light-danger d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background-color: #fff5f5;">
                                <i class="fas fa-exclamation-triangle text-danger fa-2x animate__animated animate__pulse animate__infinite"></i>
                            </div>
                        </div>
                        
                        <h4 class="font-weight-bold text-dark">Hapus Konten?</h4>
                        <p class="text-muted">Apakah Anda yakin ingin menghapus konten:</p>
                        <div class="bg-light p-3 rounded-lg mb-4 border">
                            <strong class="text-primary">{{ $item->judul }}</strong>
                        </div>
                        <p class="small text-danger italic"><i class="fas fa-info-circle mr-1"></i> Tindakan ini tidak dapat dibatalkan.</p>
                        
                        <div class="d-flex justify-content-center mt-4" style="gap: 15px;">
                            <button type="button" class="btn btn-light px-4 font-weight-bold border" data-dismiss="modal" style="border-radius: 10px;">Batal</button>
                            <button type="submit" class="btn btn-danger px-4 font-weight-bold shadow-sm" style="border-radius: 10px;">
                                Ya, Hapus Data
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

<script>
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

        const inputPic = document.getElementById('pic_select');
        const inputTanggal = document.getElementById('tanggal_mulai');
        const inputMulai = document.getElementById('jam_mulai');
        const inputSelesai = document.getElementById('jam_selesai');
        const inputEstimasi = document.getElementById('estimasi_menit');
        
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
