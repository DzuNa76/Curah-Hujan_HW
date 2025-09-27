@extends('layouts.app')

@section('title', 'Tambah Data Curah Hujan')

@section('content')
<div class="row">
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Tambah Data Curah Hujan</h4>

                <form action="{{ route('rainfall.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Bulan dan Tahun</label>
                        <input type="month" id="monthYear" name="monthYear" class="form-control" required autofocus>
                    </div>                    

                    <div class="form-group">
                        <label>Curah Hujan (mm)</label>
                        <input type="number" name="rainfall_amount" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Hari Hujan</label>
                        <input type="number" name="rain_days" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('rainfall.index') }}" class="btn btn-light">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

