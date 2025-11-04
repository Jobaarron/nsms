<x-registrar-layout>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta charset="utf-8">`
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @vite(['resources/js/registrar-applications.js'])
    @vite(['resources/js/registrar-data-change-requests.js'])
    @vite(['resources/js/registrar-document-management.js'])
    @vite(['resources/css/index_registrar.css'])
    
    @include('registrar.enrollment-modals')
    
    <div class="py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="section-title">
                <i class="ri-graduation-cap-line me-2"></i>
                Application Management
            </h1>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" onclick="refreshData()">
                    <i class="ri-refresh-line me-1"></i>Refresh
                </button>
                {{-- <button class="btn btn-registrar" onclick="exportData()">
                    <i class="ri-download-line me-1"></i>Export
                </button> --}}
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ri-file-list-line fs-2 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4">{{ $totalApplications ?? 0 }}</div>
                                <div class="text-muted small">Total Applications</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ri-time-line fs-2 text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4">{{ $pendingApplications ?? 0 }}</div>
                                <div class="text-muted small">Pending Review</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ri-check-line fs-2 text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4">{{ $approvedApplications ?? 0 }}</div>
                                <div class="text-muted small">Approved</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ri-calendar-check-line fs-2 text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4">{{ $scheduledAppointments ?? 0 }}</div>
                                <div class="text-muted small">Scheduled</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="enrollmentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="applications-tab" data-bs-toggle="tab" data-bs-target="#applications" type="button" role="tab">
                    <i class="ri-file-list-line me-2"></i>Applications
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                    <i class="ri-folder-line me-2"></i>Documents
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab">
                    <i class="ri-calendar-line me-2"></i>Appointments
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="notices-tab" data-bs-toggle="tab" data-bs-target="#notices" type="button" role="tab">
                    <i class="ri-notification-line me-2"></i>Notices
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="data-change-requests-tab" data-bs-toggle="tab" data-bs-target="#data-change-requests" type="button" role="tab">
                    <i class="ri-file-edit-line me-2"></i>Data Change Request
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="enrollmentTabContent">
            
            <!-- Applications Tab -->
            <div class="tab-pane fade show active" id="applications" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header" style="background-color: var(--primary-color); color: white;">
                        <h5 class="mb-0">
                            <i class="ri-file-list-line me-2"></i>
                            Enrollment Applications
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" action="{{ route('registrar.applications') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <select class="form-select" name="status" onchange="this.form.submit()">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="grade_level" onchange="this.form.submit()">
                                        <option value="">All Grade Level</option>
                                        <option value="Nursery" {{ request('grade_level') === 'Nursery' ? 'selected' : '' }}>Nursery</option>
                                        <option value="Junior Casa" {{ request('grade_level') === 'Junior Casa' ? 'selected' : '' }}>Junior Casa</option>
                                        <option value="Senior Casa" {{ request('grade_level') === 'Senior Casa' ? 'selected' : '' }}>Senior Casa</option>
                                        <option value="Grade 1" {{ request('grade_level') === 'Grade 1' ? 'selected' : '' }}>Grade 1</option>
                                        <option value="Grade 2" {{ request('grade_level') === 'Grade 2' ? 'selected' : '' }}>Grade 2</option>
                                        <option value="Grade 3" {{ request('grade_level') === 'Grade 3' ? 'selected' : '' }}>Grade 3</option>
                                        <option value="Grade 4" {{ request('grade_level') === 'Grade 4' ? 'selected' : '' }}>Grade 4</option>
                                        <option value="Grade 5" {{ request('grade_level') === 'Grade 5' ? 'selected' : '' }}>Grade 5</option>
                                        <option value="Grade 6" {{ request('grade_level') === 'Grade 6' ? 'selected' : '' }}>Grade 6</option>
                                        <option value="Grade 7" {{ request('grade_level') === 'Grade 7' ? 'selected' : '' }}>Grade 7</option>
                                        <option value="Grade 8" {{ request('grade_level') === 'Grade 8' ? 'selected' : '' }}>Grade 8</option>
                                        <option value="Grade 9" {{ request('grade_level') === 'Grade 9' ? 'selected' : '' }}>Grade 9</option>
                                        <option value="Grade 10" {{ request('grade_level') === 'Grade 10' ? 'selected' : '' }}>Grade 10</option>
                                        <option value="Grade 11" {{ request('grade_level') === 'Grade 11' ? 'selected' : '' }}>Grade 11</option>
                                        <option value="Grade 12" {{ request('grade_level') === 'Grade 12' ? 'selected' : '' }}>Grade 12</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or application ID...">
                                        <button class="btn btn-outline-primary" type="submit">
                                            <i class="ri-search-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('registrar.applications') }}" class="btn btn-outline-secondary w-100">
                                        <i class="ri-close-line me-1"></i>Clear
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Applications Table -->
                        <div class="table-responsive">
                            <table class="table table-hover" id="applications-table">
                                <thead style="background-color: var(--primary-color); color: white;">
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" id="select-all" class="form-check-input">
                                        </th>
                                        <th>Application ID</th>
                                        <th>Student Name</th>
                                        <th>Grade Level</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Applied Date</th>
                                        <th style="width: 280px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications ?? [] as $application)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input application-checkbox" value="{{ $application->id }}">
                                            </td>
                                            <td>{{ $application->application_id }}</td>
                                            <td>{{ $application->first_name }} {{ $application->last_name }}</td>
                                            <td>{{ $application->grade_level_applied }}</td>
                                            <td>{{ $application->email }}</td>
                                            <td>
                                                @php
                                                    $statusClasses = [
                                                        'pending' => 'bg-warning text-dark',
                                                        'approved' => 'bg-success',
                                                        'declined' => 'bg-danger'
                                                    ];
                                                    $statusClass = $statusClasses[$application->enrollment_status] ?? 'bg-secondary';
                                                @endphp
                                                <span class="badge {{ $statusClass }}">
                                                    {{ ucfirst($application->enrollment_status) }}
                                                </span>
                                            </td>
                                            <td>{{ $application->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <!-- Primary Actions -->
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewApplication({{ $application->id }})" title="View Application Details">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    
                                                    <!-- Status Actions (only for pending applications) -->
                                                    @if($application->enrollment_status === 'pending')
                                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="approveApplication({{ $application->id }})" title="Approve Application">
                                                            <i class="ri-check-line"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="declineApplication({{ $application->id }})" title="Decline Application">
                                                            <i class="ri-close-line"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    <!-- Send Notice (available for all applications) -->
                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="sendNoticeToApplicant('{{ $application->application_id }}')" title="Send Notice">
                                                        <i class="ri-mail-send-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="ri-inbox-line fs-1 d-block mb-2"></i>
                                                    No applications found
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Bulk Actions Panel -->
                        <div class="card mt-4 border-primary" id="bulk-actions-panel" style="display: none;">
                            <div class="card-body py-3">
                                <div class="row align-items-center g-3">
                                    <div class="col-lg-6 col-md-12">
                                        <h6 class="mb-0 text-primary">
                                            <i class="ri-checkbox-multiple-line me-2"></i>
                                            <span id="selectedCount">0</span> applications selected
                                        </h6>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end justify-content-start">
                                            <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()">
                                                <i class="ri-check-line me-1"></i>Approve
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm" onclick="bulkDecline()">
                                                <i class="ri-close-line me-1"></i>Decline
                                            </button>
                                            <button type="button" class="btn btn-info btn-sm" onclick="bulkSendNotice()">
                                                <i class="ri-mail-send-line me-1"></i>Send Notice
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllSelections()">
                                                <i class="ri-close-circle-line me-1"></i>Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        @if(isset($applications) && method_exists($applications, 'links'))
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing {{ $applications->firstItem() ?? 0 }} to {{ $applications->lastItem() ?? 0 }} of {{ $applications->total() }} applications
                            </div>
                            <div>
                                {{ $applications->appends(request()->query())->links() }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Documents Tab -->
            <div class="tab-pane fade" id="documents" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header" style="background-color: var(--secondary-color); color: white;">
                        <h5 class="mb-0">
                            <i class="ri-folder-line me-2"></i>
                            Document Review & Verification
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Document Filters -->
                        <form method="GET" action="{{ route('registrar.applications') }}" class="mb-3">
                            <input type="hidden" name="tab" value="documents">
                            <div class="row">
                                <div class="col-md-4">
                                    <select class="form-select" name="document_status" onchange="this.form.submit()">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ request('document_status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                                        <option value="approved" {{ request('document_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ request('document_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <a href="{{ route('registrar.applications') }}?tab=documents" class="btn btn-outline-secondary">
                                        <i class="ri-refresh-line me-1"></i>Clear Filters
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Documents Content -->
                        @php
                            // Get all documents from enrollees
                            $allDocuments = collect();
                            $enrolleesWithDocs = \App\Models\Enrollee::whereNotNull('documents')
                                ->select(['id', 'application_id', 'first_name', 'last_name', 'documents', 'created_at'])
                                ->orderBy('created_at', 'desc')
                                ->get();
                            
                            foreach ($enrolleesWithDocs as $enrollee) {
                                $documents = $enrollee->documents;
                                if (is_string($documents)) {
                                    $documents = json_decode($documents, true);
                                }
                                
                                if (is_array($documents)) {
                                    foreach ($documents as $index => $doc) {
                                        $document = [
                                            'application_id' => $enrollee->application_id,
                                            'applicant_name' => trim($enrollee->first_name . ' ' . $enrollee->last_name),
                                            'type' => $doc['type'] ?? 'Unknown',
                                            'filename' => $doc['filename'] ?? 'Unknown file',
                                            'path' => $doc['path'] ?? '',
                                            'status' => $doc['status'] ?? 'pending',
                                            'uploaded_at' => $doc['uploaded_at'] ?? $enrollee->created_at->toISOString(),
                                            'index' => $index,
                                            'enrollee_id' => $enrollee->id
                                        ];
                                        
                                        // Apply filters
                                        $statusFilter = request('document_status');
                                        
                                        if ($statusFilter && $document['status'] !== $statusFilter) {
                                            continue;
                                        }
                                        
                                        $allDocuments->push($document);
                                    }
                                }
                            }
                            
                            // Sort by upload date (newest first)
                            $allDocuments = $allDocuments->sortByDesc('uploaded_at');
                        @endphp

                        @if($allDocuments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Application ID</th>
                                            <th>Applicant Name</th>
                                            <th>Document Type</th>
                                            <th>File Name</th>
                                            <th>Upload Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allDocuments as $document)
                                            <tr>
                                                <td>{{ $document['application_id'] }}</td>
                                                <td>{{ $document['applicant_name'] }}</td>
                                                <td>
                                                    @php
                                                        $iconMap = [
                                                            'Birth Certificate' => 'ri-file-text-line',
                                                            'Report Card' => 'ri-file-chart-line',
                                                            'Good Moral' => 'ri-file-shield-line',
                                                            'ID Photo' => 'ri-image-line'
                                                        ];
                                                        $icon = $iconMap[$document['type']] ?? 'ri-file-line';
                                                    @endphp
                                                    <i class="{{ $icon }} me-1"></i>
                                                    {{ $document['type'] }}
                                                </td>
                                                <td>{{ $document['filename'] }}</td>
                                                <td>
                                                    @php
                                                        $uploadDate = \Carbon\Carbon::parse($document['uploaded_at']);
                                                    @endphp
                                                    {{ $uploadDate->format('M d, Y') }}
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClasses = [
                                                            'pending' => 'bg-warning text-dark',
                                                            'approved' => 'bg-success',
                                                            'rejected' => 'bg-danger'
                                                        ];
                                                        $statusClass = $statusClasses[$document['status']] ?? 'bg-secondary';
                                                        $statusText = [
                                                            'pending' => 'Pending Review',
                                                            'approved' => 'Approved',
                                                            'rejected' => 'Rejected'
                                                        ];
                                                    @endphp
                                                    <span class="badge {{ $statusClass }}">
                                                        {{ $statusText[$document['status']] ?? ucfirst($document['status']) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if($document['path'])
                                                            <a href="{{ route('registrar.documents.serve', $document['path']) }}" 
                                                               target="_blank" 
                                                               class="btn btn-outline-primary" 
                                                               title="View Document">
                                                                <i class="ri-eye-line"></i>
                                                            </a>
                                                        @endif
                                                        
                                                        @if($document['status'] === 'pending')
                                                            <button type="button" 
                                                                    class="btn btn-outline-success" 
                                                                    title="Approve"
                                                                    onclick="approveDocumentInTab('{{ $document['application_id'] }}', {{ $document['index'] }})">
                                                                <i class="ri-check-line"></i>
                                                            </button>
                                                            
                                                            <button type="button" 
                                                                    class="btn btn-outline-danger" 
                                                                    title="Reject"
                                                                    onclick="rejectDocumentInTab('{{ $document['application_id'] }}', {{ $document['index'] }})">
                                                                <i class="ri-close-line"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ri-folder-line fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted">No documents found for review</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Appointments Tab -->
            <div class="tab-pane fade" id="appointments" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header" style="background-color: var(--accent-color); color: white;">
                        <h5 class="mb-0">
                            <i class="ri-calendar-line me-2"></i>
                            Appointment Management
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Appointment Filters -->
                        <form method="GET" action="{{ route('registrar.applications') }}" class="mb-3">
                            <input type="hidden" name="tab" value="appointments">
                            <div class="row">
                                <div class="col-md-3">
                                    <select class="form-select" name="appointment_status" onchange="this.form.submit()">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ request('appointment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ request('appointment_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ request('appointment_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="completed" {{ request('appointment_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="date" class="form-control" name="appointment_date" value="{{ request('appointment_date') }}" onchange="this.form.submit()">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="appointment_grade" onchange="this.form.submit()">
                                        <option value="">All Grades</option>
                                        <option value="Grade 7" {{ request('appointment_grade') === 'Grade 7' ? 'selected' : '' }}>Grade 7</option>
                                        <option value="Grade 8" {{ request('appointment_grade') === 'Grade 8' ? 'selected' : '' }}>Grade 8</option>
                                        <option value="Grade 9" {{ request('appointment_grade') === 'Grade 9' ? 'selected' : '' }}>Grade 9</option>
                                        <option value="Grade 10" {{ request('appointment_grade') === 'Grade 10' ? 'selected' : '' }}>Grade 10</option>
                                        <option value="Grade 11" {{ request('appointment_grade') === 'Grade 11' ? 'selected' : '' }}>Grade 11</option>
                                        <option value="Grade 12" {{ request('appointment_grade') === 'Grade 12' ? 'selected' : '' }}>Grade 12</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('registrar.applications') }}?tab=appointments" class="btn btn-outline-secondary">
                                        <i class="ri-refresh-line me-1"></i>Clear Filters
                                    </a>
                                </div>
                            </div>
                        </form>

                        @php
                            // Get appointments data
                            $appointmentsQuery = \App\Models\Enrollee::whereNotNull('preferred_schedule')
                                ->select(['id', 'application_id', 'first_name', 'last_name', 'email', 'grade_level_applied', 
                                         'preferred_schedule', 'enrollment_status', 'appointment_status', 'appointment_notes', 'created_at']);
                            
                            // Apply filters
                            if (request('appointment_status')) {
                                $appointmentsQuery->where('appointment_status', request('appointment_status'));
                            }
                            
                            if (request('appointment_date')) {
                                $appointmentsQuery->whereDate('preferred_schedule', request('appointment_date'));
                            }
                            
                            if (request('appointment_grade')) {
                                $appointmentsQuery->where('grade_level_applied', request('appointment_grade'));
                            }
                            
                            $appointments = $appointmentsQuery->orderBy('preferred_schedule', 'asc')->get();
                        @endphp

                        @if($appointments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Application ID</th>
                                            <th>Applicant Name</th>
                                            <th>Grade Level</th>
                                            <th>Preferred Schedule</th>
                                            <th>Appointment Status</th>
                                            <th>Application Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($appointments as $appointment)
                                            <tr>
                                                <td>{{ $appointment->application_id }}</td>
                                                <td>{{ $appointment->first_name }} {{ $appointment->last_name }}</td>
                                                <td>{{ $appointment->grade_level_applied }}</td>
                                                <td>
                                                    @if($appointment->preferred_schedule)
                                                        {{ \Carbon\Carbon::parse($appointment->preferred_schedule)->format('M d, Y g:i A') }}
                                                    @else
                                                        <span class="text-muted">Not specified</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $appointmentStatus = $appointment->appointment_status ?? 'pending';
                                                        $appointmentStatusClasses = [
                                                            'pending' => 'bg-warning text-dark',
                                                            'approved' => 'bg-success',
                                                            'rejected' => 'bg-danger',
                                                            'completed' => 'bg-info'
                                                        ];
                                                        $appointmentStatusClass = $appointmentStatusClasses[$appointmentStatus] ?? 'bg-secondary';
                                                    @endphp
                                                    <span class="badge {{ $appointmentStatusClass }}">
                                                        {{ ucfirst($appointmentStatus) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClasses = [
                                                            'pending' => 'bg-warning text-dark',
                                                            'approved' => 'bg-success',
                                                            'declined' => 'bg-danger',
                                                            'rejected' => 'bg-danger'
                                                        ];
                                                        $statusClass = $statusClasses[$appointment->enrollment_status] ?? 'bg-secondary';
                                                    @endphp
                                                    <span class="badge {{ $statusClass }}">
                                                        {{ ucfirst($appointment->enrollment_status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if($appointmentStatus === 'pending')
                                                            <button type="button" class="btn btn-outline-success" 
                                                                    onclick="approveAppointment('{{ $appointment->application_id }}')" 
                                                                    title="Approve Appointment">
                                                                <i class="ri-check-line"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger" 
                                                                    onclick="rejectAppointment('{{ $appointment->application_id }}')" 
                                                                    title="Reject Appointment">
                                                                <i class="ri-close-line"></i>
                                                            </button>
                                                        @endif
                                                        <!-- <button type="button" class="btn btn-outline-primary" 
                                                                onclick="scheduleAppointment('{{ $appointment->application_id }}')" 
                                                                title="Schedule/Reschedule">
                                                            <i class="ri-calendar-event-line"></i>
                                                        </button> -->
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ri-calendar-line fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted">No appointments found</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Notices Tab -->
            <div class="tab-pane fade" id="notices" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header" style="background-color: var(--dark-green); color: white;">
                        <h5 class="mb-0">
                            <i class="ri-notification-line me-2"></i>
                            Notice Management
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Notice Actions -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <button class="btn btn-primary" onclick="openStudentSelectionModal()">
                                    <i class="ri-user-search-line me-2"></i>Send Notice to Student
                                </button>
                                <small class="text-muted d-block mt-2">Select a student to send a personalized notice</small>
                            </div>
                            <div class="col-md-4">
                                <form method="GET" action="{{ route('registrar.applications') }}" class="d-flex">
                                    <input type="hidden" name="tab" value="notices">
                                    <select class="form-select me-2" name="notice_priority" onchange="this.form.submit()">
                                        <option value="">All Priorities</option>
                                        <option value="normal" {{ request('notice_priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="high" {{ request('notice_priority') === 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ request('notice_priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    <a href="{{ route('registrar.applications') }}?tab=notices" class="btn btn-outline-secondary">
                                        <i class="ri-refresh-line"></i>
                                    </a>
                                </form>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div id="notices-loading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2">Loading notices...</p>
                        </div>

                        <!-- Notices Content -->
                        <div id="notices-content" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Title</th>
                                            <th>Recipient</th>
                                            <th>Priority</th>
                                            <th>Date Sent</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="notices-table-body">
                                        <!-- Notices will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div id="notices-empty" class="text-center py-4" style="display: none;">
                            <i class="ri-notification-line fs-1 text-muted d-block mb-2"></i>
                            <p class="text-muted">No notices sent yet</p>
                            <small class="text-muted">Notices sent to students will appear here</small>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Load notices data when page loads if notices tab is active
                document.addEventListener('DOMContentLoaded', function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const activeTab = urlParams.get('tab');
                    if (activeTab === 'notices') {
                        // Small delay to ensure DOM is ready
                        setTimeout(() => {
                            if (typeof loadNoticesData === 'function') {
                                loadNoticesData();
                            }
                        }, 100);
                    }
                });
            </script>

            <!-- Data Change Request Tab -->
            <div class="tab-pane fade" id="data-change-requests" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header" style="background-color: var(--dark-green); color: white;">
                        <h5 class="mb-0">
                            <i class="ri-file-edit-line me-2"></i>
                            Data Change Request
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Test Button -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <!-- <button class="btn btn-outline-info btn-sm" onclick="testDataChangeRequests()">
                                    <i class="ri-bug-line me-1"></i>Test Connection
                                </button> -->
                                <span id="testResult" class="ms-2"></span>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0 me-3">Filters:</h6>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="changeRequestStatus" id="all-requests" value="" checked>
                                        <label class="btn btn-outline-secondary" for="all-requests">All</label>
                                        
                                        <input type="radio" class="btn-check" name="changeRequestStatus" id="pending-requests" value="pending">
                                        <label class="btn btn-outline-warning" for="pending-requests">Pending</label>
                                        
                                        <input type="radio" class="btn-check" name="changeRequestStatus" id="approved-requests" value="approved">
                                        <label class="btn btn-outline-success" for="approved-requests">Approved</label>
                                        
                                        <input type="radio" class="btn-check" name="changeRequestStatus" id="rejected-requests" value="rejected">
                                        <label class="btn btn-outline-danger" for="rejected-requests">Rejected</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="changeRequestSearch" placeholder="Search by student name or field...">
                                    <button class="btn btn-outline-secondary" type="button" onclick="searchChangeRequests()">
                                        <i class="ri-search-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Data Change Requests Table -->
                        <div class="table-responsive">
                            <table class="table table-hover" id="changeRequestsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th style="width: 20%">Student</th>
                                        <th style="width: 15%">Field</th>
                                        <th style="width: 25%">Change Details</th>
                                        <th style="width: 10%">Status</th>
                                        <th style="width: 15%">Submitted</th>
                                        <th style="width: 10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="changeRequestsTableBody">
                                    <!-- Data will be loaded via JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <!-- <div id="changeRequestsEmptyState" class="text-center py-4">
                            <i class="ri-file-edit-line fs-1 text-muted d-block mb-2"></i>
                            <p class="text-muted">No change requests found</p>
                            <small class="text-muted">Change requests will appear here when students submit them</small>
                        </div> -->
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Appointment Review Modal -->
    <div class="modal fade" id="appointmentReviewModal" tabindex="-1" aria-labelledby="appointmentReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentReviewModalLabel">
                        <i class="ri-calendar-line me-2"></i>Appointment Management
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Appointment Details -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Appointment Details</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Application ID:</strong> <span id="appt-app-id"></span></p>
                                    <p><strong>Student Name:</strong> <span id="appt-student-name"></span></p>
                                    <p><strong>Grade Level:</strong> <span id="appt-grade-level"></span></p>
                                    <p><strong>Current Schedule:</strong> <span id="appt-current-schedule"></span></p>
                                    <p><strong>Contact Number:</strong> <span id="appt-contact"></span></p>
                                    <p><strong>Email:</strong> <span id="appt-email"></span></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Appointment Actions -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Appointment Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Current Status</label>
                                        <span id="appt-current-status" class="badge fs-6"></span>
                                    </div>
                                    <div class="mb-3">
                                        <label for="appt-status-select" class="form-label">Change Status</label>
                                        <select class="form-select" id="appt-status-select">
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="appt-new-date" class="form-label">New Date</label>
                                        <input type="date" class="form-control" id="appt-new-date">
                                    </div>
                                    <div class="mb-3">
                                        <label for="appt-new-time" class="form-label">New Time</label>
                                        <input type="time" class="form-control" id="appt-new-time">
                                    </div>
                                    <div class="mb-3">
                                        <label for="appt-notes" class="form-label">Admin Notes</label>
                                        <textarea class="form-control" id="appt-notes" rows="3" placeholder="Enter appointment notes..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="saveAppointment()">
                        <i class="ri-save-line me-1"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Notice Modal -->
    <div class="modal fade" id="createNoticeModal" tabindex="-1" aria-labelledby="createNoticeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createNoticeModalLabel">
                        <i class="ri-notification-line me-2"></i>Create Notice
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="create-notice-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="notice-title" class="form-label">Notice Title</label>
                                    <input type="text" class="form-control" id="notice-title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="notice-priority" class="form-label">Priority</label>
                                    <select class="form-select" id="notice-priority" required>
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notice-recipients" class="form-label">Recipients</label>
                            <select class="form-select" id="notice-recipients" required>
                                <option value="">Select Recipients</option>
                                <option value="all">All Applicants</option>
                                <option value="pending">Pending Applications</option>
                                <option value="approved">Approved Applications</option>
                                <option value="specific">Specific Applicant</option>
                            </select>
                        </div>

                        <div class="mb-3" id="specific-applicant-div" style="display: none;">
                            <label for="specific-applicant" class="form-label">Select Applicant</label>
                            <select class="form-select" id="specific-applicant">
                                <option value="">Choose applicant...</option>
                                @foreach(\App\Models\Enrollee::select('id', 'application_id', 'first_name', 'last_name')->get() as $enrollee)
                                    <option value="{{ $enrollee->id }}">{{ $enrollee->application_id }} - {{ $enrollee->first_name }} {{ $enrollee->last_name }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="mb-3">
                            <label for="notice-message" class="form-label">Message</label>
                            <textarea class="form-control" id="notice-message" rows="5" required placeholder="Enter your notice message..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitNotice()">
                        <i class="ri-send-plane-line me-1"></i>Send Notice
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Notice Modal -->
    <div class="modal fade" id="bulkNoticeModal" tabindex="-1" aria-labelledby="bulkNoticeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkNoticeModalLabel">
                        <i class="ri-mail-send-line me-2"></i>Send Bulk Notice
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="bulk-notice-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bulk-notice-title" class="form-label">Notice Title</label>
                                    <input type="text" class="form-control" id="bulk-notice-title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bulk-notice-priority" class="form-label">Priority</label>
                                    <select class="form-select" id="bulk-notice-priority" required>
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Filter Recipients</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <select class="form-select" id="bulk-status-filter">
                                        <option value="">All Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" id="bulk-grade-filter">
                                        <option value="">All Grades</option>
                                        <option value="Grade 7">Grade 7</option>
                                        <option value="Grade 8">Grade 8</option>
                                        <option value="Grade 9">Grade 9</option>
                                        <option value="Grade 10">Grade 10</option>
                                        <option value="Grade 11">Grade 11</option>
                                        <option value="Grade 12">Grade 12</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-outline-info w-100" onclick="previewRecipients()">
                                        <i class="ri-eye-line me-1"></i>Preview Recipients
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Recipients Preview</label>
                            <div class="border rounded p-2" style="min-height: 60px; max-height: 120px; overflow-y: auto;">
                                <div id="recipients-preview" class="text-muted">
                                    Click "Preview Recipients" to see who will receive this notice
                                </div>
                            </div>
                        </div>


                        <div class="mb-3">
                            <label for="bulk-notice-message" class="form-label">Message</label>
                            <textarea class="form-control" id="bulk-notice-message" rows="4" required placeholder="Enter your notice message here..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="sendBulkNotice()">
                        <i class="ri-send-plane-line me-1"></i>Send Bulk Notice
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Notice Modal -->
    <div class="modal fade" id="viewNoticeModal" tabindex="-1" aria-labelledby="viewNoticeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewNoticeModalLabel">
                        <i class="ri-eye-line me-2"></i>View Notice
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0" id="view-notice-title"></h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p><strong>Priority:</strong> <span id="view-notice-priority" class="badge"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Sent:</strong> <span id="view-notice-date"></span></p>
                                    <p><strong>Status:</strong> <span id="view-notice-status" class="badge"></span></p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <strong>Recipient:</strong> <span id="view-notice-recipient"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Message:</strong>
                                <div class="border rounded p-3 mt-2" id="view-notice-message"></div>
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

</x-registrar-layout>
