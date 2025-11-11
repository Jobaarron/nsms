let currentPaymentId = null;
let currentTransactionId = null;

            document.addEventListener('DOMContentLoaded', function() {
                initializePaymentArchives();
                setupFiltersAndSearch();
            });

            function initializePaymentArchives() {
                console.log('Payment Archives initialized');
                loadPaymentData();
            }

            function loadPaymentData() {
                const filters = collectFilters();
                const params = new URLSearchParams(filters);
                
                fetch(`/cashier/api/payment-archives?${params}`, {
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
                        updateTable(payments);
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

            function updateTable(payments) {
                const tableBody = document.querySelector('#payment-archives-table tbody');
                if (!tableBody) return;
                
                if (payments && payments.length > 0) {
                    tableBody.innerHTML = payments.map((payment, index) => createPaymentRow(payment, index)).join('');
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

            function createPaymentRow(payment, index) {
                const student = payment.payable || payment.student;
                const priorityBadge = getPriorityBadge(index + 1);
                
                // Get cashier name (first_name + last_name)
                let processedBy = '<span class="text-muted">System</span>';
                if (payment.cashier) {
                    const cashierName = `${payment.cashier.first_name || ''} ${payment.cashier.last_name || ''}`.trim();
                    processedBy = `
                        <div class="fw-semibold">${cashierName || payment.cashier.full_name || 'Unknown Cashier'}</div>
                        <small class="text-muted">${payment.cashier.employee_id || 'N/A'}</small>
                    `;
                }
                
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
                        <td>${processedBy}</td>
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
                    element.addEventListener('change', () => loadPaymentData());
                });
                
                const searchInput = document.getElementById('payment-search');
                if (searchInput) {
                    let searchTimeout;
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => loadPaymentData(), 500);
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
                            ${payment.cashier ? `<p><strong>Processed By:</strong> ${payment.cashier.first_name || ''} ${payment.cashier.last_name || ''} (${payment.cashier.employee_id || 'N/A'})</p>` : '<p><strong>Processed By:</strong> System</p>'}
                        </div>
                    </div>
                    ${payment.cashier_notes ? `<div class="mt-3"><h6>Cashier Notes</h6><p class="bg-light p-3 rounded">${payment.cashier_notes}</p></div>` : ''}
                    ${payment.notes ? `<div class="mt-3"><h6>Payment Notes</h6><p>${payment.notes}</p></div>` : ''}
                `;
            }

            function printReceipt(transactionId) {
                console.log('Print receipt for transaction ID:', transactionId);
                
                if (!transactionId) {
                    showAlert('Cannot print receipt: Missing transaction ID', 'danger');
                    return;
                }
                
                // First, test if the route exists by making a fetch request
                const receiptUrl = `/cashier/api/pdf/cashier-receipt?transaction_id=${encodeURIComponent(transactionId)}`;
                console.log('Attempting to access receipt at:', receiptUrl);
                
                // Test the route first
                fetch(receiptUrl, {
                    method: 'HEAD',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    console.log('Receipt route response status:', response.status);
                    if (response.ok) {
                        // Route is accessible, open in new window
                        const width = 800;
                        const height = 600;
                        const left = (screen.width / 2) - (width / 2);
                        const top = (screen.height / 2) - (height / 2);
                        const printWindow = window.open(receiptUrl, '_blank', `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`);
                        if (printWindow) {
                            showAlert('Receipt opened successfully!', 'success');
                        } else {
                            showAlert('Please allow pop-ups to print receipts', 'warning');
                        }
                    } else if (response.status === 404) {
                        // Try the cashier-specific route as fallback
                        console.log('Trying cashier-specific route...');
                        const cashierReceiptUrl = `/cashier/api/pdf/cashier-receipt?transaction_id=${encodeURIComponent(transactionId)}`;
                        const printWindow = window.open(cashierReceiptUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
                        if (!printWindow) {
                            showAlert('Receipt not found. The transaction may not exist or may not be confirmed.', 'danger');
                        }
                    } else if (response.status === 403) {
                        showAlert('Access denied. You may not have permission to view this receipt.', 'danger');
                    } else {
                        showAlert(`Error accessing receipt (Status: ${response.status})`, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error accessing receipt:', error);
                    showAlert('Network error while trying to access receipt', 'danger');
                });
            }

            // function printReceiptFromModal() {
            //     if (currentTransactionId) {
            //         printReceipt(currentTransactionId);
            //     } else if (currentPaymentId) {
            //         // Fallback: if no transaction ID, show error
            //         showAlert('Cannot print receipt: Missing transaction ID. Please refresh and try again.', 'warning');
            //     }
            // }

            window.exportArchives = function() {
                console.log('Export payment archives');
                alert('Export functionality will be implemented');
            };

            // Export functions to global scope
            window.viewPaymentDetails = viewPaymentDetails;
            window.printReceipt = printReceipt;
            // window.printReceiptFromModal = printReceiptFromModal;

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