<x-student-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="section-title mb-0">{{ $quarter }} Quarter Grades</h1>
        <div class="text-muted">
          <i class="ri-file-text-line me-1"></i>
          @php
            $gradeDetailsClass = $student->grade_level . ' - ' . $student->section;
            if ($student->strand) {
                $gradeDetailsClass = $student->grade_level . ' - ' . $student->section . ' - ' . $student->strand;
                if ($student->track) {
                    $gradeDetailsClass = $student->grade_level . ' - ' . $student->section . ' - ' . $student->strand . ' - ' . $student->track;
                }
            }
          @endphp
          {{ $gradeDetailsClass }}
        </div>
      </div>
      <a href="{{ route('student.grades.index') }}" class="btn btn-outline-secondary">
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

    <!-- QUARTER STATISTICS -->
    @if(isset($stats) && $stats['total_subjects'] > 0)
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-application h-100">
          <div class="card-body text-center">
            <i class="ri-book-line display-6 mb-2"></i>
            <div>Total Subjects</div>
            <h3>{{ $stats['total_subjects'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-payment h-100">
          <div class="card-body text-center">
            <i class="ri-trophy-line display-6 mb-2"></i>
            <div>General Average</div>
            <h3 class="{{ $stats['average_grade'] >= 75 ? 'text-success' : 'text-danger' }}">
              {{ number_format($stats['average_grade'], 1) }}
            </h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-status h-100">
          <div class="card-body text-center">
            <i class="ri-check-circle-line display-6 mb-2"></i>
            <div>Passed</div>
            <h3 class="text-success">{{ $stats['passing_count'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-schedule h-100">
          <div class="card-body text-center">
            <i class="ri-close-circle-line display-6 mb-2"></i>
            <div>Failed</div>
            <h3 class="text-danger">{{ $stats['failing_count'] }}</h3>
          </div>
        </div>
      </div>
    </div>
    @endif

    <!-- GRADES TABLE -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-list-check me-2"></i>{{ $quarter }} Quarter Grades
        </h5>
      </div>
      <div class="card-body">
        @if(isset($grades) && $grades->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Subject</th>
                  <th>Teacher</th>
                  <th>Grade</th>
                  <th>Remarks</th>
                  <th>Status</th>
                  <th>Date Submitted</th>
                </tr>
              </thead>
              <tbody>
                @foreach($grades as $grade)
                <tr>
                  <td>
                    <div class="fw-medium">{{ $grade->subject->subject_name }}</div>
                    @if($grade->subject->subject_code)
                      <div class="small text-muted">{{ $grade->subject->subject_code }}</div>
                    @endif
                  </td>
                  <td>
                    <div class="fw-medium">{{ $grade->teacher->user->name }}</div>
                    <div class="small text-muted">{{ $grade->teacher->employee_id ?? 'Teacher' }}</div>
                  </td>
                  <td>
                    <span class="fw-bold fs-5 {{ $grade->grade >= 75 ? 'text-success' : 'text-danger' }}">
                      {{ number_format($grade->grade, 2) }}
                    </span>
                  </td>
                  <td>
                    {{ $grade->remarks ?? '-' }}
                  </td>
                  <td>
                    @if($grade->grade >= 75)
                      <span class="badge bg-success">Passed</span>
                    @else
                      <span class="badge bg-danger">Failed</span>
                    @endif
                  </td>
                  <td>
                    @if($grade->submitted_at)
                      <div class="small">{{ $grade->submitted_at->format('M d, Y') }}</div>
                      <div class="small text-muted">{{ $grade->submitted_at->format('g:i A') }}</div>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <!-- GRADE SUMMARY -->
          @if(isset($stats))
          <div class="mt-4 p-3 bg-light rounded">
            <div class="row">
              <div class="col-md-3">
                <strong>Highest Grade:</strong> 
                <span class="text-success">{{ number_format($stats['highest_grade'], 2) }}</span>
              </div>
              <div class="col-md-3">
                <strong>Lowest Grade:</strong> 
                <span class="text-danger">{{ number_format($stats['lowest_grade'], 2) }}</span>
              </div>
              <div class="col-md-3">
                <strong>General Average:</strong> 
                <span class="{{ $stats['average_grade'] >= 75 ? 'text-success' : 'text-danger' }}">
                  {{ number_format($stats['average_grade'], 2) }}
                </span>
              </div>
              <div class="col-md-3">
                <strong>Overall Status:</strong> 
                <span class="{{ $stats['average_grade'] >= 75 ? 'text-success' : 'text-danger' }}">
                  {{ $stats['average_grade'] >= 75 ? 'Passed' : 'Failed' }}
                </span>
              </div>
            </div>
          </div>
          @endif
        @else
          <div class="text-center py-5">
            <i class="ri-file-list-line display-4 text-muted mb-3"></i>
            <h5>No Grades Available</h5>
            <p class="text-muted">
              @if(!$student->hasPaidForQuarter($quarter))
                Please complete your payment to view grades for this quarter.
                <br><a href="{{ route('student.payments') }}" class="btn btn-outline-primary mt-2">
                  <i class="ri-money-dollar-circle-line me-2"></i>View Payments
                </a>
              @else
                Grades for {{ $quarter }} quarter have not been submitted yet.
              @endif
            </p>
          </div>
        @endif
      </div>
    </div>

    <!-- QUARTER NAVIGATION -->
    <div class="card mt-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-calendar-line me-2"></i>Other Quarters
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-2">
          @foreach(['1st', '2nd', '3rd', '4th'] as $q)
            @php
              $hasGrades = collect($availableQuarters)->firstWhere('quarter', $q) !== null;
              $isCurrentQuarter = $q === $quarter;
            @endphp
            <div class="col-6 col-md-3">
              @if($hasGrades && !$isCurrentQuarter)
                <a href="{{ route('student.grades.quarter', $q) }}" class="btn btn-outline-primary w-100">
                  <i class="ri-calendar-check-line me-2"></i>{{ $q }} Quarter
                </a>
              @elseif($isCurrentQuarter)
                <button class="btn btn-primary w-100" disabled>
                  <i class="ri-calendar-check-line me-2"></i>{{ $q }} Quarter (Current)
                </button>
              @else
                <button class="btn btn-outline-secondary w-100" disabled>
                  <i class="ri-calendar-line me-2"></i>{{ $q }} Quarter
                  <br><small>Not Available</small>
                </button>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </main>
</x-student-layout>

<style>
.grade-highlight {
  font-size: 1.2em;
  font-weight: bold;
}

.passing-grade {
  color: #198754;
}

.failing-grade {
  color: #dc3545;
}

.card-summary {
  transition: transform 0.2s;
}

.card-summary:hover {
  transform: translateY(-2px);
}
</style>
