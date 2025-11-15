// Enhanced Guidance Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Enhanced Guidance Dashboard loaded');
    
    // Initialize filter select boxes with default values
    initializeFilterDefaults();
    
    // Load initial data
    loadAllDashboardData();
    
    // Setup real-time clock
    updateClock();
    setInterval(updateClock, 60000); // Update every minute
});

// Initialize default filter values
function initializeFilterDefaults() {
    const defaultValues = {
        'activitiesDateRange': 'week',
        'activitiesType': 'all',
        'tasksDateRange': 'week', 
        'tasksPriority': 'all',
        'topCasesDateRange': 'month',
        'topCasesLimit': '5',
        'violationTrendsPeriod': '12months',
        'violationTrendsType': 'line',
        'violationSeverity': 'all',
        'violationGroupBy': 'month',
        'counselingPeriod': 'month',
        'counselingCounselor': 'all',
        'caseStatusPeriod': 'month',
        'closedCasesPeriod': '6months',
        'closedCasesView': 'monthly',
        'counselingSessionsPeriod': '6months',
        'counselingSessionsStatus': 'all',
        'disciplineStatsPeriod': '5years',
        'disciplineStatsView': 'comparison'
    };
    
    // Set default values for all filter selects
    Object.entries(defaultValues).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.value = value;
            
            // Add change event listener to trigger filter automatically
            element.addEventListener('change', function() {
                triggerRelatedFilter(id);
            });
        }
    });
}

// Trigger the appropriate filter function based on element ID
function triggerRelatedFilter(elementId) {
    const filterMap = {
        'activitiesDateRange': applyActivitiesFilter,
        'activitiesType': applyActivitiesFilter,
        'tasksDateRange': applyTasksFilter,
        'tasksPriority': applyTasksFilter,
        'topCasesDateRange': applyTopCasesFilter,
        'topCasesLimit': applyTopCasesFilter,
        'violationTrendsPeriod': applyViolationTrendsFilter,
        'violationTrendsType': applyViolationTrendsFilter,
        'violationSeverity': applyViolationTrendsFilter,
        'violationGroupBy': applyViolationTrendsFilter,
        'counselingPeriod': applyCounselingEffectivenessFilter,
        'counselingCounselor': applyCounselingEffectivenessFilter,
        'caseStatusPeriod': applyCaseStatusFilter,
        'closedCasesPeriod': applyClosedCasesFilter,
        'closedCasesView': applyClosedCasesFilter,
        'counselingSessionsPeriod': applyCounselingSessionsFilter,
        'counselingSessionsStatus': applyCounselingSessionsFilter,
        'disciplineStatsPeriod': applyDisciplineStatsFilter,
        'disciplineStatsView': applyDisciplineStatsFilter
    };
    
    const filterFunction = filterMap[elementId];
    if (typeof filterFunction === 'function') {
        filterFunction();
    }
}

// Load all dashboard data with current filter states
function loadAllDashboardData() {
    // Apply individual chart filters (with fallback for missing filters)
    if (typeof applyCaseStatusFilter === 'function') {
        applyCaseStatusFilter();
    } else {
        loadCaseStatusPieChart();
    }
    
    if (typeof applyClosedCasesFilter === 'function') {
        applyClosedCasesFilter();
    } else {
        loadClosedCasesBarChart();
    }
    
    if (typeof applyCounselingSessionsFilter === 'function') {
        applyCounselingSessionsFilter();
    } else {
        loadCounselingSessionsBarChart();
    }
    
    if (typeof applyDisciplineStatsFilter === 'function') {
        applyDisciplineStatsFilter();
    } else {
        loadDisciplineVsTotalHistogram();
    }
    
    if (typeof applyTopCasesFilter === 'function') {
        applyTopCasesFilter();
    } else {
        loadTopCasesTable();
    }
    
    // Activities and tasks
    if (typeof applyActivitiesFilter === 'function') {
        applyActivitiesFilter();
    } else {
        loadRecentActivities();
    }
    
    if (typeof applyTasksFilter === 'function') {
        applyTasksFilter();
    } else {
        loadUpcomingTasks();
    }
    
    // Advanced analytics
    if (typeof applyViolationTrendsFilter === 'function') {
        applyViolationTrendsFilter();
    } else {
        loadViolationTrends();
    }
    
    if (typeof applyCounselingEffectivenessFilter === 'function') {
        applyCounselingEffectivenessFilter();
    } else {
        loadCounselingEffectiveness();
    }
}


