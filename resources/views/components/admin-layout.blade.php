<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Portal | Nicolites Portal</title>

  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"/>
  
  @vite(['resources/sass/app.scss','resources/js/app.js'])
  @vite(['resources/css/index_admin.css'])
  @vite(['resources/css/roles_access.css'])
  @vite(['resources/css/collapsible-sidebar.css'])
  @vite(['resources/js/role-modals.js'])
  @vite(['resources/js/admin-role-access.js'])
  @vite(['resources/js/user-management.js'])
  @vite(['resources/js/collapsible-sidebar.js'])
  
 
</head>
<body>
  <!-- Sidebar Toggle Button (Desktop & Mobile) -->
  <button class="sidebar-toggle d-md-block" type="button" title="Toggle Sidebar">
    <i class="ri-menu-fold-line"></i>
  </button>

  <!-- Mobile Navigation Toggle (Hidden - using sidebar-toggle instead) -->
  <div class="d-none bg-white border-bottom p-3 fixed-top" style="z-index: 1030;">
    <div class="d-flex justify-content-between align-items-center">
      <img src="{{ Vite::asset('resources/assets/images/edusphere-logo.png.png') }}" alt="logo" class="nav__logo" style="height: 30px;">
      <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
        <i class="ri-menu-line"></i>
      </button>
    </div>
  </div>

  <!-- SIDEBAR -->
  <nav class="sidebar py-4 bg-white border-end">
        <!-- School Logo -->
        <div class="text-center mb-3">
          <img src="{{ Vite::asset('resources/assets/images/edusphere-logo.png.png') }}" alt="logo" class="sidebar-logo nav__logo" style="height: 50px; transition: all 0.3s ease;">
        </div>
        
        <!-- User Info -->
        {{-- <div class="user-info">
          <div class="user-name">{{ Auth::user()->name }}</div>
          <div class="user-role">{{ ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) }}</div>
        </div> --}}

        <div class="px-3 mb-4">
          <h5>Admin Panel</h5>
        </div>
        <ul class="nav flex-column px-3">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" title="Dashboard">
              <i class="ri-dashboard-line"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.manage.users') ? 'active' : '' }}" href="{{ route('admin.manage.users') }}" title="User Management">
              <i class="ri-shield-user-line"></i>
              <span>User Management</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.contact.messages') ? 'active' : '' }}" href="{{ route('admin.contact.messages') }}" title="Contact Messages">
              <i class="ri-mail-line"></i>
              <span>Contact Messages</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.forwarded.cases') ? 'active' : '' }}" href="{{ route('admin.forwarded.cases') }}" title="Forwarded Case Meetings">
              <i class="ri-file-list-3-line"></i>
              <span>Forwarded Case Meetings</span>
            </a>
          </li>
          <li class="nav-item mt-4">
            <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
              @csrf
              <button type="submit" title="Logout">
                <i class="ri-logout-box-line"></i>
                <span>Logout</span>
              </button>
            </form>
          </li>
        </ul>
      </nav>

  <!-- MAIN CONTENT -->
  <div class="main-content-wrapper">
    <main class="px-3 px-md-4 py-4">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        
        {{ $slot }}
    </main>
  </div>
</body>
</html>
