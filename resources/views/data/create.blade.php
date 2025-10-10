@extends('layouts.app')

@section('title', 'Tambah Data Curah Hujan')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Tambah Data Curah Hujan</h1>
    <p class="mb-4">Silakan isi form berikut untuk menambahkan data curah hujan baru.</p>

    <div class="card shadow mb-4 col-lg-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Tambah Data</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('rainfall.store') }}" method="POST">
                @csrf

                {{-- Pilihan Stasiun --}}
                <div class="form-group">
                    <label for="station_id">Pos / Stasiun Pengamatan</label>
                    <select id="station_id" name="station_id" class="form-control" required>
                        <option value="">-- Pilih Stasiun --</option>
                        @foreach ($stations as $station)
                            <option value="{{ $station->id }}">
                                {{ $station->station_name }} — 
                                {{ $station->village->name }},
                                {{ $station->village->district->name }},
                                {{ $station->village->district->regency->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="monthYear">Bulan dan Tahun</label>
                    <input type="month" id="monthYear" name="monthYear" class="form-control" required autofocus>
                </div>

                <div class="form-group">
                    <label for="rainfall_amount">Curah Hujan (mm)</label>
                    <input type="number" id="rainfall_amount" name="rainfall_amount" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="rain_days">Hari Hujan</label>
                    <input type="number" id="rain_days" name="rain_days" class="form-control" required>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('rainfall.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
