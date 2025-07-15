<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nicolites Portal</title>
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet" />
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">  
      <!-- Bootstrap 5 / CSS / JS -->
      @vite(['resources/sass/app.scss','resources/js/app.js'])
      @vite(['resources/css/landingpage.css'])
      @vite(['resources/js/landingpage.js'])
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                {{-- <img src="{{ asset('img/logos.png') }}" alt="logo" class="nav__logo"/> --}}
                Navbar Brand
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
           <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-3">
                    <li class="nav-item">
                        <a href="/" class="btn btn-custom me-2  {{ request()->is('/') ? 'active' : '' }}">Home</a>
                    </li>

                    {{-- Excluded Button --}}
                    
                    <li class="nav-item">
                        <a href="/enroll" class="btn btn-custom me-2 {{ request()->is('enroll*') ? 'active' : '' }}">Enroll</a>
                    </li>
                </ul>
            </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            {{ $slot }}
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-section mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-white fw-bold mb-3">Footer</h5>
                    <p class="footer-text">
                        Footer Message
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="social-links">
                        <a href="#" class="text-decoration-none me-3" aria-label="Facebook">
                            <i class="ri-facebook-fill"></i>
                        </a>
                        <a href="#" class="text-decoration-none me-3" aria-label="Instagram">
                            <i class="ri-instagram-line"></i>
                        </a>
                        <a href="#" class="text-decoration-none" aria-label="Twitter">
                            <i class="ri-twitter-fill"></i>
                        </a>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color: var(--secondary-color); opacity: 0.3;">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="footer-text mb-0">
                        © {{ date('Y') }} Nicolites Portal: Student Management System All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer> 
</body>
</html>
