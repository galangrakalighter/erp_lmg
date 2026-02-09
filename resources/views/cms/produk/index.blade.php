@include('partials.header')

<div class="container-fluid mt-4">

    <div class="card shadow">
        {{-- CARD HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Produk Jasa</h6>
            {{-- <a href="{{ route('create_produk_jasa') }}" class="btn btn-sm btn-primary">
                Tambah
            </a> --}}
            @if(Auth::user()->role != "digital_marketing")
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#tambahModal">
                Tambah
            </button>
            @endif
        </div>


        {{-- CARD BODY --}}
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Kategori Jasa</th>
                            <th>Client</th>
                            <th>Status</th>
                            @if(Auth::user()->role != "digital_marketing")
                            <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataClient as $item)
                            <tr>
                                <th>{{ $loop->iteration }}</th>
                                <th>{{ $item->category }}</th>
                                <th>{{ $item->nama }}</th>
                                <th>{{ $item->status == 1 ? "Ditampilkan" : "Disembunyikan"}}</th>
                                @if(Auth::user()->role != "digital_marketing")
                                <th>
                                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editModal_{{ $item->id }}">Edit</button>
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal_{{ $item->id }}">Hapus</button>
                                </th>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div class="modal fade" id="tambahModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <form action="{{ route('produk_create') }}" method="POST">
                    @csrf

                    {{-- MODAL HEADER --}}
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Produk Jasa</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    {{-- MODAL BODY --}}
                    <div class="modal-body">

                        <div class="form-group">
                            <label>Kategori Jasa</label>
                            <select name="kategori_jasa_id" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($kategori as $k)
                                    <option value="{{ $k->id }}">{{ $k->category }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Nama Produk</label>
                            <input type="text" name="client" class="form-control" required>
                        </div>

                        
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="1">Ditampilkan</option>
                                <option value="2">Disembunyikan</option>
                            </select>
                        </div>

                        <div id="platform-container" class="form-group">
                            <label>Platform & Akses Token</label>
                            <div class="platform-row mb-3">
                                <div class="input-group">
                                    <select name="platform[]" class="form-control platform-select">
                                        <option value="">-- Pilih Platform --</option>
                                        {{-- <option value="Tiktok">Tiktok</option> --}}
                                        <option value="Instagram">Instagram</option>
                                        <option value="Facebook">Facebook</option>
                                        {{-- <option value="Youtube">Youtube</option> --}}
                                    </select>
                                    <input type="text" name="access[]" class="form-control" placeholder="Input Access Token">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success add-platform">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(Auth::user()->role == "digital_marketing_manager" || Auth::user()->role == "head_of_division")
                        <div id="dm-container" class="form-group">
                            <label>Digital Marketing</label>
                            <div class="platform-row mb-3">
                                <div class="input-group">
                                    <select name="dm[]" class="form-control dm-select">
                                        <option value="">-- Pilih DM --</option>
                                        @if(Auth::user()->role == "digital_marketing_manager")
                                            @foreach ($dm->where('dmm', Auth::user()->id) as $d)
                                                <option value="{{ $d->id }}" {{ in_array($d->id, $d->client) ? 'selected' : '' }}>
                                                    {{ $d->email }}
                                                </option>
                                            @endforeach
                                        @else
                                            @foreach ($dm as $d)
                                                <option value="{{ $d->id }}" {{ in_array($d->id, $d->client) ? 'selected' : '' }}>
                                                    {{ $d->email }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success add-dm-row">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="cc-container" class="form-group">
                            <label>Content Creator</label>
                            <div class="platform-row mb-3">
                                <div class="input-group">
                                    <select name="cc[]" class="form-control cc-select">
                                        <option value="">-- Pilih CC --</option>
                                        @foreach ($cc as $c)
                                            <option value="{{ $c->id }}">{{ $c->email }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success add-cc-row">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <small class="form-text text-muted">
                            <a target="_blank" href="{{ route('docs') }}">Klik</a> untuk bantuan mendapatkan token.
                        </small>                    

                    </div>

                    {{-- MODAL FOOTER --}}
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Simpan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    @foreach ($dataClient as $item)
    {{-- Modal Edit --}}
    @php
        // Ubah string JSON menjadi array PHP
        $decoded_token = is_string($item->access_token) ? json_decode($item->access_token, true) : $item->access_token;
    @endphp
    <div class="modal fade" id="editModal_{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form action="{{ route('produk_edit', $item->id) }}" method="POST">
                    @csrf
                    {{-- Gunakan method PUT jika route Anda menggunakan resource/put --}}
                    {{-- @method('PUT') --}}

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Produk Jasa</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Kategori Jasa</label>
                            <select name="kategori_jasa_id" class="form-control" required>
                                @foreach ($kategori as $k)
                                    <option value="{{ $k->id }}" {{ $item->kategori_jasa_id == $k->id ? 'selected' : '' }}>
                                        {{ $k->category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Nama Produk</label>
                            <input type="text" name="client" class="form-control" value="{{ $item->nama }}" required>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="1" {{ $item->status == 1 ? 'selected' : '' }}>Ditampilkan</option>
                                <option value="2" {{ $item->status == 2 ? 'selected' : '' }}>Disembunyikan</option>
                            </select>
                        </div>

                        <div id="platform-container-edit-{{ $item->id }}" class="form-group">
                            <label>Platform & Akses Token</label>
                            
                            {{-- Cek jika ada data platform sebelumnya --}}
                            @if(isset($decoded_token['platform']) && count($decoded_token['platform']) > 0)
                                @foreach($decoded_token['platform'] as $index => $plt)
                                <div class="platform-row mb-3">
                                    <div class="input-group">
                                        <select name="platform[]" class="form-control platform-select" required>
                                            {{-- <option value="Tiktok" {{ $plt == 'Tiktok' ? 'selected' : '' }}>Tiktok</option> --}}
                                            <option value="Instagram" {{ $plt == 'Instagram' ? 'selected' : '' }}>Instagram</option>
                                            <option value="Facebook" {{ $plt == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                                            {{-- <option value="Youtube" {{ $plt == 'Youtube' ? 'selected' : '' }}>Youtube</option> --}}
                                        </select>
                                        <input type="text" name="access[]" class="form-control" placeholder="Input Access Token" value="{{ $decoded_token['access'][$index] ?? '' }}">
                                        <div class="input-group-append">
                                            @if($loop->first)
                                                <button type="button" class="btn btn-success add-platform-edit" data-target="#platform-container-edit-{{ $item->id }}">+</button>
                                            @else
                                                <button type="button" class="btn btn-danger remove-platform">x</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                {{-- Jika data kosong, tampilkan satu baris input kosong --}}
                                <div class="platform-row mb-3">
                                    <div class="input-group">
                                        <select name="platform[]" class="form-control platform-select" required>
                                            <option value="">-- Pilih Platform --</option>
                                            {{-- <option value="Tiktok">Tiktok</option> --}}
                                            <option value="Instagram">Instagram</option>
                                            <option value="Facebook">Facebook</option>
                                            {{-- <option value="Youtube">Youtube</option> --}}
                                        </select>
                                        <input type="text" name="access[]" class="form-control" placeholder="Input Access Token">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success add-platform-edit" data-target="#platform-container-edit-{{ $item->id }}">+</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <small class="form-text text-muted">
                            <a target="_blank" href="{{ route('docs') }}">Klik</a> untuk bantuan mendapatkan token.
                        </small>

                        <div id="dm-container-edit-{{ $item->id }}" class="form-group">
                            <label class="font-weight-bold">Digital Marketing</label>
                            <div class="dm-edit-row mb-2">
                                <div class="input-group">
                                    <select name="dm[]" class="form-control">
                                        <option value="">-- Pilih DM --</option>
                                        @if(Auth::user()->role == "digital_marketing_manager")
                                            @foreach ($dm->where('dmm', Auth::user()->id) as $d)
                                                <option value="{{ $d->id }}" {{ in_array($item->id, $d->client) ? 'selected' : '' }}>
                                                    {{ $d->email }}
                                                </option>
                                            @endforeach
                                        @else
                                            @foreach ($dm as $d)
                                                <option value="{{ $d->id }}" {{ in_array($item->id, $d->client) ? 'selected' : '' }}>
                                                    {{ $d->email }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="input-group-append">
                                        @foreach ($dm as $d)
                                            @if($loop->first)
                                                <button type="button" class="btn btn-success add-dm-row-edit" data-target="#dm-container-edit-{{ $item->id }}">+</button>
                                            @else
                                                <button type="button" class="btn btn-danger remove-dm-row">-</button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION CONTENT CREATOR --}}
                        <div id="cc-container-edit-{{ $item->id }}" class="form-group">
                            <label class="font-weight-bold">Content Creator (Bisa > 1)</label>
                            <div class="cc-edit-row mb-2">
                                <div class="input-group">
                                    <select name="cc[]" class="form-control">
                                        <option value="">-- Pilih CC --</option>
                                        @foreach ($cc as $c)
                                            <option value="{{ $c->id }}" {{ in_array($item->id, $c->client) ? 'selected' : '' }}>
                                                {{ $c->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        @foreach ($cc as $c)
                                            @if($loop->first)
                                            <button type="button" class="btn btn-success add-cc-row-edit" data-target="#cc-container-edit-{{ $item->id }}">+</button>
                                            @else
                                            <button type="button" class="btn btn-danger remove-cc-row">-</button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Modal Hapus --}}
    <div class="modal fade" id="deleteModal_{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <form action="{{ route('produk_delete', $item->id) }}" method="POST">
                    @csrf
                    {{-- HEADER --}}
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Hapus Produk Jasa</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            &times;
                        </button>
                    </div>

                    {{-- BODY --}}
                    <div class="modal-body text-center">
                        <p class="mb-2">
                            Apakah kamu yakin ingin menghapus
                        </p>
                        <strong>{{ $item->nama }}</strong>?
                        <p class="text-muted mt-2">
                            Data yang dihapus tidak dapat dikembalikan.
                        </p>
                    </div>

                    {{-- FOOTER --}}
                    <div class="modal-footer justify-content-center">
                        <button class="btn btn-secondary" data-dismiss="modal">
                            Batal
                        </button>
                        <button class="btn btn-danger">
                            Ya, Hapus
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
    @endforeach
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

$(document).ready(function() {
    const allPlatforms = ["Tiktok", "Instagram"];

    function updateOptions() {
        // Kita perlu updateOptions secara spesifik per Modal agar tidak bentrok antar modal
        $('.modal').each(function() {
            let $modal = $(this);
            let selectedValues = [];
            
            $modal.find('.platform-select').each(function() {
                if ($(this).val()) {
                    selectedValues.push($(this).val());
                }
            });

            $modal.find('.platform-select').each(function() {
                let currentVal = $(this).val();
                $(this).find('option').each(function() {
                    let optVal = $(this).val();
                    if (optVal !== "" && optVal !== currentVal && selectedValues.includes(optVal)) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });

            // Sembunyikan tombol tambah di modal ini jika platform sudah penuh
            if (selectedValues.length >= allPlatforms.length) {
                $modal.find('.add-platform, .add-platform-edit').hide();
            } else {
                $modal.find('.add-platform, .add-platform-edit').show();
            }
        });
    }

    // Event Klik Tombol Tambah (Dinamis untuk Tambah & Edit)
    $(document).on('click', '.add-platform, .add-platform-edit', function() {
        // Ambil target container dari atribut data-target
        // Jika tidak ada (untuk modal tambah), default ke #platform-container
        let targetSelector = $(this).data('target') || '#platform-container';
        let $container = $(targetSelector);

        let newRow = `
            <div class="platform-row mb-2 mt-2">
                <div class="input-group">
                    <select name="platform[]" class="form-control platform-select" required>
                        <option value="">-- Pilih Platform --</option>
                        ${allPlatforms.map(p => `<option value="${p}">${p}</option>`).join('')}
                    </select>
                    <input type="text" name="access[]" class="form-control" placeholder="Input Access Token">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-platform">-</button>
                    </div>
                </div>
            </div>`;
        
        $container.append(newRow);
        updateOptions();
    });

    $(document).on('click', '.add-dm-row, .add-dm-row-edit', function() {
        let targetSelector = $(this).data('target') || '#dm-container';
        let $container = $(targetSelector);
        
        let options = `<option value="">-- Pilih DM --</option>`;
        @foreach ($dm as $d)
            options += `<option value="{{ $d->id }}">{{ $d->email }}</option>`;
        @endforeach

        let newRow = `
            <div class="dm-row mb-3 mt-2">
                <div class="input-group">
                    <select name="dm[]" class="form-control" required>${options}</select>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger dm-remove-row">-</button>
                    </div>
                </div>
            </div>`;
        $container.append(newRow);
    });

    // --- Logic untuk Multiple CC ---
    $(document).on('click', '.add-cc-row, .add-cc-row-edit', function() {
        let targetSelector = $(this).data('target') || '#cc-container';
        let $container = $(targetSelector);

        let options = `<option value="">-- Pilih CC --</option>`;
        @foreach ($cc as $c)
            options += `<option value="{{ $c->id }}">{{ $c->email }}</option>`;
        @endforeach

        let newRow = `
            <div class="cc-row mb-3 mt-2">
                <div class="input-group">
                    <select name="cc[]" class="form-control" required>${options}</select>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger cc-remove-row">-</button>
                    </div>
                </div>
            </div>`;
        $container.append(newRow);
    });

    // Event Klik Tombol Hapus
    $(document).on('click', '.remove-platform', function() {
        $(this).closest('.platform-row').remove();
        updateOptions();
    });

    $(document).on('click', '.remove-dm-row', function() {
        $(this).closest('.dm-edit-row').remove();
    });

    $(document).on('click', '.remove-cc-row', function() {
        $(this).closest('.cc-edit-row').remove();
    });

    $(document).on('click', '.dm-remove-row', function() {
        $(this).closest('.dm-row').remove();
    });

    $(document).on('click', '.cc-remove-row', function() {
        $(this).closest('.cc-row').remove();
    });

    // Event saat pilihan berubah
    $(document).on('change', '.platform-select', function() {
        updateOptions();
    });

    // Jalankan sekali saat load untuk inisialisasi modal edit yang sudah ada datanya
    updateOptions();
});
</script>
@include('partials.footer')
