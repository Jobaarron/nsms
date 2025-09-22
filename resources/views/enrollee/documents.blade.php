<x-enrollee-layout>
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
                        @if($enrollee->enrollment_status === 'pending' || $enrollee->enrollment_status === 'approved')
                        <button class="btn btn-primary btn-sm" id="uploadBtn" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="ri-upload-line me-1"></i>
                            Upload Document
                        </button>
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
                                @if($enrollee->enrollment_status === 'pending' || $enrollee->enrollment_status === 'approved')
                                <button class="btn btn-primary" id="uploadFirstBtn" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                    <i class="ri-upload-line me-1"></i>
                                    Upload Your First Document
                                </button>
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
                            $verifiedDocs = collect($documents)->where('status', 'verified')->count();
                            $pendingDocs = collect($documents)->where('status', 'pending')->count();
                            $rejectedDocs = collect($documents)->where('status', 'rejected')->count();
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

    <style>
        .spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        #documentContent img {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        #documentContent iframe {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-group-sm .btn {
            margin-right: 2px;
        }
        
        .btn-group-sm .btn:last-child {
            margin-right: 0;
        }
    </style>

    <script>
        // Global variables for DOM elements
        let uploadBtn, uploadFirstBtn, uploadModal, uploadForm;
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Document management JavaScript loaded successfully');
            
            // Get all DOM elements once
            uploadBtn = document.getElementById('uploadBtn');
            uploadFirstBtn = document.getElementById('uploadFirstBtn');
            uploadModal = document.getElementById('uploadModal');
            uploadForm = document.getElementById('uploadForm');
            
            // Add manual click handlers if Bootstrap modal doesn't work
            if (uploadBtn && uploadModal) {
                uploadBtn.addEventListener('click', function(e) {
                    console.log('Upload button clicked');
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modal = new bootstrap.Modal(uploadModal);
                        modal.show();
                    } else {
                        // Fallback: show modal manually
                        uploadModal.style.display = 'block';
                        uploadModal.classList.add('show');
                        document.body.classList.add('modal-open');
                        
                        // Create backdrop
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.id = 'modalBackdrop';
                        document.body.appendChild(backdrop);
                    }
                });
            }
            
            if (uploadFirstBtn && uploadModal) {
                uploadFirstBtn.addEventListener('click', function(e) {
                    console.log('Upload first button clicked');
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modal = new bootstrap.Modal(uploadModal);
                        modal.show();
                    } else {
                        // Fallback: show modal manually
                        uploadModal.style.display = 'block';
                        uploadModal.classList.add('show');
                        document.body.classList.add('modal-open');
                        
                        // Create backdrop
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.id = 'modalBackdrop';
                        document.body.appendChild(backdrop);
                    }
                });
            }
            
            // Add close button handlers for manual modal
            const closeButtons = uploadModal?.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
            closeButtons?.forEach(btn => {
                btn.addEventListener('click', function() {
                    console.log('Modal close button clicked');
                    closeModal();
                });
            });
            
            // Close modal function
            function closeModal() {
                if (uploadModal) {
                    uploadModal.style.display = 'none';
                    uploadModal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    
                    // Remove all backdrops
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                    
                    // Reset form using the dedicated function
                    resetModalForm();
                }
            }
            
            // Close modal when clicking outside
            uploadModal?.addEventListener('click', function(e) {
                if (e.target === uploadModal) {
                    closeModal();
                }
            });
            
            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && uploadModal && uploadModal.classList.contains('show')) {
                    closeModal();
                }
            });
            
            // Handle document type selection
            const documentTypeSelect = document.getElementById('document_type');
            const otherTypeField = document.getElementById('other_type_field');
            
            if (documentTypeSelect && otherTypeField) {
                documentTypeSelect.addEventListener('change', function() {
                    if (this.value === 'other') {
                        otherTypeField.style.display = 'block';
                        document.getElementById('other_document_type').required = true;
                    } else {
                        otherTypeField.style.display = 'none';
                        document.getElementById('other_document_type').required = false;
                    }
                });
            }

            // Handle upload form submission
            if (uploadForm) {
                uploadForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('Upload form submitted');
                    
                    // Validate form before submission
                    const documentType = document.getElementById('document_type').value;
                    const documentFile = document.getElementById('document_file').files[0];
                    const otherDocumentType = document.getElementById('other_document_type').value;
                    
                    if (!documentType) {
                        showAlert('Please select a document type', 'error');
                        return;
                    }
                    
                    if (documentType === 'other' && !otherDocumentType.trim()) {
                        showAlert('Please specify the document type', 'error');
                        return;
                    }
                    
                    if (!documentFile) {
                        showAlert('Please select a file to upload', 'error');
                        return;
                    }
                    
                    console.log('Form validation passed:', { documentType, otherDocumentType, fileName: documentFile.name });
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                
                // Show loading state
                submitBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Uploading...';
                submitBtn.disabled = true;
                
                // Create progress indicator
                const progressHtml = `
                    <div id="uploadProgress" class="mt-3">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted">Uploading document...</small>
                    </div>
                `;
                
                const modalBody = uploadModal.querySelector('.modal-body');
                modalBody.insertAdjacentHTML('beforeend', progressHtml);
                
                // Submit form with progress tracking
                console.log('Submitting to:', this.action);
                console.log('FormData contents:', Array.from(formData.entries()));
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Response text:', text);
                            throw new Error(`HTTP error! status: ${response.status} - ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    
                    if (data.success) {
                        // Update progress to 100%
                        const progressBar = document.querySelector('#uploadProgress .progress-bar');
                        if (progressBar) {
                            progressBar.style.width = '100%';
                            progressBar.classList.remove('progress-bar-animated');
                        }
                        
                        showAlert(data.message || 'Document uploaded successfully!', 'success');
                        
                        // Close modal and reload page after delay
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(uploadModal);
                            if (modal) {
                                modal.hide();
                            }
                            window.location.reload();
                        }, 1500);
                    } else {
                        console.error('Upload failed:', data);
                        throw new Error(data.message || 'Upload failed');
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    showAlert(error.message || 'Failed to upload document. Please try again.', 'error');
                    
                    // Reset button state
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                    
                    // Remove progress indicator
                    const progressDiv = document.getElementById('uploadProgress');
                    if (progressDiv) {
                        progressDiv.remove();
                    }
                });
                });
            }

            // File input change handler for preview
            const fileInput = document.getElementById('document_file');
            const filePreview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            
            if (fileInput && filePreview && fileName && fileSize) {
                fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (5MB = 5 * 1024 * 1024 bytes)
                    if (file.size > 5 * 1024 * 1024) {
                        showAlert('File size must be less than 5MB', 'error');
                        this.value = '';
                        filePreview.style.display = 'none';
                        return;
                    }
                    
                    // Validate file type
                    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    if (!allowedTypes.includes(file.type)) {
                        showAlert('Invalid file type. Please select PDF, JPG, PNG, or DOCX files only.', 'error');
                        this.value = '';
                        filePreview.style.display = 'none';
                        return;
                    }
                    
                    // Show file preview
                    fileName.textContent = file.name;
                    fileSize.textContent = ` (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                    filePreview.style.display = 'block';
                } else {
                    filePreview.style.display = 'none';
                }
                });
            }

            // Reset form when modal is hidden (Bootstrap event)
            if (uploadModal && uploadForm) {
                uploadModal.addEventListener('hidden.bs.modal', function() {
                    resetModalForm();
                });
            }
            
            // Reset modal form function
            function resetModalForm() {
                if (uploadForm) {
                    uploadForm.reset();
                    const submitBtn = uploadForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="ri-upload-line me-1"></i>Upload Document';
                        submitBtn.disabled = false;
                    }
                
                    // Remove progress indicator if exists
                    const progressDiv = document.getElementById('uploadProgress');
                    if (progressDiv) {
                        progressDiv.remove();
                    }
                    
                    // Hide other type field and file preview
                    const otherTypeField = document.getElementById('other_type_field');
                    const filePreview = document.getElementById('filePreview');
                    
                    if (otherTypeField) {
                        otherTypeField.style.display = 'none';
                        document.getElementById('other_document_type').required = false;
                    }
                    if (filePreview) {
                        filePreview.style.display = 'none';
                    }
                }
            }

            // Add event listeners for document action buttons
            document.addEventListener('click', function(e) {
                if (e.target.closest('.view-doc-btn')) {
                    const btn = e.target.closest('.view-doc-btn');
                    const index = btn.dataset.index;
                    const filename = btn.dataset.filename;
                    const path = btn.dataset.path;
                    const type = btn.dataset.type;
                    viewDocument(index, filename, path, type);
                }
                
                if (e.target.closest('.download-doc-btn')) {
                    const btn = e.target.closest('.download-doc-btn');
                    const index = btn.dataset.index;
                    const filename = btn.dataset.filename;
                    const path = btn.dataset.path;
                    downloadDocument(index, filename, path);
                }
                
                if (e.target.closest('.delete-doc-btn')) {
                    const btn = e.target.closest('.delete-doc-btn');
                    const index = btn.dataset.index;
                    deleteDocument(index);
                }
            });
        });

        // Global variables for document handling
        let currentDocumentPath = '';
        let currentDocumentName = '';

        function viewDocument(index, filename, path, type) {
            console.log('viewDocument called:', { index, filename, path, type });
            
            if (!path) {
                showAlert('Document path not found', 'error');
                return;
            }

            currentDocumentPath = path;
            currentDocumentName = filename;
            
            // Update modal title
            const titleElement = document.getElementById('documentTitle');
            if (titleElement) {
                titleElement.textContent = filename;
            }
            
            // Get file extension to determine how to display
            const fileExtension = path.split('.').pop().toLowerCase();
            const documentContent = document.getElementById('documentContent');
            
            // Clear previous content
            documentContent.innerHTML = '<div class="p-4"><i class="ri-loader-4-line spin fs-1 text-primary"></i><br>Loading document...</div>';
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('documentViewerModal'));
            modal.show();
            
            // Build document URL (assuming documents are stored in storage/app/public/documents)
            const documentUrl = `/storage/${path}`;
            
            // Handle different file types
            if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExtension)) {
                // Display image
                documentContent.innerHTML = `
                    <img src="${documentUrl}" class="img-fluid" alt="${filename}" style="max-height: 70vh; width: auto;">
                `;
            } else if (fileExtension === 'pdf') {
                // Display PDF
                documentContent.innerHTML = `
                    <iframe src="${documentUrl}" width="100%" height="600px" frameborder="0">
                        <p>Your browser does not support PDFs. 
                        <a href="${documentUrl}" target="_blank">Click here to view the PDF</a></p>
                    </iframe>
                `;
            } else {
                // For other file types, show download option
                documentContent.innerHTML = `
                    <div class="p-5">
                        <i class="ri-file-text-line display-1 text-muted"></i>
                        <h5 class="mt-3">${filename}</h5>
                        <p class="text-muted">File type: ${fileExtension.toUpperCase()}</p>
                        <p class="text-muted">This file type cannot be previewed. Please download to view.</p>
                        <a href="${documentUrl}" class="btn btn-primary" download="${filename}">
                            <i class="ri-download-line me-1"></i>
                            Download File
                        </a>
                    </div>
                `;
            }
            
            // Update footer buttons
            document.getElementById('downloadBtn').onclick = () => downloadDocument(index, filename, path);
            document.getElementById('openNewTabBtn').onclick = () => window.open(documentUrl, '_blank');
        }

        function downloadDocument(index, filename, path) {
            console.log('downloadDocument called:', { index, filename, path });
            
            if (!path) {
                showAlert('Document path not found', 'error');
                return;
            }
            
            const documentUrl = `/storage/${path}`;
            console.log('Download URL:', documentUrl);
            
            const link = document.createElement('a');
            link.href = documentUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showAlert('Download started', 'success');
        }

        function deleteDocument(index) {
            console.log('deleteDocument called:', { index });
            
            if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
                // Get CSRF token
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                             document.querySelector('input[name="_token"]')?.value;
                
                if (!token) {
                    showAlert('Security token not found. Please refresh the page.', 'error');
                    return;
                }

                // Show loading state
                const deleteBtn = document.querySelector(`button[data-index="${index}"].delete-doc-btn`);
                const originalText = deleteBtn ? deleteBtn.innerHTML : '';
                if (deleteBtn) {
                    deleteBtn.innerHTML = '<i class="ri-loader-4-line spin"></i>';
                    deleteBtn.disabled = true;
                }

                // Send delete request
                fetch(`{{ route('enrollee.documents.delete') }}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        document_index: index
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message || 'Document deleted successfully', 'success');
                        // Reload page to update the document list
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message || 'Failed to delete document', 'error');
                        if (deleteBtn) {
                            deleteBtn.innerHTML = originalText;
                            deleteBtn.disabled = false;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while deleting the document', 'error');
                    if (deleteBtn) {
                        deleteBtn.innerHTML = originalText;
                        deleteBtn.disabled = false;
                    }
                });
            }
        }

        function testJavaScript() {
            console.log('Test JavaScript function called');
            showAlert('JavaScript is working! Check console for detailed logs.', 'success');
            
            // Test Bootstrap
            console.log('Bootstrap check:', {
                bootstrap: typeof bootstrap !== 'undefined',
                Modal: typeof bootstrap !== 'undefined' && !!bootstrap.Modal
            });
            
            // Test form elements (reusing already declared variables)
            const documentType = document.getElementById('document_type');
            const documentFile = document.getElementById('document_file');
            
            console.log('Form elements check:', {
                uploadForm: !!uploadForm,
                uploadModal: !!uploadModal,
                documentType: !!documentType,
                documentFile: !!documentFile,
                uploadBtn: !!uploadBtn,
                uploadFirstBtn: !!uploadFirstBtn
            });
            
            // Test CSRF token
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('CSRF token:', token ? 'Present' : 'Missing');
            
            // Test route
            if (uploadForm) {
                console.log('Upload form action:', uploadForm.action);
            }
            
            // Test modal manually
            if (uploadModal) {
                console.log('Testing modal manually...');
                uploadModal.style.display = 'block';
                uploadModal.classList.add('show');
                setTimeout(() => {
                    uploadModal.style.display = 'none';
                    uploadModal.classList.remove('show');
                    console.log('Modal test completed');
                }, 2000);
            }
        }

        function showAlert(message, type = 'info') {
            const alertClass = type === 'error' ? 'alert-danger' : 
                              type === 'success' ? 'alert-success' : 
                              type === 'warning' ? 'alert-warning' : 'alert-info';
            
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="ri-${type === 'error' ? 'error-warning' : type === 'success' ? 'check-circle' : 'information'}-line me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Insert alert at the top of the page
            const container = document.querySelector('.py-4');
            container.insertAdjacentHTML('afterbegin', alertHtml);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
    </script>
</x-enrollee-layout>
