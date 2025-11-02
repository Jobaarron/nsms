<x-registrar-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">
        <i class="ri-group-line me-2"></i>Class Lists
      </h1>
      <div class="text-muted">
        <i class="ri-calendar-line me-1"></i>{{ $currentAcademicYear }}
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

    <!-- FILTER FORM -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-filter-3-line me-2"></i>Filter Class Lists
        </h5>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('registrar.class-lists') }}" class="row g-3">
          <!-- Grade Level -->
          <div class="col-md-3">
            <label class="form-label">Grade Level</label>
            <select name="grade_level" id="grade_level" class="form-select" required>
              <option value="">Select Grade Level</option>
              @if(isset($orderedGrades) && $orderedGrades->count() > 0)
                @foreach($orderedGrades as $grade)
                  <option value="{{ $grade }}" {{ $selectedGrade === $grade ? 'selected' : '' }}>
                    {{ $grade }}
                  </option>
                @endforeach
              @else
                <option value="Nursery">Nursery</option>
                <option value="Junior Casa">Junior Casa</option>
                <option value="Senior Casa">Senior Casa</option>
                <option value="Kinder">Kinder</option>
                <option value="Grade 1">Grade 1</option>
                <option value="Grade 2">Grade 2</option>
                <option value="Grade 3">Grade 3</option>
                <option value="Grade 4">Grade 4</option>
                <option value="Grade 5">Grade 5</option>
                <option value="Grade 6">Grade 6</option>
                <option value="Grade 7">Grade 7</option>
                <option value="Grade 8">Grade 8</option>
                <option value="Grade 9">Grade 9</option>
                <option value="Grade 10">Grade 10</option>
                <option value="Grade 11">Grade 11</option>
                <option value="Grade 12">Grade 12</option>
              @endif
            </select>
          </div>

          <!-- Strand (for Grade 11 & 12) -->
          <div class="col-md-2" id="strandField" style="display: {{ in_array($selectedGrade, ['Grade 11', 'Grade 12']) ? 'block' : 'none' }};">
            <label class="form-label">Strand</label>
            <select name="strand" id="strand" class="form-select">
              <option value="">Select Strand</option>
              @if(isset($availableStrands) && $availableStrands->count() > 0)
                @foreach($availableStrands as $strand)
                  <option value="{{ $strand }}" {{ $selectedStrand === $strand ? 'selected' : '' }}>
                    {{ $strand }}
                  </option>
                @endforeach
              @endif
            </select>
          </div>

          <!-- Track (for TVL) -->
          <div class="col-md-2" id="trackField" style="display: {{ $selectedStrand === 'TVL' ? 'block' : 'none' }};">
            <label class="form-label">Track</label>
            <select name="track" id="track" class="form-select">
              <option value="">Select Track</option>
              @if(isset($availableTracks) && $availableTracks->count() > 0)
                @foreach($availableTracks as $track)
                  <option value="{{ $track }}" {{ $selectedTrack === $track ? 'selected' : '' }}>
                    {{ $track }}
                  </option>
                @endforeach
              @endif
            </select>
          </div>

          <!-- Section -->
          <div class="col-md-2">
            <label class="form-label">Section</label>
            <select name="section" id="section" class="form-select" required>
              <option value="">Select Section</option>
              @if(isset($availableSections) && $availableSections->count() > 0)
                @foreach($availableSections as $section)
                  <option value="{{ $section }}" {{ $selectedSection === $section ? 'selected' : '' }}>
                    {{ $section }}
                  </option>
                @endforeach
              @endif
            </select>
          </div>

          <!-- Submit Button -->
          <div class="col-md-3">
            <label class="form-label">&nbsp;</label>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">
                <i class="ri-search-line me-2"></i>View Class List
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    @if($classInfo && $students->count() > 0)
    <!-- CLASS INFORMATION -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-information-line me-2"></i>{{ $classInfo }}
          <span class="badge bg-primary ms-2">{{ $students->count() }} Students</span>
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Class Adviser -->
          <div class="col-md-6">
            <h6 class="text-muted mb-2">Class Adviser</h6>
            @if($classAdviser)
              <div class="d-flex align-items-center">
                <i class="ri-user-star-line text-primary me-2"></i>
                <strong>{{ $classAdviser->teacher->user->name }}</strong>
              </div>
            @else
              <div class="text-muted">
                <i class="ri-user-star-line me-2"></i>No adviser assigned
              </div>
            @endif
          </div>

          <!-- Subject Teachers Count -->
          <div class="col-md-6">
            <h6 class="text-muted mb-2">Subject Teachers</h6>
            <div class="d-flex align-items-center">
              <i class="ri-book-open-line text-success me-2"></i>
              <strong>{{ $subjectTeachers->count() }} Teachers</strong>
            </div>
          </div>
        </div>

        @if($subjectTeachers->count() > 0)
        <!-- Subject Teachers List -->
        <div class="mt-3">
          <h6 class="text-muted mb-2">Subjects & Teachers</h6>
          <div class="row">
            @foreach($subjectTeachers as $teacher)
            <div class="col-md-6 mb-2">
              <div class="d-flex align-items-center">
                <i class="ri-book-2-line text-info me-2"></i>
                <div>
                  <strong>{{ $teacher->subject->subject_name }}</strong>
                  <br>
                  <small class="text-muted">{{ $teacher->teacher->user->name }}</small>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endif
      </div>
    </div>

    <!-- STUDENT LIST -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-group-line me-2"></i>Student List - {{ $classInfo }}
        </h5>
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
                @if($selectedStrand)
                <th>Strand</th>
                @endif
                @if($selectedTrack)
                <th>Track</th>
                @endif
                <th>Contact</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($students as $index => $student)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $student->student_id }}</strong></td>
                <td>
                  <div>
                    <strong>{{ $student->last_name }}, {{ $student->first_name }}</strong>
                    @if($student->middle_name)
                      {{ $student->middle_name }}
                    @endif
                  </div>
                  @if($student->suffix)
                    <small class="text-muted">{{ $student->suffix }}</small>
                  @endif
                </td>
                <td>
                  <span class="badge bg-primary">{{ $student->grade_level }}</span>
                </td>
                <td>
                  <span class="badge bg-secondary">{{ $student->section }}</span>
                </td>
                @if($selectedStrand)
                <td>
                  <span class="badge bg-info">{{ $student->strand }}</span>
                </td>
                @endif
                @if($selectedTrack)
                <td>
                  <span class="badge bg-warning text-dark">{{ $student->track }}</span>
                </td>
                @endif
                <td>
                  @if($student->contact_number)
                    <small>{{ $student->contact_number }}</small>
                  @else
                    <small class="text-muted">No contact</small>
                  @endif
                </td>
                <td>
                  @if($student->is_active)
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-danger">Inactive</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @elseif($classInfo)
    <!-- NO STUDENTS FOUND -->
    <div class="card">
      <div class="card-body text-center py-5">
        <i class="ri-group-line display-1 text-muted mb-3"></i>
        <h4 class="text-muted">No Students Found</h4>
        <p class="text-muted">No students found for {{ $classInfo }}.</p>
        <p class="text-muted">Please check if students are enrolled in this class or try different filters.</p>
      </div>
    </div>
    @else
    <!-- INITIAL STATE -->
    <div class="card">
      <div class="card-body text-center py-5">
        <i class="ri-filter-3-line display-1 text-muted mb-3"></i>
        <h4 class="text-muted">Select Class to View</h4>
        <p class="text-muted">Use the filters above to select a grade level and section to view the class list.</p>
        <div class="mt-4">
          <h6 class="text-muted">Examples:</h6>
          <ul class="list-unstyled text-muted">
            <li>• <strong>Elementary:</strong> Grade 1 → Section A</li>
            <li>• <strong>Junior High:</strong> Grade 7 → Section B</li>
            <li>• <strong>Senior High STEM:</strong> Grade 11 → STEM → Section A</li>
            <li>• <strong>Senior High TVL:</strong> Grade 11 → TVL → ICT → Section A</li>
          </ul>
        </div>
      </div>
    </div>
    @endif
  </main>

</x-registrar-layout>
