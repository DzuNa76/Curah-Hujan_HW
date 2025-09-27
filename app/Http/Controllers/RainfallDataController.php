<?php

namespace App\Http\Controllers;

use App\Models\RainfallData;
use Illuminate\Http\Request;

class RainfallDataController extends Controller
{
    // ✅ Tampilkan semua data
    public function index()
    {
        $data = RainfallData::orderBy('date', 'desc')->get();
        return view('data.index', compact('data')); 
    }

    // ✅ Form input data baru
    public function create()
    {
        return view('data.create'); 
    }

    // ✅ Simpan data baru
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'rainfall_amount' => 'required|numeric',
            'rain_days' => 'required|integer',
        ]);

        RainfallData::create($request->all());

        return redirect()->route('rainfall.index')->with('success', 'Data curah hujan berhasil ditambahkan');
    }

    // ✅ Form edit data
    public function edit($id)
    {
        $rainfall = RainfallData::findOrFail($id);
        return view('data.edit', compact('rainfall')); 
    }

    // ✅ Update data
    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'rainfall_amount' => 'required|numeric',
            'rain_days' => 'required|integer',
        ]);

        $rainfall = RainfallData::findOrFail($id);
        $rainfall->update($request->all());

        return redirect()->route('rainfall.index')->with('success', 'Data curah hujan berhasil diperbarui');
    }

    // ✅ Hapus data
    public function destroy($id)
    {
        $rainfall = RainfallData::findOrFail($id);
        $rainfall->delete();

        return redirect()->route('rainfall.index')->with('success', 'Data curah hujan berhasil dihapus');
    }
}
