<x-registrar-layout>
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="section-title" style="color: var(--primary-color, #2B7A3B);">
                    <i class="ri-archive-line me-2" style="color: var(--primary-color, #2B7A3B);"></i>
                    Applicant Archives
                </h1>
                <p class="text-muted mb-0">View and manage approved and declined applications</p>
            </div>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ri-check-line fs-2" style="color: var(--secondary-color, #4CAF50);"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4" id="approved-count">{{ $approvedCount ?? 0 }}</div>
                                <div class="text-muted small">Approved Applications</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ri-close-line fs-2" style="color: #dc3545;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4" id="declined-count">{{ $declinedCount ?? 0 }}</div>
                                <div class="text-muted small">Declined Applications</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ri-file-list-3-line fs-2" style="color: var(--accent-color, #1B5E20);"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4" id="total-archived">{{ ($approvedCount ?? 0) + ($declinedCount ?? 0) }}</div>
                                <div class="text-muted small">Total Archived</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ri-user-follow-line fs-2" style="color: var(--primary-color, #2B7A3B);"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4" id="enrolled-count">{{ $enrolledCount ?? 0 }}</div>
                                <div class="text-muted small">Admitted Applicants</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- FILTERS -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('registrar.applicant-archives') }}" class="mb-0">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Grade Level</label>
                            <select class="form-select" name="grade_level" onchange="this.form.submit()">
                                <option value="">All Grades</option>
                                <option value="Nursery" {{ request('grade_level') === 'Nursery' ? 'selected' : '' }}>Nursery</option>
                                <option value="Junior Casa" {{ request('grade_level') === 'Junior Casa' ? 'selected' : '' }}>Junior Casa</option>
                                <option value="Senior Casa" {{ request('grade_level') === 'Senior Casa' ? 'selected' : '' }}>Senior Casa</option>
                                <option value="Kinder" {{ request('grade_level') === 'Kinder' ? 'selected' : '' }}>Kinder</option>
                                <option value="Grade 1" {{ request('grade_level') === 'Grade 1' ? 'selected' : '' }}>Grade 1</option>
                                <option value="Grade 2" {{ request('grade_level') === 'Grade 2' ? 'selected' : '' }}>Grade 2</option>
                                <option value="Grade 3" {{ request('grade_level') === 'Grade 3' ? 'selected' : '' }}>Grade 3</option>
                                <option value="Grade 4" {{ request('grade_level') === 'Grade 4' ? 'selected' : '' }}>Grade 4</option>
                                <option value="Grade 5" {{ request('grade_level') === 'Grade 5' ? 'selected' : '' }}>Grade 5</option>
                                <option value="Grade 6" {{ request('grade_level') === 'Grade 6' ? 'selected' : '' }}>Grade 6</option>
                                <option value="Grade 7" {{ request('grade_level') === 'Grade 7' ? 'selected' : '' }}>Grade 7</option>
                                <option value="Grade 8" {{ request('grade_level') === 'Grade 8' ? 'selected' : '' }}>Grade 8</option>
                                <option value="Grade 9" {{ request('grade_level') === 'Grade 9' ? 'selected' : '' }}>Grade 9</option>
                                <option value="Grade 10" {{ request('grade_level') === 'Grade 10' ? 'selected' : '' }}>Grade 10</option>
                                <option value="Grade 11" {{ request('grade_level') === 'Grade 11' ? 'selected' : '' }}>Grade 11</option>
                                <option value="Grade 12" {{ request('grade_level') === 'Grade 12' ? 'selected' : '' }}>Grade 12</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or application ID...">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="ri-search-line"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <a href="{{ route('registrar.applicant-archives') }}" class="btn btn-outline-secondary">
                                    <i class="ri-close-line me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- APPLICATIONS TABLE -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-archive-line me-2"></i>
                        Archived Applications
                        @if(request('status'))
                            - {{ ucfirst(request('status')) }}
                        @endif
                    </h5>
                    <div class="text-muted small">
                        Total: {{ ($approvedCount ?? 0) + ($declinedCount ?? 0) }} applications
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Application ID</th>
                                <th>Name</th>
                                <th>Grade Level</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Decision Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Combine and sort all applications based on filter
                                $allApplications = collect();
                                
                                if (!request('status') || request('status') === 'approved') {
                                    $allApplications = $allApplications->concat($approvedApplications ?? []);
                                }
                                
                                if (!request('status') || request('status') === 'declined') {
                                    $allApplications = $allApplications->concat($declinedApplications ?? []);
                                }
                                
                                $allApplications = $allApplications->sortByDesc('updated_at');
                            @endphp
                            
                            @forelse($allApplications as $application)
                            <tr data-id="{{ $application->id }}">
                                <td>
                                    <span class="fw-medium">{{ $application->application_id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="ri-user-line text-muted"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $application->first_name }} {{ $application->last_name }}</div>
                                            <small class="text-muted">{{ $application->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $application->grade_level_applied }}</span>
                                </td>
                                <td class="d-none d-md-table-cell">{{ $application->email }}</td>
                                <td>
                                    @php
                                        $statusClasses = [
                                            'approved' => 'bg-success',
                                            'rejected' => 'bg-danger'
                                        ];
                                        $statusClass = $statusClasses[$application->enrollment_status] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        <i class="ri-{{ $application->enrollment_status === 'approved' ? 'check' : 'close' }}-line me-1"></i>
                                        {{ $application->enrollment_status === 'rejected' ? 'Declined' : ucfirst($application->enrollment_status) }}
                                    </span>
                                    @if($application->enrollment_status === 'approved' && $application->student)
                                        <br><small class="text-success">Admitted</small>
                                    @endif
                                </td>
                                <td>
                                    @if($application->enrollment_status === 'approved')
                                        {{ $application->approved_at ? $application->approved_at->format('M d, Y') : 'N/A' }}
                                    @else
                                        {{ $application->rejected_at ? $application->rejected_at->format('M d, Y') : 'N/A' }}
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewArchiveApplication({{ $application->id }})" title="View Details">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        @if($application->enrollment_status === 'rejected')
                                        <button class="btn btn-outline-warning" onclick="reconsiderApplication({{ $application->id }})" title="Reconsider Application">
                                            <i class="ri-refresh-line"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ri-archive-line fs-1 d-block mb-3 opacity-50"></i>
                                        <h6>No archived applications found</h6>
                                        <p class="mb-0">Applications that have been approved or declined will appear here.</p>
                                    </div>
                                </td>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PAGINATION -->
        @if(request('status') === 'approved' && isset($approvedApplications))
            <div class="d-flex justify-content-center mt-4">
                {{ $approvedApplications->appends(request()->query())->links('pagination.custom') }}
            </div>
        @elseif(request('status') === 'declined' && isset($declinedApplications))
            <div class="d-flex justify-content-center mt-4">
                {{ $declinedApplications->appends(request()->query())->links('pagination.custom') }}
            </div>
        @elseif(!request('status') && isset($allArchives))
            <div class="d-flex justify-content-center mt-4">
                {{ $allArchives->appends(request()->query())->links('pagination.custom') }}
            </div>
        @endif
    </div>

    <!-- VIEW ARCHIVE APPLICATION MODAL -->
    <div class="modal fade" id="viewArchiveModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Application Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="archive-details">
                        <!-- Application details will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <div id="modal-actions">
                        <!-- Dynamic action buttons will be loaded here based on application status -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RECONSIDER APPLICATION MODAL -->
    <div class="modal fade" id="reconsiderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reconsider Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><i class="ri-information-line me-2"></i>Reconsider Application</h6>
                        <p class="mb-0">This will change the application status back to "pending" for re-evaluation.</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Reconsideration</label>
                        <textarea class="form-control" id="reconsider-reason" rows="3" placeholder="Enter reason for reconsidering this application..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="confirmReconsider()">
                        <i class="ri-refresh-line me-1"></i>Accept Application
                    </button>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/registrar-applicant-archives.js'])
</x-registrar-layout>
