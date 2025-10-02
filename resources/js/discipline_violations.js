document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal event listeners
    setTimeout(function() {
      initializeModalEventListeners();
    }, 100);

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
    // Search and filter functionality
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const severityFilter = document.getElementById('severityFilter');
    const typeFilter = document.getElementById('typeFilter');
    const dateFilter = document.getElementById('dateFilter');
    
    function filterTable() {
      const searchTerm = searchInput.value.toLowerCase();
      const statusValue = statusFilter.value;
      const severityValue = severityFilter.value;
      const typeValue = typeFilter.value;
      const dateValue = dateFilter.value;
      const rows = document.querySelectorAll('#violationsTable tbody tr');
      
      rows.forEach(row => {
        if (row.cells.length < 8) return; // Skip empty rows
        
        const student = row.cells[1].textContent.toLowerCase();
        const violation = row.cells[2].textContent.toLowerCase();
        const type = row.cells[3].textContent.toLowerCase();
        const severity = row.cells[4].textContent.toLowerCase();
        const date = row.cells[5].textContent;
        const status = row.cells[6].textContent.toLowerCase();
        
        const matchesSearch = student.includes(searchTerm) || violation.includes(searchTerm);
        const matchesStatus = !statusValue || status.includes(statusValue);
        const matchesSeverity = !severityValue || severity.includes(severityValue);
        const matchesType = !typeValue || type.includes(typeValue);
        const matchesDate = !dateValue || date.includes(new Date(dateValue).toLocaleDateString());
        
        row.style.display = matchesSearch && matchesStatus && matchesSeverity && matchesType && matchesDate ? '' : 'none';
      });
    }
    
    [searchInput, statusFilter, severityFilter, typeFilter, dateFilter].forEach(element => {
      element.addEventListener('input', filterTable);
      element.addEventListener('change', filterTable);
    });
  });

// Global functions for CRUD operations (must be in global scope)
window.viewViolation = function(violationId) {
    // Fetch violation data from server
    fetch(`/guidance/violations/${violationId}`)
      .then(response => response.json())
      .then(data => {
        document.getElementById('viewViolationModalBody').innerHTML = `
          <div class="row">
            <div class="col-md-6">
              <h6>Student Information</h6>
              <table class="table table-sm">
                <tbody>
                  <tr><td><strong>Name:</strong></td><td>${data.student.first_name} ${data.student.last_name}</td></tr>
                  <tr><td><strong>Student ID:</strong></td><td>${data.student.student_id || 'N/A'}</td></tr>
                  <tr><td><strong>Grade Level:</strong></td><td>${data.student.grade_level || 'N/A'}</td></tr>
                  <tr><td><strong>Section:</strong></td><td>${data.student.section || 'N/A'}</td></tr>
                </tbody>
              </table>
              
              <h6 class="mt-3">Violation Details</h6>
              <table class="table table-sm">
                <tbody>
                  <tr><td><strong>Type:</strong></td><td>
                    <span class="badge bg-secondary">${data.violation_type ? data.violation_type.charAt(0).toUpperCase() + data.violation_type.slice(1) : 'N/A'}</span>
                  </td></tr>
                  <tr><td><strong>Severity:</strong></td><td>
                    <span class="badge bg-${data.severity === 'minor' ? 'success' : (data.severity === 'major' ? 'warning' : 'danger')}">
                      ${data.severity ? data.severity.charAt(0).toUpperCase() + data.severity.slice(1) : 'N/A'}
                    </span>
                  </td></tr>
                  <tr><td><strong>Status:</strong></td><td>
                    <span class="badge bg-${data.status === 'pending' ? 'warning' : (data.status === 'resolved' ? 'success' : 'info')}">
                      ${data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : 'N/A'}
                    </span>
                  </td></tr>
                  <tr><td><strong>Date:</strong></td><td>${new Date(data.violation_date).toLocaleDateString()}</td></tr>
                  <tr><td><strong>Time:</strong></td><td>${data.violation_time ? (data.violation_time.length > 5 ? data.violation_time.substring(0, 5) : data.violation_time) : 'N/A'}</td></tr>
                  <tr><td><strong>Location:</strong></td><td>${data.location || 'N/A'}</td></tr>
                </tbody>
              </table>
            </div>
            <div class="col-md-6">
              <h6>Violation Information</h6>
              <div class="mb-3">
                <label class="form-label fw-bold">Title:</label>
                <p>${data.title}</p>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Description:</label>
                <p>${data.description}</p>
              </div>
              
              ${data.witnesses && data.witnesses.length > 0 ? `
                <div class="mb-3">
                  <label class="form-label fw-bold">Witnesses:</label>
                  <ul class="list-unstyled">
                    ${data.witnesses.map(witness => `<li>• ${witness}</li>`).join('')}
                  </ul>
                </div>
              ` : ''}
              
              
              ${data.resolution ? `
                <div class="mb-3">
                  <label class="form-label fw-bold">Resolution:</label>
                  <p>${data.resolution}</p>
                </div>
              ` : ''}
              
              ${data.disciplinary_action ? `
                <div class="mb-3">
                  <label class="form-label fw-bold">Disciplinary Action:</label>
                  <p>${data.disciplinary_action}</p>
                </div>
              ` : ''}
              
              ${data.notes ? `
                <div class="mb-3">
                  <label class="form-label fw-bold">Notes:</label>
                  <p>${data.notes}</p>
                </div>
              ` : ''}
              
              <div class="mb-3">
                <label class="form-label fw-bold">Reported By:</label>
                <p>${data.reported_by ? (data.reported_by.first_name + ' ' + data.reported_by.last_name) : 'N/A'}</p>
              </div>
              
              <div class="mb-3">
                <label class="form-label fw-bold">Reported On:</label>
                <p>${new Date(data.created_at).toLocaleDateString()} at ${new Date(data.created_at).toLocaleTimeString()}</p>
              </div>
              
              ${data.resolved_by ? `
                <div class="mb-3">
                  <label class="form-label fw-bold">Resolved By:</label>
                  <p>${data.resolved_by.first_name} ${data.resolved_by.last_name}</p>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Resolved On:</label>
                  <p>${new Date(data.resolved_at).toLocaleDateString()} at ${new Date(data.resolved_at).toLocaleTimeString()}</p>
                </div>
              ` : ''}
            </div>
          </div>
        `;
        showModal('viewViolationModal');
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error loading violation details');
      });
  }

