// Registrar Applications Management JavaScript - Comprehensive Version
console.log('Registrar Applications Management JavaScript: File loaded successfully');

// Global variables
let currentApplicationId = null;
let currentDocumentIndex = null;
let selectedApplications = [];
let currentBulkAction = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Registrar Applications Management: DOM loaded, initializing...');
    
    // Check if we're on the applications page
    if (window.location.pathname.includes('/registrar/applications')) {
        initializeSystem();
        setupEventListeners();
        setupCSRFToken();
    }
    
    // Initialize filters
    setupFilters();
    
    // Initialize modals
    initializeModals();
    
    // Initialize tab event listeners
    setupTabEventListeners();
});

// Setup CSRF token for all AJAX requests
function setupCSRFToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        window.csrfToken = token.getAttribute('content');
        // Set default headers for fetch requests
        window.fetchDefaults = {
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };
    }
}

// Initialize the system
function initializeSystem() {
    console.log('Initializing registrar applications management system...');
    
    // Setup Bootstrap 5 tab navigation
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            const targetId = e.target.getAttribute('data-bs-target');
            console.log('Tab switched to:', targetId);
            
            // Load data based on active tab
            switch(targetId) {
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
                case '#data-change-requests':
                    // Data change requests are handled by their own dedicated script
                    console.log('Data change requests tab activated - handled by dedicated script');
                    break;
                default:
                    console.log('Unknown tab activated:', targetId);
                    break;
            }
        });
    });

    // Notice recipients dropdown event listener
    const noticeRecipientsSelect = document.getElementById('notice-recipients');
    if (noticeRecipientsSelect) {
        noticeRecipientsSelect.addEventListener('change', function() {
            const specificDiv = document.getElementById('specific-applicant-div');
            if (specificDiv) {
                if (this.value === 'specific') {
                    specificDiv.style.display = 'block';
                    console.log('Showing specific applicant selection');
                } else {
                    specificDiv.style.display = 'none';
                    console.log('Hiding specific applicant selection, selected:', this.value);
                }
            }
        });
    }
    
    // Initialize modals
    initializeModals();
}

// Initialize Bootstrap modals
function initializeModals() {
    // Initialize appointment modal
    const appointmentModal = document.getElementById('appointmentReviewModal');
    if (appointmentModal) {
        new bootstrap.Modal(appointmentModal);
    }
    
    // Initialize notice modals
    const createNoticeModal = document.getElementById('createNoticeModal');
    if (createNoticeModal) {
        new bootstrap.Modal(createNoticeModal);
    }
    
    const bulkNoticeModal = document.getElementById('bulkNoticeModal');
    if (bulkNoticeModal) {
        new bootstrap.Modal(bulkNoticeModal);
    }
    
    const viewNoticeModal = document.getElementById('viewNoticeModal');
    if (viewNoticeModal) {
        new bootstrap.Modal(viewNoticeModal);
    }
    
    // Initialize view application modal
    const viewModal = document.getElementById('viewApplicationModal');
    if (viewModal) {
        new bootstrap.Modal(viewModal);
    }
    
    // Initialize decline reason modal
    const declineModal = document.getElementById('declineReasonModal');
    if (declineModal) {
        new bootstrap.Modal(declineModal);
    }

    // Setup checkbox selection
    setupCheckboxSelection();
    
    // Setup document filters
    setupDocumentFilters();
    
    // Load initial data
    loadApplicationsData();
    
    // Handle tab switching based on URL parameters
    handleTabSwitching();
}

// Setup event listeners
function setupEventListeners() {
    console.log('Setting up event listeners...');
    
    // Setup checkbox selection for bulk actions
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.application-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionsPanel();
        });
    }
}

// Setup checkbox selection functionality
function setupCheckboxSelection() {
    // Individual checkbox change handler
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('application-checkbox')) {
            updateBulkActionsPanel();
            updateSelectAllCheckbox();
        }
    });
}

// Update bulk actions panel visibility
function updateBulkActionsPanel() {
    const checkboxes = document.querySelectorAll('.application-checkbox:checked');
    const bulkPanel = document.getElementById('bulk-actions-panel');
    const selectedCount = document.getElementById('selectedCount');
    
    selectedApplications = Array.from(checkboxes).map(cb => cb.value);
    
    if (bulkPanel) {
        bulkPanel.style.display = selectedApplications.length > 0 ? 'block' : 'none';
    }
    if (selectedCount) {
        selectedCount.textContent = selectedApplications.length;
    }
}

// Update select all checkbox state
function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.application-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.application-checkbox:checked');
    
    if (selectAllCheckbox) {
        if (checkboxes.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedCheckboxes.length === checkboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else if (checkedCheckboxes.length > 0) {
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        }
    }
}

// Load applications data
function loadApplicationsData() {
    console.log('Loading applications data...');
    // This would typically make an AJAX call to refresh the data
    // For now, we'll just update the UI state
    updateBulkActionsPanel();
}

// Load documents data
function loadDocumentsData() {
    console.log('Loading documents data...');
    const loadingDiv = document.getElementById('documents-loading');
    const contentDiv = document.getElementById('documents-content');
    const emptyDiv = document.getElementById('documents-empty');
    const tableBody = document.getElementById('documents-table-body');
    
    // Show loading state
    if (loadingDiv) loadingDiv.style.display = 'block';
    if (contentDiv) contentDiv.style.display = 'none';
    if (emptyDiv) emptyDiv.style.display = 'none';
    
    // Get filter values
    const statusFilter = document.getElementById('document-status-filter')?.value || '';
    const typeFilter = document.getElementById('document-type-filter')?.value || '';
    
    const params = new URLSearchParams();
    if (statusFilter) params.append('status', statusFilter);
    if (typeFilter) params.append('type', typeFilter);
    
    fetch(`/registrar/documents?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': window.csrfToken || ''
        }
    })
        .then(response => {
            console.log('Documents response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Documents data received:', data);
            if (loadingDiv) loadingDiv.style.display = 'none';
            
            if (data.success && data.documents && data.documents.length > 0) {
                // Populate documents table
                if (tableBody) {
                    tableBody.innerHTML = '';
                    data.documents.forEach(docData => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${docData.application_id || 'N/A'}</td>
                            <td>${docData.applicant_name || 'N/A'}</td>
                            <td>
                                <i class="${getFileIcon(docData.type)} me-1"></i>
                                ${docData.type || 'Unknown'}
                            </td>
                            <td>${docData.filename || 'N/A'}</td>
                            <td>${formatDate(docData.uploaded_at)}</td>
                            <td>${getDocumentStatusBadge(docData.status)}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" onclick="viewDocumentInTab('${docData.application_id}', ${docData.index})" title="View Document">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    ${docData.status === 'pending' ? `
                                        <button class="btn btn-outline-success" onclick="approveDocumentInTab('${docData.application_id}', ${docData.index})" title="Approve">
                                            <i class="ri-check-line"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="rejectDocumentInTab('${docData.application_id}', ${docData.index})" title="Reject">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                }
                if (contentDiv) contentDiv.style.display = 'block';
            } else {
                console.log('No documents found or empty response');
                if (emptyDiv) emptyDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading documents:', error);
            if (loadingDiv) loadingDiv.style.display = 'none';
            if (emptyDiv) emptyDiv.style.display = 'block';
            showAlert('Failed to load documents: ' + error.message, 'error');
        });
}

