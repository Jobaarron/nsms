// File Upload Manager
class FileUploadManager {
    constructor() {
        this.uploadedFiles = {
            id_photo: null,
            documents: []
        };
        this.init();
        this.loadExistingFiles();
    }

    init() {
        this.setupDateValidation();
        this.setupGradeStrandLogic();
        this.setupGuardianAutoPopulation();
        this.setupFileUploads();
        this.setupFormValidation();
    }

    setupDateValidation() {
        const dob = document.getElementById('date_of_birth');
        if (!dob) return;
        
        // Prevent selecting a future date
        dob.max = new Date().toISOString().split('T')[0];

        dob.addEventListener('change', () => {
            if (!dob.value) {
                dob.classList.add('is-invalid');
            } else {
                dob.classList.remove('is-invalid');
            }
        });
    }

    setupGradeStrandLogic() {
        const gradeSelect = document.getElementById('grade_level');
        const strandGroup = document.getElementById('strand-group');
        const strandSelect = document.getElementById('strand');
        const trackContainer = document.getElementById('track-container');
        const trackSelect = document.getElementById('track_applied');

        function toggleStrandField() {
            const selectedGrade = gradeSelect.value;
            
            if (selectedGrade === 'Grade 11' || selectedGrade === 'Grade 12') {
                strandGroup.classList.remove('d-none');
                strandSelect.setAttribute('required', 'required');
            } else {
                strandGroup.classList.add('d-none');
                strandSelect.removeAttribute('required');
                strandSelect.value = '';
                // Also hide track field when grade is not 11-12
                trackContainer.style.display = 'none';
                trackSelect.required = false;
                trackSelect.value = '';
            }
        }

        function toggleTrackField() {
            if (strandSelect.value === 'TVL') {
                trackContainer.style.display = 'block';
                trackSelect.required = true;
            } else {
                trackContainer.style.display = 'none';
                trackSelect.required = false;
                trackSelect.value = '';
            }
        }

        // Initial checks
        toggleStrandField();
        toggleTrackField();
        
        // Listen for changes
        gradeSelect.addEventListener('change', toggleStrandField);
        strandSelect.addEventListener('change', toggleTrackField);
    }

    setupGuardianAutoPopulation() {
        const fatherName = document.getElementById('father_name');
        const motherName = document.getElementById('mother_name');
        const fatherContact = document.getElementById('father_contact');
        const motherContact = document.getElementById('mother_contact');
        const guardianName = document.getElementById('guardian_name');
        const guardianContact = document.getElementById('guardian_contact');

        fatherName.addEventListener('input', function() {
            if (!guardianName.value) {
                guardianName.value = this.value;
            }
        });

        motherName.addEventListener('input', function() {
            if (!guardianName.value) {
                guardianName.value = this.value;
            }
        });

        fatherContact.addEventListener('input', function() {
            if (!guardianContact.value) {
                guardianContact.value = this.value;
            }
        });

        motherContact.addEventListener('input', function() {
            if (!guardianContact.value) {
                guardianContact.value = this.value;
            }
        });
    }

    setupFileUploads() {
        this.setupIdPhotoUpload();
        this.setupDocumentsUpload();
    }

    setupIdPhotoUpload() {
        const uploadZone = document.getElementById('id_photo_zone');
        const fileInput = document.getElementById('id_photo');
        const uploadArea = document.getElementById('id_photo_upload_area');
        const preview = document.getElementById('id_photo_preview');

        // Click to upload
        uploadZone.addEventListener('click', () => {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.jpg,.jpeg,.png';
            input.onchange = (e) => this.handleIdPhotoUpload(e.target.files[0]);
            input.click();
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            if (file) {
                this.handleIdPhotoUpload(file);
            }
        });

        // Remove file
        preview.addEventListener('click', (e) => {
            if (e.target.closest('.remove-file')) {
                this.removeIdPhoto();
            }
        });
    }

    setupDocumentsUpload() {
        const uploadZone = document.getElementById('documents_zone');
        const uploadArea = document.getElementById('documents_upload_area');
        const preview = document.getElementById('documents_preview');

        // Click to upload
        uploadZone.addEventListener('click', () => {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.pdf,.jpg,.jpeg,.png';
            input.multiple = true;
            input.onchange = (e) => this.handleDocumentsUpload(Array.from(e.target.files));
            input.click();
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = Array.from(e.dataTransfer.files);
            if (files.length > 0) {
                this.handleDocumentsUpload(files);
            }
        });

        // Remove files
        preview.addEventListener('click', (e) => {
            if (e.target.closest('.remove-file')) {
                const fileId = e.target.closest('.file-item').dataset.fileId;
                this.removeDocument(fileId);
            }
        });
    }

    async handleIdPhotoUpload(file) {
        if (!this.validateFile(file, 'id_photo')) return;

        // Remove existing photo first
        if (this.uploadedFiles.id_photo) {
            await this.removeIdPhoto();
        }

        await this.uploadFile(file, 'id_photo');
    }

    async handleDocumentsUpload(files) {
        for (const file of files) {
            if (this.validateFile(file, 'documents')) {
                await this.uploadFile(file, 'documents');
            }
        }
    }

    validateFile(file, type) {
        const maxSizes = {
            id_photo: 5 * 1024 * 1024, // 5MB
            documents: 8 * 1024 * 1024  // 8MB
        };

        const allowedTypes = {
            id_photo: ['image/jpeg', 'image/png', 'image/jpg'],
            documents: ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']
        };

        if (file.size > maxSizes[type]) {
            this.showError(`File size must be less than ${maxSizes[type] / (1024 * 1024)}MB`);
            return false;
        }

        if (!allowedTypes[type].includes(file.type)) {
            this.showError(`Invalid file type. Please upload ${type === 'id_photo' ? 'JPG or PNG' : 'PDF, JPG, or PNG'} files only.`);
            return false;
        }

        return true;
    }

