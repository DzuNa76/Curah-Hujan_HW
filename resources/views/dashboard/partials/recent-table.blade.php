<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-info">Data Curah Hujan Terbaru</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Stasiun</th>
                        <th>Bulan</th>
                        <th>Curah Hujan (mm)</th>
                        <th>Hari Hujan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentData as $data)
                        <tr>
                            <td>{{ $data->station->station_name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($data->date)->translatedFormat('F Y') }}</td>
                            <td>{{ number_format($data->rainfall_amount, 2) }}</td>
                            <td>{{ $data->rain_days }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
