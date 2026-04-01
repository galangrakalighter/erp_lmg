@include('partials.header')

<style>
    /* Styling Filter & Tabel */
    .filter-wrapper {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e3e6f0;
    }
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }
    #dataTable thead th {
        vertical-align: middle;
        text-transform: uppercase;
        font-size: 0.7rem;
        background-color: #4e73df;
        color: white;
    }
    .badge-category {
        background-color: #eaecf4;
        color: #4e73df;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
        margin: 2px;
        display: inline-block;
        font-size: 0.75rem;
    }
    .cursor-pointer { cursor: pointer; }
    .shadow-inner { box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); }
    .text-xs { font-size: 0.75rem; }
    .modal-xl { max-width: 1140px; }
    input:focus, select:focus { border-color: #4e73df !important; box-shadow: 0 0 0 0.2rem rgba(78,115,223,0.1) !important; }
    .choices__list--dropdown, .choices__inner {
        background-color: #ffffff !important;
        opacity: 1 !important;
        z-index: 9999 !important; /* Pastikan di atas input lain */
    }

    /* 2. Beri border agar terlihat jelas batasnya */
    .choices__list--dropdown {
        border: 1px solid #d1d3e2 !important; 
        box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Tambah bayangan agar estetik */
    }

    /* 3. Warna saat opsi disorot (hover) */
    .choices__list--dropdown .choices__item--selectable.is-highlighted {
        background-color: #f8f9fc !important;
        color: #4e73df !important;
    }

    /* 4. Menyesuaikan ukuran agar seukuran form-control-sm (opsional) */
    .choices__inner {
        min-height: 31px !important;
        padding: 2px 8px !important;
        border: 1px solid #d1d3e2 !important;
        border-radius: 4px !important;
    }
</style>

<div class="container-fluid mt-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Database Affiliator</h1>
        <div>
            {{-- <button type="button" class="btn btn-outline-success btn-sm shadow-sm mr-2" data-toggle="modal" data-target="#importModal">
                <i class="fas fa-file-excel fa-sm mr-1"></i> Import Excel
            </button> --}}
            <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#tambahModal">
                <i class="fas fa-plus fa-sm mr-1"></i> Tambah Affiliator
            </button>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form id="filterForm" class="row">
                <div class="col-md-3 mb-3">
                    <label class="small font-weight-bold">Kategori</label>
                    <select class="form-control form-control-sm border-left-primary shadow-sm" id="filterKategori" onchange="filterTable()">
                        <option value="">Semua Kategori</option>
                        @php
                            $categories = $data->flatMap(function ($item) {
                                return explode(',', $item->category);
                            })->map(function ($cat) {
                                return trim($cat);
                            })->unique()->filter()->sort();
                        @endphp
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="small font-weight-bold">Provinsi</label>
                    <select class="form-control form-control-sm border-left-success shadow-sm" id="filterLokasi">
                        <option value="">Semua Provinsi</option>
                        <option value="Banten">Banten</option>
                        <option value="DKI Jakarta">DKI Jakarta</option>
                        <option value="Jawa Barat">Jawa Barat</option>
                        <option value="Jawa Tengah">Jawa Tengah</option>
                        <option value="DI Yogyakarta">DI Yogyakarta</option>
                        <option value="Jawa Timur">Jawa Timur</option>

                        <option value="Aceh">Aceh</option>
                        <option value="Sumatera Utara">Sumatera Utara</option>
                        <option value="Sumatera Barat">Sumatera Barat</option>
                        <option value="Riau">Riau</option>
                        <option value="Kepulauan Riau">Kepulauan Riau</option>
                        <option value="Jambi">Jambi</option>
                        <option value="Sumatera Selatan">Sumatera Selatan</option>
                        <option value="Bengkulu">Bengkulu</option>
                        <option value="Lampung">Lampung</option>
                        <option value="Kepulauan Bangka Belitung">Bangka Belitung</option>

                        <option value="Bali">Bali</option>
                        <option value="Nusa Tenggara Barat">Nusa Tenggara Barat</option>
                        <option value="Nusa Tenggara Timur">Nusa Tenggara Timur</option>

                        <option value="Kalimantan Barat">Kalimantan Barat</option>
                        <option value="Kalimantan Tengah">Kalimantan Tengah</option>
                        <option value="Kalimantan Selatan">Kalimantan Selatan</option>
                        <option value="Kalimantan Timur">Kalimantan Timur</option>
                        <option value="Kalimantan Utara">Kalimantan Utara</option>

                        <option value="Sulawesi Utara">Sulawesi Utara</option>
                        <option value="Gorontalo">Gorontalo</option>
                        <option value="Sulawesi Tengah">Sulawesi Tengah</option>
                        <option value="Sulawesi Barat">Sulawesi Barat</option>
                        <option value="Sulawesi Selatan">Sulawesi Selatan</option>
                        <option value="Sulawesi Tenggara">Sulawesi Tenggara</option>

                        <option value="Maluku">Maluku</option>
                        <option value="Maluku Utara">Maluku Utara</option>
                        <option value="Papua">Papua</option>
                        <option value="Papua Barat">Papua Barat</option>
                        <option value="Papua Selatan">Papua Selatan</option>
                        <option value="Papua Tengah">Papua Tengah</option>
                        <option value="Papua Pegunungan">Papua Pegunungan</option>
                        <option value="Papua Barat Daya">Papua Barat Daya</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="small font-weight-bold">Platform Followers</label>
                    <select class="form-control form-control-sm border-left-warning shadow-sm" id="filterPlatform" onchange="filterTable()">
                        <option value="all">Gabungan (Semua)</option>
                        <option value="tiktok">TikTok</option>
                        <option value="instagram">Instagram</option>
                        <option value="shopee">Shopee</option>
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="small font-weight-bold">Min. Followers</label>
                    <input type="number" class="form-control form-control-sm shadow-sm" id="filterFollowers" oninput="filterTable()" placeholder="0">
                </div>

                <div class="col-md-2 mb-3">
                    <label class="small font-weight-bold">Platform GMV</label>
                    <select class="form-control form-control-sm border-left-warning shadow-sm" id="filterPlatformGMV" onchange="filterTable()">
                        <option value="all">Gabungan (Semua)</option>
                        <option value="tiktok">TikTok</option>
                        <option value="shopee">Shopee</option>
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="small font-weight-bold">Min. GMV</label>
                    <input type="number" class="form-control form-control-sm" id="filterGMV" oninput="filterTable()" placeholder="0">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <div class="w-100 mr-2">
                        <label class="small font-weight-bold">Gender</label>
                        <select class="form-control form-control-sm" id="filterGender" onchange="filterTable()">
                            <option value="">Semua</option>
                            <option value="Pria">Pria</option>
                            <option value="Wanita">Wanita</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="resetFilter()"><i class="fas fa-sync"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm nowrap" id="dataTables1" width="100%" cellspacing="0">
                    <thead class="bg-primary text-white text-center">
                        <tr>
                            <th rowspan="2" class="align-middle">No</th>
                            <th colspan="15" class="align-middle text-uppercase" style="font-size: 1.1rem; letter-spacing: 1px;">Data Affiliator</th>
                            <th colspan="6" class="align-middle text-uppercase" style="font-size: 1.1rem; letter-spacing: 1px;">Rate Card</th>
                            <th rowspan="2" class="align-middle">Action</th>
                        </tr>
                        <tr class="bg-light text-dark" style="font-size: 0.8rem;">
                            <th>TikTok</th>
                            <th>URL TT</th>
                            <th>Followers TT</th>
                            <th>Shopee</th>
                            <th>URL SHP</th>
                            <th>Followers SHP</th>
                            <th>Instagram</th>
                            <th>URL IG</th>
                            <th>Followers IG</th>
                            <th>Kategori</th>
                            <th>GMV TT</th>
                            <th>GMV Shopee</th>
                            <th>Provinsi</th>
                            <th>Gender</th>
                            <th>Contact</th>
                            
                            <th>Detail</th>
                            <th>Reels</th>
                            <th>VT</th>
                            <th>Mirroring</th>
                            <th>Kerkun</th>
                            <th>Story</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $d)
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            
                            <td class="align-middle">{{ $d->user_tt }}</td>
                            <td class="align-middle"><a href="{{ $d->url_tt }}" target="_blank" class="btn btn-xs btn-link p-0"><i class="fab fa-tiktok"></i> Link</a></td>
                            <td class="align-middle" data-fol="{{ $d->fol_tt }}"">{{ number_format($d->fol_tt) }}</td>
                            <td class="align-middle">{{ $d->user_shopee }}</td>
                            <td class="align-middle"><a href="{{ $d->url_shp }}" target="_blank" class="btn btn-xs btn-link p-0"><i class="fas fa-shopping-bag"></i> Link</a></td>
                            <td class="align-middle" data-fol="{{ $d->fol_shopee }}">{{ number_format($d->fol_shopee) }}</td>
                            <td class="align-middle">{{ $d->user_ig }}</td>
                            <td class="align-middle"><a href="{{ $d->url_ig }}" target="_blank" class="btn btn-xs btn-link p-0"><i class="fab fa-instagram"></i> Link</a></td>
                            <td class="align-middle" data-fol="{{ $d->fol_ig }}">{{ number_format($d->fol_ig) }}</td>
                            <td class="align-middle">
                                @if($d->category)
                                    @foreach(explode(',', $d->category) as $cat)
                                        <span class="badge badge-secondary mb-1">{{ trim($cat) }}</span>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                            <td class="align-middle" data-gmv="{{ $d->gmv_tt }}">Rp {{ number_format($d->gmv_tt, 0, ',', '.') }}</td>
                            <td class="align-middle" data-gmv="{{ $d->gmv_shopee }}">Rp {{ number_format($d->gmv_shopee, 0, ',', '.') }}</td>
                            <td class="align-middle">{{ $d->provinsi }}</td>
                            <td class="align-middle"><span class="badge badge-secondary mb-1">{{ $d->gender }}</td>
                            <td class="align-middle">@php
                                    // Menghapus '0' di awal dan menggantinya dengan '62'
                                    $waNumber = preg_replace(['/-/', '/^0/'], ['', '62'], $d->contact);
                                @endphp
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="text-success font-weight-bold">
                                    {{ $d->contact }}
                                </a></td>

                            <td class="text-center align-middle">{{ $d->detail }}</td>
                            <td class="text-center align-middle">{{ $d->reels }}</td>
                            <td class="text-center align-middle">{{ $d->vt }}</td>
                            <td class="text-center align-middle">{{ $d->mirroring }}</td>
                            <td class="text-center align-middle">{{ $d->kerkun }}</td>
                            <td class="text-center align-middle">{{ $d->story }}</td>

                            <td class="text-center align-middle">
                                <button class="btn btn-xs btn-warning shadow-sm" data-toggle="modal" data-target="#editModal{{ $d->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-xs btn-danger shadow-sm" data-toggle="modal" data-target="#deleteModal{{ $d->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahModal" tabindex="-1" role="dialog" aria-labelledby="tambahModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered shadow-lg" role="document">
        <form action="{{ route('social_creator.store') }}" method="POST" class="w-100">
            @csrf
            <div class="modal-content border-0">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="tambahModalLabel">
                        <i class="fas fa-user-plus mr-2"></i>Tambah Data Affiliator Baru
                    </h5>
                    <button class="close text-white opacity-1" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body bg-light p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-primary mb-3 border-bottom pb-2">
                                        <i class="fab fa-tiktok mr-2"></i>Data Identitas & Sosmed
                                    </h6>
                                    
                                    <div class="form-group">
                                        <label class="small font-weight-bold text-dark">Username TikTok</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text bg-white">@</span></div>
                                            <input type="text" name="user_tt" class="form-control" placeholder="username" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="small font-weight-bold text-dark">URL TikTok</label>
                                        <input type="text" name="url_tt" class="form-control form-control-sm" placeholder="https://tiktok.com/@...">
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small font-weight-bold text-dark text-truncate d-block">Followers TikTok</label>
                                                <input type="number" name="fol_tt" class="form-control form-control-sm border-left-info" value="0">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small font-weight-bold text-dark text-truncate d-block">GMV TikTok</label>
                                                <input type="number" name="gmv_tt" class="form-control form-control-sm border-left-success" value="0">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="small font-weight-bold text-dark">Provinsi</label>
                                                <select name="provinsi" id="choices-provinsi" class="form-control" required>
                                                    <optgroup label="Sumatra">
                                                        <option value="Aceh">Aceh</option>
                                                        <option value="Sumatera Utara">Sumatera Utara</option>
                                                        <option value="Sumatera Barat">Sumatera Barat</option>
                                                        <option value="Riau">Riau</option>
                                                        <option value="Kepulauan Riau">Kepulauan Riau</option>
                                                        <option value="Jambi">Jambi</option>
                                                        <option value="Bengkulu">Bengkulu</option>
                                                        <option value="Sumatera Selatan">Sumatera Selatan</option>
                                                        <option value="Kepulauan Bangka Belitung">Kepulauan Bangka Belitung</option>
                                                        <option value="Lampung">Lampung</option>
                                                    </optgroup>
                                                    <optgroup label="Jawa">
                                                        <option value="DKI Jakarta">DKI Jakarta</option>
                                                        <option value="Banten">Banten</option>
                                                        <option value="Jawa Barat">Jawa Barat</option>
                                                        <option value="Jawa Tengah">Jawa Tengah</option>
                                                        <option value="DI Yogyakarta">DI Yogyakarta</option>
                                                        <option value="Jawa Timur">Jawa Timur</option>
                                                    </optgroup>
                                                    <optgroup label="Bali & Nusa Tenggara">
                                                        <option value="Bali">Bali</option>
                                                        <option value="Nusa Tenggara Barat">Nusa Tenggara Barat</option>
                                                        <option value="Nusa Tenggara Timur">Nusa Tenggara Timur</option>
                                                    </optgroup>
                                                    <optgroup label="Kalimantan">
                                                        <option value="Kalimantan Barat">Kalimantan Barat</option>
                                                        <option value="Kalimantan Tengah">Kalimantan Tengah</option>
                                                        <option value="Kalimantan Selatan">Kalimantan Selatan</option>
                                                        <option value="Kalimantan Timur">Kalimantan Timur</option>
                                                        <option value="Kalimantan Utara">Kalimantan Utara</option>
                                                    </optgroup>
                                                    <optgroup label="Sulawesi">
                                                        <option value="Sulawesi Utara">Sulawesi Utara</option>
                                                        <option value="Gorontalo">Gorontalo</option>
                                                        <option value="Sulawesi Tengah">Sulawesi Tengah</option>
                                                        <option value="Sulawesi Barat">Sulawesi Barat</option>
                                                        <option value="Sulawesi Selatan">Sulawesi Selatan</option>
                                                        <option value="Sulawesi Tenggara">Sulawesi Tenggara</option>
                                                    </optgroup>
                                                    <optgroup label="Maluku & Papua">
                                                        <option value="Maluku">Maluku</option>
                                                        <option value="Maluku Utara">Maluku Utara</option>
                                                        <option value="Papua">Papua</option>
                                                        <option value="Papua Barat">Papua Barat</option>
                                                        <option value="Papua Selatan">Papua Selatan</option>
                                                        <option value="Papua Tengah">Papua Tengah</option>
                                                        <option value="Papua Pegunungan">Papua Pegunungan</option>
                                                        <option value="Papua Barat Daya">Papua Barat Daya</option>
                                                    </optgroup>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="small font-weight-bold text-dark">Gender</label>
                                                <select name="gender" class="form-control form-control-sm" required>
                                                    <option value="" disabled selected>Pilih...</option>
                                                    <option value="Pria">Pria</option>
                                                    <option value="Wanita">Wanita</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-danger">Kategori (Bisa pilih lebih dari 1)</label>
                                        <div class="p-2 border rounded bg-white shadow-inner" style="max-height: 120px; overflow-y: auto;">
                                            @foreach($categories as $cat)
                                            <div class="custom-control custom-checkbox py-1">
                                                <input type="checkbox" name="categories_array[]" value="{{ $cat }}" class="custom-control-input" id="cat_{{ $loop->index }}">
                                                <label class="custom-control-label small cursor-pointer" for="cat_{{ $loop->index }}">{{ $cat }}</label>
                                            </div>
                                            @endforeach
                                        </div>
                                        <small class="text-muted italic">Centang kategori yang sesuai.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-4 border-left-success">
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-success mb-3 border-bottom pb-2">
                                        <i class="fas fa-tags mr-2"></i>Rate Card (Harga)
                                    </h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group"><label class="small font-weight-bold">Detail</label><input type="text" name="detail" class="form-control form-control-sm" placeholder="Rp"></div>
                                            <div class="form-group"><label class="small font-weight-bold">Reels IG</label><input type="text" name="reels" class="form-control form-control-sm" placeholder="Rp"></div>
                                            <div class="form-group mb-0"><label class="small font-weight-bold text-truncate d-block">Video TikTok (VT)</label><input type="text" name="vt" class="form-control form-control-sm" placeholder="Rp"></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group"><label class="small font-weight-bold">Mirroring</label><input type="text" name="mirroring" class="form-control form-control-sm" placeholder="Rp"></div>
                                            <div class="form-group"><label class="small font-weight-bold">Kerkun</label><input type="text" name="kerkun" class="form-control form-control-sm" placeholder="Rp"></div>
                                            <div class="form-group mb-0"><label class="small font-weight-bold text-truncate d-block">IG Story</label><input type="text" name="story" class="form-control form-control-sm" placeholder="Rp"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm border-left-info overflow-hidden">
                                <div class="card-body pb-3">
                                    <h6 class="font-weight-bold text-info mb-3 border-bottom pb-2 d-flex align-items-center">
                                        <i class="fas fa-share-alt-square mr-2 fa-lg"></i> Koneksi E-Commerce & Media Sosial
                                    </h6>

                                    <div class="bg-light p-2 rounded mb-3 border-left-orange" style="border-left: 3px solid #ee4d2d;">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-shopping-bag text-danger mr-2 small"></i>
                                            <span class="small font-weight-bold text-uppercase" style="color: #ee4d2d; letter-spacing: 0.5px;">Shopee Data</span>
                                        </div>
                                        <div class="row mx-0">
                                            <div class="col-md-3 px-1">
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold mb-1">Username</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" name="user_shopee" class="form-control text-xs border-0 shadow-sm" placeholder="user_shp">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 px-1">
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold mb-1">URL Toko</label>
                                                    <input type="text" name="url_shopee" class="form-control form-control-sm border-0 shadow-sm" placeholder="shopee.co.id/...">
                                                </div>
                                            </div>
                                            <div class="col-md-3 px-1">
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold mb-1">Followers</label>
                                                    <input type="number" name="fol_shopee" class="form-control form-control-sm border-0 shadow-sm" placeholder="0">
                                                </div>
                                            </div>
                                            <div class="col-md-3 px-1">
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold mb-1">GMV Shopee</label>
                                                    <input type="number" name="gmv_shopee" class="form-control form-control-sm border-0 shadow-sm" placeholder="Rp">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-light p-2 rounded border-left-purple" style="border-left: 3px solid #833ab4;">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fab fa-instagram text-primary mr-2 fa-lg"></i>
                                            <span class="small font-weight-bold text-uppercase" style="color: #833ab4; letter-spacing: 0.5px;">Instagram Data</span>
                                        </div>
                                        <div class="row mx-0">
                                            <div class="col-md-4 px-1">
                                                <div class="form-group mb-1">
                                                    <label class="small font-weight-bold mb-1">Username IG</label>
                                                    <div class="input-group input-group-sm border-0 shadow-sm">
                                                        <div class="input-group-prepend"><span class="input-group-text bg-white border-0 text-muted">@</span></div>
                                                        <input type="text" name="user_ig" class="form-control border-0 text-xs" placeholder="username">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 px-1">
                                                <div class="form-group mb-1">
                                                    <label class="small font-weight-bold mb-1">URL Profile</label>
                                                    <input type="text" name="url_ig" class="form-control form-control-sm border-0 shadow-sm" placeholder="instagram.com/...">
                                                </div>
                                            </div>
                                            <div class="col-md-4 px-1">
                                                <div class="form-group mb-1">
                                                    <label class="small font-weight-bold mb-1 text-truncate">Follower IG</label>
                                                    <input type="number" name="fol_ig" class="form-control form-control-sm border-0 shadow-sm" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white py-3 border-top">
                    <button class="btn btn-light btn-sm px-4 text-secondary border font-weight-bold" type="button" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" type="submit">
                        <i class="fas fa-save mr-1"></i> Simpan Data Affiliator
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('creator.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="exampleModalLabel">Import Data Affiliator</h5>
                    <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Pilih File Excel (.xlsx, .xls)</label>
                        <input type="file" name="file" class="form-control-file" required>
                        <small class="text-danger mt-2 d-block">*Pastikan format header sesuai template.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <button class="btn btn-success" type="submit">Mulai Import</button>
                </div>
            </div>
        </form>
    </div>
</div> --}}

