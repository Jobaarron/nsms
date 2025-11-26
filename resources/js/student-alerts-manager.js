/**
 * Student Alerts Manager
 * Pure JavaScript alert system using localStorage
 * Shows red badges on sidebar for: Payments (due soon), Grades (new), Violations (new)
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeStudentAlerts();
});

function initializeStudentAlerts() {
    console.log('🔔 Student Alerts Manager initialized');
    
    const currentPath = window.location.pathname;
    
    // Check which page we're on and update alerts
    if (currentPath.includes('/student/payments')) {
        console.log('📍 On payments page - checking for due payments');
        checkPaymentAlerts();
        clearAlertBadge('payments');
    } else if (currentPath.includes('/student/grades')) {
        console.log('📍 On grades page - checking for new grades');
        checkGradeAlerts();
        clearAlertBadge('grades');
    } else if (currentPath.includes('/student/violations')) {
        console.log('📍 On violations page - checking for new violations');
        checkViolationAlerts();
        clearAlertBadge('violations');
    } else {
        console.log('📍 On other page - displaying stored alerts');
        displayStoredAlerts();
    }
}

// ============================================
// PAYMENT ALERTS
// ============================================
function checkPaymentAlerts() {
    const paymentRows = document.querySelectorAll('[data-payment-schedule] table tbody tr');
    console.log(`Found ${paymentRows.length} payment rows`);
    
    let upcomingPaymentsCount = 0;
    
    paymentRows.forEach((row, index) => {
        const scheduledDate = row.getAttribute('data-scheduled-date');
        const paymentStatus = row.getAttribute('data-payment-status');
        
        if (scheduledDate && paymentStatus === 'pending') {
            const dueDate = new Date(scheduledDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const daysUntilDue = Math.ceil((dueDate - today) / (1000 * 60 * 60 * 24));
            
            console.log(`Payment ${index + 1}: Due in ${daysUntilDue} days (${scheduledDate})`);
            
            // Alert if due within 7 days
            if (daysUntilDue <= 7) {
                upcomingPaymentsCount++;
            }
        }
    });
    
    console.log(`Upcoming payments (due within 7 days): ${upcomingPaymentsCount}`);
    storeAlertState('payments', upcomingPaymentsCount);
    displayAlertBadge('payments', upcomingPaymentsCount);
}

// ============================================
// GRADE ALERTS
// ============================================
function checkGradeAlerts() {
    const gradeCards = document.querySelectorAll('[data-quarter]');
    console.log(`Found ${gradeCards.length} grade cards`);
    
    let newGradesCount = 0;
    
    gradeCards.forEach((card, index) => {
        const timestamp = card.getAttribute('data-updated-at');
        
        if (timestamp) {
            const lastUpdate = new Date(timestamp);
            const now = new Date();
            const hoursSinceUpdate = (now - lastUpdate) / (1000 * 60 * 60);
            
            console.log(`Grade card ${index + 1}: Updated ${hoursSinceUpdate.toFixed(1)} hours ago`);
            
            // Alert if updated within 24 hours
            if (hoursSinceUpdate < 24) {
                newGradesCount++;
            }
        }
    });
    
    console.log(`New grades (updated within 24 hours): ${newGradesCount}`);
    storeAlertState('grades', newGradesCount);
    displayAlertBadge('grades', newGradesCount);
}

// ============================================
// VIOLATION ALERTS
// ============================================
function checkViolationAlerts() {
    const violationRows = document.querySelectorAll('[data-violation]');
    console.log(`Found ${violationRows.length} violation records`);
    
    let newViolationsCount = 0;
    
    violationRows.forEach((row, index) => {
        const timestamp = row.getAttribute('data-created-at');
        
        if (timestamp) {
            const violationDate = new Date(timestamp);
            const now = new Date();
            const hoursSinceViolation = (now - violationDate) / (1000 * 60 * 60);
            
            console.log(`Violation ${index + 1}: Created ${hoursSinceViolation.toFixed(1)} hours ago`);
            
            // Alert if created within 24 hours
            if (hoursSinceViolation < 24) {
                newViolationsCount++;
            }
        }
    });
    
    console.log(`New violations (created within 24 hours): ${newViolationsCount}`);
    storeAlertState('violations', newViolationsCount);
    displayAlertBadge('violations', newViolationsCount);
}

// ============================================
// ALERT STATE MANAGEMENT
// ============================================
function storeAlertState(alertType, count) {
    const state = {
        count: count,
        timestamp: new Date().toISOString(),
        hasAlert: count > 0
    };
    localStorage.setItem(`student_alert_${alertType}`, JSON.stringify(state));
    console.log(`✅ Stored ${alertType} alert state:`, state);
}

function getAlertState(alertType) {
    const stored = localStorage.getItem(`student_alert_${alertType}`);
    if (stored) {
        try {
            return JSON.parse(stored);
        } catch (e) {
            console.error(`Error parsing ${alertType} alert state:`, e);
            return null;
        }
    }
    return null;
}

function clearAlertBadge(alertType) {
    console.log(`🗑️ Clearing ${alertType} alert`);
    localStorage.removeItem(`student_alert_${alertType}`);
    
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
        console.warn(`Badge element not found for ${alertType}`);
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
            console.log(`🔴 Showing ${alertType} badge with count: ${count} and red highlight`);
        }
    } else {
        badge.classList.add('d-none');
        
        // Remove red highlight from sidebar link
        if (link) {
            link.style.backgroundColor = '';
            link.style.borderLeft = '';
            link.style.paddingLeft = '';
            console.log(`⚪ Hiding ${alertType} badge and removing highlight`);
        }
    }
}

function displayStoredAlerts() {
    console.log('📋 Displaying stored alerts on dashboard/other pages');
    
    const alertTypes = ['payments', 'grades', 'violations'];
    
    alertTypes.forEach(alertType => {
        const state = getAlertState(alertType);
        
        if (state && state.hasAlert && state.count > 0) {
            console.log(`📍 Restoring ${alertType} alert: ${state.count} items`);
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
        console.log(`🔴 Highlighted ${alertType} sidebar link in red`);
    }
}

function removeHighlightFromSidebarLink(alertType) {
    const link = document.querySelector(`a[data-alert-link="${alertType}"]`);
    if (link) {
        link.style.backgroundColor = '';
        link.style.borderLeft = '';
        link.style.paddingLeft = '';
        console.log(`⚪ Removed highlight from ${alertType} sidebar link`);
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
