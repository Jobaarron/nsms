<x-enrollee-layout>
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="section-title">Payment Information</h1>
            <div>
                <span class="badge {{ $enrollee->is_paid ? 'bg-success' : 'bg-warning' }}">
                    {{ $enrollee->is_paid ? 'Paid' : 'Pending Payment' }}
                </span>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- PAYMENT STATUS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-money-dollar-circle-line me-2"></i>
                            Payment Status
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($enrollee->is_paid)
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="ri-checkbox-circle-line me-2"></i>
                                <div>
                                    <strong>Payment Completed!</strong>
                                    <br>Your enrollment fee has been successfully paid on {{ $enrollee->payment_date->format('F d, Y g:i A') }}.
                                </div>
                            </div>
                        @elseif($enrollee->enrollment_status === 'approved')
                            <div class="alert alert-warning d-flex align-items-center">
                                <i class="ri-time-line me-2"></i>
                                <div>
                                    <strong>Payment Required!</strong>
                                    <br>Your application has been approved. Please complete your payment to proceed with enrollment.
                                </div>
                            </div>
                        @elseif($enrollee->enrollment_status === 'pending')
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="ri-information-line me-2"></i>
                                <div>
                                    <strong>Application Under Review</strong>
                                    <br>Payment will be available once your application is approved.
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary d-flex align-items-center">
                                <i class="ri-information-line me-2"></i>
                                <div>
                                    <strong>Payment Not Available</strong>
                                    <br>Payment is not available for your current application status.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- PAYMENT DETAILS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-file-list-line me-2"></i>
                            Payment Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Application ID</dt>
                            <dd class="col-sm-8">{{ $enrollee->application_id }}</dd>
                            <dt class="col-sm-4">Student Name</dt>
                            <dd class="col-sm-8">{{ $enrollee->full_name }}</dd>
                            <dt class="col-sm-4">Grade Level</dt>
                            <dd class="col-sm-8">{{ $enrollee->grade_level_applied }}{{ $enrollee->strand_applied ? ' - ' . $enrollee->strand_applied : '' }}</dd>
                            <dt class="col-sm-4">Academic Year</dt>
                            <dd class="col-sm-8">{{ $enrollee->academic_year }}</dd>
                            <dt class="col-sm-4">Payment Mode</dt>
                            <dd class="col-sm-8">{{ ucfirst($enrollee->payment_mode) }}</dd>
                            
                            @php
                                $feeCalculation = \App\Models\Fee::calculateTotalFeesForGrade($enrollee->grade_level_applied, $enrollee->academic_year);
                                $breakdown = $feeCalculation['breakdown'] ?? [];
                            @endphp
                            
                            @if($breakdown['entrance'] > 0)
                            <dt class="col-sm-4">Entrance Fee</dt>
                            <dd class="col-sm-8">
                                <strong class="text-primary">₱{{ number_format($breakdown['entrance'], 2) }}</strong>
                            </dd>
                            @endif
                            
                            @if($breakdown['miscellaneous'] > 0)
                            <dt class="col-sm-4">Miscellaneous Fee</dt>
                            <dd class="col-sm-8">
                                <strong class="text-primary">₱{{ number_format($breakdown['miscellaneous'], 2) }}</strong>
                            </dd>
                            @endif
                            
                            @if($breakdown['tuition'] > 0)
                            <dt class="col-sm-4">Tuition Fee</dt>
                            <dd class="col-sm-8">
                                <strong class="text-primary">₱{{ number_format($breakdown['tuition'], 2) }}</strong>
                            </dd>
                            @endif
                            
                            <dt class="col-sm-4"><strong>Total Amount Due</strong></dt>
                            <dd class="col-sm-8">
                                <strong class="text-primary fs-5">₱{{ number_format($feeCalculation['total_amount'] ?? 0, 2) }}</strong>
                            </dd>
                            @if($enrollee->payment_date)
                            <dt class="col-sm-4">Payment Date</dt>
                            <dd class="col-sm-8">{{ $enrollee->payment_date->format('F d, Y g:i A') }}</dd>
                            @endif
                            @if($enrollee->payment_reference)
                            <dt class="col-sm-4">Payment Reference</dt>
                            <dd class="col-sm-8">
                                <code>{{ $enrollee->payment_reference }}</code>
                            </dd>
                            @endif
                        </dl>
                    </div>
                </div>

                @if(!$enrollee->is_paid && $enrollee->enrollment_status === 'approved' && $enrollee->total_fees_due)
                <!-- PAYMENT FORM -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-secure-payment-line me-2"></i>
                            Make Payment
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="paymentForm" method="POST" action="{{ route('enrollee.payment.process') }}">
                            @csrf
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="gcash">GCash</option>
                                        <option value="paymaya">PayMaya</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="over_counter">Over the Counter</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="amount" class="form-label">Amount</label>
                                    @php $balanceDue = $enrollee->total_fees_due - $enrollee->total_paid; @endphp
                                    <input type="text" class="form-control" id="amount" value="₱{{ number_format($balanceDue, 2) }}" readonly>
                                    <input type="hidden" name="amount" value="{{ $balanceDue }}">
                                    <div class="form-text">Balance due after previous payments</div>
                                </div>
                            </div>

                            <div id="payment_instructions" class="alert alert-info" style="display: none;">
                                <h6>Payment Instructions:</h6>
                                <div id="instruction_content"></div>
                            </div>

                            <div class="mb-3" id="reference_field" style="display: none;">
                                <label for="payment_reference" class="form-label">Payment Reference/Transaction ID</label>
                                <input type="text" class="form-control" id="payment_reference" name="payment_reference" placeholder="Enter your payment reference or transaction ID">
                                <div class="form-text">Please provide the reference number from your payment transaction.</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="ri-secure-payment-line me-2"></i>
                                    Submit Payment Information
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-4">
                <!-- PAYMENT STATUS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-wallet-line me-2"></i>
                            Payment Status
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $feeCalculation = \App\Models\Fee::calculateTotalFeesForGrade($enrollee->grade_level_applied, $enrollee->academic_year);
                            $totalAmount = $feeCalculation['total_amount'] ?? 0;
                            $balance = $totalAmount - $enrollee->total_paid;
                        @endphp
                        
                        @if($totalAmount > 0)
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="p-3 border rounded">
                                        <h6 class="text-primary mb-1">Total Amount</h6>
                                        <h4 class="text-primary mb-0">₱{{ number_format($totalAmount, 2) }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded">
                                        <h6 class="text-success mb-1">Amount Paid</h6>
                                        <h4 class="text-success mb-0">₱{{ number_format($enrollee->total_paid, 2) }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded">
                                        <h6 class="{{ $balance > 0 ? 'text-danger' : 'text-success' }} mb-1">Balance Due</h6>
                                        <h4 class="{{ $balance > 0 ? 'text-danger' : 'text-success' }} mb-0">₱{{ number_format($balance, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            
                            @if($balance > 0)
                            <div class="alert alert-info mt-3">
                                <i class="ri-information-line me-2"></i>
                                <strong>Payment Instructions:</strong> 
                                Please pay the balance of <strong>₱{{ number_format($balance, 2) }}</strong> 
                                using your selected payment mode: <strong>{{ ucfirst($enrollee->payment_mode) }}</strong>
                            </div>
                            @else
                            <div class="alert alert-success mt-3">
                                <i class="ri-check-line me-2"></i>
                                <strong>Payment Complete!</strong> All fees have been paid in full.
                            </div>
                            @endif
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="ri-calculator-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-2 mb-0">Fee calculation not available</p>
                                <small>Please contact the school administration</small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- FEE BREAKDOWN -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-calculator-line me-2"></i>
                            Fee Breakdown
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $feeCalculation = \App\Models\Fee::calculateTotalFeesForGrade($enrollee->grade_level_applied, $enrollee->academic_year);
                            $fees = $feeCalculation['fees'] ?? [];
                            $breakdown = $feeCalculation['breakdown'] ?? [];
                        @endphp
                        
                        @if(!empty($fees))
                            @foreach($fees as $fee)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <span class="fw-medium">{{ $fee['name'] }}</span>
                                    @if($fee['payment_schedule'] !== 'full_payment')
                                        <br><small class="text-muted">{{ ucwords(str_replace('_', ' ', $fee['payment_schedule'])) }}</small>
                                    @endif
                                </div>
                                <span class="fw-bold">₱{{ number_format($fee['amount'], 2) }}</span>
                            </div>
                            @endforeach
                            
                            <hr class="my-3">
                            
                            <!-- Summary by Category -->
                            @if($breakdown['entrance'] > 0)
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Entrance Fees:</small>
                                <small>₱{{ number_format($breakdown['entrance'], 2) }}</small>
                            </div>
                            @endif
                            @if($breakdown['tuition'] > 0)
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Tuition Fees:</small>
                                <small>₱{{ number_format($breakdown['tuition'], 2) }}</small>
                            </div>
                            @endif
                            @if($breakdown['miscellaneous'] > 0)
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Miscellaneous Fees:</small>
                                <small>₱{{ number_format($breakdown['miscellaneous'], 2) }}</small>
                            </div>
                            @endif
                            
                            <hr class="my-2">
                            
                            <div class="d-flex justify-content-between fw-bold text-primary">
                                <span>Total Amount:</span>
                                <span>₱{{ number_format($feeCalculation['total_amount'] ?? 0, 2) }}</span>
                            </div>
                            
                            @if($enrollee->total_paid > 0)
                            <div class="d-flex justify-content-between text-success mt-2">
                                <span>Amount Paid:</span>
                                <span>-₱{{ number_format($enrollee->total_paid, 2) }}</span>
                            </div>
                            <hr class="my-2">
                            @php $balance = ($feeCalculation['total_amount'] ?? 0) - $enrollee->total_paid; @endphp
                            <div class="d-flex justify-content-between fw-bold {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                <span>Balance Due:</span>
                                <span>₱{{ number_format($balance, 2) }}</span>
                            </div>
                            @endif
                        @else
                            <div class="text-center py-3">
                                <i class="ri-calculator-line display-4 text-muted"></i>
                                <p class="text-muted mt-2">Fee calculation not available</p>
                                <small class="text-muted">Please contact the school for fee information.</small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- PAYMENT SCHEDULE INFO -->
                @if(!empty($fees))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-calendar-schedule-line me-2"></i>
                            Payment Options
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Payment Schedule Options:</h6>
                            <ul class="mb-0">
                                <li><strong>Full Payment:</strong> Pay all fees at once</li>
                                <li><strong>Pay Separate:</strong> Pay each fee category individually</li>
                                <li><strong>Pay Before Exam:</strong> Payment required before examination periods</li>
                            </ul>
                        </div>
                        
                        @php
                            $educationalLevel = \App\Models\Fee::getEducationalLevel($enrollee->grade_level_applied);
                        @endphp
                        
                        <div class="mt-3">
                            <h6>Available for {{ ucwords(str_replace('_', ' ', $educationalLevel)) }} Level:</h6>
                            <div class="row">
                                @foreach($fees as $fee)
                                <div class="col-12 mb-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                        <span class="fw-medium">{{ $fee['name'] }}</span>
                                        <div class="text-end">
                                            <div class="fw-bold">₱{{ number_format($fee['amount'], 2) }}</div>
                                            <small class="text-muted">{{ ucwords(str_replace('_', ' ', $fee['payment_schedule'])) }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- PAYMENT METHODS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-bank-card-line me-2"></i>
                            Accepted Payment Methods
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center">
                                <i class="ri-smartphone-line text-primary me-2"></i>
                                <span>GCash</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="ri-smartphone-line text-success me-2"></i>
                                <span>PayMaya</span>
                            </div>
                            {{-- <div class="d-flex align-items-center">
                                <i class="ri-bank-line text-info me-2"></i>
                                <span>Bank Transfer</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="ri-store-line text-warning me-2"></i>
                                <span>Over the Counter</span>
                            </div> To be use soon in the future --}}
                        </div>
                    </div>
                </div>

                <!-- PAYMENT HELP -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-question-line me-2"></i>
                            Need Help?
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">If you have questions about payment or need assistance:</p>
                        <div class="d-grid gap-2">
                            <a href="tel:+1234567890" class="btn btn-outline-primary btn-sm">
                                <i class="ri-phone-line me-1"></i>
                                Call Support
                            </a>
                            <a href="mailto:support@nsms.edu" class="btn btn-outline-primary btn-sm">
                                <i class="ri-mail-line me-1"></i>
                                Email Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pass enrollee data to JavaScript
        window.enrolleeData = {
            applicationId: '{{ $enrollee->application_id }}',
            amountDue: '{{ number_format(($enrollee->total_fees_due ?? 0) - $enrollee->total_paid, 2) }}'
        };
    </script>
    @vite(['resources/js/enrollee-payment.js'])
</x-enrollee-layout>
