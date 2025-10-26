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

    <!-- RECENT SUBJECT ASSIGNMENTS -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-book-open-line me-2"></i>Recent Subject Teacher Assignments
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
                <th>Strand/Track</th>
                <th>Assigned Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentSubjectAssignments as $assignment)
              <tr>
                <td>{{ $assignment->teacher->user->name }}</td>
                <td>{{ $assignment->subject->subject_name }}</td>
                <td>{{ $assignment->grade_level }} - {{ $assignment->section }}</td>
                <td>
                  @if($assignment->strand)
                    <span class="badge bg-info">{{ $assignment->strand }}</span>
                    @if($assignment->track)
                      <br><span class="badge bg-warning mt-1">{{ $assignment->track }}</span>
                    @endif
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>{{ $assignment->created_at->format('M d, Y') }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted">No recent subject assignments</td>
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
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Teacher</th>
                <th>Class</th>
                <th>Assigned Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentAdviserAssignments as $assignment)
              <tr>
                <td>{{ $assignment->teacher->user->name }}</td>
                <td>{{ $assignment->grade_level }} - {{ $assignment->section }}</td>
                <td>{{ $assignment->created_at->format('M d, Y') }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="3" class="text-center text-muted">No recent adviser assignments</td>
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
@vite('resources/js/faculty-head-dashboard.js')
@endpush
