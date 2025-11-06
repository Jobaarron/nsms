// Cashier Payment Schedules JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cashier payment management
    initializeCashierPayments();
    
    // Setup real-time updates
    setupRealTimeUpdates();
    
    // Setup filters and search
    setupFiltersAndSearch();
});

function initializeCashierPayments() {
    console.log('Cashier Payment Schedules initialized');
    
    // Load initial data
    loadPaymentStatistics();
    loadPaymentSchedules();
    
    // Setup action handlers
    setupActionHandlers();
    
    // Setup real-time updates
    setupRealTimeUpdates();
}

function setupRealTimeUpdates() {
    // Real-time updates every 30 seconds for pending payments
    setInterval(() => {
        loadPaymentStatistics();
        // Only reload table if no modals are open to avoid disrupting user interaction
        if (!document.querySelector('.modal.show')) {
            loadPaymentSchedules();
        }
    }, 30000);
    
    console.log('Real-time updates enabled for pending payments');
}

function loadPaymentStatistics() {
    fetch('/cashier/api/payment-statistics', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatisticsDisplay(data.statistics);
        }
    })
    .catch(error => {
        console.error('Error loading statistics:', error);
    });
}

function updateStatisticsDisplay(stats) {
    // Update dashboard cards
    const elements = {
        'total-scheduled': stats.total_scheduled,
        'pending-payments': stats.pending_payments,
        'confirmed-payments': stats.confirmed_payments,
        'due-payments': stats.due_payments,
        'total-amount-scheduled': formatCurrency(stats.total_amount_scheduled),
        'total-amount-collected': formatCurrency(stats.total_amount_collected)
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    });
}

function loadPaymentSchedules(filters = {}) {
    const params = new URLSearchParams(filters);
    
    fetch(`/cashier/api/payment-schedules?${params}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updatePaymentSchedulesTable(data.payments);
        }
    })
    .catch(error => {
        console.error('Error loading payment schedules:', error);
    });
}

function updatePaymentSchedulesTable(payments) {
    const tableBody = document.querySelector('#payment-schedules-table tbody');
    if (!tableBody) return;
    
    if (payments.data && payments.data.length > 0) {
        tableBody.innerHTML = payments.data.map(payment => createPaymentRow(payment)).join('');
        updatePagination(payments);
    } else {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="text-muted">
                        <i class="ri-inbox-line fs-1 mb-2"></i>
                        <p>No payment schedules found</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

function createPaymentRow(payment) {
    const student = payment.payable;
    const statusBadge = getStatusBadge(payment.confirmation_status);
    const priorityBadge = getPriorityBadge(payment.scheduled_date);
    const isDue = isDuePayment(payment.scheduled_date);
    const rowClass = isDue ? 'table-danger' : '';
    
    // Debug: Log payment data to see structure (removed for cleaner console)
    
    // Format payment method display
    const paymentMethodDisplay = getPaymentMethodDisplay(payment.payment_method, payment.installment_count);
    
    // Format date range for installment payments
    const dateDisplay = getDateDisplay(payment);
    
    // Get student identifier - try different possible fields
    const studentId = student?.student_id || student?.id || payment.payable_id;
    
    return `
        <tr class="${rowClass}">
            <td>${priorityBadge}</td>
            <td>
                <span class="fw-bold">${payment.transaction_id}</span><br>
                <small class="text-muted">${paymentMethodDisplay}</small>
            </td>
            <td>
                <div class="fw-semibold">${studentId}</div>
                <small class="text-muted">${student?.first_name || 'N/A'} ${student?.last_name || ''}</small>
            </td>
            <td>
                <span class="fw-bold">₱${formatNumber(payment.total_amount || payment.amount)}</span><br>
                <small class="text-muted">${getInstallmentInfo(payment)}</small>
            </td>
            <td>
                <div>${dateDisplay}</div>
                <small class="text-muted">${getTimeAgo(payment.scheduled_date)}</small>
            </td>
            <td>${statusBadge}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewPaymentScheduleDetails('${studentId}', '${payment.payment_method}')" title="View Schedule">
                        <i class="ri-eye-line"></i>
                    </button>
                    ${payment.confirmation_status === 'pending' ? `
                        <button class="btn btn-outline-success" onclick="approvePaymentSchedule('${studentId}', '${payment.payment_method}')" title="Approve Schedule">
                            <i class="ri-check-line"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="rejectPaymentSchedule('${studentId}', '${payment.payment_method}')" title="Reject Schedule">
                            <i class="ri-close-line"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `;
}

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

function getDateDisplay(payment) {
    if (payment.payment_method === 'full') {
        return formatDate(payment.scheduled_date);
    } else {
        return `${formatDate(payment.first_due_date)} - ${formatDate(payment.last_due_date)}`;
    }
}

function getInstallmentInfo(payment) {
    if (payment.payment_method === 'full') {
        return 'One-time payment';
    } else {
        const perInstallment = payment.total_amount / payment.installment_count;
        return `₱${formatNumber(perInstallment)} × ${payment.installment_count}`;
    }
}

function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">Not yet paid</span>',
        'confirmed': '<span class="badge bg-success">Paid</span>',
        'rejected': '<span class="badge bg-warning">Not yet paid</span>' // Declined reverts to "Not yet paid"
    };
    return badges[status] || '<span class="badge bg-warning">Not yet paid</span>';
}

