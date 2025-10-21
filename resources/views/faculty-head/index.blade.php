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
            <i class="ri-user-settings-line display-6 mb-2"></i>
            <div>Active Assignments</div>
            <h3>{{ $stats['total_assignments'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-payment h-100">
          <div class="card-body text-center">
            <i class="ri-file-check-line display-6 mb-2"></i>
            <div>Pending Reviews</div>
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
                <td>{{ $submission->teacher->name }}</td>
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

    <!-- RECENT ASSIGNMENTS -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-user-settings-line me-2"></i>Recent Teacher Assignments
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
                <th>Type</th>
                <th>Assigned Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentAssignments as $assignment)
              <tr>
                <td>{{ $assignment->teacher->name }}</td>
                <td>{{ $assignment->subject->subject_name ?? 'Class Adviser' }}</td>
                <td>{{ $assignment->grade_level }} - {{ $assignment->section }}</td>
                <td>
                  @if($assignment->isClassAdviser())
                    <span class="badge bg-primary">Class Adviser</span>
                  @else
                    <span class="badge bg-secondary">Subject Teacher</span>
                  @endif
                </td>
                <td>{{ $assignment->created_at->format('M d, Y') }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted">No recent assignments</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</x-faculty-head-layout>

@push('scripts')
<script src="{{ asset('js/faculty-head-dashboard.js') }}"></script>
@endpush
