// Wait for both DOM and Bootstrap to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for Bootstrap to initialize
    // setTimeout(function() {
    //   console.log('Page loaded, Bootstrap status:', typeof window.bootstrap !== 'undefined' ? 'Available' : 'Not Available');
    //   initializeModalEventListeners();
    // }, 100);

    // Initialize modal event listeners
    function initializeModalEventListeners() {
      // Add close button functionality to all modals
      document.querySelectorAll('.modal').forEach(modal => {
        const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
        closeButtons.forEach(button => {
          button.addEventListener('click', function() {
            hideModal(modal.id);
          });
        });
        
        // Close on backdrop click
        modal.addEventListener('click', function(e) {
          if (e.target === modal) {
            hideModal(modal.id);
          }
        });
      });
      
      // Global ESC key listener
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          window.ModalManager.hideAll();
        }
      });
    }

    // Cleanup function for camera streams
    function cleanupCameraStreams() {
      if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
      }
      if (registrationStream) {
        registrationStream.getTracks().forEach(track => track.stop());
        registrationStream = null;
      }
    }

    // Cleanup when page is unloaded
    window.addEventListener('beforeunload', cleanupCameraStreams);
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const gradeFilter = document.getElementById('gradeFilter');
    // const statusFilter = document.getElementById('statusFilter'); // COMMENTED OUT FOR FUTURE USE
    const faceFilter = document.getElementById('faceFilter');
    
    function filterTable() {
      const searchTerm = searchInput.value.toLowerCase();
      const gradeValue = gradeFilter.value;
      // const statusValue = statusFilter.value; // COMMENTED OUT FOR FUTURE USE
      const faceValue = faceFilter.value;
      const rows = document.querySelectorAll('#studentsTable tbody tr');
      
      rows.forEach(row => {
        if (row.cells.length < 5) return; // Skip empty rows (updated since status column removed)
        
        const studentInfo = row.cells[1].textContent.toLowerCase();
        const grade = row.cells[2].textContent;
        // const status = row.cells[3].textContent.toLowerCase(); // COMMENTED OUT FOR FUTURE USE
        const faceStatusCell = row.cells[3]; // Face registration cell
        const faceStatus = faceStatusCell.getAttribute('data-face-status') || 'not_registered';
        
        const matchesSearch = studentInfo.includes(searchTerm);
        const matchesGrade = !gradeValue || grade.includes(gradeValue);
        // const matchesStatus = !statusValue || status.includes(statusValue); // COMMENTED OUT FOR FUTURE USE
        const matchesStatus = true; // Always true since status filter is disabled
        const matchesFace = !faceValue || faceStatus === faceValue;
        
        row.style.display = matchesSearch && matchesGrade && matchesStatus && matchesFace ? '' : 'none';
      });
    }
    
    [searchInput, gradeFilter, faceFilter].forEach(element => { // Removed statusFilter
      element.addEventListener('input', filterTable);
      element.addEventListener('change', filterTable);
    });

    // Camera functionality
    let stream = null;
    let registrationStream = null;

    // Main camera controls
    const video = document.getElementById('video');
    const cameraPlaceholder = document.getElementById('cameraPlaceholder');
    const startCameraBtn = document.getElementById('startCamera');
    const captureBtn = document.getElementById('capturePhoto');
    const stopCameraBtn = document.getElementById('stopCamera');

    startCameraBtn.addEventListener('click', async function() {
      try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        video.style.display = 'block';
        cameraPlaceholder.style.display = 'none';
        startCameraBtn.style.display = 'none';
        captureBtn.style.display = 'inline-block';
        stopCameraBtn.style.display = 'inline-block';
      } catch (err) {
        alert('Error accessing camera: ' + err.message);
      }
    });

    stopCameraBtn.addEventListener('click', function() {
      if (stream) {
        stream.getTracks().forEach(track => track.stop());
        video.srcObject = null;
        video.style.display = 'none';
        cameraPlaceholder.style.display = 'block';
        startCameraBtn.style.display = 'inline-block';
        captureBtn.style.display = 'none';
        stopCameraBtn.style.display = 'none';
      }
    });

    captureBtn.addEventListener('click', function() {
      document.getElementById('recognitionResult').style.display = 'block';
      document.getElementById('resultContent').innerHTML = 
        '<div class="alert alert-info">Facial recognition feature coming soon...</div>';
    });

    // Registration camera controls
    const registrationVideo = document.getElementById('registrationVideo');
    const registrationPlaceholder = document.getElementById('registrationPlaceholder');
    const startRegistrationBtn = document.getElementById('startRegistrationCamera');
    const captureRegistrationBtn = document.getElementById('captureRegistrationPhoto');

    startRegistrationBtn.addEventListener('click', async function() {
      try {
        registrationStream = await navigator.mediaDevices.getUserMedia({ video: true });
        registrationVideo.srcObject = registrationStream;
        registrationVideo.style.display = 'block';
        registrationPlaceholder.style.display = 'none';
        startRegistrationBtn.style.display = 'none';
        captureRegistrationBtn.style.display = 'inline-block';
      } catch (err) {
        alert('Error accessing camera: ' + err.message);
      }
    });

    captureRegistrationBtn.addEventListener('click', function() {
      alert('Face registration feature coming soon...');
      
      if (registrationStream) {
        registrationStream.getTracks().forEach(track => track.stop());
        registrationVideo.srcObject = null;
        registrationVideo.style.display = 'none';
        registrationPlaceholder.style.display = 'block';
        startRegistrationBtn.style.display = 'inline-block';
        captureRegistrationBtn.style.display = 'none';
      }
      
      hideModal('faceRegistrationModal');
    });

    // Modal cleanup
    document.getElementById('facialRecognitionModal').addEventListener('hidden.bs.modal', function() {
      if (stream) {
        stream.getTracks().forEach(track => track.stop());
        video.srcObject = null;
        video.style.display = 'none';
        cameraPlaceholder.style.display = 'block';
        startCameraBtn.style.display = 'inline-block';
        captureBtn.style.display = 'none';
        stopCameraBtn.style.display = 'none';
      }
    });

    document.getElementById('faceRegistrationModal').addEventListener('hidden.bs.modal', function() {
      if (registrationStream) {
        registrationStream.getTracks().forEach(track => track.stop());
        registrationVideo.srcObject = null;
        registrationVideo.style.display = 'none';
        registrationPlaceholder.style.display = 'block';
        startRegistrationBtn.style.display = 'inline-block';
        captureRegistrationBtn.style.display = 'none';
      }
    });
  });

  // Comprehensive modal management system
  window.ModalManager = {
    activeModals: new Set(),
    
    show: function(modalId) {
      try {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
          console.error('Modal not found:', modalId);
          return false;
        }

        // Try Bootstrap first
        if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
          const modal = new window.bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true,
            focus: true
          });
          modal.show();
          this.activeModals.add(modalId);
          
          // Add event listeners for proper cleanup
          modalElement.addEventListener('hidden.bs.modal', () => {
            this.activeModals.delete(modalId);
          }, { once: true });
          
          return true;
        }
        
        // Fallback implementation
        return this.showFallback(modalId);
        
      } catch (error) {
        console.error('Error showing modal:', error);
        return this.showFallback(modalId);
      }
    },
    
    hide: function(modalId) {
      try {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) return false;

        // Try Bootstrap first
        if (typeof window.bootstrap !== 'undefined') {
          const modal = window.bootstrap.Modal.getInstance(modalElement);
          if (modal) {
            modal.hide();
            return true;
          }
        }
        
        // Fallback implementation
        return this.hideFallback(modalId);
        
      } catch (error) {
        console.error('Error hiding modal:', error);
        return this.hideFallback(modalId);
      }
    },
    
    showFallback: function(modalId) {
      const modalElement = document.getElementById(modalId);
      const backdrop = this.createBackdrop(modalId);
      
      modalElement.style.display = 'block';
      modalElement.classList.add('show');
      modalElement.setAttribute('aria-hidden', 'false');
      modalElement.setAttribute('aria-modal', 'true');
      modalElement.setAttribute('role', 'dialog');
      
      document.body.classList.add('modal-open');
      document.body.appendChild(backdrop);
      
      this.activeModals.add(modalId);
      this.addFallbackEventListeners(modalId);
      
      return true;
    },
    
    hideFallback: function(modalId) {
      const modalElement = document.getElementById(modalId);
      const backdrop = document.getElementById(modalId + '-backdrop');
      
      modalElement.style.display = 'none';
      modalElement.classList.remove('show');
      modalElement.setAttribute('aria-hidden', 'true');
      modalElement.removeAttribute('aria-modal');
      modalElement.removeAttribute('role');
      
      if (backdrop) backdrop.remove();
      
      if (this.activeModals.size <= 1) {
        document.body.classList.remove('modal-open');
      }
      
      this.activeModals.delete(modalId);
      return true;
    },
    
    createBackdrop: function(modalId) {
      // Remove existing backdrop
      const existingBackdrop = document.getElementById(modalId + '-backdrop');
      if (existingBackdrop) existingBackdrop.remove();
      
      const backdrop = document.createElement('div');
      backdrop.className = 'modal-backdrop fade show';
      backdrop.id = modalId + '-backdrop';
      backdrop.style.zIndex = '1040';
      
      // Click to close
      backdrop.addEventListener('click', () => this.hide(modalId));
      
      return backdrop;
    },
    
    addFallbackEventListeners: function(modalId) {
      const modalElement = document.getElementById(modalId);
      
      // ESC key to close
      const escHandler = (e) => {
        if (e.key === 'Escape' && this.activeModals.has(modalId)) {
          this.hide(modalId);
          document.removeEventListener('keydown', escHandler);
        }
      };
      document.addEventListener('keydown', escHandler);
      
      // Close buttons
      const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
      closeButtons.forEach(button => {
        button.addEventListener('click', () => this.hide(modalId));
      });
      
      // Click outside to close
      modalElement.addEventListener('click', (e) => {
        if (e.target === modalElement) {
          this.hide(modalId);
        }
      });
    },
    
    hideAll: function() {
      this.activeModals.forEach(modalId => this.hide(modalId));
    }
  };

