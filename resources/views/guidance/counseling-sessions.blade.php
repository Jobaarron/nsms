<x-guidance-layout>
    @vite('resources/css/index_guidance.css')
    
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
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleCounselingModal">
                            <i class="ri-heart-pulse-line me-2"></i>Schedule Session
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
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="ri-heart-pulse-line fs-2 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-4" id="scheduled-sessions">{{ $counselingSessions->where('status', 'scheduled')->count() }}</div>
                            <div class="text-muted small">Scheduled</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="ri-checkbox-circle-line fs-2 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-4" id="completed-sessions">{{ $counselingSessions->where('status', 'completed')->count() }}</div>
                            <div class="text-muted small">Completed</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="ri-group-line fs-2 text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-4" id="group-sessions">{{ $counselingSessions->where('session_type', 'group')->count() }}</div>
                            <div class="text-muted small">Group Sessions</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="ri-briefcase-line fs-2 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-4" id="career-sessions">{{ $counselingSessions->where('session_type', 'career')->count() }}</div>
                            <div class="text-muted small">Career Counseling</div>
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
                                <select class="form-select" id="status-filter" onchange="filterCounselingSessions()">
                                    <option value="">All Status</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="rescheduled">Rescheduled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Session Type</label>
                                <select class="form-select" id="type-filter" onchange="filterCounselingSessions()">
                                    <option value="">All Types</option>
                                    <option value="individual">Individual</option>
                                    <option value="group">Group</option>
                                    <option value="family">Family</option>
                                    <option value="career">Career</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date Range</label>
                                <input type="date" class="form-control" id="date-filter" onchange="filterCounselingSessions()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search-filter" placeholder="Search student name..." onkeyup="filterCounselingSessions()">
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
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="counseling-sessions-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Session Type</th>
                                        <th>Date & Time</th>
                                        <th>Duration</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($counselingSessions as $session)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    <i class="ri-user-heart-line text-success"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $session->student->full_name ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $session->student->student_id ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $session->session_type_display['class'] }}">
                                                <i class="{{ $session->session_type_display['icon'] }} me-1"></i>
                                                {{ $session->session_type_display['text'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>{{ $session->scheduled_date->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $session->scheduled_time->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $session->duration }} min</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $session->location ?: 'TBD' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $session->status_display['class'] }}">
                                                {{ $session->status_display['text'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="viewCounselingSession({{ $session->id }})" title="View Details">
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                                @if($session->status === 'scheduled')
                                                    <button class="btn btn-outline-success" onclick="completeCounselingSession({{ $session->id }})" title="Mark Complete">
                                                        <i class="ri-check-line"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning" onclick="rescheduleCounselingSession({{ $session->id }})" title="Reschedule">
                                                        <i class="ri-calendar-todo-line"></i>
                                                    </button>
                                                @endif
                                                <button class="btn btn-outline-secondary" onclick="editCounselingSession({{ $session->id }})" title="Edit">
                                                    <i class="ri-edit-line"></i>
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
                    </div>
                    @if($counselingSessions->hasPages())
                    <div class="card-footer bg-white border-0">
                        {{ $counselingSessions->links() }}
                    </div>
                    @endif
                </div>
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

    @vite('resources/js/guidance_counseling-sessions.js')
    
    <script>
        // Show/hide follow-up date field
        document.getElementById('followUpRequired').addEventListener('change', function() {
            const followUpContainer = document.getElementById('followUpDateContainer');
            followUpContainer.style.display = this.checked ? 'block' : 'none';
        });
    </script>
</x-guidance-layout>
