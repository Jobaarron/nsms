document.addEventListener('DOMContentLoaded', function() {
    // Initialize filters
    initializeFilters();

    // Initialize modals
    initializeModals();

    // Initialize flatpickr for date inputs in schedule meeting modal
    if (typeof flatpickr !== 'undefined') {
        flatpickr("#scheduleCaseMeetingModal input[name='scheduled_date']", {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
        });

        flatpickr("#editCaseMeetingModal input[name='scheduled_date']", {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
        });
    }
});

function initializeFilters() {
    // Filter functionality
    const statusFilter = document.getElementById('status-filter');
    const dateFilterStart = document.getElementById('date-filter-start');
    const dateFilterEnd = document.getElementById('date-filter-end');
    const searchFilter = document.getElementById('search-filter');

    if (statusFilter) statusFilter.addEventListener('change', filterCaseMeetings);
    if (dateFilterStart) dateFilterStart.addEventListener('change', filterCaseMeetings);
    if (dateFilterEnd) dateFilterEnd.addEventListener('change', filterCaseMeetings);
    if (searchFilter) searchFilter.addEventListener('input', filterCaseMeetings);
}

function initializeModals() {
    // Reset forms when modals are hidden
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
                // Clear any validation states
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
            }
            // Show all schedule fields
            document.querySelectorAll('#scheduleCaseMeetingModal .schedule-field').forEach(el => el.style.display = '');
        });
    });
}

// Global functions for case meetings
window.refreshCaseMeetings = function() {
    location.reload();
};

window.filterCaseMeetings = function() {
    const statusValue = document.getElementById('status-filter').value;
    const dateStartValue = document.getElementById('date-filter-start').value;
    const dateEndValue = document.getElementById('date-filter-end').value;
    const searchValue = document.getElementById('search-filter').value.toLowerCase();

    const rows = document.querySelectorAll('#case-meetings-table tbody tr');

    rows.forEach(row => {
        if (row.cells.length < 4) return; // Adjusted for your table structure

        const studentName = row.cells[0].textContent.toLowerCase();
        const dateText = row.cells[1].textContent.trim();
        const status = row.cells[2].textContent.toLowerCase();

        // Parse date from cell (format: 'M d, Y')
        let rowDate = null;
        if (dateText) {
            // Only take the first line (date)
            const dateLine = dateText.split('\n')[0].trim();
            rowDate = new Date(dateLine);
        }

        let matchesDate = true;
        if (dateStartValue) {
            const startDate = new Date(dateStartValue);
            if (!rowDate || rowDate < startDate) matchesDate = false;
        }
        if (dateEndValue) {
            const endDate = new Date(dateEndValue);
            if (!rowDate || rowDate > endDate) matchesDate = false;
        }

        const matchesStatus = !statusValue || status.includes(statusValue.toLowerCase());
        const matchesSearch = !searchValue || studentName.includes(searchValue);

        row.style.display = matchesStatus && matchesDate && matchesSearch ? '' : 'none';
    });
};

window.clearFilters = function() {
    document.getElementById('status-filter').value = '';
    document.getElementById('date-filter-start').value = '';
    document.getElementById('date-filter-end').value = '';
    document.getElementById('search-filter').value = '';
    filterCaseMeetings();
};

