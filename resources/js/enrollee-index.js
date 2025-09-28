/**
 * Enrollee Index/Dashboard JavaScript
 * Handles pre-registration functionality
 */

// Pre-register student function
function preRegisterStudent() {
    const btn = document.getElementById('preRegisterBtn');
    
    // Show confirmation modal
    if (!confirm('Are you ready to complete your pre-registration? This will create your student account and generate your Student ID.')) {
        return;
    }
    
    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line me-2 spinner-border spinner-border-sm"></i>Processing...';
    
    // Make AJAX request to pre-register
    fetch('/enrollee/pre-register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message with credentials
            const message = `
                <div class="text-center">
                    <h5 class="text-success mb-3">Pre-registration Successful!</h5>
                    <div class="alert alert-info">
                        <strong>Your Student Credentials:</strong><br>
                        <strong>Student ID:</strong> ${data.student_id}<br>
                        <strong>Password:</strong> ${data.password}<br>
                        <small class="text-muted">Please save these credentials. You can change your password after logging in.</small>
                    </div>
                </div>
            `;
            
            // Show modal with credentials
            showCredentialsModal(data.student_id, data.password);
            
            // Reload page to show updated timeline after modal is closed
            setTimeout(() => {
                window.location.reload();
            }, 5000);
        } else {
            // Show error message
            showAlert(data.message || 'Pre-registration failed. Please try again.', 'danger');
            
            // Re-enable button
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-user-add-line me-2"></i>Pre-Register Now';
        }
    })
    .catch(error => {
        console.error('Pre-registration error:', error);
        showAlert('An error occurred during pre-registration. Please try again.', 'danger');
        
        // Re-enable button
        btn.disabled = false;
        btn.innerHTML = '<i class="ri-user-add-line me-2"></i>Pre-Register Now';
    });
}

// Show credentials modal
function showCredentialsModal(studentId, password) {
    // Create modal HTML
    const modalHtml = `
        <div class="modal fade" id="credentialsModal" tabindex="-1" aria-labelledby="credentialsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="credentialsModalLabel">
                            <i class="ri-check-circle-line me-2"></i>Pre-registration Successful!
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-4">
                            <i class="ri-user-add-line text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="mb-3">Your Student Account Has Been Created!</h6>
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <strong>Student ID:</strong>
                                    <div class="input-group mt-1">
                                        <input type="text" class="form-control text-center fw-bold" value="${studentId}" readonly id="studentIdField">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('studentIdField')">
                                            <i class="ri-file-copy-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <strong>Password:</strong>
                                    <div class="input-group mt-1">
                                        <input type="text" class="form-control text-center fw-bold" value="${password}" readonly id="passwordField">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('passwordField')">
                                            <i class="ri-file-copy-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-warning">
                            <small><i class="ri-information-line me-1"></i>Please save these credentials safely. You can change your password after logging into the student portal.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                            <i class="ri-check-line me-1"></i>Got It!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('credentialsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('credentialsModal'));
    modal.show();
}

// Copy to clipboard function
function copyToClipboard(fieldId) {
    const field = document.getElementById(fieldId);
    field.select();
    field.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        showAlert('Copied to clipboard!', 'success');
    } catch (err) {
        showAlert('Failed to copy to clipboard', 'warning');
    }
}

// Show alert function
function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Find or create alert container
    let alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alert-container';
        alertContainer.className = 'position-fixed top-0 end-0 p-3';
        alertContainer.style.zIndex = '9999';
        document.body.appendChild(alertContainer);
    }
    
    // Add alert to container
    alertContainer.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Make functions globally available
window.preRegisterStudent = preRegisterStudent;
window.copyToClipboard = copyToClipboard;
