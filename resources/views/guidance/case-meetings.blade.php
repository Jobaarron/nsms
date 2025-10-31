<x-guidance-layout>
    @vite('resources/css/index_guidance.css')

    @if(isset($showDetail))
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Automatically open the PDF modal when viewing details
    var caseMeetingId = {{ $caseMeeting->id }};
    var pdfUrl = `/guidance/pdf/case-meeting/${caseMeetingId}`;
    document.getElementById('incidentFormPdfIframe').src = pdfUrl;
    var modal = new bootstrap.Modal(document.getElementById('incidentFormPdfPreviewModal'));
    modal.show();
    });
    </script>
    <!-- Case Meeting Detail View -->
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('guidance.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('guidance.case-meetings.index') }}">Case Meetings</a></li>
                            <li class="breadcrumb-item active">Details</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-guidance">Case Meeting Details</h1>
                    <p class="text-muted">Meeting ID: {{ $caseMeeting->id }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-info" onclick="openIncidentFormPdfPreview({{ $caseMeeting->id }})">
                        <i class="ri-file-pdf-line me-2"></i>View Incident PDF
                    </button>
                    <a href="{{ url('guidance/pdf/case-meeting/' . $caseMeeting->id) }}" target="_blank" class="btn btn-outline-success">
                        <i class="ri-attachment-2"></i> View Attachment
                    </a>
    <!-- INCIDENT FORM PDF PREVIEW MODAL (READ-ONLY) -->
    <div class="modal fade" id="incidentFormPdfPreviewModal" tabindex="-1" aria-labelledby="incidentFormPdfPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="incidentFormPdfPreviewModalLabel">Incident Form PDF Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="background: #fff; min-height: 600px;">
                    <iframe id="incidentFormPdfIframe" src="" width="100%" height="600px" style="border: none;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    function openIncidentFormPdfPreview(caseMeetingId) {
        // Use the dynamic PDF route for the case meeting
        var pdfUrl = `/guidance/pdf/case-meeting/${caseMeetingId}`;
        document.getElementById('incidentFormPdfIframe').src = pdfUrl;
        var modal = new bootstrap.Modal(document.getElementById('incidentFormPdfPreviewModal'));
        modal.show();
    }
    </script>
                    <a href="{{ route('guidance.case-meetings.index') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-2"></i>Back to List
                    </a>
                    @if($caseMeeting->status !== 'completed' && $caseMeeting->status !== 'forwarded')
                        <button class="btn btn-success" onclick="completeCaseMeeting({{ $caseMeeting->id }})">
                            <i class="ri-checkbox-circle-line me-2"></i>Mark Complete
                        </button>
                    @endif
                    @if(!$caseMeeting->summary || $caseMeeting->sanctions->isEmpty())
                        <button class="btn btn-info" onclick="openCreateSummaryModal({{ $caseMeeting->id }})">
                            <i class="ri-file-text-line me-2"></i>Add Summary
                        </button>
                    @endif
                    @if($caseMeeting->summary && $caseMeeting->status === 'pre_completed' && !$caseMeeting->forwarded_to_president)
                        <button class="btn btn-warning" onclick="forwardToPresident({{ $caseMeeting->id }})">
                            <i class="ri-send-plane-line me-2"></i>Forward to President
                        </button>
                    @endif
                    @if(!in_array($caseMeeting->status, ['in_progress','scheduled','pre_completed','submitted','completed']))
                        <button class="btn btn-secondary" disabled>Action Disabled</button>
                    @endif
                    <a href="{{ route('guidance.case-meetings.edit', $caseMeeting) }}" class="btn btn-primary">
                        <i class="ri-edit-line me-2"></i>Edit Meeting
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Meeting Overview -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Meeting Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <!-- Meeting Type removed -->
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <div>
                                <span class="badge {{ $caseMeeting->status === 'scheduled' ? 'bg-primary' : ($caseMeeting->status === 'completed' ? 'bg-success' : ($caseMeeting->status === 'cancelled' ? 'bg-danger' : ($caseMeeting->status === 'in_progress' ? 'bg-warning' : 'bg-secondary'))) }}">
                                    {{ ucfirst($caseMeeting->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Scheduled Date & Time</label>
                            <div>
                                <div class="fw-semibold">
                                    {{ $caseMeeting->scheduled_date ? $caseMeeting->scheduled_date->format('M d, Y') : 'TBD' }}
                                </div>
                                <small class="text-muted">
                                    {{ $caseMeeting->scheduled_time ? $caseMeeting->scheduled_time->format('h:i A') : 'TBD' }}
                                </small>
                            </div>
                        </div>

                        @if($caseMeeting->location)
                        <div class="col-md-6">
                            <!-- Location removed -->
                        </div>
                        @endif
                        @if($caseMeeting->completed_at)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Completed At</label>
                            <div>
                                <i class="ri-calendar-check-line me-2 text-success"></i>{{ $caseMeeting->completed_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                        @endif
                        <div class="col-12">
                            <!-- Reason removed -->
                        </div>
                        @if($caseMeeting->notes)
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <div class="border rounded p-3 bg-light">{{ $caseMeeting->notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Student & Counselor Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0">
                    <h6 class="card-title mb-0">Student Information</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-lg bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="ri-user-line fs-1 text-primary"></i>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="status-filter" onchange="filterCaseMeetings()">
                                <option value="">All Status</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="pre_completed">Pre-Completed</option>
                                <option value="submitted">Submitted</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="date-filter-start" placeholder="From">
                                <span class="input-group-text">to</span>
                                <input type="date" class="form-control" id="date-filter-end" placeholder="To">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search-filter" placeholder="Search student name..." onkeyup="filterCaseMeetings()">
                                <button class="btn btn-outline-secondary" onclick="clearFilters()">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-success w-100" type="button" onclick="window.open('/guidance/conference-summary-report/pdf', '_blank')">
                                <i class="ri-file-list-3-line me-1"></i> SUMMARY REPORT
                            </button>
                        </div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Case Summary</h5>
                </div>
                <div class="card-body">
                    <div class="border rounded p-3 bg-light mb-3">{{ $caseMeeting->summary }}</div>
                    @if($caseMeeting->recommendations)
                        <h6 class="fw-semibold">Recommendations</h6>
                        <div class="border rounded p-3 bg-light mb-3">{{ $caseMeeting->recommendations }}</div>
                    @endif
                    @if($caseMeeting->follow_up_required)
                        <h6 class="fw-semibold">Follow-up</h6>
                        <div class="border rounded p-3 bg-light">
                            <div class="d-flex align-items-center">
                                <i class="ri-calendar-event-line me-2 text-warning"></i>
                                <span>{{ $caseMeeting->follow_up_required ? 'Follow-up required' : 'No follow-up needed' }}</span>
                                @if($caseMeeting->follow_up_date)
                                    <span class="ms-2">on {{ $caseMeeting->follow_up_date->format('M d, Y') }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    

    <!-- Sanctions -->
    @if($caseMeeting->sanctions->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Sanctions Applied</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($caseMeeting->sanctions as $sanction)
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $sanction->sanction }}</h6>
                                    @if($sanction->notes)
                                        <p class="mb-1 text-muted">{{ $sanction->notes }}</p>
                                    @endif
                                    <small class="text-muted">
                                        Created: {{ $sanction->created_at->format('M d, Y h:i A') }}
                                    </small>
                                </div>
                                <span class="badge {{ $sanction->status === 'active' ? 'bg-danger' : 'bg-secondary' }}">
                                    {{ ucfirst($sanction->status) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Forwarded Status -->
    @if($caseMeeting->forwarded_to_president)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="ri-send-plane-line fs-4 me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-1">Forwarded to President</h6>
                        <p class="mb-0">This case has been forwarded to the school president for review.</p>
                        @if($caseMeeting->forwarded_at)
                            <small class="text-muted">Forwarded on {{ $caseMeeting->forwarded_at->format('M d, Y h:i A') }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Create Case Summary Modal -->
    <div class="modal fade" id="createCaseSummaryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Case Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createCaseSummaryForm" onsubmit="submitCaseSummary(event)">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Case Summary <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="summary" rows="4" required placeholder="Provide a detailed summary of the case meeting..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Recommendations</label>
                                <textarea class="form-control" name="recommendations" rows="3" placeholder="Any recommendations or actions to be taken..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="follow_up_required" id="follow_up_required">
                                    <label class="form-check-label" for="follow_up_required">
                                        Follow-up Required
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Follow-up Date</label>
                                <input type="date" class="form-control" name="follow_up_date" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-2"></i>Save Summary
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @else
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-guidance">Case Meetings</h1>
                    <p class="text-muted">Manage case meetings and house visits</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="refreshCaseMeetings()">
                        <i class="ri-refresh-line me-2"></i>Refresh
                    </button>
                    <!-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleCaseMeetingModal">
                        <i class="ri-calendar-event-line me-2"></i>Schedule Meeting
                    </button> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="ri-calendar-event-line fs-2 text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold fs-4" id="scheduled-meetings">{{ $caseMeetings->where('status', 'scheduled')->count() }}</div>
                        <div class="text-muted small">Scheduled</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="ri-time-line fs-2 text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold fs-4" id="in-progress-meetings">{{ $caseMeetings->where('status', 'in_progress')->count() }}</div>
                        <div class="text-muted small">In Progress</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                            <i class="ri-file-text-line fs-2 text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold fs-4" id="pre-completed-meetings">{{ $caseMeetings->where('status', 'pre_completed')->count() }}</div>
                        <div class="text-muted small">Pre-Completed</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="flex-shrink-0 me-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="ri-checkbox-circle-line fs-2 text-success"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold fs-4" id="completed-meetings">{{ $caseMeetings->where('status', 'completed')->count() }}</div>
                    <div class="text-muted small">Completed</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                            <i class="ri-send-plane-line fs-2 text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold fs-4" id="forwarded-cases">{{ $caseMeetings->where('forwarded_to_president', true)->count() }}</div>
                        <div class="text-muted small">Submitted</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="status-filter" onchange="filterCaseMeetings()">
                                <option value="">All Status</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="pre_completed">Pre-Completed</option>
                                <option value="submitted">Submitted</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="date-filter-start" placeholder="From">
                                <span class="input-group-text">to</span>
                                <input type="date" class="form-control" id="date-filter-end" placeholder="To">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search-filter" placeholder="Search student name..." onkeyup="filterCaseMeetings()">
                                <button class="btn btn-outline-secondary" onclick="clearFilters()">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-success w-auto" type="button" onclick="window.open('/guidance/conference-summary-report/pdf', '_blank')">
                                <i class="ri-file-list-3-line me-1"></i> Conference Summary Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Case Meetings Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Case Meetings List</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="ri-more-line me-1"></i>Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportCaseMeetings()">
                                    <i class="ri-download-line me-2"></i>Export
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="printCaseMeetings()">
                                    <i class="ri-printer-line me-2"></i>Print
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="case-meetings-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Date & Time</th>
                                    <th>Meeting Status</th>

                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($caseMeetings as $meeting)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="ri-user-line text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $meeting->student ? $meeting->student->full_name : 'N/A' }}</div>
                                                <small class="text-muted">{{ $meeting->student ? $meeting->student->student_id : 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $meeting->scheduled_date ? $meeting->scheduled_date->format('M d, Y') : 'TBD' }}</div>
                                        <small class="text-muted">{{ $meeting->scheduled_time ? $meeting->scheduled_time->format('h:i A') : 'TBD' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $meeting->status_display['class'] }}">
                                            {{ $meeting->status_display['text'] }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- View Button - Always Available -->
                                            <button class="btn btn-outline-primary" onclick="viewCaseMeeting({{ $meeting->id }})" title="View Details">
                                                <i class="ri-eye-line"></i>
                                            </button>

                                            <!-- Schedule Button - Permanently disabled after scheduling -->
                                            <button class="btn btn-outline-primary {{ $meeting->scheduled_date ? 'disabled' : '' }}"
                                                onclick="{{ !$meeting->scheduled_date ? 'openScheduleMeetingModal(' . ($meeting->student ? $meeting->student->id : 0) . ')' : '' }}"
                                                title="{{ $meeting->scheduled_date ? 'Already scheduled' : 'Schedule Meeting' }}">
                                                <i class="ri-calendar-event-line"></i>
                                            </button>

                                            <!-- Summary Button - Permanently disabled after summary is added -->
                                            <button class="btn btn-outline-info {{ $meeting->summary ? 'disabled' : '' }}"
                                                onclick="{{ !$meeting->summary ? 'openCreateSummaryModal(' . $meeting->id . ')' : '' }}"
                                                title="{{ $meeting->summary ? 'Summary already added' : 'Add Summary' }}">
                                                <i class="ri-file-text-line"></i>
                                            </button>

                                            <!-- Edit Button - Always enabled -->
                                            <button class="btn btn-outline-secondary" onclick="editCaseMeeting({{ $meeting->id }})" title="Edit">
                                                <i class="ri-edit-line"></i>
                                            </button>

                                            <!-- Forward Button - Enabled only if scheduled, summary, sanctions, and not submitted -->
                                            <button class="btn btn-outline-warning {{ !$meeting->scheduled_date || !$meeting->summary || $meeting->sanctions->isEmpty() || $meeting->status === 'submitted' ? 'disabled' : '' }}"
                                                onclick="{{ $meeting->scheduled_date && $meeting->summary && $meeting->sanctions->isNotEmpty() && $meeting->status !== 'submitted' ? 'forwardToPresident(' . $meeting->id . ')' : '' }}"
                                                title="{{ $meeting->status === 'submitted' ? 'Already submitted' : ($meeting->scheduled_date && $meeting->summary && $meeting->sanctions->isNotEmpty() ? 'Forward to President' : 'Schedule, summary, and sanctions required before forwarding') }}">
                                                <i class="ri-send-plane-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri-calendar-line fs-1 d-block mb-2"></i>
                                            <p>No case meetings found</p>
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleCaseMeetingModal">
                                                Schedule First Meeting
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($caseMeetings->hasPages())
                <div class="card-footer bg-white border-0">
                    {{ $caseMeetings->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    Schedule Case Meeting Modal
    <div class="modal fade" id="scheduleCaseMeetingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Case Meeting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="scheduleCaseMeetingForm" onsubmit="submitCaseMeeting(event)">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6 schedule-field">
                                <label class="form-label">Student <span class="text-danger">*</span></label>
                                <select class="form-select" name="student_id" required>
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->student_id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 schedule-field">
                                <label class="form-label">Meeting Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="meeting_type" required>
                                    <option value="case_meeting">Case Meeting</option>
                                    <option value="house_visit">House Visit</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="scheduled_date" required min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="scheduled_time" required>
                            </div>

                            <div class="col-12 schedule-field">
                                <label class="form-label">Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" rows="3" required placeholder="Describe the reason for this meeting..."></textarea>
                            </div>
                            <div class="col-12 schedule-field">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Additional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-calendar-event-line me-2"></i>Schedule Meeting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Case Summary Modal -->
    <div class="modal fade" id="createCaseSummaryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Case Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createCaseSummaryForm" onsubmit="submitCaseSummary(event)">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Case Summary <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="summary" rows="4" required placeholder="Provide a detailed summary of the case meeting..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Recommendations</label>
                                <textarea class="form-control" name="recommendations" rows="3" placeholder="Any recommendations or actions to be taken..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="follow_up_required" id="follow_up_required">
                                    <label class="form-check-label" for="follow_up_required">
                                        Follow-up Required
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Follow-up Date</label>
                                <input type="date" class="form-control" name="follow_up_date" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-2"></i>Save Summary
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Case Meeting Modal -->
    <div class="modal fade" id="viewCaseMeetingModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Case Meeting Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewCaseMeetingModalBody">
                    <!-- Dynamic content will be injected here by JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Case Meeting Modal -->
    <div class="modal fade" id="editCaseMeetingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Case Meeting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editCaseMeetingForm" onsubmit="submitEditCaseMeeting(event)">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Student <span class="text-danger">*</span></label>
                                <select class="form-select" name="student_id" id="edit_student_id" required>
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->student_id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Meeting Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="meeting_type" id="edit_meeting_type" required>
                                    <option value="case_meeting">Case Meeting</option>
                                    <option value="house_visit">House Visit</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="scheduled_date" id="edit_scheduled_date" required min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="scheduled_time" id="edit_scheduled_time" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" id="edit_reason" rows="3" required placeholder="Describe the reason for this meeting..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" id="edit_notes" rows="2" placeholder="Additional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-2"></i>Update Meeting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @endif

    @vite('resources/js/guidance_case-meetings.js')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</x-guidance-layout>
