<x-discipline-layout>
    @vite(['resources/css/index_discipline.css'])

    <main class="col-12 col-md-10 px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="section-title mb-0">Discipline Portal Dashboard</h1>
          <div class="text-muted">
            <i class="ri-calendar-line me-1"></i>{{ now()->format('F j, Y') }}
          </div>
        </div>

        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        <!-- SUMMARY CARDS -->
        <div class="row g-3 mb-5">
          <div class="col-6 col-lg-2">
            <div class="card card-summary card-students h-100">
              <div class="card-body text-center">
                <i class="ri-team-line display-6 mb-2"></i>
                <div>Total Students</div>
                <h3>{{ $stats['total_students'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
          {{-- <div class="col-6 col-lg-2">
            <div class="card card-summary card-facerec h-100">
              <div class="card-body text-center">
                <i class="ri-scan-2-line display-6 mb-2"></i>
                <div>Faces Registered</div>
                <h3>{{ $stats['faces_registered'] ?? 0 }}</h3>
              </div>
            </div>
          </div> --}}
          <div class="col-6 col-lg-2">
            <div class="card card-summary card-violations h-100">
              <div class="card-body text-center">
                <i class="ri-alert-line display-6 mb-2"></i>
                <div>Violations This Month</div>
                <h3>{{ $stats['violations_this_month'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-2">
            <div class="card card-summary card-counsel h-100">
              <div class="card-body text-center">
                <i class="ri-flag-2-line display-6 mb-2"></i>
                <div>Total Violations</div>
                <h3>{{ $stats['total_violations'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-2">
            <div class="card card-summary card-counsel h-100">
              <div class="card-body text-center">
                <i class="ri-time-line display-6 mb-2"></i>
                <div>Pending Violations</div>
                <h3>{{ $stats['pending_violations'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-2">
            <div class="card card-summary card-reports h-100">
              <div class="card-body text-center">
                <i class="ri-calendar-check-line display-6 mb-2"></i>
                <div>Today's Violations</div>
                <h3>{{ $stats['violations_today'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-2">
            <div class="card card-summary card-violations h-100">
              <div class="card-body text-center">
                <i class="ri-error-warning-line display-6 mb-2"></i>
                <div>Major Violations</div>
                <h3>{{ $stats['major_violations'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-2">
            <div class="card card-summary card-severe h-100">
              <div class="card-body text-center">
                <i class="ri-alarm-warning-line display-6 mb-2"></i>
                <div>Severe Violations</div>
                <h3>{{ $stats['severe_violations'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
        </div>

        <!-- FACIAL RECOGNITION PANEL -->
        {{-- <h4 class="section-title">Facial Recognition</h4>
        <div class="card mb-5">
          <div class="card-body">
            <div class="row">
              <div class="col-md-8">
                <div class="border rounded p-3 text-center" style="height:300px; background:#fff;">
                  <i class="ri-camera-line display-1 text-secondary"></i>
                  <p class="text-muted">Camera feed placeholder</p>
                  <small class="text-muted">Facial recognition system will be integrated here</small>
                </div>
              </div>
              <div class="col-md-4">
                <button class="btn btn-outline-primary w-100 mb-3" disabled>
                  <i class="ri-user-add-line me-2"></i>Enroll New Face
                  <small class="d-block text-muted">Coming Soon</small>
                </button>
                <button class="btn btn-outline-primary w-100" disabled>
                  <i class="ri-file-upload-line me-2"></i>Import Face Data
                  <small class="d-block text-muted">Coming Soon</small>
                </button>
              </div>
            </div>
          </div>
        </div> --}}

        <!-- QUICK ACTIONS -->
        {{-- <h4 class="section-title">Quick Actions</h4>
        <div class="row g-3 mb-5">
          @can('create_guidance_accounts')
          <div class="col-md-4">
            <div class="card h-100">
              <div class="card-body text-center">
                <i class="ri-user-add-line display-4 text-primary mb-3"></i>
                <h5>Create New Account</h5>
                <p class="text-muted">Add new guidance counselor, discipline officer, or security guard</p>
                <a href="{{ route('guidance.create-account') }}" class="btn btn-outline-primary">
                  <i class="ri-user-add-line me-2"></i>Create Account
                </a>
              </div>
            </div>
          </div>
          @endcan --}}

          {{-- <div class="col-md-4">
            <div class="card h-100">
              <div class="card-body text-center">
                <i class="ri-user-search-line display-4 text-secondary mb-3"></i>
                <h5>Student Lookup</h5>
                <p class="text-muted">Search and view student profiles and records</p>
                <button class="btn btn-outline-secondary" disabled>
                  <i class="ri-user-search-line me-2"></i>Coming Soon
                </button>
              </div>
            </div>
          </div> --}}

          {{-- <div class="col-md-4">
            <div class="card h-100">
              <div class="card-body text-center">
                <i class="ri-file-text-line display-4 text-info mb-3"></i>
                <h5>Generate Report</h5>
                <p class="text-muted">Create comprehensive reports and analytics</p>
                <button class="btn btn-outline-info" disabled>
                  <i class="ri-file-text-line me-2"></i>Coming Soon
                </button>
              </div>
            </div>
          </div>
        </div> --}}

        {{-- <!-- VIOLATIONS TABLE -->
        <h4 class="section-title">Recent Violations</h4>
        <div class="table-responsive mb-5">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Student</th>
                <th>Violation</th>
                <th>Date</th>
                <th>Severity</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>John Smith</td>
                <td>Late to class</td>
                <td>Jul 12, 2024</td>
                <td><span class="badge bg-warning text-dark">Minor</span></td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-primary me-1">
                    <i class="ri-eye-line"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-primary">
                    <i class="ri-edit-line"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div> --}}

        <!-- COUNSELING & CAREER ADVICE -->
        {{-- <div class="row mb-5">
          <div class="col-md-6">
            <h4 class="section-title">Upcoming Counseling</h4>
            <ul class="list-group">
              <li class="list-group-item">
                <i class="ri-calendar-event-line me-2"></i> Jul 20: Academic Planning
              </li>
              <li class="list-group-item">
                <i class="ri-calendar-event-line me-2"></i> Jul 25: Peer Counseling
              </li>
            </ul>
          </div>
          <div class="col-md-6">
            <h4 class="section-title">Career Advice Slots</h4>
            <ul class="list-group">
              <li class="list-group-item">
                <i class="ri-calendar-event-line me-2"></i> Jul 22: Resume Workshop
              </li>
              <li class="list-group-item">
                <i class="ri-calendar-event-line me-2"></i> Jul 30: Interview Prep
              </li>
            </ul>
          </div>
        </div> --}}

        <!-- ANALYTICS PLACEHOLDER -->
        {{-- <h4 class="section-title">Analytics & Reports</h4>
        <div class="border rounded p-4 text-center mb-5" style="background:#fff; height:250px;">
          <i class="ri-chart-pie-line display-1 text-secondary"></i>
          <p class="text-muted">Charts and export tools go here</p>
        </div> --}}

    </main>
</x-discipline-layout>