<x-teacher-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Grade Submissions</h1>
      <div class="text-muted">
        <i class="ri-file-text-line me-1"></i>{{ $currentAcademicYear }}
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

    <!-- SUBMISSION STATISTICS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-application h-100">
          <div class="card-body text-center">
            <i class="ri-draft-line display-6 mb-2"></i>
            <div>Draft</div>
            <h3>{{ $submissionsByStatus->get('draft', collect())->count() }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-status h-100">
          <div class="card-body text-center">
            <i class="ri-send-plane-line display-6 mb-2"></i>
            <div>Submitted</div>
            <h3>{{ $submissionsByStatus->get('submitted', collect())->count() }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-payment h-100">
          <div class="card-body text-center">
            <i class="ri-checkbox-circle-line display-6 mb-2"></i>
            <div>Approved</div>
            <h3>{{ $submissionsByStatus->get('approved', collect())->count() }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-schedule h-100">
          <div class="card-body text-center">
            <i class="ri-close-circle-line display-6 mb-2"></i>
            <div>Rejected</div>
            <h3>{{ $submissionsByStatus->get('rejected', collect())->count() }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- CREATE NEW SUBMISSION -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-add-circle-line me-2"></i>Submit New Grades
        </h5>
      </div>
      <div class="card-body">
        <form action="{{ route('teacher.grades.create') }}" method="GET" class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Subject</label>
            <select name="subject_id" class="form-select" required>
              <option value="">Select Subject</option>
              @foreach($assignments as $assignment)
                <option value="{{ $assignment->subject_id }}" 
                        data-grade="{{ $assignment->grade_level }}" 
                        data-section="{{ $assignment->section }}">
                  {{ $assignment->subject->subject_name }} ({{ $assignment->grade_level }} - {{ $assignment->section }})
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Quarter</label>
            <select name="quarter" class="form-select" required>
              <option value="">Select Quarter</option>
              <option value="1st">1st Quarter</option>
              <option value="2nd">2nd Quarter</option>
              <option value="3rd">3rd Quarter</option>
              <option value="4th">4th Quarter</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button type="submit" class="btn btn-primary d-block">
              <i class="ri-add-line me-2"></i>Create Submission
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- GRADE SUBMISSIONS LIST -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-list-check me-2"></i>My Grade Submissions
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
                <th>Progress</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($submissions as $submission)
              <tr>
                <td>
                  <strong>{{ $submission->subject->subject_name }}</strong>
                  <br><small class="text-muted">{{ $submission->subject->subject_code }}</small>
                </td>
                <td>{{ $submission->grade_level }} - {{ $submission->section }}</td>
                <td>{{ $submission->quarter }}</td>
                <td>
                  <div class="progress" style="height: 20px;">
                    <div class="progress-bar" role="progressbar" 
                         style="width: {{ $submission->completion_percentage }}%"
                         aria-valuenow="{{ $submission->completion_percentage }}" 
                         aria-valuemin="0" aria-valuemax="100">
                      {{ $submission->completion_percentage }}%
                    </div>
                  </div>
                  <small class="text-muted">{{ $submission->grades_entered }}/{{ $submission->total_students }} students</small>
                </td>
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
                    @case('revision_requested')
                      <span class="badge bg-info">Revision Requested</span>
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
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('teacher.grades.show', $submission) }}" class="btn btn-outline-primary">
                      <i class="ri-eye-line"></i>
                    </a>
                    @if($submission->canEdit())
                      <a href="{{ route('teacher.grades.create', [
                        'subject_id' => $submission->subject_id,
                        'grade_level' => $submission->grade_level,
                        'section' => $submission->section,
                        'quarter' => $submission->quarter
                      ]) }}" class="btn btn-outline-secondary">
                        <i class="ri-edit-line"></i>
                      </a>
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No grade submissions found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</x-teacher-layout>

@push('scripts')
<script src="{{ asset('js/teacher-grades.js') }}"></script>
@endpush
