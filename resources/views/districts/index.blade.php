@extends('layouts.app')

@section('title', 'Daftar Kecamatan')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Daftar Kecamatan</h1>
    <p class="mb-4">
        Kelola daftar kecamatan yang berada di setiap kabupaten/kota.
        Anda dapat menambah, mengedit, atau menghapus data yang sudah ada.
    </p>

    @include('components.alert')

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Kecamatan</h6>
            <a href="{{ route('districts.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Tambah Kecamatan
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama Kecamatan</th>
                            <th>Kabupaten / Kota</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($districts as $district)
                        <tr>
                            <td>{{ $district->id }}</td>
                            <td>{{ $district->name }}</td>
                            <td>{{ $district->regency->name ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('districts.edit', $district->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                <button type="button" class="btn btn-danger btn-sm btn-delete"
                                        data-action="{{ route('districts.destroy', $district->id) }}">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('components.delete-modal')
@endsection
