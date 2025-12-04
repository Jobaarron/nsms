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
        const sidebarState = sessionStorage.getItem('sidebarState_registrar') || 'expanded';
        if (window.innerWidth > 767.98 && sidebarState === 'collapsed') {
          document.documentElement.classList.add('sidebar-collapsed-initial');
          document.documentElement.style.setProperty('--sidebar-width', '70px');
        } else {
          document.documentElement.style.setProperty('--sidebar-width', '250px');
        }
      } catch(e) {}
    })();
  </script>

  <title>Registrar Portal | Nicolites Portal</title>

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

  
  <!-- App CSS & JS (includes Bootstrap 5 via Vite) -->
  @vite(['resources/sass/app.scss','resources/js/app.js'])
  @vite(['resources/css/index_registrar.css'])
  @vite(['resources/css/collapsible-sidebar.css'])
  @vite(['resources/js/registrar-class-lists.js'])
  @vite(['resources/js/registrar-dashboard.js'])
  @vite(['resources/js/registrar-applicant-archives.js'])
  @vite(['resources/js/collapsible-sidebar.js'])
  @vite(['resources/js/registrar-alerts-manager.js'])

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
            <a class="nav-link {{ request()->routeIs('registrar.dashboard') ? 'active' : '' }}" href="{{ route('registrar.dashboard') }}" title="Dashboard">
              <i class="ri-home-line"></i><span>Dashboard</span>
            </a>
          </li>
          <li class="nav-item mb-2">
            @php
              $newApplicationsCount = \App\Http\Controllers\RegistrarController::getNewApplicationsCount();
              $applicationsViewed = session('applications_alert_viewed', false);
            @endphp
            <a class="nav-link {{ request()->routeIs('registrar.applications') ? 'active' : '' }} position-relative" href="{{ route('registrar.applications') }}" title="Applications" id="applications-link" style="{{ ($newApplicationsCount > 0 && !$applicationsViewed) ? 'background-color: #f8d7da; border-left: 4px solid #dc3545; padding-left: calc(0.75rem - 4px);' : '' }}">
              <i class="ri-file-text-line me-2"></i><span>Applications</span>
              @if($newApplicationsCount > 0 && !$applicationsViewed)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                  {{ $newApplicationsCount }}
                </span>
              @endif
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('registrar.class-lists') ? 'active' : '' }}" href="{{ route('registrar.class-lists') }}" title="Class Lists">
              <i class="ri-user-line me-2"></i><span>Class Lists</span>
            </a>
          </li>
           <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('registrar.applicant-archives') ? 'active' : '' }}" href="{{ route('registrar.applicant-archives') }}" title="Applicant Archives">
              <i class="ri-folder-line me-2"></i><span>Applicant Archives</span>
            </a>
          </li>
          
          <!-- LOGOUT SECTION -->
          <li class="nav-item mb-2">
            <form class="logout-form" action="{{ route('registrar.logout') }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-logout w-100" title="Logout">
                <i class="ri-logout-box-line me-2"></i><span>Logout</span>
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
