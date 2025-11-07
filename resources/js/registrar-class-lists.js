/**
 * Registrar Class Lists Accordion Functionality
 * Handles accordion-style navigation for grade levels, strands, tracks, and sections
 */

console.log('Registrar Class Lists JS loaded');

// Global variables
let loadedGrades = new Set();

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Registrar Class Lists: DOM loaded, initializing accordion...');
    
    // Add hover effects and styling
    addCustomStyles();
    
    // Auto-load all grade sections on page load
    autoLoadAllGrades();
});

// Auto-load all grade sections on page load
function autoLoadAllGrades() {
    const gradeButtons = document.querySelectorAll('[onclick*="loadGradeSections"]');
    
    gradeButtons.forEach(button => {
        const onclickAttr = button.getAttribute('onclick');
        const gradeMatch = onclickAttr.match(/loadGradeSections\('([^']+)'\)/);
        
        if (gradeMatch) {
            const grade = gradeMatch[1];
            // Load sections automatically with a small delay to prevent overwhelming the server
            setTimeout(() => {
                loadGradeSections(grade);
            }, Math.random() * 1000); // Random delay between 0-1 second
        }
    });
}

// Load sections for a grade level
async function loadGradeSections(grade) {
    const sectionsContainer = document.getElementById(`sections${grade.replace(' ', '')}`);
    const badge = document.getElementById(`badge${grade.replace(' ', '')}`);
    
    // Reset loaded state to allow refresh
    loadedGrades.delete(grade);
    
    try {
        // For Senior High School (Grade 11 & 12), show strand selection first
        if (grade === 'Grade 11' || grade === 'Grade 12') {
            await loadSeniorHighStrands(grade, sectionsContainer, badge);
        } else {
            // For Elementary and Junior High, load sections directly
            await loadElementaryJuniorSections(grade, sectionsContainer, badge);
        }
    } catch (error) {
        console.error('Error loading grade sections:', error);
        showErrorState(sectionsContainer, badge, 'Error loading sections');
    }
}

// Load strands for Senior High School
async function loadSeniorHighStrands(grade, sectionsContainer, badge) {
    const strands = ['STEM', 'ABM', 'HUMSS', 'TVL'];
    
    let strandsHtml = `
        <div class="strands-container p-3">
            <div class="d-flex align-items-center mb-3">
                <button class="btn btn-sm btn-light me-3" onclick="loadGradeSections('${grade}')" title="Back to grade levels">
                    <i class="ri-arrow-left-line"></i>
                </button>
                <h6 class="text-primary mb-0"><i class="ri-book-line me-2"></i>Select Strand for ${grade}</h6>
            </div>
            <div class="row g-3">
    `;
    
    for (const strand of strands) {
        strandsHtml += `
            <div class="col-md-6">
                <div class="strand-card border rounded p-3 text-center" onclick="loadStrandSections('${grade}', '${strand}')" style="cursor: pointer;">
                    <div class="strand-badge bg-primary text-white rounded px-3 py-2 mb-2">
                        ${strand}
                    </div>
                    <h6 class="mb-1">${getStrandFullName(strand)}</h6>
                    <small class="text-muted">Click to view sections</small>
                </div>
            </div>
        `;
    }
    
    strandsHtml += `
            </div>
        </div>
    `;
    
    sectionsContainer.innerHTML = strandsHtml;
    badge.textContent = 'Select Strand';
    badge.className = 'badge bg-info ms-auto me-3';
}

// Load sections for a specific strand
async function loadStrandSections(grade, strand) {
    const sectionsContainer = document.getElementById(`sections${grade.replace(' ', '')}`);
    const badge = document.getElementById(`badge${grade.replace(' ', '')}`);
    
    try {
        // For TVL strand, show track selection first
        if (strand === 'TVL') {
            await loadTVLTracks(grade, strand, sectionsContainer, badge);
        } else {
            // For non-TVL strands, load sections directly
            await loadSectionsForStrand(grade, strand, null, sectionsContainer, badge);
        }
    } catch (error) {
        console.error('Error loading strand sections:', error);
        showErrorState(sectionsContainer, badge, 'Error loading sections');
    }
}

