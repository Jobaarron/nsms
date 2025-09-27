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

  <title>Student Portal | Nicolites Portal</title>

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
  @vite('resources/sass/app.scss')
  @vite(['resources/css/index_student.css'])

  <style>
   
  </style>
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
          <div class="user-name">{{ Auth::user()->name }}</div>
          <div class="user-role">Student</div>
        </div>

        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          {{-- <li class="nav-item mb-2">
            @if($student->canAccessFeatures())
              <a class="nav-link" href="#">
                <i class="ri-cash-line me-2"></i>Payments
              </a>
            @else
              <span class="nav-link disabled d-flex justify-content-between align-items-center">
                <span><i class="ri-cash-line me-2"></i>Payments</span>
                <small class="badge bg-warning text-dark">Pay First</small>
              </span>
            @endif
          </li> --}}
          {{-- <li class="nav-item mb-2">
            @if($student->canAccessFeatures())
              <a class="nav-link" href="#">
                <i class="ri-book-open-line me-2"></i>My Subjects
              </a>
            @else
              <span class="nav-link disabled d-flex justify-content-between align-items-center">
                <span><i class="ri-book-open-line me-2"></i>My Subjects</span>
                <small class="badge bg-warning text-dark">Pay First</small>
              </span>
            @endif
          </li> --}}
          {{-- <li class="nav-item mb-2">
            @if($student->canAccessFeatures())
              <a class="nav-link" href="#">
                <i class="ri-file-paper-line me-2"></i>Guidance Notes
              </a>
            @else
              <span class="nav-link disabled d-flex justify-content-between align-items-center">
                <span><i class="ri-file-paper-line me-2"></i>Guidance Notes</span>
                <small class="badge bg-warning text-dark">Pay First</small>
              </span>
            @endif
          </li> --}}
          <li class="nav-item mb-2">
            {{-- @if($student->canAccessFeatures()) --}}
              <a class="nav-link {{ request()->routeIs('student.violations') ? 'active' : '' }}" href="{{ route('student.violations') }}">
                <i class="ri-flag-line me-2"></i>Violations
              </a>
            {{-- @else --}}
              {{-- <span class="nav-link disabled d-flex justify-content-between align-items-center">
                <span><i class="ri-flag-line me-2"></i>Discipline</span>
                <small class="badge bg-warning text-dark">Pay First</small>
              </span> --}}
            {{-- @endif --}}
          </li>
          {{-- <li class="nav-item">
            @if($student->canAccessFeatures())
              <a class="nav-link" href="#">
                <i class="ri-user-line me-2"></i>Profile
              </a>
            @else
              <span class="nav-link disabled d-flex justify-content-between align-items-center">
                <span><i class="ri-user-line me-2"></i>Profile</span>
                <small class="badge bg-warning text-dark">Pay First</small>
              </span>
            @endif
          </li> --}}
        </ul>
        
        <!-- LOGOUT SECTION -->
        <div class="mt-auto pt-3">
          <form action="{{ route('student.logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start d-flex align-items-center" style="font-weight: 600;">
              <i class="ri-logout-circle-line me-2"></i>Logout
            </button>
          </form>
        </div>
      </nav>

      {{ $slot }}
      </main>
    </div>
  </div>
</body>
</html>
