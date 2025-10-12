@extends('layouts.app')

@section('title', 'Dashboard Curah Hujan')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Dashboard Peramalan Curah Hujan</h1>

    {{-- Ringkasan Data --}}
    @include('dashboard.partials.cards', ['stats' => $stats])

    {{-- Grafik Tren --}}
    @include('dashboard.partials.trend-chart', [
        'stations' => $stations,
        'selectedStation' => $selectedStation,
        'chartData' => $chartData
    ])

    {{-- Tabel Data Terbaru --}}
    @include('dashboard.partials.recent-table', ['recentData' => $recentData])
@endsection
