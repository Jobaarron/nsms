/**
 * Student Activity Alerts Manager
 * Highlights Grades and Violations sidebar links in red for new updates
 * - Grades: Red highlight when new grades are uploaded for current quarter
 * - Violations: Red highlight when student receives new violations
 * Removes highlight when student opens the respective pages
 */

let activityPollingInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeActivityAlerts();
    startActivityPolling();
});

function initializeActivityAlerts() {
    // Check if we're on the grades or violations page
    const isGradesPage = window.location.pathname.includes('/student/grades');
    const isViolationsPage = window.location.pathname.includes('/student/violations');
    
    console.log('Initializing activity alerts - Grades page:', isGradesPage, 'Violations page:', isViolationsPage);
    
    if (isGradesPage) {
        // Clear the alert when user opens grades page
        console.log('On grades page, clearing alert');
        clearGradesAlert();
    } else {
        // Check for new grades and highlight if needed
        console.log('Not on grades page, checking for new grades');
        checkNewGrades();
    }
    
    if (isViolationsPage) {
        // Clear the alert when user opens violations page
        console.log('On violations page, clearing alert');
        clearViolationsAlert();
    } else {
        // Check for new violations and highlight if needed
        console.log('Not on violations page, checking for new violations');
        checkNewViolations();
    }
}

function startActivityPolling() {
    const POLL_INTERVAL = 3000; // 3 seconds
    
    console.log('🔄 Starting activity alert polling (every 3 seconds)');
    
    // Poll every 3 seconds
    activityPollingInterval = setInterval(() => {
        const isGradesPage = window.location.pathname.includes('/student/grades');
        const isViolationsPage = window.location.pathname.includes('/student/violations');
        
        if (!isGradesPage) {
            console.log('⏰ Polling for grade updates...');
            checkNewGrades();
        }
        
        if (!isViolationsPage) {
            console.log('⏰ Polling for violation updates...');
            checkNewViolations();
        }
    }, POLL_INTERVAL);
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (activityPollingInterval) {
            clearInterval(activityPollingInterval);
        }
    });
}

/**
 * GRADES ALERT SYSTEM
 */
function checkNewGrades() {
    try {
        // Get the current quarter from the page or meta tag
        const currentQuarter = getCurrentQuarter();
        
        if (!currentQuarter) {
            return;
        }

        // Check if there are new grades for the current quarter
        const hasNewGrades = checkForNewGradesInQuarter(currentQuarter);

        if (hasNewGrades) {
            highlightGradesSidebar();
            storeGradesAlertState(true, currentQuarter);
        } else {
            removeGradesHighlight();
            storeGradesAlertState(false, currentQuarter);
        }
    } catch (error) {
        console.error('Error checking new grades:', error);
    }
}

function checkForNewGradesInQuarter(quarter) {
    // Look for grade cards or indicators showing new grades
    const gradeCards = document.querySelectorAll('[data-quarter], .quarter-card, .grade-card');
    
    console.log(`Checking for new grades in ${quarter}, found ${gradeCards.length} cards`);
    
    let hasNewGrades = false;
    
    gradeCards.forEach((card, index) => {
        const cardQuarter = card.getAttribute('data-quarter') || card.textContent;
        const timestamp = card.getAttribute('data-updated-at');
        
        console.log(`Card ${index}: quarter="${cardQuarter}", timestamp="${timestamp}"`);
        
        // Check if this card is for the current quarter
        if (cardQuarter.toLowerCase().includes(quarter.toLowerCase())) {
            console.log(`Found matching quarter card: ${cardQuarter}`);
            
            // Check for "new" indicator or badge
            const newBadge = card.querySelector('[data-new], .badge-new, .new-indicator');
            const updatedIndicator = card.querySelector('[data-updated], .updated-at');
            
            if (newBadge || updatedIndicator) {
                hasNewGrades = true;
                console.log('Found new badge or updated indicator');
            }
            
            // Also check if the card has recent update timestamp
            if (timestamp) {
                const lastUpdate = new Date(timestamp);
                const now = new Date();
                const hoursSinceUpdate = (now - lastUpdate) / (1000 * 60 * 60);
                
                console.log(`Hours since update: ${hoursSinceUpdate}`);
                
                // If updated within last 24 hours, consider it new
                if (hoursSinceUpdate < 24) {
                    hasNewGrades = true;
                    console.log('Grade updated within 24 hours');
                }
            }
        }
    });
    
    console.log(`Has new grades: ${hasNewGrades}`);
    return hasNewGrades;
}

