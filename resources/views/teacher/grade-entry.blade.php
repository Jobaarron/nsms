<x-teacher-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="section-title mb-0">Grade Entry</h1>
        <div class="text-muted">
          <i class="ri-book-open-line me-1"></i>{{ $assignment->subject->subject_name }} - 
          @php
            $className = $assignment->grade_level . ' - ' . $assignment->section;
            if ($assignment->strand) {
                $className = $assignment->grade_level . ' - ' . $assignment->section . ' - ' . $assignment->strand;
                if ($assignment->track) {
                    $className = $assignment->grade_level . ' - ' . $assignment->section . ' - ' . $assignment->strand . ' - ' . $assignment->track;
                }
            }
          @endphp
          {{ $className }}
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
            @php
              $classInfo = $assignment->grade_level . ' - ' . $assignment->section;
              if ($assignment->strand) {
                  $classInfo = $assignment->grade_level . ' - ' . $assignment->section . ' - ' . $assignment->strand;
                  if ($assignment->track) {
                      $classInfo = $assignment->grade_level . ' - ' . $assignment->section . ' - ' . $assignment->strand . ' - ' . $assignment->track;
                  }
              }
            @endphp
            {{ $classInfo }}
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
              <label class="form-label">Quarter</label>
              <div class="form-control-plaintext">
                <span class="badge bg-primary fs-6">{{ request('quarter', '1st') }} Quarter</span>
                <div class="small text-muted mt-1">
                  <i class="ri-lock-line me-1"></i>Set by Faculty Head
                </div>
              </div>
              <!-- Hidden field to maintain the quarter value -->
              <input type="hidden" name="quarter" value="{{ request('quarter', '1st') }}">
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
                      <div class="small text-muted">
                        @php
                          $studentClass = $student->grade_level . ' - ' . $student->section;
                          if ($student->strand) {
                              $studentClass = $student->grade_level . ' - ' . $student->section . ' - ' . $student->strand;
                              if ($student->track) {
                                  $studentClass = $student->grade_level . ' - ' . $student->section . ' - ' . $student->strand . ' - ' . $student->track;
                              }
                          }
                        @endphp
                        {{ $studentClass }}
                      </div>
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

            <!-- EXCEL UPLOAD SECTION -->
            @if($submission->canEdit())
            <div class="card mt-4">
              <div class="card-header">
                <h6 class="mb-0">
                  <i class="ri-file-excel-line me-2"></i>Upload Grades from Excel/CSV
                </h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-8">
                    <input type="file" class="form-control" id="gradesFile" accept=".csv,.xlsx,.xls">
                    <div class="form-text">
                      <strong>Required columns:</strong> student_id (NS-25XXX), last_name, first_name, middle_name (optional), grade, remarks (optional)
                    </div>
                  </div>
                  <div class="col-md-4">
                    <button type="button" class="btn btn-outline-primary" onclick="uploadGradesFile()">
                      <i class="ri-upload-line me-2"></i>Upload Grades
                    </button>
                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="downloadTemplate()">
                      <i class="ri-download-line me-2"></i>Template
                    </button>
                  </div>
                </div>
                <div id="uploadProgress" class="mt-3" style="display: none;">
                  <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                  </div>
                  <small class="text-muted">Uploading and processing file...</small>
                </div>
              </div>
            </div>
            @endif

            <!-- SUBMISSION ACTIONS -->
            <div class="row mt-4">
              <div class="col-12">
                <div class="d-flex gap-2">
                  <button type="submit" name="action" value="save_draft" class="btn btn-outline-warning">
                    <i class="ri-time-line me-2"></i>Save as Pending Review
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
              <p class="text-muted">No active students found for 
                @php
                  $noStudentsClass = $assignment->grade_level . ' - ' . $assignment->section;
                  if ($assignment->strand) {
                      $noStudentsClass = $assignment->grade_level . ' - ' . $assignment->section . ' - ' . $assignment->strand;
                      if ($assignment->track) {
                          $noStudentsClass = $assignment->grade_level . ' - ' . $assignment->section . ' - ' . $assignment->strand . ' - ' . $assignment->track;
                      }
                  }
                @endphp
                {{ $noStudentsClass }}
              </p>
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

// Quarter loading function removed - quarter is now controlled by faculty head

// Excel/CSV Upload Functions
function uploadGradesFile() {
    const fileInput = document.getElementById('gradesFile');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Please select a file to upload');
        return;
    }
    
    const formData = new FormData();
    formData.append('grades_file', file);
    formData.append('submission_id', {{ $submission->id }});
    
    // Show progress
    document.getElementById('uploadProgress').style.display = 'block';
    
    fetch('{{ route("teacher.grades.upload") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('uploadProgress').style.display = 'none';
        
        if (data.success) {
            alert(`Success! Uploaded ${data.processed} grades out of ${data.total_expected} students`);
            location.reload(); // Reload to show updated grades
        } else {
            let errorMsg = data.message;
            if (data.errors) {
                errorMsg += '\n\nErrors:\n' + data.errors.join('\n');
            }
            alert(errorMsg);
        }
    })
    .catch(error => {
        document.getElementById('uploadProgress').style.display = 'none';
        alert('Upload failed: ' + error.message);
    });
}

function downloadTemplate() {
    // Create CSV template with sample data using NS-25XXX format
    const csvContent = `student_id,last_name,first_name,middle_name,grade,remarks
NS-25001,Dela Cruz,Juan,Santos,85.50,Good performance
NS-25002,Garcia,Maria,Lopez,92.00,Excellent work
NS-25003,Reyes,Pedro,,78.25,Needs improvement
NS-25004,Santos,Ana,Cruz,95.75,Outstanding
NS-25005,Mendoza,Carlos,,88.00,`;
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'grade_upload_template.csv';
    a.click();
    window.URL.revokeObjectURL(url);
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
