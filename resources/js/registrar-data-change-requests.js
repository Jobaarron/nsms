/**
 * Registrar Data Change Requests Management
 * Handles viewing, approving, and rejecting data change requests from enrollees
 */

// Namespace to prevent conflicts
window.RegistrarDataChangeRequests = (function() {
    'use strict';
    
    // Private variables
    let currentChangeRequest = null;
    let changeRequestsData = [];
    let filteredChangeRequests = [];
    let isInitialized = false;

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we're on the registrar applications page
        if (document.getElementById('data-change-requests-tab')) {
            try {
                if (!isInitialized) {
                    initializeDataChangeRequests();
                    isInitialized = true;
                }
            } catch (error) {
                console.error('Error initializing data change requests:', error);
            }
        }
    });

    // Add global error handler to prevent page refresh
    window.addEventListener('error', function(event) {
        if (event.filename && event.filename.includes('registrar-data-change-requests')) {
            console.error('Registrar Data Change Requests Error:', event.error);
            event.preventDefault();
            return false;
        }
    });

/**
 * Initialize the data change requests system
 */
function initializeDataChangeRequests() {
    console.log('Initializing registrar data change requests...');
    
    // Setup CSRF token first
    setupCSRFToken();
    
    // Check if required elements exist
    const tab = document.getElementById('data-change-requests-tab');
    const pane = document.getElementById('data-change-requests');
    const tableBody = document.getElementById('changeRequestsTableBody');
    
    console.log('Tab element:', tab);
    console.log('Pane element:', pane);
    console.log('Table body element:', tableBody);
    
    if (!tab || !pane || !tableBody) {
        console.error('Required elements not found for data change requests');
        return;
    }
    
    console.log('Data change requests elements found, setting up event listeners...');
    
    // Setup all event listeners immediately
    setupEventListeners();
    
    // Add tab click event listener - only load data when tab is clicked
    tab.addEventListener('click', function(e) {
        console.log('Data change requests tab clicked');
        
        // Add a small delay to ensure the tab pane is shown
        setTimeout(() => {
            loadChangeRequestsData();
        }, 100);
    });
}

/**
 * Setup CSRF token for AJAX requests
 */
function setupCSRFToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        window.csrfToken = token.getAttribute('content');
    } else {
        console.error('CSRF token not found in meta tags');
    }
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    console.log('Setting up event listeners for data change requests...');
    
    // Filter buttons
    const filterButtons = document.querySelectorAll('input[name="changeRequestStatus"]');
    console.log('Found filter buttons:', filterButtons.length);
    
    filterButtons.forEach((button, index) => {
        console.log(`Filter button ${index}:`, button.id, button.value);
        button.addEventListener('change', function() {
            console.log('Filter button clicked:', this.value);
            filterChangeRequests(this.value);
        });
    });
    
    // Search input
    const searchInput = document.getElementById('changeRequestSearch');
    console.log('Search input found:', !!searchInput);
    
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchChangeRequests(this.value);
            }, 300);
        });
    }
}

/**
 * Load change requests data from server
 */
