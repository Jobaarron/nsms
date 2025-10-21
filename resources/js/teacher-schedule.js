// Teacher Schedule JavaScript - Global Namespace
window.TeacherSchedule = window.TeacherSchedule || {};

// Global functions to prevent console errors
window.initializeSchedule = function() {
    // Add hover effects to schedule cards
    const scheduleCards = document.querySelectorAll('.schedule-card');
    scheduleCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
        });
    });
    
    // Initialize calendar view if present
    if (document.getElementById('calendar-container')) {
        window.initializeCalendar();
    }
};

window.loadScheduleData = function() {
    // Load schedule data via AJAX if needed
    const scheduleDataUrl = '/teacher/schedule/data';
    
    fetch(scheduleDataUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            window.updateScheduleDisplay(data);
        })
        .catch(error => {
            console.log('Schedule data not available:', error);
            // Don't show error to user for optional data
        });
};

window.updateScheduleDisplay = function(scheduleData) {
    // Update schedule display with fresh data
    if (scheduleData && scheduleData.length > 0) {
        // Update schedule table or calendar
        const scheduleContainer = document.getElementById('schedule-container');
        if (scheduleContainer) {
            // Update with new data
            console.log('Schedule updated with', scheduleData.length, 'items');
        }
    }
};

window.setupEventHandlers = function() {
    // View students button handler
    const viewStudentsButtons = document.querySelectorAll('.view-students-btn');
    viewStudentsButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const classId = this.dataset.classId;
            if (classId) {
                window.showStudentsList(classId);
            }
        });
    });
    
    // Filter handlers
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterType = this.dataset.filter;
            window.applyScheduleFilter(filterType);
        });
    });
};

window.showStudentsList = function(classId) {
    // Show modal or navigate to students list
    const studentsUrl = `/teacher/schedule/students?class=${classId}`;
    window.location.href = studentsUrl;
};

window.applyScheduleFilter = function(filterType) {
    // Apply filter to schedule display
    const scheduleItems = document.querySelectorAll('.schedule-item');
    
    scheduleItems.forEach(item => {
        if (filterType === 'all' || item.classList.contains(filterType)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
    
    // Update active filter button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeBtn = document.querySelector(`[data-filter="${filterType}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
};

window.initializeCalendar = function() {
    // Initialize calendar view for weekly schedule
    const calendarContainer = document.getElementById('calendar-container');
    if (!calendarContainer) return;
    
    // Create weekly calendar grid
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    const timeSlots = ['7:00 AM', '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM'];
    
    let calendarHTML = '<div class="calendar-grid">';
    
    // Header row
    calendarHTML += '<div class="calendar-header">';
    calendarHTML += '<div class="time-header">Time</div>';
    days.forEach(day => {
        calendarHTML += `<div class="day-header">${day}</div>`;
    });
    calendarHTML += '</div>';
    
    // Time slots
    timeSlots.forEach(time => {
        calendarHTML += '<div class="calendar-row">';
        calendarHTML += `<div class="time-slot">${time}</div>`;
        days.forEach(day => {
            calendarHTML += `<div class="schedule-slot" data-day="${day}" data-time="${time}"></div>`;
        });
        calendarHTML += '</div>';
    });
    
    calendarHTML += '</div>';
    calendarContainer.innerHTML = calendarHTML;
};

// Add CSS for calendar and animations
const style = document.createElement('style');
style.textContent = `
    .schedule-card {
        transition: all 0.3s ease;
    }
    
    .calendar-grid {
        display: grid;
        grid-template-columns: 100px repeat(5, 1fr);
        gap: 1px;
        background-color: #e9ecef;
        border: 1px solid #dee2e6;
    }
    
    .calendar-header {
        display: contents;
    }
    
    .time-header, .day-header {
        background-color: #f8f9fa;
        padding: 10px;
        font-weight: 600;
        text-align: center;
        border: 1px solid #dee2e6;
    }
    
    .calendar-row {
        display: contents;
    }
    
    .time-slot {
        background-color: #f8f9fa;
        padding: 10px;
        font-size: 0.9em;
        text-align: center;
        border: 1px solid #dee2e6;
    }
    
    .schedule-slot {
        background-color: white;
        padding: 5px;
        min-height: 50px;
        border: 1px solid #dee2e6;
        position: relative;
    }
    
    .schedule-slot:hover {
        background-color: #f8f9fa;
    }
    
    .filter-btn.active {
        background-color: #0d6efd;
        color: white;
    }
`;
document.head.appendChild(style);

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Initialize schedule functionality
        if (typeof window.initializeSchedule === 'function') {
            window.initializeSchedule();
        }
        
        // Load schedule data
        if (typeof window.loadScheduleData === 'function') {
            window.loadScheduleData();
        }
        
        // Set up event handlers
        if (typeof window.setupEventHandlers === 'function') {
            window.setupEventHandlers();
        }
    } catch (error) {
        console.log('Teacher Schedule initialization error:', error);
    }
});
