@extends('layouts.app')

@section('title', 'Data Curah Hujan')

@section('content')
    <h1 class="h3 mb-2 text-gray-800">Data Curah Hujan</h1>
    <p class="mb-4">
        Halaman ini menampilkan data curah hujan bulanan beserta pos pengamatan dan jumlah hari hujan.
        Anda dapat menambahkan data baru, mengedit, atau menghapus data yang sudah ada.
    </p>

    @include('components.alert')

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Curah Hujan</h6>
            <a href="{{ route('rainfall.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Tambah Data
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Pos / Stasiun</th>
                            <th>Lokasi</th>
                            <th>Bulan - Tahun</th>
                            <th>Curah Hujan (mm)</th>
                            <th>Hari Hujan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rainfallData as $data)
                        <tr>
                            <td>{{ $data->station->station_name ?? '-' }}</td>
                            <td>
                                {{ $data->station->village->name ?? '-' }},
                                {{ $data->station->village->district->name ?? '-' }},
                                {{ $data->station->village->district->regency->name ?? '-' }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($data->date)->translatedFormat('F Y') }}</td>
                            <td>{{ number_format($data->rainfall_amount, 2) }}</td>
                            <td>{{ $data->rain_days }}</td>
                            <td class="text-center">
                                <a href="{{ route('rainfall.edit', [$data->station_id, $data->id]) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                <button type="button" 
                                        class="btn btn-danger btn-sm btn-delete"
                                        data-action="{{ route('rainfall.destroy', [$data->station_id, $data->id]) }}">
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
