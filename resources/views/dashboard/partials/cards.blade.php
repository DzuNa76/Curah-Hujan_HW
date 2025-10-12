<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Curah Hujan Bulan Ini</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_rainfall'], 2) }} mm</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Hari Hujan Bulan Ini</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_rain_days'] }} Hari</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Stasiun Aktif</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_stations'] }}</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Kabupaten Terpantau</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_regencies'] }}</div>
            </div>
        </div>
    </div>
</div>
