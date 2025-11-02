<x-faculty-head-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Faculty Assignments</h1>
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

    <!-- ASSIGNMENT TYPE TABS -->
    <div class="card mb-4">
      <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="assignmentTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="subject-teacher-tab" data-bs-toggle="tab" data-bs-target="#subject-teacher" type="button" role="tab">
              <i class="ri-book-open-line me-2"></i>Subject Teachers
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="class-adviser-tab" data-bs-toggle="tab" data-bs-target="#class-adviser" type="button" role="tab">
              <i class="ri-user-star-line me-2"></i>Class Advisers
            </button>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content" id="assignmentTabsContent">
          
          <!-- SUBJECT TEACHER ASSIGNMENT TAB -->
          <div class="tab-pane fade show active" id="subject-teacher" role="tabpanel">
            <h5 class="mb-3">
              <i class="ri-add-circle-line me-2"></i>Assign Teacher to Subject/Section
            </h5>
            <form action="{{ route('faculty-head.assign-teacher.store') }}" method="POST" class="row g-3">
              @csrf
              <div class="col-md-3">
                <label class="form-label">Teacher</label>
                <select name="teacher_id" class="form-select" required>
                  <option value="">Teacher</option>
                  @foreach($teachers as $teacher)
                    <option value="{{ $teacher->teacher->id ?? '' }}">{{ $teacher->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Grade Level</label>
                <select name="grade_level" id="grade_level" class="form-select" required>
                  <option value="">Grade Level</option>
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
              <div class="col-md-2" id="strandField" style="display: none;">
                <label class="form-label">Strand</label>
                <select name="strand" class="form-select">
                  <option value="">Select Strand</option>
                  <option value="STEM">STEM</option>
                  <option value="ABM">ABM</option>
                  <option value="HUMSS">HUMSS</option>
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
                <label class="form-label">Section</label>
                <div class="input-group">
                  <select name="section" id="section" class="form-select" required>
                    <option value="">Section</option>
                    <!-- Sections will be populated dynamically based on selected grade level -->
                  </select>
                  <button type="button" class="btn btn-outline-info" id="checkSectionBtn" onclick="showSectionDetails()" title="Check Section Details" disabled>
                    <i class="ri-information-line"></i>
                  </button>
                </div>
              </div>
              <div class="col-md-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" id="subject_id" class="form-select" required>
                  <option value="">Subject</option>
                  <!-- Subjects will be populated dynamically based on selected grade level -->
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Effective Date</label>
                <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-d') }}" required>
              </div>
              <div class="col-12">
                <label class="form-label">Notes (Optional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes about this assignment"></textarea>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary">
                  <i class="ri-user-add-line me-2"></i>Assign Subject Teacher
                </button>
              </div>
            </form>
          </div>

          <!-- CLASS ADVISER ASSIGNMENT TAB -->
          <div class="tab-pane fade" id="class-adviser" role="tabpanel">
            <h5 class="mb-3">
              <i class="ri-add-circle-line me-2"></i>Assign Class Adviser
            </h5>
            <form action="{{ route('faculty-head.assign-adviser.store') }}" method="POST" class="row g-3">
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
                <select name="grade_level" id="grade_level_adviser" class="form-select" required>
                  <option value="">Grade Level</option>
                  @php
                    $availableGrades = $sections->pluck('grade_level')->unique();
                    $orderedGrades = collect($gradeOrder)->filter(function($grade) use ($availableGrades) {
                      return $availableGrades->contains($grade);
                    });
                  @endphp
                  @foreach($orderedGrades as $grade)
                    <option value="{{ $grade }}">{{ $grade }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2" id="strandFieldAdviser" style="display: none;">
                <label class="form-label">Strand</label>
                <select name="strand" id="strand_adviser" class="form-select">
                  <option value="">Select Strand</option>
                  <option value="STEM">STEM</option>
                  <option value="ABM">ABM</option>
                  <option value="HUMSS">HUMSS</option>
                  <option value="TVL">TVL</option>
                </select>
              </div>
              <div class="col-md-2" id="trackFieldAdviser" style="display: none;">
                <label class="form-label">Track</label>
                <select name="track" id="track_adviser" class="form-select">
                  <option value="">Select Track</option>
                  <option value="ICT">ICT</option>
                  <option value="H.E.">H.E. (Home Economics)</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Section</label>
                <div class="input-group">
                  <select name="section" id="section_adviser" class="form-select" required>
                    <option value="">Section</option>
                    <!-- Sections will be populated dynamically based on selected grade level -->
                  </select>
                  <button type="button" class="btn btn-outline-info" id="checkSectionBtnAdviser" onclick="showSectionDetailsAdviser()" title="Check Section Details" disabled>
                    <i class="ri-information-line"></i>
                  </button>
                </div>
              </div>
              <div class="col-md-3">
                <label class="form-label">Effective Date</label>
                <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-d') }}" required>
              </div>
              <div class="col-12">
                <label class="form-label">Notes (Optional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes about this assignment"></textarea>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary">
                  <i class="ri-user-add-line me-2"></i>Assign Class Adviser
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- SUBJECT TEACHER ASSIGNMENTS -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-book-open-line me-2"></i>Subject Teacher Assignments
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Teacher</th>
                <th>Subject</th>
                <th>Grade</th>
                <th>Section</th>
                <th>Strand</th>
                <th>Track</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($assignments->where('subject_id', '!=', null) as $assignment)
              <tr>
                <td>
                  <div class="fw-medium">{{ $assignment->teacher->user->name }}</div>
                </td>
                <td>
                  <div class="fw-medium">{{ $assignment->subject->subject_name }}</div>
                  @if($assignment->subject->subject_code)
                    <div class="text-muted small">{{ $assignment->subject->subject_code }}</div>
                  @endif
                </td>
                <td>
                  <span class="badge bg-primary">{{ $assignment->grade_level }}</span>
                </td>
                <td>
                  <span class="badge bg-secondary" 
                        style="cursor: pointer;" 
                        onclick="viewClassList('{{ $assignment->grade_level }}', '{{ $assignment->section }}', '{{ $assignment->strand }}', '{{ $assignment->track }}')"
                        title="Click to view class list">
                    {{ $assignment->section }}
                  </span>
                </td>
                <td>
                  @if($assignment->strand)
                    <span class="badge bg-info">{{ $assignment->strand }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  @if($assignment->track)
                    <span class="badge bg-warning text-dark">{{ $assignment->track }}</span>
                  @else
                    <span class="text-muted">-</span>
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

    <!-- CLASS ADVISER ASSIGNMENTS -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-user-star-line me-2"></i>Class Adviser Assignments
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Teacher</th>
                <th>Grade</th>
                <th>Section</th>
                <th>Strand</th>
                <th>Track</th>
                <th>Assigned</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($advisers as $adviser)
              <tr>
                <td>
                  <div class="fw-medium">{{ $adviser->teacher->user->name }}</div>
                </td>
                <td>
                  <span class="badge bg-primary">{{ $adviser->grade_level }}</span>
                </td>
                <td>
                  <span class="badge bg-secondary" 
                        style="cursor: pointer;" 
                        onclick="viewClassList('{{ $adviser->grade_level }}', '{{ $adviser->section }}', '{{ $adviser->strand }}', '{{ $adviser->track }}')"
                        title="Click to view class list">
                    {{ $adviser->section }}
                  </span>
                </td>
                <td>
                  @if($adviser->strand)
                    <span class="badge bg-info">{{ $adviser->strand }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  @if($adviser->track)
                    <span class="badge bg-warning text-dark">{{ $adviser->track }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  <div class="text-muted">{{ $adviser->assigned_date->format('M d, Y') }}</div>
                </td>
                <td>
                  @if($adviser->status === 'active')
                    <button class="btn btn-sm btn-outline-danger" onclick="removeAssignment({{ $adviser->id }})" title="Remove Class Adviser">
                      <i class="ri-user-unfollow-line"></i>
                    </button>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  <i class="ri-user-star-line display-6 mb-2 d-block"></i>
                  No class adviser assignments yet
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Class List Modal -->
  <div class="modal fade" id="sectionDetailsModal" tabindex="-1" aria-labelledby="sectionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="sectionDetailsModalLabel">
            <i class="ri-group-line me-2"></i>Class List Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="sectionDetailsContent">
            <!-- Loading State -->
            <div class="text-center" id="loadingState">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Loading class details...</p>
            </div>

            <!-- Class Content (Hidden initially) -->
            <div id="classContent" style="display: none;">
              <!-- Class Information Card -->
              <div class="card mb-4">
                <div class="card-header">
                  <h6 class="mb-0" id="classTitle">
                    <i class="ri-information-line me-2"></i>Class Information
                    <span class="badge bg-primary ms-2" id="studentCount">0 Students</span>
                  </h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    <!-- Class Adviser -->
                    <div class="col-md-6">
                      <h6 class="text-muted mb-2">Class Adviser</h6>
                      <div id="classAdviser">
                        <div class="text-muted">
                          <i class="ri-user-star-line me-2"></i>No adviser assigned
                        </div>
                      </div>
                    </div>

                    <!-- Subject Teachers Count -->
                    <div class="col-md-6">
                      <h6 class="text-muted mb-2">Subject Teachers</h6>
                      <div id="subjectTeachersCount">
                        <i class="ri-book-open-line text-success me-2"></i>
                        <strong>0 Teachers</strong>
                      </div>
                    </div>
                  </div>

                  <!-- Subject Teachers List -->
                  <div class="mt-3" id="subjectTeachersSection" style="display: none;">
                    <h6 class="text-muted mb-2">Subjects & Teachers</h6>
                    <div class="row" id="subjectTeachersList">
                      <!-- Dynamic content -->
                    </div>
                  </div>
                </div>
              </div>

              <!-- Student List Card -->
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">
                    <i class="ri-group-line me-2"></i>Student List
                  </h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover align-middle">
                      <thead class="table-light">
                        <tr>
                          <th>#</th>
                          <th>Student ID</th>
                          <th>Name</th>
                          <th>Grade</th>
                          <th>Section</th>
                          <th id="strandHeader" style="display: none;">Strand</th>
                          <th id="trackHeader" style="display: none;">Track</th>
                          <th>Contact</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody id="studentTableBody">
                        <!-- Dynamic student rows -->
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div id="emptyState" class="text-center py-5" style="display: none;">
                <i class="ri-group-line display-1 text-muted mb-3"></i>
                <h5 class="text-muted">No Students Found</h5>
                <p class="text-muted">No students are enrolled in this class.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="ri-close-line me-2"></i>Close
          </button>
        </div>
      </div>
    </div>
  </div>
</x-faculty-head-layout>
