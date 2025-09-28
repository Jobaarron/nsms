<x-admin-layout>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @vite(['resources/js/admin-enrollment-management.js'])
    @vite(['resources/css/admin-enrollment-management.css'])
    
    @include('admin.enrollment-modals')
    
    <x-slot name="title">Enrollment Management</x-slot>

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="ri-graduation-cap-line me-2"></i>
                Enrollment Management
            </h1>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="refreshData()">
                    <i class="ri-refresh-line me-1"></i>Refresh
                </button>
                <button class="btn btn-success" onclick="exportData()">
                    <i class="ri-download-line me-1"></i>Export
                </button>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alert-container"></div>

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
                                <div class="fw-bold fs-4">{{ $totalApplications }}</div>
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
                                <div class="fw-bold fs-4">{{ $pendingApplications }}</div>
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
                                <div class="fw-bold fs-4">{{ $approvedApplications }}</div>
                                <div class="text-muted small">Approved</div>
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
                                <i class="ri-calendar-check-line fs-2 text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4">{{ $scheduledAppointments }}</div>
                                <div class="text-muted small">Scheduled</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                    <i class="ri-folder-line me-2"></i>Document Review
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
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="enrollmentTabContent">
            
            <!-- Applications Tab -->
            <div class="tab-pane fade show active" id="applications" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="ri-file-list-line me-2"></i>
                            Enrollment Applications
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" action="{{ route('admin.enrollments') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <select class="form-select" name="status" onchange="this.form.submit()">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="enrolled" {{ $status === 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="grade_level" onchange="this.form.submit()">
                                        <option value="">All Grades</option>
                                        <option value="Nursery" {{ $gradeLevel === 'Nursery' ? 'selected' : '' }}>Nursery</option>
                                        <option value="Junior Casa" {{ $gradeLevel === 'Junior Casa' ? 'selected' : '' }}>Junior Casa</option>
                                        <option value="Senior Casa" {{ $gradeLevel === 'Senior Casa' ? 'selected' : '' }}>Senior Casa</option>
                                        <option value="Grade 1" {{ $gradeLevel === 'Grade 1' ? 'selected' : '' }}>Grade 1</option>
                                        <option value="Grade 2" {{ $gradeLevel === 'Grade 2' ? 'selected' : '' }}>Grade 2</option>
                                        <option value="Grade 3" {{ $gradeLevel === 'Grade 3' ? 'selected' : '' }}>Grade 3</option>
                                        <option value="Grade 4" {{ $gradeLevel === 'Grade 4' ? 'selected' : '' }}>Grade 4</option>
                                        <option value="Grade 5" {{ $gradeLevel === 'Grade 5' ? 'selected' : '' }}>Grade 5</option>
                                        <option value="Grade 6" {{ $gradeLevel === 'Grade 6' ? 'selected' : '' }}>Grade 6</option>
                                        <option value="Grade 7" {{ $gradeLevel === 'Grade 7' ? 'selected' : '' }}>Grade 7</option>
                                        <option value="Grade 8" {{ $gradeLevel === 'Grade 8' ? 'selected' : '' }}>Grade 8</option>
                                        <option value="Grade 9" {{ $gradeLevel === 'Grade 9' ? 'selected' : '' }}>Grade 9</option>
                                        <option value="Grade 10" {{ $gradeLevel === 'Grade 10' ? 'selected' : '' }}>Grade 10</option>
                                        <option value="Grade 11" {{ $gradeLevel === 'Grade 11' ? 'selected' : '' }}>Grade 11</option>
                                        <option value="Grade 12" {{ $gradeLevel === 'Grade 12' ? 'selected' : '' }}>Grade 12</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Search by name, email, or application ID...">
                                        <button class="btn btn-outline-primary" type="submit">
                                            <i class="ri-search-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.enrollments') }}" class="btn btn-outline-secondary w-100">
                                        <i class="ri-close-line me-1"></i>Clear
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Applications Table -->
                        <div class="table-responsive">
                            <table class="table table-hover" id="applications-table">
                                <thead class="table-light">
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
                                    @forelse($applications as $application)
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
                                                        'rejected' => 'bg-danger',
                                                        'enrolled' => 'bg-info'
                                                    ];
                                                    $statusClass = $statusClasses[$application->enrollment_status] ?? 'bg-secondary';
                                                @endphp
                                                <span class="badge {{ $statusClass }}">
                                                    {{ ucfirst($application->enrollment_status) }}
                                                </span>
                                            </td>
                                            <td>{{ $application->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    <!-- Primary Actions -->
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="viewApplication({{ $application->id }})" title="View Application Details">
                                                        <i class="ri-eye-line me-1"></i>View
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewDocuments({{ $application->id }})" title="View Documents">
                                                        <i class="ri-file-list-line me-1"></i>Documents
                                                    </button>
                                                    
                                                    <!-- Status Actions (only for pending applications) -->
                                                    @if($application->enrollment_status === 'pending')
                                                        <button type="button" class="btn btn-sm btn-success" onclick="approveApplication({{ $application->id }})" title="Approve Application">
                                                            <i class="ri-check-line me-1"></i>Approve
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-warning" onclick="declineApplication({{ $application->id }})" title="Decline Application">
                                                            <i class="ri-close-line me-1"></i>Decline
                                                        </button>
                                                    @else
                                                        <span class="badge bg-{{ $application->enrollment_status === 'approved' ? 'success' : ($application->enrollment_status === 'rejected' ? 'danger' : 'secondary') }} me-2">
                                                            {{ ucfirst($application->enrollment_status) }}
                                                        </span>
                                                    @endif
                                                    
                                                    <!-- Management Actions -->
                                                    <button type="button" class="btn btn-sm btn-secondary" onclick="changeAppointment({{ $application->id }})" title="Schedule/Change Appointment">
                                                        <i class="ri-calendar-line me-1"></i>Schedule
                                                    </button>
                                                    
                                                    <!-- Danger Actions -->
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteApplication({{ $application->id }})" title="Delete Application">
                                                        <i class="ri-delete-bin-line me-1"></i>Delete
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
                                            <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()" title="Approve Selected Applications">
                                                <i class="ri-check-line me-1"></i>Approve
                                            </button>
                                            
                                            <button type="button" class="btn btn-warning btn-sm" onclick="bulkDecline()" title="Decline Selected Applications">
                                                <i class="ri-close-line me-1"></i>Decline
                                            </button>
                                            
                                            <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()" title="Delete Selected Applications">
                                                <i class="ri-delete-bin-line me-1"></i>Delete
                                            </button>
                                            
                                            <button type="button" class="btn btn-info btn-sm" onclick="exportSelected()" title="Export Selected Applications">
                                                <i class="ri-download-line me-1"></i>Export
                                            </button>
                                            
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="clearAllSelections()" title="Clear All Selections">
                                                <i class="ri-close-circle-line me-1"></i>Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing {{ $applications->firstItem() ?? 0 }} to {{ $applications->lastItem() ?? 0 }} of {{ $applications->total() }} applications
                            </div>
                            <div>
                                {{ $applications->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Review Tab -->
            <div class="tab-pane fade" id="documents" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="ri-folder-line me-2"></i>
                            Document Review & Verification
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select class="form-select" id="doc-status-filter">
                                    <option value="">All Documents</option>
                                    <option value="pending">Pending Review</option>
                                    <option value="verified">Verified</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="doc-type-filter">
                                    <option value="">All Types</option>
                                    <option value="birth_certificate">Birth Certificate</option>
                                    <option value="form_137">Form 137</option>
                                    <option value="good_moral">Good Moral</option>
                                    <option value="id_photo">ID Photo</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover" id="documents-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Application ID</th>
                                        <th>Student Name</th>
                                        <th>Document Type</th>
                                        <th>Upload Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documentsData as $docData)
                                        @foreach($docData['documents'] as $index => $document)
                                            <tr>
                                                <td>{{ $docData['enrollee']->application_id }}</td>
                                                <td>{{ $docData['enrollee']->first_name }} {{ $docData['enrollee']->last_name }}</td>
                                                <td>
                                                    @if(is_array($document))
                                                        {{ $document['type'] ?? 'Unknown' }}
                                                    @else
                                                        Document
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(is_array($document) && isset($document['uploaded_at']))
                                                        {{ \Carbon\Carbon::parse($document['uploaded_at'])->format('M d, Y') }}
                                                    @else
                                                        {{ $docData['enrollee']->created_at->format('M d, Y') }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $status = is_array($document) ? ($document['status'] ?? 'pending') : 'pending';
                                                        $statusClasses = [
                                                            'pending' => 'bg-warning text-dark',
                                                            'approved' => 'bg-success',
                                                            'verified' => 'bg-success',
                                                            'rejected' => 'bg-danger'
                                                        ];
                                                        $statusClass = $statusClasses[$status] ?? 'bg-secondary';
                                                    @endphp
                                                    <span class="badge {{ $statusClass }}">
                                                        {{ ucfirst($status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.enrollments.document.view', [$docData['enrollee']->id, $index]) }}" 
                                                           class="btn btn-sm btn-outline-primary" target="_blank" title="View Document">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                        <a href="{{ route('admin.enrollments.document.download', [$docData['enrollee']->id, $index]) }}" 
                                                           class="btn btn-sm btn-outline-info" title="Download">
                                                            <i class="ri-download-line"></i>
                                                        </a>
                                                        @if($status === 'pending')
                                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                                    onclick="updateDocumentStatus({{ $docData['enrollee']->id }}, {{ $index }}, 'approved')" title="Approve">
                                                                <i class="ri-check-line"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="updateDocumentStatus({{ $docData['enrollee']->id }}, {{ $index }}, 'rejected')" title="Reject">
                                                                <i class="ri-close-line"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="ri-file-line fs-1 d-block mb-2"></i>
                                                    No documents found
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointments Tab -->
            <div class="tab-pane fade" id="appointments" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="ri-calendar-line me-2"></i>
                            Schedule Appointments
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select class="form-select" id="appointment-status-filter">
                                    <option value="">All Appointments</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" class="form-control" id="appointment-date-filter">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover" id="appointments-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Application ID</th>
                                        <th>Student Name</th>
                                        <th>Requested Date</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notices Tab -->
            <div class="tab-pane fade" id="notices" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="ri-notification-line me-2"></i>
                            Send Notices to Applicants
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <button class="btn btn-primary" onclick="openCreateNoticeModal()">
                                    <i class="ri-add-line me-1"></i>Create Notice
                                </button>
                                <button class="btn btn-outline-info ms-2" onclick="openBulkNoticeModal()">
                                    <i class="ri-mail-send-line me-1"></i>Bulk Notice
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover" id="notices-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Notice Title</th>
                                        <th>Recipients</th>
                                        <th>Priority</th>
                                        <th>Sent Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notices as $notice)
                                        <tr>
                                            <td>{{ $notice->title }}</td>
                                            <td>
                                                @if($notice->type === 'global')
                                                    <span class="badge bg-info">All Applicants</span>
                                                @else
                                                    <span class="badge bg-secondary">Individual</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $priorityClasses = [
                                                        'low' => 'bg-info',
                                                        'medium' => 'bg-warning text-dark',
                                                        'high' => 'bg-danger'
                                                    ];
                                                    $priorityClass = $priorityClasses[$notice->priority] ?? 'bg-secondary';
                                                @endphp
                                                <span class="badge {{ $priorityClass }}">
                                                    {{ ucfirst($notice->priority) }}
                                                </span>
                                            </td>
                                            <td>{{ $notice->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <span class="badge bg-success">Sent</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewNotice({{ $notice->id }})" title="View Notice">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteNotice({{ $notice->id }})" title="Delete Notice">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="ri-notification-line fs-1 d-block mb-2"></i>
                                                    No notices found
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <!-- Notices Pagination -->
                            <div class="d-flex justify-content-center mt-3">
                                {{ $notices->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Modals -->
    @include('admin.enrollment-modals')
</x-admin-layout>
