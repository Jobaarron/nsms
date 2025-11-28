/**
 * Discipline Alerts Manager
 * Real-time polling every 3 seconds for:
 * - Violations with student replies (unread by discipline officer)
 */

let pollingInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    try {
        startRealTimeAlertPolling();
        
        // Mark alerts as viewed when clicking the violations link
        const violationsLink = document.getElementById('violations-link');
        if (violationsLink) {
            violationsLink.addEventListener('click', function() {
                markAlertsAsViewed();
            });
        }
    } catch(error) {
        console.error('Error initializing discipline alerts:', error);
    }
});

function startRealTimeAlertPolling() {
    const POLL_INTERVAL = 3000; // 3 seconds
    
    console.log('🔄 Starting discipline alert polling (every 3 seconds)');
    
    // Initial check
    fetchAlertCounts();
    
    // Poll every 3 seconds
    pollingInterval = setInterval(() => {
        console.log('⏰ Polling for discipline alerts...');
        fetchAlertCounts();
    }, POLL_INTERVAL);
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
    });
}

async function fetchAlertCounts() {
    try {
        const response = await fetch('/discipline/alerts/counts', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            console.log('📊 Discipline alert counts received:', data.counts);
            updateAlertBadges(data.counts);
        }
    } catch (error) {
        console.error('Error fetching discipline alert counts:', error);
    }
}

function updateAlertBadges(counts) {
    updateBadge('violations-link', counts.student_replies || 0);
}

function updateBadge(linkId, count) {
    const link = document.getElementById(linkId);
    if (!link) return;
    
    let badge = link.querySelector('.badge');
    
    if (count > 0) {
        // Update or create badge
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
            badge.style.fontSize = '0.65rem';
            badge.style.padding = '0.25rem 0.4rem';
            link.appendChild(badge);
        }
        badge.textContent = count;
        
        // Add red highlight
        link.style.backgroundColor = '#f8d7da';
        link.style.borderLeft = '4px solid #dc3545';
        link.style.paddingLeft = 'calc(0.75rem - 4px)';
    } else {
        // Remove badge and highlight
        if (badge) {
            badge.remove();
        }
        link.style.backgroundColor = '';
        link.style.borderLeft = '';
        link.style.paddingLeft = '';
    }
}

async function markAlertsAsViewed() {
    try {
        const response = await fetch('/discipline/mark-alert-viewed', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                alert_type: 'violations'
            })
        });
        
        if (response.ok) {
            console.log('✅ Violation alerts marked as viewed');
            // Immediately fetch new counts to update the UI
            fetchAlertCounts();
        }
    } catch (error) {
        console.error('Error marking alerts as viewed:', error);
    }
}
