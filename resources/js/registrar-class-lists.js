/**
 * Registrar Class Lists Dynamic Filtering
 * Handles cascading dropdowns for grade level, strand, track, and section filtering
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Registrar Class Lists JS loaded');
    
    // Get form elements
    const gradeSelect = document.getElementById('grade_level');
    const strandField = document.getElementById('strandField');
    const trackField = document.getElementById('trackField');
    const strandSelect = document.getElementById('strand');
    const trackSelect = document.getElementById('track');
    const sectionSelect = document.getElementById('section');

    // Debug: Check if elements exist
    console.log('Elements found:', {
        gradeSelect: !!gradeSelect,
        strandField: !!strandField,
        trackField: !!trackField,
        strandSelect: !!strandSelect,
        trackSelect: !!trackSelect,
        sectionSelect: !!sectionSelect
    });

    // Initialize dropdowns on page load
    initializeDropdowns();

    // Event listeners
    if (gradeSelect) {
        gradeSelect.addEventListener('change', handleGradeChange);
    }

    if (strandSelect) {
        strandSelect.addEventListener('change', handleStrandChange);
    }

    if (trackSelect) {
        trackSelect.addEventListener('change', handleTrackChange);
    }

    /**
     * Initialize dropdowns with current values
     */
    function initializeDropdowns() {
        const currentGrade = gradeSelect ? gradeSelect.value : '';
        const currentStrand = strandSelect ? strandSelect.value : '';
        
        console.log('Initializing dropdowns with:', { currentGrade, currentStrand });
        
        // Show/hide fields based on current selections
        if (currentGrade === 'Grade 11' || currentGrade === 'Grade 12') {
            showStrandField();
            if (currentStrand) {
                loadStrands(currentGrade, currentStrand);
                if (currentStrand === 'TVL') {
                    showTrackField();
                    loadTracks(currentGrade, currentStrand);
                }
            } else {
                loadStrands(currentGrade);
            }
        }

        // Load sections if grade is selected
        if (currentGrade) {
            loadSections();
        }
        
        // Always try to load sections for any grade to test
        console.log('Testing section loading for all grades...');
        if (gradeSelect && gradeSelect.options.length > 1) {
            // Test with first available grade
            const testGrade = gradeSelect.options[1].value;
            console.log('Testing with grade:', testGrade);
        }
    }

    /**
     * Handle grade level change
     */
    function handleGradeChange() {
        const selectedGrade = gradeSelect.value;
        
        // Reset dependent fields
        resetStrandField();
        resetTrackField();
        resetSectionField();
        
        if (selectedGrade === 'Grade 11' || selectedGrade === 'Grade 12') {
            showStrandField();
            loadStrands(selectedGrade);
        } else {
            hideStrandField();
            hideTrackField();
        }
        
        // Load sections for the selected grade
        if (selectedGrade) {
            loadSections();
        }
    }

    /**
     * Handle strand change
     */
    function handleStrandChange() {
        const selectedStrand = strandSelect.value;
        const selectedGrade = gradeSelect.value;
        
        // Reset dependent fields
        resetTrackField();
        resetSectionField();
        
        if (selectedStrand === 'TVL') {
            showTrackField();
            loadTracks(selectedGrade, selectedStrand);
        } else {
            hideTrackField();
        }
        
        // Load sections for the selected grade and strand
        loadSections();
    }

    /**
     * Handle track change
     */
    function handleTrackChange() {
        resetSectionField();
        loadSections();
    }

    /**
     * Load strands via AJAX
     */
    function loadStrands(gradeLevel, selectedStrand = null) {
        if (!gradeLevel) return;

        console.log('Loading strands for grade:', gradeLevel);
        const url = `/registrar/class-lists/get-strands?grade_level=${encodeURIComponent(gradeLevel)}`;
        console.log('Strand URL:', url);

        fetch(url)
            .then(response => {
                console.log('Strand response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Strand data received:', data);
                if (data.success) {
                    populateStrandDropdown(data.strands, selectedStrand);
                } else {
                    console.error('Failed to load strands:', data.message);
                    showError('Failed to load strands');
                }
            })
            .catch(error => {
                console.error('Error loading strands:', error);
                showError('Error loading strands');
            });
    }

    /**
     * Load tracks via AJAX
     */
    function loadTracks(gradeLevel, strand, selectedTrack = null) {
        if (!gradeLevel || strand !== 'TVL') {
            resetTrackField();
            return;
        }
        
        console.log('Loading tracks for grade:', gradeLevel, 'strand:', strand);
        const url = `/registrar/class-lists/get-tracks?grade_level=${encodeURIComponent(gradeLevel)}&strand=${encodeURIComponent(strand)}`;
        console.log('Track URL:', url);
        
        fetch(url)
            .then(response => {
                console.log('Track response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Track data received:', data);
                if (data.success) {
                    populateTrackDropdown(data.tracks, selectedTrack);
                } else {
                    console.error('Failed to load tracks:', data.message);
                    showError('Failed to load tracks');
                }
            })
            .catch(error => {
                console.error('Error loading tracks:', error);
                showError('Error loading tracks');
            });
    }

    /**
     * Load sections via AJAX
     */
    function loadSections() {
        const gradeLevel = gradeSelect ? gradeSelect.value : '';
        const strand = strandSelect ? strandSelect.value : '';
        const track = trackSelect ? trackSelect.value : '';
        
        console.log('Loading sections for:', { gradeLevel, strand, track });
        
        if (!gradeLevel) {
            resetSectionField();
            return;
        }
        
        let url = `/registrar/class-lists/get-sections?grade_level=${encodeURIComponent(gradeLevel)}`;
        if (strand) url += `&strand=${encodeURIComponent(strand)}`;
        if (track) url += `&track=${encodeURIComponent(track)}`;
        
        console.log('Section URL:', url);
        
        fetch(url)
            .then(response => {
                console.log('Section response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Section data received:', data);
                if (data.success) {
                    populateSectionDropdown(data.sections);
                } else {
                    console.error('Failed to load sections:', data.message);
                    showError('Failed to load sections');
                }
            })
            .catch(error => {
                console.error('Error loading sections:', error);
                showError('Error loading sections');
            });
    }

    /**
     * Populate strand dropdown
     */
    function populateStrandDropdown(strands, selectedStrand = null) {
        if (!strandSelect) return;
        
        strandSelect.innerHTML = '<option value="">Select Strand</option>';
        strands.forEach(strand => {
            const option = document.createElement('option');
            option.value = strand;
            option.textContent = strand;
            if (selectedStrand && strand === selectedStrand) {
                option.selected = true;
            }
            strandSelect.appendChild(option);
        });
    }

    /**
     * Populate track dropdown
     */
    function populateTrackDropdown(tracks, selectedTrack = null) {
        if (!trackSelect) return;
        
        trackSelect.innerHTML = '<option value="">Select Track</option>';
        tracks.forEach(track => {
            const option = document.createElement('option');
            option.value = track;
            option.textContent = track;
            if (selectedTrack && track === selectedTrack) {
                option.selected = true;
            }
            trackSelect.appendChild(option);
        });
    }

    /**
     * Populate section dropdown
     */
    function populateSectionDropdown(sections) {
        if (!sectionSelect) return;
        
        const currentSelection = sectionSelect.value;
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        
        sections.forEach(section => {
            const option = document.createElement('option');
            option.value = section;
            option.textContent = section;
            if (currentSelection && section === currentSelection) {
                option.selected = true;
            }
            sectionSelect.appendChild(option);
        });
    }

    /**
     * Show strand field
     */
    function showStrandField() {
        if (strandField) {
            strandField.style.display = 'block';
        }
    }

    /**
     * Hide strand field
     */
    function hideStrandField() {
        if (strandField) {
            strandField.style.display = 'none';
        }
    }

    /**
     * Show track field
     */
    function showTrackField() {
        if (trackField) {
            trackField.style.display = 'block';
        }
    }

    /**
     * Hide track field
     */
    function hideTrackField() {
        if (trackField) {
            trackField.style.display = 'none';
        }
    }

    /**
     * Reset strand field
     */
    function resetStrandField() {
        if (strandSelect) {
            strandSelect.innerHTML = '<option value="">Select Strand</option>';
            strandSelect.value = '';
        }
    }

    /**
     * Reset track field
     */
    function resetTrackField() {
        if (trackSelect) {
            trackSelect.innerHTML = '<option value="">Select Track</option>';
            trackSelect.value = '';
        }
    }

    /**
     * Reset section field
     */
    function resetSectionField() {
        if (sectionSelect) {
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            sectionSelect.value = '';
        }
    }

    /**
     * Show error message
     */
    function showError(message) {
        console.error(message);
        // You can implement a toast notification or alert here if needed
    }
});

// Export functions for potential external use
window.RegistrarClassLists = {
    init: function() {
        // Re-initialize if needed
        const event = new Event('DOMContentLoaded');
        document.dispatchEvent(event);
    }
};
