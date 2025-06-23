<x-layout>
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="content-card p-5">
                <!-- Header Section -->
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="ri-book-open-line" style="font-size: 4rem; color: var(--primary-color);"></i>
                    </div>
                    <h2 class="page-header mb-2">Student Enrollment</h2>
                    <p class="text-muted">Please fill out the form below to apply</p>
                </div>

                <!-- Enrollment Form -->
                <form method="POST" action="/enroll">
                    @csrf

                    <!-- Student Name -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                <i class="ri-user-line me-2"></i>First Name
                            </label>
                            <input 
                                type="text"
                                id="first_name"
                                name="first_name"
                                class="form-control form-control-lg @error('first_name') is-invalid @enderror"
                                value="{{ old('first_name') }}"
                                required
                                placeholder="e.g. John"
                                style="border-color: var(--secondary-color); border-width: 2px;"
                            >
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                <i class="ri-user-line me-2"></i>Last Name
                            </label>
                            <input 
                                type="text"
                                id="last_name"
                                name="last_name"
                                class="form-control form-control-lg @error('last_name') is-invalid @enderror"
                                value="{{ old('last_name') }}"
                                required
                                placeholder="e.g. Doe"
                                style="border-color: var(--secondary-color); border-width: 2px;"
                            >
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Date of Birth & Gender -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dob" class="form-label fw-semibold" style="color: var(--primary-color);">
                                <i class="ri-calendar-line me-2"></i>Date of Birth
                            </label>
                            <input 
                                type="date"
                                id="dob"
                                name="dob"
                                class="form-control form-control-lg @error('dob') is-invalid @enderror"
                                value="{{ old('dob') }}"
                                required
                                style="border-color: var(--secondary-color); border-width: 2px;"
                            >
                            @error('dob') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label fw-semibold" style="color: var(--primary-color);">
                                <i class="ri-genderless-line me-2"></i>Gender
                            </label>
                            <select 
                                id="gender"
                                name="gender"
                                class="form-select form-select-lg @error('gender') is-invalid @enderror"
                                required
                                style="border-color: var(--secondary-color); border-width: 2px;"
                            >
                                <option value="">Select Gender</option>
                                <option value="male"     {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female"   {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Contact Email -->
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
                            placeholder="your_email@gmail.com"
                            style="border-color: var(--secondary-color); border-width: 2px;"
                        >
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Program Selection -->
                    <div class="mb-3">
                        <label for="program" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-book-2-line me-2"></i>Select Program
                        </label>
                        <select 
                            id="program"
                            name="program"
                            class="form-select form-select-lg @error('program') is-invalid @enderror"
                            required
                            style="border-color: var(--secondary-color); border-width: 2px;"
                        >
                            <option value="">-- Choose Program --</option>
                            <option value="nursery"         {{ old('program')=='nursery' ? 'selected':'' }}>Nursery</option>
                            <option value="grade_school"    {{ old('program')=='grade_school'?'selected':'' }}>Grade School</option>
                            <option value="junior_high"     {{ old('program')=='junior_high'?'selected':'' }}>Junior High</option>
                            <option value="senior_high"     {{ old('program')=='senior_high'?'selected':'' }}>Senior High</option>
                        </select>
                        @error('program') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Terms Agreement -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input 
                                class="form-check-input @error('terms') is-invalid @enderror" 
                                type="checkbox" 
                                id="terms" 
                                name="terms" 
                                {{ old('terms') ? 'checked':'' }}
                                required
                            >
                            <label class="form-check-label text-muted" for="terms">
                                I agree to the 
                                <a href="/terms" class="text-decoration-none" style="color: var(--accent-color);">Terms and Conditions</a> and
                                <a href="/privacy" class="text-decoration-none" style="color: var(--accent-color);">Privacy Policy</a>
                            </label>
                            @error('terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Enroll Button -->
                    <button type="submit" class="btn btn-custom btn-lg w-100 mb-3">
                        <i class="ri-send-plane-line me-2"></i>Enroll Now
                    </button>

                    <!-- Back to Login -->
                    <div class="text-center">
                        <p class="text-muted mb-0">
                            Already applied? 
                            <a href="/login" class="text-decoration-none fw-semibold" style="color: var(--accent-color);">
                                Check Status / Login
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(1,68,33,0.25);
        }
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .content-card {
            animation: fadeInUp 0.6s ease-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    @endpush
</x-layout>
