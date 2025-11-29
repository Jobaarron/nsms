// Registrar Approved Applications Management JavaScript

let currentApprovedId = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize filters
    setupApprovedFilters();
    
    // Initialize modals
    initializeApprovedModals();
});

// Setup filter event listeners
function setupApprovedFilters() {
    const gradeFilter = document.getElementById('grade-filter');
    const searchInput = document.getElementById('search-input');
    
    if (gradeFilter) {
        gradeFilter.addEventListener('change', applyApprovedFilters);
    }
    
    if (searchInput) {
        searchInput.addEventListener('keyup', debounce(applyApprovedFilters, 500));
    }
}

// Initialize Bootstrap modals
function initializeApprovedModals() {
    // Initialize view approved modal
    const viewModal = document.getElementById('viewApprovedModal');
    if (viewModal) {
        new bootstrap.Modal(viewModal);
    }
    
    // Initialize credentials modal
    const credentialsModal = document.getElementById('credentialsModal');
    if (credentialsModal) {
        new bootstrap.Modal(credentialsModal);
    }
}

// Apply filters to approved applications table
function applyApprovedFilters() {
    const gradeLevel = document.getElementById('grade-filter')?.value || '';
    const search = document.getElementById('search-input')?.value || '';
    
    const params = new URLSearchParams();
    if (gradeLevel) params.append('grade_level', gradeLevel);
    if (search) params.append('search', search);
    
    // Add loading state
    showLoading();
    
    fetch(`/registrar/approved?${params.toString()}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateApprovedTable(data.applications);
            updateApprovedPagination(data.pagination);
        } else {
            showAlert('Failed to load approved applications', 'danger');
        }
    })
    .catch(error => {
        showAlert('Error loading approved applications', 'danger');
    })
    .finally(() => {
        hideLoading();
    });
}

// Update approved applications table with new data
function updateApprovedTable(applications) {
    const tbody = document.getElementById('approved-tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    applications.forEach(application => {
        const row = createApprovedRow(application);
        tbody.appendChild(row);
    });
}

// Create table row for approved application
function createApprovedRow(application) {
    const row = document.createElement('tr');
    row.setAttribute('data-id', application.id);
    
    const approvedDate = application.approved_at ? formatDate(application.approved_at) : 'N/A';
    
    row.innerHTML = `
        <td>${application.application_id}</td>
        <td>${application.first_name} ${application.last_name}</td>
        <td>${application.grade_level_applied}</td>
        <td>${application.email}</td>
        <td>${approvedDate}</td>
        <td>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary" onclick="viewApprovedApplication(${application.id})" title="View Details">
                    <i class="ri-eye-line"></i>
                </button>
                <button class="btn btn-outline-success" onclick="generateStudentCredentials(${application.id})" title="Generate Student Credentials">
                    <i class="ri-key-line"></i>
                </button>
            </div>
        </td>
    `;
    
    return row;
}

// View approved application details
function viewApprovedApplication(applicationId) {
    currentApprovedId = applicationId;
    showLoading();
    
    fetch(`/registrar/applications/${applicationId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateApprovedModal(data.application);
            const modal = new bootstrap.Modal(document.getElementById('viewApprovedModal'));
            modal.show();
        } else {
            showAlert('Failed to load application details', 'danger');
        }
    })
    .catch(error => {
        showAlert('Error loading application details', 'danger');
    })
    .finally(() => {
        hideLoading();
    });
}

