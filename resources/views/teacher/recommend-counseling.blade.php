<x-teacher-layout>
  <!-- MAIN CONTENT -->
  <style>
    body, .card, .card-header, .card-body {
      background-color: #fff !important;
    }
    .card {
      border: 1px solid #198754;
      box-shadow: 0 2px 8px rgba(25,135,84,0.08);
      max-width: 100%;
      margin: 0 auto 2rem auto;
      width: 100%;
      padding-left: 0;
      padding-right: 0;
    }
    .card-header {
      background: #fff;
      color: #000;
      border-bottom: 1px solid #198754;
    }
    .section-title, h5, h6, strong {
      color: #000 !important;
    }
    .form-check-input:checked {
      background-color: #198754;
      border-color: #198754;
    }
    .form-check-label {
      color: #000;
    }
    .btn-warning {
      background-color: #198754;
      border-color: #198754;
      color: #fff;
    }
    .btn-warning:hover {
      background-color: #157347;
      border-color: #157347;
      color: #fff;
    }
    .btn-outline-secondary {
      border-color: #198754;
      color: #000;
      background-color: #fff;
    }
    .btn-outline-secondary:hover {
      background-color: #198754;
      color: #fff;
    }
    .btn-outline-success {
      border-color: #198754;
      color: #000;
      background-color: #fff;
    }
    .btn-outline-success:hover {
      background-color: #198754;
      color: #fff;
    }
    .btn-outline-info {
      border-color: #0dcaf0;
      color: #0dcaf0;
      background-color: #fff;
    }
    .btn-outline-info:hover {
      background-color: #0dcaf0;
      color: #fff;
    }
    .btn-outline-danger {
      border-color: #dc3545;
      color: #dc3545;
      background-color: #fff;
    }
    .btn-outline-danger:hover {
      background-color: #dc3545;
      color: #fff;
    }
    .badge {
      background-color: #198754;
      color: #fff;
      font-weight: 500;
      border-radius: 6px;
      padding: 0.35em 0.7em;
      font-size: 0.95em;
    }
    .alert-info {
      background-color: #e6ffed;
      color: #000;
      border-color: #198754;
    }
    #referral-checklist {
      background: #fff;
      border: 1px solid #198754;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    #incident_description {
  border: 1px solid #198754;
  background: #fff;
  color: #000;
    }
  </style>
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Recommend Student for Counseling</h1>
      <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary">
  <i class="ri-arrow-left-line me-2"></i>Back to Dashboard
      </a>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <div class="row justify-content-center">
  <div>
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Counseling Recommendation Form</h5>
          </div>
          <div class="card-body">
            <form action="{{ route('teacher.recommend-counseling') }}" method="POST">
              @csrf

              <div class="mb-3">
                <label for="studentSearch" class="form-label">Search Student <span class="text-danger">*</span></label>
                <div class="position-relative">
                  <input type="text" class="form-control @error('student_id') is-invalid @enderror" id="studentSearch" name="studentSearch" placeholder="Type student name or ID..." autocomplete="off" required>
                  <input type="hidden" id="student_id" name="student_id" value="{{ old('student_id') }}">
                  <div id="studentSuggestions" class="suggestions-list" style="display: none; position: absolute; z-index: 10; width: 100%; background: #fff; border: 1px solid #198754; border-radius: 0 0 8px 8px; max-height: 200px; overflow-y: auto;"></div>
                </div>
                @error('student_id')
                  <div class="invalid-feedback">{{ $error }}</div>
                @enderror
                <script>
                const students = [
                  @foreach($students as $student)
                    {
                      id: {{ $student->id }},
                      name: "{{ addslashes($student->full_name) }}",
                      student_id: "{{ addslashes($student->student_id) }}"
                    },
                  @endforeach
                ];
                const studentSearch = document.getElementById('studentSearch');
                const studentSuggestions = document.getElementById('studentSuggestions');
                const studentIdInput = document.getElementById('student_id');
                studentSearch.addEventListener('input', function() {
                  const term = this.value.toLowerCase();
                  if (!term) {
                    studentSuggestions.style.display = 'none';
                    return;
                  }
                  const matches = students.filter(s =>
                    s.name.toLowerCase().includes(term) ||
                    s.student_id.toLowerCase().includes(term)
                  );
                  if (matches.length === 0) {
                    studentSuggestions.innerHTML = '<div class="p-2 text-muted">No matches found</div>';
                  } else {
                    studentSuggestions.innerHTML = matches.map(s =>
                      `<div class='p-2 suggestion-item' style='cursor:pointer;' data-id='${s.id}' data-name='${s.name}' data-studentid='${s.student_id}'>${s.name} (${s.student_id})</div>`
                    ).join('');
                  }
                  studentSuggestions.style.display = 'block';
                });
                studentSuggestions.addEventListener('mousedown', function(e) {
                  if (e.target.classList.contains('suggestion-item')) {
                    studentSearch.value = e.target.getAttribute('data-name') + ' (' + e.target.getAttribute('data-studentid') + ')';
                    studentIdInput.value = e.target.getAttribute('data-id');
                    studentSuggestions.style.display = 'none';
                  }
                });
                document.addEventListener('click', function(e) {
                  if (!studentSuggestions.contains(e.target) && e.target !== studentSearch) {
                    studentSuggestions.style.display = 'none';
                  }
                });
                </script>
                <!-- Reason for referral checklist -->
                <div id="referral-checklist" class="mt-4">
                  <h6>Reason for referral <small>(check all that apply):</small></h6>
                  <div class="row">
                    <div class="col-md-6">
                      <strong>Academic:</strong>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_academic[]" value="Attendance"><label class="form-check-label">Attendance (excessive absenteeism)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_academic[]" value="Activity Sheets/Assignments"><label class="form-check-label">Activity Sheets/Assignments</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_academic[]" value="Others"><label class="form-check-label">Others <input type="text" name="referral_academic_other" class="form-control form-control-sm d-inline-block w-auto ms-2" placeholder="Specify"></label></div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_academic[]" value="Exams"><label class="form-check-label">Exams</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_academic[]" value="Quiz"><label class="form-check-label">Quiz</label></div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-md-6">
                      <strong>Personal/Social:</strong>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Anger Management"><label class="form-check-label">Anger Management</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Bullying"><label class="form-check-label">Bullying</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Social Skills/Friends"><label class="form-check-label">Social Skills/Friends</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Negative Attitude"><label class="form-check-label">Negative Attitude</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Others"><label class="form-check-label">Others <input type="text" name="referral_social_other" class="form-control form-control-sm d-inline-block w-auto ms-2" placeholder="Specify"></label></div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Honesty"><label class="form-check-label">Honesty</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Self-esteem"><label class="form-check-label">Self-esteem</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Personal Hygiene"><label class="form-check-label">Personal Hygiene</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Adjustment"><label class="form-check-label">Adjustment</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Family Conflict"><label class="form-check-label">Family Conflict</label></div>
                    </div>
                  </div>
                  <!-- Incident Details Section -->
                  <div class="mt-4">
                    <h6>Incident Details</h6>
                    <div class="mb-2">
                      <textarea class="form-control" id="incident_description" name="incident_description" rows="3" placeholder="Describe the incident or concern in detail..."></textarea>
                    </div>
                  </div>
                    </div>
                  </div>
                </div>
                <!-- Reason for referral checklist -->
                <div id="referral-checklist" class="mt-4" style="display:none;">
                  <h6>Reason for referral <small>(check all that apply):</small></h6>
                  <div class="row">
                    <div class="col-md-6">
                      <strong>Academic:</strong>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_academic[]" value="Attendance"><label class="form-check-label">Attendance (excessive absenteeism)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_academic[]" value="Activity Sheets/Assignments"><label class="form-check-label">Activity Sheets/Assignments</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_academic[]" value="Others"><label class="form-check-label">Others <input type="text" name="referral_academic_other" class="form-control form-control-sm d-inline-block w-auto ms-2" placeholder="Specify"></label></div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_academic[]" value="Exams"><label class="form-check-label">Exams</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_academic[]" value="Quiz"><label class="form-check-label">Quiz</label></div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-md-6">
                      <strong>Personal/Social:</strong>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Anger Management"><label class="form-check-label">Anger Management</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Bullying"><label class="form-check-label">Bullying</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Social Skills/Friends"><label class="form-check-label">Social Skills/Friends</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Negative Attitude"><label class="form-check-label">Negative Attitude</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Others"><label class="form-check-label">Others <input type="text" name="referral_social_other" class="form-control form-control-sm d-inline-block w-auto ms-2" placeholder="Specify"></label></div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Honesty"><label class="form-check-label">Honesty</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Self-esteem"><label class="form-check-label">Self-esteem</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Personal Hygiene"><label class="form-check-label">Personal Hygiene</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Adjustment"><label class="form-check-label">Adjustment</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" name="referral_social[]" value="Family Conflict"><label class="form-check-label">Family Conflict</label></div>
                    </div>
                  </div>
                </div>
              </div>

             
              <div class="alert alert-info">
                <i class="ri-information-line me-2"></i>
                <strong>Note:</strong> This recommendation will be forwarded to the guidance department for review.
                A guidance counselor will assess the situation and may schedule a counseling session if appropriate.
              </div>

              <div class="d-flex justify-content-end gap-2">

@push('scripts')
<script>
document.getElementById('student_id').addEventListener('change', function() {
  const studentId = this.value;
  const checklistDiv = document.getElementById('referral-checklist');
  if (studentId) {
    checklistDiv.style.display = 'block';
  } else {
    checklistDiv.style.display = 'none';
  }
});
</script>
@endpush
                <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary">
                  <i class="ri-close-line me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-outline-success">
                  <i class="ri-heart-pulse-line me-2"></i>Submit Recommendation
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
</x-teacher-layout>
