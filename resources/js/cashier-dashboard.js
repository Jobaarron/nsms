/**
 * Cashier Dashboard JavaScript
 * Handles all data visualization and chart functionality for the cashier dashboard
 */

// Global variables for chart data (will be populated from backend)
window.cashierDashboardData = {
    paymentMethodData: null,
    monthlyRevenue: null,
    dailyRevenue: null
};

/**
 * Initialize all dashboard charts
 */
function initializeCashierDashboard() {
    // Wait for DOM to be fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });
    } else {
        initializeCharts();
    }
}

/**
 * Initialize all charts with data
 */
function initializeCharts() {
    try {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            // console.error('Chart.js is not loaded. Please include Chart.js before this script.');
            console.log('Hello World!');
            return;
        }

        // Initialize charts if data is available
        if (window.cashierDashboardData.paymentMethodData) {
            initializePaymentMethodChart();
        }
        
        if (window.cashierDashboardData.monthlyRevenue) {
            initializeRevenueTrendChart();
        }
        
        if (window.cashierDashboardData.dailyRevenue) {
            initializeDailyRevenueChart();
        }

        console.log('Cashier dashboard charts initialized successfully');
    } catch (error) {
        console.error('Error initializing cashier dashboard charts:', error);
    }
}

/**
 * Initialize Payment Method Distribution Chart (Doughnut)
 */
function initializePaymentMethodChart() {
    const ctx = document.getElementById('paymentMethodChart');
    if (!ctx) {
        console.warn('Payment method chart canvas not found');
        return;
    }

    const paymentMethodData = window.cashierDashboardData.paymentMethodData;
    
    new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Full Payment', 'Quarterly', 'Monthly'],
            datasets: [{
                data: [
                    paymentMethodData.full.amount,
                    paymentMethodData.quarterly.amount,
                    paymentMethodData.monthly.amount
                ],
                backgroundColor: [
                    'rgba(13, 110, 253, 0.8)',
                    'rgba(13, 202, 240, 0.8)',
                    'rgba(255, 193, 7, 0.8)'
                ],
                borderColor: [
                    'rgba(13, 110, 253, 1)',
                    'rgba(13, 202, 240, 1)',
                    'rgba(255, 193, 7, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = new Intl.NumberFormat('en-PH', {
                                style: 'currency',
                                currency: 'PHP'
                            }).format(context.parsed);
                            return label + ': ' + value;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize Revenue Trend Chart (Line)
 */
function initializeRevenueTrendChart() {
    const ctx = document.getElementById('revenueTrendChart');
    if (!ctx) {
        console.warn('Revenue trend chart canvas not found');
        return;
    }

    const monthlyRevenue = window.cashierDashboardData.monthlyRevenue;
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const revenueLabels = monthlyRevenue.map(item => monthNames[item.month - 1] + ' ' + item.year);
    const revenueData = monthlyRevenue.map(item => item.total);
    
    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Monthly Revenue',
                data: revenueData,
                borderColor: 'rgba(25, 135, 84, 1)',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(25, 135, 84, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ' + new Intl.NumberFormat('en-PH', {
                                style: 'currency',
                                currency: 'PHP'
                            }).format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + new Intl.NumberFormat().format(value);
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize Daily Revenue Chart (Bar)
 */
function initializeDailyRevenueChart() {
    const ctx = document.getElementById('dailyRevenueChart');
    if (!ctx) {
        console.warn('Daily revenue chart canvas not found');
        return;
    }

    const dailyRevenue = window.cashierDashboardData.dailyRevenue;
    const dailyLabels = Array.from({length: new Date().getDate()}, (_, i) => i + 1);
    const dailyData = dailyLabels.map(day => {
        const dayData = dailyRevenue.find(item => item.day === day);
        return dayData ? dayData.total : 0;
    });
    
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Daily Revenue',
                data: dailyData,
                backgroundColor: 'rgba(13, 202, 240, 0.8)',
                borderColor: 'rgba(13, 202, 240, 1)',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ' + new Intl.NumberFormat('en-PH', {
                                style: 'currency',
                                currency: 'PHP'
                            }).format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + new Intl.NumberFormat().format(value);
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Day of Month'
                    }
                }
            }
        }
    });
}

/**
 * Set chart data from backend
 * @param {Object} data - Chart data from backend
 */
