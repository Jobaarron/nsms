<x-faculty-head-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Faculty Head Dashboard</h1>
      <div class="text-muted">
        <i class="ri-calendar-line me-1"></i>{{ $currentAcademicYear }}
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <!-- DASHBOARD STATISTICS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-application h-100">
          <div class="card-body text-center">
            <i class="ri-user-line display-6 mb-2"></i>
            <div>Total Teachers</div>
            <h3>{{ $stats['total_teachers'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-status h-100">
          <div class="card-body text-center">
            <i class="ri-book-open-line display-6 mb-2"></i>
            <div>Active Subject Assignments</div>
            <h3>{{ $stats['active_subject_assignments'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-application h-100">
          <div class="card-body text-center">
            <i class="ri-user-star-line display-6 mb-2"></i>
            <div>Active Adviser Assignments</div>
            <h3>{{ $stats['active_adviser_assignments'] }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- SECOND ROW OF STATS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-application h-100">
          <div class="card-body text-center">
            <i class="ri-task-line display-6 mb-2"></i>
            <div>Pending Grade Reviews</div>
            <h3>{{ $stats['pending_submissions'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-schedule h-100">
          <div class="card-body text-center">
            <i class="ri-book-line display-6 mb-2"></i>
            <div>Total Subjects</div>
            <h3>{{ $stats['total_subjects'] }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- RECENT GRADE SUBMISSIONS -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-file-list-3-line me-2"></i>Recent Grade Submissions for Review
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Teacher</th>
                <th>Subject</th>
                <th>Class</th>
                <th>Quarter</th>
                <th>Submitted</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentSubmissions as $submission)
              <tr>
                <td>{{ $submission->teacher->full_name }}</td>
                <td>{{ $submission->subject->subject_name }}</td>
                <td>{{ $submission->grade_level }} - {{ $submission->section }}</td>
                <td>{{ $submission->quarter }}</td>
                <td>{{ $submission->submitted_at->format('M d, Y') }}</td>
                <td>
                  <a href="{{ route('faculty-head.approve-grades') }}" class="btn btn-sm btn-outline-primary">
                    <i class="ri-eye-line me-1"></i>Review
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No pending submissions</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- RECENT SUBJECT TEACHER ASSIGNMENTS -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-book-open-line me-2"></i>Recent Subject Teacher Assignments
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Teacher</th>
                <th>Subject</th>
                <th>Grade</th>
                <th>Section</th>
                <th>Strand</th>
                <th>Track</th>
                <th>Assigned Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentSubjectAssignments as $assignment)
              <tr>
                <td>
                  <div class="fw-medium">{{ $assignment->teacher->user->name }}</div>
                </td>
                <td>
                  <div class="fw-medium">{{ $assignment->subject->subject_name }}</div>
                  @if($assignment->subject->subject_code)
                    <div class="text-muted small">{{ $assignment->subject->subject_code }}</div>
                  @endif
                </td>
                <td>
                  <span class="badge bg-primary">{{ $assignment->grade_level }}</span>
                </td>
                <td>
                  <span class="badge bg-secondary" 
                        style="cursor: pointer;" 
                        onclick="viewClassList('{{ $assignment->grade_level }}', '{{ $assignment->section }}', '{{ $assignment->strand ?? '' }}', '{{ $assignment->track ?? '' }}')"
                        title="Click to view class list">
                    {{ $assignment->section }}
                  </span>
                </td>
                <td>
                  @if($assignment->strand)
                    <span class="badge bg-info">{{ $assignment->strand }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  @if($assignment->track)
                    <span class="badge bg-warning text-dark">{{ $assignment->track }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  <div class="text-muted">{{ $assignment->created_at->format('M d, Y') }}</div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  <i class="ri-book-open-line display-6 mb-2 d-block"></i>
                  No recent subject teacher assignments
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- RECENT ADVISER ASSIGNMENTS -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-user-star-line me-2"></i>Recent Class Adviser Assignments
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Teacher</th>
                <th>Grade</th>
                <th>Section</th>
                <th>Strand</th>
                <th>Track</th>
                <th>Assigned Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentAdviserAssignments as $assignment)
              <tr>
                <td>
                  <div class="fw-medium">{{ $assignment->teacher->user->name }}</div>
                </td>
                <td>
                  <span class="badge bg-primary">{{ $assignment->grade_level }}</span>
                </td>
                <td>
                  <span class="badge bg-secondary" 
                        style="cursor: pointer;" 
                        onclick="viewClassList('{{ $assignment->grade_level }}', '{{ $assignment->section }}', '{{ $assignment->strand ?? '' }}', '{{ $assignment->track ?? '' }}')"
                        title="Click to view class list">
                    {{ $assignment->section }}
                  </span>
                </td>
                <td>
                  @if($assignment->strand)
                    <span class="badge bg-info">{{ $assignment->strand }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  @if($assignment->track)
                    <span class="badge bg-warning text-dark">{{ $assignment->track }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  <div class="text-muted">{{ $assignment->created_at->format('M d, Y') }}</div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  <i class="ri-user-star-line display-6 mb-2 d-block"></i>
                  No recent class adviser assignments
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Class List Modal -->
    <div class="modal fade" id="sectionDetailsModal" tabindex="-1" aria-labelledby="sectionDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="sectionDetailsModalLabel">
              <i class="ri-group-line me-2"></i>Class List Details
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="sectionDetailsContent">
              <!-- Loading State -->
              <div class="text-center" id="loadingState">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading class details...</p>
              </div>

              <!-- Class Content (Hidden initially) -->
              <div id="classContent" style="display: none;">
                <!-- Class Information Card -->
                <div class="card mb-4">
                  <div class="card-header">
                    <h6 class="mb-0" id="classTitle">
                      <i class="ri-information-line me-2"></i>Class Information
                      <span class="badge bg-primary ms-2" id="studentCount">0 Students</span>
                    </h6>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <!-- Class Adviser -->
                      <div class="col-md-6">
                        <h6 class="text-muted mb-2">Class Adviser</h6>
                        <div id="classAdviser">
                          <div class="text-muted">
                            <i class="ri-user-star-line me-2"></i>No adviser assigned
                          </div>
                        </div>
                      </div>

                      <!-- Subject Teachers Count -->
                      <div class="col-md-6">
                        <h6 class="text-muted mb-2">Subject Teachers</h6>
                        <div id="subjectTeachersCount">
                          <i class="ri-book-open-line text-success me-2"></i>
                          <strong>0 Teachers</strong>
                        </div>
                      </div>
                    </div>

                    <!-- Subject Teachers List -->
                    <div class="mt-3" id="subjectTeachersSection" style="display: none;">
                      <h6 class="text-muted mb-2">Subjects & Teachers</h6>
                      <div class="row" id="subjectTeachersList">
                        <!-- Dynamic content -->
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Student List Card -->
                <div class="card">
                  <div class="card-header">
                    <h6 class="mb-0">
                      <i class="ri-group-line me-2"></i>Student List
                    </h6>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle">
                        <thead class="table-light">
                          <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Grade</th>
                            <th>Section</th>
                            <th id="strandHeader" style="display: none;">Strand</th>
                            <th id="trackHeader" style="display: none;">Track</th>
                            <th>Contact</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody id="studentTableBody">
                          <!-- Dynamic student rows -->
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="text-center py-5" style="display: none;">
                  <i class="ri-group-line display-1 text-muted mb-3"></i>
                  <h5 class="text-muted">No Students Found</h5>
                  <p class="text-muted">No students are enrolled in this class.</p>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="ri-close-line me-2"></i>Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>
</x-faculty-head-layout>

@push('scripts')
@vite('resources/js/faculty-head-dashboard.js')
@endpush
