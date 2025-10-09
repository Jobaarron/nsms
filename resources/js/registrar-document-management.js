/**
 * Registrar Document Management
 * Handles document viewing, approval, and rejection in modal
 * Isolated from main application functionality to prevent conflicts
 */

// Namespace to prevent conflicts
window.RegistrarDocumentManagement = (function() {
    'use strict';
    
    // Private variables
    let currentDocuments = [];
    let currentDocumentIndex = null;
    let currentApplicationId = null;
    
    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initializeDocumentManagement();
    });

    /**
     * Initialize document management system
     */
    function initializeDocumentManagement() {
        console.log('Initializing registrar document management...');
        
        // Create document management modal if it doesn't exist
        createDocumentModal();
        
        // Set up event listeners
        setupEventListeners();
    }

    /**
     * Create document management modal
     */
    function createDocumentModal() {
        // Check if modal already exists
        if (document.getElementById('documentManagementModal')) {
            return;
        }

        const modalHtml = `
            <div class="modal fade" id="documentManagementModal" tabindex="-1" aria-labelledby="documentManagementModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                            <h5 class="modal-title" id="documentManagementModalLabel">
                                <i class="ri-file-text-line me-2"></i>Document Review
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Document Preview -->
                                <div class="col-md-8">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h6 class="mb-0" id="document-title">Document Preview</h6>
                                        </div>
                                        <div class="card-body text-center d-flex align-items-center justify-content-center">
                                            <div id="document-preview" style="width: 100%; min-height: 400px;">
                                                <div class="text-muted">
                                                    <i class="ri-file-line display-4"></i>
                                                    <p class="mt-2">Loading document...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Document Actions -->
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h6 class="mb-0">Document Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="document-info">
                                                <p><strong>Document Type:</strong> <span id="doc-type">-</span></p>
                                                <p><strong>File Name:</strong> <span id="doc-filename">-</span></p>
                                                <p><strong>Upload Date:</strong> <span id="doc-upload-date">-</span></p>
                                                <p><strong>File Size:</strong> <span id="doc-file-size">-</span></p>
                                                <p><strong>Status:</strong> <span id="doc-status" class="badge">-</span></p>
                                            </div>
                                            
                                            <hr>
                                            
                                            <div class="d-grid gap-2">
                                                <button type="button" class="btn btn-success" id="accept-document-btn">
                                                    <i class="ri-check-line me-1"></i>Accept Document
                                                </button>
                                                <button type="button" class="btn btn-danger" id="reject-document-btn">
                                                    <i class="ri-close-line me-1"></i>Reject Document
                                                </button>
                                                <button type="button" class="btn btn-warning" id="pending-document-btn">
                                                    <i class="ri-time-line me-1"></i>Mark as Pending
                                                </button>
                                                <button type="button" class="btn btn-primary" id="download-document-btn">
                                                    <i class="ri-download-line me-1"></i>Download
                                                </button>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <label for="document-notes" class="form-label">Notes</label>
                                                <textarea class="form-control" id="document-notes" rows="3" placeholder="Add notes about this document..."></textarea>
                                                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="save-notes-btn">
                                                    <i class="ri-save-line me-1"></i>Save Notes
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
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    /**
     * Set up event listeners
     */
    function setupEventListeners() {
        // Document action buttons
        document.addEventListener('click', function(e) {
            if (e.target.id === 'accept-document-btn') {
                updateDocumentStatus('approved');
            } else if (e.target.id === 'reject-document-btn') {
                updateDocumentStatus('rejected');
            } else if (e.target.id === 'pending-document-btn') {
                updateDocumentStatus('pending');
            } else if (e.target.id === 'download-document-btn') {
                downloadDocument();
            } else if (e.target.id === 'save-notes-btn') {
                saveDocumentNotes();
            }
        });
    }

    /**
     * Make documents clickable in the documents list
     */
    function makeDocumentsClickable(applicationId) {
        currentApplicationId = applicationId;
        
        // Load documents data
        loadDocumentsData(applicationId);
    }

    /**
     * Load documents data for application
     */
    function loadDocumentsData(applicationId) {
        const documentsList = document.getElementById('documents-list');
        if (!documentsList) return;

        documentsList.innerHTML = '<div class="text-center"><i class="ri-loader-4-line ri-spin"></i> Loading documents...</div>';
        
        // Fetch real documents from API
        fetch(`/registrar/applications/${applicationId}/documents`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.documents) {
                currentDocuments = data.documents.map(doc => ({
                    index: doc.index,
                    document_type: doc.type,
                    file_name: doc.filename,
                    file_path: doc.path,
                    status: doc.status,
                    created_at: doc.uploaded_at || new Date().toISOString(),
                    file_size: 'Unknown',
                    notes: ''
                }));
                displayDocuments(currentDocuments);
                updateDocumentsCount(currentDocuments.length);
            } else {
                documentsList.innerHTML = '<div class="text-muted text-center">No documents found</div>';
                updateDocumentsCount(0);
            }
        })
        .catch(error => {
            console.error('Error loading documents:', error);
            documentsList.innerHTML = '<div class="text-danger text-center">Error loading documents</div>';
            updateDocumentsCount(0);
        });
    }

    /**
     * Display documents as clickable list
     */
    function displayDocuments(documents) {
        const documentsList = document.getElementById('documents-list');
        if (!documentsList) return;
        
        if (!documents || documents.length === 0) {
            documentsList.innerHTML = '<div class="text-muted text-center">No documents uploaded</div>';
            return;
        }
        
        const documentsHtml = documents.map((doc, index) => {
            const statusClass = getDocumentStatusBadge(doc.status);
            const fileIcon = getFileIcon(doc.file_name);
            
            return `
                <div class="document-item border rounded p-3 mb-2" 
                     style="cursor: pointer; transition: all 0.2s;" 
                     onclick="RegistrarDocumentManagement.openDocumentReview(${index})"
                     onmouseover="this.style.backgroundColor='#f8f9fa'"
                     onmouseout="this.style.backgroundColor='transparent'">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="${fileIcon} fs-2 text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${doc.document_type}</h6>
                            <p class="mb-1 text-muted small">${doc.file_name}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Uploaded: ${new Date(doc.created_at).toLocaleDateString()}</small>
                                <span class="badge ${statusClass}">${doc.status}</span>
                            </div>
                        </div>
                        <div class="ms-2">
                            <i class="ri-arrow-right-line text-muted"></i>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        documentsList.innerHTML = documentsHtml;
    }

    /**
     * Open document review modal
     */
    function openDocumentReview(documentIndex) {
        currentDocumentIndex = documentIndex;
        const docData = currentDocuments[documentIndex];
        
        if (!docData) {
            console.error('Document not found');
            return;
        }
        
        // Populate document information
        document.getElementById('document-title').textContent = `${docData.document_type}`;
        document.getElementById('doc-type').textContent = docData.document_type;
        document.getElementById('doc-filename').textContent = docData.file_name;
        document.getElementById('doc-upload-date').textContent = new Date(docData.created_at).toLocaleDateString();
        document.getElementById('doc-file-size').textContent = docData.file_size || 'Unknown';
        
        const statusBadge = document.getElementById('doc-status');
        statusBadge.textContent = docData.status;
        statusBadge.className = `badge ${getDocumentStatusBadge(docData.status)}`;
        
        // Load document preview
        loadDocumentPreview(docData);
        
        // Load existing notes
        document.getElementById('document-notes').value = docData.notes || '';
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('documentManagementModal'));
        modal.show();
    }

    /**
     * Load document preview
     */
    function loadDocumentPreview(docData) {
        const previewContainer = document.getElementById('document-preview');
        const fileExtension = docData.file_name.split('.').pop().toLowerCase();
        
        // Use the proper registrar document serving route
        const documentUrl = `/registrar/documents/view/${docData.file_path}`;
        
        if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExtension)) {
            // Image preview
            previewContainer.innerHTML = `
                <img src="${documentUrl}" 
                     class="img-fluid" 
                     style="max-height: 400px;" 
                     alt="${docData.file_name}"
                     onerror="this.src='/images/no-image.png'">
            `;
        } else if (fileExtension === 'pdf') {
            // PDF preview
            previewContainer.innerHTML = `
                <embed src="${documentUrl}" 
                       type="application/pdf" 
                       width="100%" 
                       height="400px"
                       style="border: none;">
            `;
        } else {
            // Generic file preview
            previewContainer.innerHTML = `
                <div class="text-center py-4">
                    <i class="${getFileIcon(docData.file_name)} display-1 text-primary"></i>
                    <h5 class="mt-3">${docData.file_name}</h5>
                    <p class="text-muted">Preview not available for this file type</p>
                    <button class="btn btn-primary" onclick="RegistrarDocumentManagement.downloadDocument()">
                        <i class="ri-download-line me-1"></i>Download to View
                    </button>
                </div>
            `;
        }
    }

    /**
     * Update document status
     */
    function updateDocumentStatus(status) {
        if (currentDocumentIndex === null) return;
        
        const docData = currentDocuments[currentDocumentIndex];
        const notes = document.getElementById('document-notes').value;
        
        // Show loading state
        const buttons = ['accept-document-btn', 'reject-document-btn', 'pending-document-btn'];
        buttons.forEach(btnId => {
            const btn = document.getElementById(btnId);
            if (btn) btn.disabled = true;
        });
        
        // Make real API call to update document status
        fetch(`/registrar/applications/${currentApplicationId}/documents/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                document_index: currentDocumentIndex,
                status: status,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update local data
                currentDocuments[currentDocumentIndex].status = status;
                currentDocuments[currentDocumentIndex].notes = notes;
                
                // Update status badge in modal
                const statusBadge = document.getElementById('doc-status');
                statusBadge.textContent = status;
                statusBadge.className = `badge ${getDocumentStatusBadge(status)}`;
                
                // Refresh documents list
                displayDocuments(currentDocuments);
                
                // Show success message
                showAlert(`Document ${status} successfully`, 'success');
            } else {
                showAlert(data.message || `Failed to ${status} document`, 'error');
            }
        })
        .catch(error => {
            console.error(`Error ${status} document:`, error);
            showAlert(`Failed to ${status} document`, 'error');
        })
        .finally(() => {
            // Re-enable buttons
            buttons.forEach(btnId => {
                const btn = document.getElementById(btnId);
                if (btn) btn.disabled = false;
            });
        });
    }

    /**
     * Download document
     */
    function downloadDocument() {
        if (currentDocumentIndex === null) return;
        
        const docData = currentDocuments[currentDocumentIndex];
        const downloadUrl = `/registrar/documents/view/${docData.file_path}`;
        
        // Create temporary link and trigger download
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = docData.file_name;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /**
     * Save document notes
     */
    function saveDocumentNotes() {
        if (currentDocumentIndex === null) return;
        
        const notes = document.getElementById('document-notes').value;
        const saveBtn = document.getElementById('save-notes-btn');
        
        // Show loading state
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Saving...';
        
        // Mock API call - replace with actual implementation
        setTimeout(() => {
            currentDocuments[currentDocumentIndex].notes = notes;
            showAlert('Notes saved successfully', 'success');
            
            // Restore button
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="ri-save-line me-1"></i>Save Notes';
        }, 1000);
    }

    /**
     * Update documents count
     */
    function updateDocumentsCount(count) {
        const countElement = document.getElementById('documents-count');
        if (countElement) {
            countElement.textContent = count;
        }
    }

    /**
     * Get document status badge class
     */
    function getDocumentStatusBadge(status) {
        switch(status) {
            case 'approved': return 'bg-success';
            case 'rejected': return 'bg-danger';
            case 'pending': return 'bg-warning text-dark';
            default: return 'bg-secondary';
        }
    }

    /**
     * Get file icon based on file extension
     */
    function getFileIcon(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        switch(extension) {
            case 'pdf': return 'ri-file-pdf-line';
            case 'doc':
            case 'docx': return 'ri-file-word-line';
            case 'xls':
            case 'xlsx': return 'ri-file-excel-line';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'bmp':
            case 'webp': return 'ri-image-line';
            case 'zip':
            case 'rar': return 'ri-file-zip-line';
            default: return 'ri-file-line';
        }
    }

    /**
     * Show alert message
     */
    function showAlert(message, type) {
        // Simple alert implementation - can be enhanced
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Find a container to show the alert
        const container = document.querySelector('.modal-body') || document.body;
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) alert.remove();
        }, 3000);
    }

    // Public API
    return {
        makeDocumentsClickable: makeDocumentsClickable,
        openDocumentReview: openDocumentReview,
        downloadDocument: downloadDocument
    };

})();

// Expose functions to global scope for onclick handlers
window.makeDocumentsClickable = function(applicationId) {
    return window.RegistrarDocumentManagement.makeDocumentsClickable(applicationId);
};
