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

document.addEventListener('DOMContentLoaded', function() {
  const grade  = document.getElementById('grade_applied');
  const group  = document.getElementById('strand-group');
  const strand = document.getElementById('strand');
  // toggle only for these two
  const SHOW_FOR = ['Grade 11','Grade 12'];

  function toggleStrand() {
    if (SHOW_FOR.includes(grade.value)) {
      group.classList.remove('d-none');
      strand.required = true;
    } else {
      group.classList.add('d-none');
      strand.required = false;
      strand.value = '';
    }
  }

  // initialize on load
  toggleStrand();

  // re-run on every change
  grade.addEventListener('change', toggleStrand);
});