// Admin Enrollment Management JavaScript - Bootstrap 5 Compatible
console.log('Admin Enrollment Management JavaScript: File loaded successfully');

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Enrollment Management: DOM loaded, initializing...');
    
    // Check if we're on the enrollments page
    if (window.location.pathname.includes('/admin/enrollments')) {
        initializeSystem();
        loadApplicationsData();
        setupEventListeners();
    }
});

// Global variables
let currentApplicationId = null;
let currentDocumentIndex = null;
let currentAppointmentId = null;
let selectedApplications = [];
let currentBulkAction = null;

// Initialize the system
function initializeSystem() {
    console.log('Initializing enrollment management system...');
    
    // Setup Bootstrap 5 tab navigation
    const tabTriggerList = [].slice.call(document.querySelectorAll('button[data-bs-toggle="tab"]'));
    tabTriggerList.map(function (tabTriggerEl) {
        const tabTrigger = new bootstrap.Tab(tabTriggerEl);
        
        tabTriggerEl.addEventListener('shown.bs.tab', function (event) {
            const targetTab = event.target.getAttribute('data-bs-target');
            console.log('Tab switched to:', targetTab);
            
            // Load appropriate data based on active tab
            switch(targetTab) {
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
            }
        });
        
        return tabTrigger;
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
    
    // Setup create notice form
    setupCreateNoticeForm();
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

// Get status badge class for styling
function getStatusBadgeClass(status) {
    switch(status?.toLowerCase()) {
        case 'pending':
            return 'bg-warning text-dark';
        case 'approved':
            return 'bg-success';
        case 'enrolled':
            return 'bg-primary';
        case 'rejected':
            return 'bg-danger';
        case 'declined':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Format date helper function
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        return 'Invalid Date';
    }
}

// Duplicate viewApplication function removed - using the one at line 1223

// Populate application modal
function populateApplicationModal(app) {
    // Safely populate fields with null checks
    const safeSet = (id, value, fallback = 'N/A') => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value || fallback;
        }
    };
    
    // Format date helper
    const formatDate = (dateString) => {
        if (!dateString) return 'Not provided';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        } catch (error) {
            return 'Invalid date';
        }
    };
    
    // Personal Information
    safeSet('app-full-name', app.full_name || app.name);
    safeSet('app-dob', app.date_of_birth ? formatDate(app.date_of_birth) : 'Not provided');
    safeSet('app-gender', app.gender ? app.gender.charAt(0).toUpperCase() + app.gender.slice(1) : 'Not provided');
    safeSet('app-email', app.email);
    safeSet('app-contact', app.contact_number);
    safeSet('app-address', app.address);
    safeSet('app-grade', app.grade_level_applied || app.grade_level);
    safeSet('app-type', app.student_type ? app.student_type.charAt(0).toUpperCase() + app.student_type.slice(1) : 'Not provided');
    
    // Guardian Information
    safeSet('app-father', app.father_name);
    safeSet('app-mother', app.mother_name);
    safeSet('app-guardian', app.guardian_name);
    safeSet('app-guardian-contact', app.guardian_contact);
    
    // Additional fields if they exist in the modal
    safeSet('app-application-id', app.application_id);
    safeSet('app-applied-date', app.created_at ? formatDate(app.created_at) : (app.applied_date || 'Not available'));
    safeSet('app-nationality', app.nationality);
    safeSet('app-religion', app.religion);
    safeSet('app-lrn', app.lrn);
    safeSet('app-strand', app.strand_applied);
    safeSet('app-track', app.track_applied);
    safeSet('app-last-school', app.last_school_name);
    
    // Set ID photo
    const idPhoto = document.getElementById('app-id-photo');
    if (idPhoto) {
        if (app.id_photo_data_url) {
            idPhoto.src = app.id_photo_data_url;
            idPhoto.alt = `ID Photo of ${app.full_name || app.name}`;
        } else {
            idPhoto.src = '/images/default-avatar.png';
            idPhoto.alt = 'Default Avatar';
        }
    }
    
    // Set current status
    const statusBadge = document.getElementById('app-current-status');
    if (statusBadge) {
        const status = app.enrollment_status || app.status || 'pending';
        statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        statusBadge.className = `badge bg-${getStatusColor(status.toLowerCase())} fs-6`;
    }
    
    // Set status select if it exists
    const statusSelect = document.getElementById('status-select');
    if (statusSelect) {
        const status = app.enrollment_status || app.status || 'pending';
        statusSelect.value = status.toLowerCase();
    }
    
    // Clear status reason
    const statusReason = document.getElementById('status-reason');
    if (statusReason) {
        statusReason.value = app.status_reason || '';
    }
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
            // Use Bootstrap 5 compatible modal initialization
            const modal = document.getElementById('documentReviewModal');
            if (modal) {
                const bootstrapModal = new bootstrap.Modal(modal);
                bootstrapModal.show();
            }
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

// Duplicate formatDate function removed - using the one at line 244

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

// Duplicate clearFilters function removed - using the one at the end of file

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
    if (confirm('Are you sure you want to delete this notice? This action cannot be undone.')) {
        fetch(`/admin/enrollments/notices/${noticeId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Notice deleted successfully', 'success');
                // Reload the page to refresh the notices list
                window.location.reload();
            } else {
                showAlert(data.message || 'Error deleting notice', 'danger');
            }
        })
        .catch(error => {
            console.error('Error deleting notice:', error);
            showAlert('Error deleting notice', 'danger');
        });
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
                    <button class="btn btn-outline-info" onclick="changeAppointment('${appointment.application_id}')" title="Change Appointment">
                        <i class="ri-calendar-edit-line"></i>
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

function populateDocumentModal(data) {
    console.log('Populating document modal...', data);
    
    if (!data.success) {
        showAlert('Error loading document details', 'danger');
        return;
    }
    
    const documentData = data.document;
    
    // Populate document information
    document.getElementById('doc-app-id').textContent = data.application_id || 'N/A';
    document.getElementById('doc-student-name').textContent = data.student_name || 'N/A';
    document.getElementById('doc-type').textContent = documentData.type || 'N/A';
    document.getElementById('doc-upload-date').textContent = formatDate(documentData.uploaded_at) || 'N/A';
    document.getElementById('doc-file-size').textContent = formatFileSize(documentData.size) || 'N/A';
    
    // Set current status badge
    const statusElement = document.getElementById('doc-current-status');
    const status = documentData.status || 'pending';
    statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
    statusElement.className = `badge fs-6 ${getStatusBadgeClass(status)}`;
    
    // Set status select
    document.getElementById('doc-status-select').value = status;
    
    // Clear previous review notes
    document.getElementById('doc-review-notes').value = documentData.review_notes || '';
    
    // Load document viewer
    const documentViewer = document.getElementById('document-viewer');
    const docTitle = document.getElementById('doc-title');
    
    docTitle.textContent = `${documentData.type} - ${documentData.filename || 'Document'}`;
    
    if (data.document_url) {
        const fileExtension = documentData.filename ? documentData.filename.split('.').pop().toLowerCase() : '';
        
        if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExtension)) {
            // Display image
            documentViewer.innerHTML = `
                <img src="${data.document_url}" class="img-fluid" alt="Document Preview" style="max-height: 500px;">
            `;
        } else if (fileExtension === 'pdf') {
            // Display PDF
            documentViewer.innerHTML = `
                <iframe src="${data.document_url}" width="100%" height="500px" frameborder="0">
                    <p>Your browser does not support PDFs. <a href="${data.document_url}" target="_blank">Download the PDF</a>.</p>
                </iframe>
            `;
        } else {
            // Display download link for other file types
            documentViewer.innerHTML = `
                <div class="text-center py-5">
                    <i class="ri-file-line fs-1 text-muted mb-3"></i>
                    <p class="text-muted">Preview not available for this file type.</p>
                    <a href="${data.document_url}" target="_blank" class="btn btn-primary">
                        <i class="ri-download-line me-1"></i>Download Document
                    </a>
                </div>
            `;
        }
    } else {
        documentViewer.innerHTML = `
            <div class="text-center py-5">
                <i class="ri-file-line fs-1 text-muted mb-3"></i>
                <p class="text-muted">Document file not found.</p>
            </div>
        `;
    }
}

// Duplicate formatFileSize function removed - using the one at line 419

// Document review action functions
function approveDocument() {
    if (!currentApplicationId || currentDocumentIndex === null) {
        showAlert('No document selected for approval', 'warning');
        return;
    }
    
    updateDocumentStatusAction('approved');
}

function rejectDocument() {
    if (!currentApplicationId || currentDocumentIndex === null) {
        showAlert('No document selected for rejection', 'warning');
        return;
    }
    
    updateDocumentStatusAction('rejected');
}

function updateDocumentStatus() {
    if (!currentApplicationId || currentDocumentIndex === null) {
        showAlert('No document selected for status update', 'warning');
        return;
    }
    
    const newStatus = document.getElementById('doc-status-select').value;
    const reviewNotes = document.getElementById('doc-review-notes').value;
    
    updateDocumentStatusAction(newStatus, reviewNotes);
}

function updateDocumentStatusAction(status, notes = '') {
    const reviewNotes = notes || document.getElementById('doc-review-notes').value;
    
    fetch(`/admin/enrollments/documents/${currentApplicationId}/${currentDocumentIndex}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: status,
            review_notes: reviewNotes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Document ${status} successfully`, 'success');
            
            // Update the modal display
            const statusElement = document.getElementById('doc-current-status');
            statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            statusElement.className = `badge fs-6 ${getStatusBadgeClass(status)}`;
            
            // Refresh documents data if we're on documents tab
            const activeTab = document.querySelector('.nav-link.active');
            if (activeTab && activeTab.getAttribute('data-bs-target') === '#documents') {
                loadDocumentsData();
            }
        } else {
            showAlert('Error updating document status: ' + (data.message || 'Unknown error'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error updating document status:', error);
        showAlert('Error updating document status', 'danger');
    });
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
function approveApplication(id) {
    currentApplicationId = id;
    
    // Find the application data from the current page
    const row = document.querySelector(`input[value="${id}"]`).closest('tr');
    if (!row) {
        showAlert('Application not found', 'danger');
        return;
    }
    
    // Extract data from the table row
    const cells = row.querySelectorAll('td');
    const applicationId = cells[1].textContent.trim();
    const studentName = cells[2].textContent.trim();
    
    // Check if modal elements exist before setting them
    const appIdElement = document.getElementById('approve-app-id');
    const studentNameElement = document.getElementById('approve-student-name');
    const reasonElement = document.getElementById('approve-reason');
    
    if (appIdElement) appIdElement.textContent = applicationId;
    if (studentNameElement) studentNameElement.textContent = studentName;
    if (reasonElement) reasonElement.value = '';
    
    // Show confirmation dialog instead of modal if modal doesn't exist
    const modal = document.getElementById('approveApplicationModal');
    if (modal) {
        new bootstrap.Modal(modal).show();
    } else {
        if (confirm(`Are you sure you want to approve application ${applicationId} for ${studentName}?`)) {
            confirmApproveApplication();
        }
    }
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
function declineApplication(id) {
    currentApplicationId = id;
    
    // Find the application data from the current page
    const row = document.querySelector(`input[value="${id}"]`).closest('tr');
    if (!row) {
        showAlert('Application not found', 'danger');
        return;
    }
    
    // Extract data from the table row
    const cells = row.querySelectorAll('td');
    const applicationId = cells[1].textContent.trim();
    const studentName = cells[2].textContent.trim();
    
    // Check if modal elements exist before setting them
    const appIdElement = document.getElementById('decline-app-id');
    const studentNameElement = document.getElementById('decline-student-name');
    const reasonElement = document.getElementById('decline-reason');
    
    if (appIdElement) appIdElement.textContent = applicationId;
    if (studentNameElement) studentNameElement.textContent = studentName;
    if (reasonElement) reasonElement.value = '';
    
    // Show confirmation dialog instead of modal if modal doesn't exist
    const modal = document.getElementById('declineApplicationModal');
    if (modal) {
        new bootstrap.Modal(modal).show();
    } else {
        const reason = prompt(`Please provide a reason for declining application ${applicationId} for ${studentName}:`);
        if (reason !== null && reason.trim() !== '') {
            // Simulate the decline with reason
            updateApplicationStatus('declined', reason);
        }
    }
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
function deleteApplication(id) {
    currentApplicationId = id;
    
    // Find the application data from the current page
    const row = document.querySelector(`input[value="${id}"]`).closest('tr');
    if (!row) {
        showAlert('Application not found', 'danger');
        return;
    }
    
    // Extract data from the table row
    const cells = row.querySelectorAll('td');
    const applicationId = cells[1].textContent.trim();
    const studentName = cells[2].textContent.trim();
    
    // Check if modal elements exist before setting them
    const appIdElement = document.getElementById('delete-app-id');
    const studentNameElement = document.getElementById('delete-student-name');
    
    if (appIdElement) appIdElement.textContent = applicationId;
    if (studentNameElement) studentNameElement.textContent = studentName;
    
    // Show confirmation dialog instead of modal if modal doesn't exist
    const modal = document.getElementById('deleteApplicationModal');
    if (modal) {
        new bootstrap.Modal(modal).show();
    } else {
        if (confirm(`Are you sure you want to delete application ${applicationId} for ${studentName}? This action cannot be undone.`)) {
            confirmDeleteApplication();
        }
    }
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

// View application details
function viewApplication(applicationId) {
    currentApplicationId = applicationId;
    
    // Show loading state
    showLoading();
    
    // Fetch complete application data from server
    fetch(`/admin/enrollments/applications/${applicationId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.application) {
                populateApplicationModal(data.application);
                const modal = new bootstrap.Modal(document.getElementById('applicationDetailsModal'));
                modal.show();
            } else {
                showAlert(data.message || 'Failed to load application details', 'danger');
            }
        })
        .catch(error => {
            console.error('Error fetching application:', error);
            
            // Fallback: Extract basic data from table row if server fetch fails
            const row = document.querySelector(`input[value="${applicationId}"]`).closest('tr');
            if (row) {
                const cells = row.querySelectorAll('td');
                const applicationData = {
                    id: applicationId,
                    application_id: cells[1].textContent.trim(),
                    full_name: cells[2].textContent.trim(),
                    grade_level_applied: cells[3].textContent.trim(),
                    email: cells[4].textContent.trim(),
                    enrollment_status: cells[5].querySelector('.badge').textContent.trim(),
                    created_at: cells[6].textContent.trim()
                };
                
                populateApplicationModal(applicationData);
                const modal = new bootstrap.Modal(document.getElementById('applicationDetailsModal'));
                modal.show();
                
                showAlert('Showing limited data from table view. Full details unavailable.', 'warning');
            } else {
                showAlert('Application not found', 'danger');
            }
        })
        .finally(() => {
            hideLoading();
        });
}

// View documents for an application
function viewDocuments(applicationId) {
    currentApplicationId = applicationId;
    
    // Get the application row to extract application ID
    const row = document.querySelector(`input[value="${applicationId}"]`).closest('tr');
    if (!row) {
        showAlert('Application not found', 'danger');
        return;
    }
    
    const cells = row.querySelectorAll('td');
    const appId = cells[1].textContent.trim(); // Application ID from table
    
    // Switch to documents tab and show documents for this application
    const documentsTab = document.querySelector('button[data-bs-target="#documents"]');
    if (documentsTab) {
        const tab = new bootstrap.Tab(documentsTab);
        tab.show();
        
        // Filter documents for this specific application
        setTimeout(() => {
            const documentsTable = document.querySelector('#documents-table tbody');
            if (documentsTable) {
                const rows = documentsTable.querySelectorAll('tr');
                let foundDocuments = false;
                
                rows.forEach(row => {
                    const appIdCell = row.querySelector('td:first-child');
                    if (appIdCell) {
                        const rowAppId = appIdCell.textContent.trim();
                        
                        if (rowAppId === appId) {
                            row.style.backgroundColor = '#fff3cd';
                            row.style.border = '2px solid #ffc107';
                            if (!foundDocuments) {
                                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                foundDocuments = true;
                            }
                        } else {
                            row.style.backgroundColor = '';
                            row.style.border = '';
                        }
                    }
                });
                
                if (foundDocuments) {
                    showAlert(`Showing documents for Application ID: ${appId}`, 'success');
                } else {
                    showAlert(`No documents found for Application ID: ${appId}`, 'warning');
                }
            }
        }, 300);
    } else {
        showAlert('Documents tab not found', 'danger');
    }
}

// Open bulk notice modal
function openBulkNoticeModal() {
    const selectedApplications = getSelectedApplications();
    if (selectedApplications.length === 0) {
        showAlert('Please select applications to send notices to', 'warning');
        return;
    }
    
    document.getElementById('bulk-notice-count').textContent = selectedApplications.length;
    // Use Bootstrap 5 compatible modal initialization
    const modal = document.getElementById('bulkNoticeModal');
    if (modal) {
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
}

// Open create notice modal
function openCreateNoticeModal() {
    document.getElementById('create-notice-form').reset();
    // Use Bootstrap 5 compatible modal initialization
    const modal = document.getElementById('createNoticeModal');
    if (modal) {
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
}

// Send notice to individual applicant
function sendNoticeToApplicant(applicationId) {
    currentApplicationId = applicationId;
    showAlert('Individual notice feature coming soon', 'info');
}

// Duplicate functions removed - using the ones at the end of file


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
function setupCreateNoticeForm() {
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
                    document.getElementById('create-notice-form').reset();
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createNoticeModal'));
                    if (modal) modal.hide();
                } else {
                    showAlert('Error creating notice: ' + (data.message || 'Unknown error'), 'danger');
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
}

// Change appointment for an application
function changeAppointment(applicationId) {
    currentApplicationId = applicationId;
    
    // Fetch current appointment details
    fetch(`/admin/enrollments/applications/${applicationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const application = data.application;
                
                // Show appointment change modal with current details
                document.getElementById('change-app-id').textContent = application.application_id;
                document.getElementById('change-student-name').textContent = application.full_name || 'N/A';
                document.getElementById('current-schedule').textContent = application.preferred_schedule || 'Not set';
                document.getElementById('current-enrollment-date').textContent = formatDate(application.enrollment_date) || 'Not set';
                
                // Clear form fields
                document.getElementById('new-preferred-schedule').value = '';
                document.getElementById('new-enrollment-date').value = '';
                document.getElementById('appointment-notes').value = '';
                
                // Show modal
                const modal = document.getElementById('changeAppointmentModal');
                if (modal) {
                    const bootstrapModal = new bootstrap.Modal(modal);
                    bootstrapModal.show();
                }
            } else {
                showAlert('Error loading application details', 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading application:', error);
            showAlert('Error loading application details', 'danger');
        });
}

// Confirm appointment change
function confirmAppointmentChange() {
    if (!currentApplicationId) {
        showAlert('No application selected', 'warning');
        return;
    }
    
    const newSchedule = document.getElementById('new-preferred-schedule').value;
    const newEnrollmentDate = document.getElementById('new-enrollment-date').value;
    const notes = document.getElementById('appointment-notes').value;
    
    if (!newSchedule && !newEnrollmentDate) {
        showAlert('Please set at least one new appointment detail', 'warning');
        return;
    }
    
    const updateData = {};
    if (newSchedule) updateData.preferred_schedule = newSchedule;
    if (newEnrollmentDate) updateData.enrollment_date = newEnrollmentDate;
    if (notes) updateData.admin_notes = notes;
    
    fetch(`/admin/enrollments/applications/${currentApplicationId}/appointment`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(updateData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Appointment updated successfully', 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('changeAppointmentModal'));
            if (modal) modal.hide();
            
            // Refresh appointments data if we're on appointments tab
            const activeTab = document.querySelector('.nav-link.active');
            if (activeTab && activeTab.getAttribute('data-bs-target') === '#appointments') {
                loadAppointmentsData();
            }
        } else {
            showAlert('Error updating appointment: ' + (data.message || 'Unknown error'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error updating appointment:', error);
        showAlert('Error updating appointment', 'danger');
    });
}

// Duplicate document approval functions removed - using the ones earlier in the file

// Duplicate updateDocumentStatus function removed - using the one earlier in the file

// Window assignments moved to end of file after all functions are defined

// Essential functions for onclick handlers
function refreshData() {
    console.log('Refreshing data...');
    const activeTab = document.querySelector('.nav-link.active, button[data-bs-toggle="tab"].active');
    const activeTabId = activeTab ? activeTab.getAttribute('data-bs-target') : '#applications';
    
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

function clearFilters() {
    console.log('Clearing filters...');
    const statusFilter = document.getElementById('status-filter');
    const gradeFilter = document.getElementById('grade-filter');
    const searchInput = document.getElementById('search-input');
    
    if (statusFilter) statusFilter.value = '';
    if (gradeFilter) gradeFilter.value = '';
    if (searchInput) searchInput.value = '';
    
    loadApplicationsData();
    showAlert('Filters cleared', 'info');
}

function exportData() {
    console.log('Exporting data...');
    const activeTab = document.querySelector('.nav-link.active, button[data-bs-toggle="tab"].active');
    const activeTabId = activeTab ? activeTab.getAttribute('data-bs-target') : '#applications';
    
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

// Functions will be exposed at the end of file

// Make all functions globally available for onclick handlers - FINAL ASSIGNMENTS
window.viewApplication = viewApplication;
window.viewDocuments = viewDocuments;
window.approveApplication = approveApplication;
window.declineApplication = declineApplication;
window.deleteApplication = deleteApplication;
window.confirmApproveApplication = confirmApproveApplication;
window.confirmDeclineApplication = confirmDeclineApplication;
window.confirmDeleteApplication = confirmDeleteApplication;
window.updateApplicationStatus = updateApplicationStatus;
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
window.changeAppointment = changeAppointment;
window.confirmAppointmentChange = confirmAppointmentChange;
window.approveDocument = approveDocument;
window.rejectDocument = rejectDocument;
window.updateDocumentStatus = updateDocumentStatus;
window.viewDocumentFile = viewDocumentFile;
window.sendBulkNotice = sendBulkNotice;
window.viewNotice = viewNotice;
window.deleteNotice = deleteNotice;
window.viewDocument = viewDocument;
window.getSelectedApplications = getSelectedApplications;
window.exportEnrollments = exportEnrollments;

// Essential functions - ensure they override any previous assignments
window.refreshData = refreshData;
window.clearFilters = clearFilters;
window.exportData = exportData;

// Debug function availability
console.log('All functions exposed to window:', {
    refreshData: typeof window.refreshData,
    clearFilters: typeof window.clearFilters,
    exportData: typeof window.exportData,
    openCreateNoticeModal: typeof window.openCreateNoticeModal,
    openBulkNoticeModal: typeof window.openBulkNoticeModal,
    approveDocument: typeof window.approveDocument,
    viewApplication: typeof window.viewApplication
});
