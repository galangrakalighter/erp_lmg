<!DOCTYPE html>
<html>
<head>
    <title>Branding Strategy Report</title>
    <style>
        @page { size: A4 landscape; margin: 1cm; }
        body { font-family: sans-serif; font-size: 9pt; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #4e73df; padding-bottom: 10px; margin-bottom: 20px; }
        .section-title { background: #4e73df; color: white; padding: 6px; font-weight: bold; margin-top: 15px; margin-bottom: 10px; text-transform: uppercase; page-break-after: avoid; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; word-wrap: break-word; }
        th { background-color: #f8f9fc; color: #333; font-size: 8pt; }
        .bg-info { background-color: #36b9cc !important; color: white !important; }
        .text-center { text-align: center; }
        .img-container { text-align: center; border: 1px solid #ddd; padding: 10px; background: #f9f9f9; }
        .img-container img { max-width: 100%; height: auto; max-height: 400px; }
        .page-break { page-break-after: always; }
        .row { width: 100%; clear: both; }
        .col-6 { width: 48%; float: left; margin-right: 2%; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Strategi Branding</h2>
        <p>Client: {{ $produk->nama }} · Platform: {{ $platform ?: 'Semua Platform' }}</p>
    </div>

    <div class="section-title text-center">Brand Identify</div>
    <div class="img-container">
        @if($data['data'][7] && $data['data'][7]->identify && is_file(public_path('storage/' . $data['data'][7]->identify)))
            <img src="{{ public_path('storage/' . $data['data'][7]->identify) }}">
        @else
            <p style="color: #999; padding: 50px 0;">Gambar Brand Identify Tidak Tersedia</p>
        @endif
    </div>

    <div class="section-title text-center">Brand Image</div>
    <table>
        <thead>
            <tr>
                <th>Brand Image Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['data'][0] as $item)
            <tr>
                <td>{{ $item->nama_image }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title text-center">Moodboard</div>
    <div class="img-container">
        @if($data['data'][8] && $data['data'][8]->moodboard && is_file(public_path('storage/' . $data['data'][8]->moodboard)))
            <img src="{{ public_path('storage/' . $data['data'][8]->moodboard) }}">
        @else
            <p style="color: #999; padding: 100px 0;">Gambar Moodboard Tidak Tersedia</p>
        @endif
    </div>

    <div class="row">
        <div class="col-6">
            <div class="section-title text-center">Tagline</div>
            <table>
                <thead>
                    <tr>
                        <th>Tagline / Hashtag</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['data'][2] as $item)
                    <tr>
                        <td><strong>{{ $item->nama_tagline }}</strong><br><small>{{ $item->nama_hashtagline }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-6">
            <div class="section-title text-center">Gaya Komunikasi</div>
            <table>
                <thead>
                    <tr>
                        <th>Bicara / Bahasa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['data'][3] as $item)
                    <tr>
                        <td>{{ $item->gaya_bicara }} / {{ $item->gaya_bahasa }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row" style="clear: both; padding-top: 10px;">
        <div class="col-6">
            <div class="section-title text-center">Komposisi</div>
            <table>
                <thead>
                    <tr>
                        <th>Tipe</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['data'][4] as $item)
                    <tr>
                        <td>{{ $item->type_komposisi }}</td>
                        <td>{{ $item->komposisi }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-6">
            <div class="section-title text-center">Audio Branding</div>
            <table>
                <thead>
                    <tr>
                        <th>Nama Audio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['data'][5] as $item)
                    <tr>
                        <td>{{ $item->nama_audio }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row" style="clear: both; padding-top: 10px;">
        <div class="col-6">
            <div class="section-title text-center">Brand Value</div>
            <table>
                <thead>
                    <tr>
                        <th>Value Name</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['data'][1] as $item)
                    <tr>
                        <td>{{ $item->nama_value }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-6">
            <div class="section-title text-center">Alat Branding</div>
            <table>
                <thead>
                    <tr>
                        <th>Nama Alat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['data'][6] as $item)
                    <tr>
                        <td>{{ $item->nama_alat }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
