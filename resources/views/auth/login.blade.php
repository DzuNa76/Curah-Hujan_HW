{{-- LOGIN PAGE --}}
@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                            </div>
                            <form class="user" method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="form-group mb-4">
                                    <label for="email" class="form-label fw-semibold text-gray-800 mb-2">
                                        Email Address
                                    </label>
                                    <input type="email" name="email" id="email" class="form-control form-control-user @error('email') is-invalid @enderror" placeholder="Enter Email Address..." value="{{ old('email') }}" required autofocus>
                                    @error('email')
                                        <span class="text-danger small d-block mt-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="password" class="form-label fw-semibold text-gray-800 mb-2">
                                        Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control form-control-user @error('password') is-invalid @enderror" placeholder="Password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <span class="text-danger small d-block mt-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <div class="custom-control custom-checkbox small">
                                        <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                                        <label class="custom-control-label" for="remember">Remember Me</label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    Login
                                </button>
                            </form>
                            <hr>
                            <div class="text-center">
                                <a class="small" href="{{ route('password.request') }}">Forgot Password?</a>
                            </div>
                            <div class="text-center">
                                <a class="small" href="{{ route('register') }}">Create an Account!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility pada login
    const togglePassword = document.getElementById('togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }

    // Toggle password visibility pada register
    const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
    if (togglePasswordConfirm) {
        togglePasswordConfirm.addEventListener('click', function() {
            const passwordField = document.getElementById('password_confirmation');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }
});
</script>
@endsection