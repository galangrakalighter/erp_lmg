@extends('layout.app')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
                <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-book-reader mr-2"></i>Jurnal Tutup Akun</h5>
                @if(!$isPeriodLocked)
                    <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#modalTutupAkun" {{ $accounts->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-plus mr-1"></i>Tutup Satu Akun
                    </button>
                @else
                    <span class="badge badge-danger px-3 py-2"><i class="fas fa-lock mr-1"></i>Periode Dikunci</span>
                @endif
            </div>

            <div class="card-body bg-light border-bottom py-3">
                <form action="{{ route('Ledger.jurnal_penutup', $id) }}" method="GET" class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-dark">Periode Bulan:</label>
                        <input type="month" name="periode" class="form-control form-control-sm border-primary" value="{{ $periode }}" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-7 mt-2 mt-md-0 small text-muted">
                        Setiap akun dapat ditutup, diubah keterangannya, atau dibuka kembali secara terpisah.
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover m-0 text-dark">
                        <thead class="bg-dark text-white small font-weight-bold text-center">
                            <tr>
                                <th style="width:13%">Tanggal / No. Jurnal</th>
                                <th style="width:10%">Kode Akun</th>
                                <th style="width:18%">Nama Akun COA</th>
                                <th>Keterangan</th>
                                <th style="width:12%">Debit (Rp)</th>
                                <th style="width:12%">Kredit (Rp)</th>
                                <th style="width:9%">Status</th>
                                <th style="width:9%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($closings as $closing)
                                <tr>
                                    <td class="text-center align-middle" rowspan="2">
                                        <strong>{{ \Carbon\Carbon::parse($closing->closing_date)->format('d/m/Y') }}</strong><br>
                                        <span class="badge badge-light border mt-1">JT-{{ str_replace('-', '', $closing->periode) }}-{{ str_pad($closing->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="text-center align-middle font-weight-bold">{{ $closing->kode }}</td>
                                    <td class="align-middle font-weight-bold">{{ $closing->nama_akun }}</td>
                                    <td class="align-middle text-muted">{{ $closing->keterangan }}</td>
                                    <td class="text-right align-middle text-success font-weight-bold">{{ $closing->debit > 0 ? number_format($closing->debit, 2, ',', '.') : '-' }}</td>
                                    <td class="text-right align-middle text-danger font-weight-bold">{{ $closing->kredit > 0 ? number_format($closing->kredit, 2, ',', '.') : '-' }}</td>
                                    <td class="text-center align-middle" rowspan="2"><span class="badge badge-success">Ditutup</span></td>
                                    <td class="text-center align-middle" rowspan="2">
                                        @if(!$isPeriodLocked)
                                            <button type="button" class="btn btn-info btn-sm mb-1" data-toggle="modal" data-target="#modalEdit{{ $closing->id }}" title="Ubah keterangan"><i class="fas fa-edit"></i></button>
                                            <form action="{{ route('Ledger.delete_account_closing', [$id, $closing->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Buka kembali akun {{ addslashes($closing->nama_akun) }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm mb-1" title="Buka kembali"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="bg-light">
                                    <td class="text-center font-weight-bold">{{ $closing->equity_kode }}</td>
                                    <td class="font-weight-bold">{{ $closing->equity_nama }}</td>
                                    <td class="text-muted">Lawan jurnal penutupan {{ $closing->nama_akun }}</td>
                                    <td class="text-right text-success font-weight-bold">{{ $closing->kredit > 0 ? number_format($closing->kredit, 2, ',', '.') : '-' }}</td>
                                    <td class="text-right text-danger font-weight-bold">{{ $closing->debit > 0 ? number_format($closing->debit, 2, ',', '.') : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-inbox d-block fa-2x mb-2"></i>Belum ada akun yang ditutup pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTutupAkun" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <form action="{{ route('Ledger.close_account', $id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title font-weight-bold"><i class="fas fa-lock mr-2"></i>Tutup Satu Akun</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Periode yang Akan Ditutup</label>
                        <input type="month" name="periode" class="form-control" value="{{ $periode }}" required>
                        <small class="form-text text-muted">Bisa memilih periode sebelum atau sesudah bulan yang sedang ditampilkan.</small>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Akun Pendapatan / Beban</label>
                        <select name="chart_of_account_id" class="form-control" required>
                            <option value="">-- Pilih akun yang akan ditutup --</option>
                            @foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->kode }} - {{ $account->nama_akun }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0"><label class="small font-weight-bold">Keterangan</label><input type="text" name="keterangan" class="form-control" placeholder="Opsional, dibuat otomatis jika kosong"></div>
                    <div class="alert alert-warning small mt-3 mb-0">Setelah ditutup, akun tidak dapat digunakan pada transaksi di periode yang dipilih sampai penutupannya dibatalkan.</div>
                </div>
                <div class="modal-footer bg-light"><button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check mr-1"></i>Tutup Akun</button></div>
            </div>
        </form>
    </div>
</div>

@foreach($closings as $closing)
<div class="modal fade" id="modalEdit{{ $closing->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('Ledger.update_account_closing', [$id, $closing->id]) }}" method="POST">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-info text-white"><h5 class="modal-title">Ubah Keterangan Jurnal</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body"><label class="small font-weight-bold">{{ $closing->kode }} - {{ $closing->nama_akun }}</label><input type="text" name="keterangan" class="form-control" value="{{ $closing->keterangan }}" required maxlength="255"></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-info btn-sm">Simpan</button></div>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection
