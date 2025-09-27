@extends('layouts.app')

@section('title', 'Edit Data Curah Hujan')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Data Curah Hujan</h4>

                <form action="{{ route('rainfall.update', $rainfall->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                
                    <div class="form-group">
                        <label>Bulan-Tahun</label>
                        <input type="month" name="monthYear" class="form-control" value="{{ \Carbon\Carbon::parse($rainfall->date)->format('Y-m') }}" required>
                    </div>
                
                    <div class="form-group">
                        <label>Curah Hujan (mm)</label>
                        <input type="number" name="rainfall_amount" class="form-control" value="{{ $rainfall->rainfall_amount }}" required>
                    </div>
                
                    <div class="form-group">
                        <label>Hari Hujan</label>
                        <input type="number" name="rain_days" class="form-control" value="{{ $rainfall->rain_days }}" required>
                    </div>
                
                    <button type="submit" class="btn btn-success">Update</button>
                    <a href="{{ route('rainfall.index') }}" class="btn btn-light">Batal</a>
                </form>                

            </div>
        </div>
    </div>
</div>
@endsection
