<x-student-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">My Class Schedule</h1>
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

    <!-- SCHEDULE STATISTICS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-application h-100">
          <div class="card-body text-center">
            <i class="ri-book-2-line display-6 mb-2"></i>
            <div>Total Subjects</div>
            <h3>{{ $stats['total_subjects'] }}</h3>
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
            <i class="ri-graduation-cap-line display-6 mb-2"></i>
            <div>Grade Level</div>
            <h3>{{ $stats['grade_level'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-schedule h-100">
          <div class="card-body text-center">
            <i class="ri-group-line display-6 mb-2"></i>
            <div>Section</div>
            <h3>{{ $stats['section'] }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- WEEKLY SCHEDULE TABLE -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-calendar-2-line me-2"></i>Weekly Schedule
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

    <!-- SUBJECTS LIST -->
    <div class="card mt-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-book-line me-2"></i>My Subjects & Teachers
        </h5>
      </div>
      <div class="card-body">
        <div class="row" id="subjects-list">
          <!-- Subjects will be populated by JavaScript -->
        </div>
      </div>
    </div>
  </main>
</x-student-layout>

@push('scripts')
<script src="{{ asset('js/student-schedule.js') }}"></script>
@endpush
