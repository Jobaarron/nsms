<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>


  <title>Teacher Portal | Nicolites Portal</title>

  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet"/>

  <!-- App CSS & JS (includes Bootstrap 5 via Vite) -->
  @vite(['resources/sass/app.scss','resources/js/app.js'])
  @vite(['resources/css/index_teacher.css'])

  
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
            <a class="nav-link active" href="#">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link d-flex justify-content-between align-items-center">
              <span><i class="ri-book-3-line me-2"></i>My Classes</span>
              {{-- <small class="badge bg-light text-dark">Soon</small> --}}
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-team-line me-2"></i>Students</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-pencil-ruler-2-line me-2"></i>Grade Book</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-time-line me-2"></i>Attendance</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-chat-1-line me-2"></i>Messages</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>

          <li class="nav-item mt-3">
            <form method="POST" action="{{ route('teacher.logout') }}">
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