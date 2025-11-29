// Get CSRF token from meta tag
function getCSRFToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

document.addEventListener('DOMContentLoaded', function () {
    // Initialize search functionality
    initializeSearch();
    
    // Modern Modal Card Styles
    const style = document.createElement('style');
    style.innerHTML = `
        /* Modern Modal Card Styles */
        .modal-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        .modal-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        .modal-card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid rgba(25, 135, 84, 0.1);
            font-weight: 700;
            font-size: 1.1rem;
            padding: 1.25rem 1.5rem;
            color: #2d3748;
        }
        .modal-card-body {
            padding: 1.5rem;
        }
        .modal-attachment-btn {
            border-radius: 12px;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .modal-attachment-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }
        .modal-attachment-btn.btn-outline-primary {
            background: #e8f5e8;
            color: #198754;
            border-color: rgba(25, 135, 84, 0.3);
        }
        .modal-attachment-btn.btn-outline-success {
            background: #e8f5e8;
            color: #2e7d32;
            border-color: rgba(46, 125, 50, 0.3);
        }
        .modal-attachment-btn.btn-outline-info {
            background: #e0f7fa;
            color: #00695c;
            border-color: rgba(0, 105, 92, 0.3);
        }
        .sanction-item {
            background: #f8f9fa;
            border-left: 4px solid #198754;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }
        .sanction-item:hover {
            background: #e8fff0;
            transform: translateX(4px);
        }
        .status-badge-modern {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        
        /* Conditional field styles */
        .conditional-field {
            display: none;
            margin-top: 0.75rem;
            padding: 1rem;
            background: rgba(25, 135, 84, 0.05);
            border-radius: 8px;
            border-left: 3px solid #198754;
            border: 1px solid rgba(25, 135, 84, 0.1);
        }
        
        .conditional-field.show {
            display: block;
        }
        
        .conditional-field .form-control-sm {
            border: 1px solid #ced4da;
            border-radius: 6px;
        }
        
        .conditional-field .form-check {
            margin-bottom: 0;
        }
        
        .conditional-field .form-check-input {
            margin-top: 0.125rem;
        }
        
        .conditional-field .row {
            margin: 0;
        }
        
        .conditional-field .col-md-4 {
            padding: 0 0.5rem;
        }
        
        .conditional-field .col-md-4:first-child {
            padding-left: 0;
        }
        
        .conditional-field .col-md-4:last-child {
            padding-right: 0;
        }
        
        .conditional-field label.form-label {
            margin-bottom: 0.25rem;
            font-weight: 500;
        }
        
        /* Intervention details in summary view */
        .intervention-details {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 6px;
            padding: 0.5rem;
        }
        
        .intervention-details p {
            margin-bottom: 0.25rem !important;
        }
        
        .sanction-item.border-start {
            border-width: 4px !important;
        }
        
        .bg-success-subtle {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }
        
        .bg-warning-subtle {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }
        
        .bg-danger-subtle {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }
        
        .bg-info-subtle {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }
        
        .text-success-emphasis {
            color: #0a3622 !important;
        }
        
        .text-warning-emphasis {
            color: #664d03 !important;
        }
        
        .text-danger-emphasis {
            color: #58151c !important;
        }
        
        .text-info-emphasis {
            color: #055160 !important;
        }
        
        /* Current sanction styling */
        .current-sanction {
            opacity: 0.6;
            position: relative;
        }
        
        .current-sanction .form-check-input {
            cursor: not-allowed !important;
        }
        
        .current-sanction .form-check-label {
            cursor: not-allowed !important;
            color: #6c757d !important;
        }
        
        .current-sanction::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(108, 117, 125, 0.1) 10px,
                rgba(108, 117, 125, 0.1) 20px
            );
            pointer-events: none;
            border-radius: 8px;
        }
        
        .current-badge {
            font-size: 0.7rem !important;
            padding: 0.25rem 0.5rem !important;
        }
    `;
    document.head.appendChild(style);
    // View summary buttons
    const viewSummaryButtons = document.querySelectorAll('.view-summary-btn');
    viewSummaryButtons.forEach(button => {
        button.addEventListener('click', function () {
            const meetingId = this.getAttribute('data-meeting-id');
            loadSummaryReport(meetingId);
        });
    });

    // Approve sanction buttons
    const approveButtons = document.querySelectorAll('.approve-sanction-btn');
    approveButtons.forEach(button => {
        button.addEventListener('click', function () {
            const sanctionId = this.getAttribute('data-sanction-id');
            if (confirm('Are you sure you want to approve this sanction? This will mark the case meeting as completed.')) {
                fetch(`/admin/sanctions/${sanctionId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCSRFToken(),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        // Reload page to update button states
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('An error occurred while approving the sanction.');
                });
            }
        });
    });

    // Reject sanction buttons
    const rejectButtons = document.querySelectorAll('.reject-sanction-btn');
    rejectButtons.forEach(button => {
        button.addEventListener('click', function () {
            const sanctionId = this.getAttribute('data-sanction-id');
            if (confirm('Are you sure you want to reject this sanction? This action cannot be undone.')) {
                fetch(`/admin/sanctions/${sanctionId}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCSRFToken(),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('An error occurred while rejecting the sanction.');
                });
            }
        });
    });

    // Revise sanction buttons
    const reviseButtons = document.querySelectorAll('.revise-sanction-btn');
    reviseButtons.forEach(button => {
        button.addEventListener('click', function () {
            const meetingId = this.getAttribute('data-meeting-id');

            // Reset modal
            resetReviseSanctionModal();
            
            // Load current sanctions
            loadCurrentSanctions(meetingId);

            // Store meeting ID for form submission
            document.getElementById('reviseSanctionForm').setAttribute('data-meeting-id', meetingId);
        });
    });

    // Initialize conditional field toggles for revise sanction modal
    initializeReviseInterventionToggles();

    // Handle revise sanction form submission
    const reviseSanctionForm = document.getElementById('reviseSanctionForm');
    if (reviseSanctionForm) {
        reviseSanctionForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validate that exactly one sanction is selected
            const selectedSanction = document.querySelector('input[name="selected_sanction"]:checked');
            if (!selectedSanction) {
                alert('Please select exactly one sanction before submitting.');
                return;
            }

            const meetingId = this.getAttribute('data-meeting-id');
            const formData = new FormData(this);
            
            // Debug: Log all form data being sent
            
            // Explicitly collect conditional field data to ensure they're included
            const selectedValue = selectedSanction.value;
            
            // Get the visible conditional field and its inputs
            const visibleConditionalField = document.querySelector('#reviseSanctionModal .conditional-field[style*="block"]');
            if (visibleConditionalField) {
                const conditionalInputs = visibleConditionalField.querySelectorAll('input, select, textarea');
                conditionalInputs.forEach(input => {
                    if (input.value) {
                        // Ensure the field is in formData
                        if (!formData.has(input.name)) {
                            formData.append(input.name, input.value);
                        }
                    }
                });
            } else {
            }

            fetch(`/admin/case-meetings/${meetingId}/sanctions`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    // Close modal and reload page
                    const modal = bootstrap.Modal.getInstance(document.getElementById('reviseSanctionModal'));
                    if (modal) modal.hide();
                    location.reload();
                }
            })
            .catch(error => {
                alert('An error occurred while revising the sanction.');
            });
        });
    }

    // Approve case meeting buttons
    const approveCaseButtons = document.querySelectorAll('.approve-case-meeting-btn');
    
    approveCaseButtons.forEach(button => {
        button.addEventListener('click', function () {
            const meetingId = this.getAttribute('data-meeting-id');
            
            if (confirm('Are you sure you want to approve this case? It will be archived after approval.')) {
                
                fetch(`/admin/case-meetings/${meetingId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCSRFToken(),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        // Reload page to update view
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('An error occurred while approving the case.');
                });
            }
        });
    });

    // Close case buttons
    const closeButtons = document.querySelectorAll('.close-case-btn');
    closeButtons.forEach(button => {
        button.addEventListener('click', function () {
            const meetingId = this.getAttribute('data-meeting-id');
            const notes = prompt('Add president notes (optional):');
            
            if (confirm('Are you sure you want to close this case? It will be archived after closure.')) {
                fetch(`/admin/cases/${meetingId}/close`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCSRFToken(),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        president_notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        // Remove the row from the table or reload page
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('An error occurred while closing the case.');
                });
            }
        });
    });
});

// Function to reset the revise sanction modal
function resetReviseSanctionModal() {
    // Clear all radio buttons and reset their states
    const radioButtons = document.querySelectorAll('#reviseSanctionModal input[type="radio"]');
    radioButtons.forEach(radio => {
        radio.checked = false;
        radio.disabled = false;
        radio.parentElement.classList.remove('text-muted', 'current-sanction');
    });
    
    // Remove all "Current" badges
    document.querySelectorAll('#reviseSanctionModal .current-badge').forEach(badge => {
        badge.remove();
    });
    
    // Clear the sanctions display  
    const sanctionsDisplay = document.getElementById('current-sanctions-display');
    if (sanctionsDisplay) {
        sanctionsDisplay.innerHTML = 'Loading...';
    }
    
    // Hide all conditional fields
    document.querySelectorAll('#reviseSanctionModal .conditional-field').forEach(field => {
        field.style.display = 'none';
    });
}

// Initialize conditional field toggles for revise sanction modal
function initializeReviseInterventionToggles() {
    const interventionMappings = [
        { radioId: 'written_reflection', target: 'written_reflection_fields' },
        { radioId: 'mentorship_counseling', target: 'mentorship_fields' },
        { radioId: 'parent_teacher_communication', target: 'parent_teacher_fields' },
        { radioId: 'restorative_justice_activity', target: 'restorative_justice_fields' },
        { radioId: 'follow_up_meeting', target: 'follow_up_fields' },
        { radioId: 'community_service', target: 'community_service_fields' },
        { radioId: 'suspension', target: 'suspension_fields' },
        { radioId: 'expulsion', target: 'expulsion_fields' }
    ];
    
    interventionMappings.forEach(mapping => {
        const radio = document.getElementById(mapping.radioId);
        
        if (radio) {
            radio.addEventListener('change', function() {
                // Hide all conditional fields first
                document.querySelectorAll('#reviseSanctionModal .conditional-field').forEach(field => {
                    field.style.display = 'none';
                });
                
                // Show the conditional field for this intervention if it exists
                const conditionalField = document.querySelector(`#reviseSanctionModal .conditional-field[data-target="${mapping.target}"]`);
                if (conditionalField && this.checked) {
                    conditionalField.style.display = 'block';
                }
            });
        }
    });
}

