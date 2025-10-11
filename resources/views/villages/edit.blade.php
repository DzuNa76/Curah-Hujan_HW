@extends('layouts.app')

@section('title', 'Edit Desa / Kelurahan')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Edit Desa / Kelurahan</h1>
    <p class="mb-4">Perbarui data desa atau kelurahan yang sudah ada.</p>

    @include('components.alert')

    <div class="card shadow mb-4 col-lg-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Desa / Kelurahan</h6>
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

            <form action="{{ route('villages.update', $village->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nama Desa / Kelurahan</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $village->name) }}"
                           class="form-control" placeholder="Masukkan nama desa/kelurahan" required>
                </div>

                <div class="form-group">
                    <label for="district_id">Kecamatan</label>
                    <select id="district_id" name="district_id" class="form-control" required>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}"
                                {{ $village->district_id == $district->id ? 'selected' : '' }}>
                                {{ $district->name }} — {{ $district->regency->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('villages.index') }}" class="btn btn-secondary">
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
