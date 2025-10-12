<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Tren Curah Hujan 12 Bulan Terakhir</h6>

        <form action="{{ route('dashboard') }}" method="GET" class="form-inline">
            <select name="station_id" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="all">🌍 Semua Stasiun</option>
                @foreach($stations as $station)
                    <option value="{{ $station->id }}" {{ $selectedStation == $station->id ? 'selected' : '' }}>
                        {{ $station->station_name }} — {{ $station->village->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body">
        <canvas id="rainfallChart" height="100"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('rainfallChart').getContext('2d');
    const chartData = {
        labels: {!! json_encode($chartData->pluck('month')) !!},
        datasets: [{
            label: 'Curah Hujan (mm)',
            data: {!! json_encode($chartData->pluck('avg_rain')) !!},
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.3,
            fill: true,
        }]
    };

    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'mm' } },
                x: { title: { display: true, text: 'Bulan' } }
            }
        }
    });
});
</script>
