// resources/js/student-violation.js
// JS for student violations page (modals, dynamic PDF, etc.)

document.addEventListener('DOMContentLoaded', function() {
  // PDF Modal dynamic src
  var pdfModal = document.getElementById('pdfModal');
  if (pdfModal) {
    pdfModal.addEventListener('show.bs.modal', function(event) {
      var button = event.relatedTarget;
      var pdfUrl = button.getAttribute('data-pdf');
      var iframe = pdfModal.querySelector('#pdfFrame');
      if (iframe && pdfUrl) iframe.src = pdfUrl;
    });
    pdfModal.addEventListener('hidden.bs.modal', function() {
      var iframe = pdfModal.querySelector('#pdfFrame');
      if (iframe) iframe.src = "";
    });
  }

  // Reply Modal (add custom JS here if needed)
  var replyModal = document.getElementById('replyModal');
  if (replyModal) {
    replyModal.addEventListener('show.bs.modal', function(event) {
      // You can add logic here if reply modal needs to be dynamic
    });
  }

  // Enhanced search for grouped violations
  function setupViolationSearch() {
    // Minor violations search (updated for grouped violations)
    var minorSearchInput = document.getElementById('minorViolationSearch');
    if (minorSearchInput) {
      minorSearchInput.addEventListener('input', function() {
        var searchTerm = minorSearchInput.value.trim().toLowerCase();
        var rows = document.querySelectorAll('.minor-violation-row');
        rows.forEach(function(row) {
          var title = row.getAttribute('data-title');
          var isEscalated = row.classList.contains('escalated-violation');
          var hasRepeatedBadge = row.querySelector('.badge');
          
          if (!searchTerm || title.includes(searchTerm) || 
              (isEscalated && ('escalated'.includes(searchTerm) || 'week'.includes(searchTerm))) ||
              (hasRepeatedBadge && ('repeated'.includes(searchTerm) || 'week'.includes(searchTerm)))) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      });
    }

    // Major violations search (updated for grouped violations)
    var majorSearchInput = document.getElementById('majorViolationSearch');
    if (majorSearchInput) {
      majorSearchInput.addEventListener('input', function() {
        var searchTerm = majorSearchInput.value.trim().toLowerCase();
        var rows = document.querySelectorAll('.major-violation-row');
        rows.forEach(function(row) {
          var title = row.getAttribute('data-title');
          var count = row.getAttribute('data-count');
          var isEscalated = row.classList.contains('escalated-violation');
          
          if (!searchTerm || title.includes(searchTerm) || 
              (isEscalated && 'escalated'.includes(searchTerm)) ||
              (count > 1 && 'repeated'.includes(searchTerm))) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      });
    }
  }

  // Initialize search functionality
  setupViolationSearch();
});
