@extends('layouts.app')

@section('title', 'Dashboard Curah Hujan')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Dashboard Curah Hujan Tahunan</h1>

    {{-- 🔹 Ringkasan Statistik --}}
    @include('dashboard.partials.cards', ['stats' => $stats])

    {{-- 🔹 Grafik Tren per Kota --}}
    @include('dashboard.partials.trend-chart', [
        'regencies' => $regencies,
        'selectedRegency' => $selectedRegency,
        'chartData' => $chartData
    ])

@endsection
