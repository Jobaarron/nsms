<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Faculty Head Portal | Nicolites Portal</title>

  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet"/>

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"/>

  <!-- App CSS & JS (includes Bootstrap 5 via Vite) -->
  @vite(['resources/sass/app.scss','resources/js/app.js'])
  @vite(['resources/css/index_faculty_head.css'])

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
          <div class="user-name">{{ Auth::guard('faculty_head')->user()->full_name ?? 'Faculty Head' }}</div>
          <div class="user-role">Faculty Head</div>
        </div>
        
        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('faculty-head.dashboard') ? 'active' : '' }}" href="{{ route('faculty-head.dashboard') }}">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('faculty-head.assign-adviser.*') ? 'active' : '' }}" href="{{ route('faculty-head.assign-adviser') }}">
              <i class="ri-user-star-line me-2"></i>Assign Adviser per Class
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('faculty-head.assign-teacher.*') ? 'active' : '' }}" href="{{ route('faculty-head.assign-teacher') }}">
              <i class="ri-user-settings-line me-2"></i>Assign Teacher per Subject/Section
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('faculty-head.view-grades.*') ? 'active' : '' }}" href="{{ route('faculty-head.view-grades') }}">
              <i class="ri-file-list-3-line me-2"></i>View Submitted Grades
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('faculty-head.approve-grades.*') ? 'active' : '' }}" href="{{ route('faculty-head.approve-grades') }}">
              <i class="ri-checkbox-circle-line me-2"></i>Approve/Reject Grades
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('faculty-head.activate-submission.*') ? 'active' : '' }}" href="{{ route('faculty-head.activate-submission') }}">
              <i class="ri-play-circle-line me-2"></i>Activate Grade Submission
            </a>
          </li>

          <li class="nav-item mt-3">
            <form method="POST" action="{{ route('faculty-head.logout') }}">
              @csrf
              <button type="submit" class="btn btn-logout w-100">
                <i class="ri-logout-circle-line me-2"></i>Logout
              </button>
            </form>
          </li>
        </ul>
      </nav>

      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 ms-sm-auto px-md-4">
        <div class="main-content py-4">
          {{ $slot }}
        </div>
      </main>
    </div>
  </div>

  @stack('scripts')
</body>
</html>
