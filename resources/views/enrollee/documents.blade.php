<x-enrollee-layout>
    @vite(['resources/js/enrollee-documents.js'])
    @vite(['resources/css/enrollee-documents.css'])
    
    <script>
        // Pass Laravel routes to JavaScript
        window.enrolleeRoutes = {
            deleteDocument: '{{ route('enrollee.documents.delete') }}',
            viewDocument: '{{ url('enrollee/documents/view') }}',
            downloadDocument: '{{ url('enrollee/documents/download') }}'
        };
    </script>
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="section-title">My Documents</h1>
            <div>
                <span class="badge bg-info">
                    {{ is_array($enrollee->documents) ? count($enrollee->documents) : 0 }} Documents
                </span>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- DOCUMENT REQUIREMENTS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-file-list-line me-2"></i>
                            Required Documents
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            Please ensure all required documents are submitted for your enrollment application.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Primary Documents</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Birth Certificate (PSA)
                                        <span class="badge bg-danger">Required</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Report Card/Form 138
                                        <span class="badge bg-danger">Required</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Good Moral Certificate
                                        <span class="badge bg-danger">Required</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        2x2 ID Photo
                                        <span class="badge bg-danger">Required</span>
                                    </li>
                                </ul>
                            </div>
                            {{-- <div class="col-md-6">
                                <h6 class="text-muted mb-3">Additional Documents</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Medical Certificate
                                        <span class="badge bg-warning">Optional</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Transfer Certificate
                                        <span class="badge bg-secondary">If Transferee</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Barangay Clearance
                                        <span class="badge bg-warning">Optional</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Parent's ID Copy
                                        <span class="badge bg-warning">Optional</span>
                                    </li>
                                </ul>
                            </div> To be use soon--}}
                        </div>
                    </div>
                </div>

                <!-- UPLOADED DOCUMENTS -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-folder-line me-2"></i>
                            Uploaded Documents
                        </h5>
                        @php
                            // Check if any documents have been rejected (requiring resubmission)
                            $hasRejectedDocs = false;
                            $documents = is_array($enrollee->documents) ? $enrollee->documents : [];
                            foreach ($documents as $document) {
                                if (is_array($document) && ($document['status'] ?? 'pending') === 'rejected') {
                                    $hasRejectedDocs = true;
                                    break;
                                }
                            }
                            
                            // Allow upload only if:
                            // 1. Application is pending AND no documents uploaded yet (initial submission)
                            // 2. Has rejected documents (resubmission required)
                            // 3. Admin specifically requested additional documents (could be indicated by admin_notes)
                            $canUpload = ($enrollee->enrollment_status === 'pending' && count($documents) === 0) || 
                                        $hasRejectedDocs || 
                                        (str_contains(strtolower($enrollee->admin_notes ?? ''), 'resubmit') || 
                                         str_contains(strtolower($enrollee->admin_notes ?? ''), 'additional document'));
                        @endphp
                        
                        @if($canUpload)
                        <button class="btn btn-primary btn-sm" id="uploadBtn" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="ri-upload-line me-1"></i>
                            @if($hasRejectedDocs)
                                Resubmit Document
                            @else
                                Upload Document
                            @endif
                        </button>
                        @elseif($enrollee->enrollment_status === 'pending' && count($documents) > 0)
                        <span class="badge bg-info">
                            <i class="ri-time-line me-1"></i>
                            Under Review
                        </span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(is_array($enrollee->documents) && count($enrollee->documents) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Document Type</th>
                                            <th>File Name</th>
                                            <th>Upload Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach((is_array($enrollee->documents) ? $enrollee->documents : []) as $index => $document)
                                        @php
                                            // Handle both old format (string paths) and new format (arrays)
                                            if (is_string($document)) {
                                                $documentPath = $document;
                                                $filename = basename($documentPath);
                                                $extension = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
                                                $documentData = [
                                                    'type' => $extension ?: 'Unknown',
                                                    'filename' => $filename,
                                                    'path' => $documentPath,
                                                    'status' => 'pending',
                                                    'uploaded_at' => $enrollee->created_at
                                                ];
                                            } else {
                                                $documentData = $document;
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <i class="ri-file-text-line me-2"></i>
                                                {{ $documentData['type'] ?? 'Unknown' }}
                                            </td>
                                            <td>{{ $documentData['filename'] ?? 'Document ' . ($index + 1) }}</td>
                                            <td>{{ isset($documentData['uploaded_at']) ? \Carbon\Carbon::parse($documentData['uploaded_at'])->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ ($documentData['status'] ?? 'pending') === 'verified' ? 'success' : (($documentData['status'] ?? 'pending') === 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($documentData['status'] ?? 'pending') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary view-doc-btn" 
                                                            data-index="{{ $index }}" 
                                                            data-filename="{{ $documentData['filename'] ?? 'Document ' . ($index + 1) }}" 
                                                            data-path="{{ $documentData['path'] ?? '' }}" 
                                                            data-type="{{ $documentData['type'] ?? 'Unknown' }}" 
                                                            title="View Document">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <button class="btn btn-outline-success download-doc-btn" 
                                                            data-index="{{ $index }}" 
                                                            data-filename="{{ $documentData['filename'] ?? 'Document ' . ($index + 1) }}" 
                                                            data-path="{{ $documentData['path'] ?? '' }}" 
                                                            title="Download Document">
                                                        <i class="ri-download-line"></i>
                                                    </button>
                                                    @if($enrollee->enrollment_status === 'pending')
                                                    <button class="btn btn-outline-danger delete-doc-btn" 
                                                            data-index="{{ $index }}" 
                                                            title="Delete Document">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ri-folder-open-line display-4 text-muted"></i>
                                <p class="text-muted mt-2">No documents uploaded yet.</p>
                                @if($enrollee->enrollment_status === 'pending')
                                <button class="btn btn-primary" id="uploadFirstBtn" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                    <i class="ri-upload-line me-1"></i>
                                    Upload Your First Document
                                </button>
                                @else
                                <p class="text-muted">
                                    <i class="ri-information-line me-1"></i>
                                    Document upload will be available when evaluation requires additional documents.
                                </p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- DOCUMENT VERIFICATION STATUS -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-shield-check-line me-2"></i>
                            Document Verification Status
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $documents = is_array($enrollee->documents) ? $enrollee->documents : [];
                            $totalDocs = count($documents);
                            
                            // Handle both old format (strings) and new format (arrays) for statistics
                            $verifiedDocs = 0;
                            $pendingDocs = 0;
                            $rejectedDocs = 0;
                            
                            foreach ($documents as $document) {
                                if (is_string($document)) {
                                    // Old format: all documents are pending
                                    $pendingDocs++;
                                } else {
                                    // New format: check actual status
                                    $status = $document['status'] ?? 'pending';
                                    if ($status === 'verified') {
                                        $verifiedDocs++;
                                    } elseif ($status === 'rejected') {
                                        $rejectedDocs++;
                                    } else {
                                        $pendingDocs++;
                                    }
                                }
                            }
                        @endphp

                        <div class="row text-center">
                            <div class="col-3">
                                <div class="info-card">
                                    <h4 class="text-primary">{{ $totalDocs }}</h4>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="info-card">
                                    <h4 class="text-success">{{ $verifiedDocs }}</h4>
                                    <small class="text-muted">Verified</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="info-card">
                                    <h4 class="text-warning">{{ $pendingDocs }}</h4>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="info-card">
                                    <h4 class="text-danger">{{ $rejectedDocs }}</h4>
                                    <small class="text-muted">Rejected</small>
                                </div>
                            </div>
                        </div>

                        @if($totalDocs > 0)
                        <div class="mt-3">
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: {{ $totalDocs > 0 ? ($verifiedDocs / $totalDocs) * 100 : 0 }}%"></div>
                            </div>
                            <small class="text-muted">{{ $totalDocs > 0 ? round(($verifiedDocs / $totalDocs) * 100) : 0 }}% of documents verified</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-4">
                <!-- ID PHOTO -->
                {{-- @if($enrollee->hasIdPhoto())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-image-line me-2"></i>
                            ID Photo
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ $enrollee->id_photo_data_url }}" alt="ID Photo" class="img-fluid rounded mb-3" style="max-height: 200px;">
                        <div>
                            <span class="badge bg-success">
                                <i class="ri-check-line me-1"></i>
                                Uploaded
                            </span>
                        </div>
                    </div>
                </div>
                @else
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-image-line me-2"></i>
                            ID Photo
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="document-upload">
                            <i class="ri-image-add-line display-4 text-muted"></i>
                            <p class="text-muted mt-2">No ID photo uploaded</p>
                            @if($enrollee->enrollment_status === 'pending')
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="ri-upload-line me-1"></i>
                                Upload Photo
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endif --}}

                <!-- DOCUMENT GUIDELINES -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-information-line me-2"></i>
                            Upload Guidelines
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="ri-check-line text-success me-2"></i>
                                <small>Maximum file size: 5MB</small>
                            </li>
                            <li class="mb-2">
                                <i class="ri-check-line text-success me-2"></i>
                                <small>Accepted formats: PDF, JPG, PNG</small>
                            </li>
                            <li class="mb-2">
                                <i class="ri-check-line text-success me-2"></i>
                                <small>Clear and readable scans</small>
                            </li>
                            <li class="mb-2">
                                <i class="ri-check-line text-success me-2"></i>
                                <small>Original documents preferred</small>
                            </li>
                            <li>
                                <i class="ri-check-line text-success me-2"></i>
                                <small>Proper document orientation</small>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- HELP & SUPPORT -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-customer-service-line me-2"></i>
                            Need Help?
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Having trouble with document upload?</p>
                        <div class="d-grid gap-2">
                            <a href="tel:+1234567890" class="btn btn-outline-primary btn-sm">
                                <i class="ri-phone-line me-1"></i>
                                Call Support
                            </a>
                            <a href="mailto:documents@nsms.edu" class="btn btn-outline-primary btn-sm">
                                <i class="ri-mail-line me-1"></i>
                                Email Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-upload-line me-2"></i>
                        Upload Document
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="uploadForm" method="POST" action="{{ route('enrollee.documents.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="document_type" class="form-label">Document Type</label>
                            <select class="form-select" id="document_type" name="document_type" required>
                                <option value="">Select Document Type</option>
                                <option value="birth_certificate">Birth Certificate (PSA)</option>
                                <option value="report_card">Report Card/Form 138</option>
                                <option value="good_moral">Good Moral Certificate</option>
                                <option value="medical_certificate">Medical Certificate</option>
                                <option value="transfer_certificate">Transfer Certificate</option>
                                <option value="barangay_clearance">Barangay Clearance</option>
                                <option value="parent_id">Parent's ID Copy</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3" id="other_type_field" style="display: none;">
                            <label for="other_document_type" class="form-label">Specify Document Type</label>
                            <input type="text" class="form-control" id="other_document_type" name="other_document_type" placeholder="Enter document type">
                        </div>

                        <div class="mb-3">
                            <label for="document_file" class="form-label">Select Document</label>
                            <input type="file" class="form-control" id="document_file" name="document_file" accept=".pdf,.jpg,.jpeg,.png,.docx" required>
                            <div class="form-text">Accepted formats: PDF, JPG, PNG, DOCX. Maximum size: 5MB</div>
                            <div id="filePreview" class="mt-2" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="ri-file-line me-2"></i>
                                    <span id="fileName"></span>
                                    <span id="fileSize" class="text-muted"></span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="document_notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="document_notes" name="document_notes" rows="3" placeholder="Any additional notes about this document"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-upload-line me-1"></i>
                            Upload Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Document Viewer Modal -->
    <div class="modal fade" id="documentViewerModal" tabindex="-1" aria-labelledby="documentViewerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentViewerModalLabel">
                        <i class="ri-file-text-line me-2"></i>
                        <span id="documentTitle">Document Viewer</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="documentContent" class="text-center">
                        <!-- Document content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" id="downloadBtn">
                        <i class="ri-download-line me-1"></i>
                        Download
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="openNewTabBtn">
                        <i class="ri-external-link-line me-1"></i>
                        Open in New Tab
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-enrollee-layout>