    async uploadFile(file, type) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', type);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        try {
            const response = await fetch('/enroll/upload-temp-file', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                if (type === 'id_photo') {
                    this.uploadedFiles.id_photo = result.file;
                    this.updateIdPhotoPreview(result.file);
                } else {
                    this.uploadedFiles.documents.push(result.file);
                    this.updateDocumentsPreview();
                }
            } else {
                this.showError(result.message || 'Upload failed');
            }
        } catch (error) {
            console.error('Upload error:', error);
            this.showError('Upload failed. Please try again.');
        }
    }

    async removeIdPhoto() {
        if (!this.uploadedFiles.id_photo) return;

        try {
            const response = await fetch(`/enroll/delete-temp-file/${this.uploadedFiles.id_photo.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                this.uploadedFiles.id_photo = null;
                this.updateIdPhotoPreview();
            }
        } catch (error) {
            console.error('Delete error:', error);
        }
    }

    async removeDocument(fileId) {
        try {
            const response = await fetch(`/enroll/delete-temp-file/${fileId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                this.uploadedFiles.documents = this.uploadedFiles.documents.filter(f => f.id !== fileId);
                this.updateDocumentsPreview();
            }
        } catch (error) {
            console.error('Delete error:', error);
        }
    }

    updateIdPhotoPreview(file = null) {
        const uploadZone = document.getElementById('id_photo_zone');
        const preview = document.getElementById('id_photo_preview');

        if (file) {
            uploadZone.style.display = 'none';
            preview.style.display = 'block';
            preview.querySelector('.file-name').textContent = file.name;
            preview.querySelector('.file-size').textContent = this.formatFileSize(file.size);
        } else {
            uploadZone.style.display = 'block';
            preview.style.display = 'none';
        }
    }

    updateDocumentsPreview() {
        const uploadZone = document.getElementById('documents_zone');
        const preview = document.getElementById('documents_preview');

        if (this.uploadedFiles.documents.length > 0) {
            uploadZone.style.display = 'none';
            preview.innerHTML = this.uploadedFiles.documents.map(file => `
                <div class="file-item" data-file-id="${file.id}">
                    <i class="ri-file-line file-icon"></i>
                    <div class="file-info">
                        <span class="file-name">${file.name}</span>
                        <span class="file-size">${this.formatFileSize(file.size)}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-file">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            `).join('');
            preview.style.display = 'block';
            
            // Update document count indicator
            this.updateDocumentCountIndicator();
        } else {
            uploadZone.style.display = 'block';
            preview.style.display = 'none';
            this.updateDocumentCountIndicator();
        }
    }

    updateDocumentCountIndicator() {
        const uploadZone = document.getElementById('documents_zone');
        const count = this.uploadedFiles.documents.length;
        const required = 3;
        
        // Update upload zone text to show progress
        const uploadText = uploadZone.querySelector('.upload-text');
        const uploadSubtext = uploadZone.querySelector('.upload-subtext');
        
        if (count > 0) {
            uploadText.innerHTML = `${count} of ${required} minimum documents uploaded`;
            if (count < required) {
                uploadSubtext.innerHTML = `<span style="color: #dc3545;">Upload ${required - count} more document(s)</span>`;
                uploadZone.style.borderColor = '#dc3545';
            } else {
                uploadSubtext.innerHTML = `<span style="color: #198754;">✓ Minimum requirement met. You can upload more if needed.</span>`;
                uploadZone.style.borderColor = '#198754';
            }
        } else {
            uploadText.textContent = 'Click to upload or drag and drop';
            uploadSubtext.textContent = 'PDF, JPG, PNG up to 8MB each';
            uploadZone.style.borderColor = '';
        }
    }

    async loadExistingFiles() {
        try {
            const response = await fetch('/enroll/get-temp-files');
            const result = await response.json();

            if (result.success) {
                this.uploadedFiles = result.files;
                this.updateIdPhotoPreview(this.uploadedFiles.id_photo);
                this.updateDocumentsPreview();
            }
        } catch (error) {
            console.error('Load files error:', error);
        }
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    setupFormValidation() {
        const form = document.querySelector('form');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            if (!this.validateFormSubmission()) {
                e.preventDefault();
                return false;
            }
        });
    }

    validateFormSubmission() {
        const errors = [];

        // Check ID photo requirement
        if (!this.uploadedFiles.id_photo) {
            errors.push('ID Photo is required.');
        }

        // Check minimum documents requirement
        if (this.uploadedFiles.documents.length < 3) {
            errors.push(`You must upload at least 3 documents. Currently uploaded: ${this.uploadedFiles.documents.length}`);
        }

        if (errors.length > 0) {
            this.showError(errors.join(' '));
            
            // Scroll to top to show error
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            return false;
        }

        return true;
    }

    showError(message) {
        // Create or update error alert
        let alert = document.querySelector('.file-upload-error');
        if (!alert) {
            alert = document.createElement('div');
            alert.className = 'alert alert-danger file-upload-error mt-2';
            document.querySelector('form').insertBefore(alert, document.querySelector('form').firstChild);
        }
        alert.innerHTML = message;
        
        // Auto-hide after 8 seconds (longer for validation errors)
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 8000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add CSRF token meta tag if not present
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = document.querySelector('input[name="_token"]').value;
        document.head.appendChild(meta);
    }
    
    // Initialize file upload manager
    new FileUploadManager();
});