<x-teacher-layout>
  @push('scripts')
  @vite('resources/js/grade-submission-checker.js')
  @vite('resources/js/teacher-grades.js')
  @endpush

  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Grade Submissions</h1>
      <div class="d-flex align-items-center gap-3">
        <div class="text-muted">
          <i class="ri-file-text-line me-1"></i>{{ $currentAcademicYear }}
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
            <i class="ri-time-line display-6 mb-2 text-warning"></i>
            <div>Pending Review</div>
            <h3>{{ $stats['pending'] ?? 0 }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-status h-100">
          <div class="card-body text-center">
            <i class="ri-send-plane-line display-6 mb-2"></i>
            <div>Submitted</div>
            <h3>{{ $stats['submitted'] ?? 0 }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-payment h-100">
          <div class="card-body text-center">
            <i class="ri-checkbox-circle-line display-6 mb-2"></i>
            <div>Approved</div>
            <h3>{{ $stats['approved'] ?? 0 }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-schedule h-100">
          <div class="card-body text-center">
            <i class="ri-edit-circle-line display-6 mb-2 text-warning"></i>
            <div>Revised</div>
            <h3>{{ $stats['revised'] ?? 0 }}</h3>
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
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Subject</label>
            <select class="form-select" required id="assignmentSelect">
              <option value="">Select Subject</option>
              @foreach($assignments as $assignment)
                @if($assignment->subject_id && $assignment->subject)
                <option value="{{ $assignment->id }}">
                  {{ $assignment->subject->subject_name }} ({{ $assignment->grade_level }} - {{ $assignment->section }})
                </option>
                @endif
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button type="button" class="btn btn-primary d-block" onclick="handleGradeSubmission()">
              <i class="ri-add-line me-2"></i>Submit Grades
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- APPROVED GRADES AWAITING UPLOAD -->
    @php
      $approvedSubmissions = $submissions->where('status', 'approved');
    @endphp
    @if($approvedSubmissions->count() > 0)
    <div class="card mb-4">
      <div class="card-header bg-success text-white">
        <h5 class="mb-0">
          <i class="ri-upload-cloud-line me-2"></i>Approved Grades - Ready to Upload
        </h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3">
          <i class="ri-information-line me-1"></i>
          These grades have been approved by the faculty head. Click "Upload" to make them visible to students.
        </p>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Subject</th>
                <th>Class</th>
                <th>Quarter</th>
                <th>Students</th>
                <th>Approved</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($approvedSubmissions as $submission)
              <tr>
                <td>{{ $submission->subject->subject_name }}</td>
                <td>{{ $submission->grade_level }} - {{ $submission->section }}</td>
                <td>{{ $submission->quarter }}</td>
                <td>{{ $submission->total_students }}</td>
                <td>{{ $submission->reviewed_at->format('M d, Y') }}</td>
                <td>
                  <button class="btn btn-success btn-sm" onclick="uploadGrades({{ $submission->id }})">
                    <i class="ri-upload-line me-1"></i>Upload
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif

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
                    @case('finalized')
                      <span class="badge bg-secondary">Uploaded</span>
                      @break
                    @default
                      <span class="badge bg-secondary">{{ ucfirst($submission->status) }}</span>
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

  @push('scripts')
  <script>
      // Mark grades alert as viewed when teacher visits this page
      document.addEventListener('DOMContentLoaded', function() {
          fetch('{{ route("teacher.mark-alert-viewed") }}', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                  alert_type: 'grades'
              })
          }).catch(error => console.error('Error marking grades alert as viewed:', error));
      });
  </script>
  @endpush
</x-teacher-layout>


