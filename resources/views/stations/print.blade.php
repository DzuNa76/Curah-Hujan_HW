@extends('layouts.app')

@section('title', 'Cetak Data Curah Hujan - ' . ($station->station_name ?? 'Stasiun'))

@section('content')
<div class="container">
    <div class="mb-3">
        <h3 class="mb-0">Data Curah Hujan</h3>
        <div><strong>Stasiun:</strong> {{ $station->station_name }}</div>
        <div><strong>Desa:</strong> {{ $station->village->name ?? '-' }}</div>
        <div><strong>Kecamatan:</strong> {{ $station->village->district->name ?? '-' }}</div>
        <div><strong>Kabupaten:</strong> {{ $station->village->district->regency->name ?? '-' }}</div>
    </div>

    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th style="width: 60px;">#</th>
                <th>Bulan</th>
                <th class="text-right">Curah Hujan (mm)</th>
                <th class="text-right">Hari Hujan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rainfalls as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->month_year_label ?? ($row->date ? $row->date->format('F Y') : '-') }}</td>
                    <td class="text-right">{{ number_format($row->rainfall_amount, 2) }}</td>
                    <td class="text-right">{{ $row->rain_days }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    window.addEventListener('load', function() {
        window.print();
    });
    window.addEventListener('afterprint', function() {
        window.close();
    });
</script>
@endsection


