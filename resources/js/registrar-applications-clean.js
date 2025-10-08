// Registrar Applications Management JavaScript - Clean Version
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
    
    // Setup bulk selection
    setupBulkSelection();
    
    // Setup refresh functionality
    setupRefresh();
    
    console.log('System initialized successfully');
}

// Setup event listeners
function setupEventListeners() {
    console.log('Setting up event listeners...');
    
    // Setup modal event listeners
    setupModalEventListeners();
    
    // Setup form submissions
    setupFormSubmissions();
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
        
        if (data && data.appointments && data.appointments.length > 0) {
            if (contentDiv) contentDiv.style.display = 'block';
            // Handle appointments data here
        } else {
            console.log('No appointments found or empty response');
            if (emptyDiv) emptyDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error loading appointments:', error);
        if (loadingDiv) loadingDiv.style.display = 'none';
        if (emptyDiv) emptyDiv.style.display = 'block';
    });
}

// Load notices data
function loadNoticesData() {
    console.log('Loading notices data...');
    // Implementation for loading notices
}

// Application management functions
function viewApplication(id) {
    console.log('View application:', id);
    currentApplicationId = id;
    // Implementation for viewing application
}

function approveApplication(id) {
    console.log('Approve application:', id);
    // Implementation for approving application
}

function declineApplication(id) {
    console.log('Decline application:', id);
    // Implementation for declining application
}

function sendNoticeToApplicant(id) {
    console.log('Send notice to applicant:', id);
    // Implementation for sending notice
}

// Document management functions
function viewDocumentInTab(applicationId, index) {
    console.log('View document:', applicationId, index);
    // Implementation for viewing document
}

function approveDocumentInTab(applicationId, index) {
    console.log('Approve document:', applicationId, index);
    // Implementation for approving document
}

function rejectDocumentInTab(applicationId, index) {
    console.log('Reject document:', applicationId, index);
    // Implementation for rejecting document
}

// Notice management functions
function viewNotice(id) {
    console.log('View notice:', id);
    // Implementation for viewing notice
}

// Setup functions
function setupBulkSelection() {
    // Implementation for bulk selection
}

function setupRefresh() {
    const refreshBtn = document.querySelector('[onclick="refreshData()"]');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function(e) {
            e.preventDefault();
            refreshData();
        });
    }
}

function setupModalEventListeners() {
    // Implementation for modal event listeners
}

function setupFormSubmissions() {
    // Implementation for form submissions
}

function setupFilters() {
    // Implementation for filters
}

function initializeModals() {
    // Implementation for modal initialization
}

// Utility functions
function refreshData() {
    console.log('Refreshing data...');
    window.location.reload();
}

function showAlert(message, type = 'info') {
    console.log(`Alert [${type}]:`, message);
    // Implementation for showing alerts
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

console.log('Registrar Applications JavaScript: All functions loaded');
