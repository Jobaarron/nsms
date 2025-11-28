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
        const sidebarState = sessionStorage.getItem('sidebarState_cashier') || 'expanded';
        if (window.innerWidth > 767.98 && sidebarState === 'collapsed') {
          document.documentElement.classList.add('sidebar-collapsed-initial');
          document.documentElement.style.setProperty('--sidebar-width', '70px');
        } else {
          document.documentElement.style.setProperty('--sidebar-width', '250px');
        }
      } catch(e) {}
    })();
  </script>

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
    <div class="user-info">
      <div class="user-name">{{ Auth::user()->full_name }}</div>
      <div class="user-role">{{ ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) }}</div>
    </div>

        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}" href="{{ route('cashier.dashboard') }}" title="Dashboard">
              <i class="ri-dashboard-line me-2"></i><span>Dashboard</span>
            </a>
          </li>
          
          <li class="nav-item mb-2">
            @php
              $pendingPaymentsCount = \App\Models\Payment::getPendingPaymentConfirmationsCount();
              $paymentsViewed = session('payments_alert_viewed', false);
            @endphp
            <a class="nav-link {{ request()->routeIs('cashier.payments') ? 'active' : '' }} position-relative" href="{{ route('cashier.payments') }}" title="Payments" id="payments-link" style="{{ ($pendingPaymentsCount > 0 && !$paymentsViewed) ? 'background-color: #f8d7da; border-left: 4px solid #dc3545; padding-left: calc(0.75rem - 4px);' : '' }}">
              <i class="ri-time-line me-2"></i><span>Payments</span>
              @if($pendingPaymentsCount > 0 && !$paymentsViewed)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                  {{ $pendingPaymentsCount }}
                </span>
              @endif
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
