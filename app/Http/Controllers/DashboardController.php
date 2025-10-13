<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RainfallData;
use App\Models\Station;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedRegency = $request->get('regency_id', 'all');

        // Ambil semua kota
        $regencies = DB::table('regencies')->orderBy('name')->get();

        // Ambil tanggal terbaru dari data
        $latestDate = DB::table('rainfall_data')->max('date');
        if (!$latestDate) {
            // Jika belum ada data, hindari error
            return view('dashboard.dashboard', [
                'regencies' => $regencies,
                'selectedRegency' => $selectedRegency,
                'chartData' => collect(),
                'stats' => [
                    'total_rainfall' => 0,
                    'total_rain_days' => 0,
                    'active_stations' => DB::table('stations')->count(),
                    'total_regencies' => DB::table('regencies')->count(),
                ],
            ]);
        }

        // Hitung 12 bulan terakhir dari data
        $latestMonth = Carbon::parse($latestDate);
        $startDate = $latestMonth->copy()->subMonths(11)->startOfMonth();
        $endDate   = $latestMonth->copy()->endOfMonth();

        // === 📊 Grafik Curah Hujan per Kota untuk 12 Bulan Terakhir ===
        $chartData = DB::table('rainfall_data')
            ->join('stations', 'rainfall_data.station_id', '=', 'stations.id')
            ->join('villages', 'stations.village_id', '=', 'villages.id')
            ->join('districts', 'villages.district_id', '=', 'districts.id')
            ->join('regencies', 'districts.regency_id', '=', 'regencies.id')
            ->selectRaw('
                regencies.name as regency_name,
                DATE_FORMAT(rainfall_data.date, "%Y-%m") as month,
                AVG(rainfall_data.rainfall_amount) as avg_rain
            ')
            ->when($selectedRegency !== 'all', function ($query) use ($selectedRegency) {
                $query->where('regencies.id', $selectedRegency);
            })
            ->whereBetween('rainfall_data.date', [$startDate, $endDate])
            ->groupBy('regencies.name', DB::raw('DATE_FORMAT(rainfall_data.date, "%Y-%m")'))
            ->orderBy(DB::raw('MIN(rainfall_data.date)'), 'asc')
            ->get();

        // === 📈 Statistik ringkas (bulan sekarang realtime) ===
        $now = Carbon::now();
        $thisMonthData = RainfallData::whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->get();

        $stats = [
            'total_rainfall' => $thisMonthData->sum('rainfall_amount'),
            'total_rain_days' => $thisMonthData->sum('rain_days'),
            'active_stations' => DB::table('stations')->count(),
            'total_regencies' => DB::table('regencies')->count(),
        ];

        return view('dashboard.dashboard', compact(
            'regencies',
            'selectedRegency',
            'chartData',
            'stats'
        ));
    }
}
