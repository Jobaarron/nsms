// Enhanced Guidance Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
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
        'yearFilter': '2025',
        'activitiesDateRange': 'week',
        'activitiesType': 'all',
        'tasksDateRange': 'week', 
        'tasksPriority': 'all',
        'topCasesDateRange': 'month',
        'topCasesLimit': '5',
        'violationTrendsPeriod': '12months',
        'violationTrendsType': 'line',
        'violationSeverity': 'all',
        'counselingPeriod': 'month',
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
        'yearFilter': applyYearFilter,
        'activitiesDateRange': applyActivitiesFilter,
        'activitiesType': applyActivitiesFilter,
        'tasksDateRange': applyTasksFilter,
        'tasksPriority': applyTasksFilter,
        'topCasesDateRange': applyTopCasesFilter,
        'topCasesLimit': applyTopCasesFilter,
        'violationTrendsPeriod': applyViolationTrendsFilter,
        'violationTrendsType': applyViolationTrendsFilter,
        'violationSeverity': applyViolationTrendsFilter,
        'counselingPeriod': applyCounselingEffectivenessFilter,
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
    // Initialize current year
    window.currentYear = document.getElementById('yearFilter')?.value || '2025';
    
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
                    '#28a745', // Primary green
                    '#ffc107', // Yellow
                    '#20c997'  // Teal (complementary)
                ],
                borderColor: [
                    '#ffffff',
                    '#ffffff',
                    '#ffffff'
                ],
                hoverBackgroundColor: [
                    '#218838',
                    '#e0a800',
                    '#1ba085'
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
    const year = getCurrentYear();
    let url = '/guidance/case-status-stats';
    if (year !== 'all') {
        url += `?year=${encodeURIComponent(year)}`;
    }
    
    fetch(url, {
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
    });
}

