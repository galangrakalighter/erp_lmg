@extends('layout.app')
@section('content')
<div class="p-6 max-w-7xl mx-auto">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-200 pb-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 tracking-tight">{{ $title }}</h1>
            <p class="text-xs text-gray-500 mt-1">
                Periode Laporan: <span class="font-semibold text-gray-700">{{ \Carbon\Carbon::parse($periode.'-01')->isoFormat('MMMM YYYY') }}</span>
            </p>
        </div>
        
        <form action="{{ url()->current() }}" method="GET" class="d-flex flex-wrap align-items-center gap-2 mt-3 mt-sm-0">
            <input type="month" name="periode" value="{{ $periode }}" 
                class="form-control form-control-sm bg-white font-weight-medium text-dark shadow-sm" style="width: auto;"
                onchange="this.form.submit()">

            <a id="financial-pdf-export"
            href="{{ url('finance/report/cash-flow/export/'. $id .'?periode=' . $periode) }}"
            data-arus-kas-url="{{ url('finance/report/cash-flow/export/'. $id .'?periode=' . $periode) }}"
            data-laba-rugi-url="{{ url('finance/report/worksheet/export-laba-rugi/'. $id .'?periode=' . $periode) }}"
            data-neraca-url="{{ url('finance/report/worksheet/export-neraca/'. $id .'?periode=' . $periode) }}"
            class="btn btn-danger btn-sm shadow-sm px-3" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> PDF Arus Kas
            </a>

            {{-- <a href="{{ url('finance/report/cash-flow/export-pdf?periode=' . $periode) }}" target="_blank"
            class="btn btn-danger btn-sm shadow-sm px-3">
                <i class="fas fa-file-pdf mr-1"></i> Export PDF
            </a> --}}
        </form>
    </div>

    <style>
        .financial-tabs { display: flex; gap: .5rem; padding: .35rem; margin-bottom: 1.5rem; background: #f3f4f6; border-radius: .6rem; width: fit-content; }
        .financial-tab { border: 0; background: transparent; color: #6b7280; border-radius: .45rem; padding: .6rem 1rem; font-size: 12px; font-weight: 700; cursor: pointer; transition: all .2s ease; }
        .financial-tab:hover { color: #312e81; background: #fff; }
        .financial-tab.active { color: #fff; background: #312e81; box-shadow: 0 2px 5px rgba(49, 46, 129, .22); }
        .financial-pane { display: none; }
        .financial-pane.active { display: block; }
        .statement-card { overflow: hidden; border: 1px solid #e5e7eb; border-radius: .5rem; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .statement-heading { padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; background: linear-gradient(135deg, #f9fafb, #eef2ff); }
        .statement-heading h2 { margin: 0; color: #1f2937; font-size: 15px; font-weight: 700; }
        .statement-heading p { margin: .25rem 0 0; color: #6b7280; font-size: 11px; }
        .statement-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .statement-table th { padding: .75rem 1rem; color: #4b5563; background: #f9fafb; border-bottom: 2px solid #e5e7eb; text-transform: uppercase; font-size: 10px; letter-spacing: .04em; }
        .statement-table td { padding: .65rem 1rem; color: #374151; border-bottom: 1px solid #f3f4f6; }
        .statement-table tbody tr:hover:not(.statement-total):not(.statement-result) { background: #fafafa; }
        .statement-table .account-code { color: #9ca3af; font-family: monospace; }
        .statement-table .amount { text-align: right; font-family: monospace; font-weight: 500; }
        .statement-total td { background: #f3f4f6; color: #374151; font-weight: 700; border-top: 2px solid #d1d5db; }
        .statement-result td { color: #fff; background: #312e81; font-weight: 700; }
        @media (max-width: 640px) { .financial-tabs { width: 100%; } .financial-tab { flex: 1; padding: .55rem .4rem; } }
    </style>

    <div class="financial-tabs" role="tablist" aria-label="Jenis laporan keuangan">
        <button type="button" class="financial-tab active" data-financial-tab="arus-kas" role="tab" aria-selected="true">Arus Kas</button>
        <button type="button" class="financial-tab" data-financial-tab="laba-rugi" role="tab" aria-selected="false">Laba Rugi</button>
        <button type="button" class="financial-tab" data-financial-tab="neraca" role="tab" aria-selected="false">Neraca</button>
    </div>

    <div id="financial-arus-kas" class="financial-pane active">

    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; width: 100%;">
        <div style="flex: 1; bg-color: #f9fafb; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.375rem; padding: 1rem; height: auto; min-height: unset;">
            <span style="display: block; font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; tracking: 0.05em;">Saldo Awal Kas & Bank</span>
            <span style="display: block; font-size: 1.125rem; font-weight: 700; color: #1f2937; margin-top: 0.25rem;">Rp {{ number_format($totalSaldoAwal, 2, ',', '.') }}</span>
        </div>
        
        <div style="flex: 1; bg-color: #f9fafb; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.375rem; padding: 1rem; height: auto; min-height: unset;">
            <span style="display: block; font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; tracking: 0.05em;">Kenaikan / (Penurunan) Bersih</span>
            <span style="display: block; font-size: 1.125rem; font-weight: 700; color: {{ $kenaikanPenurunanBersih >= 0 ? '#10b981' : '#ef4444' }}; margin-top: 0.25rem;">
                {{ $kenaikanPenurunanBersih < 0 ? '-' : '' }}Rp {{ number_format(abs($kenaikanPenurunanBersih), 2, ',', '.') }}
            </span>
        </div>
        
        <div style="flex: 1; background-color: #eef2ff; border: 1px solid #e0e7ff; border-radius: 0.375rem; padding: 1rem; height: auto; min-height: unset;">
            <span style="display: block; font-size: 10px; font-weight: 700; color: #6366f1; text-transform: uppercase; tracking: 0.05em;">Saldo Akhir Kas & Bank</span>
            <span style="display: block; font-size: 1.125rem; font-weight: 700; color: #4338ca; margin-top: 0.25rem;">Rp {{ number_format($totalSaldoAkhir, 2, ',', '.') }}</span>
        </div>
    </div>

    <div style="border: 1px solid #e5e7eb; border-radius: 0.375rem; background-color: #ffffff; overflow: hidden; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); width: 100%; margin-top: 1.5rem;">
        <table style="width: 100%; text-align: left; border-collapse: collapse; font-size: 12px; margin: 0; padding: 0;">
            <thead>
                <tr style="background-color: #f3f4f6; border-bottom: 2px solid #e5e7eb; color: #4b5563; font-weight: 700; text-transform: uppercase;">
                    <th style="padding: 0.75rem 1rem; width: 15%; font-family: monospace;">Kode Akun</th>
                    <th style="padding: 0.75rem 1rem; width: 55%;">Deskripsi Aliran Arus Kas</th>
                    <th style="padding: 0.75rem 1rem; width: 15%; text-align: right;">Kas Masuk (Dr)</th>
                    <th style="padding: 0.75rem 1rem; width: 15%; text-align: right;">Kas Keluar (Cr)</th>
                </tr>
            </thead>
            <tbody style="color: #374151;">
                
                {{-- ================= AKTIVITAS OPERASIONAL ================= --}}
                <tr style="background-color: #f9fafb; font-weight: 700; color: #1e1b4b; border-bottom: 1px solid #e5e7eb;">
                    <td colspan="4" style="padding: 0.5rem 1rem; font-size: 13px;">1. Arus Kas dari Aktivitas Operasional</td>
                </tr>
                @forelse($operasional as $item)
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 0.5rem 1rem; color: #9ca3af; font-family: monospace; font-size: 11px;">{{ $item['kode'] }}</td>
                        <td style="padding: 0.5rem 1rem; color: #4b5563;">{{ $item['nama_akun'] }}</td>
                        <td style="padding: 0.5rem 1rem; text-align: right; font-weight: 500; color: #059669; font-family: monospace;">
                            {{ $item['jumlah'] >= 0 ? number_format($item['jumlah'], 2, ',', '.') : '-' }}
                        </td>
                        <td style="padding: 0.5rem 1rem; text-align: right; font-weight: 500; color: #dc2626; font-family: monospace;">
                            {{ $item['jumlah'] < 0 ? number_format(abs($item['jumlah']), 2, ',', '.') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td colspan="4" style="padding: 1rem; text-align: center; color: #9ca3af; font-style: italic; background-color: #ffffff;">Tidak ada transaksi kas operasional</td>
                    </tr>
                @endforelse
                <tr style="font-weight: 600; background-color: rgba(249, 250, 251, 0.5); border-bottom: 1px solid #d1d5db;">
                    <td colspan="2" style="padding: 0.5rem 1rem; text-align: right; color: #6b7280; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Total Kas Netto Operasional</td>
                    <td colspan="2" style="padding: 0.5rem 1rem; text-align: right; font-weight: 700; font-family: monospace; color: #111827;">
                        {{ $totalOperasional < 0 ? '(' . number_format(abs($totalOperasional), 2, ',', '.') . ')' : number_format($totalOperasional, 2, ',', '.') }}
                    </td>
                </tr>

                {{-- ================= AKTIVITAS INVESTASI ================= --}}
                <tr style="background-color: #f9fafb; font-weight: 700; color: #1e1b4b; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #d1d5db;">
                    <td colspan="4" style="padding: 0.5rem 1rem; font-size: 13px;">2. Arus Kas dari Aktivitas Investasi</td>
                </tr>
                @forelse($investasi as $item)
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 0.5rem 1rem; color: #9ca3af; font-family: monospace; font-size: 11px;">{{ $item['kode'] }}</td>
                        <td style="padding: 0.5rem 1rem; color: #4b5563;">{{ $item['nama_akun'] }}</td>
                        <td style="padding: 0.5rem 1rem; text-align: right; font-weight: 500; color: #059669; font-family: monospace;">
                            {{ $item['jumlah'] >= 0 ? number_format($item['jumlah'], 2, ',', '.') : '-' }}
                        </td>
                        <td style="padding: 0.5rem 1rem; text-align: right; font-weight: 500; color: #dc2626; font-family: monospace;">
                            {{ $item['jumlah'] < 0 ? number_format(abs($item['jumlah']), 2, ',', '.') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td colspan="4" style="padding: 1rem; text-align: center; color: #9ca3af; font-style: italic; background-color: #ffffff;">Tidak ada transaksi kas investasi</td>
                    </tr>
                @endforelse
                <tr style="font-weight: 600; background-color: rgba(249, 250, 251, 0.5); border-bottom: 1px solid #d1d5db;">
                    <td colspan="2" style="padding: 0.5rem 1rem; text-align: right; color: #6b7280; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Total Kas Netto Investasi</td>
                    <td colspan="2" style="padding: 0.5rem 1rem; text-align: right; font-weight: 700; font-family: monospace; color: #111827;">
                        {{ $totalInvestasi < 0 ? '(' . number_format(abs($totalInvestasi), 2, ',', '.') . ')' : number_format($totalInvestasi, 2, ',', '.') }}
                    </td>
                </tr>

                {{-- ================= AKTIVITAS PENDANAAN ================= --}}
                <tr style="background-color: #f9fafb; font-weight: 700; color: #1e1b4b; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #d1d5db;">
                    <td colspan="4" style="padding: 0.5rem 1rem; font-size: 13px;">3. Arus Kas dari Aktivitas Pendanaan</td>
                </tr>
                @forelse($pendanaan as $item)
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 0.5rem 1rem; color: #9ca3af; font-family: monospace; font-size: 11px;">{{ $item['kode'] }}</td>
                        <td style="padding: 0.5rem 1rem; color: #4b5563;">{{ $item['nama_akun'] }}</td>
                        <td style="padding: 0.5rem 1rem; text-align: right; font-weight: 500; color: #059669; font-family: monospace;">
                            {{ $item['jumlah'] >= 0 ? number_format($item['jumlah'], 2, ',', '.') : '-' }}
                        </td>
                        <td style="padding: 0.5rem 1rem; text-align: right; font-weight: 500; color: #dc2626; font-family: monospace;">
                            {{ $item['jumlah'] < 0 ? number_format(abs($item['jumlah']), 2, ',', '.') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td colspan="4" style="padding: 1rem; text-align: center; color: #9ca3af; font-style: italic; background-color: #ffffff;">Tidak ada transaksi kas pendanaan</td>
                    </tr>
                @endforelse
                <tr style="font-weight: 600; background-color: rgba(249, 250, 251, 0.5); border-bottom: 1px solid #d1d5db;">
                    <td colspan="2" style="padding: 0.5rem 1rem; text-align: right; color: #6b7280; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Total Kas Netto Pendanaan</td>
                    <td colspan="2" style="padding: 0.5rem 1rem; text-align: right; font-weight: 700; font-family: monospace; color: #111827;">
                        {{ $totalPendanaan < 0 ? '(' . number_format(abs($totalPendanaan), 2, ',', '.') . ')' : number_format($totalPendanaan, 2, ',', '.') }}
                    </td>
                </tr>

                {{-- ================= REKONSILIASI KELOMPOK AKHIR ================= --}}
                <tr style="background-color: #312e81; color: #ffffff; font-weight: 500;">
                    <td colspan="2" style="padding: 0.625rem 1rem; text-align: right; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Kenaikan / (Penurunan) Bersih Kas Periode Ini</td>
                    <td colspan="2" style="padding: 0.625rem 1rem; text-align: right; font-weight: 700; font-family: monospace; font-size: 13px;">
                        {{ $kenaikanPenurunanBersih < 0 ? '(' . number_format(abs($kenaikanPenurunanBersih), 2, ',', '.') . ')' : number_format($kenaikanPenurunanBersih, 2, ',', '.') }}
                    </td>
                </tr>
                <tr style="background-color: #1f2937; color: #d1d5db; font-weight: 500;">
                    <td colspan="2" style="padding: 0.5rem 1rem; text-align: right; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Saldo Awal Kas & Bank Bersih</td>
                    <td colspan="2" style="padding: 0.5rem 1rem; text-align: right; font-weight: 600; font-family: monospace; font-size: 12px;">
                        {{ number_format($totalSaldoAwal, 2, ',', '.') }}
                    </td>
                </tr>
                <tr style="background-color: #111827; color: #34d399; font-weight: 700; border-top: 1px solid #374151;">
                    <td colspan="2" style="padding: 0.75rem 1rem; text-align: right; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Saldo Akhir Kas & Bank Riil</td>
                    <td colspan="2" style="padding: 0.75rem 1rem; text-align: right; font-size: 14px; font-family: monospace; letter-spacing: 0.025em;">
                        {{ number_format($kenaikanPenurunanBersih + $totalSaldoAwal, 2, ',', '.') }}
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
    </div>

    <div id="financial-laba-rugi" class="financial-pane">
        <div class="statement-card">
            <div class="statement-heading">
                <h2>Laporan Laba Rugi</h2>
                <p>Pendapatan dan beban berdasarkan saldo disesuaikan pada neraca lajur.</p>
            </div>
            <div class="table-responsive">
                <table class="statement-table">
                    <thead><tr><th style="width:18%">Kode Akun</th><th>Nama Akun</th><th style="width:18%;text-align:right">Debit</th><th style="width:18%;text-align:right">Kredit</th></tr></thead>
                    <tbody>
                        @forelse($labaRugi as $row)
                            <tr><td class="account-code">{{ $row['kode'] }}</td><td>{{ $row['nama_akun'] }}</td><td class="amount">{{ $row['lr_debit'] != 0 ? number_format($row['lr_debit'], 2, ',', '.') : '-' }}</td><td class="amount">{{ $row['lr_kredit'] != 0 ? number_format($row['lr_kredit'], 2, ',', '.') : '-' }}</td></tr>
                        @empty
                            <tr><td colspan="4" style="padding:2rem;text-align:center;color:#9ca3af;font-style:italic">Tidak ada data laba rugi pada periode ini.</td></tr>
                        @endforelse
                        <tr class="statement-total"><td colspan="2" style="text-align:right">Jumlah</td><td class="amount">{{ number_format($worksheetTotals['lr_debit'], 2, ',', '.') }}</td><td class="amount">{{ number_format($worksheetTotals['lr_kredit'], 2, ',', '.') }}</td></tr>
                        <tr class="statement-result"><td colspan="3">{{ $labaRugiBersih >= 0 ? 'Laba Bersih Periode Berjalan' : 'Rugi Bersih Periode Berjalan' }}</td><td class="amount">{{ number_format(abs($labaRugiBersih), 2, ',', '.') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="financial-neraca" class="financial-pane">
        <div class="statement-card">
            <div class="statement-heading">
                <h2>Laporan Neraca</h2>
                <p>Aset, liabilitas, dan ekuitas berdasarkan saldo disesuaikan pada neraca lajur.</p>
            </div>
            <div class="table-responsive">
                <table class="statement-table">
                    <thead><tr><th style="width:18%">Kode Akun</th><th>Nama Akun</th><th style="width:18%;text-align:right">Debit</th><th style="width:18%;text-align:right">Kredit</th></tr></thead>
                    <tbody>
                        @forelse($neraca as $row)
                            <tr><td class="account-code">{{ $row['kode'] }}</td><td>{{ $row['nama_akun'] }}</td><td class="amount">{{ $row['n_debit'] != 0 ? number_format($row['n_debit'], 2, ',', '.') : '-' }}</td><td class="amount">{{ $row['n_kredit'] != 0 ? number_format($row['n_kredit'], 2, ',', '.') : '-' }}</td></tr>
                        @empty
                            <tr><td colspan="4" style="padding:2rem;text-align:center;color:#9ca3af;font-style:italic">Tidak ada data neraca pada periode ini.</td></tr>
                        @endforelse
                        <tr class="statement-result"><td colspan="2">{{ $labaRugiBersih >= 0 ? 'Laba Bersih Periode Berjalan' : 'Rugi Bersih Periode Berjalan' }}</td><td class="amount">{{ number_format(abs($labaRugiBersih), 2, ',', '.') }}</td><td class="amount">-</td></tr>
                        <tr class="statement-total"><td colspan="2" style="text-align:right">Total Neraca</td><td class="amount">{{ number_format($worksheetTotals['n_debit'] + abs($labaRugiBersih), 2, ',', '.') }}</td><td class="amount">{{ number_format($worksheetTotals['n_kredit'], 2, ',', '.') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('[data-financial-tab]');
        const exportButton = document.getElementById('financial-pdf-export');
        const exportLabels = {
            'arus-kas': 'PDF Arus Kas',
            'laba-rugi': 'PDF Laba Rugi',
            'neraca': 'PDF Neraca'
        };
        const exportUrlKeys = {
            'arus-kas': 'arusKasUrl',
            'laba-rugi': 'labaRugiUrl',
            'neraca': 'neracaUrl'
        };
        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                buttons.forEach(function (item) {
                    item.classList.remove('active');
                    item.setAttribute('aria-selected', 'false');
                });
                document.querySelectorAll('.financial-pane').forEach(function (pane) { pane.classList.remove('active'); });
                button.classList.add('active');
                button.setAttribute('aria-selected', 'true');
                document.getElementById('financial-' + button.dataset.financialTab).classList.add('active');
                exportButton.href = exportButton.dataset[exportUrlKeys[button.dataset.financialTab]];
                exportButton.innerHTML = '<i class="fas fa-file-pdf mr-1"></i> ' + exportLabels[button.dataset.financialTab];
            });
        });
    });
</script>

@endsection
