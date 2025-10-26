/**
 * Faculty Head - Assign Teacher JavaScript
 * CLEAN EXTERNAL VERSION - Loaded in HEAD
 */

console.log('=== EXTERNAL FACULTY HEAD SCRIPT LOADING ===');

function removeAssignment(assignmentId) {
    console.log('=== EXTERNAL removeAssignment called with ID:', assignmentId);
    
    if (!confirm('Are you sure you want to remove this assignment?')) {
        return false;
    }
    
    // Show loading state
    const button = event.target.closest('button');
    let originalText = '';
    if (button) {
        originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Removing...';
    }
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        alert('CSRF token not found. Please refresh the page.');
        if (button) {
            button.disabled = false;
            button.innerHTML = originalText;
        }
        return false;
    }
    
    // Make AJAX request
    fetch('/faculty-head/remove-assignment/' + assignmentId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Success: ' + data.message);
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to remove assignment');
        }
    })
    .catch(error => {
        console.error('Error removing assignment:', error);
        alert('Error: ' + error.message);
        
        // Restore button state
        if (button) {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    });
    
    return true;
}

// Make function globally available
window.removeAssignment = removeAssignment;

// Handle grade level change to show/hide strand and track fields
document.addEventListener('DOMContentLoaded', function() {
    const gradeLevelSelect = document.querySelector('select[name="grade_level"]');
    const strandField = document.getElementById('strandField');
    const trackField = document.getElementById('trackField');
    const strandSelect = document.querySelector('select[name="strand"]');
    const trackSelect = document.querySelector('select[name="track"]');
    
    if (gradeLevelSelect) {
        gradeLevelSelect.addEventListener('change', function() {
            const gradeLevel = this.value;
            
            // Show strand field for Grade 11 and Grade 12
            if (gradeLevel === 'Grade 11' || gradeLevel === 'Grade 12') {
                strandField.style.display = 'block';
                strandSelect.required = true;
            } else {
                strandField.style.display = 'none';
                trackField.style.display = 'none';
                strandSelect.required = false;
                strandSelect.value = '';
                trackSelect.required = false;
                trackSelect.value = '';
            }
        });
    }
    
    if (strandSelect) {
        strandSelect.addEventListener('change', function() {
            const strand = this.value;
            
            // Show track field only for TVL strand
            if (strand === 'TVL') {
                trackField.style.display = 'block';
                trackSelect.required = true;
            } else {
                trackField.style.display = 'none';
                trackSelect.required = false;
                trackSelect.value = '';
            }
        });
    }
});

console.log('=== EXTERNAL FACULTY HEAD SCRIPT LOADED ===');
console.log('window.removeAssignment type:', typeof window.removeAssignment);

document.addEventListener('DOMContentLoaded', function() {
    const gradeSelect = document.getElementById('grade_level');
    const subjectSelect = document.getElementById('subject_id');
    
    if (gradeSelect && subjectSelect) {
        
        // Function to jump to subjects for selected grade
        function jumpToGradeSubjects() {
            const selectedGrade = gradeSelect.value;
            
            if (!selectedGrade) {
                // Reset selection if no grade selected
                subjectSelect.value = '';
                return;
            }
            
            // Find the first subject option for the selected grade
            const firstSubjectForGrade = subjectSelect.querySelector(`option[data-grade="${selectedGrade}"]`);
            
            if (firstSubjectForGrade) {
                // Auto-select the first subject of the selected grade
                subjectSelect.value = firstSubjectForGrade.value;
                
                // Add visual feedback
                subjectSelect.style.borderColor = '#28a745';
                subjectSelect.style.backgroundColor = '#f8f9fa';
                
                // Create a brief highlight effect
                setTimeout(() => {
                    subjectSelect.style.borderColor = '';
                    subjectSelect.style.backgroundColor = '';
                }, 1500);
                
                // Show a subtle notification
                const notification = document.createElement('div');
                notification.textContent = `Auto-selected first ${selectedGrade} subject`;
                notification.style.cssText = `
                    position: absolute;
                    top: -25px;
                    left: 0;
                    background: #28a745;
                    color: white;
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 12px;
                    z-index: 1000;
                    opacity: 0.9;
                `;
                
                subjectSelect.parentElement.style.position = 'relative';
                subjectSelect.parentElement.appendChild(notification);
                
                // Remove notification after 2 seconds
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.parentElement.removeChild(notification);
                    }
                }, 2000);
            }
        }
        
        // Trigger when grade level changes
        gradeSelect.addEventListener('change', function() {
            jumpToGradeSubjects();
        });
        
        // Jump on page load if grade is already selected
        if (gradeSelect.value) {
            jumpToGradeSubjects();
        }
    }
});
