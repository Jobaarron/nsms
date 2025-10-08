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
                    <h2 class="section-title mb-1">Cashier Dashboard</h2>
                    <p class="text-muted mb-0">Welcome back, {{ $cashier->full_name }}</p>
                </div>
                {{-- <div class="text-end">
                    <small class="text-muted">Employee ID: <strong>{{ $cashier->employee_id }}</strong></small><br>
                    <small class="text-muted">Department: <strong>{{ $cashier->department }}</strong></small>
                </div> --}}
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="ri-time-line fs-2 text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="fw-bold fs-4 mb-0">{{ $pendingPayments }}</h4>
                        <small class="text-muted">Pending Payments</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="ri-alarm-warning-line fs-2 text-danger"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="fw-bold fs-4 mb-0">{{ $duePayments }}</h4>
                        <small class="text-muted">Due Payments</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="ri-check-double-line fs-2 text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="fw-bold fs-4 mb-0">{{ $completedPayments }}</h4>
                        <small class="text-muted">Completed Payments</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="ri-calendar-check-line fs-2 text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="fw-bold fs-4 mb-0">{{ $todayPayments }}</h4>
                        <small class="text-muted">Today's Confirmations</small>
                    </div>
                </div>
            </div>
        </div> -->
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-flashlight-line me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('cashier.pending-payments') }}" class="btn btn-outline-warning w-100">
                                <i class="ri-time-line me-2"></i>Process Pending
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('cashier.due-payments') }}" class="btn btn-outline-danger w-100">
                                <i class="ri-alarm-warning-line me-2"></i>Review Due
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('cashier.payment-history') }}" class="btn btn-outline-primary w-100">
                                <i class="ri-history-line me-2"></i>View History
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('cashier.reports') }}" class="btn disabled btn-outline-success w-100">
                                <i class="ri-bar-chart-line me-2"></i>Generate Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Analytics Dashboard -->
    <div class="row mb-4">
        <!-- Payment Method Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-pie-chart-line me-2"></i>Payment Method Distribution
                    </h5>
                    <small class="text-muted">Total revenue by payment schedule type</small>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h4 class="fw-bold text-primary mb-1">₱{{ number_format($paymentMethodData['full']['amount'], 0) }}</h4>
                                <small class="text-muted">Full Payment</small>
                                <div class="small text-muted">{{ $paymentMethodData['full']['count'] }} transactions</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <h4 class="fw-bold text-info mb-1">₱{{ number_format($paymentMethodData['quarterly']['amount'], 0) }}</h4>
                                <small class="text-muted">Quarterly</small>
                                <div class="small text-muted">{{ $paymentMethodData['quarterly']['count'] }} schedules</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-warning bg-opacity-10 rounded">
                                <h4 class="fw-bold text-warning mb-1">₱{{ number_format($paymentMethodData['monthly']['amount'], 0) }}</h4>
                                <small class="text-muted">Monthly</small>
                                <div class="small text-muted">{{ $paymentMethodData['monthly']['count'] }} schedules</div>
                            </div>
                        </div>
                    </div>
                    <canvas id="paymentMethodChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Revenue Trend -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-line-chart-line me-2"></i>Revenue Trend
                    </h5>
                    <small class="text-muted">Monthly revenue for the last 6 months</small>
                </div>
                <div class="card-body">
                    <canvas id="revenueTrendChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Analytics Row -->
    <div class="row mb-4">
        <!-- Daily Revenue (Current Month) -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-calendar-line me-2"></i>Daily Revenue - {{ now()->format('F Y') }}
                    </h5>
                    <small class="text-muted">Daily payment confirmations this month</small>
                </div>
                <div class="card-body">
                    <canvas id="dailyRevenueChart" height="150"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Fee Categories -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-funds-line me-2"></i>Top Fee Categories
                    </h5>
                    <small class="text-muted">Highest revenue generators</small>
                </div>
                <div class="card-body">
                    @if($topFeeCategories->count() > 0)
                        @foreach($topFeeCategories as $index => $category)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-{{ ['primary', 'success', 'info', 'warning', 'secondary'][$index] }} bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="ri-money-dollar-circle-line text-{{ ['primary', 'success', 'info', 'warning', 'secondary'][$index] }}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $category->fee_category }}</div>
                                        <small class="text-muted">{{ $category->payment_count }} payments</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success">₱{{ number_format($category->total_amount, 0) }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="ri-funds-line fs-1 text-muted mb-3"></i>
                            <p class="text-muted">No fee data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Insights -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-lightbulb-line me-2"></i>Key Insights
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-flex mb-2">
                                <i class="ri-money-dollar-circle-line fs-2 text-success"></i>
                            </div>
                            <h4 class="fw-bold text-success">₱{{ number_format($paymentMethodData['full']['amount'] + $paymentMethodData['quarterly']['amount'] + $paymentMethodData['monthly']['amount'], 0) }}</h4>
                            <small class="text-muted">Total Revenue</small>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-flex mb-2">
                                <i class="ri-user-line fs-2 text-primary"></i>
                            </div>
                            <h4 class="fw-bold text-primary">{{ $paymentMethodData['full']['count'] + $paymentMethodData['quarterly']['count'] + $paymentMethodData['monthly']['count'] }}</h4>
                            <small class="text-muted">Total Transactions</small>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3 d-inline-flex mb-2">
                                <i class="ri-calendar-check-line fs-2 text-info"></i>
                            </div>
                            <h4 class="fw-bold text-info">{{ $todayPayments }}</h4>
                            <small class="text-muted">Today's Confirmations</small>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3 d-inline-flex mb-2">
                                <i class="ri-time-line fs-2 text-warning"></i>
                            </div>
                            <h4 class="fw-bold text-warning">{{ $pendingPayments }}</h4>
                            <small class="text-muted">Pending Payments</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Payment Method Distribution Chart (Doughnut)
            const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
            const paymentMethodData = @json($paymentMethodData);
            
            new Chart(paymentMethodCtx, {
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

            // Revenue Trend Chart (Line)
            const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
            const monthlyRevenue = @json($monthlyRevenue);
            
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const revenueLabels = monthlyRevenue.map(item => monthNames[item.month - 1] + ' ' + item.year);
            const revenueData = monthlyRevenue.map(item => item.total);
            
            new Chart(revenueTrendCtx, {
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

            // Daily Revenue Chart (Bar)
            const dailyRevenueCtx = document.getElementById('dailyRevenueChart').getContext('2d');
            const dailyRevenue = @json($dailyRevenue);
            
            const dailyLabels = Array.from({length: new Date().getDate()}, (_, i) => i + 1);
            const dailyData = dailyLabels.map(day => {
                const dayData = dailyRevenue.find(item => item.day === day);
                return dayData ? dayData.total : 0;
            });
            
            new Chart(dailyRevenueCtx, {
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

            function viewPaymentDetails(paymentId) {
                // Implementation for viewing payment details
                console.log('View payment details for ID:', paymentId);
                // This would open a modal with payment details
            }
        </script>
    @endpush
</x-cashier-layout>
