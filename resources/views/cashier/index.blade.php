<x-cashier-layout>
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

    <!-- Key Insights & Statistics Cards - Side by Side -->
    <div class="row mb-3">
        <!-- Key Insights - Left Side -->
        <div class="col-lg-6">
            <div class="row">
                <div class="col-6 mb-3">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body py-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-2 d-inline-flex mb-2">
                                <i class="ri-money-dollar-circle-line fs-4 text-success"></i>
                            </div>
                            <h5 class="fw-bold text-success mb-1">₱{{ number_format($paymentMethodData['full']['amount'] + $paymentMethodData['quarterly']['amount'] + $paymentMethodData['monthly']['amount'], 0) }}</h5>
                            <small class="text-muted">Total Revenue</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body py-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-inline-flex mb-2">
                                <i class="ri-user-line fs-4 text-primary"></i>
                            </div>
                            <h5 class="fw-bold text-primary mb-1">{{ $paymentMethodData['full']['count'] + $paymentMethodData['quarterly']['count'] + $paymentMethodData['monthly']['count'] }}</h5>
                            <small class="text-muted">Total Transactions</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body py-3">
                            <div class="bg-info bg-opacity-10 rounded-circle p-2 d-inline-flex mb-2">
                                <i class="ri-calendar-2-line fs-4 text-info"></i>
                            </div>
                            <h5 class="fw-bold text-info mb-1">₱{{ number_format(collect($dailyRevenue)->sum('total'), 0) }}</h5>
                            <small class="text-muted">This Month's Revenue</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body py-3">
                            <div class="bg-secondary bg-opacity-10 rounded-circle p-2 d-inline-flex mb-2">
                                <i class="ri-calculator-line fs-4 text-secondary"></i>
                            </div>
                            <h5 class="fw-bold text-secondary mb-1">₱{{ number_format(($paymentMethodData['full']['amount'] + $paymentMethodData['quarterly']['amount'] + $paymentMethodData['monthly']['amount']) / max(1, $paymentMethodData['full']['count'] + $paymentMethodData['quarterly']['count'] + $paymentMethodData['monthly']['count']), 0) }}</h5>
                            <small class="text-muted">Average Payment</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards - Right Side -->
        <div class="col-lg-6">
            <div class="row">
                <div class="col-6 mb-3">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body py-3">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-inline-flex mb-2">
                                <i class="ri-time-line fs-4 text-warning"></i>
                            </div>
                            <h5 class="fw-bold text-warning mb-1">{{ $pendingPayments }}</h5>
                            <small class="text-muted">Pending Payments</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body py-3">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-2 d-inline-flex mb-2">
                                <i class="ri-alarm-warning-line fs-4 text-danger"></i>
                            </div>
                            <h5 class="fw-bold text-danger mb-1">{{ $duePayments }}</h5>
                            <small class="text-muted">Due Payments</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body py-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-2 d-inline-flex mb-2">
                                <i class="ri-check-double-line fs-4 text-success"></i>
                            </div>
                            <h5 class="fw-bold text-success mb-1">{{ $completedPayments }}</h5>
                            <small class="text-muted">Completed Payments</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body py-3">
                            <div class="bg-info bg-opacity-10 rounded-circle p-2 d-inline-flex mb-2">
                                <i class="ri-calendar-check-line fs-4 text-info"></i>
                            </div>
                            <h5 class="fw-bold text-info mb-1">{{ $todayPayments }}</h5>
                            <small class="text-muted">Today's Confirmations</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                            <a href="{{ route('cashier.payments') }}" class="btn btn-warning w-100 text-white">
                                <i class="ri-time-line me-2"></i>All Payments
                                <div class="small">{{ $pendingPayments }} pending</div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('cashier.payments') }}?due_status=due" class="btn btn-danger w-100">
                                <i class="ri-alarm-warning-line me-2"></i>Due Payments
                                <div class="small">{{ $duePayments }} overdue</div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('cashier.payment-archives') }}" class="btn btn-outline-primary w-100">
                                <i class="ri-archive-line me-2"></i>Payment Archives
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('cashier.fees') }}" class="btn btn-outline-info w-100">
                                <i class="ri-money-dollar-circle-line me-2"></i>Fee Management
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Analytics Dashboard -->
    <div class="row mb-3">
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
                    <div class="chart-container">
                        <canvas id="paymentMethodChart" style="max-height: 200px;"></canvas>
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
                    <div class="chart-container">
                        <canvas id="revenueTrendChart" style="max-height: 200px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

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
                    <div class="chart-container">
                        <canvas id="dailyRevenueChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Payment Methods Breakdown Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-table-line me-2"></i>Payment Methods Breakdown
                        </h5>
                        <button class="btn btn-outline-success btn-sm" onclick="exportPaymentBreakdown()">
                            <i class="ri-download-line me-2"></i>Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($paymentMethodData['full']['count'] > 0 || $paymentMethodData['quarterly']['count'] > 0 || $paymentMethodData['monthly']['count'] > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Payment Method</th>
                                        <th>Transaction Count</th>
                                        <th>Total Amount</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalMethodAmount = $paymentMethodData['full']['amount'] + $paymentMethodData['quarterly']['amount'] + $paymentMethodData['monthly']['amount'];
                                        $methods = [
                                            ['name' => 'Full Payment', 'count' => $paymentMethodData['full']['count'], 'amount' => $paymentMethodData['full']['amount']],
                                            ['name' => 'Quarterly', 'count' => $paymentMethodData['quarterly']['count'], 'amount' => $paymentMethodData['quarterly']['amount']],
                                            ['name' => 'Monthly', 'count' => $paymentMethodData['monthly']['count'], 'amount' => $paymentMethodData['monthly']['amount']]
                                        ];
                                    @endphp
                                    @foreach($methods as $method)
                                        @if($method['count'] > 0)
                                            @php
                                                $percentage = $totalMethodAmount > 0 ? ($method['amount'] / $totalMethodAmount) * 100 : 0;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="fw-bold">{{ $method['name'] }}</span>
                                                </td>
                                                <td>{{ number_format($method['count']) }}</td>
                                                <td>
                                                    <span class="fw-bold text-success">
                                                        ₱{{ number_format($method['amount'], 2) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                            <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                                        </div>
                                                        <small class="text-muted">{{ number_format($percentage, 1) }}%</small>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ri-file-list-line fs-1 text-muted mb-3"></i>
                            <p class="text-muted">No payment data available</p>
                        </div>
                    @endif
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
