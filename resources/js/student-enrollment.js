// Student Enrollment JavaScript
// Global variables - will be set from Laravel
let totalAmount = 0;
let preferredScheduleDate = '';

// Global variables will be initialized by initializeEnrollmentData() function called from PHP

// Define functions globally so they can be accessed by onclick handlers
window.populatePaymentDates = function() {
    console.log('Populating payment dates with preferred schedule:', preferredScheduleDate);
    
    // Validate and create base date
    let baseDate;
    if (preferredScheduleDate && preferredScheduleDate !== '') {
        baseDate = new Date(preferredScheduleDate);
        // Check if date is valid
        if (isNaN(baseDate.getTime())) {
            console.warn('Invalid preferredScheduleDate, using default');
            baseDate = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000); // 7 days from now
        }
    } else {
        baseDate = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000); // 7 days from now
    }
    
    // Add a small delay to ensure elements are visible
    setTimeout(() => {
        // Full payment date
        const fullPaymentDate = document.querySelector('input[name="full_payment_date"]');
        if (fullPaymentDate) {
            fullPaymentDate.value = preferredScheduleDate;
            console.log('Set full payment date to:', preferredScheduleDate);
        } else {
            console.log('Full payment date input not found');
        }
        
        // Quarterly payment dates
        for (let i = 1; i <= 4; i++) {
            const quarterlyDate = document.querySelector(`input[name="quarterly_date_${i}"]`);
            if (quarterlyDate) {
                const date = new Date(baseDate.getTime()); // Create a copy
                date.setMonth(date.getMonth() + (i - 1) * 3);
                
                // Validate the calculated date
                if (!isNaN(date.getTime())) {
                    const formattedDate = date.toISOString().split('T')[0];
                    quarterlyDate.value = formattedDate;
                    console.log(`Set quarterly date ${i} to:`, formattedDate);
                } else {
                    console.warn(`Invalid quarterly date ${i} calculated`);
                }
            } else {
                console.log(`Quarterly date ${i} input not found`);
            }
        }
        
        // Monthly payment dates
        for (let i = 1; i <= 10; i++) {
            const monthlyDate = document.querySelector(`input[name="monthly_date_${i}"]`);
            if (monthlyDate) {
                const date = new Date(baseDate.getTime()); // Create a copy
                date.setMonth(date.getMonth() + (i - 1));
                
                // Validate the calculated date
                if (!isNaN(date.getTime())) {
                    const formattedDate = date.toISOString().split('T')[0];
                    monthlyDate.value = formattedDate;
                    console.log(`Set monthly date ${i} to:`, formattedDate);
                } else {
                    console.warn(`Invalid monthly date ${i} calculated`);
                }
            } else {
                console.log(`Monthly date ${i} input not found`);
            }
        }
        
        // Auto-populate amounts
        window.populatePaymentAmounts();
    }, 100); // 100ms delay to ensure elements are rendered
}

window.populatePaymentAmounts = function() {
    // Full payment amount
    const fullPaymentAmount = document.querySelector('input[name="full_payment_amount"]');
    if (fullPaymentAmount) {
        fullPaymentAmount.value = totalAmount.toFixed(2);
        fullPaymentAmount.readOnly = true;
    }
    
    // Quarterly amounts
    const quarterlyAmount = (totalAmount / 4).toFixed(2);
    for (let i = 1; i <= 4; i++) {
        const quarterlyAmountInput = document.querySelector(`input[name="quarterly_amount_${i}"]`);
        if (quarterlyAmountInput) {
            quarterlyAmountInput.value = quarterlyAmount;
            quarterlyAmountInput.readOnly = true;
        }
    }
    
    // Monthly amounts
    const monthlyAmount = (totalAmount / 10).toFixed(2);
    for (let i = 1; i <= 10; i++) {
        const monthlyAmountInput = document.querySelector(`input[name="monthly_amount_${i}"]`);
        if (monthlyAmountInput) {
            monthlyAmountInput.value = monthlyAmount;
            monthlyAmountInput.readOnly = true;
        }
    }
    
    // Update display amounts
    document.querySelectorAll('.quarterly-amount').forEach(el => {
        el.textContent = parseFloat(quarterlyAmount).toLocaleString('en-US', {minimumFractionDigits: 2});
    });
    
    document.querySelectorAll('.monthly-amount').forEach(el => {
        el.textContent = parseFloat(monthlyAmount).toLocaleString('en-US', {minimumFractionDigits: 2});
    });
    
    // Update breakdown totals
    const fullTotalElement = document.getElementById('full-total-amount');
    const quarterlyTotalElement = document.getElementById('quarterly-total-amount');
    const quarterlyPerPaymentElement = document.getElementById('quarterly-per-payment');
    const monthlyTotalElement = document.getElementById('monthly-total-amount');
    const monthlyPerPaymentElement = document.getElementById('monthly-per-payment');
    
    if (fullTotalElement) fullTotalElement.textContent = totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
    if (quarterlyTotalElement) quarterlyTotalElement.textContent = totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
    if (quarterlyPerPaymentElement) quarterlyPerPaymentElement.textContent = parseFloat(quarterlyAmount).toLocaleString('en-US', {minimumFractionDigits: 2});
    if (monthlyTotalElement) monthlyTotalElement.textContent = totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
    if (monthlyPerPaymentElement) monthlyPerPaymentElement.textContent = parseFloat(monthlyAmount).toLocaleString('en-US', {minimumFractionDigits: 2});
}

