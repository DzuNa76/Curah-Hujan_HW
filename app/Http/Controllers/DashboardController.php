<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RainfallData;
use App\Models\Station;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $stations = Station::with('village.district.regency')->get();
        $selectedStation = $request->get('station_id');

        // Base query
        $baseQuery = RainfallData::with('station.village.district.regency');
        if ($selectedStation && $selectedStation !== 'all') {
            $baseQuery->where('station_id', $selectedStation);
        }

        // === 📊 Grafik 12 bulan terakhir (terbaru) ===
        $chartData = DB::table('rainfall_data')
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, AVG(rainfall_amount) as avg_rain')
            ->when($selectedStation && $selectedStation !== 'all', function ($query) use ($selectedStation) {
                $query->where('station_id', $selectedStation);
            })
            ->groupBy(DB::raw('DATE_FORMAT(date, "%Y-%m")'))
            ->orderByDesc(DB::raw('MIN(date)'))
            ->limit(12)
            ->get()
            ->sortBy('month') // urutkan dari Jan -> Des
            ->values();

        // === 📅 Data tabel (tahun berjalan, urut Jan - Des) ===
        $currentYear = Carbon::now()->year;
        $recentData = (clone $baseQuery)
            ->whereYear('date', $currentYear)
            ->orderBy('date', 'asc') // urutkan dari Januari ke Desember
            ->get();

        // === 📈 Statistik ringkas ===
        $currentMonth = Carbon::now()->format('Y-m');
        $thisMonthData = (clone $baseQuery)
            ->where(DB::raw('DATE_FORMAT(date, "%Y-%m")'), $currentMonth)
            ->get();

        $stats = [
            'total_rainfall' => $thisMonthData->sum('rainfall_amount'),
            'total_rain_days' => $thisMonthData->sum('rain_days'),
            'active_stations' => Station::count(),
            'total_regencies' => DB::table('regencies')->count(),
        ];

        return view('dashboard.dashboard', compact(
            'stations',
            'selectedStation',
            'chartData',
            'recentData',
            'stats'
        ));
    }
}
