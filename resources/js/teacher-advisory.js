/**
 * Teacher Advisory JavaScript
 * Handles view grades and print report card functionality
 */


document.addEventListener('DOMContentLoaded', function() {
    console.log('Teacher Advisory JS loaded');
    
    // Verify functions are available globally
    if (typeof window.viewStudentGrades === 'function' && 
        typeof window.viewAllGrades === 'function' && 
        typeof window.printReportCard === 'function' && 
        typeof window.printAllReportCards === 'function') {
        console.log('✅ All advisory functions loaded successfully');
    } else {
        console.error('❌ Some advisory functions failed to load');
    }
    
    // Initialize tooltips for action buttons
    initializeTooltips();
    
    /**
     * Initialize tooltips
     */
    function initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    /**
     * Test function to simulate all functionality
     */
    window.testAdvisoryFunctions = function() {
        console.log('=== Testing Advisory Functions ===');
        
        // Test individual student grades
        console.log('1. Testing viewStudentGrades...');
        viewStudentGrades(1);
        
        setTimeout(() => {
            console.log('2. Testing viewAllGrades...');
            viewAllGrades();
        }, 2000);
        
        setTimeout(() => {
            console.log('3. Testing printReportCard...');
            // Create a mock event target
            const mockButton = document.createElement('button');
            mockButton.innerHTML = '<i class="ri-printer-line"></i>';
            window.event = { target: mockButton };
            printReportCard(1);
        }, 4000);
        
        setTimeout(() => {
            console.log('4. Testing printAllReportCards...');
            // Mock confirm dialog
            window.confirm = () => true;
            const mockButton = document.createElement('button');
            mockButton.innerHTML = '<i class="ri-printer-line me-1"></i>Print All Report Cards';
            window.event = { target: mockButton };
            printAllReportCards();
        }, 6000);
        
        console.log('All tests initiated. Check console and modals for results.');
    };
    
    /**
     * Enable debug mode
     */
    window.enableAdvisoryDebug = function() {
        console.log('=== Advisory Debug Mode Enabled ===');
        window.advisoryDebug = true;
        
        // Add debug info to buttons
        document.querySelectorAll('[onclick*="viewStudentGrades"]').forEach(btn => {
            btn.style.border = '2px solid blue';
            btn.title += ' [DEBUG: View Grades]';
        });
        
        document.querySelectorAll('[onclick*="printReportCard"]').forEach(btn => {
            btn.style.border = '2px solid green';
            btn.title += ' [DEBUG: Print Report]';
        });
        
        console.log('Debug styling applied to buttons');
    };
});

/**
 * View individual student grades
 */
window.viewStudentGrades = function(studentId) {
    const modal = new bootstrap.Modal(document.getElementById('viewGradesModal'));
    const modalContent = document.getElementById('gradesModalContent');

    // Show loading spinner
    modalContent.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading report card PDF...</p>
        </div>
    `;
    modal.show();

    // Fetch the report card PDF as a blob and embed in modal
    fetch(`/teacher/report-card/pdf/${studentId}`)
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch PDF');
            return response.blob();
        })
        .then(blob => {
            const url = URL.createObjectURL(blob);
            modalContent.innerHTML = `
                <div class="ratio ratio-4x3 mb-2">
                    <iframe src="${url}" frameborder="0" style="width:100%;height:100%;"></iframe>
                </div>
            `;
        })
        .catch(error => {
            modalContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    Error loading report card PDF. Please try again.
                </div>
            `;
        });
}

/**
 * View all advisory students grades
 */
window.viewAllGrades = function() {
    const modal = new bootstrap.Modal(document.getElementById('allGradesModal'));
    const modalContent = document.getElementById('allGradesModalContent');
    
    // Reset modal content
    modalContent.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading all grades...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch all advisory grades via AJAX
    fetch('/teacher/advisory/all-grades')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalContent.innerHTML = data.html;
            } else {
                modalContent.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="ri-information-line me-2"></i>
                        ${data.message || 'No grades found for advisory students.'}
                    </div>
                `;
            }
        })
        .catch(error => {
            modalContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    Error loading grades. Please try again.
                </div>
            `;
        });
}

/**
 * Print individual report card
 */
window.printReportCard = function(studentId) {
    // Show loading state
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Generating...';
    button.disabled = true;

    // Open the PDF directly in a new tab for printing (no blob)
    const url = `/teacher/report-card/pdf/${studentId}`;
    const printWindow = window.open(url, '_blank');
    if (printWindow) {
        printWindow.onload = function() {
            printWindow.print();
        };
    }
    // Restore button state after a short delay (since we don't know when print finishes)
    setTimeout(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    }, 2000);
}

/**
 * Print all report cards
 * Improved: robust event handling and endpoint
 */
window.printAllReportCards = function(event) {
    if (!confirm('This will generate report cards for all advisory students. Continue?')) {
        return;
    }
    // Support both direct and event-callback usage
    let button = event && event.target ? event.target : document.activeElement;
    if (!button || button.tagName !== 'BUTTON') {
        button = document.querySelector('button[onclick*="printAllReportCards"]');
    }
    const originalContent = button ? button.innerHTML : '';
    if (button) {
        button.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Generating...';
        button.disabled = true;
    }
    // Use the correct endpoint for all report cards PDF
    fetch('/teacher/report-cards/print-all')
        .then(response => {
            if (response.ok) {
                return response.blob();
            }
            throw new Error('Failed to generate report cards');
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const printWindow = window.open(url, '_blank');
            if (printWindow) {
                printWindow.onload = function() {
                    printWindow.print();
                };
            }
        })
        .catch(error => {
            alert('Error generating report cards. Please try again.');
        })
        .finally(() => {
            if (button) {
                button.innerHTML = originalContent;
                button.disabled = false;
            }
        });
}

/**
 * Print current grades from modal
 */
window.printCurrentGrades = function() {
    const modalContent = document.getElementById('gradesModalContent');
    const printContent = modalContent.innerHTML;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Student Grades</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        .no-print { display: none !important; }
                        body { font-size: 12px; }
                    }
                </style>
            </head>
            <body>
                <div class="container-fluid">
                    <h3 class="text-center mb-4">Student Grades Report</h3>
                    ${printContent}
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

/**
 * Print all grades from modal
 */
window.printAllGrades = function() {
    const modalContent = document.getElementById('allGradesModalContent');
    const printContent = modalContent.innerHTML;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Advisory Class Grades</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        .no-print { display: none !important; }
                        body { font-size: 12px; }
                        table { font-size: 10px; }
                    }
                </style>
            </head>
            <body>
                <div class="container-fluid">
                    <h3 class="text-center mb-4">Advisory Class Grades Report</h3>
                    ${printContent}
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

