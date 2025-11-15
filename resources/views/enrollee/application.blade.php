<x-enrollee-layout>
    @vite(['resources/js/enrollee-application.js'])
    
    <script>
        // Make enrollee data available to JavaScript
        window.enrolleeData = {
            first_name: @json($enrollee->first_name),
            middle_name: @json($enrollee->middle_name),
            last_name: @json($enrollee->last_name),
            suffix: @json($enrollee->suffix),
            date_of_birth: @json($enrollee->date_of_birth),
            gender: @json($enrollee->gender),
            nationality: @json($enrollee->nationality),
            religion: @json($enrollee->religion),
            email: @json($enrollee->email),
            contact_number: @json($enrollee->contact_number),
            address: @json($enrollee->address),
            city: @json($enrollee->city),
            province: @json($enrollee->province),
            zip_code: @json($enrollee->zip_code),
            grade_level_applied: @json($enrollee->grade_level_applied),
            strand_applied: @json($enrollee->strand_applied),
            track_applied: @json($enrollee->track_applied),
            student_type: @json($enrollee->student_type),
            last_school_name: @json($enrollee->last_school_name),
            last_school_type: @json($enrollee->last_school_type),
            father_name: @json($enrollee->father_name),
            father_occupation: @json($enrollee->father_occupation),
            father_contact: @json($enrollee->father_contact),
            mother_name: @json($enrollee->mother_name),
            mother_occupation: @json($enrollee->mother_occupation),
            mother_contact: @json($enrollee->mother_contact),
            guardian_name: @json($enrollee->guardian_name),
            guardian_contact: @json($enrollee->guardian_contact),
            medical_history: @json($enrollee->medical_history)
        };
        
        // Debug: Log the enrollee data to console
        console.log('Enrollee Data:', window.enrolleeData);
    </script>
    
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="section-title">My Application</h1>
            <div class="d-flex align-items-center gap-2">
                @if($enrollee->enrollment_status === 'pending')
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#dataChangeRequestModal">
                    <i class="ri-file-edit-line me-1"></i>
                    Data Change Request
                </button>
                @endif
                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="ri-lock-password-line me-1"></i>
                    Change Password
                </button>
                {{-- <span class="badge badge-status status-{{ strtolower($enrollee->enrollment_status) }}">
                    {{ ucfirst($enrollee->enrollment_status) }}
                </span> --}}
            </div>
        </div>

        <!-- APPLICATION DETAILS -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-file-text-line me-2"></i>
                            Application Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Application ID</dt>
                            <dd class="col-sm-8">{{ $enrollee->application_id }}</dd>
                            <dt class="col-sm-4">Application Date</dt>
                            <dd class="col-sm-8">{{ $enrollee->application_date->format('F d, Y g:i A') }}</dd>
                            <dt class="col-sm-4">Academic Year</dt>
                            <dd class="col-sm-8">{{ $enrollee->academic_year }}</dd>
                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-status status-{{ strtolower($enrollee->enrollment_status) }}">
                                    {{ ucfirst($enrollee->enrollment_status) }}
                                </span>
                            </dd>
                            @if($enrollee->lrn)
                            <dt class="col-sm-4">LRN</dt>
                            <dd class="col-sm-8">{{ $enrollee->lrn }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>

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
                            {{-- <dt class="col-sm-4">Place of Birth</dt>
                            <dd class="col-sm-8">{{ $enrollee->place_of_birth ?? 'Not specified' }}</dd> --}}
                            <dt class="col-sm-4">Gender</dt>
                            <dd class="col-sm-8">{{ ucfirst($enrollee->gender) }}</dd>
                            <dt class="col-sm-4">Nationality</dt>
                            <dd class="col-sm-8">{{ $enrollee->nationality ?? 'Not specified' }}</dd>
                            <dt class="col-sm-4">Religion</dt>
                            <dd class="col-sm-8">{{ $enrollee->religion ?? 'Not specified' }}</dd>
                            <dt class="col-sm-4">Student Type</dt>
                            <dd class="col-sm-8">
                                <span class="badge 
                                    @if($enrollee->student_type === 'new') bg-success
                                    @elseif($enrollee->student_type === 'transferee') bg-info
                                    @elseif($enrollee->student_type === 'old') bg-primary
                                    @else bg-secondary
                                    @endif me-2">
                                    {{ ucfirst($enrollee->student_type) }}
                                </span>
                                {{-- <small class="text-muted">
                                    @if($enrollee->student_type === 'new')
                                        First time enrolling in any school
                                    @elseif($enrollee->student_type === 'transferee')
                                        Coming from another school
                                    @elseif($enrollee->student_type === 'old')
                                        Previously enrolled in this school or returning student
                                    @endif
                                </small> --}}
                            </dd>
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
                                <i class="ri-phone-line me-2"></i>
                                {{ $enrollee->contact_number ?? 'Not provided' }}
                            </dd>
                            <dt class="col-sm-4">Address</dt>
                            <dd class="col-sm-8">
                                <i class="ri-map-pin-line me-2"></i>
                                {{ $enrollee->address }}
                                @if($enrollee->city || $enrollee->province)
                                    <br>
                                    <small class="text-muted">
                                        {{ $enrollee->city }}{{ $enrollee->city && $enrollee->province ? ', ' : '' }}{{ $enrollee->province }}
                                        {{ $enrollee->zip_code ? ' ' . $enrollee->zip_code : '' }}
                                    </small>
                                @endif
                            </dd>
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
                                <span class="badge 
                                    @if($enrollee->student_type === 'new') bg-success
                                    @elseif($enrollee->student_type === 'transferee') bg-info
                                    @elseif($enrollee->student_type === 'old') bg-primary
                                    @else bg-secondary
                                    @endif me-2">
                                    {{ ucfirst($enrollee->student_type) }}
                                </span>
                                {{-- <small class="text-muted">
                                    @if($enrollee->student_type === 'new')
                                        First time enrolling in any school
                                    @elseif($enrollee->student_type === 'transferee')
                                        Coming from another school
                                    @elseif($enrollee->student_type === 'old')
                                        Previously enrolled in this school or returning student
                                    @endif
                                </small> --}}
                            </dd>
                            @if($enrollee->last_school_name)
                            <dt class="col-sm-4">Last School</dt>
                            <dd class="col-sm-8">{{ $enrollee->last_school_name }} ({{ ucfirst($enrollee->last_school_type) }})</dd>
                            @endif
                        </dl>
                    </div>
                </div>

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
                                <h6 class="text-muted">Father's Information</h6>
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
                                <h6 class="text-muted">Mother's Information</h6>
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
                        <h6 class="text-muted">Primary Guardian</h6>
                        <dl class="row">
                            <dt class="col-sm-3">Name</dt>
                            <dd class="col-sm-9">{{ $enrollee->guardian_name }}</dd>
                            <dt class="col-sm-3">Contact</dt>
                            <dd class="col-sm-9">{{ $enrollee->guardian_contact }}</dd>
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
                @if($enrollee->hasIdPhoto())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-image-line me-2"></i>
                            ID Photo
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ $enrollee->id_photo_data_url }}" alt="ID Photo" class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                </div>
                @endif

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
                                    <span class="badge bg-{{ ($documentData['status'] ?? 'pending') === 'approved' ? 'success' : (($documentData['status'] ?? 'pending') === 'rejected' ? 'warning' : 'warning') }} badge-sm">
                                        {{ ($documentData['status'] ?? 'pending') === 'rejected' ? 'Revised' : ucfirst($documentData['status'] ?? 'pending') }}
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
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <strong>Current Status:</strong>
                                <br>
                                <span class="badge badge-status status-{{ strtolower($enrollee->enrollment_status) }}">
                                    {{ ucfirst($enrollee->enrollment_status) }}
                                </span>
                            </div>
                        </div>
                        
                        @if($enrollee->status_reason)
                        <div class="alert alert-info">
                            <strong>Note:</strong> {{ $enrollee->status_reason }}
                        </div>
                        @endif

                        @if($enrollee->approved_at)
                        <p class="text-muted mb-2">
                            <i class="ri-check-line text-success me-1"></i>
                            Approved on {{ $enrollee->approved_at->format('M d, Y') }}
                        </p>
                        @endif

                        @if($enrollee->rejected_at)
                        <p class="text-muted mb-2">
                            <i class="ri-edit-line text-warning me-1"></i>
                            Revised on {{ $enrollee->rejected_at->format('M d, Y') }}
                        </p>
                        @endif

                        @if($enrollee->enrolled_at)
                        <p class="text-muted mb-2">
                            <i class="ri-graduation-cap-line text-primary me-1"></i>
                            Enrolled on {{ $enrollee->enrolled_at->format('M d, Y') }}
                        </p>
                        @endif
                    </div>
                </div>

                <!-- PAYMENT INFORMATION -->
                {{-- <div class="card mb-4"> --}}
                    {{-- <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-money-dollar-circle-line me-2"></i>
                            Payment Information
                        </h5>
                    </div> --}}
                    {{-- <div class="card-body">
                        <dl class="row"> --}}
                            {{-- PAYMENT MODE DISPLAY - COMMENTED OUT FOR FUTURE STUDENT PORTAL IMPLEMENTATION
                            <dt class="col-6">Payment Mode</dt>
                            <dd class="col-6">{{ ucfirst($enrollee->payment_mode) }}</dd>
                            --}}
                            {{-- <dt class="col-6">Enrollment Fee</dt>
                            <dd class="col-6">
                                @if($enrollee->enrollment_fee)
                                    ₱{{ number_format($enrollee->enrollment_fee, 2) }}
                                @else
                                    TBD
                                @endif
                            </dd> --}}
                            {{-- <dt class="col-6">Payment Status</dt>
                            <dd class="col-6">
                                <span class="badge {{ $enrollee->is_paid ? 'bg-success' : 'bg-warning' }}">
                                    {{ $enrollee->is_paid ? 'Paid' : 'Pending' }}
                                </span>
                            </dd>
                            @if($enrollee->payment_date)
                            <dt class="col-6">Payment Date</dt>
                            <dd class="col-6">{{ $enrollee->payment_date->format('M d, Y') }}</dd>
                            @endif
                            @if($enrollee->payment_reference)
                            <dt class="col-6">Reference</dt>
                            <dd class="col-6">{{ $enrollee->payment_reference }}</dd>
                            @endif
                        </dl> --}}
                        
                    {{-- </div> --}}
                </div>

                <!-- QUICK ACTIONS -->
                {{-- <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-settings-line me-2"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('enrollee.documents') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ri-folder-line me-1"></i>
                                My Documents
                            </a>
                            @if($enrollee->enrollment_status === 'pending')
                            <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#dataChangeRequestModal">
                                <i class="ri-file-edit-line me-1"></i>
                                Request Data Change
                            </button>
                            @endif
                        </div>
                    </div>
                </div> --}}

                @if($enrollee->admin_notes)
                <!-- ADMIN NOTES -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-sticky-note-line me-2"></i>
                            Admin Notes
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">{{ $enrollee->admin_notes }}</p>
                    </div>
                </div>
                @endif
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
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        <strong>Data Change Requests</strong><br>
                        View and manage your data change requests. You can only submit requests while your application is pending.
                    </div>

                    @if($enrollee->enrollment_status === 'pending')
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Change Requests</h6>
                        <button type="button" class="btn btn-primary btn-sm" id="addChangeRequestBtn" data-bs-toggle="modal" data-bs-target="#newChangeRequestModal">
                            <i class="ri-add-line me-1"></i>
                            New Request
                        </button>
                    </div>
                    @endif

                    <!-- Data Change Requests Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="changeRequestsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 20%">Field</th>
                                    <th style="width: 35%">Change Details</th>
                                    <th style="width: 15%">Status</th>
                                    <th style="width: 25%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($enrollee->dataChangeRequests as $index => $request)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $request->human_field_name }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">From:</small> {{ Str::limit($request->old_value, 30) }}<br>
                                        <small class="text-muted">To:</small> <strong>{{ Str::limit($request->new_value, 30) }}</strong>
                                        @if($request->reason)
                                        <br><small class="text-info"><i class="ri-information-line"></i> {{ Str::limit($request->reason, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $request->status_badge_class }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                        @if($request->processed_at)
                                        <br><small class="text-muted">{{ $request->processed_at->format('M d, Y') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="View Details" onclick="viewChangeRequest({{ $request->id }})">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            @if($enrollee->enrollment_status === 'pending' && $request->status === 'pending')
                                            <button class="btn btn-outline-warning" title="Edit Request" onclick="editChangeRequest({{ $request->id }})">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" title="Cancel Request" onclick="cancelChangeRequest({{ $request->id }})">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri-file-list-3-line fs-1 d-block mb-2"></i>
                                            <p class="mb-0">No data change requests submitted</p>
                                            @if($enrollee->enrollment_status === 'pending')
                                            <small>Click "New Request" to submit a change request</small>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    @if($enrollee->enrollment_status === 'pending')
                    <button type="button" class="btn btn-primary" id="newRequestBtn" data-bs-toggle="modal" data-bs-target="#newChangeRequestModal">
                        <i class="ri-add-line me-1"></i>
                        New Request
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-lock-password-line me-2"></i>
                        Change Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="changePasswordForm" method="POST" action="{{ route('enrollee.password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="ri-check-line me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="ri-error-warning-line me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Password Security</strong><br>
                            Choose a strong password to keep your account secure. Your password should be at least 8 characters long.
                        </div>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                    <i class="ri-eye-line" id="current_password_icon"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" name="new_password" required minlength="8">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                    <i class="ri-eye-line" id="new_password_icon"></i>
                                </button>
                            </div>
                            <div class="form-text">Password must be at least 8 characters long.</div>
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required minlength="8">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation')">
                                    <i class="ri-eye-line" id="new_password_confirmation_icon"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="ri-save-line me-1"></i>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- New Data Change Request Modal -->
    @if($enrollee->enrollment_status === 'pending')
    <div class="modal fade" id="newChangeRequestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-file-edit-line me-2"></i>
                        New Data Change Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="newChangeRequestForm" method="POST" action="{{ route('enrollee.data-change-requests.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Data Change Request</strong><br>
                            Select the field you want to change and provide the new value. All requests will be reviewed by the registrar.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="field_name" class="form-label">Field to Change <span class="text-danger">*</span></label>
                                    <select class="form-select" id="field_name" name="field_name" required>
                                        <option value="">Select field to change</option>
                                        <optgroup label="Personal Information">
                                            <option value="first_name">First Name</option>
                                            <option value="middle_name">Middle Name</option>
                                            <option value="last_name">Last Name</option>
                                            <option value="suffix">Suffix</option>
                                            <option value="date_of_birth">Date of Birth</option>
                                            <option value="gender">Gender</option>
                                            <option value="nationality">Nationality</option>
                                            <option value="religion">Religion</option>
                                        </optgroup>
                                        <optgroup label="Contact Information">
                                            <option value="email">Email Address</option>
                                            <option value="contact_number">Contact Number</option>
                                            <option value="address">Address</option>
                                            <option value="city">City</option>
                                            <option value="province">Province</option>
                                            <option value="zip_code">ZIP Code</option>
                                        </optgroup>
                                        <optgroup label="Academic Information">
                                            <option value="grade_level_applied">Grade Level Applied</option>
                                            <option value="strand_applied">Strand Applied</option>
                                            <option value="track_applied">Track Applied</option>
                                            <option value="student_type">Student Type</option>
                                            <option value="last_school_name">Last School Name</option>
                                            <option value="last_school_type">Last School Type</option>
                                        </optgroup>
                                        <optgroup label="Parent/Guardian Information">
                                            <option value="father_name">Father's Name</option>
                                            <option value="father_occupation">Father's Occupation</option>
                                            <option value="father_contact">Father's Contact</option>
                                            <option value="mother_name">Mother's Name</option>
                                            <option value="mother_occupation">Mother's Occupation</option>
                                            <option value="mother_contact">Mother's Contact</option>
                                            <option value="guardian_name">Guardian Name</option>
                                            <option value="guardian_contact">Guardian Contact</option>
                                        </optgroup>
                                        <optgroup label="Other Information">
                                            <option value="medical_history">Medical History</option>
                                        </optgroup>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="current_value" class="form-label">Current Value</label>
                                    <input type="text" class="form-control" id="current_value" readonly>
                                    <input type="hidden" id="old_value" name="old_value">
                                    <div class="form-text">This will be automatically filled when you select a field.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_value" class="form-label">New Value <span class="text-danger">*</span></label>
                            
                            <!-- Text Input (default) -->
                            <input type="text" class="form-control form-control-lg" id="new_value" name="new_value" required style="display: none;">
                            
                            <!-- Text Input with Uppercase (for names, addresses, etc.) -->
                            <input type="text" class="form-control form-control-lg text-uppercase" id="new_value_uppercase" name="new_value_uppercase" oninput="this.value = this.value.toUpperCase()" style="display: none;">
                            
                            <!-- Email Input -->
                            <input type="email" class="form-control form-control-lg" id="new_value_email" name="new_value_email" placeholder="example@email.com" style="display: none;">
                            
                            <!-- Tel Input -->
                            <input type="tel" class="form-control form-control-lg" id="new_value_tel" name="new_value_tel" placeholder="09171234567" style="display: none;">
                            
                            <!-- Date Input -->
                            <input type="date" class="form-control form-control-lg" id="new_value_date" name="new_value_date" style="display: none;">
                            
                            <!-- Textarea for longer text -->
                            <textarea class="form-control form-control-lg text-uppercase" id="new_value_textarea" name="new_value_textarea" rows="2" oninput="this.value = this.value.toUpperCase()" style="display: none;"></textarea>
                            
                            <!-- Gender Dropdown -->
                            <select class="form-select form-select-lg" id="new_value_gender" name="new_value_gender" style="display: none;">
                                <option value="">-- Select Gender --</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            
                            <!-- Student Type Dropdown -->
                            <select class="form-select form-select-lg" id="new_value_student_type" name="new_value_student_type" style="display: none;">
                                <option value="">-- Select Type --</option>
                                <option value="new">New</option>
                                <option value="transferee">Transferee</option>
                                <option value="old">Old</option>
                            </select>
                            
                            <!-- Grade Level Dropdown -->
                            <select class="form-select form-select-lg" id="new_value_grade_level" name="new_value_grade_level" style="display: none;">
                                <option value="">-- Select Grade --</option>
                                <option value="Nursery">Nursery</option>
                                <option value="Junior Casa">Junior Casa</option>
                                <option value="Senior Casa">Senior Casa</option>
                                <option value="Grade 1">Grade 1</option>
                                <option value="Grade 2">Grade 2</option>
                                <option value="Grade 3">Grade 3</option>
                                <option value="Grade 4">Grade 4</option>
                                <option value="Grade 5">Grade 5</option>
                                <option value="Grade 6">Grade 6</option>
                                <option value="Grade 7">Grade 7</option>
                                <option value="Grade 8">Grade 8</option>
                                <option value="Grade 9">Grade 9</option>
                                <option value="Grade 10">Grade 10</option>
                                <option value="Grade 11">Grade 11</option>
                                <option value="Grade 12">Grade 12</option>
                            </select>
                            
                            <!-- Strand Dropdown -->
                            <select class="form-select form-select-lg" id="new_value_strand" name="new_value_strand" style="display: none;">
                                <option value="">-- Select Strand --</option>
                                <option value="STEM">STEM</option>
                                <option value="ABM">ABM</option>
                                <option value="HUMSS">HUMSS</option>
                                <option value="TVL">TVL</option>
                            </select>
                            
                            <!-- Track Dropdown -->
                            <select class="form-select form-select-lg" id="new_value_track" name="new_value_track" style="display: none;">
                                <option value="">-- Select Track --</option>
                                <option value="ICT">ICT (Information and Communications Technology)</option>
                                <option value="HE">HE (Home Economics)</option>
                            </select>
                            
                            <!-- Last School Type Dropdown -->
                            <select class="form-select form-select-lg" id="new_value_last_school_type" name="new_value_last_school_type" style="display: none;">
                                <option value="">-- Select Type --</option>
                                <option value="public">Public</option>
                                <option value="private">Private</option>
                            </select>
                            
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Change</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Please explain why you need to change this information..."></textarea>
                            <div class="form-text">Optional: Provide a reason to help speed up the approval process.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-send-plane-line me-1"></i>
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- View Change Request Modal -->
    <div class="modal fade" id="viewChangeRequestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-eye-line me-2"></i>
                        Change Request Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Request Information</h6>
                            <dl class="row">
                                <dt class="col-sm-5">Field:</dt>
                                <dd class="col-sm-7" id="view_field_name">-</dd>
                                <dt class="col-sm-5">Status:</dt>
                                <dd class="col-sm-7" id="view_status">-</dd>
                                <dt class="col-sm-5">Submitted:</dt>
                                <dd class="col-sm-7" id="view_created_at">-</dd>
                                <dt class="col-sm-5">Processed:</dt>
                                <dd class="col-sm-7" id="view_processed_at">-</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Change Details</h6>
                            <dl class="row">
                                <dt class="col-sm-4">From:</dt>
                                <dd class="col-sm-8" id="view_old_value">-</dd>
                                <dt class="col-sm-4">To:</dt>
                                <dd class="col-sm-8" id="view_new_value">-</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="row mt-3" id="view_reason_section" style="display: none;">
                        <div class="col-12">
                            <h6 class="text-muted">Reason for Change</h6>
                            <p id="view_reason" class="text-muted">-</p>
                        </div>
                    </div>

                    <div class="row mt-3" id="view_admin_notes_section" style="display: none;">
                        <div class="col-12">
                            <h6 class="text-muted">Registrar Notes</h6>
                            <div class="alert alert-info">
                                <p id="view_admin_notes" class="mb-0">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/enrollee-data-change-requests.js'])
</x-enrollee-layout>
