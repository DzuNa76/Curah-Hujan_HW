@extends('layouts.app')

@section('title', 'Data Curah Hujan')

@section('content')
<div class="row">
    <!-- Card Deskripsi -->
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Data Curah Hujan</h4>
                <p class="card-description">
                    Halaman ini menampilkan data curah hujan bulanan beserta jumlah hari hujan.
                    Anda dapat menambahkan data baru, mengedit, atau menghapus data yang sudah ada.
                </p>
                <a href="{{ route('rainfall.create') }}" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-plus-circle"></i> Tambah Data
                </a>
            </div>
        </div>
    </div>

    <!-- Card Tabel -->
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="rainfallTable" class="table table-striped table-hover">
                        <thead class="table-dark">
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
                                    <a href="{{ route('rainfall.edit', $data->id) }}" class="btn btn-warning btn-sm"><i class="mdi mdi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('rainfall.destroy', $data->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Hapus data ini?')">
                                            <i class="mdi mdi-delete"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('components.datatables')

@push('scripts')
<script>
    $(document).ready(function(){
        initDataTable('rainfallTable');
    });
</script>
@endpush
