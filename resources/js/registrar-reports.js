// Registrar Reports JavaScript

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any charts or interactive elements if needed
});

// Export all applications
function exportAllApplications() {
    showAlert('Preparing export...', 'info');
    window.open('/registrar/applications?export=true', '_blank');
}

// Export approved applications only
function exportApprovedApplications() {
    showAlert('Preparing export...', 'info');
    window.open('/registrar/applications?status=approved&export=true', '_blank');
}

// Export pending applications only
function exportPendingApplications() {
    showAlert('Preparing export...', 'info');
    window.open('/registrar/applications?status=pending&export=true', '_blank');
}

// Export grade level report
function exportGradeReport() {
    showAlert('Preparing grade level report...', 'info');
    window.open('/registrar/reports?type=grade&export=true', '_blank');
}

// Export monthly report
function exportMonthlyReport() {
    showAlert('Preparing monthly report...', 'info');
    window.open('/registrar/reports?type=monthly&export=true', '_blank');
}

// Utility function to show alerts
function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}

// Expose functions to global scope for onclick handlers
window.exportAllApplications = exportAllApplications;
window.exportApprovedApplications = exportApprovedApplications;
window.exportPendingApplications = exportPendingApplications;
window.exportGradeReport = exportGradeReport;
window.exportMonthlyReport = exportMonthlyReport;
