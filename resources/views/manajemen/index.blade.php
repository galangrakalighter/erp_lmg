@extends('layout.app')
@section('content')
    @include('partials.client_data_theme')
    @include('partials.client_native_tables')

    <div class="card shadow">
        <div class="card-body border-bottom py-2">
            <div class="btn-group d-flex content-source-tabs" role="group" aria-label="Jenis Content Plan">
                @if(in_array('organic', $allowedContentSources, true))<button type="button" class="btn btn-dark flex-fill" data-content-source="organic">Content Plan Organik</button>@endif
                @if(in_array('paid', $allowedContentSources, true))<button type="button" class="btn btn-light flex-fill" data-content-source="paid">Content Plan Paid</button>@endif
            </div>
        </div>

        {{-- CARD HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-tasks mr-2"></i>Manajemen Konten {{ $produk->nama }}
            </h6>
            @if(!$multimediaMode && $contentPlanCanManage)
            <button type="button" id="contentPlanCreateButton" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#tambahModal">
                <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah Konten
            </button>
            @endif
        </div>

        {{-- FILTER TANGGAL --}}
        <div class="card-body border-bottom">
            <form id="filterForm" action="{{ url()->current() }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-uppercase">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-uppercase">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ url()->current() }}" id="contentPlanReset" class="btn btn-secondary btn-sm px-3">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- TABEL DATA --}}
        <div class="table-container px-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center" id="tableManajemenKonten" width="100%">
                    <thead class="thead-light small font-weight-bold">
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Inspirasi</th>
                            <th>Type</th>
                            <th>Strategi</th>
                            <th>Funneling</th>
                            <th>Pilar</th>
                            <th>Hook</th>
                            <th>Body</th>
                            <th>CTA</th>
                            @if($kategory->id == 2)
                                <th>Warna</th>
                            @endif
                            <th>Komposisi</th>
                            <th>Note</th>
                            <th>Tgl Posting</th>
                            <th id="contentPlanTimeHeader">Jam</th>
                            <th>Link</th>
                            <th>Status</th>
                            @if(!$multimediaMode && $contentPlanCanManage)
                            <th id="contentPlanActionHeader">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="contentPlanRows" class="small">
                        <tr><td colspan="17" class="py-4 text-center text-muted">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-between py-3" id="contentPlanPagination">
                <small class="text-muted" id="contentPlanInfo">Memuat data...</small>
                <div class="d-flex align-items-center" style="gap:8px">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="contentPlanPrev">Sebelumnya</button>
                    <span class="small font-weight-bold" id="contentPlanPage">1 / 1</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="contentPlanNext">Berikutnya</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div class="modal fade" id="tambahModal" tabindex="-1" role="dialog" aria-labelledby="tambahModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document" style="max-height: calc(100dvh - 3rem);">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden; max-height: calc(100dvh - 3rem);">
                <form action="{{ route('manajemen_create', $produk->id) }}" method="POST" style="display: flex; min-height: 0; max-height: calc(100dvh - 3rem); flex: 1 1 auto; flex-direction: column; overflow: hidden;">
                    @csrf
                    <input type="hidden" name="platform" value="{{ $platform }}">
                    <input type="hidden" name="content_source" id="create_content_source" value="organic">
                    
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

                    <div class="modal-body p-4" style="min-height: 0; flex: 1 1 auto; background-color: #fcfcfd; overflow-y: auto; overscroll-behavior: contain;">
                        
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

                            {{-- <div class="col-md-6">
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
                            </div> --}}
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
                                    {{-- <div class="col-md-3 col-6">
                                        <div class="form-group mb-0">
                                            <label class="text-dark font-weight-bold small">Durasi</label>
                                            <input type="text" name="durasi" class="form-control bg-light border-0" placeholder="60s">
                                        </div>
                                    </div> --}}
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="text-dark font-weight-bold small">Komposisi</label>
                                            <select name="komposisi" id="create_komposisi" class="form-control custom-select bg-light border-0" data-composition-select data-guidance-target="create_komposisi_guidance">
                                                <option value="">-- Pilih Komposisi --</option>
                                                @foreach ($option['composition'] as $composition)
                                                    <option value="{{ $composition->nama }}" data-function="{{ $composition->fungsi_singkat }}" data-example="{{ $composition->contoh }}">{{ $composition->nama }}</option>
                                                @endforeach
                                            </select>
                                            <small id="create_komposisi_guidance" class="form-text text-muted" hidden></small>
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
                                        <input type="date" name="tanggal_posting" id="create_tanggal_posting" class="form-control border-left-0" data-filter-date data-no-default-date>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3" data-organic-only>
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Jam Posting</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="far fa-clock text-muted"></i></span>
                                        </div>
                                        <input type="time" name="jam_posting" id="create_jam_posting" class="form-control border-left-0" step="60">
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
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Funneling <span class="text-danger">*</span></label>
                                    <select name="funneling" class="form-control custom-select shadow-sm" style="border-radius: 8px;" required>
                                        <option value="" disabled selected>-- Pilih Status --</option>
                                            <option value="TOFU">TOFU</option>
                                            <option value="MOFU">MOFU</option>
                                            <option value="BOFU">BOFU</option>
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

                    <div class="modal-footer bg-light border-0 py-3 px-4" style="position:sticky; bottom:0; z-index:2; flex:0 0 auto;">
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
    <div class="modal fade" id="modalEditKonten" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document" style="max-height: calc(100dvh - 2rem);">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden; max-height: calc(100dvh - 2rem);">
                <form id="formEditKonten" method="POST" style="display:flex; min-height:0; max-height:calc(100dvh - 2rem); flex:1 1 auto; flex-direction:column; overflow:hidden;">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="id_project" value="{{ $produk->id }}">
                    <input type="hidden" name="platform" id="edit_platform">
                    <input type="hidden" name="content_source" id="edit_content_source" value="organic">

                    <div class="modal-header bg-gradient-warning text-white py-3 border-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                                <i class="fas fa-edit text-warning" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title font-weight-bold mb-0">Edit Konten Jasa</h5>
                                <small class="text-white-50">ID Konten: #<span id="display_id"></span></small>
                            </div>
                        </div>
                        <button type="button" class="close text-white outline-none" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body p-4" style="min-height:0; flex:1 1 auto; background-color:#fcfcfd; overflow-y:auto; overscroll-behavior:contain;">
                        
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
                                        <input type="text" name="judul" id="edit_judul" class="form-control border-left-0 pl-0 font-weight-bold" {{ $role == "Content Creator" ? "readonly" : "" }} required>
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
                                        <input type="text" name="inspirasi" id="edit_inspirasi" class="form-control border-left-0 pl-0" {{ $role == "Content Creator" ? "readonly" : "" }}>
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
                                        <select name="type" id="edit_type" class="form-control custom-select bg-light border-0 shadow-none" {{ $role == "Content Creator" ? "disabled" : "" }}>
                                            @foreach ($option['type_produk'] as $type)
                                                <option value="{{ $type->id }}">{{ $type->type }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="text-muted font-weight-bold small">Strategi</label>
                                        <select name="strategy" id="edit_strategy" class="form-control custom-select bg-light border-0 shadow-none" {{ $role == "Content Creator" ? "disabled" : "" }}>
                                            @foreach ($option['strategy'] as $strategy)
                                                <option value="{{ $strategy->id }}">{{ $strategy->strategy }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="text-muted font-weight-bold small">Pilar</label>
                                        <select name="pilar" id="edit_pilar" class="form-control custom-select bg-light border-0 shadow-none" {{ $role == "Content Creator" ? "disabled" : "" }}>
                                            @foreach ($option['pilar'] as $pilar)
                                                <option value="{{ $pilar->id }}">{{ $pilar->pilar }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted font-weight-bold small">Hook</label>
                                        <select name="hooks" id="edit_hooks" class="form-control custom-select bg-light border-0 shadow-none" {{ $role == "Content Creator" ? "disabled" : "" }}>
                                            @foreach ($option['hooks'] as $hooks)
                                                <option value="{{ $hooks->id }}">{{ $hooks->hooks }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted font-weight-bold small">Jenis Body</label>
                                        <select name="jenis_body" id="edit_body" class="form-control custom-select bg-light border-0 shadow-none" {{ $role == "Content Creator" ? "disabled" : "" }}>
                                            @foreach ($option['jenis_body'] as $jenis_body)
                                                <option value="{{ $jenis_body->id }}">{{ $jenis_body->nama_body }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted font-weight-bold small">Jenis CTA</label>
                                        <select name="jenis_cta" id="edit_cta" class="form-control custom-select bg-light border-0 shadow-none" {{ $role == "Content Creator" ? "disabled" : "" }}>
                                            @foreach ($option['jenis_cta'] as $jenis_cta)
                                                <option value="{{ $jenis_cta->id }}">{{ $jenis_cta->nama_cta }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted font-weight-bold small">Komposisi</label>
                                        <select name="komposisi" id="edit_komposisi" class="form-control custom-select bg-light border-0 shadow-none" data-composition-select data-guidance-target="edit_komposisi_guidance" {{ $role == "Content Creator" ? "disabled" : "" }}>
                                            <option value="">-- Pilih Komposisi --</option>
                                            @foreach ($option['composition'] as $composition)
                                                <option value="{{ $composition->nama }}" data-function="{{ $composition->fungsi_singkat }}" data-example="{{ $composition->contoh }}">{{ $composition->nama }}</option>
                                            @endforeach
                                        </select>
                                        <small id="edit_komposisi_guidance" class="form-text text-muted" hidden></small>
                                    </div>
                                    
                                    @if($kategory->id == 2)
                                    <div class="col-md-12 mt-3">
                                        <label class="text-muted font-weight-bold small">Warna Identitas</label>
                                        <div class="input-group shadow-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-palette text-muted"></i></span>
                                            </div>
                                            <input type="text" name="warna" id="edit_warna" class="form-control border-left-0 pl-0" {{ $role == "Content Creator" ? "readonly" : "" }}>
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-muted font-weight-bold small uppercase">Tanggal Posting</label>
                                    <input type="date" name="tanggal_posting" id="edit_tanggal_posting" class="form-control shadow-sm" data-filter-date data-no-default-date {{ $role == "Content Creator" ? "readonly" : "" }}>
                                </div>
                            </div>

                            <div class="col-md-4" data-organic-only>
                                <div class="form-group">
                                    <label class="text-muted font-weight-bold small uppercase">Jam Posting</label>
                                    <input type="time" name="jam_posting" id="edit_jam_posting" class="form-control shadow-sm" step="60">
                                </div>
                            </div>

                            <div class="col-md-12 mt-2">
                                <div class="form-group">
                                    <label class="text-muted font-weight-bold small uppercase">Link Lampiran (Output)</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-external-link-alt text-muted"></i></span>
                                        </div>
                                        <input type="text" name="link" id="edit_link" class="form-control border-left-0 pl-0" placeholder="https://...">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-muted font-weight-bold small uppercase">Status Pengerjaan <span class="text-danger">*</span></label>
                                    <select name="status" id="edit_status" class="form-control shadow-sm" style="border: 2px solid #ffc107; border-radius: 8px;" {{ $role == "Content Creator" ? "disabled" : "" }}>
                                        @foreach ($option['status'] as $status)
                                            <option value="{{ $status->id }}">{{ $status->nama_status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Funneling <span class="text-danger">*</span></label>
                                    <select name="funneling" id="edit_funneling" class="form-control custom-select shadow-sm" style="border-radius: 8px;" required>
                                        <option value="TOFU">TOFU</option>
                                        <option value="MOFU">MOFU</option>
                                        <option value="BOFU">BOFU</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="text-muted font-weight-bold small uppercase">Catatan Revisi / Tambahan</label>
                                    <textarea name="note" id="edit_note" class="form-control shadow-sm" rows="3" placeholder="Tulis catatan di sini..." style="border-radius: 10px;" {{ $role == "Content Creator" ? "readonly" : "" }}></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-0 py-3" style="position:sticky; bottom:0; z-index:2; flex:0 0 auto;">
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
    <div class="modal fade" id="modalDeleteKonten" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <form id="formDeleteKonten" method="POST">
                    @csrf
                    @method('POST') <div class="modal-body p-5 text-center">
                        <div class="mb-4">
                            <div class="bg-light-danger d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background-color: #fff5f5;">
                                <i class="fas fa-exclamation-triangle text-danger fa-2x animate__animated animate__pulse animate__infinite"></i>
                            </div>
                        </div>
                        
                        <h4 class="font-weight-bold text-dark">Hapus Konten?</h4>
                        <p class="text-muted">Apakah Anda yakin ingin menghapus konten:</p>
                        <div class="bg-light p-3 rounded-lg mb-4 border">
                            <strong class="text-primary" id="delete_display_judul"></strong>
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

@push('script')
<script>
    const produkId = "{{ $produk->id }}";
    const platform = "{{ $platform }}";
    const contentPlanDataUrl = @json(route('manajemen_konten.api_data', ['produkId' => $produk->id, 'platform' => $platform]));
    const isContentCreator = {{ $RoleSekarang == 'Content Creator' ? 'true' : 'false' }};
    const multimediaMode = {{ $multimediaMode ? 'true' : 'false' }};
    const allowedContentSources = @json($allowedContentSources);
    const requestedContentSource = new URLSearchParams(window.location.search).get('content_source');
    let activeContentSource = allowedContentSources.includes(requestedContentSource) ? requestedContentSource : allowedContentSources[0];
    const canManageActiveContentSource = () => !isContentCreator || activeContentSource === 'organic';

    window.getContentPlanExportFilters = function () {
        return {
            start_date: document.querySelector('#filterForm [name="start_date"]')?.value || '',
            end_date: document.querySelector('#filterForm [name="end_date"]')?.value || '',
            content_source: activeContentSource || 'organic',
        };
    };

    let contentPlanData = [];
    let contentPlanCurrentPage = 1;
    const contentPlanPageSize = 10;

    const escapeContentPlan = (value) => String(value ?? '-')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');

    function renderContentPlan() {
        const tbody = document.getElementById('contentPlanRows');
        const totalPages = Math.max(1, Math.ceil(contentPlanData.length / contentPlanPageSize));
        contentPlanCurrentPage = Math.min(contentPlanCurrentPage, totalPages);
        const start = (contentPlanCurrentPage - 1) * contentPlanPageSize;
        const rows = contentPlanData.slice(start, start + contentPlanPageSize);

        tbody.innerHTML = rows.length ? rows.map((row, index) => `
            <tr>
                <td>${start + index + 1}</td>
                <td><strong>${escapeContentPlan(row.judul)}</strong></td>
                <td>${row.inspirasi ? `<a href="${escapeContentPlan(row.inspirasi)}" target="_blank" rel="noopener">Link</a>` : '-'}</td>
                <td>${escapeContentPlan(row.type)}</td>
                <td>${escapeContentPlan(row.strategy)}</td>
                <td>${escapeContentPlan(row.funneling)}</td>
                <td>${escapeContentPlan(row.pilar)}</td>
                <td>${escapeContentPlan(row.hooks)}</td>
                <td>${escapeContentPlan(row.nama_body)}</td>
                <td>${escapeContentPlan(row.nama_cta)}</td>
                @if($kategory->id == 2)<td>${escapeContentPlan(row.background)}</td>@endif
                <td>${escapeContentPlan(row.komposisi)}</td>
                <td>${escapeContentPlan(row.note)}</td>
                <td>${row.tanggal_posting ? new Date(row.tanggal_posting).toLocaleDateString('id-ID') : '-'}</td>
                ${activeContentSource === 'organic' ? `<td>${row.jam_posting ? escapeContentPlan(String(row.jam_posting).slice(0, 5)) : '-'}</td>` : ''}
                <td>${row.link ? `<a href="${escapeContentPlan(row.link)}" target="_blank" rel="noopener"><i class="fas fa-link"></i></a>` : '-'}</td>
                <td><span class="badge ${row.nama_status === 'Selesai' ? 'badge-dark' : 'badge-light'}">${escapeContentPlan(row.nama_status)}</span></td>
                @if(!$multimediaMode && $contentPlanCanManage)
                ${canManageActiveContentSource() ? `<td class="text-nowrap"><button class="btn btn-sm btn-warning" onclick="getDataEdit(${Number(row.id)})" title="Edit"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-danger" onclick="confirmDelete(${Number(row.id)})"><i class="fas fa-trash"></i></button></td>` : ''}
                @endif
            </tr>`).join('') : '<tr><td colspan="17" class="py-4 text-center text-muted">Tidak ada data.</td></tr>';

        document.getElementById('contentPlanInfo').textContent = contentPlanData.length
            ? `Menampilkan ${start + 1}–${Math.min(start + contentPlanPageSize, contentPlanData.length)} dari ${contentPlanData.length} data`
            : 'Tidak ada data';
        document.getElementById('contentPlanPage').textContent = `${contentPlanCurrentPage} / ${totalPages}`;
        document.getElementById('contentPlanPrev').disabled = contentPlanCurrentPage <= 1;
        document.getElementById('contentPlanNext').disabled = contentPlanCurrentPage >= totalPages;
    }

    async function loadContentPlan() {
        const params = new URLSearchParams();
        params.set('content_source', activeContentSource);
        const startDate = document.querySelector('[name="start_date"]')?.value;
        const endDate = document.querySelector('[name="end_date"]')?.value;
        if (startDate) params.set('start_date', startDate);
        if (endDate) params.set('end_date', endDate);

        const response = await fetch(`${contentPlanDataUrl}?${params.toString()}`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const payload = await response.json().catch(() => null);
        if (!response.ok || !payload) {
            throw new Error(payload?.message || `Gagal mengambil data Content Plan (${response.status})`);
        }
        contentPlanData = Array.isArray(payload.data) ? payload.data : [];
        contentPlanCurrentPage = 1;
        renderContentPlan();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const updateCompositionGuidance = select => {
            const target = document.getElementById(select.dataset.guidanceTarget || '');
            if (!target) return;
            const option = select.options[select.selectedIndex];
            const parts = [];
            if (option?.dataset.function) parts.push(`<strong>Fungsi:</strong> ${escapeContentPlan(option.dataset.function)}`);
            if (option?.dataset.example) parts.push(`<strong>Contoh:</strong> ${escapeContentPlan(option.dataset.example)}`);
            target.innerHTML = parts.join('<br>');
            target.hidden = parts.length === 0;
        };
        document.querySelectorAll('[data-composition-select]').forEach(select => {
            select.addEventListener('change', () => updateCompositionGuidance(select));
            updateCompositionGuidance(select);
        });
        window.updateCompositionGuidance = updateCompositionGuidance;

        const syncContentSourceTabs = () => {
            document.querySelectorAll('[data-content-source]').forEach(button => {
                const active = button.dataset.contentSource === activeContentSource;
                button.classList.toggle('btn-dark', active);
                button.classList.toggle('btn-light', !active);
                button.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            const createSource = document.getElementById('create_content_source');
            if (createSource) createSource.value = activeContentSource;
            document.querySelectorAll('[data-organic-only]').forEach(field => {
                const organic = activeContentSource === 'organic';
                field.classList.toggle('d-none', !organic);
                field.querySelectorAll('input, select, textarea').forEach(input => {
                    input.disabled = !organic;
                    if (!organic && input.name === 'jam_posting') input.value = '';
                });
            });
            document.getElementById('contentPlanTimeHeader')?.classList.toggle('d-none', activeContentSource !== 'organic');
            document.getElementById('contentPlanCreateButton')?.classList.toggle('d-none', !canManageActiveContentSource());
            document.getElementById('contentPlanActionHeader')?.classList.toggle('d-none', !canManageActiveContentSource());
            const resetLink = document.getElementById('contentPlanReset');
            if (resetLink) resetLink.href = `${window.location.pathname}?content_source=${activeContentSource}`;
        };
        syncContentSourceTabs();
        document.querySelectorAll('[data-content-source]').forEach(button => button.addEventListener('click', () => {
            activeContentSource = button.dataset.contentSource;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('content_source', activeContentSource);
            history.replaceState({}, '', currentUrl);
            syncContentSourceTabs();
            loadContentPlan();
        }));
        loadContentPlan().catch(error => {
            console.error('Content Plan gagal dimuat:', error);
            document.getElementById('contentPlanRows').innerHTML = '<tr><td colspan="17" class="py-4 text-center text-danger">Data gagal dimuat.</td></tr>';
        });
        document.getElementById('filterForm')?.addEventListener('submit', event => {
            event.preventDefault();
            loadContentPlan().catch(error => {
                console.error('Content Plan gagal dimuat:', error);
                document.getElementById('contentPlanRows').innerHTML = '<tr><td colspan="17" class="py-4 text-center text-danger">Data gagal dimuat.</td></tr>';
            });
        });
        document.getElementById('contentPlanPrev')?.addEventListener('click', () => { contentPlanCurrentPage--; renderContentPlan(); });
        document.getElementById('contentPlanNext')?.addEventListener('click', () => { contentPlanCurrentPage++; renderContentPlan(); });
    });

    function confirmDelete(id) {
        var rowData = contentPlanData.find(x => Number(x.id) === Number(id));

        if (rowData) {
            // 3. Masukkan judul ke dalam kotak peringatan di modal
            $('#delete_display_judul').text(rowData.judul);

            // 4. Update URL Action Form secara dinamis
            // Pastikan route 'manajemen_delete' mengarah ke URL yang benar
            $('#formDeleteKonten').attr('action', `/manajemen/${id}/delete`);

            // 5. Tampilkan Modal
            $('#modalDeleteKonten').modal('show');
        } else {
            console.error("Data dengan ID " + id + " tidak ditemukan di tabel.");
        }
    }
    
    function getDataEdit(id) {
        var rowData = contentPlanData.find(x => Number(x.id) === Number(id));

        if (rowData) {
            // 2. Isi nilai ke dalam input modal
            $('#edit_id').val(rowData.id);
            $('#display_id').text(rowData.id);
            $('#edit_platform').val(rowData.platform);
            $('#edit_content_source').val(rowData.content_source || activeContentSource);
            $('#edit_judul').val(rowData.judul);
            $('#edit_inspirasi').val(rowData.inspirasi);
            $('#edit_type').val(rowData.type_produk_id);
            $('#edit_strategy').val(rowData.strategy_id);
            $('#edit_pilar').val(rowData.pilar_id);
            $('#edit_hooks').val(rowData.hooks_id);
            $('#edit_body').val(rowData.body_id);
            $('#edit_cta').val(rowData.cta_id);
            if (rowData.komposisi && !Array.from(document.getElementById('edit_komposisi').options).some(option => option.value === rowData.komposisi)) {
                $('#edit_komposisi').append(new Option(`${rowData.komposisi} (data lama)`, rowData.komposisi));
            }
            $('#edit_komposisi').val(rowData.komposisi);
            window.updateCompositionGuidance?.(document.getElementById('edit_komposisi'));
            $('#edit_warna').val(rowData.background);
            const postingDateValue = rowData.tanggal_posting ? String(rowData.tanggal_posting).slice(0, 10) : '';
            const editPostingDate = document.getElementById('edit_tanggal_posting');
            if (editPostingDate?._flatpickr) {
                editPostingDate._flatpickr.setDate(postingDateValue, false, 'Y-m-d');
            } else if (editPostingDate) {
                editPostingDate.value = postingDateValue;
            }
            $('#edit_jam_posting').val(rowData.jam_posting ? String(rowData.jam_posting).slice(0, 5) : '');
            $('#edit_link').val(rowData.link);
            $('#edit_status').val(rowData.status_id);
            $('#edit_note').val(rowData.note);
            $('#edit_funneling').val(rowData.funneling);

            // 3. Update URL Form Action secara dinamis
            $('#formEditKonten').attr('action', `/manajemen/${id}/edit`);

            // 4. Tampilkan Modal
            $('#modalEditKonten').modal('show');
        } else {
            alert('Data tidak ditemukan!');
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
@endpush
@endsection