function setCashierDashboardData(data) {
    window.cashierDashboardData = {
        paymentMethodData: data.paymentMethodData || null,
        monthlyRevenue: data.monthlyRevenue || null,
        dailyRevenue: data.dailyRevenue || null
    };
    
    // Re-initialize charts with new data
    initializeCharts();
}

/**
 * View payment details (placeholder function)
 * @param {number} paymentId - Payment ID to view
 */
function viewPaymentDetails(paymentId) {
    // Implementation for viewing payment details
    console.log('View payment details for ID:', paymentId);
    // This would open a modal with payment details
    // TODO: Implement payment details modal
}

/**
 * Set date range for reports filtering
 * @param {string} range - Range type (today, week, month, year)
 */
function setDateRange(range) {
    const today = new Date();
    let fromDate, toDate;

    switch(range) {
        case 'today':
            fromDate = toDate = today.toISOString().split('T')[0];
            break;
        case 'week':
            const weekStart = new Date(today.setDate(today.getDate() - today.getDay()));
            fromDate = weekStart.toISOString().split('T')[0];
            toDate = new Date().toISOString().split('T')[0];
            break;
        case 'month':
            fromDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            toDate = new Date().toISOString().split('T')[0];
            break;
        case 'year':
            fromDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
            toDate = new Date().toISOString().split('T')[0];
            break;
    }

    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    if (dateFromInput) dateFromInput.value = fromDate;
    if (dateToInput) dateToInput.value = toDate;
}

/**
 * Export payment breakdown data
 */
function exportPaymentBreakdown() {
    console.log('Export payment breakdown');
    
    // Show loading state
    const exportBtn = document.querySelector('button[onclick="exportPaymentBreakdown()"]');
    if (exportBtn) {
        const originalText = exportBtn.innerHTML;
        exportBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Exporting...';
        exportBtn.disabled = true;
        
        // Restore button after export
        setTimeout(() => {
            exportBtn.innerHTML = originalText;
            exportBtn.disabled = false;
        }, 2000);
    }
    
    // Get current dashboard data
    const dashboardData = window.cashierDashboardData || {};
    
    // Create payment breakdown CSV
    const headers = ['Payment Method', 'Total Payments', 'Total Amount', 'Percentage'];
    const csvContent = [
        headers.join(','),
        // Add payment method breakdown
        `"Full Payment","${dashboardData.full_payments || 0}","₱${formatCurrency(dashboardData.full_amount || 0)}","${((dashboardData.full_amount || 0) / (dashboardData.total_amount || 1) * 100).toFixed(1)}%"`,
        `"Quarterly Payment","${dashboardData.quarterly_payments || 0}","₱${formatCurrency(dashboardData.quarterly_amount || 0)}","${((dashboardData.quarterly_amount || 0) / (dashboardData.total_amount || 1) * 100).toFixed(1)}%"`,
        `"Monthly Payment","${dashboardData.monthly_payments || 0}","₱${formatCurrency(dashboardData.monthly_amount || 0)}","${((dashboardData.monthly_amount || 0) / (dashboardData.total_amount || 1) * 100).toFixed(1)}%"`,
        '',
        'Status Breakdown',
        `"Confirmed Payments","${dashboardData.confirmed_payments || 0}","₱${formatCurrency(dashboardData.confirmed_amount || 0)}",""`,
        `"Pending Payments","${dashboardData.pending_payments || 0}","₱${formatCurrency(dashboardData.pending_amount || 0)}",""`,
        '',
        'Summary',
        `"Total Payments","${dashboardData.total_payments || 0}","₱${formatCurrency(dashboardData.total_amount || 0)}","100%"`,
        `"Export Date","${new Date().toLocaleDateString()}","",""`
    ].join('\n');
    
    // Create and download file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `payment_breakdown_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Show success message
    showAlert('Payment breakdown exported successfully', 'success');
}

/**
 * Format currency for display
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount || 0);
}

/**
 * Show alert message
 */
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

/**
 * Export all reports
 */
function exportAllReports() {
    console.log('Export all reports');
    alert('Export functionality will be implemented');
}

// Global function assignments for onclick handlers
window.viewPaymentDetails = viewPaymentDetails;
window.setCashierDashboardData = setCashierDashboardData;
window.initializeCashierDashboard = initializeCashierDashboard;
window.setDateRange = setDateRange;
window.exportPaymentBreakdown = exportPaymentBreakdown;
window.exportAllReports = exportAllReports;

// Auto-initialize when script loads
initializeCashierDashboard();
