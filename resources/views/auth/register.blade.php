{{-- REGISTER PAGE --}}
@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
                            </div>
                            <form class="user" method="POST" action="{{ route('register') }}">
                                @csrf

                                <div class="form-group mb-4">
                                    <label for="name" class="form-label fw-semibold text-gray-800 mb-2">
                                        Full Name
                                    </label>
                                    <input type="text" name="name" id="name" class="form-control form-control-user @error('name') is-invalid @enderror" placeholder="Full Name" value="{{ old('name') }}" required autofocus>
                                    @error('name')
                                        <span class="text-danger small d-block mt-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="email" class="form-label fw-semibold text-gray-800 mb-2">
                                        Email Address
                                    </label>
                                    <input type="email" name="email" id="email" class="form-control form-control-user @error('email') is-invalid @enderror" placeholder="Email Address" value="{{ old('email') }}" required>
                                    @error('email')
                                        <span class="text-danger small d-block mt-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-6 mb-4">
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
                                    <div class="col-sm-6 mb-4">
                                        <label for="password_confirmation" class="form-label fw-semibold text-gray-800 mb-2">
                                            Repeat Password
                                        </label>
                                        <div class="input-group">
                                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-user" placeholder="Repeat Password" required>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    Register Account
                                </button>
                            </form>
                            <hr>
                            <div class="text-center">
                                <a class="small" href="{{ route('login') }}">Already have an account? Login!</a>
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