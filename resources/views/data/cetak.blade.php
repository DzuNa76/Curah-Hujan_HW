@extends('layouts.app')

@section('title', $title ?? 'Cetak Data Curah Hujan')

@section('content')
<div class="container">
    <div class="mb-4">
        <h3 class="mb-2">Data Curah Hujan</h3>
        <div class="mb-2">
            <strong>{{ $subtitle ?? '' }}</strong>
        </div>
        <div>
            <strong>Periode:</strong> 
            {{ \Carbon\Carbon::parse($bulanMulai)->translatedFormat('F Y') }} 
            s/d 
            {{ \Carbon\Carbon::parse($bulanAkhir)->translatedFormat('F Y') }}
        </div>
        <div>
            <strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }}
        </div>
    </div>

    <table class="table table-bordered table-sm">
        <thead class="thead-light">
            <tr>
                <th style="width: 60px;">#</th>
                <th>Bulan</th>
                <th>Stasiun</th>
                <th>Kota</th>
                <th>Lokasi Lengkap</th>
                <th class="text-right">Curah Hujan (mm)</th>
                <th class="text-right">Hari Hujan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rainfallData as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->date)->translatedFormat('F Y') }}</td>
                    <td>{{ $row->station->station_name ?? '-' }}</td>
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
                    <td colspan="7" class="text-center">Tidak ada data untuk periode yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
        @if($rainfallData->count() > 0)
        <tfoot>
            <tr class="font-weight-bold">
                <td colspan="5" class="text-right">Total:</td>
                <td class="text-right">{{ number_format($rainfallData->sum('rainfall_amount'), 2) }}</td>
                <td class="text-right">{{ number_format($rainfallData->sum('rain_days'), 0) }}</td>
            </tr>
            <tr class="font-weight-bold">
                <td colspan="5" class="text-right">Rata-rata:</td>
                <td class="text-right">{{ number_format($rainfallData->avg('rainfall_amount'), 2) }}</td>
                <td class="text-right">{{ number_format($rainfallData->avg('rain_days'), 1) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

<style>
    @media print {
        .container {
            padding: 0;
        }
        .btn, .navbar, .sidebar {
            display: none !important;
        }
        body {
            font-size: 12px;
        }
        table {
            font-size: 11px;
        }
    }
</style>

<script>
    window.addEventListener('load', function() {
        window.print();
    });
    window.addEventListener('afterprint', function() {
        window.close();
    });
</script>
@endsection

