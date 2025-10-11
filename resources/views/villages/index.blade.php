@extends('layouts.app')

@section('title', 'Daftar Desa / Kelurahan')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Daftar Desa / Kelurahan</h1>
    <p class="mb-4">
        Kelola daftar desa atau kelurahan yang termasuk dalam setiap kecamatan.
        Anda dapat menambahkan, mengedit, atau menghapus data.
    </p>

    @include('components.alert')

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Desa / Kelurahan</h6>
            <a href="{{ route('villages.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Tambah Desa
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama Desa / Kelurahan</th>
                            <th>Kecamatan</th>
                            <th>Kabupaten / Kota</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($villages as $village)
                        <tr>
                            <td>{{ $village->id }}</td>
                            <td>{{ $village->name }}</td>
                            <td>{{ $village->district->name ?? '-' }}</td>
                            <td>{{ $village->district->regency->name ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('villages.edit', $village->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                <button type="button" class="btn btn-danger btn-sm btn-delete"
                                        data-action="{{ route('villages.destroy', $village->id) }}">
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
