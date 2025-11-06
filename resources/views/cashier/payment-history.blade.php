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
                    <p class="text-muted mb-0">Complete history of all confirmed payment transactions</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-success fs-6" id="confirmed-count">0 Confirmed</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Payment History -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-history-line me-2"></i>Confirmed Payment Records
                        </h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm payment-filter" name="payment_method">
                                <option value="">All Payment Methods</option>
                                <option value="full">Full Payment</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="monthly">Monthly</option>
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
                                    <th>Priority</th>
                                    <th>Transaction ID</th>
                                    <th>Student ID</th>
                                    <th>Amount</th>
                                    <th>Confirmed Date</th>
                                    <th>Status</th>
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
                    <div id="pagination-container"></div>
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
            // Payment History JavaScript - Updated to match current structure
            document.addEventListener('DOMContentLoaded', function() {
                initializePaymentHistory();
                setupRealTimeUpdates();
                setupFiltersAndSearch();
            });

            function initializePaymentHistory() {
                console.log('Payment History initialized');
                
                // Load initial data
                loadPaymentHistory();
                
                // Setup action handlers
                setupActionHandlers();
            }

            function loadPaymentHistory(filters = {}) {
                // Always filter for confirmed payments only in payment history
                filters.status = 'confirmed';
                const params = new URLSearchParams(filters);
                
                fetch(`/cashier/api/payment-history?${params}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Payment history API response:', data);
                    if (data.success) {
                        // Handle the nested data structure from the API
                        const payments = data.payments && data.payments.data ? data.payments.data : [];
                        console.log('Extracted payments:', payments);
                        updatePaymentHistoryTable(payments);
                        updateStatisticsDisplay(data.statistics);
                    }
                })
                .catch(error => {
                    console.error('Error loading payment history:', error);
                    showAlert('Error loading payment history', 'danger');
                });
            }

            function setupRealTimeUpdates() {
                // Real-time updates every 30 seconds
                setInterval(() => {
                    loadPaymentHistory();
                }, 30000);
                
                console.log('Real-time updates enabled for payment history');
            }

            function updatePaymentHistoryTable(payments) {
                const tableBody = document.querySelector('#payment-history-table tbody');
                if (!tableBody) return;
                
                if (payments && payments.length > 0) {
                    tableBody.innerHTML = payments.map((payment, index) => createPaymentHistoryRow(payment, index)).join('');
                } else {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ri-inbox-line fs-1 mb-2"></i>
                                    <p>No confirmed payments found</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }
            }

            function createPaymentHistoryRow(payment, index) {
                const student = payment.payable || payment.student;
                const priorityBadge = getPriorityBadge(index + 1);
                
                return `
                    <tr>
                        <td>${priorityBadge}</td>
                        <td>
                            <div class="fw-semibold">${payment.transaction_id}</div>
                            <small class="text-muted">${getPaymentMethodDisplay(payment.payment_method)} - ${payment.period_name || 'Payment'}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">${student ? student.student_id : 'N/A'}</div>
                            <small class="text-muted">${student ? (student.first_name + ' ' + student.last_name) : 'Unknown Student'}</small>
                        </td>
                        <td>
                            <span class="fw-bold text-success">₱${formatNumber(payment.amount)}</span>
                        </td>
                        <td>
                            <div class="fw-semibold">${formatDate(payment.confirmed_at)}</div>
                            <small class="text-muted">${formatTime(payment.confirmed_at)}</small>
                        </td>
                        <td>
                            <span class="badge bg-success">Confirmed</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="viewPaymentScheduleDetails('${student ? student.student_id : payment.payable_id}', '${payment.payment_method}')" title="View Schedule">
                                    <i class="ri-eye-line"></i>
                                </button>
                                <button class="btn btn-outline-success" onclick="printReceipt('${payment.transaction_id}')" title="Print Receipt">
                                    <i class="ri-printer-line"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }

            function updateStatisticsDisplay(statistics) {
                // Update confirmed count
                const confirmedCount = document.getElementById('confirmed-count');
                if (confirmedCount && statistics) {
                    confirmedCount.textContent = `${statistics.confirmed_payments || 0} Confirmed`;
                }
            }

            function getPriorityBadge(priority) {
                if (priority <= 3) {
                    return `<span class="badge bg-danger">${priority}</span>`;
                } else if (priority <= 10) {
                    return `<span class="badge bg-warning">${priority}</span>`;
                } else {
                    return `<span class="badge bg-secondary">${priority}</span>`;
                }
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

            // Utility functions - Updated to match current structure
            function getPaymentMethodDisplay(method) {
                switch(method) {
                    case 'full':
                        return 'Full Payment';
                    case 'quarterly':
                        return 'Quarterly';
                    case 'monthly':
                        return 'Monthly';
                    default:
                        return 'Full Payment';
                }
            }

            function formatNumber(number) {
                return new Intl.NumberFormat().format(number || 0);
            }

            function formatDate(dateString) {
                if (!dateString || dateString === 'null' || dateString === 'undefined') {
                    return 'N/A';
                }
                
                try {
                    const date = new Date(dateString);
                    if (isNaN(date.getTime())) {
                        return 'N/A';
                    }
                    
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                } catch (error) {
                    console.error('Date formatting error:', error);
                    return 'N/A';
                }
            }

            function formatTime(dateString) {
                if (!dateString || dateString === 'null' || dateString === 'undefined') {
                    return '';
                }
                
                try {
                    const date = new Date(dateString);
                    if (isNaN(date.getTime())) {
                        return '';
                    }
                    
                    return date.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch (error) {
                    console.error('Time formatting error:', error);
                    return '';
                }
            }

            function setupFiltersAndSearch() {
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

            function setupActionHandlers() {
                // Action handlers are set up via onclick attributes in the HTML
                console.log('Action handlers ready');
            }

            function showAlert(message, type = 'info') {
                // Simple alert implementation
                const alertClass = type === 'danger' ? 'alert-danger' : 
                                 type === 'success' ? 'alert-success' : 
                                 type === 'warning' ? 'alert-warning' : 'alert-info';
                
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                // Try to find a container for alerts, or create one
                let alertContainer = document.querySelector('.alert-container');
                if (!alertContainer) {
                    alertContainer = document.createElement('div');
                    alertContainer.className = 'alert-container position-fixed top-0 end-0 p-3';
                    alertContainer.style.zIndex = '9999';
                    document.body.appendChild(alertContainer);
                }
                
                alertContainer.insertAdjacentHTML('beforeend', alertHtml);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    const alerts = alertContainer.querySelectorAll('.alert');
                    if (alerts.length > 0) {
                        alerts[0].remove();
                    }
                }, 5000);
            }

            let currentPaymentId = null;

            function viewPaymentHistoryDetails(paymentId) {
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
                            
                            const modalElement = document.getElementById('paymentDetailsModal');
                            if (typeof bootstrap !== 'undefined') {
                                new bootstrap.Modal(modalElement).show();
                            } else {
                                // Fallback for when Bootstrap is not available
                                modalElement.classList.add('show');
                                modalElement.style.display = 'block';
                                modalElement.setAttribute('aria-modal', 'true');
                                modalElement.setAttribute('role', 'dialog');
                                
                                // Add backdrop
                                const backdrop = document.createElement('div');
                                backdrop.className = 'modal-backdrop fade show';
                                document.body.appendChild(backdrop);
                            }
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
                // Open cashier receipt PDF in a new tab using transaction_id
                if (!paymentId) {
                    alert('Invalid transaction ID');
                    return;
                }
                window.open(`/pdf/cashier-receipt?transaction_id=${paymentId}`, '_blank');
            }

            function printReceiptFromModal() {
                if (currentPaymentId) {
                    printReceipt(currentPaymentId);
                }
            }
        </script>
    @endpush
</x-cashier-layout>
