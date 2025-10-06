<x-layout>
    @vite(['resources/css/enroll.css', 'resources/css/password-field.css'])
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="content-card p-5">
                <div class="text-center mb-4">
                    <i class="ri-money-dollar-circle-line" style="font-size: 4rem; color: var(--primary-color);"></i>
                    <h2 class="page-header mb-2">Cashier Portal Login</h2>
                    <p class="text-muted">Login to access the cashier management system</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            
            <form method="POST" action="{{ route('cashier.login.submit') }}">
                @csrf
                
                <div class="mb-3">
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
                        autofocus
                    />
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold" style="color: var(--primary-color);">
                        <i class="ri-lock-line me-2"></i>Password
                    </label>
                    <div class="password-input-container">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control form-control-lg custom-password-input @error('password') is-invalid @enderror"
                            required
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            data-toggle="password"
                        />
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('password')" style="color: var(--primary-color);">
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
                    <i class="ri-login-circle-line me-2"></i>Login to Portal
                </button>
                </form>

                <div class="text-center">
                    <small class="text-muted">
                        <i class="ri-information-line me-1"></i>
                        For cashier staff only - Contact administrator for assistance
                    </small>
                </div>
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
    </script>
</x-layout>
