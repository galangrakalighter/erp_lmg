@include('partials.header')

<div class="container-fluid mt-4 pb-5">
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-fingerprint mr-2"></i>Brand Identify</h6>
                    @if($role == 'Advertising' && !$identify || $role == 'Busines Unit Head' && !$identify)
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#tambahModalIdentify">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if ($identify)
                        {{-- Tampilan saat data ADA --}}
                        <div class="position-relative text-center">
                            @if($identify->identify)
                                <a href="{{ asset('storage/' . $identify->identify) }}" target="_blank" class="d-block mb-3">
                                    <img src="{{ asset('storage/' . $identify->identify) }}" 
                                        alt="Brand Identify" 
                                        class="img-fluid rounded shadow-sm border" 
                                        style="width: 100%; max-height: 500px; object-fit: contain; background-color: #f8f9fc;">
                                </a>
                            @else
                                <div class="alert alert-light text-center py-5 border">
                                    <i class="fas fa-image fa-3x text-gray-300 mb-3"></i>
                                    <p class="text-muted mb-0">File gambar tidak ditemukan</p>
                                </div>
                            @endif

                            {{-- Tombol Aksi melayang di pojok kanan atas --}}
                            @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                <div class="position-absolute" style="top: 10px; right: 10px;">
                                    <div class="btn-group shadow-sm">
                                        <button class="btn btn-warning btn-sm text-white" 
                                                data-toggle="modal" 
                                                data-target="#editModalIdentify_{{ $identify->id }}" 
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" 
                                                data-toggle="modal" 
                                                data-target="#hapusModalIdentify_{{ $identify->id }}" 
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Tampilan saat data KOSONG --}}
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-gray-200 mb-3"></i>
                            <p class="text-muted">Belum ada data Brand Identify. Silakan klik tombol Tambah.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-image mr-2"></i>Brand Image</h6>
                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#tambahModalImage">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="brand_image">
                            <thead class="bg-dark text-white">
                                <tr style="font-size: 0.85rem;">
                                    <th>No</th>
                                    <th>Brand Image</th>
                                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                    <th class="text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.875rem;">
                                @foreach ($image as $item)
                                    <tr>
                                        <th>{{ $loop->iteration }}</th>
                                        <th>{{ $item->nama_image }}</th>
                                        @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                        <th>
                                            <button class="btn btn-warning btn-sm text-white" 
                                                    data-toggle="modal" 
                                                    data-target="#editModalImage_{{ $item->id }}" 
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- Tombol Hapus --}}
                                            <button class="btn btn-danger btn-sm" 
                                                    data-toggle="modal" 
                                                    data-target="#deleteModalImage_{{ $item->id }}" 
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </th>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-palette mr-2"></i>Moodboard</h6>
                    @if($role == 'Advertising' && !$moodboard || $role == 'Busines Unit Head' && !$moodboard)
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#tambahModalMoodboard">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if ($moodboard)
                        {{-- Tampilan saat data ADA --}}
                        <div class="position-relative text-center">
                            @if($moodboard->moodboard)
                                <a href="{{ asset('storage/' . $moodboard->moodboard) }}" target="_blank" class="d-block mb-3">
                                    <img src="{{ asset('storage/' . $moodboard->moodboard) }}" 
                                        alt="Brand Identify" 
                                        class="img-fluid rounded shadow-sm border" 
                                        style="width: 100%; max-height: 500px; object-fit: contain; background-color: #f8f9fc;">
                                </a>
                            @else
                                <div class="alert alert-light text-center py-5 border">
                                    <i class="fas fa-image fa-3x text-gray-300 mb-3"></i>
                                    <p class="text-muted mb-0">File gambar tidak ditemukan</p>
                                </div>
                            @endif

                            {{-- Tombol Aksi melayang di pojok kanan atas --}}
                            @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                <div class="position-absolute" style="top: 10px; right: 10px;">
                                    <div class="btn-group shadow-sm">
                                        <button class="btn btn-warning btn-sm text-white" 
                                                data-toggle="modal" 
                                                data-target="#editModalMoodboard_{{ $moodboard->id }}" 
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" 
                                                data-toggle="modal" 
                                                data-target="#hapusModalMoodboard_{{ $moodboard->id }}" 
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Tampilan saat data KOSONG --}}
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-gray-200 mb-3"></i>
                            <p class="text-muted">Belum ada data Brand Identify. Silakan klik tombol Tambah.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-users mr-2"></i>Tagline</h6>
                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#tambahModalTagline">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tagline">
                            <thead class="bg-info text-white">
                                <tr style="font-size: 0.85rem;">
                                    <th>No</th>
                                    <th>Tagline</th>
                                    <th>Hashtag</th>
                                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                    <th>Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.875rem;">
                                @foreach ($tagline as $item)
                                <tr>
                                    <th>{{ $loop->iteration }}</th>
                                    <th>{{ $item->nama_tagline }}</th>
                                    <th>{{ $item->nama_hashtagline }}</th>
                                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                    <th>
                                        <button class="btn btn-warning btn-sm text-white" 
                                                data-toggle="modal" 
                                                data-target="#editModalTagline_{{ $item->id }}" 
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <button class="btn btn-danger btn-sm" 
                                                data-toggle="modal" 
                                                data-target="#deleteModalTagline_{{ $item->id }}" 
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </th>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-comments mr-2"></i>Gaya Komunikasi</h6>
                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#tambahModalKomunikasi">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="komunikasi">
                            <thead class="bg-info text-white">
                                <tr style="font-size: 0.85rem;">
                                    <th>No</th>
                                    <th>Gaya Bicara</th>
                                    <th>Gaya Bahasa</th>
                                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                    <th>Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.875rem;">
                                @foreach ($komunikasi as $item)
                                    <tr>
                                        <th>{{ $loop->iteration }}</th>
                                        <th>{{ $item->gaya_bicara }}</th>
                                        <th>{{ $item->gaya_bahasa }}</th>
                                        @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                        <th>
                                            <button class="btn btn-warning btn-sm text-white" 
                                                    data-toggle="modal" 
                                                    data-target="#editModalKomunikasi_{{ $item->id }}" 
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- Tombol Hapus --}}
                                            <button class="btn btn-danger btn-sm" 
                                                    data-toggle="modal" 
                                                    data-target="#deleteModalKomunikasi_{{ $item->id }}" 
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </th>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-flask mr-2"></i>Komposisi</h6>
                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#tambahModalKomposisi">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah
                    </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="komposisi">
                            <thead class="bg-info text-white">
                                <tr style="font-size: 0.85rem;">
                                    <th class="pl-4 border-right">No</th>
                                    <th colspan="2" class="text-center border">Komposisi</th>
                                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                    <th>Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.875rem;">
                                @foreach ($komposisi as $item)
                                    <tr>
                                        <th>{{ $loop->iteration }}</th>
                                        <th class="border-right border-left text-center">{{ $item->type_komposisi }}</th>
                                        <th class="border-right text-center">{{ $item->komposisi }}</th>
                                        @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                        <th>
                                            <button class="btn btn-warning btn-sm text-white" 
                                                    data-toggle="modal" 
                                                    data-target="#editModalKomposisi_{{ $item->id }}" 
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- Tombol Hapus --}}
                                            <button class="btn btn-danger btn-sm" 
                                                    data-toggle="modal" 
                                                    data-target="#deleteModalKomposisi_{{ $item->id }}" 
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </th>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-volume-up mr-2"></i>Audio Branding</h6>
                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#tambahModalAudio">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah
                    </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="audio">
                            <thead class="bg-info text-white">
                                <tr style="font-size: 0.85rem;">
                                    <th class="pl-4">No</th>
                                    <th>Audio Branding</th>
                                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                    <th>Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.875rem;">
                                @foreach ($audio as $item)
                                    <tr>
                                        <th>{{ $loop->iteration }}</th>
                                        <th>{{ $item->nama_audio }}</th>
                                        @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                        <th>
                                            <button class="btn btn-warning btn-sm text-white" 
                                                    data-toggle="modal" 
                                                    data-target="#editModalAudio_{{ $item->id }}" 
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- Tombol Hapus --}}
                                            <button class="btn btn-danger btn-sm" 
                                                    data-toggle="modal" 
                                                    data-target="#deleteModalAudio_{{ $item->id }}" 
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </th>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-star mr-2"></i>Brand Value</h6>
                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#tambahModalValue">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="segmentasi">
                            <thead class="bg-info text-white">
                                <tr style="font-size: 0.85rem;">
                                    <th>No</th>
                                    <th>Brand Value</th>
                                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                    <th class="text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.875rem;">
                                @foreach ($value as $item)
                                    <tr>
                                        <th>{{ $loop->iteration }}</th>
                                        <th>{{ $item->nama_value }}</th>
                                        @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                        <th>
                                            <button class="btn btn-warning btn-sm text-white" 
                                                    data-toggle="modal" 
                                                    data-target="#editModalValue_{{ $item->id }}" 
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- Tombol Hapus --}}
                                            <button class="btn btn-danger btn-sm" 
                                                    data-toggle="modal" 
                                                    data-target="#deleteModalValue_{{ $item->id }}" 
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </th>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-tools mr-2"></i>Alat Branding</h6>
                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#tambahModalAlat">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <table class="table table-hover align-middle mb-0" id="alatBranding">
                        <thead class="bg-info text-white">
                            <tr style="font-size: 0.85rem;">
                                <th>No</th>
                                <th>Alat Branding</th>
                                @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($alat as $item)
                                <tr>
                                    <th>{{$loop->iteration}}</th>
                                    <th>{{ $item->nama_alat }}</th>
                                    @if($role == 'Advertising' || $role == 'Busines Unit Head')
                                    <th>
                                        <button class="btn btn-warning btn-sm text-white" 
                                                data-toggle="modal" 
                                                data-target="#editModalAlat_{{ $item->id }}" 
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <button class="btn btn-danger btn-sm" 
                                                data-toggle="modal" 
                                                data-target="#deleteModalAlat_{{ $item->id }}" 
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </th>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include("cms_branding.identify")
@include("cms_branding.moodboard")
@include("cms_branding.alatBranding")
@include("cms_branding.image")
@include("cms_branding.komunikasi")
@include("cms_branding.tagline")
@include("cms_branding.value")
@include("cms_branding.audio")
@include("cms_branding.komposisi")
@include('partials.footer')