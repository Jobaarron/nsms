/**
 * Cashier Fee Management JavaScript
 * Handles fee status toggling and other fee management interactions
 */

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cashier Fee Management initialized');
    
    // Setup CSRF token for all AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        window.csrfToken = csrfToken.getAttribute('content');
    }
});

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
        console.error('Error:', error);
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
