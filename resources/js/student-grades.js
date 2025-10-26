// Student Grades JavaScript - No jQuery/Bootstrap dependencies

// Make functions globally available immediately
window.viewQuarterGrades = viewQuarterGrades;
window.closeModal = closeModal;

document.addEventListener('DOMContentLoaded', function() {
    console.log('Student grades JS loaded');
});

function viewQuarterGrades(quarter) {
    // Set modal title
    const modalQuarter = document.getElementById('modalQuarter');
    if (modalQuarter) {
        modalQuarter.textContent = quarter;
    }
    
    // Show modal using pure JavaScript
    showModal();
    
    // Reset content to loading state
    const gradesContent = document.getElementById('gradesContent');
    if (gradesContent) {
        gradesContent.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Loading grades...</div>
            </div>
        `;
    }
    
    // Clear summary
    const gradesSummary = document.getElementById('gradesSummary');
    if (gradesSummary) {
        gradesSummary.innerHTML = '';
    }
    
    // Fetch grades via AJAX
    fetch(`/student/grades/${quarter}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayGrades(data.grades, data.stats, quarter);
        } else {
            displayError(data.message || 'Failed to load grades');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        displayError('An error occurred while loading grades');
    });
}

function showModal() {
    const modalElement = document.getElementById('gradesModal');
    if (modalElement) {
        modalElement.classList.add('show');
        modalElement.style.display = 'block';
        modalElement.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        
        // Add backdrop
        let backdrop = document.getElementById('modalBackdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'modalBackdrop';
            document.body.appendChild(backdrop);
        }
        
        // Setup close functionality
        setupModalClose();
    }
}

function setupModalClose() {
    const modalElement = document.getElementById('gradesModal');
    if (!modalElement) return;
    
    // Close buttons
    const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"]');
    closeButtons.forEach(button => {
        button.onclick = closeModal;
    });
    
    // Backdrop click
    const backdrop = document.getElementById('modalBackdrop');
    if (backdrop) {
        backdrop.onclick = closeModal;
    }
    
    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
}

function closeModal() {
    const modalElement = document.getElementById('gradesModal');
    const backdrop = document.getElementById('modalBackdrop');
    
    if (modalElement) {
        modalElement.classList.remove('show');
        modalElement.style.display = 'none';
        modalElement.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    }
    
    if (backdrop) {
        backdrop.remove();
    }
}

function displayGrades(grades, stats, quarter) {
    let html = '';
    
    if (grades && grades.length > 0) {
        // Statistics summary
        if (stats) {
            html += `
                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card bg-light h-100">
                            <div class="card-body text-center p-3">
                                <i class="ri-book-line fs-1 text-primary mb-2 d-block"></i>
                                <div class="small text-muted">Total Subjects</div>
                                <h5 class="mb-0">${stats.total_subjects}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-light h-100">
                            <div class="card-body text-center p-3">
                                <i class="ri-trophy-line fs-1 text-warning mb-2 d-block"></i>
                                <div class="small text-muted">Average</div>
                                <h5 class="mb-0 ${(stats.average_grade || 0) >= 75 ? 'text-success' : 'text-danger'}">${(stats.average_grade || 0).toFixed(1)}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-light h-100">
                            <div class="card-body text-center p-3">
                                <i class="ri-check-double-line fs-1 text-success mb-2 d-block"></i>
                                <div class="small text-muted">Passed</div>
                                <h5 class="mb-0 text-success">${stats.passing_count}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-light h-100">
                            <div class="card-body text-center p-3">
                                <i class="ri-close-circle-line fs-1 text-danger mb-2 d-block"></i>
                                <div class="small text-muted">Failed</div>
                                <h5 class="mb-0 text-danger">${stats.failing_count}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Grades table
        html += `
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">Subject</th>
                            <th>Teacher</th>
                            <th class="text-center">Grade</th>
                            <th>Remarks</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        grades.forEach(grade => {
            const isPassing = parseFloat(grade.grade) >= 75;
            html += `
                <tr>
                    <td class="ps-3">
                        <div class="fw-semibold">${grade.subject ? grade.subject.subject_name : 'Unknown Subject'}</div>
                        ${grade.subject && grade.subject.subject_code ? `<small class="text-muted">${grade.subject.subject_code}</small>` : ''}
                    </td>
                    <td>
                        <div class="fw-medium">${grade.teacher && grade.teacher.user ? grade.teacher.user.name : 'Unknown Teacher'}</div>
                    </td>
                    <td class="text-center">
                        <span class="fw-bold fs-4 ${isPassing ? 'text-success' : 'text-danger'}">
                            ${parseFloat(grade.grade).toFixed(0)}
                        </span>
                    </td>
                    <td>
                        <span class="text-muted">${grade.remarks || '-'}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge ${isPassing ? 'bg-success' : 'bg-danger'} px-3 py-2">
                            ${isPassing ? 'Passed' : 'Failed'}
                        </span>
                    </td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        // Update summary in footer
        if (stats) {
            const gradesSummary = document.getElementById('gradesSummary');
            if (gradesSummary) {
                gradesSummary.innerHTML = `
                    <small class="text-muted">
                        General Average: <strong class="${(stats.average_grade || 0) >= 75 ? 'text-success' : 'text-danger'}">${(stats.average_grade || 0).toFixed(2)}</strong> | 
                        ${stats.passing_count}/${stats.total_subjects} Subjects Passed
                    </small>
                `;
            }
        }
    } else {
        html = `
            <div class="text-center py-5">
                <i class="ri-file-list-line display-4 text-muted mb-3"></i>
                <h5>No Grades Available</h5>
                <p class="text-muted">No grades found for ${quarter} quarter.</p>
            </div>
        `;
    }
    
    const gradesContent = document.getElementById('gradesContent');
    if (gradesContent) {
        gradesContent.innerHTML = html;
    }
}

function displayError(message) {
    const gradesContent = document.getElementById('gradesContent');
    if (gradesContent) {
        gradesContent.innerHTML = `
            <div class="text-center py-5">
                <i class="ri-error-warning-line display-4 text-danger mb-3"></i>
                <h5>Error Loading Grades</h5>
                <p class="text-muted">${message}</p>
            </div>
        `;
    }
}
