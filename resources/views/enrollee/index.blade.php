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
    
    <!-- Main Content -->
    <main class="container-fluid px-3 px-md-4 py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <h1 class="section-title mb-2 mb-md-0">Welcome, {{ $enrollee->first_name ?? 'Applicant' }}</h1>
            <div class="text-muted">
                <i class="ri-user-add-line me-1"></i>Application Portal
            </div>
        </div>
        
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
                <div class="flex-grow-1">
                    <strong>Application Rejected</strong> 
                    @if($enrollee->status_reason)
                        <br><small>Reason: {{ $enrollee->status_reason }}</small>
                    @endif
                    @if($enrollee->canSubmitAppeal())
                        <br><small class="text-info">You can submit an appeal to reconsider your application.</small>
                    @endif
                </div>
                @if($enrollee->canSubmitAppeal())
                <div class="ms-3">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#appealModal">
                        <i class="ri-file-edit-line me-1"></i>Appeal Decision
                    </button>
                </div>
                @elseif($enrollee->getLatestAppeal())
                <div class="ms-3">
                    <span class="badge bg-info">
                        Appeal {{ ucfirst($enrollee->getLatestAppeal()->status) }}
                    </span>
                </div>
                @endif
            </div>
        @endif

        <!-- SUMMARY CARDS -->
        <div class="row g-3 mb-5">
            <div class="col-6 col-sm-6 col-lg-3">
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
                            <h5>{{ $enrollee->enrollment_status === 'rejected_appeal' ? 'Rejected/Appeal' : ucfirst(str_replace('_', ' ', $enrollee->enrollment_status)) }}</h5>
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
                    
                    <!-- Step 3: Decision by Registrar -->
                    <div class="progress-step {{ in_array($enrollee->enrollment_status, ['approved', 'enrolled']) ? 'completed' : (in_array($enrollee->enrollment_status, ['rejected', 'declined']) ? 'rejected' : '') }}">
                        @if(in_array($enrollee->enrollment_status, ['rejected', 'declined']))
                            <i class="ri-close-line"></i>
                        @else
                            <i class="ri-check-double-line"></i>
                        @endif
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
                                @if($enrollee->canSubmitAppeal())
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#appealModal">
                                            <i class="ri-file-edit-line me-1"></i>Submit Appeal
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <small class="text-muted">{{ $enrollee->rejected_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @endif

                    @if($enrollee->getLatestAppeal())
                    @php $latestAppeal = $enrollee->getLatestAppeal(); @endphp
                    <div class="timeline-item {{ $latestAppeal->status === 'approved' ? 'completed' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 text-{{ $latestAppeal->status === 'approved' ? 'success' : ($latestAppeal->status === 'rejected' ? 'danger' : 'info') }}">
                                    Appeal {{ ucfirst($latestAppeal->status) }}
                                </h6>
                                <p class="text-muted mb-0">
                                    @if($latestAppeal->status === 'pending')
                                        Your appeal has been submitted and is waiting for review.
                                    @elseif($latestAppeal->status === 'under_review')
                                        Your appeal is currently being reviewed by the registrar.
                                    @elseif($latestAppeal->status === 'approved')
                                        Your appeal has been approved and your application is being reconsidered.
                                    @elseif($latestAppeal->status === 'rejected')
                                        Your appeal has been rejected. {{ $latestAppeal->admin_notes }}
                                    @endif
                                </p>
                                @if($latestAppeal->admin_notes && $latestAppeal->status === 'approved')
                                    <small class="text-success">{{ $latestAppeal->admin_notes }}</small>
                                @endif
                            </div>
                            <small class="text-muted">{{ $latestAppeal->submitted_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <h4 class="section-title">Quick Actions</h4>
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-lg-6">
                <a href="{{ route('enrollee.application') }}" class="btn btn-outline-primary w-100 py-3">
                    <i class="ri-file-text-line me-2"></i>View Application
                </a>
            </div>
            <div class="col-12 col-sm-6 col-lg-6">
                <a href="{{ route('enrollee.documents') }}" class="btn btn-outline-primary w-100 py-3">
                    <i class="ri-folder-line me-2"></i>My Documents
                </a>
            </div>
        </div>
    </main>

    <!-- Appeal Modal -->
    @if($enrollee->enrollment_status === 'rejected' && $enrollee->canSubmitAppeal())
    <div class="modal fade" id="appealModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-file-edit-line me-2"></i>
                        Submit Appeal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="appealForm" method="POST" action="{{ route('enrollee.appeals.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <i class="ri-error-warning-line me-2"></i>
                                <strong>Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Appeal Your Application Decision</strong><br>
                            If you believe your application was rejected in error or if you have additional information that might change the decision, you can submit an appeal. Please provide a detailed explanation and any supporting documents.
                        </div>

                        <!-- Application Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Application Information</h6>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4">Application ID:</dt>
                                    <dd class="col-sm-8">{{ $enrollee->application_id }}</dd>
                                    <dt class="col-sm-4">Applicant Name:</dt>
                                    <dd class="col-sm-8">{{ $enrollee->full_name }}</dd>
                                    <dt class="col-sm-4">Grade Level Applied:</dt>
                                    <dd class="col-sm-8">{{ $enrollee->grade_level_applied }}</dd>
                                    <dt class="col-sm-4">Rejection Reason:</dt>
                                    <dd class="col-sm-8">{{ $enrollee->status_reason ?? 'Not specified' }}</dd>
                                </dl>
                            </div>
                        </div>

                        <!-- Appeal Reason -->
                        <div class="mb-4">
                            <label for="appeal_reason" class="form-label">
                                <strong>Reason for Appeal</strong> <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                class="form-control" 
                                id="appeal_reason" 
                                name="reason" 
                                rows="5" 
                                required 
                                minlength="50"
                                maxlength="2000"
                                placeholder="Please provide a detailed explanation of why you believe the rejection decision should be reconsidered. Include any additional information, circumstances, or corrections that might affect the decision."
                            ></textarea>
                            <div class="form-text">
                                <small class="text-muted">
                                    <span id="appeal-char-count">0</span>/2000 characters (minimum 50 required)
                                </small><br>
                                Be specific about what aspects of the decision you're appealing and provide any new information or circumstances that weren't considered in the original application.
                            </div>
                        </div>

                        <!-- Supporting Documents -->
                        <div class="mb-4">
                            <label for="appeal_documents" class="form-label">
                                <strong>Supporting Documents</strong> (Optional)
                            </label>
                            <input 
                                type="file" 
                                class="form-control" 
                                id="appeal_documents" 
                                name="appeal_documents[]" 
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" 
                                multiple
                            >
                            <div class="form-text">
                                Upload any additional documents that support your appeal (certificates, transcripts, medical records, etc.). 
                                Accepted formats: PDF, JPG, PNG, DOC, DOCX. Maximum size: 5MB per file.
                            </div>
                            <div id="appeal-file-preview" class="mt-2" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="ri-file-line me-2"></i>
                                    <span id="appeal-file-count"></span>
                                    <div id="appeal-file-list" class="mt-2"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Declaration -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="appeal_declaration" name="declaration" required>
                                <label class="form-check-label" for="appeal_declaration">
                                    I declare that all information provided in this appeal is true and accurate to the best of my knowledge. I understand that providing false information may result in the permanent rejection of my application.
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submit-appeal">
                            <i class="ri-send-plane-line me-1"></i>Submit Appeal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @vite(['resources/js/enrollee-index.js'])

    <script>
        // Handle appeal form file preview and character counting
        document.addEventListener('DOMContentLoaded', function() {
            const appealFileInput = document.getElementById('appeal_documents');
            const appealFilePreview = document.getElementById('appeal-file-preview');
            const appealFileCount = document.getElementById('appeal-file-count');
            const appealFileList = document.getElementById('appeal-file-list');
            const appealReasonTextarea = document.getElementById('appeal_reason');
            const appealCharCount = document.getElementById('appeal-char-count');

            if (appealFileInput) {
                appealFileInput.addEventListener('change', function() {
                    const files = this.files;
                    if (files.length > 0) {
                        appealFileCount.textContent = `${files.length} file(s) selected`;
                        
                        let fileListHTML = '';
                        for (let i = 0; i < files.length; i++) {
                            const file = files[i];
                            const fileSize = (file.size / 1024 / 1024).toFixed(2);
                            fileListHTML += `<small class="d-block"><i class="ri-file-line me-1"></i>${file.name} (${fileSize} MB)</small>`;
                        }
                        appealFileList.innerHTML = fileListHTML;
                        appealFilePreview.style.display = 'block';
                    } else {
                        appealFilePreview.style.display = 'none';
                    }
                });
            }

            // Handle character counting for appeal reason
            if (appealReasonTextarea && appealCharCount) {
                function updateCharCount() {
                    const length = appealReasonTextarea.value.length;
                    appealCharCount.textContent = length;
                    
                    // Change color based on requirements
                    if (length < 50) {
                        appealCharCount.style.color = '#dc3545'; // Red
                    } else if (length > 1900) {
                        appealCharCount.style.color = '#ffc107'; // Yellow warning
                    } else {
                        appealCharCount.style.color = '#28a745'; // Green
                    }
                }
                
                appealReasonTextarea.addEventListener('input', updateCharCount);
                appealReasonTextarea.addEventListener('paste', function() {
                    setTimeout(updateCharCount, 0);
                });
                
                // Initial count
                updateCharCount();
            }

            // Handle appeal form submission validation
            const appealForm = document.getElementById('appealForm');
            if (appealForm) {
                appealForm.addEventListener('submit', function(e) {
                    const reason = appealReasonTextarea.value.trim();
                    const declaration = document.getElementById('appeal_declaration').checked;
                    
                    if (reason.length < 50) {
                        e.preventDefault();
                        alert('Please provide a reason for your appeal with at least 50 characters.');
                        appealReasonTextarea.focus();
                        return false;
                    }
                    
                    if (!declaration) {
                        e.preventDefault();
                        alert('Please accept the declaration to continue.');
                        document.getElementById('appeal_declaration').focus();
                        return false;
                    }
                    
                    // Show loading state
                    const submitBtn = appealForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="ri-loader-4-line spin me-2"></i>Submitting...';
                    }
                });
            }
        });
        
        // Reopen appeal modal if there are errors
        @if($errors->any() && old('reason'))
            const appealModal = new bootstrap.Modal(document.getElementById('appealModal'));
            appealModal.show();
            
            // Restore form data
            document.getElementById('appeal_reason').value = @json(old('reason'));
            if (document.getElementById('appeal_declaration') && @json(old('declaration'))) {
                document.getElementById('appeal_declaration').checked = true;
            }
        @endif
    </script>

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
