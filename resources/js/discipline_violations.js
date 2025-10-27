// Global functions for CRUD operations (must be in global scope)

// Global debounce function
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Global variable to store violation options fetched from the database
window.offenseOptions = null;

// Function to fetch violation options from the database
async function fetchViolationOptions() {
    try {
        const response = await fetch('/discipline/violations/summary', { credentials: 'include' });
        if (response.status === 401) {
            alert('Your session has expired. Please log in again.');
            window.location.href = '/discipline/login';
            return { minor: [], major: { "Category 1": [], "Category 2": [], "Category 3": [] } };
        }
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        window.offenseOptions = data.options;
        console.log('Violation options loaded from database:', window.offenseOptions);
        return window.offenseOptions;
    } catch (error) {
        console.error('Failed to fetch violation options:', error);
        // Fallback to empty options if fetch fails
        window.offenseOptions = { minor: [], major: { "Category 1": [], "Category 2": [], "Category 3": [] } };
        return window.offenseOptions;
    }
}

// Automatic Sanction Management System
window.SanctionSystem = {
    // Student violation tracking
    studentViolations: new Map(),
    
    // Initialize from server data
    initFromServer(violationsData = []) {
        this.studentViolations.clear();
        violationsData.forEach(violation => {
            this.addViolationToHistory(
                violation.student_id, 
                violation.severity, 
                violation.category
            );
        });
    },
    
    // Add violation to student history
    addViolationToHistory(studentId, severity, category) {
    if (!this.studentViolations.has(studentId)) {
            this.studentViolations.set(studentId, {
                minorCount: 0,
                majorCount: 0,
                majorByCategory: { 1: 0, 2: 0, 3: 0 },
                allViolations: []
            });
        }

        const studentRecord = this.studentViolations.get(studentId);
        const violationRecord = {
            severity,
            category,
            date: new Date().toISOString(),
            sanction: null
        };

        if (severity === 'minor') {
            studentRecord.minorCount++;

            // Check for escalation: if minor count reaches 3, escalate to major
            if (studentRecord.minorCount >= 3) {
                // Remove the previous 2 minor violations from history
                const minorViolations = studentRecord.allViolations.filter(v => v.severity === 'minor');
                if (minorViolations.length >= 2) {
                    // Remove the first 2 minor violations
                    const indicesToRemove = [];
                    let minorCount = 0;
                    for (let i = 0; i < studentRecord.allViolations.length; i++) {
                        if (studentRecord.allViolations[i].severity === 'minor') {
                            minorCount++;
                            if (minorCount <= 2) {
                                indicesToRemove.push(i);
                            }
                        }
                    }

                    // Remove indices in reverse order to maintain correct indices
                    indicesToRemove.reverse().forEach(index => {
                        studentRecord.allViolations.splice(index, 1);
                    });

                    // Decrement minor count by 2
                    studentRecord.minorCount -= 2;

                    // Increment major count
                    studentRecord.majorCount++;

                    // Add escalation record as major violation (Category 1 by default)
                    const escalationRecord = {
                        severity: 'major',
                        category: 1,
                        date: new Date().toISOString(),
                        sanction: 'Escalated from 3 minor violations',
                        isEscalation: true
                    };
                    studentRecord.allViolations.push(escalationRecord);
                    studentRecord.majorByCategory[1] = (studentRecord.majorByCategory[1] || 0) + 1;
                }
            }
        } else if (severity === 'major') {
            studentRecord.majorCount++;
            if (category) {
                studentRecord.majorByCategory[category] =
                    (studentRecord.majorByCategory[category] || 0) + 1;
            }
        }

        studentRecord.allViolations.push(violationRecord);
        return studentRecord;
    },
    
    // Calculate automatic sanction based on policy
    calculateAutomaticSanction(studentId, severity, category) {
        const studentRecord = this.addViolationToHistory(studentId, severity, category);
        
        if (severity === 'minor') {
            return this.calculateMinorSanction(studentRecord.minorCount);
        } else if (severity === 'major') {
            return this.calculateMajorSanction(studentRecord.majorCount, category);
        }
        
        return null;
    },
    
    // Minor offense sanctions (from policy 2.6.1)
    calculateMinorSanction(offenseCount) {
        switch(offenseCount) {
            case 1:
                return {
                    sanction: "Verbal reprimand / warning",
                    deportmentGrade: "No change",
                    suspension: "None",
                    notes: "First minor offense"
                };
            case 2:
                return {
                    sanction: "Written warning", 
                    deportmentGrade: "No change",
                    suspension: "None",
                    notes: "Second minor offense"
                };
            case 3:
        return {
          sanction: "Escalated sanction",
          deportmentGrade: "Lowered by one step",
          suspension: "None",
          notes: "Third minor offense - cumulative sanction"
        };
            default:
                return {
                    sanction: "One step lower in Deportment Grade",
                    deportmentGrade: "Lowered by one step", 
                    suspension: "None",
                    notes: `Repeat minor offense (${offenseCount} total)`
                };
        }
    },
    
    // Major offense sanctions (from policy 2.6.2)
    calculateMajorSanction(offenseCount, category) {
        const categoryText = category ? `Category ${category}` : "Major";
        
        switch(offenseCount) {
            case 1:
                return {
                    sanction: "One step lower in Deportment Grade, CS",
                    deportmentGrade: "Lowered by one step",
                    suspension: "None",
                    notes: `First ${categoryText} offense - Community Service required`
                };
            case 2:
                let suspensionDays = "3-5 days";
                if (category === 3) suspensionDays = "5-7 days";
                
                return {
                    sanction: "NI in Deportment, " + suspensionDays + " suspension, CS",
                    deportmentGrade: "Needs Improvement (NI)",
                    suspension: suspensionDays,
                    notes: `Second ${categoryText} offense - escalating sanctions`
                };
            case 3:
                return {
                    sanction: "NI in Deportment, Dismissal or Expulsion",
                    deportmentGrade: "Needs Improvement (NI)", 
                    suspension: "Dismissal/Expulsion",
                    notes: `Third ${categoryText} offense - maximum sanction`
                };
            default:
                return {
                    sanction: "NI in Deportment, Dismissal or Expulsion",
                    deportmentGrade: "Needs Improvement (NI)",
                    suspension: "Dismissal/Expulsion", 
                    notes: `Multiple ${categoryText} offenses (${offenseCount} total) - disciplinary hearing required`
                };
        }
    },
    
    // Get student violation summary
    getStudentSummary(studentId) {
        if (!this.studentViolations.has(studentId)) {
            return {
                minorCount: 0,
                majorCount: 0,
                totalCount: 0,
                currentSanction: "No violations"
            };
        }
        
        const record = this.studentViolations.get(studentId);
        return {
            minorCount: record.minorCount,
            majorCount: record.majorCount,
            majorByCategory: {...record.majorByCategory},
            totalCount: record.minorCount + record.majorCount,
            allViolations: [...record.allViolations]
        };
    },
    
    // Reset student violations (for administrative purposes)
    resetStudentRecord(studentId) {
        this.studentViolations.delete(studentId);
    }
};

