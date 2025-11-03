<x-discipline-layout>
@vite(['resources/js/discipline_violations.js'])

      <style>
        body {
          background: #f7fafc;
        }
        .dashboard-kpi-card {
          transition: box-shadow 0.2s, transform 0.2s;
          box-shadow: 0 2px 8px 0 rgba(67,184,100,0.08);
          border-radius: 1.25rem !important;
          background: #fff;
          border-width: 2px !important;
          min-height: 180px;
        }
        .dashboard-kpi-card:hover {
          box-shadow: 0 6px 24px 0 rgba(67,184,100,0.18);
          transform: translateY(-2px) scale(1.03);
          z-index: 2;
        }
        .dashboard-kpi-icon {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 60px;
          height: 60px;
          border-radius: 50%;
          background: #eafaf1;
          margin-bottom: 0.5rem;
          font-size: 2.2rem;
        }
        .dashboard-kpi-label {
          font-weight: 600;
          font-size: 1.1rem;
          margin-bottom: 0.2rem;
        }
        .dashboard-kpi-value {
          font-size: 2rem;
          font-weight: 700;
          letter-spacing: 1px;
        }
      </style>
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



        <!-- SUMMARY CARDS ROW -->
        <div class="row g-3 mb-2 align-items-stretch">
          <div class="col-12 col-md-3">
              <div class="dashboard-kpi-card border border-success">
              <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                <span class="dashboard-kpi-icon" style="color:#43b864;"><i class="ri-user-3-line"></i></span>
                <div class="dashboard-kpi-label" style="color:#43b864;">Total Students</div>
                <div class="dashboard-kpi-value">{{ $stats['total_students'] ?? 0 }}</div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-3">
              <div class="dashboard-kpi-card border border-success">
              <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                <span class="dashboard-kpi-icon" style="color:#217a36;"><i class="ri-flag-2-line"></i></span>
                <div class="dashboard-kpi-label" style="color:#217a36;">Total Violations</div>
                <div class="dashboard-kpi-value">{{ $stats['total_violations'] ?? 0 }}</div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-3">
              <div class="dashboard-kpi-card border border-success">
              <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                <span class="dashboard-kpi-icon" style="color:#43b864;"><i class="ri-error-warning-line"></i></span>
                <div class="dashboard-kpi-label" style="color:#43b864;">Student Risk Percentage</div>
                <div class="dashboard-kpi-value">
                  @php
                    $riskPercent = ($stats['total_students'] ?? 0) > 0
                      ? round((($stats['major_violations'] ?? 0) / max(1, $stats['total_students'])) * 100, 1)
                      : 0;
                  @endphp
                  {{ $riskPercent }}%
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-3">
              <div class="dashboard-kpi-card border border-success">
              <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                <span class="dashboard-kpi-icon" style="color:#1ba7c3;"><i class="ri-calendar-line"></i></span>
                  <div class="dashboard-kpi-label" style="color:#1ba7c3;">Pending Disciplinary Actions</div>
                  <div class="dashboard-kpi-value">{{ $stats['pending_violations'] ?? 0 }}</div>
              </div>
            </div>
          </div>
        </div>

          <!-- PIE CHART AND BAR CHART BELOW, SIDE BY SIDE -->
        <div class="row mb-4 d-flex align-items-stretch">
          <div class="col-12 col-md-6 d-flex align-items-stretch">
            <div class="card h-100 border border-success rounded-3 shadow-sm h-100 w-100 d-flex flex-column" style="border-width:2px;">
              <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0 text-center">Minor vs Major Violations</h5>
              </div>
              <div class="card-body flex-grow-1 d-flex align-items-center justify-content-center">
                <canvas id="violationPieChart" style="max-height: 300px;"></canvas>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6 d-flex align-items-stretch">
            <div class="card h-100 border border-success rounded-3 shadow-sm h-100 w-100 d-flex flex-column" style="border-width:2px;">
              <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0 text-center">Violations Per Month</h5>
              </div>
              <div class="card-body flex-grow-1 d-flex align-items-center justify-content-center">
                <canvas id="violationBarChart" style="max-height: 300px;"></canvas>
              </div>
            </div>
          </div>
        </div>
        <!-- Pie Chart for Pending, On Going, Completed Cases -->
        <div class="row mb-4 d-flex align-items-stretch">
          <div class="col-12 col-md-6 d-flex align-items-stretch">
            <div class="card h-100 border border-success rounded-3 shadow-sm h-100 w-100 d-flex flex-column" style="border-width:2px;">
              <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0 text-center">Case Status Overview</h5>
              </div>
              <div class="card-body flex-grow-1 d-flex align-items-center justify-content-center">
                <canvas id="caseStatusPieChart" style="max-height: 300px;"></canvas>
              </div>
            </div>
          </div>
        </div>

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
          window.violationStatsUrl = "{{ route('discipline.minor-major-violation-stats') }}";
          window.violationBarUrl = "{{ route('discipline.violation-bar-stats') }}";
          window.caseStatusStatsUrl = "{{ route('discipline.case-status-stats') }}";
        </script>
        @vite(['resources/js/discipline_dashboard.js'])
        <script>
        document.addEventListener('DOMContentLoaded', function () {
          var caseStatusCanvas = document.getElementById('caseStatusPieChart');
          if (caseStatusCanvas) {
            fetch(window.caseStatusStatsUrl)
              .then(response => response.json())
              .then(data => {
                if (typeof data.pending === 'undefined' || typeof data.ongoing === 'undefined' || typeof data.completed === 'undefined') {
                  throw new Error('Invalid data: ' + JSON.stringify(data));
                }
                new Chart(caseStatusCanvas.getContext('2d'), {
                  type: 'pie',
                  data: {
                    labels: ['Pending', 'On Going', 'Completed'],
                    datasets: [{
                      data: [data.pending, data.ongoing, data.completed],
                      backgroundColor: ['#ffc107', '#36a2eb', '#4caf50'],
                      borderWidth: 2
                    }]
                  },
                  options: {
                    responsive: true,
                    plugins: {
                      legend: {
                        display: true,
                        position: 'bottom'
                      }
                    }
                  }
                });
              })
              .catch(error => {
                console.error('Case status pie chart error:', error);
                var chartContainer = caseStatusCanvas.parentElement;
                var errorDiv = document.createElement('div');
                errorDiv.className = 'text-danger text-center mt-3';
               // errorDiv.innerText = 'Failed to load case status data.';
                chartContainer.appendChild(errorDiv);
              });
          }
        });
        </script>
        @endpush

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