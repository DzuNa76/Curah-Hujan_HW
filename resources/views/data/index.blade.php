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

{{-- Alert Missing Data (Mengikuti Style Forecasting Index) --}}
@if(isset($missingDataInfo) && $missingDataInfo['has_gaps'])
    <div class="card shadow mb-4 border-warning">
        <a href="#collapseMissingData" class="d-block card-header bg-warning text-dark d-flex align-items-center justify-content-between" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseMissingData" style="text-decoration: none;">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fa-2x mr-3"></i>
                <div>
                    <h5 class="mb-0 font-weight-bold">Data Tidak Lengkap - Peringatan</h5>
                    <small>Ditemukan data yang hilang dari {{ \Carbon\Carbon::parse($missingDataInfo['start_month'] . '-01')->translatedFormat('F Y') }} sampai {{ \Carbon\Carbon::parse($missingDataInfo['end_month'] . '-01')->translatedFormat('F Y') }}</small>
                </div>
            </div>
        </a>
        <div class="collapse show" id="collapseMissingData">
        <div class="card-body">
            {{-- Statistik Data --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h3 class="text-primary mb-0">{{ $missingDataInfo['expected_count'] ?? 0 }}</h3>
                            <small class="text-muted">Bulan Diharapkan</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h3 class="text-success mb-0">{{ $missingDataInfo['actual_count'] ?? 0 }}</h3>
                            <small class="text-muted">Bulan Tersedia</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h3 class="text-danger mb-0">{{ $missingDataInfo['total_missing'] ?? 0 }}</h3>
                            <small class="text-muted">Bulan Missing</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h3 class="text-warning mb-0">{{ $missingDataInfo['completeness_ratio'] ?? 0 }}%</h3>
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
                        @if($missingDataInfo['completeness_ratio'] >= 100) bg-success
                        @elseif($missingDataInfo['completeness_ratio'] >= 80) bg-warning
                        @else bg-danger
                        @endif" 
                        role="progressbar" 
                        style="width: {{ $missingDataInfo['completeness_ratio'] }}%"
                        aria-valuenow="{{ $missingDataInfo['completeness_ratio'] }}" 
                        aria-valuemin="0" 
                        aria-valuemax="100">
                        <strong>{{ $missingDataInfo['completeness_ratio'] }}%</strong>
                    </div>
                </div>
            </div>

            {{-- Daftar Bulan yang Missing --}}
            @if(!empty($missingDataInfo['missing_months']))
                <div class="mb-4">
                    <label class="font-weight-bold mb-2">Bulan yang Missing:</label>
                    <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                        @foreach($missingDataInfo['missing_months'] as $month)
                            <span class="badge badge-danger badge-lg px-3 py-2" style="font-size: 0.9rem;">
                                <i class="fas fa-calendar-times mr-1"></i>
                                {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Penjelasan dan Call to Action --}}
            {{-- <div class="alert alert-info">
                <h6 class="font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Mengapa Data Harus Lengkap?</h6>
                <p class="mb-2">
                    Sistem memeriksa kelengkapan data dari awal tahun {{ $selectedYear }} sampai bulan saat ini ({{ \Carbon\Carbon::parse($missingDataInfo['current_month'] . '-01')->translatedFormat('F Y') }}). 
                    Data yang tidak lengkap dapat menyebabkan:
                </p>
                <ul class="mb-0">
                    <li>Analisis trend yang tidak akurat</li>
                    <li>Kesalahan dalam perhitungan forecasting</li>
                    <li>Hasil prediksi yang tidak reliable untuk pengambilan keputusan</li>
                </ul>
            </div> --}}

            {{-- Tabel Detail Stasiun dengan Missing Data (untuk filter semua stasiun) --}}
            @if(isset($stationsDetail) && !empty($stationsDetail) && ($selectedStation === 'all'))
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

            <div class="alert alert-warning">
                <h6 class="font-weight-bold"><i class="fas fa-tasks mr-2"></i>Tindakan yang Diperlukan:</h6>
                <ol class="mb-0">
                    <li>Gunakan tombol <strong>"Tambah Data"</strong> di atas untuk menambahkan data yang hilang</li>
                    <li>Lengkapi data curah hujan untuk bulan-bulan yang missing (ditandai dengan badge merah di atas)</li>
                    @if(isset($stationsDetail) && !empty($stationsDetail))
                        <li>Perhatikan tabel detail stasiun di atas untuk mengetahui stasiun mana yang perlu dilengkapi datanya</li>
                    @endif
                    <li>Pastikan semua bulan dari {{ \Carbon\Carbon::parse($missingDataInfo['start_month'] . '-01')->translatedFormat('F Y') }} sampai {{ \Carbon\Carbon::parse($missingDataInfo['end_month'] . '-01')->translatedFormat('F Y') }} memiliki data</li>
                    <li>Setelah data lengkap, sistem akan otomatis memperbarui status kelengkapan</li>
                </ol>
            </div>
        </div>
        </div>
    </div>
@endif

{{-- ======================== --}}
{{-- 🔹 CARD TABEL DATA --}}
{{-- ======================== --}}
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Tabel Data Curah Hujan</h6>
        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalCetakData">
            <i class="fas fa-print"></i> Cetak Data
        </button>
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
{{-- 🔹 MODAL CETAK DATA --}}
{{-- ======================== --}}
<div class="modal fade" id="modalCetakData" tabindex="-1" role="dialog" aria-labelledby="modalCetakDataLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCetakDataLabel">
                    <i class="fas fa-print mr-2"></i>Cetak Data Curah Hujan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formCetakData" method="POST" action="{{ route('data.cetak') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="kategori_cetak">Kategori Cetak <span class="text-danger">*</span></label>
                        <select name="kategori" id="kategori_cetak" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="kota">Kota</option>
                            <option value="pos">Pos/Stasiun</option>
                        </select>
                        <small class="form-text text-muted">Pilih kategori berdasarkan Kota atau Pos/Stasiun</small>
                    </div>

                    <div class="form-group" id="groupKota" style="display: none;">
                        <label for="kota_id">Pilih Kota <span class="text-danger">*</span></label>
                        <select name="kota_id" id="kota_id" class="form-control">
                            <option value="">-- Pilih Kota --</option>
                            @foreach ($regencies as $regency)
                                <option value="{{ $regency->id }}">{{ $regency->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="groupPos" style="display: none;">
                        <label for="pos_id">Pilih Pos/Stasiun <span class="text-danger">*</span></label>
                        <select name="pos_id" id="pos_id" class="form-control">
                            <option value="">-- Pilih Pos/Stasiun --</option>
                            @foreach ($stations as $station)
                                <option value="{{ $station->id }}">
                                    {{ $station->station_name }} — {{ $station->village->district->regency->name ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bulan_mulai">Bulan Mulai <span class="text-danger">*</span></label>
                        <select name="bulan_mulai" id="bulan_mulai" class="form-control" required>
                            <option value="">-- Pilih Bulan Mulai --</option>
                        </select>
                        <small class="form-text text-muted">Pilih bulan awal untuk rentang data yang akan dicetak</small>
                    </div>

                    <div class="form-group">
                        <label for="bulan_akhir">Bulan Akhir <span class="text-danger">*</span></label>
                        <select name="bulan_akhir" id="bulan_akhir" class="form-control" required>
                            <option value="">-- Pilih Bulan Akhir --</option>
                        </select>
                        <small class="form-text text-muted">Pilih bulan akhir untuk rentang data yang akan dicetak</small>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        <small>Pastikan semua field telah diisi dengan benar sebelum mencetak data.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i> Ekspor PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
            return found ? found.avg_rain : null;
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

    // ========================
    // 🔹 MODAL CETAK DATA - JavaScript
    // ========================
    const kategoriCetak = document.getElementById('kategori_cetak');
    const groupKota = document.getElementById('groupKota');
    const groupPos = document.getElementById('groupPos');
    const kotaId = document.getElementById('kota_id');
    const posId = document.getElementById('pos_id');
    const formCetakData = document.getElementById('formCetakData');
    const bulanMulai = document.getElementById('bulan_mulai');
    const bulanAkhir = document.getElementById('bulan_akhir');

    // Fungsi untuk memuat bulan yang tersedia
    function loadAvailableMonths() {
        const kategori = kategoriCetak.value;
        const kotaIdVal = kotaId.value;
        const posIdVal = posId.value;
        
        if (!kategori) {
            bulanMulai.innerHTML = '<option value="">-- Pilih Bulan Mulai --</option>';
            bulanAkhir.innerHTML = '<option value="">-- Pilih Bulan Akhir --</option>';
            return;
        }
        
        // Tampilkan loading
        bulanMulai.innerHTML = '<option value="">Memuat data...</option>';
        bulanAkhir.innerHTML = '<option value="">Memuat data...</option>';
        bulanMulai.disabled = true;
        bulanAkhir.disabled = true;
        
        // Siapkan parameter
        const params = new URLSearchParams({ kategori: kategori });
        if (kategori === 'kota' && kotaIdVal) {
            params.append('kota_id', kotaIdVal);
        } else if (kategori === 'pos' && posIdVal) {
            params.append('pos_id', posIdVal);
        }
        
        // Panggil API
        fetch('{{ route("data.available-months") }}?' + params.toString())
            .then(response => response.json())
            .then(data => {
                if (data.success && data.months && data.months.length > 0) {
                    // Fungsi untuk format bulan
                    const formatMonth = (monthStr) => {
                        const [year, month] = monthStr.split('-');
                        const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        return monthNames[parseInt(month) - 1] + ' ' + year;
                    };
                    
                    // Isi dropdown bulan mulai
                    bulanMulai.innerHTML = '<option value="">-- Pilih Bulan Mulai --</option>';
                    data.months.forEach(month => {
                        const option = document.createElement('option');
                        option.value = month;
                        option.textContent = formatMonth(month);
                        bulanMulai.appendChild(option);
                    });
                    
                    // Isi dropdown bulan akhir
                    bulanAkhir.innerHTML = '<option value="">-- Pilih Bulan Akhir --</option>';
                    data.months.forEach(month => {
                        const option = document.createElement('option');
                        option.value = month;
                        option.textContent = formatMonth(month);
                        bulanAkhir.appendChild(option);
                    });
                    
                    // Set default jika ada min dan max
                    if (data.min_month) {
                        bulanMulai.value = data.min_month;
                    }
                    if (data.max_month) {
                        bulanAkhir.value = data.max_month;
                    }
                } else {
                    bulanMulai.innerHTML = '<option value="">Tidak ada data tersedia</option>';
                    bulanAkhir.innerHTML = '<option value="">Tidak ada data tersedia</option>';
                }
                bulanMulai.disabled = false;
                bulanAkhir.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                bulanMulai.innerHTML = '<option value="">Error memuat data</option>';
                bulanAkhir.innerHTML = '<option value="">Error memuat data</option>';
                bulanMulai.disabled = false;
                bulanAkhir.disabled = false;
            });
    }

    // Toggle dropdown berdasarkan kategori
    if (kategoriCetak) {
        kategoriCetak.addEventListener('change', function() {
            const kategori = this.value;
            
            // Reset dropdown
            kotaId.value = '';
            posId.value = '';
            kotaId.removeAttribute('required');
            posId.removeAttribute('required');
            bulanMulai.innerHTML = '<option value="">-- Pilih Bulan Mulai --</option>';
            bulanAkhir.innerHTML = '<option value="">-- Pilih Bulan Akhir --</option>';
            
            if (kategori === 'kota') {
                groupKota.style.display = 'block';
                groupPos.style.display = 'none';
                kotaId.setAttribute('required', 'required');
            } else if (kategori === 'pos') {
                groupKota.style.display = 'none';
                groupPos.style.display = 'block';
                posId.setAttribute('required', 'required');
            } else {
                groupKota.style.display = 'none';
                groupPos.style.display = 'none';
            }
        });
    }

    // Load bulan saat kota/pos dipilih
    if (kotaId) {
        kotaId.addEventListener('change', function() {
            if (kategoriCetak.value === 'kota' && this.value) {
                loadAvailableMonths();
            }
        });
    }

    if (posId) {
        posId.addEventListener('change', function() {
            if (kategoriCetak.value === 'pos' && this.value) {
                loadAvailableMonths();
            }
        });
    }

    // Validasi form sebelum submit
    if (formCetakData) {
        formCetakData.addEventListener('submit', function(e) {
            const kategori = kategoriCetak.value;
            const bulanMulaiVal = bulanMulai.value;
            const bulanAkhirVal = bulanAkhir.value;
            
            // Validasi kategori
            if (!kategori) {
                e.preventDefault();
                alert('Silakan pilih kategori cetak terlebih dahulu!');
                kategoriCetak.focus();
                return false;
            }
            
            // Validasi dropdown berdasarkan kategori
            if (kategori === 'kota' && !kotaId.value) {
                e.preventDefault();
                alert('Silakan pilih Kota terlebih dahulu!');
                kotaId.focus();
                return false;
            }
            
            if (kategori === 'pos' && !posId.value) {
                e.preventDefault();
                alert('Silakan pilih Pos/Stasiun terlebih dahulu!');
                posId.focus();
                return false;
            }
            
            // Validasi bulan
            if (!bulanMulaiVal || !bulanAkhirVal) {
                e.preventDefault();
                alert('Silakan pilih Bulan Mulai dan Bulan Akhir!');
                return false;
            }
            
            // Validasi bulan akhir harus >= bulan mulai
            if (bulanAkhirVal < bulanMulaiVal) {
                e.preventDefault();
                alert('Bulan Akhir harus lebih besar atau sama dengan Bulan Mulai!');
                bulanAkhir.focus();
                return false;
            }
        });
    }

    // Reset form saat modal ditutup
    $('#modalCetakData').on('hidden.bs.modal', function () {
        formCetakData.reset();
        groupKota.style.display = 'none';
        groupPos.style.display = 'none';
        kotaId.removeAttribute('required');
        posId.removeAttribute('required');
        bulanMulai.innerHTML = '<option value="">-- Pilih Bulan Mulai --</option>';
        bulanAkhir.innerHTML = '<option value="">-- Pilih Bulan Akhir --</option>';
    });
});
</script>
@endsection
