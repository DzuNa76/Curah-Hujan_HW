@extends('layouts.app')

@section('title', 'Data Curah Hujan')

@section('content')
<h1 class="h3 mb-2 text-gray-800">Data Curah Hujan</h1>
<p class="mb-4">
    Menampilkan data curah hujan bulanan berdasarkan stasiun pengamatan dan tahun tertentu.
</p>

@include('components.alert')

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <h6 class="m-0 font-weight-bold text-primary mb-2 mb-md-0">Filter Data</h6>

            <form method="GET" action="{{ route('rainfall.index') }}" class="form-inline mb-2 mb-md-0">
                <div class="form-group mr-2">
                    <label for="station_id" class="mr-2">Stasiun:</label>
                    <select name="station_id" id="station_id" class="form-control">
                        <option value="all" {{ $selectedStation == 'all' ? 'selected' : '' }}>Semua Stasiun</option>
                        @foreach ($stations as $station)
                            <option value="{{ $station->id }}" {{ $selectedStation == $station->id ? 'selected' : '' }}>
                                {{ $station->station_name }} — 
                                {{ $station->village->name ?? '-' }},
                                {{ $station->village->district->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mr-2">
                    <label for="year" class="mr-2">Tahun:</label>
                    <select name="year" id="year" class="form-control">
                        @foreach ($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Tampilkan
                </button>
            </form>

            <a href="{{ route('rainfall.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Tambah Data
            </a>
        </div>
    </div>

    <div class="card-body">
        {{-- GRAFIK GARIS --}}
        <div class="mb-4">
            <h6 class="font-weight-bold text-secondary mb-3">
                Grafik Rata-Rata Curah Hujan ({{ $selectedYear }})
            </h6>
            <canvas id="rainfallChart" height="100"></canvas>
        </div>

        {{-- TABEL DATA --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>Bulan</th>
                        <th>Stasiun</th>
                        <th>Lokasi</th>
                        <th>Curah Hujan (mm)</th>
                        <th>Hari Hujan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rainfallData as $data)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($data->date)->translatedFormat('F Y') }}</td>
                            <td>{{ $data->station->station_name ?? '-' }}</td>
                            <td>
                                {{ $data->station->village->name ?? '-' }},
                                {{ $data->station->village->district->name ?? '-' }},
                                {{ $data->station->village->district->regency->name ?? '-' }}
                            </td>
                            <td>{{ number_format($data->rainfall_amount, 2) }}</td>
                            <td>{{ $data->rain_days }}</td>
                            <td class="text-center">
                                <a href="{{ route('rainfall.edit', [$data->station_id, $data->id]) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button type="button" 
                                        class="btn btn-danger btn-sm btn-delete"
                                        data-action="{{ route('rainfall.destroy', [$data->station_id, $data->id]) }}">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Tidak ada data untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('components.delete-modal')

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('rainfallChart').getContext('2d');
        const chartData = @json($chartData);

        const labels = chartData.map(item => {
            const bulan = new Date(0, item.month - 1).toLocaleString('id-ID', { month: 'long' });
            return bulan.charAt(0).toUpperCase() + bulan.slice(1);
        });

        const values = chartData.map(item => item.avg_rain);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Curah Hujan (mm)',
                    data: values,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Curah Hujan (mm)' }
                    },
                    x: {
                        title: { display: true, text: 'Bulan' }
                    }
                }
            }
        });
    });
</script>
@endsection