// Load tracks for TVL strand
async function loadTVLTracks(grade, strand, sectionsContainer, badge) {
    const tracks = ['ICT', 'H.E'];
    
    let tracksHtml = `
        <div class="tracks-container p-3">
            <div class="d-flex align-items-center mb-3">
                <button class="btn btn-sm btn-light me-3" onclick="loadSeniorHighStrands('${grade}', document.getElementById('sections${grade.replace(' ', '')}'), document.getElementById('badge${grade.replace(' ', '')}'))" title="Back to strands">
                    <i class="ri-arrow-left-line"></i>
                </button>
                <h6 class="text-primary mb-0"><i class="ri-settings-line me-2"></i>Select Track for ${grade} - ${strand}</h6>
            </div>
            <div class="row g-3">
    `;
    
    for (const track of tracks) {
        tracksHtml += `
            <div class="col-md-6">
                <div class="track-card border rounded p-3 text-center" onclick="loadSectionsForStrand('${grade}', '${strand}', '${track}', document.getElementById('sections${grade.replace(' ', '')}'), document.getElementById('badge${grade.replace(' ', '')}'))" style="cursor: pointer;">
                    <div class="track-badge bg-success text-white rounded px-3 py-2 mb-2">
                        ${track}
                    </div>
                    <h6 class="mb-1">${getTrackFullName(track)}</h6>
                    <small class="text-muted">Click to view sections</small>
                </div>
            </div>
        `;
    }
    
    tracksHtml += `
            </div>
        </div>
    `;
    
    sectionsContainer.innerHTML = tracksHtml;
    badge.textContent = 'Select Track';
    badge.className = 'badge bg-success ms-auto me-3';
}

