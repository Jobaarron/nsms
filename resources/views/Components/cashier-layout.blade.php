<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Cashier Portal | Nicolites Portal</title>

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
  @vite('resources/sass/app.scss')
  @vite(['resources/css/index_student.css'])

  <style>
    :root {
      --cashier-accent: #28a745; /* Green for finance/money theme */
    }
    
    .sidebar .nav-link.active {
      background-color: var(--cashier-accent);
    }
    
    .sidebar .nav-link.active:hover {
      background-color: #218838;
    }
    
    .user-info {
      border-color: var(--cashier-accent);
    }
    
    .avatar-circle {
      background: linear-gradient(135deg, var(--cashier-accent), #20c997);
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">

      <!-- SIDEBAR -->
      <nav class="col-12 col-md-2 sidebar d-none d-md-block py-4">
        <!-- School Logo -->
        <div class="text-center mb-3">
          <img src="{{ Vite::asset('resources/assets/images/nms logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">
        </div>
        
        <!-- User Info -->
        {{-- <div class="user-info mb-4 p-3 bg-light rounded">
          <div class="d-flex align-items-center">
            <div class="avatar-circle me-3">
              <i class="ri-money-dollar-circle-line"></i>
            </div>
            <div>
              <h6 class="mb-0">{{ Auth::guard('cashier')->user()->first_name ?? 'Cashier' }}</h6>
              <small class="text-muted">{{ Auth::guard('cashier')->user()->employee_id ?? 'ID: N/A' }}</small>
            </div>
          </div>
        </div> --}}

        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}" href="{{ route('cashier.dashboard') }}">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('cashier.pending-payments') ? 'active' : '' }}" href="{{ route('cashier.pending-payments') }}">
              <i class="ri-time-line me-2"></i>Pending Payments
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('cashier.due-payments') ? 'active' : '' }}" href="{{ route('cashier.due-payments') }}">
              <i class="ri-alarm-warning-line me-2"></i>Due Payments
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('cashier.completed-payments') ? 'active' : '' }}" href="{{ route('cashier.completed-payments') }}">
              <i class="ri-check-double-line me-2"></i>Completed Payments
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('cashier.payment-history') ? 'active' : '' }}" href="{{ route('cashier.payment-history') }}">
              <i class="ri-history-line me-2"></i>Payment History
            </a>
          </li>
          
          {{-- <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('cashier.reports') ? 'active' : '' }}" href="{{ route('cashier.reports') }}">
              <i class="ri-bar-chart-line me-2"></i>Reports
            </a>
          </li> --}}
         
        </ul>
        
        <!-- LOGOUT SECTION -->
        <div class="mt-auto pt-3">
          <form action="{{ route('cashier.logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start d-flex align-items-center" style="font-weight: 600;">
              <i class="ri-logout-circle-line me-2"></i>Logout
            </button>
          </form>
        </div>
      </nav>

      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 ms-sm-auto px-md-4">
        <div class="main-content py-4">
          {{ $slot }}
        </div>
      </main>
    </div>
  </div>
</body>
</html>
