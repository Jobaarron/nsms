<x-teacher-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Teacher Dashboard</h1>
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

    <!-- DASHBOARD STATISTICS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-application h-100">
          <div class="card-body text-center">
            <i class="ri-book-2-line display-6 mb-2"></i>
            <div>My Classes</div>
            <h3>{{ $stats['total_classes'] ?? 0 }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-payment h-100">
          <div class="card-body text-center">
            <i class="ri-file-list-3-line display-6 mb-2"></i>
            <div>Grade Submissions</div>
            <h3>{{ $stats['grade_submissions'] ?? 0 }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- MY CLASS ASSIGNMENTS -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-book-open-line me-2"></i>My Class Assignments
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
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
      </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-flashlight-line me-2"></i>Quick Actions
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center">
                <i class="ri-user-star-line display-4 text-primary mb-3"></i>
                <h6>View My Advisory</h6>
                <p class="text-muted small">View students in your advisory class</p>
                <a href="{{ route('teacher.advisory') }}" class="btn btn-outline-primary">
                  <i class="ri-user-star-line me-2"></i>View Advisory
                </a>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card h-100 border">
              <div class="card-body text-center">
                <i class="ri-file-list-3-line display-4 text-success mb-3"></i>
                <h6>Submit Grades</h6>
                <p class="text-muted small">Enter and submit student grades for review</p>
                <a href="{{ route('teacher.grades') }}" class="btn btn-outline-success">
                  <i class="ri-file-list-3-line me-2"></i>Manage Grades
                </a>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card h-100 border">
              <div class="card-body text-center">
                <i class="ri-heart-pulse-line display-4 text-warning mb-3"></i>
                <h6>Recommend for Counseling</h6>
                <p class="text-muted small">Refer students to guidance counseling services</p>
                <a href="{{ route('teacher.recommend-counseling.form') }}" class="btn btn-outline-warning">
                  <i class="ri-heart-pulse-line me-2"></i>Recommend Student
                </a>
              </div>
            </div>
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
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
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
                <td>{{ $submission->quarter }}</td>
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
      </div>
    </div>
    @endif
  </main>
</x-teacher-layout>
