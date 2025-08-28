<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Account Generator • NSMS</title>
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet"/>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"/>
    
    <!-- Bootstrap 5 & Custom CSS -->
    @vite(['resources/sass/app.scss','resources/js/app.js'])
    @vite(['resources/css/admin_generator.css'])
    <style>
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0 text-center">
                            <i class="ri-user-3-line me-2"></i>Teacher Account Generator
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        @if(session('success'))
                            <div class="alert alert-success mb-4">
                                <i class="ri-check-line me-2"></i>{{ session('success') }}
                                <div class="mt-2">
                                    <a href="{{ route('teacher.login') }}" class="btn btn-sm btn-success">
                                        <i class="ri-login-circle-line me-1"></i>Login Now
                                    </a>
                                </div>
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
                        
                        @if(!$teacherRoleExists)
                            <div class="alert alert-info mb-4">
                                <i class="ri-information-line me-2"></i>
                                <strong>Setup Required:</strong> Teacher roles and permissions will be created automatically. 
                                This will populate the necessary permission tables for proper access control.
                            </div>
                        @elseif($teacherExists)
                            <div class="alert alert-info mb-4">
                                <i class="ri-information-line me-2"></i>
                                <strong>Additional Teacher:</strong> A teacher account already exists. Creating another will give you an additional teacher account.
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('generate.teacher') }}" id="teacherForm">
                            @csrf
                            
                            <!-- Basic Information -->
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
                            
                            <!-- Teacher Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="employee_id" class="form-label">Employee ID</label>
                                        <input id="employee_id" type="text" class="form-control @error('employee_id') is-invalid @enderror" 
                                               name="employee_id" value="{{ old('employee_id') }}" required 
                                               placeholder="e.g., TCH001">
                                        @error('employee_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="position" class="form-label">Position/Title</label>
                                        <input id="position" type="text" class="form-control @error('position') is-invalid @enderror" 
                                               name="position" value="{{ old('position') }}" required
                                               placeholder="e.g., Senior Teacher, Department Head">
                                        @error('position')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="department" class="form-label">Department</label>
                                        <select id="department" class="form-control @error('department') is-invalid @enderror" 
                                                name="department" required>
                                            <option value="">Select Department</option>
                                            <option value="Elementary" {{ old('department') == 'Elementary' ? 'selected' : '' }}>Elementary</option>
                                            <option value="Junior High School" {{ old('department') == 'Junior High School' ? 'selected' : '' }}>Junior High School</option>
                                            <option value="Senior High School" {{ old('department') == 'Senior High School' ? 'selected' : '' }}>Senior High School</option>
                                            <option value="Mathematics" {{ old('department') == 'Mathematics' ? 'selected' : '' }}>Mathematics</option>
                                            <option value="Science" {{ old('department') == 'Science' ? 'selected' : '' }}>Science</option>
                                            <option value="English" {{ old('department') == 'English' ? 'selected' : '' }}>English</option>
                                            <option value="Filipino" {{ old('department') == 'Filipino' ? 'selected' : '' }}>Filipino</option>
                                            <option value="Social Studies" {{ old('department') == 'Social Studies' ? 'selected' : '' }}>Social Studies</option>
                                            <option value="MAPEH" {{ old('department') == 'MAPEH' ? 'selected' : '' }}>MAPEH</option>
                                            <option value="TLE" {{ old('department') == 'TLE' ? 'selected' : '' }}>TLE</option>
                                            <option value="ICT" {{ old('department') == 'ICT' ? 'selected' : '' }}>ICT</option>
                                            <option value="TVL" {{ old('department') == 'TVL' ? 'selected' : '' }}>TVL</option>
                                            <option value="GAS" {{ old('department') == 'GAS' ? 'selected' : '' }}>GAS</option>
                                            <option value="HUMSS" {{ old('department') == 'HUMSS' ? 'selected' : '' }}>HUMSS</option>
                                            <option value="STEM" {{ old('department') == 'STEM' ? 'selected' : '' }}>STEM</option>
                                            <option value="ABM" {{ old('department') == 'ABM' ? 'selected' : '' }}>ABM</option>
                                        </select>
                                        @error('department')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="hire_date" class="form-label">Hire Date</label>
                                        <input id="hire_date" type="date" class="form-control @error('hire_date') is-invalid @enderror" 
                                               name="hire_date" value="{{ old('hire_date') }}" required>
                                        @error('hire_date')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone_number" class="form-label">Phone Number</label>
                                        <input id="phone_number" type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                                               name="phone_number" value="{{ old('phone_number') }}" 
                                               placeholder="e.g., +63 912 345 6789">
                                        @error('phone_number')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <input id="address" type="text" class="form-control @error('address') is-invalid @enderror" 
                                               name="address" value="{{ old('address') }}" 
                                               placeholder="Complete address">
                                        @error('address')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="qualifications" class="form-label">Qualifications & Certifications</label>
                                <textarea id="qualifications" class="form-control @error('qualifications') is-invalid @enderror" 
                                          name="qualifications" rows="3" 
                                          placeholder="Educational background, certifications, relevant experience...">{{ old('qualifications') }}</textarea>
                                @error('qualifications')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
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
                                    <i class="ri-user-add-line me-2"></i>Generate Teacher Account
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
        document.getElementById('teacherForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line me-2 animate-spin"></i>Creating Account...';
        });
    </script>
</body>
</html>
