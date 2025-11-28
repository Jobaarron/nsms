<x-teacher-layout>
  <!-- MAIN CONTENT -->
   <script src="{{ asset('js/teacher-advisory.js') }}"></script>
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Advisory</h1>
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

    <!-- ADVISORY CLASS -->
    @if($advisoryAssignment)
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="ri-user-star-line me-2"></i>Advisory Class - {{ $className }}
            <span class="badge bg-primary ms-2">{{ $students->count() }} Students</span>
          </h5>
          <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-success" onclick="printAllReportCards()">
              <i class="ri-printer-line me-1"></i>Print All Report Cards
            </button>
            <button type="button" class="btn btn-outline-info" onclick="viewAllGrades()">
              <i class="ri-file-list-3-line me-1"></i>View All Grades
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if($students->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Student ID</th>
                  <th>Name</th>
                  <th>Grade Level</th>
                  <th>Section</th>
                  <th>Contact</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($students as $student)
                <tr>
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
                  <td>{{ $student->grade_level }}</td>
                  <td>{{ $student->section }}</td>
                  <td>
                    @if($student->contact_number)
                      <small class="text-muted">{{ $student->contact_number }}</small>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      <button type="button" class="btn btn-sm btn-outline-primary" 
                              onclick="viewStudentGrades({{ $student->id }}, '{{ $student->grade_level }}')" title="View Grades">
                        <i class="ri-file-list-3-line"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-success" 
                              onclick="printReportCard({{ $student->id }}, '{{ $student->grade_level }}')" title="Print Report Card">
                        <i class="ri-printer-line"></i>
                      </button>
                      <a href="{{ route('teacher.recommend-counseling.form', ['student_id' => $student->id]) }}" 
                         class="btn btn-sm btn-outline-warning" title="Recommend Counseling">
                        <i class="ri-heart-pulse-line"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center text-muted py-4">
            <i class="ri-group-line display-4 mb-3"></i>
            <p>No students found in your advisory class.</p>
          </div>
        @endif
      </div>
    </div>

    <!-- SUMMARY CARD -->
    <div class="card mt-4">
      <div class="card-body">
        <div class="row text-center">
          <div class="col-md-4">
            <h4 class="text-primary">{{ $students->count() }}</h4>
            <p class="text-muted mb-0">Advisory Students</p>
          </div>
          <div class="col-md-4">
            <h4 class="text-success">{{ $className }}</h4>
            <p class="text-muted mb-0">Advisory Class</p>
          </div>
          <div class="col-md-4">
            <h4 class="text-info">{{ $currentAcademicYear }}</h4>
            <p class="text-muted mb-0">Academic Year</p>
          </div>
        </div>
      </div>
    </div>
    @else
    <!-- NO ADVISORY ASSIGNMENT -->
    <div class="card">
      <div class="card-body text-center py-5">
        <i class="ri-user-star-line display-1 text-muted mb-3"></i>
        <h4 class="text-muted">No Advisory Assignment</h4>
        <p class="text-muted">You are not assigned as a class adviser for the current academic year.</p>
        <p class="text-muted">Please contact the Faculty Head if you believe this is an error.</p>
      </div>
    </div>
    @endif

    <!-- View Grades Modal -->
    <div class="modal fade" id="viewGradesModal" tabindex="-1" aria-labelledby="viewGradesModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="viewGradesModalLabel">
              <i class="ri-file-list-3-line me-2"></i>Student Grades
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="gradesModalContent">
            <div class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Loading grades...</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-success" onclick="printCurrentGrades()">
              <i class="ri-printer-line me-1"></i>Print Grades
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- All Grades Modal -->
    <div class="modal fade" id="allGradesModal" tabindex="-1" aria-labelledby="allGradesModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-fullscreen-lg-down modal-xl" style="max-width: 95vw;">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="allGradesModalLabel">
              <i class="ri-file-list-3-line me-2"></i>All Advisory Students Grades
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="allGradesModalContent">
            <div class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Loading all grades...</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </main>

</x-teacher-layout>