// Load sections for Elementary/Junior High or specific strand/track
async function loadSectionsForStrand(grade, strand = null, track = null, sectionsContainer, badge) {
    try {
        let url = `/registrar/class-lists/get-sections?grade_level=${encodeURIComponent(grade)}`;
        if (strand) url += `&strand=${encodeURIComponent(strand)}`;
        if (track) url += `&track=${encodeURIComponent(track)}`;
        
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.sections) {
            let sectionsHtml = '';
            let totalStudents = 0;
            
            // Add back button for Senior High School strands
            if ((grade === 'Grade 11' || grade === 'Grade 12') && strand && strand !== '') {
                sectionsHtml += `
                    <div class="d-flex align-items-center mb-3 p-3">
                        <button class="btn btn-sm btn-light me-3" onclick="loadSeniorHighStrands('${grade}', document.getElementById('sections${grade.replace(' ', '')}'), document.getElementById('badge${grade.replace(' ', '')}'))" title="Back to strands">
                            <i class="ri-arrow-left-line"></i>
                        </button>
                        <h6 class="text-primary mb-0"><i class="ri-grid-line me-2"></i>Sections for ${grade} - ${strand}${track ? ' - ' + track : ''}</h6>
                    </div>
                `;
            }
            
            data.sections.forEach(section => {
                const studentCount = section.student_count || 0;
                totalStudents += studentCount;
                
                sectionsHtml += `
                    <div class="section-item border-bottom" onclick="loadSectionStudents('${grade}', '${section.section}', '${strand || ''}', '${track || ''}')" style="cursor: pointer;">
                        <div class="d-flex justify-content-between align-items-center p-3 hover-bg-light">
                            <div class="d-flex align-items-center">
                                <div class="section-badge bg-warning text-white rounded px-3 py-2 me-3">
                                    Section ${section.section}
                                </div>
                                <div>
                                    <h6 class="mb-0">Section ${section.section}</h6>
                                    <small class="text-muted">Click to view students</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-secondary">(${studentCount} students)</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            if (sectionsHtml === '') {
                sectionsHtml = `
                    <div class="text-center py-4">
                        <i class="ri-folder-open-line fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No sections found for ${grade}</p>
                    </div>
                `;
            }
            
            sectionsContainer.innerHTML = sectionsHtml;
            badge.textContent = `${totalStudents} students`;
            badge.className = 'badge bg-primary ms-auto me-3';
            
            loadedGrades.add(grade);
        } else {
            sectionsContainer.innerHTML = `
                <div class="text-center py-4">
                    <i class="ri-error-warning-line fs-1 text-danger"></i>
                    <p class="text-danger mt-2">Failed to load sections</p>
                </div>
            `;
            badge.textContent = 'Error';
            badge.className = 'badge bg-danger ms-auto me-3';
        }
    } catch (error) {
        console.error('Error loading sections:', error);
        showErrorState(sectionsContainer, badge, 'Error loading sections');
    }
}

// Load students for a specific section (with strand/track support)
async function loadSectionStudents(grade, section, strand = null, track = null) {
    try {
        let url = `/registrar/class-lists/get-students?grade_level=${encodeURIComponent(grade)}&section=${encodeURIComponent(section)}`;
        if (strand) url += `&strand=${encodeURIComponent(strand)}`;
        if (track) url += `&track=${encodeURIComponent(track)}`;
        
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.students) {
            showStudentsList(grade, section, data.students, data.class_info, strand, track);
        } else {
            alert('Failed to load students for this section');
        }
    } catch (error) {
        console.error('Error loading students:', error);
        alert('Error loading students');
    }
}

// Show students list in expanded section
function showStudentsList(grade, section, students, classInfo, strand = null, track = null) {
    const sectionsContainer = document.getElementById(`sections${grade.replace(' ', '')}`);
    const badge = document.getElementById(`badge${grade.replace(' ', '')}`);
    
    // Determine the correct back function based on grade level and strand/track
    let backFunction = `loadGradeSections('${grade}')`;
    if (grade === 'Grade 11' || grade === 'Grade 12') {
        if (strand && track) {
            // Back to track selection for TVL
            backFunction = `loadTVLTracks('${grade}', '${strand}', document.getElementById('sections${grade.replace(' ', '')}'), document.getElementById('badge${grade.replace(' ', '')}'))`;
        } else if (strand) {
            // Back to sections for non-TVL strands
            backFunction = `loadSectionsForStrand('${grade}', '${strand}', null, document.getElementById('sections${grade.replace(' ', '')}'), document.getElementById('badge${grade.replace(' ', '')}'))`;
        } else {
            // Back to strand selection
            backFunction = `loadSeniorHighStrands('${grade}', document.getElementById('sections${grade.replace(' ', '')}'), document.getElementById('badge${grade.replace(' ', '')}'))`;
        }
    }
    
    let studentsHtml = `
        <div class="section-header text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <button class="btn btn-sm btn-light me-3" onclick="${backFunction}" title="Back">
                        <i class="ri-arrow-left-line"></i>
                    </button>
                    <h5 class="mb-0">
                        <i class="ri-group-line me-2"></i>${classInfo || grade + ' - Section ' + section}
                    </h5>
                </div>
                <span class="badge bg-light text-dark">${students.length} students</span>
            </div>
        </div>
        <div class="students-list p-3">
    `;
    
    if (students.length > 0) {
        students.forEach((student, index) => {
            studentsHtml += `
                <div class="student-item d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <span class="student-number me-3 text-muted">${index + 1}.</span>
                        <div>
                            <h6 class="mb-0">${student.first_name} ${student.last_name}</h6>
                            <small class="text-muted">${student.student_id || 'N/A'}</small>
                        </div>
                    </div>
                    <div class="student-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewStudentDetails('${student.id}')">
                            <i class="ri-eye-line"></i>
                        </button>
                    </div>
                </div>
            `;
        });
    } else {
        studentsHtml += `
            <div class="text-center py-4">
                <i class="ri-user-line fs-1 text-muted"></i>
                <p class="text-muted mt-2">No students found in this section</p>
            </div>
        `;
    }
    
    studentsHtml += '</div>';
    sectionsContainer.innerHTML = studentsHtml;
}

// View student details
async function viewStudentDetails(studentId) {
    try {
        const response = await fetch(`/registrar/class-lists/student/${studentId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.student) {
            const student = data.student;
            const modalBody = document.getElementById('studentModalBody');
            
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3"><i class="ri-user-line me-2"></i>Personal Information</h6>
                        <p class="mb-2"><strong>Name:</strong> ${student.first_name} ${student.last_name}</p>
                        <p class="mb-2"><strong>Student ID:</strong> ${student.student_id || 'N/A'}</p>
                        <p class="mb-2"><strong>Class:</strong> ${student.class_info || student.grade_level + ' - ' + student.section}</p>
                        ${student.strand ? `<p class="mb-2"><strong>Strand:</strong> ${student.strand}${student.strand_full_name ? ` - ${student.strand_full_name}` : ''}</p>` : ''}
                        ${student.track ? `<p class="mb-0"><strong>Track:</strong> ${student.track}${student.track_full_name ? ` - ${student.track_full_name}` : ''}</p>` : ''}
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3"><i class="ri-contacts-line me-2"></i>Contact Information</h6>
                        <p class="mb-2"><strong>Email:</strong> ${student.email || 'N/A'}</p>
                        <p class="mb-2"><strong>Contact:</strong> ${student.contact_number || 'N/A'}</p>
                        <p class="mb-0"><strong>Status:</strong> 
                            <span class="badge bg-${student.enrollment_status === 'enrolled' ? 'success' : 'warning'}">
                                ${student.enrollment_status}
                            </span>
                        </p>
                    </div>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('studentModal'));
            modal.show();
        } else {
            alert('Failed to load student details');
        }
    } catch (error) {
        console.error('Error loading student details:', error);
        alert('Error loading student details');
    }
}

// Load sections for Elementary and Junior High
async function loadElementaryJuniorSections(grade, sectionsContainer, badge) {
    await loadSectionsForStrand(grade, null, null, sectionsContainer, badge);
}

// Helper functions for full names
function getStrandFullName(strand) {
    const strandNames = {
        'STEM': 'Science, Technology, Engineering, and Mathematics',
        'ABM': 'Accountancy, Business, and Management',
        'HUMSS': 'Humanities and Social Sciences',
        'TVL': 'Technical-Vocational-Livelihood'
    };
    return strandNames[strand] || strand;
}

function getTrackFullName(track) {
    const trackNames = {
        'ICT': 'Information and Communications Technology',
        'H.E': 'Home Economics'
    };
    return trackNames[track] || track;
}

// Show error state
function showErrorState(sectionsContainer, badge, message) {
    sectionsContainer.innerHTML = `
        <div class="text-center py-4">
            <i class="ri-error-warning-line fs-1 text-danger"></i>
            <p class="text-danger mt-2">${message}</p>
        </div>
    `;
    badge.textContent = 'Error';
    badge.className = 'badge bg-danger ms-auto me-3';
}

// Add simple custom styles
function addCustomStyles() {
    // Keep minimal - most styling will be in the view file
}

// Expose functions globally
window.loadGradeSections = loadGradeSections;
window.loadStrandSections = loadStrandSections;
window.loadSectionStudents = loadSectionStudents;
window.viewStudentDetails = viewStudentDetails;
window.loadSeniorHighStrands = loadSeniorHighStrands;
window.loadTVLTracks = loadTVLTracks;
window.loadSectionsForStrand = loadSectionsForStrand;
window.showStudentsList = showStudentsList;
window.loadElementaryJuniorSections = loadElementaryJuniorSections;
