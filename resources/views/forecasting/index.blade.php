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
                <label for="alpha">Alpha (α)
                    <small class="text-muted">(Batas Parameter 0 - 1)</small>
                </label>
                <input type="number" step="0.001" min="0" max="1" name="alpha" id="alpha"
                    value="{{ old('alpha', $alpha ?? '0.3') }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label for="beta">Beta (β)
                    <small class="text-muted">(Batas Parameter 0 - 1)</small>
                </label>
                <input type="number" step="0.001" min="0" max="1" name="beta" id="beta"
                    value="{{ old('beta', $beta ?? '0.2') }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label for="gamma">Gamma (γ)
                    <small class="text-muted">(Batas Parameter 0 - 1)</small>
                </label>
                <input type="number" step="0.001" min="0" max="1" name="gamma" id="gamma"
                    value="{{ old('gamma', $gamma ?? '0.3') }}" class="form-control" required>
            </div>

            {{-- 🔹 Rentang Data (Dynamic Dropdown) --}}
            @php
                $sortedDates = isset($allDates) ? collect($allDates)->sortDesc()->values() : collect([]);
                $defaultEnd = $sortedDates->first() ? \Carbon\Carbon::parse($sortedDates->first())->format('Y-m-d') : null;
            @endphp

            <div class="col-md-3">
                <label for="start">Data Awal</label>
                <select id="start" name="start" class="form-control" required>
                    <option value="">Memuat...</option>
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
                <select id="end" name="end" class="form-control" required>
                    <option value="">Memuat...</option>
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

