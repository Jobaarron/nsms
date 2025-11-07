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
                    <h2 class="section-title mb-1">Payment Archives</h2>
                    <p class="text-muted mb-0">Complete history of all confirmed and completed payments</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-success fs-6" id="total-count">0 Total</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <ul class="nav nav-tabs card-header-tabs" id="archiveTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-payments" type="button" role="tab">
                                <i class="ri-file-list-line me-2"></i>All Payments
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed-payments" type="button" role="tab">
                                <i class="ri-check-double-line me-2"></i>Completed
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#payment-history" type="button" role="tab">
                                <i class="ri-history-line me-2"></i>History
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <!-- Filters Row -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select payment-filter" name="payment_method">
                                <option value="">All Payment Methods</option>
                                <option value="full">Full Payment</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select payment-filter" name="status">
                                <option value="">All Status</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="payment-search" placeholder="Search by Student ID, Name, Transaction ID...">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-success w-100" onclick="exportArchives()">
                                <i class="ri-download-line me-2"></i>Export
                            </button>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content" id="archiveTabContent">
                        <!-- All Payments Tab -->
                        <div class="tab-pane fade show active" id="all-payments" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover" id="all-payments-table">
                                    <thead>
                                        <tr>
                                            <th>Priority</th>
                                            <th>Transaction ID</th>
                                            <th>Student Info</th>
                                            <th>Amount</th>
                                            <th>Date</th>
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
                                                <p class="text-muted mt-2">Loading payment archives...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Completed Payments Tab -->
                        <div class="tab-pane fade" id="completed-payments" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover" id="completed-payments-table">
                                    <thead>
                                        <tr>
                                            <th>Priority</th>
                                            <th>Transaction ID</th>
                                            <th>Student Info</th>
                                            <th>Amount</th>
                                            <th>Confirmed Date</th>
                                            <th>Processed By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Content loaded via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Payment History Tab -->
                        <div class="tab-pane fade" id="payment-history" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover" id="payment-history-table">
                                    <thead>
                                        <tr>
                                            <th>Priority</th>
                                            <th>Transaction ID</th>
                                            <th>Student Info</th>
                                            <th>Amount</th>
                                            <th>Confirmed Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Content loaded via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination-container" class="d-flex justify-content-center mt-4"></div>
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
                    <button type="button" class="btn btn-success" id="printReceiptBtn" onclick="printReceiptFromModal()">
                        <i class="ri-printer-line me-2"></i>Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentPaymentId = null;
            let currentTab = 'all';

            document.addEventListener('DOMContentLoaded', function() {
                initializePaymentArchives();
                setupTabHandlers();
                setupFiltersAndSearch();
            });

            function initializePaymentArchives() {
                console.log('Payment Archives initialized');
                loadPaymentData('all');
            }

            function setupTabHandlers() {
                const tabButtons = document.querySelectorAll('#archiveTabs button[data-bs-toggle="tab"]');
                tabButtons.forEach(button => {
                    button.addEventListener('shown.bs.tab', function(event) {
                        const target = event.target.getAttribute('data-bs-target');
                        currentTab = target.replace('#', '').replace('-payments', '').replace('-', '');
                        loadPaymentData(currentTab);
                    });
                });
            }

            function loadPaymentData(type) {
                const filters = collectFilters();
                filters.type = type;
                
                const endpoint = type === 'all' ? '/cashier/api/payment-archives' : 
                               type === 'completed' ? '/cashier/api/completed-payments' : 
                               '/cashier/api/payment-history';
                
                const params = new URLSearchParams(filters);
                
                fetch(`${endpoint}?${params}`, {
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
                    if (data.success) {
                        const payments = data.payments && data.payments.data ? data.payments.data : data.payments || [];
                        updateTable(type, payments);
                        updateStatistics(data.statistics);
                    }
                })
                .catch(error => {
                    console.error('Error loading payment data:', error);
                    showAlert('Error loading payment data', 'danger');
                });
            }

            function collectFilters() {
                const filters = {};
                
                document.querySelectorAll('.payment-filter').forEach(element => {
                    if (element.value) {
                        filters[element.name] = element.value;
                    }
                });
                
                const searchInput = document.getElementById('payment-search');
                if (searchInput && searchInput.value) {
                    filters.search = searchInput.value;
                }
                
                return filters;
            }

            function updateTable(type, payments) {
                const tableId = type === 'all' ? 'all-payments-table' : 
                               type === 'completed' ? 'completed-payments-table' : 
                               'payment-history-table';
                
                const tableBody = document.querySelector(`#${tableId} tbody`);
                if (!tableBody) return;
                
                if (payments && payments.length > 0) {
                    tableBody.innerHTML = payments.map((payment, index) => createPaymentRow(payment, index, type)).join('');
                } else {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ri-inbox-line fs-1 mb-2"></i>
                                    <p>No payments found</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }
            }

            function createPaymentRow(payment, index, type) {
                const student = payment.payable || payment.student;
                const priorityBadge = getPriorityBadge(index + 1);
                const statusBadge = getStatusBadge(payment.confirmation_status);
                
                return `
                    <tr>
                        <td>${priorityBadge}</td>
                        <td>
                            <div class="fw-semibold">${payment.transaction_id}</div>
                            <small class="text-muted">${getPaymentMethodDisplay(payment.payment_method)} - ${payment.period_name || 'Payment'}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">${student ? (student.first_name + ' ' + student.last_name) : 'Unknown'}</div>
                            <small class="text-muted">${student ? (student.student_id || student.application_id) : 'N/A'}</small>
                        </td>
                        <td>
                            <span class="fw-bold text-success">₱${formatNumber(payment.amount)}</span>
                        </td>
                        <td>
                            <div class="fw-semibold">${formatDate(payment.confirmed_at || payment.created_at)}</div>
                            <small class="text-muted">${formatTime(payment.confirmed_at || payment.created_at)}</small>
                        </td>
                        ${type === 'completed' ? `
                            <td>
                                ${payment.cashier ? `
                                    <div class="fw-semibold">${payment.cashier.full_name}</div>
                                    <small class="text-muted">${payment.cashier.employee_id}</small>
                                ` : '<span class="text-muted">System</span>'}
                            </td>
                        ` : `<td>${statusBadge}</td>`}
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

            function setupFiltersAndSearch() {
                document.querySelectorAll('.payment-filter').forEach(element => {
                    element.addEventListener('change', () => loadPaymentData(currentTab));
                });
                
                const searchInput = document.getElementById('payment-search');
                if (searchInput) {
                    let searchTimeout;
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => loadPaymentData(currentTab), 500);
                    });
                }
            }

            function updateStatistics(statistics) {
                const totalCount = document.getElementById('total-count');
                if (totalCount && statistics) {
                    const total = (statistics.confirmed_payments || 0) + (statistics.completed_payments || 0);
                    totalCount.textContent = `${total} Total`;
                }
            }

            // Utility functions
            function getPriorityBadge(priority) {
                if (priority <= 3) return `<span class="badge bg-danger">${priority}</span>`;
                if (priority <= 10) return `<span class="badge bg-warning">${priority}</span>`;
                return `<span class="badge bg-secondary">${priority}</span>`;
            }

            function getStatusBadge(status) {
                switch(status) {
                    case 'confirmed': return '<span class="badge bg-success">Confirmed</span>';
                    case 'completed': return '<span class="badge bg-info">Completed</span>';
                    default: return '<span class="badge bg-secondary">Unknown</span>';
                }
            }

            function getPaymentMethodDisplay(method) {
                switch(method) {
                    case 'full': return 'Full Payment';
                    case 'quarterly': return 'Quarterly';
                    case 'monthly': return 'Monthly';
                    default: return 'Full Payment';
                }
            }

            function formatNumber(number) {
                return new Intl.NumberFormat().format(number || 0);
            }

            function formatDate(dateString) {
                if (!dateString) return 'N/A';
                try {
                    return new Date(dateString).toLocaleDateString('en-US', {
                        year: 'numeric', month: 'short', day: 'numeric'
                    });
                } catch { return 'N/A'; }
            }

            function formatTime(dateString) {
                if (!dateString) return '';
                try {
                    return new Date(dateString).toLocaleTimeString('en-US', {
                        hour: '2-digit', minute: '2-digit'
                    });
                } catch { return ''; }
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
                const statusClass = payment.confirmation_status === 'confirmed' ? 'success' : 'warning';
                
                content.innerHTML = `
                    <div class="alert alert-${statusClass}">
                        <i class="ri-information-line me-2"></i>
                        <strong>Status:</strong> ${payment.confirmation_status === 'confirmed' ? 'Confirmed & Paid' : 'Not yet confirmed'}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Payment Information</h6>
                            <p><strong>Transaction ID:</strong> ${payment.transaction_id}</p>
                            <p><strong>Amount:</strong> ₱${parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                            <p><strong>Payment Method:</strong> ${getPaymentMethodDisplay(payment.payment_method)}</p>
                            <p><strong>Reference Number:</strong> ${payment.reference_number || 'N/A'}</p>
                            <p><strong>Date Submitted:</strong> ${formatDate(payment.created_at)}</p>
                            ${payment.confirmed_at ? `<p><strong>Date Confirmed:</strong> ${formatDate(payment.confirmed_at)}</p>` : ''}
                        </div>
                        <div class="col-md-6">
                            <h6>Student Information</h6>
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

            function exportArchives() {
                console.log('Export payment archives');
                alert('Export functionality will be implemented');
            }

            function showAlert(message, type = 'info') {
                const alertClass = type === 'danger' ? 'alert-danger' : 
                                 type === 'success' ? 'alert-success' : 
                                 type === 'warning' ? 'alert-warning' : 'alert-info';
                
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                let alertContainer = document.querySelector('.alert-container');
                if (!alertContainer) {
                    alertContainer = document.createElement('div');
                    alertContainer.className = 'alert-container position-fixed top-0 end-0 p-3';
                    alertContainer.style.zIndex = '9999';
                    document.body.appendChild(alertContainer);
                }
                
                alertContainer.insertAdjacentHTML('beforeend', alertHtml);
                
                setTimeout(() => {
                    const alerts = alertContainer.querySelectorAll('.alert');
                    if (alerts.length > 0) {
                        alerts[0].remove();
                    }
                }, 5000);
            }
        </script>
    @endpush
</x-cashier-layout>
