<x-student-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">My Grades</h1>
      <div class="text-muted">
        <i class="ri-file-text-line me-1"></i>{{ $currentAcademicYear }}
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

    <!-- ACADEMIC PERFORMANCE OVERVIEW -->
    @if(isset($performance) && !empty($performance['quarters']))
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-bar-chart-line me-2"></i>Academic Performance Overview
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          @foreach($performance['quarters'] as $quarter => $data)
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-info h-100">
              <div class="card-body text-center">
                <div class="fw-bold">{{ $quarter }} Quarter</div>
                <h4 class="mt-2">{{ number_format($data['average'], 1) }}</h4>
                <small>{{ $data['passing_count'] }}/{{ $data['subjects_count'] }} Passed</small>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif

    <!-- QUARTERS SELECTION -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-calendar-check-line me-2"></i>Select Quarter
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          @foreach(['1st', '2nd', '3rd', '4th'] as $quarter)
          <div class="col-6 col-lg-3">
            @php
              $quarterData = collect($availableQuarters)->firstWhere('quarter', $quarter);
              $hasGrades = $quarterData !== null;
            @endphp
            
            @if($hasGrades)
              <a href="{{ route('student.grades.quarter', $quarter) }}" class="text-decoration-none">
                <div class="card card-summary card-application h-100">
                  <div class="card-body text-center">
                    <i class="ri-file-check-line display-6 mb-2"></i>
                    <div>{{ $quarter }} Quarter</div>
                    <small>{{ $quarterData['grade_count'] }} subjects</small>
                  </div>
                </div>
              </a>
            @else
              <div class="card h-100 opacity-50">
                <div class="card-body text-center">
                  <i class="ri-file-line display-6 mb-2 text-muted"></i>
                  <div class="text-muted">{{ $quarter }} Quarter</div>
                  <small class="text-muted">
                    @if(!$student->hasPaidForQuarter($quarter))
                      Payment Required
                    @else
                      No Grades Yet
                    @endif
                  </small>
                </div>
              </div>
            @endif
          </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- PAYMENT STATUS INFO -->
    @if(!$student->is_paid && $student->total_paid <= 0)
    <div class="alert alert-warning">
      <h5><i class="ri-alert-line me-2"></i>Payment Required</h5>
      <p class="mb-2">To view your grades, please ensure your payments are up to date.</p>
      <a href="{{ route('student.payments') }}" class="btn btn-outline-primary">
        <i class="ri-money-dollar-circle-line me-2"></i>View Payments
      </a>
    </div>
    @endif
  </main>
</x-student-layout>

@push('scripts')
<script src="{{ asset('js/student-grades.js') }}"></script>
@endpush
