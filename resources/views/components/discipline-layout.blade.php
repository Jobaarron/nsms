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
  @vite(['resources/css/collapsible-sidebar.css'])
  @vite(['resources/js/collapsible-sidebar.js'])
</head>
<body>
  <!-- Sidebar Toggle Button (Desktop & Mobile) -->
  <button class="sidebar-toggle d-md-block" type="button" title="Toggle Sidebar">
    <i class="ri-menu-fold-line"></i>
  </button>

  <!-- SIDEBAR -->
  <nav class="sidebar py-4 bg-white border-end">
    <!-- School Logo -->
    <div class="text-center mb-4">
      <img src="{{ Vite::asset('resources/assets/images/edusphere-logo.png.png') }}" alt="logo" class="sidebar-logo nav__logo" style="height: 50px; transition: all 0.3s ease;">
    </div>
        
        <!-- User Info -->
        {{-- <div class="user-info">
          <div class="user-name">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</div>
          <div class="user-role">{{ Auth::check() ? ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) : 'Discipline Portal' }}</div>
        </div> --}}

        <!-- Navigation -->
        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('discipline.dashboard') ? 'active' : '' }}" href="{{ route('discipline.dashboard') }}" title="Dashboard">
              <i class="ri-dashboard-line me-2"></i><span>Dashboard</span>
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('discipline.students.*') ? 'active' : '' }}" href="{{ route('discipline.students.index') }}" title="Student Profiles">
              <i class="ri-team-line me-2"></i><span>Student Profiles</span>
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('discipline.violations.*') ? 'active' : '' }}" href="{{ route('discipline.violations.index') }}" title="Violations Management">
              <i class="ri-alert-line me-2"></i><span>Violations Management</span>
            </a>
          </li>
          
          
          <li class="nav-item mt-3">
            <form class="logout-form" method="POST" action="{{ route('discipline.logout') }}">
              @csrf
              <button type="submit" class="btn btn-logout w-100" title="Logout">
                <i class="ri-logout-circle-line me-2"></i><span>Logout</span>
              </button>
            </form>
          </li>
        </ul>
      </nav>
      
      
  <!-- MAIN CONTENT -->
  <div class="main-content-wrapper">
    <main class="px-3 px-md-4 py-4">
      {{ $slot }}
      @stack('scripts')
    </main>
  </div>
</body>
</html>