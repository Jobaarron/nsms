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

    @php
      // Group sections by grade level
      $groupedSections = $sections->groupBy('grade_level');
      $gradeOrder = [
        'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
        'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
      ];
    @endphp

    <!-- GRADE LEVEL ACCORDION -->
    <div class="accordion" id="gradeAccordion">
      @foreach($gradeOrder as $grade)
        @if($groupedSections->has($grade))
          <div class="accordion-item mb-3">
            <h2 class="accordion-header" id="heading{{ str_replace(' ', '', $grade) }}">
              <button class="accordion-button collapsed fw-bold" type="button" 
                      data-bs-toggle="collapse" 
                      data-bs-target="#collapse{{ str_replace(' ', '', $grade) }}" 
                      aria-expanded="false"
                      onclick="loadGradeSectionsForAssignment('{{ $grade }}')">
                <i class="ri-graduation-cap-line me-2 text-primary"></i>
                {{ $grade }}
                <span class="badge bg-secondary rounded-pill ms-auto me-3" id="badge{{ str_replace(' ', '', $grade) }}">{{ $groupedSections[$grade]->count() }} sections</span>
              </button>
            </h2>
            <div id="collapse{{ str_replace(' ', '', $grade) }}" 
                 class="accordion-collapse collapse" 
                 data-bs-parent="#gradeAccordion">
              <div class="accordion-body p-0">
                <div id="sections{{ str_replace(' ', '', $grade) }}" class="loading-sections">
                  <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading sections...</span>
                    </div>
                    <p class="text-muted mt-2">Loading sections for {{ $grade }}...</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endif
      @endforeach
    </div>

    <!-- ASSIGNMENTS TABLES FOR VIEWING/DELETING -->
    <div class="row mt-4">
      <!-- SUBJECT TEACHER ASSIGNMENTS TABLE -->
      <div class="col-12 mb-4">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
              <i class="ri-book-open-line me-2"></i>Subject Teacher Assignments
            </h5>
            <span class="badge bg-primary" id="assignmentsCount">{{ $assignments->count() }}</span>
          </div>
          <div class="card-body border-bottom">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="ri-search-line"></i>
                  </span>
                  <input type="text" class="form-control" id="searchAssignments" placeholder="Search by teacher name, subject, class, or strand...">
                  <button class="btn btn-outline-secondary" type="button" id="clearSearchAssignments">
                    <i class="ri-close-line"></i>
                  </button>
                </div>
              </div>
              <div class="col-md-6 text-end">
                <small class="text-muted">Found: <span id="foundAssignments">{{ $assignments->count() }}</span> assignments</small>
              </div>
            </div>
          </div>
          <div class="card-body">
            @forelse($assignments as $assignment)
              <div class="card mb-3 assignment-item" 
                   data-teacher="{{ strtolower($assignment->teacher->user->name) }}"
                   data-subject="{{ strtolower($assignment->subject->subject_name) }}"
                   data-grade="{{ strtolower($assignment->grade_level) }}"
                   data-section="{{ strtolower($assignment->section) }}"
                   data-strand="{{ strtolower($assignment->strand ?? '') }}"
                   data-track="{{ strtolower($assignment->track ?? '') }}">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-md-3">
                      <label class="text-muted small">Teacher</label>
                      <div class="fw-medium">{{ $assignment->teacher->user->name }}</div>
                    </div>
                    <div class="col-md-3">
                      <label class="text-muted small">Subject</label>
                      <div class="fw-medium">{{ $assignment->subject->subject_name }}</div>
                      @if($assignment->subject->subject_code)
                        <div class="text-muted small">{{ $assignment->subject->subject_code }}</div>
                      @endif
                    </div>
                    <div class="col-md-2">
                      <label class="text-muted small">Class</label>
                      <div>
                        <span class="badge bg-primary">{{ $assignment->grade_level }}</span>
                        <span class="badge bg-secondary">{{ $assignment->section }}</span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <label class="text-muted small">Strand/Track</label>
                      <div>
                        @if($assignment->strand)
                          <span class="badge bg-info">{{ $assignment->strand }}</span>
                        @endif
                        @if($assignment->track)
                          <span class="badge bg-warning text-dark">{{ $assignment->track }}</span>
                        @endif
                        @if(!$assignment->strand && !$assignment->track)
                          <span class="text-muted">-</span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-1 text-end">
                      @if($assignment->status === 'active')
                        <button class="btn btn-sm btn-outline-danger" onclick="removeAssignment({{ $assignment->id }})" title="Remove Assignment">
                          <i class="ri-user-unfollow-line"></i>
                        </button>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center text-muted py-4">
                <i class="ri-book-open-line display-6 mb-2 d-block"></i>
                <p>No subject teacher assignments yet</p>
              </div>
            @endforelse
          </div>
        </div>
      </div>

      <!-- CLASS ADVISER ASSIGNMENTS TABLE -->
      <div class="col-12 mb-4">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
              <i class="ri-user-star-line me-2"></i>Class Adviser Assignments
            </h5>
            <span class="badge bg-primary" id="advisersCount">{{ $advisers->count() }}</span>
          </div>
          <div class="card-body border-bottom">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="ri-search-line"></i>
                  </span>
                  <input type="text" class="form-control" id="searchAdvisers" placeholder="Search by teacher name, class, or strand...">
                  <button class="btn btn-outline-secondary" type="button" id="clearSearchAdvisers">
                    <i class="ri-close-line"></i>
                  </button>
                </div>
              </div>
              <div class="col-md-6 text-end">
                <small class="text-muted">Found: <span id="foundAdvisers">{{ $advisers->count() }}</span> advisers</small>
              </div>
            </div>
          </div>
          <div class="card-body">
            @forelse($advisers as $adviser)
              <div class="card mb-3 adviser-item" 
                   data-teacher="{{ strtolower($adviser->teacher->user->name) }}"
                   data-grade="{{ strtolower($adviser->grade_level) }}"
                   data-section="{{ strtolower($adviser->section) }}"
                   data-strand="{{ strtolower($adviser->strand ?? '') }}"
                   data-track="{{ strtolower($adviser->track ?? '') }}">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-md-3">
                      <label class="text-muted small">Teacher</label>
                      <div class="fw-medium">{{ $adviser->teacher->user->name }}</div>
                    </div>
                    <div class="col-md-2">
                      <label class="text-muted small">Class</label>
                      <div>
                        <span class="badge bg-primary">{{ $adviser->grade_level }}</span>
                        <span class="badge bg-secondary">{{ $adviser->section }}</span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <label class="text-muted small">Strand/Track</label>
                      <div>
                        @if($adviser->strand)
                          <span class="badge bg-info">{{ $adviser->strand }}</span>
                        @endif
                        @if($adviser->track)
                          <span class="badge bg-warning text-dark">{{ $adviser->track }}</span>
                        @endif
                        @if(!$adviser->strand && !$adviser->track)
                          <span class="text-muted">-</span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-3">
                      <label class="text-muted small">Assigned Date</label>
                      <div class="text-muted">{{ $adviser->assigned_date->format('M d, Y') }}</div>
                    </div>
                    <div class="col-md-1 text-end">
                      @if($adviser->status === 'active')
                        <button class="btn btn-sm btn-outline-danger" onclick="removeAssignment({{ $adviser->id }})" title="Remove Class Adviser">
                          <i class="ri-user-unfollow-line"></i>
                        </button>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center text-muted py-4">
                <i class="ri-user-star-line display-6 mb-2 d-block"></i>
                <p>No class adviser assignments yet</p>
              </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </main>

  {{-- Pass data to JavaScript --}}
  <script>
    window.facultyData = {
      teachers: @json($teachers),
      subjects: @json($subjects),
      assignments: @json($assignments),
      advisers: @json($advisers),
      sections: @json($sections)
    };
  </script>

  @vite('resources/js/faculty-head-assign-teacher.js')
</x-faculty-head-layout>
                