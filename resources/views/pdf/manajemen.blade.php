<!DOCTYPE html>
<html>
<head>
    <title>Export Data PDF</title>
    <style>
        /* Mengatur orientasi kertas ke Landscape */
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: sans-serif;
            font-size: 9pt; /* Ukuran font diperkecil agar kolom muat */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Memaksa kolom mengikuti lebar yang ditentukan */
        }
        th, td {
            border: 1px solid #333;
            padding: 4px;
            text-align: center;
            word-wrap: break-word; /* Memotong kata jika terlalu panjang */
        }
        thead {
            background-color: #f2f2f2;
        }
        .judul-dokumen {
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        /* Mengatur lebar kolom spesifik jika diperlukan */
        .col-no { width: 30px; }
        .col-tanggal { width: 70px; }
        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>
<body>

    <div class="judul-dokumen">
        <h2>Content Plan</h2>
    </div>

    <table>
        <thead class="text-nowrap">
            <tr>
                <th>Judul</th>
                <th>Inspirasi</th>
                <th>Type</th>
                <th>Strategi</th>
                <th class="nowrap">Funneling</th>
                <th>Pilar</th>
                <th>Hook</th>
                <th>Body</th>
                <th>CTA</th>
                <th>Durasi</th>
                @if($kategory->id == 2)
                    <th>Warna</th>
                @endif
                <th>Komposisi</th>
                <th>Note</th>
                <th>Tanggal Posting</th>
                <th>Tanggal Deadline</th>
                <th>Link</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                <tr>
                    <th>{{ $item->judul }}</th>
                    <th><a href="{{ $item->inspirasi }}" target="_blank">{{ $item->inspirasi }}</a></th>
                    <th>{{ $item->type }}</th>
                    <th>{{ $item->strategy }}</th>
                    <td>{{ $item->funneling }}</td>
                    <th>{{ $item->pilar }}</th>
                    <th>{{ $item->hooks }}</th>
                    <th>{{ $item->nama_body }}</th>
                    <th>{{ $item->nama_cta }}</th>
                    <th>{{ $item->durasi }}</th>
                    @if($kategory->id == 2)
                    <th>{{ $item->background }}</th>
                    @endif
                    <th>{{ $item->komposisi }}</th>
                    <th>{{ $item->note }}</th>
                    <th>{{ $item->tanggal_posting }}</th>
                    <th>{{ $item->tanggal_deadline }}</th>
                    <th><a href="{{ $item->link }}" target="_blank">{{ $item->link }}</a></th>
                    <th>{{ $item->nama_status }}</th>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>