@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="card col-lg-4 mx-auto">
    <div class="card-body px-5 py-5">
        <h3 class="card-title text-left mb-3">Login</h3>

        <!-- Form Login -->
        <form method="POST" action="{{ route('login.perform') }}">
            @csrf

            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" class="form-control p_input" 
                    value="{{ old('email') }}" required autofocus>
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

            <div class="form-group d-flex align-items-center justify-content-between">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" name="remember"> Remember me
                    </label>
                </div>
                <a href="{{ route('password.request') }}" class="forgot-pass">Forgot password</a>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-block enter-btn">Login</button>
            </div>

            <p class="sign-up text-center mt-3">Don't have an Account?
                <a href="{{ route('register') }}"> Sign Up</a>
            </p>
        </form>
    </div>
</div>
@endsection
