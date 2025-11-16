// Teacher Recommend Counseling JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeRecommendCounseling();
});

function initializeRecommendCounseling() {
    // Only initialize if we're on the recommend counseling page
    if (!document.getElementById('studentSearch')) {
        return;
    }

    // Get advisory students data from the page (will be populated by Laravel)
    const students = window.advisoryStudentsData || [];
    
    // Get DOM elements
    const studentSearch = document.getElementById('studentSearch');
    const studentSuggestions = document.getElementById('studentSuggestions');
    const studentIdInput = document.getElementById('student_id');

    if (!studentSearch || !studentSuggestions || !studentIdInput) {
        console.warn('Required elements not found for recommend counseling functionality');
        return;
    }

    // Student search functionality with debouncing
    let searchTimeout;
    studentSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const term = this.value.toLowerCase().trim();
        
        // Clear validation states when typing
        this.classList.remove('is-valid', 'is-invalid');
        studentIdInput.value = '';
        
        if (!term || term.length < 2) {
            studentSuggestions.style.display = 'none';
            return;
        }
        
        // Debounce the search
        searchTimeout = setTimeout(() => {
            const matches = students.filter(s =>
                (s.name && s.name.toLowerCase().includes(term)) ||
                (s.student_id && s.student_id.toLowerCase().includes(term))
            );
            
            if (matches.length === 0) {
                studentSuggestions.innerHTML = '<div class="p-3 text-muted text-center"><i class="ri-search-line me-1"></i>No matches found in your advisory class</div>';
            } else {
                studentSuggestions.innerHTML = matches.map(s =>
                    `<div class='p-2 suggestion-item border-bottom' style='cursor:pointer; transition: background-color 0.2s;' 
                          data-id='${s.id}' data-name='${s.name}' data-studentid='${s.student_id}'
                          onmouseover='this.style.backgroundColor="#f8f9fa"' 
                          onmouseout='this.style.backgroundColor="white"'>
                       <div class="d-flex align-items-center">
                         <i class="ri-user-line me-2 text-muted"></i>
                         <div>
                           <div class="fw-medium">${s.name}</div>
                           <small class="text-muted">ID: ${s.student_id}</small>
                         </div>
                       </div>
                     </div>`
                ).join('');
            }
            studentSuggestions.style.display = 'block';
        }, 300);
    });

    // Handle suggestion selection
    studentSuggestions.addEventListener('click', function(e) {
        const suggestionItem = e.target.closest('.suggestion-item');
        if (suggestionItem) {
            const studentId = suggestionItem.getAttribute('data-id');
            const studentName = suggestionItem.getAttribute('data-name');
            const studentIdNum = suggestionItem.getAttribute('data-studentid');
            
            studentSearch.value = studentName + ' (' + studentIdNum + ')';
            studentIdInput.value = studentId;
            studentSuggestions.style.display = 'none';
            
            // Add visual feedback
            studentSearch.classList.add('is-valid');
            studentSearch.classList.remove('is-invalid');
        }
    });

    // Keyboard navigation
    let currentFocus = -1;
    studentSearch.addEventListener('keydown', function(e) {
        const suggestions = studentSuggestions.querySelectorAll('.suggestion-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus = currentFocus < suggestions.length - 1 ? currentFocus + 1 : 0;
            updateFocus(suggestions);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus = currentFocus > 0 ? currentFocus - 1 : suggestions.length - 1;
            updateFocus(suggestions);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentFocus >= 0 && suggestions[currentFocus]) {
                suggestions[currentFocus].click();
            }
        } else if (e.key === 'Escape') {
            studentSuggestions.style.display = 'none';
            currentFocus = -1;
        }
    });

    function updateFocus(suggestions) {
        suggestions.forEach((item, index) => {
            if (index === currentFocus) {
                item.style.backgroundColor = '#e9ecef';
            } else {
                item.style.backgroundColor = 'white';
            }
        });
    }

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!studentSuggestions.contains(e.target) && e.target !== studentSearch) {
            studentSuggestions.style.display = 'none';
            currentFocus = -1;
        }
    });

    // Form validation
    const form = studentSearch.closest('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!studentIdInput.value) {
                e.preventDefault();
                studentSearch.classList.add('is-invalid');
                studentSearch.focus();
                
                // Show custom error message
                let errorMsg = studentSearch.parentNode.querySelector('.invalid-feedback');
                if (!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'invalid-feedback';
                    studentSearch.parentNode.appendChild(errorMsg);
                }
                errorMsg.textContent = 'Please select a student from the suggestions.';
            }
        });
    }

    // Student selection change handler (for showing/hiding referral checklist)
    studentIdInput.addEventListener('change', function() {
        const studentId = this.value;
        const checklistDiv = document.getElementById('referral-checklist');
        if (checklistDiv) {
            if (studentId) {
                checklistDiv.style.display = 'block';
            } else {
                checklistDiv.style.display = 'none';
            }
        }
    });
}

// Export for potential use in other modules
window.initializeRecommendCounseling = initializeRecommendCounseling;