window.editViolation = function(violationId) {
    // Fetch violation data for editing
    fetch(`/guidance/violations/${violationId}/edit`)
      .then(response => response.json())
      .then(data => {
        const violation = data.violation;
        const students = data.students;
        
        document.getElementById('editViolationForm').action = `/guidance/violations/${violationId}`;
        
        document.getElementById('editViolationModalBody').innerHTML = `
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_student_id" class="form-label">Student <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_student_id" name="student_id" required>
                  ${students.map(student => `
                    <option value="${student.id}" ${student.id === violation.student_id ? 'selected' : ''}>
                      ${student.first_name} ${student.last_name} (${student.student_id || 'No ID'})
                    </option>
                  `).join('')}
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_violation_type" class="form-label">Violation Type <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_violation_type" name="violation_type" required>
                  <option value="late" ${violation.violation_type === 'late' ? 'selected' : ''}>Late Arrival</option>
                  <option value="uniform" ${violation.violation_type === 'uniform' ? 'selected' : ''}>Uniform Violation</option>
                  <option value="misconduct" ${violation.violation_type === 'misconduct' ? 'selected' : ''}>Misconduct</option>
                  <option value="academic" ${violation.violation_type === 'academic' ? 'selected' : ''}>Academic Dishonesty</option>
                  <option value="other" ${violation.violation_type === 'other' ? 'selected' : ''}>Other</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="edit_title" class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_title" name="title" value="${violation.title}" required>
          </div>
          
          <div class="mb-3">
            <label for="edit_description" class="form-label">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="edit_description" name="description" rows="3" required>${violation.description}</textarea>
          </div>
          
          <div class="row">
            <div class="col-md-3">
              <div class="mb-3">
                <label for="edit_severity" class="form-label">Severity <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_severity" name="severity" required>
                  <option value="minor" ${violation.severity === 'minor' ? 'selected' : ''}>Minor</option>
                  <option value="major" ${violation.severity === 'major' ? 'selected' : ''}>Major</option>
                  <option value="severe" ${violation.severity === 'severe' ? 'selected' : ''}>Severe</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label for="edit_violation_date" class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="edit_violation_date" name="violation_date" value="${violation.violation_date ? (violation.violation_date.includes('T') ? violation.violation_date.split('T')[0] : violation.violation_date) : ''}" required>
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label for="edit_violation_time" class="form-label">Time</label>
                <input type="time" class="form-control" id="edit_violation_time" name="violation_time" value="${violation.violation_time ? (violation.violation_time.length > 5 ? violation.violation_time.substring(0, 5) : violation.violation_time) : ''}">
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_status" name="status" required>
                  <option value="pending" ${violation.status === 'pending' ? 'selected' : ''}>Pending</option>
                  <option value="investigating" ${violation.status === 'investigating' ? 'selected' : ''}>Investigating</option>
                  <option value="resolved" ${violation.status === 'resolved' ? 'selected' : ''}>Resolved</option>
                  <option value="dismissed" ${violation.status === 'dismissed' ? 'selected' : ''}>Dismissed</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="edit_location" class="form-label">Location</label>
            <input type="text" class="form-control" id="edit_location" name="location" value="${violation.location || ''}">
          </div>
          
          <div class="mb-3">
            <label for="edit_witnesses" class="form-label">Witnesses</label>
            <textarea class="form-control" id="edit_witnesses" name="witnesses" rows="2">${violation.witnesses ? violation.witnesses.join('\n') : ''}</textarea>
          </div>
          
          
          <div class="mb-3">
            <label for="edit_resolution" class="form-label">Resolution</label>
            <textarea class="form-control" id="edit_resolution" name="resolution" rows="2">${violation.resolution || ''}</textarea>
          </div>
          
          <div class="mb-3">
            <label for="edit_student_statement" class="form-label">Student Statement</label>
            <textarea class="form-control" id="edit_student_statement" name="student_statement" rows="2">${violation.student_statement || ''}</textarea>
          </div>
          
          <div class="mb-3">
            <label for="edit_disciplinary_action" class="form-label">Disciplinary Action</label>
            <textarea class="form-control" id="edit_disciplinary_action" name="disciplinary_action" rows="2">${violation.disciplinary_action || ''}</textarea>
          </div>
          
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="edit_parent_notified" name="parent_notified" value="1" ${violation.parent_notified ? 'checked' : ''}>
              <label class="form-check-label" for="edit_parent_notified">
                Parent/Guardian Notified
              </label>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="edit_notes" class="form-label">Additional Notes</label>
            <textarea class="form-control" id="edit_notes" name="notes" rows="2">${violation.notes || ''}</textarea>
          </div>
        `;
        
        showModal('editViolationModal');
        
        // Add form submission handler
        const form = document.getElementById('editViolationForm');
        const currentViolationId = violationId; // Store violation ID in closure
        form.onsubmit = function(e) {
          e.preventDefault();
          const formData = new FormData(form);
          const submitBtn = form.querySelector('button[type="submit"]');
          const originalText = submitBtn.innerHTML;
          
          // Add CSRF token and method spoofing
          formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
          formData.append('_method', 'PUT');
          
          // Handle checkbox explicitly
          const parentNotifiedCheckbox = form.querySelector('#edit_parent_notified');
          if (parentNotifiedCheckbox) {
            formData.set('parent_notified', parentNotifiedCheckbox.checked ? '1' : '0');
          }
          

          
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<i class="ri-loader-line me-2 spinner-border spinner-border-sm"></i>Updating...';
          
          fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          })
          .then(response => {
            if (response.ok) {
              return response.json();
            } else {
              return response.json().then(errorData => {
                let errorMsg = 'Update failed with status: ' + response.status;
                if (errorData.errors) {
                  errorMsg += '\\n\\nValidation errors:';
                  Object.keys(errorData.errors).forEach(field => {
                    errorMsg += '\\n- ' + field + ': ' + errorData.errors[field].join(', ');
                  });
                }
                if (errorData.message) {
                  errorMsg += '\\n\\nMessage: ' + errorData.message;
                }
                throw new Error(errorMsg);
              });
            }
          })
          .then(data => {
            if (data.success) {
              hideModal('editViolationModal');
              
              // Show success message
              const alertDiv = document.createElement('div');
              alertDiv.className = 'alert alert-success alert-dismissible fade show';
              alertDiv.innerHTML = `
                <strong>Success!</strong> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              `;
              
              const mainContent = document.querySelector('main');
              mainContent.insertBefore(alertDiv, mainContent.firstChild);
              
              // Auto-dismiss after 3 seconds
              setTimeout(() => {
                if (alertDiv.parentNode) {
                  alertDiv.remove();
                }
              }, 3000);
              
              // Update the row in the table instead of full reload
              updateViolationRow(currentViolationId, data.violation);
            } else {
              throw new Error(data.message || 'Update failed');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            
            // Show error message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
              <strong>Error!</strong> ${error.message}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const modalBody = document.getElementById('editViolationModalBody');
            modalBody.insertBefore(alertDiv, modalBody.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
              if (alertDiv.parentNode) {
                alertDiv.remove();
              }
            }, 5000);
          })
          .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
          });
        };
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error loading violation for editing');
      });
  }

