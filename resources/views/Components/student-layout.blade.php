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
  
  <!-- Student JavaScript - Load in HEAD for onclick handlers -->
  @vite('resources/js/student-grades.js')
  @vite(['resources/css/index_student.css'])
  @vite('resources/js/student-enrollment.js')

  <style>
    .nav-link.disabled {
      cursor: not-allowed !important;
      opacity: 0.6;
      pointer-events: none;
    }
    
    .nav-link.disabled:hover {
      background-color: transparent !important;
      color: #6c757d !important;
    }
    
    .nav-link .ri-lock-line {
      font-size: 0.875rem;
      opacity: 0.7;
    }
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

        @php
          $currentStudent = Auth::guard('student')->user();
          $hasNoEnrollment = !$currentStudent || !in_array($currentStudent->enrollment_status, ['enrolled', 'pre_registered']);
          
          // Check if student has at least one confirmed payment (1st quarter)
          $hasConfirmedPayment = $currentStudent ? \App\Models\Payment::where('payable_type', 'App\\Models\\Student')
              ->where('payable_id', $currentStudent->id)
              ->where('confirmation_status', 'confirmed')
              ->exists() : false;
          
          $hasNoPayment = !$currentStudent || $currentStudent->enrollment_status !== 'enrolled' || !$hasConfirmedPayment;
          $isEnrollmentComplete = $currentStudent && in_array($currentStudent->enrollment_status, ['enrolled', 'pre_registered']);
          $isPaymentSettled = $currentStudent && $currentStudent->enrollment_status === 'enrolled' && $hasConfirmedPayment;
        @endphp

        <ul class="nav flex-column">
          <!-- Always accessible -->
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          
          <!-- Step 1: Enrollment (always accessible) -->
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('student.enrollment') ? 'active' : '' }}" href="{{ route('student.enrollment') }}">
              <i class="ri-file-list-3-line me-2"></i>Enrollment
            </a>
          </li>
          
          <!-- Step 2: Payments (disabled until payment settled - same as other features) -->
          <li class="nav-item mb-2">
            @if($hasNoPayment)
              <span class="nav-link disabled text-muted" title="Complete enrollment and settle payment to access this feature">
                <i class="ri-money-dollar-circle-line me-2"></i>Payments
                <i class="ri-lock-line ms-auto"></i>
              </span>
            @else
              <a class="nav-link {{ request()->routeIs('student.payments') ? 'active' : '' }}" href="{{ route('student.payments') }}">
                <i class="ri-money-dollar-circle-line me-2"></i>Payments
              </a>
            @endif
          </li>
          
          <!-- Step 3: Other features (disabled only if payment not settled) -->
          <li class="nav-item mb-2">
            @if($hasNoPayment)
              <span class="nav-link disabled text-muted" title="Complete enrollment and settle payment to access this feature">
                <i class="ri-book-open-line me-2"></i>Subjects
                <i class="ri-lock-line ms-auto"></i>
              </span>
            @else
              <a class="nav-link {{ request()->routeIs('student.subjects') ? 'active' : '' }}" href="{{ route('student.subjects') }}">
                <i class="ri-book-open-line me-2"></i>Subjects
              </a>
            @endif
          </li>
          
          <li class="nav-item mb-2">
            @if($hasNoPayment)
              <span class="nav-link disabled text-muted" title="Complete enrollment and settle payment to access this feature">
                <i class="ri-user-smile-line me-2"></i>ID Capturing
                <i class="ri-lock-line ms-auto"></i>
              </span>
            @else
              <a class="nav-link {{ request()->routeIs('student.face-registration') ? 'active' : '' }}" href="{{ route('student.face-registration') }}">
                <i class="ri-user-smile-line me-2"></i>ID Capturing
              </a>
            @endif
          </li>
          
          <li class="nav-item mb-2">
            @if($hasNoPayment)
              <span class="nav-link disabled text-muted" title="Complete enrollment and settle payment to access this feature">
                <i class="ri-file-text-line me-2"></i>Grades
                <i class="ri-lock-line ms-auto"></i>
              </span>
            @else
              <a class="nav-link {{ request()->routeIs('student.grades.*') ? 'active' : '' }}" href="{{ route('student.grades.index') }}">
                <i class="ri-file-text-line me-2"></i>Grades
              </a>
            @endif
          </li>
          
          <li class="nav-item mb-2">
            @if($hasNoPayment)
              <span class="nav-link disabled text-muted" title="Complete enrollment and settle payment to access this feature">
                <i class="ri-flag-line me-2"></i>Violations
                <i class="ri-lock-line ms-auto"></i>
              </span>
            @else
              <a class="nav-link {{ request()->routeIs('student.violations') ? 'active' : '' }}" href="{{ route('student.violations') }}">
                <i class="ri-flag-line me-2"></i>Violations
              </a>
            @endif
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
