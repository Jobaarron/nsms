document.addEventListener('DOMContentLoaded', function() {
    
    // Check if we're on the activate submission page
    const isActivateSubmissionPage = window.location.pathname.includes('/activate-submission');
    
    if (!isActivateSubmissionPage) {
        return;
    }
    
    const toggleBtn = document.getElementById('toggleSubmissionBtn');
    let toggleIcon = document.getElementById('toggleIcon');
    let toggleText = document.getElementById('toggleText');
    const quarterSwitches = document.querySelectorAll('.quarter-switch');
    
    // Test if elements exist
    if (!toggleBtn) {
        return;
    }
    
    if (!document.querySelector('meta[name="csrf-token"]')) {
        return;
    }
    
    // Add global debug function
    window.debugActivateSubmission = function() {
        // Debug function
    };
    
    // Add global test function
    window.testActivateSubmission = function() {
        if (toggleBtn) {
            toggleBtn.click();
        }
    };
    
    // Initialize page state
    initializePage();
    
    // Handle main toggle button
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const isCurrentlyActive = this.dataset.active === '1';
            const newStatus = !isCurrentlyActive;
            
            // Simple confirmation and direct call
            const action = newStatus ? 'activate' : 'deactivate';
            if (confirm(`Are you sure you want to ${action} grade submission? This will ${newStatus ? 'allow' : 'prevent'} teachers from submitting grades.`)) {
                toggleGradeSubmission(newStatus);
            }
            
            // Original modal code (commented out for debugging)
            /*
            showConfirmationModal(
                newStatus ? 'Activate Grade Submission' : 'Deactivate Grade Submission',
                newStatus 
                    ? 'Are you sure you want to activate grade submission? Teachers will be able to submit grades for review.'
                    : 'Are you sure you want to deactivate grade submission? Teachers will not be able to submit new grades or edit existing drafts.',
                () => toggleGradeSubmission(newStatus)
            );
            */
        });
    }
    
    // Handle quarter switches
    quarterSwitches.forEach(switchElement => {
        switchElement.addEventListener('change', function() {
            const quarter = this.dataset.quarter;
            const isActive = this.checked;
            updateQuarterSetting(quarter, isActive, this);
        });
    });
    
    /**
     * Initialize page state and check current settings
     */
    function initializePage() {
        // Disable quarter switches if main submission is inactive
        const mainActive = toggleBtn && toggleBtn.dataset.active === '1';
        updateQuarterSwitchesState(mainActive);
        
        // Set up periodic status check for real-time updates
        setInterval(checkStatusUpdates, 30000); // Check every 30 seconds
    }
    
    /**
     * Update quarter switches based on main submission status
     */
    function updateQuarterSwitchesState(mainActive) {
        quarterSwitches.forEach(switchElement => {
            if (!mainActive) {
                switchElement.disabled = true;
                switchElement.checked = false;
                switchElement.parentElement.parentElement.style.opacity = '0.5';
            } else {
                switchElement.disabled = false;
                switchElement.parentElement.parentElement.style.opacity = '1';
            }
        });
    }
    
    /**
     * Check for status updates (for real-time sync with teacher views)
     */
    function checkStatusUpdates() {
        fetch('/faculty-head/api/grade-submission-status', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI if status changed
                const currentActive = toggleBtn.dataset.active === '1';
                if (currentActive !== data.active) {
                    updateToggleButton(data.active);
                    updateStatusCard(data.active);
                    toggleBtn.dataset.active = data.active ? '1' : '0';
                }
                
                // Update quarter switches
                Object.keys(data.quarters).forEach(quarter => {
                    const switchElement = document.getElementById(`${quarter}Switch`);
                    if (switchElement) {
                        switchElement.checked = data.quarters[quarter];
                    }
                });
                
                updateQuarterSwitchesState(data.active);
            }
        })
        .catch(error => {
        });
    }
    
    /**
     * Toggle grade submission status
     */
    function toggleGradeSubmission(newStatus) {
        
        // Disable button during request and show loading state
        toggleBtn.disabled = true;
        const originalHTML = toggleBtn.innerHTML;
        const actionText = newStatus ? 'Activating...' : 'Deactivating...';
        
        // Update button appearance immediately for better UX
        toggleBtn.className = 'btn btn-lg btn-secondary btn-updating';
        toggleBtn.innerHTML = `<i class="ri-loader-4-line me-2 spin"></i>${actionText}`;
        
        
        fetch('/faculty-head/activate-submission/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                active: newStatus
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update page data first
                toggleBtn.dataset.active = data.active ? '1' : '0';
                
                // Force immediate UI updates with multiple repaints
                requestAnimationFrame(() => {
                    updateToggleButton(data.active);
                    updateStatusCard(data.active);
                    updateQuarterSwitchesState(data.active);
                    
                    // Update quarter switches if quarters data is provided
                    if (data.quarters) {
                        updateQuarterSwitches(data.quarters);
                    }
                    
                    // Force another repaint
                    requestAnimationFrame(() => {
                        
                        // Show success message with quarter info if available
                        let message = data.message;
                        if (data.activated_quarter) {
                            const quarterNames = {
                                'q1': '1st Quarter',
                                'q2': '2nd Quarter', 
                                'q3': '3rd Quarter',
                                'q4': '4th Quarter'
                            };
                            message += ` (${quarterNames[data.activated_quarter]} automatically activated)`;
                        }
                        showAlert('success', message, 7000);
                        
                        // Notify teacher views of status change
                        notifyTeacherViews(data.active);
                    });
                });
            } else {
                throw new Error(data.message || 'Failed to update grade submission status');
            }
        })
        .catch(error => {
            showAlert('danger', 'Failed to update grade submission status. Please try again.');
            
            // Restore original button state on error
            const currentActive = toggleBtn.dataset.active === '1';
            updateToggleButton(currentActive);
        })
        .finally(() => {
            // Re-enable button
            toggleBtn.disabled = false;
        });
    }
    
    /**
     * Update quarter-specific setting
     */
    function updateQuarterSetting(quarter, isActive, switchElement) {
        // Store original state in case we need to revert
        const originalState = !isActive;
        
        fetch('/faculty-head/activate-submission/quarter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                quarter: quarter,
                active: isActive
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert('success', data.message, 3000);
            } else {
                throw new Error(data.message || 'Failed to update quarter setting');
            }
        })
        .catch(error => {
            
            // Revert switch state
            switchElement.checked = originalState;
            
            showAlert('danger', 'Failed to update quarter setting. Please try again.');
        });
    }
    
    /**
     * Update toggle button appearance
     */
    function updateToggleButton(isActive) {
        
        // Remove all existing classes and force DOM update
        toggleBtn.removeAttribute('class');
        toggleBtn.style.display = 'none';
        toggleBtn.offsetHeight; // Force reflow
        toggleBtn.style.display = '';
        
        // Build new button content
        const iconClass = isActive ? 'ri-pause-circle-line' : 'ri-play-circle-line';
        const buttonClass = isActive ? 'btn btn-lg btn-danger' : 'btn btn-lg btn-success';
        const buttonText = isActive ? 'Deactivate Grade Submission' : 'Activate Grade Submission';
        
        // Update classes and content in one operation
        toggleBtn.className = buttonClass;
        toggleBtn.classList.remove('btn-updating'); // Remove updating class
        toggleBtn.innerHTML = `<i class="${iconClass} me-2" id="toggleIcon"></i><span id="toggleText">${buttonText}</span>`;
        
        // Re-assign element references after innerHTML change
        window.toggleIcon = document.getElementById('toggleIcon');
        window.toggleText = document.getElementById('toggleText');
        
        // Force multiple repaints to ensure visibility
        toggleBtn.offsetHeight;
        toggleBtn.style.transform = 'scale(1.01)';
        setTimeout(() => {
            toggleBtn.style.transform = '';
        }, 50);
        
    }

    /**
     * Update quarter switches based on data
     */
    function updateQuarterSwitches(quartersData) {
        Object.keys(quartersData).forEach(quarter => {
            const switchElement = document.getElementById(`${quarter}Switch`);
            if (switchElement) {
                switchElement.checked = quartersData[quarter];
            }
        });
    }
    
    /**
     * Update status card
     */
    function updateStatusCard(isActive) {
        
        const statusCard = document.querySelector('.card-summary');
        const statusIcon = statusCard?.querySelector('i');
        const statusTitle = statusCard?.querySelector('h2');
        const statusDescription = statusCard?.querySelector('p');
        
        if (statusCard && statusIcon && statusTitle && statusDescription) {
            // Force immediate visual update with animation
            statusCard.style.opacity = '0.5';
            statusCard.style.transform = 'scale(0.98)';
            
            setTimeout(() => {
                // Clear existing classes
                statusCard.removeAttribute('class');
                statusIcon.removeAttribute('class');
                
                if (isActive) {
                    statusCard.className = 'card card-summary card-payment h-100';
                    statusIcon.className = 'ri-play-circle-fill display-1 mb-3';
                    statusTitle.textContent = 'ACTIVE';
                    statusDescription.textContent = 'Teachers can submit grades';
                } else {
                    statusCard.className = 'card card-summary card-schedule h-100';
                    statusIcon.className = 'ri-pause-circle-fill display-1 mb-3';
                    statusTitle.textContent = 'INACTIVE';
                    statusDescription.textContent = 'Grade submission is disabled';
                }
                
                // Restore animation
                statusCard.style.opacity = '1';
                statusCard.style.transform = 'scale(1)';
                statusCard.style.transition = 'all 0.3s ease';
                
                // Force repaint
                statusCard.offsetHeight;
                
            }, 100);
        }
    }
    
    /**
     * Show confirmation modal
     */
    function showConfirmationModal(title, message, onConfirm) {
        const modal = document.getElementById('confirmModal');
        const modalTitle = modal?.querySelector('.modal-title');
        const modalMessage = modal?.querySelector('#confirmMessage');
        const confirmBtn = modal?.querySelector('#confirmBtn');
        
        if (modal && modalTitle && modalMessage && confirmBtn) {
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            
            // Remove existing event listeners
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            // Add new event listener
            newConfirmBtn.addEventListener('click', function() {
                onConfirm();
                bootstrap.Modal.getInstance(modal)?.hide();
            });
            
            // Show modal
            new bootstrap.Modal(modal).show();
        }
    }
    
    /**
     * Show alert message
     */
    function showAlert(type, message, duration = 5000) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert.alert-dismissible');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="ri-${type === 'success' ? 'check' : 'alert'}-line me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert after page title
        const pageTitle = document.querySelector('.section-title');
        if (pageTitle && pageTitle.parentNode) {
            pageTitle.parentNode.insertBefore(alertDiv, pageTitle.nextSibling);
        }
        
        // Auto-hide after duration
        if (duration > 0) {
            setTimeout(() => {
                if (alertDiv && alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, duration);
        }
    }
    
    /**
     * Notify teacher views of grade submission status change
     */
    function notifyTeacherViews(isActive) {
        // Broadcast event for other tabs/windows
        if (window.BroadcastChannel) {
            const channel = new BroadcastChannel('grade_submission_status');
            channel.postMessage({
                type: 'status_changed',
                active: isActive,
                timestamp: Date.now()
            });
        }
        
        // Store in localStorage for cross-tab communication
        localStorage.setItem('grade_submission_status', JSON.stringify({
            active: isActive,
            timestamp: Date.now()
        }));
    }
    
    /**
     * Listen for storage changes (for cross-tab communication)
     */
    window.addEventListener('storage', function(e) {
        if (e.key === 'grade_submission_status') {
            const data = JSON.parse(e.newValue);
            if (data && toggleBtn) {
                const currentActive = toggleBtn.dataset.active === '1';
                if (currentActive !== data.active) {
                    updateToggleButton(data.active);
                    updateStatusCard(data.active);
                    updateQuarterSwitchesState(data.active);
                    toggleBtn.dataset.active = data.active ? '1' : '0';
                    
                    showAlert('info', `Grade submission has been ${data.active ? 'activated' : 'deactivated'} by another user.`);
                }
            }
        }
    });
});

// Add CSS for loading spinner and enhanced UI
const style = document.createElement('style');
style.textContent = `
    .spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .quarter-switch:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .quarter-switch:disabled + label {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Enhanced button transitions */
    #toggleSubmissionBtn {
        transition: all 0.2s ease-in-out !important;
    }
    
    .card-summary {
        transition: all 0.3s ease-in-out !important;
    }
    
    /* Force immediate visual updates */
    .btn-updating {
        transform: scale(1.02) !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2) !important;
    }
`;
document.head.appendChild(style);

// Global function for teacher views to check submission status
window.checkGradeSubmissionStatus = function(quarter = null) {
    return fetch('/faculty-head/api/grade-submission-status')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (quarter) {
                    return data.active && data.quarters[`q${quarter}`];
                }
                return data.active;
            }
            return false;
        })
        .catch(() => false);
};
