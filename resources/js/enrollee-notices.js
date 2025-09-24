document.addEventListener('DOMContentLoaded', function() {
    // Initialize notices functionality
    initializeNoticesPage();
});

function markAsRead(noticeId) {
    // Find the row and remove unread styling
    const row = document.querySelector(`tr:has(span:contains('${noticeId}'))`);
    if (row) {
        row.classList.remove('table-warning');
        // Update unread count
        updateUnreadCount();
    }
    
    // Here you would typically send an AJAX request to mark as read
    console.log('Marking notice ' + noticeId + ' as read');
}

function markAllAsRead() {
    // Remove all unread styling
    const unreadRows = document.querySelectorAll('.table-warning');
    unreadRows.forEach(row => {
        row.classList.remove('table-warning');
    });
    
    // Update unread count
    updateUnreadCount();
    
    // Here you would typically send an AJAX request to mark all as read
    console.log('Marking all notices as read');
}

function refreshNotices() {
    // Reload the page or fetch new notices via AJAX
    location.reload();
}

function updateUnreadCount() {
    const unreadRows = document.querySelectorAll('.table-warning');
    const unreadCount = unreadRows.length;
    
    // Update badge
    const badge = document.querySelector('.badge.bg-info');
    if (badge) {
        badge.innerHTML = `<i class="ri-notification-line me-1"></i>${unreadCount} Unread`;
    }
    
    // Update sidebar count
    const sidebarUnread = document.querySelector('.text-warning').previousElementSibling;
    if (sidebarUnread) {
        sidebarUnread.textContent = unreadCount;
    }
}

function initializeNoticesPage() {
    // Auto-refresh notices every 30 seconds
    setInterval(function() {
        // In a real application, you would fetch new notices via AJAX
        console.log('Auto-refreshing notices...');
    }, 30000);
    
    // Add any other initialization code here
    console.log('Notices page initialized');
}

// Make functions globally available for onclick handlers
window.markAsRead = markAsRead;
window.markAllAsRead = markAllAsRead;
window.refreshNotices = refreshNotices;