{{-- Error Message yang Informatif dan Terstruktur --}}
@if(session('error') || session('validation_error'))
    @php
        $validationError = session('validation_error');
    @endphp
    
    <div class="card shadow mb-4 border-danger">
        <div class="card-header bg-danger text-white d-flex align-items-center">
            <i class="fas fa-exclamation-circle fa-2x mr-3"></i>
            <div>
                <h5 class="mb-0 font-weight-bold">Data Tidak Lengkap - Forecasting Dibatalkan</h5>
                <small>Proses peramalan dihentikan karena data tidak memenuhi syarat kelengkapan 100%</small>
            </div>
        </div>
        <div class="card-body">
            @if($validationError)
                {{-- Statistik Data --}}
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="text-primary mb-0">{{ $validationError['expected_count'] ?? 0 }}</h3>
                                <small class="text-muted">Bulan Diharapkan</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="text-success mb-0">{{ $validationError['actual_count'] ?? 0 }}</h3>
                                <small class="text-muted">Bulan Tersedia</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="text-danger mb-0">{{ count($validationError['missing_months'] ?? []) }}</h3>
                                <small class="text-muted">Bulan Missing</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="text-warning mb-0">{{ $validationError['completeness_ratio'] ?? 0 }}%</h3>
                                <small class="text-muted">Kelengkapan Data</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Progress Bar Kelengkapan --}}
                <div class="mb-4">
                    <label class="font-weight-bold">Tingkat Kelengkapan Data:</label>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar 
                            @if($validationError['completeness_ratio'] >= 100) bg-success
                            @elseif($validationError['completeness_ratio'] >= 80) bg-warning
                            @else bg-danger
                            @endif" 
                            role="progressbar" 
                            style="width: {{ $validationError['completeness_ratio'] }}%"
                            aria-valuenow="{{ $validationError['completeness_ratio'] }}" 
                            aria-valuemin="0" 
                            aria-valuemax="100">
                            <strong>{{ $validationError['completeness_ratio'] }}%</strong>
                        </div>
                    </div>
                </div>

                {{-- Daftar Bulan yang Missing --}}
                @if(!empty($validationError['missing_months']))
                    <div class="mb-4">
                        <label class="font-weight-bold mb-2">Bulan yang Missing:</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($validationError['missing_months'] as $month)
                                <span class="badge badge-danger badge-lg px-3 py-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-calendar-times mr-1"></i>
                                    {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Tabel Detail Stasiun dengan Missing Data (untuk filter semua stasiun) --}}
                @if(session('stations_detail') && !empty(session('stations_detail')))
                    @php
                        $stationsDetail = session('stations_detail');
                    @endphp
                    <div class="mb-4">
                        <h6 class="font-weight-bold mb-3">
                            <i class="fas fa-list-alt mr-2"></i>Detail Stasiun dengan Data Missing:
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Stasiun</th>
                                        <th>Kabupaten</th>
                                        <th>Kecamatan</th>
                                        <th>Desa</th>
                                        <th class="text-center">Data Tersedia</th>
                                        <th class="text-center">Data Diharapkan</th>
                                        <th class="text-center">Data Missing</th>
                                        <th class="text-center">Kelengkapan</th>
                                        <th>Bulan yang Missing</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stationsDetail as $index => $station)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $station['station_name'] }}</strong></td>
                                            <td>{{ $station['regency_name'] }}</td>
                                            <td>{{ $station['district_name'] }}</td>
                                            <td>{{ $station['village_name'] }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-info">{{ $station['data_count'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary">{{ $station['expected_count'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-danger">{{ $station['missing_count'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $station['completeness_ratio'] >= 100 ? 'success' : ($station['completeness_ratio'] >= 80 ? 'warning' : 'danger') }}">
                                                    {{ $station['completeness_ratio'] }}%
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap" style="gap: 0.25rem;">
                                                    @foreach(array_slice($station['missing_months'], 0, 6) as $month)
                                                        <span class="badge badge-danger badge-sm" style="font-size: 0.75rem;">
                                                            {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('M Y') }}
                                                        </span>
                                                    @endforeach
                                                    @if(count($station['missing_months']) > 6)
                                                        <span class="badge badge-secondary badge-sm" style="font-size: 0.75rem;">
                                                            +{{ count($station['missing_months']) - 6 }} lainnya
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            <small>
                                <i class="fas fa-info-circle mr-1"></i>
                                Menampilkan {{ count($stationsDetail) }} stasiun yang memiliki data missing dari total stasiun yang dianalisis.
                            </small>
                        </div>
                    </div>
                @endif

                {{-- Penjelasan dan Call to Action --}}
                <div class="alert alert-info">
                    <h6 class="font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Mengapa Data Harus Lengkap?</h6>
                    <p class="mb-2">
                        Sistem forecasting Holt-Winters memerlukan data yang lengkap 100% untuk menghasilkan peramalan yang akurat dan dapat dipertanggungjawabkan. 
                        Data yang tidak lengkap dapat menyebabkan:
                    </p>
                    <ul class="mb-0">
                        <li>Inisialisasi komponen seasonal yang tidak akurat</li>
                        <li>Error propagasi yang signifikan dalam perhitungan smoothing</li>
                        <li>Hasil forecast yang tidak reliable untuk pengambilan keputusan</li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <h6 class="font-weight-bold"><i class="fas fa-tasks mr-2"></i>Tindakan yang Diperlukan:</h6>
                    <ol class="mb-0">
                        <li>Buka menu <strong>Data Curah Hujan</strong> di sidebar</li>
                        <li>Lengkapi data curah hujan untuk bulan-bulan yang missing (ditandai dengan badge merah di atas)</li>
                        @if(session('stations_detail') && !empty(session('stations_detail')))
                            <li>Perhatikan tabel detail stasiun di atas untuk mengetahui stasiun mana yang perlu dilengkapi datanya</li>
                        @endif
                        <li>Pastikan semua bulan dalam rentang yang dipilih memiliki data</li>
                        <li>Kembali ke halaman ini dan coba proses forecasting lagi</li>
                    </ol>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
@endif

{{-- Error Message untuk Konsistensi Data Antar Stasiun --}}
@if(session('consistency_error'))
    @php
        $consistencyError = session('consistency_error');
    @endphp
    
    <div class="card shadow mb-4 border-danger">
        <div class="card-header bg-danger text-white d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
            <div>
                <h5 class="mb-0 font-weight-bold">Data Antar Stasiun Tidak Konsisten - Forecasting Dibatalkan</h5>
                <small>Proses peramalan dihentikan karena ditemukan ketidakkonsistenan data antar stasiun</small>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-danger">
                <h6 class="font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Ringkasan Masalah:</h6>
                <ul class="mb-0">
                    <li>Total stasiun yang dianalisis: <strong>{{ $consistencyError['total_stations'] }}</strong></li>
                    <li>Stasiun dengan data tidak konsisten: <strong>{{ $consistencyError['inconsistent_stations'] }}</strong></li>
                    <li>Bulan yang diharapkan per stasiun: <strong>{{ $consistencyError['expected_count'] }}</strong> bulan</li>
                    <li>Variasi panjang data: <strong>{{ implode(', ', $consistencyError['unique_data_counts']) }}</strong> bulan</li>
                </ul>
            </div>

            @if(!empty($consistencyError['inconsistencies']))
                <div class="mb-4">
                    <h6 class="font-weight-bold mb-3">Detail Stasiun dengan Data Tidak Konsisten:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Stasiun</th>
                                    <th>Kabupaten</th>
                                    <th>Data Tersedia</th>
                                    <th>Data Diharapkan</th>
                                    <th>Data Missing</th>
                                    <th>Kelengkapan</th>
                                    <th>Bulan yang Missing</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($consistencyError['inconsistencies'] as $index => $inconsistency)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $inconsistency['station_name'] }}</strong></td>
                                        <td>{{ $inconsistency['regency_name'] }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ $inconsistency['data_count'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary">{{ $inconsistency['expected_count'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-danger">{{ $inconsistency['missing_count'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ $inconsistency['completeness_ratio'] >= 100 ? 'success' : ($inconsistency['completeness_ratio'] >= 80 ? 'warning' : 'danger') }}">
                                                {{ $inconsistency['completeness_ratio'] }}%
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach(array_slice($inconsistency['missing_months'], 0, 5) as $month)
                                                    <span class="badge badge-danger badge-sm">
                                                        {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('M Y') }}
                                                    </span>
                                                @endforeach
                                                @if(count($inconsistency['missing_months']) > 5)
                                                    <span class="badge badge-secondary badge-sm">
                                                        +{{ count($inconsistency['missing_months']) - 5 }} lainnya
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="alert alert-warning">
                <h6 class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Mengapa Konsistensi Data Penting?</h6>
                <p class="mb-2">
                    Untuk forecasting level kabupaten, sistem menghitung rata-rata curah hujan dari semua stasiun dalam wilayah tersebut. 
                    Ketidakkonsistenan data antar stasiun dapat menyebabkan:
                </p>
                <ul class="mb-0">
                    <li>Rata-rata yang tidak akurat karena beberapa stasiun memiliki data lebih sedikit</li>
                    <li>Bias dalam perhitungan forecasting yang mengarah pada prediksi yang tidak reliable</li>
                    <li>Ketidakseimbangan representasi data dari stasiun-stasiun yang berbeda</li>
                </ul>
            </div>

            <div class="alert alert-info">
                <h6 class="font-weight-bold"><i class="fas fa-tasks mr-2"></i>Rekomendasi Perbaikan:</h6>
                <ol class="mb-0">
                    <li>Buka menu <strong>Data Curah Hujan</strong> di sidebar</li>
                    <li>Periksa setiap stasiun yang tercantum dalam tabel di atas</li>
                    <li>Lengkapi data untuk bulan-bulan yang missing pada setiap stasiun</li>
                    <li>Pastikan semua stasiun memiliki jumlah data yang sama dalam rentang yang dipilih</li>
                    <li>Setelah data lengkap dan konsisten, kembali ke halaman ini dan coba proses forecasting lagi</li>
                </ol>
            </div>
        </div>
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

        const form = document.getElementById('forecastForm');
        const typeSelect = document.getElementById('type');
        const regencySelect = document.getElementById('regency_id');
        const stationSelect = document.getElementById('station_id');
        const startSelect = document.getElementById('start');
        const endSelect = document.getElementById('end');

        // --- 🔹 Fungsi untuk update dropdown tanggal via AJAX ---
        function updateDateDropdowns() {
            const type = typeSelect.value;
            const id = type === 'station' ? stationSelect.value : regencySelect.value;
            
            // Tampilkan loading state
            startSelect.innerHTML = '<option value="">Memuat...</option>';
            endSelect.innerHTML = '<option value="">Memuat...</option>';
            startSelect.disabled = true;
            endSelect.disabled = true;

            // AJAX call untuk mendapatkan available dates
            fetch(`{{ route('forecasting.available-dates') }}?type=${type}&id=${id}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.dates && data.dates.length > 0) {
                    // Update start dropdown
                    startSelect.innerHTML = '';
                    data.dates.forEach(date => {
                        const option = document.createElement('option');
                        option.value = date.value;
                        option.textContent = date.label;
                        startSelect.appendChild(option);
                    });

                    // Update end dropdown (sama dengan start)
                    endSelect.innerHTML = '';
                    data.dates.forEach(date => {
                        const option = document.createElement('option');
                        option.value = date.value;
                        option.textContent = date.label;
                        endSelect.appendChild(option);
                    });

                    // Set default values (first and last)
                    if (data.dates.length > 0) {
                        startSelect.value = data.dates[0].value;
                        endSelect.value = data.dates[data.dates.length - 1].value;
                    }

                    startSelect.disabled = false;
                    endSelect.disabled = false;
                } else {
                    startSelect.innerHTML = '<option value="">Tidak ada data</option>';
                    endSelect.innerHTML = '<option value="">Tidak ada data</option>';
                }
            })
            .catch(error => {
                console.error('Error fetching available dates:', error);
                startSelect.innerHTML = '<option value="">Error memuat data</option>';
                endSelect.innerHTML = '<option value="">Error memuat data</option>';
            });
        }

        // --- 🔹 Event listeners untuk update dropdown saat lokasi berubah ---
        typeSelect.addEventListener('change', function() {
            toggleLocationSelect(this.value);
            // Tunggu sebentar untuk memastikan select sudah ter-update
            setTimeout(updateDateDropdowns, 100);
        });

        stationSelect.addEventListener('change', function() {
            if (typeSelect.value === 'station') {
                updateDateDropdowns();
            }
        });

        regencySelect.addEventListener('change', function() {
            if (typeSelect.value === 'regency') {
                updateDateDropdowns();
            }
        });

        // --- 🔹 Reset sessionStorage jika user datang dari halaman lain ---
        const navEntries = performance.getEntriesByType('navigation');
        const navType = navEntries.length > 0 ? navEntries[0].type : null;
        // Jika navigation type adalah 'navigate' (halaman baru, bukan reload/back-forward)
        if (navType === 'navigate' || navType === 'reload') {
            sessionStorage.removeItem('forecast_start');
            sessionStorage.removeItem('forecast_end');
        }

        // --- 🔹 Simpan pilihan start & end selama halaman aktif ---
        startSelect.addEventListener('change', () => {
            sessionStorage.setItem('forecast_start', startSelect.value);
        });
        endSelect.addEventListener('change', () => {
            sessionStorage.setItem('forecast_end', endSelect.value);
        });

        // Kembalikan nilai sebelumnya jika halaman direload
        const savedStart = sessionStorage.getItem('forecast_start');
        const savedEnd = sessionStorage.getItem('forecast_end');
        if (savedStart) startSelect.value = savedStart;
        if (savedEnd) endSelect.value = savedEnd;

        // --- 🔹 Render chart jika ada data ---
        @if(!empty($labels) && count($labels) > 0 && isset($mae) && isset($rmse))
            @php
                // Siapkan data untuk missing indicators jika ada validation error
                $missingIndices = session('validation_error')['missing_indices'] ?? [];
                $missingDataPoints = [];
                if (!empty($missingIndices)) {
                    foreach ($missingIndices as $idx) {
                        if (isset($labels[$idx])) {
                            $missingDataPoints[] = [
                                'x' => $idx,
                                'y' => null,
                                'label' => $labels[$idx]
                            ];
                        }
                    }
                }
            @endphp

            const ctx = document.getElementById('forecastChart').getContext('2d');
            
            // Siapkan datasets
            const datasets = [
                        {
                            label: 'Aktual',
                            data: @json($values ?? []),
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            fill: false,
                            tension: 0.2,
                            pointRadius: 3,
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    pointHoverRadius: 5
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
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    pointHoverRadius: 5
                }
            ];

            // Chart configuration
            const chartConfig = {
                type: 'line',
                data: {
                    labels: @json($labels ?? []),
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { 
                        legend: { 
                            position: 'bottom',
                            display: true
                        },
                        title: { 
                            display: true, 
                            text: 'Grafik Peramalan Curah Hujan',
                            font: { size: 16, weight: 'bold' }
                        },
                        tooltip: {
                            callbacks: {
                                afterLabel: function(context) {
                                    @if(!empty($missingIndices))
                                        const missingIndices = @json($missingIndices);
                                        if (missingIndices.includes(context.dataIndex)) {
                                            return '⚠️ Data Missing - Tidak tersedia';
                                        }
                                    @endif
                                    return '';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: { callback: function(value) { return value.toFixed(2); } },
                            title: { display: true, text: 'Curah Hujan (mm)' }
                        },
                        x: { 
                            title: { display: true, text: 'Bulan - Tahun' },
                            ticks: {
                                callback: function(value, index) {
                                    @if(!empty($missingIndices))
                                        const missingIndices = @json($missingIndices);
                                        if (missingIndices.includes(index)) {
                                            return this.getLabelForValue(value) + ' ⚠️';
                                        }
                                    @endif
                                    return this.getLabelForValue(value);
                                }
                            }
                        }
                    }
                }
            };

            const chart = new Chart(ctx, chartConfig);

            // Tambahkan visual indicator untuk missing data setelah chart dibuat
            @if(!empty($missingIndices))
                const missingIndices = @json($missingIndices);
                const chartArea = chart.chartArea;
                
                // Tambahkan annotation visual untuk missing data
                missingIndices.forEach(index => {
                    // Chart.js tidak memiliki built-in annotation, jadi kita gunakan CSS atau plugin
                    // Untuk sekarang, kita akan menandai di tooltip dan label saja
                });
            @endif
        @endif

        // --- 🔹 Handle form submission ---
        form.addEventListener('submit', function(e) {
            const type = typeSelect.value;

            if (type === 'regency') {
                regencySelect.setAttribute('name', 'id');
                regencySelect.disabled = false;
                stationSelect.disabled = true;
            } else {
                stationSelect.setAttribute('name', 'id');
                stationSelect.disabled = false;
                regencySelect.disabled = true;
            }

            // Simpan rentang terakhir selama halaman aktif
            sessionStorage.setItem('forecast_start', startSelect.value);
            sessionStorage.setItem('forecast_end', endSelect.value);
        });
    });
</script>

@endsection