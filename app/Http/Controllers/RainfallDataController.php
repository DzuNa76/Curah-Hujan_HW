<?php

namespace App\Http\Controllers;

use App\Models\RainfallData;
use App\Models\Station;
use App\Models\Regency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RainfallDataController extends Controller
{
    // Tampilkan semua data curah hujan dengan relasi stasiun
    public function index(Request $request)
    {
        // Ambil filter
        $selectedStation = $request->get('station_id', 'all');
        $selectedRegency = $request->get('regency_id', 'all');
        $selectedYear = $request->get('year');

        // Ambil tahun terbaru jika tidak ada filter
        $latestYear = RainfallData::max(DB::raw('YEAR(date)')) ?? Carbon::now()->year;
        $selectedYear = $selectedYear ?? $latestYear;

        // Ambil semua daftar stasiun & kota
        $stations = Station::with('village.district.regency')->orderBy('station_name')->get();
        $regencies = Regency::orderBy('name')->get();

        // --- Query utama data tabel ---
        $rainfallQuery = RainfallData::with('station.village.district.regency')
            ->whereYear('date', $selectedYear);

        if ($selectedStation !== 'all') {
            $rainfallQuery->where('station_id', $selectedStation);
        } elseif ($selectedRegency !== 'all') {
            $rainfallQuery->whereHas('station.village.district.regency', function ($q) use ($selectedRegency) {
                $q->where('id', $selectedRegency);
            });
        }

        $rainfallData = $rainfallQuery->orderBy('date', 'asc')->get();

        // --- Data grafik (rata-rata curah hujan per bulan per kota atau stasiun) ---
        $chartDataQuery = DB::table('rainfall_data')
            ->join('stations', 'rainfall_data.station_id', '=', 'stations.id')
            ->join('villages', 'stations.village_id', '=', 'villages.id')
            ->join('districts', 'villages.district_id', '=', 'districts.id')
            ->join('regencies', 'districts.regency_id', '=', 'regencies.id')
            ->selectRaw('
                DATE_FORMAT(rainfall_data.date, "%Y-%m") as month,
                AVG(rainfall_data.rainfall_amount) as avg_rain,
                regencies.name as regency_name,
                stations.station_name as station_name
            ')
            ->whereYear('rainfall_data.date', $selectedYear);

        if ($selectedStation !== 'all') {
            $chartDataQuery->where('stations.id', $selectedStation);
        } elseif ($selectedRegency !== 'all') {
            $chartDataQuery->where('regencies.id', $selectedRegency);
        }

        $chartData = $chartDataQuery
            ->groupBy('month', 'regency_name', 'station_name')
            ->orderBy('month', 'asc')
            ->get();

        // --- Tahun tersedia di database ---
        $availableYears = RainfallData::selectRaw('DISTINCT YEAR(date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Pastikan semua variabel dikirim
        return view('data.index', compact(
            'rainfallData',
            'stations',
            'regencies',
            'selectedStation',
            'selectedRegency',
            'selectedYear',
            'chartData',
            'availableYears'
        ));
    }
    
    // Form tambah data baru
    public function create()
    {
        $stations = \App\Models\Station::all();
        return view('data.create', compact('stations'));
    }

    // Simpan data baru
    public function store(Request $request)
    {
        $request->validate([
            'station_id' => 'required|exists:stations,id',
            'monthYear' => 'required|date_format:Y-m',
            'rainfall_amount' => 'required|numeric',
            'rain_days' => 'required|integer',
        ]);

        $date = \Carbon\Carbon::createFromFormat('Y-m', $request->monthYear);
        $id = $date->translatedFormat('M-Y');

        RainfallData::create([
            'station_id' => $request->station_id,
            'id' => $id,
            'date' => $date->startOfMonth(),
            'rainfall_amount' => $request->rainfall_amount,
            'rain_days' => $request->rain_days,
        ]);

        return redirect()->route('rainfall.index')->with('success', 'Data berhasil ditambahkan!');
    }

    // Form edit data
    public function edit($station_id, $id)
    {
        $rainfall = RainfallData::where('station_id', $station_id)
                                ->where('id', $id)
                                ->firstOrFail();
        $stations = \App\Models\Station::all();

        return view('data.edit', compact('rainfall', 'stations'));
    }

    // Update data curah hujan
    public function update(Request $request, $station_id, $id)
    {
        $request->validate([
            'station_id' => 'required|exists:stations,id',
            'monthYear' => 'required|date_format:Y-m',
            'rainfall_amount' => 'required|numeric',
            'rain_days' => 'required|integer',
        ]);

        $date = \Carbon\Carbon::createFromFormat('Y-m', $request->monthYear);
        $newId = $date->translatedFormat('M-Y');

        $rainfall = RainfallData::where('station_id', $station_id)
                                ->where('id', $id)
                                ->firstOrFail();

        $rainfall->update([
            'station_id' => $request->station_id,
            'id' => $newId,
            'date' => $date->startOfMonth(),
            'rainfall_amount' => $request->rainfall_amount,
            'rain_days' => $request->rain_days,
        ]);

        return redirect()->route('rainfall.index')->with('success', 'Data berhasil diperbarui!');
    }

    // Hapus data
    public function destroy($station_id, $id)
    {
        $rainfall = RainfallData::where('station_id', $station_id)
                                ->where('id', $id)
                                ->firstOrFail();

        $rainfall->delete();

        return redirect()->route('rainfall.index')
            ->with('success', 'Data curah hujan berhasil dihapus!');
    }
}
