<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Regency;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function index()
    {
        $districts = District::with('regency')->get();
        return view('districts.index', compact('districts'));
    }

    public function create()
    {
        $regencies = Regency::all();
        return view('districts.create', compact('regencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'regency_id' => 'required|exists:regencies,id'
        ]);

        District::create($request->only('name', 'regency_id'));
        return redirect()->route('districts.index')->with('success', 'Kecamatan berhasil ditambahkan!');
    }

    public function edit(District $district)
    {
        $regencies = Regency::all();
        return view('districts.edit', compact('district', 'regencies'));
    }

    public function update(Request $request, District $district)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'regency_id' => 'required|exists:regencies,id'
        ]);

        $district->update($request->only('name', 'regency_id'));
        return redirect()->route('districts.index')->with('success', 'Kecamatan berhasil diperbarui!');
    }

    public function destroy(District $district)
    {
        $district->delete();
        return redirect()->route('districts.index')->with('success', 'Kecamatan berhasil dihapus!');
    }
}
