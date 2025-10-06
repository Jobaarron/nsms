<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Guidance & Discipline | Nicolites Portal</title>

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

  <!-- App CSS (includes Bootstrap 5 via Vite) -->
  @vite(['resources/sass/app.scss','resources/js/app.js'])
  @vite(['resources/css/index_discipline.css'])
  

</head>
<body>
  <div class="container-fluid">
    <div class="row">

      <!-- SIDEBAR -->
      <nav class="col-12 col-md-2 sidebar d-none d-md-block py-4">
        <!-- School Logo -->
        <div class="text-center mb-3">
          <img src="{{ Vite::asset('resources/assets/images/nms logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">
        </div>
        
        <!-- User Info -->
            <div class="user-info">
                <div class="user-name">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</div>
          <div class="user-role">{{ Auth::check() ? ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) : '' }}</div>
        </div>

        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('discipline.dashboard') ? 'active' : '' }}" href="{{ route('discipline.dashboard') }}">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          
            <li class="nav-item">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-12 col-md-2 sidebar p-3">
                <!-- Logo -->
                <div class="text-center mb-4">
                    <img src="{{ asset('images/nicolites-logo.png') }}" alt="Nicolites Logo" class="sidebar-logo">
                </div>
                
                <!-- User Info -->
                {{-- <div class="user-info">
                    <div class="user-name">{{ Auth::user()->discipline->full_name ?? Auth::user()->name ?? 'Discipline Staff' }}</div>
                    <div class="user-role">Discipline Portal</div>
                </div> --}}
                
                <!-- Navigation -->
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('discipline.dashboard') ? 'active' : '' }}" 
                           href="{{ route('discipline.dashboard') }}">
                            <i class="ri-dashboard-line me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('discipline.students.*') ? 'active' : '' }}" 
                           href="{{ route('discipline.students.index') }}">
                            <i class="ri-team-line me-2"></i>
                            <span>Student Profiles</span>
                        </a>
                    </li>
          
         <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('discipline.violations.*') ? 'active' : '' }}" 
                           href="{{ route('discipline.violations.index') }}">
                            <i class="ri-alert-line me-2"></i>
                            <span>Violations</span>
                        </a>
                    </li>
          
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-bar-chart-line me-2"></i>Analytics</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-settings-3-line me-2"></i>Settings</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          
          <li class="nav-item mt-3">
            <form method="POST" action="{{ route('discipline.logout') }}">
              @csrf
              <button type="submit" class="btn btn-logout w-100">
                <i class="ri-logout-circle-line me-2"></i>Logout
              </button>
            </form>
          </li>
        </ul>
      </nav>
      
      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 px-4 py-4">
        {{ $slot }}
      </main>
    </div>
  </div>
</body>
</html>