// Student Enrollment JavaScript
// Global variables - will be set from Laravel
let totalAmount = 0;
let preferredScheduleDate = '';

// Define functions globally so they can be accessed by onclick handlers
window.populatePaymentDates = function() {
    console.log('Populating payment dates with preferred schedule:', preferredScheduleDate);
    const baseDate = new Date(preferredScheduleDate);
    
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
                const date = new Date(baseDate);
                date.setMonth(date.getMonth() + (i - 1) * 3);
                const formattedDate = date.toISOString().split('T')[0];
                quarterlyDate.value = formattedDate;
                console.log(`Set quarterly date ${i} to:`, formattedDate);
            } else {
                console.log(`Quarterly date ${i} input not found`);
            }
        }
        
        // Monthly payment dates
        for (let i = 1; i <= 10; i++) {
            const monthlyDate = document.querySelector(`input[name="monthly_date_${i}"]`);
            if (monthlyDate) {
                const date = new Date(baseDate);
                date.setMonth(date.getMonth() + (i - 1));
                const formattedDate = date.toISOString().split('T')[0];
                monthlyDate.value = formattedDate;
                console.log(`Set monthly date ${i} to:`, formattedDate);
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

// Export functions for global access
window.showAlert = showAlert;

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