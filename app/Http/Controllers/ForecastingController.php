<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RainfallData;
use Carbon\Carbon;

class ForecastingController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data historis
        $data = RainfallData::orderBy('date')->get(['date', 'rainfall_amount']);

        if ($data->isEmpty()) {
            return view('forecasting.index', [
                'message' => 'Belum ada data curah hujan.',
                'alpha' => 0.5, 'beta' => 0.3, 'gamma' => 0.2,
                'labels' => [], 'values' => [],
            ]);
        }

        // Ambil parameter input
        $alpha = (float) $request->input('alpha', 0.5);
        $beta  = (float) $request->input('beta', 0.3);
        $gamma = (float) $request->input('gamma', 0.2);
        $start = $request->input('start');
        $end   = $request->input('end');

        // Validasi nilai parameter
        if ($alpha <= 0 || $alpha >= 1 || $beta <= 0 || $beta >= 1 || $gamma <= 0 || $gamma >= 1) {
            return back()->with('error', 'Nilai alpha, beta, dan gamma harus antara 0 dan 1.');
        }

        // Filter data berdasarkan periode
        $filtered = $data;
        if ($start && $end) {
            $filtered = $data->filter(fn($item) => $item->date >= $start && $item->date <= $end);
        }

        $dates = $filtered->pluck('date')->map(fn($d) => Carbon::parse($d)->translatedFormat('M Y'))->toArray();
        $values = $filtered->pluck('rainfall_amount')->map(fn($v) => (float) $v)->toArray();

        $n = count($values);
        $m = 12; // asumsikan musiman 12 bulan

        if ($n < $m) {
            return view('forecasting.index', [
                'labels' => $dates,
                'message' => 'Data tidak cukup untuk melakukan peramalan (minimal 12 bulan).',
                'alpha' => $alpha, 'beta' => $beta, 'gamma' => $gamma,
                'start' => $start, 'end' => $end,
            ]);
        }

        // --- Inisialisasi variabel ---
        $L = []; $T = []; $S = []; $F = []; $errors = [];

        // Level dan tren awal
        $L[0] = $values[0];
        $T[0] = $values[1] - $values[0];

        // Seasonal awal (diasumsikan 0)
        for ($i = 0; $i < $m; $i++) {
            $S[$i] = 0;
        }

        // Forecast pertama tidak ada (belum bisa dihitung)
        $F[0] = null;
        $errors[0] = null;

        // --- Iterasi Holt–Winters Additive ---
        for ($t = 1; $t < $n; $t++) {
            $prevSeason = $S[$t - $m] ?? 0;

            $L[$t] = $alpha * ($values[$t] - $prevSeason) + (1 - $alpha) * ($L[$t - 1] + $T[$t - 1]);
            $T[$t] = $beta * ($L[$t] - $L[$t - 1]) + (1 - $beta) * $T[$t - 1];
            $S[$t] = $gamma * ($values[$t] - $L[$t]) + (1 - $gamma) * $prevSeason;

            $F[$t] = $L[$t - 1] + $T[$t - 1] + $prevSeason;
            $errors[$t] = $values[$t] - $F[$t];
        }

        // --- Evaluasi ---
        $validErrors = array_filter($errors, fn($e) => !is_null($e));
        $absErrors = array_map('abs', $validErrors);
        $squaredErrors = array_map(fn($e) => $e ** 2, $validErrors);

        $mae = count($absErrors) ? round(array_sum($absErrors) / count($absErrors), 3) : 0;
        $rmse = count($squaredErrors) ? round(sqrt(array_sum($squaredErrors) / count($squaredErrors)), 3) : 0;

        // --- Prediksi 12 bulan ke depan ---
        $forecastFuture = [];
        $lastL = end($L);
        $lastT = end($T);
        $lastIndex = count($L) - 1;

        for ($k = 1; $k <= 12; $k++) {
            $nextMonth = Carbon::parse($filtered->last()->date)->addMonths($k)->translatedFormat('M Y');
            $seasonal = $S[$lastIndex - $m + $k] ?? 0;
            $forecastFuture[] = [
                'month' => $nextMonth,
                'forecast' => round($lastL + $k * $lastT + $seasonal, 3),
            ];
        }

        // --- Kirim ke view ---
        return view('forecasting.index', [
            'labels' => $dates,
            'values' => $values,
            'L' => $L,
            'T' => $T,
            'S' => $S,
            'F' => $F,
            'errors' => $errors,
            'forecast' => $forecastFuture,
            'mae' => $mae,
            'rmse' => $rmse,
            'alpha' => $alpha,
            'beta' => $beta,
            'gamma' => $gamma,
            'start' => $start,
            'end' => $end,
            'allDates' => $data->pluck('date')->map(fn($d) => Carbon::parse($d)->translatedFormat('M Y'))->toArray(),
            'message' => null,
        ]);
    }
}
