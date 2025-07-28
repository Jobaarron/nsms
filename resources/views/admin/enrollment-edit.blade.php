<x-admin-layout>
    <x-slot name="title">Edit Student Enrollment</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Edit Student: {{ $student->first_name }} {{ $student->last_name }}</h1>
            <a href="{{ route('admin.enrollments') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i>Back to Enrollments
            </a>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Student Information</h5>
                    </div>
                    <div class="card-body">
                        <!-- Change this line - remove 'admin.' prefix -->
                        <form action="{{ route('enrollments.update', $student->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Rest of your form remains the same -->
                            <!-- Personal Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Personal Information</h6>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="first_name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name', $student->first_name) }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="middle_name" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control @error('middle_name') is-invalid @enderror" 
                                           id="middle_name" name="middle_name" value="{{ old('middle_name', $student->middle_name) }}">
                                    @error('middle_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="last_name" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" value="{{ old('last_name', $student->last_name) }}" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="suffix" class="form-label">Suffix</label>
                                    <input type="text" class="form-control @error('suffix') is-invalid @enderror" 
                                           id="suffix" name="suffix" value="{{ old('suffix', $student->suffix) }}">
                                    @error('suffix')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $student->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="contact_number" class="form-label">Contact Number *</label>
                                    <input type="text" class="form-control @error('contact_number') is-invalid @enderror" 
                                           id="contact_number" name="contact_number" value="{{ old('contact_number', $student->contact_number) }}" required>
                                    @error('contact_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="lrn" class="form-label">LRN</label>
                                    <input type="text" class="form-control @error('lrn') is-invalid @enderror" 
                                           id="lrn" name="lrn" value="{{ old('lrn', $student->lrn) }}">
                                    @error('lrn')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="date_of_birth" class="form-label">Date of Birth *</label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" required>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="gender" class="form-label">Gender *</label>
                                    <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="religion" class="form-label">Religion</label>
                                    <input type="text" class="form-control @error('religion') is-invalid @enderror" 
                                           id="religion" name="religion" value="{{ old('religion', $student->religion) }}">
                                    @error('religion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Address Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Address Information</h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="address" class="form-label">Address *</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3" required>{{ old('address', $student->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-2">
                                    <label for="city" class="form-label">City *</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city', $student->city) }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-2">
                                    <label for="province" class="form-label">Province *</label>
                                    <input type="text" class="form-control @error('province') is-invalid @enderror" 
                                           id="province" name="province" value="{{ old('province', $student->province) }}" required>
                                    @error('province')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-2">
                                    <label for="zip_code" class="form-label">Zip Code *</label>
                                    <input type="text" class="form-control @error('zip_code') is-invalid @enderror" 
                                           id="zip_code" name="zip_code" value="{{ old('zip_code', $student->zip_code) }}" required>
                                    @error('zip_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Academic Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Academic Information</h6>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="grade_level" class="form-label">Grade Level *</label>
                                    <select class="form-select @error('grade_level') is-invalid @enderror" id="grade_level" name="grade_level" required>
                                        <option value="">Select Grade Level</option>
                                        <option value="Nursery" {{ old('grade_level', $student->grade_level) == 'Nursery' ? 'selected' : '' }}>Nursery</option>
                                        <option value="Kinder 1" {{ old('grade_level', $student->grade_level) == 'Kinder 1' ? 'selected' : '' }}>Kinder 1</option>
                                        <option value="Kinder 2" {{ old('grade_level', $student->grade_level) == 'Kinder 2' ? 'selected' : '' }}>Kinder 2</option>
                                        <option value="Grade 1" {{ old('grade_level', $student->grade_level) == 'Grade 1' ? 'selected' : '' }}>Grade 1</option>
                                        <option value="Grade 2" {{ old('grade_level', $student->grade_level) == 'Grade 2' ? 'selected' : '' }}>Grade 2</option>
                                        <option value="Grade 3" {{ old('grade_level', $student->grade_level) == 'Grade 3' ? 'selected' : '' }}>Grade 3</option>
                                        <option value="Grade 4" {{ old('grade_level', $student->grade_level) == 'Grade 4' ? 'selected' : '' }}>Grade 4</option>
                                        <option value="Grade 5" {{ old('grade_level', $student->grade_level) == 'Grade 5' ? 'selected' : '' }}>Grade 5</option>
                                        <option value="Grade 6" {{ old('grade_level', $student->grade_level) == 'Grade 6' ? 'selected' : '' }}>Grade 6</option>
                                        <option value="Grade 7" {{ old('grade_level', $student->grade_level) == 'Grade 7' ? 'selected' : '' }}>Grade 7</option>
                                        <option value="Grade 8" {{ old('grade_level', $student->grade_level) == 'Grade 8' ? 'selected' : '' }}>Grade 8</option>
                                        <option value="Grade 9" {{ old('grade_level', $student->grade_level) == 'Grade 9' ? 'selected' : '' }}>Grade 9</option>
                                        <option value="Grade 10" {{ old('grade_level', $student->grade_level) == 'Grade 10' ? 'selected' : '' }}>Grade 10</option>
                                        <option value="Grade 11" {{ old('grade_level', $student->grade_level) == 'Grade 11' ? 'selected' : '' }}>Grade 11</option>
                                        <option value="Grade 12" {{ old('grade_level', $student->grade_level) == 'Grade 12' ? 'selected' : '' }}>Grade 12</option>
                                    </select>
                                    @error('grade_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="strand" class="form-label">Strand (for SHS)</label>
                                    <input type="text" class="form-control @error('strand') is-invalid @enderror" 
                                           id="strand" name="strand" value="{{ old('strand', $student->strand) }}">
                                    @error('strand')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="enrollment_status" class="form-label">Enrollment Status *</label>
                                    <select class="form-select @error('enrollment_status') is-invalid @enderror" id="enrollment_status" name="enrollment_status" required>
                                        <option value="pending" {{ old('enrollment_status', $student->enrollment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="enrolled" {{ old('enrollment_status', $student->enrollment_status) == 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                                        <option value="rejected" {{ old('enrollment_status', $student->enrollment_status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                    @error('enrollment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Guardian Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Guardian Information</h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="guardian_name" class="form-label">Guardian Name *</label>
                                    <input type="text" class="form-control @error('guardian_name') is-invalid @enderror" 
                                           id="guardian_name" name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}" required>
                                    @error('guardian_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="guardian_contact" class="form-label">Guardian Contact *</label>
                                    <input type="text" class="form-control @error('guardian_contact') is-invalid @enderror" 
                                           id="guardian_contact" name="guardian_contact" value="{{ old('guardian_contact', $student->guardian_contact) }}" required>
                                    @error('guardian_contact')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Parent Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Parent Information</h6>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="father_name" class="form-label">Father's Name</label>
                                    <input type="text" class="form-control @error('father_name') is-invalid @enderror" 
                                           id="father_name" name="father_name" value="{{ old('father_name', $student->father_name) }}">
                                    @error('father_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="father_occupation" class="form-label">Father's Occupation</label>
                                    <input type="text" class="form-control @error('father_occupation') is-invalid @enderror" 
                                           id="father_occupation" name="father_occupation" value="{{ old('father_occupation', $student->father_occupation) }}">
                                    @error('father_occupation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="father_contact" class="form-label">Father's Contact</label>
                                    <input type="text" class="form-control @error('father_contact') is-invalid @enderror" 
                                           id="father_contact" name="father_contact" value="{{ old('father_contact', $student->father_contact) }}">
                                    @error('father_contact')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="mother_name" class="form-label">Mother's Name</label>
                                    <input type="text" class="form-control @error('mother_name') is-invalid @enderror" 
                                           id="mother_name" name="mother_name" value="{{ old('mother_name', $student->mother_name) }}">
                                    @error('mother_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="mother_occupation" class="form-label">Mother's Occupation</label>
                                    <input type="text" class="form-control @error('mother_occupation') is-invalid @enderror" 
                                           id="mother_occupation" name="mother_occupation" value="{{ old('mother_occupation', $student->mother_occupation) }}">
                                    @error('mother_occupation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="mother_contact" class="form-label">Mother's Contact</label>
                                    <input type="text" class="form-control @error('mother_contact') is-invalid @enderror" 
                                           id="mother_contact" name="mother_contact" value="{{ old('mother_contact', $student->mother_contact) }}">
                                    @error('mother_contact')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Previous School Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Previous School Information</h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="last_school_type" class="form-label">Last School Type</label>
                                    <select class="form-select @error('last_school_type') is-invalid @enderror" id="last_school_type" name="last_school_type">
                                        <option value="">Select School Type</option>
                                        <option value="public" {{ old('last_school_type', $student->last_school_type) == 'public' ? 'selected' : '' }}>Public</option>
                                        <option value="private" {{ old('last_school_type', $student->last_school_type) == 'private' ? 'selected' : '' }}>Private</option>
                                    </select>
                                    @error('last_school_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="last_school_name" class="form-label">Last School Name</label>
                                    <input type="text" class="form-control @error('last_school_name') is-invalid @enderror" 
                                           id="last_school_name" name="last_school_name" value="{{ old('last_school_name', $student->last_school_name) }}">
                                    @error('last_school_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Additional Information</h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="medical_history" class="form-label">Medical History</label>
                                    <textarea class="form-control @error('medical_history') is-invalid @enderror" 
                                              id="medical_history" name="medical_history" rows="3">{{ old('medical_history', $student->medical_history) }}</textarea>
                                    @error('medical_history')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="payment_mode" class="form-label">Payment Mode *</label>
                                    <select class="form-select @error('payment_mode') is-invalid @enderror" id="payment_mode" name="payment_mode" required>
                                        <option value="">Select Payment Mode</option>
                                        <option value="cash" {{ old('payment_mode', $student->payment_mode) == 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="installment" {{ old('payment_mode', $student->payment_mode) == 'installment' ? 'selected' : '' }}>Installment</option>
                                        <option value="scholarship" {{ old('payment_mode', $student->payment_mode) == 'scholarship' ? 'selected' : '' }}>Scholarship</option>
                                    </select>
                                    @error('payment_mode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Password Management -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">Password Management</h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="new_password" class="form-label">New Password (Optional)</label>
                                    <input type="text" class="form-control @error('new_password') is-invalid @enderror" 
                                           id="new_password" name="new_password" placeholder="Leave blank to keep current password">
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">If you want to set a new password for this student, enter it here.</small>
                                </div>

                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-primary" id="generatePassword">
                                        <i class="ri-refresh-line me-1"></i>Generate Random Password
                                    </button>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.enrollments') }}" class="btn btn-outline-secondary">
                                            <i class="ri-close-line me-1"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line me-1"></i>Update Student
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        
        .text-primary {
            color: #667eea !important;
        }
        
        .border-bottom {
            border-bottom: 2px solid #e9ecef !important;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px 15px 0 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        
        .invalid-feedback {
            display: block;
        }
        
        .is-invalid {
            border-color: #dc3545;
        }
        
        .is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
    </style>

    <script>
        document.getElementById('generatePassword').addEventListener('click', function() {
            // Generate a random password
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            
            // Set the generated password to the input field
            document.getElementById('new_password').value = password;
            
            // Show a success message
            const button = this;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="ri-check-line me-1"></i>Password Generated!';
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-success');
            
            // Reset button after 2 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-primary');
            }, 2000);
        });
    </script>
</x-admin-layout>