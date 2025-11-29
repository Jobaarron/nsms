/**
 * Cashier Alerts Manager
 * Real-time polling every 3 seconds for:
 * - Pending payment confirmations
 */

let pollingInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    try {
        startRealTimeAlertPolling();
    } catch(error) {
    }
});

function startRealTimeAlertPolling() {
    const POLL_INTERVAL = 3000; // 3 seconds
    
    
    // Initial check
    fetchAlertCounts();
    
    // Poll every 3 seconds
    pollingInterval = setInterval(() => {
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
        const response = await fetch('/cashier/alerts/counts', {
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
            updateAlertBadges(data.counts);
        }
    } catch (error) {
    }
}

function updateAlertBadges(counts) {
    updateBadge('payments-link', counts.pending_payments || 0);
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
