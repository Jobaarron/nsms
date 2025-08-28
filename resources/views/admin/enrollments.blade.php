<x-admin-layout>
        @vite(['resources/js/admin-enrollments.js'])
        @vite(['resources/css/admin_enrollments.css'])
  
    <x-slot name="title">Enrollments Management</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Student Enrollments</h1>
        <div class="d-flex gap-2">
            {{-- <button class="btn btn-outline-primary" onclick="exportEnrollments()">
                <i class="ri-download-line me-1"></i>Export
            </button> Soon to be use--}}
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Applications</h6>
                            <h3 class="mb-0">{{ $totalCount }}</h3>
                        </div>
                        <i class="ri-file-list-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Pending</h6>
                            <h3 class="mb-0">{{ $pendingCount }}</h3>
                        </div>
                        <i class="ri-time-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Enrolled</h6>
                            <h3 class="mb-0">{{ $approvedCount }}</h3>
                        </div>
                        <i class="ri-check-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Rejected</h6>
                            <h3 class="mb-0">{{ $rejectedCount }}</h3>
                        </div>
                        <i class="ri-close-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.enrollments') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="enrolled" {{ request('status') == 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Grade Level</label>
                        <select name="grade_level" class="form-select">
                            <option value="">All Grades</option>
                            <option value="Nursery" {{ request('grade_level') == 'Nursery' ? 'selected' : '' }}>Nursery</option>
                            <option value="Kinder 1" {{ request('grade_level') == 'Kinder 1' ? 'selected' : '' }}>Kinder 1</option>
                            <option value="Kinder 2" {{ request('grade_level') == 'Kinder 2' ? 'selected' : '' }}>Kinder 2</option>
                            <option value="Grade 1" {{ request('grade_level') == 'Grade 1' ? 'selected' : '' }}>Grade 1</option>
                            <option value="Grade 2" {{ request('grade_level') == 'Grade 2' ? 'selected' : '' }}>Grade 2</option>
                            <option value="Grade 3" {{ request('grade_level') == 'Grade 3' ? 'selected' : '' }}>Grade 3</option>
                            <option value="Grade 4" {{ request('grade_level') == 'Grade 4' ? 'selected' : '' }}>Grade 4</option>
                            <option value="Grade 5" {{ request('grade_level') == 'Grade 5' ? 'selected' : '' }}>Grade 5</option>
                            <option value="Grade 6" {{ request('grade_level') == 'Grade 6' ? 'selected' : '' }}>Grade 6</option>
                            <option value="Grade 7" {{ request('grade_level') == 'Grade 7' ? 'selected' : '' }}>Grade 7</option>
                            <option value="Grade 8" {{ request('grade_level') == 'Grade 8' ? 'selected' : '' }}>Grade 8</option>
                            <option value="Grade 9" {{ request('grade_level') == 'Grade 9' ? 'selected' : '' }}>Grade 9</option>
                            <option value="Grade 10" {{ request('grade_level') == 'Grade 10' ? 'selected' : '' }}>Grade 10</option>
                            <option value="Grade 11" {{ request('grade_level') == 'Grade 11' ? 'selected' : '' }}>Grade 11</option>
                            <option value="Grade 12" {{ request('grade_level') == 'Grade 12' ? 'selected' : '' }}>Grade 12</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-search-line"></i> Filter
                            </button>
                            <a href="{{ route('admin.enrollments') }}" class="btn btn-outline-secondary">
                                <i class="ri-refresh-line"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Enhanced Toolbar -->
    {{-- <div class="card mb-4 no-print">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="btn-group" role="group">
                       <button type="button" class="btn btn-success" onclick="quickApproveAll()" 
                                data-bs-toggle="tooltip" title="Approve all pending students">
                            <i class="ri-check-double-line me-1"></i>Quick Approve All
                        </button> 
                        
                         <button type="button" class="btn btn-outline-primary" onclick="exportEnrollments('excel')" 
                                data-bs-toggle="tooltip" title="Export all enrollments to Excel">
                            <i class="ri-file-excel-line me-1"></i>Export Excel
                        </button> 
                        
                        <button type="button" class="btn btn-outline-secondary" onclick="exportEnrollments('pdf')" 
                                data-bs-toggle="tooltip" title="Export all enrollments to PDF">
                            <i class="ri-file-pdf-line me-1"></i>Export PDF
                        </button> 
                    </div>
                </div>
                
                <div class="col-md-6 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" id="autoRefreshBtn" class="btn btn-outline-secondary" 
                                onclick="toggleAutoRefresh()" 
                                data-bs-toggle="tooltip" title="Toggle auto-refresh">
                            <i class="ri-refresh-line me-1"></i>Auto Refresh: OFF
                        </button>
                        
                        <button type="button" class="btn btn-outline-info" onclick="location.reload()" 
                                data-bs-toggle="tooltip" title="Refresh page">
                            <i class="ri-refresh-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div> Soon to be use --}}

    <!-- Enrollments Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>
                                {{-- <input type="checkbox" class="form-check-input" id="selectAll"> Soon to be use --}}
                            </th>
                            <th>Student Info</th>
                            <th>Grade/Strand</th>
                            <th>Guardian</th>
                            <th>Status</th>
                            <th>Applied Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($enrollments ?? [] as $student)
                            <tr data-status="{{ $student->enrollment_status }}">
                                <td>
                                    {{-- <input type="checkbox" class="form-check-input student-checkbox" value="{{ $student->id }}"> Soon to be use --}}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        {{-- <div class="avatar-circle me-3"> --}}
                                            {{-- {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }} --}}
                                           {{-- <img src="{{ asset('storage/' . $student->id_photo) }}" alt="Student Avatar" class="avatar-img"> --}}
                                        {{-- </div> --}}
                                        <div>
                                            <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $student->email }}</small>
                                            @if($student->lrn)
                                                <br><small class="text-muted">LRN: {{ $student->lrn }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $student->grade_level }}</span>
                                    @if($student->strand)
                                        <br><small class="text-muted">{{ $student->strand }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $student->guardian_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $student->guardian_contact }}</small>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($student->enrollment_status) {
                                            'enrolled' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst($student->enrollment_status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $student->created_at->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $student->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-center action-buttons">
                                    <div class="btn-group" role="group">
                                        <!-- View Button -->
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="viewStudent({{ $student->id }})" 
                                                data-bs-toggle="tooltip" 
                                                title="View Details">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        
                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editStudent({{ $student->id }})" 
                                                data-bs-toggle="tooltip" 
                                                title="Edit Student">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        
                                        <!-- Status-based Action Buttons -->
                                        @if($student->enrollment_status === 'pending')
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="approveStudent({{ $student->id }}, '{{ $student->first_name }} {{ $student->last_name }}')" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Approve Student">
                                                <i class="ri-check-line"></i>
                                            </button>
                                            
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                    onclick="rejectStudent({{ $student->id }}, '{{ $student->first_name }} {{ $student->last_name }}')" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Reject Student">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        @endif
                                        
                                        <!-- Status Change Button -->
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                onclick="changeStatus({{ $student->id }}, '{{ $student->enrollment_status }}', '{{ $student->first_name }} {{ $student->last_name }}')" 
                                                data-bs-toggle="tooltip" 
                                                title="Change Status">
                                            <i class="ri-refresh-line"></i>
                                        </button>
                                        
                                        <!-- Delete Button -->
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteStudent({{ $student->id }}, '{{ $student->first_name }} {{ $student->last_name }}')" 
                                                data-bs-toggle="tooltip"
                                                title="Delete Student">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="ri-inbox-line display-4 d-block mb-3"></i>
                                        <h5>No enrollment records found</h5>
                                        <p>Try adjusting your filters or check back later.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
    
                @if(isset($enrollments) && $enrollments->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $enrollments->links() }}
                    </div>
                @endif
            </div>
        </div>
    
        <!-- Bulk Actions -->
        <div class="card mt-3" id="bulkActions" style="display: none;">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-0">
                            <i class="ri-checkbox-multiple-line me-2"></i>
                            <span id="selectedCount">0</span> students selected
                        </h6>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group flex-wrap" role="group">
                            <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()">
                                <i class="ri-check-line me-1"></i>Approve
                            </button>
                            
                            <button type="button" class="btn btn-warning btn-sm" onclick="bulkReject()">
                                <i class="ri-close-line me-1"></i>Reject
                            </button>
                            
                            <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                                <i class="ri-delete-bin-line me-1"></i>Delete
                            </button>
                            
                            <button type="button" class="btn btn-info btn-sm" onclick="exportSelected()">
                                <i class="ri-download-line me-1"></i>Export
                            </button>
                            
                            <button type="button" class="btn btn-secondary btn-sm" onclick="clearAllSelections()">
                                <i class="ri-close-circle-line me-1"></i>Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</x-admin-layout>