function getPriorityBadge(scheduledDate) {
    const today = new Date();
    const scheduled = new Date(scheduledDate);
    const diffDays = Math.ceil((scheduled - today) / (1000 * 60 * 60 * 24));
    
    if (diffDays < 0) {
        const overdueDays = Math.abs(diffDays);
        if (overdueDays > 7) {
            return '<span class="badge bg-danger">Critical</span>';
        } else if (overdueDays > 3) {
            return '<span class="badge bg-warning">High</span>';
        } else {
            return '<span class="badge bg-info">Medium</span>';
        }
    } else if (diffDays === 0) {
        return '<span class="badge bg-warning">Due Today</span>';
    } else {
        return '<span class="badge bg-secondary">Scheduled</span>';
    }
}

function isDuePayment(scheduledDate) {
    const today = new Date();
    const scheduled = new Date(scheduledDate);
    return scheduled <= today;
}

function setupFiltersAndSearch() {
    // Setup due status filter
    const dueStatusFilter = document.querySelector('select[name="due_status"]');
    if (dueStatusFilter) {
        dueStatusFilter.addEventListener('change', function() {
            applyFilters();
        });
    }
    
    // Setup other filters
    const statusFilter = document.querySelector('select[name="status"]');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            applyFilters();
        });
    }
    
    // Setup search
    const searchInput = document.getElementById('payment-search');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            applyFilters();
        }, 300));
    }
}

function applyFilters() {
    const filters = {
        status: document.querySelector('select[name="status"]')?.value || '',
        due_status: document.querySelector('select[name="due_status"]')?.value || '',
        search: document.getElementById('payment-search')?.value || ''
    };
    
    loadPaymentSchedules(filters);
    updateBadgeCounts(filters);
}

