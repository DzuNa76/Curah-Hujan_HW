<!-- @extends('layouts.app')

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
        <form method="POST" action="{{ route('forecasting.process') }}" class="row g-3">
            @csrf

            {{-- 🔹 Jenis Lokasi --}}
            <div class="col-md-3">
                <label for="type">Pilih Jenis Lokasi</label>
                <select id="type" name="type" class="form-control" onchange="toggleLocationSelect(this.value)">
                    <option value="station" {{ ($selectedType ?? 'station') === 'station' ? 'selected' : '' }}>Stasiun</option>
                    <option value="regency" {{ ($selectedType ?? '') === 'regency' ? 'selected' : '' }}>Kota</option>
                </select>
            </div>

            {{-- 🔹 Pilih Stasiun --}}
            <div class="col-md-3" id="stationSelect">
                <label for="station_id">Stasiun</label>
                <select id="station_id" name="id" class="form-control">
                    <option value="all" {{ ($selectedType === 'station' && ($selectedId ?? 'all') === 'all') ? 'selected' : '' }}>Semua Stasiun</option>
                    @if(isset($stations) && $stations->count())
                        @foreach($stations as $st)
                            <option value="{{ $st->id }}" 
                                {{ ($selectedType === 'station' && ($selectedId ?? null) == $st->id) ? 'selected' : '' }}>
                                {{ $st->station_name }} — {{ $st->village->name ?? '-' }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- 🔹 Pilih Kota --}}
            <div class="col-md-3" id="regencySelect">
                <label for="regency_id">Kota</label>
                <select id="regency_id" name="id" class="form-control">
                    <option value="all" {{ ($selectedType === 'regency' && ($selectedId ?? 'all') === 'all') ? 'selected' : '' }}>Semua Kota</option>
                    @if(isset($regencies) && $regencies->count())
                        @foreach($regencies as $r)
                            <option value="{{ $r->id }}" 
                                {{ ($selectedType === 'regency' && ($selectedId ?? null) == $r->id) ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- 🔹 Parameter Alpha, Beta, Gamma --}}
            <div class="col-md-2">
                <label for="alpha">Alpha (α)</label>
                <input type="number" step="0.01" min="0" max="1" name="alpha" id="alpha"
                    value="{{ old('alpha', $alpha ?? '0.3') }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label for="beta">Beta (β)</label>
                <input type="number" step="0.01" min="0" max="1" name="beta" id="beta"
                    value="{{ old('beta', $beta ?? '0.2') }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label for="gamma">Gamma (γ)</label>
                <input type="number" step="0.01" min="0" max="1" name="gamma" id="gamma"
                    value="{{ old('gamma', $gamma ?? '0.1') }}" class="form-control" required>
            </div>

            {{-- 🔹 Rentang Data --}}
            @php
                // Sort descending agar yang terbaru di atas
                $sortedDates = isset($allDates) ? collect($allDates)->sortDesc()->values() : collect([]);
                $defaultEnd = $sortedDates->first() ? \Carbon\Carbon::parse($sortedDates->first())->format('Y-m-d') : null;
            @endphp

            <div class="col-md-3">
                <label for="start">Data Awal</label>
                <select id="start" name="start" class="form-control">
                    @foreach($sortedDates as $date)
                        <option value="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}"
                            {{ ($start ?? '') == \Carbon\Carbon::parse($date)->format('Y-m-d') ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('F Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="end">Data Akhir</label>
                <select id="end" name="end" class="form-control">
                    @foreach($sortedDates as $date)
                        <option value="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}"
                            {{ ($end ?? $defaultEnd) == \Carbon\Carbon::parse($date)->format('Y-m-d') ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('F Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 🔹 Tombol Submit --}}
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-sync"></i> Proses Peramalan
                </button>
            </div>
        </form>
    </div>
</div>



{{-- Jika ada pesan (mis. data terlalu sedikit) tampilkan --}}
@if(isset($message) && $message)
    <div class="alert alert-warning">
        {{ $message }}
    </div>
@endif

{{-- Evaluasi dan Hasil --}}
@if(!isset($message) || !$message)
    {{-- Evaluasi --}}
    <div class="card shadow mb-4">
        <div class="card-header bg-secondary text-white">
            <h6 class="m-0 font-weight-bold">Evaluasi Peramalan</h6>
        </div>
        <div class="card-body">
            <p><strong>MAE (Mean Absolute Error):</strong> {{ isset($mae) ? number_format($mae,4) : '-' }}</p>
            <p><strong>RMSE (Root Mean Square Error):</strong> {{ isset($rmse) ? number_format($rmse,4) : '-' }}</p>
        </div>
    </div>

    {{-- Hasil Peramalan --}}
    <div class="card shadow mb-4">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Hasil Peramalan</h6>

            {{-- Tombol Cetak (kirim semua array hasil sebagai json) --}}
            <form method="POST" action="{{ route('forecasting.print') }}" target="_blank" class="m-0">
                @csrf
                <input type="hidden" name="labels" value="{{ isset($labels) ? htmlentities(json_encode($labels)) : '' }}">
                <input type="hidden" name="values" value="{{ isset($values) ? htmlentities(json_encode($values)) : '' }}">
                <input type="hidden" name="L" value="{{ isset($L) ? htmlentities(json_encode($L)) : '' }}">
                <input type="hidden" name="T" value="{{ isset($T) ? htmlentities(json_encode($T)) : '' }}">
                <input type="hidden" name="S" value="{{ isset($S) ? htmlentities(json_encode($S)) : '' }}">
                <input type="hidden" name="F" value="{{ isset($F) ? htmlentities(json_encode($F)) : '' }}">
                <input type="hidden" name="errors" value="{{ isset($errors) ? htmlentities(json_encode($errors)) : '' }}">
                <input type="hidden" name="mae" value="{{ isset($mae) ? $mae : '' }}">
                <input type="hidden" name="rmse" value="{{ isset($rmse) ? $rmse : '' }}">
                <button type="submit" class="btn btn-light text-dark btn-sm">
                    <i class="fas fa-print"></i> Cetak Hasil Peramalan
                </button>
            </form>
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
                        @if(isset($labels) && isset($values))
                            @foreach($labels as $i => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                <td class="text-right">{{ number_format($values[$i] ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($L[$i] ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($T[$i] ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($S[$i] ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($F[$i] ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($errors[$i] ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format(abs($errors[$i] ?? 0), 2) }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tidak ada hasil peramalan.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Toggle select visibility (stasiun / regency)
    function toggleLocationSelect(type) {
        document.getElementById('stationSelect').classList.toggle('d-none', type !== 'station');
        document.getElementById('regencySelect').classList.toggle('d-none', type !== 'regency');
    }

    document.addEventListener('DOMContentLoaded', function () {
        // set initial visibility
        toggleLocationSelect("{{ isset($selectedType) ? $selectedType : 'station' }}");

        @if(!isset($message) || !$message)
            const ctx = document.getElementById('forecastChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($labels ?? []),
                    datasets: [
                        {
                            label: 'Aktual',
                            data: @json($values ?? []),
                            borderColor: 'rgba(54, 162, 235, 1)',
                            fill: false,
                            tension: 0.2,
                            pointRadius: 3
                        },
                        {
                            label: 'Forecast',
                            data: @json($F ?? []),
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderDash: [5,5],
                            fill: false,
                            tension: 0.2,
                            pointRadius: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: 'Curah Hujan (mm)' } },
                        x: { title: { display: true, text: 'Bulan - Tahun' } }
                    }
                }
            });
        @endif
    });
</script>

{{-- 🔹 Script Toggle Dropdown --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        toggleLocationSelect('{{ $selectedType ?? 'station' }}');
    });
    
    function toggleLocationSelect(type) {
        const stationDiv = document.getElementById('stationSelect');
        const regencyDiv = document.getElementById('regencySelect');
    
        if (type === 'regency') {
            regencyDiv.classList.remove('d-none');
            stationDiv.classList.add('d-none');
        } else {
            stationDiv.classList.remove('d-none');
            regencyDiv.classList.add('d-none');
        }
    }
    </script>

@endsection -->
