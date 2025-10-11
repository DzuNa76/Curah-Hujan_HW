@extends('layouts.app')

@section('title', 'Daftar Kabupaten / Kota')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Daftar Kabupaten / Kota</h1>
    <p class="mb-4">Kelola daftar kabupaten/kota. Anda dapat menambah, mengubah atau menghapus entri.</p>

    @include('components.alert')

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Kabupaten / Kota</h6>
            <a href="{{ route('regencies.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Tambah Kabupaten
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama Kabupaten / Kota</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($regencies as $regency)
                        <tr>
                            <td>{{ $regency->id }}</td>
                            <td>{{ $regency->name }}</td>
                            <td class="text-center">
                                <a href="{{ route('regencies.edit', $regency->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                <button type="button" class="btn btn-danger btn-sm btn-delete"
                                        data-action="{{ route('regencies.destroy', $regency->id) }}">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>

                                @include('components.delete-modal')
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
