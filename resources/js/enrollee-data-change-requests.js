// Data Change Requests JavaScript for Enrollee Portal
// Handles creating, viewing, editing, and managing data change requests

// Global variables
let currentEnrolleeData = {};
let currentRequestId = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeDataChangeRequests();
});

/**
 * Initialize data change requests functionality
 */
function initializeDataChangeRequests() {
    console.log('Initializing data change requests...');
    
    // Load enrollee data for field population
    loadEnrolleeData();
    
    // Setup event listeners
    setupEventListeners();
    
    // Setup form validation
    setupFormValidation();
}

/**
 * Load enrollee data for populating current values
 */
function loadEnrolleeData() {
    // First try to get data from window.enrolleeData (set by the Blade template)
    if (window.enrolleeData && Object.keys(window.enrolleeData).length > 0) {
        currentEnrolleeData = window.enrolleeData;
        console.log('Available enrollee data:', currentEnrolleeData);
        return;
    }
    
    // Fallback: Get enrollee data from a data element (if exists)
    const enrolleeDataElement = document.getElementById('enrollee-data');
    if (enrolleeDataElement) {
        try {
            currentEnrolleeData = JSON.parse(enrolleeDataElement.textContent);
        } catch (error) {
            console.error('Error parsing enrollee data:', error);
        }
    }
    
    // If no data available, extract from the page
    if (Object.keys(currentEnrolleeData).length === 0) {
        extractEnrolleeDataFromPage();
    }
}

/**
 * Extract enrollee data from the displayed page content
 */
function extractEnrolleeDataFromPage() {
    // This is a fallback method to extract data from the displayed content
    currentEnrolleeData = {
        first_name: getTextContent('First Name'),
        middle_name: getTextContent('Middle Name'),
        last_name: getTextContent('Last Name'),
        suffix: getTextContent('Suffix'),
        date_of_birth: getTextContent('Date of Birth'),
        gender: getTextContent('Gender'),
        nationality: getTextContent('Nationality'),
        religion: getTextContent('Religion'),
        email: getTextContent('Email Address'),
        contact_number: getTextContent('Contact Number'),
        address: getTextContent('Address'),
        city: getTextContent('City'),
        province: getTextContent('Province'),
        zip_code: getTextContent('ZIP Code'),
        grade_level_applied: getTextContent('Grade Level Applied'),
        strand_applied: getTextContent('Strand Applied'),
        track_applied: getTextContent('Track Applied'),
        student_type: getTextContent('Student Type'),
        father_name: getTextContent('Father\'s Name'),
        father_occupation: getTextContent('Father\'s Occupation'),
        father_contact: getTextContent('Father\'s Contact'),
        mother_name: getTextContent('Mother\'s Name'),
        mother_occupation: getTextContent('Mother\'s Occupation'),
        mother_contact: getTextContent('Mother\'s Contact'),
        guardian_name: getTextContent('Guardian Name'),
        guardian_contact: getTextContent('Guardian Contact'),
        last_school_name: getTextContent('Last School Name'),
        last_school_type: getTextContent('Last School Type'),
        medical_history: getTextContent('Medical History')
    };
}

/**
 * Helper function to get text content from page
 */
function getTextContent(label) {
    const elements = document.querySelectorAll('dt');
    for (let element of elements) {
        if (element.textContent.trim() === label) {
            const dd = element.nextElementSibling;
            if (dd && dd.tagName === 'DD') {
                return dd.textContent.trim();
            }
        }
    }
    return '';
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Field selection change
    const fieldSelect = document.getElementById('field_name');
    if (fieldSelect) {
        fieldSelect.addEventListener('change', handleFieldSelection);
    }
    
    // Form submissions
    const newRequestForm = document.getElementById('newChangeRequestForm');
    if (newRequestForm) {
        newRequestForm.addEventListener('submit', handleNewRequestSubmit);
    }
    
    const editRequestForm = document.getElementById('editChangeRequestForm');
    if (editRequestForm) {
        editRequestForm.addEventListener('submit', handleEditRequestSubmit);
    }
}

/**
 * Handle field selection change
 */
function handleFieldSelection(event) {
    const selectedField = event.target.value;
    const currentValueInput = document.getElementById('current_value');
    
    console.log('Selected field:', selectedField);
    console.log('Enrollee data keys:', Object.keys(currentEnrolleeData));
    console.log('Field exists in data:', selectedField in currentEnrolleeData);
    
    if (selectedField && currentValueInput) {
        let currentValue = currentEnrolleeData[selectedField];
        console.log('Raw current value for', selectedField, ':', currentValue);
        
        // Handle different data types and null/empty values
        let displayValue = '';
        if (currentValue === null || currentValue === undefined || currentValue === '') {
            displayValue = 'Not provided';
        } else {
            // Handle special cases for display
            if (selectedField === 'date_of_birth' && currentValue) {
                // Format date for display
                try {
                    const date = new Date(currentValue);
                    displayValue = date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                } catch (e) {
                    displayValue = String(currentValue);
                }
            } else {
                displayValue = String(currentValue);
            }
        }
        
        console.log('Display value:', displayValue);
        currentValueInput.value = displayValue;
        
        // Store the old value for the form
        const form = event.target.closest('form');
        let oldValueInput = form.querySelector('input[name="old_value"]');
        if (!oldValueInput) {
            oldValueInput = document.createElement('input');
            oldValueInput.type = 'hidden';
            oldValueInput.name = 'old_value';
            form.appendChild(oldValueInput);
        }
        oldValueInput.value = String(currentValue || ''); // Store raw value for form submission
    } else {
        console.log('Field not found in enrollee data or no field selected');
        if (currentValueInput) {
            currentValueInput.value = 'Not provided';
        }
    }
}

