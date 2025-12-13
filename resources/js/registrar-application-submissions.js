// Registrar Application Submission Toggle JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Check if we're on the registrar dashboard page
    const isDashboardPage = window.location.pathname.includes('/registrar/dashboard') || 
                          window.location.pathname === '/registrar' || 
                          window.location.pathname === '/registrar/';
    
    if (!isDashboardPage) {
        return;
    }
    
    const toggleBtn = document.getElementById('toggleSubmissionBtn');
    let toggleIcon = document.getElementById('toggleIcon');
    let toggleText = document.getElementById('toggleText');
    
    // Test if elements exist
    if (!toggleBtn) {
        return;
    }
    
    if (!document.querySelector('meta[name="csrf-token"]')) {
        return;
    }
    
    // Add global debug function for testing
    window.debugApplicationSubmission = function() {
        console.log('Toggle Button:', toggleBtn);
        console.log('Current Active Status:', toggleBtn.dataset.active);
        console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
    };
    
    // Handle main toggle button
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const currentActive = this.dataset.active === '1';
            const newStatus = !currentActive;
            
            // Show confirmation dialog
            const actionText = newStatus ? 'activate' : 'deactivate';
            const confirmMessage = `Are you sure you want to ${actionText} application submissions?\n\n` +
                                 (newStatus ? 
                                    'Students will be able to submit new applications.' : 
                                    'Students will not be able to submit new applications until you reactivate this feature.');
            
            if (!confirm(confirmMessage)) {
                return;
            }
            
            toggleApplicationSubmission(newStatus);
        });
    }
    
    /**
     * Toggle application submission status
     */
    function toggleApplicationSubmission(newStatus) {
        
        // Disable button during request and show loading state
        toggleBtn.disabled = true;
        const originalHTML = toggleBtn.innerHTML;
        const actionText = newStatus ? 'Activating...' : 'Deactivating...';
        
        // Update button appearance immediately for better UX
        toggleBtn.className = 'btn btn-lg btn-secondary btn-updating';
        toggleBtn.innerHTML = `<i class="ri-loader-4-line me-2 spin"></i>${actionText}`;
        
        fetch('/registrar/application-submissions/toggle', {
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
                // Update button state
                toggleBtn.dataset.active = newStatus ? '1' : '0';
                
                // Update button appearance
                const iconClass = newStatus ? 'ri-check-line' : 'ri-pause-line';
                const buttonClass = newStatus ? 'btn-success' : 'btn-warning';
                const statusText = newStatus ? 'Submissions Active' : 'Submissions Closed';
                
                toggleBtn.className = `btn btn-lg ${buttonClass}`;
                toggleBtn.innerHTML = `<i class="${iconClass} me-2"></i><span>${statusText}</span>`;
                
                // Show success message
                showToast(data.message, 'success');
                
                // Update page alert if needed
                updateSubmissionAlert(newStatus);
                
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        })
        .catch(error => {
            console.error('Error toggling application submission:', error);
            
            // Restore original button state
            toggleBtn.innerHTML = originalHTML;
            const currentActive = toggleBtn.dataset.active === '1';
            toggleBtn.className = `btn btn-lg ${currentActive ? 'btn-success' : 'btn-warning'}`;
            
            // Show error message
            showToast('Failed to update application submission status. Please try again.', 'error');
        })
        .finally(() => {
            // Re-enable button
            toggleBtn.disabled = false;
        });
    }
    
    /**
     * Update submission alert based on status
     */
    function updateSubmissionAlert(isActive) {
        const existingAlert = document.querySelector('.alert-warning');
        
        if (isActive) {
            // Remove warning alert if submissions are active
            if (existingAlert && existingAlert.textContent.includes('Application Submissions Closed')) {
                existingAlert.remove();
            }
        } else {
            // Add warning alert if submissions are paused and no alert exists
            if (!existingAlert || !existingAlert.textContent.includes('Application Submissions Closed')) {
                const alertHtml = `
                    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                        <i class="ri-pause-circle-line me-2"></i>
                        <strong>Application Submissions Closed:</strong> New student applications are currently disabled. 
                        Students will see a notice that applications are closed.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                // Insert after header section
                const headerSection = document.querySelector('.section-title').closest('div').closest('div');
                if (headerSection) {
                    headerSection.insertAdjacentHTML('afterend', alertHtml);
                }
            }
        }
    }
    
    /**
     * Show toast notification
     */
    function showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '11';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast
        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
        const iconClass = type === 'success' ? 'ri-check-circle-line' : type === 'error' ? 'ri-error-warning-line' : 'ri-information-line';
        
        const toastHtml = `
            <div id="${toastId}" class="toast ${bgClass} text-white" role="alert">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center">
                        <i class="${iconClass} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        // Initialize and show toast
        const toastElement = document.getElementById(toastId);
        if (window.bootstrap && window.bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 5000
            });
            toast.show();
            
            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', function() {
                this.remove();
            });
        } else {
            // Fallback if Bootstrap Toast is not available
            toastElement.style.display = 'block';
            setTimeout(() => {
                toastElement.remove();
            }, 5000);
        }
    }
    
    // Add CSS for loading animation
    const style = document.createElement('style');
    style.textContent = `
        .btn-updating {
            position: relative;
        }
        
        .spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
});