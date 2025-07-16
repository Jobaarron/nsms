<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login • NSMS</title>
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet"/>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"/>
    
    <!-- App CSS & JS (includes Bootstrap 5 via Vite) -->
    @vite(['resources/sass/app.scss','resources/js/app.js'])
    @vite(['resources/css/login_admin.css'])
    <style>
        
    </style>
</head>
<body>
    @php
        if(Auth::check()) {
                header('Location: ' . route('admin.dashboard'));
                exit;
            }
        @endphp
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0 text-center">
                            <i class="ri-admin-line me-2"></i>Admin Login
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        @if(session('error'))
                            <div class="alert alert-danger mb-4">
                                {{ session('error') }}
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('admin.login.submit') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember Me</label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="ri-login-box-line me-2"></i>Login
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="/" class="text-decoration-none text-primary">
                        <i class="ri-home-line me-1"></i>Return to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