window.viewCaseMeeting = function(meetingId) {
    // Fetch meeting data and populate view modal
    fetch(`/guidance/case-meetings/${meetingId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const meeting = data.meeting;
            // Debug: log meeting object to check for student_id, violation_id, and possible narrative_report_url
            console.log('Meeting data:', meeting);

            // Build the PDF URL for the narrative report - only show if student has replied
            let narrativePdfUrl = '';
            if (meeting.student_id && meeting.violation_id && 
                (meeting.student_statement || meeting.incident_feelings || meeting.action_plan)) {
                narrativePdfUrl = `/narrative-report/view/${meeting.student_id}/${meeting.violation_id}`;
            } else if (meeting.narrative_report_url) {
                narrativePdfUrl = meeting.narrative_report_url;
            }

            // Build the PDF URL for the case meeting attachment - only show if student has replied
            let caseMeetingAttachmentUrl = '';
            if (meeting.id && (meeting.student_statement || meeting.incident_feelings || meeting.action_plan)) {
                caseMeetingAttachmentUrl = `/guidance/pdf/case-meeting/${meeting.id}`;
            }

            // Build the PDF URL for the teacher observation report (guidance route)
            let teacherObservationReportUrl = '';
            if (meeting.id && (meeting.teacher_statement || meeting.action_plan)) {
                teacherObservationReportUrl = `/guidance/observationreport/pdf/${meeting.id}`;
            }

            // Compose modal HTML (two-column, similar to violation modal)
            document.getElementById('viewCaseMeetingModalBody').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Student Information</h6>
                        <table class="table table-sm">
                            <tbody>
                                <tr><td><strong>Name:</strong></td><td>${meeting.student_name || 'N/A'}</td></tr>
                                <tr><td><strong>Student ID:</strong></td><td>${meeting.student_id || 'N/A'}</td></tr>
                                <tr><td><strong>Status:</strong></td><td>
                                    <span class="badge bg-${meeting.status_class ? meeting.status_class.replace('bg-', '') : 'secondary'}">
                                        ${meeting.status_text || 'N/A'}
                                    </span>
                                </td></tr>
                                <tr><td><strong>Schedule Date:</strong></td><td>${meeting.scheduled_date ? new Date(meeting.scheduled_date).toLocaleDateString() : 'N/A'}</td></tr>
                                <tr><td><strong>Schedule Time:</strong></td><td>${meeting.scheduled_time ? meeting.scheduled_time.substring(0,5) : 'N/A'}</td></tr>
                            </tbody>
                        </table>
                        <!-- Attachment Report Section -->
                        <div class="mt-4">
                            <div style="font-weight: bold; font-size: 16px; margin-bottom: 12px;">Attachment Report</div>
                            ${caseMeetingAttachmentUrl ? `
                                <div style="margin-bottom: 10px;">
                                    <a href="${caseMeetingAttachmentUrl}" target="_blank" style="display: inline-flex; align-items: center; border: 2px solid #388e3c; color: #388e3c; border-radius: 6px; padding: 8px 18px; font-size: 16px; font-weight: 500; background: #fff; text-decoration: none; margin-bottom: 8px;">
                                        <span style="margin-right: 8px; font-size: 18px;">&#128206;</span> <!-- Paperclip Unicode -->
                                        Student Narrative Report
                                    </a>
                                </div>
                            ` : ''}
                            ${teacherObservationReportUrl ? `
                                <div>
                                    <a href="${teacherObservationReportUrl}" target="_blank" style="display: inline-flex; align-items: center; border: 2px solid #388e3c; color: #388e3c; border-radius: 6px; padding: 8px 18px; font-size: 16px; font-weight: 500; background: #fff; text-decoration: none;">
                                        <span style="margin-right: 8px; font-size: 18px;">&#128196;</span> <!-- Page with curl Unicode (PDF icon alternative) -->
                                        View Teacher Observation Report
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                <div class="col-md-6">
                    ${meeting.violation ? `
                        <div class="mb-3">
                            <label class="form-label fw-bold">Violation Details:</label>
                            <table class="table table-sm">
                                <tbody>
                                    <tr><td><strong>Violation Title:</strong></td><td>${meeting.violation.title || 'N/A'}</td></tr>
                                    <tr><td><strong>Description:</strong></td><td>${meeting.violation.description || 'N/A'}</td></tr>
                                    <tr><td><strong>Incident Date:</strong></td><td>${meeting.violation.violation_date ? new Date(meeting.violation.violation_date).toLocaleDateString() : 'N/A'}</td></tr>
                                    <tr><td><strong>IncidentTime:</strong></td><td>${meeting.violation.violation_time ? (function() { const d = new Date('1970-01-01T' + meeting.violation.violation_time); return isNaN(d) ? meeting.violation.violation_time : d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true }); })() : 'N/A'}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    ` : ''}
                    ${meeting.summary ? `
                        <div class="mb-3">
                            <label class="form-label fw-bold">Summary:</label>
                            <p>${meeting.summary}</p>
                        </div>
                    ` : ''}
                    ${meeting.recommendations ? `
                        <div class="mb-3">
                            <tr><td><strong>Date:</strong></td><td>${meeting.scheduled_date ? meeting.scheduled_date : 'N/A'}</td></tr>
                            <tr><td><strong>Time:</strong></td><td>${meeting.scheduled_time ? meeting.scheduled_time : 'N/A'}</td></tr>
                        </div>
                    ` : ''}
                    
                    ${meeting.completed_at ? `
                        <div class="mb-3">
                            <label class="form-label fw-bold">Completed On:</label>
                            <p>${new Date(meeting.completed_at).toLocaleDateString()} at ${new Date(meeting.completed_at).toLocaleTimeString()}</p>
                        </div>
                    ` : ''}
                    ${meeting.follow_up_required ? `
                        <div class="mb-3">
                            <label class="form-label fw-bold">Follow Up:</label>
                            <p>${meeting.follow_up_date ? 'Scheduled for ' + new Date(meeting.follow_up_date).toLocaleDateString() : 'Required'}</p>
                        </div>
                    ` : ''}
                    <!-- Sanctions (if any) -->
                    ${(meeting.sanctions && meeting.sanctions.length > 0) ? `
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sanctions:</label>
                            <ul class="list-group">
                                ${meeting.sanctions.map(sanction => `
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <span class="fw-semibold">${sanction.type}</span>
                                            ${sanction.description ? `<small class='text-muted ms-2'>${sanction.description}</small>` : ''}
                                            <div class="small text-muted mt-1"><i class="ri-calendar-line me-1"></i>${new Date(sanction.created_at).toLocaleDateString()}</div>
                                        </span>
                                        <span class="badge bg-${getSanctionStatusColor(sanction.status || 'pending')}">${ucfirst(sanction.status || 'pending')}</span>
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('viewCaseMeetingModal'));
                        modal.show();
                } else {
                        showAlert('danger', 'Failed to load meeting details');
                }
        })
        .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Error loading meeting details');
        });
};