function updateBadgeCounts(filters) {
    const pendingBadge = document.getElementById('pending-count');
    const dueBadge = document.getElementById('due-count');
    
    if (filters.due_status === 'due') {
        pendingBadge.style.display = 'none';
        dueBadge.style.display = 'inline-block';
    } else if (filters.due_status === 'not_due') {
        pendingBadge.style.display = 'inline-block';
        dueBadge.style.display = 'none';
    } else {
        pendingBadge.style.display = 'inline-block';
        dueBadge.style.display = 'none';
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function processPayment(paymentId, action) {
    const actionText = action === 'confirm' ? 'confirm' : 'reject';
    
    if (!confirm(`Are you sure you want to ${actionText} this payment?`)) {
        return;
    }
    
    const notes = prompt(`Please enter notes for this ${actionText}ion (optional):`);
    
    const requestData = {
        action: action,
        cashier_notes: notes || ''
    };
    
    if (action === 'confirm') {
        const amountReceived = prompt('Enter amount received (leave empty for scheduled amount):');
        if (amountReceived && !isNaN(amountReceived)) {
            requestData.amount_received = parseFloat(amountReceived);
        }
    }
    
    fetch(`/cashier/api/payment-schedules/${paymentId}/process`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadPaymentSchedules(); // Reload the table
            loadPaymentStatistics(); // Update statistics
        } else {
            showAlert(data.message || `Failed to ${actionText} payment.`, 'danger');
        }
    })
    .catch(error => {
        console.error('Error processing payment:', error);
        showAlert('An error occurred while processing the payment.', 'danger');
    });
}

// Process individual payment installment (for partial payments)
function processIndividualPayment(paymentId, action) {
    const actionText = action === 'approve' ? 'approve' : 'reject';
    if (action === 'approve') {
        if (!confirm('Are you sure you want to approve this individual payment?')) {
            return;
        }
        const requestData = {
            action: 'approve',
            reason: ''
        };
        fetch(`/cashier/api/payment-schedules/individual/${paymentId}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show PDF modal after approval
                if (typeof showPdfModal === 'function') {
                    showPdfModal(data.transaction_id || paymentId);
                } else if (window.showPdfModal) {
                    window.showPdfModal(data.transaction_id || paymentId);
                }
                showAlert(data.message, 'success');
                updatePaymentRowStatus(paymentId, data.payment.status);
                loadPaymentSchedules();
                loadPaymentStatistics();
            } else {
                showAlert(data.message || `Failed to approve payment.`, 'danger');
            }
        })
        .catch(error => {
            console.error('Error processing individual payment:', error);
            showAlert(`Error processing payment: ${error.message}`, 'danger');
        });
    } else {
        // Reject flow (keep prompt for now)
        let reason = prompt('Please enter reason for rejection:');
        if (!reason) {
            showAlert('Reason is required for rejection.', 'warning');
            return;
        }
        const requestData = {
            action: 'reject',
            reason: reason
        };
        fetch(`/cashier/api/payment-schedules/individual/${paymentId}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                updatePaymentRowStatus(paymentId, data.payment.status);
                loadPaymentSchedules();
                loadPaymentStatistics();
            } else {
                showAlert(data.message || `Failed to reject payment.`, 'danger');
            }
        })
        .catch(error => {
            console.error('Error processing individual payment:', error);
            showAlert(`Error processing payment: ${error.message}`, 'danger');
        });
    }
}

function updatePaymentRowStatus(paymentId, status) {
    // Find and update the payment row in the modal
    const paymentRow = document.querySelector(`[data-payment-id="${paymentId}"]`);
    if (paymentRow) {
        const statusCell = paymentRow.querySelector('.payment-status');
        const actionCell = paymentRow.querySelector('.payment-actions');
        
        if (statusCell) {
            if (status === 'confirmed') {
                statusCell.innerHTML = '<span class="badge bg-success">Paid</span>';
            } else if (status === 'rejected') {
                statusCell.innerHTML = '<span class="badge bg-danger">Rejected</span>';
            }
        }
        
        if (actionCell && status !== 'pending') {
            actionCell.innerHTML = '<span class="text-muted">Processed</span>';
        }
    }
}

