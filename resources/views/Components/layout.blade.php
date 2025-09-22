<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Landing Page | Nicolites Portal</title>
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
                <img src="{{ Vite::asset('resources/assets/images/nms logo.png') }}" alt="logo" class="nav__logo"/>
                
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
           <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-3">
                    <li class="nav-item">
                        <a href="/" class="btn btn-custom me-2 {{ request()->is('/') ? 'active' : '' }}">Home</a>
                    </li>

                    {{-- Excluded Button --}}
                    
                    <li class="nav-item dropdown-elegant">
                        <a href="#" class="btn btn-custom me-2 dropdown-toggle {{ request()->is('enroll*') || request()->is('portal*') || request()->is('login*') ? 'active' : '' }}">
                            Enrollment
                        </a>
                        <div class="dropdown-menu">
                            <a href="/enroll" class="dropdown-item {{ request()->is('enroll*') ? 'active' : '' }}">
                                <i class="ri-user-add-line"></i>
                                Student Enrollment
                            </a>
                            {{-- <a href="#" class="dropdown-item #">
                                <i class="ri-discuss-line"></i>
                                Enrollment Instruction
                            </a>
                            <a href="#" class="dropdown-item #">
                                <i class="ri-file-paper-line"></i>
                                Enrollment Requirements
                            </a> --}}
                            
                            {{-- <a href="/portal/parent" class="dropdown-item {{ request()->is('portal/parent*') ? 'active' : '' }}">
                                <i class="ri-parent-line"></i>
                                Parent Portal
                            </a> To be use later or not (draft) --}}
                        </div>
                    </li>

                    <li class="nav-item dropdown-elegant">
                        <a href="#" class="btn btn-custom me-2 dropdown-toggle {{  request()->is('portal*') || request()->is('login*') ? 'active' : '' }}">
                            Portal
                        </a>
                        <div class="dropdown-menu">
                            {{-- <a href="/enroll" class="dropdown-item {{ request()->is('enroll*') ? 'active' : '' }}">
                                <i class="ri-user-add-line"></i>
                                Student Enrollment
                            </a> --}}
                            <a href="/student/login" class="dropdown-item {{ request()->is('/student/login*') ? 'active' : '' }}">
                                <i class="ri-graduation-cap-line"></i>
                                Student
                            </a>
                            {{-- <div class="dropdown-divider"></div> --}}
                            <a href="teacher/login" class="dropdown-item {{ request()->is('teacher/login*') ? 'active' : '' }}">
                                <i class="ri-user-star-line"></i>
                                Teacher
                            </a>
                            <a href="/guidance/login" class="dropdown-item {{ request()->is('/guidance/login') ? 'active' : '' }}">
                                <i class="ri-building-4-line"></i>
                                Guidance & Discipline 
                            </a>
                            <a href="/admin/login" class="dropdown-item {{ request()->is('/admin/login') ? 'active' : '' }}">
                                <i class="ri-admin-line"></i>
                                Registrar & Administration
                            </a>
                            {{-- <a href="/portal/parent" class="dropdown-item {{ request()->is('portal/parent*') ? 'active' : '' }}">
                                <i class="ri-parent-line"></i>
                                Parent Portal
                            </a> To be use later or not (draft) --}}
                        </div>
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
                    {{-- <h5 class="text-white fw-bold mb-3">Footer</h5> --}}
                    <p class="footer-text">
                        All Rights Reserved / Privacy Policy Nasugbu, Batangas Philippines.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="social-links">
                        <a href="https://www.facebook.com/NicolitesMontessoriSchool/" target="_blank" rel="noopener noreferrer" class="text-decoration-none me-3" aria-label="Facebook">
                            <i class="ri-facebook-fill"></i>
                        </a>
                        {{-- <a href="#" class="text-decoration-none me-3" aria-label="Instagram">
                            <i class="ri-instagram-line"></i>
                        </a>
                        <a href="#" class="text-decoration-none" aria-label="Twitter">
                            <i class="ri-twitter-fill"></i> WIP / lalagyan soon if ever may existing sila na accounts for that comment out social media--}}
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
