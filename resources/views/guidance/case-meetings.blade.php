<x-guidance-layout>
    @vite('resources/css/index_guidance.css')

    @if(isset($showDetail))
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
                    @if($caseMeeting->summary && $caseMeeting->sanctions->isNotEmpty() && !$caseMeeting->forwarded_to_president)
                        <button class="btn btn-warning" onclick="forwardToPresident({{ $caseMeeting->id }})">
                            <i class="ri-send-plane-line me-2"></i>Forward to President
                        </button>
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
                            <label class="form-label fw-semibold">Meeting Type</label>
                            <div>
                                <span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $caseMeeting->meeting_type)) }}</span>
                            </div>
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
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Urgency Level</label>
                            <div>
                                @if($caseMeeting->urgency_level)
                                    <span class="badge bg-{{ $caseMeeting->urgency_level === 'urgent' ? 'danger' : ($caseMeeting->urgency_level === 'high' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($caseMeeting->urgency_level) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                        @if($caseMeeting->location)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location</label>
                            <div>
                                <i class="ri-map-pin-line me-2 text-muted"></i>{{ $caseMeeting->location }}
                            </div>
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
                            <label class="form-label fw-semibold">Reason</label>
                            <div class="border rounded p-3 bg-light">{{ $caseMeeting->reason }}</div>
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
                        <div class="flex-grow-1">
                            <div class="fw-semibold fs-5">{{ $caseMeeting->student ? $caseMeeting->student->full_name : 'Unknown' }}</div>
                            <small class="text-muted">{{ $caseMeeting->student ? $caseMeeting->student->student_id : 'Unknown' }}</small>
                        </div>
                    </div>
                    @if($caseMeeting->student)
                        <div class="row g-2 text-sm">
                            <div class="col-6">
                                <strong>Grade:</strong><br>
                                {{ $caseMeeting->student->grade ?? 'N/A' }}
                            </div>
                            <div class="col-6">
                                <strong>Section:</strong><br>
                                {{ $caseMeeting->student->section ?? 'N/A' }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="card-title mb-0">Counselor Information</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-lg bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="ri-user-star-line fs-1 text-info"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold fs-5">{{ $caseMeeting->counselor ? $caseMeeting->counselor->name : 'Unknown' }}</div>
                            <small class="text-muted">Guidance Counselor</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Case Summary -->
    @if($caseMeeting->summary)
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
    @endif

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
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleCaseMeetingModal">
                        <i class="ri-calendar-event-line me-2"></i>Schedule Meeting
                    </button>
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
                        <div class="text-muted small">Forwarded</div>
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
                                <option value="in_progress">In Progress</option>
                                <option value="pre_completed">Pre-Completed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="forwarded">Forwarded</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <input type="date" class="form-control" id="date-filter">
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
                                    <th>Urgency</th>
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
                                        @if($meeting->urgency_level)
                                            <span class="badge bg-{{ $meeting->urgency_level === 'urgent' ? 'danger' : ($meeting->urgency_level === 'high' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($meeting->urgency_level) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- View Button - Always Available -->
                                            <button class="btn btn-outline-primary" onclick="viewCaseMeeting({{ $meeting->id }})" title="View Details">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            
                                            <!-- Schedule Button - Always Available -->
                                            <button class="btn btn-outline-primary" onclick="openScheduleMeetingModal({{ $meeting->student ? $meeting->student->id : 0 }})" title="Schedule Meeting">
                                                <i class="ri-calendar-event-line"></i>
                                            </button>

                                            <!-- Forward Button - Available when summary and sanctions exist -->
                                            <button class="btn btn-outline-warning {{ !$meeting->summary || $meeting->sanctions->isEmpty() ? 'disabled' : '' }}" 
                                                    onclick="{{ $meeting->summary && $meeting->sanctions->isNotEmpty() ? 'forwardToPresident(' . $meeting->id . ')' : '' }}" 
                                                    title="{{ $meeting->summary && $meeting->sanctions->isNotEmpty() ? 'Forward to President' : 'Summary and sanctions required before forwarding' }}">
                                                <i class="ri-send-plane-line"></i>
                                            </button>

                                            <!-- Summary Button - Available when no summary exists -->
                                            <button class="btn btn-outline-info {{ $meeting->summary ? 'disabled' : '' }}" 
                                                    onclick="{{ !$meeting->summary ? 'openCreateSummaryModal(' . $meeting->id . ')' : '' }}" 
                                                    title="{{ $meeting->summary ? 'Summary already added' : 'Add Summary' }}">
                                                <i class="ri-file-text-line"></i>
                                            </button>

                                            <!-- Edit Button - Always Available -->
                                            <button class="btn btn-outline-secondary" onclick="editCaseMeeting({{ $meeting->id }})" title="Edit">
                                                <i class="ri-edit-line"></i>
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

    <!-- Schedule Case Meeting Modal -->
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
                            <div class="col-md-6 schedule-field">
                                <label class="form-label">Urgency Level</label>
                                <select class="form-select" name="urgency_level">
                                    <option value="">Select Urgency</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
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
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Student</label>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="ri-user-line text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold" id="view_student_name">N/A</div>
                                    <small class="text-muted" id="view_student_id">N/A</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Counselor</label>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="ri-user-star-line text-info"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold" id="view_counselor_name">N/A</div>
                                    <small class="text-muted">Guidance Counselor</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Meeting Type</label>
                            <div>
                                <span class="badge bg-secondary" id="view_meeting_type">N/A</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <div>
                                <span class="badge" id="view_status">N/A</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Scheduled Date & Time</label>
                            <div>
                                <div class="fw-semibold" id="view_scheduled_date">TBD</div>
                                <small class="text-muted" id="view_scheduled_time">TBD</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Urgency Level</label>
                            <div>
                                <span class="badge" id="view_urgency_level">N/A</span>
                            </div>
                        </div>
                        <div class="col-md-6" id="view_location_container" style="display: none;">
                            <label class="form-label fw-semibold">Location</label>
                            <div>
                                <i class="ri-map-pin-line me-2 text-muted"></i><span id="view_location"></span>
                            </div>
                        </div>
                        <div class="col-md-6" id="view_completed_at_container" style="display: none;">
                            <label class="form-label fw-semibold">Completed At</label>
                            <div>
                                <i class="ri-calendar-check-line me-2 text-success"></i><span id="view_completed_at"></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Reason</label>
                            <div class="border rounded p-3 bg-light" id="view_reason">N/A</div>
                        </div>
                        <div class="col-12" id="view_notes_container" style="display: none;">
                            <label class="form-label fw-semibold">Notes</label>
                            <div class="border rounded p-3 bg-light" id="view_notes"></div>
                        </div>
                        <div class="col-12" id="view_summary_container" style="display: none;">
                            <label class="form-label fw-semibold">Case Summary</label>
                            <div class="border rounded p-3 bg-light" id="view_summary"></div>
                        </div>
                        <div class="col-12" id="view_recommendations_container" style="display: none;">
                            <label class="form-label fw-semibold">Recommendations</label>
                            <div class="border rounded p-3 bg-light" id="view_recommendations"></div>
                        </div>
                        <div class="col-12" id="view_follow_up_container" style="display: none;">
                            <label class="form-label fw-semibold">Follow-up</label>
                            <div class="border rounded p-3 bg-light" id="view_follow_up">
                                <div class="d-flex align-items-center">
                                    <i class="ri-calendar-event-line me-2 text-warning"></i>
                                    <span id="view_follow_up_text"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sanctions Section -->
                    <div class="mt-4" id="view_sanctions_container" style="display: none;">
                        <h6 class="fw-semibold mb-3">Sanctions</h6>
                        <div class="list-group list-group-flush" id="view_sanctions_list">
                            <!-- Sanctions will be populated here -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <div class="d-flex gap-2" id="view_actions_container">
                        <!-- Actions will be populated here -->
                    </div>
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
                            <div class="col-md-6">
                                <label class="form-label">Urgency Level</label>
                                <select class="form-select" name="urgency_level" id="edit_urgency_level">
                                    <option value="">Select Urgency</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
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