function viewPaymentDetails(paymentId) {
    // Fetch payment details and show in modal
    fetch(`/cashier/api/payment-schedules/${paymentId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayPaymentDetailsModal(data.payment);
        } else {
            showAlert('Failed to load payment details.', 'danger');
        }
    })
    .catch(error => {
        console.error('Error loading payment details:', error);
        showAlert('An error occurred while loading payment details.', 'danger');
    });
}

function displayPaymentDetailsModal(payment) {
    // Find or create modal
    let modal = document.getElementById('paymentDetailsModal');
    if (!modal) {
        // Create modal if it doesn't exist
        modal = createPaymentDetailsModal();
        document.body.appendChild(modal);
    }
    
    // Populate modal content
    const content = document.getElementById('paymentDetailsContent');
    if (content) {
        const student = payment.payable;
        const daysOverdue = calculateDaysOverdue(payment.scheduled_date);
        const priorityClass = daysOverdue > 7 ? 'danger' : (daysOverdue > 3 ? 'warning' : 'info');
        
        content.innerHTML = `
            <div class="alert alert-${priorityClass}">
                <i class="ri-alarm-warning-line me-2"></i>
                <strong>Priority:</strong> ${daysOverdue > 7 ? 'Critical' : (daysOverdue > 3 ? 'High' : 'Medium')} 
                ${daysOverdue > 0 ? `(${daysOverdue} days overdue)` : daysOverdue === 0 ? '(Due today)' : `(Due in ${Math.abs(daysOverdue)} days)`}
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6>Student Information</h6>
                    <p><strong>Name:</strong> ${student.first_name} ${student.last_name}</p>
                    <p><strong>Student ID:</strong> ${student.student_id}</p>
                    <p><strong>Grade Level:</strong> ${student.grade_level}</p>
                </div>
                <div class="col-md-6">
                    <h6>Payment Details</h6>
                    <p><strong>Transaction ID:</strong> ${payment.transaction_id}</p>
                    <p><strong>Amount:</strong> ₱${formatNumber(payment.amount)}</p>
                    <p><strong>Payment Method:</strong> ${payment.payment_method.replace('_', ' ').toUpperCase()}</p>
                    <p><strong>Payment Mode:</strong> ${payment.payment_mode || 'N/A'}</p>
                    <p><strong>Period:</strong> ${payment.period_name}</p>
                    <p><strong>Scheduled Date:</strong> ${formatDate(payment.scheduled_date)}</p>
                    ${payment.reference_number ? `<p><strong>Reference Number:</strong> ${payment.reference_number}</p>` : ''}
                    <p><strong>Status:</strong> ${getStatusBadge(payment.confirmation_status)}</p>
                </div>
            </div>
            ${payment.notes ? `<div class="mt-3"><h6>Notes</h6><p>${payment.notes}</p></div>` : ''}
        `;
    }
    
    // Show modal - check if Bootstrap is available
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } else {
        // Fallback: add modal classes manually
        modal.classList.add('show');
        modal.style.display = 'block';
        document.body.classList.add('modal-open');
        
        // Add backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
        
        // Close modal functionality
        const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                modal.classList.remove('show');
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
                document.body.removeChild(backdrop);
            });
        });
    }
}

function createPaymentDetailsModal() {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'paymentDetailsModal';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="paymentDetailsContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    return modal;
}

function calculateDaysOverdue(scheduledDate) {
    const today = new Date();
    const scheduled = new Date(scheduledDate);
    const diffTime = today - scheduled;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
}


function setupActionHandlers() {
    // Setup bulk actions if needed
    const bulkActionBtn = document.getElementById('bulk-action-btn');
    if (bulkActionBtn) {
        bulkActionBtn.addEventListener('click', handleBulkActions);
    }
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount || 0);
}

function formatNumber(number) {
    return new Intl.NumberFormat().format(number || 0);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function getTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Tomorrow';
    if (diffDays < 7) return `In ${diffDays} days`;
    if (diffDays < 30) return `In ${Math.ceil(diffDays / 7)} weeks`;
    return `In ${Math.ceil(diffDays / 30)} months`;
}

// New functions for handling payment schedules - expose to global scope
window.viewPaymentScheduleDetails = function(studentId, paymentMethod) {
    fetch(`/cashier/api/payment-schedules/student/${studentId}/${paymentMethod}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayPaymentScheduleModal(data.schedule);
        }
    })
    .catch(error => {
        console.error('Error loading payment schedule:', error);
    });
};

