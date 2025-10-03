<x-cashier-layout>
    @push('styles')
        @vite('resources/css/index_student.css')
    @endpush

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="section-title mb-1">Completed Payments</h2>
                    <p class="text-muted mb-0">Successfully confirmed and processed payments</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-success fs-6">{{ $payments->total() }} Completed</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-check-double-line me-2 text-success"></i>Confirmed Payments
                        </h5>
                        <button class="btn btn-outline-success btn-sm" onclick="exportCompletedPayments()">
                            <i class="ri-download-line me-2"></i>Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Student/Enrollee</th>
                                        <th>Fee Type</th>
                                        <th>Amount</th>
                                        <th>Payment Method</th>
                                        <th>Processed By</th>
                                        <th>Confirmed Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>
                                                <span class="fw-bold">{{ $payment->transaction_id }}</span>
                                            </td>
                                            <td>
                                                @if($payment->payable)
                                                    <div>
                                                        <span class="fw-bold">
                                                            {{ $payment->payable->first_name }} {{ $payment->payable->last_name }}
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $payment->payable->student_id ?? $payment->payable->application_id }}
                                                        </small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>{{ $payment->fee->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    ₱{{ number_format($payment->amount, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst($payment->payment_method) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($payment->cashier)
                                                    <div>
                                                        <span class="fw-bold">{{ $payment->cashier->full_name }}</span>
                                                        <br>
                                                        <small class="text-muted">{{ $payment->cashier->employee_id }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $payment->confirmed_at ? $payment->confirmed_at->format('M d, Y g:i A') : 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewPaymentDetails({{ $payment->id }})">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success" 
                                                            onclick="printReceipt({{ $payment->id }})">
                                                        <i class="ri-printer-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $payments->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ri-file-list-line fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No Completed Payments</h5>
                            <p class="text-muted">No payments have been confirmed yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Details Modal -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="paymentDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="printReceiptFromModal()">
                        <i class="ri-printer-line me-2"></i>Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentPaymentId = null;

            function viewPaymentDetails(paymentId) {
                fetch(`/cashier/payments/${paymentId}/details`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayPaymentDetails(data.payment);
                            currentPaymentId = paymentId;
                            new bootstrap.Modal(document.getElementById('paymentDetailsModal')).show();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to load payment details');
                    });
            }

            function displayPaymentDetails(payment) {
                const content = document.getElementById('paymentDetailsContent');
                
                content.innerHTML = `
                    <div class="alert alert-success">
                        <i class="ri-check-double-line me-2"></i>
                        <strong>Status:</strong> Payment Confirmed and Completed
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Payment Information</h6>
                            <p><strong>Transaction ID:</strong> ${payment.transaction_id}</p>
                            <p><strong>Amount:</strong> ₱${parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                            <p><strong>Payment Method:</strong> ${payment.payment_method}</p>
                            <p><strong>Reference Number:</strong> ${payment.reference_number || 'N/A'}</p>
                            <p><strong>Date Submitted:</strong> ${new Date(payment.created_at).toLocaleDateString()}</p>
                            <p><strong>Date Confirmed:</strong> ${payment.confirmed_at ? new Date(payment.confirmed_at).toLocaleDateString() : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Student/Enrollee Information</h6>
                            <p><strong>Name:</strong> ${payment.payable ? payment.payable.first_name + ' ' + payment.payable.last_name : 'N/A'}</p>
                            <p><strong>ID:</strong> ${payment.payable ? (payment.payable.student_id || payment.payable.application_id) : 'N/A'}</p>
                            <p><strong>Fee Type:</strong> ${payment.fee ? payment.fee.name : 'N/A'}</p>
                            <p><strong>Status:</strong> <span class="badge bg-success">${payment.confirmation_status}</span></p>
                            ${payment.cashier ? `<p><strong>Processed By:</strong> ${payment.cashier.full_name} (${payment.cashier.employee_id})</p>` : ''}
                        </div>
                    </div>
                    ${payment.cashier_notes ? `<div class="mt-3"><h6>Cashier Notes</h6><p class="bg-light p-3 rounded">${payment.cashier_notes}</p></div>` : ''}
                    ${payment.notes ? `<div class="mt-3"><h6>Payment Notes</h6><p>${payment.notes}</p></div>` : ''}
                `;
            }

            function printReceipt(paymentId) {
                // Implementation for printing receipt
                console.log('Print receipt for payment ID:', paymentId);
                alert('Receipt printing functionality will be implemented');
            }

            function printReceiptFromModal() {
                if (currentPaymentId) {
                    printReceipt(currentPaymentId);
                }
            }

            function exportCompletedPayments() {
                // Implementation for exporting completed payments
                console.log('Export completed payments');
                alert('Export functionality will be implemented');
            }
        </script>
    @endpush
</x-cashier-layout>