function highlightGradesSidebar() {
    const gradesLink = document.querySelector('a[href*="/student/grades"], a[href*="student.grades"]');
    
    if (gradesLink) {
        // Add red highlight styling
        gradesLink.style.backgroundColor = '#f8d7da';
        gradesLink.style.borderLeft = '4px solid #dc3545';
        gradesLink.style.color = '#721c24';
        gradesLink.style.fontWeight = '600';
        
        // Add pulsing animation
        gradesLink.classList.add('activity-alert-active');
        
        // Add visual indicator (red dot)
        if (!gradesLink.querySelector('.activity-alert-dot')) {
            const dot = document.createElement('span');
            dot.className = 'activity-alert-dot';
            dot.style.display = 'inline-block';
            dot.style.width = '8px';
            dot.style.height = '8px';
            dot.style.backgroundColor = '#dc3545';
            dot.style.borderRadius = '50%';
            dot.style.marginRight = '8px';
            dot.style.animation = 'pulse 2s infinite';
            
            const icon = gradesLink.querySelector('i');
            if (icon) {
                icon.parentNode.insertBefore(dot, icon);
            }
        }
    }
}

function removeGradesHighlight() {
    const gradesLink = document.querySelector('a[href*="/student/grades"], a[href*="student.grades"]');
    
    if (gradesLink) {
        gradesLink.style.backgroundColor = '';
        gradesLink.style.borderLeft = '';
        gradesLink.style.color = '';
        gradesLink.style.fontWeight = '';
        gradesLink.classList.remove('activity-alert-active');
        
        // Remove red dot
        const dot = gradesLink.querySelector('.activity-alert-dot');
        if (dot) {
            dot.remove();
        }
    }
}

function clearGradesAlert() {
    // Remove highlight when user opens grades page
    removeGradesHighlight();
    
    // Store that alert has been viewed
    const currentQuarter = getCurrentQuarter();
    storeGradesAlertState(false, currentQuarter);
    
    // Set a flag so it doesn't show again until new grades are uploaded
    sessionStorage.setItem('gradesAlertViewed', 'true');
}

function storeGradesAlertState(hasAlert, quarter) {
    const state = {
        hasAlert: hasAlert,
        quarter: quarter,
        timestamp: new Date().toISOString()
    };
    localStorage.setItem('student_grades_alert_state', JSON.stringify(state));
}

/**
 * VIOLATIONS ALERT SYSTEM
 */
function checkNewViolations() {
    try {
        // Check if there are new violations
        const hasNewViolations = checkForNewViolationRecords();

        if (hasNewViolations) {
            highlightViolationsSidebar();
            storeViolationsAlertState(true);
        } else {
            removeViolationsHighlight();
            storeViolationsAlertState(false);
        }
    } catch (error) {
        console.error('Error checking new violations:', error);
    }
}

function checkForNewViolationRecords() {
    // Look for violation cards or indicators showing new violations
    const violationCards = document.querySelectorAll('[data-violation], .violation-card, .violation-item');
    
    console.log(`Checking for new violations, found ${violationCards.length} violation records`);
    
    let hasNewViolations = false;
    
    violationCards.forEach((card, index) => {
        const timestamp = card.getAttribute('data-created-at') || card.getAttribute('data-date');
        console.log(`Violation ${index}: timestamp="${timestamp}"`);
        
        // Check for "new" indicator or badge
        const newBadge = card.querySelector('[data-new], .badge-new, .new-indicator');
        const recentIndicator = card.querySelector('[data-recent], .recent-violation');
        
        if (newBadge || recentIndicator) {
            hasNewViolations = true;
            console.log('Found new badge or recent indicator');
        }
        
        // Also check if the card has recent timestamp
        if (timestamp) {
            const violationDate = new Date(timestamp);
            const now = new Date();
            const hoursSinceViolation = (now - violationDate) / (1000 * 60 * 60);
            
            console.log(`Hours since violation: ${hoursSinceViolation}`);
            
            // If violation recorded within last 24 hours, consider it new
            if (hoursSinceViolation < 24) {
                hasNewViolations = true;
                console.log('Violation recorded within 24 hours');
            }
        }
    });
    
    console.log(`Has new violations: ${hasNewViolations}`);
    return hasNewViolations;
}