function loadChangeRequestsData() {
    console.log('Loading change requests data...');
    
    const tableBody = document.getElementById('changeRequestsTableBody');
    
    if (!tableBody) {
        console.error('Change requests table body not found');
        return;
    }
    
    // Show loading state
    tableBody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2">Loading change requests...</p>
            </td>
        </tr>
    `;
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    console.log('CSRF Token:', csrfToken ? 'Found' : 'Not found');
    
    fetch('/registrar/data-change-requests', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken || ''
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Change requests data loaded:', data);
        
        if (data.success && data.requests) {
            changeRequestsData = data.requests;
            filteredChangeRequests = [...changeRequestsData];
            
            if (changeRequestsData.length === 0) {
                // Show empty state
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="ri-file-list-3-line fs-2 text-muted"></i>
                            <p class="text-muted mt-2">No data change requests found</p>
                        </td>
                    </tr>
                `;
            } else {
                console.log('Found ' + changeRequestsData.length + ' requests');
                displayChangeRequests(filteredChangeRequests);
            }
        } else {
            throw new Error(data.message || 'Failed to load change requests');
        }
    })
    .catch(error => {
        console.error('Error loading change requests:', error);
        
        // Show error state
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="ri-error-warning-line fs-2 text-danger"></i>
                    <p class="text-danger mt-2">Failed to load change requests</p>
                    <small class="text-muted">Error: ${error.message}</small><br>
                    <div class="mt-3">
                        <button class="btn btn-outline-primary btn-sm" onclick="loadChangeRequestsData()">
                            <i class="ri-refresh-line me-1"></i>Retry
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
}

/**
 * Show empty state when no requests found
 */
function showEmptyState() {
    const tableBody = document.getElementById('changeRequestsTableBody');
    const emptyState = document.getElementById('changeRequestsEmptyState');
    
    if (tableBody) {
        tableBody.innerHTML = '';
    }
    
    if (emptyState) {
        emptyState.style.display = 'block';
    }
}

/**
 * Display change requests in the table
 */
function displayChangeRequests(requests) {
    const tableBody = document.getElementById('changeRequestsTableBody');
    const emptyState = document.getElementById('changeRequestsEmptyState');
    
    if (!tableBody) return;
    
    if (!requests || requests.length === 0) {
        tableBody.innerHTML = '';
        if (emptyState) {
            emptyState.style.display = 'block';
        }
        return;
    }
    
    if (emptyState) {
        emptyState.style.display = 'none';
    }
    
    const tbody = requests.map((request, index) => {
        const statusBadge = getStatusBadge(request.status);
        const submittedDate = formatDateTime(request.created_at);
        
        return `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div>
                            <div class="fw-medium">${escapeHtml(request.enrollee?.full_name || 'N/A')}</div>
                            <small class="text-muted">${escapeHtml(request.enrollee?.application_id || 'N/A')}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-light text-dark">${escapeHtml(request.human_field_name || request.field_name)}</span>
                </td>
                <td>
                    <div class="change-details">
                        <div class="text-muted small">From:</div>
                        <div class="small mb-1">${escapeHtml(request.old_value || 'Not provided')}</div>
                        <div class="text-muted small">To:</div>
                        <div class="fw-medium">${escapeHtml(request.new_value || 'N/A')}</div>
                        ${request.reason ? `<div class="text-muted small mt-1"><i class="ri-information-line me-1"></i>${escapeHtml(request.reason)}</div>` : ''}
                    </div>
                </td>
                <td>${statusBadge}</td>
                <td>
                    <small class="text-muted">${submittedDate}</small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary" onclick="viewChangeRequest(${request.id})" title="View Details">
                            <i class="ri-eye-line"></i>
                        </button>
                        ${request.status === 'pending' ? `
                            <button class="btn btn-outline-success" onclick="approveChangeRequest(${request.id})" title="Approve">
                                <i class="ri-check-line"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="rejectChangeRequest(${request.id})" title="Reject">
                                <i class="ri-close-line"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
    
    tableBody.innerHTML = tbody;
}

/**
 * Filter change requests by status
 */
function filterChangeRequests(status) {
    console.log('Filtering change requests by status:', status);
    console.log('Total change requests data:', changeRequestsData.length);
    
    if (!status) {
        filteredChangeRequests = [...changeRequestsData];
    } else {
        filteredChangeRequests = changeRequestsData.filter(request => request.status === status);
    }
    
    console.log('Filtered results:', filteredChangeRequests.length);
    displayChangeRequests(filteredChangeRequests);
}

/**
 * Search change requests
 */
function searchChangeRequests(searchTerm = '') {
    const searchInput = document.getElementById('changeRequestSearch');
    const query = searchTerm || (searchInput ? searchInput.value.trim() : '');
    
    console.log('Searching change requests:', query);
    
    if (!query) {
        // Get current filter status
        const activeFilter = document.querySelector('input[name="changeRequestStatus"]:checked');
        const status = activeFilter ? activeFilter.value : '';
        filterChangeRequests(status);
        return;
    }
    
    const searchResults = changeRequestsData.filter(request => {
        const studentName = (request.enrollee?.full_name || '').toLowerCase();
        const applicationId = (request.enrollee?.application_id || '').toLowerCase();
        const fieldName = (request.human_field_name || request.field_name || '').toLowerCase();
        const newValue = (request.new_value || '').toLowerCase();
        const searchLower = query.toLowerCase();
        
        return studentName.includes(searchLower) || 
               applicationId.includes(searchLower) || 
               fieldName.includes(searchLower) ||
               newValue.includes(searchLower);
    });
    
    filteredChangeRequests = searchResults;
    displayChangeRequests(filteredChangeRequests);
}

/**
 * View change request details
 */
function viewChangeRequest(requestId) {
    const request = changeRequestsData.find(r => r.id === requestId);
    if (!request) {
        showAlert('Change request not found', 'error');
        return;
    }
    
    currentChangeRequest = request;
    
    // Create and show modal
    const modalHtml = `
        <div class="modal fade" id="viewChangeRequestModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ri-file-edit-line me-2"></i>
                            Data Change Request Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Student Information</h6>
                                <dl class="row">
                                    <dt class="col-sm-4">Name:</dt>
                                    <dd class="col-sm-8">${escapeHtml(request.enrollee?.full_name || 'N/A')}</dd>
                                    <dt class="col-sm-4">Application ID:</dt>
                                    <dd class="col-sm-8">${escapeHtml(request.enrollee?.application_id || 'N/A')}</dd>
                                    <dt class="col-sm-4">Email:</dt>
                                    <dd class="col-sm-8">${escapeHtml(request.enrollee?.email || 'N/A')}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Request Information</h6>
                                <dl class="row">
                                    <dt class="col-sm-4">Field:</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge bg-light text-dark">${escapeHtml(request.human_field_name || request.field_name)}</span>
                                    </dd>
                                    <dt class="col-sm-4">Status:</dt>
                                    <dd class="col-sm-8">${getStatusBadge(request.status)}</dd>
                                    <dt class="col-sm-4">Submitted:</dt>
                                    <dd class="col-sm-8">${formatDateTime(request.created_at)}</dd>
                                </dl>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted">Change Details</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Current Value:</label>
                                                <div class="p-2 bg-white rounded border">
                                                    ${escapeHtml(request.old_value || 'Not provided')}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Requested Value:</label>
                                                <div class="p-2 bg-white rounded border fw-medium">
                                                    ${escapeHtml(request.new_value || 'N/A')}
                                                </div>
                                            </div>
                                        </div>
                                        ${request.reason ? `
                                            <div class="mt-3">
                                                <label class="form-label text-muted">Reason for Change:</label>
                                                <div class="p-2 bg-white rounded border">
                                                    ${escapeHtml(request.reason)}
                                                </div>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${request.admin_notes ? `
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-muted">Admin Notes</h6>
                                    <div class="alert alert-info">
                                        ${escapeHtml(request.admin_notes)}
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                        
                        ${request.status === 'pending' ? `
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-muted">Admin Notes (Optional)</h6>
                                    <textarea class="form-control" id="adminNotes" rows="3" placeholder="Add notes for this request..."></textarea>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                    <div class="modal-footer">
                        ${request.status === 'pending' ? `
                            <button type="button" class="btn btn-success" onclick="approveChangeRequestFromModal(${request.id})">
                                <i class="ri-check-line me-1"></i>Approve
                            </button>
                            <button type="button" class="btn btn-danger" onclick="rejectChangeRequestFromModal(${request.id})">
                                <i class="ri-close-line me-1"></i>Reject
                            </button>
                        ` : ''}
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal
    const existingModal = document.getElementById('viewChangeRequestModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewChangeRequestModal'));
    modal.show();
    
    // Clean up when modal is hidden
    document.getElementById('viewChangeRequestModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

/**
 * Approve change request
 */
function approveChangeRequest(requestId) {
    if (!confirm('Are you sure you want to approve this change request?')) {
        return;
    }
    
    processChangeRequest(requestId, 'approved');
}

/**
 * Approve change request from modal
 */
function approveChangeRequestFromModal(requestId) {
    const adminNotes = document.getElementById('adminNotes')?.value || '';
    processChangeRequest(requestId, 'approved', adminNotes);
}

/**
 * Reject change request
 */
function rejectChangeRequest(requestId) {
    const reason = prompt('Please provide a reason for rejecting this request:');
    if (!reason) {
        return;
    }
    
    processChangeRequest(requestId, 'rejected', reason);
}

/**
 * Reject change request from modal
 */
function rejectChangeRequestFromModal(requestId) {
    const adminNotes = document.getElementById('adminNotes')?.value || '';
    if (!adminNotes.trim()) {
        showAlert('Please provide a reason for rejecting this request', 'warning');
        return;
    }
    
    processChangeRequest(requestId, 'rejected', adminNotes);
}

/**
 * Process change request (approve/reject)
 */
function processChangeRequest(requestId, action, notes = '') {
    const request = changeRequestsData.find(r => r.id === requestId);
    if (!request) {
        showAlert('Change request not found', 'error');
        return;
    }
    
    // Show loading state
    const actionButtons = document.querySelectorAll(`button[onclick*="${requestId}"]`);
    actionButtons.forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-line ri-spin"></i>';
    });
    
    // Get fresh CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken || '';
    
    if (!csrfToken) {
        console.error('CSRF token not available');
        showAlert('Security token not found. Please refresh the page.', 'error');
        return;
    }
    
    fetch(`/registrar/data-change-requests/${requestId}/process`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            action: action,
            admin_notes: notes
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert(`Change request ${action} successfully`, 'success');
            
            // Close modal if open
            const modal = document.getElementById('viewChangeRequestModal');
            if (modal) {
                bootstrap.Modal.getInstance(modal)?.hide();
            }
            
            // Reload data
            loadChangeRequestsData();
        } else {
            throw new Error(data.message || `Failed to ${action} change request`);
        }
    })
    .catch(error => {
        console.error(`Error ${action}ing change request:`, error);
        showAlert(`Failed to ${action} change request: ${error.message}`, 'error');
    })
    .finally(() => {
        // Restore buttons
        actionButtons.forEach(btn => {
            btn.disabled = false;
            if (action === 'approved') {
                btn.innerHTML = '<i class="ri-check-line"></i>';
            } else {
                btn.innerHTML = '<i class="ri-close-line"></i>';
            }
        });
    });
}

/**
 * Get status badge HTML
 */
function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">Pending</span>',
        'approved': '<span class="badge bg-success">Approved</span>',
        'rejected': '<span class="badge bg-danger">Rejected</span>'
    };
    
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

