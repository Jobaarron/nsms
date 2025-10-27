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
            console.error('Chart.js is not loaded. Please include Chart.js before this script.');
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
            maintainAspectRatio: true,
            aspectRatio: 1.5,
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
            maintainAspectRatio: true,
            aspectRatio: 2,
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
            maintainAspectRatio: true,
            aspectRatio: 3,
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

// Global function assignments for onclick handlers
window.viewPaymentDetails = viewPaymentDetails;
window.setCashierDashboardData = setCashierDashboardData;
window.initializeCashierDashboard = initializeCashierDashboard;

// Auto-initialize when script loads
initializeCashierDashboard();
