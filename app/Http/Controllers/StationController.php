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
        $villages = Village::with('district.regency')->get();
        return view('stations.index', compact('stations', 'villages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'station_name' => 'required|string',
            'village_id' => 'required|exists:villages,id'
        ]);
        Station::create($request->only('station_name', 'village_id'));
        return back()->with('success', 'Stasiun berhasil ditambahkan.');
    }

    public function destroy(Station $station)
    {
        $station->delete();
        return back()->with('success', 'Stasiun dihapus.');
    }
}