/**
 * Format datetime for display
 */
function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    
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
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    
    const alertId = 'alert-' + Date.now();
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" id="${alertId}">
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

/**
 * Test function to check if the route is accessible
 */
function testDataChangeRequests() {
    const testResult = document.getElementById('testResult');
    if (testResult) {
        testResult.innerHTML = '<span class="text-info">Testing...</span>';
    }
    
    fetch('/registrar/test-data-change-requests', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Test result:', data);
        if (testResult) {
            testResult.innerHTML = `<span class="text-success">✓ Connected! Requests: ${data.total_requests}, User: ${data.auth_user}</span>`;
        }
    })
    .catch(error => {
        console.error('Test failed:', error);
        if (testResult) {
            testResult.innerHTML = `<span class="text-danger">✗ Failed: ${error.message}</span>`;
        }
    });
}

    // Return public methods
    return {
        loadChangeRequestsData: loadChangeRequestsData,
        viewChangeRequest: viewChangeRequest,
        approveChangeRequest: approveChangeRequest,
        rejectChangeRequest: rejectChangeRequest,
        approveChangeRequestFromModal: approveChangeRequestFromModal,
        rejectChangeRequestFromModal: rejectChangeRequestFromModal,
        searchChangeRequests: searchChangeRequests,
        testDataChangeRequests: testDataChangeRequests
    };

})();

