<style>
  /* Fix hover for View PDF (outline-primary) and Reply (outline-info) */
  .btn-outline-primary:hover, .btn-outline-primary:focus {
    color: #fff !important;
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
  }
  .btn-outline-info:hover, .btn-outline-info:focus {
    color: #fff !important;
    background-color: #0dcaf0 !important;
    border-color: #0dcaf0 !important;
    box-shadow: 0 0 0 0.2rem rgba(13,202,240,.25);
  }
</style>
<x-student-layout>
  @vite(['resources/css/student_violations.css'])

  <div class="py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="section-title">
          <i class="ri-flag-line me-2"></i>
          My Violations
        </h1>
        <small class="text-muted">Last updated: {{ now()->format('M d, Y H:i:s') }}</small>
      </div>
    </div>

    <!-- Alert Messages -->
    @if(session('info'))
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @php
      $escalatedCount = $violations->where('escalated', true)->count();
      $minorViolations = $violations->where('effective_severity', 'minor');
      $majorViolations = $violations->where('effective_severity', 'major');
    @endphp

    @if($escalatedCount > 0)
      <div class="alert alert-success alert-dismissible fade show" role="alert" style="background:#198754;color:#fff;">
        <i class="ri-alert-line me-2"></i>
        <strong>Important:</strong> {{ $escalatedCount }} of your minor violation{{ $escalatedCount > 1 ? 's have' : ' has' }} been escalated to major severity due to multiple offenses.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @php
      $approvedViolationsNeedingAttachment = $violations->filter(function($violation) {
        return $violation->disciplinary_action && 
               !$violation->student_attachment_path;
      });
    @endphp

    @if($approvedViolationsNeedingAttachment->count() > 0)
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="ri-attachment-line me-2"></i>
        <strong>Action Required:</strong> You have {{ $approvedViolationsNeedingAttachment->count() }} approved disciplinary action{{ $approvedViolationsNeedingAttachment->count() > 1 ? 's' : '' }} that require{{ $approvedViolationsNeedingAttachment->count() === 1 ? 's' : '' }} documentation. Please upload the required attachments.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
          <div class="card-body d-flex align-items-center py-3">
            <div class="me-3">
              <i class="ri-flag-line" style="font-size: 2.5rem; color: #198754;"></i>
            </div>
            <div class="flex-grow-1">
              <h2 class="mb-0 fw-bold" style="font-size: 2rem;">{{ $violations->count() }}</h2>
              <p class="text-muted mb-0">Total Violations</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #FFA500 !important;">
          <div class="card-body d-flex align-items-center py-3">
            <div class="me-3">
              <i class="ri-information-line" style="font-size: 2.5rem; color: #FFA500;"></i>
            </div>
            <div class="flex-grow-1">
              <h2 class="mb-0 fw-bold" style="font-size: 2rem;">{{ $minorViolations->count() }}</h2>
              <p class="text-muted mb-0">Minor Violations</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
          <div class="card-body d-flex align-items-center py-3">
            <div class="me-3">
              <i class="ri-alert-line" style="font-size: 2.5rem; color: #dc3545;"></i>
            </div>
            <div class="flex-grow-1">
              <h2 class="mb-0 fw-bold" style="font-size: 2rem;">{{ $majorViolations->count() }}</h2>
              <p class="text-muted mb-0">Major Violations</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0dcaf0 !important;">
          <div class="card-body d-flex align-items-center py-3">
            <div class="me-3">
              <i class="ri-time-line" style="font-size: 2.5rem; color: #0dcaf0;"></i>
            </div>
            <div class="flex-grow-1">
              <h2 class="mb-0 fw-bold" style="font-size: 2rem;">{{ $violations->where('violation_date', '>=', now()->startOfMonth())->count() }}</h2>
              <p class="text-muted mb-0">This Month</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="violationTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="minor-violations-tab" data-bs-toggle="tab" data-bs-target="#minor-violations" type="button" role="tab">
          <i class="ri-information-line me-2"></i>Minor Violations
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="major-violations-tab" data-bs-toggle="tab" data-bs-target="#major-violations" type="button" role="tab">
          <i class="ri-alert-line me-2"></i>Major Violations
        </button>
      </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="violationTabContent">
      
      <!-- Minor Violations Tab -->
      <div class="tab-pane fade show active" id="minor-violations" role="tabpanel">
        <div class="card shadow">
          <div class="card-header" style="background-color: #198754; color: white;">
            <h5 class="mb-0">
              <i class="ri-information-line me-2"></i>
              Minor Violations
            </h5>
          </div>
          <div class="card-body">
            <!-- Search -->
            <div class="mb-3">
              <div class="input-group">
                <input type="text" class="form-control" id="minorViolationSearch" placeholder="Search by violation title...">
                <button class="btn btn-outline-secondary" type="button">
                  <i class="ri-search-line"></i>
                </button>
              </div>
            </div>

            <!-- Minor Violations Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead style="background-color: #198754; color: white;">
                  <tr>
                    <th>Violations</th>
                    <th>Date</th>
                    <th>Reported By</th>
                  </tr>
                </thead>
                <tbody id="minor-violations-tbody">
                  @forelse($minorViolations as $violation)
                    <tr class="minor-violation-row" data-title="{{ strtolower($violation->title) }}">
                      <td>{{ $violation->title }}</td>
                      <td>{{ $violation->violation_date->format('M d, Y') }}</td>
                      <td>{{ $violation->reported_by_name ?? '-' }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="text-center py-4">
                        <i class="ri-shield-check-line fs-1 text-success d-block mb-2"></i>
                        <p class="text-muted">No minor violations on record</p>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Major Violations Tab -->
      <div class="tab-pane fade" id="major-violations" role="tabpanel">
        <div class="card shadow">
          <div class="card-header" style="background-color: #198754; color: white;">
            <h5 class="mb-0">
              <i class="ri-alert-line me-2"></i>
              Major Violations
            </h5>
          </div>
          <div class="card-body">
            <!-- Search -->
            <div class="mb-3">
              <div class="input-group">
                <input type="text" class="form-control" id="majorViolationSearch" placeholder="Search by violation title...">
                <button class="btn btn-outline-secondary" type="button">
                  <i class="ri-search-line"></i>
                </button>
              </div>
            </div>

            <!-- Major Violations Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead style="background-color: #198754; color: white;">
                  <tr>
                    <th>Violations</th>
                    <th>Date</th>
                    <th>Reported By</th>
                    <th>Action Taken</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="major-violations-tbody">
                  @forelse($majorViolations as $violation)
                    <tr class="major-violation-row" data-title="{{ strtolower($violation->title) }}">
                      <td>{{ $violation->title }}</td>
                      <td>{{ $violation->violation_date->format('M d, Y') }}</td>
                      <td>{{ $violation->reported_by_name ?? '-' }}</td>
                      <td>
                        {{ $violation->action_taken ?? '-' }}
                        @if($violation->disciplinary_action)
                          <br><small class="text-info"><strong>Disciplinary Action:</strong> {{ $violation->disciplinary_action }}</small>
                        @endif
                        @if($violation->student_attachment_path)
                          <br><small class="text-success"><i class="ri-attachment-line"></i> Attachment uploaded</small>
                        @elseif($violation->disciplinary_action)
                          <br><small class="text-warning"><i class="ri-attachment-line"></i> Attachment required</small>
                        @endif
                      </td>
                      <td>
                        <div class="btn-group" role="group">
                          <button type="button" class="btn btn-sm btn-outline-primary"
                                  data-bs-toggle="modal"
                                  data-bs-target="#pdfModal"
                                  onclick="document.getElementById('pdfFrame').src='{{ route('student.pdf.studentNarrative', ['studentId' => $violation->student_id, 'violationId' => $violation->id]) }}'"
                                  title="View PDF">
                            <i class="ri-file-pdf-line"></i> View PDF
                          </button>
                          <button type="button" class="btn btn-sm btn-outline-info"
                                  data-bs-toggle="modal"
                                  data-bs-target="#replyModal"
                                  data-id="{{ $violation->id }}"
                                  title="Reply to Report">
                            <i class="ri-reply-line"></i> Reply
                          </button>
                          @if($violation->disciplinary_action)
                            @if($violation->student_attachment_path)
                              <a href="{{ route('student.violations.download-attachment', $violation->id) }}" 
                                 class="btn btn-sm btn-outline-success" 
                                 title="Download Attachment" target="_blank">
                                <i class="ri-download-line"></i> Download
                              </a>
                            @else
                              <button type="button" class="btn btn-sm btn-outline-warning"
                                      data-bs-toggle="modal"
                                      data-bs-target="#attachmentModal"
                                      data-violation-id="{{ $violation->id }}"
                                      title="Upload Attachment for Approved Disciplinary Action">
                                <i class="ri-attachment-line"></i> Upload
                              </button>
                            @endif
                          @endif
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center py-4">
                        <i class="ri-shield-check-line fs-1 text-success d-block mb-2"></i>
                        <p class="text-muted">No major violations on record</p>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>

    @if($violations->count() > 0)

      <!-- Reply Modal (only one instance) -->
      <div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="replyModalLabel">Reply to Narrative Report</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form method="POST" id="replyForm" action="#">
                @csrf
                <div class="mb-4">
                  <label for="incident" class="form-label fw-bold">Please state the incident based on your understanding.</label>
                  <textarea id="incident" name="incident" class="form-control" rows="3" placeholder="Your answer..." required></textarea>
                </div>
                <div class="mb-4">
                  <label for="feeling" class="form-label fw-bold">How do you feel about what happened?</label>
                  <textarea id="feeling" name="feeling" class="form-control" rows="3" placeholder="Your answer..." required></textarea>
                </div>
                <div class="mb-4">
                  <label for="action_plan" class="form-label fw-bold">What plan of action would you like to do?</label>
                  <textarea id="action_plan" name="action_plan" class="form-control" rows="3" placeholder="Your answer..." required></textarea>
                </div>
                <button type="submit" class="btn btn-success">Submit Reply</button>
              </form>
<script>
// Violation search filter for minor violations
document.addEventListener('DOMContentLoaded', function() {
  var minorSearchInput = document.getElementById('minorViolationSearch');
  if (minorSearchInput) {
    minorSearchInput.addEventListener('input', function() {
      var searchTerm = minorSearchInput.value.trim().toLowerCase();
      var rows = document.querySelectorAll('.minor-violation-row');
      rows.forEach(function(row) {
        var title = row.getAttribute('data-title');
        if (!searchTerm || title.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  }

  // Violation search filter for major violations
  var majorSearchInput = document.getElementById('majorViolationSearch');
  if (majorSearchInput) {
    majorSearchInput.addEventListener('input', function() {
      var searchTerm = majorSearchInput.value.trim().toLowerCase();
      var rows = document.querySelectorAll('.major-violation-row');
      rows.forEach(function(row) {
        var title = row.getAttribute('data-title');
        if (!searchTerm || title.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  }

  // Handle reply modal
  var replyModal = document.getElementById('replyModal');
  var replyForm = document.getElementById('replyForm');
  var violationLinks = document.querySelectorAll('[data-bs-target="#replyModal"]');

  violationLinks.forEach(function(link) {
    link.addEventListener('click', function() {
      var violationId = link.getAttribute('data-id');
      var actionUrl = "{{ route('student.violations.reply', ['violation' => 'VIOLATION_ID']) }}".replace('VIOLATION_ID', violationId);
      replyForm.setAttribute('action', actionUrl);
    });
  });

  // Handle attachment modal
  var attachmentModal = document.getElementById('attachmentModal');
  var attachmentForm = document.getElementById('attachmentForm');
  var attachmentButtons = document.querySelectorAll('[data-bs-target="#attachmentModal"]');

  attachmentButtons.forEach(function(button) {
    button.addEventListener('click', function() {
      var violationId = button.getAttribute('data-violation-id');
      attachmentForm.setAttribute('data-violation-id', violationId);
    });
  });

  // Handle attachment form submission
  if (attachmentForm) {
    attachmentForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      var violationId = this.getAttribute('data-violation-id');
      var formData = new FormData(this);
      var submitBtn = this.querySelector('button[type="submit"]');
      var originalText = submitBtn.innerHTML;
      
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Uploading...';
      
      fetch('/student/violations/' + violationId + '/upload-attachment', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Attachment uploaded successfully!');
          var modal = bootstrap.Modal.getInstance(attachmentModal);
          modal.hide();
          this.reset();
          location.reload(); // Refresh to show updated status
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while uploading the attachment.');
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      });
    });
  }
});
</script>
            </div>
          </div>
        </div>
      </div>

      <!-- PDF Modal (only one instance) -->
      <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="pdfModalLabel">Student Narrative Report</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="height:80vh;">
              <iframe id="pdfFrame" src="" width="100%" height="100%" style="border:1px solid #198754;border-radius:8px;"></iframe>
            </div>
          </div>
        </div>
      </div>

      <!-- Attachment Upload Modal -->
      <div class="modal fade" id="attachmentModal" tabindex="-1" aria-labelledby="attachmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="attachmentModalLabel">Upload Attachment for Approved Disciplinary Action</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="alert alert-info mb-3">
                <i class="ri-information-line me-2"></i>
                <strong>Important:</strong> Your violation has been reviewed and approved with a disciplinary action. 
                Please upload any relevant documentation or response regarding this decision.
              </div>
              <form id="attachmentForm" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                  <label for="attachment" class="form-label fw-bold">Select File</label>
                  <input type="file" class="form-control" id="attachment" name="attachment" 
                         accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                  <div class="form-text">Allowed formats: PDF, DOC, DOCX, JPG, JPEG, PNG. Maximum size: 5MB</div>
                </div>
                <div class="mb-3">
                  <label for="description" class="form-label fw-bold">Description (Optional)</label>
                  <textarea class="form-control" id="description" name="description" rows="3" 
                            placeholder="Brief description of the attachment (e.g., written apology, parent acknowledgment, etc.)..."></textarea>
                </div>
                <div class="d-grid">
                  <button type="submit" class="btn btn-success">
                    <i class="ri-upload-line me-1"></i>Upload Attachment
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

    @endif

  </div>

  <!-- Bootstrap JS (must come before your JS file) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  @push('scripts')
  <script>
      // Mark violations alert as viewed when student visits this page
      document.addEventListener('DOMContentLoaded', function() {
          fetch('{{ route("student.mark-alert-viewed") }}', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                  alert_type: 'violations'
              })
          }).catch(error => console.error('Error marking violations alert as viewed:', error));
      });
  </script>
  @endpush
</x-student-layout>
