<x-layout>
    @vite(['resources/css/landingpage.css'])
    
    <div class="row justify-content-center mt-5 pt-5">
        <div class="col-lg-6 col-md-8">
            <div class="content-card p-5">
                <!-- Header -->
                <div class="text-center mb-4">
                    <i class="ri-lock-line" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <h2 class="page-header mb-2" style="color: var(--primary-color);">Forgot Password?</h2>
                    <p class="text-muted">Don't worry! We'll help you reset your password</p>
                </div>

                <!-- Success Message -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-check-circle-line me-2"></i>
                        <strong>Success!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Info Message -->
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="ri-information-line me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

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
                    <strong>Select your account type</strong> and enter your email, student ID, or enrollee ID to receive password reset instructions.
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('password.forgot.send') }}" id="forgotPasswordForm">
                    @csrf

                    <!-- User Type Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color: var(--primary-color);">I am a:</label>
                        <div class="row g-2">
                            <!-- System User -->
                            <div class="col-4">
                                <div class="form-check">
                                    <input type="radio" id="system_user" name="user_type" value="system_user" class="form-check-input" required>
                                    <label for="system_user" class="form-check-label w-100 p-3 text-center border rounded" style="cursor: pointer; background: var(--light-gray); transition: all 0.3s;">
                                        <div><i class="ri-admin-line fs-5" style="color: var(--primary-color);"></i></div>
                                        <small class="fw-semibold">Nicolites Staff/Teacher</small>
                                    </label>
                                </div>
                            </div>

                            <!-- Student -->
                            <div class="col-4">
                                <div class="form-check">
                                    <input type="radio" id="student" name="user_type" value="student" class="form-check-input" required>
                                    <label for="student" class="form-check-label w-100 p-3 text-center border rounded" style="cursor: pointer; background: var(--light-gray); transition: all 0.3s;">
                                        <div><i class="ri-graduation-cap-line fs-5" style="color: var(--primary-color);"></i></div>
                                        <small class="fw-semibold">Student</small>
                                    </label>
                                </div>
                            </div>

                            <!-- Enrollee -->
                            <div class="col-4">
                                <div class="form-check">
                                    <input type="radio" id="enrollee" name="user_type" value="enrollee" class="form-check-input" required>
                                    <label for="enrollee" class="form-check-label w-100 p-3 text-center border rounded" style="cursor: pointer; background: var(--light-gray); transition: all 0.3s;">
                                        <div><i class="ri-user-add-line fs-5" style="color: var(--primary-color);"></i></div>
                                        <small class="fw-semibold">Enrollee</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Identifier Input -->
                    <div class="mb-4">
                        <label for="identifier" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-mail-line me-2"></i>Email, Student ID, or Enrollee ID
                        </label>
                        <input 
                            type="text" 
                            class="form-control form-control-lg @error('identifier') is-invalid @enderror" 
                            id="identifier" 
                            name="identifier" 
                            placeholder="Enter your email or ID"
                            value="{{ old('identifier') }}"
                            required
                        >
                        <small class="form-text text-muted d-block mt-2" id="identifierHint">
                            <i class="ri-information-line me-1"></i>
                            <span id="hintText">Select an account type to see the format</span>
                        </small>
                        @error('identifier')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-custom btn-lg w-100 mb-3" id="submitBtn">
                        <i class="ri-mail-send-line me-2"></i>
                        <span>Submit</span>
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
            const userTypeRadios = document.querySelectorAll('input[name="user_type"]');
            const identifierInput = document.getElementById('identifier');
            const hintText = document.getElementById('hintText');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('forgotPasswordForm');

            const hints = {
                system_user: 'Enter your email address (e.g., john@example.com)',
                student: 'Enter your student ID (e.g., NS-25001)',
                enrollee: 'Enter your enrollee/application ID (e.g., 25-001)'
            };

            const placeholders = {
                system_user: 'your@email.com',
                student: 'NS-25001',
                enrollee: '25-001'
            };

            // Update hint when user type changes
            userTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const userType = this.value;
                    hintText.textContent = hints[userType];
                    identifierInput.placeholder = placeholders[userType];
                    identifierInput.focus();
                });
            });

            // Handle form submission
            form.addEventListener('submit', function(e) {
                const selectedType = document.querySelector('input[name="user_type"]:checked');
                if (!selectedType) {
                    e.preventDefault();
                    alert('Please select your account type');
                    return;
                }

                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="ri-loader-4-line me-2 spin"></i><span>Sending...</span>';

                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                }, 3000);
            });
        });
    </script>
</x-layout>