// Expose functions to global scope for onclick handlers
window.loadChangeRequestsData = function() {
    if (window.RegistrarDataChangeRequests) {
        return window.RegistrarDataChangeRequests.loadChangeRequestsData();
    }
};
window.viewChangeRequest = function(id) {
    if (window.RegistrarDataChangeRequests) {
        return window.RegistrarDataChangeRequests.viewChangeRequest(id);
    }
};
window.approveChangeRequest = function(id) {
    if (window.RegistrarDataChangeRequests) {
        return window.RegistrarDataChangeRequests.approveChangeRequest(id);
    }
};
window.rejectChangeRequest = function(id) {
    if (window.RegistrarDataChangeRequests) {
        return window.RegistrarDataChangeRequests.rejectChangeRequest(id);
    }
};
window.approveChangeRequestFromModal = function(id) {
    if (window.RegistrarDataChangeRequests) {
        return window.RegistrarDataChangeRequests.approveChangeRequestFromModal(id);
    }
};
window.rejectChangeRequestFromModal = function(id) {
    if (window.RegistrarDataChangeRequests) {
        return window.RegistrarDataChangeRequests.rejectChangeRequestFromModal(id);
    }
};
window.searchChangeRequests = function(searchTerm) {
    if (window.RegistrarDataChangeRequests) {
        return window.RegistrarDataChangeRequests.searchChangeRequests(searchTerm);
    }
};
window.testDataChangeRequests = function() {
    if (window.RegistrarDataChangeRequests) {
        return window.RegistrarDataChangeRequests.testDataChangeRequests();
    }
};
