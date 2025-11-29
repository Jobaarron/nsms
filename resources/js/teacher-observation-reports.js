/**
 * Teacher Observation Reports JavaScript
 * Handles functionality for teacher observation report page
 */

document.addEventListener('DOMContentLoaded', function() {
    let currentCaseMeetingId = null;
    
    // Initialize the observation reports functionality
    initializeObservationReports();

    /**
     * Initialize all observation report functionalities
     */
    function initializeObservationReports() {
        initializeReplyButtons();
        initializePdfViewers();
        initializeSearch();
        initializeModals();
        markAlertAsViewed();
    }

    /**
     * Initialize reply button functionality
     */
    function initializeReplyButtons() {
        // Handle Reply button click to populate modal and set form action
        document.querySelectorAll('.reply-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                // Skip if button is disabled
                if (btn.disabled || btn.classList.contains('disabled')) {
                    e.preventDefault();
                    return false;
                }
                
                currentCaseMeetingId = btn.getAttribute('data-id');
                const teacherStatement = btn.getAttribute('data-teacher_statement') || '';
                const actionPlan = btn.getAttribute('data-action_plan') || '';
                
                
                // Populate form fields with existing data
                const teacherStatementField = document.getElementById('teacher_statement');
                const actionPlanField = document.getElementById('action_plan');
                const submitReplyBtn = document.getElementById('submitReplyBtn');
                
                if (teacherStatementField) teacherStatementField.value = teacherStatement;
                if (actionPlanField) actionPlanField.value = actionPlan;
                
                // Reset button states
                resetButtonStates();
                
                // Check if already replied (has existing data)
                if (submitReplyBtn) {
                    if (teacherStatement || actionPlan) {
                        submitReplyBtn.textContent = 'Update Reply';
                    } else {
                        submitReplyBtn.textContent = 'Submit Reply';
                    }
                }
                
                // Manually open modal in case Bootstrap attributes don't work
                const replyModal = document.getElementById('replyModal');
                if (replyModal && currentCaseMeetingId) {
                    const modalInstance = new bootstrap.Modal(replyModal);
                    modalInstance.show();
                }
            });
        });

        // Handle Submit Reply button click - show confirmation
        const submitReplyBtn = document.getElementById('submitReplyBtn');
        if (submitReplyBtn) {
            submitReplyBtn.addEventListener('click', function() {
                
                const teacherStatement = document.getElementById('teacher_statement').value.trim();
                const actionPlan = document.getElementById('action_plan').value.trim();
                
                
                // Validate required fields
                if (!teacherStatement || !actionPlan) {
                    showAlert('Please fill in both Teacher Statement and Action Plan before submitting.', 'warning');
                    return;
                }
                
                // Validate case meeting ID
                if (!currentCaseMeetingId) {
                    showAlert('Error: No case meeting selected. Please try again.', 'error');
                    return;
                }
                
                // Store case meeting ID in confirmation modal for backup
                const confirmationModalEl = document.getElementById('confirmationModal');
                if (confirmationModalEl && currentCaseMeetingId) {
                    confirmationModalEl.setAttribute('data-case-meeting-id', currentCaseMeetingId);
                }
                
                // Populate confirmation modal
                document.getElementById('confirmTeacherStatement').textContent = teacherStatement;
                document.getElementById('confirmActionPlan').textContent = actionPlan;
                
                // Hide reply modal and show confirmation
                const replyModal = bootstrap.Modal.getInstance(document.getElementById('replyModal'));
                const confirmationModal = new bootstrap.Modal(confirmationModalEl);
                
                replyModal.hide();
                setTimeout(() => {
                    confirmationModal.show();
                }, 300);
            });
        }

        // Handle final confirmation submit
        const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
        if (confirmSubmitBtn) {
            confirmSubmitBtn.addEventListener('click', function() {
                // Try to get case meeting ID from confirmation modal if not available
                if (!currentCaseMeetingId) {
                    const confirmationModal = document.getElementById('confirmationModal');
                    currentCaseMeetingId = confirmationModal ? confirmationModal.getAttribute('data-case-meeting-id') : null;
                }
                
                if (!currentCaseMeetingId) {
                    showAlert('Error: No case meeting ID found. Please try again.', 'error');
                    return;
                }
                
                submitObservationReply();
            });
        }
    }

    /**
     * Initialize PDF viewer functionality
     */
    function initializePdfViewers() {
        // Handle View Report button to show PDF in modal
        document.querySelectorAll('.view-pdf-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const pdfUrl = btn.getAttribute('data-pdf-url');
                const pdfFrame = document.getElementById('pdfFrame');
                if (pdfFrame) {
                    pdfFrame.src = pdfUrl;
                }
            });
        });

        // Clear PDF src when modal is closed (optional, for cleanup)
        const pdfModal = document.getElementById('pdfModal');
        if (pdfModal) {
            pdfModal.addEventListener('hidden.bs.modal', function () {
                const pdfFrame = document.getElementById('pdfFrame');
                if (pdfFrame) {
                    pdfFrame.src = '';
                }
            });
        }
    }

    /**
     * Initialize search functionality
     */
    function initializeSearch() {
        const searchInput = document.getElementById('reportSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.report-row');
                
                let visibleCount = 0;
                rows.forEach(function(row) {
                    const studentName = row.getAttribute('data-student') || '';
                    const violationTitle = row.getAttribute('data-violation') || '';
                    
                    if (studentName.includes(searchTerm) || violationTitle.includes(searchTerm)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Update search results indicator
                updateSearchResults(visibleCount, rows.length);
            });

            // Add clear search button functionality
            const clearSearchBtn = document.getElementById('clearSearch');
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input'));
                });
            }
        }
    }

    /**
     * Initialize modal event listeners
     */
    function initializeModals() {
        // Reset button states when modals are hidden
        const confirmationModal = document.getElementById('confirmationModal');
        if (confirmationModal) {
            confirmationModal.addEventListener('hidden.bs.modal', function() {
                resetButtonStates();
            });
        }
        
        const replyModal = document.getElementById('replyModal');
        if (replyModal) {
            replyModal.addEventListener('hidden.bs.modal', function() {
                resetButtonStates();
                clearForm();
            });
        }
    }

    /**
     * Submit observation reply using fetch for better error handling
     */
    function submitObservationReply() {
        if (!currentCaseMeetingId) {
            showAlert('No case meeting ID found. Please try again.', 'error');
            return;
        }
        
        
        // Disable button and show spinner
        const submitBtn = document.getElementById('confirmSubmitBtn');
        const submitBtnText = document.getElementById('submitBtnText');
        const submitSpinner = document.getElementById('submitSpinner');
        
        if (submitBtn) submitBtn.disabled = true;
        if (submitBtnText) submitBtnText.textContent = 'Submitting...';
        if (submitSpinner) submitSpinner.style.display = 'inline-block';
        
        // Get form data from confirmation modal (where the final data is displayed)
        const confirmTeacherStatementEl = document.getElementById('confirmTeacherStatement');
        const confirmActionPlanEl = document.getElementById('confirmActionPlan');
        
        let teacherStatement = '';
        let actionPlan = '';
        
        // Try to get data from confirmation modal first (this is the final data)
        if (confirmTeacherStatementEl && confirmActionPlanEl) {
            teacherStatement = confirmTeacherStatementEl.textContent.trim();
            actionPlan = confirmActionPlanEl.textContent.trim();
            
        }
        
        // If confirmation modal data is empty, try to get from original form fields
        if (!teacherStatement || !actionPlan) {
            const modal = document.getElementById('replyModal');
            let teacherStatementField = document.getElementById('teacher_statement');
            let actionPlanField = document.getElementById('action_plan');
            
            // If fields not found by ID, try searching within the modal
            if ((!teacherStatementField || !actionPlanField) && modal) {
                teacherStatementField = teacherStatementField || modal.querySelector('#teacher_statement') || modal.querySelector('[name="teacher_statement"]');
                actionPlanField = actionPlanField || modal.querySelector('#action_plan') || modal.querySelector('[name="action_plan"]');
            }
            
            if (teacherStatementField && actionPlanField) {
                teacherStatement = teacherStatement || teacherStatementField.value.trim();
                actionPlan = actionPlan || actionPlanField.value.trim();
                
            }
        }
        
        if (!teacherStatement || !actionPlan) {
            showAlert('Please fill in all required fields before submitting.', 'error');
            resetButtonStates();
            return;
        }
        
        // Prepare form data
        const formData = new FormData();
        const csrfToken = getCSRFToken();
        
        formData.append('_token', csrfToken);
        formData.append('teacher_statement', teacherStatement);
        formData.append('action_plan', actionPlan);
        
        const actionUrl = `/teacher/observationreport/reply/${currentCaseMeetingId}`;
        
        // Submit using fetch
        fetch(actionUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.redirected) {
                // Handle redirect (success case)
                window.location.href = response.url;
                return;
            } else if (response.status === 422) {
                // Handle validation errors
                return response.json().then(errorData => {
                    
                    let errorMessage = 'Validation failed: ';
                    if (errorData.errors) {
                        const errors = Object.values(errorData.errors).flat();
                        errorMessage += errors.join(', ');
                    } else if (errorData.message) {
                        errorMessage += errorData.message;
                    } else {
                        errorMessage += 'Please check your input and try again.';
                    }
                    showAlert(errorMessage, 'error');
                    resetButtonStates();
                    throw new Error('Validation failed');
                });
            } else if (response.status === 403) {
                // Handle authorization errors
                return response.json().then(errorData => {
                    showAlert(errorData.message || 'You are not authorized to perform this action.', 'error');
                    resetButtonStates();
                    throw new Error('Authorization failed');
                });
            } else if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                showAlert(data.message, 'success');
                
                // Close modals
                const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
                const replyModal = bootstrap.Modal.getInstance(document.getElementById('replyModal'));
                
                if (confirmationModal) confirmationModal.hide();
                if (replyModal) replyModal.hide();
                
                // Reload page after a short delay to show the success message
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        })
        .catch(error => {
            if (error.message !== 'Validation failed') { // Don't show generic error for validation
                showAlert('Failed to submit reply. Please try again.', 'error');
                resetButtonStates();
            }
        });
    }

    /**
     * Reset button states
     */
    function resetButtonStates() {
        const submitBtn = document.getElementById('confirmSubmitBtn');
        const submitBtnText = document.getElementById('submitBtnText');
        const submitSpinner = document.getElementById('submitSpinner');
        
        if (submitBtn) submitBtn.disabled = false;
        if (submitBtnText) submitBtnText.textContent = 'Yes, Submit Reply';
        if (submitSpinner) submitSpinner.style.display = 'none';
    }

    /**
     * Clear form fields
     */
    function clearForm() {
        const teacherStatement = document.getElementById('teacher_statement');
        const actionPlan = document.getElementById('action_plan');
        
        if (teacherStatement) teacherStatement.value = '';
        if (actionPlan) actionPlan.value = '';
        
        currentCaseMeetingId = null;
    }

    /**
     * Update search results indicator
     */
    function updateSearchResults(visibleCount, totalCount) {
        let indicator = document.getElementById('searchResults');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'searchResults';
            indicator.className = 'text-muted small mt-2';
            const searchContainer = document.getElementById('reportSearch').parentElement;
            searchContainer.appendChild(indicator);
        }

        if (visibleCount === totalCount) {
            indicator.textContent = '';
        } else {
            indicator.textContent = `Showing ${visibleCount} of ${totalCount} reports`;
        }
    }

    /**
     * Get CSRF token from meta tag or form
     */
    function getCSRFToken() {
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        const inputToken = document.querySelector('input[name="_token"]');
        
        if (metaToken) {
            return metaToken.getAttribute('content');
        } else if (inputToken) {
            return inputToken.value;
        }
        return null;
    }

    /**
     * Show alert message
     */
    function showAlert(message, type = 'info') {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="ri-${type === 'error' ? 'error-warning' : type === 'warning' ? 'alert-triangle' : 'information'}-line me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Insert at the top of the container
        const container = document.querySelector('.container-fluid');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    }

    /**
     * Mark observation reports alert as viewed
     */
    function markAlertAsViewed() {
        try {
            const csrfToken = getCSRFToken();
            if (!csrfToken) {
                return;
            }
            
            fetch('/teacher/mark-alert-viewed', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    alert_type: 'observation_reports'
                })
            })
            .then(response => {
                if (!response.ok) {
                }
            })
            .catch(error => {});
        } catch(error) {
        }
    }

    // Export functions for global access if needed
    window.TeacherObservationReports = {
        resetButtonStates,
        clearForm,
        showAlert,
        markAlertAsViewed
    };
});