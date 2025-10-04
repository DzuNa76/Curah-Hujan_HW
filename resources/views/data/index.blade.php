@extends('layouts.app')

@section('title', 'Data Curah Hujan')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Data Curah Hujan</h1>
    <p class="mb-4">
        Halaman ini menampilkan data curah hujan bulanan beserta jumlah hari hujan.
        Anda dapat menambahkan data baru, mengedit, atau menghapus data yang sudah ada.
    </p>

    {{-- alert success 5 second --}}
    @include('components.alert')

    <!-- DataTables Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Curah Hujan</h6>
            <a href="{{ route('rainfall.create') }}" class="btn btn-sm btn-primary">Tambah Data</a>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="rainfallTable" width="100%" cellspacing="0">
                    <thead >
                        <tr>
                            <th>No</th>
                            <th>Bulan - Tahun</th>
                            <th>Curah Hujan (mm)</th>
                            <th>Hari Hujan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rainfallData as $data)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($data->month_year)->translatedFormat('F Y') }}</td>
                            <td>{{ number_format($data->rainfall_amount, 2) }}</td>
                            <td>{{ $data->rain_days }}</td>
                            <td class="text-center">
                                <a href="{{ route('rainfall.edit', $data->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button type="button" class="btn btn-danger btn-sm btn-delete"
                                        data-action="{{ route('rainfall.destroy', $data->id) }}">
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