// Convenient wrapper functions
window.showModal = function(modalId) {
  return window.ModalManager.show(modalId);
}

window.hideModal = function(modalId) {
  return window.ModalManager.hide(modalId);
}


// <tr><td><strong>Date of Birth:</strong></td><td>${data.date_of_birth || 'N/A'}</td></tr> Keep this here and do not remove
// Global functions for button actions
window.viewStudent = function(studentId) {
    // Fetch student data from server
    fetch(`/guidance/students/${studentId}`)
      .then(response => response.json())
      .then(data => {
        document.getElementById('studentModalBody').innerHTML = `
          <div class="row">
            <div class="col-md-4 text-center">
              ${data.id_photo_data_url && data.id_photo_data_url !== null ? 
                `<img src="${data.id_photo_data_url}" alt="Student Photo" class="img-fluid rounded-circle mb-3" style="max-width: 150px;">` :
                `<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 150px; height: 150px;">
                   <i class="ri-user-line text-white display-4"></i>
                 </div>`
              }
              <h5>${data.first_name} ${data.last_name}</h5>
              <p class="text-muted">${data.grade_level}${data.section ? ' - ' + data.section : ''}</p>
            </div>
            <div class="col-md-8">
              <h6>Student Information</h6>
              <table class="table table-sm">
                <tbody>
                  <!-- <tr><td><strong>Student ID:</strong></td><td>${data.student_id || 'N/A'}</td></tr> -->
                  <tr><td><strong>LRN:</strong></td><td>${data.lrn || 'N/A'}</td></tr>
                  <tr><td><strong>Gender:</strong></td><td>${data.gender || 'N/A'}</td></tr>
                  
                  <tr><td><strong>Contact:</strong></td><td>${data.contact_number || 'N/A'}</td></tr>
                  <tr><td><strong>Email:</strong></td><td>${data.email || 'N/A'}</td></tr>
                  <tr><td><strong>Address:</strong></td><td>${data.address || 'N/A'}</td></tr>
                  <!-- COMMENTED OUT FOR FUTURE USE: Status Row
                  <tr><td><strong>Status:</strong></td><td>
                    <span class="badge bg-${data.enrollment_status === 'enrolled' ? 'success' : (data.enrollment_status === 'pending' ? 'warning' : 'info')}">
                      ${data.enrollment_status ? data.enrollment_status.charAt(0).toUpperCase() + data.enrollment_status.slice(1) : 'N/A'}
                    </span>
                  </td></tr>
                  -->
                </tbody>
              </table>
              
              <h6 class="mt-3">Parent/Guardian Information</h6>
              <table class="table table-sm">
                <tbody>
                  <tr><td><strong>Father:</strong></td><td>${data.father_name || 'N/A'}</td></tr>
                  <tr><td><strong>Father Contact:</strong></td><td>${data.father_contact || 'N/A'}</td></tr>
                  <tr><td><strong>Mother:</strong></td><td>${data.mother_name || 'N/A'}</td></tr>
                  <tr><td><strong>Mother Contact:</strong></td><td>${data.mother_contact || 'N/A'}</td></tr>
                  <tr><td><strong>Guardian:</strong></td><td>${data.guardian_name || 'N/A'}</td></tr>
                  <tr><td><strong>Guardian Contact:</strong></td><td>${data.guardian_contact || 'N/A'}</td></tr>
                </tbody>
              </table>
              
              ${data.violations && data.violations.length > 0 ? `
                <h6 class="mt-3">Recent Violations (${data.violations.length})</h6>
                <div class="list-group">
                  ${data.violations.slice(0, 3).map(violation => `
                    <div class="list-group-item">
                      <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${violation.title}</h6>
                        <small class="text-muted">${new Date(violation.violation_date).toLocaleDateString()}</small>
                      </div>
                      <p class="mb-1">${violation.description}</p>
                      <small class="badge bg-${violation.severity === 'minor' ? 'success' : (violation.severity === 'major' ? 'warning' : 'danger')}">${violation.severity}</small>
                    </div>
                  `).join('')}
                  ${data.violations.length > 3 ? `<small class="text-muted">... and ${data.violations.length - 3} more</small>` : ''}
                </div>
              ` : '<p class="text-muted mt-3">No violations recorded</p>'}
            </div>
          </div>
        `;
        showModal('studentModal');
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error loading student information');
      });
  }