// Setup filter event listeners
function setupFilters() {
    const statusFilter = document.getElementById('status-filter');
    const gradeFilter = document.getElementById('grade-filter');
    const searchInput = document.getElementById('search-input');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', applyFilters);
    }
    
    if (gradeFilter) {
        gradeFilter.addEventListener('change', applyFilters);
    }
    
    if (searchInput) {
        searchInput.addEventListener('keyup', debounce(applyFilters, 500));
    }
}

// Setup document filter event listeners
function setupDocumentFilters() {
    const documentStatusFilter = document.getElementById('document-status-filter');
    const documentTypeFilter = document.getElementById('document-type-filter');
    
    if (documentStatusFilter) {
        documentStatusFilter.addEventListener('change', loadDocumentsData);
    }
    
    if (documentTypeFilter) {
        documentTypeFilter.addEventListener('change', loadDocumentsData);
    }
}


// Apply filters to applications table
function applyFilters() {
    const status = document.getElementById('status-filter')?.value || '';
    const gradeLevel = document.getElementById('grade-filter')?.value || '';
    const search = document.getElementById('search-input')?.value || '';
    
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (gradeLevel) params.append('grade_level', gradeLevel);
    if (search) params.append('search', search);
    
    // Add loading state
    showLoading();
    
    fetch(`/registrar/applications?${params.toString()}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateApplicationsTable(data.applications);
            updatePagination(data.pagination);
        } else {
            showAlert('Failed to load applications', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error loading applications', 'danger');
    })
    .finally(() => {
        hideLoading();
    });
}

// Update applications table with new data
function updateApplicationsTable(applications) {
    const tbody = document.getElementById('applications-tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    applications.forEach(application => {
        const row = createApplicationRow(application);
        tbody.appendChild(row);
    });
}

// Create table row for application
function createApplicationRow(application) {
    const row = document.createElement('tr');
    row.setAttribute('data-id', application.id);
    
    const statusBadge = getStatusBadge(application.enrollment_status);
    const actionButtons = createActionButtons(application);
    
    row.innerHTML = `
        <td>${application.application_id}</td>
        <td>${application.first_name} ${application.last_name}</td>
        <td>${application.grade_level_applied}</td>
        <td>${application.email}</td>
        <td>${statusBadge}</td>
        <td>${formatDate(application.created_at)}</td>
        <td>${actionButtons}</td>
    `;
    
    return row;
}

// Get status badge HTML
function getStatusBadge(status) {
    const badgeClass = status === 'pending' ? 'warning' : (status === 'approved' ? 'success' : 'danger');
    return `<span class="badge bg-${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
}

// Create action buttons for application
function createActionButtons(application) {
    let buttons = `
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" onclick="viewApplication(${application.id})" title="View Details">
                <i class="ri-eye-line"></i>
            </button>
    `;
    
    if (application.enrollment_status === 'pending') {
        buttons += `
            <button class="btn btn-outline-success" onclick="approveApplication(${application.id})" title="Approve">
                <i class="ri-check-line"></i>
            </button>
            <button class="btn btn-outline-danger" onclick="declineApplication(${application.id})" title="Decline">
                <i class="ri-close-line"></i>
            </button>
        `;
    }
    
    buttons += '</div>';
    return buttons;
}

// View application details
function viewApplication(applicationId) {
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
            populateApplicationModal(data.application);
            const modal = new bootstrap.Modal(document.getElementById('applicationDetailsModal'));
            modal.show();
        } else {
            showAlert('Failed to load application details', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error loading application details', 'danger');
    })
    .finally(() => {
        hideLoading();
    });
}

