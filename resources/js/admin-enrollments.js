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
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.status === 401) {
                hideLoadingOverlay();
                showAlert('error', 'Your session has expired. Please log in again.');
                setTimeout(() => {
                    window.location.href = '/admin/login';
                }, 2000);
                return;
            }
            
            if (response.status === 403) {
                hideLoadingOverlay();
                showAlert('error', 'You do not have permission to perform this action.');
                return;
            }
            
            return response.json().catch(() => {
                throw new Error(`Server returned ${response.status}: ${response.statusText}`);
            });
        })
        .then(data => {
            if (!data) return;
            
            hideLoadingOverlay();
            if (data.success) {
                showAlert('success', successMessage);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.message || 'Operation failed');
                
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                }
            }
        })
        .catch(error => {
            hideLoadingOverlay();
            console.error('Error:', error);
            
            if (error.message.includes('500')) {
                showAlert('error', 'Server error occurred. Please try again or contact support.');
            } else if (error.message.includes('404')) {
                showAlert('error', 'The requested resource was not found.');
            } else {
                showAlert('error', 'An error occurred. Please check your connection and try again.');
            }
        });
    }
    
    function performBulkAction(url, studentIds, successMessage) {
        showLoadingOverlay();
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ student_ids: studentIds })
        })
        .then(response => {
            if (response.status === 401) {
                hideLoadingOverlay();
                showAlert('error', 'Your session has expired. Please log in again.');
                setTimeout(() => {
                    window.location.href = '/admin/login';
                }, 2000);
                return;
            }
            
            if (response.status === 403) {
                hideLoadingOverlay();
                showAlert('error', 'You do not have permission to perform this action.');
                return;
            }
            
            return response.json().catch(() => {
                throw new Error(`Server returned ${response.status}: ${response.statusText}`);
            });
        })
        .then(data => {
            if (!data) return;
            
            hideLoadingOverlay();
            if (data.success) {
                showAlert('success', successMessage);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.message || 'Bulk operation failed');
                
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                }
            }
        })
        .catch(error => {
            hideLoadingOverlay();
            console.error('Error:', error);
            
            if (error.message.includes('500')) {
                showAlert('error', 'Server error occurred during bulk operation.');
            } else if (error.message.includes('404')) {
                showAlert('error', 'The requested resource was not found.');
            } else {
                showAlert('error', 'An error occurred during bulk operation.');
            }
        });
    }

    function getSelectedStudents() {
        const checkboxes = document.querySelectorAll('.student-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    // Enhanced alert function with auto-dismiss and better styling
    function showAlert(type, message) {
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
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.style.opacity = '0';
                setTimeout(() => alertDiv.remove(), 300);
            }
        }, 5000);
    }

    // Enhanced confirmation modal with Bootstrap fallback
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
        
        if (modalTitle) modalTitle.textContent = title;
        if (modalBody) modalBody.innerHTML = message;
        
        const btnClass = type === 'danger' ? 'btn-danger' : 
                        type === 'warning' ? 'btn-warning' : 'btn-success';
        if (confirmBtn) {
            confirmBtn.className = `btn ${btnClass}`;
            confirmBtn.textContent = type === 'danger' ? 'Delete' : 
                                    type === 'warning' ? 'Reject' : 'Approve';
        }
        
        if (confirmBtn) {
            confirmBtn.onclick = function() {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) modalInstance.hide();
                } else {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                }
                onConfirm();
            };
        }
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
        } else {
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
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

        const loadingMessage = overlay.querySelector('.loading-message');
        if (loadingMessage) loadingMessage.textContent = message;
        overlay.style.display = 'block';
    }

    function hideLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
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

        const studentNameEl = modal.querySelector('.student-name');
        const currentStatusEl = modal.querySelector('.current-status');
        const newStatusEl = modal.querySelector('#newStatus');
        const statusReasonEl = modal.querySelector('#statusReason');
        
        if (studentNameEl) studentNameEl.textContent = name;
        if (currentStatusEl) {
            currentStatusEl.textContent = currentStatus;
            currentStatusEl.className = `badge bg-${getStatusColor(currentStatus)}`;
        }
        if (newStatusEl) newStatusEl.value = currentStatus;
        if (statusReasonEl) statusReasonEl.value = '';
        
        modal.dataset.studentId = id;
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
        } else {
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
        }
    }

    window.updateStudentStatus = function() {
        const modal = document.getElementById('statusChangeModal');
        if (!modal) return;
        
        const studentId = modal.dataset.studentId;
        const newStatusEl = modal.querySelector('#newStatus');
        const statusReasonEl = modal.querySelector('#statusReason');
        
        if (!newStatusEl || !statusReasonEl) return;
        
        const newStatus = newStatusEl.value;
        const reason = statusReasonEl.value;
        
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
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) modalInstance.hide();
                } else {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                }
                
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
        
        const selectedCountEl = modal.querySelector('.selected-count');
        const subjectEl = modal.querySelector('#notificationSubject');
        const messageEl = modal.querySelector('#notificationMessage');
        const typeEl = modal.querySelector('#notificationType');
        
        if (selectedCountEl) selectedCountEl.textContent = selectedIds.length;
        if (subjectEl) subjectEl.value = '';
        if (messageEl) messageEl.value = '';
        if (typeEl) typeEl.value = 'email';
        
        modal.dataset.selectedIds = JSON.stringify(selectedIds);
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
        } else {
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
        }
    }

    window.sendBulkNotification = function() {
        const modal = document.getElementById('notificationModal');
        if (!modal) return;
        
        const selectedIds = JSON.parse(modal.dataset.selectedIds || '[]');
        const subjectEl = modal.querySelector('#notificationSubject');
        const messageEl = modal.querySelector('#notificationMessage');
        const typeEl = modal.querySelector('#notificationType');
        
        if (!subjectEl || !messageEl || !typeEl) return;
        
        const subject = subjectEl.value;
        const message = messageEl.value;
        const type = typeEl.value;
        
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
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) modalInstance.hide();
                } else {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                }
                
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
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/enrollments/print';
        form.target = '_blank';
        form.style.display = 'none';
        
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
        
        pendingStudents.forEach(checkbox => checkbox.checked = true);
        updateBulkActions();
        
        bulkApprove();
    };

    window.clearAllSelections = function() {
        document.querySelectorAll('.student-checkbox:checked').forEach(checkbox => {
            checkbox.checked = false;
        });
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) selectAllCheckbox.checked = false;
        updateBulkActions();
        showAlert('info', 'All selections cleared.');
    };

    // Enhanced select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
            
            if (this.checked) {
                showAlert('info', `${checkboxes.length} students selected.`);
            }
        });
    }

    // Individual checkbox functionality with counter
    document.querySelectorAll('.student-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActions();
            
            const allCheckboxes = document.querySelectorAll('.student-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.student-checkbox:checked');
            const selectAllCheckbox = document.getElementById('selectAll');
            
            if (selectAllCheckbox) {
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
            }
        });
    });

    function updateBulkActions() {
        const selectedCheckboxes = document.querySelectorAll('.student-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');

        if (bulkActions && selectedCount) {
            if (selectedCheckboxes.length > 0) {
                bulkActions.style.display = 'block';
                selectedCount.textContent = selectedCheckboxes.length;
                
                updateBulkButtonStates(selectedCheckboxes);
            } else {
                bulkActions.style.display = 'none';
            }
        }
    }

    function updateBulkButtonStates(selectedCheckboxes) {
        const statuses = Array.from(selectedCheckboxes).map(cb => 
            cb.closest('tr').dataset.status
        );
        
        const hasPending = statuses.includes('pending');
        
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

    // Keyboard shortcuts - FIXED: Remove conflicting search auto-submit
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'a' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            const selectAllCheckbox = document.getElementById('selectAll');
            if (selectAllCheckbox) selectAllCheckbox.click();
        }
    
        if (e.key === 'Escape') {
            clearAllSelections();
        }
    
        if (e.ctrlKey && e.key === 'e') {
            e.preventDefault();
            exportSelected();
        }
    });

    // Auto-refresh functionality
    let autoRefreshInterval;
    window.toggleAutoRefresh = function() {
        const btn = document.getElementById('autoRefreshBtn');
        if (!btn) return;
        
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
            }, 30000);
            
            btn.innerHTML = '<i class="ri-refresh-line me-1"></i>Auto Refresh: ON';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            showAlert('info', 'Auto-refresh enabled (30 seconds).');
        }
    };

    // Initialize tooltips with fallback
    function initializeTooltips() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    // Initialize page
    updateBulkActions();
    initializeTooltips();

    // Clean up intervals on page unload
    window.addEventListener('beforeunload', function() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    });

    // Additional utility functions for better UX
    window.refreshPage = function() {
        showLoadingOverlay('Refreshing...');
        location.reload();
    };

    // Handle modal close events
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-close') || e.target.getAttribute('data-bs-dismiss') === 'modal') {
            const modal = e.target.closest('.modal');
            if (modal) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) modalInstance.hide();
                } else {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                }
            }
        }
    });

    // Handle escape key for modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) modalInstance.hide();
                } else {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                }
            });
        }
    });

    // Enhanced error handling for network issues
    window.addEventListener('online', function() {
        showAlert('success', 'Connection restored!');
    });

    window.addEventListener('offline', function() {
        showAlert('warning', 'Connection lost. Some features may not work properly.');
    });

    // FIXED: Remove the problematic debounced search that was interfering with filters
    // The search input will now work normally with the form submission
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        // Remove the auto-submit functionality that was causing issues
        // Let the form handle search normally
        console.log('Search input found, normal form submission enabled');
    }

    // FIXED: Ensure filter form works properly
    const filterForm = document.querySelector('form[method="GET"]');
    if (filterForm) {
        // Remove the loading overlay that was preventing form submission
        filterForm.addEventListener('submit', function(e) {
            // Don't prevent default or show loading overlay for GET forms
            console.log('Filter form submitted');
        });
    }

    // Add confirmation for dangerous actions
    const dangerousButtons = document.querySelectorAll('.btn-danger, .btn-outline-danger');
    dangerousButtons.forEach(button => {
        if (!button.onclick && button.textContent.toLowerCase().includes('delete')) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const confirmed = confirm('Are you sure you want to perform this action? This cannot be undone.');
                if (confirmed) {
                    if (this.onclick) {
                        this.onclick();
                    }
                }
            });
        }
    });

    // FIXED: Only add loading state to non-form buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn') && 
            !e.target.classList.contains('btn-close') &&
            e.target.type !== 'submit' &&
            !e.target.closest('form')) {
            
            const button = e.target;
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Loading...';
            
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            }, 3000);
        }
    });

    // Add smooth scrolling for better UX
    const smoothScrollLinks = document.querySelectorAll('a[href^="#"]');
    smoothScrollLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add table row hover effects
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Add progress indicator for bulk operations
    function showProgressModal(title, total) {
        const modalId = 'progressModal';
        let modal = document.getElementById(modalId);
        
        if (!modal) {
            modal = document.createElement('div');
            modal.id = modalId;
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                        </div>
                        <div class="modal-body">
                            <div class="progress mb-3">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <p class="text-center mb-0">
                                Processing <span class="current">0</span> of <span class="total">${total}</span> items...
                            </p>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = new bootstrap.Modal(modal, { backdrop: 'static', keyboard: false });
            modalInstance.show();
        } else {
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
        }
        
        return modal;
    }

    // Console log for debugging
    console.log('Admin Enrollments JS loaded successfully');

    // Show success message if page loaded successfully
    setTimeout(() => {
        if (document.readyState === 'complete') {
            console.log('Page fully loaded and interactive');
        }
    }, 1000);

}); // End of DOMContentLoaded event listener

// Global error handler
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        // Only show alerts in development
        console.log('Development mode - JavaScript Error:', e.message);
    }
});

// Global unhandled promise rejection handler
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled Promise Rejection:', e.reason);
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        // Only show alerts in development
        console.log('Development mode - Promise Rejection:', e.reason);
    }
});
