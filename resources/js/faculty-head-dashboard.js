/**
 * Faculty Head Dashboard JavaScript
 * Handles dashboard functionality and interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Faculty Head Dashboard loaded');
    
    // Add any dashboard-specific functionality here
    // For example: charts, statistics updates, etc.
});

// Function to view class list from assignment tables
function viewClassList(gradeLevel, section, strand, track) {
    // Build class title using consistent format
    let classTitle = `${gradeLevel} - ${section}`;
    if (strand && strand !== '') {
        classTitle = `${gradeLevel} - ${section} - ${strand}`;
        if (track && track !== '') {
            classTitle = `${gradeLevel} - ${section} - ${strand} - ${track}`;
        }
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('sectionDetailsModal'));
    modal.show();
    
    // Show loading state
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('classContent').style.display = 'none';
    
    // Update modal title
    document.getElementById('sectionDetailsModalLabel').innerHTML = `
        <i class="ri-group-line me-2"></i>Class List - ${classTitle}
    `;
    
    // Build query parameters
    let queryParams = `grade_level=${encodeURIComponent(gradeLevel)}&section=${encodeURIComponent(section)}`;
    if (strand && strand !== '') queryParams += `&strand=${encodeURIComponent(strand)}`;
    if (track && track !== '') queryParams += `&track=${encodeURIComponent(track)}`;
    
    // Fetch section details
    fetch(`/faculty-head/section-details?${queryParams}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide loading state
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('classContent').style.display = 'block';
                
                // Update class adviser info
                const adviserInfo = document.getElementById('adviserInfo');
                if (data.adviser) {
                    adviserInfo.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="ri-user-star-line me-2 text-primary"></i>
                            <div>
                                <div class="fw-medium">${data.adviser.name}</div>
                                <small class="text-muted">Assigned: ${data.adviser.assigned_date}</small>
                            </div>
                        </div>
                    `;
                } else {
                    adviserInfo.innerHTML = `
                        <div class="text-muted">
                            <i class="ri-user-line me-2"></i>
                            No adviser assigned
                        </div>
                    `;
                }
                
                // Update subject teachers
                const subjectTeachersContainer = document.getElementById('subjectTeachers');
                if (data.subject_teachers && data.subject_teachers.length > 0) {
                    subjectTeachersContainer.innerHTML = data.subject_teachers.map(teacher => `
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-medium">${teacher.subject_name}</div>
                                <small class="text-muted">${teacher.teacher_name}</small>
                            </div>
                        </div>
                    `).join('');
                } else {
                    subjectTeachersContainer.innerHTML = `
                        <div class="text-muted text-center py-3">
                            <i class="ri-book-line me-2"></i>
                            No subject teachers assigned
                        </div>
                    `;
                }
                
                // Update students list
                const studentsContainer = document.getElementById('studentsList');
                const emptyState = document.getElementById('emptyState');
                
                if (data.students && data.students.length > 0) {
                    studentsContainer.style.display = 'block';
                    emptyState.style.display = 'none';
                    
                    const studentsTableBody = document.getElementById('studentsTableBody');
                    studentsTableBody.innerHTML = data.students.map((student, index) => `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <div class="fw-medium">${student.first_name} ${student.middle_name || ''} ${student.last_name} ${student.suffix || ''}</div>
                                <small class="text-muted">${student.student_id}</small>
                            </td>
                            <td>${student.grade_level} - ${student.section}${student.strand ? ' - ' + student.strand : ''}${student.track ? ' - ' + student.track : ''}</td>
                            <td>
                                <span class="badge ${student.is_active ? 'bg-success' : 'bg-secondary'}">
                                    ${student.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    studentsContainer.style.display = 'none';
                    emptyState.style.display = 'block';
                }
            } else {
                // Show error state
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('classContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="ri-error-warning-line me-2"></i>
                        Error loading class details. Please try again.
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('classContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    Error loading class details. Please try again.
                </div>
            `;
        });
}

// Make function globally available
window.viewClassList = viewClassList;
