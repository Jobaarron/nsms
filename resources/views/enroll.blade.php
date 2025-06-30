        <x-layout>
            @vite('resources/css/enroll.css')
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="content-card p-5">
                        <div class="text-center mb-4">
                            <i class="ri-user-add-line" style="font-size: 4rem; color: var(--primary-color);"></i>
                            <h2 class="page-header mb-2">Student Enrollment</h2>
                            <p class="text-muted">Please fill out the form below to apply</p>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                            <ul class="mb-0">
                            @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                            @endforeach
                            </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('enroll.store') }}" enctype="multipart/form-data">
                            @csrf
                            <!-- ID Photo Upload -->
                            <div class="mb-3">
                                <label 
                                  for="id_photo" 
                                  class="form-label fw-semibold" 
                                  style="color: var(--primary-color);"
                                >
                                  <i class="ri-image-add-line me-2"></i>
                                  ID Photo (JPG, PNG)
                                </label>
                                <input
                                  type="file"
                                  id="id_photo"
                                  name="id_photo"
                                  class="form-control form-control-lg @error('id_photo') is-invalid @enderror"
                                  accept=".jpg,.jpeg,.png"
                                  required
                                />
                                @error('id_photo')
                                  <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                              </div>

                            <!-- Name Fields -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="first_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                        First Name
                                    </label>
                                    <input
                                        type="text"
                                        id="first_name"
                                        name="first_name"
                                        class="form-control form-control-lg @error('first_name') is-invalid @enderror"
                                        value="{{ old('first_name') }}"
                                        required
                                        placeholder="John"
                                    >
                                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="middle_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                        Middle Name
                                    </label>
                                    <input
                                        type="text"
                                        id="middle_name"
                                        name="middle_name"
                                        class="form-control form-control-lg @error('middle_name') is-invalid @enderror"
                                        value="{{ old('middle_name') }}"
                                        placeholder="Doe"
                                    >
                                    @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="last_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                        Last Name
                                    </label>
                                    <input
                                        type="text"
                                        id="last_name"
                                        name="last_name"
                                        class="form-control form-control-lg @error('last_name') is-invalid @enderror"
                                        value="{{ old('last_name') }}"
                                        required
                                        placeholder="Smith"
                                    >
                                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- DOB & Religion -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="dob" class="form-label fw-semibold" style="color: var(--primary-color);">
                                        Date of Birth
                                    </label>
                                    <input
                                        type="date"
                                        id="dob"
                                        name="dob"
                                        class="form-control form-control-lg @error('dob') is-invalid @enderror"
                                        value="{{ old('dob') }}"
                                        required
                                    >
                                    @error('dob') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="religion" class="form-label fw-semibold" style="color: var(--primary-color);">
                                        Religion
                                    </label>
                                    <input
                                        type="text"
                                        id="religion"
                                        name="religion"
                                        class="form-control form-control-lg @error('religion') is-invalid @enderror"
                                        value="{{ old('religion') }}"
                                        placeholder="e.g. Roman Catholic"
                                    >
                                    @error('religion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Email & Address -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold" style="color: var(--primary-color);">
                                    Email Address
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}"
                                    required
                                    placeholder="johndoesmith12345@gmail.com"
                                >
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label fw-semibold" style="color: var(--primary-color);">
                                    Address
                                </label>
                                <textarea
                                    id="address"
                                    name="address"
                                    rows="2"
                                    class="form-control form-control-lg @error('address') is-invalid @enderror"
                                    required
                                    placeholder="Street, City, Province"
                                >{{ old('address') }}</textarea>
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Document Upload -->
                            <div class="mb-3">
                                <label 
                                  for="documents" 
                                  class="form-label fw-semibold" 
                                  style="color: var(--primary-color);"
                                >
                                  <i class="ri-file-list-3-line me-2"></i>
                                  Documents (PDF, DOCX, JPG, PNG)
                                </label>
                                <input
                                  type="file"
                                  id="documents"
                                  name="documents[]"
                                  class="form-control form-control-lg @error('documents') is-invalid @enderror"
                                  accept=".pdf,.docx,.jpg,.jpeg,.png"
                                  multiple
                                  required
                                />
                                @error('documents')
                                  <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                              </div>

                            <!-- Grade & Strand -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="grade_applied" class="form-label fw-semibold" style="color: var(--primary-color);">
                                        Grade Applied
                                    </label>
                                    <select
                                        id="grade_applied"
                                        name="grade_applied"
                                        class="form-select form-select-lg @error('grade_applied') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">-- Select Grade --</option>
                                        <option value="Nursery"    {{ old('grade_applied')=='Nursery'   ? 'selected':'' }}>Nursery</option>
                                        <option value="Kinder 1"   {{ old('grade_applied')=='Kinder 1'  ? 'selected':'' }}>Kinder 1</option>
                                        <option value="Kinder 2"   {{ old('grade_applied')=='Kinder 2'  ? 'selected':'' }}>Kinder 2</option>
                                        <option value="Grade 1"    {{ old('grade_applied')=='Grade 1'   ? 'selected':'' }}>Grade 1</option>
                                        <option value="Grade 2"    {{ old('grade_applied')=='Grade 2'   ? 'selected':'' }}>Grade 2</option>
                                        <option value="Grade 3"    {{ old('grade_applied')=='Grade 3'   ? 'selected':'' }}>Grade 3</option>
                                        <option value="Grade 4"    {{ old('grade_applied')=='Grade 4'   ? 'selected':'' }}>Grade 4</option>
                                        <option value="Grade 5"    {{ old('grade_applied')=='Grade 5'   ? 'selected':'' }}>Grade 5</option>
                                        <option value="Grade 6"    {{ old('grade_applied')=='Grade 6'   ? 'selected':'' }}>Grade 6</option>
                                        <option value="Grade 7"    {{ old('grade_applied')=='Grade 7'   ? 'selected':'' }}>Grade 7</option>
                                        <option value="Grade 8"    {{ old('grade_applied')=='Grade 8'   ? 'selected':'' }}>Grade 8</option>
                                        <option value="Grade 9"    {{ old('grade_applied')=='Grade 9'   ? 'selected':'' }}>Grade 9</option>
                                        <option value="Grade 10"   {{ old('grade_applied')=='Grade 10'  ? 'selected':'' }}>Grade 10</option>
                                        <option value="Grade 11"   {{ old('grade_applied')=='Grade 11'  ? 'selected':'' }}>Grade 11</option>
                                        <option value="Grade 12"   {{ old('grade_applied')=='Grade 12'  ? 'selected':'' }}>Grade 12</option>
                                    </select>
                                    @error('grade_applied') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="strand" class="form-label fw-semibold" style="color: var(--primary-color);">
                                        Strand (if applicable)
                                    </label>
                                    <input
                                        type="text"
                                        id="strand"
                                        name="strand"
                                        class="form-control form-control-lg @error('strand') is-invalid @enderror"
                                        value="{{ old('strand') }}"
                                        placeholder="e.g. STEM, ABM"
                                    >
                                    @error('strand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Parent / Guardian -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="guardian_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                        Name of Parent/Guardian
                                    </label>
                                    <input
                                        type="text"
                                        id="guardian_name"
                                        name="guardian_name"
                                        class="form-control form-control-lg @error('guardian_name') is-invalid @enderror"
                                        value="{{ old('guardian_name') }}"
                                        required
                                        placeholder="Jane Doe"
                                    >
                                    @error('guardian_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="guardian_contact" class="form-label fw-semibold" style="color: var(--primary-color);">
                                        Parent/Guardian's Contact No.
                                    </label>
                                    <input
                                        type="tel"
                                        id="guardian_contact"
                                        name="guardian_contact"
                                        class="form-control form-control-lg @error('guardian_contact') is-invalid @enderror"
                                        value="{{ old('guardian_contact') }}"
                                        required
                                        placeholder="09171234567"
                                    >
                                    @error('guardian_contact') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Last School Attended -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="color: var(--primary-color);">
                                    Last School Attended
                                </label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input
                                            class="form-check-input @error('last_school_type') is-invalid @enderror"
                                            type="radio"
                                            name="last_school_type"
                                            id="last_public"
                                            value="Public"
                                            {{ old('last_school_type')=='Public' ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label" for="last_public">Public</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input
                                            class="form-check-input @error('last_school_type') is-invalid @enderror"
                                            type="radio"
                                            name="last_school_type"
                                            id="last_private"
                                            value="Private"
                                            {{ old('last_school_type')=='Private' ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label" for="last_private">Private</label>
                                    </div>
                                </div>
                                @error('last_school_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                <input
                                    type="text"
                                    name="last_school_name"
                                    class="form-control form-control-lg mt-2 @error('last_school_name') is-invalid @enderror"
                                    value="{{ old('last_school_name') }}"
                                    placeholder="Name of last school"
                                >
                                @error('last_school_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Medical History -->
                            <div class="mb-3">
                                <label for="medical_history" class="form-label fw-semibold" style="color: var(--primary-color);">
                                    Medical History (e.g. Allergies, Conditions)
                                </label>
                                <textarea
                                    id="medical_history"
                                    name="medical_history"
                                    rows="2"
                                    class="form-control form-control-lg @error('medical_history') is-invalid @enderror"
                                    placeholder="List any allergies or conditions"
                                >{{ old('medical_history') }}</textarea>
                                @error('medical_history') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Mode of Payment & Preferred Schedule -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="payment_mode" class="form-label fw-semibold" style="color: var(--primary-color);">
                                        Mode of Payment
                                    </label>
                                    <select
                                        id="payment_mode"
                                        name="payment_mode"
                                        class="form-select form-select-lg @error('payment_mode') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">-- Select Mode --</option>
                                        <option value="Cash" {{ old('payment_mode')=='Cash'?'selected':'' }}>Cash</option>
                                        {{-- <option value="Online Payment" {{ old('payment_mode')=='Online'?'selected':'' }}>Online</option> --}}
                                    </select>
                                    @error('payment_mode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="preferred_schedule" class="form-label fw-semibold" style="color: var(--primary-color);">
                                        Preferred Schedule (if applicable)
                                    </label>
                                    <input
                                        type="date"
                                        id="preferred_schedule"
                                        name="preferred_schedule"
                                        class="form-control form-control-lg @error('preferred_schedule') is-invalid @enderror"
                                        value="{{ old('preferred_schedule') }}"
                                    >
                                    @error('preferred_schedule') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-custom btn-lg w-100 mb-3">
                                <i class="ri-send-plane-line me-2"></i>Enroll Now
                            </button>

                            {{-- <div class="text-center">
                                <p class="text-muted mb-0">
                                    Already applied?
                                    <a href="/login" class="text-decoration-none fw-semibold" style="color: var(--accent-color);">
                                        Check Status / Login
                                    </a>
                                </p>
                            </div> --}}
                        </form>
                    </div>
                </div>
            </div>

            @push('styles')
           
            @endpush
        </x-layout>
