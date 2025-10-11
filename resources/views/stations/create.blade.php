@extends('layouts.app')

@section('title', 'Tambah Stasiun / Pos Pengamatan')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Tambah Stasiun / Pos Pengamatan</h1>
    <p class="mb-4">Isi form berikut untuk menambahkan pos pengamatan iklim baru.</p>

    @include('components.alert')

    <div class="card shadow mb-4 col-lg-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Tambah Stasiun</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('stations.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="station_name">Nama Stasiun / Pos</label>
                    <input type="text" id="station_name" name="station_name" value="{{ old('station_name') }}"
                           class="form-control" placeholder="Masukkan nama stasiun atau pos" required autofocus>
                </div>

                <div class="form-group">
                    <label for="village_id">Desa / Kelurahan</label>
                    <select id="village_id" name="village_id" class="form-control" required>
                        <option value="">-- Pilih Desa / Kelurahan --</option>
                        @foreach ($villages as $village)
                            <option value="{{ $village->id }}">
                                {{ $village->name }},
                                {{ $village->district->name }},
                                {{ $village->district->regency->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('stations.index') }}" class="btn btn-secondary">
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
