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


  <title>Teacher Portal | Nicolites Portal</title>

  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet"/>

  <!-- App CSS & JS (includes Bootstrap 5 via Vite) -->
  @vite(['resources/sass/app.scss','resources/js/app.js'])
  @vite(['resources/css/index_teacher.css'])
  @vite('resources/js/teacher-dashboard.js')
  @vite('resources/js/teacher-recommend-counseling.js')
  @vite('resources/js/teacher-advisory.js')
  @vite('resources/js/teacher-grades.js')

  <style>
    .spin {
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
  </style>
  
</head>
<body>
  <!-- Mobile Navigation Toggle -->
  <div class="d-md-none bg-white border-bottom p-3 fixed-top" style="z-index: 1030;">
    <div class="d-flex justify-content-between align-items-center">
      <img src="{{ Vite::asset('resources/assets/images/nms logo.png') }}" alt="Nicolites Montessori School" style="height: 30px;">
      <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#teacherSidebar" aria-controls="teacherSidebar">
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
          <img src="{{ Vite::asset('resources/assets/images/nms logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">
        </div>
        
        <!-- User Info -->
        <!-- <div class="user-info">
          <div class="user-name">{{ Auth::user()->name }}</div>
          <div class="user-role">{{ ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) }}</div>
        </div> -->
        
        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('teacher.grades*') ? 'active' : '' }}" href="{{ route('teacher.grades') }}">
              <i class="ri-pencil-ruler-2-line me-2"></i>Submit Grades
            </a>
          </li>

          @php
            $currentUser = auth()->user();
            $currentTeacher = $currentUser && $currentUser->teacher ? $currentUser->teacher : null;
            $isClassAdviser = $currentTeacher ? $currentTeacher->isClassAdviser() : false;
          @endphp

          @if($isClassAdviser)
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('teacher.observationreport*') ? 'active' : '' }}" href="{{ route('teacher.observationreport') }}">
                <i class="ri-file-list-3-line me-2"></i>Observation Report
              </a>
            </li>
            
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('teacher.advisory') ? 'active' : '' }}" href="{{ route('teacher.advisory') }}">
                <i class="ri-user-star-line me-2"></i>Advisory
              </a>
            </li>
          @else
            <li class="nav-item mb-2">
              <span class="nav-link disabled" title="Only available for class advisers">
                <i class="ri-file-list-3-line me-2"></i>Observation Report
                <i class="ri-lock-line ms-auto text-muted"></i>
              </span>
            </li>
            
            <li class="nav-item mb-2">
              <span class="nav-link disabled" title="Only available for class advisers">
                <i class="ri-user-star-line me-2"></i>Advisory
                <i class="ri-lock-line ms-auto text-muted"></i>
              </span>
            </li>
          @endif
          
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('teacher.recommend-counseling.*') ? 'active' : '' }}" href="{{ route('teacher.recommend-counseling.form') }}">
              <i class="ri-heart-pulse-line me-2"></i>Recommend Counseling
            </a>
          </li>

          <li class="nav-item mt-3">
            <form method="POST" action="{{ route('teacher.logout') }}">
              @csrf
              <button type="submit" class="btn btn-logout w-100">
                <i class="ri-logout-circle-line me-2"></i>Logout
              </button>
            </form>
          </li>
        </ul>
      </nav>

      <!-- MOBILE SIDEBAR (Offcanvas) -->
      <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="teacherSidebar" aria-labelledby="teacherSidebarLabel">
        <div class="offcanvas-header border-bottom">
          <div class="d-flex align-items-center">
            <img src="{{ Vite::asset('resources/assets/images/nms logo.png') }}" alt="Nicolites Montessori School" style="height: 30px;" class="me-2">
            <h5 class="offcanvas-title mb-0" id="teacherSidebarLabel">Teacher Portal</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
          @php
            $currentUser = auth()->user();
            $currentTeacher = $currentUser && $currentUser->teacher ? $currentUser->teacher : null;
            $isClassAdviser = $currentTeacher ? $currentTeacher->isClassAdviser() : false;
          @endphp
          
          <ul class="nav flex-column">
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}">
                <i class="ri-dashboard-line me-2"></i>Dashboard
              </a>
            </li>
            
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('teacher.grades*') ? 'active' : '' }}" href="{{ route('teacher.grades') }}">
                <i class="ri-pencil-ruler-2-line me-2"></i>Submit Grades
              </a>
            </li>

            @if($isClassAdviser)
              <li class="nav-item mb-2">
                <a class="nav-link {{ request()->routeIs('teacher.observationreport*') ? 'active' : '' }}" href="{{ route('teacher.observationreport') }}">
                  <i class="ri-file-list-3-line me-2"></i>Observation Report
                </a>
              </li>
              
              <li class="nav-item mb-2">
                <a class="nav-link {{ request()->routeIs('teacher.advisory') ? 'active' : '' }}" href="{{ route('teacher.advisory') }}">
                  <i class="ri-user-star-line me-2"></i>Advisory
                </a>
              </li>
            @else
              <li class="nav-item mb-2">
                <span class="nav-link disabled" title="Only available for class advisers">
                  <i class="ri-file-list-3-line me-2"></i>Observation Report
                  <i class="ri-lock-line ms-auto text-muted"></i>
                </span>
              </li>
              
              <li class="nav-item mb-2">
                <span class="nav-link disabled" title="Only available for class advisers">
                  <i class="ri-user-star-line me-2"></i>Advisory
                  <i class="ri-lock-line ms-auto text-muted"></i>
                </span>
              </li>
            @endif
            
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('teacher.recommend-counseling.*') ? 'active' : '' }}" href="{{ route('teacher.recommend-counseling.form') }}">
                <i class="ri-heart-pulse-line me-2"></i>Recommend Counseling
              </a>
            </li>

            <li class="nav-item mt-3">
              <form method="POST" action="{{ route('teacher.logout') }}">
                @csrf
                <button type="submit" class="btn btn-logout w-100">
                  <i class="ri-logout-circle-line me-2"></i>Logout
                </button>
              </form>
            </li>
          </ul>
        </div>
      </div>

      <main class="col-12 col-md-10 px-3 px-md-4 py-4" style="margin-top: 70px;">
        <div class="d-md-none mb-3"></div>
        {{ $slot }}
      </main>
    </div>
  </div>
</body>
</html>