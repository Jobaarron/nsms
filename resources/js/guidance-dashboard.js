// Guidance Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Guidance Dashboard loaded');
    // Load initial data
    loadCaseStatusPieChart();
    loadClosedCasesBarChart();
    loadCounselingSessionsBarChart();
    loadDisciplineVsTotalHistogram();
    loadWeeklyViolationListTable();
});

// Refresh dashboard (now only refreshes charts)
function refreshDashboard() {
    console.log('Refreshing dashboard...');
    loadCaseStatusPieChart();
    loadClosedCasesBarChart();
    loadDisciplineVsTotalHistogram();
    showAlert('Dashboard refreshed', 'success');
}

// Quick action functions
function scheduleNewCaseMeeting() {
    window.location.href = '/guidance/case-meetings';
}

function scheduleNewCounseling() {
    window.location.href = '/guidance/counseling-sessions';
}

function scheduleHouseVisit() {
    window.location.href = '/guidance/case-meetings?type=house_visit';
}

function createCaseSummary() {
    window.location.href = '/guidance/case-meetings?action=summary';
}

// Open quick action modal
function openQuickActionModal() {
    const modal = new bootstrap.Modal(document.getElementById('quickActionModal'));
    modal.show();
}

// Close modal helper
function closeModal(modalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
    if (modal) {
        modal.hide();
    }
}

// Show alert helper
function showAlert(message, type = 'info') {
    // Create alert element
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="ri-information-line me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Find or create alert container
    let container = document.getElementById('alert-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'alert-container';
        container.className = 'position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    // Add alert
    container.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        const alerts = container.querySelectorAll('.alert');
        if (alerts.length > 0) {
            alerts[0].remove();
        }
    }, 5000);
}

// Pie Chart for Case Statuses
function renderCaseStatusPieChart(onGoing, scheduled, preCompleted) {
    const ctx = document.getElementById('caseStatusPieChart');
    if (!ctx || typeof Chart === 'undefined') return;
    new Chart(ctx.getContext('2d'), {
        type: 'pie',
        data: {
            labels: ['On Going Cases', 'Scheduled Meeting', 'Pre-Completed'],
            datasets: [{
                data: [onGoing, scheduled, preCompleted],
                backgroundColor: [
                    '#81c784', // green 1
                    '#4caf50', // green 2
                    '#2e7d32'  // green 3
                ],
                borderColor: [
                    '#81c784',
                    '#4caf50',
                    '#2e7d32'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            return label + ': ' + value;
                        }
                    }
                }
            }
        }
    });
}

// Pie Chart for Case Statuses (Dynamic)
function loadCaseStatusPieChart() {
    fetch('/guidance/case-status-stats', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderCaseStatusPieChart(
                data.on_going_cases || 0,
                data.scheduled_meeting || 0,
                data.pre_completed || 0
            );
        }
    })
    .catch(error => {
        console.error('Error loading case status stats:', error);
    });
}

// Bar Chart for Closed Cases Per Month (Dynamic)
function loadClosedCasesBarChart() {
    fetch('/guidance/closed-cases-stats', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderClosedCasesBarChart(data.labels, data.data);
        }
    })
    .catch(error => {
        console.error('Error loading closed cases stats:', error);
    });
}

function renderClosedCasesBarChart(labels, data) {
    const ctx = document.getElementById('closedCasesBarChart');
    if (!ctx || typeof Chart === 'undefined') return;
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Closed Cases',
                data: data,
                backgroundColor: '#4caf50',
                borderColor: '#2e7d32',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Closed: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

// Bar Chart for Counseling Sessions Per Month (Dynamic)
function loadCounselingSessionsBarChart() {
    fetch('/guidance/counseling-sessions-stats', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then((data) => {
        if (data.success) {
            renderCounselingSessionsBarChart(data.labels, data.data);
        }
    })
    .catch(error => {
        console.error('Error loading counseling sessions stats:', error);
    });
}

function renderCounselingSessionsBarChart(labels, data) {
    const ctx = document.getElementById('counselingSessionsBarChart');
    if (!ctx || typeof Chart === 'undefined') return;
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Counseling Sessions',
                data: data,
                backgroundColor: '#81c784',
                borderColor: '#388e3c',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Sessions: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

// Histogram for Annual Students with Disciplinary Record vs Total Students
function loadDisciplineVsTotalHistogram() {
    fetch('/guidance/discipline-vs-total-stats', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderDisciplineVsTotalHistogram(data.labels, data.data);
        }
    })
    .catch(error => {
        console.error('Error loading discipline vs total stats:', error);
    });
}

function renderDisciplineVsTotalHistogram(labels, data) {
    const ctx = document.getElementById('disciplineVsTotalHistogram');
    if (!ctx || typeof Chart === 'undefined') return;

    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'With Disciplinary Record',
                    data: data.with_discipline,
                    backgroundColor: '#1b5e20',  // Dark forest green
                    borderColor: '#2e7d32',      // Medium green border
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false
                },
                {
                    label: 'Total Students',
                    data: data.total_students,
                    backgroundColor: '#66bb6a',  // Fresh leafy green
                    borderColor: '#81c784',      // Light green border
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

// Load and render the Weekly Violation List table
function loadWeeklyViolationListTable() {
    fetch('/guidance/weekly-violations', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('weekly-violation-list-table');
        if (!container) return;
        if (data.success && data.violations.length > 0) {
            let html = '<div class="table-responsive"><table class="table table-bordered table-striped table-sm mb-0">';
            html += '<thead><tr><th>#</th><th>Student Name</th><th>Violation</th><th>Date</th></tr></thead><tbody>';
            data.violations.forEach((v, i) => {
                html += `<tr>` +
                    `<td>${i + 1}</td>` +
                    `<td>${v.student_name || 'Unknown Student'}</td>` +
                    `<td>${v.violation_type || 'Violation'}</td>` +
                    `<td>${v.violation_date}</td>` +
                `</tr>`;
            });
            html += '</tbody></table></div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-muted">No violations in the last 7 days.</div>';
        }
    })
    .catch(() => {
        const container = document.getElementById('weekly-violation-list-table');
        if (container) {
            container.innerHTML = '<div class="text-danger">Failed to load weekly violations.</div>';
        }
    });
}

// Expose functions to global scope for onclick handlers
window.refreshDashboard = refreshDashboard;
window.openQuickActionModal = openQuickActionModal;
window.scheduleNewCaseMeeting = scheduleNewCaseMeeting;
window.scheduleNewCounseling = scheduleNewCounseling;
window.scheduleHouseVisit = scheduleHouseVisit;
window.createCaseSummary = createCaseSummary;
window.closeModal = closeModal;
window.renderCaseStatusPieChart = renderCaseStatusPieChart;
window.renderClosedCasesBarChart = renderClosedCasesBarChart;
window.renderCounselingSessionsBarChart = renderCounselingSessionsBarChart;
window.renderDisciplineVsTotalHistogram = renderDisciplineVsTotalHistogram;
