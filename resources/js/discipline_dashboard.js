// Enhanced Discipline Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Enhanced Discipline Dashboard loaded');
    
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
        'violationsDateRange': 'week',
        'violationSeverity': 'all',
        'pendingPriority': 'all',
        'criticalCasesLimit': '5',
        'violationTrendsPeriod': '12months',
        'violationTrendsType': 'line',
        'disciplinePeriod': 'month'
    };
    
    // Set default values for all filter selects
    Object.entries(defaultValues).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.value = value;
            // Add change event listener
            element.addEventListener('change', () => triggerRelatedFilter(id));
        }
    });
}

// Trigger the appropriate filter function based on element ID
function triggerRelatedFilter(elementId) {
    const filterMap = {
        'violationsDateRange': applyViolationsFilter,
        'violationSeverity': applyViolationsFilter,
        'pendingPriority': applyPendingFilter,
        'criticalCasesLimit': applyCriticalCasesFilter,
        'violationTrendsPeriod': applyViolationTrendsFilter,
        'violationTrendsType': applyViolationTrendsFilter
        // 'disciplinePeriod': applyDisciplineEffectivenessFilter // Removed
    };
    
    const filterFunction = filterMap[elementId];
    if (typeof filterFunction === 'function') {
        filterFunction();
    }
}

// Load all dashboard data with current filter states
function loadAllDashboardData() {
    // Load charts first
    loadViolationPieChart();
    loadViolationBarChart();
    loadCaseStatusChart();
    
    // Apply individual chart filters (with fallback for missing filters)
    if (typeof applyViolationsFilter === 'function') {
        applyViolationsFilter();
    } else {
        loadRecentViolations();
    }
    
    if (typeof applyPendingFilter === 'function') {
        applyPendingFilter();
    } else {
        loadPendingActions();
    }
    
    if (typeof applyCriticalCasesFilter === 'function') {
        applyCriticalCasesFilter();
    } else {
        loadCriticalCases();
    }
    
    if (typeof applyViolationTrendsFilter === 'function') {
        applyViolationTrendsFilter();
    } else {
        loadViolationTrends();
    }
    
    // Note: Discipline effectiveness functionality removed
    // if (typeof applyDisciplineEffectivenessFilter === 'function') {
    //     applyDisciplineEffectivenessFilter();
    // } else {
    //     loadDisciplineEffectiveness();
    // }
}


// Real-time clock update
function updateClock() {
    const now = new Date();
    const timeElement = document.querySelector('.ri-time-line').nextSibling;
    if (timeElement) {
        timeElement.textContent = now.toLocaleString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

// Show loading states for better UX
function showLoadingStates() {
    const loadingElements = [
        'recent-violations',
        'pending-actions',
        'critical-cases-table'
    ];
    
    loadingElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = `
                <div class="d-flex justify-content-center align-items-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
        }
    });
}

// Quick action functions
function recordNewViolation() {
    window.location.href = '/discipline/violations';
}

// Removed: scheduleDisciplinaryMeeting and generateReport functions

function studentLookup() {
    window.location.href = '/discipline/students';
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
        newAlert.style.transform = 'translateX(100%)';
        newAlert.style.opacity = '0';
        setTimeout(() => {
            if (newAlert.parentNode) {
                newAlert.parentNode.removeChild(newAlert);
            }
        }, 300);
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
        
        oscillator.frequency.value = type === 'danger' ? 800 : 400;
        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
        
        oscillator.start();
        oscillator.stop(audioContext.currentTime + 0.3);
    } catch (e) {
        console.log('Audio not supported');
    }
}

// Load recent violations
function loadRecentViolations() {
    const container = document.getElementById('recent-violations');
    if (!container) return;
    
    showLoadingToast('Loading recent violations...');
    
    fetch('/discipline/recent-violations')
        .then(response => response.json())
        .then(data => {
            hideLoadingToast();
            renderRecentViolations(data.violations || []);
        })
        .catch(error => {
            hideLoadingToast();
            console.error('Error loading recent violations:', error);
            container.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="ri-error-warning-line fs-3 mb-2"></i>
                    <p>Failed to load recent violations</p>
                </div>
            `;
        });
}