// Bar Chart for Closed Cases Per Month (Dynamic)
function loadClosedCasesBarChart() {
    const year = getCurrentYear();
    let url = '/guidance/closed-cases-stats';
    if (year !== 'all') {
        url += `?year=${encodeURIComponent(year)}`;
    }
    
    fetch(url, {
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
                backgroundColor: '#28a745',
                borderColor: '#28a745',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: '#218838'
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
    const year = getCurrentYear();
    let url = '/guidance/counseling-sessions-stats';
    if (year !== 'all') {
        url += `?year=${encodeURIComponent(year)}`;
    }
    
    fetch(url, {
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
                backgroundColor: '#ffc107',
                borderColor: '#ffc107',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: '#e0a800'
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
    const year = getCurrentYear();
    let url = '/guidance/discipline-vs-total-stats';
    if (year !== 'all') {
        url += `?year=${encodeURIComponent(year)}`;
    }
    
    fetch(url, {
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
            backgroundColor: '#ffc107',
            borderColor: '#ffc107',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false,
            hoverBackgroundColor: '#e0a800'
        }];
    } else if (data.with_discipline && data.total_students) {
        // Comparison view
        datasets = [
            {
                label: 'With Disciplinary Record',
                data: data.with_discipline,
                backgroundColor: '#28a745',  // Primary green
                borderColor: '#28a745',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: '#218838'
            },
            {
                label: 'Total Students',
                data: data.total_students,
                backgroundColor: '#ffc107',  // Yellow
                borderColor: '#ffc107',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: '#e0a800'
            }
        ];
    } else if (data.with_discipline) {
        // Discipline only view
        datasets = [{
            label: 'Students with Disciplinary Record',
            data: data.with_discipline,
            backgroundColor: '#28a745',
            borderColor: '#28a745',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false,
            hoverBackgroundColor: '#218838'
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
    const year = getCurrentYear();
    let url = '/guidance/top-cases';
    if (year !== 'all') {
        url += `?year=${encodeURIComponent(year)}`;
    }
    
    fetch(url, {
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
    const year = getCurrentYear();
    let url = '/guidance/recent-activities';
    if (year !== 'all') {
        url += `?year=${encodeURIComponent(year)}`;
    }
    
    fetch(url, {
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
        const container = document.getElementById('recent-activities');
        if (container) {
            container.innerHTML = '<div class="text-center text-danger py-4"><i class="ri-error-warning-line fs-4 d-block mb-2"></i>Failed to load activities</div>';
        }
    });
}

// Load upcoming tasks
function loadUpcomingTasks() {
    const year = getCurrentYear();
    let url = '/guidance/upcoming-tasks';
    if (year !== 'all') {
        url += `?year=${encodeURIComponent(year)}`;
    }
    
    fetch(url, {
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
        const container = document.getElementById('upcoming-tasks');
        if (container) {
            container.innerHTML = '<div class="text-center text-danger py-4"><i class="ri-error-warning-line fs-4 d-block mb-2"></i>Failed to load tasks</div>';
        }
    });
}

// Load violation trends chart
function loadViolationTrends(period = '12months') {
    const year = getCurrentYear();
    let url = '/guidance/violation-trends';
    const params = [];
    if (period) params.push(`period=${period}`);
    if (year !== 'all') params.push(`year=${encodeURIComponent(year)}`);
    if (params.length > 0) url += '?' + params.join('&');
    
    fetch(url, {
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
    });
}

function renderViolationTrendsChart(labels, data, chartType = 'line') {
    const ctx = document.getElementById('violationTrendsChart');
    if (!ctx || typeof Chart === 'undefined') return;
    
    // Validate chart type - only allow line and bar charts
    if (!['line', 'bar'].includes(chartType)) {
        chartType = 'line';
    }
    
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
                borderColor: '#28a745',
                backgroundColor: chartType === 'line' ? 'rgba(40, 167, 69, 0.1)' : '#28a745',
                fill: chartType === 'line',
                tension: chartType === 'line' ? 0.4 : 0,
                borderWidth: 3,
                pointBackgroundColor: '#28a745',
                pointHoverBackgroundColor: '#218838',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: chartType === 'line' ? 5 : 0,
                borderRadius: chartType === 'bar' ? 6 : 0,
                borderSkipped: chartType === 'bar' ? false : undefined
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
    const year = getCurrentYear();
    let url = '/guidance/counseling-effectiveness';
    if (year !== 'all') {
        url += `?year=${encodeURIComponent(year)}`;
    }
    
    fetch(url, {
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
                backgroundColor: ['#28a745', '#ffc107', '#20c997'],
                borderColor: ['#ffffff', '#ffffff', '#ffffff'],
                borderWidth: 2,
                hoverBackgroundColor: ['#218838', '#e0a800', '#1ba085']
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

// Guidance Notification Bell Functions
function toggleGuidanceNotificationPanel() {
    const panel = document.getElementById('guidanceNotificationPanel');
    const isVisible = panel.style.display !== 'none';
    
    if (isVisible) {
        panel.style.display = 'none';
    } else {
        panel.style.display = 'block';
        loadGuidanceNotifications();
    }
    
    // Close panel when clicking outside
    if (!isVisible) {
        setTimeout(() => {
            document.addEventListener('click', closeGuidanceNotificationOnClickOutside, { once: true });
        }, 100);
    }
}

function closeGuidanceNotificationOnClickOutside(event) {
    const panel = document.getElementById('guidanceNotificationPanel');
    const bell = document.querySelector('.guidance-notification-bell');
    
    if (!panel.contains(event.target) && !bell.contains(event.target)) {
        panel.style.display = 'none';
    }
}

function loadGuidanceNotifications() {
    const listContainer = document.getElementById('guidanceNotificationList');
    
    // Show loading state
    listContainer.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Loading notifications...</div>
        </div>
    `;
    
    // Fetch notifications from the Notice model for guidance (filtered for specific types)
    fetch('/guidance/notifications/api?filter=guidance_only', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.notifications) {
            renderGuidanceNotifications(data.notifications);
            updateGuidanceNotificationBadge(data.unread_count || 0);
        } else {
            showEmptyGuidanceNotifications();
        }
    })
    .catch(error => {
        listContainer.innerHTML = `
            <div class="text-center py-3 text-muted">
                <i class="ri-error-warning-line fs-4 d-block mb-2 text-danger"></i>
                <div>Error loading notifications</div>
                <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadGuidanceNotifications()">
                    Try Again
                </button>
            </div>
        `;
    });
}

function renderGuidanceNotifications(notifications) {
    const listContainer = document.getElementById('guidanceNotificationList');
    
    if (notifications.length === 0) {
        showEmptyNotifications();
        return;
    }
    
    const notificationHtml = notifications.map(notification => {
        // Determine notification type and icon
        let icon = 'ri-notification-line';
        let badgeColor = 'bg-info';
        let typeText = '';
        
        const title = notification.title.toLowerCase();
        if (title.includes('case meeting')) {
            icon = 'ri-calendar-event-line';
            badgeColor = 'bg-primary';
            typeText = 'Case Meeting';
        } else if (title.includes('counseling session')) {
            icon = 'ri-heart-pulse-line';
            badgeColor = 'bg-success';
            typeText = 'Counseling';
        } else if (title.includes('teacher reply')) {
            icon = 'ri-reply-line';
            badgeColor = 'bg-warning';
            typeText = 'Teacher Reply';
        } else if (title.includes('forwarded')) {
            icon = 'ri-share-forward-line';
            badgeColor = 'bg-danger';
            typeText = 'Forwarded Case';
        } else if (title.includes('recommended')) {
            icon = 'ri-user-heart-line';
            badgeColor = 'bg-info';
            typeText = 'Recommendation';
        }
        
        return `
            <div class="notification-item ${!notification.is_read ? 'unread' : ''}" 
                 onclick="viewNotification(${notification.id})" 
                 style="cursor: pointer;" 
                 title="Click to view details">
                <div class="d-flex align-items-start">
                    <div class="me-2 mt-1">
                        <i class="${icon} text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-message">${notification.preview_message || notification.message.substring(0, 80) + '...'}</div>
                        <div class="notification-time">
                            <i class="ri-time-line me-1"></i>${notification.time_ago || notification.formatted_date}
                            ${typeText ? `<span class="badge ${badgeColor} ms-2">${typeText}</span>` : ''}
                            ${!notification.is_read ? '<span class="badge bg-warning ms-1">New</span>' : ''}
                            <i class="ri-arrow-right-line ms-2 text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    listContainer.innerHTML = notificationHtml;
}

function showEmptyGuidanceNotifications() {
    const listContainer = document.getElementById('guidanceNotificationList');
    listContainer.innerHTML = `
        <div class="text-center py-3 text-muted">
            <i class="ri-notification-off-line fs-3 d-block mb-2"></i>
            <div>No guidance notifications</div>
            <small class="text-muted">Notifications for case meetings, counseling sessions, teacher replies, forwarded cases, and recommendations will appear here</small>
        </div>
    `;
}

function updateGuidanceNotificationBadge(count) {
    const badge = document.getElementById('guidance-notification-count');
    const bell = document.querySelector('.guidance-notification-bell');
    
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'block';
        bell.classList.add('has-notifications');
    } else {
        badge.style.display = 'none';
        bell.classList.remove('has-notifications');
    }
}

function viewNotification(notificationId) {
    // Mark as read and redirect to appropriate page
    fetch(`/guidance/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh notifications
            loadGuidanceNotifications();
            // Redirect based on notification type
            redirectBasedOnNotificationType(data.notification);
        }
    })
    .catch(error => {
    });
    
    // Close guidance notification panel
    document.getElementById('guidanceNotificationPanel').style.display = 'none';
}

function redirectBasedOnNotificationType(notification) {
    const title = notification.title.toLowerCase();
    const message = notification.message.toLowerCase();
    
    if (title.includes('case meeting') || title.includes('forwarded') || message.includes('case meeting')) {
        // Redirect to case meetings page
        window.location.href = '/guidance/case-meetings';
    } else if (title.includes('counseling session') || title.includes('recommended') || message.includes('counseling session') || message.includes('recommended')) {
        // Redirect to counseling sessions page
        window.location.href = '/guidance/counseling-sessions';
    } else if (title.includes('teacher reply') || message.includes('teacher reply')) {
        // Redirect to case meetings page (since teacher replies are for case meetings)
        window.location.href = '/guidance/case-meetings';
    } else {
        // Default: show notification details in modal for other types
        showNotificationDetails(notification);
    }
}

function showNotificationDetails(notification) {
    // Mark notification as read when viewing details
    markNotificationAsRead(notification.id);
    
    // Create and show notification details modal
    const modalHtml = `
        <div class="modal fade" id="notificationDetailModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ri-notification-line me-2"></i>${notification.title}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <strong>Message:</strong>
                            <div class="mt-2 p-3 bg-light rounded">${notification.message}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <small><strong>Date:</strong> ${notification.formatted_date}</small>
                            </div>
                            <div class="col-md-6">
                                <small><strong>From:</strong> ${notification.creator_name || 'System'}</small>
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
    
    // Remove existing modal if any
    const existingModal = document.getElementById('notificationDetailModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body and show
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('notificationDetailModal'));
    modal.show();
    
    // Remove modal after hiding
    document.getElementById('notificationDetailModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function markNotificationAsRead(notificationId) {
    fetch(`/guidance/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh notifications to update count and remove read notification
            loadGuidanceNotifications();
            loadGuidanceNotificationCount();
        }
    })
    .catch(error => {
    });
}

function markAllGuidanceAsRead() {
    fetch('/guidance/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadGuidanceNotifications();
            showAlert('All notifications marked as read', 'success');
        }
    })
    .catch(error => {
        showAlert('Error marking notifications as read', 'danger');
    });
}

function viewAllNotifications() {
    // Navigate to full notifications page (to be created)
    window.location.href = '/guidance/notifications';
}

// Auto-load guidance notifications on page load and set up periodic refresh
document.addEventListener('DOMContentLoaded', function() {
    // Initial load of notification count
    setTimeout(loadGuidanceNotificationCount, 1000);
    
    // Refresh notification count every 5 seconds for near real-time updates
    setInterval(loadGuidanceNotificationCount, 5000);
});

function loadGuidanceNotificationCount() {
    fetch('/guidance/notifications/count?filter=guidance_only', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateGuidanceNotificationBadge(data.count || 0);
        }
    })
    .catch(error => {
    });
}

// Expose functions to global scope for onclick handlers
window.openQuickActionModal = openQuickActionModal;
window.scheduleNewCaseMeeting = scheduleNewCaseMeeting;
window.scheduleNewCounseling = scheduleNewCounseling;
window.scheduleHouseVisit = scheduleHouseVisit;
window.createCaseSummary = createCaseSummary;
window.closeModal = closeModal;
window.loadViolationTrends = loadViolationTrends;

// Guidance Notification functions
window.toggleGuidanceNotificationPanel = toggleGuidanceNotificationPanel;
window.loadGuidanceNotifications = loadGuidanceNotifications;
window.viewNotification = viewNotification;
window.markAllGuidanceAsRead = markAllGuidanceAsRead;
window.viewAllNotifications = viewAllNotifications;

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
window.applyYearFilter = applyYearFilter;
window.getCurrentYear = getCurrentYear;
window.refreshDashboardStats = refreshDashboardStats;
window.updateStatisticsCards = updateStatisticsCards;
window.applyActivitiesFilter = applyActivitiesFilter;
window.applyTasksFilter = applyTasksFilter;
window.applyTopCasesFilter = applyTopCasesFilter;
window.applyViolationTrendsFilter = applyViolationTrendsFilter;
window.resetViolationTrendsFilter = resetViolationTrendsFilter;
window.applyCounselingEffectivenessFilter = applyCounselingEffectivenessFilter;
window.applyCaseStatusFilter = applyCaseStatusFilter;
window.applyClosedCasesFilter = applyClosedCasesFilter;
window.applyCounselingSessionsFilter = applyCounselingSessionsFilter;
window.applyDisciplineStatsFilter = applyDisciplineStatsFilter;

// Reset violation trends filter to defaults
function resetViolationTrendsFilter() {
    document.getElementById('violationTrendsPeriod').value = '12months';
    document.getElementById('violationTrendsType').value = 'line';
    document.getElementById('violationSeverity').value = 'all';
    
    // Apply the reset filters
    applyViolationTrendsFilter();
}

// Individual Chart Filter Functions

// Recent Activities Filter
function applyActivitiesFilter() {
    const dateRange = document.getElementById('activitiesDateRange')?.value || 'week';
    const type = document.getElementById('activitiesType')?.value || 'all';
    const year = getCurrentYear();
    
    const params = new URLSearchParams({
        date_range: dateRange,
        content_types: type === 'all' ? 'case_meeting,counseling,violation' : type
    });
    
    if (year !== 'all') {
        params.append('year', year);
    }
    
    loadFilteredRecentActivities(params);
}

// Upcoming Tasks Filter
function applyTasksFilter() {
    const dateRange = document.getElementById('tasksDateRange')?.value || 'week';
    const priority = document.getElementById('tasksPriority')?.value || 'all';
    const year = getCurrentYear();
    
    const params = new URLSearchParams({
        date_range: dateRange,
        priority: priority,
        status: dateRange === 'overdue' ? 'overdue' : 'all'
    });
    
    if (year !== 'all') {
        params.append('year', year);
    }
    
    loadFilteredUpcomingTasks(params);
    

}

// Top Cases Filter
function applyTopCasesFilter() {
    const dateRange = document.getElementById('topCasesDateRange')?.value || 'month';
    const limit = document.getElementById('topCasesLimit')?.value || '5';
    const year = getCurrentYear();
    
    let url = `/guidance/top-cases?date_range=${dateRange}&limit=${limit}`;
    if (year !== 'all') {
        url += `&year=${encodeURIComponent(year)}`;
    }
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        renderTopCases(data.cases || []);
    })
    .catch(error => {
    });
}

// Violation Trends Filter
function applyViolationTrendsFilter() {
    const period = document.getElementById('violationTrendsPeriod')?.value || '12months';
    const chartType = document.getElementById('violationTrendsType')?.value || 'line';
    const severity = document.getElementById('violationSeverity')?.value || 'all';
    const year = getCurrentYear();
    
    const params = new URLSearchParams({
        period: period,
        chart_type: chartType,
        severity: severity
    });
    
    if (year !== 'all') {
        params.append('year', year);
    }
    
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
    })
    .catch(error => {
    });
}

// Counseling Effectiveness Filter
function applyCounselingEffectivenessFilter() {
    const period = document.getElementById('counselingPeriod')?.value || 'month';
    const year = getCurrentYear();
    
    const params = new URLSearchParams({
        period: period
    });
    
    if (year !== 'all') {
        params.append('year', year);
    }
    
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
    })
    .catch(error => {
    });
}

// Case Status Filter
function applyCaseStatusFilter() {
    const period = document.getElementById('caseStatusPeriod')?.value || 'month';
    const year = getCurrentYear();
    
    let url = `/guidance/case-status-stats?period=${period}`;
    if (year !== 'all') {
        url += `&year=${encodeURIComponent(year)}`;
    }
    
    fetch(url, {
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
    })
    .catch(error => {
    });
}

// Closed Cases Filter
function applyClosedCasesFilter() {
    const period = document.getElementById('closedCasesPeriod')?.value || '6months';
    const view = document.getElementById('closedCasesView')?.value || 'monthly';
    const year = getCurrentYear();
    
    const params = new URLSearchParams({
        period: period,
        view: view
    });
    
    if (year !== 'all') {
        params.append('year', year);
    }
    
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
    })
    .catch(error => {
    });
}

// Counseling Sessions Filter
function applyCounselingSessionsFilter() {
    const period = document.getElementById('counselingSessionsPeriod')?.value || '6months';
    const status = document.getElementById('counselingSessionsStatus')?.value || 'all';
    const year = getCurrentYear();
    
    const params = new URLSearchParams({
        period: period,
        status: status
    });
    
    if (year !== 'all') {
        params.append('year', year);
    }
    
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
    })
    .catch(error => {
    });
}

// Get current year for API calls
function getCurrentYear() {
    return window.currentYear || document.getElementById('yearFilter')?.value || '2025';
}

// Refresh dashboard statistics cards
function refreshDashboardStats() {
    const year = getCurrentYear();
    console.log('Refreshing guidance dashboard stats for year:', year);
    
    fetch(`/guidance/dashboard-stats?year=${year}`)
        .then(response => {
            console.log('Guidance dashboard stats response:', response);
            return response.json();
        })
        .then(data => {
            console.log('Guidance dashboard stats data:', data);
            if (data.success) {
                updateStatisticsCards(data.stats);
            } else {
                console.error('Guidance dashboard stats request failed:', data);
            }
        })
        .catch(error => {
            console.error('Error refreshing guidance dashboard stats:', error);
        });
}

// Update statistics cards with new data
function updateStatisticsCards(stats) {
    // Use specific IDs for reliable targeting
    const totalStudentsElement = document.getElementById('stat-total-students');
    const activeCaseMeetingsElement = document.getElementById('stat-active-case-meetings');
    const scheduledCounselingElement = document.getElementById('stat-scheduled-counseling');
    const studentsDisciplinaryRecordElement = document.getElementById('stat-students-disciplinary-record');

    // Update the statistics with animation
    if (totalStudentsElement) {
        animateNumberChange(totalStudentsElement, stats.total_students || 0);
    }
    if (activeCaseMeetingsElement) {
        animateNumberChange(activeCaseMeetingsElement, stats.active_case_meetings || 0);
    }
    if (scheduledCounselingElement) {
        animateNumberChange(scheduledCounselingElement, stats.scheduled_counseling || 0);
    }
    if (studentsDisciplinaryRecordElement) {
        animateNumberChange(studentsDisciplinaryRecordElement, stats.students_with_disciplinary_record || 0);
    }

    console.log('Guidance statistics updated:', stats);
}

// Animate number changes for better UX
function animateNumberChange(element, newValue) {
    const currentValue = parseInt(element.textContent) || 0;
    if (currentValue === newValue) return;
    
    element.style.transition = 'transform 0.2s ease';
    element.style.transform = 'scale(1.1)';
    
    setTimeout(() => {
        element.textContent = newValue;
        element.style.transform = 'scale(1)';
    }, 100);
}

// Apply Year Filter
function applyYearFilter(yearOverride = null) {
    const year = yearOverride || document.getElementById('yearFilter')?.value || '2025';
    window.currentYear = year;
    
    // Refresh dashboard statistics first
    refreshDashboardStats();
    
    // Reload all dashboard data with new year
    loadAllDashboardData();
}

// Discipline Stats Filter
function applyDisciplineStatsFilter() {
    const period = document.getElementById('disciplineStatsPeriod')?.value || '5years';
    const view = document.getElementById('disciplineStatsView')?.value || 'comparison';
    const year = getCurrentYear();
    
    const params = new URLSearchParams({
        period: period,
        view: view
    });
    
    if (year !== 'all') {
        params.append('year', year);
    }
    
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
    })
    .catch(error => {
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
        const container = document.getElementById('upcoming-tasks');
        if (container) {
            container.innerHTML = '<div class="text-center text-danger py-4"><i class="ri-error-warning-line fs-4 d-block mb-2"></i>Error loading tasks</div>';
        }
        // Use fallback alert if showAlert is not available
        if (typeof showAlert === 'function') {
            showAlert('Error loading tasks', 'danger', 3000);
        } else {
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