/**
 * Student Payment Alerts Manager
 * Highlights Payments sidebar link in red when payment due date is near or overdue
 * Removes highlight when student opens the payments page
 */

document.addEventListener('DOMContentLoaded', function() {
    initializePaymentAlerts();
});

function initializePaymentAlerts() {
    // Check if we're on the payments page
    const isPaymentsPage = window.location.pathname.includes('/student/payments');
    
    console.log('Payment alerts - Current page:', window.location.pathname, 'Is payments page:', isPaymentsPage);
    
    if (isPaymentsPage) {
        // Clear the alert when user opens payments page
        console.log('On payments page, clearing alert');
        clearPaymentAlert();
    } else {
        // Only check for upcoming payments on non-payments pages (dashboard, etc)
        console.log('Not on payments page, checking for upcoming payments');
        checkUpcomingPayments();
    }
}

function checkUpcomingPayments() {
    // Get payment schedule data from the page
    const paymentScheduleCard = document.querySelector('[data-payment-schedule]');
    
    if (!paymentScheduleCard) {
        console.log('Payment schedule card not found');
        return;
    }

    try {
        // Get all payment rows from the table
        const paymentRows = document.querySelectorAll('[data-payment-schedule] table tbody tr');
        let hasUpcomingPayment = false;
        let nearestDueDate = null;

        console.log('Found payment rows:', paymentRows.length);

        paymentRows.forEach((row, index) => {
            // Get scheduled date from data attribute
            const scheduledDate = row.getAttribute('data-scheduled-date');
            const paymentStatus = row.getAttribute('data-payment-status');
            
            console.log(`Row ${index}: date=${scheduledDate}, status=${paymentStatus}`);
            
            if (scheduledDate && paymentStatus) {
                // Only check pending payments
                if (paymentStatus.toLowerCase() === 'pending') {
                    const dueDate = parseDate(scheduledDate);
                    
                    if (dueDate) {
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        
                        const daysUntilDue = Math.ceil((dueDate - today) / (1000 * 60 * 60 * 24));
                        
                        console.log(`Days until due: ${daysUntilDue}`);
                        
                        // Highlight if due date is within 7 days or overdue
                        if (daysUntilDue <= 7) {
                            hasUpcomingPayment = true;
                            console.log('Found upcoming payment within 7 days');
                            
                            if (!nearestDueDate || dueDate < nearestDueDate) {
                                nearestDueDate = dueDate;
                            }
                        }
                    }
                }
            }
        });

        console.log('Has upcoming payment:', hasUpcomingPayment);

        if (hasUpcomingPayment) {
            highlightPaymentSidebar();
            storePaymentAlertState(true);
        } else {
            removePaymentHighlight();
            storePaymentAlertState(false);
        }
    } catch (error) {
        console.error('Error checking upcoming payments:', error);
    }
}

function highlightPaymentSidebar() {
    const paymentLink = document.querySelector('a[href*="/student/payments"], a[href*="student.payments"]');
    
    if (paymentLink) {
        // Add red highlight styling
        paymentLink.style.backgroundColor = '#f8d7da';
        paymentLink.style.borderLeft = '4px solid #dc3545';
        paymentLink.style.color = '#721c24';
        paymentLink.style.fontWeight = '600';
        
        // Add pulsing animation
        paymentLink.classList.add('payment-alert-active');
        
        // Add visual indicator (red dot)
        if (!paymentLink.querySelector('.payment-alert-dot')) {
            const dot = document.createElement('span');
            dot.className = 'payment-alert-dot';
            dot.style.display = 'inline-block';
            dot.style.width = '8px';
            dot.style.height = '8px';
            dot.style.backgroundColor = '#dc3545';
            dot.style.borderRadius = '50%';
            dot.style.marginRight = '8px';
            dot.style.animation = 'pulse 2s infinite';
            
            const icon = paymentLink.querySelector('i');
            if (icon) {
                icon.parentNode.insertBefore(dot, icon);
            }
        }
    }
}

function removePaymentHighlight() {
    const paymentLink = document.querySelector('a[href*="/student/payments"], a[href*="student.payments"]');
    
    if (paymentLink) {
        paymentLink.style.backgroundColor = '';
        paymentLink.style.borderLeft = '';
        paymentLink.style.color = '';
        paymentLink.style.fontWeight = '';
        paymentLink.classList.remove('payment-alert-active');
        
        // Remove red dot
        const dot = paymentLink.querySelector('.payment-alert-dot');
        if (dot) {
            dot.remove();
        }
    }
}

function clearPaymentAlert() {
    // Remove highlight when user opens payments page
    removePaymentHighlight();
    
    // Store that alert has been viewed
    storePaymentAlertState(false);
    
    // Set a flag so it doesn't show again until next session or new payment is due
    sessionStorage.setItem('paymentAlertViewed', 'true');
}

function storePaymentAlertState(hasAlert) {
    const state = {
        hasAlert: hasAlert,
        timestamp: new Date().toISOString()
    };
    localStorage.setItem('student_payment_alert_state', JSON.stringify(state));
}

function getPaymentAlertState() {
    const stored = localStorage.getItem('student_payment_alert_state');
    if (stored) {
        try {
            return JSON.parse(stored);
        } catch (e) {
            return null;
        }
    }
    return null;
}

function parseDate(dateString) {
    // Parse dates like "Nov 22, 2025" or "May 22, 2026"
    try {
        const date = new Date(dateString);
        if (!isNaN(date.getTime())) {
            return date;
        }
    } catch (e) {
        console.error('Error parsing date:', dateString);
    }
    return null;
}

// Add CSS animation for pulsing effect
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    
    .payment-alert-active {
        animation: paymentPulse 2s infinite;
    }
    
    @keyframes paymentPulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
        }
        50% {
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1);
        }
    }
`;
document.head.appendChild(style);

// Export functions for global access
window.checkUpcomingPayments = checkUpcomingPayments;
window.clearPaymentAlert = clearPaymentAlert;
