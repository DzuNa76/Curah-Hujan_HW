<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RainfallData;
use Carbon\Carbon;

class ForecastingController extends Controller
{
    public function index()
    {
        $rainfalls = RainfallData::orderBy('date')->get();

        $months = $rainfalls
            ->map(fn($r) => $r->month_year)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $start = count($months) ? Carbon::parse($months[0] . '-01')->format('Y-m-d') : null;
        $end   = count($months) ? Carbon::parse(end($months) . '-01')->format('Y-m-d') : null;

        return view('forecasting.index', [
            'values'   => [],
            'labels'   => [],
            'alpha'    => 0.3,
            'beta'     => 0.2,
            'gamma'    => 0.3,
            'L'        => [],
            'T'        => [],
            'S'        => [],
            'F'        => [],
            'errors'   => [],
            'mae'      => 0,
            'rmse'     => 0,
            'mape'     => 0,
            'message'  => true,
            'allDates' => $months,
            'start'    => $start,
            'end'      => $end,
        ]);
    }

    public function process(Request $request)
{
    // Validasi input
    $request->validate([
        'alpha' => 'required|numeric|min:0|max:1',
        'beta'  => 'required|numeric|min:0|max:1',
        'gamma' => 'required|numeric|min:0|max:1',
        'start' => 'required|date',
        'end'   => 'required|date|after_or_equal:start',
    ]);

    $alpha = (float) $request->alpha;
    $beta  = (float) $request->beta;
    $gamma = (float) $request->gamma;

    $start = Carbon::parse($request->start)->startOfMonth();
    $end   = Carbon::parse($request->end)->endOfMonth();

    $rainfalls = RainfallData::whereBetween('date', [$start->toDateString(), $end->toDateString()])
        ->orderBy('date')
        ->get();

    $data = [];
    foreach ($rainfalls as $r) {
        if ($r->month_year) {
            $data[$r->month_year] = (float) $r->rainfall_amount;
        }
    }

    if (empty($data)) {
        return back()->with('error', 'Tidak ada data curah hujan pada rentang waktu yang dipilih.');
    }

    $values = array_values($data);
    $labels = array_keys($data);
    $m = 12; // panjang musim (12 bulan)
    $n = count($values);

    if ($n < 2 * $m) {
        return back()->with('error', "Dibutuhkan minimal " . (2 * $m) . " bulan data (2 musim). Data saat ini: " . $n . " bulan.");
    }

    // ========================================
    // INISIALISASI KOMPONEN (Bootstrap)
    // ========================================
    
    $seasons = intdiv($n, $m);
    $avg_month = array_fill(0, $m, 0.0);
    
    // 1) Hitung rata-rata tiap posisi bulan
    for ($i = 0; $i < $m; $i++) {
        $sum = 0.0;
        for ($s = 0; $s < $seasons; $s++) {
            $sum += $values[$s * $m + $i];
        }
        $avg_month[$i] = $sum / $seasons;
    }

    // 2) Overall mean
    $overall_mean = array_sum(array_slice($values, 0, $seasons * $m)) / ($seasons * $m);

    // 3) Seasonal index awal (DENGAN NORMALISASI)
    $S = array_fill(0, $m, 0.0);
    for ($i = 0; $i < $m; $i++) {
        $S[$i] = $avg_month[$i] - $overall_mean;
    }
    
    // Normalisasi: pastikan Σ S[i] = 0
    $S_sum = array_sum($S);
    $S_correction = $S_sum / $m;
    for ($i = 0; $i < $m; $i++) {
        $S[$i] -= $S_correction;
    }

    // 4) Level awal (L₀)
    $level0 = 0.0;
    for ($i = 0; $i < $m; $i++) {
        $level0 += ($values[$i] - $S[$i]);
    }
    $level0 /= $m;

    // 5) Trend awal (T₀)
    // Rumus: T₀ = (1/m²) × Σ[Y[m+i] - Y[i]]
    $trend0 = 0.0;
    for ($i = 0; $i < $m; $i++) {
        $trend0 += ($values[$m + $i] - $values[$i]);
    }
    $trend0 /= ($m * $m); // PENTING: dibagi m²

    // ========================================
    // SETUP ARRAYS
    // ========================================
    
    $L = array_fill(0, $n, null);
    $T = array_fill(0, $n, null);
    $F = array_fill(0, $n, null);
    $errors = array_fill(0, $n, null);
    
    // Set nilai awal
    $L[0] = $level0;
    $T[0] = $trend0;
    $F[0] = null; // tidak ada forecast untuk periode pertama
    $errors[0] = null;

    // Optional: tracking seasonal per waktu (untuk visualisasi)
    $S_history = [];
    $S_history[0] = $S;

    // ========================================
    // SMOOTHING (Pilih salah satu pendekatan)
    // ========================================
    
    // OPSI A: Mulai dari t=1 (lebih optimal, lebih banyak update)
    for ($t = 1; $t < $n; $t++) {
        // Forecast
        $F[$t] = $L[$t - 1] + $T[$t - 1] + $S[$t % $m];
        
        // Error
        $errors[$t] = $values[$t] - $F[$t];
        
        // Simpan seasonal lama sebelum update
        $S_old = $S[$t % $m];
        
        // Update Level
        $L[$t] = $alpha * ($values[$t] - $S_old) + (1 - $alpha) * ($L[$t - 1] + $T[$t - 1]);
        
        // Update Trend
        $T[$t] = $beta * ($L[$t] - $L[$t - 1]) + (1 - $beta) * $T[$t - 1];
        
        // Update Seasonal (circular)
        $S[$t % $m] = $gamma * ($values[$t] - $L[$t]) + (1 - $gamma) * $S_old;
        
        // Simpan snapshot seasonal (opsional)
        $S_history[$t] = $S;
    }

    /* OPSI B: Mulai dari t=m (lebih konservatif, tunggu 1 musim)
    // Untuk t < m, gunakan nilai inisialisasi
    for ($t = 1; $t < $m; $t++) {
        $L[$t] = $L[0];
        $T[$t] = $T[0];
        $F[$t] = $L[0] + $T[0] + $S[$t % $m];
        $errors[$t] = $values[$t] - $F[$t];
    }
    
    // Smoothing mulai dari t = m
    for ($t = $m; $t < $n; $t++) {
        $F[$t] = $L[$t - 1] + $T[$t - 1] + $S[($t - $m) % $m];
        $errors[$t] = $values[$t] - $F[$t];
        
        $S_old = $S[$t % $m];
        $L[$t] = $alpha * ($values[$t] - $S_old) + (1 - $alpha) * ($L[$t - 1] + $T[$t - 1]);
        $T[$t] = $beta * ($L[$t] - $L[$t - 1]) + (1 - $beta) * $T[$t - 1];
        $S[$t % $m] = $gamma * ($values[$t] - $L[$t]) + (1 - $gamma) * $S_old;
        
        $S_history[$t] = $S;
    }
    */

    // ========================================
    // FORECAST H LANGKAH KE DEPAN
    // ========================================
    
    $H = 12;
    $lastL = $L[$n - 1];
    $lastT = $T[$n - 1];
    
    for ($h = 1; $h <= $H; $h++) {
        $pos = ($n - 1 + $h) % $m;
        $forecast = $lastL + $h * $lastT + $S[$pos];
        
        $nextLabel = Carbon::parse($labels[$n - 1] . '-01')->addMonths($h)->format('Y-m');
        
        $labels[] = $nextLabel;
        $values[] = null;
        $L[] = $lastL;
        $T[] = $lastT;
        $F[] = $forecast;
        $errors[] = null;
    }

    // ========================================
    // EVALUASI AKURASI
    // ========================================
    
    // Skip periode awal untuk stabilisasi (opsional: 0 atau m)
    $skipPeriods = $m; // skip 1 musim pertama
    $validErrors = [];
    
    for ($t = $skipPeriods; $t < $n; $t++) {
        if (!is_null($errors[$t])) {
            $validErrors[] = $errors[$t];
        }
    }

    $mae = count($validErrors) > 0 
        ? array_sum(array_map('abs', $validErrors)) / count($validErrors) 
        : 0;
        
    $rmse = count($validErrors) > 0 
        ? sqrt(array_sum(array_map(fn($e) => $e * $e, $validErrors)) / count($validErrors)) 
        : 0;

    $mapeSum = 0;
    $mapeCount = 0;
    for ($t = $skipPeriods; $t < $n; $t++) {
        if (!is_null($errors[$t]) && $values[$t] != 0) {
            $mapeSum += abs($errors[$t] / $values[$t]);
            $mapeCount++;
        }
    }
    $mape = $mapeCount > 0 ? ($mapeSum / $mapeCount) * 100 : 0;

    // Ambil daftar tanggal unik untuk dropdown
    $allDates = RainfallData::orderBy('date')
        ->get()
        ->map(fn($r) => $r->month_year)
        ->filter()
        ->unique()
        ->values()
        ->toArray();

    return view('forecasting.index', [
        'values'      => $values,
        'labels'      => $labels,
        'alpha'       => $alpha,
        'beta'        => $beta,
        'gamma'       => $gamma,
        'L'           => $L,
        'T'           => $T,
        'S'           => $S, // seasonal index final
        'S_history'   => $S_history, // seasonal per waktu (opsional)
        'F'           => $F,
        'errors'      => $errors,
        'mae'         => round($mae, 2),
        'rmse'        => round($rmse, 2),
        'mape'        => round($mape, 2),
        'allDates'    => $allDates,
        'start'       => $request->start,
        'end'         => $request->end,
    ]);
}
}
