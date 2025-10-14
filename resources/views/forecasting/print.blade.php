<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peramalan Curah Hujan</title>
    <style>
        @page { margin: 20mm; }
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            color: #333;
        }
        h1, h2, h3 { margin: 0; padding: 0; }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h2 {
            font-size: 18px;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 13px;
            color: #555;
        }
        .info {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 10px;
            background: #f9f9f9;
        }
        .info p { margin: 3px 0; }
        .summary {
            margin-top: 10px;
            border: 1px solid #666;
            padding: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #666;
            padding: 6px 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .signature {
            margin-top: 40px;
            width: 100%;
        }
        .signature td {
            text-align: center;
            vertical-align: bottom;
            height: 100px;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h2>LAPORAN PERAMALAN CURAH HUJAN</h2>
        <p>Metode Holt–Winters Additive</p>
    </div>

    {{-- Informasi Umum --}}
    <div class="info">
        <p><strong>Tanggal Cetak:</strong> {{ $printed_at }}</p>
        <p><strong>Dicetak oleh:</strong> {{ $user }}</p>

        {{-- Jika memakai filter stasiun --}}
        @if(isset($station) && $station)
            <p><strong>Stasiun:</strong> {{ $station->station_name }}</p>
            <p><strong>Lokasi:</strong>
                {{ $station->village->name ?? '-' }},
                {{ $station->village->district->name ?? '-' }},
                {{ $station->village->district->regency->name ?? '-' }}
            </p>
        @elseif(isset($regency) && $regency)
            <p><strong>Kota / Kabupaten:</strong> {{ $regency->name }}</p>
        @else
            <p><strong>Wilayah:</strong> Semua Kota / Stasiun</p>
        @endif

        <p><strong>Periode Data:</strong>
            {{ \Carbon\Carbon::parse($start_date)->translatedFormat('F Y') }}
            – {{ \Carbon\Carbon::parse($end_date)->translatedFormat('F Y') }}
        </p>
    </div>

    {{-- Ringkasan Evaluasi --}}
    <div class="summary">
        <p><strong>Ringkasan Evaluasi:</strong></p>
        <ul>
            <li>MAE (Mean Absolute Error): <strong>{{ number_format($mae, 2) }}</strong></li>
            <li>RMSE (Root Mean Square Error): <strong>{{ number_format($rmse, 2) }}</strong></li>
        </ul>
    </div>

    {{-- Tabel Hasil Peramalan --}}
    <table>
        <thead>
            <tr>
                <th>Bulan - Tahun</th>
                <th>Aktual</th>
                <th>Level</th>
                <th>Tren</th>
                <th>Musiman</th>
                <th>Peramalan</th>
                <th>Error</th>
                <th>Absolut Error</th>
            </tr>
        </thead>
        <tbody>
            @foreach($labels as $i => $label)
            <tr>
                <td>{{ $label }}</td>
                <td>{{ isset($values[$i]) && $values[$i] !== null ? number_format($values[$i], 2) : '-' }}</td>
                <td>{{ isset($L[$i]) && $L[$i] !== null ? number_format($L[$i], 2) : '-' }}</td>
                <td>{{ isset($T[$i]) && $T[$i] !== null ? number_format($T[$i], 2) : '-' }}</td>
                <td>{{ isset($S[$i]) && $S[$i] !== null ? number_format($S[$i], 2) : '-' }}</td>
                <td>{{ isset($F[$i]) && $F[$i] !== null ? number_format($F[$i], 2) : '-' }}</td>
                <td>{{ isset($errors[$i]) && $errors[$i] !== null ? number_format($errors[$i], 2) : '-' }}</td>
                <td>{{ isset($errors[$i]) && $errors[$i] !== null ? number_format(abs($errors[$i]), 2) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Tanda Tangan --}}
    <table class="signature">
        <tr>
            <td style="width: 70%"></td>
            <td>
                <p>Mengetahui,</p>
                <br><br><br>
                <p><strong>{{ $user }}</strong></p>
                <p><em>(Petugas Pencetak)</em></p>
            </td>
        </tr>
    </table>

</body>
</html>
