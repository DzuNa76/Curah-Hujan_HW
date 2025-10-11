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
        return view('villages.index', compact('villages'));
    }

    public function create()
    {
        $regencies = \App\Models\Regency::all();
        $districts = \App\Models\District::all();
        return view('villages.create', compact('regencies', 'districts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'district_id' => 'required|exists:districts,id'
        ]);

        Village::create($request->only('name', 'district_id'));
        return redirect()->route('villages.index')->with('success', 'Desa berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $village   = \App\Models\Village::findOrFail($id);
        $regencies = \App\Models\Regency::all();
        $districts = \App\Models\District::all();
        return view('villages.edit', compact('village', 'regencies', 'districts'));
    }

    public function update(Request $request, Village $village)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'district_id' => 'required|exists:districts,id'
        ]);

        $village->update($request->only('name', 'district_id'));
        return redirect()->route('villages.index')->with('success', 'Desa berhasil diperbarui!');
    }

    public function destroy(Village $village)
    {
        $village->delete();
        return redirect()->route('villages.index')->with('success', 'Desa berhasil dihapus!');
    }
}
