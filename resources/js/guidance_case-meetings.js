// Guidance Case Meetings JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Case Meetings page loaded');
    
    // Initialize page functionality
    initializeCaseMeetings();
});

// Initialize case meetings functionality
function initializeCaseMeetings() {
    // Set up form validation
    setupFormValidation();
    
    // Set up date/time constraints
    setupDateTimeConstraints();
}

// Setup form validation
function setupFormValidation() {
    const form = document.getElementById('scheduleCaseMeetingForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitCaseMeeting(e);
        });
    }
}

// Setup date/time constraints
function setupDateTimeConstraints() {
    const dateInput = document.querySelector('input[name="scheduled_date"]');
    if (dateInput) {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
    }
}

// Submit case meeting form
function submitCaseMeeting(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ri-loader-4-line me-2 spinner-border spinner-border-sm"></i>Scheduling...';
    submitBtn.disabled = true;
    
    fetch('/guidance/case-meetings', {
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
            showAlert('Case meeting scheduled successfully!', 'success');
            closeModal('scheduleCaseMeetingModal');
            form.reset();
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to schedule case meeting', 'danger');
        }
    })
    .catch(error => {
        console.error('Error scheduling case meeting:', error);
        showAlert('An error occurred while scheduling the meeting', 'danger');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// View case meeting details
function viewCaseMeeting(meetingId) {
    fetch(`/guidance/case-meetings/${meetingId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showCaseMeetingModal(data.meeting);
        } else {
            showAlert('Failed to load meeting details', 'danger');
        }
    })
    .catch(error => {
        console.error('Error loading meeting:', error);
        showAlert('Error loading meeting details', 'danger');
    });
}

// Show case meeting details modal
function showCaseMeetingModal(meeting) {
    const modalHtml = `
        <div class="modal fade" id="viewCaseMeetingModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Case Meeting Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Student</label>
                                <p class="form-control-plaintext">${meeting.student_name}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Meeting Type</label>
                                <p class="form-control-plaintext">
                                    <span class="badge ${meeting.meeting_type === 'house_visit' ? 'bg-info' : 'bg-primary'}">
                                        ${meeting.meeting_type_display}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date & Time</label>
                                <p class="form-control-plaintext">${meeting.scheduled_date} at ${meeting.scheduled_time}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Location</label>
                                <p class="form-control-plaintext">${meeting.location || 'TBD'}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <p class="form-control-plaintext">
                                    <span class="badge ${meeting.status_class}">${meeting.status_text}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Urgency Level</label>
                                <p class="form-control-plaintext">
                                    ${meeting.urgency_level ? `<span class="badge bg-${meeting.urgency_color}">${meeting.urgency_level}</span>` : 'Normal'}
                                </p>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Reason</label>
                                <p class="form-control-plaintext">${meeting.reason}</p>
                            </div>
                            ${meeting.notes ? `
                            <div class="col-12">
                                <label class="form-label fw-bold">Notes</label>
                                <p class="form-control-plaintext">${meeting.notes}</p>
                            </div>
                            ` : ''}
                            ${meeting.summary ? `
                            <div class="col-12">
                                <label class="form-label fw-bold">Summary</label>
                                <p class="form-control-plaintext">${meeting.summary}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    <div class="modal-footer">
                        ${['scheduled', 'in_progress'].includes(meeting.status) ? `
                            <button type="button" class="btn btn-success" onclick="completeCaseMeeting(${meeting.id}); closeModal('viewCaseMeetingModal');">
                                <i class="ri-check-line me-2"></i>Mark Complete
                            </button>
                            <button type="button" class="btn btn-warning" onclick="forwardToPresident(${meeting.id}); closeModal('viewCaseMeetingModal');">
                                <i class="ri-send-plane-line me-2"></i>Forward to President
                            </button>
                        ` : ''}
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('viewCaseMeetingModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add new modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewCaseMeetingModal'));
    modal.show();
}

// Complete case meeting
function completeCaseMeeting(meetingId) {
    if (!confirm('Mark this case meeting as completed?')) {
        return;
    }
    
    fetch(`/guidance/case-meetings/${meetingId}/complete`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Case meeting marked as completed', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to complete meeting', 'danger');
        }
    })
    .catch(error => {
        console.error('Error completing meeting:', error);
        showAlert('Error completing meeting', 'danger');
    });
}

// Forward case to president
function forwardToPresident(meetingId) {
    const reason = prompt('Please provide a reason for forwarding this case to the president:');
    if (!reason || reason.trim() === '') {
        return;
    }
    
    fetch(`/guidance/case-meetings/${meetingId}/forward`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            reason: reason.trim()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Case forwarded to president successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to forward case', 'danger');
        }
    })
    .catch(error => {
        console.error('Error forwarding case:', error);
        showAlert('Error forwarding case', 'danger');
    });
}

// Edit case meeting
function editCaseMeeting(meetingId) {
    // Redirect to edit page or show edit modal
    window.location.href = `/guidance/case-meetings/${meetingId}/edit`;
}

// Filter case meetings
function filterCaseMeetings() {
    const status = document.getElementById('status-filter')?.value || '';
    const type = document.getElementById('type-filter')?.value || '';
    const date = document.getElementById('date-filter')?.value || '';
    const search = document.getElementById('search-filter')?.value || '';
    
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (type) params.append('type', type);
    if (date) params.append('date', date);
    if (search) params.append('search', search);
    
    const url = `/guidance/case-meetings${params.toString() ? '?' + params.toString() : ''}`;
    window.location.href = url;
}

// Clear filters
function clearFilters() {
    const statusFilter = document.getElementById('status-filter');
    const typeFilter = document.getElementById('type-filter');
    const dateFilter = document.getElementById('date-filter');
    const searchFilter = document.getElementById('search-filter');
    
    if (statusFilter) statusFilter.value = '';
    if (typeFilter) typeFilter.value = '';
    if (dateFilter) dateFilter.value = '';
    if (searchFilter) searchFilter.value = '';
    
    window.location.href = '/guidance/case-meetings';
}

// Refresh case meetings
function refreshCaseMeetings() {
    window.location.reload();
}

// Export case meetings
function exportCaseMeetings() {
    window.open('/guidance/case-meetings/export', '_blank');
}

// Print case meetings
function printCaseMeetings() {
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

function openScheduleMeetingModal(studentId) {
    // Open the schedule case meeting modal programmatically
    const modalElement = document.getElementById('scheduleCaseMeetingModal');
    if (!modalElement) return;

    // Reset the form
    const form = document.getElementById('scheduleCaseMeetingForm');
    if (form) {
        form.reset();
        // Set the student select field to the given studentId
        const studentSelect = form.querySelector('select[name="student_id"]');
        if (studentSelect) {
            studentSelect.value = studentId;
        }
    }

    // Show the modal using Bootstrap's modal API
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

// Open create summary modal
function openCreateSummaryModal(meetingId) {
    const modalElement = document.getElementById('createCaseSummaryModal');
    if (!modalElement) return;

    // Reset the form
    const form = document.getElementById('createCaseSummaryForm');
    if (form) {
        form.reset();
        // Set the meeting ID in a hidden field or data attribute
        form.setAttribute('data-meeting-id', meetingId);
    }

    // Show the modal using Bootstrap's modal API
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

// Submit case summary form
function submitCaseSummary(event) {
    event.preventDefault();

    const form = event.target;
    const meetingId = form.getAttribute('data-meeting-id');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ri-loader-4-line me-2 spinner-border spinner-border-sm"></i>Saving...';
    submitBtn.disabled = true;

    fetch(`/guidance/case-meetings/${meetingId}/summary`, {
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
            showAlert('Case summary created successfully!', 'success');
            closeModal('createCaseSummaryModal');
            form.reset();
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to create case summary', 'danger');
        }
    })
    .catch(error => {
        console.error('Error creating case summary:', error);
        showAlert('An error occurred while creating the summary', 'danger');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Expose functions to global scope for onclick handlers
window.submitCaseMeeting = submitCaseMeeting;
window.viewCaseMeeting = viewCaseMeeting;
window.completeCaseMeeting = completeCaseMeeting;
window.forwardToPresident = forwardToPresident;
window.editCaseMeeting = editCaseMeeting;
window.filterCaseMeetings = filterCaseMeetings;
window.clearFilters = clearFilters;
window.refreshCaseMeetings = refreshCaseMeetings;
window.exportCaseMeetings = exportCaseMeetings;
window.printCaseMeetings = printCaseMeetings;
window.closeModal = closeModal;
window.openScheduleMeetingModal = openScheduleMeetingModal;
window.openCreateSummaryModal = openCreateSummaryModal;
window.submitCaseSummary = submitCaseSummary;
