<x-discipline-layout>
    @vite(['resources/js/app.js', 'resources/css/index_discipline.css', 'resources/js/discipline_violations.js'])
    
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Violations Management</h1>
      <div class="d-flex align-items-center gap-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#recordViolationModal">
          <i class="ri-add-line me-2"></i>Record Violation
        </button>
        <div class="text-muted">
          <i class="ri-calendar-line me-1"></i>{{ now()->format('F j, Y') }}
        </div>
      </div>
    </div>

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

    <!-- STATISTICS CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-4">
        <div class="card card-summary stats-card h-100" style="background-color: #ffc107; color: black;">
          <div class="card-body text-center">
            <i class="ri-time-line display-6 mb-2"></i>
            <div>Pending</div>
            <h3>{{ $stats['pending'] ?? 0 }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-4">
        <div class="card card-summary stats-card h-100" style="background-color: #17a2b8; color: white;">
          <div class="card-body text-center">
            <i class="ri-loader-4-line display-6 mb-2"></i>
            <div>In Progress</div>
            <h3>{{ $stats['investigating'] ?? 0 }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-4">
        <div class="card card-summary stats-card h-100" style="background-color: #28a745; color: white;">
          <div class="card-body text-center">
            <i class="ri-check-line display-6 mb-2"></i>
            <div>Case Closed</div>
            <h3>{{ $stats['case_closed'] ?? 0 }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- SEARCH AND FILTER SECTION -->
    <div class="search-filter-section">
      <div class="row align-items-end">
        <div class="col-md-4">
          <label for="searchInput" class="form-label fw-bold">Search Violations</label>
          <div class="input-group">
            <span class="input-group-text"><i class="ri-search-line"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Search violations...">
          </div>
        </div>
        <div class="col-md-4">
          <label for="dateFilter" class="form-label fw-bold">Date Filter</label>
          <input type="date" class="form-control" id="dateFilter" title="Filter by date">
        </div>
      </div>
    </div>

    <!-- VIOLATIONS TABLE -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Violations List</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="violationsTable">
            <thead>
              <tr>
                <th>Student</th>
                <th>Violation</th>
                <th>Date</th>
                <th>Sanction</th>
                <th>Status</th>
                <th>Student Reply</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($violations as $violation)
                <tr>
                  <td>
                    <div>
                      <strong>{{ $violation->student->first_name }} {{ $violation->student->last_name }}</strong>
                      <br><small class="text-muted">{{ $violation->student->student_id }}</small>
                    </div>
                  </td>
                  <td>
                    <strong>{{ $violation->title }}</strong>
                    <br><small class="text-muted">{{ Str::limit($violation->description, 50) }}</small>
                    
                    @if($violation->is_escalated ?? false)
                      <br><span class="badge bg-danger mt-1"><i class="ri-arrow-up-line me-1"></i>Escalated</span>
                    @endif
                    
                    @if($violation->escalation_reason ?? false)
                      <br><small class="text-muted"><em>{{ $violation->escalation_reason }}</em></small>
                    @endif
                  </td>
                  <td>
                    {{ $violation->violation_date->format('M d, Y') }}
                    @if($violation->violation_time)
                      <br><small class="text-muted">{{ date('h:i A', strtotime($violation->violation_time)) }}</small>
                    @endif
                  </td>
                  <td>
                    @if($violation->status === 'case_closed' && $violation->caseMeeting)
                      @php
                        $interventions = [];
                        // Build detailed intervention list with dates/details
                        if($violation->caseMeeting->written_reflection) {
                          $details = $violation->caseMeeting->written_reflection_due ? 'Due: ' . date('M j, Y', strtotime($violation->caseMeeting->written_reflection_due)) : '';
                          $interventions[] = ['name' => 'Written Reflection', 'details' => $details, 'class' => 'bg-primary'];
                        }
                        if($violation->caseMeeting->follow_up_meeting) {
                          $details = $violation->caseMeeting->follow_up_meeting_date ? 'Date: ' . date('M j, Y', strtotime($violation->caseMeeting->follow_up_meeting_date)) : '';
                          $interventions[] = ['name' => 'Follow-up Meeting', 'details' => $details, 'class' => 'bg-info'];
                        }
                        if($violation->caseMeeting->mentorship_counseling) {
                          $details = $violation->caseMeeting->mentor_name ? 'Mentor: ' . $violation->caseMeeting->mentor_name : '';
                          $interventions[] = ['name' => 'Mentorship/Counseling', 'details' => $details, 'class' => 'bg-success'];
                        }
                        if($violation->caseMeeting->parent_teacher_communication) {
                          $details = $violation->caseMeeting->parent_teacher_date ? 'Date: ' . date('M j, Y', strtotime($violation->caseMeeting->parent_teacher_date)) : '';
                          $interventions[] = ['name' => 'Parent Communication', 'details' => $details, 'class' => 'bg-warning'];
                        }
                        if($violation->caseMeeting->community_service) {
                          $details = [];
                          if($violation->caseMeeting->community_service_date) $details[] = 'Date: ' . date('M j, Y', strtotime($violation->caseMeeting->community_service_date));
                          if($violation->caseMeeting->community_service_area) $details[] = 'Area: ' . $violation->caseMeeting->community_service_area;
                          $interventions[] = ['name' => 'Community Service', 'details' => implode(', ', $details), 'class' => 'bg-secondary'];
                        }
                        if($violation->caseMeeting->suspension) {
                          $details = [];
                          if($violation->caseMeeting->suspension_start) $details[] = 'Start: ' . date('M j, Y', strtotime($violation->caseMeeting->suspension_start));
                          if($violation->caseMeeting->suspension_end) $details[] = 'End: ' . date('M j, Y', strtotime($violation->caseMeeting->suspension_end));
                          if($violation->caseMeeting->suspension_other_days) $details[] = $violation->caseMeeting->suspension_other_days . ' days';
                          elseif($violation->caseMeeting->suspension_3days) $details[] = '3 days';
                          elseif($violation->caseMeeting->suspension_5days) $details[] = '5 days';
                          $interventions[] = ['name' => 'Suspension', 'details' => implode(', ', $details), 'class' => 'bg-danger'];
                        }
                        if($violation->caseMeeting->expulsion) {
                          $details = $violation->caseMeeting->expulsion_date ? 'Date: ' . date('M j, Y', strtotime($violation->caseMeeting->expulsion_date)) : '';
                          $interventions[] = ['name' => 'Expulsion', 'details' => $details, 'class' => 'bg-danger'];
                        }
                      @endphp
                      @if(count($interventions) > 0)
                        @foreach(array_slice($interventions, 0, 2) as $intervention)
                          <div class="mb-1">
                            <span class="badge {{ $intervention['class'] }} text-white small">{{ $intervention['name'] }}</span>
                            @if($intervention['details'])
                              <br><small class="text-muted">{{ $intervention['details'] }}</small>
                            @endif
                          </div>
                        @endforeach
                        @if(count($interventions) > 2)
                          <small class="text-muted">+{{ count($interventions) - 2 }} more interventions</small>
                        @endif
                      @else
                        <span class="text-muted">No interventions assigned</span>
                      @endif
                    @else
                      <span class="text-muted">{{ $violation->sanction ?? 'N/A' }}</span>
                    @endif
                  </td>
                  <td>
                    @if($violation->status === 'pending')
                      <span class="badge bg-warning text-dark">Pending</span>
                    @elseif(in_array($violation->status, ['investigating', 'in_progress']))
                      <span class="badge bg-info text-dark">In Progress</span>
                    @elseif($violation->status === 'resolved')
                      <span class="badge bg-success">Resolved</span>
                    @else
                      <span class="badge bg-secondary">{{ ucfirst($violation->status) }}</span>
                    @endif
                  </td>
                  <td>
                    @php
                      $hasStudentReply = !empty($violation->student_statement) || !empty($violation->incident_feelings) || !empty($violation->action_plan);
                    @endphp
                    @if($hasStudentReply)
                      <span class="badge bg-success">
                        <i class="ri-check-line me-1"></i>Replied
                      </span>
                    @else
                      <span class="badge bg-warning text-dark">
                        <i class="ri-time-line me-1"></i>Pending Reply
                      </span>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      <button type="button" class="btn btn-sm btn-outline-primary"
                              onclick="viewViolation({{ $violation->id }})"
                              title="View Details">
                        <i class="ri-eye-line"></i>
                      </button>
                      @php
                        // Check if student has replied
                        $hasStudentReply = !empty($violation->student_statement) || 
                                          !empty($violation->incident_feelings) || 
                                          !empty($violation->action_plan);
                        // Disable edit button if status is not pending or if student has replied
                        $canEdit = $violation->status === 'pending' && !$hasStudentReply;
                        $editTooltip = !$canEdit ? 
                          ($hasStudentReply ? 'Cannot edit: Student has already replied' : 'Cannot edit: Violation is ' . $violation->status) : 
                          'Edit';
                      @endphp
                      <button type="button" class="btn btn-sm btn-outline-warning"
                              onclick="editViolation({{ $violation->id }})"
                              title="{{ $editTooltip }}"
                              @if(!$canEdit) disabled @endif>
                        <i class="ri-edit-line"></i>
                      </button>
                      @php
                        // Check if violation can be forwarded
                        $canForward = false;
                        $forwardTooltip = 'Forward to Case Meeting';
                        
                        // Check if student has replied (reuse from above)
                        $hasStudentReplyForForward = !empty($violation->student_statement) || 
                                                     !empty($violation->incident_feelings) || 
                                                     !empty($violation->action_plan);
                        
                        // Only enable if status is pending AND student has replied
                        if ($violation->status === 'pending' && $hasStudentReplyForForward) {
                            $canForward = true;
                            $forwardTooltip = 'Forward to Case Meeting';
                        } elseif (!$hasStudentReplyForForward) {
                            $forwardTooltip = 'Cannot forward: Student must reply to narrative report first';
                        } elseif ($violation->status !== 'pending') {
                            $forwardTooltip = 'Cannot forward: Violation is ' . $violation->status;
                        }
                      @endphp
                      <button type="button" class="btn btn-sm btn-outline-info"
                              onclick="{{ $canForward ? 'forwardViolation(' . $violation->id . ')' : '' }}"
                              title="{{ $forwardTooltip }}"
                              @if(!$canForward) disabled @endif>
                        <i class="ri-send-plane-line"></i>
                      </button>
                      @php
                        // Disable delete button if status is not pending or if student has replied
                        $canDelete = $violation->status === 'pending' && !$hasStudentReply;
                        $deleteTooltip = !$canDelete ? 
                          ($hasStudentReply ? 'Cannot delete: Student has already replied' : 'Can only delete pending violations without student reply') : 
                          'Delete';
                      @endphp
                      <button type="button" class="btn btn-sm btn-outline-danger"
                              onclick="deleteViolation({{ $violation->id }})"
                              title="{{ $deleteTooltip }}"
                              @if(!$canDelete) disabled @endif>
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center py-5">
                  <i class="ri-alert-line display-4 text-muted"></i>
                  <p class="text-muted mt-2">No violations found</p>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        @if($violations->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
          <div>
            <small class="text-muted">
              Showing {{ $violations->firstItem() ?: 0 }} to {{ $violations->lastItem() ?: 0 }} 
              of {{ $violations->total() }} violations
            </small>
          </div>
          {{ $violations->links('pagination.custom') }}
        </div>
        @endif
      </div>
    </div>

    <!-- Record Violation Modal -->
  <div class="modal fade" id="recordViolationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <form id="recordViolationForm" class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-alert-line me-2"></i>Record Violation
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
        <!-- Hidden Fields -->
        <input type="hidden" name="reported_by" value="{{ auth()->id() }}">

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Title/Offense</label>
                <select class="form-select" name="title" id="violationTitle" required>
                  <option value="">-- Select Offense --</option>
                </select>
                <small class="text-muted">Select an offense to automatically determine severity and category</small>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Student</label>
                <div class="position-relative">
                  <input type="text" class="form-control" id="violationStudentSearch" placeholder="Type student name or ID..." autocomplete="off">
                  <div id="studentSuggestions" class="suggestions-list" style="display: none;">
                    <!-- Suggestions will be populated here -->
                  </div>
                </div>
                <div id="selectedStudentsContainer" class="mt-2">
                  <!-- Selected students will be added here -->
                </div>
                <small class="text-muted">Start typing to search for students</small>
                <div class="alert alert-info mt-2 mb-0" style="font-size: 0.875em;">
                  <i class="ri-information-line me-1"></i>
                  <strong>Note:</strong> Only fully enrolled students can have violations recorded.
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Violation Date</label>
                <input type="text" class="form-control" name="violation_date" id="violationDate" value="{{ now()->toDateString() }}" required readonly>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Violation Time</label>
                <input type="text" class="form-control" id="violationTime" name="violation_time" 
                       value="7:00 AM" readonly placeholder="Select time...">
                <small class="text-muted">School hours: 7:00 AM - 4:00 PM</small>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Confirm Violation</button>
        </div>
      </form>
    </div>
  </div>

  <!-- VIEW VIOLATION MODAL -->
  <div class="modal fade" id="viewViolationModal" tabindex="-1" aria-labelledby="viewViolationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewViolationModalLabel">Violation Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="viewViolationModalBody">
          <!-- Content will be loaded dynamically -->
        </div>
      </div>
    </div>
  </div>

  <!-- EDIT VIOLATION MODAL -->
  <div class="modal fade" id="editViolationModal" tabindex="-1" aria-labelledby="editViolationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editViolationModalLabel">Edit Violation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editViolationForm" method="POST">
          <div class="modal-body" id="editViolationModalBody">
            <!-- Content will be loaded dynamically -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="ri-save-line me-2"></i>Update Violation
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- INCIDENT FORM PREVIEW MODAL -->
  <div class="modal fade" id="incidentFormPreviewModal" tabindex="-1" aria-labelledby="incidentFormPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="incidentFormPreviewModalLabel">Incident Form Preview</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="incidentFormPreviewBody" style="background: #fff;">
          <!-- Incident form HTML will be injected here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="downloadIncidentPDFBtn">
            <i class="ri-download-2-line me-2"></i>Save as PDF
          </button>
          <button type="button" class="btn btn-success" id="printIncidentFormBtn">
            <i class="ri-printer-line me-2"></i>Print
          </button>
        </div>
      </div>
    </div>
  </div>

<script>
  // Make current user data available to JavaScript
  window.currentUser = {
    id: {{ auth()->id() }},
    name: "{{ auth()->user()->name }}",
  };
</script>
    </div>
</x-discipline-layout>