// Teacher Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Teacher dashboard JS loaded');
    
    // Check if we're on the teacher dashboard by looking for the exact title
    const titleElement = document.querySelector('h1.section-title');
    const titleText = titleElement?.textContent?.trim() || '';
    
    console.log('Page title:', titleText);
    console.log('Current URL:', window.location.pathname);
    
    // Only run dashboard functionality if we're on the actual dashboard
    if (titleText === 'Teacher Dashboard' && window.location.pathname === '/teacher') {
        console.log('On teacher dashboard - initializing functionality');
        initializeDashboard();
    } else {
        console.log('Not on teacher dashboard - skipping dashboard functionality');
        // Still initialize basic functionality for other teacher pages
        initializeBasicFunctionality();
    }
});

function initializeBasicFunctionality() {
    // Initialize functionality needed on all teacher pages (except statistics)
    // Add click handlers for action buttons (without statistics interference)
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
}

function initializeDashboard() {
    console.log('Initializing teacher dashboard functionality');
    
    // Load statistics immediately
    loadDashboardStats();
    
    // Set up auto-refresh for statistics every 5 minutes
    setInterval(loadDashboardStats, 300000);
    
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
}

function loadDashboardStats() {
    console.log('Loading dashboard stats from API...');
    
    fetch('/teacher/dashboard/stats')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Dashboard stats received:', data);
            updateStatistics(data);
        })
        .catch(error => {
            console.error('Error loading dashboard stats:', error);
            // Don't show error to user for background updates
        });
}

function updateStatistics(stats) {
    console.log('updateStatistics called with:', stats);
    
    // Update statistics cards - look for the specific teacher dashboard structure
    const statElements = {
        'total_classes': document.querySelector('.card-application h3'),
        'total_students': document.querySelector('.card-status h3'),
        'grade_submissions': document.querySelector('.card-payment h3'),
        'weekly_hours': document.querySelector('.card-schedule h3')
    };
    
    console.log('Found stat elements:', statElements);
    
    Object.keys(statElements).forEach(key => {
        const element = statElements[key];
        if (element && stats[key] !== undefined) {
            console.log(`Updating ${key} from ${element.textContent} to ${stats[key]}`);
            // Directly set the value without animation for now to debug
            element.textContent = stats[key];
        } else {
            console.log(`Skipping ${key}: element=${!!element}, value=${stats[key]}`);
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
    
    // Check if grade submission is active and get active quarters
    fetch('/teacher/check-submission-status')
        .then(response => response.json())
        .then(data => {
            if (data.active) {
                // Check if there are active quarters
                const activeQuarters = data.active_quarters || [];
                
                if (activeQuarters.length === 0) {
                    alert('No quarters are currently active for grade submission. Please contact the faculty head.');
                    return;
                }
                
                if (activeQuarters.length === 1) {
                    // Only one quarter active, redirect directly
                    const quarter = activeQuarters[0];
                    window.location.href = `/teacher/grades/submit/${assignmentId}?quarter=${quarter}`;
                } else {
                    // Multiple quarters active, show selection modal with only active quarters
                    showActiveQuarterSelectionModal(assignmentId, activeQuarters);
                }
            } else {
                alert('Grade submission is currently disabled by the faculty head.');
            }
        })
        .catch(error => {
            console.error('Error checking submission status:', error);
            alert('Unable to check grade submission status. Please try again.');
        });
};

function showActiveQuarterSelectionModal(assignmentId, activeQuarters) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    // Generate buttons only for active quarters
    let quarterButtons = '';
    const quarterNames = {
        '1st': '1st Quarter',
        '2nd': '2nd Quarter', 
        '3rd': '3rd Quarter',
        '4th': '4th Quarter'
    };
    
    activeQuarters.forEach(quarter => {
        quarterButtons += `
            <div class="col-6">
                <button class="btn btn-outline-primary w-100" onclick="redirectToGradeEntry(${assignmentId}, '${quarter}')">
                    <i class="ri-calendar-line me-2"></i>${quarterNames[quarter]}
                </button>
            </div>
        `;
    });
    
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Active Quarter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Select from the currently active quarters for grade submission:</p>
                    <div class="row g-2">
                        ${quarterButtons}
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="ri-information-line me-2"></i>
                        <small>Only quarters activated by the faculty head are shown.</small>
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

// Add handleGradeSubmission function for grades page compatibility
window.handleGradeSubmission = function() {
    const assignmentSelect = document.getElementById('assignmentSelect');
    
    if (!assignmentSelect || !assignmentSelect.value) {
        alert('Please select a subject first.');
        return;
    }
    
    const assignmentId = assignmentSelect.value;
    
    // Use the same logic as submitGrades
    window.submitGrades(assignmentId);
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
