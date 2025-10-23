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
});
