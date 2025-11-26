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
                    @if($caseMeeting->summary)
                        <a href="{{ route('guidance.case-meetings.disciplinary-conference-report.pdf', $caseMeeting->id) }}" target="_blank" class="btn btn-outline-danger">
                            <i class="ri-file-text-line me-2"></i>Discipline Conference Report
                        </a>
                    @endif
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
                        Back to List
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
                    @if(!$caseMeeting->forwarded_to_president)
                        <button class="btn btn-warning" onclick="forwardToPresident({{ $caseMeeting->id }}, '')" 
                                title="Forward to President">
                            <i class="ri-send-plane-line me-2"></i>Forward to President
                        </button>
                    @else
                        <button class="btn btn-secondary" disabled>
                            <i class="ri-check-line me-2"></i>Already Forwarded
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
                                <option value="submitted">Submitted</option>
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
    

    <!-- Interventions -->
    @if($caseMeeting->sanctions->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Interventions Applied</h5>
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

    @else
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-guidance">Case Meetings</h1>
                    <p class="text-muted mb-0">Manage case meetings and house visits</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="status-filter" onchange="filterCaseMeetings()">
                                <option value="">All Status</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="submitted">Submitted</option>
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
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search-filter" placeholder="Search student name..." onkeyup="filterCaseMeetings()">
                                <button class="btn btn-outline-secondary" onclick="clearFilters()">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-success w-100" type="button" onclick="window.open('/guidance/conference-summary-report/pdf', '_blank')">
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

                                            <!-- Summary Button - Disabled when summary exists or status is in_progress -->
                                            <button class="btn btn-outline-info {{ ($meeting->summary || $meeting->status === 'in_progress') ? 'disabled' : '' }}"
                                                onclick="{{ (!$meeting->summary && $meeting->status !== 'in_progress') ? 'openCreateSummaryModal(' . $meeting->id . ')' : '' }}"
                                                title="{{ $meeting->summary ? 'Summary already added' : ($meeting->status === 'in_progress' ? 'Cannot add summary while meeting is in progress' : 'Add Summary') }}">
                                                <i class="ri-file-text-line"></i>
                                            </button>

                                            <!-- Edit Button - Disabled when status is submitted -->
                                            <button class="btn btn-outline-secondary {{ $meeting->status === 'submitted' ? 'disabled' : '' }}" 
                                                onclick="{{ $meeting->status !== 'submitted' ? 'editCaseMeeting(' . $meeting->id . ')' : '' }}" 
                                                title="{{ $meeting->status === 'submitted' ? 'Cannot edit submitted meeting' : 'Edit' }}">
                                                <i class="ri-edit-line"></i>
                                            </button>

                                            <!-- Forward Button -->
                                            @if($meeting->status !== 'submitted')
                                                <button class="btn btn-outline-warning"
                                                    onclick="forwardToPresident({{ $meeting->id }}, '')"
                                                    title="Forward to President">
                                            @else
                                                <button class="btn btn-outline-secondary" disabled
                                                    title="Already submitted">
                                            @endif
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
                @if($caseMeetings->hasPages() || $caseMeetings->count() > 0)
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $caseMeetings->firstItem() ?? 0 }} to {{ $caseMeetings->lastItem() ?? 0 }} of {{ $caseMeetings->total() }} {{ Str::plural('case meeting', $caseMeetings->total()) }}
                        </div>
                        @if($caseMeetings->hasPages())
                            <div>
                                {{ $caseMeetings->links('pagination.custom') }}
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

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
                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold">AGREED ACTIONS AND INTERVENTION:</label>
                                <div class="border rounded p-3 bg-light">
                                    <p class="mb-2">To address the pupil's/student's behavior and support his/her/their improvement, the following actions and interventions have been agreed upon:</p>
                                    <div class="alert alert-info mb-3 p-2">
                                        <i class="ri-information-line me-2"></i><small><strong>Note:</strong> Options marked with <span class="badge bg-info text-white">Auto-Intervention</span> will automatically create corresponding intervention when selected.</small>
                                    </div>
                                    <div class="form-check mb-2 border border-info bg-light-blue p-2 rounded" style="background-color: #e3f2fd;">
                                        <input type="hidden" name="written_reflection" value="0">
                                        <input class="form-check-input" type="checkbox" name="written_reflection" value="1" id="action_written_reflection">
                                        <label class="form-check-label" for="action_written_reflection">
                                            <strong>Written Reflection as Warning</strong> <span class="badge bg-info text-white ms-1">Auto-Intervention</span> – The student will write a one-page reflection on the importance of respect, responsibility, and self-control.<span class="conditional-field" data-target="written_reflection"> To be submitted on or before: <input type="date" name="written_reflection_due" class="form-control d-inline-block w-auto ms-2" autocomplete="off"></span>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2 border border-info bg-light-blue p-2 rounded" style="background-color: #e3f2fd;">
                                        <input type="hidden" name="mentorship_counseling" value="0">
                                        <input class="form-check-input" type="checkbox" name="mentorship_counseling" value="1" id="action_mentorship">
                                        <label class="form-check-label" for="action_mentorship">
                                            <strong>Mentorship/Counseling</strong> <span class="badge bg-info text-white ms-1">Auto-Intervention</span> – The student will meet with the school counselor or a mentor weekly to discuss behavior management and coping strategies.<span class="conditional-field" data-target="mentorship_counseling"> Name of Mentor: <input type="text" name="mentor_name" class="form-control d-inline-block w-auto ms-2" placeholder="Name" autocomplete="off"></span>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="hidden" name="parent_teacher_communication" value="0">
                                        <input class="form-check-input" type="checkbox" name="parent_teacher_communication" value="1" id="action_parent_teacher">
                                        <label class="form-check-label" for="action_parent_teacher">
                                            <strong>Parent-Teacher Communication</strong> – Weekly progress updates will be shared with the parents to monitor the student's behavior and academic performance.<span class="conditional-field" data-target="parent_teacher_communication"> Date: <input type="date" name="parent_teacher_date" class="form-control d-inline-block w-auto ms-2" autocomplete="off"></span>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="hidden" name="restorative_justice_activity" value="0">
                                        <input class="form-check-input" type="checkbox" name="restorative_justice_activity" value="1" id="action_restorative_justice">
                                        <label class="form-check-label" for="action_restorative_justice">
                                            <strong>Restorative Justice Activity</strong> – The student will participate in a peer mediation or conflict resolution session if their behavior impacted others.<span class="conditional-field" data-target="restorative_justice_activity"> Date: <input type="date" name="restorative_justice_date" class="form-control d-inline-block w-auto ms-2" autocomplete="off"></span>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="hidden" name="follow_up_meeting" value="0">
                                        <input class="form-check-input" type="checkbox" name="follow_up_meeting" value="1" id="action_follow_up_meeting">
                                        <label class="form-check-label" for="action_follow_up_meeting">
                                            <strong>Follow-up Meeting</strong> – A follow-up conference will be held in one month to assess progress and determine if further interventions are needed.<span class="conditional-field" data-target="follow_up_meeting"> Date: <input type="date" name="follow_up_meeting_date" class="form-control d-inline-block w-auto ms-2" autocomplete="off"></span>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="hidden" name="community_service" value="0">
                                        <input class="form-check-input" type="checkbox" name="community_service" value="1" id="action_community_service">
                                        <label class="form-check-label" for="action_community_service">
                                            <strong>Conduct Community/ School Service</strong> – The student will be given the task to participate community or school service activity to promote cleanliness and orderliness of the surroundings.<span class="conditional-field" data-target="community_service"> <br>Date: <input type="date" name="community_service_date" class="form-control d-inline-block w-auto ms-2" autocomplete="off"> Assigned Area: <input type="text" name="community_service_area" class="form-control d-inline-block w-auto ms-2" placeholder="Area" autocomplete="off">.</span>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2 align-items-center d-flex flex-wrap border border-warning bg-warning-light p-2 rounded" style="background-color: #fff3cd;">
                                        <input type="hidden" name="suspension" value="0">
                                        <input class="form-check-input me-2" type="checkbox" name="suspension" value="1" id="action_suspension">
                                        <label class="form-check-label me-2" for="action_suspension"><strong>Suspension</strong> <span class="badge bg-warning text-dark ms-1">Auto-Intervention</span></label>
                                        <span class="conditional-field" data-target="suspension">– The student will serve
                                        <input type="hidden" name="suspension_3days" value="0">
                                        <input type="checkbox" class="form-check-input ms-2 me-1" name="suspension_3days" value="1" id="suspension_3days">
                                        <label for="suspension_3days" class="form-check-label me-2">3 days,</label>
                                        <input type="hidden" name="suspension_5days" value="0">
                                        <input type="checkbox" class="form-check-input ms-2 me-1" name="suspension_5days" value="1" id="suspension_5days">
                                        <label for="suspension_5days" class="form-check-label me-2">5 days</label>
                                        <span class="ms-2">or</span>
                                        <input type="number" name="suspension_other_days" class="form-control d-inline-block w-auto ms-2" min="1" placeholder="Other" maxlength="3" autocomplete="off">
                                        <span class="ms-2">more days suspension as a consequence for his/her/their actions, starting</span>
                                        <input type="date" name="suspension_start" class="form-control d-inline-block w-auto ms-2" placeholder="" autocomplete="off">
                                        <span class="ms-2">until</span>
                                        <input type="date" name="suspension_end" class="form-control d-inline-block w-auto ms-2" placeholder="" autocomplete="off">
                                        <span class="ms-2">and must accomplish the activity sheets missed during classes when he/she/they return to school on</span>
                                        <input type="date" name="suspension_return" class="form-control d-inline-block w-auto ms-2" placeholder="" autocomplete="off">.</span>
                                    </div>
                                    <div class="form-check mb-2 border border-danger bg-danger-light p-2 rounded" style="background-color: #f8d7da;">
                                        <input type="hidden" name="expulsion" value="0">
                                        <input class="form-check-input" type="checkbox" name="expulsion" value="1" id="action_expulsion">
                                        <label class="form-check-label" for="action_expulsion">
                                            <strong>Expulsion</strong> <span class="badge bg-danger text-white ms-1">Auto-Intervention</span> – A student may not be issued his certificate of eligibility to transfer at the end of the school year when he is undergoing a penalty of suspension or expulsion for failure to settle satisfactorily his financial or property obligations to the school. However, it shall be released as soon as he will finish serving the suspension or expulsion shall have been lifted. (RMPS Sec. 146)<span class="conditional-field" data-target="expulsion"> Date: <input type="date" name="expulsion_date" class="form-control d-inline-block w-auto ms-2" autocomplete="off">.</span>
                                        </label>
                                    </div>
                                </div>
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
                    @if(isset($caseMeeting) && isset($caseMeeting->violation))
                        <div class="mb-3">
                            <label class="form-label fw-bold">Violation Details:</label>
                            <table class="table table-sm">
                                <tbody>
                                    <tr><td><strong>Title:</strong></td><td>{{ $caseMeeting->violation->title ?? 'N/A' }}</td></tr>
                                    <tr><td><strong>Description:</strong></td><td>{{ $caseMeeting->violation->description ?? 'N/A' }}</td></tr>
                                    <tr><td><strong>Severity:</strong></td><td>{{ $caseMeeting->violation->severity ?? 'N/A' }}</td></tr>
                                    <tr><td><strong>Category:</strong></td><td>{{ $caseMeeting->violation->major_category ?? 'N/A' }}</td></tr>
                                    <tr><td><strong>Status:</strong></td><td>{{ $caseMeeting->violation->status ?? 'N/A' }}</td></tr>
                                    <tr><td><strong>Date:</strong></td><td>{{ $caseMeeting->violation->violation_date ? \Carbon\Carbon::parse($caseMeeting->violation->violation_date)->format('M d, Y') : 'N/A' }}</td></tr>
                                    <tr><td><strong>Time:</strong></td><td>{{ $caseMeeting->violation->violation_time ? date('h:i A', strtotime($caseMeeting->violation->violation_time)) : 'N/A' }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
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
                                 <label class="form-label">Date <span class="text-danger">*</span></label>
                                 <input type="date" class="form-control" name="scheduled_date" id="edit_scheduled_date" required min="{{ date('Y-m-d') }}">
                             </div>
                             <div class="col-md-6">
                                 <label class="form-label">Time <span class="text-danger">*</span></label>
                                 <input type="time" class="form-control" name="scheduled_time" id="edit_scheduled_time" required>
                             </div>

                             <div class="col-12">
                                 <label class="form-label">Summary</label>
                                 <textarea class="form-control" name="summary" id="edit_summary" rows="3" placeholder="Enter summary..."></textarea>
                             </div>
                             <div class="col-12">
                                 <label class="form-label">Interventions</label>
                                 <select class="form-control" name="sanction" id="edit_sanction">
                                     <option value="">Select Intervention</option>
                                     <!-- Options will be populated by JS -->
                                 </select>
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