/**
 * Setup form validation
 */
function setupFormValidation() {
    // Add Bootstrap validation classes
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

/**
 * Handle new request form submission
 */
function handleNewRequestSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Submitting...';
    submitBtn.disabled = true;
    
    // Submit via fetch
    fetch(form.action, {
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
            showAlert('Data change request submitted successfully!', 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('newChangeRequestModal'));
            modal.hide();
            
            // Reset form
            form.reset();
            
            // Reload the page to show the new request
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Error submitting request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error submitting request. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

/**
 * Handle edit request form submission
 */
function handleEditRequestSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Updating...';
    submitBtn.disabled = true;
    
    // Submit via fetch
    fetch(form.action, {
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
            showAlert('Data change request updated successfully!', 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editChangeRequestModal'));
            modal.hide();
            
            // Reload the page to show the updated request
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Error updating request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error updating request. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

/**
 * View change request details
 */
function viewChangeRequest(requestId) {
    fetch(`/enrollee/data-change-requests/${requestId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateViewModal(data.request);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('viewChangeRequestModal'));
            modal.show();
        } else {
            showAlert(data.message || 'Error loading request details', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error loading request details. Please try again.', 'error');
    });
}

/**
 * Edit change request
 */
function editChangeRequest(requestId) {
    fetch(`/enrollee/data-change-requests/${requestId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateEditModal(data.request);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editChangeRequestModal'));
            modal.show();
        } else {
            showAlert(data.message || 'Error loading request details', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error loading request details. Please try again.', 'error');
    });
}

/**
 * Cancel change request
 */
function cancelChangeRequest(requestId) {
    if (!confirm('Are you sure you want to cancel this change request? This action cannot be undone.')) {
        return;
    }
    
    fetch(`/enrollee/data-change-requests/${requestId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Change request cancelled successfully!', 'success');
            
            // Reload the page to remove the cancelled request
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Error cancelling request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error cancelling request. Please try again.', 'error');
    });
}

/**
 * Populate view modal with request data
 */
function populateViewModal(request) {
    document.getElementById('view_field_name').textContent = request.human_field_name || request.field_name;
    document.getElementById('view_status').innerHTML = `<span class="badge bg-${request.status_badge_class}">${request.status}</span>`;
    document.getElementById('view_created_at').textContent = formatDate(request.created_at);
    document.getElementById('view_processed_at').textContent = request.processed_at ? formatDate(request.processed_at) : 'Not processed';
    document.getElementById('view_old_value').textContent = request.old_value || 'Not provided';
    document.getElementById('view_new_value').textContent = request.new_value;
    
    // Show/hide reason section
    const reasonSection = document.getElementById('view_reason_section');
    if (request.reason) {
        document.getElementById('view_reason').textContent = request.reason;
        reasonSection.style.display = 'block';
    } else {
        reasonSection.style.display = 'none';
    }
    
    // Show/hide admin notes section
    const adminNotesSection = document.getElementById('view_admin_notes_section');
    if (request.admin_notes) {
        document.getElementById('view_admin_notes').textContent = request.admin_notes;
        adminNotesSection.style.display = 'block';
    } else {
        adminNotesSection.style.display = 'none';
    }
}

/**
 * Populate edit modal with request data
 */
function populateEditModal(request) {
    document.getElementById('edit_field_name').value = request.human_field_name || request.field_name;
    document.getElementById('edit_current_value').value = request.old_value || 'Not provided';
    document.getElementById('edit_new_value').value = request.new_value;
    document.getElementById('edit_reason').value = request.reason || '';
    
    // Set form action
    const form = document.getElementById('editChangeRequestForm');
    form.action = `/enrollee/data-change-requests/${request.id}`;
    
    currentRequestId = request.id;
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    if (!dateString) return 'Not available';
    
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return dateString;
    }
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    
    alertDiv.innerHTML = `
        <i class="ri-${type === 'success' ? 'check' : type === 'error' ? 'error-warning' : 'information'}-line me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Expose functions to global scope for onclick handlers
window.viewChangeRequest = viewChangeRequest;
window.editChangeRequest = editChangeRequest;
window.cancelChangeRequest = cancelChangeRequest;
