// Admin Enrollment Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Enrollment Management: DOM loaded, initializing...');
    
    // Check if we're on the enrollments page
    if (window.location.pathname.includes('/admin/enrollments')) {
        console.log('Admin Enrollment Management: On enrollments page, initializing...');
        
        // Initialize the enrollment management system
        initializeEnrollmentManagement();
        
        console.log('Admin Enrollment Management: Loading applications data...');
        loadApplicationsData();
        
        setupEventListeners();
    } else {
        console.log('Admin Enrollment Management: Not on enrollments page, skipping initialization');
    }
});

// Global variables
let currentApplicationId = null;
let currentDocumentIndex = null;
let currentAppointmentId = null;
let selectedApplications = [];
let currentBulkAction = null;

// Initialize the system
function initializeEnrollmentManagement() {
    console.log('Initializing enrollment management system...');
    
    // Setup Bootstrap 5 tab change handlers
    const tabElements = document.querySelectorAll('#enrollmentTabs button[data-bs-toggle="tab"]');
    console.log('Found tab elements:', tabElements.length);
    
    tabElements.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            const targetTab = event.target.getAttribute('data-bs-target');
            console.log('Tab changed to:', targetTab);
            
            switch(targetTab) {
                case '#applications':
                    console.log('Loading applications data...');
                    loadApplicationsData();
                    break;
                case '#documents':
                    console.log('Loading documents data...');
                    loadDocumentsData();
                    break;
                case '#appointments':
                    console.log('Loading appointments data...');
                    loadAppointmentsData();
                    break;
                case '#notices':
                    console.log('Loading notices data...');
                    loadNoticesData();
                    break;
            }
        });
    });
}

// Setup event listeners
function setupEventListeners() {
    // Filter change handlers
    document.getElementById('status-filter').addEventListener('change', filterApplications);
    document.getElementById('grade-filter').addEventListener('change', filterApplications);
    document.getElementById('search-input').addEventListener('input', debounce(filterApplications, 300));
    
    // Document filters
    document.getElementById('doc-status-filter').addEventListener('change', filterDocuments);
    document.getElementById('doc-type-filter').addEventListener('change', filterDocuments);
    
    // Appointment filters
    document.getElementById('appointment-status-filter').addEventListener('change', filterAppointments);
    document.getElementById('appointment-date-filter').addEventListener('change', filterAppointments);
    
    // Notice recipients change
    document.getElementById('notice-recipients').addEventListener('change', function() {
        const specificDiv = document.getElementById('specific-applicant-div');
        if (this.value === 'specific') {
            specificDiv.style.display = 'block';
            loadApplicantsList();
        } else {
            specificDiv.style.display = 'none';
        }
    });
}

// Load applications data
function loadApplicationsData() {
    console.log('Loading applications data...');
    
    // Show loading state
    const tbody = document.querySelector('#applications-table tbody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
    }
    
    const statusFilter = document.getElementById('status-filter')?.value || '';
    const gradeFilter = document.getElementById('grade-filter')?.value || '';
    const searchQuery = document.getElementById('search-input')?.value || '';
    
    const params = new URLSearchParams();
    if (statusFilter) params.append('status', statusFilter);
    if (gradeFilter) params.append('grade_level', gradeFilter);
    if (searchQuery) params.append('search', searchQuery);
    
    const url = `/admin/enrollments/applications?${params.toString()}`;
    console.log('Fetching from URL:', url);
    
    // Add CSRF token to headers
    const headers = {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    };
    
    fetch(url, { headers })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            if (data.success) {
                console.log('Applications count:', data.applications?.length || 0);
                console.log('Summary data:', data.summary);
                populateApplicationsTable(data.applications || []);
                updateSummaryCards(data.summary || {});
            } else {
                console.error('API returned error:', data.message);
                showAlert('Error loading applications data: ' + (data.message || 'Unknown error'), 'danger');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Error loading data</td></tr>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading applications:', error);
            showAlert('Error loading applications: ' + error.message, 'danger');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Error loading data</td></tr>';
            }
        });
}

