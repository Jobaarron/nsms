<x-enrollee-layout>
    @vite(['resources/js/enrollee-documents.js'])
    @vite(['resources/css/enrollee-documents.css'])
    
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
                                                <span class="badge bg-{{ ($documentData['status'] ?? 'pending') === 'approved' ? 'success' : (($documentData['status'] ?? 'pending') === 'rejected' ? 'warning' : 'warning') }}">
                                                    {{ ($documentData['status'] ?? 'pending') === 'rejected' ? 'Revised' : ucfirst($documentData['status'] ?? 'pending') }}
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
                                                    @if($enrollee->enrollment_status === 'pending' && ($documentData['status'] ?? 'pending') === 'rejected')
                                                    <button class="btn btn-outline-warning replace-doc-btn" 
                                                            data-index="{{ $index }}" 
                                                            title="Replace Document">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    @elseif($enrollee->enrollment_status === 'pending' && ($documentData['status'] ?? 'pending') === 'pending')
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
                            $approvedDocs = 0;
                            $pendingDocs = 0;
                            $revisedDocs = 0;
                            
                            foreach ($documents as $document) {
                                if (is_string($document)) {
                                    // Old format: all documents are pending
                                    $pendingDocs++;
                                } else {
                                    // New format: check actual status (use 'approved' to match admin system)
                                    $status = $document['status'] ?? 'pending';
                                    if ($status === 'approved' || $status === 'verified') {
                                        $approvedDocs++;
                                    } elseif ($status === 'rejected') {
                                        $revisedDocs++;
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
                                    <h4 class="text-success">{{ $approvedDocs }}</h4>
                                    <small class="text-muted">Approved</small>
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
                                    <h4 class="text-warning">{{ $revisedDocs }}</h4>
                                    <small class="text-muted">Revised</small>
                                </div>
                            </div>
                        </div>

                        @if($totalDocs > 0)
                        <div class="mt-3">
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: {{ $totalDocs > 0 ? ($approvedDocs / $totalDocs) * 100 : 0 }}%"></div>
                            </div>
                            <small class="text-muted">{{ $totalDocs > 0 ? round(($approvedDocs / $totalDocs) * 100) : 0 }}% of documents approved</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-4">
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
                        Upload Documents
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="uploadForm" method="POST" action="{{ route('enrollee.documents.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="document_files" class="form-label">Select Documents</label>
                            <input type="file" class="form-control" id="document_files" name="document_files[]" accept=".pdf,.jpg,.jpeg,.png" multiple required>
                            <div class="form-text">Accepted formats: PDF, JPG, PNG. Maximum size: 5MB per file. You can select multiple files at once.</div>
                            <div id="filePreview" class="mt-2" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="ri-file-line me-2"></i>
                                    <span id="fileCount"></span>
                                    <div id="fileList" class="mt-2"></div>
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

    <!-- Replace Document Modal -->
    <div class="modal fade" id="replaceDocumentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-edit-line me-2"></i>
                        Replace Document
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="replaceDocumentForm" method="POST" action="{{ route('enrollee.documents.replace') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="replace_document_index" name="document_index">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="ri-information-line me-2"></i>
                            You are replacing a document that was marked for revision. The new document will be submitted for review.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Current Document</label>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-file-text-line fs-4 text-muted me-3"></i>
                                        <div>
                                            <h6 class="mb-1" id="current-doc-type">Document Type</h6>
                                            <small class="text-muted" id="current-doc-filename">filename.pdf</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="replace_document_file" class="form-label">Select New Document</label>
                            <input type="file" class="form-control" id="replace_document_file" name="document_file" accept=".pdf,.jpg,.jpeg,.png" required>
                            <div class="form-text">Accepted formats: PDF, JPG, PNG. Maximum size: 5MB</div>
                            <div id="replaceFilePreview" class="mt-2" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="ri-file-line me-2"></i>
                                    <span id="replaceFileName"></span>
                                    <span id="replaceFileSize" class="text-muted"></span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="replace_document_notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="replace_document_notes" name="document_notes" rows="3" placeholder="Any additional notes about this replacement document"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="ri-edit-line me-1"></i>
                            Replace Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-enrollee-layout>
