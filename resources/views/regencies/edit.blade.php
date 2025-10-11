@extends('layouts.app')

@section('title', 'Edit Kabupaten / Kota')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Edit Kabupaten / Kota</h1>
    <p class="mb-4">Perbarui nama kabupaten/kota.</p>

    @include('components.alert')

    <div class="card shadow mb-4 col-lg-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Kabupaten / Kota</h6>
        </div>
        <div class="card-body">
            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('regencies.update', $regency->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nama Kabupaten / Kota</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $regency->name) }}" class="form-control" placeholder="Masukkan nama kabupaten/kota" required autofocus>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('regencies.index') }}" class="btn btn-secondary">
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
