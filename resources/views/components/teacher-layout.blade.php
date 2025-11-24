<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Prevent sidebar flash by applying state immediately -->
  <script>
    (function() {
      try {
        const sidebarState = sessionStorage.getItem('sidebarState_teacher') || 'expanded';
        if (window.innerWidth > 767.98 && sidebarState === 'collapsed') {
          document.documentElement.classList.add('sidebar-collapsed-initial');
          document.documentElement.style.setProperty('--sidebar-width', '70px');
        } else {
          document.documentElement.style.setProperty('--sidebar-width', '250px');
        }
      } catch(e) {}
    })();
  </script>

  <title>Teacher Portal | Nicolites Portal</title>

  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet"/>

  <!-- App CSS & JS (includes Bootstrap 5 via Vite) -->
  @vite(['resources/sass/app.scss','resources/js/app.js'])
  @vite(['resources/css/index_teacher.css'])
  @vite(['resources/css/collapsible-sidebar.css'])
  @vite(['resources/js/teacher-dashboard.js'])
  @vite(['resources/js/teacher-advisory.js'])
  @vite(['resources/js/teacher-grades.js'])
  @vite(['resources/js/teacher-grade-entry.js'])
  @vite(['resources/js/collapsible-sidebar.js'])

  <style>
    .spin {
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
  </style>

  <!-- Page-specific data injection -->
  @stack('page-data')
  
</head>
<body>
  <!-- Sidebar Toggle Button (Desktop & Mobile) -->
  <button class="sidebar-toggle d-md-block" type="button">
    <i class="ri-menu-fold-line"></i>
  </button>

  <!-- SIDEBAR -->
  <nav class="sidebar py-4 bg-white border-end">
    <!-- School Logo -->
    <div class="text-center mb-3">
      <img src="{{ Vite::asset('resources/assets/images/nms-logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">
    </div>
        
        <!-- User Info -->
        <!-- <div class="user-info">
          <div class="user-name">{{ Auth::user()->name }}</div>
          <div class="user-role">{{ ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) }}</div>
        </div> -->
        
        <ul class="nav flex-column px-3">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}" title="Dashboard">
              <i class="ri-dashboard-line"></i><span>Dashboard</span>
            </a>
          </li>
          
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('teacher.grades*') ? 'active' : '' }}" href="{{ route('teacher.grades') }}" title="Submit Grades">
              <i class="ri-pencil-ruler-2-line me-2"></i><span>Submit Grades</span>
            </a>
          </li>

          @php
            $currentUser = auth()->user();
            $currentTeacher = $currentUser && $currentUser->teacher ? $currentUser->teacher : null;
            $isClassAdviser = $currentTeacher ? $currentTeacher->isClassAdviser() : false;
          @endphp

          @if($isClassAdviser)
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('teacher.observationreport*') ? 'active' : '' }}" href="{{ route('teacher.observationreport') }}" title="Observation Report">
                <i class="ri-file-list-3-line me-2"></i><span>Observation Report</span>
              </a>
            </li>
            
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('teacher.advisory') ? 'active' : '' }}" href="{{ route('teacher.advisory') }}" title="Advisory">
                <i class="ri-user-star-line me-2"></i><span>Advisory</span>
              </a>
            </li>
            
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('teacher.recommend-counseling.*') ? 'active' : '' }}" href="{{ route('teacher.recommend-counseling.form') }}" title="Recommend Counseling">
                <i class="ri-heart-pulse-line me-2"></i><span>Recommend Counseling</span>
              </a>
            </li>
          @else
            <li class="nav-item mb-2">
              <span class="nav-link disabled" title="Only available for class advisers">
                <i class="ri-file-list-3-line me-2"></i><span>Observation Report</span>
                <i class="ri-lock-line ms-auto text-muted"></i>
              </span>
            </li>
            
            <li class="nav-item mb-2">
              <span class="nav-link disabled" title="Only available for class advisers">
                <i class="ri-user-star-line me-2"></i><span>Advisory</span>
                <i class="ri-lock-line ms-auto text-muted"></i>
              </span>
            </li>
            
            <li class="nav-item mb-2">
              <span class="nav-link disabled" title="Only available for class advisers">
                <i class="ri-heart-pulse-line me-2"></i><span>Recommend Counseling</span>
                <i class="ri-lock-line ms-auto text-muted"></i>
              </span>
            </li>
          @endif

          <li class="nav-item mt-3">
            <form class="logout-form" method="POST" action="{{ route('teacher.logout') }}">
              @csrf
              <button type="submit" class="btn btn-logout w-100" title="Logout">
                <i class="ri-logout-circle-line me-2"></i><span>Logout</span>
              </button>
            </form>
          </li>
        </ul>
      </nav>

  <div class="main-content-wrapper">
    <main class="px-3 px-md-4 py-4">
      {{ $slot }}
    </main>
  </div>
</body>
</html>