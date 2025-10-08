// Student Enrollment JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize enrollment functionality
    initializeEnrollment();
    
    // Setup payment mode selection
    setupPaymentModeSelection();
    
    // Setup payment scheduling
    setupPaymentScheduling();
    
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
            showPaymentScheduleCard();
        });
    });
    
    // Initialize with selected option
    updatePaymentModeStyles();
}

function setupPaymentScheduling() {
    // Get total amount from fee breakdown
    const totalAmountElement = document.querySelector('h5.text-primary');
    let totalAmount = 0;
    
    if (totalAmountElement) {
        const amountText = totalAmountElement.textContent.replace(/[₱,]/g, '');
        totalAmount = parseFloat(amountText) || 0;
    }
    
    // Update all amount displays
    updateAmountDisplays(totalAmount);
    
    // Setup payment method selection
    setupPaymentMethodSelection();
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
        
        // Payment mode validation removed - user can submit without selecting mode
        
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
    
    // Add confirmation and AJAX submission
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Always prevent default form submission
        
        const selectedMode = document.querySelector('input[name="payment_mode"]:checked');
        
        // Payment method validation removed - not needed in current form
        
        // Payment mode validation removed - selectedMode can be null
        
        let modeName = 'Full Payment'; // Default
        if (selectedMode) {
            const modeLabel = selectedMode.closest('label');
            modeName = modeLabel ? modeLabel.querySelector('h6')?.textContent || selectedMode.value : selectedMode.value;
        }
        
        if (!confirm(`Are you sure you want to submit your payment schedule with ${modeName}? This will be sent to the cashier for confirmation.`)) {
            return;
        }
        
        // Collect form data and submit via AJAX
        submitPaymentSchedule();
    });
}

function submitPaymentSchedule() {
    const submitBtn = document.getElementById('enrollBtn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ri-loader-4-line me-2"></i>Processing...';
    }
    
    // Collect form data
    let formData;
    try {
        formData = collectFormData();
    } catch (error) {
        showAlert(error.message, 'danger');
        resetSubmitButton();
        return;
    }
    
    // Submit to backend
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        showAlert('CSRF token not found. Please refresh the page.', 'danger');
        resetSubmitButton();
        return;
    }
    
    fetch('/student/enrollment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams(formData)
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, it might be a redirect - treat as success
            if (response.ok) {
                return { success: true, message: 'Payment schedule submitted successfully!', redirect_url: '/student/dashboard' };
            } else {
                throw new Error('Server returned non-JSON response');
            }
        }
    })
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            
            // Redirect after a short delay
            setTimeout(() => {
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    window.location.reload();
                }
            }, 2000);
        } else {
            showAlert(data.message || 'Failed to create payment schedule.', 'danger');
            resetSubmitButton();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while processing your request.', 'danger');
        resetSubmitButton();
    });
}

function collectFormData() {
    const selectedMode = document.querySelector('input[name="payment_mode"]:checked');
    const totalAmountElement = document.querySelector('h5.text-primary');
    const notesElement = document.querySelector('textarea[name="payment_notes"]');
    
    // No validation needed - payment mode is optional, payment method determined by cashier
    
    // Get total amount
    let totalAmount = 0;
    if (totalAmountElement) {
        const amountText = totalAmountElement.textContent.replace(/[₱,]/g, '');
        totalAmount = parseFloat(amountText) || 0;
    }
    
    // Use selected mode or default to 'full'
    const paymentMode = selectedMode ? selectedMode.value : 'full';
    
    // Collect scheduled payments based on mode
    const scheduledPayments = collectScheduledPayments(paymentMode, totalAmount);
    
    return {
        payment_method: 'cash', // Default payment method, will be determined by cashier
        payment_mode: paymentMode,
        total_amount: totalAmount,
        payment_notes: notesElement ? notesElement.value : '',
        scheduled_payments: scheduledPayments
    };
}

function collectScheduledPayments(method, totalAmount) {
    const payments = [];
    
    switch(method) {
        case 'full':
            const fullDate = document.querySelector('input[name="full_payment_date"]');
            const fullAmount = document.querySelector('input[name="full_payment_amount"]');
            
            payments.push({
                period: 'Full Payment',
                amount: fullAmount && fullAmount.value ? parseFloat(fullAmount.value) : totalAmount,
                date: fullDate ? fullDate.value : new Date().toISOString().split('T')[0]
            });
            break;
            
        case 'quarterly':
            for (let i = 1; i <= 4; i++) {
                const dateInput = document.querySelector(`input[name="quarterly_date_${i}"]`);
                const amountInput = document.querySelector(`input[name="quarterly_amount_${i}"]`);
                
                if (dateInput && dateInput.value) {
                    payments.push({
                        period: `${i}${getOrdinalSuffix(i)} Quarter`,
                        amount: amountInput && amountInput.value ? parseFloat(amountInput.value) : totalAmount / 4,
                        date: dateInput.value
                    });
                }
            }
            break;
            
        case 'monthly':
            const months = ['June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March'];
            
            for (let i = 1; i <= 10; i++) {
                const dateInput = document.querySelector(`input[name="monthly_date_${i}"]`);
                const amountInput = document.querySelector(`input[name="monthly_amount_${i}"]`);
                
                if (dateInput && dateInput.value) {
                    payments.push({
                        period: months[i - 1],
                        amount: amountInput && amountInput.value ? parseFloat(amountInput.value) : totalAmount / 10,
                        date: dateInput.value
                    });
                }
            }
            break;
    }
    
    return payments;
}