// Enhanced violation form submission
function setupEnhancedViolationSubmission() {
    const violationForm = document.getElementById('recordViolationForm');
    if (!violationForm) return;

  violationForm.addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!window.selectedStudents || window.selectedStudents.length === 0) {
      alert('Please select at least one student for the violation.');
      return;
    }

    const submitBtn = document.querySelector('#recordViolationModal button[type="submit"]');
    const originalText = submitBtn.textContent;

    // Show loading state
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;

    try {
      const title = getViolationTitle();
      const severity = window.titleToSeverityMap[title]?.severity || 'minor';
      const category = window.titleToSeverityMap[title]?.category || null;

      // Process each student
      const results = [];
      for (const student of window.selectedStudents) {
        const formData = new FormData();
        formData.append('student_id', student.id);
        formData.append('title', title);
        formData.append('violation_date', document.getElementById('violationDate').value);
        formData.append('violation_time', document.getElementById('violationTime').value);
        formData.append('severity', severity);
        formData.append('major_category', category);
        formData.append('status', 'pending');

        const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenEl ? csrfTokenEl.getAttribute('content') : '';
        formData.append('_token', csrfToken);

        const response = await fetch('/discipline/violations', {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: formData
        });

        if (response.status === 401) {
          // User is not authenticated, redirect to login
          alert('Your session has expired. Please log in again.');
          window.location.href = '/discipline/login';
          return;
        }

        if (response.status === 409) {
          // Duplicate violation detected
          showDuplicateViolationModal();
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
          return;
        }

        if (!response.ok) {
          throw new Error(`Server error: ${response.status}`);
        }

        const data = await response.json();
        if (!data.success) {
          throw new Error(data.message || 'Submission failed');
        }

        results.push({
          student: student.name,
          data: data
        });
      }

      alert(`Violation recorded successfully for ${window.selectedStudents.length} student(s)!`);

      // Close modal and refresh
      const modal = bootstrap.Modal.getInstance(document.getElementById('recordViolationModal'));
      if (modal) modal.hide();
      window.location.reload();

    } catch (err) {
      console.error('Violation submission error:', err);
      alert('Error submitting violation: ' + err.message);
    } finally {
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    }
  });
}

// Show modal for duplicate violation
function showDuplicateViolationModal(message) {
  let modal = document.getElementById('duplicateViolationModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'duplicateViolationModal';
    modal.tabIndex = -1;
    modal.innerHTML = `
    <div class="modal-dialog">
      <div class="modal-content" style="background:#fff; border:2px solid #198754;">
        <div class="modal-header" style="background:#198754; color:#fff;">
          <h5 class="modal-title" style="color:#fff;">Duplicate Violation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0" style="color:#198754;">${message || 'No same violation within the same date is allowed.'}</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn" style="background:#198754;color:#fff;" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>`;
    document.body.appendChild(modal);
  } else {
    // Update message if modal already exists
    const msgP = modal.querySelector('.modal-body p');
    if (msgP) msgP.textContent = message || 'No same violation within the same date is allowed.';
  }
  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();
}



// Enhanced student sanction overview
function loadStudentSanctionOverview(studentId) {
    const summary = window.SanctionSystem.getStudentSummary(studentId);
    const card = document.getElementById('sanctionOverviewCard');
    const content = document.getElementById('sanctionOverviewContent');
    
    if (!card || !content) return;
    
    if (summary.totalCount === 0) {
        card.style.display = 'none';
        return;
    }
    
    card.style.display = 'block';
    
    content.innerHTML = `
        <div class="row text-center mb-3">
            <div class="col-md-3">
                <div class="border rounded p-2">
                    <div class="h5 mb-1 text-primary">${summary.totalCount}</div>
                    <small class="text-muted">Total Violations</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-2">
                    <div class="h5 mb-1 text-warning">${summary.minorCount}</div>
                    <small class="text-muted">Minor Offenses</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-2">
                    <div class="h5 mb-1 text-danger">${summary.majorCount}</div>
                    <small class="text-muted">Major Offenses</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-2">
                    <div class="h6 mb-1">${getCurrentSanction(summary)}</div>
                    <small class="text-muted">Current Status</small>
                </div>
            </div>
        </div>
        
        ${summary.majorCount > 0 ? `
            <div class="mb-3">
                <h6>Major Offenses by Category:</h6>
                <div class="d-flex gap-2">
                    ${Object.entries(summary.majorByCategory).map(([cat, count]) => 
                        count > 0 ? `<span class="badge bg-danger">Category ${cat}: ${count}</span>` : ''
                    ).join('')}
                </div>
            </div>
        ` : ''}
        
        <div class="alert alert-warning">
            <small>
                <strong>Next Offense Consequences:</strong><br>
                ${getNextSanctionWarning(summary)}
            </small>
        </div>
    `;
}

function getCurrentSanction(summary) {
    if (summary.majorCount >= 3) return 'Dismissal/Expulsion';
    if (summary.majorCount === 2) return 'Suspension + NI Grade';
    if (summary.majorCount === 1) return 'Grade Reduction + CS';
    if (summary.minorCount >= 3) return 'Grade Reduction';
    if (summary.minorCount === 2) return 'Written Warning';
    if (summary.minorCount === 1) return 'Verbal Warning';
    return 'Clear Record';
}

function getNextSanctionWarning(summary) {
    if (summary.majorCount >= 2) return 'Next major offense may result in dismissal or expulsion';
    if (summary.majorCount === 1) return 'Next major offense: suspension and NI grade';
    if (summary.minorCount >= 2) return 'Next minor offense: reduction in deportment grade';
    return 'Maintain clear record';
}

