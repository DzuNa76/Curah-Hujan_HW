<?php

namespace App\Http\Controllers;

use App\Models\RainfallData;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RainfallDataController extends Controller
{
    public function index()
    {
        $rainfallData = RainfallData::all();
        return view('data.index', compact('rainfallData'));
    }

    public function create()
    {
        return view('data.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'monthYear' => 'required|date_format:Y-m',
            'rainfall_amount' => 'required|numeric',
            'rain_days' => 'required|integer',
        ]);

        // konversi input monthYear jadi format Des-2024
        $date = Carbon::createFromFormat('Y-m', $request->monthYear);
        $id = $date->translatedFormat('M-Y'); // Des-2024

        RainfallData::create([
            'id' => $id,
            'date' => $date->startOfMonth(), // simpan tanggal awal bulan
            'rainfall_amount' => $request->rainfall_amount,
            'rain_days' => $request->rain_days,
        ]);

        return redirect()->route('rainfall.index')->with('success', 'Data berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $rainfall = RainfallData::findOrFail($id);
        return view('data.edit', compact('rainfall'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'monthYear' => 'required|date_format:Y-m',
            'rainfall_amount' => 'required|numeric',
            'rain_days' => 'required|integer',
        ]);

        $date = Carbon::createFromFormat('Y-m', $request->monthYear);
        $newId = $date->translatedFormat('M-Y');

        $rainfall = RainfallData::findOrFail($id);
        $rainfall->update([
            'id' => $newId,
            'date' => $date->startOfMonth(),
            'rainfall_amount' => $request->rainfall_amount,
            'rain_days' => $request->rain_days,
        ]);

        return redirect()->route('rainfall.index')->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $rainfall = RainfallData::findOrFail($id);
        $rainfall->delete();

        return redirect()->route('rainfall.index')->with('success', 'Data berhasil dihapus!');
    }
}
