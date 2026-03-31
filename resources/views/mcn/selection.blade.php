@include('partials.header')

<style>
    /* Styling khusus agar tabel tidak terlihat terlalu kaku */
    #dataTableEikyoo thead th {
        vertical-align: middle;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .custom-checkbox-lg .custom-control-label::before,
    .custom-checkbox-lg .custom-control-label::after {
        top: 0.1rem !important;
        width: 1.25rem !important;
        height: 1.25rem !important;
        cursor: pointer;
    }
    .table td {
        vertical-align: middle !important;
    }
    .badge-price {
        font-size: 11px;
        padding: 5px 8px;
    }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Database Influencer</h1>
        <button class="btn btn-primary shadow-sm rounded-pill px-4" data-toggle="modal" data-target="#modalTambah">
            <i class="fas fa-plus fa-sm text-white-50 mr-2"></i> Tambah Data Influencer
        </button>
    </div>

    <div class="card shadow-sm mb-4 border-left-primary">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped text-nowrap" id="dataTableEikyoo" width="100%" cellspacing="0" style="font-size: 12.5px;">
                    <thead class="bg-primary text-white text-center">
                        <tr>
                            <th rowspan="2">No</th>
                            <th colspan="4" class="bg-dark">Status Monitoring</th>
                            
                            <th rowspan="2">Username IG</th>
                            <th rowspan="2">Link IG</th>
                            <th rowspan="2">Username TT</th>
                            <th rowspan="2">Link TT</th>
                            <th rowspan="2">Username SPE</th>
                            <th rowspan="2">Link SPE</th>
                            <th rowspan="2">Alamat</th>
                            <th rowspan="2">No WA</th>
                            <th rowspan="2">GMV</th>
                            <th rowspan="2">Harga CO Real</th>
                            
                            <th colspan="{{ count($productHeaders) }}" class="bg-info text-white">Harga Produk</th>
                            <th rowspan="2" class="bg-light text-primary">Total Harga</th>
                            
                            <th colspan="{{ count($shippingHeaders) }}" class="bg-success text-white">Shipping Fee</th>
                            <th rowspan="2" class="bg-light text-success">Total Shipping</th>
                            <th rowspan="2">Ongkir</th>
                            <th rowspan="2">Biaya Layanan Pembeli</th>
                            <th rowspan="2">Biaya Penanganan</th>
                            
                            <th rowspan="2">Action</th>
                        </tr>
                        <tr>
                            <th class="bg-dark border-0">4 Konten</th>
                            <th class="bg-dark border-0">CB</th>
                            <th class="bg-dark border-0">TD/PD</th>
                            <th class="bg-dark border-0">Cek</th>

                            @foreach($productHeaders as $namaKategori)
                                <th class="bg-info border-0 font-weight-normal">{{ $namaKategori }}</th>
                            @endforeach

                            @foreach($shippingHeaders as $namaKategori)
                                <th class="bg-success border-0 font-weight-normal">{{ $namaKategori }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $index => $item)
                            <tr>
                                <td class="text-center font-weight-bold">{{ $index + 1 }}</td>
                                
                                <td class="text-center">
                                    <div class="custom-control custom-checkbox custom-checkbox-lg">
                                        <input type="checkbox" class="custom-control-input" id="done4_{{ $item->id }}" {{ $item->done_4_konten == "true" ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="done4_{{ $item->id }}"></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="custom-control custom-checkbox custom-checkbox-lg">
                                        <input type="checkbox" class="custom-control-input" id="cb_{{ $item->id }}" {{ $item->cb == "true" ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="cb_{{ $item->id }}"></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="custom-control custom-checkbox custom-checkbox-lg">
                                        <input type="checkbox" class="custom-control-input" id="tdpd_{{ $item->id }}" {{ $item->td_pd == "true" ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="tdpd_{{ $item->id }}"></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="custom-control custom-checkbox custom-checkbox-lg">
                                        <input type="checkbox" class="custom-control-input" id="cek_{{ $item->id }}" {{ $item->cek == "true" ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="cek_{{ $item->id }}"></label>
                                    </div>
                                </td>

                                <td>{{ $item->user_ig }}</td>
                                <td class="text-center">
                                    <a href="{{ $item->url_ig }}" target="_blank" class="btn btn-sm btn-outline-danger shadow-sm"><i class="fab fa-instagram"></i> Visit</a>
                                </td>
                                <td>{{ $item->user_tt }}</td>
                                <td class="text-center">
                                    <a href="{{ $item->url_tt }}" target="_blank" class="btn btn-sm btn-outline-dark shadow-sm"><i class="fab fa-tiktok"></i> Visit</a>
                                </td>
                                <td>{{ $item->user_shopee }}</td>
                                <td class="text-center">
                                    <a href="{{ $item->url_spe }}" target="_blank" class="btn btn-sm btn-outline-warning shadow-sm"><i class="fas fa-shopping-bag"></i> Visit</a>
                                </td>
                                <td class="text-truncate" style="max-width: 150px;">{{ $item->alamat }}</td>
                                <td class="text-center">
                                    <a href="https://wa.me/{{ $item->contact }}" class="text-success font-weight-bold" target="_blank">{{ $item->contact }}</a>
                                </td>
                                <td class="font-weight-bold">Rp {{ number_format($item->gmv, 2, ',', '.') }}</td>
                                <td class="font-weight-bold text-info">Rp {{ number_format($item->harga_co_real, 2, ',', '.') }}</td>

                                @foreach($hargaProduct as $header)
                                    <td class="text-muted">Rp. {{ number_format($header, 2, '.', ',') }}</td> 
                                @endforeach
                                    <td class="bg-light font-weight-bold text-primary">Rp {{ number_format($project->total_harga_produk, 2, '.', ',') }}</td>

                                @foreach($hargaFee as $header)
                                    <td class="text-muted">Rp {{ number_format((int)$header, 2, '.', ',') }}</td>
                                @endforeach
                                <td class="bg-light font-weight-bold text-success">Rp {{ number_format($project->total_shipping_fee, 2, '.', ',') }}</td>
                                <td class="bg-light font-weight-bold text-success">Rp {{ number_format($project->ongkir, 2, '.', ',') }}</td>
                                <td class="bg-light font-weight-bold text-success">Rp {{ number_format($project->biaya_layanan_pembeli, 2, '.', ',') }}</td>
                                <td class="bg-light font-weight-bold text-success">Rp {{ number_format($project->biaya_penanganan, 2, '.', ',') }}</td>

                                <td class="text-center">
                                    <button class="btn btn-sm btn-info rounded-circle mr-1" data-toggle="modal" data-target="#modalEdit{{ $item->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger rounded-circle" data-toggle="modal" data-target="#modalHapus{{ $item->id }}"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTambahLabel">
                    <i class="fas fa-user-plus mr-2"></i>Tambah Data Influencer
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('selection.store') }}" method="POST">
                @csrf
                <div class="modal-body bg-light">
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">Pilih Affiliator</label>
                        <select name="affiliator_id" class="form-control border-0 shadow-sm" required>
                            <option value="" disabled selected>-- Pilih Affiliator --</option>
                            @foreach($affiliators as $aff)
                                <option value="{{ $aff->id_eikyoo }}">{{ $aff->user_tt ?: ($aff->user_ig ?: ($aff->user_shopee ?: '-')) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">Tipe Seleksi</label>
                        <input type="text" name="type" class="form-control border-0 shadow-sm" required>
                    </div>

                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-dark">Harga Checkout Real</label>
                        <div class="input-group shadow-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-0 text-muted small">Rp</span>
                            </div>
                            <input type="text" name="harga_co_real" class="form-control border-0 format-rupiah" placeholder="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach ($data as $item)
<div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEditLabel{{ $item->id }}">
                    <i class="fas fa-edit mr-2"></i>Edit Data Influencer
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('selection.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body bg-light">
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">Affiliator</label>
                        <select name="affiliator_id" class="form-control border-0 shadow-sm">
                            <option value="{{ $item->affiliator_id }}" selected>
                                {{ $item->user_tt ?: ($item->user_ig ?: ($item->user_shopee ?: 'Data Terpilih')) }}
                            </option>

                            @if($affiliators->count() > 0)
                                <option disabled>--------- Ganti Ke Affiliator Lain ---------</option>
                                
                                @foreach($affiliators as $aff)
                                    <option value="{{ $aff->id_eikyoo }}">
                                        {{ $aff->user_tt ?: ($aff->user_ig ?: ($aff->user_shopee ?: '-')) }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">Tipe Seleksi</label>
                        <input type="text" name="type" class="form-control border-0 shadow-sm" value="{{ $item->type }}" required>
                    </div>

                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-dark">Harga Checkout Real</label>
                        <div class="input-group shadow-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-0 text-muted small">Rp</span>
                            </div>
                            <input type="text" name="harga_co_real" class="form-control border-0 format-rupiah" 
                                   value="{{ number_format($item->harga_co_real, 0, ',', '.') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info rounded-pill px-4 shadow">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modalHapus{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="modalHapusLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document"> <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <i class="fas fa-exclamation-circle text-danger fa-3x mb-3"></i>
                
                <h5 class="font-weight-bold">Hapus Data?</h5>
                <p class="text-muted small">
                    Apakah Anda yakin ingin menghapus data <strong>{{ $item->user_tt ?: ($item->user_ig ?: 'Influencer ini') }}</strong>? Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="d-flex justify-content-center mt-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 mr-2" data-dismiss="modal">Batal</button>
                    
                    <form action="{{ route('selection.destroy', $item->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <script>
$(document).ready(function() {
    // Tangkap event klik pada checkbox status
    $('.custom-control-input').on('change', function() {
        let checkbox = $(this);
        let id = checkbox.attr('id').split('_')[1]; // Ambil ID dari id="cek_123"
        let field = checkbox.attr('id').split('_')[0]; // Ambil nama kolom (done4, cb, tdpd, cek)
        let isChecked = checkbox.is(':checked') ? 'true' : 'false';

        // Mapping nama field ke kolom database yang benar
        let columnMap = {
            'done4': 'done_4_konten',
            'cb': 'cb',
            'tdpd': 'td/pd',
            'cek': 'cek'
        };

        let dbColumn = columnMap[field];

        // Jalankan AJAX
        $.ajax({
            url: "{{ route('selection.updateStatus') }}", // Ganti dengan nama route Anda
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                column: dbColumn,
                value: isChecked
            },
            success: function(response) {
                // Notifikasi sukses kecil di pojok (Toast)
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1000,
                    timerProgressBar: true
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Status updated!'
                });
            },
            error: function(xhr) {
                // Jika error, kembalikan posisi checkbox
                checkbox.prop('checked', !checkbox.is(':checked'));
                alert('Gagal mengupdate status. Silakan coba lagi.');
            }
        });
    });
});
function formatRupiah(angka, prefix) {
    var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? 'Rp' + rupiah : '');
}

$(document).on('keyup', '.format-rupiah', function() {
    $(this).val(formatRupiah($(this).val()));
});

</script>

@include('partials.footer')