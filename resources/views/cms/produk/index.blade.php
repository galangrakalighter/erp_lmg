@extends('layout.app')
@section('content')

    <div class="card shadow">
        {{-- CARD HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Produk Jasa</h6>
            {{-- <a href="{{ route('create_produk_jasa') }}" class="btn btn-sm btn-primary">
                Tambah
            </a> --}}
            @if($role == 'Digital Marketer' || $role == 'Busines Unit Head' || $role == 'Super Admin')
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#tambahModal">
                Tambah
            </button>
            @endif
        </div>


        {{-- CARD BODY --}}
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="produkTable" style="width:100%">
                    <thead class="thead-light text-center">
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Kategori Jasa</th>
                            <th scope="col">Client</th>
                            <th scope="col">Status</th>
                            <th scope="col">BUH</th>
                            <th scope="col">Digital Marketer</th>
                            <th scope="col">Advertising</th>
                            <th scope="col">Content Creator Manager</th>
                            <th scope="col">Content Creator</th>

                            @if ($role == 'Digital Marketer' || $role == 'Busines Unit Head' || $role == 'Super Admin')
                                <th scope="col">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Kosong, akan diisi oleh AJAX DataTable --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <input type="hidden" id="user_id" value="{{ Auth::user()->id }}">

    {{-- MODAL TAMBAH --}}
    <div class="modal fade" id="tambahModal" tabindex="-1" role="dialog" aria-labelledby="tambahModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('produk_create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="divisi" value="{{ $divisi }}">
                    
                    <div class="modal-header bg-primary text-white py-3">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Tambah Produk Jasa Baru</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body p-4" style="background-color: #f8f9fc;">
                        <div class="section-title mb-3 d-flex align-items-center">
                            <div class="bg-primary mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-primary font-weight-bold mb-0">Informasi Dasar</h6>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Kategori Jasa <span class="text-danger">*</span></label>
                                    <select name="kategori_jasa_id" class="form-control custom-select" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach ($kategori as $k)
                                            <option value="{{ $k->id }}">{{ $k->category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Nama Produk <span class="text-danger">*</span></label>
                                    <input type="text" name="client" class="form-control" placeholder="Contoh: Social Media Management" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control custom-select" required>
                                        <option value="1">Ditampilkan</option>
                                        <option value="2">Disembunyikan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mb-3 d-flex align-items-center mt-4">
                            <div class="bg-primary mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-primary font-weight-bold mb-0">Platform & Akses Token</h6>
                        </div>

                        <div class="card border-0 shadow-sm rounded-lg mb-4">
                            <div class="card-body p-3" id="platform-container">
                                <div class="platform-row mb-3">
                                    <div class="input-group">
                                        <select name="platform[]" class="form-control col-md-4 platform-select">
                                            <option value="">-- Pilih Platform --</option>
                                            <option value="Instagram">Instagram</option>
                                            <option value="Facebook">Facebook</option>
                                            <option value="Tiktok">Tiktok</option>
                                        </select>
                                        <input type="text" name="access[]" class="form-control" placeholder="Input Access Token">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success add-platform">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mb-3 d-flex align-items-center mt-4">
                            <div class="bg-primary mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-primary font-weight-bold mb-0">Assign Team Management</h6>
                        </div>

                        <div class="row">
                            @if(Auth::user()->isAdmin == true)
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-danger">Busines Unit Head (BUH)</label>
                                <select name="buh_ids[]" id="select-buh" class="form-control select2-multiple" multiple data-placeholder="Pilih BUH">
                                    @foreach ($buh as $m)
                                        <option value="{{ $m->id }}">{{ $m->email }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                                <input type="hidden" name="buh_ids[]" id="select-buh-hidden" value="{{ Auth::user()->id }}">
                            @endif

                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-primary">Digital Marketer (DM)</label>
                                <select name="dm_ids[]" id="select-dm" class="form-control select2-multiple" multiple data-placeholder="Pilih DM" {{ Auth::user()->role == 'digital_marketing_manager' ? '' : 'disabled' }}>
                                    @foreach ($dm as $d)
                                        @php $parentIdDm = $d->userDivisions->first()->parent_id ?? 0; @endphp
                                        <option value="{{ $d->id }}" data-parent="{{ $parentIdDm }}">{{ $d->email }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-dark">Advertising (ADS)</label>
                                <select name="ads_ids[]" id="select-ads" class="form-control select2-multiple" multiple data-placeholder="Pilih ADS" disabled>
                                    @foreach ($ads as $s)
                                        @php $parentIScc = $s->userDivisions->first()->parent_id ?? 0; @endphp
                                        <option value="{{ $s->id }}" data-parent="{{ $parentIScc }}">{{ $s->email }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-warning">Conten Creator Manage (CCM)</label>
                                <select name="ccm_ids[]" id="select-ccm" class="form-control select2-multiple" multiple data-placeholder="Pilih CCM" disabled>
                                    @foreach ($ccm as $c)
                                        @php $parentICc = $c->userDivisions->first()->parent_id ?? 0; @endphp
                                        <option value="{{ $c->id }}" data-parent="{{ $parentICc }}">{{ $c->email }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-warning">Content Creator (CC)</label>
                                <select name="cc_ids[]" id="select-cc" class="form-control select2-multiple" multiple data-placeholder="Pilih CC" disabled>
                                    @foreach ($cc as $c)
                                        @php $parentICc = $c->userDivisions->first()->parent_id ?? 0; @endphp
                                        <option value="{{ $c->id }}" data-parent="{{ $parentICc }}">{{ $c->email }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-white border-0 py-3">
                        <button type="button" class="btn btn-light text-dark px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow-sm">
                            <i class="fas fa-save mr-2"></i>Simpan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditGlobal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="formEditProduk" method="POST">
                    @csrf
                    <div id="modalEditContent"></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDeleteGlobal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <form id="formDeleteProduk" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Hapus Produk Jasa</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body text-center">
                        <p class="mb-2">Apakah kamu yakin ingin menghapus</p>
                        <strong id="deleteTargetName"></strong>?
                        <p class="text-muted mt-2 small">
                            Data yang dihapus tidak dapat dikembalikan dan akses tim terkait akan otomatis diputus.
                        </p>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    window.produkConfig = {
        userId: "{{ Auth::id() }}",
        role: @json($role),

        detailUrl: "{{ url('api/produk/detail') }}",
        updateUrl: "{{ url('cms/produk/edit') }}",
        deleteUrl: "{{ url('cms/produk/delete') }}"
    };

    const Produk = {
        config: window.produkConfig,

        get userId() {
            return this.config.userId;
        },

        get role() {
            return this.config.role;
        }
    };
</script>
@endsection