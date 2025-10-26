<x-faculty-head-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Assign Teacher per Subject/Section</h1>
      <div class="text-muted">
        <i class="ri-user-settings-line me-1"></i>{{ $currentAcademicYear }}
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

    <!-- ASSIGN NEW TEACHER -->
    <div class="card faculty-assignment-form mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-add-circle-line me-2"></i>Assign Teacher to Subject/Section
        </h5>
      </div>
      <div class="card-body">
        <form action="{{ route('faculty-head.assign-teacher.store') }}" method="POST" class="row g-3">
          @csrf
          <div class="col-md-3">
            <label class="form-label">Teacher</label>
            <select name="teacher_id" class="form-select" required>
              <option value="">Select Teacher</option>
              @foreach($teachers as $teacher)
                <option value="{{ $teacher->teacher->id ?? '' }}">{{ $teacher->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Grade Level</label>
            <select name="grade_level" id="grade_level" class="form-select" required>
              <option value="">Select Grade</option>
              @php
                $gradeOrder = [
                  'Nursery', 'Junior Casa', 'Senior Casa', 'Kinder',
                  'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
                  'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
                ];
                $availableGrades = $subjects->pluck('grade_level')->unique();
                $orderedGrades = collect($gradeOrder)->filter(function($grade) use ($availableGrades) {
                  return $availableGrades->contains($grade);
                });
              @endphp
              @foreach($orderedGrades as $grade)
                <option value="{{ $grade }}">{{ $grade }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Subject</label>
            <select name="subject_id" id="subject_id" class="form-select" required>
              <option value="">Select Subject</option>
              @php
                $gradeOrder = [
                  'Nursery', 'Junior Casa', 'Senior Casa', 'Kinder',
                  'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
                  'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
                ];
                $subjectsByGrade = $subjects->groupBy('grade_level');
              @endphp
              @foreach($gradeOrder as $grade)
                @if(isset($subjectsByGrade[$grade]))
                  <optgroup label="--- {{ $grade }} ---" data-grade="{{ $grade }}">
                    @foreach($subjectsByGrade[$grade] as $subject)
                      <option value="{{ $subject->id }}" data-grade="{{ $grade }}">
                        {{ $subject->subject_name }}
                        @if($subject->subject_code)
                          ({{ $subject->subject_code }})
                        @endif
                      </option>
                    @endforeach
                  </optgroup>
                @endif
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Section</label>
            <select name="section" id="section" class="form-select" required>
              <option value="">Select Section</option>
              @foreach(['A', 'B', 'C', 'D', 'E', 'F'] as $section)
                <option value="{{ $section }}">{{ $section }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2" id="strandField" style="display: none;">
            <label class="form-label">Strand</label>
            <select name="strand" class="form-select">
              <option value="">Select Strand</option>
              <option value="STEM">STEM</option>
              <option value="ABM">ABM</option>
              <option value="HUMSS">HUMSS</option>
              <option value="GAS">GAS</option>
              <option value="TVL">TVL</option>
            </select>
          </div>
          <div class="col-md-2" id="trackField" style="display: none;">
            <label class="form-label">Track</label>
            <select name="track" class="form-select">
              <option value="">Select Track</option>
              <option value="ICT">ICT</option>
              <option value="H.E.">H.E. (Home Economics)</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Effective Date</label>
            <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-d') }}" required>
          </div>
        </div>
        
        <!-- ASSIGNMENT NOTES -->
        <div class="col-12">
          <label class="form-label">Notes (Optional)</label>
          <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes about this assignment"></textarea>
        </div>
        
        <div class="col-12">
          <button type="submit" class="btn btn-primary">
            <i class="ri-user-add-line me-2"></i>Assign Teacher
          </button>
        </div>
        </form>
      </div>
    </div>

    <!-- CURRENT SUBJECT TEACHER ASSIGNMENTS -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-book-open-line me-2"></i>Current Subject Teacher Assignments
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Teacher</th>
                <th>Subject</th>
                <th>Class</th>
                <th>Strand/Track</th>
                <th>Assigned Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($assignments->where('subject_id', '!=', null) as $assignment)
              <tr>
                <td>
                  <div class="fw-medium">{{ $assignment->teacher->user->name }}</div>
                  <div class="small text-muted">Subject Teacher</div>
                </td>
                <td>
                  <div class="fw-medium">{{ $assignment->subject->subject_name }}</div>
                  @if($assignment->subject->subject_code)
                    <div class="small text-muted">{{ $assignment->subject->subject_code }}</div>
                  @endif
                </td>
                <td>
                  <span class="badge bg-primary">{{ $assignment->grade_level }} - {{ $assignment->section }}</span>
                </td>
                <td>
                  @if($assignment->strand)
                    <div class="small">
                      <span class="badge bg-info">{{ $assignment->strand }}</span>
                      @if($assignment->track)
                        <br><span class="badge bg-warning mt-1">{{ $assignment->track }}</span>
                      @endif
                    </div>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  <div class="small">{{ $assignment->assigned_date->format('M d, Y') }}</div>
                </td>
                <td>
                  @if($assignment->status === 'active')
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-secondary">Inactive</span>
                  @endif
                </td>
                <td>
                  @if($assignment->status === 'active')
                    <button class="btn btn-sm btn-outline-danger" onclick="removeAssignment({{ $assignment->id }})" title="Remove Assignment">
                      <i class="ri-user-unfollow-line"></i>
                    </button>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  <i class="ri-book-open-line display-6 mb-2 d-block"></i>
                  No subject teacher assignments yet
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</x-faculty-head-layout>