window.openScheduleMeetingModal = function(studentId = 0) {
    const modal = new bootstrap.Modal(document.getElementById('scheduleCaseMeetingModal'));
    const studentSelect = document.querySelector('#scheduleCaseMeetingModal select[name="student_id"]');

    if (studentId > 0 && studentSelect) {
        studentSelect.value = studentId;
        // Hide other fields for simplified view
        document.querySelectorAll('#scheduleCaseMeetingModal .schedule-field').forEach(el => el.style.display = 'none');
        // Set default values for hidden fields
        document.querySelector('#scheduleCaseMeetingModal select[name="meeting_type"]').value = 'case_meeting';

        document.querySelector('#scheduleCaseMeetingModal textarea[name="reason"]').value = 'Scheduled meeting';
        document.querySelector('#scheduleCaseMeetingModal textarea[name="notes"]').value = '';
    } else {
        // Show all fields when no studentId is provided (normal schedule meeting)
        document.querySelectorAll('#scheduleCaseMeetingModal .schedule-field').forEach(el => el.style.display = '');
        // Reset form fields
        const form = document.getElementById('scheduleCaseMeetingForm');
        if (form) form.reset();
    }

    modal.show();
};

window.submitCaseMeeting = function(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Add CSRF token
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-line me-2 spinner-border spinner-border-sm"></i>Scheduling...';

    fetch('/guidance/case-meetings', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        } else {
            return response.json().then(errorData => {
                let errorMsg = 'Failed to schedule meeting';
                if (errorData.errors) {
                    errorMsg += '\n\nValidation errors:';
                    Object.keys(errorData.errors).forEach(field => {
                        errorMsg += '\n- ' + field + ': ' + errorData.errors[field].join(', ');
                    });
                }
                throw new Error(errorMsg);
            });
        }
    })
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleCaseMeetingModal'));
            modal.hide();

            // Show success message
            showAlert('success', data.message);

            // Reload page to show new meeting
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Failed to schedule meeting');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', error.message);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
};