// Populate application modal with comprehensive data
function populateApplicationModal(application) {
    // Helper function to safely set element content
    function safeSet(id, value, fallback = 'Not provided') {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value || fallback;
        }
    }

    // Helper function to safely set HTML content
    function safeSetHTML(id, value, fallback = 'Not provided') {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = value || fallback;
        }
    }

    // Application Details
    safeSet('app-application-id', application.application_id);
    safeSet('app-application-date', application.application_date || application.created_at);
    safeSet('app-academic-year', application.academic_year);
    safeSet('app-lrn', application.lrn);

    // Personal Information
    safeSet('app-full-name', application.full_name);
    
    // Format date of birth with age if available
    if (application.date_of_birth) {
        const birthDate = new Date(application.date_of_birth);
        const age = new Date().getFullYear() - birthDate.getFullYear();
        const formattedDate = birthDate.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        safeSet('app-dob', `${formattedDate} (${age} years old)`);
    } else {
        safeSet('app-dob', 'Not provided');
    }
    
    safeSet('app-gender', application.gender ? application.gender.charAt(0).toUpperCase() + application.gender.slice(1) : 'Not provided');
    safeSet('app-nationality', application.nationality);
    safeSet('app-religion', application.religion);
    
    // Student type with badge
    if (application.student_type) {
        const studentTypeBadges = {
            'new': '<span class="badge bg-success me-2">New</span><small class="text-muted">First time enrolling in any school</small>',
            'transferee': '<span class="badge bg-primary me-2">Transferee</span><small class="text-muted">Coming from another school</small>',
            'old': '<span class="badge bg-warning me-2">Old</span><small class="text-muted">Previously enrolled in this school or returning student</small>',
            'returnee': '<span class="badge bg-info me-2">Returnee</span><small class="text-muted">Returning to this school</small>'
        };
        safeSetHTML('app-type', studentTypeBadges[application.student_type] || application.student_type);
    } else {
        safeSet('app-type', 'Not specified');
    }

    // Contact Information
    safeSet('app-email', application.email);
    safeSet('app-contact', application.contact_number);
    safeSet('app-address', application.address);
    
    // Address details (city, province, zip)
    const addressDetails = [];
    if (application.city) addressDetails.push(application.city);
    if (application.province) addressDetails.push(application.province);
    if (application.zip_code) addressDetails.push(application.zip_code);
    safeSet('app-address-details', addressDetails.join(', ') || '');

    // Academic Information
    safeSet('app-grade', application.grade_level_applied);
    safeSet('app-strand', application.strand_applied || 'Not applicable');
    safeSet('app-track', application.track_applied || 'Not applicable');
    
    // Last school information
    if (application.last_school_name) {
        const lastSchoolInfo = `${application.last_school_name}${application.last_school_type ? ` (${application.last_school_type.charAt(0).toUpperCase() + application.last_school_type.slice(1)})` : ''}`;
        safeSet('app-last-school', lastSchoolInfo);
    } else {
        safeSet('app-last-school', 'Not provided');
    }

    // Parent/Guardian Information
    safeSet('app-father-name', application.father_name);
    safeSet('app-father-occupation', application.father_occupation);
    safeSet('app-father-contact', application.father_contact);
    safeSet('app-mother-name', application.mother_name);
    safeSet('app-mother-occupation', application.mother_occupation);
    safeSet('app-mother-contact', application.mother_contact);
    safeSet('app-guardian', application.guardian_name);
    safeSet('app-guardian-contact', application.guardian_contact);

    // Medical Information
    const medicalCard = document.getElementById('medical-info-card');
    if (application.medical_history && application.medical_history.trim()) {
        safeSet('app-medical-history', application.medical_history);
        if (medicalCard) medicalCard.style.display = 'block';
    } else {
        if (medicalCard) medicalCard.style.display = 'none';
    }

    // Set ID photo
    const idPhoto = document.getElementById('app-id-photo');
    const noPhotoPlaceholder = document.getElementById('no-photo-placeholder');
    if (idPhoto && noPhotoPlaceholder) {
        if (application.id_photo_data_url) {
            idPhoto.src = application.id_photo_data_url;
            idPhoto.alt = 'Student ID Photo';
            idPhoto.style.display = 'block';
            noPhotoPlaceholder.style.display = 'none';
        } else {
            idPhoto.style.display = 'none';
            noPhotoPlaceholder.style.display = 'block';
        }
    }

    // Handle documents
    const documentsInfo = document.getElementById('documents-info');
    const noDocuments = document.getElementById('no-documents');
    const documentsCount = document.getElementById('documents-count');
    const documentsList = document.getElementById('documents-list');
    
    if (application.documents && Array.isArray(application.documents) && application.documents.length > 0) {
        if (documentsCount) documentsCount.textContent = application.documents.length;
        if (documentsInfo) documentsInfo.style.display = 'block';
        if (noDocuments) noDocuments.style.display = 'none';
        
        // Populate documents list
        if (documentsList) {
            documentsList.innerHTML = '';
            application.documents.forEach((doc, index) => {
                const docElement = document.createElement('div');
                docElement.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                docElement.innerHTML = `
                    <div>
                        <i class="ri-file-line me-2"></i>
                        <small>${doc.filename || `Document ${index + 1}`}</small>
                    </div>
                    <span class="badge bg-${doc.status === 'approved' ? 'success' : doc.status === 'rejected' ? 'danger' : 'warning'}">${doc.status || 'pending'}</span>
                `;
                documentsList.appendChild(docElement);
            });
        }
    } else {
        if (documentsCount) documentsCount.textContent = '0';
        if (documentsInfo) documentsInfo.style.display = 'none';
        if (noDocuments) noDocuments.style.display = 'block';
    }

    // Set status badge
    const statusBadge = document.getElementById('app-current-status');
    if (statusBadge) {
        const status = application.enrollment_status || 'pending';
        statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        
        // Set appropriate badge class
        const statusClasses = {
            'pending': 'badge bg-warning text-dark',
            'approved': 'badge bg-success',
            'declined': 'badge bg-danger',
            'rejected': 'badge bg-danger',
            'enrolled': 'badge bg-primary'
        };
        statusBadge.className = statusClasses[status] || 'badge bg-secondary';
    }

    // Store current application ID for actions
    window.currentApplicationId = application.id;
}

// Approve application
function approveApplication(applicationId) {
    if (!confirm('Are you sure you want to approve this application?')) {
        return;
    }
    
    processApplicationAction(applicationId, 'approve');
}

// Approve application from modal
function approveApplicationFromModal(applicationId) {
    // Close modal first
    const modal = bootstrap.Modal.getInstance(document.getElementById('applicationDetailsModal'));
    if (modal) modal.hide();
    
    approveApplication(applicationId || currentApplicationId);
}

// Decline application
function declineApplication(applicationId) {
    currentApplicationId = applicationId;
    const modal = new bootstrap.Modal(document.getElementById('declineReasonModal'));
    modal.show();
}

// Decline application from modal
function declineApplicationFromModal(applicationId) {
    // Close view modal first
    const viewModal = bootstrap.Modal.getInstance(document.getElementById('applicationDetailsModal'));
    if (viewModal) viewModal.hide();
    
    declineApplication(applicationId || currentApplicationId);
}

// Submit decline with reason
function submitDecline() {
    const form = document.getElementById('decline-form');
    const formData = new FormData(form);
    const reason = formData.get('reason');
    
    if (!reason || reason.trim() === '') {
        showAlert('Please provide a reason for declining', 'warning');
        return;
    }
    
    if (!currentApplicationId) {
        showAlert('No application selected', 'danger');
        return;
    }
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('declineReasonModal'));
    if (modal) modal.hide();
    
    // Process decline
    processApplicationAction(currentApplicationId, 'decline', { reason: reason });
    
    // Reset form
    form.reset();
    currentApplicationId = null;
}

// Process application action (approve/decline)
function processApplicationAction(applicationId, action, data = {}) {
    showLoading();
    
    const url = `/registrar/applications/${applicationId}/${action}`;
    const requestData = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    };
    
    fetch(url, requestData)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            // Refresh the applications table
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Action failed', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error processing request', 'danger');
    })
    .finally(() => {
        hideLoading();
    });
}

// Refresh applications
function refreshApplications() {
    location.reload();
}

// Export applications
function exportApplications() {
    const params = new URLSearchParams();
    const status = document.getElementById('status-filter')?.value;
    const gradeLevel = document.getElementById('grade-filter')?.value;
    const search = document.getElementById('search-input')?.value;
    
    if (status) params.append('status', status);
    if (gradeLevel) params.append('grade_level', gradeLevel);
    if (search) params.append('search', search);
    params.append('export', 'true');
    
    window.open(`/registrar/applications?${params.toString()}`, '_blank');
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

// View document in new tab from documents tab
function viewDocumentInTab(applicationId, documentIndex) {
    console.log('Viewing document:', applicationId, documentIndex);
    
    // First get the document details
    fetch(`/registrar/applications/${applicationId}/documents`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': window.csrfToken || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.documents && data.documents[documentIndex]) {
            const document = data.documents[documentIndex];
            if (document.path) {
                // Open document in new tab using secure document serving route
                const documentUrl = `/registrar/documents/view/${document.path}`;
                window.open(documentUrl, '_blank');
            } else {
                showAlert('Document path not found', 'error');
            }
        } else {
            showAlert('Document not found', 'error');
        }
    })
    .catch(error => {
        console.error('Error fetching document:', error);
        showAlert('Failed to load document', 'error');
    });
}

