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
        // Ambil semua stasiun untuk dropdown
        $stations = Station::with('village.district.regency')->get();
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
            'rainfall_amount' => 'required|numeric|min:0',
            'rain_days' => 'required|integer|min:0',
        ]);

        // Konversi "YYYY-MM" jadi objek tanggal Carbon
        $date = Carbon::createFromFormat('Y-m', $request->monthYear);
        $id = $date->translatedFormat('M-Y'); // contoh: Des-2024

        RainfallData::create([
            'id' => $id,
            'station_id' => $request->station_id,
            'date' => $date->startOfMonth(),
            'rainfall_amount' => $request->rainfall_amount,
            'rain_days' => $request->rain_days,
        ]);

        return redirect()->route('rainfall.index')
            ->with('success', 'Data curah hujan berhasil ditambahkan!');
    }

    /**
     * Form edit data
     */
    public function edit($id)
    {
        $rainfall = RainfallData::findOrFail($id);
        $stations = Station::with('village.district.regency')->get();
        return view('data.edit', compact('rainfall', 'stations'));
    }

    /**
     * Update data curah hujan
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'station_id' => 'required|exists:stations,id',
            'monthYear' => 'required|date_format:Y-m',
            'rainfall_amount' => 'required|numeric|min:0',
            'rain_days' => 'required|integer|min:0',
        ]);

        $date = Carbon::createFromFormat('Y-m', $request->monthYear);
        $newId = $date->translatedFormat('M-Y');

        $rainfall = RainfallData::findOrFail($id);
        $rainfall->update([
            'id' => $newId,
            'station_id' => $request->station_id,
            'date' => $date->startOfMonth(),
            'rainfall_amount' => $request->rainfall_amount,
            'rain_days' => $request->rain_days,
        ]);

        return redirect()->route('rainfall.index')
            ->with('success', 'Data curah hujan berhasil diperbarui!');
    }

    /**
     * Hapus data
     */
    public function destroy($id)
    {
        $rainfall = RainfallData::findOrFail($id);
        $rainfall->delete();

        return redirect()->route('rainfall.index')
            ->with('success', 'Data curah hujan berhasil dihapus!');
    }
}
