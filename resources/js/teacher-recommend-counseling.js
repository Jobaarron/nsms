// Teacher Recommend Counseling JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeRecommendCounseling();
});

function initializeRecommendCounseling() {
    // Only initialize if we're on the recommend counseling page
    if (!document.getElementById('studentSearch')) {
        return;
    }

    // Get students data from the page (will be populated by Laravel)
    const students = window.studentsData || [];
    
    // Get DOM elements
    const studentSearch = document.getElementById('studentSearch');
    const studentSuggestions = document.getElementById('studentSuggestions');
    const studentIdInput = document.getElementById('student_id');

    if (!studentSearch || !studentSuggestions || !studentIdInput) {
        console.warn('Required elements not found for recommend counseling functionality');
        return;
    }

    // Student search functionality
    studentSearch.addEventListener('input', function() {
        const term = this.value.toLowerCase();
        if (!term) {
            studentSuggestions.style.display = 'none';
            return;
        }
        
        const matches = students.filter(s =>
            s.name.toLowerCase().includes(term) ||
            s.student_id.toLowerCase().includes(term)
        );
        
        if (matches.length === 0) {
            studentSuggestions.innerHTML = '<div class="p-3 text-muted text-center"><i class="ri-search-line me-1"></i>No matches found</div>';
        } else {
            studentSuggestions.innerHTML = matches.map(s =>
                `<div class='p-3 suggestion-item border-bottom' style='cursor:pointer; transition: background-color 0.2s;' 
                      data-id='${s.id}' data-name='${s.name}' data-studentid='${s.student_id}'
                      onmouseover='this.style.backgroundColor="#f8f9fa"' 
                      onmouseout='this.style.backgroundColor="white"'>
                   <i class="ri-user-line me-2 text-muted"></i>${s.name} 
                   <small class="text-muted">(${s.student_id})</small>
                 </div>`
            ).join('');
        }
        studentSuggestions.style.display = 'block';
    });

    // Handle suggestion selection
    studentSuggestions.addEventListener('mousedown', function(e) {
        if (e.target.classList.contains('suggestion-item')) {
            studentSearch.value = e.target.getAttribute('data-name') + ' (' + e.target.getAttribute('data-studentid') + ')';
            studentIdInput.value = e.target.getAttribute('data-id');
            studentSuggestions.style.display = 'none';
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!studentSuggestions.contains(e.target) && e.target !== studentSearch) {
            studentSuggestions.style.display = 'none';
        }
    });

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
