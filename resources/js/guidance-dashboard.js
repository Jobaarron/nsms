// Guidance Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Guidance Dashboard loaded');
    
    // Initialize dashboard
    initializeDashboard();
    
    // Load initial data
    loadRecentActivities();
    loadTodaysSchedule();
});

// Initialize dashboard functionality
function initializeDashboard() {
    // Set up auto-refresh every 5 minutes
    setInterval(function() {
        loadRecentActivities();
        loadTodaysSchedule();
    }, 300000); // 5 minutes
}

// Load recent activities
function loadRecentActivities() {
    const activitiesContainer = document.getElementById('activities-list');
    
    fetch('/guidance/activities', {
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
            populateActivitiesList(data.activities);
        } else {
            showNoActivities();
        }
    })
    .catch(error => {
        console.error('Error loading activities:', error);
        showActivitiesError();
    });
}

// Populate activities list
function populateActivitiesList(activities) {
    const container = document.getElementById('activities-list');
    
    if (!activities || activities.length === 0) {
        showNoActivities();
        return;
    }
    
    let html = '';
    activities.forEach(activity => {
        html += `
            <div class="activity-item d-flex align-items-start mb-3 p-3 border rounded">
                <div class="activity-icon me-3">
                    <div class="rounded-circle bg-${activity.type_color} bg-opacity-10 p-2">
                        <i class="${activity.icon} text-${activity.type_color}"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${activity.title}</h6>
                            <p class="text-muted mb-1">${activity.description}</p>
                            <small class="text-muted">
                                <i class="ri-time-line me-1"></i>${activity.time_ago}
                            </small>
                        </div>
                        <span class="badge bg-${activity.status_color}">${activity.status}</span>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Show no activities message
function showNoActivities() {
    const container = document.getElementById('activities-list');
    container.innerHTML = `
        <div class="text-center py-4">
            <i class="ri-calendar-line fs-1 text-muted mb-3"></i>
            <p class="text-muted">No recent activities</p>
            <button class="btn btn-primary btn-sm" onclick="scheduleNewCaseMeeting()">
                Schedule First Activity
            </button>
        </div>
    `;
}

// Show activities error
function showActivitiesError() {
    const container = document.getElementById('activities-list');
    container.innerHTML = `
        <div class="text-center py-4">
            <i class="ri-error-warning-line fs-1 text-danger mb-3"></i>
            <p class="text-muted">Failed to load activities</p>
            <button class="btn btn-outline-primary btn-sm" onclick="loadRecentActivities()">
                Try Again
            </button>
        </div>
    `;
}

// Load today's schedule
function loadTodaysSchedule() {
    const scheduleContainer = document.getElementById('todays-schedule');
    
    fetch('/guidance/todays-schedule', {
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
            populateTodaysSchedule(data.schedule);
        } else {
            showNoSchedule();
        }
    })
    .catch(error => {
        console.error('Error loading schedule:', error);
        showScheduleError();
    });
}

// Populate today's schedule
function populateTodaysSchedule(schedule) {
    const container = document.getElementById('todays-schedule');
    
    if (!schedule || schedule.length === 0) {
        showNoSchedule();
        return;
    }
    
    let html = '';
    schedule.forEach(item => {
        html += `
            <div class="schedule-item d-flex align-items-center mb-3 p-2 border-start border-3 border-${item.type_color}">
                <div class="me-3">
                    <div class="text-${item.type_color} fw-bold">${item.time}</div>
                    <small class="text-muted">${item.duration}</small>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">${item.title}</div>
                    <small class="text-muted">${item.student_name}</small>
                </div>
                <div>
                    <span class="badge bg-${item.status_color} bg-opacity-10 text-${item.status_color}">
                        ${item.status}
                    </span>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Show no schedule message
function showNoSchedule() {
    const container = document.getElementById('todays-schedule');
    container.innerHTML = `
        <div class="text-center py-3">
            <i class="ri-calendar-check-line fs-3 text-muted mb-2"></i>
            <p class="text-muted small">No appointments today</p>
        </div>
    `;
}

// Show schedule error
function showScheduleError() {
    const container = document.getElementById('todays-schedule');
    container.innerHTML = `
        <div class="text-center py-3">
            <i class="ri-error-warning-line fs-3 text-danger mb-2"></i>
            <p class="text-muted small">Failed to load schedule</p>
            <button class="btn btn-outline-primary btn-sm" onclick="loadTodaysSchedule()">
                Retry
            </button>
        </div>
    `;
}

// Filter activities
function filterActivities(type) {
    console.log('Filtering activities by type:', type);
    
    fetch(`/guidance/activities?type=${type}`, {
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
            populateActivitiesList(data.activities);
        }
    })
    .catch(error => {
        console.error('Error filtering activities:', error);
    });
}

// Refresh dashboard
function refreshDashboard() {
    console.log('Refreshing dashboard...');
    loadRecentActivities();
    loadTodaysSchedule();
    showAlert('Dashboard refreshed', 'success');
}

// Quick action functions
function scheduleNewCaseMeeting() {
    window.location.href = '/guidance/case-meetings';
}

function scheduleNewCounseling() {
    window.location.href = '/guidance/counseling-sessions';
}

function scheduleHouseVisit() {
    window.location.href = '/guidance/case-meetings?type=house_visit';
}

function createCaseSummary() {
    window.location.href = '/guidance/case-meetings?action=summary';
}

// Open quick action modal
function openQuickActionModal() {
    const modal = new bootstrap.Modal(document.getElementById('quickActionModal'));
    modal.show();
}

// Close modal helper
function closeModal(modalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
    if (modal) {
        modal.hide();
    }
}

// Show alert helper
function showAlert(message, type = 'info') {
    // Create alert element
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="ri-information-line me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Find or create alert container
    let container = document.getElementById('alert-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'alert-container';
        container.className = 'position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    // Add alert
    container.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        const alerts = container.querySelectorAll('.alert');
        if (alerts.length > 0) {
            alerts[0].remove();
        }
    }, 5000);
}

// Expose functions to global scope for onclick handlers
window.refreshDashboard = refreshDashboard;
window.openQuickActionModal = openQuickActionModal;
window.scheduleNewCaseMeeting = scheduleNewCaseMeeting;
window.scheduleNewCounseling = scheduleNewCounseling;
window.scheduleHouseVisit = scheduleHouseVisit;
window.createCaseSummary = createCaseSummary;
window.filterActivities = filterActivities;
window.closeModal = closeModal;
