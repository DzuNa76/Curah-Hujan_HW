@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="card col-lg-4 mx-auto">
    <div class="card-body px-5 py-5">
        <h3 class="card-title text-left mb-3">Register</h3>

        <form method="POST" action="{{ route('register.perform') }}">
            @csrf

            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" class="form-control p_input" 
                    value="{{ old('name') }}" required>
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" class="form-control p_input" 
                    value="{{ old('email') }}" required>
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" class="form-control p_input" required>
                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="password_confirmation" class="form-control p_input" required>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-block enter-btn">Register</button>
            </div>

            <p class="sign-up text-center mt-3">Already have an Account? 
                <a href="{{ route('login') }}">Login</a>
            </p>
        </form>
    </div>
</div>
@endsection
