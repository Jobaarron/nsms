<x-student-layout>
    @push('styles')
        @vite('resources/css/index_student.css')
    @endpush

    <!-- Page Header -->
    <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="section-title mb-1">Payments & Billing</h2>
                        <p class="text-muted mb-0">Manage your payments and view billing information</p>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Student ID: <strong>{{ $student->student_id }}</strong></small><br>
                        <small class="text-muted">Payment Status: 
                            <span class="badge bg-{{ $student->is_paid ? 'success' : 'warning' }}">
                                {{ $student->is_paid ? 'Paid' : 'Pending' }}
                            </span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Summary Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm card-summary card-paid h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="ri-money-dollar-circle-line fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="fw-bold fs-4 mb-0">₱{{ number_format($student->total_paid ?? 0, 2) }}</h3>
                            <small class="text-white-50">Total Paid</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm card-summary card-credits h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="ri-money-dollar-box-line fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            @php
                                $balance = ($student->total_fees_due ?? 0) - ($student->total_paid ?? 0);
                            @endphp
                            <h3 class="fw-bold fs-4 mb-0">₱{{ number_format($balance, 2) }}</h3>
                            <small class="text-white-50">Balance Due</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm card-summary card-subjects h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="ri-bill-line fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="fw-bold fs-4 mb-0">₱{{ number_format($student->total_fees_due ?? 0, 2) }}</h3>
                            <small class="text-white-50">Total Fees</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm card-summary card-gpa h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="ri-calendar-line fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="fw-bold fs-4 mb-0">{{ ucfirst($student->payment_mode ?? 'Not Set') }}</h3>
                            <small class="opacity-75">Payment Mode</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Payment Schedule -->
                @if($student->payment_mode && $student->total_fees_due > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="ri-calendar-check-line me-2"></i>Payment Schedule
                                </h5>
                                <span class="badge bg-primary">{{ ucfirst($student->payment_mode) }} Payment</span>
                            </div>
                        </div>
                        <div class="card-body">
                            @php
                                $totalAmount = $student->total_fees_due;
                                $paymentSchedule = [];
                                
                                switch($student->payment_mode) {
                                    case 'full':
                                        $paymentSchedule = [
                                            ['period' => 'Full Payment', 'amount' => $totalAmount, 'due_date' => 'Upon Enrollment', 'status' => $student->is_paid ? 'paid' : 'pending']
                                        ];
                                        break;
                                    case 'quarterly':
                                        $quarterlyAmount = $totalAmount / 4;
                                        $paymentSchedule = [
                                            ['period' => '1st Quarter', 'amount' => $quarterlyAmount, 'due_date' => 'August 2024', 'status' => 'pending'],
                                            ['period' => '2nd Quarter', 'amount' => $quarterlyAmount, 'due_date' => 'November 2024', 'status' => 'pending'],
                                            ['period' => '3rd Quarter', 'amount' => $quarterlyAmount, 'due_date' => 'February 2025', 'status' => 'pending'],
                                            ['period' => '4th Quarter', 'amount' => $quarterlyAmount, 'due_date' => 'May 2025', 'status' => 'pending']
                                        ];
                                        break;
                                    case 'monthly':
                                        $monthlyAmount = $totalAmount / 10;
                                        $months = ['August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May'];
                                        foreach($months as $month) {
                                            $paymentSchedule[] = ['period' => $month, 'amount' => $monthlyAmount, 'due_date' => $month . ' 2024/2025', 'status' => 'pending'];
                                        }
                                        break;
                                }
                            @endphp
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Payment Period</th>
                                            <th>Amount</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paymentSchedule as $index => $payment)
                                            <tr>
                                                <td class="fw-semibold">{{ $payment['period'] }}</td>
                                                <td class="fw-bold text-primary">₱{{ number_format($payment['amount'], 2) }}</td>
                                                <td>{{ $payment['due_date'] }}</td>
                                                <td>
                                                    @if($payment['status'] === 'paid')
                                                        <span class="badge bg-success">Paid</span>
                                                    @elseif($payment['status'] === 'overdue')
                                                        <span class="badge bg-danger">Overdue</span>
                                                    @else
                                                        <span class="badge bg-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($payment['status'] === 'paid')
                                                        <button class="btn btn-outline-success btn-sm" disabled>
                                                            <i class="ri-check-line me-1"></i>Paid
                                                        </button>
                                                    @else
                                                        <button class="btn btn-primary btn-sm" onclick="makePayment({{ $index }}, '{{ $payment['period'] }}', {{ $payment['amount'] }})">
                                                            <i class="ri-money-dollar-circle-line me-1"></i>Pay Now
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Payment History -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="card-title mb-0">
                            <i class="ri-history-line me-2"></i>Payment History
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            // Mock payment history - in real implementation, this would come from payments table
                            $paymentHistory = [];
                            if($student->is_paid) {
                                $paymentHistory = [
                                    [
                                        'id' => 'PAY-001',
                                        'date' => now()->subDays(5),
                                        'amount' => $student->total_paid ?? 0,
                                        'method' => 'Cash',
                                        'reference' => 'CASH-' . now()->format('Ymd') . '-001',
                                        'status' => 'completed'
                                    ]
                                ];
                            }
                        @endphp
                        
                        @if(count($paymentHistory) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Payment ID</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Reference</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paymentHistory as $payment)
                                            <tr>
                                                <td class="fw-semibold">{{ $payment['id'] }}</td>
                                                <td>{{ $payment['date']->format('M d, Y') }}</td>
                                                <td class="fw-bold text-success">₱{{ number_format($payment['amount'], 2) }}</td>
                                                <td>{{ $payment['method'] }}</td>
                                                <td class="font-monospace">{{ $payment['reference'] }}</td>
                                                <td>
                                                    <span class="badge bg-success">{{ ucfirst($payment['status']) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="ri-history-line fs-1 text-muted mb-3"></i>
                                <h6 class="text-muted">No payment history</h6>
                                <p class="text-muted small">Your payment transactions will appear here once you make payments.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Fee Breakdown -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h6 class="card-title mb-0">
                            <i class="ri-bill-line me-2"></i>Fee Breakdown
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $feeCalculation = \App\Models\Fee::calculateTotalFeesForGrade($student->grade_level);
                            $fees = $feeCalculation['fees'];
                            $totalAmount = $feeCalculation['total_amount'];
                        @endphp
                        
                        @if(count($fees) > 0)
                            <div class="fee-list mb-3">
                                @foreach($fees as $fee)
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <div>
                                            <div class="fw-semibold">{{ $fee['name'] }}</div>
                                            @if($fee['description'])
                                                <small class="text-muted">{{ $fee['description'] }}</small>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold">₱{{ number_format($fee['amount'], 2) }}</div>
                                            @if(!$fee['is_required'])
                                                <small class="text-muted">Optional</small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0">Total Amount:</h6>
                                    <h5 class="fw-bold text-primary mb-0">₱{{ number_format($totalAmount, 2) }}</h5>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Paid:</span>
                                    <span class="fw-semibold text-success">₱{{ number_format($student->total_paid ?? 0, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Balance:</span>
                                    <span class="fw-bold text-{{ $balance > 0 ? 'danger' : 'success' }}">₱{{ number_format($balance, 2) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ri-bill-line fs-1 text-muted mb-3"></i>
                                <h6 class="text-muted">No fees configured</h6>
                                <p class="text-muted small">Please contact the registrar for fee information.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h6 class="card-title mb-0">
                            <i class="ri-bank-card-line me-2"></i>Payment Methods
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <i class="ri-money-dollar-circle-line fs-4 text-success me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Cash Payment</h6>
                                        <small class="text-muted">Pay at the cashier's office</small>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <i class="ri-bank-line fs-4 text-primary me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Bank Transfer</h6>
                                        <small class="text-muted">Transfer to school account</small>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <i class="ri-smartphone-line fs-4 text-info me-3"></i>
                                    <div>
                                        <h6 class="mb-1">GCash / PayMaya</h6>
                                        <small class="text-muted">Mobile wallet payment</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Mode Change -->
                @if(!$student->is_paid && $student->enrollment_status === 'enrolled')
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="card-title mb-0">
                                <i class="ri-settings-line me-2"></i>Change Payment Mode
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('student.payment.mode.update') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Current Mode</label>
                                    <div class="fw-semibold">{{ ucfirst($student->payment_mode ?? 'Not Set') }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Payment Mode</label>
                                    <select class="form-select" name="payment_mode" required>
                                        <option value="">Select Payment Mode</option>
                                        <option value="full" {{ $student->payment_mode === 'full' ? 'selected' : '' }}>Full Payment</option>
                                        <option value="quarterly" {{ $student->payment_mode === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                        <option value="monthly" {{ $student->payment_mode === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="ri-refresh-line me-1"></i>Update Payment Mode
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h6 class="card-title mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary">
                                <i class="ri-dashboard-line me-2"></i>Back to Dashboard
                            </a>
                            @if($student->enrollment_status === 'pre_registered')
                                <a href="{{ route('student.enrollment') }}" class="btn btn-primary">
                                    <i class="ri-user-add-line me-2"></i>Complete Enrollment
                                </a>
                            @endif
                            <button class="btn btn-success" onclick="window.print()">
                                <i class="ri-printer-line me-2"></i>Print Statement
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Make Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <div class="mb-3">
                            <label class="form-label">Payment Period</label>
                            <input type="text" class="form-control" id="paymentPeriod" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="text" class="form-control" id="paymentAmount" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" id="paymentMethod" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="gcash">GCash</option>
                                <option value="paymaya">PayMaya</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reference Number (Optional)</label>
                            <input type="text" class="form-control" id="referenceNumber" placeholder="Enter reference number">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="processPayment()">
                        <i class="ri-money-dollar-circle-line me-1"></i>Process Payment
                    </button>
                </div>
            </div>
        </div>

    @push('scripts')
        <script>
            let currentPaymentIndex = null;
            
            function makePayment(index, period, amount) {
                currentPaymentIndex = index;
                document.getElementById('paymentPeriod').value = period;
                document.getElementById('paymentAmount').value = '₱' + amount.toLocaleString('en-US', {minimumFractionDigits: 2});
                
                const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                modal.show();
            }
            
            function processPayment() {
                const method = document.getElementById('paymentMethod').value;
                const reference = document.getElementById('referenceNumber').value;
                
                if (!method) {
                    alert('Please select a payment method');
                    return;
                }
                
                // In a real implementation, this would send the payment data to the server
                alert('Payment processing functionality will be implemented with actual payment gateway integration.');
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                modal.hide();
            }
        </script>
    @endpush
</x-student-layout>