window.displayPaymentScheduleModal = function(schedule) {
    // Create a modal to display the payment schedule details
    const modalHtml = `
        <div class="modal fade" id="paymentScheduleModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Payment Schedule Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Student Information</h6>
                                <p><strong>Name:</strong> ${schedule.student.first_name} ${schedule.student.last_name}</p>
                                <p><strong>Student ID:</strong> ${schedule.student.student_id}</p>
                                <p><strong>Email:</strong> ${schedule.student.email}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Payment Schedule</h6>
                                <p><strong>Payment Method:</strong> ${schedule.payment_method.charAt(0).toUpperCase() + schedule.payment_method.slice(1)}</p>
                                <p><strong>Total Amount:</strong> ₱${formatNumber(schedule.total_amount)}</p>
                                <p><strong>Installments:</strong> ${schedule.installment_count}</p>
                                <p><strong>Status:</strong> <span class="badge bg-${schedule.status === 'confirmed' ? 'success' : 'warning'}">${schedule.status === 'confirmed' ? 'Paid' : 'Not yet paid'}</span></p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6>Payment Breakdown</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Period</th>
                                            <th>Amount</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${schedule.payments.map((payment, index) => `
                                            <tr data-payment-id="${payment.id}">
                                                <td>${payment.period_name}</td>
                                                <td>₱${formatNumber(payment.amount)}</td>
                                                <td>${formatDate(payment.scheduled_date)}</td>
                                                <td class="payment-status">
                                                    ${payment.confirmation_status === 'confirmed' ? 
                                                        '<span class="badge bg-success">Paid</span>' : 
                                                        payment.confirmation_status === 'rejected' ? 
                                                        '<span class="badge bg-danger">Rejected</span>' : 
                                                        '<span class="badge bg-warning">Not yet paid</span>'
                                                    }
                                                </td>
                                                <td class="payment-actions">
                                                    ${(payment.confirmation_status === 'pending' || !payment.confirmation_status) ? `
                                                        ${index === 0 || (schedule.payments[index - 1] && schedule.payments[index - 1].confirmation_status === 'confirmed') ? `
                                                            <button type="button" class="btn btn-success btn-sm me-1" 
                                                                onclick="processIndividualPayment(${payment.id}, 'approve')" 
                                                                title="Approve this payment">
                                                                <i class="ri-check-line"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm" 
                                                                onclick="processIndividualPayment(${payment.id}, 'reject')" 
                                                                title="Reject this payment">
                                                                <i class="ri-close-line"></i>
                                                            </button>
                                                        ` : `
                                                            <span class="text-muted small">Pay previous first</span>
                                                        `}
                                                    ` : `
                                                        <span class="text-muted">Processed</span>
                                                    `}
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <div class="text-muted small">
                            <i class="ri-information-line me-1"></i>
                            Approve payments individually. First quarter must be approved before others.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('paymentScheduleModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modalElement = document.getElementById('paymentScheduleModal');
    if (typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    } else {
        // Fallback for when Bootstrap is not available in global scope
        modalElement.classList.add('show');
        modalElement.style.display = 'block';
        modalElement.setAttribute('aria-modal', 'true');
        modalElement.setAttribute('role', 'dialog');
        
        // Add backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
        
        // Add close functionality
        const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"]');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                modalElement.classList.remove('show');
                modalElement.style.display = 'none';
                modalElement.removeAttribute('aria-modal');
                modalElement.removeAttribute('role');
                document.body.removeChild(backdrop);
            });
        });
    }
};

window.approvePaymentSchedule = function(studentId, paymentMethod) {
    if (confirm('Are you sure you want to approve this entire payment schedule?')) {
        // Call the processPaymentSchedule and on success, show the PDF modal
        processPaymentSchedule(studentId, paymentMethod, 'approve', null, function(paymentId) {
            if (paymentId) {
                showPdfModal(paymentId);
            }
        });
    }
};

window.rejectPaymentSchedule = function(studentId, paymentMethod) {
    const reason = prompt('Please provide a reason for rejecting this payment schedule:');
    if (reason) {
        processPaymentSchedule(studentId, paymentMethod, 'reject', reason);
    }
};

function processPaymentSchedule(studentId, paymentMethod, action, reason = null, onSuccess = null) {
    console.log('Processing payment schedule:', {
        studentId: studentId,
        paymentMethod: paymentMethod,
        action: action,
        reason: reason
    });
    const url = `/cashier/api/payment-schedules/student/${studentId}/${paymentMethod}/process`;
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: action,
            reason: reason
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => { throw new Error(text); });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            loadPaymentSchedules();
            if (typeof onSuccess === 'function') {
                // Try to get the transaction_id from the response if available
                let paymentId = null;
                if (data.payment && data.payment.transaction_id) {
                    paymentId = data.payment.transaction_id;
                } else if (data.transaction_id) {
                    paymentId = data.transaction_id;
                }
                onSuccess(paymentId);
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error processing payment schedule:', error);
        alert('An error occurred while processing the payment schedule.');
    });
}

// Show PDF modal for full schedule approval
function showPdfModal(paymentId) {
    // Remove existing modal if any
    var existingModal = document.getElementById('pdfReceiptModal');
    if (existingModal) existingModal.remove();

    let pdfUrl = `/cashier/api/pdf/cashier-receipt?transaction_id=${paymentId}`;
    const modalHtml = `
        <div class="modal fade" id="pdfReceiptModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cashier Receipt</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="height:80vh;position:relative;">
                        <div id="pdf-error" class="alert alert-danger my-3" style="display:none;"></div>
                        <iframe src="${pdfUrl}" style="width:100%;height:100%;border:none;display:none;" id="pdfReceiptFrame"></iframe>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="printPdfReceiptBtn">
                            <i class="ri-printer-line me-2"></i>Print
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById('pdfReceiptModal');
    const iframe = document.getElementById('pdfReceiptFrame');
    const errorDiv = document.getElementById('pdf-error');
    if (iframe) {
        iframe.onload = function() {
            iframe.style.display = 'block';
            if (errorDiv) errorDiv.style.display = 'none';
        };
        iframe.onerror = function() {
            iframe.style.display = 'none';
            if (errorDiv) {
                errorDiv.textContent = '404 PDF Not Found. Please check if the payment is approved and the transaction ID is valid.';
                errorDiv.style.display = 'block';
            }
        };
    }
    // Show modal (Bootstrap 5)
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        new bootstrap.Modal(modalElement).show();
    } else {
        modalElement.classList.add('show');
        modalElement.style.display = 'block';
        modalElement.setAttribute('aria-modal', 'true');
        modalElement.setAttribute('role', 'dialog');
        // Add backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }
    const printBtn = document.getElementById('printPdfReceiptBtn');
    if (printBtn) {
        printBtn.onclick = function() {
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } else {
                window.open(iframe.src, '_blank');
            }
        };
    }
}

function updatePagination(payments) {
    // Update pagination controls if they exist
    const paginationContainer = document.getElementById('pagination-container');
    if (paginationContainer && payments.links) {
        // Implementation depends on your pagination structure
        console.log('Update pagination:', payments);
    }
}

function showAlert(message, type = 'info') {
    // Create alert container if it doesn't exist
    let alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alert-container';
        alertContainer.className = 'position-fixed top-0 end-0 p-3';
        alertContainer.style.zIndex = '9999';
        document.body.appendChild(alertContainer);
    }
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="ri-information-line me-2"></i>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Missing functions that views are calling
function confirmPayment(paymentId) {
    processPayment(paymentId, 'confirm');
}

function rejectPayment(paymentId) {
    processPayment(paymentId, 'reject');
}

function printReceipt(paymentId) {
    // Open cashier receipt PDF in a new tab using transaction_id
    if (!paymentId) {
        alert('Invalid transaction ID');
        return;
    }
    // Try /cashier/api/pdf/cashier-receipt, fallback to /pdf/cashier-receipt if 404
    const url = `/cashier/api/pdf/cashier-receipt?transaction_id=${paymentId}`;
    const win = window.open(url, '_blank');
    if (win) {
        win.onerror = function() {
            win.location.href = `/pdf/cashier-receipt?transaction_id=${paymentId}`;
        };
    }
}

// Modal-based confirmation functions
let currentPaymentId = null;

function confirmFromModal() {
    if (currentPaymentId) {
        processPayment(currentPaymentId, 'confirm');
        // Close modal
        const modalElement = document.getElementById('confirmModal');
        if (typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
        } else {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
        }
    }
}

function rejectFromModal() {
    if (currentPaymentId) {
        processPayment(currentPaymentId, 'reject');
        // Close modal
        const modalElement = document.getElementById('rejectModal');
        if (typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
        } else {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
        }
    }
}

function processConfirmation() {
    if (currentPaymentId) {
        const amountReceived = document.getElementById('confirmAmountReceived')?.value;
        const notes = document.getElementById('confirmNotes')?.value;
        
        const requestData = {
            action: 'confirm',
            cashier_notes: notes || ''
        };
        
        if (amountReceived && !isNaN(amountReceived)) {
            requestData.amount_received = parseFloat(amountReceived);
        }
        
        fetch(`/cashier/api/payment-schedules/${currentPaymentId}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                loadPaymentSchedules();
                loadPaymentStatistics();
                // Close modal
                const modalElement = document.getElementById('confirmModal');
                if (typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) modal.hide();
                } else {
                    modalElement.style.display = 'none';
                    modalElement.classList.remove('show');
                }
            } else {
                showAlert(data.message || 'Failed to confirm payment.', 'danger');
            }
        })
        .catch(error => {
            console.error('Error confirming payment:', error);
            showAlert('An error occurred while confirming the payment.', 'danger');
        });
    }
}

