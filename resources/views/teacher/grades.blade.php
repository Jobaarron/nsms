<x-teacher-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Grade Submissions</h1>
      <div class="d-flex align-items-center gap-3">
        <div class="text-muted">
          <i class="ri-file-text-line me-1"></i>{{ $currentAcademicYear }}
        </div>
        <div class="d-flex align-items-center">
          <span class="text-muted me-2">Submission Status:</span>
          <span class="grade-submission-status badge bg-secondary">Checking...</span>
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
                @if($assignment->subject_id && $assignment->subject)
                <option value="{{ $assignment->subject_id }}" 
                        data-grade="{{ $assignment->grade_level }}" 
                        data-section="{{ $assignment->section }}">
                  {{ $assignment->subject->subject_name }} ({{ $assignment->grade_level }} - {{ $assignment->section }})
                </option>
                @endif
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
                  <div class="fw-medium">{{ $submission->subject->subject_name }}</div>
                  @if($submission->subject->subject_code)
                    <div class="small text-muted">{{ $submission->subject->subject_code }}</div>
                  @endif
                </td>
                <td>
                  <div>{{ $submission->grade_level }} - {{ $submission->section }}</div>
                  @php
                    $assignment = \App\Models\FacultyAssignment::where('teacher_id', $submission->teacher_id)
                      ->where('subject_id', $submission->subject_id)
                      ->where('grade_level', $submission->grade_level)
                      ->where('section', $submission->section)
                      ->where('academic_year', $submission->academic_year)
                      ->first();
                  @endphp
                  @if($assignment && $assignment->strand)
                    <div class="mt-1">
                      <span class="badge bg-info">{{ $assignment->strand }}</span>
                      @if($assignment->track)
                        <span class="badge bg-warning ms-1">{{ $assignment->track }}</span>
                      @endif
                    </div>
                  @endif
                </td>
                <td>{{ $submission->quarter }}</td>
                <td>{{ $submission->total_students }}</td>
                <td>
                  @php
                    $percentage = $submission->total_students > 0 ? ($submission->grades_entered / $submission->total_students) * 100 : 0;
                  @endphp
                  <div class="progress" style="height: 20px;">
                    <div class="progress-bar {{ $percentage == 100 ? 'bg-success' : 'bg-primary' }}" 
                         role="progressbar" 
                         style="width: {{ $percentage }}%"
                         aria-valuenow="{{ $percentage }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                      {{ number_format($percentage, 0) }}%
                    </div>
                  </div>
                  <small class="text-muted">{{ $submission->grades_entered }}/{{ $submission->total_students }} entered</small>
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
                  @if($submission->review_notes)
                    <div class="small text-muted mt-1" title="{{ $submission->review_notes }}">
                      <i class="ri-message-line"></i> Has feedback
                    </div>
                  @endif
                </td>
                <td>
                  @if($submission->submitted_at)
                    <div class="small">{{ $submission->submitted_at->format('M d, Y') }}</div>
                    <div class="small text-muted">{{ $submission->submitted_at->format('g:i A') }}</div>
                  @else
                    <span class="text-muted">Not submitted</span>
                  @endif
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    @if(in_array($submission->status, ['draft', 'revision_requested']))
                      <a href="{{ route('teacher.grades.submit', $submission->getFacultyAssignment()) }}?quarter={{ $submission->quarter }}" 
                         class="btn btn-outline-primary" title="Edit Grades">
                        <i class="ri-edit-line"></i>
                      </a>
                    @else
                      <button class="btn btn-outline-secondary" title="View Only" disabled>
                        <i class="ri-eye-line"></i>
                      </button>
                    @endif
                    
                    @if($submission->review_notes)
                      <button class="btn btn-outline-info" title="View Feedback" onclick="showFeedback('{{ addslashes($submission->review_notes) }}')">
                        <i class="ri-message-line"></i>
                      </button>
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  <i class="ri-file-list-line display-6 mb-2 d-block"></i>
                  No grade submissions yet
                  <div class="small">Start by clicking "Submit Grades" on your assignments above.</div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</x-teacher-layout>

<script>
function showFeedback(notes) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-message-line me-2"></i>Faculty Head Feedback
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        <strong>Review Notes:</strong>
                    </div>
                    <p class="mb-0">${notes}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
    
    // Clean up modal when hidden
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}
</script>

@push('scripts')
@vite('resources/js/grade-submission-checker.js')
<script src="{{ asset('js/teacher-grades.js') }}"></script>
@endpush
