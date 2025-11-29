/**
 * Cashier Fee Management JavaScript
 * Handles fee status toggling and other fee management interactions
 */

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Setup CSRF token for all AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        window.csrfToken = csrfToken.getAttribute('content');
    }
    
    // Initialize grade levels functionality for create/edit forms
    initializeGradeLevels();
});

/**
 * Initialize grade levels functionality for fee forms
 */
function initializeGradeLevels() {
    const educationalLevelSelect = document.getElementById('educational_level');
    const container = document.getElementById('grade-levels-container');
    
    // Only initialize if elements exist (on create/edit pages)
    if (!educationalLevelSelect || !container) {
        return;
    }
    
    // Grade levels by educational level
    const gradeLevels = {
        'preschool': ['Toddler', 'Nursery', 'Junior Casa', 'Kindergarten'],
        'elementary': ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
        'junior_high': ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'],
        'senior_high': ['Grade 11', 'Grade 12']
    };
    
    // Get current grade levels for edit form
    let currentGradeLevels = [];
    const currentGradeLevelsElement = document.querySelector('meta[name="current-grade-levels"]');
    if (currentGradeLevelsElement) {
        try {
            currentGradeLevels = JSON.parse(currentGradeLevelsElement.getAttribute('content')) || [];
        } catch (e) {
            currentGradeLevels = [];
        }
    }
    
    function updateGradeLevels() {
        const level = educationalLevelSelect.value;
        
        if (level && gradeLevels[level]) {
            let html = '<div class="row">';
            gradeLevels[level].forEach((grade, index) => {
                const isChecked = currentGradeLevels.includes(grade) ? 'checked' : '';
                html += `
                    <div class="col-md-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="grade_levels[]" 
                                   value="${grade}" id="grade_${index}" ${isChecked}>
                            <label class="form-check-label" for="grade_${index}">
                                ${grade}
                            </label>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            html += '<small class="text-muted"><i class="ri-check-line me-1"></i>Select all applicable grade levels for this fee</small>';
            container.innerHTML = html;
            container.className = 'border rounded p-3';
        } else {
            container.innerHTML = `
                <p class="text-muted mb-0">
                    <i class="ri-information-line me-2"></i>
                    Please select an educational level first to see available grade levels.
                </p>
            `;
            container.className = 'border rounded p-3 bg-light';
        }
    }
    
    // Add event listener for educational level changes
    educationalLevelSelect.addEventListener('change', updateGradeLevels);
    
    // Trigger initial update if there's already a selected value
    if (educationalLevelSelect.value) {
        updateGradeLevels();
    }
}

/**
 * Toggle fee active/inactive status
 * @param {number} feeId - The ID of the fee to toggle
 */
function toggleFeeStatus(feeId) {
    if (!confirm('Are you sure you want to change the status of this fee?')) {
        return;
    }
    
    // Show loading state
    const button = document.querySelector(`button[onclick="toggleFeeStatus(${feeId})"]`);
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="ri-loader-4-line"></i>';
    }
    
    fetch(`/cashier/fees/${feeId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert(data.message, 'success');
            
            // Reload page to reflect changes
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            throw new Error(data.message || 'Failed to update fee status');
        }
    })
    .catch(error => {
        showAlert('An error occurred while updating the fee status: ' + error.message, 'error');
        
        // Re-enable button
        if (button) {
            button.disabled = false;
            button.innerHTML = '<i class="ri-pause-circle-line"></i>';
        }
    });
}

/**
 * Show alert message to user
 * @param {string} message - The message to display
 * @param {string} type - The type of alert (success, error, warning, info)
 */
function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="ri-${getAlertIcon(type)}-line me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of container
    const container = document.querySelector('.container-fluid');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alertDiv && alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
}

/**
 * Get appropriate icon for alert type
 * @param {string} type - Alert type
 * @returns {string} Icon name
 */
function getAlertIcon(type) {
    switch (type) {
        case 'success': return 'check-circle';
        case 'error': return 'error-warning';
        case 'warning': return 'alert';
        case 'info': return 'information';
        default: return 'information';
    }
}

// Expose functions globally for onclick handlers
window.toggleFeeStatus = toggleFeeStatus;
window.showAlert = showAlert;