// Real-time clock update
function updateClock() {
    const now = new Date();
    const timeElement = document.querySelector('.ri-time-line').nextSibling;
    if (timeElement) {
        timeElement.textContent = now.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });
    }
}

// Show loading states for better UX
function showLoadingStates() {
    const loadingElements = [
        'recent-activities',
        'upcoming-tasks',
        'top-cases-table'
    ];
    
    loadingElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = `
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2 small text-muted">Refreshing...</div>
                </div>
            `;
        }
    });
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

// Enhanced alert system with better UX
function showAlert(message, type = 'info', duration = 5000) {
    const icons = {
        'success': 'ri-check-circle-line',
        'danger': 'ri-error-warning-line', 
        'warning': 'ri-alert-line',
        'info': 'ri-information-line'
    };
    
    const icon = icons[type] || icons['info'];
    
    // Create enhanced alert element
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show shadow-sm border-0" role="alert" style="border-left: 4px solid var(--bs-${type}) !important;">
            <div class="d-flex align-items-center">
                <i class="${icon} me-2 fs-5"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    `;
    
    // Find or create alert container with enhanced styling
    let container = document.getElementById('alert-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'alert-container';
        container.className = 'position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        container.style.maxWidth = '400px';
        document.body.appendChild(container);
    }
    
    // Add alert with animation
    container.insertAdjacentHTML('beforeend', alertHtml);
    
    // Get the newest alert and add entrance animation
    const newAlert = container.lastElementChild;
    newAlert.style.transform = 'translateX(100%)';
    newAlert.style.opacity = '0';
    
    setTimeout(() => {
        newAlert.style.transform = 'translateX(0)';
        newAlert.style.opacity = '1';
    }, 10);
    
    // Auto-remove with exit animation
    setTimeout(() => {
        if (newAlert && newAlert.parentNode) {
            newAlert.style.transform = 'translateX(100%)';
            newAlert.style.opacity = '0';
            setTimeout(() => {
                if (newAlert.parentNode) {
                    newAlert.remove();
                }
            }, 300);
        }
    }, duration);
    
    // Add sound notification for important alerts
    if (type === 'danger' || type === 'warning') {
        playNotificationSound();
    }
}

// Play subtle notification sound (optional)
function playNotificationSound() {
    // Create a subtle beep using Web Audio API
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = type === 'danger' ? 800 : 600;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0, audioContext.currentTime);
        gainNode.gain.linearRampToValueAtTime(0.1, audioContext.currentTime + 0.01);
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.2);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.2);
    } catch (e) {
        // Ignore if audio context not supported
    }
}

// Show loading toast for better user feedback
function showLoadingToast(message = 'Loading...') {
    const toastHtml = `
        <div id="loading-toast" class="toast show" role="alert" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
            <div class="toast-body d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                ${message}
            </div>
        </div>
    `;
    
    // Remove existing loading toast
    const existingToast = document.getElementById('loading-toast');
    if (existingToast) existingToast.remove();
    
    document.body.insertAdjacentHTML('beforeend', toastHtml);
}

function hideLoadingToast() {
    const loadingToast = document.getElementById('loading-toast');
    if (loadingToast) loadingToast.remove();
}

// Pie Chart for Case Statuses
function renderCaseStatusPieChart(onGoing, scheduled, preCompleted) {
    const ctx = document.getElementById('caseStatusPieChart');
    if (!ctx || typeof Chart === 'undefined') return;
    
    // Destroy existing chart if it exists
    if (window.caseStatusChart) {
        window.caseStatusChart.destroy();
    }
    
    window.caseStatusChart = new Chart(ctx.getContext('2d'), {
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
    
    // Destroy existing chart if it exists
    if (window.closedCasesChart) {
        window.closedCasesChart.destroy();
    }
    
    window.closedCasesChart = new Chart(ctx.getContext('2d'), {
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
    
    // Destroy existing chart if it exists
    if (window.counselingSessionsChart) {
        window.counselingSessionsChart.destroy();
    }
    
    window.counselingSessionsChart = new Chart(ctx.getContext('2d'), {
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
    
    // Destroy existing chart if it exists
    if (window.disciplineChart) {
        window.disciplineChart.destroy();
    }

    // Build datasets based on data structure
    let datasets = [];
    
    if (data.percentages) {
        // Percentage view
        datasets = [{
            label: 'Discipline Percentage',
            data: data.percentages,
            backgroundColor: '#2e7d32',
            borderColor: '#1b5e20',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false
        }];
    } else if (data.with_discipline && data.total_students) {
        // Comparison view
        datasets = [
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
                borderColor: '#81c784',     // Light green border
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false
            }
        ];
    } else if (data.with_discipline) {
        // Discipline only view
        datasets = [{
            label: 'Students with Disciplinary Record',
            data: data.with_discipline,
            backgroundColor: '#1b5e20',
            borderColor: '#2e7d32',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false
        }];
    }

    window.disciplineChart = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
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

// Load and render the Top 5 Cases table
function loadTopCasesTable() {
    fetch('/guidance/top-cases', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('top-cases-table');
        if (!container) return;
        if (data.success && data.cases.length > 0) {
            let html = '';
            data.cases.forEach((c, i) => {
                const percentage = Math.max(20, (c.count / data.cases[0].count) * 100);
                html += `
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="fw-bold">${c.student_name || 'Unknown Student'}</small>
                            <span class="badge bg-success">${c.count}</span>
                        </div>
                        <div class="small text-muted mb-1">${c.case_title || 'Case'}</div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: ${percentage}%"></div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center text-muted py-3"><i class="ri-inbox-line fs-4 d-block mb-2"></i>No cases found</div>';
        }
    })
    .catch(() => {
        const container = document.getElementById('top-cases-table');
        if (container) {
            container.innerHTML = '<div class="text-center text-danger py-3"><i class="ri-error-warning-line fs-4 d-block mb-2"></i>Failed to load cases</div>';
        }
    });
}

