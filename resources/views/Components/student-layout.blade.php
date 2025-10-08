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
        {{-- <div class="user-info mb-4 p-3 bg-light rounded">
          <div class="d-flex align-items-center">
            <div class="avatar-circle me-3">
              <i class="ri-user-line"></i>
            </div>
            <div>
              <h6 class="mb-0">{{ Auth::guard('student')->user()->first_name ?? (Auth::guard('web')->user()->first_name ?? 'Student') }}</h6>
              <small class="text-muted">{{ Auth::guard('student')->user()->student_id ?? (Auth::guard('web')->user()->student_id ?? 'ID: N/A') }}</small>
            </div>
          </div>
        </div> --}}

        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('student.enrollment') ? 'active' : '' }}" href="{{ route('student.enrollment') }}">
              <i class="ri-file-list-3-line me-2"></i>Enrollment
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('student.subjects') ? 'active' : '' }}" href="{{ route('student.subjects') }}">
              <i class="ri-book-open-line me-2"></i>Subjects
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('student.payments') ? 'active' : '' }}" href="{{ route('student.payments') }}">
              <i class="ri-money-dollar-circle-line me-2"></i>Payments
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('student.face-registration') ? 'active' : '' }}" href="{{ route('student.face-registration') }}">
              <i class="ri-user-smile-line me-2"></i>Face Registration
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('student.violations') ? 'active' : '' }}" href="{{ route('student.violations') }}">
              <i class="ri-flag-line me-2"></i>Violations
            </a>
          </li>
         
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
