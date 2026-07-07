@include('partials.header')

<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('finance_ledger.list_account', ['periode' => $periode]) }}" class="btn btn-sm btn-light border shadow-sm mb-3">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Pilih Akun
        </a>
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <h1 class="h4 text-gray-800 font-weight-bold mb-1">
                    [{{ $ledgerData['account']->kode }}] - {{ $ledgerData['account']->nama_akun }}
                </h1>
                <p class="text-muted small mb-0">
                    Tipe Akun: <span class="badge badge-secondary text-uppercase">{{ $ledgerData['account']->type === 'D' ? 'Debit' : 'Kredit' }}</span> | 
                    Kategori: <span class="text-capitalize">{{ $ledgerData['account']->category }}</span>
                </p>
            </div>
            
            <div class="mt-3 mt-md-0">
                <form action="{{ url()->current() }}" method="GET" class="form-inline bg-white p-2 rounded shadow-sm border">
                    <label for="periode" class="mr-2 small font-weight-bold text-muted">PERIODE LAPORAN:</label>
                    <input type="month" name="periode" id="periode" class="form-control form-control-sm border-0 bg-light font-weight-bold text-dark" 
                           value="{{ $periode }}" onchange="this.form.submit()">
                </form>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="bg-gray-100 text-gray-700 text-xs font-weight-bold text-uppercase border-bottom">
                        <tr>
                            <th class="px-4 py-3" style="width: 12%;">Tanggal</th>
                            <th class="py-3" style="width: 15%;">No. Jurnal</th>
                            <th class="py-3">Keterangan Mutasi</th>
                            <th class="text-right py-3" style="width: 15%;">Debit</th>
                            <th class="text-right py-3" style="width: 15%;">Kredit</th>
                            <th class="text-right px-4 py-3" style="width: 18%;">Saldo Akhir Berjalan</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @php
                            $totalDebit = 0;
                            $totalKredit = 0;
                        @endphp

                        <tr class="table-info font-weight-bold text-dark">
                            <td class="px-4 py-3 text-center" colspan="3">
                                <i class="fas fa-door-open mr-1 text-info"></i> SALDO AWAL PEMBUKA PERIODE IMIGRASI
                            </td>
                            <td class="text-right py-3"></td>
                            <td class="text-right py-3"></td>
                            <td class="text-right px-4 py-3 font-mono">
                                Rp. {{ number_format($ledgerData['opening_balance'], 0, ',', '.') }}
                            </td>
                        </tr>

                        @forelse($ledgerData['mutasi'] as $row)
                        @php
                            $totalDebit += $row['debit'];
                            $totalKredit += $row['kredit'];
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-muted">
                                {{ date('d/m/Y', strtotime($row['tanggal'])) }}
                            </td>
                            <td class="py-3">
                                <span class="badge badge-light border text-gray-800 px-2 py-1 font-mono">
                                    {{ $row['nomor_jurnal'] }}
                                </span>
                            </td>
                            <td class="py-3 text-gray-700">
                                {{ $row['keterangan'] }}
                            </td>
                            <td class="text-right py-3 text-success font-mono">
                                {{ $row['debit'] > 0 ? 'Rp. ' . number_format($row['debit'], 0, ',', '.') : '' }}
                            </td>
                            <td class="text-right py-3 text-danger font-mono">
                                {{ $row['kredit'] > 0 ? 'Rp. ' . number_format($row['kredit'], 0, ',', '.') : '' }}
                            </td>
                            <td class="text-right px-4 py-3 font-weight-bold text-gray-900 font-mono">
                                Rp. {{ number_format($row['saldo_berjalan'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-receipt d-block fa-2x mb-2 opacity-3"></i>
                                Tidak ada mutasi transaksi jurnal umum untuk akun ini pada periode terpilih.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    
                    @if(count($ledgerData['mutasi']) > 0)
                    <tfoot class="bg-light font-weight-bold border-top text-dark">
                        <tr style="border-bottom: 2px solid #e3e6f0;">
                            <td colspan="3" class="px-4 py-3 text-right text-uppercase text-xs tracking-wider text-muted">
                                Total Pergerakan Mutasi Bulan Ini:
                            </td>
                            <td class="text-right py-3 text-success font-mono">
                                Rp. {{ number_format($totalDebit, 0, ',', '.') }}
                            </td>
                            <td class="text-right py-3 text-danger font-mono">
                                Rp. {{ number_format($totalKredit, 0, ',', '.') }}
                            </td>
                            <td class="text-right px-4 py-3 text-muted small font-italic"></td>
                        </tr>
                        
                        <tr class="table-warning">
                            <td colspan="5" class="px-4 py-3 text-right text-uppercase text-xs tracking-wider font-weight-bold">
                                Saldo Akhir Buku Besar Penutupan:
                            </td>
                            <td class="text-right px-4 py-3 font-mono text-primary text-base font-weight-bold">
                                Rp. {{ number_format($ledgerData['final_balance'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@include('partials.footer')