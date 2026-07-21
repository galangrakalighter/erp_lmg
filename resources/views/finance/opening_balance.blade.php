@extends('layout.app')
@section('content')

    <h1 class="mt-4">Informasi Saldo Awal Akun</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="#">Finance</a></li>
        <li class="breadcrumb-item active">Saldo Awal</li>
    </ol>

    <div class="card shadow mb-4">
        <div class="card-header bg-light py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-end row-gap-2">
            <div>
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle mr-1"></i> Data Saldo Master Akun</h6>
                <small class="text-muted">Menampilkan posisi saldo pembuka akun berdasarkan periode yang dipilih.</small>
            </div>
            <div class="col-md-3 p-0">
                <label class="small font-weight-bold text-dark">Pilih Periode Saldo:</label>
                <input type="month" id="global_periode" class="form-control form-control-sm border-primary" value="{{ $periode }}" onchange="loadOpeningBalances()">
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped" id="tableOpeningBalance" width="100%" cellspacing="0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th width="15%">Kode Akun</th>
                            <th width="40%">Nama Akun</th>
                            <th width="15%" class="text-center">Tipe / Normal</th>
                            <th width="30%" class="text-right">Nilai Saldo Awal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody id="openingBalanceTableBody">
                        @if($coas->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Tidak ada data Master Akun (COA) yang aktif.</td>
                            </tr>
                        @else
                            @foreach($coas as $coa)
                            <tr>
                                <td class="font-weight-bold align-middle">{{ $coa->kode_akun }}</td>
                                <td class="align-middle">{{ $coa->nama_akun }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge {{ $coa->tipe_akun == 'D' ? 'badge-info' : 'badge-secondary' }} px-2 py-1">
                                        {{ $coa->tipe_akun == 'D' ? 'DEBIT' : 'KREDIT' }}
                                    </span>
                                </td>
                                <td class="text-right align-middle font-weight-bold text-dark">
                                    Rp. {{ number_format($coa->saldo_awal, 2, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script>
    // Helper untuk membuat format mata uang Rp (Format Indonesia) di JavaScript
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(angka);
    }

    // AJAX: Mengambil data saldo saat periode bulan diubah (Hanya untuk Read-Only)
    function loadOpeningBalances() {
        const periode = document.getElementById('global_periode').value;
        const tbody = document.getElementById('openingBalanceTableBody');

        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data saldo periode ${periode}...</td></tr>`;

        fetch(`/opening-balance?periode=${periode}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(response => {
            tbody.innerHTML = '';

            if(response.status === 'success' && response.data.length > 0) {
                
                // Inisialisasi formatter Rupiah langsung di sini agar performa looping cepat
                const rupiahFormatter = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 2
                });

                response.data.forEach(row => {
                    const badgeClass = row.tipe_akun === 'D' ? 'badge-info' : 'badge-secondary';
                    const badgeText = row.tipe_akun === 'D' ? 'DEBIT' : 'KREDIT';

                    // Konversi string/null dari database ke float agar tidak error saat di-format
                    const nominalSaldo = parseFloat(row.saldo_awal) || 0; 

                    const tr = `
                        <tr>
                            <td class="font-weight-bold align-middle">${row.kode_akun}</td>
                            <td class="align-middle">${row.nama_akun}</td>
                            <td class="text-center align-middle">
                                <span class="badge ${badgeClass} px-2 py-1">${badgeText}</span>
                            </td>
                            <td class="text-right align-middle font-weight-bold text-dark">
                                Rp. ${rupiahFormatter.format(nominalSaldo)}
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted">Tidak ada data COA aktif ditemukan.</td></tr>`;
            }
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Gagal memuat data dari server.</td></tr>`;
        });
    }
</script>

@endsection