function renderRecentViolations(violations) {
    const container = document.getElementById('recent-violations');
    if (!container) return;
    
    if (!violations || violations.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="ri-shield-check-line fs-3 mb-2 text-success"></i>
                <p>No recent violations</p>
            </div>
        `;
        return;
    }
    
    const html = violations.map(violation => {
        const dateTimeText = violation.date ? 
            (violation.time ? `${violation.date} at ${violation.time}` : violation.date) : 
            'Date not available';
        
        return `
        <div class="activity-item ${violation.severity === 'major' ? 'priority-high' : 'priority-medium'}">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${violation.title || 'Violation'}</h6>
                    <p class="text-muted mb-1 small"><strong>${violation.student_name}</strong></p>
                    <small class="text-muted">${dateTimeText}</small>
                </div>
                <span class="badge" style="background-color: ${violation.severity === 'major' ? '#198754' : '#28a745'}">${violation.severity}</span>
            </div>
        </div>
        `;
    }).join('');
    
    container.innerHTML = html;
}

// Load pending actions
function loadPendingActions() {
    const container = document.getElementById('pending-actions');
    if (!container) return;
    
    showLoadingToast('Loading pending actions...');
    
    fetch('/discipline/pending-actions')
        .then(response => response.json())
        .then(data => {
            hideLoadingToast();
            renderPendingActions(data.actions || []);
        })
        .catch(error => {
            hideLoadingToast();
            console.error('Error loading pending actions:', error);
            container.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="ri-error-warning-line fs-3 mb-2"></i>
                    <p>Failed to load pending actions</p>
                </div>
            `;
        });
}

