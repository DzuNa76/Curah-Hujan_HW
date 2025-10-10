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
        $regencies = Regency::all();
        return view('districts.index', compact('districts', 'regencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'regency_id' => 'required|exists:regencies,id'
        ]);
        District::create($request->only('name', 'regency_id'));
        return back()->with('success', 'Kecamatan berhasil ditambahkan.');
    }

    public function destroy(District $district)
    {
        $district->delete();
        return back()->with('success', 'Kecamatan dihapus.');
    }
}
