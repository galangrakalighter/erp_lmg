@include('partials.header')

<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1" style="font-weight: 600; color: #333;">NERACA LAJUR</h2>
            <p class="text-muted mb-0">Periode: <strong>{{ \Carbon\Carbon::parse($periode)->translatedFormat('F Y') }}</strong></p>
        </div>
        <div>
            <form action="{{ url()->current() }}" method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <input type="month" name="periode" class="form-control" value="{{ $periode }}">
                </div>
                <div class="btn-group" role="group">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    
                    <a href="{{ url('finance/report/worksheet/export-laba-rugi?periode=' . $periode) }}" class="btn btn-success">
                        <i class="fas fa-file-excel mr-1"></i> Export Laba Rugi
                    </a>
                    
                    <a href="{{ url('finance/report/worksheet/export-neraca?periode=' . $periode) }}" class="btn btn-info text-white">
                        <i class="fas fa-file-excel mr-1"></i> Export Neraca
                    </a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .table-lajur { width: 100%; border-collapse: collapse; background: #fff; }
        .table-lajur th { background-color: #5B9BD5; color: white; border: 1px solid #41719C; text-align: center; padding: 8px 4px; font-weight: 600; text-transform: uppercase; font-size: 12px; }
        .table-lajur td { border: 1px solid #BFBFBF; padding: 6px 8px; font-size: 13px; color: #333; }
        .table-lajur .bg-header-sub { background-color: #BDD7EE; color: #333; font-weight: bold; border: 1px solid #9BC2E6; }
        .table-lajur .bg-total { background-color: #D9E1F2; font-weight: bold; }
        .table-lajur .bg-grand-total { background-color: #8EA9DB; font-weight: bold; color: black; border-top: 2px solid #4F81BD; border-bottom: 2px double #4F81BD; }
    </style>

    <div class="table-responsive shadow-sm rounded">
        <table class="table-lajur">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 70px; vertical-align: middle;">Kode Akun</th>
                    <th rowspan="2" style="width: 250px; vertical-align: middle;">Nama Akun</th>
                    <th colspan="2">Neraca Saldo</th>
                    <th colspan="2">Ayat Jurnal Penyesuaian</th>
                    <th colspan="2">Neraca Saldo Disesuaikan</th>
                    <th colspan="2">Laba Rugi</th>
                    <th colspan="2">Neraca</th>
                </tr>
                <tr>
                    <th class="bg-header-sub" style="width: 100px;">DEBET</th>
                    <th class="bg-header-sub" style="width: 100px;">KREDIT</th>
                    <th class="bg-header-sub" style="width: 100px;">DEBET</th>
                    <th class="bg-header-sub" style="width: 100px;">KREDIT</th>
                    <th class="bg-header-sub" style="width: 100px;">DEBET</th>
                    <th class="bg-header-sub" style="width: 100px;">KREDIT</th>
                    <th class="bg-header-sub" style="width: 100px;">DEBET</th>
                    <th class="bg-header-sub" style="width: 100px;">KREDIT</th>
                    <th class="bg-header-sub" style="width: 100px;">DEBET</th>
                    <th class="bg-header-sub" style="width: 100px;">KREDIT</th>
                </tr>
            </thead>
            <tbody>
                @forelse($worksheetData as $row)
                    <tr>
                        <td class="text-center">{{ $row['kode'] }}</td>
                        <td>{{ $row['nama_akun'] }}</td>
                        
                        <td class="text-right">{{ $row['ns_debit'] > 0 ? number_format($row['ns_debit'], 0, ',', '.') : '' }}</td>
                        <td class="text-right">{{ $row['ns_kredit'] > 0 ? number_format($row['ns_kredit'], 0, ',', '.') : '' }}</td>
                        
                        <td class="text-right">{{ $row['aj_debit'] > 0 ? number_format($row['aj_debit'], 0, ',', '.') : '' }}</td>
                        <td class="text-right">{{ $row['aj_kredit'] > 0 ? number_format($row['aj_kredit'], 0, ',', '.') : '' }}</td>
                        
                        <td class="text-right">{{ $row['nsd_debit'] > 0 ? number_format($row['nsd_debit'], 0, ',', '.') : '' }}</td>
                        <td class="text-right">{{ $row['nsd_kredit'] > 0 ? number_format($row['nsd_kredit'], 0, ',', '.') : '' }}</td>
                        
                        <td class="text-right">{{ $row['lr_debit'] > 0 ? number_format($row['lr_debit'], 0, ',', '.') : '' }}</td>
                        <td class="text-right">{{ $row['lr_kredit'] > 0 ? number_format($row['lr_kredit'], 0, ',', '.') : '' }}</td>
                        
                        <td class="text-right">{{ $row['n_debit'] > 0 ? number_format($row['n_debit'], 0, ',', '.') : '' }}</td>
                        <td class="text-right">{{ $row['n_kredit'] > 0 ? number_format($row['n_kredit'], 0, ',', '.') : '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted py-4">Tidak ada data transaksi pada periode ini.</td>
                    </tr>
                @endforelse

                <tr class="bg-total">
                    <td colspan="2" class="text-center">Jumlah / Sub-Total</td>
                    <td class="text-right">{{ number_format($totals['ns_debit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['ns_kredit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['aj_debit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['aj_kredit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['nsd_debit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['nsd_kredit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['lr_debit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['lr_kredit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['n_debit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['n_kredit'], 0, ',', '.') }}</td>
                </tr>

                <tr class="bg-total" style="background-color: #E2EFDA;">
                    <td colspan="2" class="text-center" style="font-style: italic;">
                        {{ $lr_balance >= 0 ? 'Laba Bersih Periode Berjalan' : 'Rugi Bersih Periode Berjalan' }}
                    </td>
                    <td colspan="6" style="background-color: #f9f9f9;"></td> 
                    
                    <td class="text-right">{{ $lr_balance < 0 ? number_format(abs($lr_balance), 0, ',', '.') : '' }}</td>
                    <td class="text-right">{{ $lr_balance >= 0 ? number_format($lr_balance, 0, ',', '.') : '' }}</td>
                    
                    <td class="text-right">{{ $lr_balance >= 0 ? number_format($lr_balance, 0, ',', '.') : '' }}</td>
                    <td class="text-right">{{ $lr_balance < 0 ? number_format(abs($lr_balance), 0, ',', '.') : '' }}</td>
                </tr>

                <tr class="bg-grand-total">
                    <td colspan="2" class="text-center">TOTAL</td>
                    <td class="text-right">{{ number_format($totals['ns_debit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['ns_kredit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['aj_debit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['aj_kredit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['nsd_debit'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['nsd_kredit'], 0, ',', '.') }}</td>
                    
                    <td class="text-right">{{ number_format(max($totals['lr_debit'], $totals['lr_kredit']), 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format(max($totals['lr_debit'], $totals['lr_kredit']), 0, ',', '.') }}</td>
                    
                    <td class="text-right">{{ number_format(max($totals['n_debit'], $totals['n_kredit']), 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format(max($totals['n_debit'], $totals['n_kredit']), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@include('partials.footer')