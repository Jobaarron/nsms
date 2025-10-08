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
                    <h2 class="section-title mb-1">Payment History</h2>
                    <p class="text-muted mb-0">Complete history of all payment transactions</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-info fs-6">{{ $payments->total() }} Records</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        {{-- <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="card-title mb-0">
                        <i class="ri-filter-line me-2"></i>Filters
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('cashier.payment-history') }}">
                        <div class="row g-3">
                            <div class="col-lg-2 col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Not yet paid</option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Paid</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Declined</option>
                                </select>
                            </div>
                            <!-- Payment Method filter removed for on-site processing -->
                            <!-- <div class="col-lg-2 col-md-4">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="">All Methods</option>
                                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="online_payment" {{ request('payment_method') == 'online_payment' ? 'selected' : '' }}>Online Payment</option>
                                    <option value="scholarship" {{ request('payment_method') == 'scholarship' ? 'selected' : '' }}>Scholarship</option>
                                    <option value="voucher" {{ request('payment_method') == 'voucher' ? 'selected' : '' }}>Voucher</option>
                                </select>
                            </div> -->
                            <!-- Payment mode filter removed - now handled by payment_method -->
                            <!-- <div class="col-lg-2 col-md-4">
                                <label for="payment_mode" class="form-label">Payment Schedule</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="">All Schedules</option>
                                    <option value="full" {{ request('payment_method') == 'full' ? 'selected' : '' }}>Full Payment</option>
                                    <option value="quarterly" {{ request('payment_method') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                    <option value="monthly" {{ request('payment_method') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div> -->
                            <div class="col-lg-2 col-md-6">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="{{ request('date_to') }}">
                            </div>
                            <div class="col-lg-2 col-md-8">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Student ID, Name, Transaction ID...">
                            </div>
                            <div class="col-lg-12 col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i>
                                    </button>
                                    <a href="{{ route('cashier.payment-history') }}" class="btn btn-outline-secondary">
                                        <i class="ri-refresh-line"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}
    </div>

    <!-- Payments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-history-line me-2"></i>Payment Records
                        </h5>
                        {{-- <button class="btn btn-outline-success btn-sm" onclick="exportPaymentHistory()">
                            <i class="ri-download-line me-2"></i>Export All
                        </button> --}}
                    </div>
                </div>
                <div class="card-body">
                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Priority</th>
                                        <th>Transaction ID</th>
                                        <th>Student ID</th>
                                        <th>Amount</th>
                                        <th>Scheduled Date</th>
                                        <th>Status</th>
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
{{-- <td>{{ $payment->fee->name ?? 'N/A' }}</td> --}}
                                            <td>
                                                <span class="fw-bold text-success">
                                                    ₱{{ number_format($payment->amount, 2) }}
                                                </span>
                                            </td>
                                            <!-- Payment method badge removed for on-site processing -->
                                            <!-- <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst($payment->payment_method) }}
                                                </span>
                                            </td> -->
                                            <td>
                                                @php
                                                    $statusClass = match($payment->confirmation_status) {
                                                        'pending' => 'warning',
                                                        'confirmed' => 'success',
                                                        'rejected' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">
                                                    {{ ucfirst($payment->confirmation_status) }}
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
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <small><strong>Submitted:</strong> {{ $payment->created_at->format('M d, Y') }}</small>
                                                    @if($payment->confirmed_at)
                                                        <br>
                                                        <small><strong>Confirmed:</strong> {{ $payment->confirmed_at->format('M d, Y') }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewPaymentDetails({{ $payment->id }})">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    @if($payment->confirmation_status === 'confirmed')
                                                        <button class="btn btn-sm btn-outline-success" 
                                                                onclick="printReceipt({{ $payment->id }})">
                                                            <i class="ri-printer-line"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $payments->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ri-file-list-line fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No Payment Records Found</h5>
                            <p class="text-muted">Try adjusting your search filters</p>
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
                    <button type="button" class="btn btn-success" id="printReceiptBtn" onclick="printReceiptFromModal()" style="display: none;">
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
                            
                            // Show/hide print button based on status
                            const printBtn = document.getElementById('printReceiptBtn');
                            if (data.payment.confirmation_status === 'confirmed') {
                                printBtn.style.display = 'inline-block';
                            } else {
                                printBtn.style.display = 'none';
                            }
                            
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
                const statusClass = payment.confirmation_status === 'confirmed' ? 'success' : 
                                  payment.confirmation_status === 'rejected' ? 'danger' : 'warning';
                
                content.innerHTML = `
                    <div class="alert alert-${statusClass}">
                        <i class="ri-information-line me-2"></i>
                        <strong>Status:</strong> ${payment.confirmation_status.charAt(0).toUpperCase() + payment.confirmation_status.slice(1)}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Payment Information</h6>
                            <p><strong>Transaction ID:</strong> ${payment.transaction_id}</p>
                            <p><strong>Amount:</strong> ₱${parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                            <p><strong>Payment Method:</strong> ${payment.payment_method}</p>
                            <p><strong>Reference Number:</strong> ${payment.reference_number || 'N/A'}</p>
                            <p><strong>Date Submitted:</strong> ${new Date(payment.created_at).toLocaleDateString()}</p>
                            ${payment.confirmed_at ? `<p><strong>Date Confirmed:</strong> ${new Date(payment.confirmed_at).toLocaleDateString()}</p>` : ''}
                        </div>
                        <div class="col-md-6">
                            <h6>Student/Enrollee Information</h6>
                            <p><strong>Name:</strong> ${payment.payable ? payment.payable.first_name + ' ' + payment.payable.last_name : 'N/A'}</p>
                            <p><strong>ID:</strong> ${payment.payable ? (payment.payable.student_id || payment.payable.application_id) : 'N/A'}</p>
                            <p><strong>Fee Type:</strong> ${payment.fee ? payment.fee.name : 'N/A'}</p>
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

            function exportPaymentHistory() {
                // Implementation for exporting payment history
                console.log('Export payment history');
                alert('Export functionality will be implemented');
            }
        </script>
    @endpush
</x-cashier-layout>
