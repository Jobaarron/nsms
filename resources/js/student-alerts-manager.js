/**
 * Student Alerts Manager
 * Pure JavaScript alert system using localStorage
 * Shows red badges on sidebar for: Payments (due soon), Grades (new), Violations (new)
 * Production-ready with error handling and fallbacks
 */

// Check if localStorage is available
window.localStorageAvailable = (function() {
    try {
        const test = '__localStorage_test__';
        localStorage.setItem(test, test);
        localStorage.removeItem(test);
        return true;
    } catch(e) {
        return false;
    }
})();

document.addEventListener('DOMContentLoaded', function() {
    try {
        initializeStudentAlerts();
        startRealTimeAlertPolling();
    } catch(error) {
    }
});

function initializeStudentAlerts() {
    
    const currentPath = window.location.pathname;
    
    // Check which page we're on and update alerts
    if (currentPath.includes('/student/payments')) {
        checkPaymentAlerts();
        clearAlertBadge('payments');
    } else if (currentPath.includes('/student/grades')) {
        checkGradeAlerts();
        clearAlertBadge('grades');
    } else if (currentPath.includes('/student/violations')) {
        checkViolationAlerts();
        clearAlertBadge('violations');
    } else {
        displayStoredAlerts();
    }
}

// ============================================
// REAL-TIME POLLING
// ============================================
let pollingInterval = null;

function startRealTimeAlertPolling() {
    const POLL_INTERVAL = 3000; // 3 seconds
    
    
    // Initial check
    fetchAlertCounts();
    
    // Poll every 3 seconds
    pollingInterval = setInterval(() => {
        fetchAlertCounts();
    }, POLL_INTERVAL);
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
    });
}

async function fetchAlertCounts() {
    try {
        const response = await fetch('/student/alerts/counts', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            updateAlertBadges(data.counts);
        }
    } catch (error) {
    }
}

function updateAlertBadges(counts) {
    const currentPath = window.location.pathname;
    
    // Update each alert type
    Object.keys(counts).forEach(alertType => {
        const count = counts[alertType] || 0;
        
        // Don't show badge on the current page for that alert type
        if (currentPath.includes(`/student/${alertType}`)) {
            displayAlertBadge(alertType, 0);
            storeAlertState(alertType, 0);
        } else {
            displayAlertBadge(alertType, count);
            storeAlertState(alertType, count);
        }
    });
}

