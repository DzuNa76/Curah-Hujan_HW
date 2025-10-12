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
    public function index()
    {
        // Ambil data dengan relasi stasiun dan wilayah
        $rainfallData = RainfallData::with('station.village.district.regency')
            ->orderBy('date', 'asc')
            ->get();

        return view('data.index', compact('rainfallData'));
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
