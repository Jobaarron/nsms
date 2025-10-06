<x-guidance-layout>
    @vite('resources/css/index_guidance.css')
    
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
                <div class="card-body d-flex align-items-center">
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
                            <input type="date" class="form-control" id="date-filter" onchange="filterCaseMeetings()">
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
                                    <th>Location</th>
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
                                                <div class="fw-semibold">{{ $meeting->student->full_name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $meeting->student->student_id ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $meeting->scheduled_date ? $meeting->scheduled_date->format('M d, Y') : 'TBD' }}</div>
                                        <small class="text-muted">{{ $meeting->scheduled_time ? $meeting->scheduled_time->format('h:i A') : 'TBD' }}</small>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $meeting->location ?: 'TBD' }}</span>
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
                                            <button class="btn btn-outline-primary" onclick="openScheduleMeetingModal({{ $meeting->student->id }})" title="Schedule Meeting">
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
                                    <td colspan="7" class="text-center py-4">
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
                            <div class="col-md-6">
                                <label class="form-label">Student <span class="text-danger">*</span></label>
                                <select class="form-select" name="student_id" required>
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->student_id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" name="location" placeholder="e.g., Guidance Office, Student's Home">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Urgency Level</label>
                                <select class="form-select" name="urgency_level">
                                    <option value="">Normal</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" rows="3" required placeholder="Describe the reason for this meeting..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Additional notes or preparation needed..."></textarea>
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
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" name="location" id="edit_location" placeholder="e.g., Guidance Office, Student's Home">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Urgency Level</label>
                                <select class="form-select" name="urgency_level" id="edit_urgency_level">
                                    <option value="">Normal</option>
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
                                <textarea class="form-control" name="notes" id="edit_notes" rows="2" placeholder="Additional notes or preparation needed..."></textarea>
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

    @vite('resources/js/guidance_case-meetings.js')
</x-guidance-layout>