// Approve document from documents tab
function approveDocumentInTab(applicationId, documentIndex) {
    if (!confirm('Are you sure you want to approve this document?')) {
        return;
    }
    
    updateDocumentStatusInTab(applicationId, documentIndex, 'approved');
}

// Reject document from documents tab
function rejectDocumentInTab(applicationId, documentIndex) {
    const notes = prompt('Please provide a reason for rejecting this document:');
    if (notes === null) {
        return; // User cancelled
    }
    
    updateDocumentStatusInTab(applicationId, documentIndex, 'rejected', notes);
}

// Update document status from documents tab
function updateDocumentStatusInTab(applicationId, documentIndex, status, notes = '') {
    showLoading();
    
    fetch(`/registrar/applications/${applicationId}/documents/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken || ''
        },
        body: JSON.stringify({
            document_index: documentIndex,
            status: status,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Document ${status} successfully`, 'success');
            // Refresh the documents table
            loadDocumentsData();
        } else {
            showAlert(data.message || `Failed to ${status} document`, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating document status:', error);
        showAlert(`Failed to ${status} document`, 'error');
    })
    .finally(() => {
        hideLoading();
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

function updatePagination(pagination) {
    // Update pagination if needed
    // This would be implemented based on your pagination structure
}

// Setup tab event listeners
function setupTabEventListeners() {
    const appointmentsTab = document.getElementById('appointments-tab');
    const noticesTab = document.getElementById('notices-tab');
    
    if (appointmentsTab) {
        appointmentsTab.addEventListener('click', function() {
            loadAppointmentsData();
        });
    }
    
    if (noticesTab) {
        noticesTab.addEventListener('click', function() {
            loadNoticesData();
        });
    }
}

// Load appointments data
function loadAppointmentsData() {
    console.log('Loading appointments data...');
    const loadingDiv = document.getElementById('appointments-loading');
    const contentDiv = document.getElementById('appointments-content');
    const emptyDiv = document.getElementById('appointments-empty');
    const tableBody = document.getElementById('appointments-table-body');
    
    // Show loading state
    if (loadingDiv) loadingDiv.style.display = 'block';
    if (contentDiv) contentDiv.style.display = 'none';
    if (emptyDiv) emptyDiv.style.display = 'none';
    
    fetch('/registrar/appointments', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': window.csrfToken || ''
        }
    })
        .then(response => {
            console.log('Appointments response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Appointments data received:', data);
            if (loadingDiv) loadingDiv.style.display = 'none';
            
            if (data.success && data.appointments && data.appointments.length > 0) {
                // Populate appointments table
                if (tableBody) {
                    tableBody.innerHTML = '';
                    data.appointments.forEach(appointment => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${appointment.application_id || 'N/A'}</td>
                            <td>${appointment.full_name || 'N/A'}</td>
                            <td>${appointment.grade_level || 'N/A'}</td>
                            <td>${formatDateTime(appointment.preferred_schedule)}</td>
                            <td><span class="badge bg-${getAppointmentStatusColor(appointment.appointment_status)}">${appointment.appointment_status || 'Pending'}</span></td>
                            <td><span class="badge bg-${getStatusColor(appointment.status)}">${appointment.status || 'Pending'}</span></td>
                        `;
                        tableBody.appendChild(row);
                    });
                }
                if (contentDiv) contentDiv.style.display = 'block';
            } else {
                console.log('No appointments found or empty response');
                if (emptyDiv) emptyDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading appointments:', error);
            if (loadingDiv) loadingDiv.style.display = 'none';
            if (emptyDiv) emptyDiv.style.display = 'block';
            showAlert('Failed to load appointments: ' + error.message, 'error');
        });
}

// Load notices data
function loadNoticesData() {
    console.log('Loading notices data...');
    const loadingDiv = document.getElementById('notices-loading');
    const contentDiv = document.getElementById('notices-content');
    const emptyDiv = document.getElementById('notices-empty');
    const tableBody = document.getElementById('notices-table-body');
    
    // Show loading state
    if (loadingDiv) loadingDiv.style.display = 'block';
    if (contentDiv) contentDiv.style.display = 'none';
    if (emptyDiv) emptyDiv.style.display = 'none';
    
    fetch('/registrar/notices', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': window.csrfToken || ''
        }
    })
        .then(response => {
            console.log('Notices response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Notices data received:', data);
            if (loadingDiv) loadingDiv.style.display = 'none';
            
            if (data.success && data.notices && data.notices.length > 0) {
                // Populate notices table
                if (tableBody) {
                    tableBody.innerHTML = '';
                    data.notices.forEach(notice => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${notice.title || 'N/A'}</td>
                            <td>${notice.enrollee ? `${notice.enrollee.full_name} (${notice.enrollee.application_id})` : 'Global Notice'}</td>
                            <td><span class="badge bg-${getNoticeTypeColor(notice.type)}">${notice.type || 'info'}</span></td>
                            <td><span class="badge bg-${getPriorityColor(notice.priority)}">${notice.priority || 'normal'}</span></td>
                            <td>${notice.created_at || 'N/A'}</td>
                            <td><span class="badge bg-${notice.read_at ? 'success' : 'warning'}">${notice.read_at ? 'Read' : 'Unread'}</span></td>
                        `;
                        tableBody.appendChild(row);
                    });
                }
                if (contentDiv) contentDiv.style.display = 'block';
            } else {
                console.log('No notices found or empty response');
                if (emptyDiv) emptyDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading notices:', error);
            if (loadingDiv) loadingDiv.style.display = 'none';
            if (emptyDiv) emptyDiv.style.display = 'block';
            showAlert('Failed to load notices: ' + error.message, 'error');
        });
}

// Helper functions for status colors
function getStatusColor(status) {
    switch(status) {
        case 'pending': return 'warning';
        case 'approved': return 'success';
        case 'declined': return 'danger';
        case 'rejected': return 'danger';
        case 'enrolled': return 'primary';
        case 'completed': return 'success';
        default: return 'secondary';
    }
}

function getAppointmentStatusColor(status) {
    switch(status) {
        case 'Completed': return 'success';
        case 'Today': return 'warning';
        case 'Scheduled': return 'info';
        case 'Overdue': return 'danger';
        default: return 'secondary';
    }
}

function getNoticeTypeColor(type) {
    switch(type) {
        case 'success': return 'success';
        case 'error': return 'danger';
        case 'warning': return 'warning';
        case 'info': return 'info';
        default: return 'secondary';
    }
}

function getPriorityColor(priority) {
    switch(priority) {
        case 'high': return 'danger';
        case 'urgent': return 'danger';
        case 'normal': return 'info';
        default: return 'secondary';
    }
}

