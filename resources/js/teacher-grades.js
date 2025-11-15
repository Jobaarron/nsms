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

// Show feedback modal function
window.showFeedback = function(notes) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-message-line me-2"></i>Faculty Head Feedback
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        <strong>Review Notes:</strong>
                    </div>
                    <p class="mb-0">${notes}</p>
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
};

// Handle subject selection to populate hidden fields
window.handleSubjectSelection = function() {
    const subjectSelect = document.getElementById('subjectSelect');
    const gradeLevelInput = document.getElementById('gradeLevel');
    const sectionInput = document.getElementById('section');
    
    console.log('Subject selection handler setup:');
    console.log('Subject select found:', !!subjectSelect);
    console.log('Grade level input found:', !!gradeLevelInput);
    console.log('Section input found:', !!sectionInput);
    
    if (subjectSelect && gradeLevelInput && sectionInput) {
        console.log('All elements found, attaching change listener');
        
        subjectSelect.addEventListener('change', function() {
            console.log('Subject selection changed');
            const selectedOption = this.options[this.selectedIndex];
            console.log('Selected option:', selectedOption);
            console.log('Selected value:', selectedOption.value);
            
            if (selectedOption.value) {
                // Get data attributes from selected option
                const gradeLevel = selectedOption.getAttribute('data-grade');
                const section = selectedOption.getAttribute('data-section');
                
                console.log('Data attributes:');
                console.log('data-grade:', gradeLevel);
                console.log('data-section:', section);
                
                // Populate hidden fields
                gradeLevelInput.value = gradeLevel || '';
                sectionInput.value = section || '';
                
                console.log('Hidden fields populated:');
                console.log('Grade Level input value:', gradeLevelInput.value);
                console.log('Section input value:', sectionInput.value);
                
                console.log('Selected subject:', selectedOption.value);
                console.log('Grade Level:', gradeLevel);
                console.log('Section:', section);
            } else {
                // Clear hidden fields if no subject selected
                gradeLevelInput.value = '';
                sectionInput.value = '';
                console.log('Cleared hidden fields');
            }
        });
    } else {
        console.log('Missing elements for subject selection handler');
    }
};

// Handle grade submission (modified for faculty head controlled quarters)
window.handleGradeSubmission = function() {
    const assignmentSelect = document.getElementById('assignmentSelect');
    
    if (!assignmentSelect || !assignmentSelect.value) {
        alert('Please select a subject first.');
        return;
    }
    
    const assignmentId = assignmentSelect.value;
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
                    // Multiple quarters active, show selection modal
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

// Handle create submission form (old approach - keeping for reference)
window.handleCreateSubmissionForm = function() {
    const form = document.getElementById('createSubmissionForm');
    
    if (form) {
        console.log('Create submission form found and handler attached');
        
        form.addEventListener('submit', function(e) {
            console.log('Form submission triggered');
            
            // Get form data for debugging
            const formData = new FormData(this);
            console.log('Form data:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            // Check if required fields are filled
            const subjectId = formData.get('subject_id');
            const quarter = formData.get('quarter');
            const gradeLevel = formData.get('grade_level');
            const section = formData.get('section');
            
            console.log('Validation check:');
            console.log('Subject ID:', subjectId);
            console.log('Quarter:', quarter);
            console.log('Grade Level:', gradeLevel);
            console.log('Section:', section);
            
            if (!subjectId) {
                e.preventDefault();
                alert('Please select a subject');
                console.log('Form blocked: No subject selected');
                return false;
            }
            
            if (!quarter) {
                e.preventDefault();
                alert('Please select a quarter');
                console.log('Form blocked: No quarter selected');
                return false;
            }
            
            if (!gradeLevel || !section) {
                e.preventDefault();
                alert('Grade level and section are required. Please select a subject first.');
                console.log('Form blocked: Missing grade level or section');
                return false;
            }
            
            console.log('Form validation passed, submitting...');
            console.log('Form action:', this.action);
            console.log('Form method:', this.method);
            // Let the form submit normally
        });
        
        // Also add click handler to button for additional debugging
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.addEventListener('click', function(e) {
                console.log('Submit button clicked');
                console.log('Button type:', this.type);
                console.log('Form valid:', form.checkValidity());
            });
        }
    } else {
        console.log('Create submission form NOT found');
    }
};

// Grade upload function - Define immediately for onclick access
window.uploadGrades = function(submissionId) {
    console.log('uploadGrades called with ID:', submissionId);
    if (!confirm('Are you sure you want to upload these grades? Once uploaded, grades will be visible to students.')) {
        return;
    }

    const button = event.target;
    const originalContent = button.innerHTML;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<i class="ri-loader-4-line me-1"></i>Uploading...';

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        window.showErrorMessage('CSRF token not found. Please refresh the page.');
        button.disabled = false;
        button.innerHTML = originalContent;
        return;
    }

    fetch(`/teacher/grades/${submissionId}/finalize`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showSuccessMessage(data.message);
            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(data.error || 'Failed to upload grades');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        window.showErrorMessage('Error: ' + error.message);
        
        // Restore button state
        button.disabled = false;
        button.innerHTML = originalContent;
    });
};

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
        
        // Handle subject selection for create submission form
        if (typeof window.handleSubjectSelection === 'function') {
            window.handleSubjectSelection();
        }
        
        // Handle create submission form
        if (typeof window.handleCreateSubmissionForm === 'function') {
            window.handleCreateSubmissionForm();
        }
        
        console.log('Teacher Grades JavaScript loaded with upload support');
        console.log('uploadGrades function available:', typeof window.uploadGrades);
    } catch (error) {
        console.log('Teacher Grades initialization error:', error);
    }
});

// Test function to verify script loading
window.testUpload = function() {
    console.log('Test function called - script is loaded');
    return 'Script loaded successfully';
};