// ============================================
// PAYMENT ALERTS
// ============================================
function checkPaymentAlerts() {
    const paymentRows = document.querySelectorAll('[data-payment-schedule] table tbody tr');
    
    let upcomingPaymentsCount = 0;
    
    paymentRows.forEach((row, index) => {
        const scheduledDate = row.getAttribute('data-scheduled-date');
        const paymentStatus = row.getAttribute('data-payment-status');
        
        if (scheduledDate && paymentStatus === 'pending') {
            const dueDate = new Date(scheduledDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const daysUntilDue = Math.ceil((dueDate - today) / (1000 * 60 * 60 * 24));
            
            
            // Alert if due within 7 days
            if (daysUntilDue <= 7) {
                upcomingPaymentsCount++;
            }
        }
    });
    
    storeAlertState('payments', upcomingPaymentsCount);
    displayAlertBadge('payments', upcomingPaymentsCount);
}

// ============================================
// GRADE ALERTS
// ============================================
function checkGradeAlerts() {
    const gradeCards = document.querySelectorAll('[data-quarter]');
    
    let newGradesCount = 0;
    
    gradeCards.forEach((card, index) => {
        const timestamp = card.getAttribute('data-updated-at');
        
        if (timestamp) {
            const lastUpdate = new Date(timestamp);
            const now = new Date();
            const hoursSinceUpdate = (now - lastUpdate) / (1000 * 60 * 60);
            
            
            // Alert if updated within 24 hours
            if (hoursSinceUpdate < 24) {
                newGradesCount++;
            }
        }
    });
    
    storeAlertState('grades', newGradesCount);
    displayAlertBadge('grades', newGradesCount);
}

// ============================================
// VIOLATION ALERTS
// ============================================
function checkViolationAlerts() {
    const violationRows = document.querySelectorAll('[data-violation]');
    
    let newViolationsCount = 0;
    
    violationRows.forEach((row, index) => {
        const timestamp = row.getAttribute('data-created-at');
        
        if (timestamp) {
            const violationDate = new Date(timestamp);
            const now = new Date();
            const hoursSinceViolation = (now - violationDate) / (1000 * 60 * 60);
            
            
            // Alert if created within 24 hours
            if (hoursSinceViolation < 24) {
                newViolationsCount++;
            }
        }
    });
    
    storeAlertState('violations', newViolationsCount);
    displayAlertBadge('violations', newViolationsCount);
}

// ============================================
// ALERT STATE MANAGEMENT
// ============================================
function storeAlertState(alertType, count) {
    if (!window.localStorageAvailable) {
        return;
    }
    
    try {
        const state = {
            count: count,
            timestamp: new Date().toISOString(),
            hasAlert: count > 0
        };
        localStorage.setItem(`student_alert_${alertType}`, JSON.stringify(state));
    } catch(error) {
    }
}

function getAlertState(alertType) {
    if (!window.localStorageAvailable) {
        return null;
    }
    
    try {
        const stored = localStorage.getItem(`student_alert_${alertType}`);
        if (stored) {
            return JSON.parse(stored);
        }
    } catch (error) {
    }
    return null;
}

function clearAlertBadge(alertType) {
    
    if (window.localStorageAvailable) {
        try {
            localStorage.removeItem(`student_alert_${alertType}`);
        } catch(error) {
        }
    }
    
    const badge = document.getElementById(`${alertType}-alert-badge`);
    if (badge) {
        badge.classList.add('d-none');
        badge.textContent = '0';
    }
}

// ============================================
// BADGE DISPLAY
// ============================================
function displayAlertBadge(alertType, count) {
    const badge = document.getElementById(`${alertType}-alert-badge`);
    const link = document.querySelector(`a[data-alert-link="${alertType}"]`);
    
    if (!badge) {
        return;
    }
    
    if (count > 0) {
        badge.textContent = count;
        badge.classList.remove('d-none');
        
        // Add red highlight to sidebar link
        if (link) {
            link.style.backgroundColor = '#f8d7da';
            link.style.borderLeft = '4px solid #dc3545';
            link.style.paddingLeft = 'calc(0.75rem - 4px)';
        }
    } else {
        badge.classList.add('d-none');
        
        // Remove red highlight from sidebar link
        if (link) {
            link.style.backgroundColor = '';
            link.style.borderLeft = '';
            link.style.paddingLeft = '';
        }
    }
}

function displayStoredAlerts() {
    
    const alertTypes = ['payments', 'grades', 'violations'];
    
    alertTypes.forEach(alertType => {
        const state = getAlertState(alertType);
        
        if (state && state.hasAlert && state.count > 0) {
            displayAlertBadge(alertType, state.count);
        } else {
            displayAlertBadge(alertType, 0);
        }
    });
}

// ============================================
// HIGHLIGHT MANAGEMENT
// ============================================
function highlightSidebarLink(alertType) {
    const link = document.querySelector(`a[data-alert-link="${alertType}"]`);
    if (link) {
        link.style.backgroundColor = '#f8d7da';
        link.style.borderLeft = '4px solid #dc3545';
        link.style.paddingLeft = 'calc(0.75rem - 4px)';
    }
}

function removeHighlightFromSidebarLink(alertType) {
    const link = document.querySelector(`a[data-alert-link="${alertType}"]`);
    if (link) {
        link.style.backgroundColor = '';
        link.style.borderLeft = '';
        link.style.paddingLeft = '';
    }
}

// ============================================
// GLOBAL FUNCTIONS
// ============================================
window.getStudentAlertState = getAlertState;
window.clearStudentAlert = clearAlertBadge;
window.displayStudentAlerts = displayStoredAlerts;
window.highlightSidebarLink = highlightSidebarLink;
window.removeHighlightFromSidebarLink = removeHighlightFromSidebarLink;
