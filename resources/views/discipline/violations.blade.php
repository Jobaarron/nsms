<x-discipline-layout>

  @vite(['resources/js/app.js', 'resources/css/index_discipline.css', 'resources/js/discipline_violations.js'])

  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
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
            <div>Resolved</div>
            <h3>{{ $stats['resolved'] ?? 0 }}</h3>
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
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($violations as $violation)
                @if($violation->effective_severity === 'major')
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
                  </td>
                  <td>
                    {{ $violation->violation_date->format('M d, Y') }}
                    @if($violation->violation_time)
                      <br><small class="text-muted">{{ date('h:i A', strtotime($violation->violation_time)) }}</small>
                    @endif
                  </td>
                  <td>
                    {{ $violation->sanction ?? 'N/A' }}
                  </td>
                  <td>
                    @if($violation->status === 'pending')
                      <span class="badge bg-warning text-dark">Pending</span>
                    @elseif($violation->status === 'investigating')
                      <span class="badge bg-info text-dark">In Progress</span>
                    @elseif($violation->status === 'resolved')
                      <span class="badge bg-success">Resolved</span>
                    @else
                      <span class="badge bg-secondary">{{ ucfirst($violation->status) }}</span>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      <button type="button" class="btn btn-sm btn-outline-primary"
                              onclick="viewViolation({{ $violation->id }})"
                              title="View Details">
                        <i class="ri-eye-line"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-warning"
                              onclick="editViolation({{ $violation->id }})"
                              title="Edit">
                        <i class="ri-edit-line"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-info"
                              onclick="forwardViolation({{ $violation->id }})"
                              title="Forward to Case Meeting">
                        <i class="ri-send-plane-line"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-danger"
                              onclick="deleteViolation({{ $violation->id }})"
                              title="Delete">
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                @endif
              @empty
              <tr>
                <td colspan="6" class="text-center py-5">
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
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Violation Date</label>
                <input type="date" class="form-control" name="violation_date" id="violationDate" value="{{ now()->toDateString() }}" required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Violation Time</label>
                <input type="time" class="form-control" id="violationTime" name="violation_time" value="{{ now()->format('H:i') }}">
              </div>




            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Violation</button>
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


</x-discipline-layout>