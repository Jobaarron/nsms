/**
 * Grade Submission Status Checker
 * Used by teacher views to check if grade submission is active
 */

class GradeSubmissionChecker {
    constructor() {
        this.statusCache = null;
        this.lastCheck = 0;
        this.cacheTimeout = 30000; // 30 seconds
        
        this.init();
    }
    
    init() {
        // Listen for status changes from faculty head
        this.setupEventListeners();
        
        // Check status on page load
        this.checkStatus();
    }
    
    setupEventListeners() {
        // Listen for BroadcastChannel messages
        if (window.BroadcastChannel) {
            const channel = new BroadcastChannel('grade_submission_status');
            channel.addEventListener('message', (event) => {
                if (event.data.type === 'status_changed') {
                    this.statusCache = {
                        active: event.data.active,
                        timestamp: event.data.timestamp
                    };
                    this.updateUI(event.data.active);
                }
            });
        }
        
        // Listen for localStorage changes (cross-tab communication)
        window.addEventListener('storage', (e) => {
            if (e.key === 'grade_submission_status') {
                const data = JSON.parse(e.newValue);
                if (data) {
                    this.statusCache = data;
                    this.updateUI(data.active);
                }
            }
        });
    }
    
    async checkStatus(forceRefresh = false) {
        const now = Date.now();
        
        // Use cache if available and not expired
        if (!forceRefresh && this.statusCache && (now - this.lastCheck) < this.cacheTimeout) {
            return this.statusCache;
        }
        
        try {
            const response = await fetch('/faculty-head/api/grade-submission-status', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.statusCache = {
                    active: data.active,
                    quarters: data.quarters,
                    timestamp: now
                };
                this.lastCheck = now;
                
                this.updateUI(data.active);
                return this.statusCache;
            }
        } catch (error) {
        }
        
        return null;
    }
    
    async isActive(quarter = null) {
        const status = await this.checkStatus();
        
        if (!status || !status.active) {
            return false;
        }
        
        if (quarter && status.quarters) {
            return status.quarters[`q${quarter}`] || false;
        }
        
        return status.active;
    }
    
    updateUI(isActive) {
        // Update grade submission buttons/forms
        const gradeSubmissionElements = document.querySelectorAll('[data-grade-submission]');
        
        gradeSubmissionElements.forEach(element => {
            if (isActive) {
                element.disabled = false;
                element.classList.remove('disabled');
                element.title = 'Grade submission is active';
            } else {
                element.disabled = true;
                element.classList.add('disabled');
                element.title = 'Grade submission is currently disabled by faculty head';
            }
        });
        
        // Update status indicators
        const statusIndicators = document.querySelectorAll('.grade-submission-status');
        statusIndicators.forEach(indicator => {
            indicator.textContent = isActive ? 'Active' : 'Inactive';
            indicator.className = `grade-submission-status badge ${isActive ? 'bg-success' : 'bg-danger'}`;
        });
        
        // Show notification if status changed
        this.showStatusNotification(isActive);
    }
    
    showStatusNotification(isActive) {
        // Only show notification if this is a status change, not initial load
        if (this.statusCache && this.statusCache.timestamp) {
            const message = isActive 
                ? 'Grade submission has been activated. You can now submit grades.'
                : 'Grade submission has been deactivated. Grade submission is temporarily disabled.';
            
            this.showAlert(isActive ? 'success' : 'warning', message);
        }
    }
    
    showAlert(type, message, duration = 5000) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.grade-submission-alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show grade-submission-alert`;
        alertDiv.innerHTML = `
            <i class="ri-${type === 'success' ? 'check' : 'alert'}-line me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at top of main content
        const mainContent = document.querySelector('main') || document.querySelector('.container') || document.body;
        mainContent.insertBefore(alertDiv, mainContent.firstChild);
        
        // Auto-hide after duration
        if (duration > 0) {
            setTimeout(() => {
                if (alertDiv && alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, duration);
        }
    }
}

// Initialize the checker when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.gradeSubmissionChecker = new GradeSubmissionChecker();
});

// Global helper functions for easy access
window.checkGradeSubmissionStatus = function(quarter = null) {
    if (window.gradeSubmissionChecker) {
        return window.gradeSubmissionChecker.isActive(quarter);
    }
    return Promise.resolve(false);
};

window.refreshGradeSubmissionStatus = function() {
    if (window.gradeSubmissionChecker) {
        return window.gradeSubmissionChecker.checkStatus(true);
    }
    return Promise.resolve(null);
};
