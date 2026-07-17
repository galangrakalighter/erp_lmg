@extends('layout.app')
@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Preview Jurnal Penutup - Periode {{ $periode }}</h5>
            <div>
                @if($isLocked)
                    <span class="badge bg-danger p-2"><i class="fas fa-lock"></i> Periode Terkunci</span>
                @else
                    <span class="badge bg-success p-2">Periode Terbuka</span>
                    <button type="button" class="btn btn-warning btn-sm ms-2" data-toggle="modal" data-target="#confirmLockModal">
                        <i class="fas fa-lock"></i> Tutup Periode
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('Ledger.jurnal_penutup') }}" method="GET" id="filterForm" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Pilih Periode</label>
                        {{-- Tambahkan onchange="this.form.submit()" di sini --}}
                        <input type="month" name="periode" id="periode" class="form-control" 
                            value="{{ $periode }}" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-3">
                        {{-- Tombol tampilkan sekarang bisa disembunyikan atau dihilangkan jika diinginkan --}}
                        <a href="{{ route('Ledger.jurnal_penutup') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
            
            @if(empty($data))
                <div class="alert alert-warning">Tidak ada data jurnal penutup untuk periode {{ $periode }}.</div>
            @else
                <table class="table table-bordered table-sm">
                    <thead class="table-primary text-center">
                        <tr>
                            <th colspan="2">Tanggal</th>
                            <th>Keterangan</th>
                            <th>Debet</th>
                            <th>Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Baris Modal --}}
                        <tr>
                            <td class="text-center align-middle" rowspan="2">{{ date('Y', strtotime($periode)) }}<br>{{ date('M', strtotime($periode)) }}</td>
                            <td class="text-center align-middle">31</td>
                            <td>{{ $modals < 0 ? 'Modal' : 'Ikhtisar Laba/Rugi' }}</td>
                            <td class="text-end">{{ $modals < 0 ? number_format(abs($modals), 0) : '' }}</td>
                            <td class="text-end">{{ $modals >= 0 ? number_format(abs($modals), 0) : '' }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="padding-left: 30px;">{{ $modals < 0 ? 'Ikhtisar Laba/Rugi' : 'Modal' }}</td>
                            <td class="text-end">{{ $modals >= 0 ? number_format(abs($modals), 0) : '' }}</td>
                            <td class="text-end">{{ $modals < 0 ? number_format(abs($modals), 0) : '' }}</td>
                        </tr>
                        {{-- Baris Pendapatan --}}
                        <tr>
                            <td class="text-center align-middle" rowspan="2"></td>
                            <td class="text-center align-middle">31</td>
                            <td>Pendapatan Jasa</td>
                            <td class="text-end">{{ number_format(abs($pendapatan), 0) }}</td>
                            <td class="text-end"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="padding-left: 30px;">Ikhtisar Laba/Rugi</td>
                            <td></td>
                            <td class="text-end">{{ number_format(abs($pendapatan), 0) }}</td>
                        </tr>
                        {{-- Baris Penutup Laba/Rugi --}}
                        <tr>
                            <td class="text-center align-middle" rowspan="2"></td>
                            <td class="text-center align-middle">31</td>
                            <td>Ikhtisar Laba/Rugi</td>
                            <td class="text-end">{{ number_format(abs($jumlah), 0) }}</td>
                            <td></td>
                        </tr>
                        @foreach ($data as $d)
                        <tr>
                            <td></td>
                            <td style="padding-left: 30px;">{{ $d['keterangan'] }}</td>
                            <td class="text-end">{{ $d['kredit'] > 0 ? number_format(abs($d['kredit']), 0) : '' }}</td>
                            <td class="text-end">{{ $d['debit'] > 0 ? number_format(abs($d['debit']), 0) : '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary font-weight-bold">
                        <tr>
                            <th colspan="3" class="text-center">TOTAL</th>
                            <th class="text-end">{{ number_format($totalDebet, 0) }}</th>
                            <th class="text-end">{{ number_format($totalKredit, 0) }}</th>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="confirmLockModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('Ledger.lock_periode') }}" method="POST">
            @csrf
            <input type="hidden" name="periode" value="{{ $periode }}">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Konfirmasi Tutup Buku</h5></div>
                <div class="modal-body">Apakah Anda yakin ingin menutup periode {{ $periode }}? Tindakan ini permanen.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Kunci Sekarang</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection