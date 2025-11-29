// Student Schedule JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Load schedule data
    loadScheduleData();
    
    // Load subjects list
    loadSubjectsList();
});

function loadScheduleData() {
    fetch('/student/schedule/data')
        .then(response => response.json())
        .then(data => {
            if (data.events) {
                populateScheduleTable(data.events);
            }
        })
        .catch(error => {
            showError('Failed to load schedule data');
        });
}

function populateScheduleTable(events) {
    const tableBody = document.getElementById('schedule-table-body');
    if (!tableBody) return;
    
    // Create time slots from 7:00 AM to 6:00 PM
    const timeSlots = [];
    for (let hour = 7; hour <= 18; hour++) {
        timeSlots.push(`${hour.toString().padStart(2, '0')}:00`);
    }
    
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    
    tableBody.innerHTML = '';
    
    timeSlots.forEach(time => {
        const row = document.createElement('tr');
        row.innerHTML = `<td class="fw-bold">${formatTime(time)}</td>`;
        
        days.forEach(day => {
            const cell = document.createElement('td');
            const event = events.find(e => e.day === day && e.start_time === time);
            
            if (event) {
                cell.innerHTML = `
                    <div class="schedule-item p-2 rounded" style="background-color: ${event.color}20; border-left: 3px solid ${event.color}">
                        <div class="fw-bold">${event.title}</div>
                        <small class="text-muted">${event.teacher}</small>
                        <br><small class="text-muted">${event.room}</small>
                    </div>
                `;
            }
            
            row.appendChild(cell);
        });
        
        tableBody.appendChild(row);
    });
}

function loadSubjectsList() {
    fetch('/student/schedule/data')
        .then(response => response.json())
        .then(data => {
            if (data.events) {
                populateSubjectsList(data.events);
            }
        })
        .catch(error => {
        });
}

function populateSubjectsList(events) {
    const subjectsList = document.getElementById('subjects-list');
    if (!subjectsList) return;
    
    const uniqueSubjects = events.reduce((acc, event) => {
        if (!acc.find(s => s.title === event.title)) {
            acc.push(event);
        }
        return acc;
    }, []);
    
    subjectsList.innerHTML = '';
    
    uniqueSubjects.forEach(subject => {
        const col = document.createElement('div');
        col.className = 'col-md-6 col-lg-4 mb-3';
        
        col.innerHTML = `
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title">${subject.title}</h6>
                    <p class="card-text">
                        <strong>Teacher:</strong> ${subject.teacher}<br>
                        <strong>Room:</strong> ${subject.room}<br>
                        <strong>Schedule:</strong> ${subject.day} ${subject.time_range}
                    </p>
                </div>
            </div>
        `;
        
        subjectsList.appendChild(col);
    });
}

function formatTime(time24) {
    const [hours, minutes] = time24.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${hour12}:${minutes} ${ampm}`;
}

function showError(message) {
    // Create and show error alert
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('main');
    if (container) {
        container.insertBefore(alert, container.firstChild);
    }
}