function processRejection() {
    if (currentPaymentId) {
        const reason = document.getElementById('rejectReason')?.value;
        const notes = document.getElementById('rejectNotes')?.value;
        
        if (!reason || reason.trim() === '') {
            showAlert('Please provide a reason for rejection.', 'warning');
            return;
        }
        
        const requestData = {
            action: 'reject',
            cashier_notes: `${reason}${notes ? '. ' + notes : ''}`
        };
        
        fetch(`/cashier/api/payment-schedules/${currentPaymentId}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                loadPaymentSchedules();
                loadPaymentStatistics();
                // Close modal
                const modalElement = document.getElementById('rejectModal');
                if (typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) modal.hide();
                } else {
                    modalElement.style.display = 'none';
                    modalElement.classList.remove('show');
                }
            } else {
                showAlert(data.message || 'Failed to reject payment.', 'danger');
            }
        })
        .catch(error => {
            console.error('Error rejecting payment:', error);
            showAlert('An error occurred while rejecting the payment.', 'danger');
        });
    }
}

// Export functions for global access
window.loadPaymentSchedules = loadPaymentSchedules;
window.processPayment = processPayment;
window.viewPaymentDetails = viewPaymentDetails;
window.confirmPayment = confirmPayment;
window.rejectPayment = rejectPayment;
window.printReceipt = printReceipt;
window.confirmFromModal = confirmFromModal;
window.rejectFromModal = rejectFromModal;
window.processConfirmation = processConfirmation;
window.processRejection = processRejection;
window.processIndividualPayment = processIndividualPayment;

// Additional exports for payments page modal functions
window.confirmPaymentModal = confirmPayment;
window.rejectPaymentModal = rejectPayment;
window.viewPaymentDetailsModal = viewPaymentDetails;
window.processConfirmationModal = processConfirmation;
window.processRejectionModal = processRejection;
