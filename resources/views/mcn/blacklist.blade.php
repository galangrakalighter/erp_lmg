@include('partials.header')

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Data Affiliator</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 m-0">
                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Affiliator</li>
            </ol>
        </nav>
    </div>

    <style>
        .dataTables_wrapper .row:first-child {
            padding: 15px;
            background: #fff;
            border-radius: 8px 8px 0 0;
            margin: 0;
        }

        .dataTables_length label {
            font-weight: 600;
            color: #858796;
            font-size: 0.8rem;
            margin-bottom: 0;
        }

        .dataTables_filter label {
            font-weight: 600;
            color: #858796;
            font-size: 0.8rem;
            margin-bottom: 0;
        }

        .dataTables_filter input {
            border-radius: 20px;
            padding: 5px 15px;
            border: 1px solid #d1d3e2;
            margin-left: 10px;
        }
        /* Card & Filter Styling */
        .filter-section {
            background: #f8f9fc;
            border-left: 4px solid #D8A7B1;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .filter-section:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* Table Core Styling */
        .table-custom {
            border: 1px solid #e3e6f0;
            border-collapse: separate !important;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
        }
        .table-custom thead th {
            background-color: #D8A7B1 !important;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 12px !important;
            vertical-align: middle;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.05) !important;
        }
        .table-custom tbody td {
            border: 1px solid #f1f1f1 !important;
            vertical-align: middle;
            padding: 10px 12px;
            font-size: 0.8rem;
            color: #4e73df;
            font-weight: 500;
        }
        
        /* Table Row Hover */
        .table-custom tbody tr:hover {
            background-color: rgba(216, 167, 177, 0.05) !important;
        }

        /* Status Colors */
        .bg-bulan {
            background-color: #FADADD !important;
            color: #b36b79 !important;
            font-weight: 800 !important;
            text-align: center;
        }
        .bg-blacklist {
            background-color: #1a1a1a !important;
            color: #ff4d4d !important;
            font-weight: bold;
            text-align: center;
            box-shadow: inset 0 0 10px rgba(255,0,0,0.1);
        }
        .text-orange {
            color: #e67e22 !important;
            font-weight: bold;
        }

        /* Link Badges */
        .btn-link-custom {
            padding: 2px 8px;
            border-radius: 20px;
            background: #f0f2f7;
            color: #4e73df;
            text-decoration: none !important;
            font-size: 0.7rem;
            font-weight: bold;
            transition: all 0.2s;
            border: 1px solid #d1d3e2;
            display: inline-block;
        }
        .btn-link-custom:hover {
            background: #4e73df;
            color: white;
            transform: translateY(-1px);
        }

        /* Icon Styles */
        .status-icon {
            font-size: 0.9rem;
            margin-right: 5px;
        }

        #modalHapus .modal-content {
            border-radius: 15px;
        }

        #modalHapus .fa-trash-alt {
            filter: drop-shadow(0 0 10px rgba(231, 74, 59, 0.2));
        }

        /* Membuat tombol terlihat lebih modern */
        #modalHapus .btn {
            padding: 10px;
            border-radius: 8px;
            letter-spacing: 0.5px;
        }
    </style>

    <div class="card shadow-sm mb-4 filter-section">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="small font-weight-bold text-uppercase text-muted">Bulan</label>
                    <select id="filterBulan" class="form-control form-control-sm select2 shadow-none">
                        <option value="">Semua Bulan</option>
                        @foreach($data->pluck('bulan')->unique() as $bulan)
                            <option value="{{ $bulan }}">{{ $bulan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold text-uppercase text-muted">Job Campaign</label>
                    <select id="filterJob" class="form-control form-control-sm select2">
                        <option value="">Semua Job</option>
                        @foreach($data->pluck('job')->unique() as $job)
                            <option value="{{ $job }}">{{ $job }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold text-uppercase text-muted">Status Keterangan</label>
                    <select id="filterKet" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        <option value="Tidak Mengerjakan Job">Tidak Mengerjakan Job</option>
                        <option value="Tidak Menyelesaikan Job">Tidak Menyelesaikan Job</option>
                        <option value="Cancel Campaign">Cancel Campaign</option>
                        <option value="Telat Menyelesaikan Job">Telat Menyelesaikan Job</option>
                        <option value="BLACK LIST">BLACK LIST</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button onclick="window.location.reload()" class="btn btn-sm btn-outline-secondary btn-block shadow-none">
                        <i class="fas fa-sync-alt fa-sm mr-1"></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow border-0 mb-4">
        <div class="card-header py-3 bg-white border-bottom-0 d-flex align-items-center justify-content-center">
            <div class="px-4 py-1 border-left border-right border-danger">
                <h5 class="m-0 font-weight-bold text-danger text-uppercase" style="letter-spacing: 3px;">Affiliator Report</h5>
            </div>
            <div class="card-header py-3 bg-white border-bottom-0 d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-danger btn-sm shadow-sm" data-toggle="modal" data-target="#modalTambah">
                    <i class="fas fa-plus fa-sm text-white-50 mr-2"></i> Tambah Blacklist
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom m-0" id="dataTableAffiliator" width="100%">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Job</th>
                            <th>Report</th>
                            <th>User IG</th>
                            <th>Link IG</th>
                            <th>User TT</th>
                            <th>Link TT</th>
                            <th>User SPE</th>
                            <th>Link SPE</th>
                            <th>Alamat</th>
                            <th>WhatsApp</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $d)
                            <tr>
                                <td class="bg-bulan">{{ $d->bulan }}</td>
                                <td class="text-center font-weight-bold text-dark">{{ $d->job }}</td>
                                <td class="text-center">
                                    @if($d->link_report)
                                        <a href="{{ $d->link_report }}" target="_blank" class="text-danger"><i class="fas fa-file-alt"></i></a>
                                    @else 
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-dark">{{ $d->user_ig }}</td>
                                <td><a href="{{ $d->url_ig }}" target="_blank" class="btn-link-custom">Visit IG</a></td>
                                <td class="text-dark">{{ $d->user_tt }}</td>
                                <td><a href="{{ $d->url_tt }}" target="_blank" class="btn-link-custom">Visit TT</a></td>
                                <td class="text-dark">{{ $d->user_shopee }}</td>
                                <td><a href="{{ $d->url_shopee }}" target="_blank" class="btn-link-custom">Shopee</a></td>
                                <td class="small">{{ Str::limit($d->alamat, 20) }}</td>
                                <td>@php
                                    // Menghapus '0' di awal dan menggantinya dengan '62'
                                    $waNumber = preg_replace('/^0/', '62', $d->contact);
                                @endphp
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="text-success font-weight-bold">
                                    {{ $d->contact }}
                                </a></td>
                                
                                @php
                                    $textKet = '-';
                                    $classKet = '';
                                    $icon = '';

                                    if($d->keterangan == 1) {
                                        $textKet = "Tidak Mengerjakan Job";
                                        $classKet = "bg-danger text-white font-weight-bold text-center";
                                        $icon = '<i class="fas fa-times-circle status-icon"></i>';
                                    } elseif($d->keterangan == 2) {
                                        $textKet = "Tidak Menyelesaikan Job";
                                        $classKet = "bg-warning text-dark font-weight-bold text-center";
                                        $icon = '<i class="fas fa-exclamation-triangle status-icon"></i>';
                                    } elseif($d->keterangan == 3) {
                                        $textKet = "Cancel Campaign";
                                        $classKet = "bg-blacklist";
                                        $icon = '<i class="fas fa-ban status-icon"></i>';
                                    } elseif($d->keterangan == 4) {
                                        $textKet = "Telat Menyelesaikan Job";
                                        $classKet = "bg-bulan text-orange text-center";
                                        $icon = '<i class="fas fa-clock status-icon"></i>';
                                    } elseif($d->keterangan == 5) {
                                        $textKet = "BLACK LIST";
                                        $classKet = "bg-blacklist";
                                        $icon = '<i class="fas fa-user-slash status-icon"></i>';
                                    }
                                @endphp
                                
                                <td class="{{ $classKet }}">
                                    {!! $icon !!} {{ $textKet }}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-light text-primary shadow-none" data-toggle="modal" data-target="#modalEdit{{ $d->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light text-danger shadow-none" data-toggle="modal" data-target="#modalHapus{{ $d->id }}">
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
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold">Tambah Data Blacklist</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('blacklist.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Pilih Kreator</label>
                            <select name="id_creator" class="form-control select-choices-creator" required>
                                @foreach($creators as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->user_ig ?? '-' }} (IG) / {{ $c->user_tt ?? '-' }} (TT)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Bulan</label>
                            <input type="text" name="bulan" class="form-control" placeholder="Contoh: JULI-AGUST" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Job Campaign</label>
                            <input type="text" name="job" class="form-control" placeholder="Nama Campaign" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Status Keterangan</label>
                            <select name="keterangan" class="form-control" required>
                                <option value="1">Tidak Mengerjakan Job</option>
                                <option value="2">Tidak Menyelesaikan Job</option>
                                <option value="3">Cancel Campaign</option>
                                <option value="4">Telat Menyelesaikan Job</option>
                                <option value="5">BLACK LIST</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold">Link Report (Optional)</label>
                            <input type="text" name="link_report" class="form-control" placeholder="https://...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach ($data as $d)
    <div class="modal fade" id="modalEdit{{ $d->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-edit mr-2"></i>Edit: {{ $d->user_ig ?? $d->user_tt }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('blacklist.update', $d->id) }}" method="POST">
                    @csrf 
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label class="font-weight-bold">Pilih Kreator</label>
                                <select name="id_creator" class="form-control select-choices-blacklist-edit" required>
                                    @foreach($creators_blacklist as $c)
                                        <option value="{{ $c->id }}" {{ $d->id_creator == $c->id ? 'selected' : '' }}>
                                            {{ $c->user_ig }} (IG) / {{ $c->user_tt }} (TT)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Bulan</label>
                                <input type="text" name="bulan" class="form-control" value="{{ $d->bulan }}" required>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Job Campaign</label>
                                <input type="text" name="job" class="form-control" value="{{ $d->job }}" required>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Keterangan Status</label>
                                <select name="keterangan" class="form-control">
                                    <option value="1" {{ $d->keterangan == 1 ? 'selected' : '' }}>Tidak Mengerjakan Job</option>
                                    <option value="2" {{ $d->keterangan == 2 ? 'selected' : '' }}>Tidak Menyelesaikan Job</option>
                                    <option value="3" {{ $d->keterangan == 3 ? 'selected' : '' }}>Cancel Campaign</option>
                                    <option value="4" {{ $d->keterangan == 4 ? 'selected' : '' }}>Telat Menyelesaikan Job</option>
                                    <option value="5" {{ $d->keterangan == 5 ? 'selected' : '' }}>BLACK LIST</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Link Report</label>
                                <input type="url" name="link_report" class="form-control" value="{{ $d->link_report }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalHapus{{ $d->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div style="height: 4px; background: linear-gradient(90deg, #e74a3b, #be2617); border-radius: 5px 5px 0 0;"></div>
                
                <div class="modal-body text-center p-4">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle shadow-sm" style="width: 80px; height: 80px;">
                            <i class="fas fa-trash-alt fa-2x text-danger animate__animated animate__pulse animate__infinite"></i>
                        </div>
                    </div>
                    
                    <h5 class="font-weight-bold text-dark mb-2">Konfirmasi Hapus</h5>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Anda yakin ingin menghapus data:</p>
                    <p class="font-weight-bold text-danger mb-3" style="font-size: 0.9rem;">
                        "{{ $d->user_tt != null ? $d->user_tt : $d->user_ig }}"
                    </p>
                    <div class="alert alert-warning py-2 px-3 mb-4" style="border-radius: 8px; border: none;">
                        <small class="text-dark font-weight-bold" style="font-size: 0.7rem;">
                            <i class="fas fa-exclamation-triangle mr-1"></i> TINDAKAN TIDAK DAPAT DIBATALKAN
                        </small>
                    </div>
                    
                    <form action="{{ route('blacklist.destroy', $d->id) }}" method="POST">
                        @csrf 
                        @method('DELETE')
                        <div class="row no-gutters">
                            <div class="col-6 pr-2">
                                <button type="button" class="btn btn-light btn-block font-weight-bold text-secondary border shadow-none" data-dismiss="modal" style="font-size: 0.75rem; border-radius: 10px; height: 45px;">
                                    BATAL
                                </button>
                            </div>
                            <div class="col-6 pl-2">
                                <button type="submit" class="btn btn-danger btn-block font-weight-bold shadow-sm" style="font-size: 0.75rem; border-radius: 10px; height: 45px;">
                                    YA, HAPUS
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@include('partials.footer')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#dataTableAffiliator').DataTable({
        "rowsGroup": [0, 1],
        "pageLength": 25,
        "dom": "<'row px-3 py-2'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row px-3 py-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        "ordering": false,
        "language": {
            "search": "Cari Cepat:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "paginate": {
                "previous": "<i class='fas fa-angle-left'></i>",
                "next": "<i class='fas fa-angle-right'></i>"
            }
        }
    });

    $('#filterBulan').on('change', function() { table.column(0).search(this.value).draw(); });
    $('#filterJob').on('change', function() { table.column(1).search(this.value).draw(); console.log(this.value);
     });
    $('#filterKet').on('change', function() { 
        var val = $.fn.dataTable.util.escapeRegex($(this).val());
        table.column(11).search(val ? '^' + val + '$' : '', true, false).draw(); 
    });
});
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

    const elementsCreator = document.querySelectorAll('.select-choices-creator');
    elementsCreator.forEach(el => {
        new Choices(el, {
            ...config,
            searchPlaceholderValue: 'Ketik username...',
        });
    });

    const elementsBlacklistEdit = document.querySelectorAll('.select-choices-blacklist-edit');
    elementsBlacklistEdit.forEach(el => {
        new Choices(el, {
            ...config,
            searchPlaceholderValue: 'Ketik username...',
        });
    });
});
</script>