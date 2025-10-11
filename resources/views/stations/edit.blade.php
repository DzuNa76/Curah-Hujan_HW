@extends('layouts.app')

@section('title', 'Edit Stasiun / Pos Pengamatan')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Edit Stasiun / Pos Pengamatan</h1>
    <p class="mb-4">Perbarui informasi pos pengamatan iklim.</p>

    @include('components.alert')

    <div class="card shadow mb-4 col-lg-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Stasiun</h6>
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

            <form action="{{ route('stations.update', $station->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="station_name">Nama Stasiun / Pos</label>
                    <input type="text" id="station_name" name="station_name"
                           value="{{ old('station_name', $station->station_name) }}"
                           class="form-control" placeholder="Masukkan nama stasiun" required>
                </div>

                <div class="form-group">
                    <label for="village_id">Desa / Kelurahan</label>
                    <select id="village_id" name="village_id" class="form-control" required>
                        @foreach ($villages as $village)
                            <option value="{{ $village->id }}"
                                {{ $station->village_id == $village->id ? 'selected' : '' }}>
                                {{ $village->name }},
                                {{ $village->district->name }},
                                {{ $village->district->regency->name }}
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
