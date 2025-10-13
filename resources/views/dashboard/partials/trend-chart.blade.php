<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            📊 Tren Curah Hujan 12 Bulan Terakhir per Kota
        </h6>

        <form action="{{ route('dashboard') }}" method="GET" class="form-inline">
            <select name="regency_id" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="all">🌍 Semua Kota</option>
                @foreach($regencies as $regency)
                    <option value="{{ $regency->id }}" {{ $selectedRegency == $regency->id ? 'selected' : '' }}>
                        {{ $regency->name }}
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
    const rawData = @json($chartData);

    if (!rawData.length) {
        ctx.font = '16px sans-serif';
        ctx.fillText('Tidak ada data curah hujan tersedia.', 100, 60);
        return;
    }

    // Kelompokkan berdasarkan kota (regency)
    const grouped = {};
    rawData.forEach(item => {
        if (!grouped[item.regency_name]) grouped[item.regency_name] = [];
        grouped[item.regency_name].push({
            month: item.month,
            avg_rain: item.avg_rain
        });
    });

    // Ambil label bulan (urut berdasarkan waktu)
    const months = [...new Set(rawData.map(d => d.month))].sort();

    // Format bulan agar lebih enak dibaca
    const formattedMonths = months.map(m => {
        const [year, month] = m.split('-');
        const date = new Date(year, month - 1);
        return date.toLocaleString('id-ID', { month: 'short', year: 'numeric' });
    });

    // Palet warna lembut (lebih dari cukup untuk banyak kota)
    const colors = [
        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
        '#858796', '#20c9a6', '#fd7e14', '#6f42c1', '#17a2b8'
    ];

    // Dataset per kota
    const datasets = Object.entries(grouped).map(([regency, data], i) => ({
        label: regency,
        data: months.map(m => {
            const found = data.find(d => d.month === m);
            return found ? found.avg_rain : 0;
        }),
        borderColor: colors[i % colors.length],
        backgroundColor: colors[i % colors.length] + '33',
        tension: 0.3,
        fill: true,
        borderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
    }));

    // Inisialisasi chart
    new Chart(ctx, {
        type: 'line',
        data: { labels: formattedMonths, datasets },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.formattedValue} mm`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Curah Hujan (mm)' }
                },
                x: {
                    title: { display: true, text: 'Bulan (Tahun)' }
                }
            }
        }
    });
});
</script>