@foreach ($data as $d)
<div class="modal fade" id="editModal{{ $d->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $d->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered shadow-lg" role="document">
        <form action="{{ route('social_creator.update', $d->id) }}" method="POST" class="w-100">
            @csrf
            @method('PUT') <div class="modal-content border-0">
                <div class="modal-header bg-warning text-dark py-3">
                    <h5 class="modal-title font-weight-bold" id="editModalLabel{{ $d->id }}">
                        <i class="fas fa-edit mr-2"></i>Edit Data Affiliator: {{ $d->user_tt }}
                    </h5>
                    <button class="close text-dark" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body bg-light p-4 text-left">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-primary mb-3 border-bottom pb-2">
                                        <i class="fab fa-tiktok mr-2"></i>Data Identitas & Sosmed
                                    </h6>
                                    
                                    <div class="form-group">
                                        <label class="small font-weight-bold">Username TikTok</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text bg-white">@</span></div>
                                            <input type="text" name="user_tt" class="form-control" value="{{ $d->user_tt }}" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="small font-weight-bold">URL TikTok</label>
                                        <input type="text" name="url_tt" class="form-control form-control-sm" value="{{ $d->url_tt }}">
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small font-weight-bold text-truncate d-block">Followers TikTok</label>
                                                <input type="number" name="fol_tt" class="form-control form-control-sm border-left-info" value="{{ $d->fol_tt }}">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small font-weight-bold text-truncate d-block">GMV TikTok</label>
                                                <input type="number" name="gmv_tt" class="form-control form-control-sm border-left-success" value="{{ $d->gmv_tt }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="small font-weight-bold">Provinsi</label>
                                                <select name="provinsi" class="form-control form-control-sm select-choices-edit" required>
                                                    <optgroup label="Jawa">
                                                        <option value="DKI Jakarta" {{ $d->provinsi == 'DKI Jakarta' ? 'selected' : '' }}>DKI Jakarta</option>
                                                        <option value="Banten" {{ $d->provinsi == 'Banten' ? 'selected' : '' }}>Banten</option>
                                                        <option value="Jawa Barat" {{ $d->provinsi == 'Jawa Barat' ? 'selected' : '' }}>Jawa Barat</option>
                                                        <option value="Jawa Tengah" {{ $d->provinsi == 'Jawa Tengah' ? 'selected' : '' }}>Jawa Tengah</option>
                                                        <option value="DI Yogyakarta" {{ $d->provinsi == 'DI Yogyakarta' ? 'selected' : '' }}>DI Yogyakarta</option>
                                                        <option value="Jawa Timur" {{ $d->provinsi == 'Jawa Timur' ? 'selected' : '' }}>Jawa Timur</option>
                                                    </optgroup>

                                                    <optgroup label="Sumatra">
                                                        <option value="Aceh" {{ $d->provinsi == 'Aceh' ? 'selected' : '' }}>Aceh</option>
                                                        <option value="Sumatera Utara" {{ $d->provinsi == 'Sumatera Utara' ? 'selected' : '' }}>Sumatera Utara</option>
                                                        <option value="Sumatera Barat" {{ $d->provinsi == 'Sumatera Barat' ? 'selected' : '' }}>Sumatera Barat</option>
                                                        <option value="Riau" {{ $d->provinsi == 'Riau' ? 'selected' : '' }}>Riau</option>
                                                        <option value="Kepulauan Riau" {{ $d->provinsi == 'Kepulauan Riau' ? 'selected' : '' }}>Kepulauan Riau</option>
                                                        <option value="Jambi" {{ $d->provinsi == 'Jambi' ? 'selected' : '' }}>Jambi</option>
                                                        <option value="Bengkulu" {{ $d->provinsi == 'Bengkulu' ? 'selected' : '' }}>Bengkulu</option>
                                                        <option value="Sumatera Selatan" {{ $d->provinsi == 'Sumatera Selatan' ? 'selected' : '' }}>Sumatera Selatan</option>
                                                        <option value="Kepulauan Bangka Belitung" {{ $d->provinsi == 'Kepulauan Bangka Belitung' ? 'selected' : '' }}>Kepulauan Bangka Belitung</option>
                                                        <option value="Lampung" {{ $d->provinsi == 'Lampung' ? 'selected' : '' }}>Lampung</option>
                                                    </optgroup>

                                                    <optgroup label="Kalimantan">
                                                        <option value="Kalimantan Barat" {{ $d->provinsi == 'Kalimantan Barat' ? 'selected' : '' }}>Kalimantan Barat</option>
                                                        <option value="Kalimantan Tengah" {{ $d->provinsi == 'Kalimantan Tengah' ? 'selected' : '' }}>Kalimantan Tengah</option>
                                                        <option value="Kalimantan Selatan" {{ $d->provinsi == 'Kalimantan Selatan' ? 'selected' : '' }}>Kalimantan Selatan</option>
                                                        <option value="Kalimantan Timur" {{ $d->provinsi == 'Kalimantan Timur' ? 'selected' : '' }}>Kalimantan Timur</option>
                                                        <option value="Kalimantan Utara" {{ $d->provinsi == 'Kalimantan Utara' ? 'selected' : '' }}>Kalimantan Utara</option>
                                                    </optgroup>

                                                    <optgroup label="Sulawesi">
                                                        <option value="Sulawesi Utara" {{ $d->provinsi == 'Sulawesi Utara' ? 'selected' : '' }}>Sulawesi Utara</option>
                                                        <option value="Gorontalo" {{ $d->provinsi == 'Gorontalo' ? 'selected' : '' }}>Gorontalo</option>
                                                        <option value="Sulawesi Tengah" {{ $d->provinsi == 'Sulawesi Tengah' ? 'selected' : '' }}>Sulawesi Tengah</option>
                                                        <option value="Sulawesi Barat" {{ $d->provinsi == 'Sulawesi Barat' ? 'selected' : '' }}>Sulawesi Barat</option>
                                                        <option value="Sulawesi Selatan" {{ $d->provinsi == 'Sulawesi Selatan' ? 'selected' : '' }}>Sulawesi Selatan</option>
                                                        <option value="Sulawesi Tenggara" {{ $d->provinsi == 'Sulawesi Tenggara' ? 'selected' : '' }}>Sulawesi Tenggara</option>
                                                    </optgroup>

                                                    <optgroup label="Bali & Nusa Tenggara">
                                                        <option value="Bali" {{ $d->provinsi == 'Bali' ? 'selected' : '' }}>Bali</option>
                                                        <option value="Nusa Tenggara Barat" {{ $d->provinsi == 'Nusa Tenggara Barat' ? 'selected' : '' }}>Nusa Tenggara Barat</option>
                                                        <option value="Nusa Tenggara Timur" {{ $d->provinsi == 'Nusa Tenggara Timur' ? 'selected' : '' }}>Nusa Tenggara Timur</option>
                                                    </optgroup>

                                                    <optgroup label="Maluku & Papua">
                                                        <option value="Maluku" {{ $d->provinsi == 'Maluku' ? 'selected' : '' }}>Maluku</option>
                                                        <option value="Maluku Utara" {{ $d->provinsi == 'Maluku Utara' ? 'selected' : '' }}>Maluku Utara</option>
                                                        <option value="Papua" {{ $d->provinsi == 'Papua' ? 'selected' : '' }}>Papua</option>
                                                        <option value="Papua Barat" {{ $d->provinsi == 'Papua Barat' ? 'selected' : '' }}>Papua Barat</option>
                                                        <option value="Papua Selatan" {{ $d->provinsi == 'Papua Selatan' ? 'selected' : '' }}>Papua Selatan</option>
                                                        <option value="Papua Tengah" {{ $d->provinsi == 'Papua Tengah' ? 'selected' : '' }}>Papua Tengah</option>
                                                        <option value="Papua Pegunungan" {{ $d->provinsi == 'Papua Pegunungan' ? 'selected' : '' }}>Papua Pegunungan</option>
                                                        <option value="Papua Barat Daya" {{ $d->provinsi == 'Papua Barat Daya' ? 'selected' : '' }}>Papua Barat Daya</option>
                                                    </optgroup>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="small font-weight-bold">Gender</label>
                                                <select name="gender" class="form-control form-control-sm" required>
                                                    <option value="Pria" {{ $d->gender == 'Pria' ? 'selected' : '' }}>Pria</option>
                                                    <option value="Wanita" {{ $d->gender == 'Wanita' ? 'selected' : '' }}>Wanita</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-danger">Kategori</label>
                                        <div class="p-2 border rounded bg-white shadow-inner" style="max-height: 120px; overflow-y: auto;">
                                            @php 
                                                // Merubah string "Beauty, Fashion" menjadi array ['Beauty', 'Fashion']
                                                $selectedCats = explode(', ', $d->category); 
                                            @endphp
                                            @foreach($categories as $cat)
                                            <div class="custom-control custom-checkbox py-1">
                                                <input type="checkbox" name="categories_array[]" value="{{ $cat }}" 
                                                    class="custom-control-input" id="edit_cat_{{ $d->id }}_{{ $loop->index }}"
                                                    {{ in_array($cat, $selectedCats) ? 'checked' : '' }}>
                                                <label class="custom-control-label small cursor-pointer" for="edit_cat_{{ $d->id }}_{{ $loop->index }}">{{ $cat }}</label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-4 border-left-success">
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-success mb-3 border-bottom pb-2"><i class="fas fa-tags mr-2"></i>Rate Card</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group"><label class="small font-weight-bold">Detail</label><input type="text" name="detail" class="form-control form-control-sm" value="{{ $d->detail }}"></div>
                                            <div class="form-group"><label class="small font-weight-bold">Reels IG</label><input type="text" name="reels" class="form-control form-control-sm" value="{{ $d->reels }}"></div>
                                            <div class="form-group"><label class="small font-weight-bold">VT</label><input type="text" name="vt" class="form-control form-control-sm" value="{{ $d->vt }}"></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group"><label class="small font-weight-bold">Mirroring</label><input type="text" name="mirroring" class="form-control form-control-sm" value="{{ $d->mirroring }}"></div>
                                            <div class="form-group"><label class="small font-weight-bold">Kerkun</label><input type="text" name="kerkun" class="form-control form-control-sm" value="{{ $d->kerkun }}"></div>
                                            <div class="form-group"><label class="small font-weight-bold">IG Story</label><input type="text" name="story" class="form-control form-control-sm" value="{{ $d->story }}"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm border-left-info">
                                <div class="card-body">
                                    <div class="bg-light p-2 rounded mb-2 border-left-orange" style="border-left: 3px solid #ee4d2d;">
                                        <label class="small font-weight-bold mb-1 text-uppercase" style="color:#ee4d2d">Shopee</label>
                                        <div class="row mx-0">
                                            <div class="col-4 px-1"><input type="text" name="user_shopee" class="form-control form-control-sm" value="{{ $d->user_shopee }}" placeholder="User"></div>
                                            <div class="col-4 px-1"><input type="text" name="url_shopee" class="form-control form-control-sm" value="{{ $d->url_shopee }}" placeholder="URL"></div>
                                            <div class="col-4 px-1"><input type="number" name="fol_shopee" class="form-control form-control-sm" value="{{ $d->fol_shopee }}" placeholder="Fol"></div>
                                        </div>
                                    </div>
                                    <div class="bg-light p-2 rounded border-left-purple" style="border-left: 3px solid #833ab4;">
                                        <label class="small font-weight-bold mb-1 text-uppercase" style="color:#833ab4">Instagram</label>
                                        <div class="row mx-0">
                                            <div class="col-4 px-1"><input type="text" name="user_ig" class="form-control form-control-sm" value="{{ $d->user_ig }}" placeholder="User"></div>
                                            <div class="col-4 px-1"><input type="text" name="url_ig" class="form-control form-control-sm" value="{{ $d->url_ig }}" placeholder="URL"></div>
                                            <div class="col-4 px-1"><input type="number" name="fol_ig" class="form-control form-control-sm" value="{{ $d->fol_ig }}" placeholder="Fol"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white py-3 border-top">
                    <button class="btn btn-light btn-sm px-4 border" type="button" data-dismiss="modal">Batal</button>
                    <button class="btn btn-warning btn-sm px-4 font-weight-bold shadow-sm" type="submit">
                        <i class="fas fa-sync-alt mr-1"></i> Update Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="deleteModal{{ $d->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="exampleModalLabel text-uppercase">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Konfirmasi Hapus
                </h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-trash-alt fa-4x text-danger mb-3"></i>
                <p class="mb-0">Apakah Anda yakin ingin menghapus data affiliator:</p>
                <h5 class="font-weight-bold text-dark mt-2">{{ $d->user_tt }}</h5>
                <p class="text-muted small">*Data yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-center">
                <button class="btn btn-secondary btn-sm px-4" type="button" data-dismiss="modal">Batal</button>
                <form action="{{ route('social_creator.destroy', $d->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm px-4 shadow-sm">Ya, Hapus Data</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const config = {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false,
            placeholder: true,
            position: 'bottom',
            // Hapus bg-white dari sini agar tidak error
            classNames: {
                containerInner: 'choices__inner', 
            }
        };

        // Inisialisasi Modal Tambah
        const elementTambah = document.getElementById('choices-provinsi');
        if (elementTambah) new Choices(elementTambah, config);

        // Inisialisasi Modal Edit
        const elementsEdit = document.querySelectorAll('.select-choices-edit');
        elementsEdit.forEach(el => new Choices(el, config));
        
        const elementsFilter = document.getElementById('filterLokasi');
        if (elementsFilter) new Choices(elementsFilter, config);

        const table = $('#dataTables1').DataTable();

        // 2. Daftarkan Fungsi Filter Global (Vanilla JS Logic)
        // DataTable akan menjalankan fungsi ini untuk SETIAP baris di SEMUA halaman
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            // Ambil elemen asli barisnya (untuk baca dataset)
            const row = table.row(dataIndex).node();
            const cells = row.querySelectorAll('td');

            // --- FILTER ANGKA (Ambil dari dataset agar akurat) ---
            var platform = $('#filterPlatform').val();
            var platformGMV = $('#filterPlatformGMV').val();
            const minFolInput = parseInt(document.getElementById('filterFollowers').value) || 0;
            const minGMVInput = parseInt(document.getElementById('filterGMV').value) || 0;

            const folTT  = parseInt(cells[3].dataset.fol) || 0;
            const folSHP = parseInt(cells[6].dataset.fol) || 0;
            const folIG  = parseInt(cells[9].dataset.fol) || 0;
            const gmvTT  = parseInt(cells[11].dataset.gmv) || 0;
            const gmvSHP = parseInt(cells[12].dataset.gmv) || 0;

            // --- FILTER TEKS ---
            const fKat = document.getElementById('filterKategori').value.toLowerCase();
            const fLok = document.getElementById('filterLokasi').value.toLowerCase();
            const fGen = document.getElementById('filterGender').value.toLowerCase().trim();

            const txtKat = data[10].toLowerCase(); // data[index] adalah isi teks kolom
            const txtLok = data[13].toLowerCase();
            const txtGen = data[14].toLowerCase().trim();

            // --- LOGIKA PERBANDINGAN ---
            var matchFol = false;
            if (platform === "tiktok") {
                matchFol = (folTT >= minFolInput);
            } else if (platform === "instagram") {
                matchFol = (folIG >= minFolInput);
            } else if (platform === "shopee") {
                matchFol = (folSHP >= minFolInput);
            } else {
                // Default: Lolos jika salah satu dari ketiganya memenuhi
                matchFol = (folTT >= minFolInput || folSHP >= minFolInput || folIG >= minFolInput);
            }

            var matchGMV = false;
            if (platformGMV === "tiktok") {
                matchGMV = (gmvTT >= minGMVInput);
            } else if (platformGMV === "shopee") {
                matchGMV = (gmvSHP >= minGMVInput);
            } else {
                // Default: Lolos jika salah satu dari ketiganya memenuhi
                matchGMV = (gmvTT >= minGMVInput || gmvSHP >= minGMVInput);
            }
            const matchKat = fKat === "" || txtKat.includes(fKat);
            const matchLok = fLok === "" || txtLok.includes(fLok);
            const matchGen = fGen === "" || txtGen === fGen;

            // Kembalikan true jika baris ini boleh tampil
            return matchFol && matchGMV && matchKat && matchLok && matchGen;
        });

        // 3. Hubungkan Event Listener ke Input (Vanilla JS)
        const filterInputs = ['filterKategori', 'filterLokasi', 'filterFollowers', 'filterGMV', 'filterGender'];
        filterInputs.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                // Setiap kali input berubah, suruh DataTable gambar ulang (draw)
                el.addEventListener('input', () => table.draw());
                el.addEventListener('change', () => table.draw());
            }
        });

        // Fungsi Reset
        window.resetFilter = function() {
            document.getElementById('filterForm').reset();
            table.draw();
        };
    });
</script>

@include('partials.footer')