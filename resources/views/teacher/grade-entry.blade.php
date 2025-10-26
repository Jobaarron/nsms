<x-teacher-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="section-title mb-0">Grade Entry</h1>
        <div class="text-muted">
          <i class="ri-book-open-line me-1"></i>{{ $assignment->subject->subject_name }} - {{ $assignment->grade_level }} {{ $assignment->section }}
        </div>
      </div>
      <a href="{{ route('teacher.grades') }}" class="btn btn-outline-secondary">
        <i class="ri-arrow-left-line me-2"></i>Back to Grades
      </a>
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

    <!-- ASSIGNMENT INFO -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <strong>Subject:</strong><br>
            {{ $assignment->subject->subject_name }}
            @if($assignment->subject->subject_code)
              <small class="text-muted">({{ $assignment->subject->subject_code }})</small>
            @endif
          </div>
          <div class="col-md-3">
            <strong>Class:</strong><br>
            {{ $assignment->grade_level }} - {{ $assignment->section }}
          </div>
          <div class="col-md-3">
            <strong>Academic Year:</strong><br>
            {{ $assignment->academic_year }}
          </div>
          <div class="col-md-3">
            <strong>Total Students:</strong><br>
            <span class="badge bg-primary">{{ $students->count() }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- GRADE ENTRY FORM -->
    <form action="{{ route('teacher.grades.submit.store', $assignment) }}" method="POST" id="gradeEntryForm">
      @csrf
      
      <!-- QUARTER SELECTION -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="ri-calendar-check-line me-2"></i>Quarter Selection
          </h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <label class="form-label">Select Quarter</label>
              <select name="quarter" class="form-select" required onchange="loadGradesForQuarter(this.value)">
                <option value="1st" {{ request('quarter', '1st') === '1st' ? 'selected' : '' }}>1st Quarter</option>
                <option value="2nd" {{ request('quarter') === '2nd' ? 'selected' : '' }}>2nd Quarter</option>
                <option value="3rd" {{ request('quarter') === '3rd' ? 'selected' : '' }}>3rd Quarter</option>
                <option value="4th" {{ request('quarter') === '4th' ? 'selected' : '' }}>4th Quarter</option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label">Submission Notes (Optional)</label>
              <input type="text" name="notes" class="form-control" placeholder="Add any notes about this grade submission" value="{{ $submission->submission_notes }}">
            </div>
          </div>
        </div>
      </div>

      <!-- STUDENTS GRADES -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="ri-user-line me-2"></i>Student Grades
          </h5>
        </div>
        <div class="card-body">
          @if($students->count() > 0)
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">Student Name</th>
                    <th style="width: 20%;">Student ID</th>
                    <th style="width: 15%;">Grade</th>
                    <th style="width: 20%;">Remarks</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($students as $index => $student)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                      <div class="fw-medium">{{ $student->full_name }}</div>
                      <div class="small text-muted">{{ $student->grade_level }} - {{ $student->section }}</div>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark">{{ $student->student_id }}</span>
                    </td>
                    <td>
                      <input type="hidden" name="grades[{{ $index }}][student_id]" value="{{ $student->id }}">
                      <input type="number" 
                             name="grades[{{ $index }}][grade]" 
                             class="form-control grade-input" 
                             min="0" 
                             max="100" 
                             step="0.01"
                             placeholder="0.00"
                             value="{{ $existingGrades[$student->id]['grade'] ?? '' }}"
                             onchange="validateGrade(this)">
                    </td>
                    <td>
                      <input type="text" 
                             name="grades[{{ $index }}][remarks]" 
                             class="form-control" 
                             placeholder="Optional remarks"
                             value="{{ $existingGrades[$student->id]['remarks'] ?? '' }}">
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <!-- SUBMISSION ACTIONS -->
            <div class="row mt-4">
              <div class="col-12">
                <div class="d-flex gap-2">
                  <button type="submit" name="action" value="save_draft" class="btn btn-outline-primary">
                    <i class="ri-save-line me-2"></i>Save as Draft
                  </button>
                  <button type="submit" name="action" value="submit_for_review" class="btn btn-success" onclick="return confirmSubmission()">
                    <i class="ri-send-plane-line me-2"></i>Submit for Review
                  </button>
                  <div class="ms-auto">
                    <small class="text-muted">
                      Status: <span class="badge bg-{{ $submission->status === 'draft' ? 'warning' : ($submission->status === 'submitted' ? 'info' : 'success') }}">
                        {{ ucfirst($submission->status) }}
                      </span>
                    </small>
                  </div>
                </div>
              </div>
            </div>
          @else
            <div class="text-center py-5">
              <i class="ri-user-line display-4 text-muted mb-3"></i>
              <h5>No Students Found</h5>
              <p class="text-muted">No active students found for {{ $assignment->grade_level }} - {{ $assignment->section }}</p>
            </div>
          @endif
        </div>
      </div>
    </form>
  </main>
</x-teacher-layout>

<script>
function validateGrade(input) {
    const value = parseFloat(input.value);
    if (value < 0 || value > 100) {
        alert('Grade must be between 0 and 100');
        input.focus();
        return false;
    }
    
    // Add visual feedback for passing/failing grades
    if (value >= 75) {
        input.classList.remove('border-danger');
        input.classList.add('border-success');
    } else if (value > 0) {
        input.classList.remove('border-success');
        input.classList.add('border-danger');
    } else {
        input.classList.remove('border-success', 'border-danger');
    }
}

function confirmSubmission() {
    const filledGrades = document.querySelectorAll('.grade-input').length;
    const emptyGrades = Array.from(document.querySelectorAll('.grade-input')).filter(input => !input.value).length;
    
    if (emptyGrades > 0) {
        return confirm(`You have ${emptyGrades} empty grades out of ${filledGrades} students. Are you sure you want to submit for review?`);
    }
    
    return confirm('Are you sure you want to submit these grades for faculty head review? You won\'t be able to edit them once submitted.');
}

function loadGradesForQuarter(quarter) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('quarter', quarter);
    window.location.href = currentUrl.toString();
}

// Auto-save functionality (optional)
let autoSaveTimer;
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('grade-input')) {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            // Could implement auto-save here
            console.log('Auto-save triggered');
        }, 2000);
    }
});
</script>