window.editViolation = function(violationId) {
    console.log('🚀 editViolation called with id:', violationId);

    // Show loading state immediately
    const modalBody = document.getElementById('editViolationModalBody');
    if (modalBody) {
        modalBody.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading violation data for ID: ${violationId}...</p>
            </div>
        `;
    }

    // Show modal immediately
    try {
        window.ModalManager.show('editViolationModal');
        console.log('✅ Modal shown successfully');
    } catch (modalError) {
        console.error('❌ Modal error:', modalError);
    }

    // Fetch violation data for editing
    console.log('📡 Making API call to:', `/discipline/violations/${violationId}/edit`);

        fetch(`/discipline/violations/${violationId}/edit`, { credentials: 'include' })
        .then(response => {
            console.log('📡 Response received:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok,
                url: response.url
            });

            if (response.status === 401) {
                alert('Your session has expired. Please log in again.');
                window.location.href = '/discipline/login';
                throw new Error('Authentication required');
            }

            if (!response.ok) {
                // Handle HTTP errors (404, 500, etc.)
                if (response.status === 404) {
                    throw new Error(`Violation not found (404). Check if the ID ${violationId} exists.`);
                } else if (response.status === 500) {
                    throw new Error('Server error (500). Please check the server logs.');
                } else {
                    throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                }
            }
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned non-JSON response');
            }
            
            return response.json();
        })
        .then(data => {
            console.log('✅ Data received:', data);
            
            if (!data) {
                throw new Error('No data received from server');
            }
            
            if (!data.violation) {
                throw new Error('No violation data in response');
            }

            const violation = data.violation;
            const students = data.students || [];

            console.log('📝 Violation data:', violation);
            console.log('👥 Students data:', students);

            // Update form action
            const form = document.getElementById('editViolationForm');
            if (!form) {
                throw new Error('Edit form not found');
            }
            form.action = `/discipline/violations/${violationId}`;

            // Use the same structure as viewViolation but with editable inputs
            const studentSelectDisabled = 'disabled';
            const studentSelectHelp = '<small class="text-muted">Student cannot be changed.</small>';

            // Populate modal body
            modalBody.innerHTML = `
    <div class="row">
        <!-- Left Column: Student Info & Basic Violation Details -->
        <div class="col-lg-6">
            <h6 class="mb-3">Student Information</h6>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label fw-bold small">Student Name</label>
                    <input type="hidden" name="student_id" value="${violation.student_id}">
                    <select class="form-select form-select-sm" id="edit_student_id" name="student_id_display" required ${studentSelectDisabled}>
                        ${students.length > 0 ? students.map(student => `
                            <option value="${student.id}" ${student.id == (violation.student ? violation.student.id : violation.student_id) ? 'selected' : ''}>
                                ${student.first_name} ${student.last_name} (${student.student_id || 'No ID'})
                            </option>
                        `).join('') : '<option value="">No students available</option>'}
                    </select>
                    ${studentSelectHelp}
                </div>
            </div>

           
            <div class="mb-2">
                <label class="form-label fw-bold small">Title</label>
                <input type="text" class="form-control form-control-sm" id="edit_title" name="title" value="${violation.title || ''}" required>
            </div>

            <div class="col-12">
    <label class="form-label fw-bold small">Status</label>
    <select class="form-select form-select-sm" id="edit_status" name="status" required disabled>
        <option value="pending" ${violation.status === 'pending' ? 'selected' : ''}>Pending</option>
                <option value="investigating" ${violation.status === 'investigating' ? 'selected' : ''}>In Progress</option>
        <option value="resolved" ${violation.status === 'resolved' ? 'selected' : ''}>Resolved</option>
    </select>
    <small class="text-muted">Status cannot be changed.</small>
</div>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label fw-bold small">Date</label>
                    <input type="date" class="form-control form-control-sm" id="edit_violation_date" name="violation_date" value="${violation.violation_date ? (violation.violation_date.includes('T') ? violation.violation_date.split('T')[0] : violation.violation_date) : ''}" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small">Time</label>
                    <input type="time" class="form-control form-control-sm" id="edit_violation_time" name="violation_time" value="${violation.violation_time ? (violation.violation_time.length > 5 ? violation.violation_time.substring(0, 5) : violation.violation_time) : ''}">
                </div>
            </div>
        </div>

        <!-- Right Column: Investigation & Resolution Details -->
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label fw-bold small">Student Statement</label>
                <textarea class="form-control form-control-sm" id="edit_student_statement" name="student_statement" rows="3">${violation.student_statement || ''}</textarea>
            </div>

            <h6 class="mt-3 mb-3">Resolution Details</h6>
            <div class="mb-2" id="edit_resolution_wrapper" style="display: ${(violation.status === 'resolved' || violation.status === 'dismissed') ? 'block' : 'none'};">
                <label class="form-label fw-bold small">Resolution</label>
                <textarea class="form-control form-control-sm" id="edit_resolution" name="resolution" rows="2">${violation.resolution || ''}</textarea>
            </div>
            <div class="mb-2">
                <label class="form-label fw-bold small">Disciplinary Action</label>
                <textarea class="form-control form-control-sm" id="edit_disciplinary_action" name="disciplinary_action" rows="2">${violation.disciplinary_action || ''}</textarea>
            </div>
            <div class="mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="edit_parent_notified" name="parent_notified" value="1" ${violation.parent_notified ? 'checked' : ''}>
                    <label class="form-check-label fw-bold small" for="edit_parent_notified">
                        Parent/Guardian Notified
                    </label>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small">Additional Notes</label>
                <textarea class="form-control form-control-sm" id="edit_notes" name="notes" rows="3">${violation.notes || ''}</textarea>
            </div>
        </div>
    </div>
`;


            console.log('✅ Modal populated successfully');

            // Add event listeners for dynamic form behavior
            const editStatusSelect = document.getElementById('edit_status');
            const editResolutionWrapper = document.getElementById('edit_resolution_wrapper');

            if (editStatusSelect) {
                editStatusSelect.addEventListener('change', function() {
                    if (editResolutionWrapper) {
                        editResolutionWrapper.style.display = (this.value === 'resolved' || this.value === 'dismissed') ? 'block' : 'none';
                    }
                });
            }

            // Add form submission handler
            const currentViolationId = violationId;
            form.onsubmit = async function(e) {
                e.preventDefault();
                console.log('📤 Form submission started');

                const formData = new FormData(form);
            const submitBtn = document.querySelector('#editViolationModal button[type="submit"]');
            const originalText = submitBtn.innerHTML;

                // Add CSRF token and method spoofing
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('_method', 'PUT');

                // Ensure required fields are included (disabled fields may not be in FormData)
                formData.append('severity', violation.severity || 'minor');
                formData.append('status', violation.status || 'pending');

                // Handle checkbox explicitly
                const parentNotifiedCheckbox = form.querySelector('#edit_parent_notified');
                if (parentNotifiedCheckbox) {
                    formData.set('parent_notified', parentNotifiedCheckbox.checked ? '1' : '0');
                }

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ri-loader-line me-2 spinner-border spinner-border-sm"></i>Updating...';

                try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'include',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                    console.log('📡 Update response status:', response.status);

                    if (!response.ok) {
                        const errorData = await response.json();
                        let errorMsg = 'Update failed with status: ' + response.status;
                        if (errorData.errors) {
                            errorMsg += '\n\nValidation errors:';
                            Object.keys(errorData.errors).forEach(field => {
                                errorMsg += '\n- ' + field + ': ' + errorData.errors[field].join(', ');
                            });
                        }
                        if (errorData.message) {
                            errorMsg += '\n\nMessage: ' + errorData.message;
                        }
                        throw new Error(errorMsg);
                    }

                    const data = await response.json();
                    if (data.success) {
                        console.log('✅ Update successful:', data);
                        window.ModalManager.hide('editViolationModal');

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
                        if (typeof updateViolationRow === 'function') {
                            updateViolationRow(currentViolationId, data.violation);
                        } else {
                            console.warn('updateViolationRow function not found, reloading page');
                            window.location.reload();
                        }
                    } else {
                        throw new Error(data.message || 'Update failed');
                    }
                } catch (error) {
                    console.error('❌ Form submission error:', error);

                    // Show error message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        <strong>Error!</strong> ${error.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;

                    modalBody.insertBefore(alertDiv, modalBody.firstChild);

                    // Auto-dismiss after 5 seconds
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 5000);
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            };
        })
        .catch(error => {
            console.error('❌ Fetch error:', error);
            
            // Show detailed error in modal
            if (modalBody) {
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <h6>Error Loading Violation</h6>
                        <p><strong>${error.message}</strong></p>
                        <p>URL: <code>/discipline/violations/${violationId}/edit</code></p>
                        <div class="mt-3">
                            <button class="btn btn-sm btn-outline-secondary" onclick="window.ModalManager.hide('editViolationModal')">
                                Close
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="window.editViolation(${violationId})">
                                Retry
                            </button>
                        </div>
                    </div>
                `;
            }
            
            alert('Failed to load violation: ' + error.message);
        });
};
console.log('editViolation function defined on window:', typeof window.editViolation);

// Helper function to get violation title
function getViolationTitle() {
    const violationTitleSelect = document.getElementById('violationTitle');
    const customInput = document.getElementById('customOffenseText');

    if (!violationTitleSelect) {
        throw new Error('Violation title element is missing.');
    }

    if (violationTitleSelect.value === 'custom' && customInput && customInput.value.trim()) {
        return customInput.value.trim();
    }
    return violationTitleSelect.value;
}

