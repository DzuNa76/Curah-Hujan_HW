<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Models\District;
use Illuminate\Http\Request;

class VillageController extends Controller
{
    public function index()
    {
        $villages = Village::with('district.regency')->get();
        $districts = District::with('regency')->get();
        return view('villages.index', compact('villages', 'districts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'district_id' => 'required|exists:districts,id'
        ]);
        Village::create($request->only('name', 'district_id'));
        return back()->with('success', 'Desa berhasil ditambahkan.');
    }

    public function destroy(Village $village)
    {
        $village->delete();
        return back()->with('success', 'Desa dihapus.');
    }
}