// Populate applications table
function populateApplicationsTable(applications) {
    console.log('Populating applications table with:', applications);
    const tbody = document.querySelector('#applications-table tbody');
    
    if (!tbody) {
        console.error('Applications table tbody not found');
        return;
    }
    
    if (!applications || applications.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No applications found</td></tr>';
        return;
    }
    
    tbody.innerHTML = applications.map(app => {
        // Create full name from first_name, middle_name, last_name
        const fullName = [app.first_name, app.middle_name, app.last_name].filter(Boolean).join(' ');
        
        return `
        <tr>
            <td>
                <input type="checkbox" class="form-check-input application-checkbox" value="${app.id}">
            </td>
            <td>
                <div class="fw-bold">${app.application_id || 'N/A'}</div>
                <small class="text-muted">${formatDate(app.application_date)}</small>
            </td>
            <td>
                <div class="fw-bold">${fullName || app.full_name || 'N/A'}</div>
                <small class="text-muted">${app.email || 'N/A'}</small>
            </td>
            <td>${app.grade_level_applied || 'N/A'}</td>
            <td>${app.contact_number || 'N/A'}</td>
            <td>
                <span class="badge ${getStatusBadgeClass(app.enrollment_status)}">${app.enrollment_status || 'pending'}</span>
            </td>
            <td>${app.student_type || 'N/A'}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewApplication(${app.id})" title="View Details">
                        <i class="ri-eye-line"></i>
                    </button>
                    <button class="btn btn-outline-info" onclick="viewDocuments(${app.id})" title="View Documents">
                        <i class="ri-folder-line"></i>
                    </button>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="ri-more-line"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="approveApplication(${app.id})">
                                <i class="ri-check-line me-2"></i>Approve
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="declineApplication(${app.id})">
                                <i class="ri-close-line me-2"></i>Decline
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteApplication(${app.id})">
                                <i class="ri-delete-bin-line me-2"></i>Delete
                            </a></li>
                        </ul>
                    </div>
                </div>
            </td>
        </tr>
        `;
    }).join('');
    
    console.log('Applications table populated successfully');
}

// View application details
function viewApplication(applicationId) {
    currentApplicationId = applicationId;
    
    fetch(`/admin/enrollments/applications/${applicationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateApplicationModal(data.application);
                new bootstrap.Modal(document.getElementById('applicationModal')).show();
            } else {
                showAlert('Error loading application details', 'danger');
            }
        })
        .catch(error => {
            console.error('Error fetching application:', error);
            showAlert('Error loading application', 'danger');
        });
}

// Populate application modal
function populateApplicationModal(app) {
    // Safely populate fields with null checks
    const safeSet = (id, value, fallback = 'N/A') => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value || fallback;
        }
    };
    
    safeSet('app-full-name', app.full_name || `${app.first_name || ''} ${app.last_name || ''}`.trim());
    safeSet('app-dob', formatDate(app.date_of_birth));
    safeSet('app-gender', app.gender);
    safeSet('app-email', app.email);
    safeSet('app-contact', app.contact_number);
    safeSet('app-address', app.address);
    safeSet('app-grade', app.grade_level_applied);
    safeSet('app-type', app.student_type);
    safeSet('app-father', app.father_name);
    safeSet('app-mother', app.mother_name);
    safeSet('app-guardian', app.guardian_name);
    safeSet('app-guardian-contact', app.guardian_contact);
    
    // Set ID photo
    const idPhoto = document.getElementById('app-id-photo');
    if (app.id_photo_data_url) {
        idPhoto.src = app.id_photo_data_url;
    } else {
        idPhoto.src = '/images/default-avatar.png';
    }
    
    // Set current status
    const statusBadge = document.getElementById('app-current-status');
    statusBadge.textContent = app.enrollment_status;
    statusBadge.className = `badge bg-${getStatusColor(app.enrollment_status)} fs-6`;
    
    // Set status select
    document.getElementById('status-select').value = app.enrollment_status;
    document.getElementById('status-reason').value = app.status_reason || '';
}

// Update application status
function updateApplicationStatus(status, reason = '') {
    if (!currentApplicationId) {
        showAlert('No application selected', 'danger');
        return;
    }
    
    fetch(`/admin/enrollments/applications/${currentApplicationId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: status,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Application status updated successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('applicationDetailsModal')).hide();
            loadApplicationsData();
        } else {
            showAlert(data.message || 'Error updating status', 'danger');
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        showAlert('Error updating application status', 'danger');
    });
}

// Load documents data
function loadDocumentsData() {
    fetch('/admin/enrollments/documents')
        .then(response => response.json())
        .then(data => {
            populateDocumentsTable(data.documents);
        })
        .catch(error => {
            console.error('Error loading documents:', error);
            showAlert('Error loading documents data', 'danger');
        });
}

// View document
function viewDocument(applicationId, documentIndex) {
    currentApplicationId = applicationId;
    currentDocumentIndex = documentIndex;
    
    fetch(`/admin/enrollments/documents/${applicationId}/${documentIndex}`)
        .then(response => response.json())
        .then(data => {
            populateDocumentModal(data);
            new bootstrap.Modal(document.getElementById('documentReviewModal')).show();
        })
        .catch(error => {
            console.error('Error loading document:', error);
            showAlert('Error loading document', 'danger');
        });
}

// Utility functions
function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'approved': 'success',
        'rejected': 'danger',
        'enrolled': 'primary'
    };
    return colors[status] || 'secondary';
}

function getAppointmentStatusColor(status) {
    const colors = {
        'pending': 'secondary',
        'scheduled': 'info',
        'completed': 'success',
        'overdue': 'danger'
    };
    return colors[status] || 'secondary';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
}

function formatFileSize(bytes) {
    if (!bytes) return 'N/A';
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    if (bytes === 0) return '0 Bytes';
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
}

function viewDocumentFile(path) {
    if (!path) {
        showAlert('Document path not found', 'danger');
        return;
    }
    // Open document in new tab
    window.open(`/storage/${path}`, '_blank');
}

function showLoading() {
    new bootstrap.Modal(document.getElementById('loadingModal')).show();
}

function hideLoading() {
    const loadingModal = bootstrap.Modal.getInstance(document.getElementById('loadingModal'));
    if (loadingModal) {
        loadingModal.hide();
    }
}

function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container');
    const alertId = 'alert-' + Date.now();
    
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert" id="${alertId}">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.insertAdjacentHTML('beforeend', alertHTML);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
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

// Filter functions
function filterApplications() {
    // Implementation for filtering applications
    loadApplicationsData();
}

function filterDocuments() {
    // Implementation for filtering documents
    loadDocumentsData();
}

function filterAppointments() {
    // Implementation for filtering appointments
    loadAppointmentsData();
}

function clearFilters() {
    const statusFilter = document.getElementById('status-filter');
    const gradeFilter = document.getElementById('grade-filter');
    const searchInput = document.getElementById('search-input');
    
    if (statusFilter) statusFilter.value = '';
    if (gradeFilter) gradeFilter.value = '';
    if (searchInput) searchInput.value = '';
    
    loadApplicationsData();
    showAlert('Filters cleared', 'info');
}

function exportEnrollments() {
    window.location.href = '/admin/enrollments/export';
}

// Load appointments data
function loadAppointmentsData() {
    console.log('Loading appointments data...');
    
    fetch('/admin/enrollments/appointments')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateAppointmentsTable(data.appointments);
            } else {
                showAlert('Error loading appointments data', 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading appointments:', error);
            showAlert('Error loading appointments data', 'danger');
        });
}

function loadNoticesData() {
    console.log('Loading notices data...');
    
    fetch('/admin/enrollments/notices')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateNoticesTable(data.notices);
            } else {
                showAlert('Error loading notices data', 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading notices:', error);
            showAlert('Error loading notices data', 'danger');
        });
}

