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
    
    // Check for auto-open parameter from dashboard
    checkForAutoOpenModal();
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

// Load applications data with AJAX and tab filtering
function loadApplicationsData(tab = null) {
    console.log('Loading applications data with AJAX...');
    
    // Get current tab if not specified
    if (!tab) {
        const activeTab = document.querySelector('.nav-link.active[data-bs-target]');
        if (activeTab) {
            const target = activeTab.getAttribute('data-bs-target');
            tab = target ? target.replace('#', '') : 'pending';
        } else {
            // Fallback: check URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            tab = urlParams.get('tab') || 'pending';
        }
    }
    
    // Show loading state
    showLoading();
    
    // Get current filters
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const gradeFilter = document.getElementById('grade-filter');
    
    const params = new URLSearchParams();
    params.append('tab', tab);
    
    if (searchInput?.value) params.append('search', searchInput.value);
    if (statusFilter?.value) params.append('status', statusFilter.value);
    if (gradeFilter?.value) params.append('grade_level', gradeFilter.value);
    
    fetch(`/registrar/applications/data?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateApplicationsTable(data.applications, tab);
            updateApplicationsCount(data.counts);
            updateBulkActionsPanel();
        } else {
            showAlert('Failed to load applications', 'danger');
        }
    })
    .catch(error => {
        console.error('Error loading applications:', error);
        showAlert('Error loading applications', 'danger');
    })
    .finally(() => {
        hideLoading();
    });
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
function updateApplicationsTable(applications, tabType = 'applications') {
    const tableBody = document.getElementById(`${tabType}-table-body`) || document.getElementById('applications-tbody');
    const contentDiv = document.getElementById(`${tabType}-content`);
    const emptyDiv = document.getElementById(`${tabType}-empty`);
    
    if (!tableBody) return;
    
    if (applications && applications.length > 0) {
        tableBody.innerHTML = '';
        applications.forEach(application => {
            const row = createApplicationRow(application, tabType);
            tableBody.appendChild(row);
        });
        
        if (contentDiv) contentDiv.style.display = 'block';
        if (emptyDiv) emptyDiv.style.display = 'none';
    } else {
        if (contentDiv) contentDiv.style.display = 'none';
        if (emptyDiv) emptyDiv.style.display = 'block';
    }
}

// Create table row for application
function createApplicationRow(application, tabType = 'applications') {
    const row = document.createElement('tr');
    row.setAttribute('data-id', application.id);
    
    const statusBadge = getStatusBadge(application.enrollment_status);
    const actionButtons = createActionButtons(application);
    
    row.innerHTML = `
        <td>
            <input type="checkbox" class="form-check-input application-checkbox" value="${application.id}">
        </td>
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

// Check for auto-open modal parameter from dashboard
function checkForAutoOpenModal() {
    const urlParams = new URLSearchParams(window.location.search);
    const viewApplicationId = urlParams.get('view');
    
    if (viewApplicationId && window.location.pathname.includes('/registrar/applications')) {
        console.log('Auto-opening application modal for ID:', viewApplicationId);
        
        // Wait a bit for the page to fully load, then open the modal
        setTimeout(() => {
            viewApplication(viewApplicationId);
            
            // Clean up the URL parameter after opening the modal
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }, 500);
    }
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
        
        // Initialize document management for this application
        if (window.RegistrarDocumentManagement) {
            window.RegistrarDocumentManagement.makeDocumentsClickable(application.application_id || application.id);
        }
        
        // Populate documents list
        if (documentsList) {
            documentsList.innerHTML = '';
            application.documents.forEach((doc, index) => {
                const docElement = document.createElement('div');
                docElement.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                docElement.innerHTML = `
                    <div>
                        <i class="ri-file-text-line me-2"></i>
                        <strong>${doc.type || 'Document'}</strong><br>
                        <small class="text-muted">${doc.filename || 'Unknown file'}</small>
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
    const modal = new bootstrap.Modal(document.getElementById('declineModal'));
    modal.show();
}

// Decline application from modal
function declineApplicationFromModal(applicationId) {
    // Close view modal first
    const viewModal = bootstrap.Modal.getInstance(document.getElementById('applicationDetailsModal'));
    if (viewModal) viewModal.hide();
    
    declineApplication(applicationId || currentApplicationId);
}

// Confirm decline with reason
function confirmDecline() {
    const reasonTextarea = document.getElementById('decline-reason');
    const reason = reasonTextarea ? reasonTextarea.value.trim() : '';
    
    if (!reason) {
        showAlert('Please provide a reason for declining the application', 'warning');
        return;
    }
    
    if (!currentApplicationId) {
        showAlert('No application selected', 'error');
        return;
    }
    
    // Show loading state
    const confirmBtn = document.querySelector('#declineModal .btn-warning');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Declining...';
    }
    
    // Make API call to decline application
    fetch(`/registrar/applications/${currentApplicationId}/decline`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Application declined successfully', 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('declineModal'));
            if (modal) modal.hide();
            
            // Clear form
            if (reasonTextarea) reasonTextarea.value = '';
            
            // Refresh data
            if (typeof loadApplicationsData === 'function') {
                loadApplicationsData();
            }
        } else {
            showAlert(data.message || 'Failed to decline application', 'error');
        }
    })
    .catch(error => {
        console.error('Error declining application:', error);
        showAlert('Error declining application', 'error');
    })
    .finally(() => {
        // Restore button
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="ri-close-line me-1"></i>Decline Application';
        }
    });
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

// Process application action (approve/decline) with AJAX refresh
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
            // Refresh data without page reload
            setTimeout(() => {
                loadApplicationsData();
                if (typeof loadDocumentsData === 'function') {
                    loadDocumentsData();
                }
            }, 1000);
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
    const noticesTab = document.getElementById('notices-tab');
    
    if (noticesTab) {
        noticesTab.addEventListener('click', function() {
            loadNoticesData();
        });
    }
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
                            <td>${escapeHtml(notice.title || 'N/A')}</td>
                            <td>
                                ${notice.is_global ? 
                                    '<span class="badge bg-info">All Applicants</span>' : 
                                    notice.enrollee ? 
                                        `${escapeHtml(notice.enrollee.full_name)}<br><small class="text-muted">(${escapeHtml(notice.enrollee.application_id)})</small>` : 
                                        '<span class="text-muted">Unknown</span>'
                                }
                            </td>
                            <td><span class="badge bg-${getPriorityColor(notice.priority)}">${escapeHtml(notice.priority || 'normal')}</span></td>
                            <td>${escapeHtml(notice.created_at || 'N/A')}</td>
                            <td><span class="badge bg-${notice.read_at ? 'success' : 'warning text-dark'}">${notice.read_at ? 'Read' : 'Unread'}</span></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="viewNotice(${notice.id})" 
                                            title="View Notice">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    ${!notice.is_global && notice.enrollee ? `
                                        <button type="button" class="btn btn-outline-info" 
                                                onclick="sendNoticeToApplicant('${notice.enrollee.application_id}')" 
                                                title="Send Another Notice">
                                            <i class="ri-mail-send-line"></i>
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

function getPriorityColor(priority) {
    switch(priority) {
        case 'urgent': return 'danger';
        case 'high': return 'warning';
        case 'normal': return 'secondary';
        default: return 'secondary';
    }
}

function getNoticeTypeColor(type) {
    switch(type) {
        case 'info': return 'info';
        case 'warning': return 'warning';
        case 'success': return 'success';
        case 'error': return 'danger';
        default: return 'primary';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
    console.log('sendNoticeToApplicant called with:', applicationId);
    console.log('Call stack:', new Error().stack);
    
    // Check if modal already exists and is visible
    const existingModal = document.getElementById('noticeModal');
    if (existingModal) {
        // If modal exists, just update the title and show it
        const titleElement = existingModal.querySelector('.modal-title');
        if (titleElement) {
            titleElement.textContent = `Send Notice to Applicant (${applicationId})`;
        }
        
        const modalInstance = bootstrap.Modal.getOrCreateInstance(existingModal);
        modalInstance.show();
        
        // Ensure event listeners are set up for reused modal
        setTimeout(() => {
            const subjectEl = document.getElementById('notice-subject');
            const messageEl = document.getElementById('simple-notice-message');
            const priorityEl = document.getElementById('notice-priority');
            
            // Initialize storage for form values if not exists
            if (!window.currentFormValues) {
                window.currentFormValues = {};
            }
            
            // Only add listeners if they don't exist (prevent duplicates)
            if (subjectEl && !subjectEl.hasAttribute('data-listener-added')) {
                subjectEl.addEventListener('input', function() {
                    window.currentFormValues.subject = this.value;
                    console.log('Subject updated:', this.value);
                });
                subjectEl.setAttribute('data-listener-added', 'true');
            }
            
            if (messageEl && !messageEl.hasAttribute('data-listener-added')) {
                messageEl.addEventListener('input', function() {
                    window.currentFormValues.message = this.value;
                    console.log('Message updated:', this.value);
                });
                messageEl.setAttribute('data-listener-added', 'true');
            }
            
            if (priorityEl && !priorityEl.hasAttribute('data-listener-added')) {
                priorityEl.addEventListener('change', function() {
                    window.currentFormValues.priority = this.value;
                    console.log('Priority updated:', this.value);
                });
                priorityEl.setAttribute('data-listener-added', 'true');
            }
            
            console.log('Event listeners ensured for reused modal');
        }, 100);
        
        console.log('Reusing existing modal');
        return; // Don't recreate, just reuse
    }
    
    currentApplicationId = applicationId;
    
    // Store existing form values if modal exists
    let existingValues = {};
    if (existingModal) {
        const subjectEl = document.getElementById('notice-subject');
        const messageEl = document.getElementById('simple-notice-message');
        const priorityEl = document.getElementById('notice-priority');
        
        existingValues = {
            subject: subjectEl?.value || '',
            message: messageEl?.value || '',
            priority: priorityEl?.value || 'normal'
        };
        
        console.log('Preserving existing form values:', existingValues);
    }
    
    // Create a simple notice modal
    const modalHtml = `
        <div class="modal fade" id="noticeModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Send Notice to Applicant (${applicationId})</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="notice-form">
                            <div class="mb-3">
                                <label for="notice-subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="notice-subject" value="${existingValues.subject || ''}" required>
                            </div>
                            <div class="mb-3">
                                <label for="simple-notice-message" class="form-label">Message</label>
                                <input type="text" class="form-control" id="simple-notice-message" value="${existingValues.message || ''}" required placeholder="Enter your notice message...">
                            </div>
                            <div class="mb-3">
                                <label for="notice-priority" class="form-label">Priority</label>
                                <select class="form-select" id="notice-priority" required>
                                    <option value="normal" ${(existingValues.priority || 'normal') === 'normal' ? 'selected' : ''}>Normal</option>
                                    <option value="high" ${existingValues.priority === 'high' ? 'selected' : ''}>High</option>
                                    <option value="urgent" ${existingValues.priority === 'urgent' ? 'selected' : ''}>Urgent</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="submit-notice-btn" onclick="submitNotice()">Send Notice</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('noticeModal'));
    
    // Add event listener to clear form when modal is hidden
    document.getElementById('noticeModal').addEventListener('hidden.bs.modal', function() {
        const subjectEl = document.getElementById('notice-subject');
        const messageEl = document.getElementById('simple-notice-message');
        const priorityEl = document.getElementById('notice-priority');
        
        if (subjectEl) subjectEl.value = '';
        if (messageEl) messageEl.value = '';
        if (priorityEl) priorityEl.value = 'normal';
        
        // Clear stored values
        window.currentFormValues = {};
        
        console.log('Modal closed, form cleared');
    }, { once: true }); // Only add this listener once
    
    modal.show();
    
    // Add real-time value tracking
    setTimeout(() => {
        const subjectEl = document.getElementById('notice-subject');
        const messageEl = document.getElementById('simple-notice-message');
        const priorityEl = document.getElementById('notice-priority');
        
        // Initialize storage for form values
        if (!window.currentFormValues) {
            window.currentFormValues = {};
        }
        
        // Track subject changes
        if (subjectEl) {
            subjectEl.addEventListener('input', function() {
                window.currentFormValues.subject = this.value;
                console.log('Subject updated:', this.value);
            });
        }
        
        // Track message changes (now using input field)
        if (messageEl) {
            console.log('Setting up message event listeners on:', messageEl);
            console.log('Message element details:', {
                id: messageEl.id,
                tagName: messageEl.tagName,
                type: messageEl.type,
                value: messageEl.value,
                className: messageEl.className
            });
            
            // Test immediate value setting
            messageEl.value = 'TEST_IMMEDIATE';
            console.log('After setting test value:', messageEl.value);
            messageEl.value = '';
            
            messageEl.addEventListener('input', function() {
                window.currentFormValues.message = this.value;
                console.log('Message updated via input event:', this.value);
            });
            
            // Also try other events as backup
            messageEl.addEventListener('keyup', function() {
                console.log('Message keyup event:', this.value);
            });
            
            messageEl.addEventListener('change', function() {
                console.log('Message change event:', this.value);
            });
            
            // Test focus event
            messageEl.addEventListener('focus', function() {
                console.log('Message field focused');
            });
        }
        
        // Track priority changes
        if (priorityEl) {
            priorityEl.addEventListener('change', function() {
                window.currentFormValues.priority = this.value;
                console.log('Priority updated:', this.value);
            });
        }
        
        console.log('Real-time value tracking enabled');
    }, 100);
    
    // Ensure values are properly set after modal is shown
    setTimeout(() => {
        const newSubjectEl = document.getElementById('notice-subject');
        const newMessageEl = document.getElementById('simple-notice-message');
        const newPriorityEl = document.getElementById('notice-priority');
        
        if (existingValues.subject && newSubjectEl) {
            newSubjectEl.value = existingValues.subject;
        }
        if (existingValues.message && newMessageEl) {
            newMessageEl.value = existingValues.message;
        }
        if (existingValues.priority && newPriorityEl) {
            newPriorityEl.value = existingValues.priority;
        }
        
        console.log('Values set after modal creation:', {
            subject: newSubjectEl?.value,
            message: newMessageEl?.value,
            priority: newPriorityEl?.value
        });
    }, 100);
    
    console.log('Modal created and shown with preserved values');
}

// Test function to debug textarea issues
function testTextareaValue() {
    const messageEl = document.getElementById('simple-notice-message');
    if (messageEl) {
        console.log('Testing textarea manipulation:');
        console.log('Element found:', messageEl);
        console.log('Before setting:', messageEl.value);
        
        messageEl.value = 'TEST VALUE';
        console.log('After setting via .value:', messageEl.value);
        
        messageEl.textContent = 'TEST CONTENT';
        console.log('After setting via .textContent:', messageEl.value, messageEl.textContent);
        
        messageEl.innerHTML = 'TEST HTML';
        console.log('After setting via .innerHTML:', messageEl.value, messageEl.innerHTML);
        
        // Test if we can focus and type
        messageEl.focus();
        console.log('Focused on textarea');
        
        // Simulate typing
        messageEl.value = 'SIMULATED TYPING';
        messageEl.dispatchEvent(new Event('input', { bubbles: true }));
        console.log('After simulated typing:', messageEl.value);
        
    } else {
        console.log('Input field with id "simple-notice-message" not found!');
        
        // Check if any textarea exists
        const allTextareas = document.querySelectorAll('textarea');
        console.log('All textareas found:', allTextareas);
        
        // Check if modal exists
        const modal = document.getElementById('noticeModal');
        console.log('Modal found:', modal);
        if (modal) {
            console.log('Modal HTML:', modal.outerHTML);
        }
    }
}

// Check current form state
function checkFormState() {
    console.log('=== FORM STATE CHECK ===');
    
    const modal = document.getElementById('noticeModal');
    console.log('Modal exists:', !!modal);
    
    const subjectEl = document.getElementById('notice-subject');
    const messageEl = document.getElementById('simple-notice-message');
    const priorityEl = document.getElementById('notice-priority');
    
    console.log('Form elements:', {
        subject: !!subjectEl,
        message: !!messageEl,
        priority: !!priorityEl
    });
    
    if (subjectEl) console.log('Subject value:', `"${subjectEl.value}"`);
    if (messageEl) console.log('Message value:', `"${messageEl.value}"`);
    if (priorityEl) console.log('Priority value:', `"${priorityEl.value}"`);
    
    console.log('Tracked values:', window.currentFormValues);
    console.log('Current application ID:', currentApplicationId);
    
    console.log('=== END FORM STATE ===');
}


// Submit notice
function submitNotice() {
    // Prevent multiple submissions
    const submitBtn = document.querySelector('#noticeModal .btn-primary');
    if (submitBtn && submitBtn.disabled) {
        console.log('Submit already in progress, ignoring...');
        return;
    }
    
    // Check if this is the simple notice modal (Send Notice to Applicant)
    const isSimpleModal = document.getElementById('notice-subject') !== null;
    
    let title, message, priority, type, recipients, specificApplicant;
    
    if (isSimpleModal) {
        // Simple notice modal fields
        const subjectElement = document.getElementById('notice-subject');
        const messageElement = document.getElementById('simple-notice-message');
        const priorityElement = document.getElementById('notice-priority');
        
        console.log('Form elements found:', {
            subjectElement: !!subjectElement,
            messageElement: !!messageElement,
            priorityElement: !!priorityElement
        });
        
        // Manually capture current values right before validation
        const currentSubject = subjectElement?.value?.trim() || '';
        const currentMessage = messageElement?.value?.trim() || '';
        const currentPriority = priorityElement?.value?.trim() || 'normal';
        
        console.log('Current DOM values at submit time:', {
            subject: currentSubject,
            message: currentMessage,
            priority: currentPriority
        });
        
        // Force update tracked values with current DOM values
        if (!window.currentFormValues) {
            window.currentFormValues = {};
        }
        
        // Always use the most current DOM values
        window.currentFormValues.subject = currentSubject;
        window.currentFormValues.message = currentMessage;
        window.currentFormValues.priority = currentPriority;
        
        title = currentSubject;
        message = currentMessage;
        priority = currentPriority;
        
        console.log('Using current DOM values (forced sync):', {
            subject: title,
            message: message,
            priority: priority
        });
        
        // Additional debugging for message field
        console.log('Message element details:', {
            element: messageElement,
            value: messageElement?.value,
            innerHTML: messageElement?.innerHTML,
            textContent: messageElement?.textContent,
            outerHTML: messageElement?.outerHTML
        });
        
        // Try alternative methods to get textarea value
        if (messageElement) {
            console.log('Alternative value retrieval methods:', {
                getAttribute: messageElement.getAttribute('value'),
                defaultValue: messageElement.defaultValue,
                innerText: messageElement.innerText,
                nodeValue: messageElement.nodeValue
            });
        }
        
        // Set default values for simple modal
        type = 'info';
        recipients = 'specific';
        specificApplicant = currentApplicationId;
        
        console.log('Simple modal values:', { 
            title: `"${title}"`, 
            message: `"${message}"`, 
            priority: `"${priority}"`, 
            specificApplicant 
        });
        
        if (!title || !message || !priority) {
            console.log('Validation failed - missing fields:', {
                titleEmpty: !title,
                messageEmpty: !message,
                priorityEmpty: !priority
            });
            
            // Show specific field that's missing
            let missingFields = [];
            if (!title) missingFields.push('Subject');
            if (!message) missingFields.push('Message');
            if (!priority) missingFields.push('Priority');
            
            showAlert(`Please fill in the following required fields: ${missingFields.join(', ')}`, 'error');
            
            // Focus on the first missing field
            if (!title && subjectElement) {
                subjectElement.focus();
            } else if (!message && messageElement) {
                messageElement.focus();
            } else if (!priority && priorityElement) {
                priorityElement.focus();
            }
            
            return;
        }
        
        if (!specificApplicant) {
            console.log('Validation failed - no application ID:', {
                currentApplicationId,
                specificApplicant
            });
            showAlert('No application selected', 'error');
            return;
        }
        
        // Set loading state for submit button
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Sending...';
        }
    } else {
        // Comprehensive create notice modal fields
        title = document.getElementById('notice-title')?.value;
        message = document.getElementById('notice-message')?.value;
        priority = document.getElementById('notice-priority')?.value;
        type = document.getElementById('notice-type')?.value;
        recipients = document.getElementById('notice-recipients')?.value;
        specificApplicant = document.getElementById('specific-applicant')?.value;
        
        if (!title || !message || !priority || !type || !recipients) {
            showAlert('Please fill in all required fields', 'error');
            return;
        }
        
        // Validate specific applicant selection
        if (recipients === 'specific' && !specificApplicant) {
            showAlert('Please select a specific applicant', 'error');
            return;
        }
    }
    
    showLoading();
    
    let url, method, requestData;
    
    if (isSimpleModal) {
        // Simple notice modal - send to specific applicant
        url = `/registrar/applications/${specificApplicant}/notice`;
        method = 'POST';
        requestData = {
            subject: title,
            message: message,
            priority: priority
        };
        console.log('Simple modal request:', { url, method, requestData });
    } else {
        // Complex notice modal - use the original logic
        requestData = {
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
        url = isUpdate ? `/registrar/notices/${window.currentNoticeId}/update` : '/registrar/notices/create';
        method = isUpdate ? 'PUT' : 'POST';
    }
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken || '';
    console.log('CSRF Token:', csrfToken ? 'Found' : 'Not found');
    
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Notice sent successfully', 'success');
            
            if (isSimpleModal) {
                // Close simple notice modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('noticeModal'));
                if (modal) modal.hide();
            } else {
                // Close complex notice modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('createNoticeModal'));
                if (modal) modal.hide();
                
                // Clear current notice ID
                window.currentNoticeId = null;
            }
            
            // Refresh notices data if on notices tab
            if (document.querySelector('.nav-link[data-bs-target="#notices-tab"]')?.classList.contains('active')) {
                loadNoticesData();
            }
        } else {
            showAlert(data.message || 'Failed to send notice', 'error');
        }
    })
    .catch(error => {
        console.error(`Error ${isUpdate ? 'updating' : 'sending'} notice:`, error);
        showAlert(`Failed to ${isUpdate ? 'update' : 'send'} notice`, 'error');
    })
    .finally(() => {
        hideLoading();
        
        // Restore submit button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Send Notice';
        }
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

// Bulk send notice to applications
function bulkSendNotice() {
    if (selectedApplications.length === 0) {
        showAlert('Please select applications to send notices to.', 'warning');
        return;
    }
    
    // Create a simple bulk notice modal
    const modalHtml = `
        <div class="modal fade" id="bulkNoticeModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Send Notice to Selected Students</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            Sending notice to <strong>${selectedApplications.length}</strong> selected student(s)
                        </div>
                        <form id="bulk-notice-form">
                            <div class="mb-3">
                                <label for="bulk-notice-subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="bulk-notice-subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="bulk-notice-message" class="form-label">Message</label>
                                <textarea class="form-control" id="bulk-notice-message" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="bulk-notice-priority" class="form-label">Priority</label>
                                <select class="form-select" id="bulk-notice-priority" required>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="submitBulkNotice()">Send Notices</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('bulkNoticeModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('bulkNoticeModal'));
    modal.show();
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

// Open student selection modal for notices
function openStudentSelectionModal() {
    // Create student selection modal
    const modalHtml = `
        <div class="modal fade" id="studentSelectionModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ri-user-search-line me-2"></i>Select Student for Notice
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="student-search" class="form-label">Search Student</label>
                            <input type="text" class="form-control" id="student-search" placeholder="Search by name or application ID...">
                        </div>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Application ID</th>
                                        <th>Student Name</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="students-list">
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="text-muted mt-2">Loading students...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('studentSelectionModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('studentSelectionModal'));
    modal.show();
    
    // Load students data
    loadStudentsForSelection();
    
    // Setup search functionality
    const searchInput = document.getElementById('student-search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterStudentsList(this.value);
            }, 300);
        });
    }
}

// Load students for selection
function loadStudentsForSelection() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    fetch('/registrar/applications/data', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.applications) {
            displayStudentsList(data.applications);
        } else {
            showStudentsError('Failed to load students');
        }
    })
    .catch(error => {
        console.error('Error loading students:', error);
        showStudentsError('Failed to load students');
    });
}

// Display students list
function displayStudentsList(applications) {
    const tbody = document.getElementById('students-list');
    if (!tbody) return;
    
    if (!applications || applications.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4">
                    <i class="ri-user-line fs-2 text-muted"></i>
                    <p class="text-muted mt-2">No students found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    const rows = applications.map(app => {
        const statusClass = {
            'pending': 'bg-warning text-dark',
            'approved': 'bg-success',
            'declined': 'bg-danger',
            'rejected': 'bg-danger'
        }[app.enrollment_status] || 'bg-secondary';
        
        return `
            <tr data-student-id="${app.application_id}" data-student-name="${app.first_name} ${app.last_name}">
                <td>${app.application_id}</td>
                <td>
                    <div class="fw-medium">${app.first_name} ${app.last_name}</div>
                    <small class="text-muted">${app.email || ''}</small>
                </td>
                <td>
                    <span class="badge ${statusClass}">
                        ${app.enrollment_status ? app.enrollment_status.charAt(0).toUpperCase() + app.enrollment_status.slice(1) : 'Unknown'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="selectStudentForNotice('${app.application_id}', '${app.first_name} ${app.last_name}')">
                        <i class="ri-mail-send-line me-1"></i>Send Notice
                    </button>
                </td>
            </tr>
        `;
    }).join('');
    
    tbody.innerHTML = rows;
}

// Filter students list
function filterStudentsList(searchTerm) {
    const rows = document.querySelectorAll('#students-list tr[data-student-id]');
    const term = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        const studentId = row.getAttribute('data-student-id').toLowerCase();
        const studentName = row.getAttribute('data-student-name').toLowerCase();
        
        if (studentId.includes(term) || studentName.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Select student for notice
function selectStudentForNotice(applicationId, studentName) {
    // Close student selection modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('studentSelectionModal'));
    if (modal) modal.hide();
    
    // Open notice modal for selected student
    sendNoticeToApplicant(applicationId);
}

// Show error in students list
function showStudentsError(message) {
    const tbody = document.getElementById('students-list');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4">
                    <i class="ri-error-warning-line fs-2 text-danger"></i>
                    <p class="text-danger mt-2">${message}</p>
                    <button class="btn btn-outline-primary btn-sm" onclick="loadStudentsForSelection()">
                        <i class="ri-refresh-line me-1"></i>Retry
                    </button>
                </td>
            </tr>
        `;
    }
}

// Submit bulk notice
function submitBulkNotice() {
    const subject = document.getElementById('bulk-notice-subject')?.value;
    const message = document.getElementById('bulk-notice-message')?.value;
    const priority = document.getElementById('bulk-notice-priority')?.value;
    
    if (!subject || !message || !priority) {
        showAlert('Please fill in all required fields', 'error');
        return;
    }
    
    if (selectedApplications.length === 0) {
        showAlert('No applications selected', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('#bulkNoticeModal .btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Sending...';
    submitBtn.disabled = true;
    
    fetch('/registrar/notices/bulk', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken || '',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            application_ids: selectedApplications,
            title: subject,
            message: message,
            priority: priority,
            type: 'info'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Successfully sent notice to ${selectedApplications.length} student(s)`, 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('bulkNoticeModal'));
            if (modal) modal.hide();
            
            // Clear selections
            clearAllSelections();
        } else {
            showAlert(data.message || 'Failed to send notices', 'error');
        }
    })
    .catch(error => {
        console.error('Error sending bulk notice:', error);
        showAlert('Failed to send notices. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
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

// View notice
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
            
            // Populate modal with null checks
            const titleEl = document.getElementById('view-notice-title');
            const typeEl = document.getElementById('view-notice-type');
            const priorityEl = document.getElementById('view-notice-priority');
            const dateEl = document.getElementById('view-notice-date');
            const statusEl = document.getElementById('view-notice-status');
            const recipientEl = document.getElementById('view-notice-recipient');
            const messageEl = document.getElementById('view-notice-message');
            
            if (titleEl) titleEl.textContent = notice.title || 'N/A';
            if (typeEl) {
                typeEl.textContent = notice.type || 'N/A';
                typeEl.className = `badge bg-${getNoticeTypeColor(notice.type)}`;
            }
            if (priorityEl) {
                priorityEl.textContent = notice.priority || 'N/A';
                priorityEl.className = `badge bg-${getPriorityColor(notice.priority)}`;
            }
            if (dateEl) dateEl.textContent = notice.created_at || 'N/A';
            if (statusEl) {
                statusEl.textContent = notice.read_at ? 'Read' : 'Unread';
                statusEl.className = `badge bg-${notice.read_at ? 'success' : 'warning'}`;
            }
            if (recipientEl) recipientEl.textContent = notice.is_global ? 'All Applicants' : (notice.enrollee ? `${notice.enrollee.full_name} (${notice.enrollee.application_id})` : 'Unknown');
            if (messageEl) messageEl.textContent = notice.message || 'N/A';
            
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


// Handle tab switching based on URL parameters
function handleTabSwitching() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'pending';
    
    // Activate the correct tab
    const tabButton = document.querySelector(`[data-bs-target="#${activeTab}"]`);
    const tabContent = document.getElementById(activeTab);
    
    if (tabButton && tabContent) {
        // Remove active classes from all tabs
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active', 'show'));
        
        // Add active classes to current tab
        tabButton.classList.add('active');
        tabContent.classList.add('active', 'show');
    }
    
    // Add event listeners to tabs to update URL
    document.querySelectorAll('.nav-link[data-bs-target]').forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-bs-target').replace('#', '');
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tab', targetTab);
            window.history.pushState({}, '', newUrl);
        });
    });
}

// Approve appointment function
function approveAppointment(appointmentId) {
    if (!confirm('Are you sure you want to approve this appointment?')) {
        return;
    }
    
    fetch(`/registrar/appointments/${appointmentId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Appointment approved successfully', 'success');
            loadApplicationsData(); // Refresh data
        } else {
            showAlert(data.message || 'Failed to approve appointment', 'error');
        }
    })
    .catch(error => {
        console.error('Error approving appointment:', error);
        showAlert('Failed to approve appointment', 'error');
    });
}

// Reject appointment function
function rejectAppointment(appointmentId) {
    if (!confirm('Are you sure you want to reject this appointment?')) {
        return;
    }
    
    fetch(`/registrar/appointments/${appointmentId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Appointment rejected successfully', 'success');
            loadApplicationsData(); // Refresh data
        } else {
            showAlert(data.message || 'Failed to reject appointment', 'error');
        }
    })
    .catch(error => {
        console.error('Error rejecting appointment:', error);
        showAlert('Failed to reject appointment', 'error');
    });
}




// Update applications count in tab badges
function updateApplicationsCount(counts) {
    if (!counts) return;
    
    Object.keys(counts).forEach(tab => {
        const badge = document.querySelector(`[data-bs-target="#${tab}"] .badge`);
        if (badge) {
            badge.textContent = counts[tab];
        }
    });
}


// Global function assignments for onclick handlers
window.viewApplication = viewApplication;
window.approveApplication = approveApplication;
window.declineApplication = declineApplication;
window.sendNoticeToApplicant = sendNoticeToApplicant;
window.approveAppointment = approveAppointment;
window.rejectAppointment = rejectAppointment;
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
window.bulkApprove = bulkApprove;
window.bulkDecline = bulkDecline;
window.bulkSendNotice = bulkSendNotice;
window.submitBulkNotice = submitBulkNotice;
window.openStudentSelectionModal = openStudentSelectionModal;
window.loadStudentsForSelection = loadStudentsForSelection;
window.selectStudentForNotice = selectStudentForNotice;
window.testTextareaValue = testTextareaValue;
window.checkFormState = checkFormState;
window.bulkDelete = bulkDelete;
window.exportSelected = exportSelected;
window.clearAllSelections = clearAllSelections;
window.refreshData = refreshData;
window.exportData = exportData;
window.clearFilters = clearFilters;
window.confirmBulkAction = confirmBulkAction;
window.approveApplicationFromModal = approveApplicationFromModal;
window.declineApplicationFromModal = declineApplicationFromModal;
window.confirmDecline = confirmDecline;

// Load appointments data (placeholder function)
function loadAppointmentsData() {
    console.log('Loading appointments data...');
    // This function should load appointments data if needed
    // For now, it's a placeholder to prevent errors
}

// Additional utility functions
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
window.getPriorityColor = getPriorityColor;
window.viewDocumentInTab = viewDocumentInTab;
window.approveDocumentInTab = approveDocumentInTab;
window.rejectDocumentInTab = rejectDocumentInTab;
window.formatDate = formatDate;
window.updateDocumentStatusInTab = updateDocumentStatusInTab;
window.setupDocumentFilters = setupDocumentFilters;
window.handleTabSwitching = handleTabSwitching;
window.loadApplicationsData = loadApplicationsData;
window.updateApplicationsTable = updateApplicationsTable;
window.createApplicationRow = createApplicationRow;
window.updateApplicationsCount = updateApplicationsCount;
window.processApplicationAction = processApplicationAction;