// Load recent activities
function loadRecentActivities() {
    fetch('/guidance/recent-activities', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('recent-activities');
        if (!container) return;
        
        if (data.success && data.activities.length > 0) {
            let html = '';
            data.activities.forEach(activity => {
                html += `
                    <div class="activity-item ${activity.color}">
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-${activity.color} bg-opacity-10 p-2 me-3">
                                <i class="${activity.icon} text-${activity.color}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold small">${activity.title}</div>
                                <div class="text-muted small">${activity.description}</div>
                                <div class="text-muted small mt-1">
                                    <i class="ri-time-line me-1"></i>${activity.human_time}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center text-muted py-4"><i class="ri-history-line fs-4 d-block mb-2"></i>No recent activities</div>';
        }
    })
    .catch(error => {
        console.error('Error loading recent activities:', error);
        const container = document.getElementById('recent-activities');
        if (container) {
            container.innerHTML = '<div class="text-center text-danger py-4"><i class="ri-error-warning-line fs-4 d-block mb-2"></i>Failed to load activities</div>';
        }
    });
}

// Load upcoming tasks
function loadUpcomingTasks() {
    fetch('/guidance/upcoming-tasks', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('upcoming-tasks');
        const countBadge = document.getElementById('task-count');
        
        if (!container) return;
        
        if (data.success && data.tasks.length > 0) {
            if (countBadge) countBadge.textContent = data.tasks.length;
            
            let html = '';
            data.tasks.forEach(task => {
                const priorityClass = `priority-${task.priority}`;
                const statusBadge = task.status === 'overdue' ? 
                    '<span class="badge bg-warning text-dark">Overdue</span>' : 
                    '<span class="badge bg-success">Upcoming</span>';
                
                html += `
                    <div class="task-item ${priorityClass}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="fw-bold small">${task.title}</div>
                                <div class="text-muted small">${task.student}</div>
                                ${task.date ? `<div class="text-muted small mt-1">
                                    <i class="ri-calendar-line me-1"></i>${new Date(task.date).toLocaleDateString()}
                                    ${task.time ? ` at ${task.time}` : ''}
                                </div>` : ''}
                            </div>
                            <div class="ms-2">
                                ${statusBadge}
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            if (countBadge) countBadge.textContent = '0';
            container.innerHTML = '<div class="text-center text-muted py-4"><i class="ri-task-line fs-4 d-block mb-2"></i>No upcoming tasks</div>';
        }
    })
    .catch(error => {
        console.error('Error loading upcoming tasks:', error);
        const container = document.getElementById('upcoming-tasks');
        if (container) {
            container.innerHTML = '<div class="text-center text-danger py-4"><i class="ri-error-warning-line fs-4 d-block mb-2"></i>Failed to load tasks</div>';
        }
    });
}

// Load violation trends chart
function loadViolationTrends(period = '12months') {
    fetch('/guidance/violation-trends', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderViolationTrendsChart(data.labels, data.data);
        }
    })
    .catch(error => {
        console.error('Error loading violation trends:', error);
    });
}

