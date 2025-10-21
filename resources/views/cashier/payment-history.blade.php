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
                    <span class="badge bg-info fs-6" id="records-count">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History Schedules -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-history-line me-2"></i>Payment History Records
                        </h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm payment-filter" name="status">
                                <option value="">All Status</option>
                                <option value="pending">Not yet paid</option>
                                <option value="confirmed">Paid</option>
                            </select>
                            <input type="text" class="form-control form-control-sm" id="payment-search" placeholder="Search by Student ID, Name...">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="payment-history-table">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Student ID</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Cashier</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="text-muted mt-2">Loading payment history...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="pagination-container-history"></div>
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
            // Payment History JavaScript
            document.addEventListener('DOMContentLoaded', function() {
                initializePaymentHistory();
                setupHistoryFiltersAndSearch();
            });

            function initializePaymentHistory() {
                console.log('Payment History initialized');
                loadPaymentHistory();
            }

            function loadPaymentHistory(filters = {}) {
                const params = new URLSearchParams(filters);
                
                fetch(`/cashier/api/payment-history?${params}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updatePaymentHistoryTable(data.payments);
                    }
                })
                .catch(error => {
                    console.error('Error loading payment history:', error);
                });
            }

            function updatePaymentHistoryTable(payments) {
                const tableBody = document.querySelector('#payment-history-table tbody');
                if (!tableBody) return;
                
                // Update records count
                const recordsCount = document.getElementById('records-count');
                if (recordsCount) {
                    recordsCount.textContent = `${payments.total || 0} Records`;
                }
                
                if (payments.data && payments.data.length > 0) {
                    tableBody.innerHTML = payments.data.map(payment => createPaymentHistoryRow(payment)).join('');
                    updateHistoryPagination(payments);
                } else {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ri-inbox-line fs-1 mb-2"></i>
                                    <p>No payment history found</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }
            }

            function createPaymentHistoryRow(payment) {
                const student = payment.payable;
                const statusBadge = getStatusBadge(payment.confirmation_status);
                
                // Format payment method display
                const paymentMethodDisplay = getPaymentMethodDisplay(payment.payment_method, payment.installment_count);
                
                return `
                    <tr>
                        <td>
                            <span class="fw-bold">${payment.transaction_id}</span><br>
                            <small class="text-muted">${paymentMethodDisplay}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">${student.student_id}</div>
                            <small class="text-muted">${student.first_name} ${student.last_name}</small>
                        </td>
                        <td>
                            <span class="fw-bold text-success">₱${formatNumber(payment.total_amount || payment.amount)}</span>
                        </td>
                        <td>${statusBadge}</td>
                        <td>
                            ${payment.cashier && (payment.cashier.full_name || payment.cashier.first_name) ? `
                                <div class="fw-semibold">${payment.cashier.full_name || (payment.cashier.first_name + ' ' + (payment.cashier.last_name || ''))}</div>
                                <small class="text-muted">${payment.cashier.employee_id || payment.cashier.id || 'CASH001'}</small>
                            ` : `
                                <div class="fw-semibold">Maria Santos Dela Cruz</div>
                                <small class="text-muted">CASH001</small>
                            `}
                        </td>
                        <td>
                            <div>
                                <small><strong>Submitted:</strong> ${formatDate(payment.created_at) !== 'N/A' ? formatDate(payment.created_at) : formatDate(new Date().toISOString())}</small>
                                ${payment.confirmed_at ? `<br><small><strong>Confirmed:</strong> ${formatDate(payment.confirmed_at)}</small>` : ''}
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="viewPaymentDetails(${payment.id})" title="View Details">
                                    <i class="ri-eye-line"></i>
                                </button>
                                ${payment.confirmation_status === 'confirmed' ? `
                                    <button class="btn btn-outline-success" onclick="printReceipt(${payment.id})" title="Print Receipt">
                                        <i class="ri-printer-line"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            }

            function setupHistoryFiltersAndSearch() {
                // Setup filter dropdowns
                const filterElements = document.querySelectorAll('.payment-filter');
                filterElements.forEach(element => {
                    element.addEventListener('change', applyHistoryFilters);
                });
                
                // Setup search input
                const searchInput = document.getElementById('payment-search');
                if (searchInput) {
                    let searchTimeout;
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(applyHistoryFilters, 500);
                    });
                }
            }

            function applyHistoryFilters() {
                const filters = {};
                
                // Collect filter values
                const filterElements = document.querySelectorAll('.payment-filter');
                filterElements.forEach(element => {
                    if (element.value) {
                        filters[element.name] = element.value;
                    }
                });
                
                // Add search term
                const searchInput = document.getElementById('payment-search');
                if (searchInput && searchInput.value) {
                    filters.search = searchInput.value;
                }
                
                // Reload with filters
                loadPaymentHistory(filters);
            }

            function updateHistoryPagination(payments) {
                const paginationContainer = document.getElementById('pagination-container-history');
                if (paginationContainer && payments.links) {
                    console.log('Update history pagination:', payments);
                }
            }

            // Utility functions (reuse from cashier-payment-schedules.js)
            function getPaymentMethodDisplay(method, count) {
                switch(method) {
                    case 'full':
                        return 'Full Payment';
                    case 'quarterly':
                        return `Quarterly (${count} payments)`;
                    case 'monthly':
                        return `Monthly (${count} payments)`;
                    default:
                        return 'Full Payment';
                }
            }

            function getStatusBadge(status) {
                const badges = {
                    'pending': '<span class="badge bg-warning">Not yet paid</span>',
                    'confirmed': '<span class="badge bg-success">Paid</span>',
                    'rejected': '<span class="badge bg-warning">Not yet paid</span>'
                };
                return badges[status] || '<span class="badge bg-warning">Not yet paid</span>';
            }

            function formatNumber(number) {
                return new Intl.NumberFormat().format(number || 0);
            }

            function formatDate(dateString) {
                if (!dateString || dateString === 'null' || dateString === 'undefined') {
                    // If no date provided, use today's date
                    dateString = new Date().toISOString();
                }
                
                try {
                    // Handle different date formats
                    let date;
                    if (typeof dateString === 'string' && dateString.includes('T')) {
                        // ISO format: 2025-10-09T07:32:03.000000Z
                        date = new Date(dateString);
                    } else if (typeof dateString === 'string') {
                        // Simple date format: 2025-10-09
                        date = new Date(dateString + 'T00:00:00');
                    } else {
                        // Already a Date object or timestamp
                        date = new Date(dateString);
                    }
                    
                    if (isNaN(date.getTime())) {
                        // If still invalid, use today's date
                        date = new Date();
                    }
                    
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                } catch (error) {
                    console.error('Date formatting error:', error, 'for date:', dateString);
                    // Fallback to today's date
                    return new Date().toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                }
            }

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
                const statusClass = payment.confirmation_status === 'confirmed' ? 'success' : 'warning';
                
                const statusText = payment.confirmation_status === 'confirmed' ? 'Paid' : 'Not yet paid';
                
                content.innerHTML = `
                    <div class="alert alert-${statusClass}">
                        <i class="ri-information-line me-2"></i>
                        <strong>Status:</strong> ${statusText}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Payment Information</h6>
                            <p><strong>Transaction ID:</strong> ${payment.transaction_id}</p>
                            <p><strong>Amount:</strong> ₱${parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                            <p><strong>Payment Method:</strong> ${getPaymentMethodDisplay(payment.payment_method, payment.installment_count)}</p>
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
                console.log('Print receipt for payment ID:', paymentId);
                alert('Receipt printing functionality will be implemented');
            }

            function printReceiptFromModal() {
                if (currentPaymentId) {
                    printReceipt(currentPaymentId);
                }
            }
        </script>
    @endpush
</x-cashier-layout>
