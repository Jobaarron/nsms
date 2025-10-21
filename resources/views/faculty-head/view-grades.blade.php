<x-faculty-head-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">View Submitted Grades from Teachers</h1>
      <div class="text-muted">
        <i class="ri-file-list-3-line me-1"></i>All Submissions
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <!-- SUBMISSION STATISTICS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-status h-100">
          <div class="card-body text-center">
            <i class="ri-send-plane-line display-6 mb-2"></i>
            <div>Submitted</div>
            <h3>{{ $submissionsByStatus->get('submitted', collect())->count() }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-payment h-100">
          <div class="card-body text-center">
            <i class="ri-checkbox-circle-line display-6 mb-2"></i>
            <div>Approved</div>
            <h3>{{ $submissionsByStatus->get('approved', collect())->count() }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-schedule h-100">
          <div class="card-body text-center">
            <i class="ri-close-circle-line display-6 mb-2"></i>
            <div>Rejected</div>
            <h3>{{ $submissionsByStatus->get('rejected', collect())->count() }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card card-summary card-application h-100">
          <div class="card-body text-center">
            <i class="ri-file-list-line display-6 mb-2"></i>
            <div>Total</div>
            <h3>{{ $submissions->count() }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- FILTER TABS -->
    <div class="card mb-4">
      <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="submissionTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
              All Submissions
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="submitted-tab" data-bs-toggle="tab" data-bs-target="#submitted" type="button" role="tab">
              Submitted
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
              Approved
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
              Rejected
            </button>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content" id="submissionTabsContent">
          <!-- ALL SUBMISSIONS -->
          <div class="tab-pane fade show active" id="all" role="tabpanel">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Teacher</th>
                    <th>Subject</th>
                    <th>Class</th>
                    <th>Quarter</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($submissions as $submission)
                  <tr>
                    <td>{{ $submission->teacher->name }}</td>
                    <td>{{ $submission->subject->subject_name }}</td>
                    <td>{{ $submission->grade_level }} - {{ $submission->section }}</td>
                    <td>{{ $submission->quarter }}</td>
                    <td>{{ $submission->total_students }}</td>
                    <td>
                      @switch($submission->status)
                        @case('submitted')
                          <span class="badge bg-warning">Under Review</span>
                          @break
                        @case('approved')
                          <span class="badge bg-success">Approved</span>
                          @break
                        @case('rejected')
                          <span class="badge bg-danger">Rejected</span>
                          @break
                      @endswitch
                    </td>
                    <td>
                      @if($submission->submitted_at)
                        {{ $submission->submitted_at->format('M d, Y') }}
                      @else
                        <span class="text-muted">Not submitted</span>
                      @endif
                    </td>
                    <td>
                      <a href="{{ route('faculty-head.approve-grades') }}" class="btn btn-sm btn-outline-primary">
                        <i class="ri-eye-line me-1"></i>View Details
                      </a>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="8" class="text-center text-muted">No grade submissions found</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          
          <!-- Other tab contents would be similar with filtered data -->
        </div>
      </div>
    </div>
  </main>
</x-faculty-head-layout>

@push('scripts')
<script src="{{ asset('js/faculty-head-view-grades.js') }}"></script>
@endpush
