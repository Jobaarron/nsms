<x-discipline-layout>
    @vite('resources/css/index_discipline.css')
    @vite('resources/css/enhanced-dashboard.css')
    
    <div class="container-fluid">
        <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-discipline">
                        <i class="ri-shield-check-line me-2"></i>Discipline Dashboard
                    </h1>
                    <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name }}</p>
                    <small class="text-muted">
                        <i class="ri-time-line me-1"></i>
                        {{ now()->format('l, F j, Y g:i A') }}
                    </small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <!-- Year Filter -->
                    <div class="d-flex align-items-center gap-2">
                        <label for="yearFilter" class="form-label mb-0 text-muted small">Year:</label>
                        <select id="yearFilter" class="form-select form-select-sm" style="width: 120px;" onchange="applyYearFilter()">
                            <option value="all">All Years</option>
                            <option value="2026">2026</option>
                            <option value="2025" selected>2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                            <option value="2021">2021</option>
                        </select>
                    </div>
                    <!-- Notification Bell -->
                    <div class="position-relative" style="display: inline-block;">
                        <button class="btn btn-outline-success btn-sm" id="notificationBell" data-bs-toggle="modal" data-bs-target="#notificationsModal" title="Case Closed Violations with Approved Interventions (Updates every 5 seconds)">
                            <i class="ri-notification-3-line fs-5"></i>
                        </button>
                        <span class="badge rounded-pill bg-danger" id="notificationBadge">
                            0
                        </span>
                    </div>
                    <button class="btn btn-success btn-sm" onclick="openQuickActionModal()">
                        <i class="ri-add-circle-line me-1 me-sm-2"></i><span class="d-none d-sm-inline">Quick Actions</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        <!-- Enhanced Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-6 col-sm-6 col-md-6 col-lg-3">
                <div class="card border border-success h-100 shadow-sm hover-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-2 small">
                                    <i class="ri-user-3-line me-1"></i>Total Students
                                </p>
                                <h3 id="stat-total-students" class="mb-0 text-success fw-bold">{{ $stats['total_students'] ?? 0 }}</h3>
                                <small class="text-muted">
                                    <i class="ri-arrow-up-line text-success me-1"></i>
                                    Active enrollment
                                </small>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-circle p-3 ms-3">
                                <i class="ri-user-3-line text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-sm-6 col-md-6 col-lg-3">
                <div class="card border border-success h-100 shadow-sm hover-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-2 small">
                                    <i class="ri-flag-2-line me-1"></i>Total Violations
                                </p>
                                <h3 id="stat-total-violations" class="mb-0 text-danger fw-bold">{{ $stats['total_violations'] ?? 0 }}</h3>
                                <small class="text-muted">
                                    <i class="ri-information-line me-1"></i>
                                    All recorded incidents
                                </small>
                            </div>
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3 ms-3">
                                <i class="ri-flag-2-line text-danger fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-sm-6 col-md-6 col-lg-3">
                <div class="card border border-success h-100 shadow-sm hover-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-2 small">
                                    <i class="ri-error-warning-line me-1"></i>Risk Percentage
                                </p>
                                <h3 id="stat-risk-percentage" class="mb-0 text-warning fw-bold">
                                  @php
                                    $riskPercent = ($stats['total_students'] ?? 0) > 0
                                      ? round((($stats['major_violations'] ?? 0) / max(1, $stats['total_students'])) * 100, 1)
                                      : 0;
                                  @endphp
                                  {{ $riskPercent }}%
                                </h3>
                                <small class="text-muted">
                                    <i class="ri-alert-line text-warning me-1"></i>
                                    Students at risk
                                </small>
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3 ms-3">
                                <i class="ri-error-warning-line text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-sm-6 col-md-6 col-lg-3">
                <div class="card border border-success h-100 shadow-sm hover-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-2 small">
                                    <i class="ri-calendar-line me-1"></i>Pending Actions
                                </p>
                                <h3 id="stat-pending-actions" class="mb-0 text-primary fw-bold">{{ $stats['pending_violations'] ?? 0 }}</h3>
                                <small class="text-muted">
                                    <i class="ri-time-line text-primary me-1"></i>
                                    Requires attention
                                </small>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 ms-3">
                                <i class="ri-calendar-line text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

          <!-- PIE CHART AND BAR CHART BELOW, SIDE BY SIDE -->
        <div class="row mb-4 d-flex align-items-stretch">
          <div class="col-12 col-md-6 d-flex align-items-stretch">
            <div class="card h-100 border border-success rounded-3 shadow-sm h-100 w-100 d-flex flex-column" style="border-width:2px;">
              <div class="card-header bg-white border-0 pb-0">
                  <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0">Minor vs Major Violations</h5>
                  <select class="form-select form-select-sm" id="pieChartPeriod" onchange="loadViolationPieChart()" style="width: auto;">
                    <option value="all">All Time</option>
                    <option value="month" selected>This Month</option>
                    <option value="quarter">This Quarter</option>
                    <option value="year">This Year</option>
                  </select>
                </div>
              </div>
              <div class="card-body flex-grow-1 d-flex align-items-center justify-content-center">
                <canvas id="violationPieChart" style="max-height: 300px;"></canvas>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6 d-flex align-items-stretch">
            <div class="card h-100 border border-success rounded-3 shadow-sm h-100 w-100 d-flex flex-column" style="border-width:2px;">
              <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0">Violations Per Month</h5>
                  <select class="form-select form-select-sm" id="barChartPeriod" onchange="loadViolationBarChart()" style="width: auto;">
                    <option value="6months">Last 6 Months</option>
                    <option value="12months" selected>Last 12 Months</option>
                    <option value="24months">Last 24 Months</option>
                  </select>
                </div>
              </div>
              <div class="card-body flex-grow-1 d-flex align-items-center justify-content-center">
                <canvas id="violationBarChart" style="max-height: 300px;"></canvas>
              </div>
            </div>
          </div>
        </div>
        <!-- Case Status Overview and Violation Trends -->
        <div class="row mb-4 d-flex align-items-stretch">
          <div class="col-12 col-md-6 d-flex align-items-stretch">
            <div class="card h-100 border border-success rounded-3 shadow-sm h-100 w-100 d-flex flex-column" style="border-width:2px;">
              <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0">Case Status Overview</h5>
                  <select class="form-select form-select-sm" id="caseStatusPeriod" onchange="loadCaseStatusChart()" style="width: auto;">
                    <option value="all">All Time</option>
                    <option value="month" selected>This Month</option>
                    <option value="quarter">This Quarter</option>
                    <option value="year">This Year</option>
                  </select>
                </div>
              </div>
              <div class="card-body flex-grow-1 d-flex align-items-center justify-content-center">
                <canvas id="caseStatusPieChart" style="max-height: 300px;"></canvas>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6 d-flex align-items-stretch">
            <div class="card h-100 border border-success rounded-3 shadow-sm h-100 w-100 d-flex flex-column" style="border-width:2px;">
              <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0">
                    <i class="ri-line-chart-line text-primary me-2"></i>Violation Trends
                  </h5>
                  <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="violationTrendsPeriod" onchange="applyViolationTrendsFilter()">
                      <option value="3months">Last 3 Months</option>
                      <option value="6months">Last 6 Months</option>
                      <option value="12months" selected>Last 12 Months</option>
                    </select>
                    <select class="form-select form-select-sm" id="violationTrendsType" onchange="applyViolationTrendsFilter()">
                      <option value="line" selected>Line Chart</option>
                      <option value="bar">Bar Chart</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="card-body flex-grow-1 d-flex align-items-center justify-content-center">
                <canvas id="violationTrendsChart" style="max-height: 300px;"></canvas>
              </div>
            </div>
          </div>
        </div>

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
          // Chart data URLs
          window.violationStatsUrl = "{{ route('discipline.minor-major-violation-stats') }}";
          window.violationBarUrl = "{{ route('discipline.violation-bar-stats') }}";
          window.caseStatusStatsUrl = "{{ route('discipline.case-status-stats') }}";
          
          // Dynamic content URLs (you'll need to create these routes)
          window.recentViolationsUrl = "/discipline/recent-violations";
          window.pendingActionsUrl = "/discipline/pending-actions";
          window.criticalCasesUrl = "/discipline/critical-cases";
          window.violationTrendsUrl = "/discipline/violation-trends";
        </script>
        @vite(['resources/js/discipline_dashboard.js'])
        <script>
        // Charts will be loaded by discipline_dashboard.js
        document.addEventListener('DOMContentLoaded', function () {
          
          // Force all dropdowns to open downwards
          const dropdowns = document.querySelectorAll('.card-header .dropdown-toggle');
          dropdowns.forEach(dropdown => {
            dropdown.addEventListener('click', function(e) {
              // Ensure dropdown opens below the button
              const dropdownMenu = this.nextElementSibling;
              if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                // Force positioning
                setTimeout(() => {
                  dropdownMenu.style.top = '100%';
                  dropdownMenu.style.bottom = 'auto';
                  dropdownMenu.style.transform = 'translateY(0)';
                }, 10);
              }
            });
          });
        });

        // All chart and dashboard functionality moved to discipline_dashboard.js
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

        <!-- Enhanced Analytics Row -->
        <div class="row mb-4">
            <!-- Recent Violations -->
            <div class="col-lg-4 mb-4">
                <div class="card border border-success h-100">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-flag-2-line text-danger me-2"></i>Recent Violations
                            </h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true">
                                    <i class="ri-filter-3-line"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" onclick="applyViolationsFilter('today')">Today</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="applyViolationsFilter('week')">This Week</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="applyViolationsFilter('month')">This Month</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="recent-violations" class="activity-timeline">
                            <!-- Content will be loaded dynamically -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pending Actions -->
            <div class="col-lg-4 mb-4">
                <div class="card border border-success h-100">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-time-line text-warning me-2"></i>Pending Actions
                            </h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true">
                                    <i class="ri-filter-3-line"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" onclick="applyPendingFilter('high')">High Priority</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="applyPendingFilter('medium')">Medium Priority</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="applyPendingFilter('all')">All</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="pending-actions">
                            <!-- Content will be loaded dynamically -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Cases -->
            <div class="col-lg-4 mb-4">
                <div class="card border border-success h-100">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-alert-line text-info me-2"></i>Critical Cases
                            </h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true">
                                    <i class="ri-filter-3-line"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" onclick="applyCriticalCasesFilter('5')">Top 5</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="applyCriticalCasesFilter('10')">Top 10</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="applyCriticalCasesFilter('15')">Top 15</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="critical-cases-table">
                            <!-- Content will be loaded dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </div>


        </div>

        <!-- Filters Modal -->
        <div class="modal fade" id="filtersModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title">
                            <i class="ri-filter-3-line me-2"></i>Advanced Filters
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Global Filters -->
                            <div class="col-md-12 mb-3">
                                <div class="card border-success">
                                    <div class="card-header py-2 bg-success text-white">
                                        <h6 class="mb-0"><i class="ri-filter-3-line me-2"></i>Global Filters</h6>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label class="form-label small">School Year</label>
                                                <select class="form-select form-select-sm" id="schoolYearFilter">
                                                    <option value="all">All School Years</option>
                                                    <option value="2024-2025" selected>2024-2025</option>
                                                    <option value="2023-2024">2023-2024</option>
                                                    <option value="2022-2023">2022-2023</option>
                                                    <option value="2021-2022">2021-2022</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small">Calendar Year</label>
                                                <select class="form-select form-select-sm" id="modalYearFilter" onchange="syncYearFilters()">
                                                    <option value="all">All Years</option>
                                                    <option value="2026">2026</option>
                                                    <option value="2025" selected>2025</option>
                                                    <option value="2024">2024</option>
                                                    <option value="2023">2023</option>
                                                    <option value="2022">2022</option>
                                                    <option value="2021">2021</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Violations Filters -->
                            <div class="col-md-6">
                                <label class="form-label">Violations Date Range</label>
                                <select class="form-select" id="violationsDateRange">
                                    <option value="today">Today</option>
                                    <option value="week" selected>This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="quarter">This Quarter</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Violation Severity</label>
                                <select class="form-select" id="violationSeverity">
                                    <option value="all" selected>All Severities</option>
                                    <option value="minor">Minor</option>
                                    <option value="major">Major</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            
                            <!-- Pending Actions Filters -->
                            <div class="col-md-6">
                                <label class="form-label">Pending Actions Priority</label>
                                <select class="form-select" id="pendingPriority">
                                    <option value="all" selected>All Priorities</option>
                                    <option value="high">High Priority</option>
                                    <option value="medium">Medium Priority</option>
                                    <option value="low">Low Priority</option>
                                </select>
                            </div>
                            
                            <!-- Critical Cases Filters -->
                            <div class="col-md-6">
                                <label class="form-label">Critical Cases Limit</label>
                                <select class="form-select" id="criticalCasesLimit">
                                    <option value="5" selected>Top 5</option>
                                    <option value="10">Top 10</option>
                                    <option value="15">Top 15</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" onclick="applyAllFilters()" data-bs-dismiss="modal">Apply Filters</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Quick Action Modal -->
        <div class="modal fade" id="quickActionModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title">
                            <i class="ri-lightning-line me-2"></i>Quick Actions
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body text-center">
                                        <div class="bg-danger bg-opacity-10 rounded-circle p-3 mx-auto mb-3" style="width: fit-content;">
                                            <i class="ri-flag-2-line text-danger fs-4"></i>
                                        </div>
                                        <h6>Record New Violation</h6>
                                        <p class="text-muted small">Document a new disciplinary incident</p>
                                        <button class="btn btn-danger btn-sm" onclick="recordNewViolation()">
                                            <i class="ri-add-line me-1"></i>Record
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body text-center">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3 mx-auto mb-3" style="width: fit-content;">
                                            <i class="ri-user-search-line text-success fs-4"></i>
                                        </div>
                                        <h6>Student Lookup</h6>
                                        <p class="text-muted small">Search student disciplinary records</p>
                                        <button class="btn btn-success btn-sm" onclick="studentLookup()">
                                            <i class="ri-search-line me-1"></i>Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add custom styles for dashboard enhancements -->
        <style>
            .hover-card {
                transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            }
            
            .hover-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
            }
            
            .activity-timeline {
                max-height: 400px;
                overflow-y: auto;
            }
            
            .activity-item {
                padding: 12px 16px;
                border-left: 3px solid #e9ecef;
                margin-bottom: 8px;
                transition: all 0.2s ease;
            }
            
            .activity-item:hover {
                background-color: #f8f9fa;
                border-left-color: #007bff;
            }
            
            .activity-item.primary { border-left-color: #007bff; }
            .activity-item.success { border-left-color: #28a745; }
            .activity-item.danger { border-left-color: #dc3545; }
            .activity-item.warning { border-left-color: #ffc107; }
            
            .task-item {
                padding: 10px 16px;
                border-bottom: 1px solid #f0f0f0;
                transition: background-color 0.2s ease;
            }
            
            .task-item:hover {
                background-color: #f8f9fa;
            }
            
            .task-item:last-child {
                border-bottom: none;
            }
            
            .priority-high {
                border-left: 3px solid #dc3545;
            }
            
            .priority-medium {
                border-left: 3px solid #ffc107;
            }
            
            .priority-low {
                border-left: 3px solid #28a745;
            }
            
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .card {
                animation: fadeInUp 0.5s ease-out;
            }
            
            .card:nth-child(1) { animation-delay: 0.1s; }
            .card:nth-child(2) { animation-delay: 0.2s; }
            .card:nth-child(3) { animation-delay: 0.3s; }
            .card:nth-child(4) { animation-delay: 0.4s; }
            
            /* Enhanced content area styling */
            #recent-violations, #pending-actions, #critical-cases-table {
                max-height: 350px;
                min-height: 300px;
                overflow-y: auto;
                padding: 1rem;
            }
            
            .activity-item {
                padding: 0.75rem;
                margin-bottom: 0.5rem;
                border-radius: 0.375rem;
                background-color: #f8f9fa;
                border-left: 3px solid #dee2e6;
                transition: all 0.3s ease;
            }
            
            .activity-item:hover {
                background-color: #e9ecef;
                transform: translateX(2px);
            }
            
            .task-item {
                padding: 0.75rem;
                margin-bottom: 0.5rem;
                border-radius: 0.375rem;
                background-color: #f8f9fa;
                border-left: 3px solid #dee2e6;
                transition: all 0.3s ease;
            }
            
            .task-item:hover {
                background-color: #e9ecef;
                transform: translateX(2px);
            }
            
            /* Critical cases table styling */
            .table > :not(caption) > * > * {
                padding: 0.5rem 0.75rem;
            }
            
            /* Custom scrollbar */
            #recent-violations::-webkit-scrollbar, 
            #pending-actions::-webkit-scrollbar, 
            #critical-cases-table::-webkit-scrollbar {
                width: 6px;
            }
            
            #recent-violations::-webkit-scrollbar-track, 
            #pending-actions::-webkit-scrollbar-track, 
            #critical-cases-table::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 3px;
            }
            
            #recent-violations::-webkit-scrollbar-thumb, 
            #pending-actions::-webkit-scrollbar-thumb, 
            #critical-cases-table::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 3px;
            }
            
            #recent-violations::-webkit-scrollbar-thumb:hover, 
            #pending-actions::-webkit-scrollbar-thumb:hover, 
            #critical-cases-table::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
            }
            
            /* Fix dropdown positioning */
            .card-header {
                position: relative;
                z-index: 10;
            }
            
            .card-header .dropdown {
                position: relative;
            }
            
            .card-header .dropdown-menu {
                position: absolute !important;
                top: 100% !important;
                bottom: auto !important;
                right: 0;
                left: auto;
                z-index: 1050;
                min-width: 160px;
                margin: 0.125rem 0 0;
                transform: translateX(0) translateY(0) !important;
                will-change: transform;
            }
            
            .card-header .dropdown-menu-end {
                right: 0;
                left: auto;
            }
            
            /* Ensure proper card header alignment */
            .card-header .d-flex {
                align-items: center !important;
                width: 100%;
            }
            
            .card-header .card-title {
                flex-grow: 1;
                margin-bottom: 0 !important;
            }
            
            /* Style dropdown button */
            .card-header .dropdown .btn {
                border-radius: 0.375rem;
                font-size: 0.875rem;
                padding: 0.25rem 0.5rem;
                line-height: 1.5;
            }
            
            .card-header .dropdown .btn:focus {
                box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
            }
            
            /* Force dropdown to always open downwards */
            .card-header .dropdown[data-bs-popper] {
                position: static !important;
            }
            
            .card-header .dropdown .dropdown-menu.show {
                top: 100% !important;
                bottom: auto !important;
                transform: translate3d(0px, 0px, 0px) !important;
            }
            
            /* Override Bootstrap's automatic dropdown positioning */
            .dropdown-menu[data-bs-popper] {
                top: 100% !important;
                left: auto !important;
                right: 0 !important;
                bottom: auto !important;
                transform: translate3d(0px, 0px, 0px) !important;
            }
            
            /* Notification Bell Styling */
            #notificationBell {
                transition: all 0.3s ease;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0;
                position: relative;
            }
            
            #notificationBell:hover {
                background-color: #198754;
                border-color: #198754;
                color: white;
                transform: scale(1.1);
            }
            
            #notificationBell:hover .ri-notification-3-line {
                animation: bellRing 0.5s ease-in-out;
            }
            
            @keyframes bellRing {
                0%, 100% { transform: rotate(0deg); }
                10%, 30%, 50%, 70%, 90% { transform: rotate(-10deg); }
                20%, 40%, 60%, 80% { transform: rotate(10deg); }
            }
            
            /* Real-time notification animations */
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
                20%, 40%, 60%, 80% { transform: translateX(2px); }
            }
            
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.2); background-color: #ff4757; }
                100% { transform: scale(1); }
            }
            
            .shake {
                animation: shake 0.5s ease-in-out;
            }
            
            .pulse {
                animation: pulse 0.8s ease-in-out 2;
            }
            
            #notificationBadge {
                position: absolute;
                top: -8px;
                right: -8px;
                font-size: 0.65rem;
                padding: 0.25rem 0.45rem;
                min-width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: pulse 2s infinite;
                transform: none !important;
                z-index: 10;
            }
            
            @keyframes pulse {
                0%, 100% { 
                    opacity: 1; 
                    transform: scale(1);
                }
                50% { 
                    opacity: 0.8; 
                    transform: scale(1.1);
                }
            }
            
            /* Notification Card Hover */
            #notificationsModalBody .card {
                transition: all 0.3s ease;
            }
            
            #notificationsModalBody .card:hover {
                transform: translateX(5px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }
        </style>

    <!-- Toast container for notifications -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>

    <!-- Initialize dashboard enhancements -->
    <script>
        // Dashboard initialization with enhanced features
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize filters with default settings
            if (typeof applyFilters === 'function') {
                applyFilters();
            }
            
            // Setup keyboard shortcuts
            setupKeyboardShortcuts();

        });
        
        // Show notification function
        function showNotification(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container');
            if (!toastContainer) return;

            const toastId = 'toast-' + Date.now();
            const bgClass = type === 'success' ? 'bg-success' : 
                           type === 'warning' ? 'bg-warning' :
                           type === 'danger' ? 'bg-danger' : 'bg-info';

            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center ${bgClass} text-white" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;

            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
            toast.show();

            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }
        
        // Setup keyboard shortcuts for quick navigation
        function setupKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + N for new action
                if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                    e.preventDefault();
                    openQuickActionModal();
                }
                
                // Esc to close modals
                if (e.key === 'Escape') {
                    const modals = document.querySelectorAll('.modal.show');
                    modals.forEach(modal => {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        if (modalInstance) modalInstance.hide();
                    });
                }
            });
        }
        
        // Add tooltip initialization for better UX
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Quick action functions
        function openQuickActionModal() {
            const modal = new bootstrap.Modal(document.getElementById('quickActionModal'));
            modal.show();
        }

        function recordNewViolation() {
            window.location.href = '/discipline/violations';
        }

        function scheduleDisciplinaryMeeting() {
            showNotification('Disciplinary meeting scheduler coming soon!', 'info');
        }

        function generateReport() {
            showNotification('Report generator coming soon!', 'info');
        }

        function studentLookup() {
            window.location.href = '/discipline/students';
        }

        // Add fallback for missing showAlert function
        if (typeof showAlert === 'undefined') {
            window.showAlert = function(message, type, duration = 3000) {
                showNotification(message, type);
            };
        }
    </script>

    <!-- Notifications Modal -->
    <div class="modal fade" id="notificationsModal" tabindex="-1" aria-labelledby="notificationsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <div class="d-flex flex-column">
                        <h5 class="modal-title mb-0" id="notificationsModalLabel">
                            <i class="ri-notification-3-line me-2"></i>Case Closed Notifications
                        </h5>
                        <small class="text-white-50 mt-1">
                            <i class="ri-time-line me-1"></i>Real-time updates every 5 seconds
                        </small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="notificationsModalBody" style="max-height: 500px; overflow-y: auto;">
                    <div class="text-center py-4">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading notifications...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="markAllReadBtn">
                        <i class="ri-check-double-line me-1"></i>Mark All as Read
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    </div>
</x-discipline-layout>