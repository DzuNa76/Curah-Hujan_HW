@extends('layouts.app')

@section('title', 'Edit Stasiun / Pos Pengamatan')

@section('content')
    <h1 class="h3 mb-2 text-gray-800">Edit Stasiun / Pos Pengamatan</h1>
    <p class="mb-4">Perbarui informasi lokasi stasiun pengamatan.</p>

    @include('components.alert')

    <div class="card shadow mb-4 col-lg-5">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Stasiun</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('stations.update', $station->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="station_name">Nama Stasiun / Pos</label>
                    <input type="text" id="station_name" name="station_name"
                           value="{{ old('station_name', $station->station_name) }}"
                           class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="regency_id">Kabupaten / Kota</label>
                    <select name="regency_id" id="regency_id" class="form-control" required>
                        @foreach ($regencies as $r)
                            <option value="{{ $r->id }}"
                                {{ $station->regency_id == $r->id ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="district_id">Kecamatan</label>
                    <select name="district_id" id="district_id" class="form-control" required>
                        @foreach ($districts as $d)
                            <option value="{{ $d->id }}"
                                {{ $station->district_id == $d->id ? 'selected' : '' }}>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="village_id">Desa / Kelurahan</label>
                    <select name="village_id" id="village_id" class="form-control" required>
                        @foreach ($villages as $v)
                            <option value="{{ $v->id }}"
                                {{ $station->village_id == $v->id ? 'selected' : '' }}>
                                {{ $v->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('stations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
