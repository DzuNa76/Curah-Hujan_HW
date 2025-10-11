@extends('layouts.app')

@section('title', 'Tambah Desa / Kelurahan')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Tambah Desa / Kelurahan</h1>
    <p class="mb-4">Isi form berikut untuk menambahkan desa atau kelurahan baru.</p>

    @include('components.alert')

    <div class="card shadow mb-4 col-lg-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Tambah Desa / Kelurahan</h6>
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

            <form action="{{ route('villages.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Nama Desa / Kelurahan</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="form-control" placeholder="Masukkan nama desa/kelurahan" required autofocus>
                </div>

                <div class="form-group">
                    <label for="district_id">Kecamatan</label>
                    <select id="district_id" name="district_id" class="form-control" required>
                        <option value="">-- Pilih Kecamatan --</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}">
                                {{ $district->name }} — {{ $district->regency->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('villages.index') }}" class="btn btn-secondary">
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
