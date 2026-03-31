@include('partials.header')

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Database Affiliator</h1>
        <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambah">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Data Affiliator
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-1"></i> Filter Pencarian</h6>
        </div>
        <div class="card-body">
            <div class="row"> 
                <div class="col-md-3 mb-2">
                    <select id="filter_kategori" class="form-control">
                        <option value="">Semua Kategori Akun</option>
                        @foreach($data->pluck('category')->unique() as $cat)
                            @if($cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" id="filter_provinsi" class="form-control" placeholder="Ketik Provinsi...">
                </div>
                <div class="col-md-3 mb-2">
                    <select id="filter_status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="Tidak Sesuai">Tidak Sesuai</option>
                        <option value="Sesuai">Sesuai</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" id="reset_filter" class="btn btn-outline-secondary btn-block">Reset Filter</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-nowrap" id="dataTableEikyoo" width="100%" cellspacing="0" style="font-size: 12px;">
                    <thead class="bg-primary text-white text-center">
                        <tr>
                            <th>No</th>
                            <th>Aktif Di Social Media</th>
                            <th>Username Instagram</th>
                            <th>Link Instagram</th>
                            <th>Jumlah Follower Instagram</th>
                            <th>Username Shopee</th>
                            <th>Link Shopee</th>
                            <th>Jumlah Follower Shopee</th>
                            <th>Username Tiktok</th>
                            <th>Link Tiktok</th>
                            <th>Jumlah Follower Tiktok</th>
                            <th>Kategori Akun</th>
                            <th>GMV Tiktok</th>
                            <th>GMV Shopee</th>
                            <th>Provinsi</th>
                            <th>Domisili</th>
                            <th>Alamat</th>
                            <th>Gender</th>
                            <th>No Wa</th>
                            <th>Wajib Follow</th>
                            <th>Status</th>
                            <th>Surat Pernyataan</th>
                            <th>Project</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $d)
                            <tr>
                                <td class="align-middle">{{ $loop->iteration }}</td>
                                <td class="align-middle">{{ $d->social_media }}</td>
                                <td class="align-middle font-weight-bold text-primary">{{ $d->user_ig }}</td>
                                <td class="align-middle"><a href="{{ $d->url_ig }}" target="_blank" class="btn btn-xs btn-outline-danger"><i class="fab fa-instagram"></i> Link</a></td>
                                <td class="align-middle">{{ number_format($d->fol_ig) }}</td>
                                <td class="align-middle text-orange font-weight-bold">{{ $d->user_shopee }}</td>
                                <td class="align-middle"><a href="{{ $d->url_shopee }}" target="_blank" class="btn btn-xs btn-outline-warning"><i class="fas fa-shopping-bag"></i> Link</a></td>
                                <td class="align-middle">{{ number_format($d->fol_shopee) }}</td>
                                <td class="align-middle font-weight-bold text-dark">{{ $d->user_tt }}</td>
                                <td class="align-middle"><a href="{{ $d->url_tt }}" target="_blank" class="btn btn-xs btn-outline-dark"><i class="fab fa-tiktok"></i> Link</a></td>
                                <td class="align-middle">{{ number_format($d->fol_tt) }}</td>
                                <td class="align-middle"><span class="badge badge-info">{{ $d->category }}</span></td>
                                <td class="align-middle text-success">{{ $d->gmv_tt }}</td>
                                <td class="align-middle text-success">{{ $d->gmv_shopee }}</td>
                                <td class="align-middle">{{ $d->provinsi }}</td>
                                <td class="align-middle">{{ $d->domisili }}</td> <td class="align-middle"><small>{{ Str::limit($d->alamat, 30) }}</small></td>
                                <td class="align-middle text-center">{{ $d->gender }}</td>
                                <td class="align-middle">
                                    <a href="https://wa.me/{{ $d->contact }}" target="_blank" class="text-success font-weight-bold">
                                        <i class="fab fa-whatsapp"></i> {{ $d->contact }}
                                    </a>
                                </td>
                                <td class="align-middle text-center">{{ $d->wajib_follow }}</td>
                                <td class="align-middle text-center">
                                    <span class="badge {{ $d->status == 'Aktif' ? 'badge-success' : 'badge-secondary' }}">{{ $d->status }}</span>
                                </td>
                                <td class="align-middle text-center">{{ $d->surat_pernyataan }}</td>
                                <td class="align-middle">{{ $d->project }}</td>
                                <td class="align-middle">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalEdit{{ $d->id_eikyoo }}"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalHapus{{ $d->id_eikyoo }}"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalEdit{{ $d->id_eikyoo }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content border-0 shadow-lg">
                                        <div class="modal-header bg-warning text-white">
                                            <h5 class="modal-title font-weight-bold">
                                                <i class="fas fa-edit mr-2"></i>Edit Data Affiliate
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                        </div>
                                        <form action="{{ route('gform_affiliate.update', $d->id_eikyoo) }}" method="POST">
                                            @csrf 
                                            @method('PUT')
                                            <div class="modal-body bg-light">
                                                <div class="row">
                                                    <div class="col-md-6 border-right">
                                                        <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-user-check mr-1"></i> Data Utama</h6>
                                                        
                                                        <div class="form-group">
                                                            <label class="small font-weight-bold">Affiliator Terpilih</label>
                                                            <select name="creator_id" class="form-control select2" required style="width: 100%;">
                                                                @foreach($creator as $c)
                                                                    <option value="{{ $c->id }}" {{ $d->id_affiliator == $c->id ? 'selected' : '' }}>
                                                                        {{ $c->user_ig ?: ($c->user_tt ?: ($c->user_shopee ?: 'No Username')) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="small font-weight-bold">Project Name</label>
                                                            <input type="text" name="project" value="{{ $d->project }}" class="form-control" style="text-transform: uppercase;" readonly>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="small font-weight-bold">Status Verifikasi</label>
                                                            <select name="status" class="form-control">
                                                                <option value="Sesuai" {{ $d->status == 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                                                                <option value="Tidak Sesuai" {{ $d->status == 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                                                <option value="Pending" {{ $d->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                                <option value="Aktif" {{ $d->status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                                                <option value="Non-Aktif" {{ $d->status == 'Non-Aktif' ? 'selected' : '' }}>Non-Aktif</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <h6 class="font-weight-bold text-success mb-3"><i class="fas fa-tasks mr-1"></i> Detail & Ketentuan</h6>
                                                        
                                                        <div class="form-group">
                                                            <label class="small font-weight-bold text-success">Aktif Di Social Media (Bisa Pilih Banyak)</label>
                                                            <select name="social_media[]" class="form-control select2" multiple="multiple" required style="width: 100%;">
                                                                @php
                                                                    // Memecah string "Instagram, TikTok" menjadi array untuk pengecekan selected
                                                                    $selected_sosmed = explode(', ', $d->social_media);
                                                                @endphp
                                                                <option value="Instagram" {{ in_array('Instagram', $selected_sosmed) ? 'selected' : '' }}>Instagram</option>
                                                                <option value="TikTok" {{ in_array('TikTok', $selected_sosmed) ? 'selected' : '' }}>TikTok</option>
                                                                <option value="Shopee" {{ in_array('Shopee', $selected_sosmed) ? 'selected' : '' }}>Shopee</option>
                                                                <option value="Facebook" {{ in_array('Facebook', $selected_sosmed) ? 'selected' : '' }}>Facebook</option>
                                                                <option value="YouTube" {{ in_array('YouTube', $selected_sosmed) ? 'selected' : '' }}>YouTube</option>
                                                                <option value="Twitter/X" {{ in_array('Twitter/X', $selected_sosmed) ? 'selected' : '' }}>Twitter/X</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="small font-weight-bold">Wajib Follow</label>
                                                            <select name="wajib_follow" class="form-control">
                                                                <option value="Udah Follow semua dong!!" {{ $d->wajib_follow == 'Udah Follow semua dong!!' ? 'selected' : '' }}>Udah Follow semua dong!!</option>
                                                                <option value="Ya" {{ $d->wajib_follow == 'Ya' ? 'selected' : '' }}>Ya</option>
                                                                <option value="Tidak" {{ $d->wajib_follow == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="small font-weight-bold">Surat Pernyataan</label>
                                                            <select name="surat_pernyataan" class="form-control">
                                                                <option value="Saya SETUJU dan siap menjalankan seluruh ketentuan campaign Eikyo MCN" {{ $d->surat_pernyataan == 'Saya SETUJU dan siap menjalankan seluruh ketentuan campaign Eikyo MCN' ? 'selected' : '' }}>Saya SETUJU...</option>
                                                                <option value="Tidak Ada" {{ $d->surat_pernyataan == 'Tidak Ada' ? 'selected' : '' }}>Tidak Ada</option>
                                                                <option value="Dalam Proses" {{ $d->surat_pernyataan == 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-white">
                                                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-warning px-5 shadow font-weight-bold">
                                                    <i class="fas fa-save mr-1"></i> Update Data
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="modalHapus{{ $d->id_eikyoo }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-body text-center p-4">
                                            <i class="fas fa-trash text-danger fa-4x mb-3"></i>
                                            <h4 class="font-weight-bold">Konfirmasi Hapus</h4>
                                            <p>Apakah Anda yakin ingin menghapus data <strong>{{ $d->user_ig }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                                            <form action="{{ route('gform_affiliate.destroy', $d->id_eikyoo) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Ya, Hapus!</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalTambahLabel">
                    <i class="fas fa-user-plus mr-2"></i>Tambah Data Affiliate Baru
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('gform_affiliate.store') }}" method="POST">
                @csrf
                <div class="modal-body bg-light">
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-user-check mr-1"></i> Data Utama</h6>
                            
                            <div class="form-group">
                                <label class="small font-weight-bold">Pilih Affiliator (dari Creator List)</label>
                                <select name="creator_id" class="form-control select2" required style="width: 100%;">
                                    <option value="">-- Pilih Affiliator --</option>
                                    @foreach($creator as $c)
                                        <option value="{{ $c->id }}">
                                            {{ $c->user_ig ?: ($c->user_tt ?: ($c->user_shopee ?: 'No Username')) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Memilih data ini akan menghubungkan profil affiliator.</small>
                            </div>

                            <div class="form-group">
                                <label class="small font-weight-bold">Project Name</label>
                                <input type="text" name="project" value="{{ $project->project }}" class="form-control" placeholder="Contoh: Campaign Maret 2024" readonly>
                            </div>

                            <div class="form-group">
                                <label class="small font-weight-bold">Status Verifikasi</label>
                                <select name="status" class="form-control">
                                    <option value="Sesuai">Sesuai</option>
                                    <option value="Tidak Sesuai">Tidak Sesuai</option>
                                    <option value="Pending">Pending</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-success mb-3"><i class="fas fa-tasks mr-1"></i> Detail & Ketentuan</h6>
                            
                            <div class="form-group">
                                <label class="small font-weight-bold text-success">Aktif Di Social Media (Bisa Pilih Banyak)</label>
                                <select name="social_media[]" class="form-control select2" multiple="multiple" data-placeholder="Pilih Social Media" required style="width: 100%;">
                                    <option value="Instagram">Instagram</option>
                                    <option value="TikTok">TikTok</option>
                                    <option value="Shopee">Shopee</option>
                                    <option value="Facebook">Facebook</option>
                                    <option value="YouTube">YouTube</option>
                                    <option value="Twitter/X">Twitter/X</option>
                                </select>
                                <small class="text-muted">Gunakan CTRL Untuk Pilih Lebih Dari</small>
                            </div>

                            <div class="form-group">
                                <label class="small font-weight-bold">Wajib Follow</label>
                                <select name="wajib_follow" class="form-control">
                                    <option value="Udah Follow semua dong!!">Udah Follow semua dong!!</option>
                                    <option value="Tidak">Tidak</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="small font-weight-bold">Surat Pernyataan</label>
                                <select name="surat_pernyataan" class="form-control">
                                    <option value="Saya SETUJU dan siap menjalankan seluruh ketentuan campaign Eikyo MCN">Saya SETUJU dan siap menjalankan seluruh ketentuan campaign Eikyo MCN</option>
                                    <option value="Tidak Ada">Tidak Ada</option>
                                    <option value="Dalam Proses">Dalam Proses</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-5 shadow">
                        <i class="fas fa-save mr-1"></i> Daftarkan Affiliate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .modal-body label {
        color: #4e73df;
    }
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1);
    }
    .border-right {
        border-right: 1px dashed #e3e6f0 !important;
    }
</style>

<style>
    /* Tambahan agar modal terlihat lebih compact */
    .modal-body label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #5a5c69;
        margin-bottom: 2px;
    }
    .form-control-sm {
        border-radius: 4px;
    }
    .text-orange { color: #ee4d2d; }
    .border-right { border-right: 1px solid #e3e6f0; }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // 1. Inisialisasi DataTable
        var table = $('#dataTableEikyoo').DataTable({
            "pageLength": 10,
            "language": {
                "search": "Cari Cepat:",
                "lengthMenu": "Tampilkan _MENU_ data"
            }
        });

        // 2. Filter Kategori (Index 11) - Menggunakan RegEx agar pas (Exact Match)
        $('#filter_kategori').on('change', function() {
            var val = $(this).val();
            // Menggunakan '^' + val + '$' agar mencari kata yang persis sama
            table.column(11).search(val ? '^' + val + '$' : '', true, false).draw();
        });

        // 3. Filter Provinsi (Index 14) - Pencarian bebas (Partial Match)
        $('#filter_provinsi').on('keyup', function() {
            table.column(14).search(this.value).draw();
        });

        // 4. Filter Status (Index 20) - Menggunakan RegEx agar "Aktif" tidak tertukar dengan "Non-Aktif"
        $('#filter_status').on('change', function() {
            var val = $(this).val();
            // Parameter true di search() mengaktifkan RegEx
            table.column(20).search(val ? '^' + val + '$' : '', true, false).draw();
        });

        // 5. Tombol Reset
        $('#reset_filter').on('click', function() {
            // Reset form input
            $('#filter_kategori').val('');
            $('#filter_provinsi').val('');
            $('#filter_status').val('');
            
            // Reset pencarian di DataTables dan gambar ulang
            table.search('').columns().search('').draw();
        });
    });
</script>
@include('partials.footer')