<?php

namespace App\Http\Controllers;

use App\Models\RainfallData;
use App\Models\Station;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RainfallDataController extends Controller
{
    /**
     * Tampilkan semua data curah hujan dengan relasi stasiun
     */
    public function index(Request $request)
    {
        $stations = Station::with('village.district.regency')->get();
        $selectedStation = $request->get('station_id', 'all');
        $selectedYear = $request->get('year', Carbon::now()->year);

        // --- Query dasar ---
        $rainfallQuery = RainfallData::with('station.village.district.regency')
            ->whereYear('date', $selectedYear);

        if ($selectedStation !== 'all') {
            $rainfallQuery->where('station_id', $selectedStation);
        }

        $rainfallData = $rainfallQuery->orderBy('date', 'asc')->get();

        // --- Data grafik (rata-rata curah hujan per bulan) ---
        $chartData = RainfallData::selectRaw('MONTH(date) as month, AVG(rainfall_amount) as avg_rain')
            ->whereYear('date', $selectedYear)
            ->when($selectedStation !== 'all', function ($q) use ($selectedStation) {
                $q->where('station_id', $selectedStation);
            })
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // --- Tahun tersedia di database ---
        $availableYears = RainfallData::selectRaw('DISTINCT YEAR(date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('data.index', compact(
            'rainfallData',
            'stations',
            'selectedStation',
            'selectedYear',
            'chartData',
            'availableYears'
        ));
    }

    /**
     * Form tambah data baru
     */
    public function create()
    {
        $stations = \App\Models\Station::all();
        return view('data.create', compact('stations'));
    }

    /**
     * Simpan data baru
     */
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

    /**
     * Form edit data
     */
    public function edit($station_id, $id)
    {
        $rainfall = RainfallData::where('station_id', $station_id)
                                ->where('id', $id)
                                ->firstOrFail();
        $stations = \App\Models\Station::all();

        return view('data.edit', compact('rainfall', 'stations'));
    }

    /**
     * Update data curah hujan
     */
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

    /**
     * Hapus data
     */
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
