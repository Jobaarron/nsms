<x-layout>
    @vite('resources/css/enroll.css')
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="content-card p-5">
                <div class="text-center mb-4">
                    <i class="ri-file-user-line" style="font-size: 4rem; color: var(--primary-color);"></i>
                    <h2 class="page-header mb-2">Applicant Portal Login</h2>
                    <p class="text-muted">Login to track your enrollment application status</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

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

                <form method="POST" action="{{ route('enrollee.login.submit') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="application_id" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-user-card-line me-2"></i>Applicant ID
                        </label>
                        <input
                            type="text"
                            id="application_id"
                            name="application_id"
                            class="form-control form-control-lg @error('application_id') is-invalid @enderror"
                            value="{{ old('application_id') }}"
                            required
                            placeholder="Enter your Applicant ID (e.g., 25-001)"
                            autofocus
                        />
                        @error('application_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-lock-line me-2"></i>Password
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control form-control-lg @error('password') is-invalid @enderror"
                            required
                            placeholder="Enter your password (e.g., 25-001)"
                        />
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-custom btn-lg w-100 mb-3">
                        <i class="ri-login-circle-line me-2"></i>Login to Portal
                    </button>
                </form>

                {{-- <div class="text-center">
                    <p class="text-muted">
                        Don't have an Applicant ID? 
                        <a href="{{ route('enroll.create') }}" style="color: var(--primary-color);">Apply for enrollment</a>
                    </p>
                    <p class="text-muted small">
                        <i class="ri-information-line me-1"></i>
                        Your Applicant ID and Password were sent to your email after submitting your application.
                    </p>
                </div> --}}
            </div>
        </div>
    </div>
</x-layout>