window.showPaymentScheduleCard = function(mode) {
    // Show the payment schedule card
    const paymentScheduleCard = document.getElementById('payment-schedule-card');
    if (paymentScheduleCard) {
        paymentScheduleCard.style.display = 'block';
    }
    
    // Hide all payment breakdowns first
    document.querySelectorAll('.payment-breakdown').forEach(breakdown => {
        breakdown.style.display = 'none';
    });
    
    // Show the selected payment breakdown
    const selectedBreakdown = document.getElementById(`${mode}-payment-breakdown`);
    if (selectedBreakdown) {
        selectedBreakdown.style.display = 'block';
    }
}

// Initialize global variables from Laravel data
window.initializeEnrollmentData = function(amount, scheduleDate) {
    totalAmount = amount;
    preferredScheduleDate = scheduleDate;
    console.log('Enrollment data initialized:', { totalAmount, preferredScheduleDate });
    
    // Update amount displays immediately
    if (typeof window.populatePaymentAmounts === 'function') {
        window.populatePaymentAmounts();
    }
}

// DOM Content Loaded Event Listener
document.addEventListener('DOMContentLoaded', function() {
    const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
    const scheduleDiv = document.getElementById('payment-schedule');
    const scheduleContent = document.getElementById('schedule-content');
    
    // Auto-populate payment dates based on preferred schedule
    window.populatePaymentDates();
    
    paymentOptions.forEach(option => {
        option.addEventListener('change', function() {
            console.log('Payment mode changed to:', this.value);
            updatePaymentSchedule(this.value);
            updatePaymentOptionStyles();
            window.showPaymentScheduleCard(this.value);
            // Populate dates after showing the card with a longer delay
            setTimeout(() => {
                window.populatePaymentDates();
            }, 200);
        });
    });
    
    // Initialize with selected option
    const selectedOption = document.querySelector('input[name="payment_method"]:checked');
    if (selectedOption) {
        updatePaymentSchedule(selectedOption.value);
        updatePaymentOptionStyles();
        window.showPaymentScheduleCard(selectedOption.value);
        window.populatePaymentDates(); // Populate dates on initialization
    }
    
    function updatePaymentSchedule(mode) {
        if (totalAmount <= 0) return;
        
        scheduleDiv.style.display = 'block';
        let html = '';
        
        switch(mode) {
            case 'full':
                html = `
                    <div class="alert alert-success">
                        <strong>Full Payment:</strong> ₱${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}
                        <br><small>Pay the entire amount upon enrollment</small>
                    </div>
                `;
                break;
            case 'quarterly':
                const quarterlyAmount = totalAmount / 4;
                html = `
                    <div class="alert alert-warning">
                        <strong>Quarterly Payment:</strong> ₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})} per quarter
                        <br><small>4 payments throughout the academic year</small>
                    </div>
                `;
                break;
            case 'monthly':
                const monthlyAmount = totalAmount / 10;
                html = `
                    <div class="alert alert-info">
                        <strong>Monthly Payment:</strong> ₱${monthlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})} per month
                        <br><small>10 payments (excluding vacation months)</small>
                    </div>
                `;
                break;
        }
        
        scheduleContent.innerHTML = html;
    }
    
    function updatePaymentOptionStyles() {
        document.querySelectorAll('.payment-option').forEach(card => {
            card.classList.remove('border-primary', 'bg-light');
        });
        
        const selectedInput = document.querySelector('input[name="payment_method"]:checked');
        if (selectedInput) {
            const selectedCard = selectedInput.closest('.form-check').querySelector('.payment-option');
            selectedCard.classList.add('border-primary', 'bg-light');
        }
    }
});

// Update amount displays function
function updateAmountDisplays(totalAmount) {
    if (typeof window.populatePaymentAmounts === 'function') {
        window.populatePaymentAmounts();
    }
}

