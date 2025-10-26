<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RainfallData;
use App\Models\Station;
use App\Models\Regency;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ForecastingController extends Controller
{
    /**
     * Tampilkan form peramalan (index)
     */
    public function index(Request $request)
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

        $stations = Station::with('village.district.regency')->orderBy('station_name')->get();
        $regencies = Regency::orderBy('name')->get();

        return view('forecasting.index', [
            'values'       => [],
            'labels'       => [],
            'alpha'        => $request->old('alpha', 0.3),
            'beta'         => $request->old('beta', 0.2),
            'gamma'        => $request->old('gamma', 0.3),
            'L'            => [],
            'T'            => [],
            'S'            => [],
            'F'            => [],
            'errors'       => [],
            'mae'          => 0,
            'rmse'         => 0,
            'mape'         => 0,
            'message'      => true,
            'allDates'     => $months,
            'start'        => $request->old('start', $start),
            'end'          => $request->old('end', $end),
            'stations'     => $stations,
            'regencies'    => $regencies,
            'selectedType' => $request->old('type', $request->get('type', 'station')),
            'selectedId'   => $request->old('id', $request->get('id', 'all')),
        ]);
    }

    /**
     * Proses peramalan menggunakan Holt-Winters Additive
     */
    public function process(Request $request)
    {
        // ------------------------------
        // Validasi input
        // ------------------------------
        $request->validate([
            'alpha' => 'required|numeric|min:0|max:1',
            'beta'  => 'required|numeric|min:0|max:1',
            'gamma' => 'required|numeric|min:0|max:1',
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
            'type'  => 'nullable|in:station,regency',
            'id'    => 'nullable',
        ]);

        $alpha = (float) $request->alpha;
        $beta  = (float) $request->beta;
        $gamma = (float) $request->gamma;

        $start = Carbon::parse($request->start)->startOfMonth();
        $end   = Carbon::parse($request->end)->endOfMonth();

        $type = $request->get('type', 'station');
        $id   = $request->get('id', 'all');

        $stations = Station::with('village.district.regency')->orderBy('station_name')->get();
        $regencies = Regency::orderBy('name')->get();

        $station = null;
        $regency = null;
        $data = [];

        // ========================================
        // AMBIL DATA SESUAI TIPE
        // ========================================
        if ($type === 'station') {
            if ($id !== 'all') {
                $station = Station::with('village.district.regency')->find($id);
                if (!$station) {
                    return redirect()->route('forecasting.index', [
                        'type' => $type,
                        'id' => $id
                    ])->withInput()->with('error', 'Stasiun tidak ditemukan.');
                }

                $rows = RainfallData::where('station_id', $id)
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->orderBy('date')
                    ->get(['date', 'rainfall_amount']);

                foreach ($rows as $r) {
                    $key = Carbon::parse($r->date)->format('Y-m');
                    $data[$key] = (float) $r->rainfall_amount;
                }
            } else {
                // Rata-rata semua stasiun
                $rows = DB::table('rainfall_data')
                    ->selectRaw('DATE_FORMAT(rainfall_data.date, "%Y-%m") as month, AVG(rainfall_data.rainfall_amount) as avg_rain')
                    ->whereBetween('rainfall_data.date', [$start->toDateString(), $end->toDateString()])
                    ->groupBy('month')
                    ->orderBy('month', 'asc')
                    ->get();

                foreach ($rows as $r) {
                    $data[$r->month] = (float) $r->avg_rain;
                }
            }
        } else {
            // Type = regency
            if ($id !== 'all') {
                $regency = Regency::find($id);
                if (!$regency) {
                    return redirect()->route('forecasting.index', [
                        'type' => $type,
                        'id' => $id
                    ])->withInput()->with('error', 'Kabupaten tidak ditemukan.');
                }
            }

            $q = DB::table('rainfall_data')
                ->join('stations', 'rainfall_data.station_id', '=', 'stations.id')
                ->join('villages', 'stations.village_id', '=', 'villages.id')
                ->join('districts', 'villages.district_id', '=', 'districts.id')
                ->join('regencies', 'districts.regency_id', '=', 'regencies.id')
                ->selectRaw('DATE_FORMAT(rainfall_data.date, "%Y-%m") as month, AVG(rainfall_data.rainfall_amount) as avg_rain')
                ->whereBetween('rainfall_data.date', [$start->toDateString(), $end->toDateString()]);

            if ($id !== 'all') {
                $q->where('regencies.id', $id);
            }

            $rows = $q->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();

            foreach ($rows as $r) {
                $data[$r->month] = (float) $r->avg_rain;
            }
        }

        // Cek data kosong
        if (empty($data)) {
            return redirect()->route('forecasting.index', [
                'type' => $type,
                'id' => $id
            ])->withInput()->with('error', 'Tidak ada data curah hujan pada rentang waktu yang dipilih.');
        }

        ksort($data);
        $values = array_values($data);
        $labels = array_keys($data);
        $m = 12; // panjang musim (L dalam rumus)
        $n = count($values);

        // ========================================
        // VALIDASI MINIMUM DATA
        // ========================================
        if ($n < 2 * $m) {
            return redirect()->route('forecasting.index', [
                'type' => $type,
                'id' => $id
            ])->withInput()->with('error', "Dibutuhkan minimal " . (2 * $m) . " bulan data (2 musim). Data saat ini: " . $n . " bulan.");
        }

        // ========================================
        // INISIALISASI KOMPONEN ADDITIVE
        // Menggunakan 1 tahun pertama (12 bulan)
        // ========================================

        // PERSAMAAN 2.1: Nilai Awal Level
        // S_L = (1/L) × (X₁ + X₂ + X₃ + ... + X_L)
        $level0 = 0.0;
        for ($i = 0; $i < $m; $i++) {
            $level0 += $values[$i];
        }
        $level0 = $level0 / $m;

        // PERSAMAAN 2.2: Nilai Awal Trend
        // T_L = (1/L) × [(X_{L+1} - X₁)/L + (X_{L+2} - X₂)/L + ... + (X_{2L} - X_L)/L]
        // Atau: T_L = [(sum musim ke-2) - (sum musim ke-1)] / L²
        $trend_sum = 0.0;
        for ($i = 0; $i < $m; $i++) {
            $trend_sum += ($values[$m + $i] - $values[$i]) / $m;
        }
        $trend0 = $trend_sum / $m;

        // PERSAMAAN 2.3: Nilai Awal Musiman (Additive)
        // I_t = X_t - S_L, untuk t = 1, 2, ..., L
        $S = array_fill(0, $m, 0.0);
        for ($i = 0; $i < $m; $i++) {
            $S[$i] = $values[$i] - $level0;
        }

        // Normalisasi agar Σ I_t = 0
        $S_sum = array_sum($S);
        for ($i = 0; $i < $m; $i++) {
            $S[$i] -= ($S_sum / $m);
        }

        // ========================================
        // SMOOTHING
        // ========================================
        $L = array_fill(0, $n + 12, null);
        $T = array_fill(0, $n + 12, null);
        $F = array_fill(0, $n + 12, null);
        $errors = array_fill(0, $n + 12, null);
        $S_values = array_fill(0, $n + 12, null);

        // Set nilai awal untuk periode 0 sampai m-1
        for ($t = 0; $t < $m; $t++) {
            $L[$t] = $level0;
            $T[$t] = $trend0;
            $S_values[$t] = $S[$t % $m];
        }

        // Iterasi smoothing mulai dari t = m
        for ($t = $m; $t < $n; $t++) {
            $s_idx = $t % $m;

            // Forecast (one-step ahead)
            $F[$t] = $L[$t - 1] + $T[$t - 1] + $S[$s_idx];

            // Error
            $errors[$t] = $values[$t] - $F[$t];

            // Simpan nilai seasonal lama (I_{t-L})
            $S_old = $S[$s_idx];

            // PERSAMAAN 2.5: Update Level
            // S_t = α(X_t - I_{t-L}) + (1 - α)(S_{t-1} + T_{t-1})
            $L[$t] = $alpha * ($values[$t] - $S_old) + (1 - $alpha) * ($L[$t - 1] + $T[$t - 1]);

            // PERSAMAAN 2.6: Update Trend
            // T_t = β(S_t - S_{t-1}) + (1 - β)T_{t-1}
            $T[$t] = $beta * ($L[$t] - $L[$t - 1]) + (1 - $beta) * $T[$t - 1];

            // PERSAMAAN 2.7: Update Musiman
            // I_t = γ(X_t - S_t) + (1 - γ)I_{t-1}
            $S[$s_idx] = $gamma * ($values[$t] - $L[$t]) + (1 - $gamma) * $S_old;

            $S_values[$t] = $S[$s_idx];
        }

        // ========================================
        // PERSAMAAN 2.8: FORECAST KE DEPAN (12 BULAN)
        // F_{t+m} = S_t + m×T_t + I_{t-L+m}
        // ========================================
        $H = 12;
        $lastL = $L[$n - 1];
        $lastT = $T[$n - 1];

        for ($h = 1; $h <= $H; $h++) {
            $t = $n + $h - 1;
            $s_idx = $t % $m;

            $forecast = $lastL + $h * $lastT + $S[$s_idx];
            $nextLabel = Carbon::parse($labels[$n - 1] . '-01')->addMonths($h)->format('Y-m');

            $labels[] = $nextLabel;
            $values[] = null;
            $L[$t] = $lastL;
            $T[$t] = $lastT;
            $S_values[$t] = $S[$s_idx];
            $F[$t] = $forecast;
            $errors[$t] = null;
        }

        // ========================================
        // EVALUASI AKURASI
        // ========================================
        $validErrors = [];
        for ($t = $m; $t < $n; $t++) {
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
        for ($t = $m; $t < $n; $t++) {
            if (!is_null($errors[$t]) && $values[$t] != 0) {
                $mapeSum += abs($errors[$t] / $values[$t]);
                $mapeCount++;
            }
        }
        $mape = $mapeCount > 0 ? ($mapeSum / $mapeCount) * 100 : 0;

        // Ambil tanggal untuk dropdown
        $allDates = RainfallData::orderBy('date')
            ->get()
            ->map(fn($r) => $r->month_year)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // ========================================
        // RETURN VIEW
        // ========================================
        return view('forecasting.index', [
            'values'       => $values,
            'labels'       => $labels,
            'alpha'        => $alpha,
            'beta'         => $beta,
            'gamma'        => $gamma,
            'L'            => $L,
            'T'            => $T,
            'S'            => $S_values,
            'F'            => $F,
            'errors'       => $errors,
            'errorValues'  => $errors, // Alias untuk kompatibilitas view
            'mae'          => round($mae, 2),
            'rmse'         => round($rmse, 2),
            'mape'         => round($mape, 2),
            'message'      => false, // Set false agar hasil ditampilkan
            'allDates'     => $allDates,
            'start'        => $request->start,
            'end'          => $request->end,
            'stations'     => $stations,
            'regencies'    => $regencies,
            'selectedType' => $type,
            'selectedId'   => $id,
            'station'      => $station,
            'regency'      => $regency,
        ]);
    }

    /**
     * Cetak PDF hasil peramalan
     */
    public function print(Request $request)
    {
        $request->validate([
            'labels' => 'required',
            'values' => 'required',
            'F'      => 'required',
            'mae'    => 'nullable',
            'rmse'   => 'nullable',
            'mape'   => 'nullable',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'station_id' => 'nullable',
            'regency_id' => 'nullable',
        ]);

        $labels = json_decode(html_entity_decode($request->labels), true);
        $values = json_decode(html_entity_decode($request->values), true);
        $F      = json_decode(html_entity_decode($request->F), true);
        $L      = json_decode(html_entity_decode($request->L ?? '[]'), true);
        $T      = json_decode(html_entity_decode($request->T ?? '[]'), true);
        $S      = json_decode(html_entity_decode($request->S ?? '[]'), true);
        $errors = json_decode(html_entity_decode($request->errorValues ?? $request->errors ?? '[]'), true);

        $mae = $request->mae ?? null;
        $rmse = $request->rmse ?? null;
        $mape = $request->mape ?? null;

        $start_date = $request->start_date ?? null;
        $end_date = $request->end_date ?? null;

        $station = null;
        $regency = null;
        if ($request->filled('station_id') && $request->station_id !== 'all') {
            $station = Station::with('village.district.regency')->find($request->station_id);
        }
        if ($request->filled('regency_id') && $request->regency_id !== 'all') {
            $regency = Regency::find($request->regency_id);
        }

        $user = Auth::user()->name ?? 'Administrator';
        $printed_at = Carbon::now()->translatedFormat('d F Y H:i');

        $pdf = Pdf::loadView('forecasting.print', [
            'labels' => $labels,
            'values' => $values,
            'F' => $F,
            'L' => $L,
            'T' => $T,
            'S' => $S,
            'errors' => $errors,
            'mae' => $mae,
            'rmse' => $rmse,
            'mape' => $mape,
            'user' => $user,
            'printed_at' => $printed_at,
            'station' => $station,
            'regency' => $regency,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);

        return $pdf->download('Laporan_Peramalan_Curah_Hujan_'.date('Ymd_His').'.pdf');
    }
}