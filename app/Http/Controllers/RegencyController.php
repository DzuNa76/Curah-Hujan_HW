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
        $regency->delete();
        return redirect()->route('regencies.index')->with('success', 'Kabupaten berhasil dihapus!');
    }
}
