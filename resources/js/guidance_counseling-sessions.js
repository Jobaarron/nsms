// Set session ID for approval modal
function setApproveSessionId(id) {
    document.getElementById('approveSessionId').value = id;
}

window.setApproveSessionId = setApproveSessionId;

// Approve counseling session
function submitApproveSession(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="ri-loader-4-line me-2 spinner-border spinner-border-sm"></i>Approving...';
    submitBtn.disabled = true;

    // Debug: log form data
    for (let [key, value] of formData.entries()) {
        console.log(key + ':', value);
    }

    fetch('/guidance/counseling-sessions/approve', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(async response => {
        if (!response.ok) {
            // Try to parse validation errors
            let errorMsg = 'Error approving session';
            try {
                const errorData = await response.json();
                if (errorData.errors) {
                    errorMsg = Object.values(errorData.errors).map(arr => arr.join(' ')).join(' ');
                }
            } catch (e) {}
            showAlert(errorMsg, 'danger');
            throw new Error(errorMsg);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert('Session approved and scheduled!', 'success');
            closeModal('approveSessionModal');
            refreshCounselingSessions();
        } else {
            showAlert(data.message || 'Failed to approve session', 'danger');
        }
    })
    .catch(error => {
        showAlert(error.message || 'Error approving session', 'danger');
    })
    .finally(() => {
        submitBtn.innerHTML = 'Approve';
        submitBtn.disabled = false;
    });
}

window.submitApproveSession = submitApproveSession;

// Guidance Counseling Sessions JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Counseling Sessions page loaded');
    
    // Initialize page functionality
    initializeCounselingSessions();

    // Instant search for counseling sessions table
    const searchInput = document.getElementById('search-filter');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = searchInput.value.trim().toLowerCase();
            const rows = document.querySelectorAll('#counseling-sessions-table tbody tr');
            rows.forEach(row => {
                // Find student name in first cell, inside .fw-semibold if present
                let studentCell = row.querySelector('td:first-child .fw-semibold');
                if (!studentCell) {
                    studentCell = row.querySelector('td:first-child');
                }
                if (studentCell) {
                    const text = studentCell.textContent.trim().toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                }
            });
        });
    }
});

// Initialize counseling sessions functionality
function initializeCounselingSessions() {
    // Set up form validation
    setupFormValidation();
    
    // Set up date/time constraints
    setupDateTimeConstraints();
    
    // Set up follow-up toggle
    setupFollowUpToggle();
}

// Setup form validation
function setupFormValidation() {
    const form = document.getElementById('scheduleCounselingForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitCounselingSession(e);
        });
    }
}

// Setup date/time constraints
function setupDateTimeConstraints() {
    const dateInput = document.querySelector('input[name="scheduled_date"]');
    const followUpDateInput = document.querySelector('input[name="follow_up_date"]');
    
    if (dateInput) {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
    }
    
    if (followUpDateInput) {
        // Set minimum follow-up date to today
        const today = new Date().toISOString().split('T')[0];
        followUpDateInput.min = today;
    }
}

// Setup follow-up toggle functionality
function setupFollowUpToggle() {
    const followUpCheckbox = document.getElementById('followUpRequired');
    const followUpContainer = document.getElementById('followUpDateContainer');
    
    if (followUpCheckbox && followUpContainer) {
        followUpCheckbox.addEventListener('change', function() {
            followUpContainer.style.display = this.checked ? 'block' : 'none';
            const followUpInput = followUpContainer.querySelector('input[name="follow_up_date"]');
            if (followUpInput) {
                followUpInput.required = this.checked;
            }
        });
    }
}

// Submit counseling session form
function submitCounselingSession(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ri-loader-4-line me-2 spinner-border spinner-border-sm"></i>Scheduling...';
    submitBtn.disabled = true;
    
    fetch('/guidance/counseling-sessions', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Counseling session scheduled successfully!', 'success');
            closeModal('scheduleCounselingModal');
            form.reset();
            // Reset follow-up container
            const followUpContainer = document.getElementById('followUpDateContainer');
            if (followUpContainer) {
                followUpContainer.style.display = 'none';
            }
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to schedule counseling session', 'danger');
        }
    })
    .catch(error => {
        console.error('Error scheduling counseling session:', error);
        showAlert('An error occurred while scheduling the session', 'danger');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Complete counseling session
function completeCounselingSession(sessionId) {
    const summary = prompt('Please provide a brief summary of the counseling session:');
    if (summary === null) {
        return; // User cancelled
    }
    
    if (summary.trim() === '') {
        showAlert('Please provide a session summary', 'warning');
        return;
    }
    
    fetch(`/guidance/counseling-sessions/${sessionId}/complete`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            summary: summary.trim()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Counseling session marked as completed', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to complete session', 'danger');
        }
    })
    .catch(error => {
        console.error('Error completing session:', error);
        showAlert('Error completing session', 'danger');
    });
}

