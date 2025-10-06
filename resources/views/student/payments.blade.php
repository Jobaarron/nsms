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
                            <h3 class="fw-bold fs-4 mb-0 text-white">₱{{ number_format($totalPaid, 2) }}</h3>
                            <small class="text-white">Total Paid</small>
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
                            <h3 class="fw-bold fs-4 mb-0 text-white">₱{{ number_format($balanceDue, 2) }}</h3>
                            <small class="text-white">Balance Due</small>
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
                            <h3 class="fw-bold fs-4 mb-0 text-white">₱{{ number_format($totalFeesAmount, 2) }}</h3>
                            <small class="text-white">Total Fees</small>
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
                            <h3 class="fw-bold fs-4 mb-0 text-white">{{ ucfirst($student->payment_mode ?? 'Not Set') }}</h3>
                            <small class="text-white">Payment Mode</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Payment Schedule -->
                @if($paymentSchedules->count() > 0)
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
                            @if($paymentSchedules->count() > 0)
                                @php
                                    $pendingCount = $paymentSchedules->where('confirmation_status', 'pending')->count();
                                    $confirmedCount = $paymentSchedules->where('confirmation_status', 'confirmed')->count();
                                    $rejectedCount = $paymentSchedules->where('confirmation_status', 'rejected')->count();
                                @endphp
                                
                                @if($pendingCount > 0)
                                    <div class="alert alert-warning mb-3">
                                        <i class="ri-time-line me-2"></i>
                                        <strong>Payment Schedule Status:</strong> Your payment schedule has been submitted and is awaiting cashier confirmation.
                                        <br><small>You cannot modify payment settings until this is processed.</small>
                                    </div>
                                @elseif($confirmedCount > 0)
                                    <div class="alert alert-success mb-3">
                                        <i class="ri-check-line me-2"></i>
                                        <strong>Payment Schedule Confirmed:</strong> Your payment schedule has been approved by the cashier.
                                        <br><small>Contact the cashier's office for any changes needed.</small>
                                    </div>
                                @elseif($rejectedCount > 0)
                                    <div class="alert alert-danger mb-3">
                                        <i class="ri-close-line me-2"></i>
                                        <strong>Payment Schedule Rejected:</strong> Your payment schedule was not approved.
                                        <br><small>Please contact the cashier's office for assistance.</small>
                                    </div>
                                @else
                                    <div class="alert alert-info mb-3">
                                        <i class="ri-information-line me-2"></i>
                                        <strong>Payment Schedule Status:</strong> Your payment schedule has been submitted and is being processed by the cashier.
                                    </div>
                                @endif
                            @endif
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Payment Period</th>
                                            <th>Amount</th>
                                            <th>Scheduled Date</th>
                                            <th>Payment Status</th>
                                            <th>Cashier Status</th>
                                            <th>Transaction ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paymentSchedules as $payment)
                                            <tr>
                                                <td class="fw-semibold">{{ $payment->period_name }}</td>
                                                <td class="fw-bold text-primary">₱{{ number_format($payment->amount, 2) }}</td>
                                                <td>{{ $payment->scheduled_date ? $payment->scheduled_date->format('M d, Y') : 'Not Set' }}</td>
                                                <td>
                                                    @if($payment->status === 'paid')
                                                        <span class="badge bg-success">Paid</span>
                                                    @else
                                                        <span class="badge bg-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($payment->confirmation_status === 'confirmed')
                                                        <span class="badge bg-success">
                                                            <i class="ri-check-line me-1"></i>Confirmed
                                                        </span>
                                                    @elseif($payment->confirmation_status === 'rejected')
                                                        <span class="badge bg-danger">
                                                            <i class="ri-close-line me-1"></i>Rejected
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning">
                                                            <i class="ri-time-line me-1"></i>Pending Review
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="font-monospace text-muted">{{ $payment->transaction_id }}</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="ri-calendar-check-line me-2"></i>Payment Schedule
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-5">
                                <i class="ri-calendar-line fs-1 text-muted mb-3"></i>
                                <h6 class="text-muted">No Payment Schedule</h6>
                                <p class="text-muted small">You haven't submitted a payment schedule yet.</p>
                                @php
                                    $hasAnySchedule = \App\Models\Payment::where('payable_type', \App\Models\Student::class)
                                        ->where('payable_id', $student->id)
                                        ->exists();
                                @endphp
                                
                                @if($student->enrollment_status === 'pre_registered' && !$hasAnySchedule)
                                    <a href="{{ route('student.enrollment') }}" class="btn btn-primary">
                                        <i class="ri-send-plane-line me-2"></i>Submit Payment Schedule
                                    </a>
                                @elseif($hasAnySchedule)
                                    <div class="alert alert-info">
                                        <i class="ri-information-line me-2"></i>
                                        <strong>Payment Schedule Already Submitted</strong><br>
                                        <small>You have already submitted a payment schedule. Please wait for cashier confirmation or contact the cashier's office for changes.</small>
                                    </div>
                                @endif
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
                        @if(isset($paymentHistory) && $paymentHistory->count() > 0)
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
                                                <td class="fw-semibold">{{ $payment->transaction_id }}</td>
                                                <td>{{ $payment->confirmed_at ? $payment->confirmed_at->format('M d, Y') : 'N/A' }}</td>
                                                <td class="fw-bold text-success">₱{{ number_format($payment->amount_received ?: $payment->amount, 2) }}</td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                                <td class="font-monospace">{{ $payment->reference_number ?: 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <i class="ri-check-line me-1"></i>Completed
                                                    </span>
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
                                    <span class="fw-semibold text-success">₱{{ number_format($totalPaid, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Balance:</span>
                                    <span class="fw-bold text-{{ $balanceDue > 0 ? 'danger' : 'success' }}">₱{{ number_format($balanceDue, 2) }}</span>
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
                            <i class="ri-bank-card-line me-2"></i>Payment Mode
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
                @php
                    $hasPendingSchedule = $paymentSchedules->where('confirmation_status', 'pending')->count() > 0;
                    $hasSubmittedSchedule = $paymentSchedules->count() > 0;
                @endphp
                
                @if(!$student->is_paid && $student->enrollment_status === 'enrolled')
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="card-title mb-0">
                                <i class="ri-settings-line me-2"></i>Payment Mode Settings
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($hasSubmittedSchedule)
                                <!-- Payment Schedule Submitted - Locked State -->
                                <div class="alert alert-warning mb-3">
                                    <i class="ri-lock-line me-2"></i>
                                    <strong>Payment Schedule Submitted</strong><br>
                                    <small>Your payment schedule has been submitted and is under review. You cannot modify payment settings at this time.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Current Payment Mode</label>
                                    <div class="fw-semibold text-muted">
                                        <i class="ri-lock-line me-1"></i>{{ ucfirst($student->payment_mode ?? 'Not Set') }}
                                    </div>
                                </div>
                                
                                @if($hasPendingSchedule)
                                    <div class="alert alert-info mb-3">
                                        <i class="ri-information-line me-2"></i>
                                        <strong>Status:</strong> Waiting for cashier confirmation
                                    </div>
                                @endif
                                
                                <div class="text-center">
                                    <p class="text-muted small mb-2">
                                        <i class="ri-customer-service-line me-1"></i>
                                        Need to make changes? Contact the cashier's office.
                                    </p>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="ri-lock-line me-1"></i>Payment Settings Locked
                                        </button>
                                        <small class="text-muted">
                                            Visit the cashier to cancel or modify your payment schedule
                                        </small>
                                    </div>
                                    
                                    <div class="mt-3 p-3 bg-light rounded">
                                        <h6 class="fw-bold text-primary mb-2">
                                            <i class="ri-customer-service-line me-1"></i>Nicolites Montessori School
                                        </h6>
                                        <div class="row text-sm">
                                            <div class="col-12 mb-1">
                                                <i class="ri-map-pin-line me-1 text-muted"></i>
                                                <small>San Roque St., Brgy 4 Nasugbu, Batangas</small>
                                            </div>
                                            <div class="col-12 mb-1">
                                                <i class="ri-time-line me-1 text-muted"></i>
                                                <small>Mon-Fri: 8:00 AM - 5:00 PM</small>
                                            </div>
                                            <div class="col-12">
                                                <i class="ri-phone-line me-1 text-muted"></i>
                                                <small>(043) 416-0149</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- No Payment Schedule - Allow Changes -->
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
                            @endif
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
                            <button class="btn btn-success disabled" onclick="window.print()">
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
