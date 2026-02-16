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
                            <th>Prioritas</th>
                            <th>Type</th>
                            <th>Strategi</th>
                            <th>Pilar</th>
                            <th>Hook</th>
                            <th>Body</th>
                            <th>CTA</th>
                            <th>Durasi</th>
                            @if($kategory->id == 2)
                            <th>Warna</th>
                            @endif
                            <th>Komposisi</th>
                            <th>Note</th>
                            <th>Tanggal Posting (Y-M-D)</th>
                            <th>Tanggal Mulai (Y-M-D)</th>
                            <th>Tanggal Deadline (Y-M-D)</th>
                            <th>Durasi Mengerjakan</th>
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
                                <th><span class="badge {{ $prioData['class'] }} shadow-sm px-3">{{ $prioData['label'] }}</span></th>
                                <th>{{ $item->type }}</th>
                                <th>{{ $item->strategy }}</th>
                                <th>{{ $item->pilar }}</th>
                                <th>{{ $item->hooks }}</th>
                                <th>{{ $item->nama_body }}</th>
                                <th>{{ $item->nama_cta }}</th>
                                <th>{{ $item->durasi }}</th>
                                @if($kategory->id == 2)
                                <th>{{ $item->background }}</th>
                                @endif
                                <th>{{ $item->komposisi }}</th>
                                <th>{{ $item->note }}</th>
                                <th>{{ $item->tanggal_posting }}</th>
                                <th>{{ $item->tanggal_mulai }}</th>
                                <th>{{ $item->tanggal_deadline }}</th>
                                <th>{{ $item->menit ? $item->menit . " Menit" : ""}}</th>
                                <th><a href="{{ $item->link }}">{{ $item->link }}</a></th>
                                <th>{{ $item->nama_status }}</th>
                                <th>
                                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editModal_{{ $item->id }}"><i class="fas fa-edit"></i></button>
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
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('manajemen_create', $produk->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="platform" value={{ $platform }}>
                    <!-- HEADER: Menggunakan gradient dan padding yang lebih luas -->
                    <div class="modal-header bg-gradient-primary text-white py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-plus text-primary"></i>
                            </div>
                            <h5 class="modal-title font-weight-bold" id="tambahModalLabel">Tambah Produk Jasa Baru</h5>
                        </div>
                        <button type="button" class="close text-white outline-none" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body p-4" style="background-color: #f8f9fc;">
                        
                        <!-- SEKSI 1: INFORMASI DASAR -->
                        <div class="section-title mb-3 d-flex align-items-center">
                            <div class="bg-primary mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-primary font-weight-bold mb-0">Informasi Dasar</h6>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Judul Konten <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-heading text-muted"></i></span>
                                        </div>
                                        <input type="text" name="judul" class="form-control border-left-0 pl-0" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Inspirasi / Referensi</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-link text-muted"></i></span>
                                        </div>
                                        <input type="text" name="inspirasi" class="form-control border-left-0 pl-0">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">PIC (Person In Charge) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-user-tag text-muted"></i></span>
                                        </div>
                                        <select name="pic" class="form-control border-left-0 pl-0" required>
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
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-exclamation-circle text-muted"></i></span>
                                        </div>
                                        <select name="prioritas" class="form-control border-left-0 pl-0" required>
                                            {{-- Value menggunakan angka 1, 2, 3 sesuai permintaan --}}
                                            <option value="1">Rendah</option>
                                            <option value="2" selected>Sedang</option>
                                            <option value="3">TInggi</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SEKSI 2: STRATEGI & PILAR -->
                        <div class="section-title mb-3 d-flex align-items-center mt-4">
                            <div class="bg-primary mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-primary font-weight-bold mb-0">Strategi Konten</h6>
                        </div>

                        <div class="card border-0 shadow-sm rounded-lg mb-4">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Tipe Produk <span class="text-danger">*</span></label>
                                            <select name="type" class="form-control custom-select bg-light border-0" required>
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
                                            <select name="strategy" class="form-control custom-select bg-light border-0" required>
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
                                            <select name="pilar" class="form-control custom-select bg-light border-0" required>
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
                                            <select name="hooks" class="form-control custom-select bg-light border-0" required>
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
                                            <select name="jenis_body" class="form-control custom-select bg-light border-0" required>
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
                                            <select name="jenis_cta" class="form-control custom-select bg-light border-0" required>
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
                                            <input type="text" name="durasi" class="form-control bg-light border-0">
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="form-group mb-0">
                                            <label class="text-dark font-weight-bold small">Komposisi</label>
                                            <input type="text" name="komposisi" class="form-control bg-light border-0">
                                        </div>
                                    </div>
                                    @if($kategory->id == 2)
                                    <div class="col-md-6 col-12">
                                        <div class="form-group mb-0">
                                            <label class="text-dark font-weight-bold small">Warna Identitas</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-palette text-muted"></i></span>
                                                </div>
                                                <input type="text" name="warna" class="form-control border-left-0 pl-0" style="height: 38px;">
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- SEKSI 3: JADWAL & STATUS -->
                        <div class="section-title mb-3 d-flex align-items-center mt-4">
                            <div class="bg-primary mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-primary font-weight-bold mb-0">Jadwal & Status</h6>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Estimasi Durasi Mengerjakan (Menit) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-stopwatch text-warning"></i></span>
                                        </div>
                                        <input type="number" name="menit" class="form-control border-left-0 pl-0" min="1" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light border-left-0 small">Menit</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Tanggal Mulai</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-play-circle text-success"></i></span>
                                        </div>
                                        <input type="date" name="tanggal_mulai" class="form-control border-left-0 pl-0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Tanggal Posting</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="far fa-calendar-check text-muted"></i></span>
                                        </div>
                                        <input type="date" name="tanggal_posting" class="form-control border-left-0 pl-0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Tanggal Deadline</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="far fa-calendar-times text-danger"></i></span>
                                        </div>
                                        <input type="date" name="tanggal_deadline" class="form-control border-left-0 pl-0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Link yang Sudah Di upload <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fab fa-google-drive text-muted"></i></span>
                                        </div>
                                        <input type="text" name="link" class="form-control border-left-0 pl-0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control custom-select border-primary-light shadow-sm" style="border: 1px solid #d1d3e2;" required>
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
                                    <textarea name="note" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- FOOTER -->
                    <div class="modal-footer bg-white border-0 py-3">
                        <button type="button" class="btn btn-light text-dark px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow-sm">
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
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('manajemen_update', $item->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="platform" value={{ $item->platform }}>
                    <!-- HEADER: Menggunakan gradient orange/warning -->
                    <div class="modal-header bg-gradient-warning text-white py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-edit text-warning"></i>
                            </div>
                            <h5 class="modal-title font-weight-bold">Edit Konten Jasa</h5>
                        </div>
                        <button type="button" class="close text-white outline-none" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body p-4" style="background-color: #f8f9fc;">
                        
                        <!-- SEKSI 1: INFORMASI DASAR -->
                        <div class="section-title mb-3 d-flex align-items-center">
                            <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-warning font-weight-bold mb-0">Informasi Dasar</h6>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Judul Konten <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-heading text-muted"></i></span>
                                        </div>
                                        <input type="text" name="judul" class="form-control border-left-0 pl-0" value="{{ $item->judul }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }} required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Inspirasi / Referensi</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-link text-muted"></i></span>
                                        </div>
                                        <input type="text" name="inspirasi" class="form-control border-left-0 pl-0" value="{{ $item->inspirasi }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }}>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">PIC (Person In Charge)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-user-tag text-muted"></i></span>
                                        </div>
                                        <select name="pic" class="form-control border-left-0 pl-0 bg-light" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $item->pic == $user->id ? 'selected' : '' }}>
                                                    {{ $user->email }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Prioritas <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-exclamation-circle text-muted"></i></span>
                                        </div>
                                        <select name="prioritas" class="form-control border-left-0 pl-0 bg-light" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }} required>
                                            {{-- Perbaikan: Tambahkan logika selected berdasarkan angka 1, 2, 3 --}}
                                            <option value="1" {{ $item->priority == 1 ? 'selected' : '' }}>Rendah</option>
                                            <option value="2" {{ $item->priority == 2 ? 'selected' : '' }}>Sedang</option>
                                            <option value="3" {{ $item->priority == 3 ? 'selected' : '' }}>Tinggi</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SEKSI 2: STRATEGI & PILAR -->
                        <div class="section-title mb-3 d-flex align-items-center mt-4">
                            <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-warning font-weight-bold mb-0">Strategi Konten</h6>
                        </div>
                        <div class="card border-0 shadow-sm rounded-lg mb-4">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Tipe Produk</label>
                                            <select name="type" class="form-control custom-select bg-light border-0" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                                @foreach ($option['type_produk'] as $type)
                                                    <option value="{{ $type->id }}" {{ $item->type_produk_id == $type->id ? 'selected' : '' }}>{{ $type->type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Strategi</label>
                                            <select name="strategy" class="form-control custom-select bg-light border-0" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                                @foreach ($option['strategy'] as $strategy)
                                                    <option value="{{ $strategy->id }}" {{ $item->strategy_id == $strategy->id ? 'selected' : '' }}>{{ $strategy->strategy }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Pilar</label>
                                            <select name="pilar" class="form-control custom-select bg-light border-0" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                                @foreach ($option['pilar'] as $pilar)
                                                    <option value="{{ $pilar->id }}" {{ $item->pilar_id == $pilar->id ? 'selected' : '' }}>{{ $pilar->pilar }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Hook</label>
                                            <select name="hooks" class="form-control custom-select bg-light border-0" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                                @foreach ($option['hooks'] as $hooks)
                                                    <option value="{{ $hooks->id }}" {{ $item->hooks_id == $hooks->id ? 'selected' : '' }}>{{ $hooks->hooks }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Jenis Body</label>
                                            <select name="jenis_body" class="form-control custom-select bg-light border-0" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                                @foreach ($option['jenis_body'] as $jenis_body)
                                                    <option value="{{ $jenis_body->id }}" {{ $item->body_id == $jenis_body->id ? 'selected' : '' }}>{{ $jenis_body->nama_body }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-md-0">
                                            <label class="text-dark font-weight-bold small">Jenis CTA</label>
                                            <select name="jenis_cta" class="form-control custom-select bg-light border-0" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                                @foreach ($option['jenis_cta'] as $jenis_cta)
                                                    <option value="{{ $jenis_cta->id }}" {{ $item->cta_id == $jenis_cta->id ? 'selected' : '' }}>{{ $jenis_cta->nama_cta }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="form-group mb-0">
                                            <label class="text-dark font-weight-bold small">Durasi</label>
                                            <input type="text" name="durasi" class="form-control bg-light border-0" value="{{ $item->durasi }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }}>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="form-group mb-0">
                                            <label class="text-dark font-weight-bold small">Komposisi</label>
                                            <input type="text" name="komposisi" class="form-control bg-light border-0" value="{{ $item->komposisi }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }}>
                                        </div>
                                    </div>
                                    @if($kategory->id == 2)
                                    <div class="col-md-6 col-12">
                                        <div class="form-group mb-0">
                                            <label class="text-dark font-weight-bold small">Warna Identitas</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-palette text-muted"></i></span>
                                                </div>
                                                <input type="text" name="warna" class="form-control border-left-0 pl-0" style="height: 38px;" value="{{ $item->background }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }}>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- SEKSI 3: JADWAL & STATUS -->
                        <div class="section-title mb-3 d-flex align-items-center mt-4">
                            <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-warning font-weight-bold mb-0">Jadwal & Status</h6>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Durasi Mengerjakan (Menit)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white"><i class="fas fa-stopwatch text-warning"></i></span>
                                        </div>
                                        {{-- Ambil data menit dari tabel task yang berelasi --}}
                                        <input type="number" name="menit" class="form-control" value="{{ $item->menit ?? 0 }}" min="1">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Menit</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Tanggal Mulai</label>
                                    <input type="date" name="tanggal_mulai" class="form-control border-left-0 pl-0" value="{{ $item->tanggal_mulai }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }} required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Tanggal Posting</label>
                                    <input type="date" name="tanggal_posting" class="form-control" value="{{ $item->tanggal_posting }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }}>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Tanggal Deadline</label>
                                    <input type="date" name="tanggal_deadline" class="form-control" value="{{ $item->tanggal_deadline }}" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }} required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Link yang Sudah Di upload</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-external-link-alt text-muted"></i></span>
                                        </div>
                                        <input type="text" name="link" class="form-control border-left-0 pl-0" value="{{ $item->link }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control custom-select shadow-sm" style="border: 1px solid #ffc107;" {{ Auth::user()->role == "content_creator" ? "disabled" : "" }}>
                                        @foreach ($option['status'] as $status)
                                            <option value="{{ $status->id }}" {{ $item->status_id == $status->id ? 'selected' : '' }}>{{ $status->nama_status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="text-dark font-weight-bold small">Catatan Tambahan</label>
                                    <textarea name="note" class="form-control" rows="3" {{ Auth::user()->role == "content_creator" ? "readonly" : "" }}>{{ $item->note }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-white border-0 py-3">
                        <button type="button" class="btn btn-light text-dark px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-white px-5 font-weight-bold shadow-sm">
                            <i class="fas fa-sync-alt mr-2"></i> Update Data
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

@include('partials.footer')
