// Teacher Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard functionality
    initializeDashboard();
    
    // Load real-time statistics
    loadDashboardStats();
    
    // Set up auto-refresh for statistics
    setInterval(loadDashboardStats, 300000); // Refresh every 5 minutes
});

function initializeDashboard() {
    // Add click handlers for quick action cards
    const actionCards = document.querySelectorAll('.card-body .btn');
    actionCards.forEach(button => {
        button.addEventListener('click', function(e) {
            // Add loading state
            if (!this.href || this.href === '#') {
                e.preventDefault();
                showComingSoon();
            } else {
                // Add loading animation
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="ri-loader-2-line me-2 spinner"></i>Loading...';
                this.disabled = true;
                
                // Re-enable after a short delay (in case of navigation issues)
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 3000);
            }
        });
    });
    
    // Add hover effects to statistics cards
    const statCards = document.querySelectorAll('.card-summary');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

function loadDashboardStats() {
    fetch('/teacher/dashboard/stats')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            updateStatistics(data);
        })
        .catch(error => {
            console.error('Error loading dashboard stats:', error);
            // Don't show error to user for background updates
        });
}

function updateStatistics(stats) {
    // Update statistics cards with animation
    const statElements = {
        'total_classes': document.querySelector('.card-application h3'),
        'total_students': document.querySelector('.card-status h3'),
        'grade_submissions': document.querySelector('.card-payment h3'),
        'weekly_hours': document.querySelector('.card-schedule h3')
    };
    
    Object.keys(statElements).forEach(key => {
        const element = statElements[key];
        if (element && stats[key] !== undefined) {
            animateNumber(element, parseInt(element.textContent) || 0, stats[key]);
        }
    });
}

function animateNumber(element, from, to) {
    const duration = 1000; // 1 second
    const steps = 30;
    const stepValue = (to - from) / steps;
    const stepDuration = duration / steps;
    
    let current = from;
    let step = 0;
    
    const timer = setInterval(() => {
        step++;
        current += stepValue;
        
        if (step >= steps) {
            current = to;
            clearInterval(timer);
        }
        
        element.textContent = Math.round(current);
    }, stepDuration);
}

function showComingSoon() {
    // Create and show coming soon modal
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    modal.innerHTML = `
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Coming Soon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="ri-time-line display-1 text-muted mb-3"></i>
                    <p>This feature is coming soon!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
    
    // Clean up modal when hidden
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}

// Add CSS for spinner animation
const style = document.createElement('style');
style.textContent = `
    .spinner {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .card-summary {
        transition: all 0.3s ease;
    }
`;
document.head.appendChild(style);
