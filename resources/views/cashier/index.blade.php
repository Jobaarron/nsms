<x-cashier-layout>
    @push('styles')
        @vite('resources/css/index_student.css')
        <style>
            /* Chart Container Constraints */
            .chart-container {
                position: relative !important;
                overflow: hidden !important;
                max-height: 400px !important;
            }
            
            .chart-container canvas {
                max-height: 100% !important;
                width: 100% !important;
                height: auto !important;
            }
            
            /* Prevent infinite chart expansion */
            #revenueTrendChart, #paymentMethodChart, #dailyRevenueChart {
                max-height: 350px !important;
            }
            
            /* Card body constraints for charts */
            .card-body .chart-container {
                margin: 0 !important;
                padding: 0 !important;
            }
        </style>
    @endpush
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
    <!-- <div class="row mb-4">
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
    </div> -->

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
                    <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                        <canvas id="paymentMethodChart"></canvas>
                    </div>
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
                    <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
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
                    <div class="chart-container" style="position: relative; height: 250px; width: 100%;">
                        <canvas id="dailyRevenueChart"></canvas>
                    </div>
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
            // Initialize dashboard with backend data
            document.addEventListener('DOMContentLoaded', function() {
                // Set chart data from backend
                if (typeof setCashierDashboardData === 'function') {
                    setCashierDashboardData({
                        paymentMethodData: @json($paymentMethodData),
                        monthlyRevenue: @json($monthlyRevenue),
                        dailyRevenue: @json($dailyRevenue)
                    });
                } else {
                    console.error('setCashierDashboardData function not found. Make sure cashier-dashboard.js is loaded.');
                }
            });
        </script>
    @endpush
</x-cashier-layout>
