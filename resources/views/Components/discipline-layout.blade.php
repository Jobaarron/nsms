<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Discipline Portal</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    @vite(['resources/css/discipline.css'])
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-12 col-md-2 sidebar p-3">
                <!-- Logo -->
                <div class="text-center mb-4">
                    <img src="{{ asset('images/nicolites-logo.png') }}" alt="Nicolites Logo" class="sidebar-logo">
                </div>
                
                <!-- User Info -->
                {{-- <div class="user-info">
                    <div class="user-name">{{ Auth::user()->discipline->full_name ?? Auth::user()->name ?? 'Discipline Staff' }}</div>
                    <div class="user-role">Discipline Portal</div>
                </div> --}}
                
                <!-- Navigation -->
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('discipline.dashboard') ? 'active' : '' }}" 
                           href="{{ route('discipline.dashboard') }}">
                            <i class="ri-dashboard-line me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('discipline.students.*') ? 'active' : '' }}" 
                           href="{{ route('discipline.students.index') }}">
                            <i class="ri-team-line me-2"></i>
                            <span>Student Profiles</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('discipline.violations.*') ? 'active' : '' }}" 
                           href="{{ route('discipline.violations.index') }}">
                            <i class="ri-alert-line me-2"></i>
                            <span>Violations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="ri-file-list-line me-2"></i>Incident Reports
                        </a>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="ri-bar-chart-line me-2"></i>Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="ri-settings-line me-2"></i>Settings
                        </a>
                    </li> --}}
                </ul>
                
                <!-- Logout -->
                <div class="mt-auto pt-3">
                    <form method="POST" action="{{ route('discipline.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="ri-logout-box-line me-2"></i>Logout
                        </button>
                    </form>
                </div>
            </nav>
            
            <!-- Main Content -->
            <main class="col-12 col-md-10 px-4 py-4">
                {{ $slot }}
            </main>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    @vite(['resources/js/discipline.js'])
    
    @stack('scripts')
</body>
</html>
