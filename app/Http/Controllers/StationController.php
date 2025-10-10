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
        $villages = Village::with('district.regency')->get();
        return view('stations.create', compact('villages'));
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

    public function edit(Station $station)
    {
        $villages = Village::with('district.regency')->get();
        return view('stations.edit', compact('station', 'villages'));
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
        $station->delete();
        return redirect()->route('stations.index')->with('success', 'Stasiun berhasil dihapus!');
    }
}