function renderPendingActions(actions) {
    const container = document.getElementById('pending-actions');
    if (!container) return;
    
    if (!actions || actions.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="ri-checkbox-circle-line fs-3 mb-2 text-success"></i>
                <p>No pending actions</p>
            </div>
        `;
        return;
    }
    
    const html = actions.map(action => `
        <div class="task-item priority-${action.priority}">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${action.title}</h6>
                    <p class="text-muted mb-1 small">${action.description}</p>
                    <small class="text-muted">${action.date || 'No date specified'}</small>
                </div>
                <span class="badge" style="background-color: ${action.priority === 'high' ? '#198754' : action.priority === 'medium' ? '#28a745' : '#20c997'}">${action.priority}</span>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

// Load critical cases
function loadCriticalCases() {
    const container = document.getElementById('critical-cases-table');
    if (!container) return;
    
    showLoadingToast('Loading critical cases...');
    
    fetch('/discipline/critical-cases')
        .then(response => response.json())
        .then(data => {
            hideLoadingToast();
            renderCriticalCases(data.cases || []);
        })
        .catch(error => {
            hideLoadingToast();
            console.error('Error loading critical cases:', error);
            container.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="ri-error-warning-line fs-3 mb-2"></i>
                    <p>Failed to load critical cases</p>
                </div>
            `;
        });
}

function renderCriticalCases(cases) {
    const container = document.getElementById('critical-cases-table');
    if (!container) return;
    
    if (!cases || cases.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="ri-shield-check-line fs-3 mb-2 text-success"></i>
                <p>No critical cases</p>
            </div>
        `;
        return;
    }
    
    const html = `
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Violations</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${cases.map(case_ => `
                        <tr>
                            <td>
                                <strong>${case_.student_name}</strong>
                                <br><small class="text-muted">${case_.student_id}</small>
                            </td>
                            <td>
                                <span class="badge bg-success">${case_.violation_count}</span>
                            </td>
                            <td>
                                <span class="badge bg-${getStatusBadgeColor(case_.status)}">${case_.status.toUpperCase()}</span>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
}

// Helper function to get status badge color
function getStatusBadgeColor(status) {
    switch (status) {
        case 'active':
            return 'success'; // Light green for active
        case 'serious':
            return 'warning'; // Yellow-green for serious
        case 'critical':
            return 'dark'; // Dark green for critical
        default:
            return 'secondary';
    }
}

// Load violation trends chart
function loadViolationTrends() {
    const canvas = document.getElementById('violationTrendsChart');
    if (!canvas) return;
    
    fetch('/discipline/violation-trends')
        .then(response => response.json())
        .then(data => {
            // Convert backend data format to chart format
            const datasets = [
                {
                    label: 'Minor Violations',
                    data: data.minor || []
                },
                {
                    label: 'Major Violations', 
                    data: data.major || []
                }
            ];
            renderViolationTrendsChart(data.labels || [], datasets);
        })
        .catch(error => {
            console.error('Error loading violation trends:', error);
        });
}

function renderViolationTrendsChart(labels, datasets, chartType = 'line') {
    const canvas = document.getElementById('violationTrendsChart');
    if (!canvas) return;
    
    // Validate input data
    if (!labels || !Array.isArray(labels)) labels = [];
    if (!datasets || !Array.isArray(datasets)) datasets = [];
    
    // Destroy existing chart if it exists
    if (window.violationTrendsChart && typeof window.violationTrendsChart.destroy === 'function') {
        window.violationTrendsChart.destroy();
    }
    
    const ctx = canvas.getContext('2d');
    window.violationTrendsChart = new Chart(ctx, {
        type: chartType,
        data: {
            labels: labels,
            datasets: datasets.map((dataset, index) => ({
                label: dataset.label,
                data: dataset.data,
                backgroundColor: index === 0 ? 'rgba(40, 167, 69, 0.2)' : 'rgba(25, 135, 84, 0.2)',
                borderColor: index === 0 ? '#28a745' : '#198754',
                borderWidth: 2,
                tension: chartType === 'line' ? 0.4 : 0
            }))
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Load discipline effectiveness
function loadDisciplineEffectiveness() {
    const canvas = document.getElementById('disciplineEffectivenessChart');
    if (!canvas) return;
    
    fetch('/discipline/effectiveness')
        .then(response => response.json())
        .then(data => {
            renderDisciplineEffectivenessChart(data);
            document.getElementById('resolutionRate').textContent = data.resolution_rate + '%';
        })
        .catch(error => {
            console.error('Error loading discipline effectiveness:', error);
        });
}

function renderDisciplineEffectivenessChart(data) {
    const canvas = document.getElementById('disciplineEffectivenessChart');
    if (!canvas) return;
    
    // Destroy existing chart if it exists
    if (window.disciplineEffectivenessChart) {
        window.disciplineEffectivenessChart.destroy();
    }
    
    const ctx = canvas.getContext('2d');
    window.disciplineEffectivenessChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Resolved', 'Pending', 'Escalated'],
            datasets: [{
                data: [data.resolved, data.pending, data.escalated],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
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

// Individual Chart Filter Functions

// Violations Filter
function applyViolationsFilter(dateRangeOverride = null, severityOverride = null) {
    const dateRange = dateRangeOverride || document.getElementById('violationsDateRange')?.value || 'week';
    const severity = severityOverride || document.getElementById('violationSeverity')?.value || 'all';
    
    const filterParams = { filter: dateRange, severity };
    loadFilteredRecentViolations(filterParams);
    
    // Show filter feedback
    if (dateRangeOverride) {
        showNotification(`Violations filtered to: ${dateRange}`, 'success');
    }
}

// Pending Actions Filter
function applyPendingFilter(priorityOverride = null) {
    const priority = priorityOverride || document.getElementById('pendingPriority')?.value || 'all';
    
    const filterParams = { filter: priority };
    loadFilteredPendingActions(filterParams);
    
    // Show filter feedback
    if (priorityOverride) {
        showNotification(`Pending actions filtered to: ${priority} priority`, 'success');
    }
}

// Critical Cases Filter
function applyCriticalCasesFilter(limitOverride = null) {
    const limit = limitOverride || document.getElementById('criticalCasesLimit')?.value || '5';
    
    const filterParams = { limit };
    loadFilteredCriticalCases(filterParams);
    
    // Show filter feedback
    if (limitOverride) {
        showNotification(`Critical cases limited to: top ${limit}`, 'success');
    }
}

// Violation Trends Filter
function applyViolationTrendsFilter() {
    const period = document.getElementById('violationTrendsPeriod')?.value || '12months';
    const type = document.getElementById('violationTrendsType')?.value || 'line';
    
    fetch(`/discipline/violation-trends?period=${period}`)
        .then(response => response.json())
        .then(data => {
            // Convert backend data format to chart format
            const datasets = [
                {
                    label: 'Minor Violations',
                    data: data.minor || []
                },
                {
                    label: 'Major Violations', 
                    data: data.major || []
                }
            ];
            renderViolationTrendsChart(data.labels || [], datasets, type);
        })
        .catch(error => {
            console.error('Error loading violation trends:', error);
        });
}

// Discipline Effectiveness Filter
function applyDisciplineEffectivenessFilter() {
    const period = document.getElementById('disciplinePeriod')?.value || 'month';
    
    fetch(`/discipline/effectiveness?period=${period}`)
        .then(response => response.json())
        .then(data => {
            renderDisciplineEffectivenessChart(data);
            document.getElementById('resolutionRate').textContent = data.resolution_rate + '%';
        })
        .catch(error => {
            console.error('Error loading discipline effectiveness:', error);
        });
}

// Helper functions for filtered data
function loadFilteredRecentViolations(filterParams) {
    const container = document.getElementById('recent-violations');
    if (!container) return;
    
    const queryString = new URLSearchParams(filterParams).toString();
    
    fetch(`/discipline/recent-violations?${queryString}`)
        .then(response => response.json())
        .then(data => {
            renderRecentViolations(data.violations || []);
        })
        .catch(error => {
            console.error('Error loading filtered violations:', error);
        });
}

function loadFilteredPendingActions(filterParams) {
    const container = document.getElementById('pending-actions');
    if (!container) return;
    
    const queryString = new URLSearchParams(filterParams).toString();
    
    fetch(`/discipline/pending-actions?${queryString}`)
        .then(response => response.json())
        .then(data => {
            renderPendingActions(data.actions || []);
        })
        .catch(error => {
            console.error('Error loading filtered pending actions:', error);
        });
}

function loadFilteredCriticalCases(filterParams) {
    const container = document.getElementById('critical-cases-table');
    if (!container) return;
    
    const queryString = new URLSearchParams(filterParams).toString();
    
    fetch(`/discipline/critical-cases?${queryString}`)
        .then(response => response.json())
        .then(data => {
            renderCriticalCases(data.cases || []);
        })
        .catch(error => {
            console.error('Error loading filtered critical cases:', error);
        });
}

// Load Violation Pie Chart (Minor vs Major)
function loadViolationPieChart() {
    const canvas = document.getElementById('violationPieChart');
    if (!canvas) return;

    const period = document.getElementById('pieChartPeriod')?.value || 'month';
    
    fetch(`/discipline/minor-major-violation-stats?period=${period}`)
        .then(response => response.json())
        .then(data => {
            const ctx = canvas.getContext('2d');
            
            if (window.violationPieChart && typeof window.violationPieChart.destroy === 'function') {
                window.violationPieChart.destroy();
            }

            window.violationPieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Minor Violations', 'Major Violations'],
                    datasets: [{
                        data: [data.minor || 0, data.major || 0],
                        backgroundColor: ['#28a745', '#198754'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Violation pie chart error:', error);
        });
}

// Load Violation Bar Chart (Monthly)
function loadViolationBarChart() {
    const canvas = document.getElementById('violationBarChart');
    if (!canvas) return;

    const period = document.getElementById('barChartPeriod')?.value || '12months';
    
    fetch(`/discipline/violation-bar-stats?period=${period}`)
        .then(response => response.json())
        .then(data => {
            const ctx = canvas.getContext('2d');
            
            if (window.violationBarChart && typeof window.violationBarChart.destroy === 'function') {
                window.violationBarChart.destroy();
            }

            window.violationBarChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: 'Minor Violations',
                        data: data.minor || [],
                        backgroundColor: '#28a745',
                        borderColor: '#28a745',
                        borderWidth: 1
                    }, {
                        label: 'Major Violations',
                        data: data.major || [],
                        backgroundColor: '#198754',
                        borderColor: '#198754',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Violation bar chart error:', error);
        });
}

// Load Case Status Chart
function loadCaseStatusChart() {
    const canvas = document.getElementById('caseStatusPieChart');
    if (!canvas) return;

    const period = document.getElementById('caseStatusPeriod')?.value || 'month';
    
    fetch(`/discipline/case-status-stats?period=${period}`)
        .then(response => response.json())
        .then(data => {
            const ctx = canvas.getContext('2d');
            
            if (window.caseStatusChart && typeof window.caseStatusChart.destroy === 'function') {
                window.caseStatusChart.destroy();
            }

            window.caseStatusChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Pending', 'On Going', 'Completed'],
                    datasets: [{
                        data: [data.pending || 0, data.ongoing || 0, data.completed || 0],
                        backgroundColor: ['#198754', '#28a745', '#20c997'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Case status chart error:', error);
        });
}

// Apply all filters
function applyAllFilters() {
    applyViolationsFilter();
    applyPendingFilter();
    applyCriticalCasesFilter();
    applyViolationTrendsFilter();
    // applyDisciplineEffectivenessFilter(); // Removed - endpoint not available
}

// Expose functions to global scope for onclick handlers
window.openQuickActionModal = openQuickActionModal;
window.recordNewViolation = recordNewViolation;
window.studentLookup = studentLookup;
window.closeModal = closeModal;
window.applyAllFilters = applyAllFilters;

// Chart rendering functions
window.renderViolationTrendsChart = renderViolationTrendsChart;
window.renderDisciplineEffectivenessChart = renderDisciplineEffectivenessChart;

// Individual chart filter functions
window.applyViolationsFilter = applyViolationsFilter;
window.applyPendingFilter = applyPendingFilter;
window.applyCriticalCasesFilter = applyCriticalCasesFilter;
window.applyViolationTrendsFilter = applyViolationTrendsFilter;
window.applyDisciplineEffectivenessFilter = applyDisciplineEffectivenessFilter;

// Chart loading functions
window.loadViolationPieChart = loadViolationPieChart;
window.loadViolationBarChart = loadViolationBarChart;
window.loadCaseStatusChart = loadCaseStatusChart;

// Data loading functions
window.loadAllDashboardData = loadAllDashboardData;
window.loadRecentViolations = loadRecentViolations;
window.loadPendingActions = loadPendingActions;
window.loadCriticalCases = loadCriticalCases;
window.loadViolationTrends = loadViolationTrends;
window.loadDisciplineEffectiveness = loadDisciplineEffectiveness;
