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

        <div class="col-lg-3 col-md-6 mb-3">
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

    <!-- Recent Payments -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-list-check-line me-2"></i>Recent Payments
                        </h5>
                        <a href="{{ route('cashier.payment-history') }}" class="btn btn-sm btn-outline-primary">
                            View All <i class="ri-arrow-right-line ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($recentPayments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Student/Enrollee</th>
{{-- <th>Fee Type</th> --}}
                                        <th>Amount</th>
                                        <th>Payment Method</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPayments as $payment)
                                        <tr>
                                            <td>
                                                <span class="fw-bold">{{ $payment->transaction_id }}</span>
                                            </td>
                                            <td>
                                                @if($payment->payable)
                                                    <div>
                                                        <span class="fw-bold">
                                                            {{ $payment->payable->first_name }} {{ $payment->payable->last_name }}
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $payment->payable->student_id ?? $payment->payable->application_id }}
                                                        </small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    ₱{{ number_format($payment->amount, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($payment->payment_method)
                                                    <span class="badge bg-info">
                                                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                                    </span>
{{-- @if($payment->reference_number)
                                                        <br>
                                                        <small class="text-muted">Ref: {{ $payment->reference_number }}</small>
                                                    @endif --}}
                                                @else
                                                    <span class="badge bg-secondary">Not Specified</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match($payment->confirmation_status) {
                                                        'pending' => 'warning',
                                                        'confirmed' => 'success',
                                                        'rejected' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">
                                                    {{ ucfirst($payment->confirmation_status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $payment->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewPaymentDetails({{ $payment->id }})">
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ri-file-list-line fs-1 text-muted mb-3"></i>
                            <p class="text-muted">No recent payments found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function viewPaymentDetails(paymentId) {
                // Implementation for viewing payment details
                console.log('View payment details for ID:', paymentId);
                // This would open a modal with payment details
            }
        </script>
    @endpush
</x-cashier-layout>
