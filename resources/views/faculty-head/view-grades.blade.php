<x-faculty-head-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Review & Approve Grades</h1>
      <div class="text-muted">
        <i class="ri-file-check-line me-1"></i>Grade Submissions Management
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if(!isset($submission))
    <!-- SUBMISSION STATISTICS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-status h-100">
          <div class="card-body text-center">
            <i class="ri-time-line display-6 mb-2 text-warning"></i>
            <div>Pending Review</div>
            <h3>{{ $stats['pending'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-payment h-100">
          <div class="card-body text-center">
            <i class="ri-checkbox-circle-line display-6 mb-2 text-success"></i>
            <div>Approved</div>
            <h3>{{ $stats['approved'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-schedule h-100">
          <div class="card-body text-center">
            <i class="ri-close-circle-line display-6 mb-2 text-danger"></i>
            <div>Rejected</div>
            <h3>{{ $stats['rejected'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-application h-100">
          <div class="card-body text-center">
            <i class="ri-file-edit-line display-6 mb-2 text-secondary"></i>
            <div>Drafts</div>
            <h3>{{ $stats['draft'] }}</h3>
          </div>
        </div>
      </div>
    </div>
    @endif

    @if(isset($submission))
    <!-- DETAILED SUBMISSION REVIEW -->
    <div class="card mb-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="ri-file-check-line me-2"></i>Grade Submission Review
          </h5>
          <a href="{{ route('faculty-head.view-grades') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-2"></i>Back to List
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <strong>Teacher:</strong><br>
            {{ $submission->teacher->user->name }}
          </div>
          <div class="col-md-3">
            <strong>Subject:</strong><br>
            {{ $submission->subject->subject_name }}
          </div>
          <div class="col-md-3">
            <strong>Class:</strong><br>
            {{ $submission->grade_level }} - {{ $submission->section }}
            @php
              $assignment = \App\Models\FacultyAssignment::where('teacher_id', $submission->teacher_id)
                ->where('subject_id', $submission->subject_id)
                ->where('grade_level', $submission->grade_level)
                ->where('section', $submission->section)
                ->where('academic_year', $submission->academic_year)
                ->first();
            @endphp
            @if($assignment && $assignment->strand)
              <br><span class="badge bg-info">{{ $assignment->strand }}</span>
              @if($assignment->track)
                <span class="badge bg-warning ms-1">{{ $assignment->track }}</span>
              @endif
            @endif
          </div>
          <div class="col-md-3">
            <strong>Quarter:</strong><br>
            {{ $submission->quarter }}
            <br><strong>Submitted:</strong><br>
            {{ $submission->submitted_at->format('M d, Y g:i A') }}
          </div>
        </div>
        @if($submission->submission_notes)
          <div class="mt-3">
            <strong>Teacher Notes:</strong>
            <p class="text-muted mb-0">{{ $submission->submission_notes }}</p>
          </div>
        @endif
      </div>
    </div>

    <!-- GRADES TABLE -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-list-check me-2"></i>Student Grades ({{ count($submission->grades_data) }} students)
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Student ID</th>
                <th>Grade</th>
                <th>Remarks</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($submission->grades_data as $index => $gradeData)
                @php
                  $student = $students->find($gradeData['student_id']);
                  $grade = floatval($gradeData['grade']);
                  $isPassing = $grade >= 75;
                @endphp
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>
                    @if($student)
                      <div class="fw-medium">{{ $student->full_name }}</div>
                      <div class="small text-muted">{{ $student->grade_level }} - {{ $student->section }}</div>
                    @else
                      <span class="text-danger">Student not found</span>
                    @endif
                  </td>
                  <td>
                    @if($student)
                      <span class="badge bg-light text-dark">{{ $student->student_id }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>
                    <span class="fw-bold {{ $isPassing ? 'text-success' : 'text-danger' }}">
                      {{ number_format($grade, 2) }}
                    </span>
                  </td>
                  <td>
                    {{ $gradeData['remarks'] ?? '-' }}
                  </td>
                  <td>
                    @if($isPassing)
                      <span class="badge bg-success">Passed</span>
                    @else
                      <span class="badge bg-danger">Failed</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- APPROVAL ACTIONS -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-checkbox-circle-line me-2"></i>Review Actions
        </h5>
      </div>
      <div class="card-body">
        <form action="{{ route('faculty-head.approve-grades.approve', $submission) }}" method="POST">
          @csrf
          <div class="row">
            <div class="col-md-8">
              <label class="form-label">Review Notes (Optional)</label>
              <textarea name="review_notes" class="form-control" rows="3" placeholder="Add any comments about this grade submission..."></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label">Action</label>
              <div class="d-grid gap-2">
                <button type="submit" name="action" value="approve" class="btn btn-success" onclick="return confirm('Are you sure you want to approve these grades? Students will be able to view them.')">
                  <i class="ri-check-circle-line me-2"></i>Approve Grades
                </button>
                <button type="submit" name="action" value="request_revision" class="btn btn-warning" onclick="return confirm('Request revision? Teacher will be able to edit and resubmit.')">
                  <i class="ri-edit-line me-2"></i>Request Revision
                </button>
                <button type="submit" name="action" value="reject" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject these grades? Please provide review notes.')">
                  <i class="ri-close-circle-line me-2"></i>Reject Grades
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    @else
    <!-- SUBMISSIONS LIST -->

    <!-- FILTER TABS -->
    <div class="card mb-4">
      <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="submissionTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
              All Submissions
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="submitted-tab" data-bs-toggle="tab" data-bs-target="#submitted" type="button" role="tab">
              Submitted
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
              Approved
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
              Rejected
            </button>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content" id="submissionTabsContent">
          <!-- ALL SUBMISSIONS -->
          <div class="tab-pane fade show active" id="all" role="tabpanel">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Teacher</th>
                    <th>Subject</th>
                    <th>Class</th>
                    <th>Quarter</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pendingSubmissions as $submission)
                  <tr>
                    <td>{{ $submission->teacher->user->name }}</td>
                    <td>{{ $submission->subject->subject_name }}</td>
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
                      @switch($submission->status)
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
                      <a href="{{ route('faculty-head.approve-grades', ['submission' => $submission->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="ri-eye-line me-1"></i>Review
                      </a>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="8" class="text-center text-muted">No grade submissions found</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          
          <!-- Other tab contents would be similar with filtered data -->
        </div>
      </div>
    </div>
    @endif
  </main>
</x-faculty-head-layout>

@push('scripts')
@vite('resources/js/faculty-head-view-grades.js')
@endpush
