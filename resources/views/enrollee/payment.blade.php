<x-enrollee-layout>
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="section-title">Entrance Fee Payment</h1>
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
                            Entrance Fee Status
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($enrollee->is_paid)
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="ri-checkbox-circle-line me-2"></i>
                                <div>
                                    <strong>Entrance Fee Paid!</strong>
                                    <br>Your entrance fee has been successfully paid. Remaining fees will be handled after enrollment approval.
                                </div>
                            </div>
                        @elseif($enrollee->enrollment_status === 'approved')
                            <div class="alert alert-warning d-flex align-items-center">
                                <i class="ri-time-line me-2"></i>
                                <div>
                                    <strong>Entrance Fee Payment Required!</strong>
                                    <br>Your application has been approved. Please pay the entrance fee to proceed with enrollment.
                                </div>
                            </div>
                        @elseif($enrollee->enrollment_status === 'pending')
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="ri-information-line me-2"></i>
                                <div>
                                    <strong>Application Under Review</strong>
                                    <br>Entrance fee payment will be available once your application is approved.
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary d-flex align-items-center">
                                <i class="ri-information-line me-2"></i>
                                <div>
                                    <strong>Payment Not Available</strong>
                                    <br>Entrance fee payment is not available for your current application status.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- ENTRANCE FEE DETAILS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-file-list-line me-2"></i>
                            Entrance Fee Details
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            // Get only entrance fee for enrollees
                            $entranceFee = \App\Models\Fee::where('fee_category', 'entrance')
                                ->where('academic_year', $enrollee->academic_year)
                                ->where('is_active', true)
                                ->whereJsonContains('applicable_grades', $enrollee->grade_level_applied)
                                ->first();
                            
                            $entranceFeeAmount = $entranceFee ? $entranceFee->amount : 0;
                        @endphp
                        
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
                            
                            <dt class="col-sm-4"><strong>Entrance Fee</strong></dt>
                            <dd class="col-sm-8">
                                <strong class="text-primary fs-5">₱{{ number_format($entranceFeeAmount, 2) }}</strong>
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
                        
                        <!-- IMPORTANT NOTE -->
                        <div class="alert alert-info mt-3">
                            <i class="ri-information-line me-2"></i>
                            <strong>Important:</strong> This is only the entrance fee payment. Additional fees (tuition, miscellaneous) will be handled after you are officially enrolled and become a student.
                        </div>
                    </div>
                </div>

                @if(!$enrollee->is_paid && $enrollee->enrollment_status === 'approved' && $entranceFeeAmount > 0)
                <!-- PAYMENT FORM -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-secure-payment-line me-2"></i>
                            Pay Entrance Fee
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- PAYMENT MODE SELECTION -->
                        <div class="alert alert-warning mb-4">
                            <i class="ri-information-line me-2"></i>
                            <strong>Choose your payment mode:</strong> This will determine how you complete your payment and future student fees.
                        </div>
                        
                        <form id="paymentForm" method="POST" action="{{ route('enrollee.payment.process') }}">
                            @csrf
                            
                            <!-- PAYMENT MODE -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Payment Mode</label>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-2" id="cash-card">
                                            <div class="card-body text-center">
                                                <input type="radio" class="form-check-input" id="cash" name="payment_mode" value="cash" required>
                                                <label for="cash" class="form-check-label w-100">
                                                    <i class="ri-money-dollar-circle-line display-6 text-success d-block mb-2"></i>
                                                    <strong>Cash Payment</strong>
                                                    <small class="text-muted d-block">In-person payment at school</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-2" id="online-card">
                                            <div class="card-body text-center">
                                                <input type="radio" class="form-check-input" id="online" name="payment_mode" value="online" required>
                                                <label for="online" class="form-check-label w-100">
                                                    <i class="ri-smartphone-line display-6 text-primary d-block mb-2"></i>
                                                    <strong>Online Payment</strong>
                                                    <small class="text-muted d-block">E-wallet & digital payments</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-2" id="installment-card">
                                            <div class="card-body text-center">
                                                <input type="radio" class="form-check-input" id="installment" name="payment_mode" value="installment" required>
                                                <label for="installment" class="form-check-label w-100">
                                                    <i class="ri-calendar-check-line display-6 text-warning d-block mb-2"></i>
                                                    <strong>Installment</strong>
                                                    <small class="text-muted d-block">In-person or online payments</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- PAYMENT METHOD (CONDITIONAL) -->
                            <div id="payment-method-section" class="mb-3" style="display: none;">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="">Select Payment Method</option>
                                    <!-- Cash options -->
                                    <option value="cash_counter" data-mode="cash">Cash at School Counter</option>
                                    <!-- Online options -->
                                    <option value="gcash" data-mode="online">GCash</option>
                                    <option value="paymaya" data-mode="online">PayMaya</option>
                                    <option value="bank_transfer" data-mode="online">Bank Transfer</option>
                                    <!-- Installment options -->
                                    <option value="installment_cash" data-mode="installment">Installment - Cash</option>
                                    <option value="installment_online" data-mode="installment">Installment - Online</option>
                                </select>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="amount" class="form-label">Entrance Fee Amount</label>
                                    <input type="text" class="form-control" id="amount" value="₱{{ number_format($entranceFeeAmount, 2) }}" readonly>
                                    <input type="hidden" name="amount" value="{{ $entranceFeeAmount }}">
                                    <div class="form-text">One-time entrance fee for enrollment</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_date" class="form-label">Payment Date</label>
                                    <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                                    <div class="form-text">Date when payment was made</div>
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
                                <button type="submit" class="btn btn-primary btn-lg" id="submit-payment">
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
                <!-- ENTRANCE FEE STATUS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-wallet-line me-2"></i>
                            Entrance Fee Status
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <h4 class="text-primary mb-1">₱{{ number_format($entranceFeeAmount, 2) }}</h4>
                            <small class="text-muted">Entrance Fee Amount</small>
                        </div>
                        
                        @if($enrollee->is_paid)
                            <div class="alert alert-success">
                                <i class="ri-check-circle-line me-2"></i>
                                <strong>Paid</strong>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="ri-time-line me-2"></i>
                                <strong>Pending Payment</strong>
                            </div>
                        @endif
                        
                        <div class="text-muted small">
                            <p class="mb-1"><strong>Payment Mode:</strong> {{ ucfirst($enrollee->payment_mode) }}</p>
                            @if($enrollee->payment_date)
                                <p class="mb-0"><strong>Paid on:</strong> {{ $enrollee->payment_date->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- SUBJECT BREAKDOWN -->
                @php
                    $subjects = \App\Models\Subject::getSubjectsForEnrollee($enrollee);
                    $totalSubjects = $subjects->count();
                @endphp
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="ri-book-line me-2"></i>
                            Subject Assessment ({{ $enrollee->grade_level_applied }}{{ $enrollee->strand_applied ? ' - ' . $enrollee->strand_applied : '' }}{{ $enrollee->track_applied ? ' (' . $enrollee->track_applied . ')' : '' }})
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($subjects->count() > 0)
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">
                                        <i class="ri-book-open-line me-1"></i>
                                        Academic Subjects ({{ $subjects->count() }} subjects)
                                    </h6>
                                    <div class="row">
                                        @foreach($subjects as $subject)
                                            <div class="col-md-6 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-book-line me-2 text-muted"></i>
                                                    <span class="small">{{ $subject->subject_name }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="ri-information-line me-2"></i>
                                        <strong>Total Academic Subjects:</strong> {{ $totalSubjects }} subjects for {{ $enrollee->grade_level_applied }}
                                        @if($enrollee->strand_applied)
                                            - {{ $enrollee->strand_applied }}
                                            @if($enrollee->track_applied)
                                                ({{ $enrollee->track_applied }})
                                            @endif
                                        @endif
                                    </span>
                                    <span class="badge bg-primary">{{ $totalSubjects }} subjects</span>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="ri-alert-line me-2"></i>
                                <strong>No subjects available</strong><br>
                                Subjects for your grade level and strand are not yet configured in the system.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- FEE ASSESSMENT BREAKDOWN -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="ri-calculator-line me-2"></i>
                            Complete Fee Assessment
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $feeCalculation = \App\Models\Fee::calculateTotalFeesForGrade($enrollee->grade_level_applied, $enrollee->academic_year);
                            $breakdown = $feeCalculation['breakdown'] ?? [];
                            $totalFees = $feeCalculation['total'] ?? 0;
                        @endphp
                        
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="text-success mb-3">
                                    <i class="ri-money-dollar-circle-line me-1"></i>
                                    Fee Breakdown
                                </h6>
                                
                                @if($breakdown['entrance'] > 0)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <span class="fw-semibold">Entrance Fee</span>
                                        <br><small class="text-muted">One-time application processing fee</small>
                                    </div>
                                    <span class="text-success fw-bold">₱{{ number_format($breakdown['entrance'], 2) }}</span>
                                </div>
                                @endif
                                
                                @if($breakdown['tuition'] > 0)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <span class="fw-semibold">Tuition Fee</span>
                                        <br><small class="text-muted">Academic instruction for {{ $totalSubjects }} subjects</small>
                                    </div>
                                    <span class="text-primary fw-bold">₱{{ number_format($breakdown['tuition'], 2) }}</span>
                                </div>
                                @endif
                                
                                @if($breakdown['miscellaneous'] > 0)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <span class="fw-semibold">Miscellaneous Fee</span>
                                        <br><small class="text-muted">Laboratory, library, activities, and other fees</small>
                                    </div>
                                    <span class="text-warning fw-bold">₱{{ number_format($breakdown['miscellaneous'], 2) }}</span>
                                </div>
                                @endif
                                
                                <div class="d-flex justify-content-between align-items-center py-3 mt-2 bg-light rounded">
                                    <span class="fw-bold fs-5">Total Annual Fees:</span>
                                    <span class="fw-bold fs-4 text-danger">₱{{ number_format($totalFees, 2) }}</span>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <h6 class="text-info mb-3">
                                    <i class="ri-calendar-check-line me-1"></i>
                                    Payment Schedule
                                </h6>
                                
                                <div class="small">
                                    <div class="mb-2 p-2 border rounded">
                                        <strong class="text-success">Upon Enrollment:</strong><br>
                                        Entrance Fee: ₱{{ number_format($breakdown['entrance'] ?? 0, 2) }}
                                    </div>
                                    
                                    @if($enrollee->payment_mode === 'installment')
                                        <div class="mb-2 p-2 border rounded">
                                            <strong class="text-primary">1st Quarter:</strong><br>
                                            ₱{{ number_format(($breakdown['tuition'] + $breakdown['miscellaneous']) / 4, 2) }}
                                        </div>
                                        <div class="mb-2 p-2 border rounded">
                                            <strong class="text-primary">2nd Quarter:</strong><br>
                                            ₱{{ number_format(($breakdown['tuition'] + $breakdown['miscellaneous']) / 4, 2) }}
                                        </div>
                                        <div class="mb-2 p-2 border rounded">
                                            <strong class="text-primary">3rd Quarter:</strong><br>
                                            ₱{{ number_format(($breakdown['tuition'] + $breakdown['miscellaneous']) / 4, 2) }}
                                        </div>
                                        <div class="mb-2 p-2 border rounded">
                                            <strong class="text-primary">4th Quarter:</strong><br>
                                            ₱{{ number_format(($breakdown['tuition'] + $breakdown['miscellaneous']) / 4, 2) }}
                                        </div>
                                    @else
                                        <div class="mb-2 p-2 border rounded">
                                            <strong class="text-primary">After Approval:</strong><br>
                                            Remaining: ₱{{ number_format($breakdown['tuition'] + $breakdown['miscellaneous'], 2) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FUTURE FEES INFO -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="ri-information-line me-2"></i>
                            Future Student Fees
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>After enrollment approval:</strong> Additional fees (tuition, miscellaneous) will be handled through the student portal with your chosen payment mode.
                        </div>
                        
                        @php
                            $feeCalculation = \App\Models\Fee::calculateTotalFeesForGrade($enrollee->grade_level_applied, $enrollee->academic_year);
                            $breakdown = $feeCalculation['breakdown'] ?? [];
                        @endphp
                        
                        <div class="small text-muted">
                            <p class="mb-2"><strong>Estimated Additional Fees:</strong></p>
                            @if($breakdown['tuition'] > 0)
                            <div class="d-flex justify-content-between mb-1">
                                <span>• Tuition Fee:</span>
                                <span>₱{{ number_format($breakdown['tuition'], 2) }}</span>
                            </div>
                            @endif
                            @if($breakdown['miscellaneous'] > 0)
                            <div class="d-flex justify-content-between mb-1">
                                <span>• Miscellaneous Fee:</span>
                                <span>₱{{ number_format($breakdown['miscellaneous'], 2) }}</span>
                            </div>
                            @endif
                            
                            @if($breakdown['tuition'] > 0 || $breakdown['miscellaneous'] > 0)
                            <hr class="my-2">
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total Additional:</span>
                                <span>₱{{ number_format(($breakdown['tuition'] ?? 0) + ($breakdown['miscellaneous'] ?? 0), 2) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pass data to JavaScript -->
    <script>
        // Pass enrollee data to JavaScript
        window.enrolleeData = {
            applicationId: '{{ $enrollee->application_id }}',
            entranceFeeAmount: '{{ number_format($entranceFeeAmount, 2) }}'
        };
    </script>
    @vite(['resources/js/enrollee-payment.js'])
</x-enrollee-layout>
