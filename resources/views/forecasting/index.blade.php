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
        <form method="POST" action="{{ route('forecasting.process') }}" class="row g-3" id="forecastForm">
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
                <select id="station_id" name="station_id" class="form-control">
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
            <div class="col-md-3" id="regencySelect" style="display: none;">
                <label for="regency_id">Kota</label>
                <select id="regency_id" name="regency_id" class="form-control">
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
                <input type="number" step="0.001" min="0" max="1" name="alpha" id="alpha"
                    value="{{ old('alpha', $alpha ?? '0.3') }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label for="beta">Beta (β)</label>
                <input type="number" step="0.001" min="0" max="1" name="beta" id="beta"
                    value="{{ old('beta', $beta ?? '0.2') }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label for="gamma">Gamma (γ)</label>
                <input type="number" step="0.001" min="0" max="1" name="gamma" id="gamma"
                    value="{{ old('gamma', $gamma ?? '0.3') }}" class="form-control" required>
            </div>

            {{-- 🔹 Rentang Data --}}
            @php
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

{{-- Jika ada pesan error/warning tampilkan --}}
@if(session('error'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

{{-- 🔹 PERBAIKAN: Kondisi tampil hasil yang benar --}}
@if(!empty($labels) && count($labels) > 0 && isset($mae) && isset($rmse))
    {{-- Evaluasi --}}
    <div class="card shadow mb-4">
        <div class="card-header bg-secondary text-white">
            <h6 class="m-0 font-weight-bold">Evaluasi Peramalan</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>MAE (Mean Absolute Error):</strong> {{ number_format($mae, 4) }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>RMSE (Root Mean Square Error):</strong> {{ number_format($rmse, 4) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Hasil Peramalan --}}
    <div class="card shadow mb-4">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Hasil Peramalan</h6>

            {{-- Tombol Cetak --}}
            <form method="POST" action="{{ route('forecasting.print') }}" target="_blank" class="m-0">
                @csrf
                <input type="hidden" name="labels" value="{{ isset($labels) ? htmlentities(json_encode($labels)) : '' }}">
                <input type="hidden" name="values" value="{{ isset($values) ? htmlentities(json_encode($values)) : '' }}">
                <input type="hidden" name="L" value="{{ isset($L) ? htmlentities(json_encode($L)) : '' }}">
                <input type="hidden" name="T" value="{{ isset($T) ? htmlentities(json_encode($T)) : '' }}">
                <input type="hidden" name="S" value="{{ isset($S) ? htmlentities(json_encode($S)) : '' }}">
                <input type="hidden" name="F" value="{{ isset($F) ? htmlentities(json_encode($F)) : '' }}">
                <input type="hidden" name="errorValues" value="{{ isset($errors) ? htmlentities(json_encode($errors)) : (isset($errorValues) ? htmlentities(json_encode($errorValues)) : '') }}">
                <input type="hidden" name="mae" value="{{ isset($mae) ? $mae : '' }}">
                <input type="hidden" name="rmse" value="{{ isset($rmse) ? $rmse : '' }}">
                <input type="hidden" name="mape" value="{{ isset($mape) ? $mape : '' }}">
                <input type="hidden" name="start_date" value="{{ $start ?? '' }}">
                <input type="hidden" name="end_date" value="{{ $end ?? '' }}">
                
                @if(isset($station) && $station)
                    <input type="hidden" name="station_id" value="{{ $station->id }}">
                @elseif(isset($selectedType) && $selectedType === 'station' && isset($selectedId) && $selectedId !== 'all')
                    <input type="hidden" name="station_id" value="{{ $selectedId }}">
                @endif

                @if(isset($regency) && $regency)
                    <input type="hidden" name="regency_id" value="{{ $regency->id }}">
                @elseif(isset($selectedType) && $selectedType === 'regency' && isset($selectedId) && $selectedId !== 'all')
                    <input type="hidden" name="regency_id" value="{{ $selectedId }}">
                @endif

                <button type="submit" class="btn btn-light text-dark btn-sm">
                    <i class="fas fa-print"></i> Cetak Hasil Peramalan
                </button>
            </form>
        </div>

        <div class="card-body">
            <canvas id="forecastChart" height="120"></canvas>

            <div class="table-responsive mt-4">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>Bulan - Tahun</th>
                            <th>Aktual</th>
                            <th>Level (L)</th>
                            <th>Tren (T)</th>
                            <th>Seasonal (S)</th>
                            <th>Forecast (F)</th>
                            <th>Error (e)</th>
                            <th>|Error|</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $errorData = $errors ?? $errorValues ?? [];
                        @endphp
                        @foreach($labels as $i => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td class="text-right">{{ $values[$i] !== null ? number_format($values[$i], 2) : '-' }}</td>
                            <td class="text-right">{{ isset($L[$i]) ? number_format($L[$i], 2) : '-' }}</td>
                            <td class="text-right">{{ isset($T[$i]) ? number_format($T[$i], 2) : '-' }}</td>
                            <td class="text-right">{{ isset($S[$i]) ? number_format($S[$i], 2) : '-' }}</td>
                            <td class="text-right">{{ isset($F[$i]) ? number_format($F[$i], 2) : '-' }}</td>
                            <td class="text-right">{{ isset($errorData[$i]) && $errorData[$i] !== null ? number_format($errorData[$i], 2) : '-' }}</td>
                            <td class="text-right">{{ isset($errorData[$i]) && $errorData[$i] !== null ? number_format(abs($errorData[$i]), 2) : '-' }}</td>
                        </tr>
                        @endforeach
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
        const stationDiv = document.getElementById('stationSelect');
        const regencyDiv = document.getElementById('regencySelect');
        
        if (type === 'regency') {
            stationDiv.style.display = 'none';
            regencyDiv.style.display = 'block';
        } else {
            stationDiv.style.display = 'block';
            regencyDiv.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Set initial visibility
        toggleLocationSelect("{{ $selectedType ?? 'station' }}");

        // 🔹 PERBAIKAN: Render chart jika ada data
        @if(!empty($labels) && count($labels) > 0 && isset($mae) && isset($rmse))
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
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            fill: false,
                            tension: 0.2,
                            pointRadius: 3,
                            pointBackgroundColor: 'rgba(54, 162, 235, 1)'
                        },
                        {
                            label: 'Forecast',
                            data: @json($F ?? []),
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0.2,
                            pointRadius: 3,
                            pointBackgroundColor: 'rgba(255, 99, 132, 1)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { 
                        legend: { position: 'bottom' },
                        title: { display: true, text: 'Grafik Peramalan Curah Hujan' }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: { callback: function(value) { return value.toFixed(2); } },
                            title: { display: true, text: 'Curah Hujan (mm)' }
                        },
                        x: { title: { display: true, text: 'Bulan - Tahun' } }
                    }
                }
            });
        @endif
    });

    // Handle form submission - ensure correct field name
    document.getElementById('forecastForm').addEventListener('submit', function(e) {
        const type = document.getElementById('type').value;
        
        if (type === 'regency') {
            // Rename regency_id to 'id' before submit
            const regencySelect = document.getElementById('regency_id');
            regencySelect.setAttribute('name', 'id');
            
            // Disable station field
            document.getElementById('station_id').disabled = true;
        } else {
            // Rename station_id to 'id' before submit
            const stationSelect = document.getElementById('station_id');
            stationSelect.setAttribute('name', 'id');
            
            // Disable regency field
            document.getElementById('regency_id').disabled = true;
        }
    });
</script>

@endsection