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

  <!-- Prevent sidebar flash by applying state immediately -->
  <script>
    (function() {
      try {
        const sidebarState = sessionStorage.getItem('sidebarState_student') || 'expanded';
        if (window.innerWidth > 767.98 && sidebarState === 'collapsed') {
          document.documentElement.classList.add('sidebar-collapsed-initial');
          document.documentElement.style.setProperty('--sidebar-width', '70px');
        } else {
          document.documentElement.style.setProperty('--sidebar-width', '250px');
        }
      } catch(e) {}
    })();
  </script>

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


  @vite(['resources/sass/app.scss','resources/js/app.js'])
  @vite(['resources/css/index_student.css'])
  @vite(['resources/css/collapsible-sidebar.css'])
  @vite(['resources/js/face-registration.js'])
  @vite(['resources/js/student-dashboard.js'])
  @vite(['resources/js/student-grades.js'])
  @vite(['resources/js/student-subjects.js'])
  @vite(['resources/js/student-violation.js'])
  @vite(['resources/js/student-enrollment.js'])
  @vite(['resources/js/collapsible-sidebar.js'])
  @vite(['resources/js/student-payment-alerts.js'])
</head>
<body>
  <!-- Sidebar Toggle Button (Desktop & Mobile) -->
  <button class="sidebar-toggle d-md-block" type="button">
    <i class="ri-menu-fold-line"></i>
  </button>

  <!-- Mobile Navigation Toggle (Hidden - using sidebar-toggle instead) -->
  <div class="d-none bg-white border-bottom p-3 fixed-top" style="z-index: 1030;">
    <div class="d-flex justify-content-between align-items-center">
      <img src="{{ Vite::asset('resources/assets/images/nms-logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">
      <div class="d-flex gap-2">
        <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#studentSidebar" aria-controls="studentSidebar">
          <i class="ri-menu-line"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- SIDEBAR -->
  <nav class="sidebar py-4 bg-white border-end">
        <!-- School Logo -->
        <div class="text-center mb-3">
         <img src="{{ Vite::asset('resources/assets/images/nms-logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">
        </div>
        

        @php
          $currentStudent = Auth::guard('student')->user();
          // Only allow access if student is fully 'enrolled', not just 'pre_registered'
          $hasNoEnrollment = !$currentStudent || $currentStudent->enrollment_status !== 'enrolled';
          
          
          // Check if student has at least one confirmed payment (1st quarter)
          $hasConfirmedPayment = $currentStudent ? \App\Models\Payment::where('payable_type', 'App\\Models\\Student')
              ->where('payable_id', $currentStudent->id)
              ->where('confirmation_status', 'confirmed')
              ->exists() : false;
          
          $hasNoPayment = !$currentStudent || $currentStudent->enrollment_status !== 'enrolled' || !$hasConfirmedPayment;
          $isEnrollmentComplete = $currentStudent && in_array($currentStudent->enrollment_status, ['enrolled', 'pre_registered']);
          $isPaymentSettled = $currentStudent && $currentStudent->enrollment_status === 'enrolled' && $hasConfirmedPayment;
        @endphp

        <ul class="nav flex-column px-3">
         
          <li class="nav-item mb-2">
            @if($hasNoEnrollment)
              <span class="nav-link disabled" title="Complete enrollment first to access dashboard">
                <i class="ri-dashboard-line me-2"></i><span>Dashboard</span>
                <i class="ri-lock-line ms-auto"></i>
              </span>
            @else
              <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}" title="Dashboard">
                <i class="ri-dashboard-line me-2"></i><span>Dashboard</span>
              </a>
            @endif
          </li>
          
          
          @if($currentStudent && $currentStudent->enrollment_status !== 'enrolled')
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('student.enrollment') ? 'active' : '' }}" href="{{ route('student.enrollment') }}" title="Complete Enrollment">
                <i class="ri-file-list-3-line me-2"></i><span>Complete Enrollment</span>
              </a>
            </li>
          @endif
          
         
          <li class="nav-item mb-2">
            @if($hasNoEnrollment)
              <span class="nav-link disabled" title="Complete enrollment first to access payments">
                <i class="ri-money-dollar-circle-line me-2"></i><span>Payments</span>
                <i class="ri-lock-line ms-auto"></i>
              </span>
            @else
              <a class="nav-link {{ request()->routeIs('student.payments') ? 'active' : '' }}" href="{{ route('student.payments') }}" title="Payments">
                <i class="ri-money-dollar-circle-line me-2"></i><span>Payments</span>
              </a>
            @endif
          </li>
          
         
          <li class="nav-item mb-2">
            @if($hasNoPayment)
              <span class="nav-link disabled" title="Complete enrollment and settle payment to access this feature">
                <i class="ri-book-open-line me-2"></i><span>Subjects</span>
                <i class="ri-lock-line ms-auto"></i>
              </span>
            @else
              <a class="nav-link {{ request()->routeIs('student.subjects') ? 'active' : '' }}" href="{{ route('student.subjects') }}" title="Subjects">
                <i class="ri-book-open-line me-2"></i><span>Subjects</span>
              </a>
            @endif
          </li>
          
          <li class="nav-item mb-2">
            @if($hasNoPayment)
              <span class="nav-link disabled" title="Complete enrollment and settle payment to access this feature">
                <i class="ri-user-smile-line me-2"></i><span>ID Capturing</span>
                <i class="ri-lock-line ms-auto"></i>
              </span>
            @else
              <a class="nav-link {{ request()->routeIs('student.face-registration') ? 'active' : '' }}" href="{{ route('student.face-registration') }}" title="ID Capturing">
                <i class="ri-user-smile-line me-2"></i><span>ID Capturing</span>
              </a>
            @endif
          </li>
          
          <li class="nav-item mb-2">
            @if($hasNoPayment)
              <span class="nav-link disabled" title="Complete enrollment and settle payment to access this feature">
                <i class="ri-file-text-line me-2"></i><span>Grades</span>
                <i class="ri-lock-line ms-auto"></i>
              </span>
            @else
              <a class="nav-link {{ request()->routeIs('student.grades.*') ? 'active' : '' }}" href="{{ route('student.grades.index') }}" title="Grades">
                <i class="ri-file-text-line me-2"></i><span>Grades</span>
              </a>
            @endif
          </li>
          
          <li class="nav-item mb-2">
            @if($hasNoPayment)
              <span class="nav-link disabled" title="Complete enrollment and settle payment to access this feature">
                <i class="ri-flag-line me-2"></i><span>Violations</span>
                <i class="ri-lock-line ms-auto"></i>
              </span>
            @else
              <a class="nav-link {{ request()->routeIs('student.violations') ? 'active' : '' }}" href="{{ route('student.violations') }}" title="Violations">
                <i class="ri-flag-line me-2"></i><span>Violations</span>
              </a>
            @endif
          </li>
          
          
          <li class="nav-item mb-2">
            <form class="logout-form" action="{{ route('student.logout') }}" method="POST">
              @csrf
              <button type="submit" class="nav-link logout-btn" title="Logout">
                <i class="ri-logout-circle-line me-2"></i><span>Logout</span>
              </button>
            </form>
          </li>
         
        </ul>
      </nav>


  <div class="main-content-wrapper">
    <main class="px-3 px-md-4 py-4">
      <div class="main-content">
        {{ $slot }}
      </div>
    </main>
  </div>
  @stack('scripts')
</body>
</html>
