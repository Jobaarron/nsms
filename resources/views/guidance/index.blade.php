<x-guidance-layout>
    @vite('resources/css/index_guidance.css')
    @vite('resources/css/enhanced-dashboard.css')
    
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-guidance">
                        <i class="ri-dashboard-line me-2"></i>Guidance Dashboard
                    </h1>
                    <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name }}</p>
                    <small class="text-muted">
                        <i class="ri-calendar-line me-1"></i>{{ now()->format('l, F j, Y') }}
                        <span class="mx-2">•</span>
                        <i class="ri-time-line me-1"></i>{{ now()->format('g:i A') }}
                    </small>
                </div>
                <div class="d-flex flex-column flex-sm-row gap-2 align-items-start align-items-sm-center">
                    <!-- Auto-refresh toggle -->
                    <div class="form-check form-switch me-3 d-none d-sm-block">
                        <input class="form-check-input" type="checkbox" id="autoRefresh">
                        <label class="form-check-label small text-muted" for="autoRefresh">
                            Auto-refresh
                        </label>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-success btn-sm" onclick="refreshDashboard()">
                            <i class="ri-refresh-line me-1 me-sm-2"></i><span class="d-none d-sm-inline">Refresh</span>
                        </button>
                        <button class="btn btn-success btn-sm" onclick="openQuickActionModal()">
                            <i class="ri-add-line me-1 me-sm-2"></i><span class="d-none d-sm-inline">Quick Action</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>



        @php
            // Ensure all required stats have default values
            $stats = $stats ?? [];
            $stats = array_merge([
                'total_students' => 0,
                'active_case_meetings' => 0,
                'completed_case_meetings' => 0,
                'scheduled_counseling' => 0,
                'completed_counseling_sessions' => 0,
                'students_with_disciplinary_record' => 0,
                'pending_cases' => 0,
                'student_growth' => 0,
                'student_target' => 1000
            ], $stats);
        @endphp

        <!-- Enhanced Statistics Cards -->
        <div class="row mb-4">
            <div class="col-6 col-sm-6 col-md-6 col-xl-3 mb-3">
                <div class="card border border-success h-100 shadow-sm hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="ri-user-3-line fs-2 text-success"></i>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success small">
                                <i class="ri-trending-up-line me-1"></i>Active
                            </span>
                        </div>
                        <div class="fw-bold fs-3 text-success mb-1">{{ number_format($stats['total_students'] ?? 0) }}</div>
                        <div class="text-muted small mb-2">Total Students</div>
                        <div class="progress" style="height: 4px;">
                            @php
                                $student_progress = $stats['total_students'] > 0 ? min(100, ($stats['total_students'] / ($stats['student_target'] ?? 1000)) * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-success" style="width: {{ $student_progress }}%"></div>
                        </div>
                        <small class="text-success mt-1">
                            @if(($stats['student_growth'] ?? 0) >= 0)
                                <i class="ri-arrow-up-line"></i> {{ $stats['student_growth'] ?? 0 }}% from last month
                            @else
                                <i class="ri-arrow-down-line"></i> {{ abs($stats['student_growth'] ?? 0) }}% from last month
                            @endif
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-sm-6 col-md-6 col-xl-3 mb-3">
                <div class="card border border-success h-100 shadow-sm hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="ri-calendar-event-line fs-2 text-warning"></i>
                            </div>
                            <span class="badge bg-warning bg-opacity-10 text-warning small">
                                <i class="ri-time-line me-1"></i>Pending
                            </span>
                        </div>
                        <div class="fw-bold fs-3 text-warning mb-1">{{ $stats['active_case_meetings'] ?? 0 }}</div>
                        <div class="text-muted small mb-2">Active Case Meetings</div>
                        <div class="progress" style="height: 4px;">
                            @php
                                $active_meetings = $stats['active_case_meetings'] ?? 0;
                                $completed_meetings = $stats['completed_case_meetings'] ?? 0;
                                $total_meetings = $active_meetings + $completed_meetings;
                                $case_progress = $total_meetings > 0 ? ($completed_meetings / $total_meetings) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-warning" style="width: {{ $case_progress }}%"></div>
                        </div>
                        <small class="text-muted mt-1">
                            <i class="ri-calendar-line"></i> {{ $stats['pending_cases'] ?? 0 }} pending cases
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-sm-6 col-md-6 col-xl-3 mb-3">
                <div class="card border border-success h-100 shadow-sm hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="ri-heart-pulse-line fs-2 text-success"></i>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success small">
                                <i class="ri-check-line me-1"></i>Scheduled
                            </span>
                        </div>
                        <div class="fw-bold fs-3 text-success mb-1">{{ $stats['scheduled_counseling'] ?? 0 }}</div>
                        <div class="text-muted small mb-2">Scheduled Counseling</div>
                        <div class="progress" style="height: 4px;">
                            @php
                                $counseling_progress = ($stats['scheduled_counseling'] ?? 0) > 0 
                                    ? (($stats['completed_counseling_sessions'] ?? 0) / ($stats['scheduled_counseling'] ?? 1)) * 100 
                                    : 0;
                            @endphp
                            <div class="progress-bar bg-success" style="width: {{ min(100, $counseling_progress) }}%"></div>
                        </div>
                        <small class="text-info mt-1">
                            <i class="ri-calendar-check-line"></i> {{ $stats['completed_counseling_sessions'] ?? 0 }} completed
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-sm-6 col-md-6 col-xl-3 mb-3">
                <div class="card border border-success h-100 shadow-sm hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                                <i class="ri-error-warning-line fs-2 text-danger"></i>
                            </div>
                            <span class="badge bg-danger bg-opacity-10 text-danger small">
                                <i class="ri-alert-line me-1"></i>Monitor
                            </span>
                        </div>
                        <div class="fw-bold fs-3 text-danger mb-1">{{ $stats['students_with_disciplinary_record'] ?? 0 }}</div>
                        <div class="text-muted small mb-2">Students with Disciplinary Record</div>
                        <div class="progress" style="height: 4px;">
                            @php
                                $percentage = $stats['total_students'] > 0 ? round(($stats['students_with_disciplinary_record'] / $stats['total_students']) * 100, 1) : 0;
                            @endphp
                            <div class="progress-bar bg-danger" style="width: {{ $percentage }}%"></div>
                        </div>
                        <small class="text-muted mt-1">
                            <i class="ri-percent-line"></i> {{ $percentage }}% of total students
                        </small>
                    </div>
                </div>
            </div>
        </div>

       
            <!-- Pie Chart for Case Statuses and Bar Chart for Closed Cases -->
            <div class="row mb-4 d-flex align-items-stretch">
                <div class="col-lg-6 col-md-6 d-flex align-items-stretch">
                    <div class="card border border-success h-100 w-100 d-flex flex-column">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title mb-0">Case Status Overview</h5>
                            </div>
                            <!-- Inline Filter -->
                            <select class="form-select form-select-sm" id="caseStatusPeriod" onchange="applyCaseStatusFilter()">
                                <option value="month" selected>This Month</option>
                                <option value="quarter">This Quarter</option>
                                <option value="year">This Year</option>
                                <option value="all">All Time</option>
                            </select>
                        </div>
                        <div class="card-body flex-grow-1 d-flex align-items-center justify-content-center">
                            <canvas id="caseStatusPieChart" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 d-flex align-items-stretch">
                    <div class="card border border-success h-100 w-100 d-flex flex-column">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title mb-0">Closed Cases</h5>
                            </div>
                            <!-- Inline Filter -->
                            <div class="row g-2">
                                <div class="col-8">
                                    <select class="form-select form-select-sm" id="closedCasesPeriod" onchange="applyClosedCasesFilter()">
                                        <option value="6months" selected>Last 6 Months</option>
                                        <option value="12months">Last 12 Months</option>
                                        <option value="2years">Last 2 Years</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <select class="form-select form-select-sm" id="closedCasesView" onchange="applyClosedCasesFilter()">
                                        <option value="monthly" selected>Monthly</option>
                                        <option value="weekly">Weekly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body flex-grow-1 d-flex align-items-center justify-content-center">
                            <canvas id="closedCasesBarChart" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4 d-flex align-items-stretch">
                <div class="col-lg-6 col-md-8 d-flex align-items-stretch">
                    <div class="card border border-success h-100 w-100 d-flex flex-column">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title mb-0">Counseling Sessions</h5>
                            </div>
                            <!-- Inline Filter -->
                            <div class="row g-2">
                                <div class="col-8">
                                    <select class="form-select form-select-sm" id="counselingSessionsPeriod" onchange="applyCounselingSessionsFilter()">
                                        <option value="6months" selected>Last 6 Months</option>
                                        <option value="12months">Last 12 Months</option>
                                        <option value="2years">Last 2 Years</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <select class="form-select form-select-sm" id="counselingSessionsStatus" onchange="applyCounselingSessionsFilter()">
                                        <option value="all" selected>All Status</option>
                                        <option value="completed">Completed</option>
                                        <option value="scheduled">Scheduled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body flex-grow-1 d-flex align-items-center justify-content-center">
                            <canvas id="counselingSessionsBarChart" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-8 d-flex align-items-stretch">
                    <div class="card border border-success h-100 w-100 d-flex flex-column">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title mb-0">Students vs Disciplinary Records</h5>
                            </div>
                            <!-- Inline Filter -->
                            <div class="row g-2">
                                <div class="col-6">
                                    <select class="form-select form-select-sm" id="disciplineStatsPeriod" onchange="applyDisciplineStatsFilter()">
                                        <option value="5years" selected>Last 5 Years</option>
                                        <option value="3years">Last 3 Years</option>
                                        <option value="10years">Last 10 Years</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select class="form-select form-select-sm" id="disciplineStatsView" onchange="applyDisciplineStatsFilter()">
                                        <option value="comparison" selected>Comparison</option>
                                        <option value="percentage">Percentage</option>
                                        <option value="trends">Trends</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body flex-grow-1 d-flex align-items-center justify-content-center">
                            <canvas id="disciplineVsTotalHistogram" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <!-- Weekly Violation List table is now loaded by guidance-dashboard.js -->

        <!-- Enhanced Analytics Row -->
        <div class="row mb-4">
            <!-- Recent Activities -->
            <div class="col-lg-4 mb-4">
                <div class="card border border-success h-100">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">
                                <i class="ri-time-line me-2 text-success"></i>Recent Activities
                            </h5>
                        </div>
                        <!-- Inline Filter Controls -->
                        <div class="row g-2">
                            <div class="col-6">
                                <select class="form-select form-select-sm" id="activitiesDateRange" onchange="applyActivitiesFilter()">
                                    <option value="today">Today</option>
                                    <option value="week" selected>This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="all">All Time</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <select class="form-select form-select-sm" id="activitiesType" onchange="applyActivitiesFilter()">
                                    <option value="all">All Types</option>
                                    <option value="case_meeting">Case Meetings</option>
                                    <option value="counseling">Counseling</option>
                                    <option value="violation">Violations</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="recent-activities" class="activity-timeline">
                            <div class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="mt-2 small text-muted">Loading activities...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Upcoming Tasks -->
            <div class="col-lg-4 mb-4">
                <div class="card border border-success h-100">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">
                                <i class="ri-task-line me-2 text-warning"></i>Upcoming Tasks
                            </h5>
                            <span class="badge bg-warning bg-opacity-10 text-warning" id="task-count">0</span>
                        </div>
                        <!-- Inline Filter Controls -->
                        <div class="row g-2">
                            <div class="col-6">
                                <select class="form-select form-select-sm" id="tasksDateRange" onchange="applyTasksFilter()">
                                    <option value="today">Today</option>
                                    <option value="week" selected>This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="overdue">Overdue</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <select class="form-select form-select-sm" id="tasksPriority" onchange="applyTasksFilter()">
                                    <option value="all">All Priority</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="upcoming-tasks">
                            <div class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-warning" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="mt-2 small text-muted">Loading tasks...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Cases -->
            <div class="col-lg-4 mb-4">
                <div class="card border border-success h-100">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">
                                <i class="ri-award-line me-2 text-success"></i>Top Cases
                            </h5>
                            <small class="text-muted">Most frequent</small>
                        </div>
                        <!-- Inline Filter Controls -->
                        <div class="row g-2">
                            <div class="col-6">
                                <select class="form-select form-select-sm" id="topCasesDateRange" onchange="applyTopCasesFilter()">
                                    <option value="month" selected>This Month</option>
                                    <option value="quarter">This Quarter</option>
                                    <option value="year">This Year</option>
                                    <option value="all">All Time</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <select class="form-select form-select-sm" id="topCasesLimit" onchange="applyTopCasesFilter()">
                                    <option value="5" selected>Top 5</option>
                                    <option value="10">Top 10</option>
                                    <option value="15">Top 15</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="top-cases-table">
                            <div class="text-center py-2">
                                <div class="spinner-border spinner-border-sm text-success" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="mt-2 small text-muted">Loading cases...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Analytics Row -->
        <div class="row mb-4">
            <!-- Violation Trends -->
            <div class="col-lg-8 mb-4">
                <div class="card border border-success">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">
                                <i class="ri-line-chart-line me-2 text-success"></i>Violation Trends
                            </h5>
                        </div>
                        <!-- Enhanced Filter Controls -->
                        <div class="row g-2 align-items-center">
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Time Period</label>
                                <select class="form-select form-select-sm" id="violationTrendsPeriod" onchange="applyViolationTrendsFilter()">
                                    <option value="3months">3 Months</option>
                                    <option value="6months">6 Months</option>
                                    <option value="12months" selected>12 Months</option>
                                    <option value="2years">2 Years</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Chart Type</label>
                                <select class="form-select form-select-sm" id="violationTrendsType" onchange="applyViolationTrendsFilter()">
                                    <option value="line" selected>Line Chart</option>
                                    <option value="bar">Bar Chart</option>
                                    <option value="area">Area Chart</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Severity</label>
                                <select class="form-select form-select-sm" id="violationSeverity" onchange="applyViolationTrendsFilter()">
                                    <option value="all" selected>All Severity</option>
                                    <option value="minor">Minor</option>
                                    <option value="major">Major</option>
                                    <option value="severe">Severe</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Group By</label>
                                <select class="form-select form-select-sm" id="violationGroupBy" onchange="applyViolationTrendsFilter()">
                                    <option value="month" selected>Monthly</option>
                                    <option value="week">Weekly</option>
                                    <option value="day">Daily</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="violationTrendsChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Counseling Effectiveness -->
            <div class="col-lg-4 mb-4">
                <div class="card border border-success">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">
                                <i class="ri-heart-pulse-line me-2 text-success"></i>Counseling Effectiveness
                            </h5>
                        </div>
                        <!-- Inline Filter Controls -->
                        <div class="row g-2">
                            <div class="col-6">
                                <select class="form-select form-select-sm" id="counselingPeriod" onchange="applyCounselingEffectivenessFilter()">
                                    <option value="month" selected>This Month</option>
                                    <option value="quarter">This Quarter</option>
                                    <option value="year">This Year</option>
                                    <option value="all">All Time</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <select class="form-select form-select-sm" id="counselingCounselor" onchange="applyCounselingEffectivenessFilter()">
                                    <option value="all" selected>All Counselors</option>
                                    <!-- Options will be populated dynamically -->
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="counselingEffectivenessChart" style="max-height: 250px;"></canvas>
                        <div class="mt-3 text-center">
                            <div class="effectiveness-rate" id="effectiveness-rate">
                                <div class="fw-bold fs-4 text-success">--</div>
                                <small class="text-muted">Success Rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- Quick Actions and Today's Schedule (Commented Out)
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="scheduleNewCaseMeeting()">
                            <i class="ri-calendar-event-line me-2"></i>Schedule Case Meeting
                        </button>
                        <button class="btn btn-outline-success" onclick="scheduleNewCounseling()">
                            <i class="ri-heart-pulse-line me-2"></i>Schedule Counseling
                        </button>
                        <button class="btn btn-outline-info" onclick="scheduleHouseVisit()">
                            <i class="ri-home-heart-line me-2"></i>Schedule House Visit
                        </button>
                        <button class="btn btn-outline-warning" onclick="createCaseSummary()">
                            <i class="ri-file-text-line me-2"></i>Create Case Summary
                        </button>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Today's Schedule</h5>
                </div>
                <div class="card-body">
                    <div id="todays-schedule"></div>
                </div>
            </div>
        </div>
        -->

                </div>

        <!-- Filters Modal -->
        <div class="modal fade" id="filtersModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title">
                                <i class="ri-filter-3-line me-2 text-success"></i>Dashboard Filters
                            </h5>
                            <p class="text-muted small mb-0">Customize your dashboard view</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Quick Filter Buttons -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Quick Filters</h6>
                            <div class="btn-group-toggle d-flex flex-wrap gap-2">
                                <button class="btn btn-outline-success btn-sm" onclick="applyQuickFilter('today')">
                                    <i class="ri-calendar-line me-1"></i>Today
                                </button>
                                <button class="btn btn-outline-success btn-sm active" onclick="applyQuickFilter('week')">
                                    <i class="ri-calendar-week-line me-1"></i>This Week
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="applyQuickFilter('month')">
                                    <i class="ri-calendar-month-line me-1"></i>This Month
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="applyQuickFilter('high_priority')">
                                    <i class="ri-alert-line me-1"></i>High Priority
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="applyQuickFilter('overdue')">
                                    <i class="ri-time-line me-1"></i>Overdue Tasks
                                </button>
                            </div>
                        </div>

                        <!-- Advanced Filters -->
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <h6 class="fw-bold mb-3">Date & Time</h6>
                                <div class="mb-3">
                                    <label class="form-label">Date Range</label>
                                    <select class="form-select" id="modalDateRange">
                                        <option value="all">All Time</option>
                                        <option value="today">Today</option>
                                        <option value="week" selected>This Week</option>
                                        <option value="month">This Month</option>
                                        <option value="quarter">This Quarter</option>
                                        <option value="year">This Year</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Time of Day</label>
                                    <select class="form-select" id="timeFilter">
                                        <option value="all">All Day</option>
                                        <option value="morning">Morning (6AM-12PM)</option>
                                        <option value="afternoon">Afternoon (12PM-6PM)</option>
                                        <option value="evening">Evening (6PM-12AM)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-6">
                                <h6 class="fw-bold mb-3">Status & Priority</h6>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="statusActive" checked>
                                        <label class="form-check-label" for="statusActive">Active</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="statusPending" checked>
                                        <label class="form-check-label" for="statusPending">Pending</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="statusCompleted" checked>
                                        <label class="form-check-label" for="statusCompleted">Completed</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Priority Level</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="priorityHigh" checked>
                                        <label class="form-check-label text-danger" for="priorityHigh">
                                            <i class="ri-alert-line me-1"></i>High Priority
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="priorityMedium" checked>
                                        <label class="form-check-label text-warning" for="priorityMedium">
                                            <i class="ri-error-warning-line me-1"></i>Medium Priority
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="priorityLow" checked>
                                        <label class="form-check-label text-success" for="priorityLow">
                                            <i class="ri-check-line me-1"></i>Low Priority
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content Type Filters -->
                        <div class="mt-4">
                            <h6 class="fw-bold mb-3">Content Types</h6>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showCaseMeetings" checked>
                                        <label class="form-check-label" for="showCaseMeetings">
                                            <i class="ri-calendar-event-line me-1"></i>Case Meetings
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showCounseling" checked>
                                        <label class="form-check-label" for="showCounseling">
                                            <i class="ri-heart-pulse-line me-1"></i>Counseling
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showViolations" checked>
                                        <label class="form-check-label" for="showViolations">
                                            <i class="ri-error-warning-line me-1"></i>Violations
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showActivities" checked>
                                        <label class="form-check-label" for="showActivities">
                                            <i class="ri-time-line me-1"></i>Activities
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                            <i class="ri-refresh-line me-1"></i>Reset All
                        </button>
                        <button type="button" class="btn btn-success" onclick="applyModalFilters()">
                            <i class="ri-filter-3-line me-1"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Quick Action Modal -->
        <div class="modal fade" id="quickActionModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title">
                                <i class="ri-rocket-line me-2 text-primary"></i>Quick Actions
                            </h5>
                            <p class="text-muted small mb-0">Choose an action to get started</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <button class="btn btn-outline-primary w-100 h-100 p-4 text-start" onclick="scheduleNewCaseMeeting(); closeModal('quickActionModal')">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                            <i class="ri-calendar-event-line fs-3 text-success"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">Case Meeting</div>
                                            <small class="text-muted">Schedule a new case meeting</small>
                                        </div>
                                    </div>
                                </button>
                            </div>
                            <div class="col-12 col-md-6">
                                <button class="btn btn-outline-success w-100 h-100 p-4 text-start" onclick="scheduleNewCounseling(); closeModal('quickActionModal')">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                            <i class="ri-heart-pulse-line fs-3 text-success"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">Counseling Session</div>
                                            <small class="text-muted">Schedule counseling session</small>
                                        </div>
                                    </div>
                                </button>
                            </div>
                            <div class="col-12 col-md-6">
                                <button class="btn btn-outline-info w-100 h-100 p-4 text-start" onclick="scheduleHouseVisit(); closeModal('quickActionModal')">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                                            <i class="ri-home-heart-line fs-3 text-secondary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">House Visit</div>
                                            <small class="text-muted">Schedule home visit</small>
                                        </div>
                                    </div>
                                </button>
                            </div>
                            <div class="col-12 col-md-6">
                                <button class="btn btn-outline-warning w-100 h-100 p-4 text-start" onclick="createCaseSummary(); closeModal('quickActionModal')">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                            <i class="ri-file-text-line fs-3 text-warning"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">Case Summary</div>
                                            <small class="text-muted">Create case summary report</small>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Quick Stats in Modal -->
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="fw-bold text-primary">{{ $stats['pending_cases'] ?? 0 }}</div>
                                    <small class="text-muted">Pending Cases</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="fw-bold text-warning">{{ $stats['scheduled_counseling'] ?? 0 }}</div>
                                    <small class="text-muted">Scheduled Today</small>
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
        </style>

    <!-- Toast container for notifications -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>

    @vite('resources/js/guidance-dashboard.js')
    
    <!-- Dynamic Configuration -->
    <script>
        // Define API endpoints dynamically using existing routes
        window.guidanceApiEndpoints = {
            upcomingTasks: "{{ route('guidance.upcoming-tasks') }}",
            recentActivities: "{{ route('guidance.recent-activities') }}",
            caseStatusStats: "{{ route('guidance.case-status-stats') }}",
            closedCasesStats: "{{ route('guidance.closed-cases-stats') }}",
            counselingSessionsStats: "{{ route('guidance.counseling-sessions-stats') }}",
            disciplineStats: "{{ route('guidance.discipline-vs-total-stats') }}",
            topCases: "{{ url('/guidance/top-cases') }}",
            violationTrends: "{{ route('guidance.violation-trends') }}",
            counselingEffectiveness: "{{ route('guidance.counseling-effectiveness') }}",
            weeklyViolations: "{{ route('guidance.weekly-violations') }}",
            dashboardStats: "{{ route('guidance.recent-activities') }}"
        };
        
        // Pass dynamic data from controller
        window.guidanceStats = {
            totalStudents: {{ $stats['total_students'] ?? 0 }},
            activeCaseMeetings: {{ $stats['active_case_meetings'] ?? 0 }},
            scheduledCounseling: {{ $stats['scheduled_counseling'] ?? 0 }},
            studentsWithDisciplinaryRecord: {{ $stats['students_with_disciplinary_record'] ?? 0 }},
            pendingCases: {{ $stats['pending_cases'] ?? 0 }},
            completedCounselingSessions: {{ $stats['completed_counseling_sessions'] ?? 0 }},
            completedCaseMeetings: {{ $stats['completed_case_meetings'] ?? 0 }},
            studentGrowth: {{ $stats['student_growth'] ?? 0 }},
            studentTarget: {{ $stats['student_target'] ?? 1000 }},
            currentUser: {
                name: "{{ Auth::user()->name ?? 'Guest' }}",
                role: "{{ Auth::user()->getRoleNames()->first() ?? 'user' }}"
            }
        };
        
        // Dynamic refresh intervals (in milliseconds)
        window.refreshConfig = {
            autoRefreshInterval: {{ config('guidance.auto_refresh_interval', 30000) }},
            chartUpdateInterval: {{ config('guidance.chart_update_interval', 60000) }},
            notificationDuration: {{ config('guidance.notification_duration', 5000) }}
        };
    </script>
    
    <!-- Initialize dashboard enhancements -->
    <script>
        // Dashboard initialization with enhanced features
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize filters with default "This Week" setting
            if (typeof applyFilters === 'function') {
                applyFilters();
            }
            
            // Load all dashboard data dynamically
            if (typeof loadAllDashboardData === 'function') {
                loadAllDashboardData();
            } else {
                console.log('Enhanced dashboard functions not loaded yet');
            }
            
            // Add personalized welcome message
            setTimeout(() => {
                const userName = window.guidanceStats.currentUser.name;
                if (typeof showAlert === 'function') {
                    showAlert(`Welcome back, ${userName}! 🎉`, 'success', 3000);
                } else {
                    console.log(`Welcome back, ${userName}! 🎉`);
                }
            }, 1000);
            
            // Check for urgent tasks on load
            setTimeout(checkForUrgentTasks, 2000);
            
            // Setup keyboard shortcuts
            setupKeyboardShortcuts();
        });
        
        // Check for urgent tasks and notify user
        function checkForUrgentTasks() {
            if (!window.guidanceApiEndpoints || !window.guidanceApiEndpoints.upcomingTasks) {
                console.warn('Upcoming tasks API endpoint not configured');
                return;
            }
            
            fetch(window.guidanceApiEndpoints.upcomingTasks)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.tasks && data.tasks.length > 0) {
                        const urgentTasks = data.tasks.filter(task => task.status === 'overdue' || task.priority === 'high').length;
                        if (urgentTasks > 0) {
                            if (typeof showAlert === 'function') {
                                showAlert(`⚠️ You have ${urgentTasks} urgent task${urgentTasks > 1 ? 's' : ''} requiring attention!`, 'warning', 8000);
                            } else {
                                console.warn(`⚠️ You have ${urgentTasks} urgent task${urgentTasks > 1 ? 's' : ''} requiring attention!`);
                            }
                        }
                    }
                })
                .catch(error => {
                    console.warn('Could not fetch urgent tasks:', error.message);
                    // Fail silently for better user experience
                });
        }
        
        // Setup keyboard shortcuts for quick navigation
        function setupKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + R for refresh
                if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                    e.preventDefault();
                    refreshDashboard();
                }
                
                // Ctrl/Cmd + N for new action
                if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                    e.preventDefault();
                    openQuickActionModal();
                }
                
                // Esc to close modals
                if (e.key === 'Escape') {
                    const modals = document.querySelectorAll('.modal.show');
                    modals.forEach(modal => {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) bsModal.hide();
                    });
                }
            });
        }
        
        // Function to update statistics cards dynamically
        function updateStatisticsCards() {
            const endpoint = (window.guidanceApiEndpoints && window.guidanceApiEndpoints.dashboardStats) 
                || '/guidance/api/dashboard-stats';
                
            fetch(endpoint)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.stats) {
                        // Update the statistics cards with fresh data
                        const stats = data.stats;
                        updateProgressBars(stats);
                        updateGrowthIndicators(stats);
                    }
                })
                .catch(error => {
                    console.warn('Could not update statistics:', error.message);
                    // Fail silently for better user experience
                });
        }
        
        // Function to update progress bars dynamically
        function updateProgressBars(stats) {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach((bar, index) => {
                let percentage = 0;
                switch(index) {
                    case 0: // Students progress
                        percentage = stats.total_students > 0 ? Math.min(100, (stats.total_students / (stats.student_target || 1000)) * 100) : 0;
                        break;
                    case 1: // Case meetings progress
                        percentage = (stats.active_case_meetings + (stats.completed_case_meetings || 0)) > 0 
                            ? ((stats.completed_case_meetings || 0) / ((stats.active_case_meetings || 0) + (stats.completed_case_meetings || 0))) * 100 
                            : 0;
                        break;
                    case 2: // Counseling progress
                        percentage = (stats.scheduled_counseling || 0) > 0 
                            ? ((stats.completed_counseling_sessions || 0) / (stats.scheduled_counseling || 1)) * 100 
                            : 0;
                        break;
                    case 3: // Disciplinary record percentage
                        percentage = stats.total_students > 0 ? ((stats.students_with_disciplinary_record || 0) / stats.total_students) * 100 : 0;
                        break;
                }
                bar.style.width = Math.min(100, percentage) + '%';
            });
        }
        
        // Function to update growth indicators
        function updateGrowthIndicators(stats) {
            const growthElement = document.querySelector('.text-success.mt-1');
            if (growthElement && stats.student_growth !== undefined) {
                const growth = stats.student_growth;
                const icon = growth >= 0 ? 'ri-arrow-up-line' : 'ri-arrow-down-line';
                const color = growth >= 0 ? 'text-success' : 'text-danger';
                
                growthElement.className = `${color} mt-1`;
                growthElement.innerHTML = `<i class="${icon}"></i> ${Math.abs(growth)}% from last month`;
            }
        }
        
        // Set up periodic updates for dashboard data
        setInterval(() => {
            updateStatisticsCards();
            if (typeof loadAllDashboardData === 'function') {
                loadAllDashboardData();
            }
        }, window.refreshConfig.chartUpdateInterval);
        
        // Add tooltip initialization for better UX
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</x-guidance-layout>
