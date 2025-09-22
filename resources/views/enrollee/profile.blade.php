<x-enrollee-layout>
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="section-title">My Profile</h1>
            <div>
                @if($enrollee->enrollment_status === 'pending')
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#dataChangeRequestModal">
                    <i class="ri-file-edit-line me-1"></i>
                    Data Change Request
                </button>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- PERSONAL INFORMATION -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-user-line me-2"></i>
                            Personal Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Full Name</dt>
                            <dd class="col-sm-8">{{ $enrollee->full_name }}</dd>
                            <dt class="col-sm-4">Date of Birth</dt>
                            <dd class="col-sm-8">{{ $enrollee->date_of_birth->format('F d, Y') }} ({{ $enrollee->date_of_birth->age }} years old)</dd>
                            <dt class="col-sm-4">Place of Birth</dt>
                            <dd class="col-sm-8">{{ $enrollee->place_of_birth ?? 'Not specified' }}</dd>
                            <dt class="col-sm-4">Gender</dt>
                            <dd class="col-sm-8">{{ ucfirst($enrollee->gender) }}</dd>
                            <dt class="col-sm-4">Nationality</dt>
                            <dd class="col-sm-8">{{ $enrollee->nationality }}</dd>
                            <dt class="col-sm-4">Religion</dt>
                            <dd class="col-sm-8">{{ $enrollee->religion ?? 'Not specified' }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- CONTACT INFORMATION -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-phone-line me-2"></i>
                            Contact Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Email Address</dt>
                            <dd class="col-sm-8">
                                <i class="ri-mail-line me-2"></i>
                                {{ $enrollee->email }}
                            </dd>
                            <dt class="col-sm-4">Contact Number</dt>
                            <dd class="col-sm-8">
                                @if($enrollee->contact_number)
                                    <i class="ri-phone-line me-2"></i>
                                    {{ $enrollee->contact_number }}
                                @else
                                    <span class="text-muted">Not provided</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- ADDRESS INFORMATION -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-map-pin-line me-2"></i>
                            Address Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Complete Address</dt>
                            <dd class="col-sm-8">{{ $enrollee->address }}</dd>
                            <dt class="col-sm-4">City</dt>
                            <dd class="col-sm-8">{{ $enrollee->city }}</dd>
                            <dt class="col-sm-4">Province</dt>
                            <dd class="col-sm-8">{{ $enrollee->province }}</dd>
                            <dt class="col-sm-4">ZIP Code</dt>
                            <dd class="col-sm-8">{{ $enrollee->zip_code }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- ACADEMIC INFORMATION -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-book-line me-2"></i>
                            Academic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Grade Level Applied</dt>
                            <dd class="col-sm-8">{{ $enrollee->grade_level_applied }}</dd>
                            @if($enrollee->strand_applied)
                            <dt class="col-sm-4">Strand Applied</dt>
                            <dd class="col-sm-8">{{ $enrollee->strand_applied }}</dd>
                            @endif
                            @if($enrollee->track_applied)
                            <dt class="col-sm-4">Track Applied</dt>
                            <dd class="col-sm-8">{{ $enrollee->track_applied }}</dd>
                            @endif
                            <dt class="col-sm-4">Student Type</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-info">{{ ucfirst($enrollee->student_type) }}</span>
                            </dd>
                            <dt class="col-sm-4">Academic Year</dt>
                            <dd class="col-sm-8">{{ $enrollee->academic_year }}</dd>
                            @if($enrollee->lrn)
                            <dt class="col-sm-4">LRN</dt>
                            <dd class="col-sm-8">{{ $enrollee->lrn }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- PREVIOUS SCHOOL INFORMATION -->
                @if($enrollee->last_school_name)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-school-line me-2"></i>
                            Previous School Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">School Name</dt>
                            <dd class="col-sm-8">{{ $enrollee->last_school_name }}</dd>
                            <dt class="col-sm-4">School Type</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-{{ $enrollee->last_school_type === 'public' ? 'primary' : 'success' }}">
                                    {{ ucfirst($enrollee->last_school_type) }}
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
                @endif

                <!-- PARENT/GUARDIAN INFORMATION -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-parent-line me-2"></i>
                            Parent/Guardian Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Father's Information</h6>
                                <dl class="row">
                                    <dt class="col-sm-5">Name</dt>
                                    <dd class="col-sm-7">{{ $enrollee->father_name ?? 'Not provided' }}</dd>
                                    <dt class="col-sm-5">Occupation</dt>
                                    <dd class="col-sm-7">{{ $enrollee->father_occupation ?? 'Not provided' }}</dd>
                                    <dt class="col-sm-5">Contact</dt>
                                    <dd class="col-sm-7">{{ $enrollee->father_contact ?? 'Not provided' }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Mother's Information</h6>
                                <dl class="row">
                                    <dt class="col-sm-5">Name</dt>
                                    <dd class="col-sm-7">{{ $enrollee->mother_name ?? 'Not provided' }}</dd>
                                    <dt class="col-sm-5">Occupation</dt>
                                    <dd class="col-sm-7">{{ $enrollee->mother_occupation ?? 'Not provided' }}</dd>
                                    <dt class="col-sm-5">Contact</dt>
                                    <dd class="col-sm-7">{{ $enrollee->mother_contact ?? 'Not provided' }}</dd>
                                </dl>
                            </div>
                        </div>
                        <hr>
                        <h6 class="text-muted mb-3">Primary Guardian</h6>
                        <dl class="row">
                            <dt class="col-sm-3">Name</dt>
                            <dd class="col-sm-9">
                                <i class="ri-user-line me-2"></i>
                                {{ $enrollee->guardian_name }}
                            </dd>
                            <dt class="col-sm-3">Contact</dt>
                            <dd class="col-sm-9">
                                <i class="ri-phone-line me-2"></i>
                                {{ $enrollee->guardian_contact }}
                            </dd>
                        </dl>
                    </div>
                </div>

                @if($enrollee->medical_history)
                <!-- MEDICAL INFORMATION -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-heart-pulse-line me-2"></i>
                            Medical Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>{{ $enrollee->medical_history }}</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-4">
                <!-- ID PHOTO -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-image-line me-2"></i>
                            ID Photo
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        @if($enrollee->hasIdPhoto())
                            <img src="{{ $enrollee->id_photo_data_url }}" alt="ID Photo" class="img-fluid rounded mb-3" style="max-height: 250px;">
                            <div>
                                <span class="badge bg-success">
                                    <i class="ri-check-line me-1"></i>
                                    Photo Uploaded
                                </span>
                            </div>
                        @else
                            <div class="document-upload">
                                <i class="ri-user-add-line display-4 text-muted"></i>
                                <p class="text-muted mt-2">No photo uploaded</p>
                                @if($enrollee->enrollment_status === 'pending')
                                <button class="btn btn-outline-primary btn-sm">
                                    <i class="ri-upload-line me-1"></i>
                                    Upload Photo
                                </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- DOCUMENTS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-folder-line me-2"></i>
                            Documents
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(is_array($enrollee->documents) && count($enrollee->documents) > 0)
                            <div class="text-center mb-3">
                                <i class="ri-file-check-line display-4 text-success"></i>
                                <p class="text-success mt-2 mb-1">
                                    <strong>{{ count($enrollee->documents) }} Document(s) Uploaded</strong>
                                </p>
                                <small class="text-muted">Click to view all documents</small>
                            </div>
                            
                            <!-- Document List -->
                            <div class="list-group list-group-flush">
                                @foreach((is_array($enrollee->documents) ? $enrollee->documents : []) as $index => $document)
                                @php
                                    // Handle both old format (string paths) and new format (arrays)
                                    if (is_string($document)) {
                                        $documentPath = $document;
                                        $filename = basename($documentPath);
                                        $extension = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
                                        $documentData = [
                                            'type' => $extension ?: 'Unknown',
                                            'filename' => $filename,
                                            'path' => $documentPath,
                                            'status' => 'pending'
                                        ];
                                    } else {
                                        $documentData = $document;
                                    }
                                @endphp
                                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-0 border-0">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-file-text-line me-2 text-primary"></i>
                                        <div>
                                            <small class="fw-medium">{{ $documentData['type'] ?? 'Unknown' }}</small>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($documentData['filename'] ?? 'Document ' . ($index + 1), 20) }}</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-{{ ($documentData['status'] ?? 'pending') === 'verified' ? 'success' : (($documentData['status'] ?? 'pending') === 'rejected' ? 'danger' : 'warning') }} badge-sm">
                                        {{ ucfirst($documentData['status'] ?? 'pending') }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="{{ route('enrollee.documents') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="ri-eye-line me-1"></i>
                                    View All Documents
                                </a>
                            </div>
                        @else
                            <div class="text-center">
                                <i class="ri-folder-open-line display-4 text-muted"></i>
                                <p class="text-muted mt-2">No documents uploaded</p>
                                @if($enrollee->enrollment_status === 'pending')
                                <a href="{{ route('enrollee.documents') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="ri-upload-line me-1"></i>
                                    Upload Documents
                                </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- APPLICATION STATUS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-flag-line me-2"></i>
                            Application Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <span class="badge badge-status status-{{ strtolower($enrollee->enrollment_status) }} fs-6">
                                {{ ucfirst($enrollee->enrollment_status) }}
                            </span>
                        </div>
                        
                        <dl class="row mb-0">
                            <dt class="col-6">Application ID</dt>
                            <dd class="col-6">{{ $enrollee->application_id }}</dd>
                            <dt class="col-6">Applied Date</dt>
                            <dd class="col-6">{{ $enrollee->application_date->format('M d, Y') }}</dd>
                            @if($enrollee->approved_at)
                            <dt class="col-6">Approved Date</dt>
                            <dd class="col-6">{{ $enrollee->approved_at->format('M d, Y') }}</dd>
                            @endif
                            @if($enrollee->enrolled_at)
                            <dt class="col-6">Enrolled Date</dt>
                            <dd class="col-6">{{ $enrollee->enrolled_at->format('M d, Y') }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- PAYMENT STATUS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-money-dollar-circle-line me-2"></i>
                            Payment Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <span class="badge {{ $enrollee->is_paid ? 'bg-success' : 'bg-warning' }} fs-6">
                                {{ $enrollee->is_paid ? 'Paid' : 'Pending' }}
                            </span>
                        </div>
                        
                        <dl class="row mb-0">
                            <dt class="col-6">Payment Mode</dt>
                            <dd class="col-6">{{ ucfirst($enrollee->payment_mode) }}</dd>
                            <dt class="col-6">Enrollment Fee</dt>
                            <dd class="col-6">
                                @if($enrollee->enrollment_fee)
                                    ₱{{ number_format($enrollee->enrollment_fee, 2) }}
                                @else
                                    TBD
                                @endif
                            </dd>
                            @if($enrollee->payment_date)
                            <dt class="col-6">Payment Date</dt>
                            <dd class="col-6">{{ $enrollee->payment_date->format('M d, Y') }}</dd>
                            @endif
                        </dl>

                        @if($enrollee->enrollment_status === 'approved' && !$enrollee->is_paid)
                        <div class="d-grid mt-3">
                            <a href="{{ route('enrollee.payment') }}" class="btn btn-primary">
                                <i class="ri-money-dollar-circle-line me-1"></i>
                                Make Payment
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- QUICK ACTIONS -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-settings-line me-2"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('enrollee.application') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ri-file-text-line me-1"></i>
                                View Application
                            </a>
                            <a href="{{ route('enrollee.documents') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ri-folder-line me-1"></i>
                                My Documents
                            </a>
                            @if($enrollee->enrollment_status === 'pending')
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#dataChangeRequestModal">
                                <i class="ri-file-edit-line me-1"></i>
                                Data Change Request
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Change Request Modal -->
    @if($enrollee->enrollment_status === 'pending')
    <div class="modal fade" id="dataChangeRequestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-file-edit-line me-2"></i>
                        Data Change Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="dataChangeRequestForm" method="POST" action="{{ route('enrollee.profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="ri-alert-line me-2"></i>
                            <strong>Data Change Request</strong><br>
                            Any changes you submit will be reviewed by school staff before being approved. You can only request changes while your application is pending.
                        </div>

                        <!-- Personal Information -->
                        <h6 class="text-muted mb-3">Personal Information</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="{{ $enrollee->first_name }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name" value="{{ $enrollee->middle_name }}">
                            </div>
                            <div class="col-md-4">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ $enrollee->last_name }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number" value="{{ $enrollee->contact_number }}">
                            </div>
                            <div class="col-md-6">
                                <label for="religion" class="form-label">Religion</label>
                                <input type="text" class="form-control" id="religion" name="religion" value="{{ $enrollee->religion }}">
                            </div>
                        </div>

                        <!-- Address Information -->
                        <h6 class="text-muted mb-3 mt-4">Address Information</h6>
                        <div class="mb-3">
                            <label for="address" class="form-label">Complete Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" required>{{ $enrollee->address }}</textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ $enrollee->city }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="province" class="form-label">Province</label>
                                <input type="text" class="form-control" id="province" name="province" value="{{ $enrollee->province }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="zip_code" class="form-label">ZIP Code</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" value="{{ $enrollee->zip_code }}" required>
                            </div>
                        </div>

                        <!-- Guardian Information -->
                        <h6 class="text-muted mb-3 mt-4">Guardian Information</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="guardian_name" class="form-label">Guardian Name</label>
                                <input type="text" class="form-control" id="guardian_name" name="guardian_name" value="{{ $enrollee->guardian_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="guardian_contact" class="form-label">Guardian Contact</label>
                                <input type="text" class="form-control" id="guardian_contact" name="guardian_contact" value="{{ $enrollee->guardian_contact }}" required>
                            </div>
                        </div>

                        <!-- Medical History -->
                        <h6 class="text-muted mb-3 mt-4">Medical Information</h6>
                        <div class="mb-3">
                            <label for="medical_history" class="form-label">Medical History</label>
                            <textarea class="form-control" id="medical_history" name="medical_history" rows="3" placeholder="Any medical conditions, allergies, or health concerns">{{ $enrollee->medical_history }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="ri-send-plane-line me-1"></i>
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</x-enrollee-layout>
