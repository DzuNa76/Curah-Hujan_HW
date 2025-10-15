<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Village;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function index()
    {
        $stations = Station::with('village.district.regency')->get();
        return view('stations.index', compact('stations'));
    }

    public function create()
    {
        $regencies = \App\Models\Regency::all();
        $districts = \App\Models\District::all();
        $villages  = \App\Models\Village::all();

        return view('stations.create', compact('regencies', 'districts', 'villages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'station_name' => 'required|string|max:100',
            'village_id' => 'required|exists:villages,id'
        ]);

        Station::create($request->only('station_name', 'village_id'));
        return redirect()->route('stations.index')->with('success', 'Stasiun berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $station   = \App\Models\Station::findOrFail($id);
        $regencies = \App\Models\Regency::all();
        $districts = \App\Models\District::all();
        $villages  = \App\Models\Village::all();

        return view('stations.edit', compact('station', 'regencies', 'districts', 'villages'));
    }

    public function update(Request $request, Station $station)
    {
        $request->validate([
            'station_name' => 'required|string|max:100',
            'village_id' => 'required|exists:villages,id'
        ]);

        $station->update($request->only('station_name', 'village_id'));
        return redirect()->route('stations.index')->with('success', 'Stasiun berhasil diperbarui!');
    }

    public function destroy(Station $station)
    {
        // 🔍 Cek apakah masih ada data curah hujan terkait stasiun ini
        if ($station->rainfallData()->exists()) {
            return redirect()->route('stations.index')
                ->with('error', 'Stasiun tidak dapat dihapus karena masih memiliki data curah hujan.');
        }

        // ✅ Jika aman
        $station->delete();

        return redirect()->route('stations.index')
            ->with('success', 'Stasiun berhasil dihapus!');
    }

    public function print(Station $station)
    {
        // Ambil seluruh data curah hujan terkait stasiun, urut per tanggal
        $rainfalls = $station->rainfallData()
            ->with('station.village.district.regency')
            ->orderBy('date', 'asc')
            ->get();

        return view('stations.print', [
            'station' => $station->load('village.district.regency'),
            'rainfalls' => $rainfalls,
        ]);
    }

}
