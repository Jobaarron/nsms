// Appeal Management JavaScript

let currentAppealId = null;
let appealsData = [];

// Load appeals data when the tab is activated
document.addEventListener('DOMContentLoaded', function() {
    // Load appeals count on page load
    loadAppealsCount();
    
    // Load appeals when appeals tab is clicked
    const appealsTab = document.getElementById('appeals-tab');
    if (appealsTab) {
        appealsTab.addEventListener('shown.bs.tab', function() {
            loadAppealsData();
        });
    }

    // Appeal status filter event listeners
    const appealStatusRadios = document.querySelectorAll('input[name="appealStatus"]');
    appealStatusRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            loadAppealsData();
        });
    });
});

// Load appeals data
function loadAppealsData() {
    const status = document.querySelector('input[name="appealStatus"]:checked')?.value || '';
    const search = document.getElementById('appealSearch')?.value || '';
    
    fetch('/registrar/appeals?' + new URLSearchParams({ status, search }), {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            appealsData = data.appeals;
            displayAppeals(data.appeals);
            updateAppealsCount(data.total);
        } else {
            showAlert('Error loading appeals data', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error loading appeals data', 'danger');
    });
}

// Display appeals in the table
window.displayAppeals = function displayAppeals(appeals) {
    const tbody = document.getElementById('appealsTableBody');
    const emptyState = document.getElementById('appealsEmptyState');
    
    if (!appeals || appeals.length === 0) {
        tbody.innerHTML = '';
        emptyState.style.display = 'block';
        return;
    }
    
    emptyState.style.display = 'none';
    
    tbody.innerHTML = appeals.map((appeal, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>
                <div>
                    <h6 class="mb-1">${appeal.enrollee.name}</h6>
                    <small class="text-muted">${appeal.enrollee.grade_level_applied}</small>
                </div>
            </td>
            <td>
                <span class="fw-medium">${appeal.enrollee.application_id}</span>
            </td>
            <td>
                <p class="mb-1">${appeal.reason_preview}</p>
                ${appeal.documents_count > 0 ? `<small class="text-info"><i class="ri-file-line me-1"></i>${appeal.documents_count} document(s)</small>` : ''}
            </td>
            <td>
                <span class="badge ${appeal.status_badge_class}">${appeal.status === 'pending' ? 'Rejected/Appeal' : appeal.status.charAt(0).toUpperCase() + appeal.status.slice(1).replace('_', ' ')}</span>
            </td>
            <td>
                <small>${appeal.submitted_at}</small><br>
                <small class="text-muted">${appeal.time_ago}</small>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewAppeal(${appeal.id})" title="View Appeal">
                        <i class="ri-eye-line"></i>
                    </button>
                    ${appeal.status === 'pending' ? `
                        <button class="btn btn-outline-success" onclick="approveAppealQuick(${appeal.id})" title="Approve Appeal">
                            <i class="ri-check-line"></i> Approve
                        </button>
                    ` : ''}
                    ${appeal.status === 'under_review' ? `
                        <button class="btn btn-outline-success" onclick="approveAppealQuick(${appeal.id})" title="Approve Appeal">
                            <i class="ri-check-line"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="rejectAppealQuick(${appeal.id})" title="Reject Appeal">
                            <i class="ri-close-line"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

// Update appeals count badge
function updateAppealsCount(total) {
    const countBadge = document.getElementById('appeals-count');
    const pendingCount = appealsData.filter(appeal => appeal.status === 'pending').length;
    
    if (pendingCount > 0) {
        countBadge.textContent = pendingCount;
        countBadge.style.display = 'inline';
    } else {
        countBadge.style.display = 'none';
    }
}

// Load appeals count only (for badge notification)
function loadAppealsCount() {
    fetch('/registrar/appeals?status=pending&count_only=1', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const countBadge = document.getElementById('appeals-count');
            if (countBadge && data.count > 0) {
                countBadge.textContent = data.count;
                countBadge.style.display = 'inline';
            } else if (countBadge) {
                countBadge.style.display = 'none';
            }
        }
    })
    .catch(error => {
        console.error('Error loading appeals count:', error);
    });
}

// View appeal details
window.viewAppeal = function viewAppeal(appealId) {
    currentAppealId = appealId;
    
    fetch(`/registrar/appeals/${appealId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const appeal = data.appeal;
            
            // Fill modal with appeal data
            document.getElementById('view-appeal-id').textContent = appeal.id;
            document.getElementById('view-appeal-applicant').textContent = appeal.enrollee.name;
            document.getElementById('view-appeal-application-id').textContent = appeal.enrollee.application_id;
            document.getElementById('view-appeal-grade-level').textContent = appeal.enrollee.grade_level_applied;
            document.getElementById('view-appeal-rejection-reason').textContent = appeal.enrollee.status_reason || 'Not specified';
            document.getElementById('view-appeal-reason').textContent = appeal.reason;
            document.getElementById('view-appeal-submitted').textContent = appeal.submitted_at;
            
            const statusBadge = document.getElementById('view-appeal-status');
            statusBadge.textContent = appeal.status.charAt(0).toUpperCase() + appeal.status.slice(1).replace('_', ' ');
            statusBadge.className = `badge ${appeal.status_badge_class}`;
            
            // Fill admin notes
            document.getElementById('appeal-admin-notes').value = appeal.admin_notes || '';
            
            // Handle documents
            const documentsContainer = document.getElementById('view-appeal-documents');
            if (appeal.documents && appeal.documents.length > 0) {
                documentsContainer.innerHTML = appeal.documents.map((doc, index) => `
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <i class="ri-file-line me-2"></i>
                            <span>${doc.filename}</span>
                            <small class="text-muted d-block">Uploaded: ${new Date(doc.uploaded_at).toLocaleDateString()}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="downloadAppealDocument(${appealId}, ${index})">
                            <i class="ri-download-line"></i>
                        </button>
                    </div>
                `).join('');
            } else {
                documentsContainer.innerHTML = '<p class="text-muted">No supporting documents uploaded</p>';
            }
            
            // Action buttons removed from appeal details view
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('viewAppealModal'));
            modal.show();
        } else {
            showAlert('Error loading appeal details', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error loading appeal details', 'danger');
    });
}

// Quick approve appeal
window.approveAppealQuick = function approveAppealQuick(appealId) {
    if (confirm('Are you sure you want to approve this appeal? This will allow the application to be reconsidered.')) {
        approveAppealAction(appealId, '');
    }
}

// Approve appeal action
function approveAppeal() {
    const notes = document.getElementById('appeal-admin-notes').value;
    approveAppealAction(currentAppealId, notes);
}

function approveAppealAction(appealId, notes) {
    fetch(`/registrar/appeals/${appealId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadAppealsData();
            // Close modal if open
            const modal = bootstrap.Modal.getInstance(document.getElementById('viewAppealModal'));
            if (modal) modal.hide();
        } else {
            showAlert(data.message || 'Error approving appeal', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error approving appeal', 'danger');
    });
}

// Quick reject appeal
window.rejectAppealQuick = function rejectAppealQuick(appealId) {
    const reason = prompt('Please provide a reason for rejecting this appeal:');
    if (reason && reason.trim().length >= 10) {
        rejectAppealAction(appealId, reason);
    } else if (reason !== null) {
        alert('Please provide a detailed reason (at least 10 characters).');
    }
}

// Reject appeal
function rejectAppeal() {
    currentAppealId = currentAppealId;
    // Show reject modal
    const modal = new bootstrap.Modal(document.getElementById('rejectAppealModal'));
    modal.show();
}

// Confirm reject appeal
function confirmRejectAppeal() {
    const reason = document.getElementById('reject-appeal-reason').value;
    
    if (!reason || reason.trim().length < 10) {
        showAlert('Please provide a detailed reason for rejection (at least 10 characters)', 'warning');
        return;
    }
    
    rejectAppealAction(currentAppealId, reason);
}

function rejectAppealAction(appealId, reason) {
    fetch(`/registrar/appeals/${appealId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadAppealsData();
            // Close modals if open
            const rejectModal = bootstrap.Modal.getInstance(document.getElementById('rejectAppealModal'));
            if (rejectModal) rejectModal.hide();
            const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewAppealModal'));
            if (viewModal) viewModal.hide();
        } else {
            showAlert(data.message || 'Error rejecting appeal', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error rejecting appeal', 'danger');
    });
}

// Reconsider application from appeal
window.reconsiderApplicationFromAppeal = function reconsiderApplicationFromAppeal(appealId) {
    const appeal = appealsData.find(a => a.id === appealId);
    if (!appeal) return;
    
    // If appeal is pending, start reconsideration process
    if (appeal.status === 'pending') {
        if (confirm('Start reconsideration of this appeal? This will move it to under review status where you can then approve or reject it.')) {
            startAppealReconsideration(appealId);
        }
        return;
    }
    
    // If appeal is approved, reconsider the actual application
    if (appeal.status === 'approved') {
        // Fill reconsider modal
        document.getElementById('reconsider-applicant-name').textContent = appeal.enrollee.name;
        document.getElementById('reconsider-application-id').textContent = appeal.enrollee.application_id;
        document.getElementById('reconsider-grade-level').textContent = appeal.enrollee.grade_level_applied;
        document.getElementById('reconsider-rejection-reason').textContent = appeal.enrollee.status_reason || 'Not specified';
        
        currentAppealId = appealId;
        
        const modal = new bootstrap.Modal(document.getElementById('reconsiderApplicationModal'));
        modal.show();
    }
}

// Start appeal reconsideration (for pending appeals)
window.startAppealReconsideration = function startAppealReconsideration(appealId) {
    fetch(`/registrar/appeals/${appealId}/start-reconsideration`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ notes: 'Appeal moved to reconsideration for review.' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadAppealsData(); // Reload to show updated status and buttons
        } else {
            showAlert(data.message || 'Error starting reconsideration', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error starting reconsideration', 'danger');
    });
}

// Reconsider application 
function reconsiderAppeal() {
    const appeal = appealsData.find(a => a.id === currentAppealId);
    if (!appeal) return;
    
    reconsiderApplicationFromAppeal(currentAppealId);
}

// Confirm reconsider application
function confirmReconsiderApplication() {
    const notes = document.getElementById('reconsider-notes').value;
    
    fetch(`/registrar/appeals/${currentAppealId}/reconsider`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadAppealsData();
            // Close modals
            const reconsiderModal = bootstrap.Modal.getInstance(document.getElementById('reconsiderApplicationModal'));
            if (reconsiderModal) reconsiderModal.hide();
            const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewAppealModal'));
            if (viewModal) viewModal.hide();
        } else {
            showAlert(data.message || 'Error reconsidering application', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error reconsidering application', 'danger');
    });
}

// Save appeal notes
function saveAppealNotes() {
    const notes = document.getElementById('appeal-admin-notes').value;
    
    fetch(`/registrar/appeals/${currentAppealId}/notes`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message || 'Error saving notes', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error saving notes', 'danger');
    });
}

// View original application
function viewOriginalApplication() {
    const appeal = appealsData.find(a => a.id === currentAppealId);
    if (appeal) {
        viewApplication(appeal.enrollee.id);
        // Close appeal modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('viewAppealModal'));
        if (modal) modal.hide();
    }
}

// Search appeals
function searchAppeals() {
    loadAppealsData();
}

// Download appeal document
window.downloadAppealDocument = function(appealId, documentIndex) {
    try {
        // Create download URL
        const downloadUrl = `/registrar/appeals/${appealId}/documents/${documentIndex}/download`;
        
        // Create temporary link and trigger download
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
    } catch (error) {
        console.error('Download error:', error);
        showAlert('Error downloading document', 'danger');
    }
}

// Utility function to show alerts
function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    if (alertContainer) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        alertContainer.innerHTML = alertHtml;
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    } else {
        // Fallback to regular alert
        alert(message);
    }
}