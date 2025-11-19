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
     * Validasi kelengkapan data bulanan dalam rentang tanggal
     * 
     * @param Carbon $start Tanggal mulai (start of month)
     * @param Carbon $end Tanggal akhir (end of month)
     * @param array $actualData Data aktual yang tersedia (format: ['Y-m' => value])
     * @return array ['is_complete' => bool, 'completeness_ratio' => float, 'missing_months' => array, 'missing_indices' => array, 'expected_count' => int, 'actual_count' => int, 'expected_months' => array]
     */
    private function validateDataCompleteness(Carbon $start, Carbon $end, array $actualData): array
    {
        // Generate semua bulan yang diharapkan dalam rentang
        $expectedMonths = [];
        $current = $start->copy();
        
        while ($current->lte($end)) {
            $expectedMonths[] = $current->format('Y-m');
            $current->addMonth();
        }
        
        $expectedCount = count($expectedMonths);
        $actualCount = count($actualData);
        
        // Identifikasi bulan yang missing beserta posisi indeksnya
        $missingMonths = [];
        $missingIndices = [];
        foreach ($expectedMonths as $index => $month) {
            if (!isset($actualData[$month])) {
                $missingMonths[] = $month;
                $missingIndices[] = $index; // Posisi indeks untuk visualisasi grafik
            }
        }
        
        // Hitung rasio kelengkapan
        $completenessRatio = $expectedCount > 0 
            ? (($expectedCount - count($missingMonths)) / $expectedCount) * 100 
            : 0;
        
        // Status lengkap jika 100% dan tidak ada bulan missing
        $isComplete = count($missingMonths) === 0 && $completenessRatio >= 100.0;
        
        return [
            'is_complete' => $isComplete,
            'completeness_ratio' => round($completenessRatio, 2),
            'missing_months' => $missingMonths,
            'missing_indices' => $missingIndices, // Untuk visualisasi grafik
            'expected_count' => $expectedCount,
            'actual_count' => $actualCount,
            'expected_months' => $expectedMonths, // Semua bulan yang diharapkan
        ];
    }

    /**
     * Deteksi missing data per stasiun untuk forecasting
     * 
     * @param array $stationIds Array ID stasiun
     * @param Carbon $start Tanggal mulai
     * @param Carbon $end Tanggal akhir
     * @return array Detail missing data per stasiun
     */
    private function detectMissingDataPerStation(array $stationIds, Carbon $start, Carbon $end): array
    {
        // Generate semua bulan yang diharapkan dalam rentang
        $expectedMonths = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $expectedMonths[] = $current->format('Y-m');
            $current->addMonth();
        }

        $stationsDetail = [];
        
        foreach ($stationIds as $stationId) {
            $station = Station::with('village.district.regency')->find($stationId);
            if (!$station) continue;

            // Ambil bulan yang tersedia untuk stasiun ini dalam rentang
            $stationMonths = RainfallData::where('station_id', $stationId)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('date', 'asc')
                ->get()
                ->map(fn($r) => Carbon::parse($r->date)->format('Y-m'))
                ->unique()
                ->values()
                ->toArray();

            // Identifikasi bulan yang missing
            $missingMonths = [];
            foreach ($expectedMonths as $month) {
                if (!in_array($month, $stationMonths)) {
                    $missingMonths[] = $month;
                }
            }

            if (count($missingMonths) > 0) {
                $completenessRatio = count($expectedMonths) > 0 
                    ? round(((count($expectedMonths) - count($missingMonths)) / count($expectedMonths)) * 100, 2) 
                    : 0;

                $stationsDetail[] = [
                    'station_id' => $station->id,
                    'station_name' => $station->station_name,
                    'regency_name' => $station->village->district->regency->name ?? 'N/A',
                    'district_name' => $station->village->district->name ?? 'N/A',
                    'village_name' => $station->village->name ?? 'N/A',
                    'data_count' => count($stationMonths),
                    'expected_count' => count($expectedMonths),
                    'missing_count' => count($missingMonths),
                    'missing_months' => $missingMonths,
                    'completeness_ratio' => $completenessRatio,
                ];
            }
        }

        return $stationsDetail;
    }

    /**
     * Validasi konsistensi data antar stasiun dalam kabupaten
     * 
     * @param int|null $regencyId ID kabupaten (null untuk semua kabupaten)
     * @param Carbon $start Tanggal mulai
     * @param Carbon $end Tanggal akhir
     * @return array ['is_consistent' => bool, 'stations_data' => array, 'inconsistencies' => array]
     */
    private function validateStationsDataConsistency(?int $regencyId, Carbon $start, Carbon $end): array
    {
        // Ambil semua stasiun dalam kabupaten
        $stationsQuery = Station::with('village.district.regency');
        
        if ($regencyId !== null) {
            $stationsQuery->whereHas('village.district.regency', function ($q) use ($regencyId) {
                $q->where('id', $regencyId);
            });
        }
        
        $stations = $stationsQuery->get();
        
        if ($stations->isEmpty()) {
            return [
                'is_consistent' => true,
                'stations_data' => [],
                'inconsistencies' => [],
            ];
        }

        // Generate semua bulan yang diharapkan
        $expectedMonths = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $expectedMonths[] = $current->format('Y-m');
            $current->addMonth();
        }

        // Analisis data per stasiun
        $stationsData = [];
        $inconsistencies = [];
        
        foreach ($stations as $station) {
            $stationMonths = RainfallData::where('station_id', $station->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('date', 'asc')
                ->get()
                ->map(fn($r) => Carbon::parse($r->date)->format('Y-m'))
                ->unique()
                ->values()
                ->toArray();

            $missingMonths = [];
            foreach ($expectedMonths as $month) {
                if (!in_array($month, $stationMonths)) {
                    $missingMonths[] = $month;
                }
            }

            $stationsData[$station->id] = [
                'station_id' => $station->id,
                'station_name' => $station->station_name,
                'regency_name' => $station->village->district->regency->name ?? 'N/A',
                'data_count' => count($stationMonths),
                'expected_count' => count($expectedMonths),
                'missing_months' => $missingMonths,
                'missing_count' => count($missingMonths),
                'completeness_ratio' => count($expectedMonths) > 0 
                    ? round((count($stationMonths) / count($expectedMonths)) * 100, 2) 
                    : 0,
            ];

            // Deteksi inkonsistensi
            if (count($missingMonths) > 0) {
                $inconsistencies[] = [
                    'station_id' => $station->id,
                    'station_name' => $station->station_name,
                    'regency_name' => $station->village->district->regency->name ?? 'N/A',
                    'data_count' => count($stationMonths),
                    'expected_count' => count($expectedMonths),
                    'missing_count' => count($missingMonths),
                    'missing_months' => $missingMonths,
                    'completeness_ratio' => $stationsData[$station->id]['completeness_ratio'],
                ];
            }
        }

        // Deteksi perbedaan panjang data antar stasiun
        $dataCounts = array_column($stationsData, 'data_count');
        $uniqueCounts = array_unique($dataCounts);
        
        $isConsistent = count($uniqueCounts) <= 1 && count($inconsistencies) === 0;

        return [
            'is_consistent' => $isConsistent,
            'stations_data' => $stationsData,
            'inconsistencies' => $inconsistencies,
            'unique_data_counts' => $uniqueCounts,
            'expected_count' => count($expectedMonths),
        ];
    }

    /**
     * Format pesan error untuk data tidak lengkap
     * 
     * @param array $validationResult Hasil dari validateDataCompleteness
     * @return string Pesan error yang detail dan actionable
     */
    private function formatIncompleteDataError(array $validationResult): string
    {
        $missingCount = count($validationResult['missing_months']);
        $completeness = $validationResult['completeness_ratio'];
        $expectedCount = $validationResult['expected_count'];
        $actualCount = $validationResult['actual_count'];
        
        // Format bulan yang missing (maksimal 5 untuk keterbacaan)
        $missingMonthsDisplay = array_slice($validationResult['missing_months'], 0, 5);
        $missingMonthsFormatted = array_map(function($month) {
            try {
                return Carbon::parse($month . '-01')->translatedFormat('F Y');
            } catch (\Exception $e) {
                return $month;
            }
        }, $missingMonthsDisplay);
        
        $missingList = implode(', ', $missingMonthsFormatted);
        if ($missingCount > 5) {
            $missingList .= ' dan ' . ($missingCount - 5) . ' bulan lainnya';
        }
        
        $message = "Data tidak lengkap! Proses peramalan dihentikan.\n\n";
        $message .= "Detail:\n";
        $message .= "• Jumlah bulan yang diharapkan: {$expectedCount} bulan\n";
        $message .= "• Jumlah bulan yang tersedia: {$actualCount} bulan\n";
        $message .= "• Jumlah bulan yang missing: {$missingCount} bulan\n";
        $message .= "• Persentase kelengkapan: {$completeness}%\n";
        $message .= "• Bulan yang missing: {$missingList}\n\n";
        $message .= "Tindakan yang diperlukan:\n";
        $message .= "Silakan lengkapi data curah hujan untuk bulan-bulan yang missing terlebih dahulu sebelum melakukan peramalan. Sistem memerlukan data lengkap 100% untuk menghasilkan peramalan yang akurat dan dapat dipertanggungjawabkan.";
        
        return $message;
    }

    /**
     * API: Get available dates berdasarkan lokasi
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableDates(Request $request)
    {
        $request->validate([
            'type' => 'required|in:station,regency',
            'id' => 'nullable|string',
        ]);

        $type = $request->get('type');
        $id = $request->get('id', 'all');

        $dates = [];

        if ($type === 'station') {
            if ($id !== 'all') {
                // Dates untuk stasiun tertentu
                $dates = RainfallData::where('station_id', $id)
                    ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, MIN(date) as min_date')
                    ->groupBy('month')
                    ->orderBy('month', 'asc')
                    ->pluck('month')
                    ->map(function($month) {
                        return [
                            'value' => Carbon::parse($month . '-01')->format('Y-m-d'),
                            'label' => Carbon::parse($month . '-01')->translatedFormat('F Y'),
                            'month' => $month
                        ];
                    })
                    ->values()
                    ->toArray();
            } else {
                // Dates untuk semua stasiun (rata-rata)
                $dates = DB::table('rainfall_data')
                    ->selectRaw('DATE_FORMAT(rainfall_data.date, "%Y-%m") as month')
                    ->groupBy('month')
                    ->orderBy('month', 'asc')
                    ->pluck('month')
                    ->map(function($month) {
                        return [
                            'value' => Carbon::parse($month . '-01')->format('Y-m-d'),
                            'label' => Carbon::parse($month . '-01')->translatedFormat('F Y'),
                            'month' => $month
                        ];
                    })
                    ->values()
                    ->toArray();
            }
        } else {
            // Type = regency
            $q = DB::table('rainfall_data')
                ->join('stations', 'rainfall_data.station_id', '=', 'stations.id')
                ->join('villages', 'stations.village_id', '=', 'villages.id')
                ->join('districts', 'villages.district_id', '=', 'districts.id')
                ->join('regencies', 'districts.regency_id', '=', 'regencies.id')
                ->selectRaw('DATE_FORMAT(rainfall_data.date, "%Y-%m") as month')
                ->groupBy('month');

            if ($id !== 'all') {
                $q->where('regencies.id', $id);
            }

            $dates = $q->orderBy('month', 'asc')
                ->pluck('month')
                ->map(function($month) {
                    return [
                        'value' => Carbon::parse($month . '-01')->format('Y-m-d'),
                        'label' => Carbon::parse($month . '-01')->translatedFormat('F Y'),
                        'month' => $month
                    ];
                })
                ->values()
                ->toArray();
        }

        return response()->json([
            'success' => true,
            'dates' => $dates
        ]);
    }

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
        // VALIDASI INPUT DENGAN KETAT
        // ------------------------------
        $validated = $request->validate([
            'alpha' => 'required|numeric|min:0|max:1',
            'beta'  => 'required|numeric|min:0|max:1',
            'gamma' => 'required|numeric|min:0|max:1',
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
            'type'  => 'nullable|in:station,regency',
            'id'    => 'nullable|string',
        ]);

        // Sanitasi dan type casting parameter
        $alpha = (float) $validated['alpha'];
        $beta  = (float) $validated['beta'];
        $gamma = (float) $validated['gamma'];

        // Parse dan validasi tanggal dengan error handling
        try {
            $start = Carbon::parse($validated['start'])->startOfMonth();
            $end   = Carbon::parse($validated['end'])->endOfMonth();
        } catch (\Exception $e) {
            return redirect()->route('forecasting.index')
                ->withInput()
                ->with('error', 'Format tanggal tidak valid. Silakan pilih tanggal yang benar.');
        }

        // Validasi rentang tanggal tidak terlalu besar (maksimal 10 tahun untuk performa)
        if ($start->diffInMonths($end) > 120) {
            return redirect()->route('forecasting.index')
                ->withInput()
                ->with('error', 'Rentang tanggal terlalu besar. Maksimal 10 tahun (120 bulan).');
        }

        $type = $validated['type'] ?? 'station';
        $id   = $validated['id'] ?? 'all';
        
        // Sanitasi ID: jika bukan 'all', pastikan adalah integer atau string yang valid
        if ($id !== 'all' && !is_numeric($id) && !ctype_alnum($id)) {
            return redirect()->route('forecasting.index')
                ->withInput()
                ->with('error', 'Parameter ID tidak valid.');
        }

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

            // ========================================
            // VALIDASI KONSISTENSI DATA ANTAR STASIUN
            // ========================================
            $regencyId = ($id !== 'all') ? (int) $id : null;
            $consistencyValidation = $this->validateStationsDataConsistency($regencyId, $start, $end);
            
            if (!$consistencyValidation['is_consistent']) {
                // Format error message untuk inkonsistensi
                $errorDetails = [
                    'type' => 'inconsistency',
                    'total_stations' => count($consistencyValidation['stations_data']),
                    'inconsistent_stations' => count($consistencyValidation['inconsistencies']),
                    'expected_count' => $consistencyValidation['expected_count'],
                    'inconsistencies' => $consistencyValidation['inconsistencies'],
                    'unique_data_counts' => $consistencyValidation['unique_data_counts'],
                ];
                
                return redirect()->route('forecasting.index', [
                    'type' => $type,
                    'id' => $id
                ])->withInput()
                  ->with('consistency_error', $errorDetails)
                  ->with('error', 'Data antar stasiun tidak konsisten! Proses peramalan dihentikan.');
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

        // ========================================
        // VALIDASI KELENGKAPAN DATA (100% THRESHOLD)
        // ========================================
        $completenessValidation = $this->validateDataCompleteness($start, $end, $data);
        
        // Deteksi missing data per stasiun untuk kasus "semua stasiun"
        $stationsDetail = null;
        if (!$completenessValidation['is_complete']) {
            // Jika filter adalah "semua stasiun" atau "kota tertentu/semua stasiun", ambil detail per stasiun
            if ($type === 'station' && $id === 'all') {
                // Semua stasiun
                $allStationIds = Station::pluck('id')->toArray();
                if (!empty($allStationIds)) {
                    $stationsDetail = $this->detectMissingDataPerStation($allStationIds, $start, $end);
                }
            } elseif ($type === 'regency') {
                // Kota tertentu dengan semua stasiun
                $regencyId = ($id !== 'all') ? (int) $id : null;
                $regencyStations = Station::whereHas('village.district.regency', function ($q) use ($regencyId) {
                    if ($regencyId !== null) {
                        $q->where('id', $regencyId);
                    }
                })->pluck('id')->toArray();
                
                if (!empty($regencyStations)) {
                    $stationsDetail = $this->detectMissingDataPerStation($regencyStations, $start, $end);
                }
            }
            
            // Simpan data validasi untuk ditampilkan di view
            return redirect()->route('forecasting.index', [
                'type' => $type,
                'id' => $id
            ])->withInput()
              ->with('validation_error', $completenessValidation)
              ->with('stations_detail', $stationsDetail)
              ->with('error', 'Data tidak lengkap! Proses peramalan dihentikan.');
        }

        ksort($data);
        $values = array_values($data);
        $labels = array_keys($data);
        $m = 12; // panjang musim (L dalam rumus)
        $n = count($values);

        // ========================================
        // VALIDASI MINIMUM DATA (24 BULAN = 2 MUSIM)
        // ========================================
        if ($n < 2 * $m) {
            return redirect()->route('forecasting.index', [
                'type' => $type,
                'id' => $id
            ])->withInput()->with('error', "Dibutuhkan minimal " . (2 * $m) . " bulan data (2 musim penuh). Data saat ini: " . $n . " bulan.");
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

        // Hitung NMAE (Normalized Mean Absolute Error)
        // NMAE = (MAE / mean(actual values)) * 100
        $validValues = [];
        for ($t = $m; $t < $n; $t++) {
            if (!is_null($values[$t]) && $values[$t] != 0) {
                $validValues[] = $values[$t];
            }
        }
        $meanActual = count($validValues) > 0 ? array_sum($validValues) / count($validValues) : 0;
        $nmae = ($meanActual > 0 && $mae > 0) ? ($mae / $meanActual) * 100 : 0;

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
            'nmae'         => round($nmae, 2),
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
            'nmae'   => 'nullable',
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
        $nmae = $request->nmae ?? null;

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
            'nmae' => $nmae,
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