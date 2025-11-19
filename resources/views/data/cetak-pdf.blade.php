<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Curah Hujan</title>
    <style>
        @page { margin: 20mm; }
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
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
            font-size: 12px;
            color: #555;
        }
        .info {
            margin-bottom: 15px;
            border: 1px solid #ccc;
            padding: 10px;
            background: #f9f9f9;
        }
        .info p { margin: 3px 0; }
        .summary {
            margin-top: 10px;
            border: 1px solid #666;
            padding: 8px;
            background: #f0f0f0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #666;
            padding: 5px 6px;
            text-align: left;
        }
        th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }
        td.text-right {
            text-align: right;
        }
        th.text-right {
            text-align: right;
        }
        .signature {
            margin-top: 40px;
            width: 100%;
        }
        .signature td {
            text-align: center;
            vertical-align: bottom;
            height: 80px;
            border: none;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h2>LAPORAN DATA CURAH HUJAN</h2>
        <p>Sistem Informasi Curah Hujan</p>
    </div>

    {{-- Informasi Umum --}}
    <div class="info">
        <p><strong>Tanggal Cetak:</strong> {{ $printed_at }}</p>
        <p><strong>Dicetak oleh:</strong> {{ $user }}</p>

        {{-- Informasi Lokasi --}}
        @if($locationInfo['type'] === 'kota')
            <p><strong>Kabupaten/Kota:</strong> {{ $locationInfo['name'] }}</p>
        @elseif($locationInfo['type'] === 'pos')
            <p><strong>Stasiun:</strong> {{ $locationInfo['station']->station_name }}</p>
            <p><strong>Lokasi:</strong></p>
            <ul style="margin: 5px 0; padding-left: 20px;">
                <li><strong>Desa:</strong> {{ $locationInfo['station']->village->name ?? '–' }}</li>
                <li><strong>Kecamatan:</strong> {{ $locationInfo['station']->village->district->name ?? '–' }}</li>
                <li><strong>Kabupaten/Kota:</strong> {{ $locationInfo['station']->village->district->regency->name ?? '–' }}</li>
            </ul>
        @endif

        <p><strong>Periode Data:</strong>
            {{ \Carbon\Carbon::parse($bulanMulai)->translatedFormat('F Y') }}
            s/d {{ \Carbon\Carbon::parse($bulanAkhir)->translatedFormat('F Y') }}
        </p>
    </div>

    {{-- Tabel Data --}}
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Bulan</th>
                @if($locationInfo['type'] === 'kota')
                    <th>Stasiun</th>
                @endif
                <th>Kota</th>
                <th>Lokasi Lengkap</th>
                <th class="text-right">Curah Hujan (mm)</th>
                <th class="text-right">Hari Hujan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rainfallData as $i => $row)
                <tr>
                    <td style="text-align: center;">{{ $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->date)->translatedFormat('F Y') }}</td>
                    @if($locationInfo['type'] === 'kota')
                        <td>{{ $row->station->station_name ?? '-' }}</td>
                    @endif
                    <td>{{ $row->station->village->district->regency->name ?? '-' }}</td>
                    <td>
                        {{ $row->station->village->name ?? '-' }},
                        {{ $row->station->village->district->name ?? '-' }}
                    </td>
                    <td class="text-right">{{ number_format($row->rainfall_amount, 2) }}</td>
                    <td class="text-right">{{ $row->rain_days }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $locationInfo['type'] === 'kota' ? '7' : '6' }}" class="text-center">
                        Tidak ada data untuk periode yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($rainfallData->count() > 0)
        <tfoot>
            <tr style="background-color: #e0e0e0; font-weight: bold;">
                <td colspan="{{ $locationInfo['type'] === 'kota' ? '5' : '4' }}" class="text-right">Total:</td>
                <td class="text-right">{{ number_format($rainfallData->sum('rainfall_amount'), 2) }}</td>
                <td class="text-right">{{ number_format($rainfallData->sum('rain_days'), 0) }}</td>
            </tr>
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="{{ $locationInfo['type'] === 'kota' ? '5' : '4' }}" class="text-right">Rata-rata:</td>
                <td class="text-right">{{ number_format($rainfallData->avg('rainfall_amount'), 2) }}</td>
                <td class="text-right">{{ number_format($rainfallData->avg('rain_days'), 1) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Ringkasan --}}
    @if($rainfallData->count() > 0)
    <div class="summary">
        <p><strong>Ringkasan:</strong></p>
        <ul style="margin: 5px 0; padding-left: 20px;">
            <li>Jumlah Data: <strong>{{ $rainfallData->count() }}</strong> record</li>
            <li>Total Curah Hujan: <strong>{{ number_format($rainfallData->sum('rainfall_amount'), 2) }}</strong> mm</li>
            <li>Rata-rata Curah Hujan: <strong>{{ number_format($rainfallData->avg('rainfall_amount'), 2) }}</strong> mm</li>
            <li>Total Hari Hujan: <strong>{{ number_format($rainfallData->sum('rain_days'), 0) }}</strong> hari</li>
            <li>Rata-rata Hari Hujan: <strong>{{ number_format($rainfallData->avg('rain_days'), 1) }}</strong> hari</li>
        </ul>
    </div>
    @endif

    {{-- Tanda Tangan --}}
    <table class="signature">
        <tr>
            <td style="width: 70%"></td>
            <td>
                <p>Mengetahui,</p>
                <br><br><br>
                <p><strong>{{ $user }}</strong></p>
            </td>
        </tr>
    </table>

</body>
</html>

