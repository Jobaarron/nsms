<x-guidance-layout>

  @vite(['resources/js/app.js', 'resources/css/guidance_student-violations.css', 'resources/js/guidance_student-violations.js'])


      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="section-title mb-0">Violations Management</h1>
          <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createViolationModal">
              <i class="ri-add-line me-2"></i>Report New Violation
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
          <div class="col-6 col-lg-3">
            <div class="card card-summary stats-card h-100" style="background-color: #ffc107;">
              <div class="card-body text-center">
                <i class="ri-time-line display-6 mb-2"></i>
                <div>Pending</div>
                <h3>{{ $stats['pending'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary stats-card h-100" style="background-color: #17a2b8;">
              <div class="card-body text-center">
                <i class="ri-search-line display-6 mb-2"></i>
                <div>Investigating</div>
                <h3>{{ $stats['investigating'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary stats-card h-100" style="background-color: #28a745;">
              <div class="card-body text-center">
                <i class="ri-check-line display-6 mb-2"></i>
                <div>Resolved</div>
                <h3>{{ $stats['resolved'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary stats-card h-100" style="background-color: #dc3545;">
              <div class="card-body text-center">
                <i class="ri-error-warning-line display-6 mb-2"></i>
                <div>Severe Cases</div>
                <h3>{{ $stats['severe'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
        </div>

        <!-- SEARCH AND FILTER SECTION -->
        <div class="search-filter-section">
          <div class="row align-items-end">
            <div class="col-md-3">
              <label for="searchInput" class="form-label fw-bold">Search Violations</label>
              <div class="input-group">
                <span class="input-group-text"><i class="ri-search-line"></i></span>
                <input type="text" class="form-control" id="searchInput" placeholder="Search violations...">
              </div>
            </div>
            <div class="col-md-2">
              <label for="statusFilter" class="form-label fw-bold">Status</label>
              <select class="form-select" id="statusFilter">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="investigating">Investigating</option>
                <option value="resolved">Resolved</option>
                <option value="dismissed">Dismissed</option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="severityFilter" class="form-label fw-bold">Severity</label>
              <select class="form-select" id="severityFilter">
                <option value="">All Severities</option>
                <option value="minor">Minor</option>
                <option value="major">Major</option>
                <option value="severe">Severe</option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="typeFilter" class="form-label fw-bold">Type</label>
              <select class="form-select" id="typeFilter">
                <option value="">All Types</option>
                <option value="late">Late Arrival</option>
                <option value="uniform">Uniform</option>
                <option value="misconduct">Misconduct</option>
                <option value="academic">Academic</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-3">
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
                    <th>ID</th>
                    <th>Student</th>
                    <th>Violation</th>
                    <th>Type</th>
                    <th>Severity</th>

                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($violations as $violation)
                  <tr>
                    <td>#{{ $violation->id }}</td>
                    <td>
                      <div>
                        <strong>{{ $violation->student->first_name }} {{ $violation->student->last_name }}</strong>
                        <br><small class="text-muted">{{ $violation->student->student_id }}</small>
                      </div>
                    </td>
                    <td>
                      <strong>{{ $violation->title }}</strong>
                      <br><small class="text-muted">{{ Str::limit($violation->description, 50) }}</small>
                    </td>
                    <td>
                      <span class="badge bg-secondary">{{ ucfirst($violation->violation_type) }}</span>
                    </td>
                    <td>
                      <span class="badge bg-{{ $violation->severity === 'minor' ? 'success' : ($violation->severity === 'major' ? 'warning' : 'danger') }}">
                        {{ ucfirst($violation->severity) }}
                      </span>
                    </td>

                    <td>
                      {{ $violation->violation_date->format('M d, Y') }}
                      @if($violation->violation_time)
                        <br><small class="text-muted">{{ date('h:i A', strtotime($violation->violation_time)) }}</small>
                      @endif
                    </td>
<td>
  @php
    // Use case meeting status if available, else fallback to violation status
    $caseMeetingStatus = $violation->caseMeeting->status ?? null;
    $statusToShow = $caseMeetingStatus ?? $violation->status;

    // Map status to badge class similar to case meetings
    $badgeClass = 'secondary';
    switch ($statusToShow) {
      case 'scheduled':
        $badgeClass = 'primary';
        break;
      case 'in_progress':
        $badgeClass = 'info';
        break;
      case 'pre_completed':
        $badgeClass = 'warning';
        break;
      case 'completed':
        $badgeClass = 'success';
        break;
      case 'cancelled':
        $badgeClass = 'danger';
        break;
      case 'forwarded':
        $badgeClass = 'warning';
        break;
      case 'pending':
        $badgeClass = 'warning';
        break;
      case 'investigating':
        $badgeClass = 'info';
        break;
      case 'resolved':
        $badgeClass = 'success';
        break;
      case 'dismissed':
        $badgeClass = 'secondary';
        break;
      default:
        $badgeClass = 'secondary';
    }
  @endphp
  <span class="badge bg-{{ $badgeClass }}">
    {{ ucfirst(str_replace('_', ' ', $statusToShow)) }}
  </span>
</td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="viewViolation({{ $violation->id }})"
                                title="View Details">
                          <i class="ri-eye-line"></i>
                        </button>
                        @if($violation->status !== 'resolved')
                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="editViolation({{ $violation->id }})"
                                title="Edit">
                          <i class="ri-edit-line"></i>
                        </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="deleteViolation({{ $violation->id }})"
                                title="Delete">
                          <i class="ri-delete-bin-line"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="9" class="text-center py-5">
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
              {{ $violations->links() }}
            </div>
            @endif
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- Edit Violation Modal -->
  <div class="modal fade" id="editViolationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-edit-line me-2"></i>Edit Violation
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="editViolationForm" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body" id="editViolationModalBody">
            <!-- Violation edit form will be loaded here -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="hideModal('editViolationModal')">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="ri-save-line me-2"></i>Update Violation
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Violation Modal -->
  <div class="modal fade" id="viewViolationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-eye-line me-2"></i>Violation Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="viewViolationModalBody">
          <!-- Violation details will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="hideModal('viewViolationModal')">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Create Violation Modal -->
  <div class="modal fade" id="createViolationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-add-line me-2"></i>Report New Violation
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('guidance.violations.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="student_id" class="form-label">Student <span class="text-danger">*</span></label>
                  <select class="form-select" id="student_id" name="student_id" required>
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                      <option value="{{ $student->id }}">
                        {{ $student->first_name }} {{ $student->last_name }} 
                        ({{ $student->student_id ?: 'No ID' }})
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="violation_type" class="form-label">Violation Type <span class="text-danger">*</span></label>
                  <select class="form-select" id="violation_type" name="violation_type" required>
                    <option value="">Select Type</option>
                    <option value="late">Late Arrival</option>
                    <option value="uniform">Uniform Violation</option>
                    <option value="misconduct">Misconduct</option>
                    <option value="academic">Academic Dishonesty</option>
                    <option value="other">Other</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="title" name="title" required 
                     placeholder="Brief description of the violation">
            </div>
            
            <div class="mb-3">
              <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
              <textarea class="form-control" id="description" name="description" rows="3" required
                        placeholder="Detailed description of what happened"></textarea>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="severity" class="form-label">Severity <span class="text-danger">*</span></label>
                  <select class="form-select" id="severity" name="severity" required>
                    <option value="minor">Minor</option>
                    <option value="major">Major</option>
                    <option value="severe">Severe</option>
                  </select>
                </div>
              </div>

            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="violation_date" class="form-label">Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="violation_date" name="violation_date"
                         value="{{ date('Y-m-d') }}" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="violation_time" class="form-label">Time</label>
                  <input type="time" class="form-control" id="violation_time" name="violation_time">
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="location" class="form-label">Location</label>
              <input type="text" class="form-control" id="location" name="location" 
                     placeholder="Where did this happen?">
            </div>
            
            <div class="mb-3">
              <label for="witnesses" class="form-label">Witnesses</label>
              <textarea class="form-control" id="witnesses" name="witnesses" rows="2"
                        placeholder="Names of witnesses (one per line)"></textarea>
            </div>
            
            
            <div class="mb-3">
              <label for="attachments" class="form-label">Attachments</label>
              <input type="file" class="form-control" id="attachments" name="attachments[]" multiple
                     accept="image/*,.pdf,.doc,.docx">
              <div class="form-text">Upload images or documents as evidence</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="ri-save-line me-2"></i>Report Violation
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Violation Modal -->
  <div class="modal fade" id="viewViolationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-eye-line me-2"></i>Violation Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="viewViolationModalBody">
          <!-- Violation details will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="hideModal('viewViolationModal')">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Violation Modal -->
  <div class="modal fade" id="editViolationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-edit-line me-2"></i>Edit Violation
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="editViolationForm" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <div class="modal-body" id="editViolationModalBody">
            <!-- Edit form will be loaded here -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="hideModal('editViolationModal')">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="ri-save-line me-2"></i>Update Violation
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

</x-guidance-layout>