function renderViolationTrendsChart(labels, data, chartType = 'line') {
    const ctx = document.getElementById('violationTrendsChart');
    if (!ctx || typeof Chart === 'undefined') return;
    
    // Destroy existing chart if it exists
    if (window.violationTrendsChart && typeof window.violationTrendsChart.destroy === 'function') {
        window.violationTrendsChart.destroy();
    }
    
        window.violationTrendsChart = new Chart(ctx.getContext('2d'), {
        type: chartType,
        data: {
            labels: labels,
            datasets: [{
                label: 'Violations',
                data: data,
                borderColor: '#2e7d32',
                backgroundColor: 'rgba(46, 125, 50, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointBackgroundColor: '#2e7d32',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    callbacks: {
                        label: function(context) {
                            return 'Violations: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#6c757d' }
                },
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5] },
                    ticks: { 
                        color: '#6c757d',
                        precision: 0 
                    }
                }
            }
        }
    });
}

// Load counseling effectiveness
function loadCounselingEffectiveness() {
    fetch('/guidance/counseling-effectiveness', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderCounselingEffectivenessChart(data.data);
            
            // Update effectiveness rate display
            const rateElement = document.querySelector('#effectiveness-rate .fw-bold');
            if (rateElement) {
                rateElement.textContent = data.data.effectiveness_rate + '%';
            }
        }
    })
    .catch(error => {
        console.error('Error loading counseling effectiveness:', error);
    });
}

function renderCounselingEffectivenessChart(data) {
    const ctx = document.getElementById('counselingEffectivenessChart');
    if (!ctx || typeof Chart === 'undefined') return;
    
    // Destroy existing chart if it exists
    if (window.counselingEffectivenessChart && typeof window.counselingEffectivenessChart.destroy === 'function') {
        window.counselingEffectivenessChart.destroy();
    }
    
    window.counselingEffectivenessChart = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Scheduled', 'Cancelled'],
            datasets: [{
                data: [data.completed, data.scheduled, data.cancelled],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderColor: ['#ffffff', '#ffffff', '#ffffff'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                }
            },
            cutout: '60%'
        }
    });
}

// Individual refresh functions removed - charts update automatically with filters

// Expose functions to global scope for onclick handlers
window.openQuickActionModal = openQuickActionModal;
window.scheduleNewCaseMeeting = scheduleNewCaseMeeting;
window.scheduleNewCounseling = scheduleNewCounseling;
window.scheduleHouseVisit = scheduleHouseVisit;
window.createCaseSummary = createCaseSummary;
window.closeModal = closeModal;
window.loadViolationTrends = loadViolationTrends;

// Chart rendering functions
window.renderCaseStatusPieChart = renderCaseStatusPieChart;
window.renderClosedCasesBarChart = renderClosedCasesBarChart;
window.renderCounselingSessionsBarChart = renderCounselingSessionsBarChart;
window.renderDisciplineVsTotalHistogram = renderDisciplineVsTotalHistogram;
window.renderViolationTrendsChart = renderViolationTrendsChart;
window.renderCounselingEffectivenessChart = renderCounselingEffectivenessChart;

// New analytics functions
window.loadRecentActivities = loadRecentActivities;
window.loadUpcomingTasks = loadUpcomingTasks;
window.loadCounselingEffectiveness = loadCounselingEffectiveness;
window.loadAllDashboardData = loadAllDashboardData;

// Individual chart filter functions
window.applyActivitiesFilter = applyActivitiesFilter;
window.applyTasksFilter = applyTasksFilter;
window.applyTopCasesFilter = applyTopCasesFilter;
window.applyViolationTrendsFilter = applyViolationTrendsFilter;
window.applyCounselingEffectivenessFilter = applyCounselingEffectivenessFilter;
window.applyCaseStatusFilter = applyCaseStatusFilter;
window.applyClosedCasesFilter = applyClosedCasesFilter;
window.applyCounselingSessionsFilter = applyCounselingSessionsFilter;
window.applyDisciplineStatsFilter = applyDisciplineStatsFilter;

// Individual Chart Filter Functions

// Recent Activities Filter
function applyActivitiesFilter() {
    const dateRange = document.getElementById('activitiesDateRange')?.value || 'week';
    const type = document.getElementById('activitiesType')?.value || 'all';
    
    showLoadingToast('Filtering activities...');
    
    const params = new URLSearchParams({
        date_range: dateRange,
        content_types: type === 'all' ? 'case_meeting,counseling,violation' : type
    });
    
    loadFilteredRecentActivities(params);
    hideLoadingToast();
}