function formatDateTime(dateTimeString) {
    if (!dateTimeString) return 'Not set';
    const date = new Date(dateTimeString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// View documents for an application
function viewDocuments(applicationId) {
    console.log('Viewing documents for application:', applicationId);
    currentApplicationId = applicationId;
    
    // Fetch documents data
    fetch(`/registrar/applications/${applicationId}/documents`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken || '',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateDocumentsModal(data.documents);
            const modal = new bootstrap.Modal(document.getElementById('documentsModal'));
            modal.show();
        } else {
            showAlert('error', data.message || 'Failed to load documents');
        }
    })
    .catch(error => {
        console.error('Error loading documents:', error);
        showAlert('error', 'Failed to load documents');
    });
}

// Populate documents modal with real data
function populateDocumentsModal(documents) {
    const container = document.getElementById('document-content');
    if (!container) return;

    if (!documents || documents.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="ri-file-list-line display-4 text-muted"></i>
                <p class="text-muted mt-2">No documents uploaded</p>
            </div>
        `;
        return;
    }

    let html = '';
    documents.forEach((doc, index) => {
        html += `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-1">
                            <i class="${getFileIcon(doc.type)} text-primary fs-4"></i>
                        </div>
                        <div class="col-md-5">
                            <h6 class="mb-1">${doc.type || 'Document'}</h6>
                            <small class="text-muted">${doc.filename || 'Unknown file'}</small>
                        </div>
                        <div class="col-md-3">
                            ${getDocumentStatusBadge(doc.status)}
                        </div>
                        <div class="col-md-3">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="viewDocumentFile('${doc.path}')" title="View Document">
                                    <i class="ri-eye-line"></i>
                                </button>
                                <button class="btn btn-outline-success" onclick="approveDocument(${index})" title="Approve">
                                    <i class="ri-check-line"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="rejectDocument(${index})" title="Reject">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Get document status badge
function getDocumentStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning text-dark">Pending Review</span>',
        'approved': '<span class="badge bg-success">Approved</span>',
        'rejected': '<span class="badge bg-danger">Rejected</span>'
    };
    return badges[status] || badges['pending'];
}

// Get file icon based on document type
function getFileIcon(type) {
    const icons = {
        'Birth Certificate': 'ri-file-text-line',
        'Report Card': 'ri-file-chart-line',
        'Good Moral': 'ri-file-shield-line',
        'ID Photo': 'ri-image-line'
    };
    return icons[type] || 'ri-file-line';
}

// View document file
function viewDocumentFile(path) {
    if (path) {
        window.open(`/storage/${path}`, '_blank');
    } else {
        showAlert('error', 'Document path not found');
    }
}

// Approve document
function approveDocument(documentIndex) {
    if (!currentApplicationId) {
        showAlert('No application selected', 'warning');
        return;
    }
    
    if (documentIndex !== undefined) {
        updateDocumentStatus(documentIndex, 'approved');
    } else if (currentDocumentIndex !== null) {
        const notes = document.getElementById('doc-review-notes')?.value || '';
        updateDocumentStatus(currentDocumentIndex, 'approved', notes);
    } else {
        showAlert('No document selected', 'warning');
    }
}

// Reject document
function rejectDocument(applicationIdOrIndex, documentIndex) {
    // Handle different call patterns
    if (typeof applicationIdOrIndex === 'string' && documentIndex !== undefined) {
        // Called from documents tab: rejectDocument(applicationId, documentIndex)
        const notes = prompt('Please provide a reason for rejection:');
        if (notes !== null && notes.trim()) {
            updateDocumentStatusInTab(applicationIdOrIndex, documentIndex, 'rejected', notes);
        }
    } else if (typeof applicationIdOrIndex === 'number' || applicationIdOrIndex === undefined) {
        // Called from modal: rejectDocument(documentIndex) or rejectDocument()
        const docIndex = applicationIdOrIndex;
        if (!currentApplicationId) {
            showAlert('No application selected', 'warning');
            return;
        }
        
        if (docIndex !== undefined) {
            const notes = prompt('Please provide a reason for rejection:');
            if (notes !== null) {
                updateDocumentStatus(docIndex, 'rejected', notes);
            }
        } else if (currentDocumentIndex !== null) {
            const notes = document.getElementById('doc-review-notes')?.value;
            if (!notes || !notes.trim()) {
                showAlert('Please provide a reason for rejecting this document', 'warning');
                return;
            }
            updateDocumentStatus(currentDocumentIndex, 'rejected', notes);
        } else {
            showAlert('No document selected', 'warning');
        }
    }
}

// Update document status
function updateDocumentStatus(documentIndex, status, notes = '') {
    showLoading();
    
    fetch(`/registrar/applications/${currentApplicationId}/documents/status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken || '',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            document_index: documentIndex,
            status: status,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Document ${status} successfully`);
            // Refresh the documents view
            viewDocuments(currentApplicationId);
        } else {
            showAlert('error', data.message || `Failed to ${status} document`);
        }
    })
    .catch(error => {
        console.error('Error updating document status:', error);
        showAlert('error', `Failed to ${status} document`);
    })
    .finally(() => {
        hideLoading();
    });
}

