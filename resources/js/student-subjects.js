// Student Subjects Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get filter buttons and subject rows
    const filterButtons = document.querySelectorAll('[data-filter]');
    const subjectRows = document.querySelectorAll('.subject-row');

    // Add click event listeners to filter buttons
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter subject rows
            subjectRows.forEach(row => {
                const rowType = row.getAttribute('data-type');
                
                if (filter === 'all') {
                    row.style.display = '';
                } else if (filter === 'core' && rowType === 'core') {
                    row.style.display = '';
                } else if (filter === 'specialized' && rowType === 'specialized') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update visible count
            updateVisibleCount();
        });
    });
    
    // Function to update visible subject count
    function updateVisibleCount() {
        const visibleRows = document.querySelectorAll('.subject-row:not([style*="display: none"])');
        const countElement = document.querySelector('.subjects-count');
        
        if (countElement) {
            countElement.textContent = visibleRows.length;
        }
    }
    
    // Initialize count
    updateVisibleCount();
});
