<x-layout>
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="content-card p-5">
                <!-- Header Section -->
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="ri-user-add-line" style="font-size: 4rem; color: var(--primary-color);"></i>
                    </div>
                    <h2 class="page-header mb-2">Create Account</h2>
                    {{-- <p class="text-muted">Join our school management system</p> --}}
                </div>

                <!-- Registration Form -->
                <form method="POST" action="/register">
                    @csrf
                    
                    <!-- Name Fields Row -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                <i class="ri-user-line me-2"></i>First Name
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-lg @error('first_name') is-invalid @enderror" 
                                id="first_name" 
                                name="first_name" 
                                value="{{ old('first_name') }}"
                                placeholder="Enter your first name"
                                required
                                style="border-color: var(--secondary-color); border-width: 2px;"
                            >
                            @error('first_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                <i class="ri-user-line me-2"></i>Last Name
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-lg @error('last_name') is-invalid @enderror" 
                                id="last_name" 
                                name="last_name" 
                                value="{{ old('last_name') }}"
                                placeholder="Enter your last name"
                                required
                                style="border-color: var(--secondary-color); border-width: 2px;"
                            >
                            @error('last_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-mail-line me-2"></i>Email Address
                        </label>
                        <input 
                            type="email" 
                            class="form-control form-control-lg @error('email') is-invalid @enderror" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            placeholder="Enter your email address"
                            required
                            style="border-color: var(--secondary-color); border-width: 2px;"
                        >
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Role Selection -->
                    <div class="mb-3">
                        <label for="role" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-shield-user-line me-2"></i>Role
                        </label>
                        <select 
                            class="form-select form-select-lg @error('role') is-invalid @enderror" 
                            id="role" 
                            name="role" 
                            required
                            style="border-color: var(--secondary-color); border-width: 2px;"
                        >
                            <option value="">Select your role</option>
                            <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="counselor" {{ old('role') == 'counselor' ? 'selected' : '' }}>Counselor</option>
                            <option value="administrator" {{ old('role') == 'administrator' ? 'selected' : '' }}>Administrator</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password Fields Row -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold" style="color: var(--primary-color);">
                                <i class="ri-lock-line me-2"></i>Password
                            </label>
                            <div class="position-relative">
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Enter your password"
                                    required
                                    style="border-color: var(--secondary-color); border-width: 2px;"
                                >
                                <button 
                                    type="button" 
                                    class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                                    onclick="togglePassword('password', 'toggleIcon1')"
                                    style="color: var(--primary-color); text-decoration: none;"
                                >
                                    <i class="ri-eye-line" id="toggleIcon1"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold" style="color: var(--primary-color);">
                                <i class="ri-lock-line me-2"></i>Confirm Password
                            </label>
                            <div class="position-relative">
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror" 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    placeholder="Confirm your password"
                                    required
                                    style="border-color: var(--secondary-color); border-width: 2px;"
                                >
                                <button 
                                    type="button" 
                                    class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                                    onclick="togglePassword('password_confirmation', 'toggleIcon2')"
                                    style="color: var(--primary-color); text-decoration: none;"
                                >
                                    <i class="ri-eye-line" id="toggleIcon2"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="terms" name="terms" required>
                            <label class="form-check-label text-muted" for="terms">
                                I agree to the 
                                <a href="/terms" class="text-decoration-none" style="color: var(--accent-color);">Terms and Conditions</a>
                                and 
                                <a href="/privacy" class="text-decoration-none" style="color: var(--accent-color);">Privacy Policy</a>
                            </label>
                            @error('terms')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Register Button -->
                    <button type="submit" class="btn btn-custom btn-lg w-100 mb-3">
                        <i class="ri-user-add-line me-2"></i>Create Account
                    </button>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="text-muted mb-0">
                        Already have an account? 
                        <a href="/login" class="text-decoration-none fw-semibold" style="color: var(--accent-color);">
                            Sign In
                        </a>
                    </p>
                </div>
              </div>
        </div>
    </div>

    <!-- Custom Styles for Registration Page -->
    <style>
        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(1, 68, 33, 0.25);
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(1, 68, 33, 0.25);
        }
        
        .btn-outline-secondary {
            border-color: var(--secondary-color);
            color: var(--primary-color);
            border-width: 2px;
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--primary-color);
        }
        
        .content-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 768px) {
            .content-card {
                margin: 1rem;
                padding: 2rem !important;
            }
            
            .row.mb-3 .col-md-6 {
                margin-bottom: 1rem;
            }
        }
    </style>

    <!-- JavaScript for Password Toggle and Validation -->
    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'ri-eye-off-line';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'ri-eye-line';
            }
        }

        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');
            
            function validatePasswordMatch() {
                if (password.value !== passwordConfirmation.value) {
                    passwordConfirmation.setCustomValidity('Passwords do not match');
                    passwordConfirmation.classList.add('is-invalid');
                } else {
                    passwordConfirmation.setCustomValidity('');
                    passwordConfirmation.classList.remove('is-invalid');
                    if (passwordConfirmation.value.length > 0) {
                        passwordConfirmation.classList.add('is-valid');
                    }
                }
            }

            password.addEventListener('input', validatePasswordMatch);
            passwordConfirmation.addEventListener('input', validatePasswordMatch);

            // Add form validation feedback
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input[required], select[required]');
            
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid') && this.value.trim() !== '') {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                });
            });
        });
    </script>
</x-layout>
