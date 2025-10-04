@extends('layouts.app')

@section('title', 'Peramalan Curah Hujan')

@section('content')
<h1 class="h3 mb-3 text-gray-800">Peramalan Curah Hujan (Holt–Winters Additive)</h1>
<p class="mb-4">Pilih parameter dan rentang data untuk melakukan peramalan curah hujan bulanan.</p>

{{-- Form Parameter --}}
<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <h6 class="m-0 font-weight-bold">Parameter Peramalan</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('forecasting.index') }}" class="row g-3">
            <div class="col-md-2">
                <label for="alpha">Alpha (α)</label>
                <input type="number" step="0.01" min="0" max="1" name="alpha" value="{{ $alpha }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label for="beta">Beta (β)</label>
                <input type="number" step="0.01" min="0" max="1" name="beta" value="{{ $beta }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label for="gamma">Gamma (γ)</label>
                <input type="number" step="0.01" min="0" max="1" name="gamma" value="{{ $gamma }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Data Awal</label>
                <select name="start" class="form-control">
                    @foreach($allDates as $date)
                        <option value="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}" {{ $start == \Carbon\Carbon::parse($date)->format('Y-m-d') ? 'selected' : '' }}>{{ $date }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Data Akhir</label>
                <select name="end" class="form-control">
                    @foreach($allDates as $date)
                        <option value="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}" {{ $end == \Carbon\Carbon::parse($date)->format('Y-m-d') ? 'selected' : '' }}>{{ $date }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-success"><i class="fas fa-sync"></i> Proses Peramalan</button>
            </div>
        </form>
    </div>
</div>

{{-- Grafik dan Tabel --}}
@if(!$message)
<div class="card shadow mb-4">
    <div class="card-header bg-info text-white">
        <h6 class="m-0 font-weight-bold">Hasil Peramalan</h6>
    </div>
    <div class="card-body">
        <canvas id="forecastChart" height="120"></canvas>

        <div class="table-responsive mt-4">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Bulan - Tahun</th>
                        <th>Aktual</th>
                        <th>Level</th>
                        <th>Tren</th>
                        <th>Seasonal</th>
                        <th>Forecast</th>
                        <th>Error</th>
                        <th>Absolut Error</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($values as $i => $v)
                    <tr>
                        <td>{{ $labels[$i] ?? '-' }}</td>
                        <td>{{ number_format($v, 2) }}</td>
                        <td>{{ number_format($L[$i] ?? 0, 2) }}</td>
                        <td>{{ number_format($T[$i] ?? 0, 2) }}</td>
                        <td>{{ number_format($S[$i] ?? 0, 2) }}</td>
                        <td>{{ number_format($F[$i] ?? 0, 2) }}</td>
                        <td>{{ number_format($errors[$i] ?? 0, 2) }}</td>
                        <td>{{ number_format(abs($errors[$i] ?? 0), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Evaluasi --}}
<div class="card shadow">
    <div class="card-header bg-secondary text-white">
        <h6 class="m-0 font-weight-bold">Evaluasi Peramalan</h6>
    </div>
    <div class="card-body">
        <p><strong>MAE (Mean Absolute Error):</strong> {{ $mae }}</p>
        <p><strong>RMSE (Root Mean Square Error):</strong> {{ $rmse }}</p>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if(!$message)
const ctx = document.getElementById('forecastChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($labels),
        datasets: [
            {
                label: 'Aktual',
                data: @json($values),
                borderColor: 'rgba(54, 162, 235, 1)',
                fill: false
            },
            {
                label: 'Forecast',
                data: @json($F),
                borderColor: 'rgba(255, 99, 132, 1)',
                borderDash: [5,5],
                fill: false
            }
        ]
    },
});
@endif
</script>
@endsection
