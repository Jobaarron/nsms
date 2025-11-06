/**
 * Registrar Dashboard JavaScript
 * Handles dashboard functionality including application approval/decline and export features
 */

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Registrar Dashboard initialized');
    
    // Add hover effects to elements with hover-bg-light class
    document.querySelectorAll('.hover-bg-light').forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        element.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
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

// Expose functions to global scope for onclick handlers
window.approveApplication = approveApplication;
window.declineApplication = declineApplication;
window.exportGradeReport = exportGradeReport;