document.addEventListener('DOMContentLoaded', async function() {
    // Initialize sanction system when page loads
    fetch('/discipline/violations/summary')
        .then(response => response.json())
        .then(data => {
            window.SanctionSystem.initFromServer(data.violations);
        })
        .catch(error => {
            console.error('Error loading violation summary:', error);
        });

    // Fetch violation options and initialize mappings
    await fetchViolationOptions();

    // Create reverse mapping: title -> {severity, category}
    window.titleToSeverityMap = {};
    if (window.offenseOptions) {
        window.offenseOptions.minor.forEach(title => {
            window.titleToSeverityMap[title] = { severity: 'minor', category: null };
        });
        Object.keys(window.offenseOptions.major).forEach(category => {
            window.offenseOptions.major[category].forEach(title => {
                window.titleToSeverityMap[title] = { severity: 'major', category: category };
            });
        });
    }

    const violationTitleSelect = document.getElementById('violationTitle');

    // Handle title selection and custom offense input
    if (violationTitleSelect) {
        violationTitleSelect.addEventListener('change', function() {
            const selectedTitle = this.value;

            // Automatically determine severity and category if title is predefined
            if (selectedTitle && selectedTitle !== 'custom' && window.titleToSeverityMap[selectedTitle]) {
                // Re-select the current title
                this.value = selectedTitle;
            }

            // Handle custom offense input
            const existingCustomInput = document.querySelector('#customOffenseInput');
            if (selectedTitle === 'custom') {
                if (!existingCustomInput) {
                    // Create custom input field
                    const inputGroup = document.createElement('div');
                    inputGroup.className = 'input-group mt-2';
                    inputGroup.id = 'customOffenseInput';
                    inputGroup.innerHTML = `
                        <input type="text" class="form-control" id="customOffenseText" placeholder="Enter custom offense...">
                        <button type="button" class="btn btn-outline-secondary" onclick="useCustomOffense()">Use</button>
                    `;
                    violationTitleSelect.parentNode.appendChild(inputGroup);
                }
            } else if (existingCustomInput) {
                existingCustomInput.remove();
            }

            // Change submit button text and behavior based on severity
            const submitBtn = document.querySelector('#recordViolationModal button[type="submit"]');
            const studentSearchInput = document.getElementById('violationStudentSearch');
            if (submitBtn) {
                const severity = window.titleToSeverityMap[selectedTitle]?.severity;
                if (severity === 'major') {
                    // Automatically show incident form for major violations
                    showIncidentForm();
                    // Disable student search input for major violations
                    if (studentSearchInput) {
                        studentSearchInput.disabled = true;
                    }
                } else {
                    // For minor violations, close incident form if open and show violation form
                    window.ModalManager.hide('incidentFormModal');
                    submitBtn.textContent = 'Submit Violation';
                    submitBtn.type = 'submit';
                    submitBtn.onclick = null;
                    // Enable student search input for minor violations
                    if (studentSearchInput) {
                        studentSearchInput.disabled = false;
                    }
                }
            }
        });
    }

    // Function to populate all offenses into the title dropdown
    function populateAllOffenses() {
        if (!violationTitleSelect) return;

        // Clear current options
        violationTitleSelect.innerHTML = '<option value="">-- Select Offense --</option>';

        // Check if violation options are loaded
        if (!window.offenseOptions) {
            console.warn('Violation options not loaded yet');
            return;
        }

        // Add minor offenses
        window.offenseOptions.minor.forEach(offense => {
            const option = document.createElement('option');
            option.value = offense;
            option.textContent = offense;
            violationTitleSelect.appendChild(option);
        });

        // Add major offenses from all categories
        Object.keys(window.offenseOptions.major).forEach(category => {
            window.offenseOptions.major[category].forEach(offense => {
                const option = document.createElement('option');
                option.value = offense;
                option.textContent = offense;
                violationTitleSelect.appendChild(option);
            });
        });

        // Add custom option
        const customOption = document.createElement('option');
        customOption.value = 'custom';
        customOption.textContent = '-- Custom Offense --';
        violationTitleSelect.appendChild(customOption);
    }

    // Set student info when modal is shown
    const violationModal = document.getElementById('recordViolationModal');
    if (violationModal) {
        violationModal.addEventListener('show.bs.modal', async function(event) {
            const button = event.relatedTarget;
            const studentId = button ? button.getAttribute('data-student-id') : null;

            // Reset student search fields
            const studentSearchInput = document.getElementById('violationStudentSearch');
            const studentSuggestions = document.getElementById('studentSuggestions');
            const selectedStudentsContainer = document.getElementById('selectedStudentsContainer');

            if (studentSearchInput) studentSearchInput.value = '';
            if (studentSuggestions) studentSuggestions.style.display = 'none';
            if (selectedStudentsContainer) selectedStudentsContainer.innerHTML = '';

            // Initialize selected students array
            window.selectedStudents = [];

            // If student ID provided, add it to selected students
            if (studentId) {
                // Fetch student details and add to selected
                fetch(`/discipline/students/${studentId}`)
                    .then(response => response.json())
                    .then(student => {
                        window.selectedStudents.push({
                            id: student.id,
                            name: `${student.first_name} ${student.last_name} (${student.student_id || 'No ID'})`
                        });
                        updateSelectedStudentsDisplay();
                        // Load sanction overview for the student
                        loadStudentSanctionOverview(student.id);
                    })
                    .catch(error => console.error('Error fetching student:', error));
            }

            // Reset form

            // Ensure violation options are loaded before populating
            if (!window.offenseOptions) {
                await fetchViolationOptions();
            }

            // Populate all offenses in the title dropdown
            populateAllOffenses();

            // Remove custom input if exists
            const customInput = document.getElementById('customOffenseInput');
            if (customInput) customInput.remove();
        });
    }

    // Enhanced form submission handler with automatic sanctions
    setupEnhancedViolationSubmission();



    // Initialize modal event listeners
    setTimeout(function() {
      initializeModalEventListeners();
    }, 100);

    function initializeModalEventListeners() {
      // Add close button functionality to all modals
      document.querySelectorAll('.modal').forEach(modal => {
        if (modal) {
          const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
          closeButtons.forEach(button => {
            if (button) {
              button.addEventListener('click', function() {
                hideModal(modal.id);
              });
            }
          });

          // Close on backdrop click
          modal.addEventListener('click', function(e) {
            if (e.target === modal) {
              hideModal(modal.id);
            }
          });
        }
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
    const dateFilter = document.getElementById('dateFilter');

    function filterTable() {
      const searchTerm = searchInput.value.toLowerCase();
      const dateValue = dateFilter.value;
      const rows = document.querySelectorAll('#violationsTable tbody tr');

      rows.forEach(row => {
        if (row.cells.length < 5 || !row.cells[0] || !row.cells[1] || !row.cells[2]) return; // Skip empty or malformed rows

        const student = row.cells[0].textContent.toLowerCase();
        const violation = row.cells[1].textContent.toLowerCase();
        const date = row.cells[2].textContent.trim();

        const matchesSearch = student.includes(searchTerm) || violation.includes(searchTerm);

        // Date filtering: convert filter date to same format as table (M d, Y)
        let matchesDate = true;
        if (dateValue) {
          const filterDate = new Date(dateValue);
          const formattedFilterDate = filterDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
          }); // Keep comma to match "Jan 15, 2024"
          matchesDate = date.includes(formattedFilterDate);
        }

        row.style.display = matchesSearch && matchesDate ? '' : 'none';
      });
    }

    [searchInput, dateFilter].forEach(element => {
      if (element) {
        element.addEventListener('input', filterTable);
        element.addEventListener('change', filterTable);
      }
    });

    // Student search functionality for violation modal
    const studentSearchInput = document.getElementById('violationStudentSearch');
    const studentSuggestions = document.getElementById('studentSuggestions');
    const selectedStudentsContainer = document.getElementById('selectedStudentsContainer');

    let searchTimeout;
    let currentFocus = -1;


    function searchStudents(query) {
      if (query.length < 2) {
        studentSuggestions.style.display = 'none';
        return;
      }

      fetch(`/discipline/students/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(students => {
          displaySuggestions(students);
        })
        .catch(error => {
          console.error('Error searching students:', error);
          studentSuggestions.style.display = 'none';
        });
    }

    function displaySuggestions(students) {
      if (students.length === 0) {
        studentSuggestions.style.display = 'none';
        return;
      }

      const suggestionsHtml = students.map(student => `
        <div class="suggestion-item" data-student-id="${student.id}" data-student-name="${student.first_name} ${student.last_name} (${student.student_id || 'No ID'})">
          <div class="suggestion-name">${student.first_name} ${student.last_name}</div>
          <div class="suggestion-details">ID: ${student.student_id || 'No ID'} | Grade: ${student.grade_level || 'N/A'} | Section: ${student.section || 'N/A'}</div>
        </div>
      `).join('');

      studentSuggestions.innerHTML = suggestionsHtml;
      studentSuggestions.style.display = 'block';
      currentFocus = -1;
    }

    function selectStudent(studentId, studentName) {
      if (!window.selectedStudents.some(s => s.id === studentId)) {
        window.selectedStudents.push({ id: studentId, name: studentName });
        updateSelectedStudentsDisplay();
        // Load sanction overview for the selected student
        loadStudentSanctionOverview(studentId);
      }
      studentSearchInput.value = '';
      studentSuggestions.style.display = 'none';
      currentFocus = -1;
    }

    function updateSelectedStudentsDisplay() {
      selectedStudentsContainer.innerHTML = window.selectedStudents.map(student => `
        <div class="badge bg-primary me-2 mb-2 d-inline-flex align-items-center">
          ${student.name}
          <button type="button" class="btn-close btn-close-white ms-2" onclick="removeSelectedStudent(${student.id})" style="font-size: 0.6em;"></button>
        </div>
      `).join('');
    }

    window.removeSelectedStudent = function(studentId) {
      const index = window.selectedStudents.findIndex(s => s.id === studentId);
      if (index > -1) {
        window.selectedStudents.splice(index, 1);
        updateSelectedStudentsDisplay();
        // Update sanction overview
        if (window.selectedStudents.length > 0) {
          loadStudentSanctionOverview(window.selectedStudents[0].id);
        } else {
          const card = document.getElementById('sanctionOverviewCard');
          if (card) card.style.display = 'none';
        }
      }
    };

    const debouncedSearch = debounce(searchStudents, 300);

    if (studentSearchInput) {
      studentSearchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        debouncedSearch(query);
      });

      studentSearchInput.addEventListener('keydown', function(e) {
        const items = studentSuggestions.querySelectorAll('.suggestion-item');

        if (e.key === 'ArrowDown') {
          e.preventDefault();
          currentFocus = currentFocus < items.length - 1 ? currentFocus + 1 : 0;
          updateFocus(items);
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          currentFocus = currentFocus > 0 ? currentFocus - 1 : items.length - 1;
          updateFocus(items);
        } else if (e.key === 'Enter') {
          e.preventDefault();
          if (currentFocus >= 0 && items[currentFocus]) {
            const item = items[currentFocus];
            const studentId = item.getAttribute('data-student-id');
            const studentName = item.getAttribute('data-student-name');
            selectStudent(studentId, studentName);
          }
        } else if (e.key === 'Escape') {
          studentSuggestions.style.display = 'none';
          currentFocus = -1;
        }
      });

      // Click outside to close suggestions
      document.addEventListener('click', function(e) {
        if (!studentSearchInput.contains(e.target) && !studentSuggestions.contains(e.target)) {
          studentSuggestions.style.display = 'none';
          currentFocus = -1;
        }
      });
    }

    if (studentSuggestions) {
      studentSuggestions.addEventListener('click', function(e) {
        const item = e.target.closest('.suggestion-item');
        if (item) {
          const studentId = item.getAttribute('data-student-id');
          const studentName = item.getAttribute('data-student-name');
          selectStudent(studentId, studentName);
        }
      });
    }

    function updateFocus(items) {
      // Remove previous focus
      items.forEach(item => item.classList.remove('active'));

      // Add focus to current item
      if (items[currentFocus]) {
        items[currentFocus].classList.add('active');
        items[currentFocus].scrollIntoView({ block: 'nearest' });
      }
    }
  });

// Global functions for CRUD operations (must be in global scope)

window.viewViolation = function(violationId) {
    // Fetch violation data from server
    fetch(`/discipline/violations/${violationId}`, { credentials: 'include' })
      .then(response => response.json())
      .then(data => {
        // Build the PDF URL for the narrative report
        let narrativePdfUrl = '';
        if (data.student && data.id && data.severity === 'major') {
          narrativePdfUrl = `/narrative-report/view/${data.student.id}/${data.id}`;
        }

        document.getElementById('viewViolationModalBody').innerHTML = `
          <div class="row">
            <div class="col-md-6">
              <h6>Student Information</h6>
              <table class="table table-sm">
                <tbody>
                  <tr><td><strong>Name:</strong></td><td>${data.student && data.student.first_name ? data.student.first_name : 'N/A'} ${data.student && data.student.last_name ? data.student.last_name : ''}</td></tr>
                  <tr><td><strong>Student ID:</strong></td><td>${data.student && data.student.student_id ? data.student.student_id : 'N/A'}</td></tr>
                  <tr><td><strong>Grade Level:</strong></td><td>${data.student && data.student.grade_level ? data.student.grade_level : 'N/A'}</td></tr>
                  <tr><td><strong>Section:</strong></td><td>${data.student && data.student.section ? data.student.section : 'N/A'}</td></tr>
                </tbody>
              </table>
              
              <h6 class="mt-3">Violation Details</h6>
              <table class="table table-sm">
                <tbody>
                  <tr><td><strong>Status:</strong></td><td>
                    <span class="badge bg-${data.status === 'pending' ? 'warning' : (data.status === 'resolved' ? 'success' : 'info')}">
                      ${data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : 'N/A'}
                    </span>
                  </td></tr>
                  <tr><td><strong>Date:</strong></td><td>${new Date(data.violation_date).toLocaleDateString()}</td></tr>
                  <tr><td><strong>Time:</strong></td><td>${data.violation_time ? (data.violation_time.length > 5 ? data.violation_time.substring(0, 5) : data.violation_time) : 'N/A'}</td></tr>
                </tbody>
              </table>
              <!-- Narrative PDF Attachment (if available) -->
              ${narrativePdfUrl ? `<div class="mt-4"><label class="form-label fw-bold">Student Narrative Report (PDF):</label><div><a href="${narrativePdfUrl}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="ri-attachment-2"></i> View Attachment</a></div></div>` : ''}
            </div>
            <div class="col-md-6">
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



window.deleteViolation = function(violationId) {
    if (confirm('Are you sure you want to delete this violation? This action cannot be undone.')) {
      // Show loading state
      const button = event.target.closest('button');
      const originalHTML = button.innerHTML;
      button.innerHTML = '<i class="ri-loader-line spinner-border spinner-border-sm"></i>';
      button.disabled = true;

      // Use AJAX for better UX
      fetch(`/discipline/violations/${violationId}`, {
        method: 'POST',
        credentials: 'include',
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
  .then((data) => {
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
        const severityClass = violation.severity === 'minor' ? 'success' : 'warning';
        severityCell.innerHTML = `<span class="badge bg-${severityClass}">${violation.severity === 'minor' ? 'Minor Offense' : 'Major Offense'}</span>`;
        
        // Update status
        const statusCell = row.cells[6];
        const statusClass = violation.status === 'pending' ? 'warning' : 
                           (violation.status === 'resolved' ? 'success' : 'info');
        statusCell.innerHTML = `<span class="badge bg-${statusClass}">${violation.status.charAt(0).toUpperCase() + violation.status.slice(1)}</span>`;
        
        // Update violation info if title changed
        const violationCell = row.cells[0];
        const titleElement = violationCell.querySelector('strong');
        if (titleElement) {
          titleElement.textContent = violation.title;
        }

        // Update student name if changed
        const studentCell = row.cells[1];
        if (studentCell && violation.student) {
          studentCell.innerHTML = `
            <div>
              <strong>${violation.student.first_name} ${violation.student.last_name}</strong>
              <br><small class="text-muted">${violation.student.student_id || 'No ID'}</small>
            </div>
          `;
        }
        
        // Add visual feedback
        row.style.backgroundColor = '#d4edda';
        setTimeout(() => {
          row.style.backgroundColor = '';
        }, 2000);
      }
    });
  }

// Forward violation to case meeting
window.forwardViolation = function(violationId) {
    if (confirm('Are you sure you want to forward this violation to case meeting?')) {
      // Show loading state
      const button = event.target.closest('button');
      const originalHTML = button.innerHTML;
      button.innerHTML = '<i class="ri-loader-line spinner-border spinner-border-sm"></i>';
      button.disabled = true;

      // Use AJAX to forward violation
      fetch(`/discipline/violations/${violationId}/forward`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          '_token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        })
      })
      .then(response => {
        if (response.ok) {
          return response.json();
        } else {
          throw new Error('Forward failed with status: ' + response.status);
        }
      })
      .then(data => {
        if (data.success) {
          // Update the violation status to "in progress" in the table
          const rows = document.querySelectorAll('#violationsTable tbody tr');
          rows.forEach(row => {
            const idCell = row.cells[0];
            if (idCell && idCell.textContent.includes('#' + violationId)) {
              // Update status cell to "In Progress"
              const statusCell = row.cells[6];
              if (statusCell) {
                statusCell.innerHTML = `<span class="badge bg-info">In Progress</span>`;
              }

              // Add visual feedback
              row.style.backgroundColor = '#d1ecf1';
              setTimeout(() => {
                row.style.backgroundColor = '';
              }, 2000);
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
          throw new Error(data.message || 'Forward failed');
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
          <strong>Error!</strong> Failed to forward violation: ${error.message}
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


  window.scheduleCaseMeeting = function(violationId) {
    console.log('🚀 scheduleCaseMeeting called with id:', violationId);

    // Show loading state
    const modalBody = document.querySelector('#scheduleCaseMeetingModal .modal-body');
    if (modalBody) {
      modalBody.innerHTML = `
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading violation data for ID: ${violationId}...</p>
        </div>
      `;
    }

    // Show modal immediately
    try {
      const modal = new bootstrap.Modal(document.getElementById('scheduleCaseMeetingModal'));
      modal.show();
      console.log('✅ Modal shown successfully');
    } catch (modalError) {
      console.error('❌ Modal error:', modalError);
    }

    // Fetch violation data
    console.log('📡 Making API call to:', `/discipline/violations/${violationId}`);

    fetch(`/discipline/violations/${violationId}`, { credentials: 'include' })
      .then(response => {
        console.log('📡 Response received:', {
          status: response.status,
          statusText: response.statusText,
          ok: response.ok,
          url: response.url
        });

        if (response.status === 401) {
          alert('Your session has expired. Please log in again.');
          window.location.href = '/discipline/login';
          throw new Error('Authentication required');
        }

        if (!response.ok) {
          if (response.status === 404) {
            throw new Error(`Violation not found (404). Check if the ID ${violationId} exists.`);
          } else if (response.status === 500) {
            throw new Error('Server error (500). Please check the server logs.');
          } else {
            throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
          }
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          throw new Error('Server returned non-JSON response');
        }

        return response.json();
      })
      .then(data => {
        console.log('✅ Violation data received:', data);

        if (!data) {
          throw new Error('No data received from server');
        }

        // Populate modal with violation data
        document.getElementById('scheduleViolationId').value = violationId;
        document.getElementById('scheduleStudentId').value = data.student_id;
        document.getElementById('scheduleStudentName').value = `${data.student.first_name} ${data.student.last_name} (${data.student.student_id || 'No ID'})`;
        document.getElementById('scheduleViolationTitle').value = data.title;

        // Pre-populate reason field
        const reasonField = document.querySelector('#scheduleCaseMeetingForm textarea[name="reason"]');
        if (reasonField) {
          reasonField.value = `Violation: ${data.title} - ${data.description || 'No description provided'}`;
        }

        // Reset form to original state
        const form = document.getElementById('scheduleCaseMeetingForm');
        form.innerHTML = `
          <input type="hidden" id="scheduleViolationId" name="violation_id" value="${violationId}">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Student <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="scheduleStudentName" value="${data.student.first_name} ${data.student.last_name} (${data.student.student_id || 'No ID'})" readonly>
                <input type="hidden" id="scheduleStudentId" name="student_id" value="${data.student_id}">
              </div>
              <div class="col-md-6">
                <label class="form-label">Violation</label>
                <input type="text" class="form-control" id="scheduleViolationTitle" value="${data.title}" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="scheduled_date" required min="${new Date().toISOString().split('T')[0]}">
              </div>
              <div class="col-md-6">
                <label class="form-label">Time <span class="text-danger">*</span></label>
                <input type="time" class="form-control" name="scheduled_time" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" name="location" placeholder="e.g., Guidance Office, Student's Home">
              </div>

              <div class="col-12">
                <label class="form-label">Reason <span class="text-danger">*</span></label>
                <textarea class="form-control" name="reason" rows="3" required placeholder="Describe the reason for this meeting...">${`Violation: ${data.title} - ${data.description || 'No description provided'}`}</textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea class="form-control" name="notes" rows="2" placeholder="Additional notes or preparation needed..."></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="ri-calendar-event-line me-2"></i>Schedule Meeting
            </button>
          </div>
        `;

        console.log('✅ Modal populated successfully');
      })
      .catch(error => {
        console.error('❌ Fetch error:', error);

        // Show error in modal
        if (modalBody) {
          modalBody.innerHTML = `
            <div class="alert alert-danger">
              <h6>Error Loading Violation</h6>
              <p><strong>${error.message}</strong></p>
              <p>URL: <code>/discipline/violations/${violationId}</code></p>
              <div class="mt-3">
                <button class="btn btn-sm btn-outline-secondary" onclick="bootstrap.Modal.getInstance(document.getElementById('scheduleCaseMeetingModal')).hide()">
                  Close
                </button>
                <button class="btn btn-sm btn-primary" onclick="scheduleCaseMeeting(${violationId})">
                  Retry
                </button>
              </div>
            </div>
          `;
        }

        alert('Failed to load violation: ' + error.message);
      });
  };

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
        modalElement.style.zIndex = '1050';
        modalElement.classList.add('show');
        modalElement.setAttribute('aria-hidden', 'false');
        modalElement.setAttribute('aria-modal', 'true');
        modalElement.setAttribute('role', 'dialog');

        document.body.classList.add('modal-open');
        document.body.appendChild(backdrop);

        this.activeModals.add(modalId);
        this.addFallbackEventListeners(modalId);
    },
    
    hideFallback: function(modalId) {
      const modalElement = document.getElementById(modalId);
      const backdrop = document.getElementById(modalId + '-backdrop');

      // Move focus to a safe element outside the modal to avoid aria-hidden warning
      const focusTarget = document.querySelector('main') || document.body;
      if (document.activeElement && modalElement.contains(document.activeElement)) {
        focusTarget.focus();
      }

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


// Function to use custom offense
window.useCustomOffense = function() {
    const customInput = document.getElementById('customOffenseText');
    const violationTitleSelect = document.getElementById('violationTitle');

    if (customInput && customInput.value.trim()) {
        // Create a temporary option with the custom value
        violationTitleSelect.innerHTML = `<option value="${customInput.value.trim()}" selected>${customInput.value.trim()}</option>`;

        // Remove the custom input field
        customInput.closest('.input-group').remove();
    }
}

// Function to use custom offense in incident form
window.useIncidentCustomOffense = function() {
    const customInput = document.getElementById('incidentCustomOffenseText');
    const violationTitleSelect = document.getElementById('incidentViolation');

    if (customInput && customInput.value.trim()) {
        // Create a temporary option with the custom value
        violationTitleSelect.innerHTML = `<option value="${customInput.value.trim()}" selected>${customInput.value.trim()}</option>`;

        // Remove the custom input field
        customInput.closest('.input-group').remove();
    }
}

window.openViolationModal = function(student) {
    // Add student to selected
    if (!window.selectedStudents.some(s => s.id === student.id)) {
        window.selectedStudents.push({
            id: student.id,
            name: `${student.first_name} ${student.last_name} (${student.student_id || 'No ID'})`
        });
        updateSelectedStudentsDisplay();
    }

    // Reset form
    document.getElementById('violationTitle').innerHTML = '<option value="">-- Select Offense --</option>';

    const customInput = document.getElementById('customOffenseInput');
    if (customInput) customInput.remove();

    // Show modal
    const modalEl = document.getElementById('recordViolationModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

// Helper functions for incident form
function updateIncidentSelectedStudentsDisplay() {
    const selectedStudentsContainer = document.getElementById('incidentSelectedStudentsContainer');
    if (selectedStudentsContainer) {
        selectedStudentsContainer.innerHTML = window.incidentSelectedStudents.map(student => `
            <div class="badge bg-primary me-2 mb-2 d-inline-flex align-items-center">
                ${student.name}
                <button type="button" class="btn-close btn-close-white ms-2" onclick="removeIncidentSelectedStudent(${student.id})" style="font-size: 0.6em;"></button>
            </div>
        `).join('');
    }
}

window.removeIncidentSelectedStudent = function(studentId) {
    const index = window.incidentSelectedStudents.findIndex(s => s.id === studentId);
    if (index > -1) {
        window.incidentSelectedStudents.splice(index, 1);
        updateIncidentSelectedStudentsDisplay();
    }
};

function initializeIncidentStudentSearch() {
    const incidentStudentSearch = document.getElementById('incidentStudentSearch');
    const incidentStudentSuggestions = document.getElementById('incidentStudentSuggestions');

    let incidentSearchTimeout;
    let incidentCurrentFocus = -1;

    function incidentSearchStudents(query) {
        if (query.length < 2) {
            incidentStudentSuggestions.style.display = 'none';
            return;
        }

        fetch(`/discipline/students/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(students => {
                incidentDisplaySuggestions(students);
            })
            .catch(error => {
                console.error('Error searching students:', error);
                incidentStudentSuggestions.style.display = 'none';
            });
    }

    function incidentDisplaySuggestions(students) {
        if (students.length === 0) {
            incidentStudentSuggestions.style.display = 'none';
            return;
        }

        const suggestionsHtml = students.map(student => `
            <div class="suggestion-item" data-student-id="${student.id}" data-student-name="${student.first_name} ${student.last_name} (${student.student_id || 'No ID'})">
                <div class="suggestion-name">${student.first_name} ${student.last_name}</div>
                <div class="suggestion-details">ID: ${student.student_id || 'No ID'} | Grade: ${student.grade_level || 'N/A'} | Section: ${student.section || 'N/A'}</div>
            </div>
        `).join('');

        incidentStudentSuggestions.innerHTML = suggestionsHtml;
        incidentStudentSuggestions.style.display = 'block';
        incidentCurrentFocus = -1;
    }

    function incidentSelectStudent(studentId, studentName) {
        if (!window.incidentSelectedStudents.some(s => s.id === studentId)) {
            window.incidentSelectedStudents.push({ id: studentId, name: studentName });
            updateIncidentSelectedStudentsDisplay();
        }
        incidentStudentSearch.value = '';
        incidentStudentSuggestions.style.display = 'none';
        incidentCurrentFocus = -1;
    }

    function incidentUpdateFocus(items) {
        items.forEach(item => item.classList.remove('active'));
        if (items[incidentCurrentFocus]) {
            items[incidentCurrentFocus].classList.add('active');
            items[incidentCurrentFocus].scrollIntoView({ block: 'nearest' });
        }
    }

    const incidentDebouncedSearch = debounce(incidentSearchStudents, 300);

    if (incidentStudentSearch) {
        incidentStudentSearch.addEventListener('input', function(e) {
            const query = e.target.value.trim();
            incidentDebouncedSearch(query);
        });

        incidentStudentSearch.addEventListener('keydown', function(e) {
      const items = incidentStudentSuggestions.querySelectorAll('.suggestion-item');

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        incidentCurrentFocus = incidentCurrentFocus < items.length - 1 ? incidentCurrentFocus + 1 : 0;
        incidentUpdateFocus(items);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        incidentCurrentFocus = incidentCurrentFocus > 0 ? incidentCurrentFocus - 1 : items.length - 1;
        incidentUpdateFocus(items);
      } else if (e.key === 'Enter') {
        e.preventDefault();
        if (incidentCurrentFocus >= 0 && items[incidentCurrentFocus]) {
          const item = items[incidentCurrentFocus];
          const studentId = item.getAttribute('data-student-id');
          const studentName = item.getAttribute('data-student-name');
          incidentSelectStudent(studentId, studentName);
        }
      } else if (e.key === 'Escape') {
        incidentStudentSuggestions.style.display = 'none';
        incidentCurrentFocus = -1;
      }
    });

        document.addEventListener('click', function(e) {
            if (!incidentStudentSearch.contains(e.target) && !incidentStudentSuggestions.contains(e.target)) {
                incidentStudentSuggestions.style.display = 'none';
                incidentCurrentFocus = -1;
            }
        });
    }

    if (incidentStudentSuggestions) {
        incidentStudentSuggestions.addEventListener('click', function(e) {
            const item = e.target.closest('.suggestion-item');
            if (item) {
                const studentId = item.getAttribute('data-student-id');
                const studentName = item.getAttribute('data-student-name');
                incidentSelectStudent(studentId, studentName);
            }
        });
    }
}

function showIncidentForm() {
    // Get violation data
    const violationTitle = getViolationTitle();
    const violationDescription = ''; // No description field in the current form

    // Create modal
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'incidentFormModal';
  modal.innerHTML = `
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Incident Form</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="incidentForm">
            <div class="mb-3">
              <label class="form-label fw-bold">Offense/Title</label>
              <select class="form-select" id="incidentViolation" required>
                <!-- Options will be populated by JavaScript -->
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Reported Students</label>
              <div class="position-relative">
                <input type="text" class="form-control" id="incidentStudentSearch" placeholder="Type student name or ID..." autocomplete="off">
                <div id="incidentStudentSuggestions" class="suggestions-list" style="display: none;">
                <!-- Suggestions will be populated here -->
                </div>
              </div>
              <div id="incidentSelectedStudentsContainer" class="mt-2">
                <!-- Selected students will be added here -->
              </div>
              <small class="text-muted">Add multiple students involved in the incident</small>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Reporter</label>
              <input type="text" class="form-control" id="incidentReporter" required>
            </div>
            <div class="row">
              <div class="col-md-6">
                <label class="form-label fw-bold">Date</label>
                <input type="date" class="form-control" id="incidentDate" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Time</label>
                <input type="time" class="form-control" id="incidentTime" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Details</label>
              <textarea class="form-control" id="incidentDetails" rows="4" required></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" form="incidentForm" class="btn btn-success">Submit Incident</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  `;
    document.body.appendChild(modal);

    // Initialize incident selected students from violation form
    window.incidentSelectedStudents = [...window.selectedStudents];

    // Update display
    updateIncidentSelectedStudentsDisplay();

    // Initialize student search functionality for incident form
    initializeIncidentStudentSearch();

    // Populate offense dropdown
    const incidentViolationSelect = document.getElementById('incidentViolation');
    if (incidentViolationSelect) {
        // Clear current options
        incidentViolationSelect.innerHTML = '<option value="">-- Select Offense --</option>';

        // Check if violation options are loaded
        if (!window.offenseOptions) {
            console.warn('Violation options not loaded yet for incident form');
            return;
        }

        // Add minor offenses
        window.offenseOptions.minor.forEach(offense => {
            const option = document.createElement('option');
            option.value = offense;
            option.textContent = offense;
            if (offense === violationTitle) option.selected = true;
            incidentViolationSelect.appendChild(option);
        });

        // Add major offenses from all categories
        Object.keys(window.offenseOptions.major).forEach(category => {
            window.offenseOptions.major[category].forEach(offense => {
                const option = document.createElement('option');
                option.value = offense;
                option.textContent = offense;
                if (offense === violationTitle) option.selected = true;
                incidentViolationSelect.appendChild(option);
            });
        });

        // Add custom option
        const customOption = document.createElement('option');
        customOption.value = 'custom';
        customOption.textContent = '-- Custom Offense --';
        incidentViolationSelect.appendChild(customOption);

        // Add event listener for custom offense and form switching
        incidentViolationSelect.addEventListener('change', function() {
            const selectedTitle = this.value;

            // Handle custom offense input
            const existingCustomInput = document.querySelector('#incidentCustomOffenseInput');
            if (selectedTitle === 'custom') {
                if (!existingCustomInput) {
                    // Create custom input field
                    const inputGroup = document.createElement('div');
                    inputGroup.className = 'input-group mt-2';
                    inputGroup.id = 'incidentCustomOffenseInput';
                    inputGroup.innerHTML = `
                        <input type="text" class="form-control" id="incidentCustomOffenseText" placeholder="Enter custom offense...">
                        <button type="button" class="btn btn-outline-secondary" onclick="useIncidentCustomOffense()">Use</button>
                    `;
                    incidentViolationSelect.parentNode.appendChild(inputGroup);
                }
            } else if (existingCustomInput) {
                existingCustomInput.remove();
            }

            // Handle form switching based on severity
            const severity = window.titleToSeverityMap[selectedTitle]?.severity;
            if (severity === 'minor') {
                // Switch to violation form for minor offenses
                window.ModalManager.hide('incidentFormModal');
                // Re-enable and show the violation form
                const violationModal = document.getElementById('recordViolationModal');
                if (violationModal) {
                    // Update the violation title select to match
                    const violationTitleSelect = document.getElementById('violationTitle');
                    if (violationTitleSelect) {
                        violationTitleSelect.value = selectedTitle;
                        // Trigger change event to update UI
                        violationTitleSelect.dispatchEvent(new Event('change'));
                    }
                    window.ModalManager.show('recordViolationModal');
                }
            }
            // If major, stay in incident form (no action needed)
        });
    }

    // Add submit handler
    const incidentForm = document.getElementById('incidentForm');
    incidentForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (window.incidentSelectedStudents.length === 0) {
            alert('Please select at least one student for the incident.');
            return;
        }

        // Collect incident data
        const reporter = document.getElementById('incidentReporter').value;
        const date = document.getElementById('incidentDate').value;
        const time = document.getElementById('incidentTime').value;
        const details = document.getElementById('incidentDetails').value;

        const submitBtn = document.querySelector('#incidentFormModal button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Submitting...';
        submitBtn.disabled = true;

        try {
            // Submit violation for each selected student
            const results = [];
            for (const student of window.incidentSelectedStudents) {
                const violationForm = document.getElementById('recordViolationForm');
                const formData = new FormData();

                // Get violation title from incident form
                const violationTitle = document.getElementById('incidentViolation').value;
                const severity = window.titleToSeverityMap[violationTitle]?.severity || 'major';
                const category = window.titleToSeverityMap[violationTitle]?.category || null;

                // Manually append all required fields from the form
                formData.append('student_id', student.id);
                formData.append('title', violationTitle);
                formData.append('description', details.trim());
                formData.append('severity', severity);
                formData.append('major_category', category);
                formData.append('violation_date', date);
                formData.append('violation_time', time);
                formData.append('status', 'pending');
                formData.append('incident_reporter', reporter);
                formData.append('incident_date', date);
                formData.append('incident_time', time);





                // Add CSRF token to FormData
                const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
                formData.append('_token', csrfTokenEl.getAttribute('content'));

                // Ensure title is set
                getViolationTitle();

                const response = await fetch('/discipline/violations', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                });

        if (response.status === 409) {
          showDuplicateViolationModal('A violation with the same title already exists for this student on this date.');
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
          return;
        }

        if (!response.ok) {
          const responseText = await response.text();
          if (responseText.startsWith('<')) {
            throw new Error('Authentication required. Please log in again.');
          } else {
            throw new Error(`Server error: ${response.status}. ${responseText.substring(0, 200)}`);
          }
        }

        const responseText = await response.text();
        let data;
        try {
          data = JSON.parse(responseText);
        } catch (parseError) {
          throw new Error(`Server returned invalid JSON. Status: ${response.status}. Response: ${responseText.substring(0, 200)}`);
        }

        if (!data.success) {
          throw new Error(data.message || `Server error: ${response.status}`);
        }

        results.push(data);
            }

            alert(`Incident recorded successfully for ${window.incidentSelectedStudents.length} student(s)!`);
            // Close modals
            window.ModalManager.hide('incidentFormModal');
            const modal = bootstrap.Modal.getInstance(document.getElementById('recordViolationModal'));
            if (modal) modal.hide();
            // Refresh
            window.location.reload();

        } catch (err) {
      if (err && err.message && err.message.includes('A violation with the same title already exists for this student on this date.')) {
        showDuplicateViolationModal('A violation with the same title already exists for this student on this date.');
      } else {
        console.error('Incident submission error:', err);
        alert('Error submitting incident: ' + err.message);
      }
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });



    // Show modal
    window.ModalManager.show('incidentFormModal');
}

// Convenient wrapper functions
window.showModal = function(modalId) {
    return window.ModalManager.show(modalId);
}

window.hideModal = function(modalId) {
    return window.ModalManager.hide(modalId);
}

// Function to generate printable incident form
window.generateIncidentForm = function() {
    // Get form data
    const reporter = document.getElementById('incidentReporter').value;
    const date = document.getElementById('incidentDate').value;
    const time = document.getElementById('incidentTime').value;
    const details = document.getElementById('incidentDetails').value;
    const violation = document.getElementById('incidentViolation').value;
    // Get selected students
    const selectedStudentsText = Array.from(document.querySelectorAll('#incidentSelectedStudentsContainer .badge'))
        .map(badge => badge.textContent.trim())
        .join(', ');
    // Validate required fields
    if (!reporter || !date || !time || !details || window.incidentSelectedStudents.length === 0) {
        alert('Please fill in all required fields before generating the incident form.');
        return;
    }
    // Only the inner content for modal preview
    const innerContent = `
      <div class="header">
        <div class="school-name">Nicolites Montessori School</div>
        <div class="form-title">INCIDENT REPORT FORM</div>
      </div>
      <div class="section">
        <div class="section-title">INCIDENT INFORMATION</div>
        <div class="field"><span class="field-label">Date of Incident:</span><span class="field-value">${new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span></div>
        <div class="field"><span class="field-label">Time of Incident:</span><span class="field-value">${time}</span></div>
        <div class="field"><span class="field-label">Reported By:</span><span class="field-value">${reporter}</span></div>
        <div class="field"><span class="field-label">Students Involved:</span><span class="field-value">${selectedStudentsText}</span></div>
      </div>
      <div class="section">
        <div class="section-title">INCIDENT DETAILS</div>
        <div style="margin-top: 10px; padding: 10px; border: 1px solid #ccc; min-height: 100px;">${details.replace(/\n/g, '<br>')}</div>
      </div>
      <div class="section">
        <div class="section-title">VIOLATION INFORMATION</div>
        <div style="margin-top: 10px; padding: 10px; border: 1px solid #ccc; background-color: #f9f9f9;">${violation.replace(/\n/g, '<br>')}</div>
      </div>
      <div class="signature-section">
        <div style="margin-bottom: 20px;"><strong>Prepared by:</strong></div>
        <div class="signature-line"></div>
        <div style="display: inline-block; font-size: 12px; color: #666;">Signature over Printed Name</div>
        <div style="margin-top: 20px; margin-bottom: 20px;"><strong>Reviewed by:</strong></div>
        <div class="signature-line"></div>
        <div style="display: inline-block; font-size: 12px; color: #666;">Guidance Counselor/Discipline Officer</div>
        <div style="margin-top: 20px; margin-bottom: 20px;"><strong>Approved by:</strong></div>
        <div class="signature-line"></div>
        <div style="display: inline-block; font-size: 12px; color: #666;">Principal/Discipline Head</div>
      </div>
      <div style="margin-top: 40px; font-size: 12px; color: #666; text-align: center;">Generated on ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</div>
      <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .school-name { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .form-title { font-size: 18px; font-weight: bold; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; font-size: 14px; margin-bottom: 8px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
        .field { margin-bottom: 10px; }
        .field-label { font-weight: bold; display: inline-block; min-width: 120px; }
        .field-value { display: inline-block; }
        .signature-section { margin-top: 40px; border-top: 1px solid #000; padding-top: 20px; }
        .signature-line { display: inline-block; width: 200px; border-bottom: 1px solid #000; margin-right: 20px; margin-bottom: 20px; }
        @media print { body { margin: 0; } }
      </style>
    `;
    // Full HTML for print/PDF
    const printContent = `<!DOCTYPE html><html><head><title>Incident Report Form</title></head><body>${innerContent}</body></html>`;
    // Show in modal (only inner content)
    document.getElementById('incidentFormPreviewBody').innerHTML = innerContent;
    // Hide the incident form modal if open
    const incidentModalEl = document.getElementById('incidentFormModal');
    if (incidentModalEl && incidentModalEl.classList.contains('show')) {
        const incidentModal = bootstrap.Modal.getInstance(incidentModalEl) || new bootstrap.Modal(incidentModalEl);
        incidentModal.hide();
    }
    // Show the preview modal
    const previewModal = new bootstrap.Modal(document.getElementById('incidentFormPreviewModal'));
    previewModal.show();
    // Print button
    document.getElementById('printIncidentFormBtn').onclick = function() {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.onload = function() {
            printWindow.print();
        };
    };
    // Save as PDF button (requires html2pdf.js)
    document.getElementById('downloadIncidentPDFBtn').onclick = function() {
        if (window.html2pdf) {
            window.html2pdf().from(document.getElementById('incidentFormPreviewBody')).set({
                margin: 0.5,
                filename: 'incident-form.pdf',
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            }).save();
        } else {
            alert('PDF export requires html2pdf.js. Please include it in your project.');
        }
    };
};