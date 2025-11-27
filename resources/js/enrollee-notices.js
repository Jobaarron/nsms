// Enrollee Notifications JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeNotificationsPage();
});

// Global variables
let currentNoticeId = null;

// Initialize notifications functionality
function initializeNotificationsPage() {
    console.log('Enrollee notifications page initialized');
    
    // Auto-refresh notices every 2 minutes
    setInterval(function() {
        console.log('Auto-checking for new notices...');
        // In production, this would fetch new notices via AJAX
    }, 120000);
}

// View notification in modal
function viewNotification(notificationId) {
    currentNoticeId = notificationId;
    
    // Fetch notification details
    fetch(`/enrollee/notices/${notificationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateNotificationModal(data.notice);
                new bootstrap.Modal(document.getElementById('notificationViewModal')).show();
                
                // Mark as read when viewed
                if (!data.notice.is_read) {
                    markAsRead(notificationId, false);
                }
            } else {
                showAlert('Error loading notification details', 'danger');
            }
        })
        .catch(error => {
            console.error('Error fetching notification:', error);
            showAlert('Error loading notification', 'danger');
        });
}

// Populate notification modal with data
function populateNotificationModal(notice) {
    document.getElementById('notification-modal-title').textContent = notice.title;
    document.getElementById('notification-modal-message').innerHTML = notice.message.replace(/\n/g, '<br>');
    document.getElementById('notice-modal-date').textContent = notice.formatted_date;
    document.getElementById('notice-modal-from').textContent = notice.creator_name || 'Registrar';
    
    // Set status badge
    const statusBadge = document.getElementById('notice-modal-status');
    statusBadge.textContent = notice.is_read ? 'Read' : 'Unread';
    statusBadge.className = `badge ${notice.is_read ? 'bg-success' : 'bg-warning'}`;
    
    // Show/hide mark as read button
    const markReadBtn = document.getElementById('mark-read-btn');
    if (notice.is_read) {
        markReadBtn.style.display = 'none';
    } else {
        markReadBtn.style.display = 'inline-block';
    }
}

// Mark single notice as read
function markAsRead(noticeId, showMessage = true) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        showAlert('CSRF token not found. Please refresh the page.', 'danger');
        return;
    }

    fetch(`/enrollee/notices/${noticeId}/mark-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Single notice response text:', text);
                throw new Error(`HTTP error! status: ${response.status} - ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update UI
            updateNoticeRowAsRead(noticeId);
            updateUnreadCount();
            
            // Fetch latest unread count from server and update sidebar badge
            fetchAndUpdateUnreadCount();
            
            if (showMessage) {
                showAlert('Notice marked as read', 'success');
            }
        } else {
            showAlert(data.message || 'Error marking notice as read', 'danger');
        }
    })
    .catch(error => {
        console.error('Error marking notice as read:', error);
        showAlert('Error updating notice status', 'danger');
    });
}

// Mark notice as read from modal
function markAsReadFromModal() {
    if (currentNoticeId) {
        markAsRead(currentNoticeId);
        
        // Update modal
        document.getElementById('notice-modal-status').textContent = 'Read';
        document.getElementById('notice-modal-status').className = 'badge bg-success';
        document.getElementById('mark-read-btn').style.display = 'none';
    }
}

// Mark all notices as read
function markAllAsRead() {
    if (confirm('Are you sure you want to mark all notices as read?')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            showAlert('CSRF token not found. Please refresh the page.', 'danger');
            return;
        }

        fetch('/enrollee/notices/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Response text:', text);
                    throw new Error(`HTTP error! status: ${response.status} - ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update all unread rows
                const unreadRows = document.querySelectorAll('.table-warning');
                unreadRows.forEach(row => {
                    row.classList.remove('table-warning');
                    const markReadBtn = row.querySelector('.btn-outline-success');
                    if (markReadBtn) {
                        markReadBtn.remove();
                    }
                    // Remove unread indicator
                    const unreadIcon = row.querySelector('.ri-notification-2-fill');
                    if (unreadIcon) {
                        unreadIcon.remove();
                    }
                });
                
                updateUnreadCount();
                
                // Fetch latest unread count from server and update sidebar badge
                fetchAndUpdateUnreadCount();
                
                showAlert('All notices marked as read', 'success');
            } else {
                showAlert(data.message || 'Error marking notices as read', 'danger');
            }
        })
        .catch(error => {
            console.error('Error marking all notices as read:', error);
            showAlert('Error updating notices. Please try again.', 'danger');
        });
    }
}

