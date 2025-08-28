<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>

  <title>Guidance & Discipline • NSMS</title>

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
  <div class="container-fluid">
    <div class="row">

      <!-- SIDEBAR -->
      <nav class="col-12 col-md-2 sidebar d-none d-md-block py-4">
        <!-- User Info -->
        <div class="user-info">
          <div class="user-name">{{ Auth::user()->name }}</div>
          <div class="user-role">{{ ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) }}</div>
        </div>

        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link" href="{{ route('guidance.dashboard') }}">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link d-flex justify-content-between align-items-center" href="{{ route('guidance.students.index') }}">
              <span><i class="ri-user-line me-2"></i>Student Profiles</span>
              {{-- <small class="badge bg-success text-white">Active</small> --}}
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link d-flex justify-content-between align-items-center" href="{{ route('guidance.violations.index') }}">
              <span><i class="ri-alert-line me-2"></i>Violations</span>
              {{-- <small class="badge bg-success text-white">Active</small> --}}
            </a>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-scan-2-line me-2"></i>Facial Recognition</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-chat-quote-line me-2"></i>Counseling</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-briefcase-line me-2"></i>Career Advice</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-bar-chart-line me-2"></i>Analytics & Reports</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-settings-3-line me-2"></i>Settings</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>

          {{-- @can('create_guidance_accounts')
          <li class="nav-item mb-2">
            <a class="nav-link" href="{{ route('guidance.create-account') }}">
              <i class="ri-user-add-line me-2"></i>Create Account
            </a>
          </li>
          @endcan --}}

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
    {{ $slot }}
     
    </div>
  </div>
</body>
</html>