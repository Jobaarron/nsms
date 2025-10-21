<x-discipline-layout>
@vite(['resources/js/discipline_violations.js'])

      <main class="col-12 col-md-10 px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="section-title mb-0">Discipline Dashboard</h1>
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
          <div class="col-12 col-md-3">
            <div class="card card-summary card-violations h-100">
              <div class="card-body text-center">
                <i class="ri-alert-line display-6 mb-2"></i>
                <div>Violations This Month</div>
                <h3>{{ $stats['violations_this_month'] ?? 0 }}</h3>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-3">
            <div class="card card-summary card-counsel h-100">
              <div class="card-body text-center">
                <i class="ri-flag-2-line display-6 mb-2"></i>
                <div>Total Violations</div>
                <h3>{{ $stats['total_violations'] ?? 0 }}</h3>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-3">
            <div class="card card-summary card-violations h-100">
              <div class="card-body text-center">
                <i class="ri-error-warning-line display-6 mb-2"></i>
                <div>Major Violations</div>
                <h3>{{ $stats['major_violations'] ?? 0 }}</h3>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-3">
            <div class="card card-summary card-weekly h-100">
              <div class="card-body text-center">
                <i class="ri-calendar-line display-6 mb-2"></i>
                <div>Weekly Violations</div>
                <h3>{{ $stats['weekly_violations'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
        </div>

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
                <i class="ri-usdo er-search-line display-4 text-secondary mb-3"></i>
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

        <!-- WEEKLY VIOLATIONS TABLE -->
        <h4 class="section-title">Weekly Violations</h4>
        <div class="table-responsive mb-5">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>Student</th>
                <th>Violation</th>
                <th>Date</th>
                <th>Severity</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($weeklyViolations as $index => $violation)
              <tr>
                <td>{{ $violation->student->first_name }} {{ $violation->student->last_name }}</td>
                <td>{{ $violation->title }}</td>
                <td>{{ $violation->violation_date->format('M j, Y') }}</td>
                <td>
                  <span class="badge bg-{{ $violation->severity_color }} text-white">
                    {{ ucfirst($violation->severity) }}
                  </span>
                </td>
                <td class="text-center">
                  <button type="button" class="btn btn-sm btn-outline-primary"
                          onclick="viewViolation({{ $violation->id }})"
                          title="View Details">
                    <i class="ri-eye-line"></i>
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted">No weekly violations found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </main>

  <!-- VIEW VIOLATION MODAL -->
  <div class="modal fade" id="viewViolationModal" tabindex="-1" aria-labelledby="viewViolationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewViolationModalLabel">Violation Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="viewViolationModalBody">
          <!-- Content will be loaded dynamically -->
        </div>
      </div>
    </div>
  </div>
</x-discipline-layout>