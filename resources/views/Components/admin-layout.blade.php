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
  @vite(['resources/js/role-modals.js'])
  @vite(['resources/js/admin-role-access.js'])
  @vite(['resources/js/user-management.js'])
  
 
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- SIDEBAR -->
      <nav class="col-12 col-md-2 sidebar d-md-block py-4">
        <!-- School Logo -->
        <div class="text-center mb-3">
          <img src="{{ Vite::asset('resources/assets/images/nms logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">
        </div>
        
        <!-- User Info -->
        {{-- <div class="user-info">
          <div class="user-name">{{ Auth::user()->name }}</div>
          <div class="user-role">{{ ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) }}</div>
        </div> --}}

        <div class="px-3 mb-4">
          <h5 class="text-uppercase fw-bold text-muted small">Admin Panel</h5>
        </div>
        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3 px-3 rounded" href="{{ route('admin.dashboard') }}">
              <i class="ri-dashboard-line me-2 fs-5"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3 px-3 rounded" href="{{ route('admin.manage.users') }}">
              <i class="ri-shield-user-line me-2 fs-5"></i>
              <span>User Management</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3 px-3 rounded" href="{{ route('admin.contact.messages') }}">
              <i class="ri-mail-line me-2 fs-5"></i>
              <span>Contact Messages</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3 px-3 rounded" href="{{ route('admin.forwarded.cases') }}">
              <i class="ri-file-list-3-line me-2 fs-5"></i>
              <span>Forwarded Case Meetings</span>
            </a>
          </li>
          <li class="nav-item mt-4">
            <form method="POST" action="{{ route('admin.logout') }}" class="px-3">
              @csrf
              <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center">
                <i class="ri-logout-box-line me-2"></i>
                <span>Logout</span>
              </button>
            </form>
          </li>
        </ul>
      </nav>

      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 px-4 py-4">
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
  </div>
</body>
</html>