// Upcoming Tasks Filter
function applyTasksFilter() {
    const dateRange = document.getElementById('tasksDateRange')?.value || 'week';
    const priority = document.getElementById('tasksPriority')?.value || 'all';
    
    if (typeof showLoadingToast === 'function') {
        showLoadingToast('Filtering tasks...');
    }
    
    const params = new URLSearchParams({
        date_range: dateRange,
        priority: priority,
        status: dateRange === 'overdue' ? 'overdue' : 'all'
    });
    
    loadFilteredUpcomingTasks(params);
    
    if (typeof hideLoadingToast === 'function') {
        hideLoadingToast();
    }
    

}

// Top Cases Filter
function applyTopCasesFilter() {
    const dateRange = document.getElementById('topCasesDateRange')?.value || 'month';
    const limit = document.getElementById('topCasesLimit')?.value || '5';
    
    showLoadingToast('Loading top cases...');
    
    fetch(`/guidance/top-cases?date_range=${dateRange}&limit=${limit}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        renderTopCases(data.cases || []);
        hideLoadingToast();
    })
    .catch(error => {
        console.error('Error loading top cases:', error);
        hideLoadingToast();
    });
}

// Violation Trends Filter
function applyViolationTrendsFilter() {
    const period = document.getElementById('violationTrendsPeriod')?.value || '12months';
    const chartType = document.getElementById('violationTrendsType')?.value || 'line';
    const severity = document.getElementById('violationSeverity')?.value || 'all';
    const groupBy = document.getElementById('violationGroupBy')?.value || 'month';
    
    showLoadingToast('Updating violation trends...');
    
    const params = new URLSearchParams({
        period: period,
        chart_type: chartType,
        severity: severity,
        group_by: groupBy
    });
    
    fetch(`/guidance/violation-trends?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderViolationTrendsChart(data.labels, data.data, chartType);
        }
        hideLoadingToast();
    })
    .catch(error => {
        console.error('Error loading violation trends:', error);
        hideLoadingToast();
    });
}

// Counseling Effectiveness Filter
function applyCounselingEffectivenessFilter() {
    const period = document.getElementById('counselingPeriod')?.value || 'month';
    const counselor = document.getElementById('counselingCounselor')?.value || 'all';
    
    showLoadingToast('Updating counseling data...');
    
    const params = new URLSearchParams({
        period: period,
        counselor: counselor
    });
    
    fetch(`/guidance/counseling-effectiveness?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderCounselingEffectivenessChart(data.data);
            
            // Update effectiveness rate display
            const rateElement = document.querySelector('#effectiveness-rate .fw-bold');
            if (rateElement) {
                rateElement.textContent = data.data.effectiveness_rate + '%';
            }
        }
        hideLoadingToast();
    })
    .catch(error => {
        console.error('Error loading counseling effectiveness:', error);
        hideLoadingToast();
    });
}

// Case Status Filter
function applyCaseStatusFilter() {
    const period = document.getElementById('caseStatusPeriod')?.value || 'month';
    
    showLoadingToast('Updating case status...');
    
    fetch(`/guidance/case-status-stats?period=${period}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
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
        hideLoadingToast();
    })
    .catch(error => {
        console.error('Error loading case status:', error);
        hideLoadingToast();
    });
}

// Closed Cases Filter
function applyClosedCasesFilter() {
    const period = document.getElementById('closedCasesPeriod')?.value || '6months';
    const view = document.getElementById('closedCasesView')?.value || 'monthly';
    
    showLoadingToast('Updating closed cases...');
    
    const params = new URLSearchParams({
        period: period,
        view: view
    });
    
    fetch(`/guidance/closed-cases-stats?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderClosedCasesBarChart(data.labels, data.data);
        }
        hideLoadingToast();
    })
    .catch(error => {
        console.error('Error loading closed cases:', error);
        hideLoadingToast();
    });
}

// Counseling Sessions Filter
function applyCounselingSessionsFilter() {
    const period = document.getElementById('counselingSessionsPeriod')?.value || '6months';
    const status = document.getElementById('counselingSessionsStatus')?.value || 'all';
    
    showLoadingToast('Updating counseling sessions...');
    
    const params = new URLSearchParams({
        period: period,
        status: status
    });
    
    fetch(`/guidance/counseling-sessions-stats?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderCounselingSessionsBarChart(data.labels, data.data);
        }
        hideLoadingToast();
    })
    .catch(error => {
        console.error('Error loading counseling sessions:', error);
        hideLoadingToast();
    });
}

