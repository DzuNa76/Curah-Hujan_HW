@extends('layouts.app')

@section('title', 'Tambah Stasiun / Pos Pengamatan')

@section('content')
    <h1 class="h3 mb-2 text-gray-800">Tambah Stasiun / Pos Pengamatan</h1>
    <p class="mb-4">Isi form berikut untuk menambahkan data stasiun baru.</p>

    @include('components.alert')

    <div class="card shadow mb-4 col-lg-5">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Tambah Stasiun</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('stations.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="station_name">Nama Stasiun / Pos</label>
                    <input type="text" id="station_name" name="station_name"
                           class="form-control" placeholder="Masukkan nama stasiun" required>
                </div>

                <div class="form-group">
                    <label for="regency_id">Kabupaten / Kota</label>
                    <select name="regency_id" id="regency_id" class="form-control" required>
                        <option value="">-- Pilih Kabupaten / Kota --</option>
                        @foreach ($regencies as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="district_id">Kecamatan</label>
                    <select name="district_id" id="district_id" class="form-control" required>
                        <option value="">-- Pilih Kecamatan --</option>
                        @foreach ($districts as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="village_id">Desa / Kelurahan</label>
                    <select name="village_id" id="village_id" class="form-control" required>
                        <option value="">-- Pilih Desa / Kelurahan --</option>
                        @foreach ($villages as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
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
