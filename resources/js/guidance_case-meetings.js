document.addEventListener('DOMContentLoaded', function() {
    // Add CSS styles for disabled intervention checkboxes and enhanced UI
    const style = document.createElement('style');
    style.textContent = `
        .form-check:has(input.form-check-input:disabled) {
            opacity: 0.5;
            pointer-events: none;
        }
        .form-check input.form-check-input:disabled + .form-check-label {
            color: #6c757d !important;
            cursor: not-allowed;
        }
        
        /* Enhanced case meeting styles */
        .meeting-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #198754;
        }
        
        .meeting-summary .row {
            margin-bottom: 0.5rem;
        }
        
        .alert-success.position-fixed {
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .progress-bar-animated {
            animation: progress-bar-stripes 1s linear infinite;
        }
        
        @keyframes progress-bar-stripes {
            0% { background-position: 1rem 0; }
            100% { background-position: 0 0; }
        }
        
        .notification-status .small {
            font-size: 0.825rem;
        }
        
        .modal-content {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .modal-header.bg-success {
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
        }
        
        /* Custom color scheme - black, white, yellow, green */
        .btn-primary {
            background-color: #198754 !important;
            border-color: #198754 !important;
        }
        
        .btn-primary:hover {
            background-color: #146c43 !important;
            border-color: #146c43 !important;
        }
        
        .text-primary {
            color: #198754 !important;
        }
        
        .border-primary {
            border-color: #198754 !important;
        }
    `;
    document.head.appendChild(style);

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

        // Initialize time pickers for case meetings with school hours (7 AM - 4 PM)
        flatpickr("#scheduleCaseMeetingModal input[name='scheduled_time']", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K",
            time_24hr: false,
            minTime: "07:00",
            maxTime: "16:00",
            minuteIncrement: 15,
            defaultHour: 7,
            defaultMinute: 0
        });

        flatpickr("#editCaseMeetingModal input[name='scheduled_time']", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K",
            time_24hr: false,
            minTime: "07:00",
            maxTime: "16:00",
            minuteIncrement: 15,
            defaultHour: 7,
            defaultMinute: 0
        });

        // Initialize flatpickr for intervention date fields in case summary modal
        flatpickr("#createCaseSummaryModal input[name='written_reflection_due']", {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
        });

        flatpickr("#createCaseSummaryModal input[name='parent_teacher_date']", {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
        });

        flatpickr("#createCaseSummaryModal input[name='restorative_justice_date']", {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
        });

        flatpickr("#createCaseSummaryModal input[name='follow_up_meeting_date']", {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
        });

        flatpickr("#createCaseSummaryModal input[name='community_service_date']", {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
        });

        flatpickr("#createCaseSummaryModal input[name='suspension_start']", {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
        });

        flatpickr("#createCaseSummaryModal input[name='suspension_end']", {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
        });

        flatpickr("#createCaseSummaryModal input[name='suspension_return']", {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
        });

        flatpickr("#createCaseSummaryModal input[name='expulsion_date']", {
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
    
    // Initialize intervention checkbox toggles
    initializeInterventionToggles();
}

// Initialize checkbox toggle functionality for intervention fields
function initializeInterventionToggles() {
    // Mapping of checkbox IDs to their data-target values
    const interventionMappings = [
        { checkboxId: 'action_written_reflection', target: 'written_reflection' },
        { checkboxId: 'action_mentorship', target: 'mentorship_counseling' },
        { checkboxId: 'action_parent_teacher', target: 'parent_teacher_communication' },
        { checkboxId: 'action_restorative_justice', target: 'restorative_justice_activity' },
        { checkboxId: 'action_follow_up_meeting', target: 'follow_up_meeting' },
        { checkboxId: 'action_community_service', target: 'community_service' },
        { checkboxId: 'action_suspension', target: 'suspension' },
        { checkboxId: 'action_expulsion', target: 'expulsion' }
    ];
    
    interventionMappings.forEach(mapping => {
        const checkbox = document.getElementById(mapping.checkboxId);
        
        if (!checkbox) {
            console.warn(`Checkbox with ID ${mapping.checkboxId} not found`);
            return;
        }
        
        // Find the corresponding conditional field
        const conditionalField = document.querySelector(`.conditional-field[data-target="${mapping.target}"]`);
        
        if (conditionalField) {
            // Initially hide the conditional field
            conditionalField.style.display = 'none';
            
            // Add event listener
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    // Show the conditional field for this intervention
                    conditionalField.style.display = 'inline';
                    
                    // Disable all other intervention checkboxes
                    interventionMappings.forEach(otherMapping => {
                        if (otherMapping.checkboxId !== mapping.checkboxId) {
                            const otherCheckbox = document.getElementById(otherMapping.checkboxId);
                            if (otherCheckbox) {
                                otherCheckbox.disabled = true;
                                otherCheckbox.checked = false;
                                
                                // Hide other conditional fields and clear their values
                                const otherConditionalField = document.querySelector(`.conditional-field[data-target="${otherMapping.target}"]`);
                                if (otherConditionalField) {
                                    otherConditionalField.style.display = 'none';
                                    const otherInputs = otherConditionalField.querySelectorAll('input');
                                    otherInputs.forEach(input => {
                                        if (input.type === 'checkbox') {
                                            input.checked = false;
                                        } else {
                                            input.value = '';
                                        }
                                    });
                                }
                            }
                        }
                    });
                } else {
                    // Hide the conditional field for this intervention
                    conditionalField.style.display = 'none';
                    
                    // Clear input values within this conditional field
                    const inputs = conditionalField.querySelectorAll('input');
                    inputs.forEach(input => {
                        if (input.type === 'checkbox') {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    });
                    
                    // Re-enable all other intervention checkboxes
                    interventionMappings.forEach(otherMapping => {
                        if (otherMapping.checkboxId !== mapping.checkboxId) {
                            const otherCheckbox = document.getElementById(otherMapping.checkboxId);
                            if (otherCheckbox) {
                                otherCheckbox.disabled = false;
                            }
                        }
                    });
                }
            });
            
            console.log(`Initialized toggle for ${mapping.checkboxId} → ${mapping.target}`);
        } else {
            console.warn(`Conditional field with data-target="${mapping.target}" not found`);
        }
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
            if (
                meeting.id && (
                    (meeting.teacher_statement && meeting.teacher_statement.trim() !== '') ||
                    (meeting.action_plan && meeting.action_plan.trim() !== '')
                )
            ) {
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
                                <div style="margin-bottom: 10px;">
                                    <a href="${teacherObservationReportUrl}" target="_blank" style="display: inline-flex; align-items: center; border: 2px solid #388e3c; color: #388e3c; border-radius: 6px; padding: 8px 18px; font-size: 16px; font-weight: 500; background: #fff; text-decoration: none;">
                                        <span style="margin-right: 8px; font-size: 18px;">&#128196;</span> <!-- Page with curl Unicode (PDF icon alternative) -->
                                        View Teacher Observation Report
                                    </a>
                                </div>
                            ` : ''}
                            ${meeting.summary ? `
                                <div>
                                    <a href="/guidance/case-meetings/${meeting.id}/disciplinary-conference-report/pdf" target="_blank" style="display: inline-flex; align-items: center; border: 2px solid #d32f2f; color: #d32f2f; border-radius: 6px; padding: 8px 18px; font-size: 16px; font-weight: 500; background: #fff; text-decoration: none;">
                                        <span style="margin-right: 8px; font-size: 18px;">&#128221;</span> <!-- Clipboard Unicode -->
                                        Discipline Conference Report
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
    
    // Enhanced pre-submission validation
    const validationResult = validateCaseMeetingForm(form);
    if (!validationResult.isValid) {
        showValidationErrors(validationResult.errors);
        return;
    }
    
    // Show confirmation modal with meeting details
    showCaseMeetingConfirmation(formData, form);
};

// Enhanced validation function
function validateCaseMeetingForm(form) {
    const errors = [];
    const studentId = form.querySelector('[name="student_id"]').value;
    const meetingType = form.querySelector('[name="meeting_type"]').value;
    const scheduledDate = form.querySelector('[name="scheduled_date"]').value;
    const scheduledTime = form.querySelector('[name="scheduled_time"]').value;
    const reason = form.querySelector('[name="reason"]').value.trim();
    
    // Basic field validation
    if (!studentId) errors.push('Please select a student');
    if (!meetingType) errors.push('Please select a meeting type');
    if (!scheduledDate) errors.push('Please select a date');
    if (!scheduledTime) errors.push('Please select a time');
    if (!reason) errors.push('Please provide a reason for the meeting');
    
    // Date validation
    if (scheduledDate) {
        const selectedDate = new Date(scheduledDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate <= today) {
            errors.push('Meeting date must be in the future');
        }
        
        const dayOfWeek = selectedDate.getDay();
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            errors.push('Meetings cannot be scheduled on weekends');
        }
    }
    
    // Time validation (school hours)
    if (scheduledTime) {
        const [hours, minutes] = scheduledTime.split(':').map(Number);
        const timeInMinutes = hours * 60 + minutes;
        const schoolStart = 7 * 60; // 7:00 AM
        const schoolEnd = 16 * 60; // 4:00 PM
        
        if (timeInMinutes < schoolStart || timeInMinutes > schoolEnd) {
            errors.push('Meeting time must be within school hours (7:00 AM - 4:00 PM)');
        }
    }
    
    return {
        isValid: errors.length === 0,
        errors: errors
    };
}

// Show validation errors
function showValidationErrors(errors) {
    const errorHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6><i class="ri-error-warning-line me-2"></i>Please correct the following errors:</h6>
            <ul class="mb-0">
                ${errors.map(error => `<li>${error}</li>`).join('')}
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert error at the top of the modal body
    const modalBody = document.querySelector('#scheduleCaseMeetingModal .modal-body');
    const existingAlert = modalBody.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    modalBody.insertAdjacentHTML('afterbegin', errorHtml);
}

// Confirmation modal for case meeting
function showCaseMeetingConfirmation(formData, form) {
    // Hide the schedule case meeting modal first
    const scheduleModal = document.getElementById('scheduleCaseMeetingModal');
    if (scheduleModal) {
        const bootstrapScheduleModal = bootstrap.Modal.getInstance(scheduleModal);
        if (bootstrapScheduleModal) {
            bootstrapScheduleModal.hide();
        }
    }
    
    const studentSelect = form.querySelector('[name="student_id"]');
    const studentName = studentSelect.options[studentSelect.selectedIndex].text;
    const meetingType = form.querySelector('[name="meeting_type"]').value;
    const scheduledDate = form.querySelector('[name="scheduled_date"]').value;
    const scheduledTime = form.querySelector('[name="scheduled_time"]').value;
    const reason = form.querySelector('[name="reason"]').value;
    
    const confirmationHtml = `
        <div class="modal fade" id="caseMeetingConfirmationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="ri-calendar-check-line me-2"></i>Confirm Case Meeting
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Please review the meeting details below:</strong>
                        </div>
                        
                        <div class="meeting-summary">
                            <div class="row mb-3">
                                <div class="col-4 fw-bold text-muted">Student:</div>
                                <div class="col-8">${studentName}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4 fw-bold text-muted">Type:</div>
                                <div class="col-8">${meetingType.replace('_', ' ').toUpperCase()}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4 fw-bold text-muted">Date:</div>
                                <div class="col-8">${new Date(scheduledDate).toLocaleDateString('en-US', { 
                                    weekday: 'long', 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric' 
                                })}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4 fw-bold text-muted">Time:</div>
                                <div class="col-8">${formatTime12Hour(scheduledTime)}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4 fw-bold text-muted">Reason:</div>
                                <div class="col-8">${reason}</div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="alert alert-warning">
                            <i class="ri-mail-send-line me-2"></i>
                            <strong>Automatic Notifications:</strong>
                            <ul class="mb-0 mt-2">
                                <li>The student's class adviser will be automatically notified</li>
                                <li>A Teacher Observation Report will be forwarded to the adviser</li>
                                <li>Meeting details will be recorded in the system</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-success" onclick="confirmCaseMeetingScheduling()">
                            <i class="ri-check-line me-1"></i>Schedule Meeting
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing confirmation modal if any
    const existingModal = document.getElementById('caseMeetingConfirmationModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add new modal to body
    document.body.insertAdjacentHTML('beforeend', confirmationHtml);
    
    // Store form data for later use
    window.pendingCaseMeetingData = formData;
    window.pendingCaseMeetingForm = form;
    
    // Show confirmation modal
    const confirmModal = new bootstrap.Modal(document.getElementById('caseMeetingConfirmationModal'));
    confirmModal.show();
}

// Confirm and proceed with scheduling
window.confirmCaseMeetingScheduling = function() {
    const confirmModal = bootstrap.Modal.getInstance(document.getElementById('caseMeetingConfirmationModal'));
    confirmModal.hide();
    
    const form = window.pendingCaseMeetingForm;
    const formData = window.pendingCaseMeetingData;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Add CSRF token
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-line me-2 spinner-border spinner-border-sm"></i>Scheduling...';
    
    // Show progress indicator
    showSchedulingProgress();

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
            hideSchedulingProgress();
            
            // Close modals
            const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleCaseMeetingModal'));
            if (modal) modal.hide();
            
            // Show enhanced success message with meeting details
            showEnhancedSuccessMessage(data.meeting || data);

            // Automatically forward Teacher Observation Report to adviser
            if (data.meeting_id) {
                const forwardData = new FormData();
                forwardData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                // Show forwarding progress
                showForwardingProgress();
                
                fetch(`/guidance/case-meetings/${data.meeting_id}/forward-observation-report`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: forwardData
                })
                .then(response => response.json())
                .then(forwardData => {
                    hideForwardingProgress();
                    if (forwardData && forwardData.success) {
                        updateSuccessMessage('Teacher Observation Report successfully forwarded to class adviser.');
                    } else {
                        updateSuccessMessage('Meeting scheduled successfully. Note: ' + (forwardData.message || 'Could not forward report to adviser.'));
                    }
                })
                .catch((error) => {
                    console.log('Forward error:', error);
                    hideForwardingProgress();
                    updateSuccessMessage('Meeting scheduled successfully. Note: Could not forward report to adviser.');
                });
            }

            // Reload page to show new meeting
            setTimeout(() => location.reload(), 3000);
        } else {
            hideSchedulingProgress();
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
                sanctionSelect.innerHTML = '<option value="">Select Intervention</option>' +
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
    // First, fetch meeting details to perform client-side validation
    fetch(`/guidance/case-meetings/${meetingId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {    
            showAlert('danger', 'Failed to load meeting details for validation');
            return;
        }

        const meeting = data.meeting;
        const validationErrors = [];

        // Check if already forwarded to president
        if (meeting.status === 'forwarded_to_president' || meeting.forwarded_to_president) {
            showAlert('warning', 'This case has already been forwarded to the president.');
            return;
        }

        // Check basic requirements
        if (!meeting.summary || meeting.summary.trim() === '') {
            validationErrors.push('Case summary is required');
        }

        if (meeting.status !== 'pre_completed') {
            validationErrors.push('Meeting must be completed before forwarding');
        }

        // Check for required attachments based on violation severity
        if (meeting.violation && meeting.violation.severity === 'major') {
            // For major violations, check if student narrative report has replies
            if (!meeting.student_statement && !meeting.incident_feelings && !meeting.action_plan) {
                validationErrors.push('Student Narrative Report requires student replies');
            }
        }

        // Check if teacher observation report has teacher replies
        if (!meeting.teacher_statement && !meeting.action_plan) {
            validationErrors.push('Teacher Observation Report requires teacher replies');
        }

        // If validation fails, show errors and don't proceed
        if (validationErrors.length > 0) {
            const errorMessage = 'Cannot forward to president:\n\n• ' + validationErrors.join('\n• ') + '\n\nPlease complete all required reports and ensure proper replies are provided.';
            showAlert('warning', errorMessage);
            return;
        }

        // If validation passes, show confirmation and proceed
        if (confirm('Are you sure you want to forward this case to the president?\n\nThis action will submit the case for final review and cannot be undone.')) {
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            // Show loading state
            showAlert('info', 'Forwarding case to president...');

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
    })
    .catch(error => {
        console.error('Error fetching meeting details:', error);
        showAlert('danger', 'Error validating meeting details');
    });
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

            // Check if interventions were selected to show enhanced success message
            const form = document.getElementById('createCaseSummaryForm');
            const formData = new FormData(form);
            const hasInterventions = formData.get('written_reflection') || 
                                   formData.get('mentorship_counseling') || 
                                   formData.get('suspension') || 
                                   formData.get('expulsion');

            // Show success message with sanctions info
            let successMessage = data.message;
            if (hasInterventions) {
                successMessage += ' Automatic sanctions have been created based on selected interventions.';
            }
            showAlert('success', successMessage);

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
        <strong>${type === 'success' ? 'Success!' : ''}</strong> ${message}
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

// Utility Functions for Enhanced Case Meeting Scheduling

// Format time to 12-hour format
function formatTime12Hour(time24) {
    const [hours, minutes] = time24.split(':');
    const hour12 = hours % 12 || 12;
    const ampm = hours >= 12 ? 'PM' : 'AM';
    return `${hour12}:${minutes} ${ampm}`;
}

// Show scheduling progress indicator
function showSchedulingProgress() {
    const progressHtml = `
        <div class="modal fade show" id="schedulingProgressModal" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h6 class="mb-2">Scheduling Case Meeting</h6>
                        <p class="text-muted mb-0 small">Please wait while we process your request...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', progressHtml);
}

// Hide scheduling progress
function hideSchedulingProgress() {
    const progressModal = document.getElementById('schedulingProgressModal');
    if (progressModal) {
        progressModal.remove();
    }
}

// Show forwarding progress
function showForwardingProgress() {
    const progressHtml = `
        <div class="modal fade show" id="forwardingProgressModal" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div class="spinner-border text-success mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h6 class="mb-2">Forwarding to Adviser</h6>
                        <p class="text-muted mb-0 small">Sending observation report to class adviser...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', progressHtml);
}

// Hide forwarding progress
function hideForwardingProgress() {
    const progressModal = document.getElementById('forwardingProgressModal');
    if (progressModal) {
        progressModal.remove();
    }
}

// Show enhanced success message with meeting details
function showEnhancedSuccessMessage(meetingData) {
    const student = meetingData.student || {};
    const scheduledDate = meetingData.scheduled_date;
    const scheduledTime = meetingData.scheduled_time;
    
    const successHtml = `
        <div class="alert alert-success alert-dismissible fade show shadow-sm position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 350px; max-width: 500px;" 
             id="enhancedSuccessAlert" role="alert">
            <div class="d-flex align-items-start">
                <div class="flex-shrink-0">
                    <i class="ri-calendar-check-fill fs-4 text-success"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="alert-heading mb-2">
                        <i class="ri-check-circle-line me-1"></i>Case Meeting Scheduled Successfully!
                    </h6>
                    
                    <div class="meeting-info">
                        <p class="mb-2"><strong>Student:</strong> ${student.first_name || ''} ${student.last_name || ''}</p>
                        ${scheduledDate ? `<p class="mb-2"><strong>Date:</strong> ${new Date(scheduledDate).toLocaleDateString('en-US', { 
                            weekday: 'long', 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        })}</p>` : ''}
                        ${scheduledTime ? `<p class="mb-2"><strong>Time:</strong> ${formatTime12Hour(scheduledTime)}</p>` : ''}
                    </div>
                    
                    <div class="progress mb-2" style="height: 4px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 100%"></div>
                    </div>
                    
                    <div class="notification-status">
                        <p class="mb-1 small text-success">
                            <i class="ri-check-line me-1"></i>Meeting created and saved
                        </p>
                        <p class="mb-0 small text-muted" id="forwardingStatus">
                            <i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i>
                            Forwarding to class adviser...
                        </p>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove any existing success alerts
    const existingAlerts = document.querySelectorAll('#enhancedSuccessAlert');
    existingAlerts.forEach(alert => alert.remove());
    
    document.body.insertAdjacentHTML('beforeend', successHtml);
    
    // Auto-dismiss after 8 seconds
    setTimeout(() => {
        const alert = document.getElementById('enhancedSuccessAlert');
        if (alert) {
            alert.remove();
        }
    }, 8000);
}

// Update the success message with forwarding status
function updateSuccessMessage(message) {
    const forwardingStatus = document.getElementById('forwardingStatus');
    if (forwardingStatus) {
        const isSuccess = message.includes('successfully');
        forwardingStatus.innerHTML = `
            <i class="ri-${isSuccess ? 'check' : 'information'}-line me-1"></i>
            ${message}
        `;
        forwardingStatus.className = `mb-0 small text-${isSuccess ? 'success' : 'warning'}`;
    }
}
