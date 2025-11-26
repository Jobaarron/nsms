// Student Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize alert manager
    initializeAlertManager();
    
    // Initialize dashboard functionality
    initializeDashboard();
    
    // Setup card hover effects
    setupCardEffects();
    
    // Initialize tooltips
    initializeTooltips();
});

function initializeAlertManager() {
    // Check and hide expired alerts
    checkAndHideExpiredAlerts();
    
    // Setup dismiss button handlers
    setupAlertDismissHandlers();
}

function initializeDashboard() {
    console.log('Student Dashboard initialized');
    
    // Add any dashboard-specific initialization here
    setupQuickActions();
    setupProgressTracking();
}

function setupCardEffects() {
    // Enhanced hover effects for summary cards
    const summaryCards = document.querySelectorAll('.card-summary');
    
    summaryCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
            this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
            this.style.boxShadow = '0 6px 12px rgba(0, 0, 0, 0.2)';
        });
    });
}

function setupQuickActions() {
    // Add click handlers for quick action buttons
    const quickActionBtns = document.querySelectorAll('.btn[href]');
    
    quickActionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Add loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="ri-loader-4-line me-2"></i>Loading...';
            this.disabled = true;
            
            // Allow navigation to proceed
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 1000);
        });
    });
}

function setupProgressTracking() {
    // Animate progress timeline items
    const timelineItems = document.querySelectorAll('.timeline-item');
    
    timelineItems.forEach((item, index) => {
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, index * 200);
    });
}

function initializeTooltips() {
    // Initialize Bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

// Utility functions
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container') || document.body;
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.insertBefore(alert, alertContainer.firstChild);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Alert Manager Functions
const ALERT_STORAGE_PREFIX = 'student_alert_';
const ALERT_DURATION_DAYS = 7;
const ALERT_DURATION_MS = ALERT_DURATION_DAYS * 24 * 60 * 60 * 1000;

function getAlertStorageKey(alertType) {
    return `${ALERT_STORAGE_PREFIX}${alertType}`;
}

function shouldHideAlert(alertType) {
    const storageKey = getAlertStorageKey(alertType);
    const storedData = localStorage.getItem(storageKey);

    if (!storedData) {
        return false; // Alert not yet dismissed
    }

    try {
        const data = JSON.parse(storedData);
        const dismissedTime = new Date(data.dismissedAt).getTime();
        const currentTime = new Date().getTime();
        const timePassed = currentTime - dismissedTime;

        // If 7 days have passed, return true (should hide)
        return timePassed >= ALERT_DURATION_MS;
    } catch (e) {
        console.error('Error parsing alert data:', e);
        return false;
    }
}

function dismissAlert(alertType) {
    const storageKey = getAlertStorageKey(alertType);
    const data = {
        dismissedAt: new Date().toISOString(),
        type: alertType
    };
    localStorage.setItem(storageKey, JSON.stringify(data));
}

function checkAndHideExpiredAlerts() {
    const alertTypes = ['enrollment_complete', 'partial_payment', 'payment_complete'];

    alertTypes.forEach(alertType => {
        const alertElement = document.querySelector(`[data-alert-type="${alertType}"]`);
        
        if (alertElement) {
            if (shouldHideAlert(alertType)) {
                hideAlertElement(alertElement);
            }
        }
    });
}

function hideAlertElement(alertElement) {
    if (!alertElement) return;

    alertElement.style.transition = 'opacity 0.3s ease-out';
    alertElement.style.opacity = '0';

    setTimeout(() => {
        alertElement.style.display = 'none';
    }, 300);
}

function setupAlertDismissHandlers() {
    const dismissButtons = document.querySelectorAll('[data-dismiss-alert]');

    dismissButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const alertType = this.getAttribute('data-dismiss-alert');
            const alertElement = this.closest('[data-alert-type]');

            if (alertElement) {
                dismissAlert(alertType);
                hideAlertElement(alertElement);
            }
        });
    });
}

function getRemainingDays(alertType) {
    const storageKey = getAlertStorageKey(alertType);
    const storedData = localStorage.getItem(storageKey);

    if (!storedData) {
        return null;
    }

    try {
        const data = JSON.parse(storedData);
        const dismissedTime = new Date(data.dismissedAt).getTime();
        const currentTime = new Date().getTime();
        const timePassed = currentTime - dismissedTime;
        const remainingMs = ALERT_DURATION_MS - timePassed;
        const remainingDays = Math.ceil(remainingMs / (24 * 60 * 60 * 1000));

        return Math.max(0, remainingDays);
    } catch (e) {
        console.error('Error calculating remaining days:', e);
        return null;
    }
}

function resetAlert(alertType) {
    const storageKey = getAlertStorageKey(alertType);
    localStorage.removeItem(storageKey);
    
    const alertElement = document.querySelector(`[data-alert-type="${alertType}"]`);
    if (alertElement) {
        alertElement.style.display = '';
        alertElement.style.opacity = '1';
    }
}

// Export functions for global access
window.showAlert = showAlert;
window.dismissAlert = dismissAlert;
window.resetAlert = resetAlert;
window.getRemainingDays = getRemainingDays;