window.deleteViolation = function(violationId) {
    if (confirm('Are you sure you want to delete this violation? This action cannot be undone.')) {
      // Show loading state
      const button = event.target.closest('button');
      const originalHTML = button.innerHTML;
      button.innerHTML = '<i class="ri-loader-line spinner-border spinner-border-sm"></i>';
      button.disabled = true;
      
      // Use AJAX for better UX
      fetch(`/guidance/violations/${violationId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          '_method': 'DELETE',
          '_token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        })
      })
      .then(response => {
        if (response.ok) {
          return response.json();
        } else {
          throw new Error('Delete failed with status: ' + response.status);
        }
      })
      .then(data => {
        if (data.success) {
          // Remove the row from table
          const rows = document.querySelectorAll('#violationsTable tbody tr');
          rows.forEach(row => {
            const idCell = row.cells[0];
            if (idCell && idCell.textContent.includes('#' + violationId)) {
              row.remove();
            }
          });
          
          // Show success message
          const alertDiv = document.createElement('div');
          alertDiv.className = 'alert alert-success alert-dismissible fade show';
          alertDiv.innerHTML = `
            <strong>Success!</strong> ${data.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          `;
          
          const mainContent = document.querySelector('main');
          mainContent.insertBefore(alertDiv, mainContent.firstChild);
          
          // Auto-dismiss after 3 seconds
          setTimeout(() => {
            if (alertDiv.parentNode) {
              alertDiv.remove();
            }
          }, 3000);
        } else {
          throw new Error(data.message || 'Delete failed');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        
        // Restore button state
        button.innerHTML = originalHTML;
        button.disabled = false;
        
        // Show error message
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
          <strong>Error!</strong> Failed to delete violation: ${error.message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const mainContent = document.querySelector('main');
        mainContent.insertBefore(alertDiv, mainContent.firstChild);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
          if (alertDiv.parentNode) {
            alertDiv.remove();
          }
        }, 5000);
      });
    }
  }

// Helper function to update violation row in table
window.updateViolationRow = function(violationId, violation) {
    const rows = document.querySelectorAll('#violationsTable tbody tr');
    rows.forEach(row => {
      const idCell = row.cells[0];
      if (idCell && idCell.textContent.includes('#' + violationId)) {
        // Update severity
        const severityCell = row.cells[4];
        const severityClass = violation.severity === 'minor' ? 'success' : 
                             (violation.severity === 'major' ? 'warning' : 'danger');
        severityCell.innerHTML = `<span class="badge bg-${severityClass}">${violation.severity.charAt(0).toUpperCase() + violation.severity.slice(1)}</span>`;
        
        // Update status
        const statusCell = row.cells[6];
        const statusClass = violation.status === 'pending' ? 'warning' : 
                           (violation.status === 'resolved' ? 'success' : 'info');
        statusCell.innerHTML = `<span class="badge bg-${statusClass}">${violation.status.charAt(0).toUpperCase() + violation.status.slice(1)}</span>`;
        
        // Update violation info if title changed
        const violationCell = row.cells[2];
        const titleElement = violationCell.querySelector('strong');
        if (titleElement) {
          titleElement.textContent = violation.title;
        }
        
        // Add visual feedback
        row.style.backgroundColor = '#d4edda';
        setTimeout(() => {
          row.style.backgroundColor = '';
        }, 2000);
      }
    });
  }

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

  // (Functions moved to global scope above)
  
  // Functions are now in global scope above