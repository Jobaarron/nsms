 let currentPaymentId = null;
 let currentTransactionId = null;
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
                
                // All endpoints use the same payment-archives API with different filters
                let endpoint = '/cashier/api/payment-archives';
                
                // Add status filter based on tab
                if (type === 'completed') {
                    filters.status = 'confirmed';
                } else if (type === 'history') {
                    // History shows all payments, no additional filter needed
                }
                
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
                                ${payment.cashier && payment.cashier.full_name ? `
                                    <div class="fw-semibold">${payment.cashier.full_name}</div>
                                    <small class="text-muted">${payment.cashier.employee_id || 'N/A'}</small>
                                ` : payment.cashier && (payment.cashier.first_name || payment.cashier.last_name) ? `
                                    <div class="fw-semibold">${(payment.cashier.first_name || '') + ' ' + (payment.cashier.last_name || '')}</div>
                                    <small class="text-muted">${payment.cashier.employee_id || 'N/A'}</small>
                                ` : payment.processed_by ? `
                                    <div class="fw-semibold">Cashier</div>
                                    <small class="text-muted">ID: ${payment.processed_by}</small>
                                ` : '<span class="text-muted">System</span>'}
                            </td>
                        ` : `<td>${statusBadge}</td>`}
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="viewPaymentDetails(${payment.id})" title="View Details">
                                    <i class="ri-eye-line"></i>
                                </button>
                                ${payment.confirmation_status === 'confirmed' ? `
                                    <button class="btn btn-outline-success" onclick="printReceipt('${payment.transaction_id}')" title="Print Receipt">
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
                            currentTransactionId = data.payment.transaction_id;
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
                            ${payment.payable && payment.payable.grade_level ? `<p><strong>Grade Level:</strong> ${payment.payable.grade_level}</p>` : ''}
                            ${payment.payable && payment.payable.strand ? `<p><strong>Strand:</strong> ${payment.payable.strand}</p>` : ''}
                            ${payment.payable && payment.payable.track ? `<p><strong>Track:</strong> ${payment.payable.track}</p>` : ''}
                            <p><strong>Fee Type:</strong> ${payment.fee ? payment.fee.name : 'N/A'}</p>
                            ${payment.cashier && payment.cashier.full_name ? 
                                `<p><strong>Processed By:</strong> ${payment.cashier.full_name} (${payment.cashier.employee_id || 'N/A'})</p>` : 
                                payment.cashier && (payment.cashier.first_name || payment.cashier.last_name) ? 
                                `<p><strong>Processed By:</strong> ${(payment.cashier.first_name || '') + ' ' + (payment.cashier.last_name || '')} (${payment.cashier.employee_id || 'N/A'})</p>` :
                                payment.processed_by ? 
                                `<p><strong>Processed By:</strong> Cashier (ID: ${payment.processed_by})</p>` : ''}
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
                if (currentTransactionId) {
                    printReceipt(currentTransactionId);
                } else if (currentPaymentId) {
                    // Fallback: if no transaction ID, show error
                    alert('Cannot print receipt: Missing transaction ID. Please refresh and try again.');
                }
            }

            function exportArchives() {
                console.log('Export payment archives');
                
                // Show loading state
                const exportBtn = document.querySelector('button[onclick="exportArchives()"]');
                const originalText = exportBtn.innerHTML;
                exportBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Exporting...';
                exportBtn.disabled = true;
                
                // Get current filters
                const filters = {
                    payment_method: document.querySelector('select[name="payment_method"]')?.value || '',
                    status: document.querySelector('select[name="status"]')?.value || '',
                    date_range: document.querySelector('select[name="date_range"]')?.value || '',
                    search: document.querySelector('#payment-search')?.value || ''
                };
                
                // Create URL with filters
                const params = new URLSearchParams(filters);
                
                // Fetch all data for export
                fetch(`/cashier/api/payment-archives?${params}&export=true&limit=all`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.payments && data.payments.data) {
                        exportToCSV(data.payments.data);
                    } else {
                        showAlert('No data available for export', 'warning');
                    }
                })
                .catch(error => {
                    console.error('Export error:', error);
                    showAlert('Failed to export payment archives', 'danger');
                })
                .finally(() => {
                    // Restore button state
                    exportBtn.innerHTML = originalText;
                    exportBtn.disabled = false;
                });
            }
            
            function exportToCSV(payments) {
                const headers = [
                    'Transaction ID',
                    'Student ID', 
                    'Student Name',
                    'Amount',
                    'Payment Method',
                    'Period',
                    'Status',
                    'Paid Date',
                    'Processed By',
                    'Notes'
                ];
                
                const csvContent = [
                    headers.join(','),
                    ...payments.map(payment => {
                        const student = payment.payable;
                        const row = [
                            `"${payment.transaction_id}"`,
                            `"${student?.student_id || 'N/A'}"`,
                            `"${student?.first_name || ''} ${student?.last_name || ''}"`.trim(),
                            `"${payment.amount}"`,
                            `"${payment.payment_method.charAt(0).toUpperCase() + payment.payment_method.slice(1)}"`,
                            `"${payment.period_name || 'N/A'}"`,
                            `"${payment.confirmation_status.charAt(0).toUpperCase() + payment.confirmation_status.slice(1)}"`,
                            `"${payment.paid_at ? new Date(payment.paid_at).toLocaleDateString() : 'N/A'}"`,
                            `"${payment.cashier && payment.cashier.full_name ? payment.cashier.full_name : 
                                payment.cashier && (payment.cashier.first_name || payment.cashier.last_name) ? 
                                (payment.cashier.first_name || '') + ' ' + (payment.cashier.last_name || '') : 
                                payment.processed_by ? 'Cashier ID: ' + payment.processed_by : 'System'}"`,
                            `"${(payment.cashier_notes || '').replace(/"/g, '""')}"`
                        ];
                        return row.join(',');
                    })
                ].join('\n');
                
                // Create and download file
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `payment_archives_${new Date().toISOString().split('T')[0]}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showAlert('Payment archives exported successfully', 'success');
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

            // Export functions to global scope
            window.exportArchives = exportArchives;
            window.viewPaymentDetails = viewPaymentDetails;
            window.printReceipt = printReceipt;
            window.printReceiptFromModal = printReceiptFromModal;