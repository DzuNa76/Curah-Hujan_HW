@extends('layouts.app')

@section('title', 'Daftar Stasiun / Pos Pengamatan')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Daftar Stasiun / Pos Pengamatan</h1>
    <p class="mb-4">
        Kelola daftar pos pengamatan iklim. Setiap stasiun terhubung dengan desa, kecamatan, dan kabupaten.
    </p>

    @include('components.alert')

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Stasiun / Pos Pengamatan</h6>
            <a href="{{ route('stations.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Tambah Stasiun
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama Stasiun</th>
                            <th>Desa / Kelurahan</th>
                            <th>Kecamatan</th>
                            <th>Kabupaten / Kota</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stations as $station)
                        <tr>
                            <td>{{ $station->id }}</td>
                            <td>{{ $station->station_name }}</td>
                            <td>{{ $station->village->name ?? '-' }}</td>
                            <td>{{ $station->village->district->name ?? '-' }}</td>
                            <td>{{ $station->village->district->regency->name ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('stations.edit', $station->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                {{-- <a href="{{ route('stations.print', $station->id) }}" target="_blank" class="btn btn-info btn-sm">
                                    <i class="fas fa-print"></i> Cetak Data
                                </a> --}}

                                <button type="button" class="btn btn-danger btn-sm btn-delete"
                                        data-action="{{ route('stations.destroy', $station->id) }}">
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
