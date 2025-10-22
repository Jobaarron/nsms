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

            <!-- QUICK ACTIONS -->
            <!-- <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h5 class="mb-0">
                            <i class="ri-settings-line me-2 text-primary"></i>
                            Quick Actions
                        </h5>
                        <p class="mb-0 mt-1 text-muted small">Common tasks and shortcuts</p>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            <a href="{{ route('registrar.applications') }}" class="btn btn-registrar btn-lg">
                                <div class="d-flex align-items-center">
                                    <i class="ri-file-list-line fs-4 me-3"></i>
                                    <div class="text-start">
                                        <div class="fw-bold">Review All Applications</div>
                                        <small class="opacity-75">View and manage all submissions</small>
                                    </div>
                                </div>
                            </a>
                            
                            @if($stats['approved_applications'] > 0)
                            <a href="{{ route('registrar.approved') }}" class="btn btn-outline-success btn-lg">
                                <div class="d-flex align-items-center">
                                    <i class="ri-check-line fs-4 me-3"></i>
                                    <div class="text-start">
                                        <div class="fw-bold">Manage Approved Students</div>
                                        <small class="opacity-75">{{ $stats['approved_applications'] }} approved applications</small>
                                    </div>
                                </div>
                            </a>
                            @endif
                            
                            <a href="{{ route('registrar.reports') }}" class="btn btn-outline-primary btn-lg">
                                <div class="d-flex align-items-center">
                                    <i class="ri-bar-chart-line fs-4 me-3"></i>
                                    <div class="text-start">
                                        <div class="fw-bold">View Reports & Analytics</div>
                                        <small class="opacity-75">Application statistics and trends</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>

    <!-- JavaScript for Quick Actions -->
    <script>
    function approveApplication(applicationId) {
        if (confirm('Are you sure you want to approve this application?')) {
            fetch(`/registrar/applications/${applicationId}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error approving application. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error approving application. Please try again.');
            });
        }
    }

    function declineApplication(applicationId) {
        const reason = prompt('Please provide a reason for declining this application:');
        if (reason && reason.trim() !== '') {
            fetch(`/registrar/applications/${applicationId}/decline`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reason: reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error declining application. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error declining application. Please try again.');
            });
        }
    }

    // Add hover effects
    document.querySelectorAll('.hover-bg-light').forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        element.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
    </script>

    <style>
    .hover-bg-light {
        transition: background-color 0.2s ease;
        cursor: pointer;
    }
    
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .btn {
        transition: all 0.2s ease;
    }
    
    .badge {
        font-weight: 500;
    }
    </style>
</x-registrar-layout>
