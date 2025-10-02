<x-layout>
    @vite('resources/css/enroll.css')
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="content-card p-5">
                <div class="text-center mb-4">
                    <i class="ri-building-line" style="font-size: 4rem; color: var(--primary-color);"></i>
                    <h2 class="page-header mb-2">Registrar Login</h2>
                    <p class="text-muted">Login to access the registrar portal</p>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('registrar.login') }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-mail-line me-2"></i>Email Address
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control form-control-lg @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            required
                            placeholder="Enter your email address"
                            autocomplete="email"
                        />
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-lock-line me-2"></i>Password
                        </label>
                        <div class="position-relative">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control form-control-lg @error('password') is-invalid @enderror"
                                required
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                style="padding-right: 3rem;"
                            />
                            <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y me-2" onclick="togglePassword('password')" style="border: none; background: none; color: var(--primary-color);">
                                <i class="ri-eye-line" id="password-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>

                    <button type="submit" class="btn btn-custom btn-lg w-100 mb-3">
                        <i class="ri-login-circle-line me-2"></i>Login
                    </button>
                </form>

                {{-- <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="ri-information-line me-1"></i>
                        For Registrar & Administration only
                    </small>
                </div> --}}
                
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(fieldId + '-eye');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.className = 'ri-eye-off-line';
            } else {
                passwordField.type = 'password';
                eyeIcon.className = 'ri-eye-line';
            }
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>
</x-layout>
