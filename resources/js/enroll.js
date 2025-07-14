document.addEventListener('DOMContentLoaded', function() {
  const dob = document.getElementById('dob');
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
document.getElementById('grade_level').addEventListener('change', function() {
  const strandGroup = document.getElementById('strand-group');
  if (this.value === 'Grade 11' || this.value === 'Grade 12') {
      strandGroup.classList.remove('d-none'); // Fixed: removed incomplete line
      document.getElementById('strand').required = true;
  } else {
      strandGroup.classList.add('d-none');
      document.getElementById('strand').required = false;
      document.getElementById('strand').value = '';
  }
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