// Send notice to applicant
function sendNoticeToApplicant(applicationId) {
    currentApplicationId = applicationId;
    
    // Create a simple notice modal
    const modalHtml = `
        <div class="modal fade" id="noticeModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Send Notice to Applicant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="notice-form">
                            <div class="mb-3">
                                <label for="notice-subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="notice-subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="notice-message" class="form-label">Message</label>
                                <textarea class="form-control" id="notice-message" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="notice-priority" class="form-label">Priority</label>
                                <select class="form-control" id="notice-priority">
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="submitNotice()">Send Notice</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('noticeModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('noticeModal'));
    modal.show();
}

// Submit notice
function submitNotice() {
    const title = document.getElementById('notice-title')?.value;
    const message = document.getElementById('notice-message')?.value;
    const priority = document.getElementById('notice-priority')?.value;
    const type = document.getElementById('notice-type')?.value;
    const recipients = document.getElementById('notice-recipients')?.value;
    const specificApplicant = document.getElementById('specific-applicant')?.value;
    
    if (!title || !message || !priority || !type || !recipients) {
        showAlert('Please fill in all required fields', 'error');
        return;
    }
    
    // Validate specific applicant selection
    if (recipients === 'specific' && !specificApplicant) {
        showAlert('Please select a specific applicant', 'error');
        return;
    }
    
    showLoading();
    
    const requestData = {
        title: title,
        message: message,
        priority: priority,
        type: type,
        recipients: recipients
    };
    
    // Add specific applicant if selected
    if (recipients === 'specific' && specificApplicant) {
        requestData.specific_applicant = specificApplicant;
    }
    
    // Determine if this is an update or create
    const isUpdate = window.currentNoticeId;
    const url = isUpdate ? `/registrar/notices/${window.currentNoticeId}/update` : '/registrar/notices/create';
    const method = isUpdate ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': window.csrfToken || '',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(isUpdate ? 'Notice updated successfully' : 'Notice sent successfully', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('createNoticeModal'));
            if (modal) modal.hide();
            
            // Clear current notice ID
            window.currentNoticeId = null;
            
            // Refresh notices data if on notices tab
            if (document.querySelector('.nav-link[data-bs-target="#notices-tab"]')?.classList.contains('active')) {
                loadNoticesData();
            }
        } else {
            showAlert(data.message || `Failed to ${isUpdate ? 'update' : 'send'} notice`, 'error');
        }
    })
    .catch(error => {
        console.error(`Error ${isUpdate ? 'updating' : 'sending'} notice:`, error);
        showAlert(`Failed to ${isUpdate ? 'update' : 'send'} notice`, 'error');
    })
    .finally(() => {
        hideLoading();
    });
}



// Bulk approve applications
function bulkApprove() {
    if (selectedApplications.length === 0) {
        showAlert('warning', 'Please select applications to approve.');
        return;
    }
    
    currentBulkAction = 'approve';
    showBulkActionModal('approve', selectedApplications.length);
}

// Bulk decline applications
function bulkDecline() {
    if (selectedApplications.length === 0) {
        showAlert('warning', 'Please select applications to decline.');
        return;
    }
    
    currentBulkAction = 'decline';
    showBulkActionModal('decline', selectedApplications.length);
}

// Show bulk action modal
function showBulkActionModal(action, count) {
    const modal = document.getElementById('bulkActionModal');
    const title = document.getElementById('bulkActionTitle');
    const message = document.getElementById('bulkActionMessage');
    
    if (title) title.textContent = `Bulk ${action.charAt(0).toUpperCase() + action.slice(1)}`;
    if (message) message.textContent = `Are you sure you want to ${action} ${count} selected application(s)?`;
    
    if (modal) {
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
}

// Confirm bulk action
function confirmBulkAction() {
    if (!currentBulkAction || selectedApplications.length === 0) {
        return;
    }
    
    showLoading();
    
    fetch(`/registrar/applications/bulk-${currentBulkAction}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken || '',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            application_ids: selectedApplications
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Successfully ${currentBulkAction}ed ${selectedApplications.length} application(s)`);
            location.reload(); // Refresh the page to show updated data
        } else {
            showAlert('error', data.message || `Failed to ${currentBulkAction} applications`);
        }
    })
    .catch(error => {
        console.error(`Error in bulk ${currentBulkAction}:`, error);
        showAlert('error', `Failed to ${currentBulkAction} applications`);
    })
    .finally(() => {
        hideLoading();
        const modal = bootstrap.Modal.getInstance(document.getElementById('bulkActionModal'));
        if (modal) modal.hide();
        currentBulkAction = null;
    });
}

// Bulk delete applications
function bulkDelete() {
    if (selectedApplications.length === 0) {
        showAlert('warning', 'Please select applications to delete.');
        return;
    }
    
    currentBulkAction = 'delete';
    showBulkActionModal('delete', selectedApplications.length);
}

// Export selected applications
function exportSelected() {
    if (selectedApplications.length === 0) {
        showAlert('warning', 'Please select applications to export.');
        return;
    }
    
    const params = new URLSearchParams();
    params.append('export', 'selected');
    selectedApplications.forEach(id => params.append('ids[]', id));
    
    window.open(`/registrar/applications/export?${params.toString()}`, '_blank');
    showAlert('info', 'Export started for selected applications.');
}

// Clear all selections
function clearAllSelections() {
    const checkboxes = document.querySelectorAll('.application-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
    
    selectedApplications = [];
    updateBulkActionsPanel();
    showAlert('info', 'All selections cleared.');
}

// Refresh data based on active tab
function refreshData() {
    const activeTab = document.querySelector('.nav-link.active, button[data-bs-toggle="tab"].active');
    const activeTabId = activeTab ? activeTab.getAttribute('data-bs-target') : '#applications';
    
    console.log('Refreshing data for tab:', activeTabId);
    
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
        case '#data-change-requests':
            // Data change requests are handled by their own dedicated script
            console.log('Data change requests refresh handled by dedicated script');
            break;
        default:
            location.reload();
    }
    
    showAlert('Data refreshed', 'info');
}

// Export data based on active tab
function exportData() {
    const activeTab = document.querySelector('.nav-link.active, button[data-bs-toggle="tab"].active');
    const activeTabId = activeTab ? activeTab.getAttribute('data-bs-target') : '#applications';
    
    console.log('Exporting data for tab:', activeTabId);
    
    const baseUrl = '/registrar/applications/export';
    const params = new URLSearchParams();
    
    // Add current filters
    const statusFilter = document.getElementById('status-filter');
    const gradeFilter = document.getElementById('grade-filter');
    const searchInput = document.getElementById('search-input');
    
    if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);
    if (gradeFilter && gradeFilter.value) params.append('grade_level', gradeFilter.value);
    if (searchInput && searchInput.value) params.append('search', searchInput.value);
    
    // Add tab-specific parameters
    switch(activeTabId) {
        case '#applications':
            params.append('type', 'applications');
            break;
        case '#documents':
            params.append('type', 'documents');
            break;
        case '#appointments':
            params.append('type', 'appointments');
            break;
        case '#notices':
            params.append('type', 'notices');
            break;
        case '#data-change-requests':
            params.append('type', 'data-change-requests');
            break;
    }
    
    window.open(`${baseUrl}?${params.toString()}`, '_blank');
    showAlert('Export started', 'info');
}

// Clear filters with null safety
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

// Open create notice modal
function openCreateNoticeModal() {
    const modal = new bootstrap.Modal(document.getElementById('createNoticeModal'));
    
    // Reset form
    const form = document.getElementById('create-notice-form');
    if (form) form.reset();
    
    const specificDiv = document.getElementById('specific-applicant-div');
    if (specificDiv) specificDiv.style.display = 'none';
    
    // Clear any stored notice ID
    window.currentNoticeId = null;
    
    // Update modal title for create mode
    const modalLabel = document.getElementById('createNoticeModalLabel');
    if (modalLabel) {
        modalLabel.innerHTML = '<i class="ri-notification-line me-2"></i>Create Notice';
    }
    
    console.log('Opening create notice modal');
    modal.show();
}

// Edit existing notice
function editNotice(noticeId) {
    fetch(`/registrar/notices/${noticeId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': window.csrfToken || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notice = data.notice;
            
            // Populate form fields
            document.getElementById('notice-title').value = notice.title;
            document.getElementById('notice-message').value = notice.message;
            document.getElementById('notice-type').value = notice.type;
            document.getElementById('notice-priority').value = notice.priority;
            
            // Handle recipients selection
            const recipientsSelect = document.getElementById('notice-recipients');
            const specificDiv = document.getElementById('specific-applicant-div');
            const specificApplicantSelect = document.getElementById('specific-applicant');
            
            if (notice.is_global) {
                recipientsSelect.value = 'all';
                specificDiv.style.display = 'none';
            } else if (notice.enrollee_id) {
                recipientsSelect.value = 'specific';
                specificDiv.style.display = 'block';
                specificApplicantSelect.value = notice.enrollee_id;
            } else {
                // Try to determine category based on notice recipients
                // This is a fallback - ideally we'd store the original category
                recipientsSelect.value = 'all';
                specificDiv.style.display = 'none';
            }
            
            // Update modal title for edit mode
            document.getElementById('createNoticeModalLabel').innerHTML = '<i class="ri-edit-line me-2"></i>Edit Notice';
            
            // Store notice ID for update
            window.currentNoticeId = noticeId;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('createNoticeModal'));
            modal.show();
        } else {
            showAlert(data.message || 'Failed to load notice', 'error');
        }
    })
    .catch(error => {
        console.error('Error loading notice:', error);
        showAlert('Failed to load notice', 'error');
    });
}

