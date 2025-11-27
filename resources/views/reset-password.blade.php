<x-layout>
    @vite(['resources/css/landingpage.css'])
    
    <div class="row justify-content-center mt-5 pt-5">
        <div class="col-lg-6 col-md-8">
            <div class="content-card p-5">
                <!-- Header -->
                <div class="text-center mb-4">
                    <i class="ri-lock-unlock-line" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <h2 class="page-header mb-2" style="color: var(--primary-color);">Reset Password</h2>
                    <p class="text-muted">Create a new password for your account</p>
                </div>

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ri-error-warning-line me-2"></i>
                        <strong>Error!</strong>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Info Alert -->
                <div class="alert alert-info mb-4">
                    <i class="ri-information-line me-2"></i>
                    <strong>Create a strong password</strong> with at least 8 characters including uppercase, lowercase, and numbers.
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('password.reset.submit') }}" id="resetPasswordForm">
                    @csrf

                    <!-- Hidden Fields -->
                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- Reset Code -->
                    <div class="mb-4">
                        <label for="reset_code" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-key-2-line me-2"></i>Reset Code (6 characters)
                        </label>
                        <input 
                            type="text" 
                            class="form-control form-control-lg @error('reset_code') is-invalid @enderror" 
                            id="reset_code" 
                            name="reset_code" 
                            placeholder="Enter the code from your email"
                            maxlength="6"
                            value="{{ old('reset_code') }}"
                            required
                        >
                        @error('reset_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Requirements -->
                    <div class="alert alert-info mb-4">
                        <strong>Password Requirements:</strong>
                        <ul class="mb-0 mt-2">
                            <li id="req-length"><i class="ri-checkbox-blank-circle-line me-2"></i>At least 8 characters</li>
                            <li id="req-uppercase"><i class="ri-checkbox-blank-circle-line me-2"></i>One uppercase letter (A-Z)</li>
                            <li id="req-lowercase"><i class="ri-checkbox-blank-circle-line me-2"></i>One lowercase letter (a-z)</li>
                            <li id="req-number"><i class="ri-checkbox-blank-circle-line me-2"></i>One number (0-9)</li>
                        </ul>
                    </div>

                    <!-- New Password -->
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-lock-line me-2"></i>New Password
                        </label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                placeholder="Enter your new password"
                                value="{{ old('password') }}"
                                required
                            >
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-lock-check-line me-2"></i>Confirm Password
                        </label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                placeholder="Confirm your new password"
                                required
                            >
                            <button type="button" class="btn btn-outline-secondary" id="toggleConfirm">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-custom btn-lg w-100 mb-3" id="submitBtn">
                        <i class="ri-check-line me-2"></i>
                        <span>Reset Password</span>
                    </button>
                </form>

                <!-- Back Link -->
                <div class="text-center">
                    <a href="/" style="color: var(--primary-color); text-decoration: none;">
                        <i class="ri-arrow-left-line me-1"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('password_confirmation');
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirm = document.getElementById('toggleConfirm');
            const form = document.getElementById('resetPasswordForm');
            const submitBtn = document.getElementById('submitBtn');

            // Password visibility toggle
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="ri-eye-line"></i>' : '<i class="ri-eye-off-line"></i>';
            });

            toggleConfirm.addEventListener('click', function() {
                const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="ri-eye-line"></i>' : '<i class="ri-eye-off-line"></i>';
            });

            // Password strength checker
            function checkPasswordStrength(password) {
                const requirements = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /[0-9]/.test(password)
                };

                // Update requirement indicators
                document.getElementById('req-length').classList.toggle('text-success', requirements.length);
                document.getElementById('req-uppercase').classList.toggle('text-success', requirements.uppercase);
                document.getElementById('req-lowercase').classList.toggle('text-success', requirements.lowercase);
                document.getElementById('req-number').classList.toggle('text-success', requirements.number);

                return requirements;
            }

            passwordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const confirm = confirmInput.value;

                if (password !== confirm) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    return;
                }

                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="ri-loader-4-line me-2 spin"></i><span>Resetting...</span>';

                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                }, 3000);
            });

            // Initialize strength check
            if (passwordInput.value) {
                checkPasswordStrength(passwordInput.value);
            }
        });
    </script>
</x-layout>