// Schedule recommended counseling session
function scheduleRecommendedSession(sessionId) {
    // Show modal for scheduling recommended session
    const modalHtml = `
        <div class="modal fade" id="scheduleRecommendedModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Schedule Recommended Counseling Session</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="scheduleRecommendedForm" onsubmit="submitScheduleRecommended(event, ${sessionId})">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Counselor <span class="text-danger">*</span></label>
                                    <select class="form-select" name="counselor_id" required>
                                        <option value="">Select Counselor</option>
                                        <!-- Counselors will be loaded dynamically -->
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="scheduled_date" required min="${new Date().toISOString().split('T')[0]}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" name="scheduled_time" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                                    <select class="form-select" name="duration" required>
                                        <option value="30">30 minutes</option>
                                        <option value="45">45 minutes</option>
                                        <option value="60" selected>60 minutes</option>
                                        <option value="90">90 minutes</option>
                                        <option value="120">120 minutes</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Location</label>
                                    <input type="text" class="form-control" name="location" placeholder="e.g., Guidance Office, Conference Room">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="ri-calendar-check-line me-2"></i>Schedule Session
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('scheduleRecommendedModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add new modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Load counselors
    loadCounselors();

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('scheduleRecommendedModal'));
    modal.show();
}

// Load counselors for the dropdown
function loadCounselors() {
    fetch('/guidance/api/counselors', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const counselorSelect = document.querySelector('#scheduleRecommendedModal select[name="counselor_id"]');
        if (counselorSelect && data.counselors) {
            counselorSelect.innerHTML = '<option value="">Select Counselor</option>';
            data.counselors.forEach(counselor => {
                counselorSelect.innerHTML += `<option value="${counselor.id}">${counselor.name}</option>`;
            });
        }
    })
    .catch(error => {
        console.error('Error loading counselors:', error);
    });
}

// Submit schedule recommended form
function submitScheduleRecommended(event, sessionId) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ri-loader-4-line me-2 spinner-border spinner-border-sm"></i>Scheduling...';
    submitBtn.disabled = true;

    fetch(`/guidance/counseling-sessions/${sessionId}/schedule-recommended`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Recommended counseling session scheduled successfully!', 'success');
            closeModal('scheduleRecommendedModal');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to schedule session', 'danger');
        }
    })
    .catch(error => {
        console.error('Error scheduling recommended session:', error);
        showAlert('An error occurred while scheduling the session', 'danger');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Reschedule counseling session
function rescheduleCounselingSession(sessionId) {
    const newDate = prompt('Enter new date (YYYY-MM-DD):');
    if (!newDate) return;

    const newTime = prompt('Enter new time (HH:MM):');
    if (!newTime) return;

    // Basic validation
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    const timeRegex = /^\d{2}:\d{2}$/;

    if (!dateRegex.test(newDate)) {
        showAlert('Invalid date format. Please use YYYY-MM-DD', 'warning');
        return;
    }

    if (!timeRegex.test(newTime)) {
        showAlert('Invalid time format. Please use HH:MM', 'warning');
        return;
    }

    fetch(`/guidance/counseling-sessions/${sessionId}/reschedule`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            scheduled_date: newDate,
            scheduled_time: newTime
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Counseling session rescheduled successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to reschedule session', 'danger');
        }
    })
    .catch(error => {
        console.error('Error rescheduling session:', error);
        showAlert('Error rescheduling session', 'danger');
    });
}

// Edit counseling session
function editCounselingSession(sessionId) {
    // Redirect to edit page or show edit modal
    window.location.href = `/guidance/counseling-sessions/${sessionId}/edit`;
}

// Filter counseling sessions
function filterCounselingSessions() {
    const status = document.getElementById('status-filter')?.value || '';
    const search = document.getElementById('search-filter')?.value || '';

    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (search) params.append('search', search);

    const url = `/guidance/counseling-sessions${params.toString() ? '?' + params.toString() : ''}`;
    window.location.href = url;
}

// Clear filters
function clearFilters() {
    const statusFilter = document.getElementById('status-filter');
    const searchFilter = document.getElementById('search-filter');

    if (statusFilter) statusFilter.value = '';
    if (searchFilter) searchFilter.value = '';

    window.location.href = '/guidance/counseling-sessions';
}

// Refresh counseling sessions
function refreshCounselingSessions() {
    window.location.reload();
}

// Export counseling sessions
function exportCounselingSessions() {
    window.open('/guidance/counseling-sessions/export', '_blank');
}

// Print counseling sessions
function printCounselingSessions() {
    window.print();
}

// Close modal helper
function closeModal(modalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
    if (modal) {
        modal.hide();
    }
}

// Show alert helper
function showAlert(message, type = 'info') {
    // Create alert element
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="ri-information-line me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Find or create alert container
    let container = document.getElementById('alert-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'alert-container';
        container.className = 'position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    // Add alert
    container.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        const alerts = container.querySelectorAll('.alert');
        if (alerts.length > 0) {
            alerts[0].remove();
        }
    }, 5000);
}

// Start inline scheduling for a session
function startInlineScheduling(sessionId) {
    const row = document.querySelector(`tr[data-session-id="${sessionId}"]`);
    if (!row) return;

    // Hide action buttons, show edit buttons
    const actionButtons = row.querySelector('.action-buttons');
    const editButtons = row.querySelector('.edit-buttons');
    if (actionButtons) actionButtons.classList.add('d-none');
    if (editButtons) editButtons.classList.remove('d-none');

    // Show edit inputs, hide display values
    const editableCells = row.querySelectorAll('.editable-cell');
    editableCells.forEach(cell => {
        const displayValue = cell.querySelector('.display-value');
        const editInput = cell.querySelector('.edit-input');
        if (displayValue) displayValue.classList.add('d-none');
        if (editInput) editInput.classList.remove('d-none');
    });
}

// Save inline scheduling changes
function saveInlineScheduling(sessionId) {
    const row = document.querySelector(`tr[data-session-id="${sessionId}"]`);
    if (!row) return;

    const dateInput = row.querySelector('input[name="scheduled_date"]');
    const timeInput = row.querySelector('input[name="scheduled_time"]');

    const scheduledDate = dateInput ? dateInput.value : '';
    const scheduledTime = timeInput ? timeInput.value : '';

    // Basic validation
    if (!scheduledDate || !scheduledTime) {
        showAlert('Please provide both date and time', 'warning');
        return;
    }

    fetch(`/guidance/counseling-sessions/${sessionId}/schedule-inline`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            scheduled_date: scheduledDate,
            scheduled_time: scheduledTime
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Counseling session scheduled successfully!', 'success');
            // Update display values
            const dateCell = row.querySelector('.editable-cell[data-field="scheduled_date"]');
            const timeCell = row.querySelector('.editable-cell[data-field="scheduled_time"]');
            if (dateCell) {
                const displayValue = dateCell.querySelector('.display-value');
                if (displayValue) {
                    const date = new Date(scheduledDate);
                    displayValue.textContent = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                }
            }
            if (timeCell) {
                const displayValue = timeCell.querySelector('.display-value');
                if (displayValue) {
                    const [hours, minutes] = scheduledTime.split(':');
                    const time = new Date();
                    time.setHours(hours, minutes);
                    displayValue.textContent = time.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                }
            }
            // Update status
            const statusCell = row.querySelector('td:nth-child(6) .badge');
            if (statusCell) {
                statusCell.className = 'badge bg-primary';
                statusCell.textContent = 'Scheduled';
            }
            row.setAttribute('data-status', 'scheduled');
            cancelInlineScheduling(sessionId); // Revert to display mode
        } else {
            showAlert(data.message || 'Failed to schedule session', 'danger');
        }
    })
    .catch(error => {
        console.error('Error scheduling session:', error);
        showAlert('Error scheduling session', 'danger');
    });
}

// Cancel inline scheduling
function cancelInlineScheduling(sessionId) {
    const row = document.querySelector(`tr[data-session-id="${sessionId}"]`);
    if (!row) return;

    // Show action buttons, hide edit buttons
    const actionButtons = row.querySelector('.action-buttons');
    const editButtons = row.querySelector('.edit-buttons');
    if (actionButtons) actionButtons.classList.remove('d-none');
    if (editButtons) editButtons.classList.add('d-none');

    // Show display values, hide edit inputs
    const editableCells = row.querySelectorAll('.editable-cell');
    editableCells.forEach(cell => {
        const displayValue = cell.querySelector('.display-value');
        const editInput = cell.querySelector('.edit-input');
        if (displayValue) displayValue.classList.remove('d-none');
        if (editInput) editInput.classList.add('d-none');
    });
}

// Show Approve Counseling Session modal
function showApproveCounselingModal(sessionId) {
    const modal = document.getElementById('approveCounselingModal');
    if (modal) {
        // Optionally set sessionId in a hidden field if needed
        modal.querySelector('form').reset();
        // Show modal using Bootstrap
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}

// Show static counseling request form PDF in modal
function showPdfModal(sessionId = null) {
    try {
        // Use new PdfController route for data fetch
        let pdfUrl = "/pdf/counseling-session";
        if (sessionId) {
            pdfUrl += `?session_id=${sessionId}`;
        }
        // Debug: log PDF URL and sessionId
        console.log('PDF URL:', pdfUrl);
        console.log('Session ID:', sessionId);

        const pdfFrame = document.getElementById('pdfFrame');
        const modalElement = document.getElementById('pdfPreviewModal');

        if (!pdfFrame) {
            showAlert('PDF viewer not available. Please refresh the page.', 'danger');
            return;
        }
        if (!modalElement) {
            showAlert('PDF modal not available. Please refresh the page.', 'danger');
            return;
        }
        if (typeof bootstrap === 'undefined') {
            showAlert('UI framework not loaded. Please refresh the page.', 'danger');
            return;
        }

        pdfFrame.src = pdfUrl;
        const modal = new bootstrap.Modal(modalElement);
        modal.show();

        pdfFrame.onload = function() {
            pdfFrame.style.width = '100%';
            pdfFrame.style.height = '100%';
            pdfFrame.style.display = 'block';
        };
        pdfFrame.onerror = function() {
            showAlert('Failed to load PDF file. Please check if the file exists.', 'danger');
        };
    } catch (error) {
        showAlert('Error opening PDF: ' + error.message, 'danger');
    }
}

// Download PDF function


// Debug PDF modal function
function debugPdfModal() {
    console.log('=== PDF Modal Debug Info ===');
    console.log('Bootstrap:', typeof bootstrap);
    console.log('Modal Element:', document.getElementById('pdfPreviewModal'));
    console.log('PDF Frame:', document.getElementById('pdfFrame'));
    console.log('showPdfModal function:', typeof showPdfModal);
    
    // Test if we can create a modal
    const testModal = document.getElementById('pdfPreviewModal');
    if (testModal) {
        console.log('Modal found, attempting to show...');
        const modal = new bootstrap.Modal(testModal);
        modal.show();
    } else {
        console.error('Modal not found in DOM');
    }
}

// Expose functions to global scope for onclick handlers
    window.showPdfModal = showPdfModal;
    window.debugPdfModal = debugPdfModal;
window.completeCounselingSession = completeCounselingSession;
window.scheduleRecommendedSession = scheduleRecommendedSession;
window.rescheduleCounselingSession = rescheduleCounselingSession;
window.editCounselingSession = editCounselingSession;
window.filterCounselingSessions = filterCounselingSessions;
window.clearFilters = clearFilters;
window.refreshCounselingSessions = refreshCounselingSessions;
window.exportCounselingSessions = exportCounselingSessions;
window.printCounselingSessions = printCounselingSessions;
window.startInlineScheduling = startInlineScheduling;
window.saveInlineScheduling = saveInlineScheduling;
window.cancelInlineScheduling = cancelInlineScheduling;
window.showApproveCounselingModal = showApproveCounselingModal;
window.showPdfModal = showPdfModal;
window.downloadPdf = downloadPdf;
window.debugPdfModal = debugPdfModal;