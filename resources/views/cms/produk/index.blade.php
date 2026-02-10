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
                    <thead class="thead-light text-center">
                        <tr>
                            <th>No</th>
                            <th>Kategori Jasa</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>HOD</th>
                            <th>DMM</th>
                            <th>DM</th>
                            <th>CC</th>
                            @if(Auth::user()->role != "digital_marketing")
                            <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataClient as $item)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $item->category }}</td>
                                <td class="font-weight-bold">{{ $item->nama }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $item->status == 1 ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $item->status == 1 ? "Ditampilkan" : "Disembunyikan"}}
                                    </span>
                                </td>
                                
                                {{-- Kolom HOD --}}
                                <td>
                                    @foreach($item->teams->where('role', 'head_of_division') as $user)
                                        <small class="badge badge-dark d-block mb-1">{{ $user->email }}</small>
                                    @endforeach
                                </td>

                                {{-- Kolom DMM --}}
                                <td>
                                    @foreach($item->teams->where('role', 'digital_marketing_manager') as $user)
                                        <small class="badge badge-primary d-block mb-1">{{ $user->email }}</small>
                                    @endforeach
                                </td>

                                {{-- Kolom DM --}}
                                <td>
                                    @foreach($item->teams->where('role', 'digital_marketing') as $user)
                                        <small class="badge badge-info d-block mb-1">{{ $user->email }}</small>
                                    @endforeach
                                </td>

                                {{-- Kolom CC --}}
                                <td>
                                    @foreach($item->teams->where('role', 'content_creator') as $user)
                                        <small class="badge badge-warning text-dark d-block mb-1">{{ $user->email }}</small>
                                    @endforeach
                                </td>

                                @if(Auth::user()->role != "digital_marketing")
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal_{{ $item->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal_{{ $item->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                                @endif
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
                <form action="{{ route('produk_create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="divisi" value="{{ $divisi }}">
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
                                    <input type="text" name="client" class="form-control" placeholder="Input Nama Produk" required>
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
                                        <select name="platform[]" class="form-control platform-select">
                                            <option value="">-- Pilih Platform --</option>
                                            <option value="Instagram">Instagram</option>
                                            <option value="Facebook">Facebook</option>
                                            <option value="Tiktok">Tiktok</option>
                                        </select>
                                        <input type="text" name="access[]" class="form-control" placeholder="Input Access Token">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success add-platform" data-target="#platform-container">+</button>
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
                            @if(Auth::user()->role == "super_admin" || Auth::user()->role == "head_of_division")
                                <div class="col-md-12 form-group">
                                    <label class="font-weight-bold small text-danger">Digital Marketing Manager</label>
                                    <select name="dmm" id="dmm-select" class="form-control" required>
                                        <option value="">-- Pilih DMM --</option>
                                        @foreach ($dmm as $m)
                                            <option value="{{ $m->id }}">{{ $m->email }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif(Auth::user()->role == "digital_marketing_manager")
                                {{-- Jika DMM, ID DMM langsung dikirim lewat hidden input --}}
                                <input type="hidden" name="dmm" id="dmm-select" class="is-dmm" value="{{ Auth::user()->id }}">
                            @endif

                            {{-- BAGIAN DM (Staff) --}}
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-primary">Digital Marketing (Staff)</label>
                                <div class="input-group mb-2">
                                    <select name="dm[]" class="form-control dm-staff-select" required>
                                        <option value="">-- Pilih DM --</option>
                                        @if(Auth::user()->role == "digital_marketing_manager")
                                            @foreach ($dm->where('dmm', Auth::user()->id) as $d)
                                                <option value="{{ $d->id }}">{{ $d->email }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success add-dm-row">+</button>
                                    </div>
                                </div>
                            </div>

                            {{-- BAGIAN CC (Bawahan DM) --}}
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-warning">Content Creator (Bawahan DM)</label>
                                <div class="input-group mb-2">
                                    <select name="cc[]" class="form-control cc-staff-select">
                                        <option value="">-- Pilih CC --</option>
                                        @if(Auth::user()->role == "digital_marketing_manager")
                                            @foreach ($cc as $c)
                                                {{-- Opsional: Jika CC juga punya parent DMM --}}
                                                <option value="{{ $c->id }}">{{ $c->email }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success add-cc-row">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <small class="form-text text-muted bg-light p-2 border rounded">
                                <i class="fas fa-info-circle mr-1"></i> <a target="_blank" href="{{ route('docs') }}" class="font-weight-bold">Klik di sini</a> untuk panduan mendapatkan token platform.
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer bg-white border-0 py-3">
                        <button type="button" class="btn btn-light text-dark px-4 font-weight-bold shadow-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow-sm">
                            <i class="fas fa-save mr-2"></i> Simpan Produk
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
    <div class="modal fade" id="editModal_{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('produk_edit', $item->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="divisi_edit" value="{{ $divisi }}">
                    <div class="modal-header bg-gradient-warning text-white">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Edit Produk: {{ $item->nama }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body p-4">
                        {{-- DECODE DATA TOKEN DARI DATABASE --}}
                        @php
                            $decoded_token = is_string($item->access_token) ? json_decode($item->access_token, true) : $item->access_token;
                        @endphp

                        {{-- INFORMASI DASAR EDIT --}}
                        <div class="row mb-3">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small">Kategori Jasa</label>
                                <select name="kategori_jasa_id" class="form-control" required>
                                    @foreach ($kategori as $k)
                                        <option value="{{ $k->id }}" {{ $item->kategori_jasa_id == $k->id ? 'selected' : '' }}>{{ $k->category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small">Nama Produk</label>
                                <input type="text" name="client" class="form-control" value="{{ $item->nama }}" required>
                            </div>
                        </div>

                        {{-- SEKSI PLATFORM & ACCESS TOKEN --}}
                        <hr>
                        <div class="section-title mb-3 d-flex align-items-center">
                            <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-warning font-weight-bold mb-0">Platform & Akses Token</h6>
                        </div>

                        <div id="platform-container-edit-{{ $item->id }}">
                            @if(isset($decoded_token['platform']) && count($decoded_token['platform']) > 0)
                                @foreach($decoded_token['platform'] as $index => $plt)
                                    <div class="platform-row mb-3">
                                        <div class="input-group">
                                            <select name="platform[]" class="form-control platform-select" required>
                                                <option value="Instagram" {{ $plt == 'Instagram' ? 'selected' : '' }}>Instagram</option>
                                                <option value="Facebook" {{ $plt == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                                                <option value="Tiktok" {{ $plt == 'Tiktok' ? 'selected' : '' }}>Tiktok</option>
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
                                {{-- Jika data kosong, tampilkan satu baris input default --}}
                                <div class="platform-row mb-3">
                                    <div class="input-group">
                                        <select name="platform[]" class="form-control platform-select">
                                            <option value="">-- Pilih Platform --</option>
                                            <option value="Instagram">Instagram</option>
                                            <option value="Facebook">Facebook</option>
                                            <option value="Tiktok">Tiktok</option>
                                        </select>
                                        <input type="text" name="access[]" class="form-control" placeholder="Input Access Token">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success add-platform-edit" data-target="#platform-container-edit-{{ $item->id }}">+</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- LOGIKA ROLE EDIT (ASSIGN TEAM) --}}
                        <hr>
                        <div class="row">
                            @if(Auth::user()->role == "super_admin" || Auth::user()->role == "head_of_division")
                                <div class="col-md-12 form-group">
                                    <label class="font-weight-bold small text-danger">Digital Marketing Manager</label>
                                    <select name="dmm" class="form-control dmm-select-edit" required>
                                        <option value="">-- Pilih DMM --</option>
                                        @foreach($dmm as $m)
                                            <option value="{{ $m->id }}" {{ in_array($item->id, $m->client) ? 'selected' : '' }}>
                                                {{ $m->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="dmm" class="dmm-select-edit is-dmm" value="{{ $item->dmm_id ?? Auth::user()->id }}">
                            @endif

                            {{-- BAGIAN DM (EDIT) --}}
                            <div class="col-md-6 form-group" id="dm-container-edit-{{ $item->id }}">
                                <label class="font-weight-bold small text-primary">Digital Marketing (Staff)</label>
                                
                                @php $dm_teams = $item->teams->where('role', 'digital_marketing'); @endphp
                                
                                @foreach($dm_teams as $staff)
                                <div class="input-group mb-2">
                                    <select name="dm[]" class="form-control dm-staff-select-edit" data-selected="{{ $staff->id }}">
                                        <option value="{{ $staff->id }}" {{ in_array($staff->id, $staff->client) ? 'selected' : '' }}>{{ $staff->email }}</option>
                                    </select>
                                    <div class="input-group-append">
                                        @if($loop->first)
                                            <button type="button" class="btn btn-success add-dm-row-edit" data-target="#dm-container-edit-{{ $item->id }}">+</button>
                                        @else
                                            <button type="button" class="btn btn-danger remove-row">-</button>
                                        @endif
                                    </div>
                                </div>
                                @endforeach

                                @if($dm_teams->count() == 0)
                                <div class="input-group mb-2">
                                    <select name="dm[]" class="form-control dm-staff-select-edit">
                                        <option value="">-- Pilih DM --</option>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success add-dm-row-edit" data-target="#dm-container-edit-{{ $item->id }}">+</button>
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- BAGIAN CC (EDIT) --}}
                            <div class="col-md-6 form-group" id="cc-container-edit-{{ $item->id }}">
                                <label class="font-weight-bold small text-warning">Content Creator (Bawahan DM)</label>
                                
                                @php $cc_teams = $item->teams->where('role', 'content_creator'); @endphp

                                @foreach($cc_teams as $staff)
                                <div class="input-group mb-2">
                                    <select name="cc[]" class="form-control cc-staff-select-edit" data-selected="{{ $staff->id }}">
                                        <option value="{{ $staff->id }}" {{ in_array($staff->id, $staff->client) ? 'selected' : '' }}</option>
                                    </select>
                                    <div class="input-group-append">
                                        @if($loop->first)
                                            <button type="button" class="btn btn-success add-cc-row-edit" data-target="#cc-container-edit-{{ $item->id }}">+</button>
                                        @else
                                            <button type="button" class="btn btn-danger remove-row">-</button>
                                        @endif
                                    </div>
                                </div>
                                @endforeach

                                @if($cc_teams->count() == 0)
                                <div class="input-group mb-2">
                                    <select name="cc[]" class="form-control cc-staff-select-edit">
                                        <option value="">-- Pilih CC --</option>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success add-cc-row-edit" data-target="#cc-container-edit-{{ $item->id }}">+</button>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning font-weight-bold text-white">Update Produk</button>
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

    $(document).ready(function() {
        let baseUrl = window.location.origin;
        $('#dmm-select').on('change', function() {
            let dmmId = $(this).val();
            if(dmmId) {
                $.get(`${baseUrl}/api/get-dm/${dmmId}`, function(data) {
                    let html = '<option value="">-- Pilih DM --</option>';
                    data.forEach(d => html += `<option value="${d.id}">${d.email}</option>`);
                    $('.dm-staff-select').html(html);
                });
            }else{
                let html = '<option value="">-- Pilih DM --</option>';
                $('.dm-staff-select').html(html);
            }
        });
        
        $(document).on('change', '.dm-staff-select', function() {
            let dmId = $(this).val();
            let $ccSelect = $(this).closest('.row').find('.cc-staff-select'); // Mencari CC di baris yang sama

            if(dmId) {
                $.get(`${baseUrl}/api/get-cc/${dmId}`, function(data) {
                    let html = '<option value="">-- Pilih CC --</option>';
                    data.forEach(c => html += `<option value="${c.id}">${c.email}</option>`);
                    $ccSelect.html(html);
                });
            }
        });

        function loadDmEdit($modal, dmmId) {
            if (!dmmId) return;
            
            $.get(`${baseUrl}/api/get-dm/${dmmId}`, function(data) {
                $modal.find('.dm-staff-select-edit').each(function() {
                    let $select = $(this);
                    let selectedId = $select.data('selected');
                    
                    let html = '<option value="">-- Pilih DM --</option>';
                    data.forEach(d => {
                        let isSelected = (d.id == selectedId) ? 'selected' : '';
                        html += `<option value="${d.id}" ${isSelected}>${d.email}</option>`;
                    });
                    $select.html(html);
                });
            });
        }
        $(document).on('change', '.dm-staff-select-edit', function() {
            let dmId = $(this).val();
            let $modal = $(this).closest('.modal');
            let $ccSelects = $modal.find('.cc-staff-select-edit');

            if (dmId != "") {
                $.get(`${baseUrl}/api/get-cc/${dmId}`, function(data) {
                    let html = '<option value="">-- Pilih CC --</option>';
                    data.forEach(c => {
                        html += `<option value="${c.id}">${c.email}</option>`;
                    });
                    // Update semua dropdown CC di modal tersebut agar opsinya sesuai DM terbaru
                    $ccSelects.each(function() {
                        let selectedId = $(this).data('selected');
                        $(this).html(html);
                        // Jika ada data CC lama, tetap pilih datanya
                        if(selectedId) $(this).val(selectedId);
                    });
                });
            }else{
                let html = '<option value="">-- Pilih CC --</option>';
                $ccSelects.html(html).val("").trigger('change');
            }
        });
        $('.modal').on('shown.bs.modal', function () {
            let $modal = $(this);
            let dmmId = $modal.find('.dmm-select-edit').val();
            let firstDmId = $modal.find('.dm-staff-select-edit').first().val();

            if (dmmId) loadDmEdit($modal, dmmId);
            
            // Jika sudah ada DM terpilih, otomatis load CC-nya juga
            if (firstDmId) {
                $modal.find('.dm-staff-select-edit').first().trigger('change');
            }
        });

        // Handle Hapus Baris
        $(document).on('click', '.remove-row', function() {
            $(this).closest('.input-group').remove();
        });
    });
});
</script>
@include('partials.footer')