// Export functions for global access
window.showAlert = showAlert;
window.showPaymentScheduleCard = showPaymentScheduleCard;
window.updateAmountDisplays = updateAmountDisplays;

// PDF Modal for Receipt
function showPDFModal({ transactionId, onClose }) {
// Remove existing modal if present
let existing = document.getElementById('pdf-modal');
if (existing) existing.remove();

// Build the receipt URL with transaction_id
let receiptUrl = '/pdf/receipt?transaction_id=' + encodeURIComponent(transactionId);

    // Modal HTML
    const modal = document.createElement('div');
    modal.id = 'pdf-modal';
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.width = '100vw';
    modal.style.height = '100vh';
    modal.style.background = 'rgba(0,0,0,0.5)';
    modal.style.zIndex = '10000';
    modal.style.display = 'flex';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';

    modal.innerHTML = `
        <div style="background: #fff; border-radius: 8px; max-width: 90vw; max-height: 90vh; width: 600px; box-shadow: 0 2px 16px rgba(0,0,0,0.2); display: flex; flex-direction: column;">
            <div style="padding: 1rem; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between;">
                <h5 style="margin: 0;">Payment Receipt</h5>
                <button id="pdf-modal-close" style="background: none; border: none; font-size: 1.5rem; line-height: 1; cursor: pointer;">&times;</button>
            </div>
            <div style="flex: 1; overflow: auto; padding: 1rem;">
                <iframe id="pdf-iframe" src="${receiptUrl}" style="width: 100%; height: 400px; border: none;"></iframe>
            </div>
            <div style="padding: 1rem; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button id="pdf-modal-print" class="btn btn-primary">Print</button>
                <button id="pdf-modal-cancel" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Close modal handler
    function closeModal() {
        modal.remove();
        if (typeof onClose === 'function') onClose();
    }
    document.getElementById('pdf-modal-close').onclick = closeModal;
    document.getElementById('pdf-modal-cancel').onclick = closeModal;

    // Print button handler
    document.getElementById('pdf-modal-print').onclick = function() {
        const iframe = document.getElementById('pdf-iframe');
        if (iframe) {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }
    };
}
window.showPDFModal = showPDFModal;

// Utility functions
function showAlert(message, type = 'info') {
    // Create alert container if it doesn't exist
    let alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alert-container';
        alertContainer.className = 'position-fixed top-0 end-0 p-3';
        alertContainer.style.zIndex = '9999';
        document.body.appendChild(alertContainer);
    }
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="ri-information-line me-2"></i>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Initialize enrollment data when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.initializeEnrollmentData === 'function') {
        // Get data from data attributes or global variables
        const enrollmentForm = document.getElementById('enrollmentForm');
        if (enrollmentForm) {
            const totalAmountValue = enrollmentForm.dataset.totalAmount || totalAmount || 0;
            const scheduleDate = enrollmentForm.dataset.scheduleDate || preferredScheduleDate;
            
            window.initializeEnrollmentData(
                parseFloat(totalAmountValue),
                scheduleDate
            );
        }
    }
});

// Custom form submission to show PDF modal after payment
window.submitEnrollmentForm = function() {
    const form = document.getElementById('enrollmentForm');
    if (!form) {
        console.error('Enrollment form not found');
        return;
    }
    
    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.transaction_id) {
            if (window.showPDFModal) {
                window.showPDFModal({
                    transactionId: data.transaction_id,
                    onClose: function() {
                        window.location.href = data.redirect_url || '/student/dashboard';
                    }
                });
            } else {
                // Fallback if PDF modal not available
                window.location.href = data.redirect_url || '/student/dashboard';
            }
        } else {
            if (window.showAlert) {
                window.showAlert(data.message || 'Failed to submit payment.', 'danger');
            } else {
                alert(data.message || 'Failed to submit payment.');
            }
        }
    })
    .catch(error => {
        console.error('Error submitting enrollment:', error);
        if (window.showAlert) {
            window.showAlert('An error occurred while submitting payment.', 'danger');
        } else {
            alert('An error occurred while submitting payment.');
        }
    });
};

// Form submission function for PDF modal integration
function submitEnrollmentForm() {
    const form = document.getElementById('enrollmentForm');
    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.transaction_id) {
            if (window.showPDFModal) {
                window.showPDFModal({
                    transactionId: data.transaction_id,
                    onClose: function() {
                        window.location.href = data.redirect_url || '/student/dashboard';
                    }
                });
            }
        } else {
            if (window.showAlert) window.showAlert(data.message || 'Failed to submit payment.', 'danger');
        }
    })
    .catch(() => {
        if (window.showAlert) window.showAlert('An error occurred while submitting payment.', 'danger');
    });
}

// Export function for global access
window.submitEnrollmentForm = submitEnrollmentForm;