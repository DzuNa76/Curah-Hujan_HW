<?php

namespace App\Http\Controllers;

use App\Models\Regency;
use Illuminate\Http\Request;

class RegencyController extends Controller
{
    public function index()
    {
        $regencies = Regency::all();
        return view('regencies.index', compact('regencies'));
    }

    public function create()
    {
        return view('regencies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:regencies,name'
        ]);

        Regency::create($request->only('name'));
        return redirect()->route('regencies.index')->with('success', 'Kabupaten berhasil ditambahkan!');
    }

    public function edit(Regency $regency)
    {
        return view('regencies.edit', compact('regency'));
    }

    public function update(Request $request, Regency $regency)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:regencies,name,' . $regency->id
        ]);

        $regency->update($request->only('name'));
        return redirect()->route('regencies.index')->with('success', 'Kabupaten berhasil diperbarui!');
    }

    public function destroy(Regency $regency)
    {
        // 🔍 Cek apakah masih ada kecamatan
        if ($regency->districts()->exists()) {
            return redirect()->route('regencies.index')
                ->with('error', 'Kabupaten tidak dapat dihapus karena masih memiliki data kecamatan terkait.');
        }

        // 🔍 Cek apakah masih ada desa (lewat kecamatan)
        if ($regency->districts()->whereHas('villages')->exists()) {
            return redirect()->route('regencies.index')
                ->with('error', 'Kabupaten tidak dapat dihapus karena masih memiliki data desa terkait.');
        }

        // 🔍 Cek apakah masih ada stasiun
        if ($regency->districts()->whereHas('villages.stations')->exists()) {
            return redirect()->route('regencies.index')
                ->with('error', 'Kabupaten tidak dapat dihapus karena masih memiliki data stasiun pengamatan.');
        }

        // 🔍 Cek apakah masih ada data curah hujan
        if ($regency->districts()->whereHas('villages.stations.rainfallData')->exists()) {
            return redirect()->route('regencies.index')
                ->with('error', 'Kabupaten tidak dapat dihapus karena masih memiliki data curah hujan.');
        }

        // ✅ Jika aman
        $regency->delete();

        return redirect()->route('regencies.index')
            ->with('success', 'Kabupaten berhasil dihapus!');
    }

}
