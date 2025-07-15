<x-admin-layout>
    <head>
        {{-- @vite(['resources/js/admin-enrollments.js']) --}}
    </head>
    <x-slot name="title">Enrollments Management</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Student Enrollments</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportEnrollments()">
                <i class="ri-download-line me-1"></i>Export
            </button>
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

    <!-- Enrollments Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input" id="selectAll">
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
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input student-checkbox" value="{{ $student->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </div>
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
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="viewStudent({{ $student->id }})">
                                                    <i class="ri-eye-line me-2"></i>View Details
                                                </a>
                                            </li>
                                            @if($student->enrollment_status === 'pending')
                                                <li>
                                                    <a class="dropdown-item text-success" href="#" onclick="approveStudent({{ $student->id }})">
                                                        <i class="ri-check-line me-2"></i>Approve
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" onclick="rejectStudent({{ $student->id }})">
                                                        <i class="ri-close-line me-2"></i>Reject
                                                    </a>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="editStudent({{ $student->id }})">
                                                    <i class="ri-edit-line me-2"></i>Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" onclick="deleteStudent({{ $student->id }})">
                                                    <i class="ri-delete-bin-line me-2"></i>Delete
                                                </a>
                                            </li>
                                        </ul>
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
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">
                    <span id="selectedCount">0</span> students selected
                </span>
                <button class="btn btn-success btn-sm" onclick="bulkApprove()">
                    <i class="ri-check-line me-1"></i>Approve Selected
                </button>
                <button class="btn btn-danger btn-sm" onclick="bulkReject()">
                    <i class="ri-close-line me-1"></i>Reject Selected
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="bulkDelete()">
                    <i class="ri-delete-bin-line me-1"></i>Delete Selected
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // CSRF token setup for AJAX
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Set up AJAX headers
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    
    // Update the existing functions to use AJAX
    window.approveStudent = function(id) {
        if (confirm('Are you sure you want to approve this student?')) {
            fetch(`/admin/enrollments/${id}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    location.reload();
                } else {
                    showAlert('error', 'Failed to approve student');
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred');
                console.error('Error:', error);
            });
        }
    };

    window.rejectStudent = function(id) {
        if (confirm('Are you sure you want to reject this student?')) {
            fetch(`/admin/enrollments/${id}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('warning', data.message);
                    location.reload();
                } else {
                    showAlert('error', 'Failed to reject student');
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred');
                console.error('Error:', error);
            });
        }
    };

    window.deleteStudent = function(id) {
        if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
            fetch(`/admin/enrollments/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    location.reload();
                } else {
                    showAlert('error', 'Failed to delete student');
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred');
                console.error('Error:', error);
            });
        }
    };

    // Bulk actions with AJAX
    window.bulkApprove = function() {
        const selected = getSelectedStudents();
        if (selected.length > 0 && confirm(`Approve ${selected.length} students?`)) {
            fetch('/admin/enrollments/bulk/approve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ student_ids: selected })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    location.reload();
                } else {
                    showAlert('error', 'Failed to approve students');
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred');
                console.error('Error:', error);
            });
        }
    };

    window.bulkReject = function() {
        const selected = getSelectedStudents();
        if (selected.length > 0 && confirm(`Reject ${selected.length} students?`)) {
            fetch('/admin/enrollments/bulk/reject', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ student_ids: selected })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('warning', data.message);
                    location.reload();
                } else {
                    showAlert('error', 'Failed to reject students');
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred');
                console.error('Error:', error);
            });
        }
    };

    window.bulkDelete = function() {
        const selected = getSelectedStudents();
        if (selected.length > 0 && confirm(`Delete ${selected.length} students? This action cannot be undone.`)) {
            fetch('/admin/enrollments/bulk/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ student_ids: selected })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    location.reload();
                } else {
                    showAlert('error', 'Failed to delete students');
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred');
                console.error('Error:', error);
            });
        }
    };

    // Alert function
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }
});

    // Select All functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    // Individual checkbox functionality
    document.querySelectorAll('.student-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const selectedCheckboxes = document.querySelectorAll('.student-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');

        if (selectedCheckboxes.length > 0) {
            bulkActions.style.display = 'block';
            selectedCount.textContent = selectedCheckboxes.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }

    // Action functions
    function viewStudent(id) {
        // Implement view student modal or redirect
        window.location.href = `/admin/enrollments/${id}/view`;
    }

    function editStudent(id) {
        // Implement edit functionality
        console.log('Edit student:', id);
    }

    function exportEnrollments() {
        // Implement export functionality
        console.log('Export enrollments');
    }

    function getSelectedStudents() {
        const checkboxes = document.querySelectorAll('.student-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }
    </script>
    <style>
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</x-admin-layout>