// Discipline Stats Filter
function applyDisciplineStatsFilter() {
    const period = document.getElementById('disciplineStatsPeriod')?.value || '5years';
    const view = document.getElementById('disciplineStatsView')?.value || 'comparison';
    
    showLoadingToast('Updating discipline statistics...');
    
    const params = new URLSearchParams({
        period: period,
        view: view
    });
    
    fetch(`/guidance/discipline-vs-total-stats?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderDisciplineVsTotalHistogram(data.labels, data.data);
        }
        hideLoadingToast();
    })
    .catch(error => {
        console.error('Error loading discipline stats:', error);
        hideLoadingToast();
    });
}

// Refresh functions removed - charts now update automatically with filters

// Helper functions for filtered data
function loadFilteredRecentActivities(filterParams) {
    fetch(`/guidance/recent-activities?${filterParams.toString()}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('recent-activities');
        if (!container) return;
        
        if (data.success && data.activities.length > 0) {
            let html = '';
            data.activities.forEach(activity => {
                html += `
                    <div class="activity-item ${activity.color}">
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-${activity.color} bg-opacity-10 p-2 me-3">
                                <i class="${activity.icon} text-${activity.color}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold small">${activity.title}</div>
                                <div class="text-muted small">${activity.description}</div>
                                <div class="text-muted small mt-1">
                                    <i class="ri-time-line me-1"></i>${activity.human_time}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center text-muted py-4"><i class="ri-history-line fs-4 d-block mb-2"></i>No activities found</div>';
        }
    })
    .catch(error => {
        console.error('Error loading filtered recent activities:', error);
    });
}

function loadFilteredUpcomingTasks(filterParams) {
    fetch(`/guidance/upcoming-tasks?${filterParams.toString()}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('upcoming-tasks');
        const countBadge = document.getElementById('task-count');
        
        if (!container) return;
        
        if (data.success && data.tasks.length > 0) {
            if (countBadge) countBadge.textContent = data.tasks.length;
            
            let html = '';
            data.tasks.forEach(task => {
                const priorityClass = `priority-${task.priority}`;
                const statusBadge = task.status === 'overdue' ? 
                    '<span class="badge bg-warning text-dark">Overdue</span>' : 
                    '<span class="badge bg-success">Upcoming</span>';
                
                html += `
                    <div class="task-item ${priorityClass}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="fw-bold small">${task.title}</div>
                                <div class="text-muted small">${task.student}</div>
                                ${task.date ? `<div class="text-muted small mt-1">
                                    <i class="ri-calendar-line me-1"></i>${new Date(task.date).toLocaleDateString()}
                                    ${task.time ? ` at ${task.time}` : ''}
                                </div>` : ''}
                            </div>
                            <div class="ms-2">
                                ${statusBadge}
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            if (countBadge) countBadge.textContent = '0';
            container.innerHTML = '<div class="text-center text-muted py-4"><i class="ri-task-line fs-4 d-block mb-2"></i>No tasks found</div>';
        }
    })
    .catch(error => {
        console.error('Error loading filtered upcoming tasks:', error);
        const container = document.getElementById('upcoming-tasks');
        if (container) {
            container.innerHTML = '<div class="text-center text-danger py-4"><i class="ri-error-warning-line fs-4 d-block mb-2"></i>Error loading tasks</div>';
        }
        // Use fallback alert if showAlert is not available
        if (typeof showAlert === 'function') {
            showAlert('Error loading tasks', 'danger', 3000);
        } else {
            console.error('showAlert function not available');
        }
    });
}

function renderTopCases(cases) {
    const container = document.getElementById('top-cases-table');
    if (!container) return;
    
    if (cases && cases.length > 0) {
        let html = '';
        cases.forEach((c, i) => {
            const percentage = Math.max(20, (c.count / cases[0].count) * 100);
            html += `
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-bold">${c.student_name || 'Unknown Student'}</small>
                        <span class="badge bg-success">${c.count}</span>
                    </div>
                    <div class="small text-muted mb-1">${c.case_title || 'Case'}</div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-gradient" style="width: ${percentage}%"></div>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    } else {
        container.innerHTML = '<div class="text-center text-muted py-3"><i class="ri-inbox-line fs-4 d-block mb-2"></i>No cases found</div>';
    }
}
