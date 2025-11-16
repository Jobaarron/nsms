<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Registrar Portal | Nicolites Portal</title>

    <!-- Remix Icons -->
    <link 
    href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" 
    rel="stylesheet"
    />

    <!-- Google Font -->
    <link 
    href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" 
    rel="stylesheet"
    />

  
  @vite('resources/sass/app.scss')
  @vite(['resources/css/index_registrar.css'])
  @vite(['resources/js/app.js'])
  @vite(['resources/js/registrar-class-lists.js'])
  @vite(['resources/js/registrar-dashboard.js'])
  @vite(['resources/js/registrar-applicant-archives.js'])

</head>
<body>
  <!-- Mobile Navigation Toggle -->
  <div class="d-md-none bg-white border-bottom p-3 fixed-top" style="z-index: 1030;">
    <div class="d-flex justify-content-between align-items-center">
      <img src="{{ Vite::asset('resources/assets/images/nms-logo.png') }}" alt="Nicolites Montessori School" style="height: 30px;">
      <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#registrarSidebar" aria-controls="registrarSidebar">
        <i class="ri-menu-line"></i>
      </button>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row">
      
      <!-- SIDEBAR -->
      <nav class="col-12 col-md-2 sidebar d-none d-md-block py-4">
        <!-- School Logo -->
        <div class="text-center mb-3">
          <img src="{{ Vite::asset('resources/assets/images/nms-logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">
        </div>
        
        <!-- User Info -->
        {{-- <div class="user-info">
          <div class="user-name">{{ auth('registrar')->user()->name ?? 'Registrar' }}</div>
          <div class="user-role">Registrar</div>
        </div> --}}

        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('registrar.dashboard') ? 'active' : '' }}" href="{{ route('registrar.dashboard') }}">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('registrar.applications') ? 'active' : '' }}" href="{{ route('registrar.applications') }}">
              <i class="ri-file-list-line me-2"></i>Applications
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('registrar.class-lists') ? 'active' : '' }}" href="{{ route('registrar.class-lists') }}">
              <i class="ri-group-line me-2"></i>Class Lists
            </a>
          </li>
           <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('registrar.applicant-archives') ? 'active' : '' }}" href="{{ route('registrar.applicant-archives') }}">
              <i class="ri-archive-line me-2"></i>Applicant Archives
            </a>
          </li> 
        </ul>
        
        <!-- LOGOUT SECTION -->
        <div class="mt-auto pt-3">
          <form action="{{ route('registrar.logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start d-flex align-items-center" style="font-weight: 600;">
              <i class="ri-logout-circle-line me-2"></i>Logout
            </button>
          </form>
        </div>
      </nav>

      <!-- MOBILE SIDEBAR (Offcanvas) -->
      <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="registrarSidebar" aria-labelledby="registrarSidebarLabel">
        <div class="offcanvas-header border-bottom">
          <div class="d-flex align-items-center">
            <img src="{{ Vite::asset('resources/assets/images/nms-logo.png') }}" alt="Nicolites Montessori School" style="height: 30px;" class="me-2">
            <h5 class="offcanvas-title mb-0" id="registrarSidebarLabel">Registrar Portal</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
          <ul class="nav flex-column">
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('registrar.dashboard') ? 'active' : '' }}" href="{{ route('registrar.dashboard') }}">
                <i class="ri-dashboard-line me-2"></i>Dashboard
              </a>
            </li>
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('registrar.applications') ? 'active' : '' }}" href="{{ route('registrar.applications') }}">
                <i class="ri-file-list-3-line me-2"></i>Applications
              </a>
            </li>
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('registrar.class-lists') ? 'active' : '' }}" href="{{ route('registrar.class-lists') }}">
                <i class="ri-group-line me-2"></i>Class Lists
              </a>
            </li>
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('registrar.applicant-archives') ? 'active' : '' }}" href="{{ route('registrar.applicant-archives') }}">
                <i class="ri-archive-line me-2"></i>Applicant Archives
              </a>
            </li>
            <li class="nav-item mt-3">
              <form method="POST" action="{{ route('registrar.logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100">
                  <i class="ri-logout-box-line me-2"></i>Logout
                </button>
              </form>
            </li>
          </ul>
        </div>
      </div>

      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 px-3 px-md-4 py-4" style="margin-top: 70px;">
        <div class="d-md-none mb-3"></div>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2 section-title">
            <i class="ri-building-line me-2"></i>
            Registrar Portal
          </h1>
          <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
              <span class="badge bg-primary">
                {{ now()->format('M d, Y') }}
              </span>
            </div>
          </div>
        </div>

        {{ $slot }}
      </main>
    </div>
  </div>
</body>
</html>
