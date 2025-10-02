<x-registrar-layout>
    <div class="py-4">
        <h1 class="section-title">Enrollment Reports</h1>
        <p class="text-muted">View comprehensive enrollment statistics and analytics</p>
        
        <!-- OVERVIEW STATISTICS -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ri-file-list-line fs-2 text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold fs-4">{{ $stats['total_applications'] }}</div>
                            <div class="text-muted small">Total Applications</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ri-time-line fs-2 text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold fs-4">{{ $stats['pending_applications'] }}</div>
                            <div class="text-muted small">Pending Review</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ri-check-line fs-2 text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold fs-4">{{ $stats['approved_applications'] }}</div>
                            <div class="text-muted small">Approved</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ri-close-line fs-2 text-danger"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold fs-4">{{ $stats['declined_applications'] }}</div>
                            <div class="text-muted small">Declined</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHARTS AND ANALYTICS -->
        <div class="row g-4">
            <!-- APPLICATIONS BY GRADE LEVEL -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0">
                            <i class="ri-bar-chart-line me-2"></i>
                            Applications by Grade Level
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($by_grade->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
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
                            <p class="text-muted text-center py-3">No data available</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- APPLICATIONS BY MONTH -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0">
                            <i class="ri-calendar-line me-2"></i>
                            Applications by Month
                        </h5>
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
                            <p class="text-muted text-center py-3">No data available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- EXPORT OPTIONS -->
        <div class="row g-4 mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        {{-- <h5 class="mb-0">
                            <i class="ri-download-line me-2"></i>
                            Export Reports
                        </h5> --}}
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-grid">
                                    {{-- <button class="btn btn-outline-primary" onclick="exportAllApplications()">
                                        <i class="ri-file-excel-line me-2"></i>
                                        Export All Applications
                                    </button> --}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-grid">
                                    {{-- <button class="btn btn-outline-success" onclick="exportApprovedApplications()">
                                        <i class="ri-file-excel-line me-2"></i>
                                        Export Approved Only
                                    </button> --}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-grid">
                                    {{-- <button class="btn btn-outline-warning" onclick="exportPendingApplications()">
                                        <i class="ri-file-excel-line me-2"></i>
                                        Export Pending Only
                                    </button> --}}
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <button class="btn btn-outline-info" onclick="exportGradeReport()">
                                        <i class="ri-bar-chart-line me-2"></i>
                                        Export Grade Level Report
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid">
                                    {{-- <button class="btn btn-outline-secondary" onclick="exportMonthlyReport()">
                                        <i class="ri-calendar-line me-2"></i>
                                        Export Monthly Report
                                    </button> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/registrar-reports.js'])
</x-registrar-layout>
