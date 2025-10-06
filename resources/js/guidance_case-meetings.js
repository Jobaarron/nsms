document.addEventListener('DOMContentLoaded', function() {
    // Initialize filters
    initializeFilters();

    // Initialize modals
    initializeModals();
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
    // Redirect to view page or open modal
    window.location.href = `/guidance/case-meetings/${meetingId}`;
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
        document.querySelector('#scheduleCaseMeetingModal input[name="location"]').value = '';
        document.querySelector('#scheduleCaseMeetingModal select[name="urgency_level"]').value = '';
        document.querySelector('#scheduleCaseMeetingModal textarea[name="reason"]').value = 'Scheduled meeting';
        document.querySelector('#scheduleCaseMeetingModal textarea[name="notes"]').value = '';
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
    fetch(`/guidance/case-meetings/${meetingId}/edit`)
        .then(response => response.json())
        .then(data => {
            const meeting = data.meeting;
            const students = data.students;

            // Populate form
            document.getElementById('edit_student_id').value = meeting.student_id;
            document.getElementById('edit_meeting_type').value = meeting.meeting_type;
            document.getElementById('edit_scheduled_date').value = meeting.scheduled_date ? meeting.scheduled_date.split(' ')[0] : '';
            document.getElementById('edit_scheduled_time').value = meeting.scheduled_time ? meeting.scheduled_time.substring(0, 5) : '';
            document.getElementById('edit_location').value = meeting.location || '';
            document.getElementById('edit_urgency_level').value = meeting.urgency_level || '';
            document.getElementById('edit_reason').value = meeting.reason || '';
            document.getElementById('edit_notes').value = meeting.notes || '';

            // Set form action
            document.getElementById('editCaseMeetingForm').action = `/guidance/case-meetings/${meetingId}`;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editCaseMeetingModal'));
            modal.show();
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

window.forwardToPresident = function(meetingId) {
    if (confirm('Are you sure you want to forward this case to the president?')) {
        fetch(`/guidance/case-meetings/${meetingId}/forward`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                '_token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            })
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
