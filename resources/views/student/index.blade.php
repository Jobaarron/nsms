<x-student-layout>
    @push('styles')
        @vite('resources/css/index_student.css')
        <style>
            .btn.disabled {
                cursor: not-allowed !important;
                opacity: 0.6;
                pointer-events: none;
            }
            
            .btn.disabled:hover {
                transform: none !important;
                box-shadow: none !important;
            }
            
            .position-relative .ri-lock-line {
                font-size: 1rem;
                color: #6c757d;
            }
        </style>
    @endpush

    <!-- Page Header -->
    <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="section-title mb-1">Student Dashboard</h2>
                        <p class="text-muted mb-0">Welcome back, {{ $student->full_name ?? ($student->first_name . ' ' . $student->last_name) }}</p>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Student ID: <strong>{{ $student->student_id }}</strong></small><br>
                        <small class="text-muted">Academic Year: <strong>{{ $student->academic_year ?? '2024-2025' }}</strong></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollment Status Alert -->
        @if($student->enrollment_status === 'pre_registered')
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-warning border-0 shadow-sm" data-alert-type="enrollment_complete">
                        <div class="d-flex align-items-center">
                            <i class="ri-information-line fs-4 me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-1">Complete Your Enrollment</h6>
                                <p class="mb-2">You have successfully pre-registered! Please complete your enrollment by selecting subjects and payment method.</p>
                                <button class="btn btn-warning btn-sm" onclick="window.location.href='{{ route('student.enrollment') }}'">
                                    <i class="ri-arrow-right-line me-1"></i>Continue Enrollment
                                </button>
                            </div>
                            <button type="button" class="btn-close ms-3" data-dismiss-alert="enrollment_complete" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($student->enrollment_status === 'enrolled')
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-success border-0 shadow-sm" data-alert-type="enrollment_complete">
                        <div class="d-flex align-items-center">
                            <i class="ri-check-line fs-4 me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-1">Enrollment Complete</h6>
                                <p class="mb-0">You are successfully enrolled for Academic Year {{ $student->academic_year ?? '2024-2025' }}.</p>
                            </div>
                            <button type="button" class="btn-close ms-3" data-dismiss-alert="enrollment_complete" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($student->enrollment_status === 'pre_registered' && \App\Models\Payment::where('payable_type', 'App\\Models\\Student')->where('payable_id', $student->id)->exists())
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info border-0 shadow-sm">
                        <div class="d-flex align-items-center">
                            <i class="ri-time-line fs-4 me-3"></i>
                            <div>
                                <h6 class="alert-heading mb-1">Payment Schedule Pending</h6>
                                <p class="mb-0">Your payment schedule has been submitted and is awaiting cashier approval.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Payment Status Alert -->
        @if($student->enrollment_status === 'enrolled')
            @if($student->is_paid)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-success border-0 shadow-sm" data-alert-type="payment_complete">
                            <div class="d-flex align-items-center">
                                <i class="ri-check-double-line fs-4 me-3"></i>
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-1">Payment Complete</h6>
                                    <p class="mb-0">All payments have been completed. Total paid: ₱{{ number_format($student->total_paid ?? 0, 2) }}</p>
                                    @if($student->payment_completed_at)
                                        <small class="text-muted">Completed on: {{ $student->payment_completed_at->format('M d, Y h:i A') }}</small>
                                    @endif
                                </div>
                                <button type="button" class="btn-close ms-3" data-dismiss-alert="payment_complete" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($student->total_paid > 0)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-warning border-0 shadow-sm" data-alert-type="partial_payment">
                            <div class="d-flex align-items-center">
                                <i class="ri-money-dollar-circle-line fs-4 me-3"></i>
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-1">Partial Payment Received</h6>
                                    <p class="mb-0">
                                        Paid: ₱{{ number_format($student->total_paid ?? 0, 2) }} of ₱{{ number_format($student->total_fees_due ?? 0, 2) }}
                                        <br>
                                        <strong>Remaining Balance: ₱{{ number_format(($student->total_fees_due ?? 0) - ($student->total_paid ?? 0), 2) }}</strong>
                                    </p>
                                </div>
                                <button type="button" class="btn-close ms-3" data-dismiss-alert="partial_payment" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- SUMMARY CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-sm-6 col-lg-4">
                <div class="card border-0 shadow-sm card-summary card-credits h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="ri-money-dollar-box-line fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="fw-bold fs-4 mb-0 text-white">₱{{ number_format(($student->total_fees_due ?? 0) - ($student->total_paid ?? 0), 2) }}</h3>
                            <small class="text-white">Balance Due</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-6 col-lg-4">
                <div class="card border-0 shadow-sm card-summary card-subjects h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="ri-book-line fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            @php
                                $subjects = \App\Models\Subject::getSubjectsForStudent(
                                    $student->grade_level,
                                    $student->strand,
                                    $student->track
                                );
                            @endphp
                            <h3 class="fw-bold fs-4 mb-0 text-white">{{ $subjects->count() }}</h3>
                            <small class="text-white">Subjects</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-6 col-lg-4">
                <div class="card border-0 shadow-sm card-summary card-gpa h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="ri-graduation-cap-line fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="fw-bold fs-4 mb-0 text-white">{{ $student->grade_level }}</h3>
                            <small class="text-white">Grade Level</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- QUICK ACTIONS -->
                <h4 class="section-title">Quick Actions</h4>
                @php
                    $hasNoEnrollment = !in_array($student->enrollment_status, ['enrolled', 'pre_registered']);
                    
                    // Check if student has at least one confirmed payment (1st quarter)
                    $hasConfirmedPayment = \App\Models\Payment::where('payable_type', 'App\\Models\\Student')
                        ->where('payable_id', $student->id)
                        ->where('confirmation_status', 'confirmed')
                        ->exists();
                    
                    $hasNoPayment = $student->enrollment_status !== 'enrolled' || !$hasConfirmedPayment;
                    $isEnrollmentComplete = in_array($student->enrollment_status, ['enrolled', 'pre_registered']);
                    $isPaymentSettled = $student->enrollment_status === 'enrolled' && $hasConfirmedPayment;
                @endphp
                
                <!-- Enrollment Status Button -->
                @if($student->enrollment_status === 'pre_registered')
                    @php
                        $hasPaymentSchedule = \App\Models\Payment::where('payable_type', 'App\\Models\\Student')->where('payable_id', $student->id)->exists();
                    @endphp
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            @if(!$hasPaymentSchedule)
                                <a href="{{ route('student.enrollment') }}" class="btn btn-outline-primary w-100 py-3">
                                    <i class="ri-user-add-line fs-4 d-block mb-2"></i>
                                    Complete Enrollment
                                </a>
                            @else
                                <div class="btn btn-outline-warning w-100 py-3 disabled">
                                    <i class="ri-time-line fs-4 d-block mb-2"></i>
                                    Awaiting Approval
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                <!-- Main Quick Actions Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6 col-lg-4">
                        <!-- View Subjects - Disabled only if payment not settled -->
                        @if($hasNoPayment)
                            <div class="btn btn-outline-secondary w-100 py-3 disabled position-relative" title="Complete enrollment and settle payment to access this feature">
                                <i class="ri-book-open-line fs-4 d-block mb-2"></i>
                                View Subjects
                                <i class="ri-lock-line position-absolute top-0 end-0 m-2"></i>
                            </div>
                        @else
                            <a href="{{ route('student.subjects') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="ri-book-open-line fs-4 d-block mb-2"></i>
                                View Subjects
                            </a>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <!-- Payment History - Disabled until payment settled (same as other features) -->
                        @if($hasNoPayment)
                            <div class="btn btn-outline-secondary w-100 py-3 disabled position-relative" title="Complete enrollment and settle payment to access this feature">
                                <i class="ri-bill-line fs-4 d-block mb-2"></i>
                                Payment History
                                <i class="ri-lock-line position-absolute top-0 end-0 m-2"></i>
                            </div>
                        @else
                            <a href="{{ route('student.payments') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="ri-bill-line fs-4 d-block mb-2"></i>
                                Payment History
                            </a>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <!-- Face Registration - Disabled only if payment not settled -->
                        @if($hasNoPayment)
                            <div class="btn btn-outline-secondary w-100 py-3 disabled position-relative" title="Complete enrollment and settle payment to access this feature">
                                <i class="ri-camera-line fs-4 d-block mb-2"></i>
                                Face Registration
                                <i class="ri-lock-line position-absolute top-0 end-0 m-2"></i>
                            </div>
                        @else
                            <a href="{{ route('student.face-registration') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="ri-camera-line fs-4 d-block mb-2"></i>
                                Face Registration
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-6 col-sm-6 col-lg-4">
                <!-- Student Profile Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center">
                        @if($student->id_photo_data_url)
                            <img src="{{ $student->id_photo_data_url }}" alt="Student Photo" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 100px; height: 100px;">
                                <i class="ri-user-line fs-1 text-muted"></i>
                            </div>
                        @endif
                        <h6 class="fw-bold mb-1">{{ $student->full_name ?? ($student->first_name . ' ' . $student->last_name) }}</h6>
                        <p class="text-muted small mb-2">{{ $student->student_id }}</p>
                        <span class="badge bg-{{ $student->enrollment_status === 'enrolled' ? 'success' : 'warning' }} mb-3">
                            {{ ucfirst(str_replace('_', ' ', $student->enrollment_status)) }}
                        </span>
                        <div class="text-start">
                            <small class="text-muted d-block">Email: {{ $student->email }}</small>
                            <small class="text-muted d-block">Contact: {{ $student->contact_number }}</small>
                            @if($student->lrn)
                                <small class="text-muted d-block">LRN: {{ $student->lrn }}</small>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Enrollment Progress -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h6 class="card-title mb-0">Enrollment Progress</h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item {{ $student->pre_registered_at ? 'completed' : '' }}">
                                <h6 class="mb-1 {{ $student->pre_registered_at ? 'text-success' : 'text-muted' }}">
                                    <i class="ri-check-line me-1"></i>Pre-Registration
                                </h6>
                                <small class="text-muted">
                                    {{ $student->pre_registered_at ? $student->pre_registered_at->format('M d, Y') : 'Pending' }}
                                </small>
                            </div>
                            @php
                                $hasPaymentSchedule = \App\Models\Payment::where('payable_type', 'App\\Models\\Student')->where('payable_id', $student->id)->exists();
                                $enrollmentCompleted = $student->enrollment_status === 'enrolled';
                                $enrollmentInProgress = $student->enrollment_status === 'pre_registered' && $hasPaymentSchedule;
                                $isRejected = $student->enrollment_status === 'rejected';
                            @endphp
                            <div class="timeline-item {{ $enrollmentCompleted ? 'completed' : ($enrollmentInProgress ? 'active' : ($isRejected ? 'rejected' : '')) }}">
                                <h6 class="mb-1 {{ $enrollmentCompleted ? 'text-success' : ($enrollmentInProgress ? 'text-warning' : ($isRejected ? 'text-danger' : 'text-muted')) }}">
                                    @if($isRejected)
                                        <i class="ri-close-line me-1"></i>Decision
                                    @else
                                        <i class="ri-user-add-line me-1"></i>Complete Enrollment
                                    @endif
                                </h6>
                                <small class="text-muted">
                                    @if($enrollmentCompleted)
                                        Completed
                                    @elseif($enrollmentInProgress)
                                        Awaiting Payment Approval
                                    @elseif($isRejected)
                                        Application Rejected
                                    @else
                                        Pending
                                    @endif
                                </small>
                            </div>
                            <div class="timeline-item {{ $student->is_paid ? 'completed' : '' }}">
                                <h6 class="mb-1 {{ $student->is_paid ? 'text-success' : 'text-muted' }}">
                                    <i class="ri-money-dollar-circle-line me-1"></i>Payment
                                </h6>
                                <small class="text-muted">
                                    {{ $student->is_paid ? 'Paid' : 'Pending Payment' }}
                                </small>
                            </div>
                            <div class="timeline-item {{ $student->section ? 'completed' : '' }}">
                                <h6 class="mb-1 {{ $student->section ? 'text-success' : 'text-muted' }}">
                                    <i class="ri-group-line me-1"></i>Class Assignment
                                </h6>
                                <small class="text-muted">
                                    @if($student->section)
                                        @php
                                            $timelineClass = $student->grade_level . ' - ' . $student->section;
                                            if ($student->strand) {
                                                $timelineClass = $student->grade_level . ' - ' . $student->section . ' - ' . $student->strand;
                                                if ($student->track) {
                                                    $timelineClass = $student->grade_level . ' - ' . $student->section . ' - ' . $student->strand . ' - ' . $student->track;
                                                }
                                            }
                                        @endphp
                                        {{ $timelineClass }}
                                    @else
                                        To be assigned
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @push('scripts')
        @vite('resources/js/student-dashboard.js')
    @endpush
</x-student-layout>