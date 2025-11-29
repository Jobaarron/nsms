// Role Modals JavaScript - Utility functions for modal management

// Show Alert Function
window.showAlert = function(message, type = 'success') {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        return;
    }
    
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="ri-${type === 'success' ? 'check-line' : 'error-warning-line'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    alertContainer.innerHTML = alertHtml;
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
};

// Modal helper functions
window.showModal = function(modalId) {
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
        return;
    }

    try {
        // Try Bootstrap 5 first
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            // Fallback method
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
            modalElement.style.paddingRight = '17px';
            document.body.classList.add('modal-open');
            
            // Add backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = modalId + '-backdrop';
            document.body.appendChild(backdrop);
        }
    } catch (error) {
    }
};

window.hideModal = function(modalId) {
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
        return;
    }

    try {
        // Try Bootstrap 5 first
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            } else {
                // Create new instance and hide
                const modal = new bootstrap.Modal(modalElement);
                modal.hide();
            }
        } else {
            // Fallback method
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            modalElement.style.paddingRight = '';
            document.body.classList.remove('modal-open');
            
            // Remove backdrop
            const backdrop = document.getElementById(modalId + '-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
    } catch (error) {
    }
};

// Setup modal close handlers
function setupModalCloseHandlers() {
    // Handle close buttons
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const modal = this.closest('.modal');
            if (modal) {
                window.hideModal(modal.id);
            }
        });
    });

    // Handle backdrop clicks
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                window.hideModal(this.id);
            }
        });
    });

    // Handle escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Find currently open modal
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                window.hideModal(openModal.id);
            }
        }
    });
}

// Setup modal event handlers
function setupModalEventHandlers() {
    // Handle form submissions
    const assignRoleForm = document.getElementById('assignRoleForm');
    if (assignRoleForm) {
        assignRoleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof window.assignRole === 'function') {
                window.assignRole();
            }
        });
    }
    
    const removeRoleForm = document.getElementById('removeRoleForm');
    if (removeRoleForm) {
        removeRoleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof window.removeRole === 'function') {
                window.removeRole();
            }
        });
    }
    
    const createRoleForm = document.getElementById('createRoleForm');
    if (createRoleForm) {
        createRoleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof window.createRole === 'function') {
                window.createRole();
            }
        });
    }
    
    const editRoleForm = document.getElementById('editRoleForm');
    if (editRoleForm) {
        editRoleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof window.updateRole === 'function') {
                window.updateRole();
            }
        });
    }
    
    const createPermissionForm = document.getElementById('createPermissionForm');
    if (createPermissionForm) {
        createPermissionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof window.createPermission === 'function') {
                window.createPermission();
            }
        });
    }
    
    const editPermissionForm = document.getElementById('editPermissionForm');
    if (editPermissionForm) {
        editPermissionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof window.updatePermission === 'function') {
                window.updatePermission();
            }
        });
    }
    
    // Handle modal close events
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            // Clear any form data when modal is closed
            const forms = this.querySelectorAll('form');
            forms.forEach(form => {
                if (form.reset) form.reset();
            });
        });
    });
    
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Setup modal close handlers
    setupModalCloseHandlers();
    
    // Setup modal event handlers
    setupModalEventHandlers();
    
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Debug: Check if Bootstrap is loaded
    
    // Mark as ready for other scripts
    window.roleModalsReady = true;
});
