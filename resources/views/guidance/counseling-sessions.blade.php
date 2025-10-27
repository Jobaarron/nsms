<x-guidance-layout>
    @vite('resources/css/index_guidance.css')

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-guidance">Counseling Sessions</h1>
                    <p class="text-muted">Manage individual and group counseling sessions</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="refreshCounselingSessions()">
                        <i class="ri-refresh-line me-2"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Counseling Schedule Table moved below -->
        </div>
    </div>

    <!-- Statistics Cards -->

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                 <input type="search" class="form-control" id="search-filter" placeholder="Search student name..." onkeypress="if(event.key === 'Enter') filterCounselingSessions()">
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

    <!-- Counseling Sessions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Counseling Sessions List</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="ri-more-line me-1"></i>Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportCounselingSessions()">
                                    <i class="ri-download-line me-2"></i>Export
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="printCounselingSessions()">
                                    <i class="ri-printer-line me-2"></i>Print
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="showPdfModal()">
                                    <i class="ri-file-pdf-line me-2"></i>View PDF Form
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-tabs mb-3" id="counselingTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active text-success" id="sessions-tab" data-bs-toggle="tab" data-bs-target="#sessionsTabPane" type="button" role="tab" aria-controls="sessionsTabPane" aria-selected="true">
                                Counseling Sessions
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-success" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#scheduleTabPane" type="button" role="tab" aria-controls="scheduleTabPane" aria-selected="false">
                                Counseling Schedule
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="counselingTabContent">
                        <div class="tab-pane fade show active" id="sessionsTabPane" role="tabpanel" aria-labelledby="sessions-tab">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="counseling-sessions-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student</th>
                                            <th>Recommended By</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($counselingSessions as $session)
                                        <tr data-session-id="{{ $session->id }}" data-status="{{ $session->status }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                                        <i class="ri-user-heart-line text-success"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $session->student->full_name ?? ($session->student ? ($session->student->first_name . ' ' . $session->student->last_name) : '-') }}</div>
                                                        <small class="text-muted">{{ $session->student->student_id ?? '-' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($session->recommender)
                                                    <div class="fw-semibold">{{ $session->recommender->name }}</div>
                                                    <small class="text-muted">{{ $session->recommender->roles->first()->name ?? 'Teacher' }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color:#198754;color:#fff;">
                                                    {{ $session->status_display['text'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm action-buttons">
                                                    <button class="btn btn-outline-info" onclick="showPdfModal({{ $session->id }})" title="View PDF">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#approveSessionModal" onclick="setApproveSessionId({{ $session->id }})" title="Approve">
                                                        <i class="ri-check-line"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="rejectCounselingSession({{ $session->id }})" title="Reject">
                                                        <i class="ri-close-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="ri-heart-pulse-line fs-1 d-block mb-2"></i>
                                                    <p>No counseling sessions found</p>
                                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleCounselingModal">
                                                        Schedule First Session
                                                    </button>
                                                </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($counselingSessions->hasPages())
                    <div class="card-footer bg-white border-0">
                        {{ $counselingSessions->links() }}
                    </div>
                    @endif
                </div>
                <div class="tab-pane fade" id="scheduleTabPane" role="tabpanel" aria-labelledby="schedule-tab">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Student Name</th>
                                    <th>Session No</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($scheduledSessions as $session)
                                <tr>
                                    <td>{{ $session->student->full_name ?? ($session->student ? ($session->student->first_name . ' ' . $session->student->last_name) : '-') }}</td>
                                    <td>{{ $session->session_no ?? '-' }}</td>
                                    <td>{{ ucfirst($session->status) }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm action-buttons">
                                            <button type="button" class="btn btn-outline-primary" title="View Details" onclick="showSessionDetailModal({{ $session->id }})">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#counselingSummaryModal" onclick="setCounselingSummarySessionId({{ $session->id }})" title="Add Summary Report">
                                                <i class="ri-file-add-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No scheduled sessions found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

    <!-- Schedule Counseling Session Modal -->
    <div class="modal fade" id="scheduleCounselingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Counseling Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="scheduleCounselingForm" onsubmit="submitCounselingSession(event)">
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
                                <label class="form-label">Session Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="session_type" required>
                                    <option value="individual">Individual Counseling</option>
                                    <option value="group">Group Counseling</option>
                                    <option value="family">Family Counseling</option>
                                    <option value="career">Career Counseling</option>
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
                                <label class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                                <select class="form-select" name="duration" required>
                                    <option value="30">30 minutes</option>
                                    <option value="45">45 minutes</option>
                                    <option value="60" selected>60 minutes</option>
                                    <option value="90">90 minutes</option>
                                    <option value="120">120 minutes</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" name="location" placeholder="e.g., Guidance Office, Conference Room">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" rows="3" required placeholder="Describe the reason for this counseling session..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Additional notes or preparation needed..."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="follow_up_required" id="followUpRequired">
                                    <label class="form-check-label" for="followUpRequired">
                                        Follow-up session required
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6" id="followUpDateContainer" style="display: none;">
                                <label class="form-label">Follow-up Date</label>
                                <input type="date" class="form-control" name="follow_up_date" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-heart-pulse-line me-2"></i>Schedule Session
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Approve Counseling Session Modal -->
    <div class="modal fade" id="approveSessionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Counseling Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="approveSessionForm" onsubmit="submitApproveSession(event)">
                    <input type="hidden" name="session_id" id="approveSessionId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Date Duration</label>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="date" class="form-control" name="start_date" required placeholder="Start Date">
                                </div>
                                <div class="col">
                                    <input type="date" class="form-control" name="end_date" required placeholder="End Date">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <!-- Duration field removed as requested -->
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time Limit</label>
                            <input type="number" class="form-control" name="time_limit" min="1" max="240" required placeholder="Enter time limit in minutes">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time</label>
                            <input type="time" class="form-control" name="time" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- PDF Preview Modal -->

    <div class="modal fade" id="pdfPreviewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Counseling Request Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="height:80vh;">
                    <iframe id="pdfFrame" src="" width="100%" height="100%" style="border:none;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" style="background-color:#198754;color:#fff;" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn" style="background-color:#198754;color:#fff;" onclick="acceptPdfSession()">Accept</button>
                    <button type="button" class="btn" style="background-color:#198754;color:#fff;" onclick="showFeedbackModal()">Reject</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="feedbackForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Please provide feedback for rejection:</label>
                            <textarea class="form-control" name="feedback" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" style="background-color:#198754;color:#fff;" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" style="background-color:#198754;color:#fff;">Submit Feedback</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Counseling Summary Modal -->
    <div class="modal fade" id="counselingSummaryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit Counseling Summary Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="counselingSummaryForm" onsubmit="submitCounselingSummary(event)">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="counseling_summary_report" class="form-label">Counseling Summary Report</label>
                            <textarea class="form-control" id="counseling_summary_report" name="counseling_summary_report" rows="5" required></textarea>
                        </div>
                        <input type="hidden" id="counseling_summary_session_id" name="session_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Alert Container for dynamic alerts -->
    <div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    @vite('resources/js/guidance_counseling-sessions.js')

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Bootstrap JS for modal functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Show feedback modal after hiding PDF modal
        function showFeedbackModal() {
            var pdfModalElem = document.getElementById('pdfPreviewModal');
            if (pdfModalElem) {
                try {
                    var pdfModal = bootstrap.Modal.getInstance(pdfModalElem);
                    if (pdfModal) {
                        pdfModal.hide();
                    } else {
                        pdfModal = bootstrap.Modal.getOrCreateInstance(pdfModalElem);
                        pdfModal.hide();
                    }
                } catch (e) {
                    // Fallback: forcibly hide modal if Bootstrap fails
                    pdfModalElem.classList.remove('show');
                    pdfModalElem.setAttribute('aria-hidden', 'true');
                    pdfModalElem.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    let backdrops = document.getElementsByClassName('modal-backdrop');
                    while (backdrops.length > 0) backdrops[0].parentNode.removeChild(backdrops[0]);
                }
            }
            // Always show feedback modal
            var feedbackModalElem = document.getElementById('feedbackModal');
            var feedbackModal = bootstrap.Modal.getOrCreateInstance(feedbackModalElem);
            feedbackModal.show();
        }
        // Add reject counseling session function if not in main JS file
        function rejectCounselingSession(sessionId) {
            if (confirm('Are you sure you want to reject this counseling session?')) {
                fetch(`/guidance/counseling-sessions/${sessionId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Counseling session rejected successfully', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message || 'Failed to reject session', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error rejecting session:', error);
                    showAlert('Error rejecting session', 'danger');
                });
            }
        }

        let counselingSummarySessionId = null;
        function setCounselingSummarySessionId(sessionId) {
            counselingSummarySessionId = sessionId;
            document.getElementById('counseling_summary_session_id').value = sessionId;
            document.getElementById('counseling_summary_report').value = '';
        }

        function submitCounselingSummary(event) {
            event.preventDefault();
            const sessionId = document.getElementById('counseling_summary_session_id').value;
            const summary = document.getElementById('counseling_summary_report').value;
            fetch(`/guidance/counseling-sessions/${sessionId}/summary-report`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ counseling_summary_report: summary })
            })
            .then(async response => {
                let data = null;
                let isJson = response.headers.get('content-type')?.includes('application/json');
                if (isJson) {
                    data = await response.json();
                } else {
                    let text = await response.text();
                    data = { message: text };
                }
                if (response.ok && data.success) {
                    showAlert('Summary report submitted successfully!', 'success');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    let msg = data && typeof data.message === 'string' ? data.message : (typeof data === 'object' ? JSON.stringify(data) : 'Failed to save summary report.');
                    alert(msg);
                    console.error('Summary report error:', data);
                }
            })
            .catch(error => {
                alert(error && error.message ? error.message : 'Error submitting summary report.');
                console.error('Network or JS error:', error);
            });
        }
    </script>

</x-guidance-layout>

<script>
// Fallback showAlert if not defined globally (for summary report modal)
if (typeof showAlert !== 'function') {
    function showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="ri-information-line me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        let container = document.getElementById('alert-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'alert-container';
            container.className = 'position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        container.insertAdjacentHTML('beforeend', alertHtml);
        setTimeout(() => {
            const alerts = container.querySelectorAll('.alert');
            if (alerts.length > 0) alerts[0].remove();
        }, 5000);
    }
}
</script>