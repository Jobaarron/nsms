/**
 * Teacher Advisory JavaScript
 * Handles view grades and print report card functionality
 * 
 * Grade-Level Routing:
 * - Grade 1 & Grade 2: Uses elementary route (/teacher/report-card/elementary/pdf/{student})
 * - Grade 10: Uses high school route (/teacher/report-card/pdf/{student}) 
 * - All other grades: Uses regular high school route (/teacher/report-card/pdf/{student})
 * 
 * Print All Report Cards: Specifically configured for Grade 10 Section A students only
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
        viewStudentGrades(1, 'Grade 11'); // Test with Grade 11
        
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
            printReportCard(1, 'Grade 1'); // Test with Grade 1 (elementary)
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

    /**
     * Test grade-level routing
     */
    window.testGradeRouting = function() {
        console.log('=== Testing Grade-Level Routing ===');
        
        // Test Grade 1 (should use elementary route)
        console.log('Testing Grade 1:', getReportCardRoute(1, 'Grade 1'));
        
        // Test Grade 2 (should use elementary route)
        console.log('Testing Grade 2:', getReportCardRoute(2, 'Grade 2'));
        
        // Test Grade 10 (should use high school route)
        console.log('Testing Grade 10:', getReportCardRoute(3, 'Grade 10'));
        
        // Test Grade 11 (should use high school route)
        console.log('Testing Grade 11:', getReportCardRoute(4, 'Grade 11'));
        
        // Test Grade 12 (should use high school route)
        console.log('Testing Grade 12:', getReportCardRoute(5, 'Grade 12'));
        
        // Test Grade 7 (should use high school route)
        console.log('Testing Grade 7:', getReportCardRoute(6, 'Grade 7'));
        
        console.log('Grade routing test completed. Check console for results.');
    };
});

/**
 * Determine the correct report card route based on grade level
 */
function getReportCardRoute(studentId, gradeLevel) {
    console.log(`Determining route for Student ID: ${studentId}, Grade Level: ${gradeLevel}`);
    
    // Check if it's Grade 1 or Grade 2 for elementary route
    if (gradeLevel === 'Grade 1' || gradeLevel === 'Grade 2') {
        const route = `/teacher/report-card/elementary/pdf/${studentId}`;
        console.log(`Using elementary route: ${route}`);
        return route;
    }
    
    // Check if it's Grade 10 for high school route
    if (gradeLevel === 'Grade 10') {
        const route = `/teacher/report-card/pdf/${studentId}`;
        console.log(`Using Grade 10 high school route: ${route}`);
        return route;
    }
    
    // Default to high school route for all other grades
    const route = `/teacher/report-card/pdf/${studentId}`;
    console.log(`Using high school route: ${route}`);
    return route;
}

/**
 * View individual student grades
 */
window.viewStudentGrades = function(studentId, gradeLevel) {
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
    // Use the appropriate route based on grade level (fallback to high school route if no grade level)
    const reportCardUrl = gradeLevel ? getReportCardRoute(studentId, gradeLevel) : `/teacher/report-card/pdf/${studentId}`;
    fetch(reportCardUrl)
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
window.printReportCard = function(studentId, gradeLevel) {
    // Show loading state
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Generating...';
    button.disabled = true;

    // Fetch the PDF as a blob and show print dialog directly
    // Use the appropriate route based on grade level (fallback to high school route if no grade level)
    const url = gradeLevel ? getReportCardRoute(studentId, gradeLevel) : `/teacher/report-card/pdf/${studentId}`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch PDF');
            return response.blob();
        })
        .then(blob => {
            const pdfUrl = URL.createObjectURL(blob);
            
            // Calculate center position for the popup window
            const windowWidth = 900;
            const windowHeight = 700;
            const screenWidth = window.screen.width;
            const screenHeight = window.screen.height;
            const left = (screenWidth - windowWidth) / 2;
            const top = (screenHeight - windowHeight) / 2;
            
            // Create a new window with the PDF for printing, centered on screen
            const printWindow = window.open('', '_blank', `width=${windowWidth},height=${windowHeight},left=${left},top=${top},scrollbars=yes,resizable=yes`);
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Report Card</title>
                        <style>
                            body { margin: 0; padding: 0; }
                            iframe { width: 100%; height: 100vh; border: none; }
                        </style>
                    </head>
                    <body>
                        <iframe src="${pdfUrl}" onload="setTimeout(() => window.print(), 500);"></iframe>
                    </body>
                </html>
            `);
            printWindow.document.close();
            
            // Clean up when window is closed
            const checkClosed = setInterval(() => {
                if (printWindow.closed) {
                    URL.revokeObjectURL(pdfUrl);
                    clearInterval(checkClosed);
                }
            }, 1000);
            
        })
        .catch(error => {
            console.error('Error loading PDF for printing:', error);
            alert('Error loading report card. Please try again.');
        })
        .finally(() => {
            // Restore button state after a delay to allow PDF to load
            setTimeout(() => {
                button.innerHTML = originalContent;
                button.disabled = false;
            }, 1000);
        });
}

/**
 * Print all report cards for Grade 10 Section A
 * Improved: robust event handling and endpoint
 */
window.printAllReportCards = function(event) {
    if (!confirm('This will generate report cards for all Grade 10 Section A students. Continue?')) {
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