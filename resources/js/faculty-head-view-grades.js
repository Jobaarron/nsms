/**
 * Faculty Head View Grades JavaScript
 * Handles grade viewing and filtering functionality
 */

// Faculty Head Grade Submissions Filtering
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const selectedValue = this.value;
            const tableRows = document.querySelectorAll('#submissionsTable tbody tr');
            
            tableRows.forEach(function(row) {
                // Get all badges and find the status badge (not the STEM badge)
                const allBadges = row.querySelectorAll('.badge');
                let statusText = '';
                
                // Look for the status badge
                allBadges.forEach(function(badge) {
                    const badgeText = badge.textContent.trim();
                    if (badgeText === 'Approved' || badgeText === 'Revised' || badgeText === 'Under Review') {
                        statusText = badgeText;
                    }
                });
                
                let shouldShow = false;
                
                switch(selectedValue) {
                    case 'all':
                        shouldShow = true;
                        break;
                    case 'submitted':
                        shouldShow = (statusText === 'Under Review');
                        break;
                    case 'approved':
                        shouldShow = (statusText === 'Approved');
                        break;
                    case 'revised':
                        shouldShow = (statusText === 'Revised');
                        break;
                }
                
                row.style.display = shouldShow ? 'table-row' : 'none';
            });
        });
        
        // Initialize with 'all' filter
        statusFilter.dispatchEvent(new Event('change'));
    }
});
