// Enrollee Notices JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeNoticesPage();
});

// Global variables
let currentNoticeId = null;

// Initialize notices functionality
function initializeNoticesPage() {
    console.log('Enrollee notices page initialized');
    
    // Auto-refresh notices every 2 minutes
    setInterval(function() {
        console.log('Auto-checking for new notices...');
        // In production, this would fetch new notices via AJAX
    }, 120000);
}

// View notice in modal
function viewNotice(noticeId) {
    currentNoticeId = noticeId;
    
    // Fetch notice details
    fetch(`/enrollee/notices/${noticeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateNoticeModal(data.notice);
                new bootstrap.Modal(document.getElementById('noticeViewModal')).show();
                
                // Mark as read when viewed
                if (!data.notice.is_read) {
                    markAsRead(noticeId, false);
                }
            } else {
                showAlert('Error loading notice details', 'danger');
            }
        })
        .catch(error => {
            console.error('Error fetching notice:', error);
            showAlert('Error loading notice', 'danger');
        });
}

// Populate notice modal with data
function populateNoticeModal(notice) {
    document.getElementById('notice-modal-title').textContent = notice.title;
    document.getElementById('notice-modal-message').innerHTML = notice.message.replace(/\n/g, '<br>');
    document.getElementById('notice-modal-date').textContent = notice.formatted_date;
    document.getElementById('notice-modal-from').textContent = notice.creator_name || 'Registrar';
    
    // Set priority badge
    const priorityBadge = document.getElementById('notice-modal-priority');
    priorityBadge.textContent = notice.priority.charAt(0).toUpperCase() + notice.priority.slice(1);
    priorityBadge.className = `badge ${notice.priority_badge}`;
    
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
function markAsRead(noticeId, showAlert = true) {
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
            
            if (showAlert) {
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
        const viewBtn = row.querySelector(`button[onclick="viewNotice(${noticeId})"]`);
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

// Update unread count in UI
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
}

// Refresh notices
function refreshNotices() {
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
window.viewNotice = viewNotice;
window.markAsRead = markAsRead;
window.markAsReadFromModal = markAsReadFromModal;
window.markAllAsRead = markAllAsRead;
window.refreshNotices = refreshNotices;