// Update notice row as read
function updateNoticeRowAsRead(noticeId) {
    const rows = document.querySelectorAll('tr');
    rows.forEach(row => {
        const viewBtn = row.querySelector(`button[onclick="viewNotification(${noticeId})"]`);
        if (viewBtn) {
            row.classList.remove('table-warning');
            
            // Remove unread indicator
            const unreadIcon = row.querySelector('.ri-notification-2-fill');
            if (unreadIcon) {
                unreadIcon.remove();
            }
            
            // Remove mark as read button
            const markReadBtn = row.querySelector('.btn-outline-success');
            if (markReadBtn) {
                markReadBtn.remove();
            }
        }
    });
}

// Update unread count in UI and sidebar badge
function updateUnreadCount() {
    const unreadRows = document.querySelectorAll('.table-warning');
    const unreadCount = unreadRows.length;
    
    // Update header badge
    const headerBadge = document.querySelector('.badge.bg-info');
    if (headerBadge) {
        headerBadge.innerHTML = `<i class="ri-notification-line me-1"></i>${unreadCount} Unread`;
    }
    
    // Update sidebar statistics (only if it exists)
    const sidebarWarningElement = document.querySelector('.text-warning');
    if (sidebarWarningElement && sidebarWarningElement.previousElementSibling) {
        sidebarWarningElement.previousElementSibling.textContent = unreadCount;
    }
    
    // Update sidebar badge in real-time
    updateSidebarBadge(unreadCount);
}

// Update sidebar notification badge
function updateSidebarBadge(count) {
    // Find the Notices link in both desktop and mobile sidebars
    const noticesLinks = document.querySelectorAll('a[href*="/enrollee/notices"]');
    
    noticesLinks.forEach(link => {
        // Remove existing badge if present
        const existingBadge = link.querySelector('.badge');
        if (existingBadge) {
            existingBadge.remove();
        }
        
        // Add new badge only if there are unread notices
        if (count > 0) {
            const badge = document.createElement('span');
            badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
            badge.textContent = count;
            badge.style.fontSize = '0.65rem';
            badge.style.padding = '0.25rem 0.4rem';
            
            // Make sure link has position-relative
            link.style.position = 'relative';
            link.appendChild(badge);
        }
    });
}

// Fetch unread count from server and update sidebar badge
function fetchAndUpdateUnreadCount() {
    fetch('/enrollee/notices/count/unread')
        .then(response => {
            if (!response.ok) {
                console.error('Error fetching unread count:', response.status);
                return null;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                const unreadCount = data.count;
                console.log('Updated unread count from server:', unreadCount);
                
                // Update sidebar badge with server count
                updateSidebarBadge(unreadCount);
            }
        })
        .catch(error => {
            console.error('Error updating unread count from server:', error);
        });
}

// Refresh notifications
function refreshNotifications() {
    location.reload();
}

// Show alert message
function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of page
    const container = document.querySelector('.py-4');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Make functions globally available
window.viewNotification = viewNotification;
window.markAsRead = markAsRead;
window.markAsReadFromModal = markAsReadFromModal;
window.markAllAsRead = markAllAsRead;
window.refreshNotifications = refreshNotifications;
window.fetchAndUpdateUnreadCount = fetchAndUpdateUnreadCount;
window.updateSidebarBadge = updateSidebarBadge;