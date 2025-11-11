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

  <title>Guidance Portal| Nicolites Portal</title>

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
  @vite(['resources/sass/app.scss','resources/js/app.js'])
  @vite(['resources/css/index_guidance.css'])
  
</head>
<body>
  <!-- Mobile Navigation Toggle -->
  <div class="d-md-none bg-white border-bottom p-3 fixed-top" style="z-index: 1030;">
    <div class="d-flex justify-content-between align-items-center">
      <img src="{{ Vite::asset('resources/assets/images/nms logo.png') }}" alt="Nicolites Montessori School" style="height: 30px;">
      <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#guidanceSidebar" aria-controls="guidanceSidebar">
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
          <img src="{{ Vite::asset('resources/assets/images/nms logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">
        </div>
        
        <!-- User Info -->
        {{-- <div class="user-info">
          <div class="user-name">{{ Auth::user()->name }}</div>
          <div class="user-role">{{ ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) }}</div>
        </div> --}}

        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('guidance.dashboard') ? 'active' : '' }}" href="{{ route('guidance.dashboard') }}">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('guidance.case-meetings.*') ? 'active' : '' }}" href="{{ route('guidance.case-meetings.index') }}">
              <i class="ri-calendar-event-line me-2"></i>Case Management
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('guidance.counseling-sessions.*') ? 'active' : '' }}" href="{{ route('guidance.counseling-sessions.index') }}">
              <i class="ri-heart-pulse-line me-2"></i>Counseling Sessions
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
            <form method="POST" action="{{ route('guidance.logout') }}">
              @csrf
              <button type="submit" class="btn btn-logout w-100">
                <i class="ri-logout-circle-line me-2"></i>Logout
              </button>
            </form>
          </li>
        </ul>
      </nav>

      <!-- MOBILE SIDEBAR (Offcanvas) -->
      <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="guidanceSidebar" aria-labelledby="guidanceSidebarLabel">
        <div class="offcanvas-header border-bottom">
          <div class="d-flex align-items-center">
            <img src="{{ Vite::asset('resources/assets/images/nms logo.png') }}" alt="Nicolites Montessori School" style="height: 30px;" class="me-2">
            <h5 class="offcanvas-title mb-0" id="guidanceSidebarLabel">Guidance Portal</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
          <ul class="nav flex-column">
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('guidance.dashboard') ? 'active' : '' }}" href="{{ route('guidance.dashboard') }}">
                <i class="ri-dashboard-line me-2"></i>Dashboard
              </a>
            </li>
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('guidance.case-meetings*') ? 'active' : '' }}" href="{{ route('guidance.case-meetings.index') }}">
                <i class="ri-folder-user-line me-2"></i>Case Management
              </a>
            </li>
            <li class="nav-item mb-2">
              <a class="nav-link {{ request()->routeIs('guidance.counseling-sessions*') ? 'active' : '' }}" href="{{ route('guidance.counseling-sessions.index') }}">
                <i class="ri-heart-pulse-line me-2"></i>Counseling Sessions
              </a>
            </li>
            <li class="nav-item mt-3">
              <form method="POST" action="{{ route('guidance.logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100">
                  <i class="ri-logout-box-line me-2"></i>Logout
                </button>
              </form>
            </li>
          </ul>
        </div>
      </div>
      
      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 px-3 px-md-4 py-4" style="margin-top: 70px;">
        <div class="d-md-none mb-3"></div>
        {{ $slot }}
      </main>
    </div>
  </div>
</body>
</html>