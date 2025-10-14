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
            // default kosong / nilai awal agar blade tidak error
            'values'      => [],
            'labels'      => [],
            'alpha'       => 0.3,
            'beta'        => 0.2,
            'gamma'       => 0.1,
            'L'           => [],
            'T'           => [],
            'S'           => [],
            'F'           => [],
            'errors'      => [],
            'mae'         => 0,
            'rmse'        => 0,
            'mape'        => 0,
            'message'     => true,
            'allDates'    => $months,
            'start'       => $start,
            'end'         => $end,
            'stations'    => $stations,
            'regencies'   => $regencies,
            'selectedType'=> $request->get('type', 'station'),
            'selectedId'  => $request->get('id', 'all'),
        ]);
    }

    /**
     * Proses peramalan (GET)
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

        $type = $request->get('type', 'station'); // 'station' or 'regency'
        $id   = $request->get('id', 'all');

        // Ambil daftar stations / regencies untuk dropdown kembali ke view
        $stations = Station::with('village.district.regency')->orderBy('station_name')->get();
        $regencies = Regency::orderBy('name')->get();

        // Siapkan variables lokasi untuk dikirim ke view / print
        $station = null;
        $regency = null;

        // Ambil data sesuai pilihan:
        if ($type === 'station' && $id !== 'all') {
            // pastikan stasiun ada
            $station = Station::with('village.district.regency')->find($id);

            $rows = RainfallData::where('station_id', $id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('date')
                ->get(['date', 'rainfall_amount']);
            $data = [];
            foreach ($rows as $r) {
                $key = Carbon::parse($r->date)->format('Y-m');
                $data[$key] = (float) $r->rainfall_amount;
            }
        } else {
            // regency OR 'all' stations: aggregate by month (average)
            $q = DB::table('rainfall_data')
                ->join('stations', 'rainfall_data.station_id', '=', 'stations.id')
                ->join('villages', 'stations.village_id', '=', 'villages.id')
                ->join('districts', 'villages.district_id', '=', 'districts.id')
                ->join('regencies', 'districts.regency_id', '=', 'regencies.id')
                ->selectRaw('DATE_FORMAT(rainfall_data.date, "%Y-%m") as month, AVG(rainfall_data.rainfall_amount) as avg_rain')
                ->whereBetween('rainfall_data.date', [$start->toDateString(), $end->toDateString()]);

            if ($type === 'regency' && $id !== 'all') {
                $regency = Regency::find($id);
                $q->where('regencies.id', $id);
            }

            $q->groupBy('month')
              ->orderBy('month', 'asc');

            $rows = $q->get();
            $data = [];
            foreach ($rows as $r) {
                $data[$r->month] = (float) $r->avg_rain;
            }
        }

        // Ambil labels & values
        if (empty($data)) {
            return back()->with('error', 'Tidak ada data curah hujan pada rentang waktu yang dipilih.');
        }

        // Ensure keys are sorted ascending by date
        ksort($data);
        $labels = array_keys($data);
        $values = array_values($data);

        // Periksa kecukupan data: butuh minimal 2 musim (2 * 12 bulan)
        $L_period = 12;
        $n = count($values);
        if ($n < 2 * $L_period) {
            return back()->with('error', "Dibutuhkan minimal " . (2 * $L_period) . " bulan data (2 musim). Data saat ini: " . $n . " bulan.");
        }

        // -----------------------------
        // Inisialisasi Holt-Winters (Additive)
        // -----------------------------
        $S_L = 0.0;
        for ($i = 0; $i < $L_period; $i++) $S_L += $values[$i];
        $S_L /= $L_period;

        $T_L = 0.0;
        for ($i = 0; $i < $L_period; $i++) $T_L += ($values[$L_period + $i] - $values[$i]) / $L_period;
        $T_L /= $L_period;

        $I = [];
        for ($i = 0; $i < $L_period; $i++) {
            $I[$i] = $values[$i] - $S_L;
        }

        // prepare arrays
        $S = array_fill(0, $n + 12, null); // level
        $T = array_fill(0, $n + 12, null); // trend
        $SI = array_fill(0, $n + 12, null); // seasonal index (I)
        $F = array_fill(0, $n + 12, null); // forecast
        $errors = array_fill(0, $n + 12, null);

        // initialize first L periods
        for ($t = 0; $t < $L_period; $t++) {
            $S[$t] = $S_L;
            $T[$t] = $T_L;
            $SI[$t] = $I[$t];
        }

        // smoothing for t = L .. n-1
        for ($t = $L_period; $t < $n; $t++) {
            $seasonal_index = $t % $L_period;

            // forecast at t (based on t-1)
            $F[$t] = $S[$t - 1] + $T[$t - 1] + $SI[$seasonal_index];

            // error
            $errors[$t] = $values[$t] - $F[$t];

            // update level
            $S[$t] = $alpha * ($values[$t] - $SI[$seasonal_index]) + (1 - $alpha) * ($S[$t - 1] + $T[$t - 1]);

            // update trend
            $T[$t] = $beta * ($S[$t] - $S[$t - 1]) + (1 - $beta) * $T[$t - 1];

            // update seasonal index (store by position)
            $I[$seasonal_index] = $gamma * ($values[$t] - $S[$t]) + (1 - $gamma) * $I[$seasonal_index];
            $SI[$t] = $I[$seasonal_index];
        }

        // forecast H months ahead (H = 12)
        $H = 12;
        $lastS = $S[$n - 1];
        $lastT = $T[$n - 1];

        for ($h = 1; $h <= $H; $h++) {
            $t = $n + $h - 1;
            $seasonal_index = $t % $L_period;

            $F[$t] = $lastS + $h * $lastT + $I[$seasonal_index];

            // append labels and null value for actual
            $nextLabel = Carbon::parse($labels[$n - 1] . '-01')->addMonths($h)->format('Y-m');
            $labels[] = $nextLabel;
            $values[] = null;
            // keep S/T/SI for completeness
            $S[$t] = $lastS;
            $T[$t] = $lastT;
            $SI[$t] = $I[$seasonal_index];
            $errors[$t] = null;
        }

        // compute metrics using valid error entries (only where forecast and actual exist)
        $validErrors = [];
        for ($t = $L_period; $t < $n; $t++) {
            if (!is_null($errors[$t])) $validErrors[] = $errors[$t];
        }

        $mae = count($validErrors) ? array_sum(array_map('abs', $validErrors)) / count($validErrors) : 0;
        $rmse = count($validErrors) ? sqrt(array_sum(array_map(fn($e) => $e * $e, $validErrors)) / count($validErrors)) : 0;

        $mapeSum = 0; $mapeCount = 0;
        for ($t = $L_period; $t < $n; $t++) {
            if (!is_null($errors[$t]) && $values[$t] != 0) {
                $mapeSum += abs($errors[$t] / $values[$t]);
                $mapeCount++;
            }
        }
        $mape = $mapeCount ? ($mapeSum / $mapeCount) * 100 : 0;

        // prepare allDates for dropdown
        $allDates = RainfallData::orderBy('date')
            ->get()
            ->map(fn($r) => $r->month_year)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // return to view with everything
        return view('forecasting.index', [
            'values'       => $values,
            'labels'       => $labels,
            'alpha'        => $alpha,
            'beta'         => $beta,
            'gamma'        => $gamma,
            'L'            => $S,
            'T'            => $T,
            'S'            => $SI,
            'F'            => $F,
            'errors'       => $errors,
            'mae'          => round($mae, 2),
            'rmse'         => round($rmse, 2),
            'mape'         => round($mape, 2),
            'allDates'     => $allDates,
            'start'        => $start->toDateString(),
            'end'          => $end->toDateString(),
            'stations'     => $stations,
            'regencies'    => $regencies,
            'selectedType' => $type,
            'selectedId'   => $id,
            'station'      => $station,
            'regency'      => $regency,
            'message'      => false,
        ]);
    }

    /**
     * Cetak PDF hasil peramalan (POST)
     */
    public function print(Request $request)
    {
        $request->validate([
            'labels' => 'required',
            'values' => 'required',
            'F'      => 'required',
            'mae'    => 'nullable',
            'rmse'   => 'nullable',
            // optional meta
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
        $errors = json_decode(html_entity_decode($request->errors ?? '[]'), true);

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