// Open bulk notice modal
function openBulkNoticeModal() {
    const modal = new bootstrap.Modal(document.getElementById('bulkNoticeModal'));
    
    // Reset form
    const form = document.getElementById('bulk-notice-form');
    if (form) form.reset();
    
    const preview = document.getElementById('recipients-preview');
    if (preview) preview.innerHTML = 'Click "Preview Recipients" to see who will receive this notice';
    
    modal.show();
}

// Send notice
function sendNotice() {
    const form = document.getElementById('create-notice-form');
    if (!form || !form.checkValidity()) {
        if (form) form.reportValidity();
        return;
    }

    const title = document.getElementById('notice-title')?.value;
    const priority = document.getElementById('notice-priority')?.value;
    const message = document.getElementById('notice-message')?.value;

    if (!title || !priority || !message) {
        showAlert('Please fill in all required fields', 'warning');
        return;
    }

    const data = {
        subject: title,
        message: message,
        priority: priority
    };

    // Use the existing sendNoticeToApplicant function if we have a current application
    if (currentApplicationId) {
        fetch(`/registrar/applications/${currentApplicationId}/notice`, {
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
                showAlert(data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('createNoticeModal')).hide();
                loadNoticesData(); // Refresh notices tab if active
            } else {
                showAlert(data.message || 'Failed to send notice', 'error');
            }
        })
        .catch(error => {
            console.error('Error sending notice:', error);
            showAlert('Failed to send notice', 'error');
        });
    }
}

// Preview recipients for bulk notice
function previewRecipients() {
    const statusFilter = document.getElementById('bulk-status-filter')?.value;
    const gradeFilter = document.getElementById('bulk-grade-filter')?.value;
    
    let url = '/registrar/applications?';
    const params = new URLSearchParams();
    
    if (statusFilter) params.append('status', statusFilter);
    if (gradeFilter) params.append('grade_level', gradeFilter);
    
    fetch(url + params.toString())
        .then(response => response.json())
        .then(data => {
            const preview = document.getElementById('recipients-preview');
            if (!preview) return;
            
            if (data.success && data.applications && data.applications.length > 0) {
                let html = `<strong>${data.applications.length} recipients:</strong><br>`;
                html += data.applications.map(r => `${r.application_id} - ${r.first_name} ${r.last_name}`).join('<br>');
                preview.innerHTML = html;
            } else {
                preview.innerHTML = '<span class="text-warning">No recipients match the selected criteria</span>';
            }
        })
        .catch(error => {
            console.error('Error previewing recipients:', error);
            const preview = document.getElementById('recipients-preview');
            if (preview) preview.innerHTML = '<span class="text-danger">Error loading recipients</span>';
        });
}

// Send bulk notice
function sendBulkNotice() {
    const form = document.getElementById('bulk-notice-form');
    if (!form || !form.checkValidity()) {
        if (form) form.reportValidity();
        return;
    }

    const title = document.getElementById('bulk-notice-title')?.value;
    const priority = document.getElementById('bulk-notice-priority')?.value;
    const message = document.getElementById('bulk-notice-message')?.value;

    if (!title || !priority || !message) {
        showAlert('Please fill in all required fields', 'warning');
        return;
    }

    showAlert('Bulk notice functionality will be implemented with backend support', 'info');
    bootstrap.Modal.getInstance(document.getElementById('bulkNoticeModal')).hide();
}





// Global function assignments for onclick handlers
window.viewApplication = viewApplication;
window.approveApplication = approveApplication;
window.declineApplication = declineApplication;
window.viewDocuments = viewDocuments;
window.scheduleAppointment = scheduleAppointment;
window.sendNoticeToApplicant = sendNoticeToApplicant;
window.bulkApprove = bulkApprove;
window.bulkDecline = bulkDecline;
window.bulkDelete = bulkDelete;
window.exportSelected = exportSelected;
window.clearAllSelections = clearAllSelections;
window.refreshData = refreshData;
window.exportData = exportData;
window.clearFilters = clearFilters;
window.openCreateNoticeModal = openCreateNoticeModal;
window.openBulkNoticeModal = openBulkNoticeModal;
window.sendNotice = sendNotice;
window.previewRecipients = previewRecipients;
window.sendBulkNotice = sendBulkNotice;
window.approveApplicationFromModal = approveApplicationFromModal;
window.declineApplicationFromModal = declineApplicationFromModal;
window.approveDocument = approveDocument;
window.rejectDocument = rejectDocument;
window.updateDocumentStatus = updateDocumentStatus;
window.viewDocumentFile = viewDocumentFile;
window.submitNotice = submitNotice;
window.saveAppointment = saveAppointment;
window.confirmBulkAction = confirmBulkAction;
window.submitDecline = submitDecline;
window.refreshApplications = refreshApplications;
window.exportApplications = exportApplications;
window.loadAppointmentsData = loadAppointmentsData;
window.loadNoticesData = loadNoticesData;
window.populateDocumentsModal = populateDocumentsModal;
window.getDocumentStatusBadge = getDocumentStatusBadge;
window.getFileIcon = getFileIcon;
window.formatDateTime = formatDateTime;
window.getStatusColor = getStatusColor;
window.getAppointmentStatusColor = getAppointmentStatusColor;
window.getNoticeTypeColor = getNoticeTypeColor;
window.getPriorityColor = getPriorityColor;
window.loadDocumentsData = loadDocumentsData;
window.viewDocumentInTab = viewDocumentInTab;
window.approveDocumentInTab = approveDocumentInTab;
window.rejectDocumentInTab = rejectDocumentInTab;
window.formatDate = formatDate;
window.updateDocumentStatusInTab = updateDocumentStatusInTab;
window.setupDocumentFilters = setupDocumentFilters;
window.rejectDocument = rejectDocument;
window.handleTabSwitching = handleTabSwitching;
window.approveAppointment = approveAppointment;
window.rejectAppointment = rejectAppointment;
window.viewNotice = viewNotice;

