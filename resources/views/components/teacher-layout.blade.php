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
  @vite(['resources/js/teacher-alerts-manager.js'])

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

  
  <nav class="sidebar py-4 bg-white border-end">
  
    <div class="text-center mb-3">
      <img src="{{ Vite::asset('resources/assets/images/nms-logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">
    </div>
        
    <!-- User Info -->
    <div class="user-info">
      <div class="user-name">{{ Auth::user()->name }}</div>
      <div class="user-role">{{ ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) }}</div>
    </div>
        
        <ul class="nav flex-column px-3">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}" title="Dashboard">
              <i class="ri-dashboard-line"></i><span>Dashboard</span>
            </a>
          </li>
          
          
          <li class="nav-item mb-2">
            @php
              $currentUser = auth()->user();
              $currentTeacher = $currentUser && $currentUser->teacher ? $currentUser->teacher : null;
              $draftGradesCount = $currentTeacher ? \App\Models\GradeSubmission::getDraftSubmissionsCountForTeacher($currentTeacher->id) : 0;
              $gradesViewed = session('grades_alert_viewed', false);
            @endphp
            <a class="nav-link {{ request()->routeIs('teacher.grades*') ? 'active' : '' }} position-relative" href="{{ route('teacher.grades') }}" title="Submit Grades" id="grades-link" style="{{ ($draftGradesCount > 0 && !$gradesViewed) ? 'background-color: #f8d7da; border-left: 4px solid #dc3545; padding-left: calc(0.75rem - 4px);' : '' }}">
              <i class="ri-pencil-ruler-2-line me-2"></i><span>Submit Grades</span>
              @if($draftGradesCount > 0 && !$gradesViewed)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                  {{ $draftGradesCount }}
                </span>
              @endif
            </a>
          </li>

          @php
            $currentUser = auth()->user();
            $currentTeacher = $currentUser && $currentUser->teacher ? $currentUser->teacher : null;
            $isClassAdviser = $currentTeacher ? $currentTeacher->isClassAdviser() : false;
            // Pass user_id (not teacher id) since adviser_id in case_meetings table references users.id
            $unrepliedObservationReportsCount = $currentUser && $currentTeacher ? \App\Models\CaseMeeting::getUnrepliedObservationReportsCountForTeacher($currentUser->id) : 0;
            // Only hide if there are no database notifications (database-driven approach)
            $observationReportsViewed = ($unrepliedObservationReportsCount === 0);
          @endphp

          @if($isClassAdviser)
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('teacher.observationreport*') ? 'active' : '' }} position-relative" href="{{ route('teacher.observationreport') }}" title="Observation Report" id="observation-reports-link" data-alert-link="observation_reports" style="{{ ($unrepliedObservationReportsCount > 0 && !$observationReportsViewed) ? 'background-color: #f8d7da; border-left: 4px solid #dc3545; padding-left: calc(0.75rem - 4px);' : '' }}">
                <i class="ri-file-list-3-line me-2"></i><span>Observation Report</span>
                @if($unrepliedObservationReportsCount > 0 && !$observationReportsViewed)
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                    {{ $unrepliedObservationReportsCount }}
                  </span>
                @endif
              </a>
            </li>
            
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('teacher.advisory') ? 'active' : '' }}" href="{{ route('teacher.advisory') }}" title="Advisory">
                <i class="ri-user-star-line me-2"></i><span>Advisory</span>
              </a>
            </li>
            
            <li class="nav-item mb-2">
              @php
                // Pass user_id (not teacher id) since recommended_by in counseling_sessions table references users.id
                $scheduledCounselingCount = $currentUser && $currentTeacher ? \App\Models\CounselingSession::getScheduledCounselingCountForAdviser($currentUser->id) : 0;
                // Only hide if there are no database notifications (since markAlertViewed updates the database)
                $counselingViewed = ($scheduledCounselingCount === 0);
              @endphp
              <a class="nav-link {{ request()->routeIs('teacher.recommend-counseling.*') ? 'active' : '' }} position-relative" href="{{ route('teacher.recommend-counseling.form') }}" title="Recommend Counseling" id="counseling-link" data-alert-link="counseling" style="{{ ($scheduledCounselingCount > 0 && !$counselingViewed) ? 'background-color: #f8d7da; border-left: 4px solid #dc3545; padding-left: calc(0.75rem - 4px);' : '' }}">
                <i class="ri-heart-pulse-line me-2"></i><span>Recommend Counseling</span>
                @if($scheduledCounselingCount > 0 && !$counselingViewed)
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                    {{ $scheduledCounselingCount }}
                  </span>
                @endif
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