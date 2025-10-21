// Teacher Grades JavaScript - Global Namespace
window.TeacherGrades = window.TeacherGrades || {};

// Global functions to prevent console errors
window.initializeGrades = function() {
    // Add hover effects to grade cards
    const gradeCards = document.querySelectorAll('.grade-card');
    gradeCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
        });
    });
    
    // Initialize grade submission forms
    window.initializeGradeForms();
};

window.initializeGradeForms = function() {
    // Handle grade submission forms
    const gradeSubmissionForms = document.querySelectorAll('.grade-submission-form');
    gradeSubmissionForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            window.handleGradeSubmission(this);
        });
    });
    
    // Handle file upload for bulk grades
    const fileUploadInputs = document.querySelectorAll('.grade-file-upload');
    fileUploadInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            window.handleGradeFileUpload(e.target.files[0]);
        });
    });
};

window.handleGradeSubmission = function(form) {
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Show loading state
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="ri-loader-2-line spinner me-2"></i>Submitting...';
    }
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessMessage('Grades submitted successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            window.showErrorMessage(data.message || 'Error submitting grades');
        }
    })
    .catch(error => {
        console.error('Grade submission error:', error);
        window.showErrorMessage('Error submitting grades. Please try again.');
    })
    .finally(() => {
        // Reset button state
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="ri-send-plane-line me-2"></i>Submit Grades';
        }
    });
};

window.handleGradeFileUpload = function(file) {
    if (!file) return;
    
    // Validate file type
    const allowedTypes = ['.xlsx', '.xls', '.csv'];
    const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
    
    if (!allowedTypes.includes(fileExtension)) {
        window.showErrorMessage('Please upload an Excel (.xlsx, .xls) or CSV file.');
        return;
    }
    
    // Show upload progress
    window.showUploadProgress();
    
    const formData = new FormData();
    formData.append('grade_file', file);
    
    fetch('/teacher/grades/upload', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        window.hideUploadProgress();
        
        if (data.success) {
            window.showSuccessMessage('Grades uploaded successfully!');
            window.displayUploadedGrades(data.grades);
        } else {
            window.showErrorMessage(data.message || 'Error uploading grades');
        }
    })
    .catch(error => {
        console.error('File upload error:', error);
        window.hideUploadProgress();
        window.showErrorMessage('Error uploading file. Please try again.');
    });
};

window.showSuccessMessage = function(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.main-content') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
};

window.showErrorMessage = function(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.main-content') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
};

window.showUploadProgress = function() {
    const progressDiv = document.createElement('div');
    progressDiv.id = 'upload-progress';
    progressDiv.className = 'alert alert-info';
    progressDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
            <span>Uploading grades file...</span>
        </div>
    `;
    
    const container = document.querySelector('.main-content') || document.body;
    container.insertBefore(progressDiv, container.firstChild);
};

window.hideUploadProgress = function() {
    const progressDiv = document.getElementById('upload-progress');
    if (progressDiv) {
        progressDiv.remove();
    }
};

window.displayUploadedGrades = function(grades) {
    // Display uploaded grades in a table or list
    const gradesContainer = document.getElementById('uploaded-grades-container');
    if (!gradesContainer) return;
    
    let gradesHTML = '<div class="table-responsive"><table class="table table-striped">';
    gradesHTML += '<thead><tr><th>Student ID</th><th>Student Name</th><th>Grade</th><th>Remarks</th></tr></thead>';
    gradesHTML += '<tbody>';
    
    grades.forEach(grade => {
        gradesHTML += `
            <tr>
                <td>${grade.student_id || 'N/A'}</td>
                <td>${grade.student_name || 'N/A'}</td>
                <td>${grade.grade || 'N/A'}</td>
                <td>${grade.remarks || ''}</td>
            </tr>
        `;
    });
    
    gradesHTML += '</tbody></table></div>';
    gradesContainer.innerHTML = gradesHTML;
};

window.filterGradeSubmissions = function(status) {
    const submissionRows = document.querySelectorAll('.submission-row');
    
    submissionRows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update active filter button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeBtn = document.querySelector(`[data-filter="${status}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
};

window.setupGradeEventHandlers = function() {
    // Filter buttons
    const filterButtons = document.querySelectorAll('.grade-filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterStatus = this.dataset.filter;
            window.filterGradeSubmissions(filterStatus);
        });
    });
    
    // Submit grade buttons
    const submitButtons = document.querySelectorAll('.submit-grade-btn');
    submitButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const submissionId = this.dataset.submissionId;
            if (submissionId) {
                window.submitGradeForReview(submissionId);
            }
        });
    });
    
    // Delete draft buttons
    const deleteButtons = document.querySelectorAll('.delete-draft-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const submissionId = this.dataset.submissionId;
            if (submissionId && confirm('Are you sure you want to delete this draft?')) {
                window.deleteDraftSubmission(submissionId);
            }
        });
    });
};

window.submitGradeForReview = function(submissionId) {
    fetch(`/teacher/grades/${submissionId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessMessage('Grades submitted for review successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            window.showErrorMessage(data.message || 'Error submitting grades for review');
        }
    })
    .catch(error => {
        console.error('Submit for review error:', error);
        window.showErrorMessage('Error submitting grades for review. Please try again.');
    });
};

window.deleteDraftSubmission = function(submissionId) {
    fetch(`/teacher/grades/${submissionId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessMessage('Draft deleted successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            window.showErrorMessage(data.message || 'Error deleting draft');
        }
    })
    .catch(error => {
        console.error('Delete draft error:', error);
        window.showErrorMessage('Error deleting draft. Please try again.');
    });
};

// Add CSS for animations and styling
const style = document.createElement('style');
style.textContent = `
    .grade-card {
        transition: all 0.3s ease;
    }
    
    .spinner {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .filter-btn.active {
        background-color: #0d6efd;
        color: white;
    }
    
    .submission-row {
        transition: opacity 0.3s ease;
    }
    
    .grade-file-upload {
        cursor: pointer;
    }
    
    .upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .upload-area:hover {
        border-color: #0d6efd;
        background-color: #f8f9fa;
    }
    
    .upload-area.dragover {
        border-color: #0d6efd;
        background-color: #e7f3ff;
    }
`;
document.head.appendChild(style);

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Initialize grades functionality
        if (typeof window.initializeGrades === 'function') {
            window.initializeGrades();
        }
        
        // Set up event handlers
        if (typeof window.setupGradeEventHandlers === 'function') {
            window.setupGradeEventHandlers();
        }
    } catch (error) {
        console.log('Teacher Grades initialization error:', error);
    }
});
