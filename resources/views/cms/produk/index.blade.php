@extends('layout.app')
@section('content')
    <div class="division-cms-page">
    @include('cms.produk.partials.division_theme')

    <div class="card shadow">
        {{-- CARD HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Manajemen Client</h6>
            @if($role == 'Digital Marketer' || $role == 'Busines Unit Head' || $role == 'Super Admin')
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#tambahModal"><i class="fas fa-plus mr-1"></i> Tambah Client</button>
            @endif
            {{-- <a href="{{ route('create_produk_jasa') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Client
            </a> --}}
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
                                <th scope="col">Aksi</th>
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
                        <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-zinc-50 px-5 py-4 sm:px-6 md:flex-row md:items-center md:justify-between" role="alert">
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-zinc-900 ring-1 ring-zinc-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <path stroke-linecap="round" d="M12 10v6"></path>
                                        <path stroke-linecap="round" d="M12 7.5h.01"></path>
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-semibold leading-5 text-zinc-900">
                                        Butuh panduan?
                                    </p>

                                    <p class="mt-0.5 text-sm leading-6 text-zinc-500">
                                        Lihat dokumentasi terlebih dahulu jika membutuhkan panduan
                                        sebelum menambahkan produk jasa.
                                    </p>
                                </div>
                            </div>

                            <a href="{{ route('docs') }}" target="_blank" rel="noopener noreferrer" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#161616] px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-white transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"></path>
                                    <path stroke-linecap="round" d="M8 6h8M8 10h8"></path>
                                </svg>

                                <span>Buka Dokumentasi</span>

                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h8v8"></path>
                                </svg>
                            </a>
                        </div>

                        <div class="section-title mb-3 d-flex align-items-center">
                            <div class="bg-primary mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-primary font-weight-bold mb-0">Informasi Dasar</h6>
                        </div>
                        
                        <div class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-2">

                            {{-- Kategori Jasa --}}
                            <div>
                                <label
                                    for="kategori-jasa"
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-900"
                                >
                                    Kategori Jasa
                                    <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="kategori-jasa"
                                    name="kategori_jasa_id"
                                    class="form-control custom-select"
                                    required
                                >
                                    <option value="">Pilih kategori jasa</option>

                                    @foreach ($kategori as $k)
                                        <option value="{{ $k->id }}">
                                            {{ $k->category }}
                                        </option>
                                    @endforeach
                                </select>

                                <p class="mt-2 text-[11px] leading-5 text-zinc-400">
                                    Tentukan kategori yang sesuai dengan produk jasa.
                                </p>
                            </div>


                            {{-- Nama Produk --}}
                            <div>
                                <label
                                    for="nama-produk"
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-900"
                                >
                                    Nama Produk
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="nama-produk"
                                    name="client"
                                    class="form-control"
                                    placeholder="Contoh: Social Media Management"
                                    required
                                >

                                <p class="mt-2 text-[11px] leading-5 text-zinc-400">
                                    Gunakan nama produk jasa yang mudah dikenali oleh client.
                                </p>
                            </div>


                            {{-- Status --}}
                            <div class="md:col-span-2">
                                <label
                                    for="status-produk"
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-900"
                                >
                                    Status
                                    <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="status-produk"
                                    name="status"
                                    class="form-control custom-select"
                                    required
                                >
                                    <option value="1">Ditampilkan</option>
                                    <option value="2">Disembunyikan</option>
                                </select>

                                <p class="mt-2 text-[11px] leading-5 text-zinc-400">
                                    Tentukan apakah produk jasa akan ditampilkan pada sistem.
                                </p>
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
                                            <option value="Threads">Threads</option>
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

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                            {{-- Busines Unit Head --}}
                            @if(Auth::user()->isAdmin == true)
                                <div>
                                    <label
                                        for="select-buh"
                                        class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-900"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full bg-zinc-900"></span>
                                        Busines Unit Head (BUH)
                                    </label>

                                    <select
                                        name="buh_ids[]"
                                        id="select-buh"
                                        class="form-control select2-multiple"
                                        multiple
                                        data-placeholder="Pilih BUH"
                                    >
                                        @foreach ($buh as $m)
                                            <option value="{{ $m->id }}">
                                                {{ $m->email }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <p class="mt-2 text-[11px] leading-5 text-zinc-400">
                                        Pilih BUH yang bertanggung jawab atas produk jasa.
                                    </p>
                                </div>
                            @else
                                <input
                                    type="hidden"
                                    name="buh_ids[]"
                                    id="select-buh-hidden"
                                    value="{{ Auth::user()->id }}"
                                >
                            @endif


                            {{-- Digital Marketer --}}
                            <div>
                                <label
                                    for="select-dm"
                                    class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-900"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-500"></span>
                                    Digital Marketer (DM)
                                </label>

                                <select
                                    name="dm_ids[]"
                                    id="select-dm"
                                    class="form-control select2-multiple"
                                    multiple
                                    data-placeholder="Pilih DM"
                                    {{ $role == 'Busines Unit Head' ? '' : 'disabled' }}
                                >
                                    @foreach ($dm as $d)
                                        @php
                                            $parentIdDm = $d->userDivisions->first()->parent_id ?? 0;
                                        @endphp

                                        <option
                                            value="{{ $d->id }}"
                                            data-parent="{{ $parentIdDm }}"
                                        >
                                            {{ $d->email }}
                                        </option>
                                    @endforeach
                                </select>

                                <p class="mt-2 text-[11px] leading-5 text-zinc-400">
                                    Pilih digital marketer yang menangani produk jasa.
                                </p>
                            </div>


                            {{-- Advertising --}}
                            <div>
                                <label
                                    for="select-ads"
                                    class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-900"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                                    Advertising (ADS)
                                </label>

                                <select
                                    name="ads_ids[]"
                                    id="select-ads"
                                    class="form-control select2-multiple"
                                    multiple
                                    data-placeholder="Pilih ADS"
                                >
                                    @foreach ($ads as $s)
                                        @php
                                            $parentIScc = $s->userDivisions->first()->parent_id ?? 0;
                                        @endphp

                                        <option
                                            value="{{ $s->id }}"
                                            data-parent="{{ $parentIScc }}"
                                        >
                                            {{ $s->email }}
                                        </option>
                                    @endforeach
                                </select>

                                <p class="mt-2 text-[11px] leading-5 text-zinc-400">
                                    Pilih tim advertising yang terlibat dalam produk jasa.
                                </p>
                            </div>


                            {{-- Content Creator Manager --}}
                            <div>
                                <label
                                    for="select-ccm"
                                    class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-900"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-700"></span>
                                    Content Creator Manager (CCM)
                                </label>

                                <select
                                    name="ccm_ids[]"
                                    id="select-ccm"
                                    class="form-control select2-multiple"
                                    multiple
                                    data-placeholder="Pilih CCM"
                                >
                                    @foreach ($ccm as $c)
                                        @php
                                            $parentICc = $c->userDivisions->first()->parent_id ?? 0;
                                        @endphp

                                        <option
                                            value="{{ $c->id }}"
                                            data-parent="{{ $parentICc }}"
                                        >
                                            {{ $c->email }}
                                        </option>
                                    @endforeach
                                </select>

                                <p class="mt-2 text-[11px] leading-5 text-zinc-400">
                                    CCM mengelola dan mengatur content creator terkait.
                                </p>
                            </div>


                            {{-- Content Creator --}}
                            <div>
                                <label
                                    for="select-cc"
                                    class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-900"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-500"></span>
                                    Content Creator (CC)
                                </label>

                                <select
                                    name="cc_ids[]"
                                    id="select-cc"
                                    class="form-control select2-multiple"
                                    multiple
                                    data-placeholder="Pilih CC"
                                >
                                    @foreach ($cc as $c)
                                        @php
                                            $parentICc = $c->userDivisions->first()->parent_id ?? 0;
                                        @endphp

                                        <option
                                            value="{{ $c->id }}"
                                            data-parent="{{ $parentICc }}"
                                        >
                                            {{ $c->email }}
                                        </option>
                                    @endforeach
                                </select>

                                <p class="mt-2 text-[11px] leading-5 text-zinc-400">
                                    Pilih content creator yang berada di bawah CCM.
                                </p>
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
