<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Discipline Portal | Nicolites Montessori School</title>

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
        <div class="text-center mb-4">
          <img src="{{ Vite::asset('resources/assets/images/nms logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">
        </div>
        
        <!-- User Info -->
        {{-- <div class="user-info">
          <div class="user-name">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</div>
          <div class="user-role">{{ Auth::check() ? ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) : 'Discipline Portal' }}</div>
        </div> --}}

        <!-- Navigation -->
        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('discipline.dashboard') ? 'active' : '' }}" href="{{ route('discipline.dashboard') }}">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('discipline.students.*') ? 'active' : '' }}" href="{{ route('discipline.students.index') }}">
              <i class="ri-team-line me-2"></i>Student Profiles
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('discipline.violations.*') ? 'active' : '' }}" href="{{ route('discipline.violations.index') }}">
              <i class="ri-alert-line me-2"></i>Violations Management
            </a>
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
      {{ $slot }}
        @stack('scripts')
    </div>
  </div>
</body>
</html>