window.editCaseMeeting = function(meetingId) {
    // Fetch meeting data and sanction options, then populate edit modal
    Promise.all([
        fetch(`/guidance/case-meetings/${meetingId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).then(response => response.json()),
        fetch('/guidance/api/sanctions/list', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).then(response => response.json())
    ]).then(([data, sanctionsData]) => {
        if (data.success && sanctionsData.success) {
            const meeting = data.meeting;
            const sanctions = sanctionsData.sanctions || [];

            // Populate form
            var studentIdEl = document.getElementById('edit_student_id');
            if (studentIdEl) studentIdEl.value = meeting.student_id;
            var meetingTypeEl = document.getElementById('edit_meeting_type');
            if (meetingTypeEl) meetingTypeEl.value = meeting.meeting_type;
            var scheduledDateEl = document.getElementById('edit_scheduled_date');
            if (scheduledDateEl) scheduledDateEl.value = meeting.scheduled_date || '';
            var scheduledTimeEl = document.getElementById('edit_scheduled_time');
            if (scheduledTimeEl) scheduledTimeEl.value = meeting.scheduled_time || '';

            var reasonEl = document.getElementById('edit_reason');
            if (reasonEl) reasonEl.value = meeting.reason || '';
            var notesEl = document.getElementById('edit_notes');
            if (notesEl) notesEl.value = meeting.notes || '';

            // Populate sanction dropdown
            var sanctionSelect = document.getElementById('edit_sanction');
            if (sanctionSelect) {
                sanctionSelect.innerHTML = '<option value="">Select Sanction</option>' +
                    sanctions.map(s => `<option value="${s}">${s}</option>`).join('');
                if (meeting.sanction) sanctionSelect.value = meeting.sanction;
            }

            // Set form action
            document.getElementById('editCaseMeetingForm').action = `/guidance/case-meetings/${meetingId}`;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editCaseMeetingModal'));
            modal.show();
        } else {
            showAlert('danger', 'Failed to load meeting details or sanctions');
        }
    }).catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Error loading meeting for editing');
    });
};

window.submitEditCaseMeeting = function(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Add CSRF token and method
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('_method', 'PUT');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-line me-2 spinner-border spinner-border-sm"></i>Updating...';

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        } else {
            return response.json().then(errorData => {
                let errorMsg = 'Failed to update meeting';
                if (errorData.errors) {
                    errorMsg += '\n\nValidation errors:';
                    Object.keys(errorData.errors).forEach(field => {
                        errorMsg += '\n- ' + field + ': ' + errorData.errors[field].join(', ');
                    });
                }
                throw new Error(errorMsg);
            });
        }
    })
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editCaseMeetingModal'));
            modal.hide();

            // Show success message
            showAlert('success', data.message);

            // Reload page to show updated meeting
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Failed to update meeting');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', error.message);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
};

window.completeCaseMeeting = function(meetingId) {
    if (confirm('Are you sure you want to mark this case meeting as completed?')) {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('_method', 'PATCH');

        fetch(`/guidance/case-meetings/${meetingId}/complete`, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', data.message || 'Failed to complete case meeting');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Error completing case meeting');
        });
    }
};

window.forwardToPresident = function(meetingId) {
    if (confirm('Are you sure you want to forward this case to the president?')) {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch(`/guidance/case-meetings/${meetingId}/forward`, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', data.message || 'Failed to forward case');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Error forwarding case to president');
        });
    }
};

window.openCreateSummaryModal = function(meetingId) {
    // Set form action
    document.getElementById('createCaseSummaryForm').action = `/guidance/case-meetings/${meetingId}/summary`;

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('createCaseSummaryModal'));
    modal.show();
};

window.submitCaseSummary = function(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Add CSRF token
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-line me-2 spinner-border spinner-border-sm"></i>Saving...';

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        } else {
            return response.json().then(errorData => {
                let errorMsg = 'Failed to save summary';
                if (errorData.errors) {
                    errorMsg += '\n\nValidation errors:';
                    Object.keys(errorData.errors).forEach(field => {
                        errorMsg += '\n- ' + field + ': ' + errorData.errors[field].join(', ');
                    });
                }
                throw new Error(errorMsg);
            });
        }
    })
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('createCaseSummaryModal'));
            modal.hide();

            // Show success message
            showAlert('success', data.message);

            // Reload page to show updated meeting
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Failed to save summary');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', error.message);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
};

window.exportCaseMeetings = function() {
    window.location.href = '/guidance/case-meetings/export';
};

window.printCaseMeetings = function() {
    window.print();
};

// Helper functions
function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function getSanctionStatusColor(status) {
    switch (status.toLowerCase()) {
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        case 'pending': return 'warning';
        default: return 'secondary';
    }
}

// Helper function to show alerts
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    const mainContent = document.querySelector('main') || document.body;
    mainContent.insertBefore(alertDiv, mainContent.firstChild);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
