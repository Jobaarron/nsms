<x-guidance-layout>
    @vite('resources/css/index_guidance.css')
    
    <!-- Header -->
    <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-guidance">Guidance Dashboard</h1>
                        <p class="text-muted">Welcome back, {{ Auth::user()->name }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                            <i class="ri-refresh-line me-2"></i>Refresh
                        </button>
                        <button class="btn btn-primary" onclick="openQuickActionModal()">
                            <i class="ri-add-line me-2"></i>Quick Action
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="ri-user-3-line fs-2 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-4">{{ $stats['total_students'] ?? 0 }}</div>
                            <div class="text-muted small">Total Students</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="ri-calendar-event-line fs-2 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-4">{{ $stats['active_case_meetings'] ?? 0 }}</div>
                            <div class="text-muted small">Active Case Meetings</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="ri-heart-pulse-line fs-2 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-4">{{ $stats['scheduled_counseling'] ?? 0 }}</div>
                            <div class="text-muted small">Scheduled Counseling</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="ri-home-heart-line fs-2 text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-4">{{ $stats['house_visits_scheduled'] ?? 0 }}</div>
                            <div class="text-muted small">House Visits</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Recent Activities -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Activities</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="ri-filter-line me-1"></i>Filter
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="filterActivities('all')">All Activities</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterActivities('case_meetings')">Case Meetings</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterActivities('counseling')">Counseling Sessions</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterActivities('house_visits')">House Visits</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="activities-list">
                            <!-- Activities will be loaded here via JavaScript -->
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2">Loading recent activities...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Upcoming -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
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

                <!-- Upcoming Schedule -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Today's Schedule</h5>
                    </div>
                    <div class="card-body">
                        <div id="todays-schedule">
                            <!-- Schedule will be loaded here via JavaScript -->
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 small">Loading today's schedule...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Modal -->
    <div class="modal fade" id="quickActionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <button class="btn btn-outline-primary w-100 h-100" onclick="scheduleNewCaseMeeting(); closeModal('quickActionModal')">
                                <i class="ri-calendar-event-line fs-3 d-block mb-2"></i>
                                <span>Case Meeting</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-success w-100 h-100" onclick="scheduleNewCounseling(); closeModal('quickActionModal')">
                                <i class="ri-heart-pulse-line fs-3 d-block mb-2"></i>
                                <span>Counseling</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-info w-100 h-100" onclick="scheduleHouseVisit(); closeModal('quickActionModal')">
                                <i class="ri-home-heart-line fs-3 d-block mb-2"></i>
                                <span>House Visit</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-warning w-100 h-100" onclick="createCaseSummary(); closeModal('quickActionModal')">
                                <i class="ri-file-text-line fs-3 d-block mb-2"></i>
                                <span>Case Summary</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite('resources/js/guidance-dashboard.js')
</x-guidance-layout>
