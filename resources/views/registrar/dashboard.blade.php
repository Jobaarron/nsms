<x-registrar-layout>
    <div class="py-4">
        <!-- HEADER SECTION -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="section-title mb-2">Student Applications Dashboard</h1>
                <p class="text-muted mb-0">Review and manage student enrollment applications</p>
            </div>
            <div>
                <a href="{{ route('registrar.applications') }}" class="btn btn-registrar btn-lg">
                    <i class="ri-file-list-line me-2"></i>View All Applications
                </a>
            </div>
        </div>
        
        <!-- STATISTICS OVERVIEW -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body py-4">
                        <div class="mb-3">
                            <i class="ri-file-list-line fs-1 text-primary"></i>
                        </div>
                        <div class="fw-bold fs-2 text-primary mb-1">{{ $stats['total_applications'] }}</div>
                        <div class="text-muted">Total Applications Received</div>
                        <small class="text-muted">All time submissions</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body py-4">
                        <div class="mb-3">
                            <i class="ri-time-line fs-1 text-warning"></i>
                        </div>
                        <div class="fw-bold fs-2 text-warning mb-1">{{ $stats['pending_applications'] }}</div>
                        <div class="text-muted">Waiting for Review</div>
                        <small class="text-muted">Needs your attention</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body py-4">
                        <div class="mb-3">
                            <i class="ri-check-line fs-1 text-success"></i>
                        </div>
                        <div class="fw-bold fs-2 text-success mb-1">{{ $stats['approved_applications'] }}</div>
                        <div class="text-muted">Successfully Approved</div>
                        <small class="text-muted">Ready for enrollment</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body py-4">
                        <div class="mb-3">
                            <i class="ri-close-line fs-1 text-danger"></i>
                        </div>
                        <div class="fw-bold fs-2 text-danger mb-1">{{ $stats['declined_applications'] }}</div>
                        <div class="text-muted">Applications Declined</div>
                        <small class="text-muted">Did not meet requirements</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- APPLICATIONS SECTION -->
        <div class="row g-4">
            <!-- PENDING APPLICATIONS - PRIORITY -->
            @if($stats['pending_applications'] > 0)
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning bg-opacity-10 border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 text-warning">
                                <i class="ri-alert-line me-2"></i>
                                Applications Requiring Review ({{ $stats['pending_applications'] }})
                            </h4>
                            <span class="badge bg-warning fs-6 px-3 py-2">Action Required</span>
                        </div>
                        <p class="mb-0 mt-2 text-muted">These applications are waiting for your review and decision</p>
                    </div>
                    <div class="card-body p-0">
                        @if($recent_applications->where('enrollment_status', 'pending')->count() > 0)
                            @foreach($recent_applications->where('enrollment_status', 'pending') as $application)
                            <div class="border-bottom p-4 hover-bg-light">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                                <i class="ri-user-line fs-4 text-warning"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-1">{{ $application->first_name }} {{ $application->last_name }}</h5>
                                                <p class="mb-0 text-muted">Applying for {{ $application->grade_level_applied }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <span class="badge bg-warning fs-6 px-3 py-2">
                                                <i class="ri-time-line me-1"></i>Pending Review
                                            </span>
                                            <p class="mb-0 mt-2 small text-muted">Submitted {{ $application->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('registrar.applications') }}?view={{ $application->id }}" class="btn btn-outline-primary">
                                                <i class="ri-eye-line me-1"></i>View Details
                                            </a>
                                            <button class="btn btn-success" onclick="approveApplication({{ $application->id }})">
                                                <i class="ri-check-line me-1"></i>Approve
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="declineApplication({{ $application->id }})">
                                                <i class="ri-close-line me-1"></i>Decline
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <i class="ri-check-double-line fs-1 text-success mb-3"></i>
                                <h5 class="text-muted">All caught up!</h5>
                                <p class="text-muted">No pending applications to review at this time.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- RECENT ACTIVITY -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h5 class="mb-0">
                            <i class="ri-history-line me-2 text-primary"></i>
                            Recent Activity
                        </h5>
                        <p class="mb-0 mt-1 text-muted small">Latest application submissions and updates</p>
                    </div>
                    <div class="card-body">
                        @if($recent_applications->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($recent_applications->take(5) as $application)
                                <div class="list-group-item border-0 px-0 py-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="bg-{{ $application->enrollment_status === 'pending' ? 'warning' : ($application->enrollment_status === 'approved' ? 'success' : 'danger') }} bg-opacity-10 rounded-circle p-2 me-3">
                                                    <i class="ri-user-line text-{{ $application->enrollment_status === 'pending' ? 'warning' : ($application->enrollment_status === 'approved' ? 'success' : 'danger') }}"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $application->first_name }} {{ $application->last_name }}</h6>
                                                    <small class="text-muted">{{ $application->grade_level_applied }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $application->enrollment_status === 'pending' ? 'warning' : ($application->enrollment_status === 'approved' ? 'success' : 'danger') }}">
                                                {{ ucfirst($application->enrollment_status) }}
                                            </span>
                                            <small class="d-block text-muted mt-1">{{ $application->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <!-- <div class="text-center mt-3">
                                <a href="{{ route('registrar.applications') }}" class="btn btn-outline-primary btn-sm">
                                    View All Applications <i class="ri-arrow-right-line ms-1"></i>
                                </a>
                            </div> -->
                        @else
                            <div class="text-center py-4">
                                <i class="ri-file-list-line fs-1 text-muted mb-3"></i>
                                <p class="text-muted">No applications submitted yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ANALYTICS SECTION -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h5 class="mb-0">
                            <i class="ri-bar-chart-line me-2 text-primary"></i>
                            Applications by Grade Level
                        </h5>
                        <p class="mb-0 mt-1 text-muted small">Distribution of applications across grade levels</p>
                    </div>
                    <div class="card-body">
                        @if($by_grade->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm" id="grade-level-table">
                                    <thead>
                                        <tr>
                                            <th>Grade Level</th>
                                            <th>Applications</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($by_grade as $grade)
                                        @php
                                            $percentage = $stats['total_applications'] > 0 ? round(($grade->count / $stats['total_applications']) * 100, 1) : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $grade->grade_level_applied }}</td>
                                            <td>{{ $grade->count }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar bg-primary" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                    <small>{{ $percentage }}%</small>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ri-bar-chart-line fs-1 text-muted mb-3"></i>
                                <p class="text-muted">No data available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- MONTHLY TRENDS -->
        <div class="row g-4 mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h5 class="mb-0">
                            <i class="ri-calendar-line me-2 text-primary"></i>
                            Applications by Month
                        </h5>
                        <p class="mb-0 mt-1 text-muted small">Monthly application submission trends</p>
                    </div>
                    <div class="card-body">
                        @if($by_month->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Applications</th>
                                            <th>Trend</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($by_month as $month)
                                        @php
                                            $monthName = date('M Y', mktime(0, 0, 0, $month->month, 1, $month->year));
                                            $maxCount = $by_month->max('count');
                                            $barWidth = $maxCount > 0 ? round(($month->count / $maxCount) * 100, 1) : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $monthName }}</td>
                                            <td>{{ $month->count }}</td>
                                            <td>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-success" style="width: {{ $barWidth }}%"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ri-calendar-line fs-1 text-muted mb-3"></i>
                                <p class="text-muted">No data available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-registrar-layout>
