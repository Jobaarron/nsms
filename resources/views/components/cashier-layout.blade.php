<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

  
  @vite('resources/sass/app.scss')
  @vite(['resources/js/app.js'])
  @vite(['resources/css/index_cashier.css'])
  @vite(['resources/css/collapsible-sidebar.css'])
  @vite(['resources/js/cashier-dashboard.js'])
  @vite(['resources/js/cashier-payment-archives.js'])
  @vite(['resources/js/collapsible-sidebar.js'])
  @stack('scripts')
  @stack('styles')

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
  <!-- Sidebar Toggle Button (Desktop & Mobile) -->
  <button class="sidebar-toggle d-md-block" type="button" title="Toggle Sidebar">
    <i class="ri-menu-fold-line"></i>
  </button>

  <!-- SIDEBAR -->
  <nav class="sidebar py-4 bg-white border-end">
    <!-- School Logo -->
    <div class="text-center mb-3">
      <img src="{{ Vite::asset('resources/assets/images/edusphere-logo.png.png') }}" alt="logo" class="sidebar-logo nav__logo" style="height: 50px; transition: all 0.3s ease;">
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
            <a class="nav-link {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}" href="{{ route('cashier.dashboard') }}" title="Dashboard">
              <i class="ri-dashboard-line me-2"></i><span>Dashboard</span>
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('cashier.payments') ? 'active' : '' }}" href="{{ route('cashier.payments') }}" title="Payments">
              <i class="ri-time-line me-2"></i><span>Payments</span>
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('cashier.payment-archives') ? 'active' : '' }}" href="{{ route('cashier.payment-archives') }}" title="Payment Archives">
              <i class="ri-archive-line me-2"></i><span>Payment Archives</span>
            </a>
          </li>
          
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('cashier.fees*') ? 'active' : '' }}" href="{{ route('cashier.fees') }}" title="Fee Management">
              <i class="ri-money-dollar-circle-line me-2"></i><span>Fee Management</span>
            </a>
          </li>
         
        </ul>
        
        <!-- LOGOUT SECTION -->
        <div class="mt-auto pt-3 logout-form">
          <form action="{{ route('cashier.logout') }}" method="POST">
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
      <div class="main-content">
        {{ $slot }}
      </div>
    </main>
  </div>
</body>
</html>