// Function to load summary report
function loadSummaryReport(meetingId) {
    const modalBody = document.getElementById('summaryModalBody');

    // Show loading spinner
    modalBody.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    fetch(`/admin/case-meetings/${meetingId}/summary`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': getCSRFToken(),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const meeting = data.meeting;
            
            // Debug: Check intervention_fields structure
            
            // Debug: Check specific intervention fields
            
            modalBody.innerHTML = generateSummaryHTML(meeting);
        } else {
            modalBody.innerHTML = '<div class="alert alert-danger">Failed to load summary report.</div>';
        }
    })
    .catch(error => {
        modalBody.innerHTML = '<div class="alert alert-danger">An error occurred while loading the summary report.</div>';
    });
}

// Function to generate HTML for summary report
function generateSummaryHTML(meeting) {
    let html = '';

    // Student Information
    html += `
        <div class="modal-card">
            <div class="modal-card-header d-flex align-items-center gap-2">
                <i class="ri-user-line text-success"></i>
                <span>Student Information</span>
            </div>
            <div class="modal-card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-success-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ri-user-line text-success fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">${meeting.student ? meeting.student.full_name : 'Unknown'}</h6>
                                <small class="text-muted">Full Name</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ri-hashtag text-success fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">${meeting.student ? meeting.student.student_id : 'N/A'}</h6>
                                <small class="text-muted">Student ID</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ri-graduation-cap-line text-warning fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">${meeting.student ? meeting.student.grade_level : 'N/A'}</h6>
                                <small class="text-muted">Grade Level</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Meeting Details
    html += `
        <div class="modal-card">
            <div class="modal-card-header d-flex align-items-center gap-2">
                <i class="ri-calendar-event-line text-success"></i>
                <span>Meeting Details</span>
            </div>
            <div class="modal-card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ri-file-list-line text-success fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">${meeting.meeting_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</h6>
                                <small class="text-muted">Meeting Type</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ri-calendar-line text-success fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">${meeting.scheduled_date ? new Date(meeting.scheduled_date).toLocaleDateString() : 'TBD'}</h6>
                                <small class="text-muted">Scheduled Date</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ri-time-line text-warning fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">${meeting.scheduled_time ? meeting.scheduled_time : 'TBD'}</h6>
                                <small class="text-muted">Scheduled Time</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Reports Card (PDF Attachments)
    let hasNarrative = meeting.student && meeting.violation_id && (meeting.student_statement || meeting.incident_feelings || meeting.action_plan);
    let hasTeacherObservation = meeting.id && (meeting.teacher_statement || meeting.action_plan);
    html += `
        <div class="modal-card">
            <div class="modal-card-header d-flex align-items-center gap-2">
                <i class="ri-file-text-line text-success"></i>
                <span>Case Reports & Documents</span>
            </div>
            <div class="modal-card-body">
                <div class="d-flex flex-column gap-3">
                    <a href="/admin/case-meetings/${meeting.id}/disciplinary-conference-report/pdf?download=1" download="Disciplinary_Conference_Report.pdf" class="modal-attachment-btn btn-outline-success">
                        <i class="ri-download-2-line"></i>
                        <span>Disciplinary Conference Report</span>
                    </a>
                    ${hasNarrative
                        ? `<a href="/narrative-report/view/${meeting.student.id}/${meeting.violation_id}" download="Student_Narrative_Report.pdf" class="modal-attachment-btn btn-outline-success">
                            <i class="ri-download-2-line"></i>
                            <span>Student Narrative Report</span>
                        </a>`
                        : ''}
                    ${hasTeacherObservation
                        ? `<a href="/guidance/observationreport/pdf/${meeting.id}?download=1" download="Teacher_Observation_Report.pdf" class="modal-attachment-btn btn-outline-info">
                            <i class="ri-download-2-line"></i>
                            <span>Teacher Observation Report</span>
                        </a>`
                        : ''}
                    ${meeting.violation && meeting.violation.student_attachment_path
                        ? `<a href="/discipline/violations/${meeting.violation_id}/download-student-attachment" download class="modal-attachment-btn btn-outline-info">
                            <i class="ri-download-2-line"></i>
                            <span>Student Attachment</span>
                        </a>`
                        : ''}
                    ${!hasNarrative && !hasTeacherObservation && (!meeting.violation || !meeting.violation.student_attachment_path)
                        ? '<div class="text-center py-4"><i class="ri-file-line text-muted fs-3 mb-2 d-block"></i><span class="text-muted fst-italic">No documents available</span></div>'
                        : ''}
                </div>
            </div>
        </div>
    `;

    // Case Summary
    if (meeting.summary) {
        html += `
            <div class="modal-card">
                <div class="modal-card-header d-flex align-items-center gap-2">
                    <i class="ri-file-list-3-line text-primary"></i>
                    <span>Case Summary</span>
                </div>
                <div class="modal-card-body">
                    <div class="bg-light rounded-4 p-3">
                        <p class="mb-0 lh-lg">${meeting.summary.replace(/\n/g, '<br>')}</p>
                    </div>
                </div>
            </div>
        `;
    }

    // Recommendations
    if (meeting.recommendations) {
        html += `
            <div class="modal-card">
                <div class="modal-card-header d-flex align-items-center gap-2">
                    <i class="ri-lightbulb-line text-success"></i>
                    <span>Recommendations</span>
                </div>
                <div class="modal-card-body">
                    <div class="bg-success-subtle rounded-4 p-3 border-start border-success border-4">
                        <p class="mb-0 lh-lg">${meeting.recommendations.replace(/\n/g, '<br>')}</p>
                    </div>
                </div>
            </div>
        `;
    }

    // Applied Sanctions and Interventions
    if ((meeting.sanctions && meeting.sanctions.length > 0) || 
        meeting.written_reflection || meeting.mentorship_counseling || meeting.parent_teacher_communication ||
        meeting.restorative_justice_activity || meeting.follow_up_meeting || meeting.community_service ||
        meeting.suspension || meeting.expulsion) {
        
        html += `
            <div class="modal-card">
                <div class="modal-card-header d-flex align-items-center gap-2">
                    <i class="ri-gavel-line text-warning"></i>
                    <span>Applied Sanctions & Interventions</span>
                </div>
                <div class="modal-card-body">
        `;

        // Show agreed interventions first
        const interventions = [];
        
        if (meeting.written_reflection) {
            interventions.push({
                title: 'Written Reflection as Warning',
                icon: 'ri-edit-line',
                color: 'success',
                details: [
                    meeting.written_reflection_due ? `Due Date: ${new Date(meeting.written_reflection_due).toLocaleDateString()}` : null
                ].filter(Boolean)
            });
        }
        
        if (meeting.mentorship_counseling) {
            let mentorDetails = [];
            const mentorName = meeting.intervention_fields?.mentor_name || meeting.mentor_name;
            if (mentorName) {
                mentorDetails.push(`Mentor: ${mentorName}`);
            } else {
                mentorDetails.push('Mentor assignment pending');
            }
            
            interventions.push({
                title: 'Mentorship/Counseling',
                icon: 'ri-user-heart-line',
                color: 'success',
                details: mentorDetails
            });
        }
        
        if (meeting.parent_teacher_communication) {
            let commDetails = ['Parent-teacher conference scheduled'];
            const commMethod = meeting.intervention_fields?.communication_method || meeting.communication_method;
            if (commMethod) {
                commDetails.push(`Method: ${commMethod}`);
            }
            
            interventions.push({
                title: 'Parent-Teacher Communication',
                icon: 'ri-parent-line',
                color: 'warning',
                details: commDetails
            });
        }
        
        if (meeting.restorative_justice_activity) {
            let activityDetails = ['Restorative justice activities assigned'];
            const details = meeting.intervention_fields?.activity_details || meeting.activity_details;
            if (details) {
                activityDetails.push(`Details: ${details}`);
            }
            
            interventions.push({
                title: 'Restorative Justice Activity',
                icon: 'ri-hand-heart-line',
                color: 'success',
                details: activityDetails
            });
        }
        
        if (meeting.follow_up_meeting) {
            let followUpDetails = [];
            const followUpDate = meeting.intervention_fields?.follow_up_date || meeting.follow_up_date || meeting.follow_up_meeting_date;
            if (followUpDate) {
                followUpDetails.push(`Meeting Date: ${new Date(followUpDate).toLocaleDateString()}`);
            } else {
                followUpDetails.push('Meeting date to be scheduled');
            }
            
            interventions.push({
                title: 'Follow-up Meeting',
                icon: 'ri-calendar-event-line',
                color: 'info',
                details: followUpDetails
            });
        }
        
        if (meeting.community_service) {
            let serviceDetails = [];
            const serviceArea = meeting.intervention_fields?.service_area || meeting.service_area || meeting.community_service_area;
            if (serviceArea) {
                serviceDetails.push(`Assigned Area: ${serviceArea}`);
            } else {
                serviceDetails.push('Area assignment pending');
            }
            
            interventions.push({
                title: 'Community/School Service',
                icon: 'ri-community-line',
                color: 'info',
                details: serviceDetails
            });
        }
        
        if (meeting.suspension) {
            let suspensionDetails = ['Student suspended from school'];
            
            const suspensionDays = meeting.intervention_fields?.suspension_days || meeting.suspension_days;
            const startDate = meeting.intervention_fields?.suspension_start_date || meeting.suspension_start_date || meeting.suspension_start;
            const endDate = meeting.intervention_fields?.suspension_end_date || meeting.suspension_end_date || meeting.suspension_end;
            
            if (suspensionDays) {
                suspensionDetails.push(`Duration: ${suspensionDays} days`);
            }
            if (startDate) {
                suspensionDetails.push(`Start Date: ${new Date(startDate).toLocaleDateString()}`);
            }
            if (endDate) {
                suspensionDetails.push(`End Date: ${new Date(endDate).toLocaleDateString()}`);
            }
            
            interventions.push({
                title: 'Suspension',
                icon: 'ri-pause-circle-line',
                color: 'danger',
                details: suspensionDetails,
                note: 'Student must accomplish activity sheets missed during suspension period.'
            });
        }
        
        if (meeting.expulsion) {
            interventions.push({
                title: 'Expulsion',
                icon: 'ri-close-circle-line',
                color: 'danger',
                details: [
                    meeting.expulsion_date ? `Expulsion Date: ${new Date(meeting.expulsion_date).toLocaleDateString()}` : null
                ].filter(Boolean),
                note: 'Certificate of eligibility may be affected per RMPS Sec. 146'
            });
        }

        // Fallback: Check if intervention details are in sanctions data
        if (interventions.length === 0 && meeting.sanctions && meeting.sanctions.length > 0) {
            meeting.sanctions.forEach(sanction => {
                // Look for intervention-related fields in sanction notes or custom fields
                if (sanction.notes || sanction.details) {
                    // Try to parse intervention details from sanction data
                }
            });
        }

        // Display interventions
        if (interventions.length > 0) {
            interventions.forEach((intervention, index) => {
                html += `
                    <div class="sanction-item border-start border-${intervention.color} border-4 bg-${intervention.color}-subtle">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-${intervention.color}-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                <i class="${intervention.icon} text-${intervention.color} fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <h6 class="mb-0 fw-bold text-${intervention.color}-emphasis">${intervention.title}</h6>
                                    <span class="badge bg-primary text-white px-2 py-1" style="font-size: 0.7rem;">Auto-Intervention</span>
                                </div>
                `;
                
                // Add details
                if (intervention.details && intervention.details.length > 0) {
                    html += `<div class="intervention-details mb-2">`;
                    intervention.details.forEach(detail => {
                        html += `<p class="mb-1 small text-${intervention.color}-emphasis"><i class="ri-arrow-right-s-line me-1"></i>${detail}</p>`;
                    });
                    html += `</div>`;
                }
                
                // Add note if present
                if (intervention.note) {
                    html += `<div class="alert alert-light border-0 p-2 mb-0"><small class="text-muted"><i class="ri-information-line me-1"></i>${intervention.note}</small></div>`;
                }
                
                html += `
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        // Show traditional sanctions if they exist
        if (meeting.sanctions && meeting.sanctions.length > 0) {
            html += `<hr class="my-3"><h6 class="fw-bold text-muted mb-3"><i class="ri-scales-line me-2"></i>Additional Sanctions</h6>`;
            
            meeting.sanctions.forEach((sanction, index) => {
                html += `
                    <div class="sanction-item">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <span class="fw-bold text-warning small">${index + 1}</span>
                                    </div>
                                    <h6 class="mb-0 fw-bold">${sanction.sanction}</h6>
                                </div>
                                ${sanction.deportment_grade_action ? `<p class="mb-2"><i class="ri-star-line me-2 text-info"></i><strong>Deportment Action:</strong> ${sanction.deportment_grade_action}</p>` : ''}
                                ${sanction.suspension ? `<p class="mb-2"><i class="ri-pause-circle-line me-2 text-danger"></i><strong>Suspension:</strong> ${sanction.suspension}</p>` : ''}
                                ${sanction.notes ? `<p class="mb-0"><i class="ri-file-text-line me-2 text-success"></i><strong>Notes:</strong> ${sanction.notes.replace(/\n/g, '<br>')}</p>` : ''}
                            </div>
                            <div class="col-md-4 text-end">
                                ${sanction.is_approved 
                                    ? '<span class="status-badge-modern bg-success text-white">Approved</span>' 
                                    : '<span class="status-badge-modern bg-warning text-dark">Pending</span>'}
                                ${sanction.approved_at ? `<p class="small text-muted mt-2 mb-0"><i class="ri-time-line me-1"></i>Approved on ${new Date(sanction.approved_at).toLocaleString()}</p>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        html += `
                </div>
            </div>
        `;
    }

    // Additional Notes
    if (meeting.notes) {
        html += `
            <div class="modal-card">
                <div class="modal-card-header d-flex align-items-center gap-2">
                    <i class="ri-sticky-note-line text-success"></i>
                    <span>Additional Notes</span>
                </div>
                <div class="modal-card-body">
                    <div class="bg-success-subtle rounded-4 p-3 border-start border-success border-4">
                        <p class="mb-0 lh-lg">${meeting.notes.replace(/\n/g, '<br>')}</p>
                    </div>
                </div>
            </div>
        `;
    }

    // President Notes
    if (meeting.president_notes) {
        html += `
            <div class="modal-card">
                <div class="modal-card-header d-flex align-items-center gap-2">
                    <i class="ri-vip-crown-line text-warning"></i>
                    <span>President Notes</span>
                </div>
                <div class="modal-card-body">
                    <div class="bg-warning-subtle rounded-4 p-3 border-start border-warning border-4">
                        <p class="mb-0 lh-lg">${meeting.president_notes.replace(/\n/g, '<br>')}</p>
                    </div>
                </div>
            </div>
        `;
    }

    return html;
}

// Load current sanctions for a case meeting
function loadCurrentSanctions(meetingId) {
    fetch(`/admin/case-meetings/${meetingId}/sanctions`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': getCSRFToken(),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Display current sanctions
            const sanctionsDisplay = document.getElementById('current-sanctions-display');
            let currentSanctions = [];
            if (data.sanctions.written_reflection) currentSanctions.push('Written Reflection');
            if (data.sanctions.mentorship_counseling) currentSanctions.push('Mentorship/Counseling');
            if (data.sanctions.parent_teacher_communication) currentSanctions.push('Parent-Teacher Communication');
            if (data.sanctions.restorative_justice_activity) currentSanctions.push('Restorative Justice Activity');
            if (data.sanctions.follow_up_meeting) currentSanctions.push('Follow-up Meeting');
            if (data.sanctions.community_service) currentSanctions.push('Community Service');
            if (data.sanctions.suspension) currentSanctions.push('Suspension');
            if (data.sanctions.expulsion) currentSanctions.push('Expulsion');
            
            sanctionsDisplay.innerHTML = currentSanctions.length > 0 ? 
                currentSanctions.join(', ') : 'No sanctions currently set';
            
            // Set radio button based on current values - find which sanction is currently true
            const sanctionFields = [
                'written_reflection', 'mentorship_counseling', 'parent_teacher_communication',
                'restorative_justice_activity', 'follow_up_meeting', 'community_service', 
                'suspension', 'expulsion'
            ];
            
            // Clear all radio buttons first and enable them
            document.querySelectorAll('input[name="selected_sanction"]').forEach(radio => {
                radio.checked = false;
                radio.disabled = false;
                radio.parentElement.classList.remove('text-muted', 'current-sanction');
            });
            
            // Track current sanctions and disable their radio buttons
            let currentSanctionField = null;
            for (let field of sanctionFields) {
                const radioElement = document.getElementById(field);
                if (data.sanctions[field] && radioElement) {
                    // This is the current sanction - disable it and mark as current
                    currentSanctionField = field;
                    radioElement.disabled = true;
                    radioElement.checked = false; // Don't pre-select current sanction
                    radioElement.parentElement.classList.add('text-muted', 'current-sanction');
                    
                    // Add visual indicator
                    const label = radioElement.parentElement.querySelector('.form-check-label');
                    if (label && !label.querySelector('.current-badge')) {
                        const currentBadge = document.createElement('span');
                        currentBadge.className = 'badge bg-success ms-2 current-badge';
                        currentBadge.textContent = 'Current';
                        label.appendChild(currentBadge);
                    }
                }
            }
            
        } else {
        }
    })
    .catch(error => {
        document.getElementById('current-sanctions-display').innerHTML = 'Error loading current sanctions';
    });
}

// Function to populate conditional fields with existing data
function populateConditionalFields(sanctionType, sanctionData) {
    
    switch(sanctionType) {
        case 'written_reflection':
            if (sanctionData.written_reflection_due) {
                const dueDateInput = document.querySelector('input[name="written_reflection_due"]');
                if (dueDateInput) dueDateInput.value = sanctionData.written_reflection_due;
            }
            break;
            
        case 'mentorship_counseling':
            if (sanctionData.mentor_name) {
                const mentorInput = document.querySelector('input[name="mentor_name"]');
                if (mentorInput) mentorInput.value = sanctionData.mentor_name;
            }
            break;
            
        case 'parent_teacher_communication':
            if (sanctionData.parent_teacher_date) {
                const dateInput = document.querySelector('input[name="parent_teacher_date"]');
                if (dateInput) dateInput.value = sanctionData.parent_teacher_date;
            }
            break;
            
        case 'restorative_justice_activity':
            if (sanctionData.restorative_justice_date) {
                const dateInput = document.querySelector('input[name="restorative_justice_date"]');
                if (dateInput) dateInput.value = sanctionData.restorative_justice_date;
            }
            break;
            
        case 'follow_up_meeting':
            if (sanctionData.follow_up_meeting_date) {
                const dateInput = document.querySelector('input[name="follow_up_meeting_date"]');
                if (dateInput) dateInput.value = sanctionData.follow_up_meeting_date;
            }
            break;
            
        case 'community_service':
            if (sanctionData.community_service_date) {
                const dateInput = document.querySelector('input[name="community_service_date"]');
                if (dateInput) dateInput.value = sanctionData.community_service_date;
            }
            if (sanctionData.community_service_area) {
                const areaInput = document.querySelector('input[name="community_service_area"]');
                if (areaInput) areaInput.value = sanctionData.community_service_area;
            }
            break;
            
        case 'suspension':
            // Populate suspension checkboxes
            if (sanctionData.suspension_3days) {
                const checkbox = document.querySelector('input[name="suspension_3days"]');
                if (checkbox) checkbox.checked = true;
            }
            if (sanctionData.suspension_5days) {
                const checkbox = document.querySelector('input[name="suspension_5days"]');
                if (checkbox) checkbox.checked = true;
            }
            if (sanctionData.suspension_other_days) {
                const input = document.querySelector('input[name="suspension_other_days"]');
                if (input) input.value = sanctionData.suspension_other_days;
            }
            // Populate dates
            if (sanctionData.suspension_start) {
                const input = document.querySelector('input[name="suspension_start"]');
                if (input) input.value = sanctionData.suspension_start;
            }
            if (sanctionData.suspension_end) {
                const input = document.querySelector('input[name="suspension_end"]');
                if (input) input.value = sanctionData.suspension_end;
            }
            if (sanctionData.suspension_return) {
                const input = document.querySelector('input[name="suspension_return"]');
                if (input) input.value = sanctionData.suspension_return;
            }
            break;
            
        case 'expulsion':
            if (sanctionData.expulsion_date) {
                const dateInput = document.querySelector('input[name="expulsion_date"]');
                if (dateInput) dateInput.value = sanctionData.expulsion_date;
            }
            break;
            
        default:
    }
}

// Search functionality
function initializeSearch() {
    // Active cases search
    const searchActive = document.getElementById('searchActive');
    const clearSearchActive = document.getElementById('clearSearchActive');
    const statusFilter = document.getElementById('statusFilter');
    
    // History search
    const searchHistory = document.getElementById('searchHistory');
    const clearSearchHistory = document.getElementById('clearSearchHistory');
    const archiveReasonFilter = document.getElementById('archiveReasonFilter');
    
    // Active cases search handler
    if (searchActive) {
        searchActive.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            filterTable('forwarded', query, statusFilter?.value || '');
            toggleClearButton(clearSearchActive, query);
        });
        
        if (clearSearchActive) {
            clearSearchActive.addEventListener('click', function() {
                searchActive.value = '';
                filterTable('forwarded', '', statusFilter?.value || '');
                toggleClearButton(clearSearchActive, '');
            });
        }
    }
    
    // Status filter handler
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const query = searchActive?.value.toLowerCase() || '';
            filterTable('forwarded', query, this.value);
        });
    }
    
    // History search handler
    if (searchHistory) {
        searchHistory.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            filterTable('history', query, archiveReasonFilter?.value || '');
            toggleClearButton(clearSearchHistory, query);
        });
        
        if (clearSearchHistory) {
            clearSearchHistory.addEventListener('click', function() {
                searchHistory.value = '';
                filterTable('history', '', archiveReasonFilter?.value || '');
                toggleClearButton(clearSearchHistory, '');
            });
        }
    }
    
    // Archive reason filter handler
    if (archiveReasonFilter) {
        archiveReasonFilter.addEventListener('change', function() {
            const query = searchHistory?.value.toLowerCase() || '';
            filterTable('history', query, this.value);
        });
    }
    
    // Reset filters handlers
    const resetFiltersActive = document.getElementById('resetFiltersActive');
    const resetFiltersHistory = document.getElementById('resetFiltersHistory');
    
    if (resetFiltersActive) {
        resetFiltersActive.addEventListener('click', function() {
            if (searchActive) searchActive.value = '';
            if (statusFilter) statusFilter.value = '';
            if (clearSearchActive) clearSearchActive.classList.add('d-none');
            filterTable('forwarded', '', '');
        });
    }
    
    if (resetFiltersHistory) {
        resetFiltersHistory.addEventListener('click', function() {
            if (searchHistory) searchHistory.value = '';
            if (archiveReasonFilter) archiveReasonFilter.value = '';
            if (clearSearchHistory) clearSearchHistory.classList.add('d-none');
            filterTable('history', '', '');
        });
    }
}

function filterTable(tabType, searchQuery, filterValue) {
    const tabId = tabType === 'forwarded' ? 'forwarded' : 'history';
    const tableSelector = `#${tabId} .modern-table tbody tr`;
    const rows = document.querySelectorAll(tableSelector);
    let visibleCount = 0;
    
    rows.forEach(row => {
        const studentName = row.querySelector('.cell-primary')?.textContent.toLowerCase() || '';
        const studentId = row.querySelector('.cell-secondary')?.textContent.toLowerCase() || '';
        const violationCell = row.children[2];
        const violation = violationCell?.textContent.toLowerCase() || '';
        const statusCell = row.children[4];
        const status = statusCell?.textContent.toLowerCase() || '';
        
        // Check search query match
        const matchesSearch = !searchQuery || 
            studentName.includes(searchQuery) || 
            studentId.includes(searchQuery) || 
            violation.includes(searchQuery);
            
        // Check filter match
        let matchesFilter = true;
        if (filterValue) {
            if (tabType === 'forwarded') {
                matchesFilter = status.includes(filterValue.toLowerCase());
            } else {
                matchesFilter = status.includes(filterValue.toLowerCase());
            }
        }
        
        const shouldShow = matchesSearch && matchesFilter;
        row.style.display = shouldShow ? '' : 'none';
        
        if (shouldShow) {
            visibleCount++;
        }
    });
    
    // Update results count
    updateResultsCount(tabType, visibleCount, rows.length);
    
    // Show/hide no results message
    showNoResultsMessage(tabType, visibleCount);
}

function toggleClearButton(clearButton, query) {
    if (clearButton) {
        if (query) {
            clearButton.classList.remove('d-none');
        } else {
            clearButton.classList.add('d-none');
        }
    }
}

function updateResultsCount(tabType, visibleCount, totalCount) {
    const tabId = tabType === 'forwarded' ? 'forwarded' : 'history';
    const badge = document.querySelector(`#${tabId} .badge`);
    
    if (badge) {
        const originalText = badge.textContent.split(' ')[1]; // Get "Cases" or "Archived"
        badge.textContent = `${visibleCount} ${originalText}`;
        
        if (visibleCount !== totalCount) {
            badge.classList.add('bg-warning', 'text-dark');
            badge.classList.remove('bg-white', 'text-success');
        } else {
            badge.classList.remove('bg-warning', 'text-dark');
            badge.classList.add('bg-white', 'text-success');
        }
    }
}

function showNoResultsMessage(tabType, visibleCount) {
    const tabId = tabType === 'forwarded' ? 'forwarded' : 'history';
    const tableContainer = document.querySelector(`#${tabId} .table-responsive`);
    let noResultsMsg = document.querySelector(`#${tabId} .no-results-message`);
    
    if (visibleCount === 0) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results-message text-center py-5';
            noResultsMsg.innerHTML = `
                <i class="ri-search-line fs-1 text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No results found</h5>
                <p class="text-muted mb-0">Try adjusting your search criteria or filters.</p>
            `;
            if (tableContainer && tableContainer.parentNode) {
                tableContainer.parentNode.insertBefore(noResultsMsg, tableContainer.nextSibling);
            }
        }
        noResultsMsg.style.display = 'block';
        if (tableContainer) tableContainer.style.display = 'none';
    } else {
        if (noResultsMsg) noResultsMsg.style.display = 'none';
        if (tableContainer) tableContainer.style.display = 'block';
    }
}
