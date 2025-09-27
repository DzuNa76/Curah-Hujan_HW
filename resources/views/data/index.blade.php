@extends('layouts.app')

@section('title', 'Data Curah Hujan')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title d-flex justify-content-between align-items-center">
                    <span>Data Curah Hujan</span>
                    <a href="{{ route('rainfall.create') }}" class="btn btn-primary">Tambah Data</a>
                </h4>

                <!-- Pesan sukses -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="table-bordered">
                    <table class="table table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>Bulan-Tahun</th>
                                <th>Curah Hujan (mm)</th>
                                <th>Hari Hujan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rainfallData as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($data->month_year)->translatedFormat('F Y') }}</td>
                                    <td>{{ $data->rainfall_amount }}</td>
                                    <td>{{ $data->rain_days }}</td>
                                    <td>
                                        <a href="{{ route('rainfall.edit', $data->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('rainfall.destroy', $data->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada data curah hujan</td>
                                </tr>
                            @endforelse
                        </tbody>                        
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
