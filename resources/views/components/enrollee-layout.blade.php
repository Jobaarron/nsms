<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Prevent sidebar flash by applying state immediately -->
  <script>
    (function() {
      try {
        const sidebarState = localStorage.getItem('sidebarState') || 'expanded';
        if (window.innerWidth > 767.98 && sidebarState === 'collapsed') {
          document.documentElement.classList.add('sidebar-collapsed-initial');
          document.documentElement.style.setProperty('--sidebar-width', '70px');
        } else {
          document.documentElement.style.setProperty('--sidebar-width', '250px');
        }
      } catch(e) {}
    })();
  </script>

  <title>Applicant Portal | Nicolites Portal</title>

    <!-- Remix Icons -->
    <link 
    href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" 
    rel="stylesheet"
    crossorigin="anonymous"
    />

    <!-- Google Font -->
    <link 
    href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" 
    rel="stylesheet"
    />

  
  @vite('resources/sass/app.scss')
  @vite(['resources/css/index_enrollee.css'])
  @vite(['resources/css/collapsible-sidebar.css'])
  @vite(['resources/js/app.js'])
  @vite(['resources/js/collapsible-sidebar.js'])

  <style>
    /* Mobile responsive improvements */
    @media (max-width: 767.98px) {
      .main-content {
        margin-top: 70px;
      }
    }
    /* Ensure icons are properly displayed */
    .btn i {
      font-size: 1rem;
      line-height: 1;
      vertical-align: middle;
    }
    
    /* Icon fallback styling */
    .btn i::before {
      font-family: "remixicon" !important;
      font-style: normal;
      font-weight: normal;
      font-variant: normal;
      text-transform: none;
      line-height: 1;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
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
    {{-- <div class="user-info">
      <div class="user-name">{{ auth('enrollee')->user()->first_name ?? 'Applicant' }} {{ auth('enrollee')->user()->last_name ?? '' }}</div>
      <div class="user-role">Applicant</div>
    </div> --}}

    <ul class="nav flex-column">
      <li class="nav-item mb-2">
        <a class="nav-link {{ request()->routeIs('enrollee.dashboard') ? 'active' : '' }}" href="{{ route('enrollee.dashboard') }}" title="Dashboard">
          <i class="ri-dashboard-line me-2"></i><span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link {{ request()->routeIs('enrollee.application') ? 'active' : '' }}" href="{{ route('enrollee.application') }}" title="My Application">
          <i class="ri-file-text-line me-2"></i><span>My Application</span>
        </a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link {{ request()->routeIs('enrollee.documents') ? 'active' : '' }}" href="{{ route('enrollee.documents') }}" title="Documents">
          <i class="ri-folder-line me-2"></i><span>Documents</span>
        </a>
      </li>
          @php
            $currentEnrollee = auth('enrollee')->user();
            $unreadCount = $currentEnrollee ? \App\Models\Notice::getUnreadCountForEnrollee($currentEnrollee->id) : 0;
          @endphp
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('enrollee.notices') ? 'active' : '' }} position-relative" href="{{ route('enrollee.notices') }}" title="Notifications">
              <i class="ri-notification-line me-2"></i><span>Notifications</span>
              @if($unreadCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                  {{ $unreadCount }}
                </span>
              @endif
            </a>
          </li>
        </ul>
        
        <!-- LOGOUT SECTION -->
        <div class="mt-auto pt-3 logout-form">
          <form action="{{ route('enrollee.logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start d-flex align-items-center" style="font-weight: 600;" title="Logout">
              <i class="ri-logout-circle-line me-2"></i><span>Logout</span>
            </button>
          </form>
        </div>
      </nav>

  <!-- MAIN CONTENT -->
  <div class="main-content-wrapper">
    <main class="px-3 px-md-4 py-4">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 section-title">
          <i class="ri-graduation-cap-line me-2"></i>
          Applicant Portal
        </h1>
      </div>

      {{ $slot }}
    </main>
  </div>
</body>
</html>