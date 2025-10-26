<x-teacher-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">My Students</h1>
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

    <!-- STUDENTS BY CLASS -->
    @forelse($studentsByClass as $classData)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-book-2-line me-2"></i>{{ $classData['subject'] }} - {{ $classData['class_name'] }}
          <span class="badge bg-primary ms-2">{{ $classData['students']->count() }} Students</span>
        </h5>
      </div>
      <div class="card-body">
        @if($classData['students']->count() > 0)
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
                @foreach($classData['students'] as $student)
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
                      <a href="{{ route('teacher.grades.create', [
                        'subject_id' => $classData['assignment']->subject_id,
                        'grade_level' => $classData['assignment']->grade_level,
                        'section' => $classData['assignment']->section,
                        'quarter' => '1st'
                      ]) }}" class="btn btn-sm btn-outline-primary" title="Submit Grades">
                        <i class="ri-pencil-line"></i>
                      </a>
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
            <p>No students found for this class.</p>
          </div>
        @endif
      </div>
    </div>
    @empty
    <div class="card">
      <div class="card-body text-center py-5">
        <i class="ri-group-line display-1 text-muted mb-3"></i>
        <h4 class="text-muted">No Class Assignments</h4>
        <p class="text-muted">You don't have any class assignments for the current academic year.</p>
        <p class="text-muted">Please contact the Faculty Head to get assigned to classes.</p>
      </div>
    </div>
    @endforelse

    <!-- SUMMARY CARD -->
    @if($studentsByClass->count() > 0)
    <div class="card mt-4">
      <div class="card-body">
        <div class="row text-center">
          <div class="col-md-4">
            <h4 class="text-primary">{{ $studentsByClass->count() }}</h4>
            <p class="text-muted mb-0">Total Classes</p>
          </div>
          <div class="col-md-4">
            <h4 class="text-success">{{ $studentsByClass->sum(function($class) { return $class['students']->count(); }) }}</h4>
            <p class="text-muted mb-0">Total Students</p>
          </div>
          <div class="col-md-4">
            <h4 class="text-info">{{ $currentAcademicYear }}</h4>
            <p class="text-muted mb-0">Academic Year</p>
          </div>
        </div>
      </div>
    </div>
    @endif
  </main>
</x-teacher-layout>
