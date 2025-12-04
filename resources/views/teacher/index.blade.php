<x-teacher-layout>
  <!-- MAIN CONTENT -->
  <main class="container-fluid px-3 px-md-4 py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
      <h1 class="section-title mb-2 mb-md-0">Teacher Dashboard</h1>
      <div class="text-muted">
        <i class="ri-calendar-line me-1"></i>{{ $currentAcademicYear ?? date('Y') . '-' . (date('Y') + 1) }}
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

    <!-- GRADE SUBMISSION STATUS -->
    <div class="alert {{ $gradeSubmissionActive ? 'alert-success' : 'alert-warning' }} mb-4">
      <div class="d-flex align-items-center">
        <i class="{{ $gradeSubmissionActive ? 'ri-check-circle-line' : 'ri-pause-circle-line' }} me-2 fs-4"></i>
        <div>
          <h6 class="mb-1">Grade Submission Status</h6>
          <p class="mb-0">
            Grade submission is currently <strong>{{ $gradeSubmissionActive ? 'ACTIVE' : 'INACTIVE' }}</strong>.
            @if($gradeSubmissionActive)
              You can submit grades for review.
            @else
              Grade submission is currently disabled by the faculty head.
            @endif
          </p>
          @if($gradeSubmissionActive)
            <div class="mt-2">
              <small class="text-muted">
                Active quarters: 
                @if($quarterSettings['q1_active']) <span class="badge bg-primary me-1">Q1</span> @endif
                @if($quarterSettings['q2_active']) <span class="badge bg-primary me-1">Q2</span> @endif
                @if($quarterSettings['q3_active']) <span class="badge bg-primary me-1">Q3</span> @endif
                @if($quarterSettings['q4_active']) <span class="badge bg-primary me-1">Q4</span> @endif
                @if(!$quarterSettings['q1_active'] && !$quarterSettings['q2_active'] && !$quarterSettings['q3_active'] && !$quarterSettings['q4_active'])
                  <span class="text-muted">No quarters currently active</span>
                @endif
              </small>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-4 col-lg-3">
        <div class="card card-summary card-application h-100">
          <div class="card-body text-center p-3">
            <i class="ri-book-2-line fs-2 mb-2 d-block"></i>
            <div class="small">My Classes</div>
            <h4 class="mb-0">{{ $stats['total_classes'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="card card-summary card-payment h-100">
          <div class="card-body text-center p-3">
            <i class="ri-file-list-3-line fs-2 mb-2 d-block"></i>
            <div class="small">Grade Submissions</div>
            <h4 class="mb-0">{{ $stats['grade_submissions'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>

    <!-- MY CLASS ASSIGNMENTS -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-book-open-line me-2"></i>My Class Assignments
        </h5>
      </div>
      <div class="card-body p-0">
        <!-- Desktop Table View -->
        <div class="table-responsive d-none d-md-block">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Subject</th>
                <th>Grade & Section</th>
                <th>Assignment Type</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($assignments as $assignment)
              <tr>
                <td>
                  <div class="fw-medium">{{ $assignment->subject->subject_name ?? 'Class Adviser' }}</div>
                  @if($assignment->subject)
                    <div class="small text-muted">{{ $assignment->subject->subject_code }}</div>
                  @endif
                </td>
                <td>
                  <div>
                    <span class="badge bg-primary">{{ $assignment->grade_level }} - {{ $assignment->section }}</span>
                    @if($assignment->strand)
                      <br><span class="badge bg-info mt-1">{{ $assignment->strand }}</span>
                      @if($assignment->track)
                        <span class="badge bg-warning ms-1">{{ $assignment->track }}</span>
                      @endif
                    @endif
                  </div>
                </td>
                <td>
                  @if($assignment->assignment_type === 'subject_teacher')
                    <span class="badge bg-info">Subject Teacher</span>
                  @else
                    <span class="badge bg-warning">Class Adviser</span>
                  @endif
                </td>
                <td>
                  @if($assignment->status === 'active')
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-secondary">Inactive</span>
                  @endif
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    @if($assignment->assignment_type === 'subject_teacher')
                      @if($gradeSubmissionActive)
                        <button class="btn btn-outline-success" title="Submit Grades" onclick="submitGrades('{{ $assignment->id }}')">
                          <i class="ri-file-list-3-line"></i>
                        </button>
                      @else
                        <button class="btn btn-outline-secondary" title="Grade Submission Disabled" disabled>
                          <i class="ri-file-list-3-line"></i>
                        </button>
                      @endif
                      <button class="btn btn-outline-primary" title="View Class Details" onclick="viewClassDetails('{{ $assignment->id }}')">
                        <i class="ri-eye-line"></i>
                      </button>
                    @else
                      <button class="btn btn-outline-info" title="Manage Class" onclick="manageClass('{{ $assignment->id }}')">
                        <i class="ri-settings-3-line"></i>
                      </button>
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  <i class="ri-book-open-line display-6 mb-2 d-block"></i>
                  No Class Assignments
                  <div class="small">You haven't been assigned to any classes yet. Contact the faculty head for assignments.</div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Mobile Card View -->
        <div class="d-md-none">
          @forelse($assignments as $assignment)
          <div class="card mb-3 mx-3">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="flex-grow-1">
                  <h6 class="card-title mb-1">{{ $assignment->subject->subject_name ?? 'Class Adviser' }}</h6>
                  @if($assignment->subject)
                    <small class="text-muted">{{ $assignment->subject->subject_code }}</small>
                  @endif
                </div>
                <div class="text-end">
                  @if($assignment->status === 'active')
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-secondary">Inactive</span>
                  @endif
                </div>
              </div>
              
              <div class="mb-2">
                <span class="badge bg-primary me-1">{{ $assignment->grade_level }} - {{ $assignment->section }}</span>
                @if($assignment->strand)
                  <span class="badge bg-info me-1">{{ $assignment->strand }}</span>
                  @if($assignment->track)
                    <span class="badge bg-warning">{{ $assignment->track }}</span>
                  @endif
                @endif
              </div>
              
              <div class="mb-3">
                @if($assignment->assignment_type === 'subject_teacher')
                  <span class="badge bg-info">Subject Teacher</span>
                @else
                  <span class="badge bg-warning">Class Adviser</span>
                @endif
              </div>
              
              <div class="d-flex gap-2">
                @if($assignment->assignment_type === 'subject_teacher')
                  @if($gradeSubmissionActive)
                    <button class="btn btn-outline-success btn-sm flex-fill" onclick="submitGrades('{{ $assignment->id }}')">
                      <i class="ri-file-list-3-line me-1"></i>Submit Grades
                    </button>
                  @else
                    <button class="btn btn-outline-secondary btn-sm flex-fill" disabled>
                      <i class="ri-file-list-3-line me-1"></i>Disabled
                    </button>
                  @endif
                  <button class="btn btn-outline-primary btn-sm flex-fill" onclick="viewClassDetails('{{ $assignment->id }}')">
                    <i class="ri-eye-line me-1"></i>View
                  </button>
                @else
                  <button class="btn btn-outline-info btn-sm flex-fill" onclick="manageClass('{{ $assignment->id }}')">
                    <i class="ri-settings-3-line me-1"></i>Manage Class
                  </button>
                @endif
              </div>
            </div>
          </div>
          @empty
          <div class="text-center text-muted py-5 px-3">
            <i class="ri-book-open-line display-6 mb-2 d-block"></i>
            <h6>No Class Assignments</h6>
            <p class="small mb-0">You haven't been assigned to any classes yet. Contact the faculty head for assignments.</p>
          </div>
          @endforelse
        </div>
      </div>
    </div>

    <!-- QUICK ACTIONS -->
    <h4 class="section-title">Quick Actions</h4>
    <div class="row g-3 mb-4">
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-3">
            <i class="ri-user-star-line fs-1 text-primary mb-3"></i>
            <h6 class="mb-2">View My Advisory</h6>
            <p class="text-muted small mb-3">View students in your advisory class</p>
            <a href="{{ route('teacher.advisory') }}" class="btn btn-outline-primary btn-sm w-100">
              <i class="ri-user-star-line me-2"></i>View Advisory
            </a>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-3">
            <i class="ri-file-list-3-line fs-1 text-success mb-3"></i>
            <h6 class="mb-2">Submit Grades</h6>
            <p class="text-muted small mb-3">Enter and submit student grades for review</p>
            <a href="{{ route('teacher.grades') }}" class="btn btn-outline-success btn-sm w-100">
              <i class="ri-file-list-3-line me-2"></i>Manage Grades
            </a>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-3">
            <i class="ri-heart-pulse-line fs-1 text-warning mb-3"></i>
            <h6 class="mb-2">Recommend for Counseling</h6>
            <p class="text-muted small mb-3">Refer students to guidance counseling services</p>
            <a href="{{ route('teacher.recommend-counseling.form') }}" class="btn btn-outline-warning btn-sm w-100">
              <i class="ri-heart-pulse-line me-2"></i>Recommend Student
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- RECENT GRADE SUBMISSIONS -->
    @if(isset($recentSubmissions) && $recentSubmissions->count() > 0)
    <div class="card mt-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-file-check-line me-2"></i>Recent Grade Submissions
        </h5>
      </div>
      <div class="card-body p-0">
        <!-- Desktop Table View -->
        <div class="table-responsive d-none d-md-block">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Subject</th>
                <th>Class</th>
                <th>Quarter</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($recentSubmissions as $submission)
              <tr>
                <td>{{ $submission->subject->subject_name }}</td>
                <td>{{ $submission->grade_level }} - {{ $submission->section }}</td>
                <td>Q{{ $submission->quarter }}</td>
                <td>
                  @switch($submission->status)
                    @case('submitted')
                      <span class="badge bg-warning">Under Review</span>
                      @break
                    @case('approved')
                      <span class="badge bg-success">Approved</span>
                      @break
                    @case('rejected')
                      <span class="badge bg-danger">Revised</span>
                      @break
                    @case('revision_requested')
                      <span class="badge bg-info">Revision Requested</span>
                      @break
                    @default
                      <span class="badge bg-secondary">{{ ucfirst($submission->status) }}</span>
                  @endswitch
                </td>
                <td>
                  @if($submission->submitted_at)
                    {{ $submission->submitted_at->format('M d, Y') }}
                  @else
                    <span class="text-muted">Not submitted</span>
                  @endif
                </td>
                <td>
                  <a href="{{ route('teacher.grades') }}" class="btn btn-sm btn-outline-primary">
                    <i class="ri-eye-line me-1"></i>View
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Mobile Card View -->
        <div class="d-md-none p-3">
          @foreach($recentSubmissions as $submission)
          <div class="card mb-3">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <h6 class="card-title mb-1">{{ $submission->subject->subject_name }}</h6>
                  <small class="text-muted">{{ $submission->grade_level }} - {{ $submission->section }} • Q{{ $submission->quarter }}</small>
                </div>
                <div>
                  @switch($submission->status)
                    @case('submitted')
                      <span class="badge bg-warning">Under Review</span>
                      @break
                    @case('approved')
                      <span class="badge bg-success">Approved</span>
                      @break
                    @case('rejected')
                      <span class="badge bg-danger">Revised</span>
                      @break
                    @case('revision_requested')
                      <span class="badge bg-info">Revision Requested</span>
                      @break
                    @default
                      <span class="badge bg-secondary">{{ ucfirst($submission->status) }}</span>
                  @endswitch
                </div>
              </div>
              
              <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                  @if($submission->submitted_at)
                    Submitted: {{ $submission->submitted_at->format('M d, Y') }}
                  @else
                    Not submitted
                  @endif
                </small>
                <a href="{{ route('teacher.grades') }}" class="btn btn-sm btn-outline-primary">
                  <i class="ri-eye-line me-1"></i>View
                </a>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif
  </main>
</x-teacher-layout>
