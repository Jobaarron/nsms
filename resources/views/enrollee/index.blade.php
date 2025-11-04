<x-enrollee-layout>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/enrollee-index.js'])
    <div class="py-4">
        <h1 class="section-title">Welcome, {{ $enrollee->first_name ?? 'Applicant' }}</h1>
        
        @if($enrollee->enrollment_status === 'pending')
            <div class="alert alert-warning d-flex align-items-center mb-4">
                <i class="ri-time-line me-2"></i>
                <div>
                    <strong>Application Under Review!</strong> 
                    Your enrollment application is being processed. Current status: <strong>{{ ucfirst($enrollee->enrollment_status) }}</strong>
                </div>
            </div>
        @elseif($enrollee->enrollment_status === 'approved')
            <div class="alert alert-success d-flex align-items-center mb-4">
                <i class="ri-check-circle-line me-2"></i>
                <div>
                    <strong>Application Approved!</strong> 
                    Congratulations! Your application has been approved. You can now pre-register to access the student portal for enrollment and payment processing.
                </div>
            </div>
        @elseif($enrollee->enrollment_status === 'enrolled')
            <div class="alert alert-success d-flex align-items-center mb-4">
                <i class="ri-checkbox-circle-line me-2"></i>
                <div>
                    <strong>Successfully Enrolled!</strong> 
                    Welcome to Nicolites Montessori School! You can now access all student features.
                </div>
            </div>
        @elseif($enrollee->enrollment_status === 'rejected')
            <div class="alert alert-danger d-flex align-items-center mb-4">
                <i class="ri-close-circle-line me-2"></i>
                <div>
                    <strong>Application Rejected</strong> 
                    @if($enrollee->status_reason)
                        <br><small>Reason: {{ $enrollee->status_reason }}</small>
                    @endif
                </div>
            </div>
        @endif

        <!-- SUMMARY CARDS -->
        <div class="row g-3 mb-5">
            <div class="col-6 col-lg-3">
                <div class="card card-summary card-application h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="ri-file-text-line display-6 me-3"></i>
                        <div>
                            <div>Application ID</div>
                            <h5>{{ $enrollee->application_id }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card card-summary card-status h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="ri-flag-line display-6 me-3"></i>
                        <div>
                            <div>Status</div>
                            <h5>{{ ucfirst($enrollee->enrollment_status) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            {{-- <div class="col-6 col-lg-3">
                <div class="card card-summary card-payment h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="ri-money-dollar-circle-line display-6 me-3"></i>
                        <div>
                            <div>Payment Status</div>
                            <h5>{{ $enrollee->is_paid ? 'Paid' : 'Pending' }}</h5>
                        </div>
                    </div>
                </div>
            </div> --}}
            <div class="col-6 col-lg-3">
                <div class="card card-summary card-schedule h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="ri-calendar-line display-6 me-3"></i>
                        <div>
                            <div>Grade Level</div>
                            <h5>{{ $enrollee->grade_level_applied }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- APPLICATION PROGRESS -->
        <h4 class="section-title">Application Progress</h4>
        <div class="card mb-5">
            <div class="card-body">
                <div class="progress-steps">
                    <!-- Step 1: Application Submitted -->
                    <div class="progress-step completed">
                        <i class="ri-file-text-line"></i>
                    </div>
                    
                    @php
                        // Use new evaluation tracking fields
                        $hasBeenViewed = $enrollee->hasBeenViewed();
                        $isBeingEvaluated = $enrollee->isBeingEvaluated();
                        $hasBeenEvaluated = $enrollee->hasBeenEvaluated();
                        
                        // Application is evaluated if registrar has completed evaluation OR final status reached
                        $isEvaluated = $hasBeenEvaluated || in_array($enrollee->enrollment_status, ['approved', 'enrolled', 'rejected']);
                        
                        // Show as "evaluating" (active) if registrar has started evaluation but not completed
                        $isEvaluating = $isBeingEvaluated && !$isEvaluated;
                    @endphp
                    
                    <!-- Step 2: Under Evaluation -->
                    <div class="progress-step {{ $isEvaluated ? 'completed' : ($isEvaluating ? 'active' : '') }}">
                        <i class="ri-search-eye-line"></i>
                    </div>
                    
                    <!-- Step 3: Approved by Registrar -->
                    <div class="progress-step {{ in_array($enrollee->enrollment_status, ['approved', 'enrolled']) ? 'completed' : (in_array($enrollee->enrollment_status, ['rejected', 'declined']) ? 'rejected' : '') }}">
                        <i class="ri-check-double-line"></i>
                    </div>
                    
                    <!-- Step 4: Pre-Register -->
                    <div class="progress-step {{ $enrollee->student_id ? 'completed' : ($enrollee->enrollment_status === 'approved' ? 'active' : '') }}">
                        <i class="ri-user-add-line"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <small class="text-muted">Submitted</small>
                    <small class="text-muted">Under Review</small>
                    <small class="text-muted">
                        @if(in_array($enrollee->enrollment_status, ['rejected', 'declined']))
                            Decision
                        @else
                            Approved
                        @endif
                    </small>
                    <small class="text-muted">Pre-Register</small>
                </div>
            </div>
        </div>

        <!-- DEBUG INFO (Remove this after checking) -->
        {{-- <div class="alert alert-info">
            <strong>Debug Payment Info:</strong><br>
            payment_date: {{ $enrollee->payment_date ?? 'NULL' }}<br>
            payment_completed_at: {{ $enrollee->payment_completed_at ?? 'NULL' }}<br>
            is_paid: {{ $enrollee->is_paid ? 'TRUE' : 'FALSE' }}<br>
            enrollment_fee: {{ $enrollee->enrollment_fee ?? 'NULL' }}<br>
            total_paid: {{ $enrollee->total_paid ?? 'NULL' }}<br>
            payment_reference: {{ $enrollee->payment_reference ?? 'NULL' }}
        </div> --}}

        <!-- APPLICATION TIMELINE -->
        <h4 class="section-title">Application Timeline</h4>
        <div class="card mb-5">
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item completed">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Application Submitted</h6>
                                <p class="text-muted mb-0">Your enrollment application has been submitted successfully.</p>
                            </div>
                            <small class="text-muted">{{ $enrollee->application_date->format('M d, Y') }}</small>
                        </div>
                    </div>
                    
                    @if($enrollee->approved_at)
                    <div class="timeline-item completed">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Application Approved</h6>
                                <p class="text-muted mb-0">Your application has been reviewed and approved by {{ $enrollee->approvedBy->name ?? 'Admin' }}. You can now pre-register to access the student portal.</p>
                            </div>
                            <small class="text-muted">{{ $enrollee->approved_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    {{-- Payment timeline removed - now handled in student portal --}}
                    
                    @if($enrollee->enrolled_at)
                    <div class="timeline-item completed">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Successfully Enrolled</h6>
                                <p class="text-muted mb-0">Welcome to NSMS! Your enrollment is now complete.</p>
                            </div>
                            <small class="text-muted">{{ $enrollee->enrolled_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($enrollee->enrollment_status === 'approved' && !$enrollee->student_id)
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-primary">Ready for Pre-Registration</h6>
                                <p class="text-muted mb-0">Application approved! Complete your pre-registration to access the student portal for enrollment and payment processing.</p>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="preRegisterStudent()" id="preRegisterBtn">
                                <i class="ri-user-add-line me-2"></i>Pre-Register Now
                            </button>
                        </div>
                    </div>
                    @endif
                    
                    @if($enrollee->student_id)
                    <div class="timeline-item completed">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Pre-Registration Complete</h6>
                                <p class="text-muted mb-0">Student ID: <strong>{{ $enrollee->student_id }}</strong></p>
                                <small class="text-success">You can now access the student portal for enrollment and payment processing.</small>
                                <br>
                                <small class="text-success">You may also access your email address and check your credentials if "Show Credentials" takes too long.</small>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="showCredentialsModal('{{ $enrollee->student_id }}', '{{ $enrollee->application_id }}')">
                                        <i class="ri-key-line me-1"></i>View Credentials
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">{{ $enrollee->updated_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($enrollee->rejected_at)
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 text-danger">Application Rejected</h6>
                                <p class="text-muted mb-0">{{ $enrollee->status_reason ?? 'Your application has been rejected.' }}</p>
                            </div>
                            <small class="text-muted">{{ $enrollee->rejected_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <h4 class="section-title">Quick Actions</h4>
        <div class="row g-3">
            <div class="col-md-4">
                <a href="{{ route('enrollee.application') }}" class="btn btn-outline-primary w-100 py-3">
                    <i class="ri-file-text-line me-2"></i>View Application
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('enrollee.documents') }}" class="btn btn-outline-primary w-100 py-3">
                    <i class="ri-folder-line me-2"></i>My Documents
                </a>
            </div>
        </div>
    </div>

    @vite(['resources/js/enrollee-index.js'])

    @if(session('new_student_credentials'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const credentials = @json(session('new_student_credentials'));
                if (credentials && credentials.show_modal) {
                    // Show the credentials modal automatically
                    showCredentialsModal(credentials.student_id, credentials.password);
                }
            });
        </script>
    @endif
</x-enrollee-layout>
