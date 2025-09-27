<!-- Application Details Modal -->
<div class="modal fade" id="applicationDetailsModal" tabindex="-1" aria-labelledby="applicationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applicationDetailsModalLabel">
                    <i class="ri-file-text-line me-2"></i>Application Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Application Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Personal Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Full Name:</strong> <span id="app-full-name"></span></p>
                                        <p><strong>Date of Birth:</strong> <span id="app-dob"></span></p>
                                        <p><strong>Gender:</strong> <span id="app-gender"></span></p>
                                        <p><strong>Email:</strong> <span id="app-email"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Contact:</strong> <span id="app-contact"></span></p>
                                        <p><strong>Address:</strong> <span id="app-address"></span></p>
                                        <p><strong>Grade Applied:</strong> <span id="app-grade"></span></p>
                                        <p><strong>Student Type:</strong> <span id="app-type"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Guardian Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Guardian Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Father:</strong> <span id="app-father"></span></p>
                                        <p><strong>Mother:</strong> <span id="app-mother"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Guardian:</strong> <span id="app-guardian"></span></p>
                                        <p><strong>Guardian Contact:</strong> <span id="app-guardian-contact"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- ID Photo -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">ID Photo</h6>
                            </div>
                            <div class="card-body text-center">
                                <img id="app-id-photo" src="" alt="ID Photo" class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                        </div>

                        <!-- Status Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Application Status</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Current Status</label>
                                    <span id="app-current-status" class="badge fs-6"></span>
                                </div>
                                <div class="mb-3">
                                    <label for="status-select" class="form-label">Change Status</label>
                                    <select class="form-select" id="status-select">
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                        <option value="enrolled">Enrolled</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="status-reason" class="form-label">Reason/Notes</label>
                                    <textarea class="form-control" id="status-reason" rows="3" placeholder="Enter reason for status change..."></textarea>
                                </div>
                                <button type="button" class="btn btn-primary w-100" onclick="updateApplicationStatus()">
                                    <i class="ri-save-line me-1"></i>Update Status
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Review Modal -->
<div class="modal fade" id="documentReviewModal" tabindex="-1" aria-labelledby="documentReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentReviewModalLabel">
                    <i class="ri-folder-line me-2"></i>Document Review
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Document Viewer -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0" id="doc-title">Document Preview</h6>
                            </div>
                            <div class="card-body text-center">
                                <div id="document-viewer">
                                    <!-- Document content will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Document Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Document Information</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Application ID:</strong> <span id="doc-app-id"></span></p>
                                <p><strong>Student Name:</strong> <span id="doc-student-name"></span></p>
                                <p><strong>Document Type:</strong> <span id="doc-type"></span></p>
                                <p><strong>Upload Date:</strong> <span id="doc-upload-date"></span></p>
                                <p><strong>File Size:</strong> <span id="doc-file-size"></span></p>
                            </div>
                        </div>

                        <!-- Review Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Review Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Current Status</label>
                                    <span id="doc-current-status" class="badge fs-6"></span>
                                </div>
                                <div class="mb-3">
                                    <label for="doc-status-select" class="form-label">Change Status</label>
                                    <select class="form-select" id="doc-status-select">
                                        <option value="pending">Pending Review</option>
                                        <option value="verified">Verified</option>
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
                                    <button type="button" class="btn btn-primary" onclick="updateDocumentStatus()">
                                        <i class="ri-save-line me-1"></i>Update Status
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Review Modal -->
<div class="modal fade" id="appointmentReviewModal" tabindex="-1" aria-labelledby="appointmentReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appointmentReviewModalLabel">
                    <i class="ri-calendar-line me-2"></i>Appointment Review
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Appointment Details -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Appointment Details</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Application ID:</strong> <span id="appt-app-id"></span></p>
                                <p><strong>Student Name:</strong> <span id="appt-student-name"></span></p>
                                <p><strong>Requested Date:</strong> <span id="appt-requested-date"></span></p>
                                <p><strong>Requested Time:</strong> <span id="appt-requested-time"></span></p>
                                <p><strong>Purpose:</strong> <span id="appt-purpose"></span></p>
                                <p><strong>Contact Number:</strong> <span id="appt-contact"></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Appointment Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Appointment Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Current Status</label>
                                    <span id="appt-current-status" class="badge fs-6"></span>
                                </div>
                                <div class="mb-3">
                                    <label for="appt-status-select" class="form-label">Change Status</label>
                                    <select class="form-select" id="appt-status-select">
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="appt-approved-date" class="form-label">Approved Date</label>
                                    <input type="date" class="form-control" id="appt-approved-date">
                                </div>
                                <div class="mb-3">
                                    <label for="appt-approved-time" class="form-label">Approved Time</label>
                                    <input type="time" class="form-control" id="appt-approved-time">
                                </div>
                                <div class="mb-3">
                                    <label for="appt-notes" class="form-label">Admin Notes</label>
                                    <textarea class="form-control" id="appt-notes" rows="3" placeholder="Enter appointment notes..."></textarea>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success" onclick="approveAppointment()">
                                        <i class="ri-check-line me-1"></i>Approve
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="rejectAppointment()">
                                        <i class="ri-close-line me-1"></i>Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Notice Modal -->
