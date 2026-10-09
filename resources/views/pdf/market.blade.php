<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Analisis Market</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #333;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        /* Layout Kolom untuk Halaman 1 */
        .row { width: 100%; clear: both; }
        .col-6 { width: 100%; float: none; }
        .spacer { display: none; }
        .clearfix::after { content: ""; clear: both; display: table; }

        .section-title {
            background: #4e73df;
            color: white;
            padding: 5px 10px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
            text-transform: uppercase;
            page-break-after: avoid;
        }
        
        table {
            width: 100%; 
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed; 
        }
        
        th, td {
            border: 1px solid #ccc;
            padding: 4px;
            vertical-align: top;
            word-wrap: break-word;
        }
        
        thead th {
            background-color: #f8f9fc;
            color: #333;
            text-align: center;
            font-size: 7pt;
        }

        .bg-dark-header { background-color: #333 !important; color: white !important; }
        .bg-info-header { background-color: #36b9cc !important; color: white !important; }
        .text-center { text-align: center; }

        /* Force Page Break */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Market Research</h2>
        <p>Client: {{ $produk->nama }} · Platform: {{ $platform ?: 'Semua Platform' }}</p>
    </div>

    <div class="row clearfix">
        <div class="col-6">
            <div class="section-title text-center">4P Analyze</div>
            <table>
                <thead>
                    <tr>
                        <th class="bg-dark-header">Nama Analisis</th>
                        <th class="bg-dark-header">Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['data'][0] as $analize_4p)
                    <tr>
                        <td>{{ $analize_4p->nama_analisis }}</td>
                        <td>{{ $analize_4p->type }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="spacer"></div>

        <div class="col-6">
            <div class="section-title text-center">SWOT Analyze</div>
            <table>
                <thead>
                    <tr>
                        <th class="bg-dark-header">S</th>
                        <th class="bg-dark-header">W</th>
                        <th class="bg-dark-header">O</th>
                        <th class="bg-dark-header">T</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['data'][1] as $analize_swot)
                    <tr>
                        <td>{{ $analize_swot->strenght }}</td>
                        <td>{{ $analize_swot->weakness }}</td>
                        <td>{{ $analize_swot->oportunity }}</td>
                        <td>{{ $analize_swot->threat }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="page-break"></div>

    <div class="section-title text-center">Segmentasi Market</div>
    <table>
        <thead>
            <tr>
                <th colspan="2" class="bg-info-header">Demografi</th>
                <th colspan="2" class="bg-info-header">Geografi</th>
                <th colspan="3" class="bg-info-header">Psikografi</th>
                <th colspan="3" class="bg-info-header">Perilaku</th>
            </tr>
            <tr>
                <th>Usia</th><th>Gender</th>
                <th>Negara</th><th>Wilayah</th>
                <th>Gaya Hidup</th><th>Sosial</th><th>Minat</th>
                <th>Konten</th><th>Loyalitas</th><th>Manfaat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['data'][3] as $segmentasi)
            <tr>
                <td>{{ $segmentasi->usia }}</td>
                <td>{{ $segmentasi->gender }}</td>
                <td>{{ $segmentasi->negara }}</td>
                <td>{{ $segmentasi->wilayah }}</td>
                <td>{{ $segmentasi->gaya_hidup }}</td>
                <td>{{ $segmentasi->status_sosial }}</td>
                <td>{{ $segmentasi->minat }}</td>
                <td>{{ $segmentasi->penggunaan }}</td>
                <td>{{ $segmentasi->loyalitas }}</td>
                <td>{{ $segmentasi->manfaat }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title text-center">Target Audience</div>
    <table>
        <thead>
            <tr>
                <th rowspan="2" class="bg-dark-header" style="width: 80px;">Indikator</th>
                <th colspan="5" class="bg-info-header">Profile</th>
                <th colspan="4" class="bg-info-header">Behavior & Interest</th>
            </tr>
            <tr>
                <th>Usia</th><th>Gender</th><th>Negara</th><th>Wilayah</th><th>Profile</th>
                <th>Gaya Hidup</th><th>Sosial</th><th>Produk</th><th>Manfaat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['data'][4] as $target)
            <tr>
                <td>{{ $target->indikator }}</td>
                <td>{{ $target->usia }}</td>
                <td>{{ $target->gender }}</td>
                <td>{{ $target->negara }}</td>
                <td>{{ $target->wilayah }}</td>
                <td>{{ $target->pekerjaan }}</td>
                <td>{{ $target->gaya_hidup }}</td>
                <td>{{ $target->status_sosial }}</td>
                <td>{{ $target->penggunaan }}</td>
                <td>{{ $target->manfaat }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title text-center">Positioning</div>
    <table>
        <thead>
            <tr>
                <th class="bg-info-header" style="width: 150px;">Indikator</th>
                <th class="bg-info-header">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['data'][2] as $pos)
            <tr>
                <td>{{ $pos->indikator }}</td>
                <td>{{ $pos->deskripsi }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
