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
    const dateFilter = document.getElementById('date-filter');
    const searchFilter = document.getElementById('search-filter');

    if (statusFilter) statusFilter.addEventListener('change', filterCaseMeetings);
    if (dateFilter) dateFilter.addEventListener('change', filterCaseMeetings);
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
    const dateValue = document.getElementById('date-filter').value;
    const searchValue = document.getElementById('search-filter').value.toLowerCase();

    const rows = document.querySelectorAll('#case-meetings-table tbody tr');

    rows.forEach(row => {
        if (row.cells.length < 6) return; // Skip if not enough cells

        const studentName = row.cells[0].textContent.toLowerCase();
        const dateTime = row.cells[1].textContent.toLowerCase();
        const location = row.cells[2].textContent.toLowerCase();
        const status = row.cells[3].textContent.toLowerCase();

        const matchesStatus = !statusValue || status.includes(statusValue.toLowerCase());
        const matchesDate = !dateValue || dateTime.includes(new Date(dateValue).toLocaleDateString().toLowerCase());
        const matchesSearch = !searchValue || studentName.includes(searchValue) || location.includes(searchValue);

        row.style.display = matchesStatus && matchesDate && matchesSearch ? '' : 'none';
    });
};

window.clearFilters = function() {
    document.getElementById('status-filter').value = '';
    document.getElementById('date-filter').value = '';
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

            // Populate modal fields
            document.getElementById('view_student_name').textContent = meeting.student_name || 'N/A';
            document.getElementById('view_student_id').textContent = meeting.student_id || 'N/A';
            document.getElementById('view_counselor_name').textContent = meeting.counselor_name || 'N/A';
            document.getElementById('view_meeting_type').textContent = meeting.meeting_type_display || 'N/A';
            document.getElementById('view_status').textContent = meeting.status_text || 'N/A';
            document.getElementById('view_status').className = `badge ${meeting.status_class || 'bg-secondary'}`;
            document.getElementById('view_scheduled_date').textContent = meeting.scheduled_date || 'TBD';
            document.getElementById('view_scheduled_time').textContent = meeting.scheduled_time || 'TBD';


            document.getElementById('view_reason').textContent = meeting.reason || 'N/A';

            // Handle optional fields
            const locationContainer = document.getElementById('view_location_container');
            const locationSpan = document.getElementById('view_location');
            if (meeting.location) {
                locationSpan.textContent = meeting.location;
                locationContainer.style.display = '';
            } else {
                locationContainer.style.display = 'none';
            }

            const completedAtContainer = document.getElementById('view_completed_at_container');
            const completedAtSpan = document.getElementById('view_completed_at');
            if (meeting.completed_at) {
                completedAtSpan.textContent = new Date(meeting.completed_at).toLocaleString();
                completedAtContainer.style.display = '';
            } else {
                completedAtContainer.style.display = 'none';
            }

            const notesContainer = document.getElementById('view_notes_container');
            const notesDiv = document.getElementById('view_notes');
            if (meeting.notes) {
                notesDiv.textContent = meeting.notes;
                notesContainer.style.display = '';
            } else {
                notesContainer.style.display = 'none';
            }

            const summaryContainer = document.getElementById('view_summary_container');
            const summaryDiv = document.getElementById('view_summary');
            if (meeting.summary) {
                summaryDiv.textContent = meeting.summary;
                summaryContainer.style.display = '';
            } else {
                summaryContainer.style.display = 'none';
            }

            const recommendationsContainer = document.getElementById('view_recommendations_container');
            const recommendationsDiv = document.getElementById('view_recommendations');
            if (meeting.recommendations) {
                recommendationsDiv.textContent = meeting.recommendations;
                recommendationsContainer.style.display = '';
            } else {
                recommendationsContainer.style.display = 'none';
            }

            const followUpContainer = document.getElementById('view_follow_up_container');
            const followUpText = document.getElementById('view_follow_up_text');
            if (meeting.follow_up_required) {
                followUpText.textContent = meeting.follow_up_date ? `Scheduled for ${new Date(meeting.follow_up_date).toLocaleDateString()}` : 'Required';
                followUpContainer.style.display = '';
            } else {
                followUpContainer.style.display = 'none';
            }

            // Handle sanctions
            const sanctionsContainer = document.getElementById('view_sanctions_container');
            const sanctionsList = document.getElementById('view_sanctions_list');
            sanctionsList.innerHTML = '';
            if (meeting.sanctions && meeting.sanctions.length > 0) {
                meeting.sanctions.forEach(sanction => {
                    const sanctionItem = document.createElement('div');
                    sanctionItem.className = 'list-group-item px-0';
                    sanctionItem.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">${sanction.type}</div>
                                ${sanction.description ? `<small class="text-muted">${sanction.description}</small>` : ''}
                                <div class="small text-muted mt-1">
                                    <i class="ri-calendar-line me-1"></i>${new Date(sanction.created_at).toLocaleDateString()}
                                </div>
                            </div>
                            <span class="badge bg-${getSanctionStatusColor(sanction.status || 'pending')}">${ucfirst(sanction.status || 'pending')}</span>
                        </div>
                    `;
                    sanctionsList.appendChild(sanctionItem);
                });
                sanctionsContainer.style.display = '';
            } else {
                sanctionsContainer.style.display = 'none';
            }

            // Handle actions
            const actionsContainer = document.getElementById('view_actions_container');
            actionsContainer.innerHTML = '';

            // Complete button
            if (meeting.status === 'scheduled' || meeting.status === 'in_progress') {
                const completeBtn = document.createElement('button');
                completeBtn.className = 'btn btn-success';
                completeBtn.onclick = () => completeCaseMeeting(meeting.id);
                completeBtn.innerHTML = '<i class="ri-checkbox-circle-line me-2"></i>Mark as Completed';
                actionsContainer.appendChild(completeBtn);
            }

            // Create summary button
            if (!meeting.summary) {
                const summaryBtn = document.createElement('button');
                summaryBtn.className = 'btn btn-info';
                summaryBtn.onclick = () => openCreateSummaryModal(meeting.id);
                summaryBtn.innerHTML = '<i class="ri-file-text-line me-2"></i>Create Summary';
                actionsContainer.appendChild(summaryBtn);
            }

            // Forward button
            if (meeting.summary && meeting.sanctions && meeting.sanctions.length > 0 && !meeting.forwarded_to_president) {
                const forwardBtn = document.createElement('button');
                forwardBtn.className = 'btn btn-warning';
                forwardBtn.onclick = () => forwardToPresident(meeting.id);
                forwardBtn.innerHTML = '<i class="ri-send-plane-line me-2"></i>Forward to President';
                actionsContainer.appendChild(forwardBtn);
            }

            // // Edit button
            // const editBtn = document.createElement('button');
            // editBtn.className = 'btn btn-outline-primary';
            // editBtn.onclick = () => editCaseMeeting(meeting.id);
            // editBtn.innerHTML = '<i class="ri-edit-line me-2"></i>Edit Meeting';
            // actionsContainer.appendChild(editBtn);

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
    // Fetch meeting data and populate edit modal
    fetch(`/guidance/case-meetings/${meetingId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const meeting = data.meeting;

            // Populate form
            document.getElementById('edit_student_id').value = meeting.student_id;
            document.getElementById('edit_meeting_type').value = meeting.meeting_type;
            document.getElementById('edit_scheduled_date').value = meeting.scheduled_date || '';
            document.getElementById('edit_scheduled_time').value = meeting.scheduled_time || '';

            document.getElementById('edit_reason').value = meeting.reason || '';
            document.getElementById('edit_notes').value = meeting.notes || '';

            // Set form action
            document.getElementById('editCaseMeetingForm').action = `/guidance/case-meetings/${meetingId}`;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editCaseMeetingModal'));
            modal.show();
        } else {
            showAlert('danger', 'Failed to load meeting details');
        }
    })
    .catch(error => {
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