// Populate approved application modal with data
function populateApprovedModal(application) {
    const detailsContainer = document.getElementById('approved-details');
    const generateBtn = document.getElementById('generate-credentials-btn');
    
    if (!detailsContainer) return;
    
    detailsContainer.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Personal Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Name:</strong></td><td>${application.full_name || 'N/A'}</td></tr>
                    <tr><td><strong>Email:</strong></td><td>${application.email || 'N/A'}</td></tr>
                    <tr><td><strong>Contact:</strong></td><td>${application.contact_number || 'N/A'}</td></tr>
                    <tr><td><strong>Date of Birth:</strong></td><td>${application.date_of_birth || 'N/A'}</td></tr>
                    <tr><td><strong>Gender:</strong></td><td>${application.gender || 'N/A'}</td></tr>
                    <tr><td><strong>Nationality:</strong></td><td>${application.nationality || 'N/A'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Academic Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Grade Level:</strong></td><td>${application.grade_level_applied || 'N/A'}</td></tr>
                    <tr><td><strong>Strand:</strong></td><td>${application.strand_applied || 'N/A'}</td></tr>
                    <tr><td><strong>Track:</strong></td><td>${application.track_applied || 'N/A'}</td></tr>
                    <tr><td><strong>Student Type:</strong></td><td>${application.student_type || 'N/A'}</td></tr>
                    <tr><td><strong>LRN:</strong></td><td>${application.lrn || 'N/A'}</td></tr>
                    <tr><td><strong>Last School:</strong></td><td>${application.last_school_name || 'N/A'}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <h6>Guardian Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Father:</strong></td><td>${application.father_name || 'N/A'}</td></tr>
                    <tr><td><strong>Mother:</strong></td><td>${application.mother_name || 'N/A'}</td></tr>
                    <tr><td><strong>Guardian:</strong></td><td>${application.guardian_name || 'N/A'}</td></tr>
                    <tr><td><strong>Guardian Contact:</strong></td><td>${application.guardian_contact || 'N/A'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Approval Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Application ID:</strong></td><td>${application.application_id}</td></tr>
                    <tr><td><strong>Status:</strong></td><td><span class="badge bg-success">Approved</span></td></tr>
                    <tr><td><strong>Applied Date:</strong></td><td>${application.created_at}</td></tr>
                </table>
            </div>
        </div>
    `;
    
    // Setup generate credentials button
    if (generateBtn) {
        generateBtn.onclick = () => generateStudentCredentialsFromModal(application.id);
    }
}

// Generate student credentials
function generateStudentCredentials(applicationId) {
    if (!confirm('Are you sure you want to generate student credentials for this applicant? This will send login details to their email.')) {
        return;
    }
    
    processCredentialsGeneration(applicationId);
}

// Generate student credentials from modal
function generateStudentCredentialsFromModal(applicationId) {
    // Close view modal first
    const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewApprovedModal'));
    if (viewModal) viewModal.hide();
    
    generateStudentCredentials(applicationId);
}

// Process credentials generation
function processCredentialsGeneration(applicationId) {
    showLoading();
    
    fetch(`/registrar/applications/${applicationId}/generate-credentials`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showCredentialsModal(data);
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message || 'Failed to generate credentials', 'danger');
        }
    })
    .catch(error => {
        showAlert('Error generating credentials', 'danger');
    })
    .finally(() => {
        hideLoading();
    });
}

// Show credentials modal
function showCredentialsModal(data) {
    const detailsContainer = document.getElementById('credentials-details');
    
    if (detailsContainer && data.student_id) {
        detailsContainer.innerHTML = `
            <div class="card bg-light">
                <div class="card-body">
                    <h6>Generated Student Credentials:</h6>
                    <table class="table table-sm mb-0">
                        <tr>
                            <td><strong>Student ID:</strong></td>
                            <td><code>${data.student_id}</code></td>
                        </tr>
                        <tr>
                            <td><strong>Email Sent To:</strong></td>
                            <td>Applicant's registered email</td>
                        </tr>
                    </table>
                </div>
            </div>
        `;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('credentialsModal'));
    modal.show();
}

// Refresh approved applications
function refreshApproved() {
    location.reload();
}

// Export approved applications
function exportApproved() {
    const params = new URLSearchParams();
    const gradeLevel = document.getElementById('grade-filter')?.value;
    const search = document.getElementById('search-input')?.value;
    
    if (gradeLevel) params.append('grade_level', gradeLevel);
    if (search) params.append('search', search);
    params.append('export', 'true');
    
    window.open(`/registrar/approved?${params.toString()}`, '_blank');
}

// Utility functions
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
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

function showLoading() {
    // Add loading spinner or disable buttons
    const buttons = document.querySelectorAll('button');
    buttons.forEach(btn => btn.disabled = true);
}

function hideLoading() {
    // Remove loading spinner or enable buttons
    const buttons = document.querySelectorAll('button');
    buttons.forEach(btn => btn.disabled = false);
}

function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
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

function updateApprovedPagination(pagination) {
    // Update pagination if needed
    // This would be implemented based on your pagination structure
}

// Expose functions to global scope for onclick handlers
window.viewApprovedApplication = viewApprovedApplication;
window.generateStudentCredentials = generateStudentCredentials;
window.generateStudentCredentialsFromModal = generateStudentCredentialsFromModal;
window.refreshApproved = refreshApproved;
window.exportApproved = exportApproved;
window.applyApprovedFilters = applyApprovedFilters;
