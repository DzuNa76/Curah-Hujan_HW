@extends('layouts.app')

@section('title', 'Edit Kecamatan')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Edit Kecamatan</h1>
    <p class="mb-4">Perbarui data kecamatan yang sudah ada.</p>

    @include('components.alert')

    <div class="card shadow mb-4 col-lg-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Kecamatan</h6>
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

            <form action="{{ route('districts.update', $district->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nama Kecamatan</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $district->name) }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="regency_id">Kabupaten / Kota</label>
                    <select id="regency_id" name="regency_id" class="form-control" required>
                        @foreach ($regencies as $regency)
                            <option value="{{ $regency->id }}"
                                {{ $district->regency_id == $regency->id ? 'selected' : '' }}>
                                {{ $regency->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('districts.index') }}" class="btn btn-secondary">
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