// Handle tab switching based on URL parameters
function handleTabSwitching() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    
    if (activeTab) {
        // Deactivate all tabs
        document.querySelectorAll('.nav-link').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
        });
        
        // Activate the specified tab
        const tabButton = document.getElementById(`${activeTab}-tab`);
        const tabPane = document.getElementById(activeTab);
        
        if (tabButton && tabPane) {
            tabButton.classList.add('active');
            tabPane.classList.add('show', 'active');
            
            // Load data for the active tab
            switch(activeTab) {
                case 'documents':
                    // Documents are loaded server-side, no need to fetch
                    break;
                case 'appointments':
                    loadAppointmentsData();
                    break;
                case 'notices':
                    loadNoticesData();
                    break;
            }
        }
    }
}


// Appointment Management Functions
function approveAppointment(applicationId) {
    if (!confirm('Are you sure you want to approve this appointment?')) {
        return;
    }
    
    fetch(`/registrar/appointments/${applicationId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            // Reload page to show updated data
            window.location.reload();
        } else {
            showAlert(data.message || 'Failed to approve appointment', 'error');
        }
    })
    .catch(error => {
        console.error('Error approving appointment:', error);
        showAlert('Failed to approve appointment', 'error');
    });
}

function rejectAppointment(applicationId) {
    const notes = prompt('Please provide a reason for rejecting this appointment:');
    if (notes === null || notes.trim() === '') {
        return;
    }
    
    fetch(`/registrar/appointments/${applicationId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken || ''
        },
        body: JSON.stringify({
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            // Reload page to show updated data
            window.location.reload();
        } else {
            showAlert(data.message || 'Failed to reject appointment', 'error');
        }
    })
    .catch(error => {
        console.error('Error rejecting appointment:', error);
        showAlert('Failed to reject appointment', 'error');
    });
}

function scheduleAppointment(applicationId) {
    // Populate modal with appointment data
    document.getElementById('appt-app-id').textContent = applicationId;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('appointmentReviewModal'));
    modal.show();
    
    // Store current application ID for saving
    window.currentAppointmentId = applicationId;
}

function saveAppointment() {
    const applicationId = window.currentAppointmentId;
    if (!applicationId) {
        showAlert('No appointment selected', 'error');
        return;
    }
    
    const status = document.getElementById('appt-status-select').value;
    const newDate = document.getElementById('appt-new-date').value;
    const newTime = document.getElementById('appt-new-time').value;
    const notes = document.getElementById('appt-notes').value;
    
    if (!status) {
        showAlert('Please select a status', 'error');
        return;
    }
    
    fetch(`/registrar/appointments/${applicationId}/schedule`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken || ''
        },
        body: JSON.stringify({
            status: status,
            new_date: newDate,
            new_time: newTime,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('appointmentReviewModal'));
            modal.hide();
            // Reload page to show updated data
            window.location.reload();
        } else {
            showAlert(data.message || 'Failed to update appointment', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating appointment:', error);
        showAlert('Failed to update appointment', 'error');
    });
}





function viewNotice(noticeId) {
    fetch(`/registrar/notices/${noticeId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': window.csrfToken || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notice = data.notice;
            
            // Populate modal
            document.getElementById('view-notice-title').textContent = notice.title;
            document.getElementById('view-notice-type').textContent = notice.type;
            document.getElementById('view-notice-type').className = `badge bg-${getNoticeTypeColor(notice.type)}`;
            document.getElementById('view-notice-priority').textContent = notice.priority;
            document.getElementById('view-notice-priority').className = `badge bg-${getPriorityColor(notice.priority)}`;
            document.getElementById('view-notice-date').textContent = notice.created_at;
            document.getElementById('view-notice-status').textContent = notice.read_at ? 'Read' : 'Unread';
            document.getElementById('view-notice-status').className = `badge bg-${notice.read_at ? 'success' : 'warning'}`;
            document.getElementById('view-notice-recipient').textContent = notice.is_global ? 'All Applicants' : (notice.enrollee ? `${notice.enrollee.full_name} (${notice.enrollee.application_id})` : 'Unknown');
            document.getElementById('view-notice-message').textContent = notice.message;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('viewNoticeModal'));
            modal.show();
        } else {
            showAlert(data.message || 'Failed to load notice', 'error');
        }
    })
    .catch(error => {
        console.error('Error loading notice:', error);
        showAlert('Failed to load notice', 'error');
    });
}


// Global function assignments for onclick handlers
window.viewApplication = viewApplication;
window.approveApplication = approveApplication;
window.declineApplication = declineApplication;
window.sendNoticeToApplicant = sendNoticeToApplicant;
window.approveAppointment = approveAppointment;
window.rejectAppointment = rejectAppointment;
window.scheduleAppointment = scheduleAppointment;
window.viewNotice = viewNotice;
window.openBulkNoticeModal = openBulkNoticeModal;
window.openCreateNoticeModal = openCreateNoticeModal;
window.editNotice = editNotice;
window.submitNotice = submitNotice;
window.sendBulkNotice = sendBulkNotice;
window.previewRecipients = previewRecipients;
window.approveDocument = approveDocument;
window.rejectDocument = rejectDocument;
window.updateDocumentStatus = updateDocumentStatus;
window.viewDocumentFile = viewDocumentFile;
window.saveAppointment = saveAppointment;
window.bulkApprove = bulkApprove;
window.bulkDecline = bulkDecline;
window.bulkDelete = bulkDelete;
window.exportSelected = exportSelected;
window.clearAllSelections = clearAllSelections;
window.refreshData = refreshData;
window.exportData = exportData;
window.clearFilters = clearFilters;
window.confirmBulkAction = confirmBulkAction;
window.submitDecline = submitDecline;
window.refreshApplications = refreshApplications;
window.exportApplications = exportApplications;
window.loadAppointmentsData = loadAppointmentsData;
window.loadNoticesData = loadNoticesData;
window.populateDocumentsModal = populateDocumentsModal;
window.getDocumentStatusBadge = getDocumentStatusBadge;
window.getFileIcon = getFileIcon;
window.formatDateTime = formatDateTime;
window.getStatusColor = getStatusColor;
window.getAppointmentStatusColor = getAppointmentStatusColor;
window.getNoticeTypeColor = getNoticeTypeColor;
window.getPriorityColor = getPriorityColor;
window.loadDocumentsData = loadDocumentsData;
window.viewDocumentInTab = viewDocumentInTab;
window.approveDocumentInTab = approveDocumentInTab;
window.rejectDocumentInTab = rejectDocumentInTab;
window.formatDate = formatDate;
window.updateDocumentStatusInTab = updateDocumentStatusInTab;
window.setupDocumentFilters = setupDocumentFilters;
window.handleTabSwitching = handleTabSwitching;
window.approveApplicationFromModal = approveApplicationFromModal;
window.declineApplicationFromModal = declineApplicationFromModal;
