document.addEventListener('DOMContentLoaded', function() {
    // Check if payment form elements exist before adding event listeners
    const paymentModeRadios = document.querySelectorAll('input[name="payment_mode"]');
    const paymentMethodSection = document.getElementById('payment-method-section');
    const paymentMethodSelect = document.getElementById('payment_method');
    const paymentInstructions = document.getElementById('payment_instructions');
    const instructionContent = document.getElementById('instruction_content');
    const referenceField = document.getElementById('reference_field');
    const submitButton = document.getElementById('submit-payment');

    // Get data from window object (passed from Blade template)
    const applicationId = window.enrolleeData?.applicationId || 'N/A';
    const entranceFeeAmount = window.enrolleeData?.entranceFeeAmount || '0.00';

    // Only add event listeners if payment form exists
    if (paymentModeRadios.length > 0 && paymentMethodSection && paymentMethodSelect) {
        // Payment mode selection handler
        paymentModeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const selectedMode = this.value;
                
                // Reset and show payment method section
                if (paymentMethodSection) {
                    paymentMethodSection.style.display = 'block';
                }
                if (paymentMethodSelect) {
                    paymentMethodSelect.innerHTML = '<option value="">Select Payment Method</option>';
                    
                    // Add options based on selected mode
                    const options = paymentMethodSelect.querySelectorAll('option[data-mode]');
                    options.forEach(option => {
                        if (option.dataset.mode === selectedMode) {
                            option.style.display = 'block';
                        } else {
                            option.style.display = 'none';
                        }
                    });
                }
                
                // Update card styling
                document.querySelectorAll('.card.border-2').forEach(card => {
                    card.classList.remove('border-primary', 'border-success', 'border-warning');
                });
                
                const cashCard = document.getElementById('cash-card');
                const onlineCard = document.getElementById('online-card');
                const installmentCard = document.getElementById('installment-card');
                
                if (selectedMode === 'cash' && cashCard) {
                    cashCard.classList.add('border-success');
                } else if (selectedMode === 'online' && onlineCard) {
                    onlineCard.classList.add('border-primary');
                } else if (selectedMode === 'installment' && installmentCard) {
                    installmentCard.classList.add('border-warning');
                }
            });
        });

        // Payment method selection handler
        if (paymentMethodSelect) {
            paymentMethodSelect.addEventListener('change', function() {
                const selectedMethod = this.value;
                
                if (selectedMethod) {
                    showPaymentInstructions(selectedMethod);
                    if (referenceField) {
                        referenceField.style.display = 'block';
                    }
                    updateSubmitButton(selectedMethod);
                } else {
                    if (paymentInstructions) {
                        paymentInstructions.style.display = 'none';
                    }
                    if (referenceField) {
                        referenceField.style.display = 'none';
                    }
                }
            });
        }
    }

    function showPaymentInstructions(method) {
        let instructions = '';
        
        switch(method) {
            case 'cash_counter':
                instructions = `
                    <p><strong>Cash Payment Instructions:</strong></p>
                    <ol>
                        <li>Visit the school finance office during business hours (8:00 AM - 5:00 PM)</li>
                        <li>Bring your Application ID: <strong>${applicationId}</strong></li>
                        <li>Pay the entrance fee amount of <strong>₱${entranceFeeAmount}</strong></li>
                        <li>Get an official receipt and reference number</li>
                        <li>Enter the receipt reference number below</li>
                    </ol>
                `;
                break;
            case 'gcash':
                instructions = `
                    <p><strong>GCash Payment Instructions:</strong></p>
                    <ol>
                        <li>Open your GCash app</li>
                        <li>Send money to: <strong>09XX-XXX-XXXX (NSMS Finance)</strong></li>
                        <li>Amount: <strong>₱${entranceFeeAmount}</strong></li>
                        <li>Message: <strong>Entrance Fee - ${applicationId}</strong></li>
                        <li>Take a screenshot of the successful transaction</li>
                        <li>Enter the GCash reference number below</li>
                    </ol>
                `;
                break;
            case 'paymaya':
                instructions = `
                    <p><strong>PayMaya Payment Instructions:</strong></p>
                    <ol>
                        <li>Open your PayMaya app</li>
                        <li>Send money to: <strong>09XX-XXX-XXXX (NSMS Finance)</strong></li>
                        <li>Amount: <strong>₱${entranceFeeAmount}</strong></li>
                        <li>Message: <strong>Entrance Fee - ${applicationId}</strong></li>
                        <li>Take a screenshot of the successful transaction</li>
                        <li>Enter the PayMaya reference number below</li>
                    </ol>
                `;
                break;
            case 'bank_transfer':
                instructions = `
                    <p><strong>Bank Transfer Instructions:</strong></p>
                    <ol>
                        <li>Transfer to: <strong>NSMS Finance Account</strong></li>
                        <li>Bank: <strong>BPI - Account #: XXXX-XXXX-XX</strong></li>
                        <li>Amount: <strong>₱${entranceFeeAmount}</strong></li>
                        <li>Reference: <strong>${applicationId} - Entrance Fee</strong></li>
                        <li>Keep the bank transfer receipt</li>
                        <li>Enter the bank reference number below</li>
                    </ol>
                `;
                break;
            case 'installment_cash':
                instructions = `
                    <p><strong>Installment Cash Payment:</strong></p>
                    <ol>
                        <li>Visit the finance office to set up installment plan</li>
                        <li>Minimum down payment: <strong>₱${(parseFloat(entranceFeeAmount.replace(/,/g, '')) * 0.5).toLocaleString()}</strong></li>
                        <li>Remaining balance can be paid in 2-3 installments</li>
                        <li>Get installment schedule and payment vouchers</li>
                        <li>Enter the installment reference number below</li>
                    </ol>
                `;
                break;
            case 'installment_online':
                instructions = `
                    <p><strong>Installment Online Payment:</strong></p>
                    <ol>
                        <li>Contact finance office to set up online installment</li>
                        <li>Minimum down payment: <strong>₱${(parseFloat(entranceFeeAmount.replace(/,/g, '')) * 0.5).toLocaleString()}</strong></li>
                        <li>Use GCash/PayMaya for installment payments</li>
                        <li>Follow the installment schedule provided</li>
                        <li>Enter the first installment reference number below</li>
                    </ol>
                `;
                break;
        }
        
        if (instructionContent) {
            instructionContent.innerHTML = instructions;
        }
        if (paymentInstructions) {
            paymentInstructions.style.display = 'block';
        }
    }

    function updateSubmitButton(method) {
        if (submitButton) {
            if (method.includes('cash')) {
                submitButton.innerHTML = '<i class="ri-file-text-line me-2"></i>Submit Payment Receipt';
            } else if (method.includes('installment')) {
                submitButton.innerHTML = '<i class="ri-calendar-check-line me-2"></i>Setup Installment Plan';
            } else {
                submitButton.innerHTML = '<i class="ri-smartphone-line me-2"></i>Submit Online Payment';
            }
        }
    }
});