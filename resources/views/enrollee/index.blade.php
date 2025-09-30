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
                                <p class="text-muted mb-0">Your application has been reviewed and approved by {{ $enrollee->approvedBy->name ?? 'Admin' }}.</p>
                            </div>
                            <small class="text-muted">{{ $enrollee->approved_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($enrollee->payment_date || $enrollee->payment_completed_at || $enrollee->is_paid)
                    <div class="timeline-item completed">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Payment Completed</h6>
                                <p class="text-muted mb-0">Enrollment fee of ₱{{ number_format($enrollee->enrollment_fee ?? $enrollee->total_paid ?? 4500, 2) }} has been paid.</p>
                                @if($enrollee->payment_reference)
                                    <small class="text-muted">Reference: {{ $enrollee->payment_reference }}</small>
                                @endif
                            </div>
                            <small class="text-muted">{{ ($enrollee->payment_date ?? $enrollee->payment_completed_at ?? $enrollee->updated_at)->format('M d, Y') }}</small>
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
                    
                    @if(($enrollee->payment_date || $enrollee->payment_completed_at || $enrollee->is_paid) && !$enrollee->student_id)
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-primary">Ready for Pre-Registration</h6>
                                <p class="text-muted mb-0">Entrance fee paid! Complete your pre-registration to access student portal and subjects.</p>
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
                                <small class="text-success">You can now access the student portal with your credentials.</small>
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

    <script>
        // Pre-register student function
        function preRegisterStudent() {
            const btn = document.getElementById('preRegisterBtn');
            
            // Show confirmation modal
            if (!confirm('Are you ready to complete your pre-registration? This will create your student account and generate your Student ID.')) {
                return;
            }
            
            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<i class="ri-loader-4-line me-2 spinner-border spinner-border-sm"></i>Processing...';
            
            // Make AJAX request to pre-register
            fetch('/enrollee/pre-register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message with credentials
                    showCredentialsModal(data.student_id, data.password);
                    
                    // Update the timeline immediately without page reload
                    updateTimelineAfterPreRegistration(data.student_id);
                } else {
                    // Show error message
                    alert(data.message || 'Pre-registration failed. Please try again.');
                    
                    // Re-enable button
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ri-user-add-line me-2"></i>Pre-Register Now';
                }
            })
            .catch(error => {
                console.error('Pre-registration error:', error);
                alert('An error occurred during pre-registration. Please try again.');
                
                // Re-enable button
                btn.disabled = false;
                btn.innerHTML = '<i class="ri-user-add-line me-2"></i>Pre-Register Now';
            });
        }

        // Show credentials modal
        function showCredentialsModal(studentId, password) {
            // Create modal HTML
            const modalHtml = `
                <div class="modal fade" id="credentialsModal" tabindex="-1" aria-labelledby="credentialsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title" id="credentialsModalLabel">
                                    <i class="ri-check-circle-line me-2"></i>Pre-registration Successful!
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <div class="mb-4">
                                    <i class="ri-user-add-line text-success" style="font-size: 3rem;"></i>
                                </div>
                                <h6 class="mb-3">Your Student Account Has Been Created!</h6>
                                <div class="alert alert-info">
                                    <div class="row">
                                        <div class="col-12 mb-2">
                                            <strong>Student ID:</strong>
                                            <div class="input-group mt-1">
                                                <input type="text" class="form-control text-center fw-bold" value="${studentId}" readonly id="studentIdField">
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('studentIdField')">
                                                    <i class="ri-file-copy-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <strong>Password:</strong>
                                            <div class="input-group mt-1">
                                                <input type="text" class="form-control text-center fw-bold" value="${password}" readonly id="passwordField">
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('passwordField')">
                                                    <i class="ri-file-copy-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-warning">
                                    <small><i class="ri-information-line me-1"></i>Please save these credentials safely. You can change your password after logging into the student portal.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                                    <i class="ri-check-line me-1"></i>Got It!
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('credentialsModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('credentialsModal'));
            modal.show();
        }

        // Update timeline after pre-registration
        function updateTimelineAfterPreRegistration(studentId) {
            // Find the pre-register timeline item and replace it
            const preRegisterItem = document.querySelector('#preRegisterBtn').closest('.timeline-item');
            if (preRegisterItem) {
                // Create the completed timeline item
                const completedItem = document.createElement('div');
                completedItem.className = 'timeline-item completed';
                completedItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">Pre-Registration Complete</h6>
                            <p class="text-muted mb-0">Student ID: <strong>${studentId}</strong></p>
                            <small class="text-success">You can now access the student portal with your credentials.</small>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="showCredentialsModal('${studentId}', '{{ $enrollee->application_id }}')">
                                    <i class="ri-key-line me-1"></i>View Credentials
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Just now</small>
                    </div>
                `;
                
                // Replace the pre-register item with the completed item
                preRegisterItem.parentNode.replaceChild(completedItem, preRegisterItem);
            }
        }

        // Copy to clipboard function
        function copyToClipboard(fieldId) {
            const field = document.getElementById(fieldId);
            field.select();
            field.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                alert('Copied to clipboard!');
            } catch (err) {
                alert('Failed to copy to clipboard');
            }
        }
    </script>
</x-enrollee-layout>
