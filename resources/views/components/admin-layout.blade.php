<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
        const sidebarState = sessionStorage.getItem('sidebarState_admin') || 'expanded';
        if (window.innerWidth > 767.98 && sidebarState === 'collapsed') {
          document.documentElement.classList.add('sidebar-collapsed-initial');
          document.documentElement.style.setProperty('--sidebar-width', '70px');
        } else {
          document.documentElement.style.setProperty('--sidebar-width', '250px');
        }
      } catch(e) {}
    })();
  </script>

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
  <button class="sidebar-toggle d-md-block" type="button">
    <i class="ri-menu-fold-line"></i>
  </button>

  <!-- Mobile Navigation Toggle (Hidden - using sidebar-toggle instead) -->
  <div class="d-none bg-white border-bottom p-3 fixed-top" style="z-index: 1030;">
    <div class="d-flex justify-content-between align-items-center">
      <img src="{{ Vite::asset('resources/assets/images/nms-logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">    </div>
      <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
        <i class="ri-menu-line"></i>
      </button>
    </div>
  </div>

  <!-- SIDEBAR -->
  <nav class="sidebar py-4 bg-white border-end">
        <!-- School Logo -->
        <div class="text-center mb-3">
          <img src="{{ Vite::asset('resources/assets/images/nms-logo.png') }}" alt="Nicolites Montessori School" class="sidebar-logo">
        </div>
        
        <!-- User Info -->
       <div class="user-info">
          <div class="user-name">{{ Auth::user()->name }}</div>
          <div class="user-role">{{ ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) }}</div>
        </div> 

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
            @php
              $unreadMessagesCount = \App\Models\ContactMessage::getUnreadMessagesCount();
              $contactMessagesViewed = session('contact_messages_alert_viewed', false);
            @endphp
            <a class="nav-link {{ request()->routeIs('admin.contact.messages') ? 'active' : '' }} position-relative" href="{{ route('admin.contact.messages') }}" title="Contact Messages" id="contact-messages-link" data-alert-link="contact_messages" style="{{ ($unreadMessagesCount > 0 && !$contactMessagesViewed) ? 'background-color: #f8d7da; border-left: 4px solid #dc3545; padding-left: calc(0.75rem - 4px);' : '' }}">
              <i class="ri-mail-line"></i>
              <span>Contact Messages</span>
              @if($unreadMessagesCount > 0 && !$contactMessagesViewed)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                  {{ $unreadMessagesCount }}
                </span>
              @endif
            </a>
          </li>
          <li class="nav-item">
            @php
              $pendingForwardedCasesCount = \App\Models\CaseMeeting::getPendingForwardedCasesCount();
              $forwardedCasesViewed = session('forwarded_cases_alert_viewed', false);
            @endphp
            <a class="nav-link {{ request()->routeIs('admin.forwarded.cases') ? 'active' : '' }} position-relative" href="{{ route('admin.forwarded.cases') }}" title="Forwarded Case Meetings" id="forwarded-cases-link" data-alert-link="forwarded_cases" style="{{ ($pendingForwardedCasesCount > 0 && !$forwardedCasesViewed) ? 'background-color: #f8d7da; border-left: 4px solid #dc3545; padding-left: calc(0.75rem - 4px);' : '' }}">
              <i class="ri-file-list-3-line"></i>
              <span>Forwarded Case Meetings</span>
              @if($pendingForwardedCasesCount > 0 && !$forwardedCasesViewed)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                  {{ $pendingForwardedCasesCount }}
                </span>
              @endif
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
