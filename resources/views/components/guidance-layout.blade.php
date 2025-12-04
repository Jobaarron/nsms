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
        const sidebarState = sessionStorage.getItem('sidebarState_guidance') || 'expanded';
        if (window.innerWidth > 767.98 && sidebarState === 'collapsed') {
          document.documentElement.classList.add('sidebar-collapsed-initial');
          document.documentElement.style.setProperty('--sidebar-width', '70px');
        } else {
          document.documentElement.style.setProperty('--sidebar-width', '250px');
        }
      } catch(e) {}
    })();
  </script>

  <title>Guidance Portal | Nicolites Portal</title>

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
  @vite(['resources/css/index_guidance.css'])
  @vite(['resources/css/collapsible-sidebar.css'])
  @vite(['resources/js/collapsible-sidebar.js'])
  
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
            <a class="nav-link {{ request()->routeIs('guidance.dashboard') ? 'active' : '' }}" href="{{ route('guidance.dashboard') }}" title="Dashboard">
              <i class="ri-dashboard-line me-2"></i><span>Dashboard</span>
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('guidance.case-meetings.*') ? 'active' : '' }}" href="{{ route('guidance.case-meetings.index') }}" title="Case Management">
              <i class="ri-calendar-event-line me-2"></i><span>Case Management</span>
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('guidance.counseling-sessions.*') && !request()->routeIs('guidance.counseling-sessions.archived') ? 'active' : '' }}" href="{{ route('guidance.counseling-sessions.index') }}" title="Counseling Sessions">
              <i class="ri-heart-pulse-line me-2"></i><span>Counseling Sessions</span>
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('guidance.counseling-sessions.archived') ? 'active' : '' }}" href="{{ route('guidance.counseling-sessions.archived') }}" title="Archive Center">
              <i class="ri-archive-line me-2"></i><span>Archive Center</span>
            </a>
          </li>
          
          {{-- <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-bar-chart-line me-2"></i>Analytics</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-settings-3-line me-2"></i>Settings</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li> --}}
          
          <li class="nav-item mt-3">
            <form class="logout-form" method="POST" action="{{ route('guidance.logout') }}">
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
