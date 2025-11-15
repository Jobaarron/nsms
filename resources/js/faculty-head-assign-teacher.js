
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
    // Build class title with strand/track first, then section for SHS
    let classTitle;
    if (strand && strand !== '') {
        if (track && track !== '') {
            // For TVL with track: "Grade 11 TVL-ICT Section A"
            classTitle = `${gradeLevel} ${strand}-${track} Section ${section}`;
        } else {
            // For non-TVL strands: "Grade 11 STEM Section A"
            classTitle = `${gradeLevel} ${strand} Section ${section}`;
        }
    } else {
        // For Elementary/JHS: "Grade 7 Section A"
        classTitle = `${gradeLevel} Section ${section}`;
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
    if (strand && strand !== '' && strand !== 'null') queryParams += `&strand=${encodeURIComponent(strand)}`;
    if (track && track !== '' && track !== 'null') queryParams += `&track=${encodeURIComponent(track)}`;
    
    
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

// Function to check section details from form fields
function checkSectionFromForm() {
    // Get values from form fields
    const gradeLevel = document.getElementById('grade_level')?.value;
    const section = document.getElementById('section')?.value;
    const strand = document.querySelector('select[name="strand"]')?.value || '';
    const track = document.querySelector('select[name="track"]')?.value || '';
    
    // Validate required fields
    if (!gradeLevel || !section) {
        alert('Please select both Grade Level and Section first.');
        return;
    }
    
    // Call the working viewClassList function with form values
    viewClassList(gradeLevel, section, strand, track);
}

// Function to check section details from adviser form fields
function checkSectionFromAdviserForm() {
    // Get values from adviser form fields
    const gradeLevel = document.getElementById('grade_level_adviser')?.value;
    const section = document.getElementById('section_adviser')?.value;
    const strand = document.getElementById('strand_adviser')?.value || '';
    const track = document.getElementById('track_adviser')?.value || '';
    
    // Validate required fields
    if (!gradeLevel || !section) {
        alert('Please select both Grade Level and Section first.');
        return;
    }
    
    // Call the working viewClassList function with form values
    viewClassList(gradeLevel, section, strand, track);
}

// Make functions globally available
window.checkSectionFromForm = checkSectionFromForm;
window.checkSectionFromAdviserForm = checkSectionFromAdviserForm;

// ============================================
// NEW ACCORDION STRUCTURE FUNCTIONS
// ============================================

// Show strand sections for Senior High (Grade 11/12)
function showStrandSections(grade, strand) {
    const container = document.getElementById(`sections${grade.replace(' ', '')}`);
    
    // Get sections for this grade and strand from facultyData
    const sections = window.facultyData.sections.filter(s => 
        s.grade_level === grade && s.section_name
    );
    
    // Check if TVL strand (needs track selection)
    if (strand === 'TVL') {
        showTVLTracks(grade, strand, container);
    } else {
        showSectionsForStrand(grade, strand, null, container, sections);
    }
}

// Show TVL tracks
function showTVLTracks(grade, strand, container) {
    const tracks = ['ICT', 'H.E'];
    const trackNames = {
        'ICT': 'Information and Communications Technology',
        'H.E': 'Home Economics'
    };
    
    let html = `
        <div class="p-3">
            <div class="d-flex align-items-center mb-3">
                <button class="btn btn-sm btn-light me-3" onclick="loadSeniorHighStrandsForAssignment('${grade}', document.getElementById('sections${grade.replace(' ', '')}'), document.getElementById('badge${grade.replace(' ', '')}'))" title="Back to strands">
                    <i class="ri-arrow-left-line"></i>
                </button>
                <h6 class="text-primary mb-0"><i class="ri-settings-line me-2"></i>Select Track for ${grade} - ${strand}</h6>
            </div>
            <div class="row g-3">
    `;
    
    tracks.forEach(track => {
        html += `
            <div class="col-md-6">
                <div class="track-card border rounded p-3 text-center" style="cursor: pointer;" onclick="showSectionsForStrand('${grade}', '${strand}', '${track}', document.getElementById('sections${grade.replace(' ', '')}'))">
                    <div class="track-badge bg-success text-white rounded px-3 py-2 mb-2">
                        ${track}
                    </div>
                    <h6 class="mb-1">${trackNames[track]}</h6>
                    <small class="text-muted">Click to view sections</small>
                </div>
            </div>
        `;
    });
    
    html += `</div></div>`;
    container.innerHTML = html;
}

// Show sections for a strand/track
function showSectionsForStrand(grade, strand, track, container, allSections = null) {
    // Get sections for this strand/track from window.facultyData
    const sections = allSections || window.facultyData.sections.filter(s => s.grade_level === grade);
    
    // Determine correct back function based on strand/track
    let backFunction;
    if (strand === 'TVL' && track) {
        // Back to track selection for TVL
        backFunction = `showTVLTracks('${grade}', '${strand}', document.getElementById('sections${grade.replace(' ', '')}'))`;
    } else if (strand) {
        // Back to strand selection for non-TVL strands
        backFunction = `loadSeniorHighStrandsForAssignment('${grade}', document.getElementById('sections${grade.replace(' ', '')}'), document.getElementById('badge${grade.replace(' ', '')}'))`;
    } else {
        // Back to grade level for elementary/junior high
        backFunction = `loadGradeSectionsForAssignment('${grade}')`;
    }
    
    let html = `
        <div class="p-3">
            <div class="d-flex align-items-center mb-3">
                <button class="btn btn-sm btn-light me-3" onclick="${backFunction}" title="Back">
                    <i class="ri-arrow-left-line"></i>
                </button>
                <h6 class="text-primary mb-0"><i class="ri-grid-line me-2"></i>Sections for ${grade} - ${strand}${track ? ' - ' + track : ''}</h6>
            </div>
    `;
    
    if (sections.length === 0) {
        html += `
            <div class="alert alert-info">
                <i class="ri-information-line me-2"></i>
                No sections available for ${grade} - ${strand}${track ? ' - ' + track : ''}. Please create sections first.
            </div>
        `;
    } else {
        sections.forEach(section => {
            const sectionId = `${grade}_${section.section_name}_${strand}_${track || ''}`.replace(/[^a-zA-Z0-9]/g, '_');
            html += `
                <div class="section-item border-bottom" style="cursor: pointer;" onclick="showSectionDetailsAccordion('${sectionId}', '${grade}', '${section.section_name}', '${strand}', '${track || ''}')">
                    <div class="d-flex justify-content-between align-items-center p-3 hover-bg-light">
                        <div class="d-flex align-items-center">
                            <div class="section-badge bg-success text-white rounded px-3 py-2 me-3">
                                Section ${section.section_name}
                            </div>
                            <div>
                                <h6 class="mb-0">Section ${section.section_name}</h6>
                                <small class="text-muted">Click to manage</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="details${sectionId}" class="section-details" style="display: none;"></div>
            `;
        });
    }
    
    html += `</div>`;
    container.innerHTML = html;
}

// Show section details in accordion format (Assign Adviser + Subjects + Students nested accordions)
function showSectionDetailsAccordion(sectionId, grade, section, strand, track) {
    const container = document.getElementById(`details${sectionId}`);
    
    // Hide all other section details
    document.querySelectorAll('.section-details').forEach(el => el.style.display = 'none');
    
    // Show this section's details
    container.style.display = 'block';
    
    // Get data for this section
    const adviser = window.facultyData.advisers.find(a => {
        // Match grade level and section (required)
        if (a.grade_level !== grade || a.section !== section) {
            return false;
        }
        
        // Handle strand matching (null values should match empty/null strand)
        const adviserStrand = a.strand || null;
        const filterStrand = strand || null;
        if (adviserStrand !== filterStrand) {
            return false;
        }
        
        // Handle track matching (null values should match empty/null track)
        const adviserTrack = a.track || null;
        const filterTrack = track || null;
        if (adviserTrack !== filterTrack) {
            return false;
        }
        
        return true;
    });
    
    const subjects = window.facultyData.subjects.filter(s => {
        let match = s.grade_level === grade;
        match = match && (s.strand === strand || s.strand === null);
        match = match && (s.track === track || s.track === null);
        return match;
    });
    
    const assignments = window.facultyData.assignments.filter(a => {
        // Match grade level and section (required)
        if (a.grade_level !== grade || a.section !== section) {
            return false;
        }
        
        // Handle strand matching (null values should match empty/null strand)
        const assignmentStrand = a.strand || null;
        const filterStrand = strand || null;
        if (assignmentStrand !== filterStrand) {
            return false;
        }
        
        // Handle track matching (null values should match empty/null track)
        const assignmentTrack = a.track || null;
        const filterTrack = track || null;
        if (assignmentTrack !== filterTrack) {
            return false;
        }
        
        return true;
    });
    
    // Debug logging to help troubleshoot
    console.log('=== SECTION DETAILS DEBUG ===');
    console.log('Grade:', grade, 'Section:', section, 'Strand:', strand, 'Track:', track);
    console.log('Found adviser:', adviser);
    console.log('Found subjects:', subjects.length);
    console.log('Found assignments:', assignments.length);
    console.log('All assignments data:', window.facultyData.assignments);
    console.log('Filtered assignments:', assignments);
    
    // Determine correct back function based on strand/track
    let backFunction;
    if (strand === 'TVL' && track) {
        // Back to sections for TVL track
        backFunction = `showSectionsForStrand('${grade}', '${strand}', '${track}', document.getElementById('sections${grade.replace(' ', '')}'))`;
    } else if (strand) {
        // Back to sections for non-TVL strands
        backFunction = `showSectionsForStrand('${grade}', '${strand}', null, document.getElementById('sections${grade.replace(' ', '')}'))`;
    } else {
        // Back to sections for elementary/junior high
        backFunction = `loadElementaryJuniorSectionsForAssignment('${grade}', document.getElementById('sections${grade.replace(' ', '')}'), document.getElementById('badge${grade.replace(' ', '')}'))`;
    }
    
    // Build HTML with nested accordions
    let html = `
        <div class="p-4 bg-light border-top">
            <button class="btn btn-sm btn-light mb-3" onclick="${backFunction}">
                <i class="ri-arrow-left-line me-1"></i> Back
            </button>
            
            <!-- ASSIGN ADVISER SECTION -->
            <div class="mb-4">
                ${adviser ? `
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <i class="ri-user-star-line me-2"></i>
                                <strong>Current Class Adviser:</strong> ${adviser.teacher.user.name}
                                <span class="text-muted ms-2">(Assigned: ${new Date(adviser.assigned_date).toLocaleDateString()})</span>
                            </div>
                            <button class="btn btn-sm btn-warning" onclick="showReplaceAdviserForm('${sectionId}', '${grade}', '${section}', '${strand}', '${track}', ${adviser.id})" title="Replace Adviser">
                                <i class="ri-user-settings-line me-1"></i> Replace
                            </button>
                        </div>
                        <div id="replaceAdviserForm${sectionId}" style="display: none;">
                            <hr>
                            <h6 class="mb-3"><i class="ri-user-settings-line me-2"></i>Replace Class Adviser</h6>
                            <form onsubmit="submitAdviserAssignment(event, '${sectionId}', '${grade}', '${section}', '${strand}', '${track}', ${adviser.id})" class="row g-3">
                                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')}">
                                <input type="hidden" name="grade_level" value="${grade}">
                                <input type="hidden" name="section" value="${section}">
                                <input type="hidden" name="strand" value="${strand}">
                                <input type="hidden" name="track" value="${track}">
                                <input type="hidden" name="replace_assignment_id" value="${adviser.id}">
                                <div class="col-md-6">
                                    <label class="form-label">New Teacher</label>
                                    <select name="teacher_id" class="form-select" required>
                                        <option value="">Select New Teacher</option>
                                        ${window.facultyData.teachers.map(t => `
                                            <option value="${t.teacher?.id || ''}" ${t.teacher?.id === adviser.teacher_id ? 'disabled' : ''}>${t.name}${t.teacher?.id === adviser.teacher_id ? ' (Current)' : ''}</option>
                                        `).join('')}
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Effective Date</label>
                                    <input type="date" name="effective_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="ri-user-settings-line me-1"></i>Replace
                                    </button>
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="hideReplaceAdviserForm('${sectionId}')">
                                        <i class="ri-close-line me-1"></i>Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                ` : `
                    <div class="card bg-white">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="ri-user-add-line me-2"></i>Assign Class Adviser</h6>
                            <form onsubmit="submitAdviserAssignment(event, '${sectionId}', '${grade}', '${section}', '${strand}', '${track}', null)" class="row g-3">
                                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')}">
                                <input type="hidden" name="grade_level" value="${grade}">
                                <input type="hidden" name="section" value="${section}">
                                <input type="hidden" name="strand" value="${strand}">
                                <input type="hidden" name="track" value="${track}">
                                <div class="col-md-6">
                                    <label class="form-label">Teacher</label>
                                    <select name="teacher_id" class="form-select" required>
                                        <option value="">Select Teacher</option>
                                        ${window.facultyData.teachers.map(t => `
                                            <option value="${t.teacher?.id || ''}">${t.name}</option>
                                        `).join('')}
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Effective Date</label>
                                    <input type="date" name="effective_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-user-add-line me-1"></i>Assign
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                `}
            </div>

            <!-- NESTED ACCORDION FOR SUBJECTS AND STUDENTS -->
            <div class="accordion" id="nested${sectionId}">
                
                <!-- SUBJECTS ACCORDION -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#subjects${sectionId}">
                            <i class="ri-book-open-line me-2"></i>Subject
                            <span class="badge bg-info ms-2">${subjects.length}</span>
                        </button>
                    </h2>
                    <div id="subjects${sectionId}" class="accordion-collapse collapse" data-bs-parent="#nested${sectionId}">
                        <div class="accordion-body">
                            ${subjects.map(subject => {
                                const assignment = assignments.find(a => a.subject_id === subject.id);
                                return `
                                    <div class="mb-3 p-3 border rounded bg-white">
                                        ${assignment ? `
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <strong>${subject.subject_name}</strong>
                                                    ${subject.subject_code ? `<span class="text-muted ms-2">(${subject.subject_code})</span>` : ''}
                                                    <div class="text-success small mt-1"><i class="ri-user-line"></i> Current: ${assignment.teacher.user.name}</div>
                                                </div>
                                                <button class="btn btn-sm btn-warning" onclick="showReplaceTeacherForm('${sectionId}', ${subject.id}, ${assignment.id})" title="Replace Teacher">
                                                    <i class="ri-user-settings-line me-1"></i> Replace
                                                </button>
                                            </div>
                                            <div id="replaceTeacherForm${sectionId}_${subject.id}" style="display: none;">
                                                <hr>
                                                <h6 class="mb-3"><i class="ri-user-settings-line me-2"></i>Replace Teacher for ${subject.subject_name}</h6>
                                                <form onsubmit="submitTeacherAssignment(event, '${sectionId}', '${grade}', '${section}', '${strand}', '${track}', ${subject.id}, ${assignment.id})" class="row g-3">
                                                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')}">
                                                    <input type="hidden" name="grade_level" value="${grade}">
                                                    <input type="hidden" name="section" value="${section}">
                                                    <input type="hidden" name="strand" value="${strand}">
                                                    <input type="hidden" name="track" value="${track}">
                                                    <input type="hidden" name="subject_id" value="${subject.id}">
                                                    <input type="hidden" name="replace_assignment_id" value="${assignment.id}">
                                                    <div class="col-md-6">
                                                        <label class="form-label">New Teacher</label>
                                                        <select name="teacher_id" class="form-select" required>
                                                            <option value="">Select New Teacher</option>
                                                            ${window.facultyData.teachers.map(t => `
                                                                <option value="${t.teacher?.id || ''}" ${t.teacher?.id === assignment.teacher_id ? 'disabled' : ''}>${t.name}${t.teacher?.id === assignment.teacher_id ? ' (Current)' : ''}</option>
                                                            `).join('')}
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Effective Date</label>
                                                        <input type="date" name="effective_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">&nbsp;</label>
                                                        <button type="submit" class="btn btn-success w-100">
                                                            <i class="ri-user-settings-line me-1"></i>Replace
                                                        </button>
                                                    </div>
                                                    <div class="col-12">
                                                        <button type="button" class="btn btn-sm btn-secondary" onclick="hideReplaceTeacherForm('${sectionId}', ${subject.id})">
                                                            <i class="ri-close-line me-1"></i>Cancel
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        ` : `
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>${subject.subject_name}</strong>
                                                    ${subject.subject_code ? `<span class="text-muted ms-2">(${subject.subject_code})</span>` : ''}
                                                </div>
                                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#assignModal${sectionId}_${subject.id}">
                                                    <i class="ri-user-add-line"></i> Assign Teacher
                                                </button>
                                            </div>
                                        `}
                                    </div>
                                    ${!assignment ? `
                                        <div class="modal fade" id="assignModal${sectionId}_${subject.id}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Assign Teacher</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form onsubmit="submitTeacherAssignment(event, '${sectionId}', '${grade}', '${section}', '${strand}', '${track}', ${subject.id}, null)">
                                                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')}">
                                                        <input type="hidden" name="grade_level" value="${grade}">
                                                        <input type="hidden" name="section" value="${section}">
                                                        <input type="hidden" name="strand" value="${strand}">
                                                        <input type="hidden" name="track" value="${track}">
                                                        <input type="hidden" name="subject_id" value="${subject.id}">
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Subject</label>
                                                                <input type="text" class="form-control" value="${subject.subject_name}" readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Teacher</label>
                                                                <select name="teacher_id" class="form-select" required>
                                                                    <option value="">Select Teacher</option>
                                                                    ${window.facultyData.teachers.map(t => `
                                                                        <option value="${t.teacher?.id || ''}">${t.name}</option>
                                                                    `).join('')}
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Effective Date</label>
                                                                <input type="date" name="effective_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Assign</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    ` : ''}
                                `;
                            }).join('')}
                            ${subjects.length === 0 ? '<p class="text-muted text-center">No subjects for this class.</p>' : ''}
                        </div>
                    </div>
                </div>

                <!-- STUDENTS ACCORDION -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#students${sectionId}" onclick="loadStudentsForSection('${sectionId}', '${grade}', '${section}', '${strand}', '${track}')">
                            <i class="ri-group-line me-2"></i>Student
                            <span class="badge bg-primary ms-2" id="badge${sectionId}">View List</span>
                        </button>
                    </h2>
                    <div id="students${sectionId}" class="accordion-collapse collapse" data-bs-parent="#nested${sectionId}">
                        <div class="accordion-body">
                            <div id="studentsList${sectionId}">
                                <div class="text-center py-3">
                                    <div class="spinner-border" role="status"></div>
                                    <p class="mt-2">Loading...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

// Load students for a section
let loadedSections = {};
function loadStudentsForSection(sectionId, grade, section, strand, track) {
    if (loadedSections[sectionId]) return;
    
    const container = document.getElementById(`studentsList${sectionId}`);
    const badge = document.getElementById(`badge${sectionId}`);
    
    // Build query parameters
    let queryParams = `grade_level=${encodeURIComponent(grade)}&section=${encodeURIComponent(section)}`;
    if (strand && strand !== '') queryParams += `&strand=${encodeURIComponent(strand)}`;
    if (track && track !== '') queryParams += `&track=${encodeURIComponent(track)}`;
    
    console.log('Loading students for:', { grade, section, strand, track });
    
    fetch(`/faculty-head/get-section-details?${queryParams}`)
        .then(r => {
            if (!r.ok) {
                throw new Error(`HTTP error! status: ${r.status}`);
            }
            return r.json();
        })
        .then(data => {
            console.log('Student data received:', data);
            
            if (data.success && data.details && data.details.students) {
                const students = data.details.students;
                
                if (students.length > 0) {
                    badge.textContent = students.length;
                    badge.className = 'badge bg-primary ms-2';
                    
                    let html = '<div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>#</th><th>Student ID</th><th>Name</th><th>Contact</th></tr></thead><tbody>';
                    students.forEach((s, i) => {
                        html += `<tr>
                            <td>${i+1}</td>
                            <td><strong>${s.student_id || 'N/A'}</strong></td>
                            <td>${s.first_name} ${s.last_name}</td>
                            <td>${s.contact_number || 'N/A'}</td>
                        </tr>`;
                    });
                    html += '</tbody></table></div>';
                    container.innerHTML = html;
                } else {
                    badge.textContent = '0';
                    badge.className = 'badge bg-secondary ms-2';
                    container.innerHTML = '<div class="text-center py-4"><i class="ri-user-line fs-1 text-muted"></i><p class="text-muted mt-2">No students enrolled in this section.</p></div>';
                }
            } else {
                badge.textContent = '0';
                badge.className = 'badge bg-secondary ms-2';
                container.innerHTML = '<div class="text-center py-4"><i class="ri-user-line fs-1 text-muted"></i><p class="text-muted mt-2">No students enrolled in this section.</p></div>';
            }
            
            loadedSections[sectionId] = true;
        })
        .catch(error => {
            console.error('Error loading students:', error);
            badge.textContent = 'Error';
            badge.className = 'badge bg-danger ms-2';
            container.innerHTML = `<div class="alert alert-danger"><i class="ri-error-warning-line me-2"></i>Error loading students: ${error.message}</div>`;
        });
}

// Load sections for assignment (handles both regular grades and SHS with strands)
let loadedGradesForAssignment = {};
function loadGradeSectionsForAssignment(grade) {
    const sectionsContainer = document.getElementById(`sections${grade.replace(' ', '')}`);
    const badge = document.getElementById(`badge${grade.replace(' ', '')}`);
    
    // Reset loaded state to allow refresh/back navigation
    loadedGradesForAssignment[grade] = false;
    
    // For Grade 11 & 12, show strands first
    if (grade === 'Grade 11' || grade === 'Grade 12') {
        loadSeniorHighStrandsForAssignment(grade, sectionsContainer, badge);
    } else {
        // For other grades, show sections directly
        loadElementaryJuniorSectionsForAssignment(grade, sectionsContainer, badge);
    }
    
    loadedGradesForAssignment[grade] = true;
}

// Load strands for Senior High
function loadSeniorHighStrandsForAssignment(grade, container, badge) {
    const strands = ['STEM', 'ABM', 'HUMSS', 'TVL'];
    const strandNames = {
        'STEM': 'Science, Technology, Engineering, and Mathematics',
        'ABM': 'Accountancy, Business, and Management',
        'HUMSS': 'Humanities and Social Sciences',
        'TVL': 'Technical-Vocational-Livelihood'
    };
    
    let html = `
        <div class="p-3">
            <div class="d-flex align-items-center mb-3">
                <button class="btn btn-sm btn-light me-3" onclick="resetAccordionToInitialState('${grade}')" title="Back to grade levels">
                    <i class="ri-arrow-left-line"></i>
                </button>
                <h6 class="text-primary mb-0">
                    <i class="ri-book-line me-2"></i>Select Strand for ${grade}
                </h6>
            </div>
            <div class="row g-3">
    `;
    
    strands.forEach(strand => {
        html += `
            <div class="col-md-6">
                <div class="strand-card border rounded-3 p-4 text-center" style="cursor: pointer; transition: all 0.3s;" 
                     onclick="showStrandSections('${grade}', '${strand}')"
                     onmouseover="this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)'; this.style.transform='translateY(-2px)'"
                     onmouseout="this.style.boxShadow=''; this.style.transform=''">
                    <div class="strand-badge bg-success text-white rounded px-4 py-3 mb-3 fw-bold fs-5">
                        ${strand}
                    </div>
                    <h6 class="mb-2">${strandNames[strand]}</h6>
                    <small class="text-muted">Click to view sections</small>
                </div>
            </div>
        `;
    });
    
    html += `</div></div>`;
    container.innerHTML = html;
    badge.textContent = 'Select Strand';
    badge.className = 'badge bg-info rounded-pill ms-auto me-3';
}

// Load sections for Elementary/Junior High
function loadElementaryJuniorSectionsForAssignment(grade, container, badge) {
    // Get sections from window.facultyData
    const sections = window.facultyData.sections.filter(s => s.grade_level === grade);
    
    let html = '<div class="p-3">';
    
    sections.forEach(section => {
        const sectionId = `${grade}_${section.section_name}`.replace(/[^a-zA-Z0-9]/g, '_');
        html += `
            <div class="section-item border-bottom" style="cursor: pointer;" onclick="showSectionDetailsAccordion('${sectionId}', '${grade}', '${section.section_name}', '', '')">
                <div class="d-flex justify-content-between align-items-center p-3 hover-bg-light">
                    <div class="d-flex align-items-center">
                        <div class="section-badge bg-success text-white rounded px-3 py-2 me-3">
                            Section ${section.section_name}
                        </div>
                        <div>
                            <h6 class="mb-0">Section ${section.section_name}</h6>
                            <small class="text-muted">Click to manage</small>
                        </div>
                    </div>
                </div>
            </div>
            <div id="details${sectionId}" class="section-details" style="display: none;"></div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
    badge.textContent = `${sections.length} sections`;
    badge.className = 'badge bg-primary rounded-pill ms-auto me-3';
}

// Reset accordion to initial state (collapse and show loading)
function resetAccordionToInitialState(grade) {
    const sectionsContainer = document.getElementById(`sections${grade.replace(' ', '')}`);
    const badge = document.getElementById(`badge${grade.replace(' ', '')}`);
    
    // Reset to loading state
    sectionsContainer.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading sections...</span>
            </div>
            <p class="text-muted mt-2">Loading sections for ${grade}...</p>
        </div>
    `;
    
    // Reset badge
    const sectionCount = window.facultyData?.sections?.filter(s => s.grade_level === grade)?.length || 0;
    badge.textContent = `${sectionCount} sections`;
    badge.className = 'badge bg-secondary rounded-pill ms-auto me-3';
    
    // Reset loaded state
    loadedGradesForAssignment[grade] = false;
    
    // Collapse the accordion
    const accordionButton = document.querySelector(`[data-bs-target="#collapse${grade.replace(' ', '')}"]`);
    const accordionCollapse = document.getElementById(`collapse${grade.replace(' ', '')}`);
    
    if (accordionButton && accordionCollapse) {
        // Use Bootstrap's collapse API to hide the accordion
        const bsCollapse = new bootstrap.Collapse(accordionCollapse, {
            toggle: false
        });
        bsCollapse.hide();
        
        // Update button state
        accordionButton.classList.add('collapsed');
        accordionButton.setAttribute('aria-expanded', 'false');
    }
}

// Make functions globally available
// AJAX submission function for adviser assignments
function submitAdviserAssignment(event, sectionId, grade, section, strand, track, replaceAssignmentId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    
    // Show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Processing...';
    
    // Make AJAX request
    fetch('/faculty-head/assign-adviser', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert(data.message, 'success');
            
            // Refresh faculty data from server to get latest assignments
            refreshFacultyDataFromServer().then(() => {
                // Refresh the section details to show updated assignment
                showSectionDetailsAccordion(sectionId, grade, section, strand, track);
            });
        } else {
            throw new Error(data.message || 'Assignment failed');
        }
    })
    .catch(error => {
        console.error('Error submitting adviser assignment:', error);
        showAlert('Error: ' + error.message, 'error');
        
        // Restore button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    });
}

// AJAX submission function for teacher assignments
function submitTeacherAssignment(event, sectionId, grade, section, strand, track, subjectId, replaceAssignmentId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    
    // Show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Processing...';
    
    // Close modal if this is from a modal
    const modal = form.closest('.modal');
    if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    }
    
    // Make AJAX request
    fetch('/faculty-head/assign-teacher', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert(data.message, 'success');
            
            // Refresh faculty data from server to get latest assignments
            refreshFacultyDataFromServer().then(() => {
                // Refresh the section details to show updated assignment
                showSectionDetailsAccordion(sectionId, grade, section, strand, track);
            });
        } else {
            throw new Error(data.message || 'Assignment failed');
        }
    })
    .catch(error => {
        console.error('Error submitting teacher assignment:', error);
        showAlert('Error: ' + error.message, 'error');
        
        // Restore button state if form is still visible
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    });
}

// Function to refresh faculty data from server
function refreshFacultyDataFromServer() {
    return fetch('/faculty-head/get-faculty-data', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the global faculty data with fresh data from server
            window.facultyData = {
                teachers: data.teachers || [],
                subjects: data.subjects || [],
                assignments: data.assignments || [],
                advisers: data.advisers || [],
                sections: data.sections || []
            };
            console.log('Faculty data refreshed from server');
        } else {
            console.error('Failed to refresh faculty data:', data.message);
        }
    })
    .catch(error => {
        console.error('Error refreshing faculty data:', error);
    });
}

// Function to update faculty data after adviser assignment (deprecated - now using server refresh)
function updateFacultyDataAfterAdviserAssignment(newAssignment, replaceAssignmentId) {
    // This function is now deprecated in favor of refreshFacultyDataFromServer
    // Keeping for backward compatibility
    if (replaceAssignmentId) {
        window.facultyData.advisers = window.facultyData.advisers.filter(a => a.id !== replaceAssignmentId);
    }
    window.facultyData.advisers.push(newAssignment);
}

// Function to update faculty data after teacher assignment (deprecated - now using server refresh)
function updateFacultyDataAfterTeacherAssignment(newAssignment, replaceAssignmentId) {
    // This function is now deprecated in favor of refreshFacultyDataFromServer
    // Keeping for backward compatibility
    if (replaceAssignmentId) {
        window.facultyData.assignments = window.facultyData.assignments.filter(a => a.id !== replaceAssignmentId);
    }
    window.facultyData.assignments.push(newAssignment);
}

// Function to show alert messages
function showAlert(message, type) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Function to show replace adviser form
function showReplaceAdviserForm(sectionId, grade, section, strand, track, assignmentId) {
    const form = document.getElementById(`replaceAdviserForm${sectionId}`);
    if (form) {
        form.style.display = 'block';
    }
}

// Function to hide replace adviser form
function hideReplaceAdviserForm(sectionId) {
    const form = document.getElementById(`replaceAdviserForm${sectionId}`);
    if (form) {
        form.style.display = 'none';
    }
}

// Function to show replace teacher form
function showReplaceTeacherForm(sectionId, subjectId, assignmentId) {
    const form = document.getElementById(`replaceTeacherForm${sectionId}_${subjectId}`);
    if (form) {
        form.style.display = 'block';
    }
}

// Function to hide replace teacher form
function hideReplaceTeacherForm(sectionId, subjectId) {
    const form = document.getElementById(`replaceTeacherForm${sectionId}_${subjectId}`);
    if (form) {
        form.style.display = 'none';
    }
}

// Auto-refresh faculty data every 30 seconds to catch changes from other users
let autoRefreshInterval;

function startAutoRefresh() {
    // Clear any existing interval
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    
    // Set up auto-refresh every 30 seconds
    autoRefreshInterval = setInterval(() => {
        console.log('Auto-refreshing faculty data...');
        refreshFacultyDataFromServer().then(() => {
            // If there's an open section details, refresh it
            const openSectionDetails = document.querySelector('.section-details[style*="block"]');
            if (openSectionDetails) {
                // Find the section ID and refresh it
                const sectionId = openSectionDetails.id.replace('details', '');
                // We need to extract the grade, section, strand, track from the current view
                // For now, just log that we detected an open section
                console.log('Open section detected, but auto-refresh of section details requires more context');
            }
        });
    }, 30000); // 30 seconds
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// Start auto-refresh when page loads
document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
    console.log('Auto-refresh started - faculty data will refresh every 30 seconds');
});

// Stop auto-refresh when page is about to unload
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
});

// Make functions globally available
window.submitAdviserAssignment = submitAdviserAssignment;
window.submitTeacherAssignment = submitTeacherAssignment;
window.refreshFacultyDataFromServer = refreshFacultyDataFromServer;
window.startAutoRefresh = startAutoRefresh;
window.stopAutoRefresh = stopAutoRefresh;
window.showReplaceAdviserForm = showReplaceAdviserForm;
window.hideReplaceAdviserForm = hideReplaceAdviserForm;
window.showReplaceTeacherForm = showReplaceTeacherForm;
window.hideReplaceTeacherForm = hideReplaceTeacherForm;
window.showStrandSections = showStrandSections;
window.showTVLTracks = showTVLTracks;
window.showSectionsForStrand = showSectionsForStrand;
window.showSectionDetailsAccordion = showSectionDetailsAccordion;
window.loadStudentsForSection = loadStudentsForSection;
window.loadGradeSectionsForAssignment = loadGradeSectionsForAssignment;
window.loadSeniorHighStrandsForAssignment = loadSeniorHighStrandsForAssignment;
window.loadElementaryJuniorSectionsForAssignment = loadElementaryJuniorSectionsForAssignment;
window.resetAccordionToInitialState = resetAccordionToInitialState;
