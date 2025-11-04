<x-student-layout>
    @vite(['resources/js/student-enrollment.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container-fluid px-4 py-4">
    @push('styles')
        @vite('resources/css/index_student.css')
    @endpush

    <!-- Page Header -->
    <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="section-title mb-1">Complete Enrollment</h2>
                        <p class="text-muted mb-0">Finalize your enrollment by selecting subjects and payment method</p>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Student ID: <strong>{{ $student->student_id }}</strong></small><br>
                        <small class="text-muted">Grade Level: <strong>{{ $student->grade_level }}</strong></small>
                    </div>
                </div>
            </div>
        </div>

        @if($paymentScheduleStatus)
            <div class="row mb-4">
                <div class="col-12">
                    @if($paymentScheduleStatus->confirmation_status === 'pending')
                        <div class="alert alert-warning border-0 shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="ri-time-line fs-4 me-3"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Payment Schedule Submitted</h6>
                                    <p class="mb-0">Your payment schedule is pending approval from the cashier's office. You will be notified once it's reviewed.</p>
                                </div>
                            </div>
                        </div>
                    @elseif($paymentScheduleStatus->confirmation_status === 'confirmed')
                        <div class="alert alert-success border-0 shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="ri-check-line fs-4 me-3"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Payment Schedule Approved</h6>
                                    <p class="mb-0">Your payment schedule has been approved! You are now fully enrolled and can proceed to make payments.</p>
                                </div>
                            </div>
                        </div>
                    @elseif(in_array($paymentScheduleStatus->confirmation_status, ['rejected', 'declined']))
                        <div class="alert alert-danger border-0 shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="ri-close-line fs-4 me-3"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Payment Schedule Rejected</h6>
                                    <p class="mb-0">Your payment schedule was rejected. Please review the feedback and submit a new schedule.</p>
                                    @if($paymentScheduleStatus->cashier_notes)
                                        <small class="text-muted"><strong>Reason:</strong> {{ $paymentScheduleStatus->cashier_notes }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <form id="enrollmentForm" action="{{ route('student.enrollment.submit') }}" method="POST">
            @csrf
            <div class="row">
                <!-- Left Column - Subjects -->
                <div class="col-lg-8">
                    <!-- Subjects to Take -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="ri-book-open-line me-2"></i>Subjects for {{ $student->grade_level }}
                                @if($student->strand)
                                    - {{ $student->strand }}
                                @endif
                                @if($student->track)
                                    ({{ $student->track }})
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            @php
                                // Use subjects passed from controller
                                $subjects = $currentSubjects ?? collect();
                            @endphp
                            
                            @if($subjects->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Subject Name</th>
                                                <th>Grade Level</th>
                                                @if($student->grade_level === 'Grade 11' || $student->grade_level === 'Grade 12')
                                                    <th>Type</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subjects as $subject)
                                                <tr>
                                                    <td class="fw-semibold">
                                                        <span class="badge bg-{{ $subject->category === 'core' ? 'secondary' : 'primary' }}">
                                                            {{ ucfirst($subject->category) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $subject->subject_name }}</td>
                                                    <td>{{ $subject->grade_level }}</td>
                                                    @if($student->grade_level === 'Grade 11' || $student->grade_level === 'Grade 12')
                                                        <td>
                                                            @if($subject->strand)
                                                                <span class="badge bg-primary">{{ $subject->strand }}</span>
                                                            @else
                                                                <span class="badge bg-secondary">Core</span>
                                                            @endif
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3 p-3 bg-light rounded">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <h6 class="fw-bold text-primary">{{ $subjects->count() }}</h6>
                                            <small class="text-muted">Total Subjects</small>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="fw-bold text-success">{{ $subjects->where('category', 'core')->count() }}</h6>
                                            <small class="text-muted">Core Subjects</small>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="fw-bold text-info">{{ $subjects->where('category', 'specialized')->count() }}</h6>
                                            <small class="text-muted">Specialized</small>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="ri-book-line fs-1 text-muted mb-3"></i>
                                    <h6 class="text-muted">No subjects found for your grade level</h6>
                                    <p class="text-muted small">Please contact the registrar for assistance.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Mode Selection -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="ri-money-dollar-circle-line me-2"></i>Payment Method
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="full_payment" value="full" {{ old('payment_method') === 'full' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="full_payment">
                                            <div class="card border-2 h-100 payment-option" data-mode="full">
                                                <div class="card-body text-center">
                                                    <i class="ri-money-dollar-circle-fill fs-2 text-success mb-2"></i>
                                                    <h6 class="fw-bold">Full Payment</h6>
                                                    <p class="text-muted small mb-0">Pay entire amount at once</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="quarterly" value="quarterly" {{ old('payment_method') === 'quarterly' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="quarterly">
                                            <div class="card border-2 h-100 payment-option" data-mode="quarterly">
                                                <div class="card-body text-center">
                                                    <i class="ri-calendar-line fs-2 text-warning mb-2"></i>
                                                    <h6 class="fw-bold">Quarterly</h6>
                                                    <p class="text-muted small mb-0">4 payments per year</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="monthly" value="monthly" {{ old('payment_method') === 'monthly' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="monthly">
                                            <div class="card border-2 h-100 payment-option" data-mode="monthly">
                                                <div class="card-body text-center">
                                                    <i class="ri-calendar-2-line fs-2 text-info mb-2"></i>
                                                    <h6 class="fw-bold">Monthly</h6>
                                                    <p class="text-muted small mb-0">10 payments per year</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('payment_method')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Payment Schedule & Amount -->
                    <div class="card border-0 shadow-sm mb-4" id="payment-schedule-card" style="display: none;">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="ri-calendar-schedule-line me-2"></i>Payment Schedule & Amount
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Payment Breakdown Display -->
                            <div id="payment-breakdown-container">
                                <!-- Full Payment Breakdown -->
                                <div id="full-payment-breakdown" class="payment-breakdown" style="display: none;">
                                    <h6 class="fw-bold text-success mb-3">
                                        <i class="ri-money-dollar-circle-line me-2"></i>Full Payment Breakdown
                                    </h6>
                                    <div class="alert alert-success">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><strong>Total Amount Due:</strong></span>
                                            <span class="fw-bold">₱<span id="full-total-amount">0.00</span></span>
                                        </div>
                                        <small class="text-muted">Pay the entire amount at once with no additional fees</small>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Payment Date</label>
                                            <input type="date" class="form-control" name="full_payment_date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Amount to Pay</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" class="form-control" name="full_payment_amount" step="0.01" min="0" value="{{ number_format($totalAmount, 2, '.', '') }}" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quarterly Payment Breakdown -->
                                <div id="quarterly-payment-breakdown" class="payment-breakdown" style="display: none;">
                                    <h6 class="fw-bold text-warning mb-3">
                                        <i class="ri-calendar-line me-2"></i>Quarterly Payment Breakdown
                                    </h6>
                                    <div class="alert alert-warning">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><strong>Total Amount:</strong></span>
                                            <span class="fw-bold">₱<span id="quarterly-total-amount">0.00</span></span>
                                        </div>
                                        <small class="text-muted">4 payments throughout the academic year</small>
                                    </div>
                                    <div class="row g-3">
                                        @php
                                            $quarterlyAmount = $totalAmount / 4;
                                            $quarterDates = [
                                                date('Y-m-d', strtotime('+1 month')),
                                                date('Y-m-d', strtotime('+4 months')),
                                                date('Y-m-d', strtotime('+7 months')),
                                                date('Y-m-d', strtotime('+10 months'))
                                            ];
                                        @endphp
                                        <div class="col-md-6 col-lg-3">
                                            <div class="card bg-light">
                                                <div class="card-body p-3">
                                                    <h6 class="card-title mb-2">1st Quarter</h6>
                                                    <input type="date" class="form-control form-control-sm mb-2" name="quarterly_date_1" min="{{ date('Y-m-d') }}" value="{{ $quarterDates[0] }}">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control" name="quarterly_amount_1" step="0.01" min="0" value="{{ number_format($quarterlyAmount, 2, '.', '') }}" placeholder="0.00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <div class="card bg-light">
                                                <div class="card-body p-3">
                                                    <h6 class="card-title mb-2">2nd Quarter</h6>
                                                    <input type="date" class="form-control form-control-sm mb-2" name="quarterly_date_2" min="{{ date('Y-m-d') }}" value="{{ $quarterDates[1] }}">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control" name="quarterly_amount_2" step="0.01" min="0" value="{{ number_format($quarterlyAmount, 2, '.', '') }}" placeholder="0.00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <div class="card bg-light">
                                                <div class="card-body p-3">
                                                    <h6 class="card-title mb-2">3rd Quarter</h6>
                                                    <input type="date" class="form-control form-control-sm mb-2" name="quarterly_date_3" min="{{ date('Y-m-d') }}" value="{{ $quarterDates[2] }}">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control" name="quarterly_amount_3" step="0.01" min="0" value="{{ number_format($quarterlyAmount, 2, '.', '') }}" placeholder="0.00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <div class="card bg-light">
                                                <div class="card-body p-3">
                                                    <h6 class="card-title mb-2">4th Quarter</h6>
                                                    <input type="date" class="form-control form-control-sm mb-2" name="quarterly_date_4" min="{{ date('Y-m-d') }}" value="{{ $quarterDates[3] }}">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control" name="quarterly_amount_4" step="0.01" min="0" value="{{ number_format($quarterlyAmount, 2, '.', '') }}" placeholder="0.00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Monthly Payment Breakdown -->
                                <div id="monthly-payment-breakdown" class="payment-breakdown" style="display: none;">
                                    <h6 class="fw-bold text-info mb-3">
                                        <i class="ri-calendar-2-line me-2"></i>Monthly Payment Breakdown
                                    </h6>
                                    <div class="alert alert-info">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><strong>Total Amount:</strong></span>
                                            <span class="fw-bold">₱<span id="monthly-total-amount">0.00</span></span>
                                        </div>
                                        <small class="text-muted">10 payments throughout the academic year (excluding vacation months)</small>
                                    </div>
                                    <div class="row g-2">
                                        @php
                                            $monthlyAmount = $totalAmount / 10;
                                            $monthDates = [
                                                date('Y-m-d', strtotime('+1 month')),
                                                date('Y-m-d', strtotime('+2 months')),
                                                date('Y-m-d', strtotime('+3 months')),
                                                date('Y-m-d', strtotime('+4 months')),
                                                date('Y-m-d', strtotime('+5 months')),
                                                date('Y-m-d', strtotime('+6 months')),
                                                date('Y-m-d', strtotime('+7 months')),
                                                date('Y-m-d', strtotime('+8 months')),
                                                date('Y-m-d', strtotime('+9 months')),
                                                date('Y-m-d', strtotime('+10 months'))
                                            ];
                                        @endphp
                                        @for($i = 1; $i <= 10; $i++)
                                            <div class="col-md-6 col-lg-4 col-xl-3">
                                                <div class="card bg-light">
                                                    <div class="card-body p-2">
                                                        <h6 class="card-title mb-1 small">Payment {{ $i }}</h6>
                                                        <input type="date" class="form-control form-control-sm mb-1" name="monthly_date_{{ $i }}" min="{{ date('Y-m-d') }}" value="{{ $monthDates[$i-1] }}">
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text">₱</span>
                                                            <input type="number" class="form-control" name="monthly_amount_{{ $i }}" step="0.01" min="0" value="{{ number_format($monthlyAmount, 2, '.', '') }}" placeholder="0.00">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                            {{-- Payment method will be determined by cashier when processing payments --}}

                            <!-- Notes Section -->
                            <div class="mt-4">
                                <label class="form-label">Additional Notes (Optional)</label>
                                <textarea class="form-control" name="payment_notes" rows="3" placeholder="Any additional information about your payment schedule..."></textarea>
                            </div>

                            <!-- Debug Button (Remove in production) -->
                           

                            <!-- Action Buttons -->
                            <div class="mt-4">
                                @if($paymentScheduleStatus)
                                    @if($paymentScheduleStatus->confirmation_status === 'pending')
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-secondary btn-lg" disabled>
                                                <i class="ri-time-line me-2"></i>Payment Schedule Pending Approval
                                            </button>
                                            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                                                <i class="ri-arrow-left-line me-2"></i>Back to Dashboard
                                            </a>
                                        </div>
                                    @elseif($paymentScheduleStatus->confirmation_status === 'confirmed')
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('student.payments') }}" class="btn btn-success btn-lg">
                                                <i class="ri-money-dollar-circle-line me-2"></i>Proceed to Payment
                                            </a>
                                            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                                                <i class="ri-arrow-left-line me-2"></i>Back to Dashboard
                                            </a>
                                        </div>
                                    @elseif(in_array($paymentScheduleStatus->confirmation_status, ['rejected', 'declined']))
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary btn-lg" id="enrollBtn">
                                                <i class="ri-send-plane-line me-2"></i>Resubmit Payment Schedule
                                            </button>
                                            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                                                <i class="ri-arrow-left-line me-2"></i>Back to Dashboard
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg" id="enrollBtn">
                                            <i class="ri-send-plane-line me-2"></i>Submit Payment Schedule
                                        </button>
                                        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                                            <i class="ri-arrow-left-line me-2"></i>Back to Dashboard
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Fee Breakdown -->
                <div class="col-lg-4">
                    <!-- Fee Breakdown -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="ri-bill-line me-2"></i>Fee Breakdown
                            </h5>
                        </div>
                        <div class="card-body">
                            @php
                                $feeCalculation = \App\Models\Fee::calculateTotalFeesForGrade($student->grade_level);
                                $fees = $feeCalculation['fees'];
                                $totalAmount = $feeCalculation['total_amount'];
                                $breakdown = $feeCalculation['breakdown'];
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
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0">Total Amount:</h6>
                                        <h5 class="fw-bold text-primary mb-0">₱{{ number_format($totalAmount, 2) }}</h5>
                                    </div>
                                </div>
                                
                                <!-- Payment Schedule Preview -->
                                <div id="payment-schedule" class="mt-4" style="display: none;">
                                    <h6 class="fw-bold mb-3">Payment Schedule</h6>
                                    <div id="schedule-content"></div>
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

                    <!-- Enrollment Summary -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="card-title mb-0">Enrollment Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Student Name</label>
                                <div class="fw-semibold">{{ $student->full_name ?? ($student->first_name . ' ' . $student->last_name) }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Student ID</label>
                                <div class="fw-semibold">{{ $student->student_id }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Grade Level</label>
                                <div class="fw-semibold">{{ $student->grade_level }}</div>
                            </div>
                            @if($student->strand)
                                <div class="mb-3">
                                    <label class="form-label text-muted small">Strand</label>
                                    <div class="fw-semibold">{{ $student->strand }}</div>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label text-muted small">Academic Year</label>
                                <div class="fw-semibold">{{ $student->academic_year ?? '2024-2025' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Total Subjects</label>
                                <div class="fw-semibold">{{ $subjects->count() }} subjects</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>

    @push('scripts')
        @vite('resources/js/student-enrollment.js')
        <script>
            // Global variables
            const totalAmount = {{ $totalAmount ?? 0 }};
            const preferredScheduleDate = '{{ now()->addDays(7)->format('Y-m-d') }}';
            
            console.log('Default schedule date loaded:', preferredScheduleDate);
            
            // Define functions globally FIRST so they can be accessed by onclick handlers
            window.populatePaymentDates = function() {
                console.log('Populating payment dates with preferred schedule:', preferredScheduleDate);
                const baseDate = new Date(preferredScheduleDate);
                
                // Add a small delay to ensure elements are visible
                setTimeout(() => {
                    // Full payment date
                    const fullPaymentDate = document.querySelector('input[name="full_payment_date"]');
                    if (fullPaymentDate) {
                        fullPaymentDate.value = preferredScheduleDate;
                        console.log('Set full payment date to:', preferredScheduleDate);
                    } else {
                        console.log('Full payment date input not found');
                    }
                    
                    // Quarterly payment dates
                    for (let i = 1; i <= 4; i++) {
                        const quarterlyDate = document.querySelector(`input[name="quarterly_date_${i}"]`);
                        if (quarterlyDate) {
                            const date = new Date(baseDate);
                            date.setMonth(date.getMonth() + (i - 1) * 3);
                            const formattedDate = date.toISOString().split('T')[0];
                            quarterlyDate.value = formattedDate;
                            console.log(`Set quarterly date ${i} to:`, formattedDate);
                        } else {
                            console.log(`Quarterly date ${i} input not found`);
                        }
                    }
                    
                    // Monthly payment dates
                    for (let i = 1; i <= 10; i++) {
                        const monthlyDate = document.querySelector(`input[name="monthly_date_${i}"]`);
                        if (monthlyDate) {
                            const date = new Date(baseDate);
                            date.setMonth(date.getMonth() + (i - 1));
                            const formattedDate = date.toISOString().split('T')[0];
                            monthlyDate.value = formattedDate;
                            console.log(`Set monthly date ${i} to:`, formattedDate);
                        } else {
                            console.log(`Monthly date ${i} input not found`);
                        }
                    }
                    
                    // Auto-populate amounts
                window.populatePaymentAmounts();
            }, 100); // 100ms delay to ensure elements are rendered
        }
        
        // Verify function is defined
        console.log('populatePaymentDates function defined:', typeof window.populatePaymentDates);

        window.populatePaymentAmounts = function() {
                // Full payment amount
                const fullPaymentAmount = document.querySelector('input[name="full_payment_amount"]');
                if (fullPaymentAmount) {
                    fullPaymentAmount.value = totalAmount.toFixed(2);
                }
                
                // Quarterly amounts
                const quarterlyAmount = (totalAmount / 4).toFixed(2);
                for (let i = 1; i <= 4; i++) {
                    const quarterlyAmountInput = document.querySelector(`input[name="quarterly_amount_${i}"]`);
                    if (quarterlyAmountInput) {
                        quarterlyAmountInput.value = quarterlyAmount;
                    }
                }
                
                // Monthly amounts
                const monthlyAmount = (totalAmount / 10).toFixed(2);
                for (let i = 1; i <= 10; i++) {
                    const monthlyAmountInput = document.querySelector(`input[name="monthly_amount_${i}"]`);
                    if (monthlyAmountInput) {
                        monthlyAmountInput.value = monthlyAmount;
                    }
                }
                
                // Update display amounts
                document.querySelectorAll('.quarterly-amount').forEach(el => {
                    el.textContent = parseFloat(quarterlyAmount).toLocaleString('en-US', {minimumFractionDigits: 2});
                });
                
                document.querySelectorAll('.monthly-amount').forEach(el => {
                    el.textContent = parseFloat(monthlyAmount).toLocaleString('en-US', {minimumFractionDigits: 2});
                });
                
                // Update breakdown totals
                const fullTotalElement = document.getElementById('full-total-amount');
                const quarterlyTotalElement = document.getElementById('quarterly-total-amount');
                const quarterlyPerPaymentElement = document.getElementById('quarterly-per-payment');
                const monthlyTotalElement = document.getElementById('monthly-total-amount');
                const monthlyPerPaymentElement = document.getElementById('monthly-per-payment');
                
                if (fullTotalElement) fullTotalElement.textContent = totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
                if (quarterlyTotalElement) quarterlyTotalElement.textContent = totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
                if (quarterlyPerPaymentElement) quarterlyPerPaymentElement.textContent = parseFloat(quarterlyAmount).toLocaleString('en-US', {minimumFractionDigits: 2});
                if (monthlyTotalElement) monthlyTotalElement.textContent = totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
                if (monthlyPerPaymentElement) monthlyPerPaymentElement.textContent = parseFloat(monthlyAmount).toLocaleString('en-US', {minimumFractionDigits: 2});
            }

            window.showPaymentScheduleCard = function(mode) {
                // Show the payment schedule card
                const paymentScheduleCard = document.getElementById('payment-schedule-card');
                if (paymentScheduleCard) {
                    paymentScheduleCard.style.display = 'block';
                }
                
                // Hide all payment breakdowns first
                document.querySelectorAll('.payment-breakdown').forEach(breakdown => {
                    breakdown.style.display = 'none';
                });
                
                // Show the selected payment breakdown
                const selectedBreakdown = document.getElementById(`${mode}-payment-breakdown`);
                if (selectedBreakdown) {
                    selectedBreakdown.style.display = 'block';
                }
            }
            
            document.addEventListener('DOMContentLoaded', function() {
                const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
                const scheduleDiv = document.getElementById('payment-schedule');
                const scheduleContent = document.getElementById('schedule-content');
                
                // Auto-populate payment dates based on preferred schedule
                window.populatePaymentDates();
                
                paymentOptions.forEach(option => {
                    option.addEventListener('change', function() {
                        console.log('Payment mode changed to:', this.value);
                        updatePaymentSchedule(this.value);
                        updatePaymentOptionStyles();
                        window.showPaymentScheduleCard(this.value);
                        // Populate dates after showing the card with a longer delay
                        setTimeout(() => {
                            window.populatePaymentDates();
                        }, 200);
                    });
                });
                
                // Initialize with selected option
                const selectedOption = document.querySelector('input[name="payment_method"]:checked');
                if (selectedOption) {
                    updatePaymentSchedule(selectedOption.value);
                    updatePaymentOptionStyles();
                    window.showPaymentScheduleCard(selectedOption.value);
                    window.populatePaymentDates(); // Populate dates on initialization
                }
                
                function updatePaymentSchedule(mode) {
                    if (totalAmount <= 0) return;
                    
                    scheduleDiv.style.display = 'block';
                    let html = '';
                    
                    switch(mode) {
                        case 'full':
                            html = `
                                <div class="alert alert-success">
                                    <strong>Full Payment:</strong> ₱${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}
                                    <br><small>Pay the entire amount upon enrollment</small>
                                </div>
                            `;
                            break;
                        case 'quarterly':
                            const quarterlyAmount = totalAmount / 4;
                            html = `
                                <div class="alert alert-warning">
                                    <strong>Quarterly Payment:</strong> ₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})} per quarter
                                    <br><small>4 payments throughout the academic year</small>
                                </div>
                                <div class="small">
                                    <div class="d-flex justify-content-between py-1">
                                        <span>1st Quarter:</span>
                                        <span>₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span>2nd Quarter:</span>
                                        <span>₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span>3rd Quarter:</span>
                                        <span>₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span>4th Quarter:</span>
                                        <span>₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    </div>
                                </div>
                            `;
                            break;
                        case 'monthly':
                            const monthlyAmount = totalAmount / 10;
                            html = `
                                <div class="alert alert-info">
                                    <strong>Monthly Payment:</strong> ₱${monthlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})} per month
                                    <br><small>10 payments throughout the academic year</small>
                                </div>
                                <div class="small">
                                    <div class="d-flex justify-content-between py-1">
                                        <span>Monthly (10 months):</span>
                                        <span>₱${monthlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    </div>
                                </div>
                            `;
                            break;
                    }
                    
                    scheduleContent.innerHTML = html;
                }
                
                function populatePaymentAmounts() {
                    // Full payment amount
                    const fullPaymentAmount = document.querySelector('input[name="full_payment_amount"]');
                    if (fullPaymentAmount) {
                        fullPaymentAmount.value = totalAmount.toFixed(2);
                    }
                    
                    // Quarterly amounts
                    const quarterlyAmount = (totalAmount / 4).toFixed(2);
                    for (let i = 1; i <= 4; i++) {
                        const quarterlyAmountInput = document.querySelector(`input[name="quarterly_amount_${i}"]`);
                        if (quarterlyAmountInput) {
                            quarterlyAmountInput.value = quarterlyAmount;
                        }
                    }
                    
                    // Monthly amounts
                    const monthlyAmount = (totalAmount / 10).toFixed(2);
                    for (let i = 1; i <= 10; i++) {
                        const monthlyAmountInput = document.querySelector(`input[name="monthly_amount_${i}"]`);
                        if (monthlyAmountInput) {
                            monthlyAmountInput.value = monthlyAmount;
                        }
                    }
                    
                    // Update display amounts
                    document.querySelectorAll('.quarterly-amount').forEach(el => {
                        el.textContent = parseFloat(quarterlyAmount).toLocaleString('en-US', {minimumFractionDigits: 2});
                    });
                    
                    document.querySelectorAll('.monthly-amount').forEach(el => {
                        el.textContent = parseFloat(monthlyAmount).toLocaleString('en-US', {minimumFractionDigits: 2});
                    });
                    
                    // Update breakdown totals
                    document.getElementById('full-total-amount').textContent = totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
                    document.getElementById('quarterly-total-amount').textContent = totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
                    document.getElementById('quarterly-per-payment').textContent = parseFloat(quarterlyAmount).toLocaleString('en-US', {minimumFractionDigits: 2});
                    document.getElementById('monthly-total-amount').textContent = totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
                    document.getElementById('monthly-per-payment').textContent = parseFloat(monthlyAmount).toLocaleString('en-US', {minimumFractionDigits: 2});
                }
                
                function updatePaymentSchedule(mode) {
                    if (totalAmount <= 0) return;
                    
                    scheduleDiv.style.display = 'block';
                    let html = '';
                    
                    switch(mode) {
                        case 'full':
                            html = `
                                <div class="alert alert-success">
                                    <strong>Full Payment:</strong> ₱${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}
                                    <br><small>Pay the entire amount upon enrollment</small>
                                </div>
                            `;
                            break;
                        case 'quarterly':
                            const quarterlyAmount = totalAmount / 4;
                            html = `
                                <div class="alert alert-warning">
                                    <strong>Quarterly Payment:</strong> ₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})} per quarter
                                    <br><small>4 payments throughout the academic year</small>
                                </div>
                                <div class="small">
                                    <div class="d-flex justify-content-between py-1">
                                        <span>1st Quarter:</span>
                                        <span>₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span>2nd Quarter:</span>
                                        <span>₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span>3rd Quarter:</span>
                                        <span>₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span>4th Quarter:</span>
                                        <span>₱${quarterlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    </div>
                                </div>
                            `;
                            break;
                        case 'monthly':
                            const monthlyAmount = totalAmount / 10;
                            html = `
                                <div class="alert alert-info">
                                    <strong>Monthly Payment:</strong> ₱${monthlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})} per month
                                    <br><small>10 payments (excluding vacation months)</small>
                                </div>
                                <div class="small">
                                    <div class="d-flex justify-content-between py-1">
                                        <span>Monthly (10 months):</span>
                                        <span>₱${monthlyAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    </div>
                                </div>
                            `;
                            break;
                    }
                    
                    scheduleContent.innerHTML = html;
                }
                
                function updatePaymentOptionStyles() {
                    document.querySelectorAll('.payment-option').forEach(card => {
                        card.classList.remove('border-primary', 'bg-light');
                    });
                    
                    const selectedInput = document.querySelector('input[name="payment_method"]:checked');
                    if (selectedInput) {
                        const selectedCard = selectedInput.closest('.form-check').querySelector('.payment-option');
                        selectedCard.classList.add('border-primary', 'bg-light');
                    }
                }
            });
        </script>
    @endpush
</x-student-layout>
