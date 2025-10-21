<x-cashier-layout>
    @push('styles')
        @vite('resources/css/index_student.css')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="section-title mb-1">Payment Reports</h2>
                    <p class="text-muted mb-0">Analytics and insights on payment transactions</p>
                </div>
                <div class="text-end">
                    <button class="btn btn-outline-success" onclick="exportAllReports()">
                        <i class="ri-download-line me-2"></i>Export Reports
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('cashier.reports') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="{{ $dateFrom }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="{{ $dateTo }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ri-search-line me-2"></i>Generate
                                </button>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('today')">Today</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('week')">This Week</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('month')">This Month</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('year')">This Year</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        @php
            $totalConfirmed = $paymentSummary->where('confirmation_status', 'confirmed')->first();
            $totalPending = $paymentSummary->where('confirmation_status', 'pending')->first();
            // Rejected status removed - now treated as "Not yet paid"
        @endphp
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="ri-check-double-line fs-2 text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="fw-bold fs-4 mb-0">₱{{ number_format($totalConfirmed->total_amount ?? 0, 2) }}</h4>
                        <small class="text-muted">Paid ({{ $totalConfirmed->count ?? 0 }})</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="ri-time-line fs-2 text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="fw-bold fs-4 mb-0">₱{{ number_format($totalPending->total_amount ?? 0, 2) }}</h4>
                        <small class="text-muted">Not yet paid ({{ $totalPending->count ?? 0 }})</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejected status removed - now treated as "Not yet paid" -->

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="ri-money-dollar-circle-line fs-2 text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        @php
                            $totalAmount = $paymentSummary->sum('total_amount');
                        @endphp
                        <h4 class="fw-bold fs-4 mb-0">₱{{ number_format($totalAmount, 2) }}</h4>
                        <small class="text-muted">Total Revenue</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Daily Payments Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-line-chart-line me-2"></i>Daily Payment Trends
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyPaymentsChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Payment Methods Chart -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-pie-chart-line me-2"></i>Payment Methods
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-table-line me-2"></i>Payment Methods Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    @if($paymentMethods->count() > 0)
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
                                        $totalMethodAmount = $paymentMethods->sum('total_amount');
                                    @endphp
                                    @foreach($paymentMethods as $method)
                                        @php
                                            $percentage = $totalMethodAmount > 0 ? ($method->total_amount / $totalMethodAmount) * 100 : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="fw-bold">{{ ucfirst(str_replace('_', ' ', $method->payment_method)) }}</span>
                                            </td>
                                            <td>{{ $method->count }}</td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    ₱{{ number_format($method->total_amount, 2) }}
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ri-file-list-line fs-1 text-muted mb-3"></i>
                            <p class="text-muted">No payment data available for the selected period</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Daily Payments Chart
            const dailyCtx = document.getElementById('dailyPaymentsChart').getContext('2d');
            const dailyPaymentsData = @json($dailyPayments);
            
            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: dailyPaymentsData.map(item => new Date(item.date).toLocaleDateString()),
                    datasets: [{
                        label: 'Daily Payments',
                        data: dailyPaymentsData.map(item => item.total_amount),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Amount: ₱' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Payment Methods Chart
            const methodsCtx = document.getElementById('paymentMethodsChart').getContext('2d');
            const paymentMethodsData = @json($paymentMethods);
            
            new Chart(methodsCtx, {
                type: 'doughnut',
                data: {
                    labels: paymentMethodsData.map(item => item.payment_method.replace('_', ' ').toUpperCase()),
                    datasets: [{
                        data: paymentMethodsData.map(item => item.total_amount),
                        backgroundColor: [
                            '#28a745',
                            '#17a2b8',
                            '#ffc107',
                            '#dc3545',
                            '#6f42c1'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ₱' + context.parsed.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Date range functions
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

                document.getElementById('date_from').value = fromDate;
                document.getElementById('date_to').value = toDate;
            }

            function exportAllReports() {
                // Implementation for exporting all reports
                console.log('Export all reports');
                alert('Export functionality will be implemented');
            }
        </script>
    @endpush
</x-cashier-layout>
