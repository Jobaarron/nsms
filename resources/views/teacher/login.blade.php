<x-layout>
    @vite('resources/css/enroll.css')
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="content-card p-5">
                <div class="text-center mb-4">
                    <i class="ri-user-3-line" style="font-size: 4rem; color: var(--primary-color);"></i>
                    <h2 class="page-header mb-2">Teacher Login</h2>
                    <p class="text-muted">Login to access your teacher dashboard</p>
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

                <form method="POST" action="{{ route('teacher.login.submit') }}">
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
                            placeholder="your.email@example.com"
                        />
                        @error('email')
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
                            placeholder="Enter your password"
                        />
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-custom btn-lg w-100 mb-3">
                        <i class="ri-login-circle-line me-2"></i>Login
                    </button>
                </form>

                {{-- <div class="text-center">
                    <p class="text-muted">
                        Don't have an account? 
                        <a href="{{ route('teacher.generator') }}" style="color: var(--primary-color);">Create Teacher Account</a>
                    </p>
                </div> --}}
            </div>
        </div>
    </div>
</x-layout>
