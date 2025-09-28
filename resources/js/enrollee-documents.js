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
    
    // Build document URL using proper route
    const documentUrl = window.enrolleeRoutes?.viewDocument ? 
        `${window.enrolleeRoutes.viewDocument}/${index}` : 
        `/enrollee/documents/view/${index}`;
    
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
    
    // Use proper download route
    const downloadUrl = window.enrolleeRoutes?.downloadDocument ? 
        `${window.enrolleeRoutes.downloadDocument}/${index}` : 
        `/enrollee/documents/download/${index}`;
    
    console.log('Download URL:', downloadUrl);
    
    const link = document.createElement('a');
    link.href = downloadUrl;
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

        // Send delete request - get the route from window object
        const deleteRoute = window.enrolleeRoutes?.deleteDocument || '/enrollee/documents/delete';
        fetch(deleteRoute, {
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