<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
  
  <!-- Teacher JavaScript - Load in HEAD for onclick handlers -->
  @vite('resources/js/teacher-dashboard.js')

  
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- SIDEBAR -->
  <nav class="col-12 col-md-2 sidebar py-4">
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
          
          <!-- <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('teacher.schedule*') ? 'active' : '' }}" href="{{ route('teacher.schedule') }}">
              <i class="ri-calendar-line me-2"></i>Class Schedule
            </a>
          </li> -->
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('teacher.grades*') ? 'active' : '' }}" href="{{ route('teacher.grades') }}">
              <i class="ri-pencil-ruler-2-line me-2"></i>Submit Grades
            </a>
          </li>

          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('teacher.observationreport*') ? 'active' : '' }}" href="{{ route('teacher.observationreport') }}">
              <i class="ri-file-list-3-line me-2"></i>Observationreport
            </a>
          </li>
          
          <!-- <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('teacher.my-students') ? 'active' : '' }}" href="{{ route('teacher.my-students') }}">
              <i class="ri-team-line me-2"></i>My Students
            </a>
          </li> -->
          
          
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

      <main class="col-12 col-md-10 py-4">
        {{ $slot }}
      </main>
    </div>
  </div>
</body>
</html>