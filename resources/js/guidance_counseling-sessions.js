// Show counseling session detail modal (schedule tab)
function showSessionDetailModal(sessionId) {
    const modalElem = document.getElementById('sessionDetailModal');
    const modalBody = document.getElementById('sessionDetailModalBody');
    if (modalBody) {
        modalBody.innerHTML = '<div class="text-center text-muted py-5"><i class="ri-loader-4-line spinner-border spinner-border-sm"></i> Loading details...</div>';
    }
    // Show modal
    const modal = bootstrap.Modal.getOrCreateInstance(modalElem);
    modal.show();
    // Fetch session details via AJAX
        fetch(`/guidance/counseling-sessions/api/counseling-sessions/${sessionId}`)
                .then(response => response.json())
                .then(data => {
                        if (data.success && data.session) {
                                const s = data.session;
                                // Schedule/Status Card (left column)
            const scheduleStatusBox = `
                     <div class="card mb-3 border-primary h-100" style="border-width:2px; min-height:420px;">
                                                                                                        <div class="card-header bg-primary bg-opacity-10 text-primary d-flex align-items-center" style="font-weight:500; font-size:1.25rem;">
                                                                                                            <i class="ri-calendar-check-line me-2"></i> Schedule & Status
                                                                                                        </div>
                                                                                                        <div class="card-body p-4">
                                                                                                            <ul class="ps-3" style="font-size:1.25rem; list-style:none; padding-left:0;">
                                                                                                                <li class="mb-2">Date: <span class='fw-normal'>${s.scheduled_date ?? '-'}</span></li>
                                                                                                                <li class="mb-2">Time: <span class='fw-normal'>${s.scheduled_time ?? '-'}</span></li>
                                                                                                                <li class="mb-2">Status: ${s.status_display ? `<span class='fw-normal badge bg-success'>${s.status_display}</span>` : '-'}</li>
                                                                                                            </ul>
                                                                                                        </div>
                                                                                                    </div>
                                 `;
                                // Personal Info Card (top right)
                                const personalInfo = `
                                    <div class="card mb-3 border-success" style="border-width:2px;">
                                        <div class="card-header bg-success bg-opacity-10 text-success d-flex align-items-center" style="font-weight:500;">
                                            <i class="ri-user-3-line me-2"></i> Personal Information
                                        </div>
                                        <div class="card-body p-3">
                                            <table class="table table-borderless mb-0">
                                                <tbody>
                                                    <tr><td class="fw-bold">Full Name</td><td>${s.student_full_name ?? '-'}</td></tr>
                                                    <tr><td class="fw-bold">Date of Birth</td><td>${s.student_birthdate ?? '-'}${s.student_age ? ` (${s.student_age} years old)` : ''}</td></tr>
                                                    <tr><td class="fw-bold">Gender</td><td>${s.student_gender ?? '-'}</td></tr>
                                                    <tr><td class="fw-bold">Nationality</td><td>${s.student_nationality ?? '-'}</td></tr>
                                                    <tr><td class="fw-bold">Religion</td><td>${s.student_religion ?? '-'}</td></tr>
                                                    <tr><td class="fw-bold">Student Type</td><td><span class="badge bg-success">${s.student_type_badge ?? 'New'}</span> <span class="text-muted small ms-1">${s.student_type_desc ?? ''}</span></td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                `;
                                // Documents Card (bottom right)
                                const pdfUrl = `/pdf/counseling-session?session_id=${sessionId}`;
                                const documentsCard = `
                                    <div class="card border-success" style="border-width:2px;">
                                        <div class="card-header bg-success bg-opacity-10 text-success" style="font-weight:500;">
                                            <span><i class="ri-file-list-3-line me-2"></i> Documents</span>
                                        </div>
                                        <div class="w-100 px-3 pt-2 pb-0">
                                            <a href="${pdfUrl}" class="btn btn-sm btn-outline-success w-100 mb-2" target="_blank" title="Download Counseling Session PDF">
                                                <i class="ri-download-2-line"></i> Student Profile Recommendation Letter
                                            </a>
                                        </div>
                                        <div class="card-body p-3">
                                            ${s.documents_html ?? '<span class="text-muted">No documents uploaded.</span>'}
                                        </div>
                                    </div>
                                `;
                                modalBody.innerHTML = `
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-6">
                                            ${scheduleStatusBox}
                                        </div>
                                        <div class="col-12 col-lg-6 d-flex flex-column">
                                            ${personalInfo}
                                            ${documentsCard}
                                        </div>
                                    </div>
                                `;
                        } else {
                                modalBody.innerHTML = '<div class="text-danger">Failed to load session details.</div>';
                        }
                })
                .catch(() => {
                        modalBody.innerHTML = '<div class="text-danger">Error loading session details.</div>';
                });
}
window.showSessionDetailModal = showSessionDetailModal;
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

    // Handle feedback form submission (Reject with feedback)
    const feedbackForm = document.getElementById('feedbackForm');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const feedback = feedbackForm.elements['feedback'].value.trim();
            if (!feedback) {
                showAlert('Please provide feedback for rejection.', 'warning');
                return;
            }
            // Try to get sessionId from PDF iframe src (if available)
            let sessionId = null;
            const pdfFrame = document.getElementById('pdfFrame');
            if (pdfFrame && pdfFrame.src) {
                try {
                    const url = new URL(pdfFrame.src, window.location.origin);
                    sessionId = url.searchParams.get('session_id');
                } catch (e) {}
            }
            if (!sessionId) {
                showAlert('Session ID not found. Please try again.', 'danger');
                return;
            }
            const submitBtn = feedbackForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="ri-loader-4-line me-2 spinner-border spinner-border-sm"></i>Submitting...';
            submitBtn.disabled = true;
            fetch(`/guidance/counseling-sessions/${sessionId}/reject-with-feedback`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ feedback })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Feedback submitted and session archived.', 'success');
                    closeModal('feedbackModal');
                    setTimeout(() => { window.location.reload(); }, 1500);
                } else {
                    showAlert(data.message || 'Failed to submit feedback.', 'danger');
                }
            })
            .catch(error => {
                showAlert(error.message || 'Error submitting feedback.', 'danger');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
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

// Accept PDF session: close PDF modal and open Approve Counseling Session modal
function acceptPdfSession(sessionId = null) {
    // Hide the PDF modal
    const pdfModalElem = document.getElementById('pdfPreviewModal');
    if (pdfModalElem) {
        try {
            let pdfModal = bootstrap.Modal.getInstance(pdfModalElem);
            if (!pdfModal) pdfModal = bootstrap.Modal.getOrCreateInstance(pdfModalElem);
            pdfModal.hide();
        } catch (e) {
            pdfModalElem.classList.remove('show');
            pdfModalElem.setAttribute('aria-hidden', 'true');
            pdfModalElem.style.display = 'none';
            document.body.classList.remove('modal-open');
            let backdrops = document.getElementsByClassName('modal-backdrop');
            while (backdrops.length > 0) backdrops[0].parentNode.removeChild(backdrops[0]);
        }
    }
    // Try to extract sessionId from PDF iframe src if not provided
    if (!sessionId) {
        const pdfFrame = document.getElementById('pdfFrame');
        if (pdfFrame && pdfFrame.src) {
            const url = new URL(pdfFrame.src, window.location.origin);
            sessionId = url.searchParams.get('session_id');
        }
    }
    // Show Approve Counseling Session modal
    setTimeout(() => {
        const approveModalElem = document.getElementById('approveSessionModal');
        if (approveModalElem) {
            // Set session id if available
            const approveSessionIdInput = document.getElementById('approveSessionId');
            if (approveSessionIdInput && sessionId) approveSessionIdInput.value = sessionId;
            let approveModal = bootstrap.Modal.getOrCreateInstance(approveModalElem);
            approveModal.show();
        }
    }, 400); // Wait for fade-out
}

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
window.acceptPdfSession = acceptPdfSession;
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
window.downloadPdf = downloadPdf;