<div class="modal fade" id="createNoticeModal" tabindex="-1" aria-labelledby="createNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createNoticeModalLabel">
                    <i class="ri-notification-line me-2"></i>Create Notice
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                <button type="button" class="btn btn-primary" onclick="sendNotice()">
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
            <div class="modal-header">
                <h5 class="modal-title" id="bulkNoticeModalLabel">
                    <i class="ri-mail-send-line me-2"></i>Send Bulk Notice
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                    <option value="rejected">Rejected</option>
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
                        <textarea class="form-control" id="bulk-notice-message" rows="5" required placeholder="Enter your bulk notice message..."></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="bulk-notice-email">
                            <label class="form-check-label" for="bulk-notice-email">
                                Also send via email
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendBulkNotice()">
                    <i class="ri-send-plane-line me-1"></i>Send Bulk Notice
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

<!-- Decline Application Modal -->
<div class="modal fade" id="declineApplicationModal" tabindex="-1" aria-labelledby="declineApplicationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="declineApplicationModalLabel">
                    <i class="ri-close-line me-2"></i>Decline Application
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <p><strong>Are you sure you want to decline this application?</strong></p>
                    <p class="text-muted">Application ID: <span id="decline-app-id"></span></p>
                    <p class="text-muted">Student Name: <span id="decline-student-name"></span></p>
                </div>
                <div class="mb-3">
                    <label for="decline-reason" class="form-label">Reason for Decline <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="decline-reason" rows="3" required placeholder="Please provide a reason for declining this application..."></textarea>
                    <div class="invalid-feedback">
                        Please provide a reason for declining this application.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmDeclineApplication()">
                    <i class="ri-close-line me-1"></i>Decline Application
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Application Modal -->
<div class="modal fade" id="deleteApplicationModal" tabindex="-1" aria-labelledby="deleteApplicationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteApplicationModalLabel">
                    <i class="ri-delete-bin-line me-2"></i>Delete Application
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                <div class="mb-3">
                    <p><strong>Are you sure you want to permanently delete this application?</strong></p>
                    <p class="text-muted">Application ID: <span id="delete-app-id"></span></p>
                    <p class="text-muted">Student Name: <span id="delete-student-name"></span></p>
                </div>
                <div class="mb-3">
                    <p class="text-danger">This will also delete:</p>
                    <ul class="text-danger">
                        <li>All associated documents</li>
                        <li>All notices sent to this applicant</li>
                        <li>All application history</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteApplication()">
                    <i class="ri-delete-bin-line me-1"></i>Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="bulk-modal-header">
                <h5 class="modal-title" id="bulkActionModalLabel">
                    <i class="ri-checkbox-multiple-line me-2"></i>Bulk Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <p><strong id="bulk-action-message">Are you sure you want to perform this action?</strong></p>
                    <p class="text-muted">Selected applications: <span id="bulk-selected-count">0</span></p>
                </div>
                <div class="mb-3" id="bulk-reason-container" style="display: none;">
                    <label for="bulk-reason" class="form-label" id="bulk-reason-label">Reason</label>
                    <textarea class="form-control" id="bulk-reason" rows="3" placeholder="Enter reason..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="bulk-confirm-btn" onclick="confirmBulkAction()">
                    <i class="ri-check-line me-1"></i>Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Documents Modal -->
<div class="modal fade" id="documentsModal" tabindex="-1" aria-labelledby="documentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentsModalLabel">
                    <i class="ri-folder-line me-2"></i>Application Documents
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="documents-modal-content">
                    <!-- Documents will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Notice Modal -->
<div class="modal fade" id="bulkNoticeModal" tabindex="-1" aria-labelledby="bulkNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkNoticeModalLabel">
                    <i class="ri-mail-send-line me-2"></i>Send Bulk Notice
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Send notice to <span id="bulk-notice-count">0</span> selected applications.</p>
                <div class="mb-3">
                    <label for="bulk-notice-title" class="form-label">Notice Title</label>
                    <input type="text" class="form-control" id="bulk-notice-title" placeholder="Enter notice title...">
                </div>
                <div class="mb-3">
                    <label for="bulk-notice-message" class="form-label">Message</label>
                    <textarea class="form-control" id="bulk-notice-message" rows="4" placeholder="Enter notice message..."></textarea>
                </div>
                <div class="mb-3">
                    <label for="bulk-notice-priority" class="form-label">Priority</label>
                    <select class="form-select" id="bulk-notice-priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendBulkNotice()">
                    <i class="ri-send-plane-line me-1"></i>Send Notice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Notice Modal -->
<div class="modal fade" id="createNoticeModal" tabindex="-1" aria-labelledby="createNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createNoticeModalLabel">
                    <i class="ri-notification-line me-2"></i>Create Notice
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="create-notice-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="notice-title" class="form-label">Notice Title</label>
                        <input type="text" class="form-control" id="notice-title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="notice-message" class="form-label">Message</label>
                        <textarea class="form-control" id="notice-message" name="message" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="notice-priority" class="form-label">Priority</label>
                            <select class="form-select" id="notice-priority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="notice-type" class="form-label">Notice Type</label>
                            <select class="form-select" id="notice-type" name="type">
                                <option value="individual">Individual</option>
                                <option value="global">Global</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3" id="target-filters" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="target-status" class="form-label">Target Status</label>
                                <select class="form-select" id="target-status" name="target_status">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="target-grade" class="form-label">Target Grade</label>
                                <select class="form-select" id="target-grade" name="target_grade_level">
                                    <option value="">All Grades</option>
                                    <option value="Grade 7">Grade 7</option>
                                    <option value="Grade 8">Grade 8</option>
                                    <option value="Grade 9">Grade 9</option>
                                    <option value="Grade 10">Grade 10</option>
                                    <option value="Grade 11">Grade 11</option>
                                    <option value="Grade 12">Grade 12</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i>Create Notice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0">Processing request...</p>
            </div>
        </div>
    </div>
</div>
