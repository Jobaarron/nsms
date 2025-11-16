<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

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
  @vite(['resources/js/app.js'])

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
  <!-- Mobile Navigation Toggle -->
  <div class="d-md-none bg-white border-bottom p-3 fixed-top" style="z-index: 1030;">
    <div class="d-flex justify-content-between align-items-center">
      <img src="{{ Vite::asset('resources/assets/images/nms-logo.png') }}" alt="Nicolites Montessori School" style="height: 30px;">
      <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#enrolleeSidebar" aria-controls="enrolleeSidebar">
        <i class="ri-menu-line"></i>
      </button>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row">
      
      <!-- SIDEBAR -->
      <nav class="col-12 col-md-2 sidebar d-none d-md-block py-4">
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
            <a class="nav-link {{ request()->routeIs('enrollee.dashboard') ? 'active' : '' }}" href="{{ route('enrollee.dashboard') }}">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('enrollee.application') ? 'active' : '' }}" href="{{ route('enrollee.application') }}">
              <i class="ri-file-text-line me-2"></i>My Application
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('enrollee.documents') ? 'active' : '' }}" href="{{ route('enrollee.documents') }}">
              <i class="ri-folder-line me-2"></i>Documents
            </a>
          </li>
          @php
            $currentEnrollee = auth('enrollee')->user();
            $unreadCount = $currentEnrollee ? \App\Models\Notice::getUnreadCountForEnrollee($currentEnrollee->id) : 0;
          @endphp
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('enrollee.notices') ? 'active' : '' }} position-relative" href="{{ route('enrollee.notices') }}">
              <i class="ri-notification-line me-2"></i>Notices
              @if($unreadCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                  {{ $unreadCount }}
                </span>
              @endif
            </a>
          </li>
        </ul>
        
        <!-- LOGOUT SECTION -->
        <div class="mt-auto pt-3">
          <form action="{{ route('enrollee.logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start d-flex align-items-center" style="font-weight: 600;">
              <i class="ri-logout-circle-line me-2"></i>Logout
            </button>
          </form>
        </div>
      </nav>

      <!-- MOBILE SIDEBAR (Offcanvas) -->
      <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="enrolleeSidebar" aria-labelledby="enrolleeSidebarLabel">
        <div class="offcanvas-header border-bottom">
          <div class="d-flex align-items-center">
            <img src="{{ Vite::asset('resources/assets/images/nms-logo.png') }}" alt="Nicolites Montessori School" style="height: 30px;" class="me-2">
            <h5 class="offcanvas-title mb-0" id="enrolleeSidebarLabel">Applicant Portal</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
          <ul class="nav flex-column">
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('enrollee.dashboard') ? 'active' : '' }}" href="{{ route('enrollee.dashboard') }}">
                <i class="ri-dashboard-line me-2"></i>Dashboard
              </a>
            </li>
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('enrollee.application') ? 'active' : '' }}" href="{{ route('enrollee.application') }}">
                <i class="ri-file-text-line me-2"></i>My Application
              </a>
            </li>
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('enrollee.documents') ? 'active' : '' }}" href="{{ route('enrollee.documents') }}">
                <i class="ri-folder-line me-2"></i>Documents
              </a>
            </li>
            @php
              $currentEnrollee = auth('enrollee')->user();
              $unreadCount = $currentEnrollee ? \App\Models\Notice::getUnreadCountForEnrollee($currentEnrollee->id) : 0;
            @endphp
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('enrollee.notices') ? 'active' : '' }} position-relative" href="{{ route('enrollee.notices') }}">
                <i class="ri-notification-line me-2"></i>Notices
                @if($unreadCount > 0)
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                    {{ $unreadCount }}
                  </span>
                @endif
              </a>
            </li>
          </ul>
          
          <!-- LOGOUT SECTION -->
          <div class="mt-auto pt-3">
            <form action="{{ route('enrollee.logout') }}" method="POST">
              @csrf
              <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start d-flex align-items-center" style="font-weight: 600;">
                <i class="ri-logout-circle-line me-2"></i>Logout
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 ms-sm-auto px-3 px-md-4" style="margin-top: 70px;">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2 section-title">
            <i class="ri-graduation-cap-line me-2"></i>
            Applicant Portal
          </h1>
        </div>

        {{ $slot }}
      </main>
    </div>
  </div>


  
</body>
</html>