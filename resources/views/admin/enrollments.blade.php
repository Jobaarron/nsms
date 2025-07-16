<x-admin-layout>
    <head>
        {{-- @vite(['resources/js/admin-enrollments.js']) External JS is not working so, refer to the script below --}}
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

    <!-- Enhanced Toolbar -->
    <div class="card mb-4 no-print">
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
                            <tr data-status="{{ $student->enrollment_status }}">
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
                    <div class="col-md-6">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // CSRF token setup for AJAX
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Set up AJAX headers
                if (typeof axios !== 'undefined') {
                    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
                }
                
                // Individual action functions with improved UX
                window.approveStudent = function(id, name) {
                    showConfirmModal(
                        'Approve Student',
                        `Are you sure you want to approve <strong>${name}</strong>?`,
                        'success',
                        function() {
                            performAction(`/admin/enrollments/${id}/approve`, 'POST', 'Student approved successfully!');
                        }
                    );
                };
    
                window.rejectStudent = function(id, name) {
                    showConfirmModal(
                        'Reject Student',
                        `Are you sure you want to reject <strong>${name}</strong>?`,
                        'warning',
                        function() {
                            performAction(`/admin/enrollments/${id}/reject`, 'POST', 'Student rejected successfully!');
                        }
                    );
                };
    
                window.deleteStudent = function(id, name) {
                    showConfirmModal(
                        'Delete Student',
                        `Are you sure you want to permanently delete <strong>${name}</strong>? This action cannot be undone.`,
                        'danger',
                        function() {
                            performAction(`/admin/enrollments/${id}`, 'DELETE', 'Student deleted successfully!');
                        }
                    );
                };
    
                window.viewStudent = function(id) {
                    showLoadingOverlay();
                    window.location.href = `/admin/enrollments/${id}/view`;
                };
    
                window.editStudent = function(id) {
                    showLoadingOverlay();
                    window.location.href = `/admin/enrollments/${id}/edit`;
                };
    
                // Bulk actions with improved feedback
                window.bulkApprove = function() {
                    const selected = getSelectedStudents();
                    if (selected.length === 0) {
                        showAlert('warning', 'Please select at least one student to approve.');
                        return;
                    }
                    
                    showConfirmModal(
                        'Bulk Approve Students',
                        `Are you sure you want to approve <strong>${selected.length}</strong> selected students?`,
                        'success',
                        function() {
                            performBulkAction('/admin/enrollments/bulk/approve', selected, `${selected.length} students approved successfully!`);
                        }
                    );
                };
    
                window.bulkReject = function() {
                    const selected = getSelectedStudents();
                    if (selected.length === 0) {
                        showAlert('warning', 'Please select at least one student to reject.');
                        return;
                    }
                    
                    showConfirmModal(
                        'Bulk Reject Students',
                        `Are you sure you want to reject <strong>${selected.length}</strong> selected students?`,
                        'warning',
                        function() {
                            performBulkAction('/admin/enrollments/bulk/reject', selected, `${selected.length} students rejected successfully!`);
                        }
                    );
                };
    
                window.bulkDelete = function() {
                    const selected = getSelectedStudents();
                    if (selected.length === 0) {
                        showAlert('warning', 'Please select at least one student to delete.');
                        return;
                    }
                    
                    showConfirmModal(
                        'Bulk Delete Students',
                        `Are you sure you want to permanently delete <strong>${selected.length}</strong> selected students? This action cannot be undone.`,
                        'danger',
                        function() {
                            performBulkAction('/admin/enrollments/bulk/delete', selected, `${selected.length} students deleted successfully!`);
                        }
                    );
                };
    
                // New additional actions
                window.exportSelected = function() {
                    const selected = getSelectedStudents();
                    if (selected.length === 0) {
                        showAlert('warning', 'Please select at least one student to export.');
                        return;
                    }
                    
                    showLoadingOverlay('Preparing export...');
                    
                    // Create form for export
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/admin/enrollments/export';
                    form.style.display = 'none';
                    
                    // Add CSRF token
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = token;
                    form.appendChild(csrfInput);
                    
                    // Add selected IDs
                    selected.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'student_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });
                    
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                    
                    hideLoadingOverlay();
                    showAlert('info', `Exporting ${selected.length} students...`);
                };
    
                window.sendNotification = function() {
                    const selected = getSelectedStudents();
                    if (selected.length === 0) {
                        showAlert('warning', 'Please select at least one student to send notification.');
                        return;
                    }
                    
                    showNotificationModal(selected);
                };
    
                window.changeStatus = function(id, currentStatus, name) {
                    showStatusChangeModal(id, currentStatus, name);
                };
    
                // Utility functions
                function performAction(url, method, successMessage) {
                    showLoadingOverlay();
                    
                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideLoadingOverlay();
                        if (data.success) {
                            showAlert('success', successMessage);
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert('error', data.message || 'Operation failed');
                        }
                    })
                    .catch(error => {
                        hideLoadingOverlay();
                        showAlert('error', 'An error occurred. Please try again.');
                        console.error('Error:', error);
                    });
                }
    
                function performBulkAction(url, studentIds, successMessage) {
                    showLoadingOverlay();
                    
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ student_ids: studentIds })
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideLoadingOverlay();
                        if (data.success) {
                            showAlert('success', successMessage);
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert('error', data.message || 'Bulk operation failed');
                        }
                    })
                    .catch(error => {
                        hideLoadingOverlay();
                        showAlert('error', 'An error occurred during bulk operation.');
                        console.error('Error:', error);
                    });
                }
    
                function getSelectedStudents() {
                    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
                    return Array.from(checkboxes).map(cb => cb.value);
                }
    
                // Enhanced alert function with auto-dismiss and better styling
                function showAlert(type, message) {
                    // Remove existing alerts
                    const existingAlerts = document.querySelectorAll('.custom-alert');
                    existingAlerts.forEach(alert => alert.remove());
                    
                    const alertDiv = document.createElement('div');
                    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed custom-alert`;
                    alertDiv.style.cssText = `
                        top: 20px; 
                        right: 20px; 
                        z-index: 9999; 
                        min-width: 350px;
                        max-width: 500px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    `;
                    
                    const iconMap = {
                        'success': 'ri-check-circle-line',
                        'error': 'ri-error-warning-line',
                        'warning': 'ri-alert-line',
                        'info': 'ri-information-line'
                    };
                    
                    alertDiv.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="${iconMap[type] || 'ri-information-line'} me-2 fs-5"></i>
                            <div class="flex-grow-1">${message}</div>
                            <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
                        </div>
                    `;
                    
                    document.body.appendChild(alertDiv);
                    
                    // Auto remove after 5 seconds
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.style.opacity = '0';
                            setTimeout(() => alertDiv.remove(), 300);
                        }
                    }, 5000);
                }
    
                // Enhanced confirmation modal
                function showConfirmModal(title, message, type, onConfirm) {
                    const modalId = 'confirmModal';
                    let modal = document.getElementById(modalId);
                    
                    if (!modal) {
                        modal = document.createElement('div');
                        modal.id = modalId;
                        modal.className = 'modal fade';
                        modal.innerHTML = `
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body"></div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn confirm-btn">Confirm</button>
                                    </div>
                                </div>
                            </div>
                        `;
                        document.body.appendChild(modal);
                    }
                    
                    const modalTitle = modal.querySelector('.modal-title');
                    const modalBody = modal.querySelector('.modal-body');
                    const confirmBtn = modal.querySelector('.confirm-btn');
                    
                    modalTitle.textContent = title;
                    modalBody.innerHTML = message;
                    
                    // Set button style based on type
                    const btnClass = type === 'danger' ? 'btn-danger' : 
                                    type === 'warning' ? 'btn-warning' : 'btn-success';
                    confirmBtn.className = `btn ${btnClass}`;
                    confirmBtn.textContent = type === 'danger' ? 'Delete' : 
                                            type === 'warning' ? 'Reject' : 'Approve';
                    
                    // Set up confirm action
                    confirmBtn.onclick = function() {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        modalInstance.hide();
                        onConfirm();
                    };
                    
                    const modalInstance = new bootstrap.Modal(modal);
                    modalInstance.show();
                }
    
                // Loading overlay
                function showLoadingOverlay(message = 'Processing...') {
                    let overlay = document.getElementById('loadingOverlay');
                    if (!overlay) {
                        overlay = document.createElement('div');
                        overlay.id = 'loadingOverlay';
                        overlay.innerHTML = `
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <div class="text-center">
                                    <div class="spinner-border text-primary mb-3" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <div class="loading-message text-muted">${message}</div>
                                </div>
                            </div>
                        `;
                        overlay.style.cssText = `
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(255,255,255,0.9);
                            z-index: 10000;
                            display: none;
                        `;
                        document.body.appendChild(overlay);
                    }
                    
                    overlay.querySelector('.loading-message').textContent = message;
                    overlay.style.display = 'block';
                }
    
                function hideLoadingOverlay() {
                    const overlay = document.getElementById('loadingOverlay');
                    if (overlay) {
                        overlay.style.
                        display = 'none';
                }
            }       
             // Status change modal
             function showStatusChangeModal(id, currentStatus, name) {
                const modalId = 'statusChangeModal';
                let modal = document.getElementById(modalId);
                
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = modalId;
                    modal.className = 'modal fade';
                    modal.innerHTML = `
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Change Student Status</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Change status for: <strong class="student-name"></strong></p>
                                    <div class="mb-3">
                                        <label class="form-label">Current Status: <span class="current-status badge"></span></label>
                                    </div>
                                    <div class="mb-3">
                                        <label for="newStatus" class="form-label">New Status</label>
                                        <select class="form-select" id="newStatus">
                                            <option value="pending">Pending</option>
                                            <option value="enrolled">Enrolled</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="statusReason" class="form-label">Reason (Optional)</label>
                                        <textarea class="form-control" id="statusReason" rows="3" placeholder="Enter reason for status change..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" onclick="updateStudentStatus()">Update Status</button>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }
                
                modal.querySelector('.student-name').textContent = name;
                modal.querySelector('.current-status').textContent = currentStatus;
                modal.querySelector('.current-status').className = `badge bg-${getStatusColor(currentStatus)}`;
                modal.querySelector('#newStatus').value = currentStatus;
                modal.querySelector('#statusReason').value = '';
                
                // Store student ID for later use
                modal.dataset.studentId = id;
                
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
            }

            window.updateStudentStatus = function() {
                const modal = document.getElementById('statusChangeModal');
                const studentId = modal.dataset.studentId;
                const newStatus = modal.querySelector('#newStatus').value;
                const reason = modal.querySelector('#statusReason').value;
                
                showLoadingOverlay('Updating status...');
                
                fetch(`/admin/enrollments/${studentId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ 
                        status: newStatus,
                        reason: reason
                    })
                })
                .then(response => response.json())
                .then(data => {
                    hideLoadingOverlay();
                    if (data.success) {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        modalInstance.hide();
                        showAlert('success', 'Student status updated successfully!');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('error', data.message || 'Failed to update status');
                    }
                })
                .catch(error => {
                    hideLoadingOverlay();
                    showAlert('error', 'An error occurred while updating status.');
                    console.error('Error:', error);
                });
            };

            // Notification modal
            function showNotificationModal(selectedIds) {
                const modalId = 'notificationModal';
                let modal = document.getElementById(modalId);
                
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = modalId;
                    modal.className = 'modal fade';
                    modal.innerHTML = `
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Send Notification</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Send notification to <strong class="selected-count"></strong> selected students</p>
                                    <div class="mb-3">
                                        <label for="notificationSubject" class="form-label">Subject</label>
                                        <input type="text" class="form-control" id="notificationSubject" placeholder="Enter notification subject">
                                    </div>
                                    <div class="mb-3">
                                        <label for="notificationMessage" class="form-label">Message</label>
                                        <textarea class="form-control" id="notificationMessage" rows="5" placeholder="Enter your message here..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="notificationType" class="form-label">Notification Type</label>
                                        <select class="form-select" id="notificationType">
                                            <option value="email">Email</option>
                                            <option value="sms">SMS</option>
                                            <option value="both">Email & SMS</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" onclick="sendBulkNotification()">Send Notification</button>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }
                
                modal.querySelector('.selected-count').textContent = selectedIds.length;
                modal.querySelector('#notificationSubject').value = '';
                modal.querySelector('#notificationMessage').value = '';
                modal.querySelector('#notificationType').value = 'email';
                
                // Store selected IDs
                modal.dataset.selectedIds = JSON.stringify(selectedIds);
                
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
            }

            window.sendBulkNotification = function() {
                const modal = document.getElementById('notificationModal');
                const selectedIds = JSON.parse(modal.dataset.selectedIds);
                const subject = modal.querySelector('#notificationSubject').value;
                const message = modal.querySelector('#notificationMessage').value;
                const type = modal.querySelector('#notificationType').value;
                
                if (!subject.trim() || !message.trim()) {
                    showAlert('warning', 'Please fill in both subject and message fields.');
                    return;
                }
                
                showLoadingOverlay('Sending notifications...');
                
                fetch('/admin/enrollments/send-notification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        student_ids: selectedIds,
                        subject: subject,
                        message: message,
                        type: type
                    })
                })
                .then(response => response.json())
                .then(data => {
                    hideLoadingOverlay();
                    if (data.success) {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        modalInstance.hide();
                        showAlert('success', `Notifications sent to ${selectedIds.length} students!`);
                    } else {
                        showAlert('error', data.message || 'Failed to send notifications');
                    }
                })
                .catch(error => {
                    hideLoadingOverlay();
                    showAlert('error', 'An error occurred while sending notifications.');
                    console.error('Error:', error);
                });
            };

            // Utility function for status colors
            function getStatusColor(status) {
                switch(status) {
                    case 'enrolled': return 'success';
                    case 'pending': return 'warning';
                    case 'rejected': return 'danger';
                    default: return 'secondary';
                }
            }

            // Enhanced export functionality
            window.exportEnrollments = function(format = 'excel') {
                showLoadingOverlay('Preparing export...');
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/enrollments/export-all';
                form.style.display = 'none';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = token;
                form.appendChild(csrfInput);
                
                const formatInput = document.createElement('input');
                formatInput.type = 'hidden';
                formatInput.name = 'format';
                formatInput.value = format;
                form.appendChild(formatInput);
                
                // Add current filters
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.forEach((value, key) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
                
                hideLoadingOverlay();
                showAlert('info', 'Export started. Download will begin shortly...');
            };

            // Print functionality
            window.printEnrollments = function() {
                const selected = getSelectedStudents();
                if (selected.length === 0) {
                    showAlert('warning', 'Please select at least one student to print.');
                    return;
                }
                
                showLoadingOverlay('Preparing print...');
                
                const printWindow = window.open('/admin/enrollments/print', '_blank');
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/enrollments/print';
                form.target = '_blank';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = token;
                form.appendChild(csrfInput);
                
                selected.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'student_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
                
                hideLoadingOverlay();
            };

            // Quick actions
            window.quickApproveAll = function() {
                const pendingStudents = document.querySelectorAll('tr[data-status="pending"] .student-checkbox');
                if (pendingStudents.length === 0) {
                    showAlert('info', 'No pending students to approve.');
                    return;
                }
                
                // Select all pending students
                pendingStudents.forEach(checkbox => checkbox.checked = true);
                updateBulkActions();
                
                // Trigger bulk approve
                bulkApprove();
            };

            window.clearAllSelections = function() {
                document.querySelectorAll('.student-checkbox:checked').forEach(checkbox => {
                    checkbox.checked = false;
                });
                document.getElementById('selectAll').checked = false;
                updateBulkActions();
                showAlert('info', 'All selections cleared.');
            };

            // Enhanced select all functionality
            document.getElementById('selectAll').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.student-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActions();
                
                if (this.checked) {
                    showAlert('info', `${checkboxes.length} students selected.`);
                }
            });

            // Individual checkbox functionality with counter
            document.querySelectorAll('.student-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateBulkActions();
                    
                    // Update select all checkbox
                    const allCheckboxes = document.querySelectorAll('.student-checkbox');
                    const checkedCheckboxes = document.querySelectorAll('.student-checkbox:checked');
                    const selectAllCheckbox = document.getElementById('selectAll');
                    
                    if (checkedCheckboxes.length === allCheckboxes.length) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else if (checkedCheckboxes.length > 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    }
                });
            });

            function updateBulkActions() {
                const selectedCheckboxes = document.querySelectorAll('.student-checkbox:checked');
                const bulkActions = document.getElementById('bulkActions');
                const selectedCount = document.getElementById('selectedCount');

                if (selectedCheckboxes.length > 0) {
                    bulkActions.style.display = 'block';
                    selectedCount.textContent = selectedCheckboxes.length;
                    
                    // Update button states based on selection
                    updateBulkButtonStates(selectedCheckboxes);
                } else {
                    bulkActions.style.display = 'none';
                }
            }

            function updateBulkButtonStates(selectedCheckboxes) {
                const statuses = Array.from(selectedCheckboxes).map(cb => 
                    cb.closest('tr').dataset.status
                );
                
                const hasPending = statuses.includes('pending');
                const hasEnrolled = statuses.includes('enrolled');
                const hasRejected = statuses.includes('rejected');
                
                // Enable/disable buttons based on selection
                const approveBtn = document.querySelector('[onclick="bulkApprove()"]');
                const rejectBtn = document.querySelector('[onclick="bulkReject()"]');
                
                if (approveBtn) {
                    approveBtn.disabled = !hasPending;
                    approveBtn.title = hasPending ? 'Approve selected pending students' : 'No pending students selected';
                }
                
                if (rejectBtn) {
                    rejectBtn.disabled = !hasPending;
                    rejectBtn.title = hasPending ? 'Reject selected pending students' : 'No pending students selected';
                }
            }

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl+A to select all
                if (e.ctrlKey && key === 'a' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    document.getElementById('selectAll').click();
                }
                
                // Escape to clear selections
                if (e.key === 'Escape') {
                    clearAllSelections();
                }
                
                // Ctrl+E to export
                if (e.ctrlKey && e.key === 'e') {
                    e.preventDefault();
                    exportSelected();
                }
            });

            // Auto-refresh functionality
            let autoRefreshInterval;
            window.toggleAutoRefresh = function() {
                const btn = document.getElementById('autoRefreshBtn');
                
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = null;
                    btn.innerHTML = '<i class="ri-refresh-line me-1"></i>Auto Refresh: OFF';
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                    showAlert('info', 'Auto-refresh disabled.');
                } else {
                    autoRefreshInterval = setInterval(() => {
                        location.reload();
                    }, 30000); // Refresh every 30 seconds
                    
                    btn.innerHTML = '<i class="ri-refresh-line me-1"></i>Auto Refresh: ON';
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-success');
                    showAlert('info', 'Auto-refresh enabled (30 seconds).');
                }
            };

            // Initialize tooltips
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            // Initialize page
            updateBulkActions();
            
            // Show page load success
            showAlert('success', 'Enrollment management loaded successfully!');
        });
    </script>
</x-admin-layout>