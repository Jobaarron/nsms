document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('payment_method');
    const instructionsDiv = document.getElementById('payment_instructions');
    const instructionContent = document.getElementById('instruction_content');
    const referenceField = document.getElementById('reference_field');

    // Get data from window object (passed from Blade template)
    const applicationId = window.enrolleeData?.applicationId || 'N/A';
    const amountDue = window.enrolleeData?.amountDue || '0.00';

    const instructions = {
        'gcash': `
            <ol>
                <li>Open your GCash app</li>
                <li>Select "Send Money" or "Pay Bills"</li>
                <li>Enter the merchant number: <strong>09123456789</strong></li>
                <li>Enter the amount: <strong>₱${amountDue}</strong></li>
                <li>Add reference: <strong>${applicationId}</strong></li>
                <li>Complete the transaction and save the reference number</li>
            </ol>
        `,
        'paymaya': `
            <ol>
                <li>Open your PayMaya app</li>
                <li>Select "Pay" or "Send Money"</li>
                <li>Enter the merchant details provided</li>
                <li>Enter the amount: <strong>₱${amountDue}</strong></li>
                <li>Add reference: <strong>${applicationId}</strong></li>
                <li>Complete the transaction and save the reference number</li>
            </ol>
        `,
        'bank_transfer': `
            <p><strong>Bank Details:</strong></p>
            <ul>
                <li>Bank Name: BPI</li>
                <li>Account Name: Nicolites School Management System</li>
                <li>Account Number: 1234-5678-90</li>
                <li>Amount: <strong>₱${amountDue}</strong></li>
                <li>Reference: <strong>${applicationId}</strong></li>
            </ul>
        `,
        'over_counter': `
            <p>Visit the school's cashier office during business hours:</p>
            <ul>
                <li>Monday to Friday: 8:00 AM - 5:00 PM</li>
                <li>Bring your Application ID: <strong>${applicationId}</strong></li>
                <li>Amount to pay: <strong>₱${amountDue}</strong></li>
            </ul>
        `
    };

    paymentMethodSelect.addEventListener('change', function() {
        const selectedMethod = this.value;
        
        if (selectedMethod && instructions[selectedMethod]) {
            instructionContent.innerHTML = instructions[selectedMethod];
            instructionsDiv.style.display = 'block';
            referenceField.style.display = 'block';
        } else {
            instructionsDiv.style.display = 'none';
            referenceField.style.display = 'none';
        }
    });
});