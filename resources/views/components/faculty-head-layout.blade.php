<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Prevent sidebar flash by applying state immediately -->
  <script>
    (function() {
      try {
        const sidebarState = sessionStorage.getItem('sidebarState_faculty_head') || 'expanded';
        if (window.innerWidth > 767.98 && sidebarState === 'collapsed') {
          document.documentElement.classList.add('sidebar-collapsed-initial');
          document.documentElement.style.setProperty('--sidebar-width', '70px');
        } else {
          document.documentElement.style.setProperty('--sidebar-width', '250px');
        }
      } catch(e) {}
    })();
  </script>

  <title>Faculty Head Portal | Nicolites Portal</title>

  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet"/>

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"/>

  <!-- App CSS & JS (includes Bootstrap 5 via Vite) -->
  @vite(['resources/sass/app.scss','resources/js/app.js'])
  @vite(['resources/css/index_faculty_head.css'])
  @vite(['resources/css/collapsible-sidebar.css'])
  
  <!-- Faculty Head JavaScript - Load in HEAD for onclick handlers -->
  @vite(['resources/js/faculty-head-assign-teacher.js'])
  @vite(['resources/js/faculty-head-activate-submission.js'])
  @vite(['resources/js/faculty-head-view-grades.js'])
  @vite(['resources/js/collapsible-sidebar.js'])
  @vite(['resources/js/faculty-head-alerts-manager.js'])

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
    <div class="user-info">
      <div class="user-name">{{ Auth::user()->name }}</div>
      <div class="user-role">{{ ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) }}</div>
    </div>
        
        <ul class="nav flex-column px-3">
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('faculty-head.dashboard') ? 'active' : '' }}" href="{{ route('faculty-head.dashboard') }}" title="Dashboard">
              <i class="ri-dashboard-line me-2"></i><span>Dashboard</span>
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('faculty-head.assign-faculty') || request()->routeIs('faculty-head.assign-teacher.*') || request()->routeIs('faculty-head.assign-adviser.*') ? 'active' : '' }}" href="{{ route('faculty-head.assign-faculty') }}" title="Faculty Assignments">
              <i class="ri-team-line me-2"></i><span>Faculty Assignments</span>
            </a>
          </li>
          
          <li class="nav-item mb-2">
            @php
              $pendingGradesCount = \App\Models\GradeSubmission::getPendingSubmissionsCount();
              $gradesViewed = session('grades_alert_viewed', false);
            @endphp
            <a class="nav-link {{ request()->routeIs('faculty-head.view-grades.*') || request()->routeIs('faculty-head.approve-grades.*') ? 'active' : '' }} position-relative" href="{{ route('faculty-head.view-grades') }}" title="Review & Approve Grades" id="grades-link" style="{{ ($pendingGradesCount > 0 && !$gradesViewed) ? 'background-color: #f8d7da; border-left: 4px solid #dc3545; padding-left: calc(0.75rem - 4px);' : '' }}">
              <i class="ri-file-list-3-line me-2"></i><span>Review & Approve Grades</span>
              @if($pendingGradesCount > 0 && !$gradesViewed)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                  {{ $pendingGradesCount }}
                </span>
              @endif
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('faculty-head.activate-submission.*') ? 'active' : '' }}" href="{{ route('faculty-head.activate-submission') }}" title="Activate Grade Submission">
              <i class="ri-play-circle-line me-2"></i><span>Activate Grade Submission</span>
            </a>
          </li>

          <li class="nav-item mt-3">
            <form class="logout-form" method="POST" action="{{ route('faculty-head.logout') }}">
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

  @stack('scripts')
</body>
</html>