window.registerFace = function(studentId) {
    // Fetch student data first
    fetch(`/guidance/students/${studentId}/info`)
      .then(response => response.json())
      .then(data => {
        document.getElementById('studentInfoForRegistration').innerHTML = `
          <div class="card">
            <div class="card-body">
              <div class="text-center mb-3">
                ${data.id_photo_data_url && data.id_photo_data_url !== null ? 
                  `<img src="${data.id_photo_data_url}" alt="Student Photo" class="img-fluid rounded-circle" style="max-width: 100px;">` :
                  `<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px;">
                     <i class="ri-user-line text-white"></i>
                   </div>`
                }
              </div>
              <h6 class="text-center">${data.first_name} ${data.last_name}</h6>
              <p class="text-center text-muted mb-1">ID: ${data.student_id || 'N/A'}</p>
              <p class="text-center text-muted">${data.grade_level}${data.section ? ' - ' + data.section : ''}</p>
            </div>
          </div>
        `;
        showModal('faceRegistrationModal');
      })
      .catch(error => {
        console.error('Error:', error);
        document.getElementById('studentInfoForRegistration').innerHTML = `
          <div class="card">
            <div class="card-body">
              <h6>Student ID: ${studentId}</h6>
              <p class="mb-0">Face registration will be implemented soon.</p>
            </div>
          </div>
        `;
        showModal('faceRegistrationModal');
      });
  }

window.viewViolations = function(studentId) {
    window.location.href = `/guidance/violations?student_id=${studentId}`;
  }