function highlightViolationsSidebar() {
    const violationsLink = document.querySelector('a[href*="/student/violations"], a[href*="student.violations"]');
    
    if (violationsLink) {
        // Add red highlight styling
        violationsLink.style.backgroundColor = '#f8d7da';
        violationsLink.style.borderLeft = '4px solid #dc3545';
        violationsLink.style.color = '#721c24';
        violationsLink.style.fontWeight = '600';
        
        // Add pulsing animation
        violationsLink.classList.add('activity-alert-active');
        
        // Add visual indicator (red dot)
        if (!violationsLink.querySelector('.activity-alert-dot')) {
            const dot = document.createElement('span');
            dot.className = 'activity-alert-dot';
            dot.style.display = 'inline-block';
            dot.style.width = '8px';
            dot.style.height = '8px';
            dot.style.backgroundColor = '#dc3545';
            dot.style.borderRadius = '50%';
            dot.style.marginRight = '8px';
            dot.style.animation = 'pulse 2s infinite';
            
            const icon = violationsLink.querySelector('i');
            if (icon) {
                icon.parentNode.insertBefore(dot, icon);
            }
        }
    }
}

function removeViolationsHighlight() {
    const violationsLink = document.querySelector('a[href*="/student/violations"], a[href*="student.violations"]');
    
    if (violationsLink) {
        violationsLink.style.backgroundColor = '';
        violationsLink.style.borderLeft = '';
        violationsLink.style.color = '';
        violationsLink.style.fontWeight = '';
        violationsLink.classList.remove('activity-alert-active');
        
        // Remove red dot
        const dot = violationsLink.querySelector('.activity-alert-dot');
        if (dot) {
            dot.remove();
        }
    }
}

function clearViolationsAlert() {
    // Remove highlight when user opens violations page
    removeViolationsHighlight();
    
    // Store that alert has been viewed
    storeViolationsAlertState(false);
    
    // Set a flag so it doesn't show again until new violations are recorded
    sessionStorage.setItem('violationsAlertViewed', 'true');
}

function storeViolationsAlertState(hasAlert) {
    const state = {
        hasAlert: hasAlert,
        timestamp: new Date().toISOString()
    };
    localStorage.setItem('student_violations_alert_state', JSON.stringify(state));
}

/**
 * UTILITY FUNCTIONS
 */
function getCurrentQuarter() {
    // Try to get from meta tag
    const metaQuarter = document.querySelector('meta[name="current-quarter"]');
    if (metaQuarter) {
        return metaQuarter.getAttribute('content');
    }
    
    // Try to get from page content
    const quarterText = document.body.textContent;
    if (quarterText.includes('1st Quarter')) return '1st Quarter';
    if (quarterText.includes('2nd Quarter')) return '2nd Quarter';
    if (quarterText.includes('3rd Quarter')) return '3rd Quarter';
    if (quarterText.includes('4th Quarter')) return '4th Quarter';
    
    return null;
}

function getGradesAlertState() {
    const stored = localStorage.getItem('student_grades_alert_state');
    if (stored) {
        try {
            return JSON.parse(stored);
        } catch (e) {
            return null;
        }
    }
    return null;
}

function getViolationsAlertState() {
    const stored = localStorage.getItem('student_violations_alert_state');
    if (stored) {
        try {
            return JSON.parse(stored);
        } catch (e) {
            return null;
        }
    }
    return null;
}

// Add CSS animation for pulsing effect
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    
    .activity-alert-active {
        animation: activityPulse 2s infinite;
    }
    
    @keyframes activityPulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
        }
        50% {
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1);
        }
    }
`;
document.head.appendChild(style);

// Export functions for global access
window.checkNewGrades = checkNewGrades;
window.clearGradesAlert = clearGradesAlert;
window.checkNewViolations = checkNewViolations;
window.clearViolationsAlert = clearViolationsAlert;
