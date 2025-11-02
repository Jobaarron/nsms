/**
 * Faculty Head - Assign Teacher JavaScript
 * CLEAN EXTERNAL VERSION - Loaded in HEAD
 */

console.log('=== EXTERNAL FACULTY HEAD SCRIPT LOADING ===');

function removeAssignment(assignmentId) {
    console.log('=== EXTERNAL removeAssignment called with ID:', assignmentId);
    
    if (!confirm('Are you sure you want to remove this assignment?')) {
        return false;
    }
    
    // Show loading state
    const button = event.target.closest('button');
    let originalText = '';
    if (button) {
        originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Removing...';
    }
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        alert('CSRF token not found. Please refresh the page.');
        if (button) {
            button.disabled = false;
            button.innerHTML = originalText;
        }
        return false;
    }
    
    // Make AJAX request
    fetch('/faculty-head/remove-assignment/' + assignmentId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Success: ' + data.message);
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to remove assignment');
        }
    })
    .catch(error => {
        console.error('Error removing assignment:', error);
        alert('Error: ' + error.message);
        
        // Restore button state
        if (button) {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    });
    
    return true;
}

// Make function globally available
window.removeAssignment = removeAssignment;

// Function to check section details
function showSectionDetails() {
    const gradeSelect = document.getElementById('grade_level');
    const sectionSelect = document.getElementById('section');
    const strandSelect = document.getElementById('strand');
    const trackSelect = document.getElementById('track');
    
    if (!gradeSelect || !sectionSelect || !gradeSelect.value || !sectionSelect.value) {
        alert('Please select both grade level and section first.');
        return;
    }
    
    const gradeLevel = gradeSelect.value;
    const sectionName = sectionSelect.value;
    const strand = strandSelect ? strandSelect.value : '';
    const track = trackSelect ? trackSelect.value : '';
    
    // Build class title
    let classTitle = `${gradeLevel}`;
    if (strand) {
        classTitle += ` ${strand}`;
        if (track) {
            classTitle += `-${track}`;
        }
    }
    classTitle += ` Section ${sectionName}`;
    
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
    let queryParams = `grade_level=${encodeURIComponent(gradeLevel)}&section=${encodeURIComponent(sectionName)}`;
    if (strand) queryParams += `&strand=${encodeURIComponent(strand)}`;
    if (track) queryParams += `&track=${encodeURIComponent(track)}`;
    
    // Fetch section details
    fetch(`/faculty-head/get-section-details?${queryParams}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading state
        document.getElementById('loadingState').style.display = 'none';
        
        if (data.success) {
            const details = data.details;
            
            // Show class content
            document.getElementById('classContent').style.display = 'block';
            
            // Update class title
            document.getElementById('classTitle').innerHTML = `
                <i class="ri-information-line me-2"></i>${classTitle}
                <span class="badge bg-primary ms-2" id="studentCount">${details.students.length} Students</span>
            `;
            
            // Update class adviser
            const classAdviserDiv = document.getElementById('classAdviser');
            if (details.adviser) {
                classAdviserDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="ri-user-star-line text-primary me-2"></i>
                        <div>
                            <strong>${details.adviser.name}</strong>
                            <br><small class="text-muted">Assigned: ${details.adviser.assigned_date}</small>
                        </div>
                    </div>
                `;
            } else {
                classAdviserDiv.innerHTML = `
                    <div class="text-muted">
                        <i class="ri-user-star-line me-2"></i>No adviser assigned
                    </div>
                `;
            }
            
            // Update subject teachers count
            const subjectTeachersCount = details.subject_teachers ? details.subject_teachers.length : 0;
            document.getElementById('subjectTeachersCount').innerHTML = `
                <i class="ri-book-open-line text-success me-2"></i>
                <strong>${subjectTeachersCount} Teachers</strong>
            `;
            
            // Update subject teachers list
            const subjectTeachersSection = document.getElementById('subjectTeachersSection');
            const subjectTeachersList = document.getElementById('subjectTeachersList');
            
            if (details.subject_teachers && details.subject_teachers.length > 0) {
                subjectTeachersSection.style.display = 'block';
                subjectTeachersList.innerHTML = details.subject_teachers.map(teacher => `
                    <div class="col-md-6 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="ri-book-2-line text-info me-2"></i>
                            <div>
                                <strong>${teacher.subject_name}</strong>
                                <br><small class="text-muted">${teacher.teacher_name}</small>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                subjectTeachersSection.style.display = 'none';
            }
            
            // Update student table
            const studentTableBody = document.getElementById('studentTableBody');
            const strandHeader = document.getElementById('strandHeader');
            const trackHeader = document.getElementById('trackHeader');
            
            // Show/hide strand and track headers
            if (strand) {
                strandHeader.style.display = 'table-cell';
            } else {
                strandHeader.style.display = 'none';
            }
            
            if (track) {
                trackHeader.style.display = 'table-cell';
            } else {
                trackHeader.style.display = 'none';
            }
            
            if (details.students.length > 0) {
                document.getElementById('emptyState').style.display = 'none';
                studentTableBody.innerHTML = details.students.map((student, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td><strong>${student.student_id}</strong></td>
                        <td>
                            <div>
                                <strong>${student.last_name}, ${student.first_name}</strong>
                                ${student.middle_name ? ` ${student.middle_name}` : ''}
                            </div>
                            ${student.suffix ? `<small class="text-muted">${student.suffix}</small>` : ''}
                        </td>
                        <td><span class="badge bg-primary">${student.grade_level}</span></td>
                        <td><span class="badge bg-secondary">${student.section}</span></td>
                        ${strand ? `<td><span class="badge bg-info">${student.strand || '-'}</span></td>` : ''}
                        ${track ? `<td><span class="badge bg-warning text-dark">${student.track || '-'}</span></td>` : ''}
                        <td>${student.contact_number || '<span class="text-muted">No contact</span>'}</td>
                        <td><span class="badge bg-${student.is_active ? 'success' : 'danger'}">${student.is_active ? 'Active' : 'Inactive'}</span></td>
                    </tr>
                `).join('');
            } else {
                studentTableBody.innerHTML = '';
                document.getElementById('emptyState').style.display = 'block';
            }
            
        } else {
            // Show error in loading state area
            document.getElementById('loadingState').innerHTML = `
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    Error loading class details: ${data.message || 'Unknown error'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading section details:', error);
        document.getElementById('loadingState').innerHTML = `
            <div class="alert alert-danger">
                <i class="ri-error-warning-line me-2"></i>
                Error loading class details. Please try again.
            </div>
        `;
    });
}

// Function to check section details for adviser assignment
function showSectionDetailsAdviser() {
    const gradeSelect = document.getElementById('grade_level_adviser');
    const sectionSelect = document.getElementById('section_adviser');
    const strandSelect = document.getElementById('strand_adviser');
    const trackSelect = document.getElementById('track_adviser');
    
    if (!gradeSelect || !sectionSelect || !gradeSelect.value || !sectionSelect.value) {
        alert('Please select both grade level and section first.');
        return;
    }
    
    const gradeLevel = gradeSelect.value;
    const sectionName = sectionSelect.value;
    const strand = strandSelect ? strandSelect.value : '';
    const track = trackSelect ? trackSelect.value : '';
    
    // Build class title
    let classTitle = `${gradeLevel}`;
    if (strand) {
        classTitle += ` ${strand}`;
        if (track) {
            classTitle += `-${track}`;
        }
    }
    classTitle += ` Section ${sectionName}`;
    
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
    let queryParams = `grade_level=${encodeURIComponent(gradeLevel)}&section=${encodeURIComponent(sectionName)}`;
    if (strand) queryParams += `&strand=${encodeURIComponent(strand)}`;
    if (track) queryParams += `&track=${encodeURIComponent(track)}`;
    
    // Fetch section details
    fetch(`/faculty-head/get-section-details?${queryParams}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading state
        document.getElementById('loadingState').style.display = 'none';
        
        if (data.success) {
            const details = data.details;
            
            // Show class content
            document.getElementById('classContent').style.display = 'block';
            
            // Update class title
            document.getElementById('classTitle').innerHTML = `
                <i class="ri-information-line me-2"></i>${classTitle}
                <span class="badge bg-primary ms-2" id="studentCount">${details.students.length} Students</span>
            `;
            
            // Update class adviser
            const classAdviserDiv = document.getElementById('classAdviser');
            if (details.adviser) {
                classAdviserDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="ri-user-star-line text-primary me-2"></i>
                        <div>
                            <strong>${details.adviser.name}</strong>
                            <br><small class="text-muted">Assigned: ${details.adviser.assigned_date}</small>
                        </div>
                    </div>
                `;
            } else {
                classAdviserDiv.innerHTML = `
                    <div class="text-muted">
                        <i class="ri-user-star-line me-2"></i>No adviser assigned
                    </div>
                `;
            }
            
            // Update subject teachers count
            const subjectTeachersCount = details.subject_teachers ? details.subject_teachers.length : 0;
            document.getElementById('subjectTeachersCount').innerHTML = `
                <i class="ri-book-open-line text-success me-2"></i>
                <strong>${subjectTeachersCount} Teachers</strong>
            `;
            
            // Update subject teachers list
            const subjectTeachersSection = document.getElementById('subjectTeachersSection');
            const subjectTeachersList = document.getElementById('subjectTeachersList');
            
            if (details.subject_teachers && details.subject_teachers.length > 0) {
                subjectTeachersSection.style.display = 'block';
                subjectTeachersList.innerHTML = details.subject_teachers.map(teacher => `
                    <div class="col-md-6 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="ri-book-2-line text-info me-2"></i>
                            <div>
                                <strong>${teacher.subject_name}</strong>
                                <br><small class="text-muted">${teacher.teacher_name}</small>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                subjectTeachersSection.style.display = 'none';
            }
            
            // Update student table
            const studentTableBody = document.getElementById('studentTableBody');
            const strandHeader = document.getElementById('strandHeader');
            const trackHeader = document.getElementById('trackHeader');
            
            // Show/hide strand and track headers
            if (strand) {
                strandHeader.style.display = 'table-cell';
            } else {
                strandHeader.style.display = 'none';
            }
            
            if (track) {
                trackHeader.style.display = 'table-cell';
            } else {
                trackHeader.style.display = 'none';
            }
            
            if (details.students.length > 0) {
                document.getElementById('emptyState').style.display = 'none';
                studentTableBody.innerHTML = details.students.map((student, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td><strong>${student.student_id}</strong></td>
                        <td>
                            <div>
                                <strong>${student.last_name}, ${student.first_name}</strong>
                                ${student.middle_name ? ` ${student.middle_name}` : ''}
                            </div>
                            ${student.suffix ? `<small class="text-muted">${student.suffix}</small>` : ''}
                        </td>
                        <td><span class="badge bg-primary">${student.grade_level}</span></td>
                        <td><span class="badge bg-secondary">${student.section}</span></td>
                        ${strand ? `<td><span class="badge bg-info">${student.strand || '-'}</span></td>` : ''}
                        ${track ? `<td><span class="badge bg-warning text-dark">${student.track || '-'}</span></td>` : ''}
                        <td>${student.contact_number || '<span class="text-muted">No contact</span>'}</td>
                        <td><span class="badge bg-${student.is_active ? 'success' : 'danger'}">${student.is_active ? 'Active' : 'Inactive'}</span></td>
                    </tr>
                `).join('');
            } else {
                studentTableBody.innerHTML = '';
                document.getElementById('emptyState').style.display = 'block';
            }
            
        } else {
            // Show error in loading state area
            document.getElementById('loadingState').innerHTML = `
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    Error loading class details: ${data.message || 'Unknown error'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading section details:', error);
        document.getElementById('loadingState').innerHTML = `
            <div class="alert alert-danger">
                <i class="ri-error-warning-line me-2"></i>
                Error loading class details. Please try again.
            </div>
        `;
    });
}

// Function to view class list from assignment tables
function viewClassList(gradeLevel, section, strand, track) {
    // Build class title
    let classTitle = `${gradeLevel}`;
    if (strand && strand !== '') {
        classTitle += ` ${strand}`;
        if (track && track !== '') {
            classTitle += `-${track}`;
        }
    }
    classTitle += ` Section ${section}`;
    
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
    fetch(`/faculty-head/get-section-details?${queryParams}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading state
        document.getElementById('loadingState').style.display = 'none';
        
        if (data.success) {
            const details = data.details;
            
            // Show class content
            document.getElementById('classContent').style.display = 'block';
            
            // Update class title
            document.getElementById('classTitle').innerHTML = `
                <i class="ri-information-line me-2"></i>${classTitle}
                <span class="badge bg-primary ms-2" id="studentCount">${details.students.length} Students</span>
            `;
            
            // Update class adviser
            const classAdviserDiv = document.getElementById('classAdviser');
            if (details.adviser) {
                classAdviserDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="ri-user-star-line text-primary me-2"></i>
                        <div>
                            <strong>${details.adviser.name}</strong>
                            <br><small class="text-muted">Assigned: ${details.adviser.assigned_date}</small>
                        </div>
                    </div>
                `;
            } else {
                classAdviserDiv.innerHTML = `
                    <div class="text-muted">
                        <i class="ri-user-star-line me-2"></i>No adviser assigned
                    </div>
                `;
            }
            
            // Update subject teachers count
            const subjectTeachersCount = details.subject_teachers ? details.subject_teachers.length : 0;
            document.getElementById('subjectTeachersCount').innerHTML = `
                <i class="ri-book-open-line text-success me-2"></i>
                <strong>${subjectTeachersCount} Teachers</strong>
            `;
            
            // Update subject teachers list
            const subjectTeachersSection = document.getElementById('subjectTeachersSection');
            const subjectTeachersList = document.getElementById('subjectTeachersList');
            
            if (details.subject_teachers && details.subject_teachers.length > 0) {
                subjectTeachersSection.style.display = 'block';
                subjectTeachersList.innerHTML = details.subject_teachers.map(teacher => `
                    <div class="col-md-6 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="ri-book-2-line text-info me-2"></i>
                            <div>
                                <strong>${teacher.subject_name}</strong>
                                <br><small class="text-muted">${teacher.teacher_name}</small>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                subjectTeachersSection.style.display = 'none';
            }
            
            // Update student table
            const studentTableBody = document.getElementById('studentTableBody');
            const strandHeader = document.getElementById('strandHeader');
            const trackHeader = document.getElementById('trackHeader');
            
            // Show/hide strand and track headers
            if (strand && strand !== '') {
                strandHeader.style.display = 'table-cell';
            } else {
                strandHeader.style.display = 'none';
            }
            
            if (track && track !== '') {
                trackHeader.style.display = 'table-cell';
            } else {
                trackHeader.style.display = 'none';
            }
            
            if (details.students.length > 0) {
                document.getElementById('emptyState').style.display = 'none';
                studentTableBody.innerHTML = details.students.map((student, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td><strong>${student.student_id}</strong></td>
                        <td>
                            <div>
                                <strong>${student.last_name}, ${student.first_name}</strong>
                                ${student.middle_name ? ` ${student.middle_name}` : ''}
                            </div>
                            ${student.suffix ? `<small class="text-muted">${student.suffix}</small>` : ''}
                        </td>
                        <td><span class="badge bg-primary">${student.grade_level}</span></td>
                        <td><span class="badge bg-secondary">${student.section}</span></td>
                        ${(strand && strand !== '') ? `<td><span class="badge bg-info">${student.strand || '-'}</span></td>` : ''}
                        ${(track && track !== '') ? `<td><span class="badge bg-warning text-dark">${student.track || '-'}</span></td>` : ''}
                        <td>${student.contact_number || '<span class="text-muted">No contact</span>'}</td>
                        <td><span class="badge bg-${student.is_active ? 'success' : 'danger'}">${student.is_active ? 'Active' : 'Inactive'}</span></td>
                    </tr>
                `).join('');
            } else {
                studentTableBody.innerHTML = '';
                document.getElementById('emptyState').style.display = 'block';
            }
            
        } else {
            // Show error in loading state area
            document.getElementById('loadingState').innerHTML = `
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    Error loading class details: ${data.message || 'Unknown error'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading section details:', error);
        document.getElementById('loadingState').innerHTML = `
            <div class="alert alert-danger">
                <i class="ri-error-warning-line me-2"></i>
                Error loading class details. Please try again.
            </div>
        `;
    });
}

// Make functions globally available
window.showSectionDetails = showSectionDetails;
window.showSectionDetailsAdviser = showSectionDetailsAdviser;
window.viewClassList = viewClassList;

// Handle grade level change to show/hide strand and track fields
document.addEventListener('DOMContentLoaded', function() {
    const gradeLevelSelect = document.querySelector('select[name="grade_level"]');
    const strandField = document.getElementById('strandField');
    const trackField = document.getElementById('trackField');
    const strandSelect = document.querySelector('select[name="strand"]');
    const trackSelect = document.querySelector('select[name="track"]');
    
    if (gradeLevelSelect) {
        gradeLevelSelect.addEventListener('change', function() {
            const gradeLevel = this.value;
            
            // Show strand field for Grade 11 and Grade 12
            if (gradeLevel === 'Grade 11' || gradeLevel === 'Grade 12') {
                strandField.style.display = 'block';
                strandSelect.required = true;
            } else {
                strandField.style.display = 'none';
                trackField.style.display = 'none';
                strandSelect.required = false;
                strandSelect.value = '';
                trackSelect.required = false;
                trackSelect.value = '';
            }
        });
    }
    
    if (strandSelect) {
        strandSelect.addEventListener('change', function() {
            const strand = this.value;
            
            // Show track field only for TVL strand
            if (strand === 'TVL') {
                trackField.style.display = 'block';
                trackSelect.required = true;
            } else {
                trackField.style.display = 'none';
                trackSelect.required = false;
                trackSelect.value = '';
            }
        });
    }
});

console.log('=== EXTERNAL FACULTY HEAD SCRIPT LOADED ===');
console.log('window.removeAssignment type:', typeof window.removeAssignment);

// Dynamic subject filtering for unified faculty assignment view
document.addEventListener('DOMContentLoaded', function() {
    const gradeSelect = document.getElementById('grade_level');
    const subjectSelect = document.getElementById('subject_id');
    
    // Store all subjects data (will be populated from backend)
    let allSubjects = [];
    
    // Function to populate subjects based on selected grade, strand, and track
    function populateSubjects(selectedGrade, selectedStrand = null, selectedTrack = null) {
        // Clear current options except the first one
        subjectSelect.innerHTML = '<option value="">Subject</option>';
        
        if (!selectedGrade) {
            return;
        }
        
        // Get current strand and track values if not provided
        if (selectedStrand === null) {
            const strandSelect = document.querySelector('select[name="strand"]');
            selectedStrand = strandSelect ? strandSelect.value : '';
        }
        if (selectedTrack === null) {
            const trackSelect = document.querySelector('select[name="track"]');
            selectedTrack = trackSelect ? trackSelect.value : '';
        }
        
        // Filter subjects for the selected grade
        let gradeSubjects = allSubjects.filter(subject => subject.grade_level === selectedGrade);
        
        // For Grade 11 and 12, filter by strand and track
        if ((selectedGrade === 'Grade 11' || selectedGrade === 'Grade 12') && selectedStrand) {
            gradeSubjects = gradeSubjects.filter(subject => {
                // Match strand
                if (subject.strand !== selectedStrand) {
                    return false;
                }
                
                // For TVL, also match track
                if (selectedStrand === 'TVL' && selectedTrack) {
                    return subject.track === selectedTrack;
                }
                
                // For non-TVL strands, include subjects without track or matching strand
                return selectedStrand !== 'TVL' || !subject.track;
            });
        }
        
        // Group subjects by name to avoid duplicates
        const uniqueSubjects = {};
        gradeSubjects.forEach(subject => {
            const key = subject.subject_name;
            if (!uniqueSubjects[key]) {
                uniqueSubjects[key] = [];
            }
            uniqueSubjects[key].push(subject);
        });
        
        // Add subjects to dropdown
        Object.keys(uniqueSubjects).sort().forEach(subjectName => {
            const subjects = uniqueSubjects[subjectName];
            
            // Use the first subject (they should be the same after filtering)
            const subject = subjects[0];
            const option = document.createElement('option');
            option.value = subject.id;
            option.textContent = subject.subject_name;
            subjectSelect.appendChild(option);
        });
        
        // Show visual feedback
        if (gradeSubjects.length > 0) {
            subjectSelect.style.borderColor = '#28a745';
            setTimeout(() => {
                subjectSelect.style.borderColor = '';
            }, 1000);
        }
    }
    
    // Load subjects data from backend
    function loadSubjects() {
        fetch('/faculty-head/get-subjects', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allSubjects = data.subjects || [];
            } else {
                console.error('Error loading subjects:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading subjects:', error);
        });
    }
    
    if (gradeSelect && subjectSelect) {
        // Load subjects on page load
        loadSubjects();
        
        // Handle grade level change
        gradeSelect.addEventListener('change', function() {
            const selectedGrade = this.value;
            populateSubjects(selectedGrade);
            populateSections(selectedGrade);
        });
        
        // Handle strand change to update subjects
        const strandSelect = document.querySelector('select[name="strand"]');
        if (strandSelect) {
            strandSelect.addEventListener('change', function() {
                const selectedGrade = gradeSelect.value;
                const selectedStrand = this.value;
                const trackSelect = document.querySelector('select[name="track"]');
                const selectedTrack = trackSelect ? trackSelect.value : '';
                
                populateSubjects(selectedGrade, selectedStrand, selectedTrack);
            });
        }
        
        // Handle track change to update subjects
        const trackSelect = document.querySelector('select[name="track"]');
        if (trackSelect) {
            trackSelect.addEventListener('change', function() {
                const selectedGrade = gradeSelect.value;
                const strandSelect = document.querySelector('select[name="strand"]');
                const selectedStrand = strandSelect ? strandSelect.value : '';
                const selectedTrack = this.value;
                
                populateSubjects(selectedGrade, selectedStrand, selectedTrack);
            });
        }
    }
    
    // Function to populate sections based on selected grade
    function populateSections(selectedGrade) {
        const sectionSelect = document.getElementById('section');
        const checkSectionBtn = document.getElementById('checkSectionBtn');
        if (!sectionSelect) return;
        
        // Clear current options except the first one
        sectionSelect.innerHTML = '<option value="">Section</option>';
        
        // Disable check button initially
        if (checkSectionBtn) {
            checkSectionBtn.disabled = true;
        }
        
        if (!selectedGrade) {
            return;
        }
        
        // Load sections for the selected grade
        fetch('/faculty-head/get-sections', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const sections = data.sections || [];
                // Filter sections for the selected grade
                const gradeSections = sections.filter(section => 
                    section.grade_level === selectedGrade
                );
                
                // Get unique section names
                const uniqueSections = [...new Set(gradeSections.map(s => s.section_name))];
                
                // Add sections to dropdown
                uniqueSections.sort().forEach(sectionName => {
                    const option = document.createElement('option');
                    option.value = sectionName;
                    option.textContent = sectionName;
                    sectionSelect.appendChild(option);
                });
                
                // Show visual feedback
                if (uniqueSections.length > 0) {
                    sectionSelect.style.borderColor = '#28a745';
                    setTimeout(() => {
                        sectionSelect.style.borderColor = '';
                    }, 1000);
                }
            }
        })
        .catch(error => {
            console.error('Error loading sections:', error);
            // Fallback to default sections
            ['A', 'B', 'C'].forEach(sectionName => {
                const option = document.createElement('option');
                option.value = sectionName;
                option.textContent = sectionName;
                sectionSelect.appendChild(option);
            });
        });
    }
    
    // Enable check section button when section is selected
    const sectionSelect = document.getElementById('section');
    const checkSectionBtn = document.getElementById('checkSectionBtn');
    
    if (sectionSelect && checkSectionBtn) {
        sectionSelect.addEventListener('change', function() {
            checkSectionBtn.disabled = !this.value;
        });
    }
    
    // Enable check section button for adviser when section is selected
    const sectionSelectAdviser = document.getElementById('section_adviser');
    const checkSectionBtnAdviser = document.getElementById('checkSectionBtnAdviser');
    
    if (sectionSelectAdviser && checkSectionBtnAdviser) {
        sectionSelectAdviser.addEventListener('change', function() {
            checkSectionBtnAdviser.disabled = !this.value;
        });
    }
    
    // Handle class adviser grade level filtering and strand/track fields
    const gradeSelectAdviser = document.getElementById('grade_level_adviser');
    const strandFieldAdviser = document.getElementById('strandFieldAdviser');
    const trackFieldAdviser = document.getElementById('trackFieldAdviser');
    const strandSelectAdviser = document.getElementById('strand_adviser');
    const trackSelectAdviser = document.getElementById('track_adviser');
    
    if (gradeSelectAdviser && sectionSelectAdviser) {
        // Store all sections data
        let allSections = [];
        
        // Load sections data from backend
        function loadSections() {
            fetch('/faculty-head/get-sections', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allSections = data.sections || [];
                } else {
                    console.error('Error loading sections:', data.message);
                    // Fallback to default sections
                    allSections = [
                        {section_name: 'A'}, {section_name: 'B'}, {section_name: 'C'},
                        {section_name: 'D'}, {section_name: 'E'}, {section_name: 'F'}
                    ];
                }
            })
            .catch(error => {
                console.error('Error loading sections:', error);
                // Fallback to default sections
                allSections = [
                    {section_name: 'A'}, {section_name: 'B'}, {section_name: 'C'},
                    {section_name: 'D'}, {section_name: 'E'}, {section_name: 'F'}
                ];
            });
        }
        
        // Load sections on page load
        loadSections();
        
        gradeSelectAdviser.addEventListener('change', function() {
            const selectedGrade = this.value;
            
            // Show/hide strand and track fields for Grade 11 and Grade 12
            if (selectedGrade === 'Grade 11' || selectedGrade === 'Grade 12') {
                strandFieldAdviser.style.display = 'block';
                strandSelectAdviser.required = true;
            } else {
                strandFieldAdviser.style.display = 'none';
                trackFieldAdviser.style.display = 'none';
                strandSelectAdviser.required = false;
                strandSelectAdviser.value = '';
                trackSelectAdviser.required = false;
                trackSelectAdviser.value = '';
            }
            
            // Clear section options except the first one
            sectionSelectAdviser.innerHTML = '<option value="">Section</option>';
            
            // Disable check button when sections are cleared
            if (checkSectionBtnAdviser) {
                checkSectionBtnAdviser.disabled = true;
            }
            
            if (selectedGrade) {
                // Filter sections for the selected grade or use all available sections
                const gradeSections = allSections.filter(section => 
                    !section.grade_level || section.grade_level === selectedGrade
                );
                
                // If no specific sections found, use default sections
                const sectionsToShow = gradeSections.length > 0 ? gradeSections : 
                    [{section_name: 'A'}, {section_name: 'B'}, {section_name: 'C'}];
                
                sectionsToShow.forEach(section => {
                    const option = document.createElement('option');
                    option.value = section.section_name;
                    option.textContent = section.section_name;
                    sectionSelectAdviser.appendChild(option);
                });
                
                // Show visual feedback
                sectionSelectAdviser.style.borderColor = '#28a745';
                setTimeout(() => {
                    sectionSelectAdviser.style.borderColor = '';
                }, 1000);
            }
        });
        
        // Handle strand change for adviser form
        if (strandSelectAdviser) {
            strandSelectAdviser.addEventListener('change', function() {
                const strand = this.value;
                
                // Show track field only for TVL strand
                if (strand === 'TVL') {
                    trackFieldAdviser.style.display = 'block';
                    trackSelectAdviser.required = true;
                } else {
                    trackFieldAdviser.style.display = 'none';
                    trackSelectAdviser.required = false;
                    trackSelectAdviser.value = '';
                }
            });
        }
    }
});
