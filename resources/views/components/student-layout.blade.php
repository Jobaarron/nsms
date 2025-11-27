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
  @vite(['resources/js/student-alerts-manager.js'])
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

  
  <nav class="sidebar py-4 bg-white border-end">
        
        <div class="text-center mb-3">
         <img src="{{ Vite::asset('resources/assets/images/nms-logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">
        </div>
        

        @php
          $currentStudent = Auth::guard('student')->user();
         
          $hasNoEnrollment = !$currentStudent || $currentStudent->enrollment_status !== 'enrolled';
          
          
          
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
            @if($hasNoPayment)
              <span class="nav-link disabled" title="Complete enrollment and settle payment to access this feature">
                <i class="ri-money-dollar-circle-line me-2"></i><span>Payments</span>
                <i class="ri-lock-line ms-auto"></i>
              </span>
            @else
              @php
                $duePaymentsCount = $currentStudent ? \App\Models\Payment::getDuePaymentsCountForStudent($currentStudent->id) : 0;
              @endphp
              <a class="nav-link {{ request()->routeIs('student.payments') ? 'active' : '' }} position-relative" href="{{ route('student.payments') }}" title="Payments" id="payments-link" data-alert-link="payments" style="{{ $duePaymentsCount > 0 ? 'background-color: #f8d7da; border-left: 4px solid #dc3545; padding-left: calc(0.75rem - 4px);' : '' }}">
                <i class="ri-money-dollar-circle-line me-2"></i><span>Payments</span>
                @if($duePaymentsCount > 0)
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                    {{ $duePaymentsCount }}
                  </span>
                @endif
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
              @php
                $newGradesCount = $currentStudent ? \App\Models\Grade::getNewGradesCountForStudent($currentStudent->id) : 0;
                $gradesViewed = session('grades_alert_viewed', false);
              @endphp
              <a class="nav-link {{ request()->routeIs('student.grades.*') ? 'active' : '' }} position-relative" href="{{ route('student.grades.index') }}" title="Grades" id="grades-link" data-alert-link="grades" style="{{ ($newGradesCount > 0 && !$gradesViewed) ? 'background-color: #f8d7da; border-left: 4px solid #dc3545; padding-left: calc(0.75rem - 4px);' : '' }}">
                <i class="ri-file-text-line me-2"></i><span>Grades</span>
                @if($newGradesCount > 0 && !$gradesViewed)
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                    {{ $newGradesCount }}
                  </span>
                @endif
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
              @php
                $newViolationsCount = $currentStudent ? \App\Models\Violation::getNewViolationsCountForStudent($currentStudent->id) : 0;
                $violationsViewed = session('violations_alert_viewed', false);
              @endphp
              <a class="nav-link {{ request()->routeIs('student.violations') ? 'active' : '' }} position-relative" href="{{ route('student.violations') }}" title="Violations" id="violations-link" data-alert-link="violations" style="{{ ($newViolationsCount > 0 && !$violationsViewed) ? 'background-color: #f8d7da; border-left: 4px solid #dc3545; padding-left: calc(0.75rem - 4px);' : '' }}">
                <i class="ri-flag-line me-2"></i><span>Violations</span>
                @if($newViolationsCount > 0 && !$violationsViewed)
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                    {{ $newViolationsCount }}
                  </span>
                @endif
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

      <!-- Hidden badge elements for JavaScript alert system -->
      <div id="payments-alert-badge" class="d-none" style="display: none;"></div>
      <div id="grades-alert-badge" class="d-none" style="display: none;"></div>
      <div id="violations-alert-badge" class="d-none" style="display: none;"></div>


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
