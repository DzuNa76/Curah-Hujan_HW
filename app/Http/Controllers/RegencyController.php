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

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:regencies,name']);
        Regency::create($request->only('name'));
        return back()->with('success', 'Kabupaten berhasil ditambahkan.');
    }

    public function destroy(Regency $regency)
    {
        $regency->delete();
        return back()->with('success', 'Kabupaten dihapus.');
    }
}
