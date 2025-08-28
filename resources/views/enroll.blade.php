<x-layout>
    @vite('resources/css/enroll.css')
    @vite('resources/js/enroll.js')
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
                        <label for="id_photo" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-image-add-line me-2"></i>ID Photo (JPG, PNG)
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

                    <!-- Student Info -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="lrn" class="form-label fw-semibold" style="color: var(--primary-color);">
                                LRN (Learner Reference Number)
                            </label>
                            <input
                                type="text"
                                id="lrn"
                                name="lrn"
                                class="form-control form-control-lg @error('lrn') is-invalid @enderror"
                                value="{{ old('lrn') }}"
                                placeholder="123456789012"
                            >
                            @error('lrn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="student_type" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Student Type
                            </label>
                            <select
                                id="student_type"
                                name="student_type"
                                class="form-select form-select-lg @error('student_type') is-invalid @enderror"
                                required
                            >
                                <option value="">-- Select Type --</option>
                                <option value="new" {{ old('student_type')=='new' ? 'selected':'' }}>New Student</option>
                                <option value="transferee" {{ old('student_type')=='transferee' ? 'selected':'' }}>Transferee</option>
                                <option value="returnee" {{ old('student_type')=='returnee' ? 'selected':'' }}>Returnee</option>
                                <option value="continuing" {{ old('student_type')=='continuing' ? 'selected':'' }}>Continuing</option>
                            </select>
                            @error('student_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Name Fields -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="first_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                First Name
                            </label>
                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                class="form-control form-control-lg text-uppercase @error('first_name') is-invalid @enderror"
                                value="{{ old('first_name') }}"
                                required
                                placeholder="John"
                                oninput="this.value = this.value.toUpperCase()"
                            >
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="middle_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Middle Name
                            </label>
                            <input
                                type="text"
                                id="middle_name"
                                name="middle_name"
                                class="form-control form-control-lg text-uppercase @error('middle_name') is-invalid @enderror"
                                value="{{ old('middle_name') }}"
                                placeholder="Doe"
                                oninput="this.value = this.value.toUpperCase()"
                            >
                            @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="last_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Last Name
                            </label>
                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                class="form-control form-control-lg text-uppercase @error('last_name') is-invalid @enderror"
                                value="{{ old('last_name') }}"
                                required
                                placeholder="Smith"
                                oninput="this.value = this.value.toUpperCase()"
                            >
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="suffix" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Suffix
                            </label>
                            <input
                                type="text"
                                id="suffix"
                                name="suffix"
                                class="form-control form-control-lg text-uppercase @error('suffix') is-invalid @enderror"
                                value="{{ old('suffix') }}"
                                placeholder="Jr., Sr., III"
                                oninput="this.value = this.value.toUpperCase()"
                            >
                            @error('suffix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Personal Info -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="date_of_birth" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Date of Birth
                            </label>
                            <input
                                type="date"
                                id="date_of_birth"
                                name="date_of_birth"
                                class="form-control form-control-lg @error('date_of_birth') is-invalid @enderror"
                                value="{{ old('date_of_birth') }}"
                                max="{{ date('Y-m-d') }}"
                                required
                            >
                            @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="gender" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Gender
                            </label>
                            <select
                                id="gender"
                                name="gender"
                                class="form-select form-select-lg @error('gender') is-invalid @enderror"
                                required
                            >
                                <option value="">-- Select Gender --</option>
                                <option value="male" {{ old('gender')=='male' ? 'selected':'' }}>Male</option>
                                <option value="female" {{ old('gender')=='female' ? 'selected':'' }}>Female</option>
                            </select>
                            @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="religion" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Religion
                            </label>
                            <input
                                type="text"
                                id="religion"
                                name="religion"
                                class="form-control form-control-lg text-uppercase @error('religion') is-invalid @enderror"
                                value="{{ old('religion') }}"
                                placeholder="Roman Catholic"
                                oninput="this.value = this.value.toUpperCase()"
                            />
                            @error('religion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="row mb-3">
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <label for="contact_number" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Contact Number
                            </label>
                            <input
                                type="tel"
                                id="contact_number"
                                name="contact_number"
                                class="form-control form-control-lg @error('contact_number') is-invalid @enderror"
                                value="{{ old('contact_number') }}"
                                placeholder="09171234567"
                            >
                            @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="mb-3">
                        <label for="address" class="form-label fw-semibold" style="color: var(--primary-color);">
                            Complete Address
                        </label>
                        <textarea
                            id="address"
                            name="address"
                            rows="2"
                            class="form-control form-control-lg text-uppercase @error('address') is-invalid @enderror"
                            required
                            placeholder="House No., Street, Barangay"
                            oninput="this.value = this.value.toUpperCase()"
                        >{{ old('address') }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="city" class="form-label fw-semibold" style="color: var(--primary-color);">
                                City/Municipality
                            </label>
                            <input
                                type="text"
                                id="city"
                                name="city"
                                class="form-control form-control-lg text-uppercase @error('city') is-invalid @enderror"
                                value="{{ old('city') }}"
                                placeholder="QUEZON CITY"
                                oninput="this.value = this.value.toUpperCase()"
                            >
                            @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="province" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Province
                            </label>
                            <input
                                type="text"
                                id="province"
                                name="province"
                                class="form-control form-control-lg text-uppercase @error('province') is-invalid @enderror"
                                value="{{ old('province') }}"
                                placeholder="METRO MANILA"
                                oninput="this.value = this.value.toUpperCase()"
                            >
                            @error('province') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="zip_code" class="form-label fw-semibold" style="color: var(--primary-color);">
                                ZIP Code
                            </label>
                            <input
                                type="text"
                                id="zip_code"
                                name="zip_code"
                                class="form-control form-control-lg @error('zip_code') is-invalid @enderror"
                                value="{{ old('zip_code') }}"
                                placeholder="1100"
                            >
                            @error('zip_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="mb-3">
                        <label for="documents" class="form-label fw-semibold" style="color: var(--primary-color);">
                            <i class="ri-file-list-3-line me-2"></i>Documents (PDF, DOCX, JPG, PNG)
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
                        @error('documents') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Grade & Strand -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="grade_level" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Grade Level Applied
                            </label>
                            <select
                                id="grade_level"
                                name="grade_level"
                                class="form-select form-select-lg @error('grade_level') is-invalid @enderror"
                                required
                            >
                                <option value="">-- Select Grade --</option>
                                <option value="Nursery" {{ old('grade_level')=='Nursery' ? 'selected':'' }}>Nursery</option>
                                <option value="Kinder 1" {{ old('grade_level')=='Kinder 1' ? 'selected':'' }}>Kinder 1</option>
                                <option value="Kinder 2" {{ old('grade_level')=='Kinder 2' ? 'selected':'' }}>Kinder 2</option>
                                <option value="Grade 1" {{ old('grade_level')=='Grade 1' ? 'selected':'' }}>Grade 1</option>
                                <option value="Grade 2" {{ old('grade_level')=='Grade 2' ? 'selected':'' }}>Grade 2</option>
                                <option value="Grade 3" {{ old('grade_level')=='Grade 3' ? 'selected':'' }}>Grade 3</option>
                                <option value="Grade 4" {{ old('grade_level')=='Grade 4' ? 'selected':'' }}>Grade 4</option>
                                <option value="Grade 5" {{ old('grade_level')=='Grade 5' ? 'selected':'' }}>Grade 5</option>
                                <option value="Grade 6" {{ old('grade_level')=='Grade 6' ? 'selected':'' }}>Grade 6</option>
                                <option value="Grade 7" {{ old('grade_level')=='Grade 7' ? 'selected':'' }}>Grade 7</option>
                                <option value="Grade 8" {{ old('grade_level')=='Grade 8' ? 'selected':'' }}>Grade 8</option>
                                <option value="Grade 9" {{ old('grade_level')=='Grade 9' ? 'selected':'' }}>Grade 9</option>
                                <option value="Grade 10" {{ old('grade_level')=='Grade 10' ? 'selected':'' }}>Grade 10</option>
                                <option value="Grade 11" {{ old('grade_level')=='Grade 11' ? 'selected':'' }}>Grade 11</option>
                                <option value="Grade 12" {{ old('grade_level')=='Grade 12' ? 'selected':'' }}>Grade 12</option>
                            </select>
                            @error('grade_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 d-none" id="strand-group">
                            <label for="strand" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Strand (for Grade 11-12)
                            </label>
                            <select
                                id="strand"
                                name="strand"
                                class="form-select form-select-lg @error('strand') is-invalid @enderror"
                            >
                                <option value="">-- Select Strand --</option>
                                <option value="STEM" {{ old('strand')=='STEM' ? 'selected':'' }}>STEM</option>
                                <option value="ABM" {{ old('strand')=='ABM' ? 'selected':'' }}>ABM</option>
                                <option value="HUMSS" {{ old('strand')=='HUMSS' ? 'selected':'' }}>HUMSS</option>
                                <option value="GAS" {{ old('strand')=='GAS' ? 'selected':'' }}>GAS</option>
                                <option value="TVL-ICT" {{ old('strand')=='TVL-ICT' ? 'selected':'' }}>TVL-ICT</option>
                                <option value="TVL-HE" {{ old('strand')=='TVL-HE' ? 'selected':'' }}>TVL-HE</option>
                            </select>
                            @error('strand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Parent/Guardian Information -->
                    <h5 class="mb-3" style="color: var(--primary-color);">
                        <i class="ri-parent-line me-2"></i>Parent/Guardian Information
                    </h5>

                    <!-- Father Info -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="father_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Father's Name
                            </label>
                            <input
                                type="text"
                                id="father_name"
                                name="father_name"
                                class="form-control form-control-lg text-uppercase @error('father_name') is-invalid @enderror"
                                value="{{ old('father_name') }}"
                                placeholder="JUAN DELA CRUZ"
                                oninput="this.value = this.value.toUpperCase()"
                            />
                            @error('father_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="father_occupation" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Father's Occupation
                            </label>
                            <input
                                type="text"
                                id="father_occupation"
                                name="father_occupation"
                                class="form-control form-control-lg text-uppercase @error('father_occupation') is-invalid @enderror"
                                value="{{ old('father_occupation') }}"
                                placeholder="ENGINEER"
                                oninput="this.value = this.value.toUpperCase()"
                            />
                            @error('father_occupation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="father_contact" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Father's Contact
                            </label>
                            <input
                                type="tel"
                                id="father_contact"
                                name="father_contact"
                                class="form-control form-control-lg @error('father_contact') is-invalid @enderror"
                                value="{{ old('father_contact') }}"
                                placeholder="09171234567"
                            />
                            @error('father_contact') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Mother Info -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="mother_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Mother's Name
                            </label>
                            <input
                                type="text"
                                id="mother_name"
                                name="mother_name"
                                class="form-control form-control-lg text-uppercase @error('mother_name') is-invalid @enderror"
                                value="{{ old('mother_name') }}"
                                placeholder="MARIA DELA CRUZ"
                                oninput="this.value = this.value.toUpperCase()"
                            />
                            @error('mother_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="mother_occupation" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Mother's Occupation
                            </label>
                            <input
                                type="text"
                                id="mother_occupation"
                                name="mother_occupation"
                                class="form-control form-control-lg text-uppercase @error('mother_occupation') is-invalid @enderror"
                                value="{{ old('mother_occupation') }}"
                                placeholder="TEACHER"
                                oninput="this.value = this.value.toUpperCase()"
                            />
                            @error('mother_occupation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="mother_contact" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Mother's Contact
                            </label>
                            <input
                                type="tel"
                                id="mother_contact"
                                name="mother_contact"
                                class="form-control form-control-lg @error('mother_contact') is-invalid @enderror"
                                value="{{ old('mother_contact') }}"
                                placeholder="09171234567"
                            />
                            @error('mother_contact') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Primary Guardian -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="guardian_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Primary Guardian Name
                            </label>
                            <input
                                type="text"
                                id="guardian_name"
                                name="guardian_name"
                                class="form-control form-control-lg text-uppercase @error('guardian_name') is-invalid @enderror"
                                value="{{ old('guardian_name') }}"
                                required
                                placeholder="MARIA DELA CRUZ"
                                oninput="this.value = this.value.toUpperCase()"
                            />
                            @error('guardian_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guardian_contact" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Guardian Contact No.
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
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="last_school_type" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Last School Type
                            </label>
                            <select
                                id="last_school_type"
                                name="last_school_type"
                                class="form-select form-select-lg @error('last_school_type') is-invalid @enderror"
                            >
                                <option value="">-- Select Type --</option>
                                <option value="public" {{ old('last_school_type')=='public' ? 'selected':'' }}>Public</option>
                                <option value="private" {{ old('last_school_type')=='private' ? 'selected':'' }}>Private</option>
                            </select>
                            @error('last_school_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="last_school_name" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Last School Name
                            </label>
                            <input
                                type="text"
                                id="last_school_name"
                                name="last_school_name"
                                class="form-control form-control-lg text-uppercase @error('last_school_name') is-invalid @enderror"
                                value="{{ old('last_school_name') }}"
                                placeholder="NAME OF LAST SCHOOL"
                                oninput="this.value = this.value.toUpperCase()"
                            >
                            @error('last_school_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Medical History -->
                    <div class="mb-3">
                        <label for="medical_history" class="form-label fw-semibold" style="color: var(--primary-color);">
                            Medical History (Allergies, Conditions)
                        </label>
                        <textarea
                            id="medical_history"
                            name="medical_history"
                            rows="2"
                            class="form-control form-control-lg text-uppercase @error('medical_history') is-invalid @enderror"
                            placeholder="List any allergies or medical conditions"
                            oninput="this.value = this.value.toUpperCase()"
                        >{{ old('medical_history') }}</textarea>
                        @error('medical_history') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Payment & Schedule -->
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
                                <option value="cash" {{ old('payment_mode')=='cash'?'selected':'' }}>Cash</option>
                                <option value="online payment" {{ old('payment_mode')=='Online Payment'?'selected':'' }}>Online Payment</option>
                                {{-- <option value="installment" {{ old('payment_mode')=='installment'?'selected':'' }}>Installment</option>
                                <option value="scholarship" {{ old('payment_mode')=='scholarship'?'selected':'' }}>Scholarship</option> --}}
                            </select>
                            @error('payment_mode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="preferred_schedule" class="form-label fw-semibold" style="color: var(--primary-color);">
                                Preferred Schedule
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
                </form>
            </div>
        </div>
    </div> 
</x-layout>
