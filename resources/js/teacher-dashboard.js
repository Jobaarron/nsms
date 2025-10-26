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
    const actionCards = document.querySelectorAll('.card-body .btn:not(.disabled):not([disabled])');
    actionCards.forEach(button => {
        button.addEventListener('click', function(e) {
            // Only add loading animation for buttons with valid hrefs
            if (this.href && this.href !== '#' && !this.classList.contains('disabled') && !this.disabled) {
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

// Teacher Assignment Actions
window.submitGrades = function(assignmentId) {
    console.log('Submit grades for assignment:', assignmentId);
    
    // Check if grade submission is active
    fetch('/teacher/check-submission-status')
        .then(response => response.json())
        .then(data => {
            if (data.active) {
                // Show quarter selection modal first
                showQuarterSelectionModal(assignmentId);
            } else {
                alert('Grade submission is currently disabled by the faculty head.');
            }
        })
        .catch(error => {
            console.error('Error checking submission status:', error);
            alert('Unable to check grade submission status. Please try again.');
        });
};

function showQuarterSelectionModal(assignmentId) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Quarter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Select the quarter for grade submission:</p>
                    <div class="row g-2">
                        <div class="col-6">
                            <button class="btn btn-outline-primary w-100" onclick="redirectToGradeEntry(${assignmentId}, '1st')">
                                <i class="ri-calendar-line me-2"></i>1st Quarter
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-primary w-100" onclick="redirectToGradeEntry(${assignmentId}, '2nd')">
                                <i class="ri-calendar-line me-2"></i>2nd Quarter
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-primary w-100" onclick="redirectToGradeEntry(${assignmentId}, '3rd')">
                                <i class="ri-calendar-line me-2"></i>3rd Quarter
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-primary w-100" onclick="redirectToGradeEntry(${assignmentId}, '4th')">
                                <i class="ri-calendar-line me-2"></i>4th Quarter
                            </button>
                        </div>
                    </div>
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

window.redirectToGradeEntry = function(assignmentId, quarter) {
    window.location.href = `/teacher/grades/submit/${assignmentId}?quarter=${quarter}`;
};

window.viewClassDetails = function(assignmentId) {
    console.log('View class details for assignment:', assignmentId);
    // Redirect to teacher grades page to view submissions for this assignment
    window.location.href = '/teacher/grades';
};

window.manageClass = function(assignmentId) {
    console.log('Manage class for assignment:', assignmentId);
    // Redirect to teacher grades page to manage submissions for this assignment
    window.location.href = '/teacher/grades';
};
