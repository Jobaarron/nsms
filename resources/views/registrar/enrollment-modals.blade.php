<!-- Application Details Modal -->
<div class="modal fade" id="applicationDetailsModal" tabindex="-1" aria-labelledby="applicationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                <h5 class="modal-title" id="applicationDetailsModalLabel">
                    <i class="ri-file-text-line me-2"></i>Application Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Application Details -->
                        <div class="card mb-3">
                            <div class="card-header" style="background-color: var(--light-green); color: var(--dark-green);">
                                <h6 class="mb-0"><i class="ri-file-text-line me-2"></i>Application Details</h6>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4">Application ID</dt>
                                    <dd class="col-sm-8" id="app-application-id"></dd>
                                    <dt class="col-sm-4">Application Date</dt>
                                    <dd class="col-sm-8" id="app-application-date"></dd>
                                    <dt class="col-sm-4">Academic Year</dt>
                                    <dd class="col-sm-8" id="app-academic-year"></dd>
                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8"><span id="app-current-status" class="badge"></span></dd>
                                    <dt class="col-sm-4">LRN</dt>
                                    <dd class="col-sm-8" id="app-lrn"></dd>
                                </dl>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="card mb-3">
                            <div class="card-header" style="background-color: var(--light-green); color: var(--dark-green);">
                                <h6 class="mb-0"><i class="ri-user-line me-2"></i>Personal Information</h6>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4">Full Name</dt>
                                    <dd class="col-sm-8" id="app-full-name"></dd>
                                    <dt class="col-sm-4">Date of Birth</dt>
                                    <dd class="col-sm-8" id="app-dob"></dd>
                                    <dt class="col-sm-4">Gender</dt>
                                    <dd class="col-sm-8" id="app-gender"></dd>
                                    <dt class="col-sm-4">Nationality</dt>
                                    <dd class="col-sm-8" id="app-nationality"></dd>
                                    <dt class="col-sm-4">Religion</dt>
                                    <dd class="col-sm-8" id="app-religion"></dd>
                                    <dt class="col-sm-4">Student Type</dt>
                                    <dd class="col-sm-8" id="app-type"></dd>
                                </dl>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="card mb-3">
                            <div class="card-header" style="background-color: var(--light-green); color: var(--dark-green);">
                                <h6 class="mb-0"><i class="ri-phone-line me-2"></i>Contact Information</h6>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4">Email Address</dt>
                                    <dd class="col-sm-8">
                                        <i class="ri-mail-line me-2"></i>
                                        <span id="app-email"></span>
                                    </dd>
                                    <dt class="col-sm-4">Contact Number</dt>
                                    <dd class="col-sm-8">
                                        <i class="ri-phone-line me-2"></i>
                                        <span id="app-contact"></span>
                                    </dd>
                                    <dt class="col-sm-4">Address</dt>
                                    <dd class="col-sm-8">
                                        <i class="ri-map-pin-line me-2"></i>
                                        <span id="app-address"></span>
                                        <br>
                                        <small class="text-muted" id="app-address-details"></small>
                                    </dd>
                                </dl>
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <div class="card mb-3">
                            <div class="card-header" style="background-color: var(--light-green); color: var(--dark-green);">
                                <h6 class="mb-0"><i class="ri-book-line me-2"></i>Academic Information</h6>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4">Grade Level Applied</dt>
                                    <dd class="col-sm-8" id="app-grade"></dd>
                                    <dt class="col-sm-4">Strand Applied</dt>
                                    <dd class="col-sm-8" id="app-strand"></dd>
                                    <dt class="col-sm-4">Track Applied</dt>
                                    <dd class="col-sm-8" id="app-track"></dd>
                                    <dt class="col-sm-4">Last School</dt>
                                    <dd class="col-sm-8" id="app-last-school"></dd>
                                </dl>
                            </div>
                        </div>

                        <!-- Parent/Guardian Information -->
                        <div class="card mb-3">
                            <div class="card-header" style="background-color: var(--light-green); color: var(--dark-green);">
                                <h6 class="mb-0"><i class="ri-parent-line me-2"></i>Parent/Guardian Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Father's Information</h6>
                                        <dl class="row">
                                            <dt class="col-sm-5">Name</dt>
                                            <dd class="col-sm-7" id="app-father-name"></dd>
                                            <dt class="col-sm-5">Occupation</dt>
                                            <dd class="col-sm-7" id="app-father-occupation"></dd>
                                            <dt class="col-sm-5">Contact</dt>
                                            <dd class="col-sm-7" id="app-father-contact"></dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Mother's Information</h6>
                                        <dl class="row">
                                            <dt class="col-sm-5">Name</dt>
                                            <dd class="col-sm-7" id="app-mother-name"></dd>
                                            <dt class="col-sm-5">Occupation</dt>
                                            <dd class="col-sm-7" id="app-mother-occupation"></dd>
                                            <dt class="col-sm-5">Contact</dt>
                                            <dd class="col-sm-7" id="app-mother-contact"></dd>
                                        </dl>
                                    </div>
                                </div>
                                <hr>
                                <h6 class="text-muted">Primary Guardian</h6>
                                <dl class="row">
                                    <dt class="col-sm-3">Name</dt>
                                    <dd class="col-sm-9" id="app-guardian"></dd>
                                    <dt class="col-sm-3">Contact</dt>
                                    <dd class="col-sm-9" id="app-guardian-contact"></dd>
                                </dl>
                            </div>
                        </div>

                        <!-- Medical Information -->
                        <div class="card mb-3" id="medical-info-card" style="display: none;">
                            <div class="card-header" style="background-color: var(--light-green); color: var(--dark-green);">
                                <h6 class="mb-0"><i class="ri-heart-pulse-line me-2"></i>Medical Information</h6>
                            </div>
                            <div class="card-body">
                                <p id="app-medical-history"></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- ID Photo -->
                        <div class="card mb-3">
                            <div class="card-header" style="background-color: var(--light-green); color: var(--dark-green);">
                                <h6 class="mb-0"><i class="ri-image-line me-2"></i>ID Photo</h6>
                            </div>
                            <div class="card-body text-center">
                                <img id="app-id-photo" src="" alt="ID Photo" class="img-fluid rounded" style="max-height: 200px;">
                                <div id="no-photo-placeholder" class="text-muted" style="display: none;">
                                    <i class="ri-image-line display-4"></i>
                                    <p class="mt-2">No ID photo uploaded</p>
                                </div>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div class="card mb-3">
                            <div class="card-header" style="background-color: var(--light-green); color: var(--dark-green);">
                                <h6 class="mb-0"><i class="ri-folder-line me-2"></i>Documents</h6>
                            </div>
                            <div class="card-body">
                                <div id="documents-info">
                                    <div class="text-center" id="documents-summary">
                                        <i class="ri-file-list-line display-6 text-success"></i>
                                        <p class="text-success mt-2 mb-1">
                                            <strong><span id="documents-count">0</span> Document(s) Uploaded</strong>
                                        </p>
                                        <small class="text-muted">Click to view documents</small>
                                    </div>
                                    <div id="documents-list" class="mt-3">
                                        <!-- Documents will be populated here -->
                                    </div>
                                </div>
                                <div id="no-documents" class="text-center text-muted" style="display: none;">
                                    <i class="ri-file-line display-6"></i>
                                    <p class="mt-2">No documents uploaded</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        {{-- <div class="card">
                            <div class="card-header" style="background-color: var(--light-green); color: var(--dark-green);">
                                <h6 class="mb-0"><i class="ri-settings-line me-2"></i>Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success btn-sm" onclick="approveApplicationFromModal()">
                                        <i class="ri-check-line me-1"></i>Approve Application
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="declineApplicationFromModal()">
                                        <i class="ri-close-line me-1"></i>Decline Application
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm" onclick="scheduleAppointment()">
                                        <i class="ri-calendar-line me-1"></i>Schedule Appointment
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="sendNotice()">
                                        <i class="ri-notification-line me-1"></i>Send Notice
                                    </button>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Viewer Modal -->
<div class="modal fade" id="documentViewerModal" tabindex="-1" aria-labelledby="documentViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--secondary-color); color: white;">
                <h5 class="modal-title" id="documentViewerModalLabel">
                    <i class="ri-folder-line me-2"></i>Document Viewer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="document-content">
                    <!-- Document content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-registrar" onclick="downloadDocument()">
                    <i class="ri-download-line me-1"></i>Download
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Scheduling Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--accent-color); color: white;">
                <h5 class="modal-title" id="appointmentModalLabel">
                    <i class="ri-calendar-line me-2"></i>Schedule Appointment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="appointment-form">
                    <div class="mb-3">
                        <label for="appointment-date" class="form-label">Appointment Date</label>
                        <input type="date" class="form-control" id="appointment-date" required>
                    </div>
                    <div class="mb-3">
                        <label for="appointment-time" class="form-label">Appointment Time</label>
                        <input type="time" class="form-control" id="appointment-time" required>
                    </div>
                    <div class="mb-3">
                        <label for="appointment-purpose" class="form-label">Purpose</label>
                        <select class="form-select" id="appointment-purpose" required>
                            <option value="">Select Purpose</option>
                            <option value="document_verification">Document Verification</option>
                            <option value="interview">Interview</option>
                            <option value="enrollment_completion">Enrollment Completion</option>
                            <option value="payment_discussion">Payment Discussion</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="appointment-notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="appointment-notes" rows="3" placeholder="Additional notes or instructions..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-registrar" onclick="saveAppointment()">
                    <i class="ri-save-line me-1"></i>Schedule Appointment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Decline Application Modal -->
<div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="declineModalLabel">
                    <i class="ri-close-circle-line me-2"></i>Decline Application
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="ri-alert-line me-2"></i>
                    Are you sure you want to decline this application? This action cannot be undone.
                </div>
                <form id="decline-form">
                    <div class="mb-3">
                        <label for="decline-reason" class="form-label">Reason for Decline <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="decline-reason" rows="4" placeholder="Please provide a detailed reason for declining this application..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="notify-applicant" checked>
                            <label class="form-check-label" for="notify-applicant">
                                Send notification email to applicant
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmDecline()">
                    <i class="ri-close-line me-1"></i>Decline Application
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Confirmation Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                <h5 class="modal-title" id="bulkActionModalLabel">
                    <i class="ri-checkbox-multiple-line me-2"></i>Bulk Action Confirmation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    You are about to perform a bulk action on <span id="bulk-count">0</span> selected applications.
                </div>
                <div id="bulk-action-content">
                    <!-- Bulk action specific content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-registrar" id="confirm-bulk-action">
                    <i class="ri-check-line me-1"></i>Confirm Action
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Notice Modal -->
<div class="modal fade" id="createNoticeModal" tabindex="-1" aria-labelledby="createNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                <h5 class="modal-title" id="createNoticeModalLabel">
                    <i class="ri-notification-line me-2"></i>Create Notice
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="create-notice-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="notice-title" class="form-label">Notice Title</label>
                                <input type="text" class="form-control" id="notice-title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="notice-priority" class="form-label">Priority</label>
                                <select class="form-select" id="notice-priority" required>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notice-recipients" class="form-label">Recipients</label>
                        <select class="form-select" id="notice-recipients" required>
                            <option value="">Select Recipients</option>
                            <option value="all">All Applicants</option>
                            <option value="pending">Pending Applications</option>
                            <option value="approved">Approved Applications</option>
                            <option value="specific">Specific Applicant</option>
                        </select>
                    </div>

                    <div class="mb-3" id="specific-applicant-div" style="display: none;">
                        <label for="specific-applicant" class="form-label">Select Applicant</label>
                        <select class="form-select" id="specific-applicant">
                            <option value="">Choose applicant...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="notice-message" class="form-label">Message</label>
                        <textarea class="form-control" id="notice-message" rows="5" required placeholder="Enter your notice message..."></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="notice-email">
                            <label class="form-check-label" for="notice-email">
                                Also send via email
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-registrar" onclick="sendNotice()">
                    <i class="ri-send-plane-line me-1"></i>Send Notice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Notice Modal -->
<div class="modal fade" id="bulkNoticeModal" tabindex="-1" aria-labelledby="bulkNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                <h5 class="modal-title" id="bulkNoticeModalLabel">
                    <i class="ri-mail-send-line me-2"></i>Send Bulk Notice
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="bulk-notice-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bulk-notice-title" class="form-label">Notice Title</label>
                                <input type="text" class="form-control" id="bulk-notice-title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bulk-notice-priority" class="form-label">Priority</label>
                                <select class="form-select" id="bulk-notice-priority" required>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Filter Recipients</label>
                        <div class="row">
                            <div class="col-md-4">
                                <select class="form-select" id="bulk-status-filter">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="declined">Declined</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="bulk-grade-filter">
                                    <option value="">All Grades</option>
                                    <option value="Grade 7">Grade 7</option>
                                    <option value="Grade 8">Grade 8</option>
                                    <option value="Grade 9">Grade 9</option>
                                    <option value="Grade 10">Grade 10</option>
                                    <option value="Grade 11">Grade 11</option>
                                    <option value="Grade 12">Grade 12</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-info w-100" onclick="previewRecipients()">
                                    <i class="ri-eye-line me-1"></i>Preview Recipients
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Recipients Preview</label>
                        <div class="border rounded p-2" style="min-height: 60px; max-height: 120px; overflow-y: auto;">
                            <div id="recipients-preview" class="text-muted">
                                Click "Preview Recipients" to see who will receive this notice
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bulk-notice-message" class="form-label">Message</label>
                        <textarea class="form-control" id="bulk-notice-message" rows="4" required placeholder="Enter your notice message here..."></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        <strong>Notice:</strong> This notice will be sent to selected applicants through the internal notification system.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-registrar" onclick="sendBulkNotice()">
                    <i class="ri-send-plane-line me-1"></i>Send Notice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Application Modal -->
<div class="modal fade" id="approveApplicationModal" tabindex="-1" aria-labelledby="approveApplicationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveApplicationModalLabel">
                    <i class="ri-check-line me-2"></i>Approve Application
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <p><strong>Are you sure you want to approve this application?</strong></p>
                    <p class="text-muted">Application ID: <span id="approve-app-id"></span></p>
                    <p class="text-muted">Student Name: <span id="approve-student-name"></span></p>
                </div>
                <div class="mb-3">
                    <label for="approve-reason" class="form-label">Approval Notes (Optional)</label>
                    <textarea class="form-control" id="approve-reason" rows="3" placeholder="Enter any notes for this approval..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmApproveApplication()">
                    <i class="ri-check-line me-1"></i>Approve Application
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Documents Modal -->
<div class="modal fade" id="documentsModal" tabindex="-1" aria-labelledby="documentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--secondary-color); color: white;">
                <h5 class="modal-title" id="documentsModalLabel">
                    <i class="ri-folder-line me-2"></i>Application Documents
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div id="documents-modal-content">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Document Type</th>
                                            <th>Filename</th>
                                            <th>Status</th>
                                            <th>Upload Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="documents-table-body">
                                        <!-- Documents will be populated here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="documents-empty" class="text-center py-4" style="display: none;">
                            <i class="ri-file-line fs-1 text-muted d-block mb-2"></i>
                            <p class="text-muted">No documents uploaded</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Document Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="doc-status-select" class="form-label">Change Status</label>
                                    <select class="form-select" id="doc-status-select">
                                        <option value="pending">Pending Review</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="doc-review-notes" class="form-label">Review Notes</label>
                                    <textarea class="form-control" id="doc-review-notes" rows="3" placeholder="Enter review notes..."></textarea>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success" onclick="approveDocument()">
                                        <i class="ri-check-line me-1"></i>Approve
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="rejectDocument()">
                                        <i class="ri-close-line me-1"></i>Reject
                                    </button>
                                    <button type="button" class="btn btn-registrar" onclick="updateDocumentStatus()">
                                        <i class="ri-save-line me-1"></i>Update Status
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="loading-spinner mx-auto mb-3"></div>
                <p class="mb-0">Processing...</p>
            </div>
        </div>
    </div>
</div>
