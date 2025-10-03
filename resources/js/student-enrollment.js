// Student Enrollment JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize enrollment functionality
    initializeEnrollment();
    
    // Setup payment mode selection
    setupPaymentModeSelection();
    
    // Setup form validation
    setupFormValidation();
});

function initializeEnrollment() {
    console.log('Student Enrollment initialized');
    
    // Initialize payment schedule display
    updatePaymentScheduleDisplay();
    
    // Setup form submission
    setupFormSubmission();
}

function setupPaymentModeSelection() {
    const paymentOptions = document.querySelectorAll('input[name="payment_mode"]');
    
    paymentOptions.forEach(option => {
        option.addEventListener('change', function() {
            updatePaymentModeStyles();
            updatePaymentScheduleDisplay();
        });
    });
    
    // Initialize with selected option
    updatePaymentModeStyles();
}

function updatePaymentModeStyles() {
    // Remove active styles from all cards
    document.querySelectorAll('.payment-option').forEach(card => {
        card.classList.remove('border-primary', 'bg-light');
        card.classList.add('border-secondary');
    });
    
    // Add active style to selected card
    const selectedInput = document.querySelector('input[name="payment_mode"]:checked');
    if (selectedInput) {
        const selectedCard = selectedInput.closest('.form-check').querySelector('.payment-option');
        selectedCard.classList.remove('border-secondary');
        selectedCard.classList.add('border-primary', 'bg-light');
    }
}

function updatePaymentScheduleDisplay() {
    const selectedMode = document.querySelector('input[name="payment_mode"]:checked');
    const scheduleDiv = document.getElementById('payment-schedule');
    const scheduleContent = document.getElementById('schedule-content');
    
    if (!selectedMode || !scheduleDiv || !scheduleContent) return;
    
    const totalAmount = parseFloat(document.querySelector('[data-total-amount]')?.dataset.totalAmount || 0);
    
    if (totalAmount <= 0) return;
    
    scheduleDiv.style.display = 'block';
    
    let html = '';
    const mode = selectedMode.value;
    
    switch(mode) {
        case 'full':
            html = `
                <div class="alert alert-success border-0">
                    <div class="d-flex align-items-center">
                        <i class="ri-money-dollar-circle-line fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-1">Full Payment</h6>
                            <p class="mb-0">Pay the entire amount of <strong>₱${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</strong> upon enrollment</p>
                        </div>
                    </div>
                </div>
            `;
            break;
            
        case 'quarterly':
            const quarterlyAmount = totalAmount / 4;
            html = `
                <div class="alert alert-warning border-0">
                    <div class="d-flex align-items-center">
                        <i class="ri-calendar-line fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-1">Quarterly Payment</h6>
                            <p class="mb-0">Pay <strong>₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</strong> per quarter (4 payments)</p>
                        </div>
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-6 col-md-3">
                        <div class="text-center p-2 bg-light rounded">
                            <small class="text-muted d-block">1st Quarter</small>
                            <strong>₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-2 bg-light rounded">
                            <small class="text-muted d-block">2nd Quarter</small>
                            <strong>₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-2 bg-light rounded">
                            <small class="text-muted d-block">3rd Quarter</small>
                            <strong>₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-2 bg-light rounded">
                            <small class="text-muted d-block">4th Quarter</small>
                            <strong>₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>
                        </div>
                    </div>
                </div>
            `;
            break;
            
        case 'monthly':
            const monthlyAmount = totalAmount / 10;
            html = `
                <div class="alert alert-info border-0">
                    <div class="d-flex align-items-center">
                        <i class="ri-calendar-2-line fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-1">Monthly Payment</h6>
                            <p class="mb-0">Pay <strong>₱${monthlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</strong> per month (10 payments, excluding vacation months)</p>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Payment months: August - May (excluding December and April vacation)</small>
                </div>
            `;
            break;
    }
    
    scheduleContent.innerHTML = html;
}

function setupFormValidation() {
    const form = document.getElementById('enrollmentForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        const selectedMode = document.querySelector('input[name="payment_mode"]:checked');
        
        if (!selectedMode) {
            e.preventDefault();
            showAlert('Please select a payment mode before continuing.', 'warning');
            
            // Scroll to payment mode section
            const paymentSection = document.querySelector('input[name="payment_mode"]').closest('.card');
            if (paymentSection) {
                paymentSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                paymentSection.classList.add('border-warning');
                setTimeout(() => {
                    paymentSection.classList.remove('border-warning');
                }, 3000);
            }
            return false;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('enrollBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line me-2"></i>Processing Enrollment...';
        }
    });
}

function setupFormSubmission() {
    const form = document.getElementById('enrollmentForm');
    if (!form) return;
    
    // Add confirmation before submission
    form.addEventListener('submit', function(e) {
        const selectedMode = document.querySelector('input[name="payment_mode"]:checked');
        if (!selectedMode) return;
        
        const modeName = selectedMode.closest('label').querySelector('h6').textContent;
        
        if (!confirm(`Are you sure you want to complete enrollment with ${modeName}? This action cannot be undone.`)) {
            e.preventDefault();
            
            // Reset button state
            const submitBtn = document.getElementById('enrollBtn');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ri-check-line me-2"></i>Complete Enrollment';
            }
        }
    });
}

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

// Export functions for global access
window.showAlert = showAlert;
