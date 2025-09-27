@extends('layouts.app')

@section('title', 'Data Curah Hujan')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title d-flex justify-content-between align-items-center">
                    <span>Data Curah Hujan</span>
                    <a href="{{ route('rainfall.create') }}" class="btn btn-sm btn-primary">
                        <i class="mdi mdi-plus"></i> Tambah Data
                    </a>
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
                                <th>Tanggal</th>
                                <th>Curah Hujan (mm)</th>
                                <th>Hari Hujan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</td>
                                    <td>{{ $item->rainfall_amount }}</td>
                                    <td>{{ $item->rain_days }}</td>
                                    <td>
                                        <a href="{{ route('rainfall.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                            <i class="mdi mdi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('rainfall.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="mdi mdi-delete"></i> Hapus
                                            </button>
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
