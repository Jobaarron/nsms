<x-registrar-layout>
    <div class="py-4">
        <h1 class="section-title">Welcome to Registrar Portal</h1>
        <p class="text-muted">Manage student applications and enrollment processes</p>
        
        <!-- STATISTICS CARDS -->
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

        <!-- QUICK ACTIONS -->
        <div class="row g-4 mb-4">
            {{-- <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0">
                            <i class="ri-settings-line me-2"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('registrar.applications') }}" class="btn btn-registrar">
                                <i class="ri-file-list-line me-2"></i>Review Applications
                            </a>
                            <a href="{{ route('registrar.approved') }}" class="btn btn-outline-primary">
                                <i class="ri-check-line me-2"></i>Manage Approved
                            </a>
                            <a href="{{ route('registrar.reports') }}" class="btn btn-outline-secondary">
                                <i class="ri-bar-chart-line me-2"></i>View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div> --}}
            
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0">
                            <i class="ri-time-line me-2"></i>
                            Recent Applications
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($recent_applications->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($recent_applications as $application)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $application->first_name }} {{ $application->last_name }}</h6>
                                            <small class="text-muted">{{ $application->grade_level_applied }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $application->enrollment_status === 'pending' ? 'warning' : ($application->enrollment_status === 'approved' ? 'success' : 'danger') }}">
                                                {{ ucfirst($application->enrollment_status) }}
                                            </span>
                                            <small class="d-block text-muted">{{ $application->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center py-3">No recent applications</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-registrar-layout>
