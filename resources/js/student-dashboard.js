// Student Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard functionality
    initializeDashboard();
    
    // Setup card hover effects
    setupCardEffects();
    
    // Initialize tooltips
    initializeTooltips();
});

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

// Export functions for global access
window.showAlert = showAlert;
