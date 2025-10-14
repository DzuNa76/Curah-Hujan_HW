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
            'alpha'        => 0.3,
            'beta'         => 0.01,
            'gamma'        => 0.2,
            'L'            => [],
            'T'            => [],
            'S'            => [],
            'F'            => [],
            'errorValues'  => [], // 🔹 PERBAIKAN: Konsisten pakai errorValues
            'mae'          => null, // 🔹 PERBAIKAN: null bukan 0
            'rmse'         => null, // 🔹 PERBAIKAN: null bukan 0
            'message'      => true,
            'allDates'     => $months,
            'start'        => $start,
            'end'          => $end,
            'stations'     => $stations,
            'regencies'    => $regencies,
            'selectedType' => $request->get('type', 'station'),
            'selectedId'   => $request->get('id', 'all'),
        ]);
    }

    /**
     * Proses peramalan menggunakan Holt-Winters Additive
     */
    public function process(Request $request)
    {
        // Validasi dasar
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
                    return back()->withInput()->with('error', 'Stasiun tidak ditemukan.');
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
                    return back()->withInput()->with('error', 'Kota tidak ditemukan.');
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
            return back()->withInput()->with('error', 'Tidak ada data curah hujan pada rentang waktu yang dipilih.');
        }

        ksort($data);
        $labels = array_keys($data);
        $values = array_values($data);

        // ========================================
        // VALIDASI MINIMUM DATA
        // ========================================
        $seasonLength = 12;
        $n = count($values);

        if ($n < 2 * $seasonLength) {
            return back()->withInput()->with('error', "Dibutuhkan minimal " . (2 * $seasonLength) . " bulan data (2 musim). Data saat ini: " . $n . " bulan.");
        }

        // ========================================
        // INISIALISASI KOMPONEN
        // ========================================
        $numSeasons = intdiv($n, $seasonLength);
        $avgMonth = array_fill(0, $seasonLength, 0.0);

        for ($i = 0; $i < $seasonLength; $i++) {
            $sum = 0.0;
            for ($s = 0; $s < $numSeasons; $s++) {
                $sum += $values[$s * $seasonLength + $i];
            }
            $avgMonth[$i] = $sum / $numSeasons;
        }

        $overallMean = array_sum(array_slice($values, 0, $numSeasons * $seasonLength)) / ($numSeasons * $seasonLength);

        $S = array_fill(0, $seasonLength, 0.0);
        for ($i = 0; $i < $seasonLength; $i++) {
            $S[$i] = $avgMonth[$i] - $overallMean;
        }

        $S_sum = array_sum($S);
        for ($i = 0; $i < $seasonLength; $i++) {
            $S[$i] -= ($S_sum / $seasonLength);
        }

        $level0 = 0.0;
        for ($i = 0; $i < $seasonLength; $i++) {
            $level0 += ($values[$i] - $S[$i]);
        }
        $level0 /= $seasonLength;

        $trendSum = 0.0;
        for ($i = 0; $i < $seasonLength; $i++) {
            $trendSum += ($values[$seasonLength + $i] - $values[$i]);
        }
        $trend0 = $trendSum / ($seasonLength * $seasonLength);

        // ========================================
        // SMOOTHING
        // ========================================
        $L = array_fill(0, $n + 12, null);
        $T = array_fill(0, $n + 12, null);
        $F = array_fill(0, $n + 12, null);
        $errorValues = array_fill(0, $n + 12, null);
        $S_values = array_fill(0, $n + 12, null);

        for ($t = 0; $t < $seasonLength; $t++) {
            $L[$t] = $level0;
            $T[$t] = $trend0;
            $S_values[$t] = $S[$t % $seasonLength];
        }

        for ($t = $seasonLength; $t < $n; $t++) {
            $s_idx = $t % $seasonLength;

            $F[$t] = $L[$t - 1] + $T[$t - 1] + $S[$s_idx];
            $errorValues[$t] = $values[$t] - $F[$t];

            $S_old = $S[$s_idx];
            $L[$t] = $alpha * ($values[$t] - $S_old) + (1 - $alpha) * ($L[$t - 1] + $T[$t - 1]);
            $T[$t] = $beta * ($L[$t] - $L[$t - 1]) + (1 - $beta) * $T[$t - 1];
            $S[$s_idx] = $gamma * ($values[$t] - $L[$t]) + (1 - $gamma) * $S_old;
            $S_values[$t] = $S[$s_idx];
        }

        // ========================================
        // FORECAST 12 BULAN KE DEPAN
        // ========================================
        $H = 12;
        $lastL = $L[$n - 1];
        $lastT = $T[$n - 1];

        for ($h = 1; $h <= $H; $h++) {
            $t = $n + $h - 1;
            $s_idx = $t % $seasonLength;

            $forecast = $lastL + $h * $lastT + $S[$s_idx];
            $nextLabel = Carbon::parse($labels[$n - 1] . '-01')->addMonths($h)->format('Y-m');

            $labels[] = $nextLabel;
            $values[] = null;
            $L[$t] = $lastL;
            $T[$t] = $lastT;
            $S_values[$t] = $S[$s_idx];
            $F[$t] = $forecast;
            $errorValues[$t] = null;
        }

        // ========================================
        // EVALUASI AKURASI
        // ========================================
        $validErrors = [];
        for ($t = $seasonLength; $t < $n; $t++) {
            if (!is_null($errorValues[$t])) {
                $validErrors[] = $errorValues[$t];
            }
        }

        $mae = count($validErrors) > 0
            ? array_sum(array_map('abs', $validErrors)) / count($validErrors)
            : 0;

        $rmse = count($validErrors) > 0
            ? sqrt(array_sum(array_map(fn($e) => $e * $e, $validErrors)) / count($validErrors))
            : 0;

        $allDates = RainfallData::orderBy('date')
            ->get()
            ->map(fn($r) => $r->month_year)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // ========================================
        // RETURN VIEW - 🔹 PERBAIKAN: Konsisten pakai errorValues
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
            'errorValues'  => $errorValues, // 🔹 Konsisten
            'mae'          => round($mae, 2),
            'rmse'         => round($rmse, 2),
            'allDates'     => $allDates,
            'start'        => $start->toDateString(),
            'end'          => $end->toDateString(),
            'stations'     => $stations,
            'regencies'    => $regencies,
            'selectedType' => $type,
            'selectedId'   => $id,
            'station'      => $station,
            'regency'      => $regency,
            'message'      => false, // 🔹 Set false setelah proses
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
        $errorValues = json_decode(html_entity_decode($request->errorValues ?? '[]'), true); // 🔹 PERBAIKAN

        $mae = $request->mae ?? null;
        $rmse = $request->rmse ?? null;

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
            'errorValues' => $errorValues, // 🔹 Konsisten
            'mae' => $mae,
            'rmse' => $rmse,
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