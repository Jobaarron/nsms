<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guidance Account Generator • NSMS</title>
    
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
                            <i class="ri-shield-user-line me-2"></i>Guidance & Discipline Account Generator
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        @if(session('success'))
                            <div class="alert alert-success mb-4">
                                <i class="ri-check-line me-2"></i>{{ session('success') }}
                                <div class="mt-2">
                                    <a href="{{ route('guidance.login') }}" class="btn btn-sm btn-success">
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
                        
                        @if(!isset($guidanceRoleExists) || !$guidanceRoleExists)
                            <div class="alert alert-info mb-4">
                                <i class="ri-information-line me-2"></i>
                                <strong>Initial Setup:</strong> Guidance roles and permissions will be created automatically. 
                                This will populate the necessary permission tables for proper access control.
                            </div>
                        @elseif(isset($guidanceExists) && $guidanceExists)
                            <div class="alert alert-info mb-4">
                                <i class="ri-information-line me-2"></i>
                                <strong>Additional Account:</strong> A guidance staff member already exists. Creating another account will give you an additional staff member with role-specific permissions.
                            </div>
                        @else
                            <div class="alert alert-info mb-4">
                                <i class="ri-information-line me-2"></i>
                                <strong>Account Creation:</strong> Create accounts for guidance counselors, discipline officers, and security guards. 
                                Each role will have specific permissions and access levels within the guidance and discipline system.
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('guidance.generator.submit') }}" id="guidanceForm">
                            @csrf
                            
                            <!-- Basic User Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                               name="first_name" value="{{ old('first_name') }}" required autofocus>
                                        @error('first_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                               name="last_name" value="{{ old('last_name') }}" required>
                                        @error('last_name')
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
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone_number" class="form-label">Phone Number</label>
                                        <input id="phone_number" type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                                               name="phone_number" value="{{ old('phone_number') }}" placeholder="e.g., +63 912 345 6789">
                                        @error('phone_number')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Staff Specific Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="employee_id" class="form-label">Employee ID</label>
                                        <input id="employee_id" type="text" class="form-control @error('employee_id') is-invalid @enderror" 
                                               name="employee_id" value="{{ old('employee_id') }}" placeholder="e.g., GD001, SC002, DP003" required>
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
                                               name="position" value="{{ old('position') }}" placeholder="e.g., Senior Counselor, Head of Discipline">
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
                                        <label for="hire_date" class="form-label">Hire Date</label>
                                        <input id="hire_date" type="date" class="form-control @error('hire_date') is-invalid @enderror" 
                                               name="hire_date" value="{{ old('hire_date') }}">
                                        @error('hire_date')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea id="address" class="form-control @error('address') is-invalid @enderror" 
                                                  name="address" rows="2" placeholder="Complete address">{{ old('address') }}</textarea>
                                        @error('address')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Role Selection -->
                            <div class="mb-3">
                                <label for="role" class="form-label">Role & Department</label>
                                <select id="role" class="form-control @error('role') is-invalid @enderror" 
                                        name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="guidance_counselor" {{ old('role') == 'guidance_counselor' ? 'selected' : '' }}>
                                        Guidance Counselor (Guidance Department)
                                    </option>
                                    <option value="discipline_officer" {{ old('role') == 'discipline_officer' ? 'selected' : '' }}>
                                        Discipline Officer (Discipline Department)
                                    </option>
                                    <option value="security_guard" {{ old('role') == 'security_guard' ? 'selected' : '' }}>
                                        Security Guard (Security Department)
                                    </option>
                                </select>
                                @error('role')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <div class="form-text">
                                    <small>
                                        <strong>Guidance Counselor:</strong> Student counseling, academic guidance, career advice, psychological support<br>
                                        <strong>Discipline Officer:</strong> Violation management, disciplinary actions, student behavior monitoring<br>
                                        <strong>Security Guard:</strong> Campus security, facial recognition access, incident reporting
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Emergency Contact Information -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                                        <input id="emergency_contact_name" type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                               name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" placeholder="Full name">
                                        @error('emergency_contact_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                                        <input id="emergency_contact_phone" type="text" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                               name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" placeholder="Phone number">
                                        @error('emergency_contact_phone')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="emergency_contact_relationship" class="form-label">Relationship</label>
                                        <select id="emergency_contact_relationship" class="form-control @error('emergency_contact_relationship') is-invalid @enderror" 
                                                name="emergency_contact_relationship">
                                            <option value="">Select Relationship</option>
                                            <option value="spouse" {{ old('emergency_contact_relationship') == 'spouse' ? 'selected' : '' }}>Spouse</option>
                                            <option value="parent" {{ old('emergency_contact_relationship') == 'parent' ? 'selected' : '' }}>Parent</option>
                                            <option value="sibling" {{ old('emergency_contact_relationship') == 'sibling' ? 'selected' : '' }}>Sibling</option>
                                            <option value="child" {{ old('emergency_contact_relationship') == 'child' ? 'selected' : '' }}>Child</option>
                                            <option value="friend" {{ old('emergency_contact_relationship') == 'friend' ? 'selected' : '' }}>Friend</option>
                                            <option value="other" {{ old('emergency_contact_relationship') == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('emergency_contact_relationship')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Qualifications -->
                            <div class="mb-3">
                                <label for="qualifications" class="form-label">Qualifications & Certifications</label>
                                <textarea id="qualifications" class="form-control @error('qualifications') is-invalid @enderror" 
                                          name="qualifications" rows="3" placeholder="Educational background, certifications, relevant experience...">{{ old('qualifications') }}</textarea>
                                @error('qualifications')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                            <!-- Additional Notes -->
                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                          name="notes" rows="2" placeholder="Any additional information or special notes...">{{ old('notes') }}</textarea>
                                @error('notes')
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
                                        <div class="form-text">
                                            <small>Minimum 8 characters required</small>
                                        </div>
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
                                    <i class="ri-shield-user-line me-2"></i>Create Guidance Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    @auth
                        @if(session('guidance_user'))
                            <a href="{{ route('guidance.dashboard') }}" class="text-decoration-none text-primary me-3">
                                <i class="ri-dashboard-line me-1"></i>Back to Dashboard
                            </a>
                        @endif
                    @endauth
                    <a href="{{ route('guidance.login') }}" class="text-decoration-none text-success me-3">
                        <i class="ri-login-circle-line me-1"></i>Login to Guidance System
                    </a>
                    <a href="/" class="text-decoration-none text-secondary">
                        <i class="ri-home-line me-1"></i>Return to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('guidanceForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="ri-loader-4-line me-2"></i>Creating Account...';
            submitBtn.disabled = true;
        });

        // Auto-generate employee ID based on role selection
        document.getElementById('role').addEventListener('change', function() {
            const employeeIdField = document.getElementById('employee_id');
            const role = this.value;
            
            if (role && !employeeIdField.value) {
                let prefix = '';
                switch(role) {
                    case 'guidance_counselor':
                        prefix = 'GC';
                        break;
                    case 'discipline_officer':
                        prefix = 'DO';
                        break;
                    case 'security_guard':
                        prefix = 'SG';
                        break;
                }
                
                if (prefix) {
                    // Generate a random 3-digit number
                    const randomNum = Math.floor(Math.random() * 900) + 100;
                    employeeIdField.value = prefix + randomNum;
                    employeeIdField.placeholder = `e.g., ${prefix}001, ${prefix}002, ${prefix}003`;
                }
            }
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            let strengthIndicator = document.getElementById('password-strength');
            
            if (!strengthIndicator) {
                const indicator = document.createElement('div');
                indicator.id = 'password-strength';
                indicator.className = 'form-text';
                this.parentNode.appendChild(indicator);
                strengthIndicator = indicator;
            }
            
            let strength = 0;
            let message = '';
            let className = '';
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            switch(strength) {
                case 0:
                case 1:
                    message = 'Very Weak';
                    className = 'text-danger';
                    break;
                case 2:
                    message = 'Weak';
                    className = 'text-warning';
                    break;
                case 3:
                    message = 'Fair';
                    className = 'text-info';
                    break;
                case 4:
                    message = 'Good';
                    className = 'text-success';
                    break;
                case 5:
                    message = 'Strong';
                    className = 'text-success fw-bold';
                    break;
            }
            
            strengthIndicator.innerHTML = 
                `<small class="${className}">Password Strength: ${message}</small>`;
        });

        // Form validation
        document.getElementById('guidanceForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password-confirm').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>
