@include('partials.header')
<style>
    /* Menghilangkan garis pinggir luar tabel agar terlihat modern */
    .table-responsive {
        border: none;
    }

    /* Styling Header: Dark background dengan teks putih */
    #dataTable thead th {
        background-color: #4e73df;
        color: white;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 1px;
        vertical-align: middle;
        border: none;
    }

    /* Row Styling */
    .table td {
        vertical-align: middle !important;
        font-size: 13px;
        color: #5a5c69;
    }

    /* Efek halus saat kursor diarahkan ke baris tabel */
    .table-hover tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.05);
        transition: 0.3s;
    }

    /* Custom Badge untuk Priority */
    .badge-priority {
        width: 80px;
        padding: 6px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 10px;
    }

    /* Button Action Styling */
    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        line-height: 32px;
        border-radius: 8px;
    }
</style>

<div class="card-body">
    <div class="card-header py-3 d-flex justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Manajemen Project</h6>
        <button class="btn btn-primary btn-sm rounded-pill px-3" data-toggle="modal" data-target="#modalTambah">
            <i class="fas fa-plus mr-1"></i> Tambah Project
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover text-center" id="dataTable" width="100%" cellspacing="0">
            <thead class="thead-light">
                <tr>
                    <th>No</th>
                    <th>Project</th>
                    <th>Barang</th>
                    <th>Shipping</th>
                    <th>Ongkir</th>
                    <th>Biaya Layanan</th>
                    <th>Biaya Penanganan</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                    @php
                        // Pastikan data didecode menjadi array
                        $barangArray = is_array($d->barang) ? $d->barang : json_decode($d->barang, true);
                        $shippingArray = is_array($d->shipping) ? $d->shipping : json_decode($d->shipping, true);

                        // Ambil objek pertama dari array data
                        $itemBarang = $barangArray[0] ?? [];
                        $itemShipping = $shippingArray[0] ?? [];
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="font-weight-bold text-dark">{{ $d->project }}</td>
                        
                        <td>
                            <div class="d-flex flex-wrap justify-content-center">
                                @if(!empty($itemBarang))
                                @foreach($itemBarang as $key => $val)
                                @if(str_contains($key, 'kategori_barang'))
                                @php
                                                // Mengambil angka indeks dari key
                                                $index = str_replace('kategori_barang_', '', $key);
                                                $hargaKey = 'harga_barang_' . $index;
                                                $harga = $itemBarang[$hargaKey] ?? 0;
                                                @endphp
                                            
                                            <div class="badge badge-light border m-2 shadow-sm d-inline-flex flex-column align-items-center" 
                                            style="border-radius: 12px; border-color: #dee2e6 !important; min-width: 130px;">
                                            
                                            <div class="px-2 pt-2 text-dark font-weight-bold" style="font-size: 0.95rem; letter-spacing: 0.3px;">
                                                    <i class="fas fa-box mr-1 text-primary"></i> 
                                                    {{ is_array($val) ? implode(', ', $val) : $val }}
                                                </div>

                                                <div class="px-2 pb-2 text-primary font-weight-bold" style="font-size: 0.9rem;">
                                                    Rp {{ number_format((float)$harga, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="text-muted small italic">Tidak ada data barang</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            <div class="d-flex flex-wrap justify-content-center">
                                @if(!empty($itemShipping))
                                    @foreach($itemShipping as $key => $val)
                                        @if(str_contains($key, 'kategori_shipping'))
                                            @php
                                                $indexShipping = str_replace('kategori_shipping_', '', $key);
                                                $hargaShippingKey = 'harga_shipping_' . $indexShipping;
                                                $hargaShipping = $itemShipping[$hargaShippingKey] ?? 0;
                                            @endphp
                                            
                                            <div class="badge badge-light border m-2 shadow-sm d-inline-flex flex-column align-items-center" 
                                                style="border-radius: 12px; border-color: #d4edda !important; min-width: 130px; transition: 0.3s;">
                                                
                                                <div class="px-2 pt-2 text-dark font-weight-bold" style="font-size: 0.95rem; letter-spacing: 0.3px;">
                                                    <i class="fas fa-truck-moving mr-1 text-success"></i> 
                                                    {{ is_array($val) ? implode(', ', $val) : $val }}
                                                </div>

                                                <div class="px-2 pb-2 text-success font-weight-bold" style="font-size: 0.9rem;">
                                                    Rp {{ number_format((float)$hargaShipping, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="text-muted small italic">Tidak ada data shipping</span>
                                @endif
                            </div>
                        </td>

                        <td class="font-weight-bold text-dark">Rp {{ number_format((float)$d->ongkir, 0, ',', '.') }}</td>
                        <td class="font-weight-bold text-dark">Rp {{ number_format((float)$d->biaya_layanan_pembeli, 0, ',', '.') }}</td>
                        <td class="font-weight-bold text-dark">Rp {{ number_format((float)$d->biaya_penanganan, 0, ',', '.') }}</td>

                        <td>
                            <div class="d-flex justify-content-center">
                                <button class="btn btn-info btn-action mr-1 shadow-sm" data-toggle="modal" data-target="#modalEdit{{ $d->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-action shadow-sm" data-toggle="modal" data-target="#modalHapus{{ $d->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('barang_client.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">Tambah Project Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body bg-light">
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">Nama Project</label>
                        <input type="text" name="project" class="form-control border-0 shadow-sm" placeholder="Masukkan nama project..." required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="font-weight-bold text-primary mb-2">
                                <i class="fas fa-box mr-1"></i> Daftar Barang
                            </label>
                            <div class="card border-0 shadow-sm rounded-lg mb-4">
                                <div class="card-body p-3" id="barang-container">
                                    <div class="barang-row mb-2">
                                        <div class="input-group">
                                            <input type="text" name="barang[]" class="form-control" placeholder="Nama Barang" required>
                                            <input type="text" name="harga_barang[]" class="form-control format-rupiah" placeholder="Harga Barang" required>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success add-barang" data-target="#barang-container">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="font-weight-bold text-success mb-2">
                                <i class="fas fa-truck mr-1"></i> Daftar Shipping
                            </label>
                            <div class="card border-0 shadow-sm rounded-lg mb-4">
                                <div class="card-body p-3" id="shipping-container">
                                    <div class="shipping-row mb-2">
                                        <div class="input-group">
                                            <input type="text" name="shipping[]" class="form-control" placeholder="Nama Barang Shipping" required>
                                            <input type="text" name="harga_shipping[]" class="form-control format-rupiah" placeholder="Harga Barang Shipping" required>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success add-shipping" data-target="#shipping-container">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark small text-uppercase">Ongkos Kirim</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-0 text-muted">Rp</span>
                                    </div>
                                    <input type="text" name="ongkir" class="form-control border-0 format-rupiah" placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark small text-uppercase">Biaya Layanan</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-0 text-muted">Rp</span>
                                    </div>
                                    <input type="text" name="biaya_layanan" class="form-control border-0 format-rupiah" placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark small text-uppercase">Biaya Penanganan</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-0 text-muted">Rp</span>
                                    </div>
                                    <input type="text" name="biaya_penanganan" class="form-control border-0 format-rupiah" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow">Simpan Project</button>
                </div>
            </div>
        </form>
    </div>
</div>

@foreach ($data as $d)
    
    @php
        // Pastikan data adalah array. Jika string JSON, decode dulu.
        $barangData = is_array($d->barang) ? $d->barang : json_decode($d->barang, true);
        $shippingData = is_array($d->shipping) ? $d->shipping : json_decode($d->shipping, true);

        // Jika data dibungkus dalam array lagi (seperti hasil store sebelumnya)
        $itemBarang = (isset($barangData[0]) && is_array($barangData[0])) ? $barangData[0] : $barangData;
        $itemShipping = (isset($shippingData[0]) && is_array($shippingData[0])) ? $shippingData[0] : $shippingData;
    @endphp

    <div class="modal fade" id="modalEdit{{ $d->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form action="{{ route('barang_client.update', $d->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-edit mr-2"></i>Edit Project: {{ $d->project }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body bg-light">
                        <div class="form-group mb-4">
                            <label class="font-weight-bold">Nama Project</label>
                            <input type="text" name="project" class="form-control border-0 shadow-sm" value="{{ $d->project }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="font-weight-bold text-primary mb-2">
                                    <i class="fas fa-box mr-1"></i> Daftar Barang & Harga
                                </label>
                                <div id="edit-barang-container-{{ $d->id }}">
                                    @php $i = 1; @endphp
                                    @while(isset($barangData[0]["kategori_barang_$i"]))
                                        <div class="barang-row mb-2">
                                            <div class="input-group shadow-sm">
                                                <input type="text" name="barang[]" class="form-control" 
                                                    value="{{ $barangData[0]["kategori_barang_$i"] }}" placeholder="Nama Barang">
                                                <input type="text" name="harga_barang[]" class="form-control format-rupiah" 
                                                    value="Rp {{ number_format($barangData[0]["harga_barang_$i"] ?? 0, 0, ',', '.') }}" placeholder="Harga">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-danger remove-barang border-0">-</button>
                                                </div>
                                            </div>
                                        </div>
                                        @php $i++; @endphp
                                    @endwhile
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm mt-2 add-barang" data-target="#edit-barang-container-{{ $d->id }}">
                                    <i class="fas fa-plus mr-1"></i> Tambah Barang
                                </button>
                            </div>

                            <div class="col-md-6">
                                <label class="font-weight-bold text-success mb-2">
                                    <i class="fas fa-truck mr-1"></i> Daftar Shipping & Harga
                                </label>
                                <div id="edit-shipping-container-{{ $d->id }}">
                                    @php $j = 1; @endphp
                                    @while(isset($shippingData[0]["kategori_shipping_$j"]))
                                        <div class="shipping-row mb-2">
                                            <div class="input-group shadow-sm">
                                                <input type="text" name="shipping[]" class="form-control" 
                                                    value="{{ $shippingData[0]["kategori_shipping_$j"] }}" placeholder="Nama Shipping">
                                                <input type="text" name="harga_shipping[]" class="form-control format-rupiah" 
                                                    value="Rp {{ number_format($shippingData[0]["harga_shipping_$j"] ?? 0, 0, ',', '.') }}" placeholder="Harga">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-danger remove-shipping border-0">-</button>
                                                </div>
                                            </div>
                                        </div>
                                        @php $j++; @endphp
                                    @endwhile
                                </div>
                                <button type="button" class="btn btn-outline-success btn-sm mt-2 add-shipping" data-target="#edit-shipping-container-{{ $d->id }}">
                                    <i class="fas fa-plus mr-1"></i> Tambah Shipping
                                </button>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark small text-uppercase">Ongkos Kirim</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-0 text-muted small">Rp</span>
                                        </div>
                                        <input type="number" name="ongkir" class="form-control border-0 format-rupiah" 
                                            value="{{ number_format($d->ongkir ?? 0, 0, ',', '.') }}" placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark small text-uppercase">Biaya Layanan</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-0 text-muted small">Rp</span>
                                        </div>
                                        <input type="number" name="biaya_layanan" class="form-control border-0 format-rupiah" 
                                            value="{{ number_format($d->biaya_layanan ?? 0, 0, ',', '.') }}" placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark small text-uppercase">Biaya Penanganan</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-0 text-muted small">Rp</span>
                                        </div>
                                        <input type="number" name="biaya_penanganan" class="form-control border-0 format-rupiah" 
                                            value="{{ number_format($d->biaya_penanganan ?? 0, 0, ',', '.') }}" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info rounded-pill px-4 shadow">Update Project</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalHapus{{ $d->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus project <b>{{ $d->project }}</b>?</p>
                    <p class="text-danger small">*Data yang dihapus tidak dapat dikembalikan.</p>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('barang_client.destroy', $d->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Ya, Hapus Sekarang</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endforeach
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
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

    $(document).on('click', '.add-barang', function() {
        let targetSelector = $(this).data('target');
        let $container = $(targetSelector);

        let newRow = `
            <div class="barang-row mb-2 mt-2">
                <div class="input-group shadow-sm">
                    <input type="text" name="barang[]" class="form-control" placeholder="Nama Barang" required>
                    <input type="text" name="harga_barang[]" class="form-control format-rupiah" placeholder="Harga Barang" required>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-barang">-</button>
                    </div>
                </div>
            </div>`;
        
        $container.append(newRow);
    });

    $(document).on('click', '.add-shipping', function() {
        let targetSelector = $(this).data('target');
        let $container = $(targetSelector);

        let newRow = `
            <div class="shipping-row mb-2 mt-2">
                <div class="input-group shadow-sm">
                    <input type="text" name="shipping[]" class="form-control" placeholder="Nama Barang Shipping" required>
                    <input type="text" name="harga_shipping[]" class="form-control format-rupiah" placeholder="Harga Barang Shipping" required>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-shipping">-</button>
                    </div>
                </div>
            </div>`;
        
        $container.append(newRow);
    });

    $(document).on('click', '.remove-barang', function() {
        $(this).closest('.barang-row').remove();
    });

    $(document).on('click', '.remove-shipping', function() {
        $(this).closest('.shipping-row').remove();
    });
</script>

@include('partials.footer')