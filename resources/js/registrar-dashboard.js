/**
 * Registrar Dashboard JavaScript
 * Handles dashboard functionality including application approval/decline and export features
 * Includes real-time updates and auto-refresh
 */

// Global variables for real-time updates
let autoRefreshInterval = null;
let isAutoRefreshEnabled = true;
const REFRESH_INTERVAL = 5000; // 5 seconds for seamless updates

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Registrar Dashboard initialized with real-time updates');
    
    // Add hover effects to elements with hover-bg-light class
    document.querySelectorAll('.hover-bg-light').forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        element.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
    
    // Start seamless auto-refresh
    startAutoRefresh();
});

/**
 * Approve application from dashboard
 */
function approveApplication(applicationId) {
    if (confirm('Are you sure you want to approve this application?')) {
        fetch(`/registrar/applications/${applicationId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error approving application. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error approving application. Please try again.');
        });
    }
}

/**
 * Decline application from dashboard
 */
function declineApplication(applicationId) {
    const reason = prompt('Please provide a reason for declining this application:');
    if (reason && reason.trim() !== '') {
        fetch(`/registrar/applications/${applicationId}/decline`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error declining application. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error declining application. Please try again.');
        });
    }
}

/**
 * Export grade level report as CSV
 */
function exportGradeReport() {
    // Create a simple CSV export for grade level report
    const table = document.querySelector('#grade-level-table');
    if (!table) {
        alert('No data available to export');
        return;
    }
    
    let csv = 'Grade Level,Applications,Percentage\n';
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 2) {
            const gradeLevel = cells[0].textContent.trim();
            const applications = cells[1].textContent.trim();
            const percentage = cells[2].querySelector('small')?.textContent.trim() || '0%';
            csv += `"${gradeLevel}","${applications}","${percentage}"\n`;
        }
    });
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'grade-level-report.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

/**
 * Start auto-refresh functionality
 */
function startAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    
    if (isAutoRefreshEnabled) {
        autoRefreshInterval = setInterval(() => {
            refreshDashboardStats();
        }, REFRESH_INTERVAL);
        
        console.log('Auto-refresh started (every 30 seconds)');
    }
}

/**
 * Stop auto-refresh functionality
 */
function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
        console.log('Auto-refresh stopped');
    }
}



/**
 * Refresh dashboard statistics silently
 */
function refreshDashboardStats() {
    fetch('/registrar/dashboard/stats', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatsCards(data.stats);
            updateRecentApplications(data.recent_applications);
            updateLastUpdatedTimestamp('dashboard-last-updated');
        } else {
            console.error('Failed to refresh stats:', data.message);
        }
    })
    .catch(error => {
        console.error('Error refreshing stats:', error);
    });
}

/**
 * Update statistics cards with new data
 */
function updateStatsCards(stats) {
    // Update total applications
    const totalElement = document.querySelector('.fs-2.text-primary');
    if (totalElement) totalElement.textContent = stats.total_applications;
    
    // Update pending applications
    const pendingElement = document.querySelector('.fs-2.text-warning');
    if (pendingElement) pendingElement.textContent = stats.pending_applications;
    
    // Update approved applications
    const approvedElement = document.querySelector('.fs-2.text-success');
    if (approvedElement) approvedElement.textContent = stats.approved_applications;
    
    // Update declined applications
    const declinedElement = document.querySelector('.fs-2.text-danger');
    if (declinedElement) declinedElement.textContent = stats.declined_applications;
    
    // Update pending applications header count if it exists
    const pendingHeader = document.querySelector('.text-warning h4');
    if (pendingHeader) {
        pendingHeader.innerHTML = `<i class="ri-alert-line me-2"></i>Applications Requiring Review (${stats.pending_applications})`;
    }
}

/**
 * Update recent applications section (basic refresh - could be enhanced)
 */
function updateRecentApplications(applications) {
    // For now, we'll just update the counts
    // In a more advanced implementation, we could update the entire list
    console.log('Recent applications updated:', applications.length);
}

/**
 * Update last updated timestamp
 */
function updateLastUpdatedTimestamp(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        const now = new Date();
        const formatted = now.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        element.textContent = `Last updated: ${formatted}`;
    }
}



// Handle page visibility changes (pause refresh when tab is not active)
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAutoRefresh();
    } else if (isAutoRefreshEnabled) {
        startAutoRefresh();
    }
});

// Expose functions to global scope for onclick handlers
window.approveApplication = approveApplication;
window.declineApplication = declineApplication;
window.exportGradeReport = exportGradeReport;
