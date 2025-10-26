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

    {{-- ACADEMIC PERFORMANCE OVERVIEW - COMMENTED OUT FOR TESTING --}}
    {{-- @if(isset($performance) && !empty($performance['quarters']))
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
    @endif --}}

    {{-- DEBUG INFORMATION - TEMPORARY --}}
    <!-- @if(config('app.debug')) -->
    <!-- <div class="card mb-4 border-warning">
      <div class="card-header bg-warning text-dark">
        <h6 class="mb-0">🔍 DEBUG: Grade Fetching Test</h6>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <strong>Student Info:</strong><br>
            ID: {{ $student->id }}<br>
            Grade Level: {{ $student->grade_level }}<br>
            Section: {{ $student->section }}<br>
            Strand: {{ $student->strand ?? 'N/A' }}<br>
            Track: {{ $student->track ?? 'N/A' }}<br>
            Academic Year: {{ $student->academic_year }}
          </div>
          <div class="col-md-6">
            <strong>Available Quarters:</strong><br>
            @if(isset($availableQuarters) && count($availableQuarters) > 0)
              @foreach($availableQuarters as $q)
                <span class="badge bg-success">{{ $q['quarter'] }} ({{ $q['grade_count'] }} grades)</span><br>
              @endforeach
            @else
              <span class="text-danger">No quarters with grades found</span>
            @endif
          </div>
        </div>
        
        @if(isset($quarter))
        <hr>
        <strong>Current Quarter: {{ $quarter }}</strong><br>
        @if(isset($grades) && $grades->count() > 0)
          <span class="text-success">✅ Found {{ $grades->count() }} grades for {{ $quarter }} quarter</span>
          <div class="mt-2">
            @foreach($grades as $grade)
              <small class="d-block">
                📚 {{ $grade->subject->subject_name ?? 'Unknown Subject' }}: 
                <strong>{{ $grade->grade }}</strong> 
                (Teacher: {{ $grade->teacher->user->name ?? 'Unknown Teacher' }})
              </small>
            @endforeach
          </div>
        @else
          <span class="text-danger">❌ No grades found for {{ $quarter }} quarter</span>
        @endif
        @endif
      </div>
    </div> -->
    <!-- @endif -->

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
              <button type="button" class="btn p-0 w-100 text-decoration-none" onclick="viewQuarterGrades('{{ $quarter }}')">
                <div class="card card-summary card-application h-100">
                  <div class="card-body text-center">
                    <i class="ri-file-check-line display-6 mb-2"></i>
                    <div>{{ $quarter }} Quarter</div>
                    <small>{{ $quarterData['grade_count'] }} subjects</small>
                  </div>
                </div>
              </button>
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

    <!-- GRADES MODAL -->
    <div class="modal fade" id="gradesModal" tabindex="-1" aria-labelledby="gradesModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="gradesModalLabel">
              <i class="ri-file-list-3-line me-2"></i><span id="modalQuarter"></span> Quarter Grades
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="gradesContent">
              <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Loading grades...</div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div id="gradesSummary" class="me-auto"></div>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </main>
</x-student-layout>
