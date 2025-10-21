document.addEventListener('DOMContentLoaded', function() {
  const dob = document.getElementById('date_of_birth');
  if (!dob) return;
  // prevent selecting a future date
  dob.max = new Date().toISOString().split('T')[0];

  // optional: when user clears, add 'is-invalid'
  dob.addEventListener('change', () => {
    if (!dob.value) {
      dob.classList.add('is-invalid');
    } else {
      dob.classList.remove('is-invalid');
    }
  });
});

// Show strand field for Grade 11-12
document.addEventListener('DOMContentLoaded', function() {
  const gradeSelect = document.getElementById('grade_level');
  const strandGroup = document.getElementById('strand-group');
  const strandSelect = document.getElementById('strand');

  function toggleStrandField() {
      const selectedGrade = gradeSelect.value;
      
      if (selectedGrade === 'Grade 11' || selectedGrade === 'Grade 12') {
          strandGroup.classList.remove('d-none');
          strandSelect.setAttribute('required', 'required');
      } else {
          strandGroup.classList.add('d-none');
          strandSelect.removeAttribute('required');
          strandSelect.value = ''; // Clear selection
      }
  }

  // Initial check
  toggleStrandField();
  
  // Listen for changes
  gradeSelect.addEventListener('change', toggleStrandField);
});


// Auto-populate guardian name from parent fields
document.getElementById('father_name').addEventListener('input', function() {
  if (!document.getElementById('guardian_name').value) {
      document.getElementById('guardian_name').value = this.value;
  }
});

document.getElementById('mother_name').addEventListener('input', function() {
  if (!document.getElementById('guardian_name').value) {
      document.getElementById('guardian_name').value = this.value;
  }
});

// Auto-populate guardian contact from parent fields
document.getElementById('father_contact').addEventListener('input', function() {
  if (!document.getElementById('guardian_contact').value) {
      document.getElementById('guardian_contact').value = this.value;
  }
});

document.getElementById('mother_contact').addEventListener('input', function() {
  if (!document.getElementById('guardian_contact').value) {
      document.getElementById('guardian_contact').value = this.value;
  }
});

// Check if strand should be shown on page load (for old input)
document.addEventListener('DOMContentLoaded', function() {
  const gradeLevel = document.getElementById('grade_level').value;
  if (gradeLevel === 'Grade 11' || gradeLevel === 'Grade 12') {
      document.getElementById('strand-group').classList.remove('d-none');
      document.getElementById('strand').required = true;
  }
});

// Show track field for TVL strand
document.addEventListener('DOMContentLoaded', function() {
    const strandSelect = document.getElementById('strand');
    const trackContainer = document.getElementById('track-container');
    const trackSelect = document.getElementById('track_applied');

    function toggleTrackField() {
        if (strandSelect.value === 'TVL') {
            trackContainer.style.display = 'block';
            trackSelect.required = true;
        } else {
            trackContainer.style.display = 'none';
            trackSelect.required = false;
            trackSelect.value = '';
        }
    }

    // Initial check on page load
    toggleTrackField();

    // Listen for changes
    strandSelect.addEventListener('change', toggleTrackField);

    // Handle old input values - check if TVL was previously selected
    const oldStrand = strandSelect.value || '';
    if (oldStrand === 'TVL') {
        trackContainer.style.display = 'block';
        trackSelect.required = true;
    }
});