function populateNoticesTable(notices) {
    console.log('Populating notices table...', notices);
    
    const tbody = document.querySelector('#notices-table tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (!notices || notices.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No notices found</td></tr>';
        return;
    }
    
    notices.forEach(notice => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${notice.title}</td>
            <td>${notice.is_global ? 'Global' : (notice.enrollee ? notice.enrollee.full_name : 'Individual')}</td>
            <td>
                <span class="badge bg-${getNoticePriorityColor(notice.priority)}">
                    ${notice.priority.charAt(0).toUpperCase() + notice.priority.slice(1)}
                </span>
            </td>
            <td>${notice.target_status || 'All'}</td>
            <td>${formatDate(notice.created_at)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewNotice('${notice.id}')" title="View Notice">
                        <i class="ri-eye-line"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="deleteNotice('${notice.id}')" title="Delete Notice">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function getNoticePriorityColor(priority) {
    const colors = {
        'low': 'info',
        'medium': 'warning',
        'high': 'danger'
    };
    return colors[priority] || 'secondary';
}

function viewNotice(noticeId) {
    showAlert('Notice viewing feature coming soon', 'info');
}

function deleteNotice(noticeId) {
    if (confirm('Are you sure you want to delete this notice?')) {
        showAlert('Notice deletion feature coming soon', 'info');
    }
}

function populateAppointmentsTable(appointments) {
    console.log('Populating appointments table...', appointments);
    
    const tbody = document.querySelector('#appointments-table tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (!appointments || appointments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No appointments found</td></tr>';
        return;
    }
    
    appointments.forEach(appointment => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${appointment.application_id}</td>
            <td>${appointment.student_name}</td>
            <td>${appointment.grade_level}${appointment.strand ? ' - ' + appointment.strand : ''}</td>
            <td>${appointment.contact_number || 'N/A'}</td>
            <td>${appointment.preferred_schedule ? formatDate(appointment.preferred_schedule) : 'Not set'}</td>
            <td>${appointment.enrollment_date ? formatDate(appointment.enrollment_date) : 'Not completed'}</td>
            <td>
                <span class="badge bg-${getAppointmentStatusColor(appointment.appointment_status)}">
                    ${appointment.appointment_status.charAt(0).toUpperCase() + appointment.appointment_status.slice(1)}
                </span>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewApplication('${appointment.application_id}')" title="View Application">
                        <i class="ri-eye-line"></i>
                    </button>
                    <button class="btn btn-outline-info" onclick="scheduleAppointment('${appointment.application_id}')" title="Schedule Appointment">
                        <i class="ri-calendar-line"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function populateDocumentsTable(documents) {
    console.log('Populating documents table...', documents);
    
    const tbody = document.querySelector('#documents-table tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (!documents || documents.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No documents found</td></tr>';
        return;
    }
    
    documents.forEach(doc => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${doc.application_id}</td>
            <td>${doc.student_name}</td>
            <td>${doc.document_type}</td>
            <td>${doc.filename}</td>
            <td>${formatDate(doc.upload_date)}</td>
            <td>
                <span class="badge bg-${getStatusColor(doc.status)}">
                    ${doc.status.charAt(0).toUpperCase() + doc.status.slice(1)}
                </span>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewDocument('${doc.enrollee_id}', '${doc.document_index}')" title="View Document">
                        <i class="ri-eye-line"></i>
                    </button>
                    <button class="btn btn-outline-success" onclick="approveDocument('${doc.enrollee_id}', '${doc.document_index}')" title="Approve">
                        <i class="ri-check-line"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="rejectDocument('${doc.enrollee_id}', '${doc.document_index}')" title="Reject">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function populateDocumentsModal(documents) {
    console.log('Populating documents modal...', documents);
    
    const container = document.getElementById('documents-modal-content');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (!documents || documents.length === 0) {
        container.innerHTML = '<p class="text-muted">No documents found for this application.</p>';
        return;
    }
    
    documents.forEach((doc, index) => {
        const docElement = document.createElement('div');
        docElement.className = 'card mb-3';
        docElement.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="card-title">${doc.type}</h6>
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="ri-file-line me-1"></i>${doc.filename}<br>
                                <i class="ri-calendar-line me-1"></i>Uploaded: ${formatDate(doc.upload_date)}<br>
                                ${doc.size ? `<i class="ri-file-info-line me-1"></i>Size: ${formatFileSize(doc.size)}<br>` : ''}
                            </small>
                        </p>
                        <span class="badge bg-${getStatusColor(doc.status)}">
                            ${doc.status.charAt(0).toUpperCase() + doc.status.slice(1)}
                        </span>
                    </div>
                    <div class="btn-group-vertical btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewDocumentFile('${doc.path}')" title="View Document">
                            <i class="ri-eye-line"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="approveDocument('${doc.enrollee_id}', '${index}')" title="Approve">
                            <i class="ri-check-line"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="rejectDocument('${doc.enrollee_id}', '${index}')" title="Reject">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(docElement);
    });
}

function populateDocumentModal(document) {
    console.log('Populating document modal...', document);
}

function updateSummaryCards(summary = {}) {
    console.log('Updating summary cards with:', summary);
    
    const totalElement = document.getElementById('total-count');
    const pendingElement = document.getElementById('pending-count');
    const approvedElement = document.getElementById('approved-count');
    const appointmentsElement = document.getElementById('appointments-count');
    
    if (totalElement) totalElement.textContent = summary.total || 0;
    if (pendingElement) pendingElement.textContent = summary.pending || 0;
    if (approvedElement) approvedElement.textContent = summary.approved || 0;
    if (appointmentsElement) appointmentsElement.textContent = summary.appointments || 0;
    
    console.log('Summary cards updated successfully');
}

// Setup select all checkbox functionality
function setupSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.application-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectionCount();
        });
    }
}

// Update selection count and show/hide bulk actions panel
function updateSelectionCount() {
    const checkboxes = document.querySelectorAll('.application-checkbox:checked');
    const count = checkboxes.length;
    selectedApplications = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    document.getElementById('selectedCount').textContent = count;
    
    const bulkPanel = document.getElementById('bulk-actions-panel');
    if (count > 0) {
        bulkPanel.style.display = 'block';
    } else {
        bulkPanel.style.display = 'none';
    }
}

// Clear all selections
function clearAllSelections() {
    const checkboxes = document.querySelectorAll('.application-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('select-all').checked = false;
    updateSelectionCount();
}

// Get selected applications helper function
function getSelectedApplications() {
    const checkboxes = document.querySelectorAll('.application-checkbox:checked');
    return Array.from(checkboxes).map(cb => parseInt(cb.value));
}

// Approve single application
function approveApplication(id, applicationId, studentName) {
    currentApplicationId = id;
    document.getElementById('approve-app-id').textContent = applicationId;
    document.getElementById('approve-student-name').textContent = studentName;
    document.getElementById('approve-reason').value = '';
    
    new bootstrap.Modal(document.getElementById('approveApplicationModal')).show();
}

// Confirm approve application
function confirmApproveApplication() {
    const reason = document.getElementById('approve-reason').value;
    
    showLoading();
    
    fetch(`/admin/enrollments/applications/${currentApplicationId}/approve`, {
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
            showAlert('Application approved successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('approveApplicationModal')).hide();
            loadApplicationsData();
        } else {
            showAlert(data.message || 'Error approving application', 'danger');
        }
    })
    .catch(error => {
        console.error('Error approving application:', error);
        showAlert('Error approving application', 'danger');
    })
    .finally(() => {
        hideLoading();
    });
}

// Decline single application
function declineApplication(id, applicationId, studentName) {
    currentApplicationId = id;
    document.getElementById('decline-app-id').textContent = applicationId;
    document.getElementById('decline-student-name').textContent = studentName;
    document.getElementById('decline-reason').value = '';
    
    new bootstrap.Modal(document.getElementById('declineApplicationModal')).show();
}

// Confirm decline application
function confirmDeclineApplication() {
    const reason = document.getElementById('decline-reason').value.trim();
    
    if (!reason) {
        document.getElementById('decline-reason').classList.add('is-invalid');
        return;
    }
    
    document.getElementById('decline-reason').classList.remove('is-invalid');
    showLoading();
    
    fetch(`/admin/enrollments/applications/${currentApplicationId}/decline`, {
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
            showAlert('Application declined successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('declineApplicationModal')).hide();
            loadApplicationsData();
        } else {
            showAlert(data.message || 'Error declining application', 'danger');
        }
    })
    .catch(error => {
        console.error('Error declining application:', error);
        showAlert('Error declining application', 'danger');
    })
    .finally(() => {
        hideLoading();
    });
}

// Delete single application
function deleteApplication(id, applicationId, studentName) {
    currentApplicationId = id;
    document.getElementById('delete-app-id').textContent = applicationId;
    document.getElementById('delete-student-name').textContent = studentName;
    
    new bootstrap.Modal(document.getElementById('deleteApplicationModal')).show();
}

// Confirm delete application
function confirmDeleteApplication() {
    showLoading();
    
    fetch(`/admin/enrollments/applications/${currentApplicationId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Application deleted successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('deleteApplicationModal')).hide();
            loadApplicationsData();
        } else {
            showAlert(data.message || 'Error deleting application', 'danger');
        }
    })
    .catch(error => {
        console.error('Error deleting application:', error);
        showAlert('Error deleting application', 'danger');
    })
    .finally(() => {
        hideLoading();
    });
}

// Bulk approve applications
function bulkApprove() {
    if (selectedApplications.length === 0) {
        showAlert('Please select applications to approve', 'warning');
        return;
    }
    
    currentBulkAction = 'approve';
    setupBulkActionModal('Approve Applications', `approve ${selectedApplications.length} applications`, false, 'success');
}

// Bulk decline applications
function bulkDecline() {
    if (selectedApplications.length === 0) {
        showAlert('Please select applications to decline', 'warning');
        return;
    }
    
    currentBulkAction = 'decline';
    setupBulkActionModal('Decline Applications', `decline ${selectedApplications.length} applications`, true, 'warning');
}

// Bulk delete applications
function bulkDelete() {
    if (selectedApplications.length === 0) {
        showAlert('Please select applications to delete', 'warning');
        return;
    }
    
    currentBulkAction = 'delete';
    setupBulkActionModal('Delete Applications', `permanently delete ${selectedApplications.length} applications`, false, 'danger');
}

// Setup bulk action modal
function setupBulkActionModal(title, message, requireReason, buttonClass) {
    document.getElementById('bulkActionModalLabel').innerHTML = `<i class="ri-checkbox-multiple-line me-2"></i>${title}`;
    document.getElementById('bulk-action-message').textContent = `Are you sure you want to ${message}?`;
    document.getElementById('bulk-selected-count').textContent = selectedApplications.length;
    
    const reasonContainer = document.getElementById('bulk-reason-container');
    const reasonLabel = document.getElementById('bulk-reason-label');
    const bulkReason = document.getElementById('bulk-reason');
    
    if (requireReason) {
        reasonContainer.style.display = 'block';
        reasonLabel.textContent = 'Reason *';
        bulkReason.required = true;
        bulkReason.value = '';
    } else {
        reasonContainer.style.display = 'block';
        reasonLabel.textContent = 'Notes (Optional)';
        bulkReason.required = false;
        bulkReason.value = '';
    }
    
    const confirmBtn = document.getElementById('bulk-confirm-btn');
    confirmBtn.className = `btn btn-${buttonClass}`;
    confirmBtn.innerHTML = `<i class="ri-check-line me-1"></i>${title}`;
    
    const header = document.getElementById('bulk-modal-header');
    header.className = `modal-header bg-${buttonClass} ${buttonClass === 'warning' ? 'text-dark' : 'text-white'}`;
    
    new bootstrap.Modal(document.getElementById('bulkActionModal')).show();
}

// Confirm bulk action
function confirmBulkAction() {
    const reason = document.getElementById('bulk-reason').value.trim();
    
    if (currentBulkAction === 'decline' && !reason) {
        document.getElementById('bulk-reason').classList.add('is-invalid');
        return;
    }
    
    document.getElementById('bulk-reason').classList.remove('is-invalid');
    showLoading();
    
    let endpoint = '';
    let payload = { application_ids: selectedApplications };
    
    switch (currentBulkAction) {
        case 'approve':
            endpoint = '/admin/enrollments/applications/bulk-approve';
            if (reason) payload.reason = reason;
            break;
        case 'decline':
            endpoint = '/admin/enrollments/applications/bulk-decline';
            payload.reason = reason;
            break;
        case 'delete':
            endpoint = '/admin/enrollments/applications/bulk-delete';
            break;
    }
    
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('bulkActionModal')).hide();
            clearAllSelections();
            loadApplicationsData();
        } else {
            showAlert(data.message || 'Error performing bulk action', 'danger');
        }
    })
    .catch(error => {
        console.error('Error performing bulk action:', error);
        showAlert('Error performing bulk action', 'danger');
    })
    .finally(() => {
        hideLoading();
    });
}

// Export selected applications
function exportSelected() {
    if (selectedApplications.length === 0) {
        showAlert('Please select applications to export', 'warning');
        return;
    }
    
    const params = new URLSearchParams();
    selectedApplications.forEach(id => params.append('ids[]', id));
    
    window.location.href = `/admin/enrollments/export?${params.toString()}`;
}

// View documents for an application
function viewDocuments(applicationId) {
    currentApplicationId = applicationId;
    
    fetch(`/admin/enrollments/applications/${applicationId}/documents`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateDocumentsModal(data.documents);
                new bootstrap.Modal(document.getElementById('documentsModal')).show();
            } else {
                showAlert('Error loading documents', 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading documents:', error);
            showAlert('Error loading documents', 'danger');
        });
}

// Open bulk notice modal
function openBulkNoticeModal() {
    const selectedApplications = getSelectedApplications();
    if (selectedApplications.length === 0) {
        showAlert('Please select applications to send notices to', 'warning');
        return;
    }
    
    document.getElementById('bulk-notice-count').textContent = selectedApplications.length;
    new bootstrap.Modal(document.getElementById('bulkNoticeModal')).show();
}

// Open create notice modal
function openCreateNoticeModal() {
    document.getElementById('create-notice-form').reset();
    new bootstrap.Modal(document.getElementById('createNoticeModal')).show();
}

// Send notice to individual applicant
function sendNoticeToApplicant(applicationId) {
    currentApplicationId = applicationId;
    showAlert('Individual notice feature coming soon', 'info');
}

// Refresh data function
function refreshData() {
    // Get current active tab
    const activeTab = document.querySelector('.nav-link.active');
    const activeTabId = activeTab ? activeTab.getAttribute('data-bs-target') : '#applications';
    
    // Reload data based on active tab
    switch(activeTabId) {
        case '#applications':
            loadApplicationsData();
            break;
        case '#documents':
            loadDocumentsData();
            break;
        case '#appointments':
            loadAppointmentsData();
            break;
        case '#notices':
            loadNoticesData();
            break;
        default:
            loadApplicationsData();
    }
    
    showAlert('Data refreshed successfully', 'success');
}

// Export data function
function exportData() {
    // Get current active tab
    const activeTab = document.querySelector('.nav-link.active');
    const activeTabId = activeTab ? activeTab.getAttribute('data-bs-target') : '#applications';
    
    // Export based on active tab
    switch(activeTabId) {
        case '#applications':
            window.location.href = '/admin/enrollments/export';
            break;
        case '#documents':
            showAlert('Document export feature coming soon', 'info');
            break;
        case '#appointments':
            showAlert('Appointment export feature coming soon', 'info');
            break;
        case '#notices':
            showAlert('Notice export feature coming soon', 'info');
            break;
        default:
            window.location.href = '/admin/enrollments/export';
    }
}


// Duplicate viewDocument function removed - using the one at line 275

// Send bulk notice
function sendBulkNotice() {
    const selectedApplications = getSelectedApplications();
    const title = document.getElementById('bulk-notice-title').value;
    const message = document.getElementById('bulk-notice-message').value;
    const priority = document.getElementById('bulk-notice-priority').value;
    
    if (!title || !message) {
        showAlert('Please fill in all required fields', 'warning');
        return;
    }
    
    fetch('/admin/enrollments/notices/bulk', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            application_ids: selectedApplications,
            title: title,
            message: message,
            priority: priority
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Bulk notice sent successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('bulkNoticeModal')).hide();
            clearAllSelections();
        } else {
            showAlert(data.message || 'Error sending bulk notice', 'danger');
        }
    })
    .catch(error => {
        console.error('Error sending bulk notice:', error);
        showAlert('Error sending bulk notice', 'danger');
    });
}

// Handle create notice form submission
document.addEventListener('DOMContentLoaded', function() {
    const createNoticeForm = document.getElementById('create-notice-form');
    if (createNoticeForm) {
        createNoticeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('/admin/enrollments/notices', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Notice created successfully', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('createNoticeModal')).hide();
                    loadNoticesData();
                } else {
                    showAlert(data.message || 'Error creating notice', 'danger');
                }
            })
            .catch(error => {
                console.error('Error creating notice:', error);
                showAlert('Error creating notice', 'danger');
            });
        });
    }
    
    // Handle notice type change to show/hide target filters
    const noticeType = document.getElementById('notice-type');
    const targetFilters = document.getElementById('target-filters');
    if (noticeType && targetFilters) {
        noticeType.addEventListener('change', function() {
            if (this.value === 'global') {
                targetFilters.style.display = 'block';
            } else {
                targetFilters.style.display = 'none';
            }
        });
    }
});

// Schedule appointment for an application
function scheduleAppointment(applicationId) {
    currentApplicationId = applicationId;
    // This would open a modal to schedule an appointment
    showAlert('Appointment scheduling feature coming soon', 'info');
}

// Document approval functions
function approveDocument(enrolleeId, documentIndex) {
    updateDocumentStatus(enrolleeId, documentIndex, 'approved');
}

function rejectDocument(enrolleeId, documentIndex) {
    updateDocumentStatus(enrolleeId, documentIndex, 'rejected');
}

function updateDocumentStatus(enrolleeId, documentIndex, status) {
    fetch(`/admin/enrollments/documents/${enrolleeId}/${documentIndex}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Document ${status} successfully`, 'success');
            loadDocumentsData();
        } else {
            showAlert(data.message || `Error ${status} document`, 'danger');
        }
    })
    .catch(error => {
        console.error('Error updating document status:', error);
        showAlert('Error updating document status', 'danger');
    });
}

// Make all functions globally available for onclick handlers
window.viewApplication = viewApplication;
window.viewDocuments = viewDocuments;
window.approveApplication = approveApplication;
window.declineApplication = declineApplication;
window.deleteApplication = deleteApplication;
window.confirmApproveApplication = confirmApproveApplication;
window.confirmDeclineApplication = confirmDeclineApplication;
window.confirmDeleteApplication = confirmDeleteApplication;
window.bulkApprove = bulkApprove;
window.bulkDecline = bulkDecline;
window.bulkDelete = bulkDelete;
window.confirmBulkAction = confirmBulkAction;
window.exportSelected = exportSelected;
window.clearAllSelections = clearAllSelections;
window.updateSelectionCount = updateSelectionCount;
window.sendNoticeToApplicant = sendNoticeToApplicant;
window.openBulkNoticeModal = openBulkNoticeModal;
window.openCreateNoticeModal = openCreateNoticeModal;
window.scheduleAppointment = scheduleAppointment;
window.approveDocument = approveDocument;
window.rejectDocument = rejectDocument;
window.viewDocumentFile = viewDocumentFile;
window.sendBulkNotice = sendBulkNotice;
window.viewNotice = viewNotice;
window.deleteNotice = deleteNotice;
window.clearFilters = clearFilters;
window.viewDocument = viewDocument;
window.refreshData = refreshData;
window.exportData = exportData;
window.sendNoticeToApplicant = sendNoticeToApplicant;
window.getSelectedApplications = getSelectedApplications;
window.exportEnrollments = exportEnrollments;
