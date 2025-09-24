<x-enrollee-layout>
    <div class="py-4">
        <h1 class="section-title">Welcome, {{ $enrollee->first_name ?? 'Enrollee' }}</h1>
        
        @if($enrollee->enrollment_status === 'pending')
            <div class="alert alert-enrollee d-flex align-items-center mb-4">
                <i class="ri-time-line me-2"></i>
                <div>
                    <strong>Application Under Review!</strong> 
                    Your enrollment application is being processed. Current status: <strong>{{ ucfirst($enrollee->enrollment_status) }}</strong>
                </div>
            </div>
        @elseif($enrollee->enrollment_status === 'approved' && !$enrollee->is_paid)
            <div class="alert alert-warning d-flex align-items-center mb-4">
                <i class="ri-money-dollar-circle-line me-2"></i>
                <div>
                    <strong>Payment Required!</strong> 
                    Your application has been approved. Please complete your payment to proceed with enrollment.
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
            <div class="col-6 col-lg-3">
                <div class="card card-summary card-payment h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="ri-money-dollar-circle-line display-6 me-3"></i>
                        <div>
                            <div>Payment Status</div>
                            <h5>{{ $enrollee->is_paid ? 'Paid' : 'Pending' }}</h5>
                        </div>
                    </div>
                </div>
            </div>
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
                    <div class="progress-step {{ in_array($enrollee->enrollment_status, ['pending', 'approved', 'enrolled', 'rejected']) ? 'completed' : '' }}">
                        <i class="ri-file-text-line"></i>
                    </div>
                    @php
                        // Check if application has been evaluated (documents reviewed)
                        $documents = is_array($enrollee->documents) ? $enrollee->documents : [];
                        $hasVerifiedDocs = false;
                        $hasRejectedDocs = false;
                        
                        foreach ($documents as $document) {
                            if (is_array($document)) {
                                $status = $document['status'] ?? 'pending';
                                if (in_array($status, ['verified', 'rejected'])) {
                                    $hasVerifiedDocs = true;
                                    break;
                                }
                            }
                        }
                        
                        // Application is evaluated if:
                        // 1. Status is approved/enrolled/rejected OR
                        // 2. At least one document has been reviewed (verified/rejected)
                        $isEvaluated = in_array($enrollee->enrollment_status, ['approved', 'enrolled', 'rejected']) || $hasVerifiedDocs;
                        
                        // Only show as "evaluating" (active) if documents are actually being reviewed
                        // Don't show active for fresh applications that haven't been touched yet
                        $isEvaluating = false; // Never show as active until actual evaluation starts
                    @endphp
                    <div class="progress-step {{ $isEvaluated ? 'completed' : ($isEvaluating ? 'active' : '') }}">
                        <i class="ri-search-eye-line"></i>
                    </div>
                    <div class="progress-step {{ in_array($enrollee->enrollment_status, ['approved', 'enrolled']) ? 'completed' : '' }}">
                        <i class="ri-check-double-line"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <small class="text-muted">Application</small>
                    <small class="text-muted">Evaluated</small>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
        </div>

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
                                <p class="text-muted mb-0">Your application has been reviewed and approved by {{ $enrollee->approvedBy->name ?? 'Admin' }}.</p>
                            </div>
                            <small class="text-muted">{{ $enrollee->approved_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($enrollee->payment_date)
                    <div class="timeline-item completed">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Payment Completed</h6>
                                <p class="text-muted mb-0">Enrollment fee of ₱{{ number_format($enrollee->enrollment_fee, 2) }} has been paid.</p>
                                @if($enrollee->payment_reference)
                                    <small class="text-muted">Reference: {{ $enrollee->payment_reference }}</small>
                                @endif
                            </div>
                            <small class="text-muted">{{ $enrollee->payment_date->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @endif
                    
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
                @if($enrollee->enrollment_status === 'approved' && !$enrollee->is_paid)
                    <a href="{{ route('enrollee.payment') }}" class="btn btn-primary w-100 py-3">
                        <i class="ri-money-dollar-circle-line me-2"></i>Make Payment
                    </a>
                @else
                    <a href="{{ route('enrollee.payment') }}" class="btn btn-outline-primary w-100 py-3">
                        <i class="ri-money-dollar-circle-line me-2"></i>Payment Details
                    </a>
                @endif
            </div>
            <div class="col-md-4">
                <a href="{{ route('enrollee.documents') }}" class="btn btn-outline-primary w-100 py-3">
                    <i class="ri-folder-line me-2"></i>My Documents
                </a>
            </div>
        </div>
    </div>
</x-enrollee-layout>
