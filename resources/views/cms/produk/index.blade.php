@extends('layout.app')
@section('content')
    <div class="division-cms-page">
    @include('cms.produk.partials.division_theme')

    <div class="card shadow">
        {{-- CARD HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Manajemen Client</h6>
            @if($role == 'Busines Unit Head' || $role == 'Super Admin')
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
                            <th scope="col">Digital Marketing Manager</th>
                            <th scope="col">Digital Marketing</th>
                            <th scope="col">Advertising</th>
                            <th scope="col">Sosial Media Spesialis</th>
                            <th scope="col">Content Creator</th>

                            @if ($role == 'Busines Unit Head' || $role == 'Super Admin')
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
                    <input type="hidden" name="oauth_draft_key" value="{{ old('oauth_draft_key', (string) Illuminate\Support\Str::uuid()) }}" data-oauth-draft-key>
                    
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
                            <h6 class="text-primary font-weight-bold mb-0">Open Authorization (OAuth 2.0)</h6>
                        </div>

                        <div class="card border-0 shadow-sm rounded-lg mb-4">
                            <div class="card-body p-4">
                                <p class="mb-3 text-sm text-zinc-500">
                                    Koneksi akun bersifat opsional. Client baru langsung memiliki Facebook, Instagram, TikTok, Threads, Google, dan YouTube; hubungkan akun hanya saat membutuhkan data insight eksternal. Token akan dikelola otomatis oleh sistem.
                                </p>
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    @foreach([
                                        ['key' => 'meta', 'name' => 'Meta', 'detail' => 'Facebook & Instagram', 'icon' => 'fab fa-facebook'],
                                        ['key' => 'threads', 'name' => 'Threads', 'detail' => 'Threads API', 'icon' => 'fas fa-at'],
                                        ['key' => 'tiktok', 'name' => 'TikTok', 'detail' => 'Login Kit & Display API', 'icon' => 'fab fa-tiktok'],
                                        ['key' => 'google', 'name' => 'Google', 'detail' => 'Google & YouTube', 'icon' => 'fab fa-google'],
                                    ] as $provider)
                                        <div class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-white p-3">
                                            <div class="flex min-w-0 items-center gap-3">
                                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700">
                                                    <i class="{{ $provider['icon'] }}"></i>
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="mb-0 text-sm font-bold text-zinc-900">{{ $provider['name'] }}</p>
                                                    <p class="mb-0 text-[11px] text-zinc-500">{{ $provider['detail'] }}</p>
                                                </div>
                                            </div>
                                            <button type="button" data-oauth-provider="{{ $provider['key'] }}" data-oauth-new-client
                                                class="shrink-0 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 hover:border-zinc-950 hover:bg-zinc-950 hover:text-white">
                                                Hubungkan
                                            </button>
                                        </div>
                                    @endforeach
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


                            {{-- Digital Marketing --}}
                            <div>
                                <label for="select-dmm" class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-900">
                                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-700"></span>
                                    Digital Marketing Manager
                                </label>
                                <select name="dmm_ids[]" id="select-dmm" class="form-control select2-multiple" multiple data-placeholder="Pilih Digital Marketing Manager" disabled>
                                    @foreach ($dmm as $manager)
                                        @php
                                            $parentId = $manager->userDivisions->firstWhere('division_id', $divisi)?->parent_id;
                                        @endphp
                                        <option value="{{ $manager->id }}" data-parent="{{ $parentId }}">
                                            {{ $manager->email }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-[11px] leading-5 text-zinc-400">
                                    Pilih manager yang membawahi tim Digital Marketing dan Content Creator.
                                </p>
                            </div>

                            {{-- Digital Marketing --}}
                            <div>
                                <label
                                    for="select-dm"
                                    class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-900"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-500"></span>
                                    Digital Marketing (DM)
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

                            {{-- Content Creator --}}
                            <div>
                                <label for="select-smm" class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-900">
                                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-500"></span>
                                    Sosial Media Spesialis
                                </label>
                                <select name="smm_ids[]" id="select-smm" class="form-control select2-multiple" multiple data-placeholder="Pilih Sosial Media Spesialis" disabled>
                                    @foreach ($smm as $specialist)
                                        @php
                                            $parentId = $specialist->userDivisions->firstWhere('division_id', $divisi)?->parent_id;
                                        @endphp
                                        <option value="{{ $specialist->id }}" data-parent="{{ $parentId }}">
                                            {{ $specialist->email }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-[11px] leading-5 text-zinc-400">
                                    Pilih spesialis sosial media yang menangani client.
                                </p>
                            </div>

                            {{-- Content Creator --}}
                            <div>
                                <label for="select-cc" class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-900">
                                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-600"></span>
                                    Content Creator (CC)
                                </label>
                                <select name="cc_ids[]" id="select-cc" class="form-control select2-multiple" multiple data-placeholder="Pilih Content Creator">
                                    @foreach ($cc as $creator)
                                        @php
                                            $parentId = $creator->userDivisions->firstWhere('division_id', $divisi)?->parent_id;
                                        @endphp
                                        <option value="{{ $creator->id }}" data-parent="{{ $parentId }}">
                                            {{ $creator->email }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-[11px] leading-5 text-zinc-400">
                                    Pilihan mengikuti Digital Marketing Manager yang menjadi atasannya.
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

    let activeOAuthContext = null;

    function openOAuthPopup(url, context) {
        const width = 620;
        const height = 760;
        const left = Math.max(0, window.screenX + (window.outerWidth - width) / 2);
        const top = Math.max(0, window.screenY + (window.outerHeight - height) / 2);
        const popup = window.open(url, `${context.provider}-oauth`, `popup=yes,width=${width},height=${height},left=${left},top=${top}`);
        if (!popup) {
            alert('Popup diblokir browser. Izinkan popup untuk ERP lalu coba kembali.');
            return;
        }
        activeOAuthContext = context;
        popup.focus();
    }

    window.addEventListener('message', function (event) {
        if (event.origin !== window.location.origin || !/^(meta|threads|tiktok)-oauth-(success|error)$/.test(event.data?.type || '')) return;

        const provider = event.data.type.split('-')[0];

        if (event.data.type.endsWith('-error')) {
            alert(event.data.message || `Koneksi ${provider} gagal.`);
            activeOAuthContext = null;
            return;
        }

        if (activeOAuthContext?.mode === 'edit' && activeOAuthContext.clientId) {
            editProduk(activeOAuthContext.clientId);
        } else {
            const cardButton = document.querySelector(`#tambahModal [data-oauth-provider="${provider}"]`);
            if (cardButton) {
                cardButton.textContent = 'Terhubung';
                cardButton.classList.remove('border-zinc-300', 'bg-white', 'text-zinc-700');
                cardButton.classList.add('border-emerald-600', 'bg-emerald-600', 'text-white');
            }
            alert(`${event.data.message} Simpan client untuk menyelesaikan koneksi.`);
        }
        activeOAuthContext = null;
    });

    document.addEventListener('click', function (event) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const disconnectButton = event.target.closest('[data-meta-disconnect]');
        if (disconnectButton) {
            if (!confirm('Putuskan koneksi Meta untuk client ini?')) return;
            fetch(`/oauth/meta/${disconnectButton.dataset.clientId}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            }).then(async response => {
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Koneksi gagal diputus.');
                alert(data.message);
                $('#modalEditGlobal').modal('hide');
            }).catch(error => alert(error.message));
            return;
        }

        const threadsDisconnectButton = event.target.closest('[data-threads-disconnect]');
        if (threadsDisconnectButton) {
            if (!confirm('Putuskan koneksi Threads untuk client ini?')) return;
            fetch(`/oauth/threads/${threadsDisconnectButton.dataset.clientId}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            }).then(async response => {
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Koneksi Threads gagal diputus.');
                alert(data.message);
                editProduk(threadsDisconnectButton.dataset.clientId);
            }).catch(error => alert(error.message));
            return;
        }

        const tiktokDisconnectButton = event.target.closest('[data-tiktok-disconnect]');
        if (tiktokDisconnectButton) {
            if (!confirm('Putuskan koneksi TikTok untuk client ini?')) return;
            fetch(`/oauth/tiktok/${tiktokDisconnectButton.dataset.clientId}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            }).then(async response => {
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Koneksi TikTok gagal diputus.');
                alert(data.message);
                editProduk(tiktokDisconnectButton.dataset.clientId);
            }).catch(error => alert(error.message));
            return;
        }

        const saveAssetsButton = event.target.closest('[data-meta-save-assets]');
        if (saveAssetsButton) {
            const panel = saveAssetsButton.closest('[data-meta-assets-panel]');
            fetch(`/oauth/meta/${saveAssetsButton.dataset.clientId}/assets`, {
                method: 'PUT',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    page_ids: Array.from(panel.querySelector('[data-meta-page]')?.selectedOptions || []).map(option => option.value),
                    instagram_account_ids: Array.from(panel.querySelector('[data-meta-instagram-account]')?.selectedOptions || []).map(option => option.value),
                    ad_account_id: panel.querySelector('[data-meta-ad-account]')?.value || null
                })
            }).then(async response => {
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Pilihan aset gagal disimpan.');
                alert(data.message);
            }).catch(error => alert(error.message));
            return;
        }

        const button = event.target.closest('[data-oauth-provider]');
        if (!button) return;

        if (button.hasAttribute('data-oauth-new-client')) {
            if (['meta', 'threads', 'tiktok'].includes(button.dataset.oauthProvider)) {
                const draftKey = button.closest('form')?.querySelector('[data-oauth-draft-key]')?.value;
                if (!draftKey) {
                    alert('Kunci form client tidak ditemukan. Muat ulang halaman lalu coba kembali.');
                    return;
                }
                const provider = button.dataset.oauthProvider;
                openOAuthPopup(`/oauth/${provider}/connect?draft_key=${encodeURIComponent(draftKey)}`, { mode: 'create', provider });
                return;
            }
            alert('Koneksi ' + button.dataset.oauthProvider + ' belum diaktifkan pada backend server.');
            return;
        }

        if (['meta', 'threads', 'tiktok'].includes(button.dataset.oauthProvider)) {
            const provider = button.dataset.oauthProvider;
            openOAuthPopup(`/oauth/${provider}/${button.dataset.clientId}/redirect`, {
                mode: 'edit',
                clientId: button.dataset.clientId,
                provider
            });
            return;
        }

        alert('Koneksi ' + button.dataset.oauthProvider + ' belum diaktifkan pada backend server.');
    });
</script>
@endsection
