@extends('layouts.app')

@section('title', 'Data Curah Hujan')

@section('content')
<h1 class="h3 mb-2 text-gray-800">Data Curah Hujan</h1>
<p class="mb-4">
    Menampilkan data curah hujan bulanan berdasarkan stasiun atau kota, dengan opsi tahun tertentu.
</p>

@include('components.alert')

{{-- ======================== --}}
{{-- 🔹 CARD FILTER DAN GRAFIK --}}
{{-- ======================== --}}
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <h6 class="m-0 font-weight-bold text-primary mb-2 mb-md-0">Filter & Grafik</h6>

            <form method="GET" action="{{ route('rainfall.index') }}" class="form-inline mb-2 mb-md-0">
                <div class="form-group mr-2">
                    <label for="regency_id" class="mr-2">Kota:</label>
                    <select name="regency_id" id="regency_id" class="form-control">
                        <option value="all" {{ $selectedRegency == 'all' ? 'selected' : '' }}>🌆 Semua Kota</option>
                        @foreach ($regencies as $regency)
                            <option value="{{ $regency->id }}" {{ $selectedRegency == $regency->id ? 'selected' : '' }}>
                                {{ $regency->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group mr-2">
                    <label for="station_id" class="mr-2">Stasiun:</label>
                    <select name="station_id" id="station_id" class="form-control">
                        <option value="all" {{ $selectedStation == 'all' ? 'selected' : '' }}>🌍 Semua Stasiun</option>
                        @foreach ($stations as $station)
                            <option value="{{ $station->id }}" {{ $selectedStation == $station->id ? 'selected' : '' }}>
                                {{ $station->station_name }} — {{ $station->village->district->regency->name ?? '-' }}
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
        <h6 class="font-weight-bold text-secondary mb-3">
            📈 Grafik Curah Hujan Tahun {{ $selectedYear }}
        </h6>
        <canvas id="rainfallChart" height="110"></canvas>
    </div>
</div>

{{-- ======================== --}}
{{-- 🔹 CARD TABEL DATA --}}
{{-- ======================== --}}
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Tabel Data Curah Hujan</h6>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>Bulan</th>
                        <th>Stasiun</th>
                        <th>Kota</th>
                        <th>Lokasi Lengkap</th>
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
                            <td>{{ $data->station->village->district->regency->name ?? '-' }}</td>
                            <td>
                                {{ $data->station->village->name ?? '-' }},
                                {{ $data->station->village->district->name ?? '-' }}
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
                            <td colspan="7" class="text-center text-muted">Tidak ada data untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('components.delete-modal')

{{-- ======================== --}}
{{-- 🔹 CHART.JS --}}
{{-- ======================== --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('rainfallChart').getContext('2d');
    const chartData = @json($chartData);

    // Kelompokkan per kota (regency) atau stasiun tergantung data
    const grouped = {};
    chartData.forEach(item => {
        const key = item.regency_name || item.station_name;
        if (!grouped[key]) grouped[key] = [];
        grouped[key].push({ month: item.month, avg_rain: item.avg_rain });
    });

    // Ambil semua bulan (urut)
    const months = [...new Set(chartData.map(i => i.month))];

    // Buat dataset dinamis
    const datasets = Object.entries(grouped).map(([label, values], i) => ({
        label,
        data: months.map(m => {
            const found = values.find(v => v.month === m);
            return found ? found.avg_rain : 0;
        }),
        borderColor: `hsl(${i * 45}, 70%, 45%)`,
        backgroundColor: `hsla(${i * 45}, 70%, 45%, 0.15)`,
        fill: true,
        tension: 0.3,
        borderWidth: 2,
        pointRadius: 3,
    }));

    new Chart(ctx, {
        type: 'line',
        data: { labels: months, datasets },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Curah Hujan (mm)' } },
                x: { title: { display: true, text: 'Bulan (YYYY-MM)' } }
            }
        }
    });
});
</script>
@endsection
