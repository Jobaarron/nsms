// Student Grades JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize grade loading functionality
    initializeGradeLoading();
});

function initializeGradeLoading() {
    // Add click handlers for quarter cards
    const quarterCards = document.querySelectorAll('[href*="grades/"]');
    quarterCards.forEach(card => {
        card.addEventListener('click', function(e) {
            const quarter = this.href.split('/').pop();
            if (quarter && ['1st', '2nd', '3rd', '4th'].includes(quarter)) {
                loadQuarterGrades(quarter);
            }
        });
    });
}

function loadQuarterGrades(quarter) {
    // Show loading state
    showLoading();
    
    fetch(`/student/grades/data/ajax?quarter=${quarter}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                showError(data.error);
            } else {
                displayGradeData(data, quarter);
            }
        })
        .catch(error => {
            console.error('Error loading grades:', error);
            showError('Failed to load grade data. Please try again.');
        })
        .finally(() => {
            hideLoading();
        });
}

function displayGradeData(data, quarter) {
    // Create modal or update existing content to show grades
    const modal = createGradeModal(data, quarter);
    document.body.appendChild(modal);
    
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
    
    // Clean up modal when hidden
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}

function createGradeModal(data, quarter) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    let gradesHtml = '';
    if (data.grades && data.grades.length > 0) {
        gradesHtml = data.grades.map(grade => `
            <tr>
                <td>${grade.subject}</td>
                <td>${grade.teacher}</td>
                <td class="text-center">
                    <span class="badge ${grade.is_passing ? 'bg-success' : 'bg-danger'}">
                        ${grade.grade}
                    </span>
                </td>
                <td>${grade.remarks || '-'}</td>
                <td>
                    <span class="badge ${grade.is_passing ? 'bg-success' : 'bg-danger'}">
                        ${grade.status}
                    </span>
                </td>
            </tr>
        `).join('');
    } else {
        gradesHtml = '<tr><td colspan="5" class="text-center text-muted">No grades available</td></tr>';
    }
    
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${quarter} Quarter Grades</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ${data.stats ? `
                    <div class="row g-3 mb-4">
                        <div class="col-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5>${data.stats.total_subjects}</h5>
                                    <small>Total Subjects</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5>${data.stats.average_grade}</h5>
                                    <small>Average Grade</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5>${data.stats.passing_count}</h5>
                                    <small>Passing</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5>${data.stats.failing_count}</h5>
                                    <small>Failing</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th class="text-center">Grade</th>
                                    <th>Remarks</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${gradesHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="/student/grades/report/${quarter}" class="btn btn-primary" target="_blank">
                        <i class="ri-printer-line me-2"></i>Print Report
                    </a>
                </div>
            </div>
        </div>
    `;
    
    return modal;
}

function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'loading-overlay';
    loading.className = 'loading-overlay';
    loading.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('loading-overlay');
    if (loading) {
        document.body.removeChild(loading);
    }
}

function showError(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('main');
    if (container) {
        container.insertBefore(alert, container.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 5000);
    }
}
