// Registrar Applicant Archives JavaScript

// Global variables
let currentApplicationId = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeArchives();
});

function initializeArchives() {
    console.log('Applicant Archives initialized');
}

function viewArchiveApplication(applicationId) {
    currentApplicationId = applicationId;
    
    // Show loading state
    const modal = document.getElementById('viewArchiveModal');
    const detailsContainer = document.getElementById('archive-details');
    const actionsContainer = document.getElementById('modal-actions');
    
    detailsContainer.innerHTML = '<div class="text-center"><i class="ri-loader-4-line spin fs-2"></i><br>Loading...</div>';
    actionsContainer.innerHTML = '';
    
    // Show modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Fetch application details
    fetch(`/registrar/applications/${applicationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayApplicationDetails(data.application);
                setupModalActions(data.application);
            } else {
                detailsContainer.innerHTML = '<div class="alert alert-danger">Failed to load application details</div>';
            }
        })
        .catch(error => {
            console.error('Error fetching application details:', error);
            detailsContainer.innerHTML = '<div class="alert alert-danger">Error loading application details</div>';
        });
}

function displayApplicationDetails(application) {
    const detailsContainer = document.getElementById('archive-details');
    
    const enrollmentStatus = application.student ? application.student.enrollment_status : 'not_enrolled';
    const statusBadge = getStatusBadge(application.enrollment_status);
    const enrollmentBadge = getEnrollmentBadge(enrollmentStatus);
    
    detailsContainer.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Application Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Application ID:</strong></td><td>${application.application_id}</td></tr>
                    <tr><td><strong>Name:</strong></td><td>${application.first_name} ${application.last_name}</td></tr>
                    <tr><td><strong>Email:</strong></td><td>${application.email}</td></tr>
                    <tr><td><strong>Grade Level:</strong></td><td>${application.grade_level_applied}</td></tr>
                    <tr><td><strong>Status:</strong></td><td>${statusBadge}</td></tr>
                    <tr><td><strong>Enrollment Status:</strong></td><td>${enrollmentBadge}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Dates</h6>
                <table class="table table-sm">
                    <tr><td><strong>Applied:</strong></td><td>${formatDate(application.created_at)}</td></tr>
                    ${application.approved_at ? `<tr><td><strong>Approved:</strong></td><td>${formatDate(application.approved_at)}</td></tr>` : ''}
                    ${application.declined_at ? `<tr><td><strong>Declined:</strong></td><td>${formatDate(application.declined_at)}</td></tr>` : ''}
                    ${application.reconsidered_at ? `<tr><td><strong>Reconsidered:</strong></td><td>${formatDate(application.reconsidered_at)}</td></tr>` : ''}
                </table>
                
                ${application.decline_reason ? `
                    <h6>Decline Reason</h6>
                    <div class="alert alert-warning">${application.decline_reason}</div>
                ` : ''}
                
                ${application.reconsider_reason ? `
                    <h6>Reconsider Reason</h6>
                    <div class="alert alert-info">${application.reconsider_reason}</div>
                ` : ''}
            </div>
        </div>
    `;
}

function setupModalActions(application) {
    const actionsContainer = document.getElementById('modal-actions');
    let actions = '';
    
    if (application.enrollment_status === 'approved' && !application.student) {
        actions += `
            <button type="button" class="btn btn-success" onclick="generateStudentCredentials(${application.id})">
                <i class="ri-key-line me-1"></i>Generate Credentials
            </button>
        `;
    } else if (application.enrollment_status === 'declined') {
        actions += `
            <button type="button" class="btn btn-warning" onclick="reconsiderApplication(${application.id})">
                <i class="ri-refresh-line me-1"></i>Reconsider
            </button>
        `;
    }
    
    actions += `
        <button type="button" class="btn btn-info" onclick="sendNoticeToApplicant('${application.application_id}')">
            <i class="ri-mail-send-line me-1"></i>Send Notice
        </button>
    `;
    
    actionsContainer.innerHTML = actions;
}

function reconsiderApplication(applicationId) {
    currentApplicationId = applicationId;
    
    // Hide the view modal and show reconsider modal
    const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewArchiveModal'));
    viewModal.hide();
    
    const reconsiderModal = new bootstrap.Modal(document.getElementById('reconsiderModal'));
    reconsiderModal.show();
    
    // Clear the reason textarea
    document.getElementById('reconsider-reason').value = '';
}

function confirmReconsider() {
    const reason = document.getElementById('reconsider-reason').value.trim();
    
    if (!reason) {
        alert('Please provide a reason for reconsidering this application.');
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Processing...';
    button.disabled = true;
    
    fetch(`/registrar/applications/${currentApplicationId}/reconsider`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide modal and refresh page
            const modal = bootstrap.Modal.getInstance(document.getElementById('reconsiderModal'));
            modal.hide();
            
            // Show success message and refresh
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Failed to reconsider application');
        }
    })
    .catch(error => {
        console.error('Error reconsidering application:', error);
        alert('Error reconsidering application');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function generateStudentCredentials(applicationId) {
    // Implementation for generating student credentials
    // This would likely be similar to existing functionality
    console.log('Generating credentials for application:', applicationId);
}

function sendNoticeToApplicant(applicationId) {
    // Implementation for sending notice
    // This would likely open a notice modal
    console.log('Sending notice to applicant:', applicationId);
}

// Helper functions
function getStatusBadge(status) {
    const badges = {
        'approved': '<span class="badge bg-success">Approved</span>',
        'declined': '<span class="badge bg-danger">Declined</span>',
        'pending': '<span class="badge bg-warning text-dark">Pending</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

function getEnrollmentBadge(status) {
    const badges = {
        'enrolled': '<span class="badge bg-success">Enrolled</span>',
        'pre_registered': '<span class="badge bg-info">Pre-registered</span>',
        'not_enrolled': '<span class="badge bg-warning text-dark">Not Enrolled</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Expose functions globally
window.viewArchiveApplication = viewArchiveApplication;
window.reconsiderApplication = reconsiderApplication;
window.confirmReconsider = confirmReconsider;
window.generateStudentCredentials = generateStudentCredentials;
window.sendNoticeToApplicant = sendNoticeToApplicant;