function getOrdinalSuffix(num) {
    const suffixes = ['th', 'st', 'nd', 'rd'];
    const v = num % 100;
    return suffixes[(v - 20) % 10] || suffixes[v] || suffixes[0];
}

function resetSubmitButton() {
    const submitBtn = document.getElementById('enrollBtn');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ri-send-plane-line me-2"></i>Submit Payment Schedule';
    }
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

function showPaymentScheduleCard() {
    const selectedMethod = document.querySelector('input[name="payment_mode"]:checked');
    const scheduleCard = document.getElementById('payment-schedule-card');
    
    if (!scheduleCard) return;
    
    if (selectedMethod) {
        scheduleCard.style.display = 'block';
        showPaymentBreakdown(selectedMethod.value);
    } else {
        scheduleCard.style.display = 'none';
    }
}

function showPaymentBreakdown(method) {
    // Hide all breakdowns first
    document.querySelectorAll('.payment-breakdown').forEach(breakdown => {
        breakdown.style.display = 'none';
    });
    
    // Show the selected breakdown
    const breakdownId = `${method}-payment-breakdown`;
    const breakdown = document.getElementById(breakdownId);
    if (breakdown) {
        breakdown.style.display = 'block';
    }
}

function updateAmountDisplays(totalAmount) {
    // Update full payment amounts
    const fullTotalElement = document.getElementById('full-total-amount');
    if (fullTotalElement) {
        fullTotalElement.textContent = totalAmount.toFixed(2);
    }
    
    // Update quarterly payment amounts
    const quarterlyAmount = totalAmount / 4;
    const quarterlyTotalElement = document.getElementById('quarterly-total-amount');
    const quarterlyPerElement = document.getElementById('quarterly-per-payment');
    
    if (quarterlyTotalElement) {
        quarterlyTotalElement.textContent = totalAmount.toFixed(2);
    }
    if (quarterlyPerElement) {
        quarterlyPerElement.textContent = quarterlyAmount.toFixed(2);
    }
    
    // Update quarterly input amounts
    for (let i = 1; i <= 4; i++) {
        const quarterlyAmountInput = document.querySelector(`input[name="quarterly_amount_${i}"]`);
        if (quarterlyAmountInput && !quarterlyAmountInput.value) {
            quarterlyAmountInput.value = quarterlyAmount.toFixed(2);
        }
    }
    
    // Update monthly payment amounts
    const monthlyAmount = totalAmount / 10;
    const monthlyTotalElement = document.getElementById('monthly-total-amount');
    const monthlyPerElement = document.getElementById('monthly-per-payment');
    
    if (monthlyTotalElement) {
        monthlyTotalElement.textContent = totalAmount.toFixed(2);
    }
    if (monthlyPerElement) {
        monthlyPerElement.textContent = monthlyAmount.toFixed(2);
    }
    
    // Update monthly input amounts
    for (let i = 1; i <= 10; i++) {
        const monthlyAmountInput = document.querySelector(`input[name="monthly_amount_${i}"]`);
        if (monthlyAmountInput && !monthlyAmountInput.value) {
            monthlyAmountInput.value = monthlyAmount.toFixed(2);
        }
    }
}

function setupPaymentMethodSelection() {
    const paymentModes = document.querySelectorAll('input[name="payment_method"]');
    
    paymentModes.forEach(mode => {
        mode.addEventListener('change', function() {
            updatePaymentMethodStyles();
        });
    });
}

function updatePaymentMethodStyles() {
    // Remove active styles from all payment method cards
    document.querySelectorAll('input[name="payment_method"]').forEach(input => {
        const card = input.closest('.form-check').querySelector('.card');
        card.classList.remove('border-primary', 'bg-light');
        card.classList.add('border-secondary');
    });
    
    // Add active style to selected card
    const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
    if (selectedMethod) {
        const selectedCard = selectedMethod.closest('.form-check').querySelector('.card');
        selectedCard.classList.remove('border-secondary');
        selectedCard.classList.add('border-primary', 'bg-light');
    }
}

// Export functions for global access
window.showAlert = showAlert;
window.showPaymentScheduleCard = showPaymentScheduleCard;
window.updateAmountDisplays = updateAmountDisplays;
