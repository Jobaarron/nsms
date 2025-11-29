// Teacher Grade Entry JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the grade entry page
    const isGradeEntryPage = window.location.pathname.includes('/grades/submit/');
    
    if (!isGradeEntryPage) {
        return;
    }
    
    
    // Check if grade entry data is available
    if (window.gradeEntryData) {
    } else {
    }
    
    // Initialize auto-save functionality
    initializeAutoSave();
});

// Grade entry data is now directly injected via script tag in the Blade template

// Grade validation function
window.validateGrade = function(input) {
    const value = parseFloat(input.value);
    if (value < 0 || value > 100) {
        alert('Grade must be between 0 and 100');
        input.focus();
        return false;
    }
    
    // Add visual feedback for passing/failing grades
    if (value >= 75) {
        input.classList.remove('border-danger');
        input.classList.add('border-success');
    } else if (value > 0) {
        input.classList.remove('border-success');
        input.classList.add('border-danger');
    } else {
        input.classList.remove('border-success', 'border-danger');
    }
    
    return true;
};

// Submission confirmation function
window.confirmSubmission = function() {
    const filledGrades = document.querySelectorAll('.grade-input').length;
    const emptyGrades = Array.from(document.querySelectorAll('.grade-input')).filter(input => !input.value).length;
    
    if (emptyGrades > 0) {
        return confirm(`You have ${emptyGrades} empty grades out of ${filledGrades} students. Are you sure you want to submit for review?`);
    }
    
    return confirm('Are you sure you want to submit these grades for faculty head review? You won\'t be able to edit them once submitted.');
};

// Excel/CSV Upload Functions
window.uploadGradesFile = function() {
    const fileInput = document.getElementById('gradesFile');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Please select a file to upload');
        return;
    }
    
    // Get submission ID from the page data
    if (!window.gradeEntryData) {
        alert('Error: Grade entry data not found. Please refresh the page.');
        return;
    }
    
    const submissionId = window.gradeEntryData.submissionId;
    if (!submissionId) {
        alert('Error: Submission ID not found. Please refresh the page.');
        return;
    }
    
    const formData = new FormData();
    formData.append('grades_file', file);
    formData.append('submission_id', submissionId);
    
    // Show progress
    const progressElement = document.getElementById('uploadProgress');
    if (progressElement) {
        progressElement.style.display = 'block';
    }
    
    // Get upload route from page data
    const uploadRoute = window.gradeEntryData.uploadRoute;
    if (!uploadRoute) {
        alert('Error: Upload route not found. Please refresh the page.');
        return;
    }
    
    fetch(uploadRoute, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        return response.json().then(data => ({
            status: response.status,
            data: data
        }));
    })
    .then(result => {
        if (progressElement) {
            progressElement.style.display = 'none';
        }
        
        const { status, data } = result;
        
        // Handle both success (200) and validation error (422) responses
        if (status === 200 && data.success) {
            alert(`Success! Uploaded ${data.processed} grades out of ${data.total_expected} students`);
            location.reload(); // Reload to show updated grades
        } else {
            // Handle validation errors or other failures
            let errorMsg = data.message || 'Upload failed';
            
            // Handle validation errors from 422 response
            if (data.errors) {
                if (typeof data.errors === 'object' && !Array.isArray(data.errors)) {
                    // Object format: {field: [errors]}
                    const errorLines = [];
                    for (const field in data.errors) {
                        const fieldErrors = data.errors[field];
                        if (Array.isArray(fieldErrors)) {
                            errorLines.push(`${field}: ${fieldErrors.join(', ')}`);
                        } else {
                            errorLines.push(`${field}: ${fieldErrors}`);
                        }
                    }
                    errorMsg += '\n\nValidation Errors:\n' + errorLines.join('\n');
                } else if (Array.isArray(data.errors)) {
                    // Array format: [error1, error2]
                    errorMsg += '\n\nDetailed Errors:\n' + data.errors.join('\n');
                }
            }
            
            if (data.total_rows) {
                errorMsg += `\n\nProcessed: ${data.processed || 0} out of ${data.total_rows} rows`;
            }
            
            alert(errorMsg);
        }
    })
    .catch(error => {
        if (progressElement) {
            progressElement.style.display = 'none';
        }
        alert('Upload failed: ' + error.message);
    });
};

// Template download function
window.downloadTemplate = function() {
    // Get template data from the page
    if (!window.gradeEntryData || !window.gradeEntryData.templateData) {
        alert('Error: Template data not found. Please refresh the page.');
        return;
    }
    
    const templateData = window.gradeEntryData.templateData;
    
    // Create CSV template with ALL actual students from this class
    let csvContent = 'student_id,last_name,first_name,middle_name,grade,remarks\n';
    
    if (templateData.students && templateData.students.length > 0) {
        // Include ALL students enrolled in this class/subject
        templateData.students.forEach(student => {
            csvContent += `${student.student_id},${student.last_name},${student.first_name},${student.middle_name || ''},${student.existing_grade || ''},${student.existing_remarks || ''}\n`;
        });
        
        // Show success message with student count
        const studentCount = templateData.students.length;
        const className = templateData.className;
        
        setTimeout(function() {
            alert(`Template downloaded with ${studentCount} students from ${className}.\n\nThe template includes:\n• All enrolled students in this class\n• Existing grades (if any)\n• Student names and IDs\n\nSimply fill in the grades and upload!`);
        }, 100);
    } else {
        // Fallback template if no students found
        csvContent += 'NS-25001,Dela Cruz,Juan,Santos,,Optional remarks\n';
        csvContent += 'NS-25002,Garcia,Maria,Lopez,,Optional remarks\n';
        csvContent += 'NS-25003,Reyes,Pedro,,,Optional remarks\n';
        
        alert('No students found in this class. Downloaded a sample template instead.\n\nPlease contact the registrar to ensure students are properly enrolled.');
    }
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    
    // Create descriptive filename with class info
    const fileName = templateData.fileName || 'grades_template.csv';
    a.download = fileName;
    a.click();
    window.URL.revokeObjectURL(url);
};

// Make initializeAutoSave global
window.initializeAutoSave = function() {
    let autoSaveTimer;
    
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('grade-input')) {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                // Could implement auto-save here
            }, 2000);
        }
    });
    
}

// Enhanced grade validation with real-time feedback
window.enhanceGradeInputs = function() {
    const gradeInputs = document.querySelectorAll('.grade-input');
    
    gradeInputs.forEach(input => {
        // Add real-time validation
        input.addEventListener('input', function() {
            validateGrade(this);
        });
        
        // Add focus/blur effects
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
}

// Initialize enhanced features when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('/grades/submit/')) {
        window.enhanceGradeInputs();
    }
});

// Add CSS for enhanced styling
const style = document.createElement('style');
style.textContent = `
    .grade-input.border-success {
        border-color: #198754 !important;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
    }
    
    .grade-input.border-danger {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    
    .focused {
        transform: scale(1.02);
        transition: transform 0.2s ease;
    }
    
    .grade-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
`;
document.head.appendChild(style);
