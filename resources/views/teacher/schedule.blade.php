<x-teacher-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">My Teaching Schedule</h1>
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

    <!-- TEACHING LOAD STATISTICS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-application h-100">
          <div class="card-body text-center">
            <i class="ri-book-2-line display-6 mb-2"></i>
            <div>Total Classes</div>
            <h3>{{ $stats['total_classes'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-status h-100">
          <div class="card-body text-center">
            <i class="ri-time-line display-6 mb-2"></i>
            <div>Weekly Hours</div>
            <h3>{{ $stats['total_hours'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-payment h-100">
          <div class="card-body text-center">
            <i class="ri-book-open-line display-6 mb-2"></i>
            <div>Subjects Taught</div>
            <h3>{{ $stats['subjects_taught'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-schedule h-100">
          <div class="card-body text-center">
            <i class="ri-group-line display-6 mb-2"></i>
            <div>Sections Handled</div>
            <h3>{{ $stats['sections_handled'] }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- WEEKLY SCHEDULE TABLE -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-calendar-2-line me-2"></i>Weekly Teaching Schedule
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Time</th>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
                <th>Saturday</th>
              </tr>
            </thead>
            <tbody id="schedule-table-body">
              <!-- Schedule will be populated by JavaScript -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- CLASS ASSIGNMENTS -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-book-line me-2"></i>My Class Assignments
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Subject</th>
                <th>Grade & Section</th>
                <th>Assignment Type</th>
                <th>Schedule</th>
                <th>Students</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($assignments as $assignment)
              <tr>
                <td>
                  <strong>{{ $assignment->subject->subject_name }}</strong>
                  <br><small class="text-muted">{{ $assignment->subject->subject_code }}</small>
                </td>
                <td>{{ $assignment->grade_level }} - {{ $assignment->section }}</td>
                <td>
                  @if($assignment->isClassAdviser())
                    <span class="badge bg-primary">Class Adviser</span>
                  @else
                    <span class="badge bg-secondary">Subject Teacher</span>
                  @endif
                </td>
                <td>
                  @if($assignment->classSchedule())
                    {{ $assignment->classSchedule()->day_of_week }}<br>
                    <small>{{ $assignment->classSchedule()->time_range }}</small>
                  @else
                    <span class="text-muted">No schedule</span>
                  @endif
                </td>
                <td>{{ $assignment->student_count }}</td>
                <td>
                  <a href="{{ route('teacher.schedule.students', [
                    'subject_id' => $assignment->subject_id,
                    'grade_level' => $assignment->grade_level,
                    'section' => $assignment->section
                  ]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="ri-group-line me-1"></i>View Students
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No assignments found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</x-teacher-layout>

@push('scripts')
<script src="{{ asset('js/teacher-schedule.js') }}"></script>
@endpush
