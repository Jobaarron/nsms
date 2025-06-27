<x-layout>
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="content-card p-5">
                <!-- Header Section -->
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="ri-shield-user-line" style="font-size: 4rem; color: var(--primary-color);"></i>
                    </div>
                    <h2 class="page-header mb-2">Welcome Back</h2>
                    <p class="text-muted">Sign in to your account to continue</p>
                </div>

                <!-- Login Form -->
                <form method="POST" action="/login">
                    @csrf
                    
                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-mail-line me-2"></i>Email Address
                        </label>
                        <input 
                            type="email" 
                            class="form-control form-control-lg custom-input @error('email') is-invalid @enderror" 
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

                    <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-lock-line me-2"></i>Password
                        </label>
                        <div class="position-relative">
                            <input 
                                type="password" 
                                class="form-control form-control-lg custom-input @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                placeholder="Enter your password"
                                required
                                style="border-color: var(--secondary-color); border-width: 2px;"
                            >
                            <button 
                                type="button" 
                                class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                                onclick="togglePassword()"
                                style="color: var(--primary-color); text-decoration: none;"
                            >
                                <i class="ri-eye-line" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label text-muted" for="remember">
                                Remember me
                            </label>
                        </div>
                        <a href="/forgot-password" class="text-decoration-none" style="color: var(--accent-color);">
                            Forgot Password?
                        </a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn btn-custom btn-lg w-100 mb-3">
                        <i class="ri-login-circle-line me-2"></i>Sign In
                    </button>

                    <!-- Divider -->
                    {{-- <div class="text-center mb-3">
                        <span class="text-muted">or</span>
                    </div> --}}

                    <!-- Social Login Options -->
                    {{-- <div class="d-grid gap-2 mb-4">
                        <button type="button" class="btn btn-outline-secondary btn-lg">
                            <i class="ri-google-fill me-2"></i>Continue with Google
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg">
                            <i class="ri-microsoft-fill me-2"></i>Continue with Microsoft
                        </button>
                    </div>
                </form> --}}

                <!-- Sign Up Link -->
                <div class="text-center">
                    <p class="text-muted mb-0">
                        Don't have an account? 
                        <a href="/signup" class="text-decoration-none fw-semibold" style="color: var(--accent-color);">
                            Create Account
                        </a>
                    </p>
                </div>
            </div>

            <!-- Additional Info Card -->
            {{-- <div class="content-card p-4 mt-4 text-center">
                <div class="row g-3">
                    <div class="col-4">
                        <i class="ri-shield-check-line" style="font-size: 2rem; color: var(--primary-color);"></i>
                        <p class="small text-muted mb-0 mt-2">Secure</p>
                    </div>
                    <div class="col-4">
                        <i class="ri-time-line" style="font-size: 2rem; color: var(--accent-color);"></i>
                        <p class="small text-muted mb-0 mt-2">24/7 Access</p>
                    </div>
                    <div class="col-4">
                        <i class="ri-customer-service-line" style="font-size: 2rem; color: var(--dark-green);"></i>
                        <p class="small text-muted mb-0 mt-2">Support</p>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Custom Styles for Login Page -->
    <style>
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(1, 68, 33, 0.25);
        }
        
        /* Custom validation styling - Green glow effect instead of check icon */
        .custom-input.is-valid {
            border-color: #28a745;
            background-image: none; /* Remove the default check icon */
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            background-color: rgba(40, 167, 69, 0.05);
        }
        
        .custom-input.is-valid:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.4);
        }
        
        /* Enhanced glow animation for valid inputs */
        .custom-input.is-valid {
            animation: validGlow 0.3s ease-in-out;
        }
        
        @keyframes validGlow {
            0% {
                box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0);
            }
            50% {
                box-shadow: 0 0 0 0.3rem rgba(40, 167, 69, 0.4);
            }
            100% {
                box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            }
        }
        
        /* Custom invalid styling to match */
        .custom-input.is-invalid {
            border-color: #dc3545;
            background-image: none;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
            background-color: rgba(220, 53, 69, 0.05);
        }
        
        .custom-input.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.4);
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
        }
    </style>

    <!-- JavaScript for Password Toggle -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'ri-eye-off-line';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'ri-eye-line';
            }
        }

        // Add form validation feedback with custom styling
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input[required].custom-input');
            
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
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
