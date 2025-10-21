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
        <div class="card card-summary card-status h-100">
          <div class="card-body text-center">
            <i class="ri-group-line display-6 mb-2"></i>
            <div>Total Students</div>
            <h3>{{ $stats['total_students'] ?? 0 }}</h3>
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
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-schedule h-100">
          <div class="card-body text-center">
            <i class="ri-time-line display-6 mb-2"></i>
            <div>Weekly Hours</div>
            <h3>{{ $stats['weekly_hours'] ?? 0 }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- MY CLASS ASSIGNMENTS -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-book-line me-2"></i>My Class Assignments
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Subject</th>
                <th>Grade & Section</th>
                <th>Assignment Type</th>
                <th>Students</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($assignments ?? [] as $assignment)
              <tr>
                <td>
                  <strong>{{ $assignment->subject->subject_name ?? 'N/A' }}</strong>
                  <br><small class="text-muted">{{ $assignment->subject->subject_code ?? '' }}</small>
                </td>
                <td>{{ $assignment->grade_level ?? 'N/A' }} - {{ $assignment->section ?? 'N/A' }}</td>
                <td>
                  @if($assignment->assignment_type === 'class_adviser')
                    <span class="badge bg-primary">Class Adviser</span>
                  @else
                    <span class="badge bg-secondary">Subject Teacher</span>
                  @endif
                </td>
                <td>{{ $assignment->student_count ?? 0 }}</td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('teacher.schedule') }}" class="btn btn-outline-primary">
                      <i class="ri-calendar-line me-1"></i>Schedule
                    </a>
                    <a href="{{ route('teacher.grades') }}" class="btn btn-outline-secondary">
                      <i class="ri-file-list-3-line me-1"></i>Grades
                    </a>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  <i class="ri-book-line display-1 text-muted mb-3"></i>
                  <h5>No Class Assignments</h5>
                  <p class="text-muted">You haven't been assigned to any classes yet.</p>
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
            <div class="card h-100 border">
              <div class="card-body text-center">
                <i class="ri-calendar-2-line display-4 text-primary mb-3"></i>
                <h6>View My Schedule</h6>
                <p class="text-muted small">Check your weekly teaching schedule and class times</p>
                <a href="{{ route('teacher.schedule') }}" class="btn btn-outline-primary">
                  <i class="ri-calendar-2-line me-2"></i>View Schedule
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
                    @case('draft')
                      <span class="badge bg-secondary">Draft</span>
                      @break
                    @case('submitted')
                      <span class="badge bg-warning">Under Review</span>
                      @break
                    @case('approved')
                      <span class="badge bg-success">Approved</span>
                      @break
                    @case('rejected')
                      <span class="badge bg-danger">Rejected</span>
                      @break
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

@push('scripts')
<script src="{{ asset('js/teacher-dashboard.js') }}"></script>
@endpush