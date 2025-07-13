<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">

  <title>{{ $title ?? 'Admin Dashboard' }} • NSMS</title>

  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"/>
  
  @vite(['resources/sass/app.scss','resources/js/app.js'])
  @vite(['resources/css/index_admin.css'])
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- SIDEBAR -->
      <nav class="col-12 col-md-2 sidebar d-md-block py-4">
        <div class="px-3 mb-4">
          <h5 class="text-uppercase fw-bold text-muted small">Admin Panel</h5>
        </div>
        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3 px-3 rounded {{ request()->routeIs('admin.dashboard') ? 'active bg-light' : '' }}" href="{{ route('admin.dashboard') }}">
              <i class="ri-dashboard-line me-2 fs-5"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3 px-3 rounded" href="{{ route('admin.roles.access') }}">
                <i class="ri-shield-user-line me-2 fs-5"></i>
                <span>Roles & Access</span>
            </a>
        </li>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3 px-3 rounded" href="{{ route('admin.manage.users') }}">
                <i class="ri-user-settings-line me-2 fs-5"></i>
                <span>Manage Users</span>
            </a>
        </li>
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center py-3 px-3 rounded" href="{{ route('admin.enrollments') }}">
              <i class="ri-file-list-line me-2 fs-5"></i>
              <span>Enrollments</span>
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
