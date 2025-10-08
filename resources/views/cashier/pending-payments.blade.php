<x-cashier-layout>
    @push('styles')
        @vite('resources/css/index_student.css')
    @endpush
    @vite(['resources/js/cashier-payment-schedules.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="section-title mb-1">Pending Payments</h2>
                    <p class="text-muted mb-0">Review and confirm pending payment submissions</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-warning fs-6">{{ $payments->total() }} Pending</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Payment Schedules -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-calendar-schedule-line me-2"></i>Student Payment Schedules
                        </h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm payment-filter" name="status">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <select class="form-select form-select-sm payment-filter" name="payment_mode">
                                <option value="">All Modes</option>
                                <option value="full">Full Payment</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                            <input type="text" class="form-control form-control-sm" id="payment-search" placeholder="Search...">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="payment-schedules-table">
                            <thead>
                                <tr>
                                    <th>Priority</th>
                                    <th>Transaction ID</th>
                                    <th>Student</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Scheduled Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="text-muted mt-2">Loading payment schedules...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="pagination-container"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Regular Payments Table - Commented out --}}
    {{--
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-time-line me-2"></i>Regular Payments Awaiting Confirmation
                    </h5>
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
                                        <th>Reference</th>
                                        <th>Date Submitted</th>
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
                                                <small>{{ $payment->reference_number ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $payment->created_at->format('M d, Y g:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewPaymentDetails({{ $payment->id }})">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-success" 
                                                            onclick="confirmPayment({{ $payment->id }})">
                                                        <i class="ri-check-line"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="rejectPayment({{ $payment->id }})">
                                                        <i class="ri-close-line"></i>
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
                            <i class="ri-check-double-line fs-1 text-success mb-3"></i>
                            <h5 class="text-muted">No Pending Payments</h5>
                            <p class="text-muted">All payments have been processed!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    --}}

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
                    {{-- <button type="button" class="btn btn-success" onclick="confirmFromModal()">
                        <i class="ri-check-line me-2"></i>Confirm Payment
                    </button>
                    <button type="button" class="btn btn-danger" onclick="rejectFromModal()">
                        <i class="ri-close-line me-2"></i>Reject Payment
                    </button> --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Payment Modal -->
    <div class="modal fade" id="confirmPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to confirm this payment?</p>
                    <div class="mb-3">
                        <label for="confirmNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="confirmNotes" rows="3" 
                                  placeholder="Add any notes about this confirmation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="processConfirmation()">
                        <i class="ri-check-line me-2"></i>Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Payment Modal -->
    <div class="modal fade" id="rejectPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Please provide a reason for rejecting this payment:</p>
                    <div class="mb-3">
                        <label for="rejectNotes" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectNotes" rows="3" required
                                  placeholder="Explain why this payment is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="processRejection()">
                        <i class="ri-close-line me-2"></i>Reject Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentPaymentId = null;

            function confirmPayment(paymentId) {
                currentPaymentId = paymentId;
                const modal = new bootstrap.Modal(document.getElementById('confirmPaymentModal'));
                modal.show();
            }

            function rejectPayment(paymentId) {
                currentPaymentId = paymentId;
                const modal = new bootstrap.Modal(document.getElementById('rejectPaymentModal'));
                modal.show();
            }

            function confirmFromModal() {
                if (currentPaymentId) {
                    confirmPayment(currentPaymentId);
                }
            }

            function rejectFromModal() {
                if (currentPaymentId) {
                    rejectPayment(currentPaymentId);
                }
            }

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
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Payment Information</h6>
                            <p><strong>Transaction ID:</strong> ${payment.transaction_id}</p>
                            <p><strong>Amount:</strong> ₱${parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                            <p><strong>Payment Method:</strong> ${payment.payment_method}</p>
                            <p><strong>Reference Number:</strong> ${payment.reference_number || 'N/A'}</p>
                            <p><strong>Date Submitted:</strong> ${new Date(payment.created_at).toLocaleDateString()}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Student/Enrollee Information</h6>
                            <p><strong>Name:</strong> ${payment.payable ? payment.payable.first_name + ' ' + payment.payable.last_name : 'N/A'}</p>
                            <p><strong>ID:</strong> ${payment.payable ? (payment.payable.student_id || payment.payable.application_id) : 'N/A'}</p>
                            <p><strong>Fee Type:</strong> ${payment.fee ? payment.fee.name : 'N/A'}</p>
                            <p><strong>Status:</strong> <span class="badge bg-warning">${payment.confirmation_status}</span></p>
                        </div>
                    </div>
                    ${payment.notes ? `<div class="mt-3"><h6>Notes</h6><p>${payment.notes}</p></div>` : ''}
                `;
            }

            function confirmPayment(paymentId) {
                currentPaymentId = paymentId;
                new bootstrap.Modal(document.getElementById('confirmPaymentModal')).show();
            }

            function rejectPayment(paymentId) {
                currentPaymentId = paymentId;
                new bootstrap.Modal(document.getElementById('rejectPaymentModal')).show();
            }

            function confirmFromModal() {
                if (currentPaymentId) {
                    confirmPayment(currentPaymentId);
                    bootstrap.Modal.getInstance(document.getElementById('paymentDetailsModal')).hide();
                }
            }

            function rejectFromModal() {
                if (currentPaymentId) {
                    rejectPayment(currentPaymentId);
                    bootstrap.Modal.getInstance(document.getElementById('paymentDetailsModal')).hide();
                }
            }

            function processConfirmation() {
                const notes = document.getElementById('confirmNotes').value;
                
                fetch(`/cashier/payments/${currentPaymentId}/confirm`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        cashier_notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('confirmPaymentModal')).hide();
                        location.reload();
                    } else {
                        alert('Failed to confirm payment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to confirm payment');
                });
            }

            function processRejection() {
                const notes = document.getElementById('rejectNotes').value;
                
                if (!notes.trim()) {
                    alert('Please provide a reason for rejection');
                    return;
                }
                
                fetch(`/cashier/payments/${currentPaymentId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        cashier_notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('rejectPaymentModal')).hide();
                        location.reload();
                    } else {
                        alert('Failed to reject payment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to reject payment');
                });
            }
        </script>
    @endpush
</x-cashier-layout>
