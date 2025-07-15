<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Generator • NSMS</title>
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet"/>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"/>
    
    <!-- Bootstrap 5 & Custom CSS -->
    @vite(['resources/sass/app.scss','resources/js/app.js'])
    @vite(['resources/css/admin_generator.css'])
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0 text-center">
                            <i class="ri-admin-line me-2"></i>Admin Account Generator
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        @if(session('success'))
                            <div class="alert alert-success mb-4">
                                <i class="ri-check-line me-2"></i>{{ session('success') }}
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="alert alert-danger mb-4">
                                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                            </div>
                        @endif
                        
                        @if($errors->any())
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        @if(!$adminRoleExists)
                            <div class="alert alert-info mb-4">
                                <i class="ri-information-line me-2"></i>
                                <strong>Setup Required:</strong> Admin roles and permissions will be created automatically. 
                                This will populate the necessary permission tables for proper access control.
                            </div>
                        @elseif($adminExists)
                            <div class="alert alert-info mb-4">
                                <i class="ri-information-line me-2"></i>
                                <strong>Additional Admin:</strong> An admin user already exists. Creating another admin will give you an additional admin account with full permissions.
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('generate.admin') }}" id="adminForm">
                            @csrf
                            
                            <!-- Basic User Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name') }}" required autofocus>
                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                               name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Admin Specific Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="employee_id" class="form-label">Employee ID</label>
                                        <input id="employee_id" type="text" class="form-control @error('employee_id') is-invalid @enderror" 
                                               name="employee_id" value="{{ old('employee_id') }}" placeholder="e.g., ADM001">
                                        @error('employee_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="department" class="form-label">Department</label>
                                        <select id="department" class="form-control @error('department') is-invalid @enderror" name="department">
                                            <option value="">Select Department</option>
                                            <option value="Administration" {{ old('department') == 'Administration' ? 'selected' : '' }}>Administration</option>
                                            <option value="Academic Affairs" {{ old('department') == 'Academic Affairs' ? 'selected' : '' }}>Academic Affairs</option>
                                            <option value="Student Affairs" {{ old('department') == 'Student Affairs' ? 'selected' : '' }}>Student Affairs</option>
                                            <option value="Finance" {{ old('department') == 'Finance' ? 'selected' : '' }}>Finance</option>
                                            <option value="IT Department" {{ old('department') == 'IT Department' ? 'selected' : '' }}>IT Department</option>
                                            <option value="Human Resources" {{ old('department') == 'Human Resources' ? 'selected' : '' }}>Human Resources</option>
                                        </select>
                                        @error('department')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Admin Level Selection -->
                            <div class="mb-3">
                                <label for="admin_level" class="form-label">Admin Level</label>
                                <select id="admin_level" class="form-control @error('admin_level') is-invalid @enderror" 
                                        name="admin_level" required>
                                    <option value="">Select Admin Level</option>
                                    <option value="super_admin" {{ old('admin_level') == 'super_admin' ? 'selected' : '' }}>
                                        Super Admin (Full System Access)
                                    </option>
                                    <option value="admin" {{ old('admin_level') == 'admin' ? 'selected' : '' }}>
                                        Admin (Standard Admin Access)
                                    </option>
                                    <option value="moderator" {{ old('admin_level') == 'moderator' ? 'selected' : '' }}>
                                        Moderator (Limited Admin Access)
                                    </option>
                                </select>
                                @error('admin_level')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <div class="form-text">
                                    <small>
                                        <strong>Super Admin:</strong> Full system access including user management, system settings, roles & permissions<br>
                                        <strong>Admin:</strong> Standard admin features, user management, enrollment management, reports<br>
                                        <strong>Moderator:</strong> Basic admin features, limited access to core functions
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Password Fields -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                               name="password" required>
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="password-confirm" class="form-label">Confirm Password</label>
                                        <input id="password-confirm" type="password" class="form-control" 
                                               name="password_confirmation" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="ri-user-add-line me-2"></i>Generate Admin Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="/" class="text-decoration-none text-primary">
                        <i class="ri-home-line me-1"></i>Return to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('adminForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="ri-loader-4-line me-2"></i>Creating Admin Account...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
