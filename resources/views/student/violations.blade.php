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

  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col-12">
      <h2 class="section-title mb-1">My Violations</h2>
      <p class="text-muted mb-0">View your disciplinary records and violations</p>
    </div>
  </div>

  @if(session('info'))
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @php
      $escalatedCount = $violations->where('escalated', true)->count();
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

    <!-- SUMMARY CARDS -->
    <div class="row g-3 mb-5">
      <div class="col-6 col-lg-3">
        <div class="card h-100" style="background:#198754;border-radius:12px;">
          <div class="card-body d-flex align-items-center">
            <i class="ri-flag-line display-6 me-3 text-white"></i>
            <div>
              <div class="text-white">Total Violations</div>
              <h3 class="text-white">{{ $violations->count() }}</h3>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card h-100" style="background:#198754;border-radius:12px;">
          <div class="card-body d-flex align-items-center">
            <i class="ri-information-line display-6 me-3 text-white"></i>
            <div>
              <div class="text-white">Minor</div>
              <h3 class="text-white">{{ $violations->where('effective_severity', 'minor')->count() }}</h3>
              @if($escalatedCount > 0)
                <small class="text-white">({{ $violations->where('severity', 'minor')->where('escalated', false)->count() }} original)</small>
              @endif
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card h-100" style="background:#198754;border-radius:12px;">
          <div class="card-body d-flex align-items-center">
            <i class="ri-alert-line display-6 me-3 text-white"></i>
            <div>
              <div class="text-white">Major</div>
              <h3 class="text-white">{{ $violations->where('effective_severity', 'major')->count() }}</h3>
              @if($escalatedCount > 0)
                <small class="text-white">({{ $escalatedCount }} escalated)</small>
              @endif
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card h-100" style="background:#fff;border-radius:12px; border:2px solid #198754;">
          <div class="card-body d-flex align-items-center">
            <i class="ri-time-line display-6 me-3" style="color:#198754;"></i>
            <div>
              <div style="color:#198754;">This Month</div>
              <h3 style="color:#198754;">{{ $violations->where('violation_date', '>=', now()->startOfMonth())->count() }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Search Violations -->
    <div class="mb-4">
      <input type="text" id="violationSearch" class="form-control" style="width:100%;" placeholder="Search violation by title...">
    </div>

    @if($violations->count() > 0)
      <!-- VIOLATIONS LIST TABLE -->
      <h4 class="section-title">Violation Records</h4>
      <div class="table-responsive mb-5">
          <table class="table align-middle" style="background:#fff;border-radius:10px;overflow:hidden;">
            <thead style="background:#198754;color:#fff;">
            <tr>
              <th>Violations</th>
              <th>Severity</th>
              <th>Date</th>
              <th>Reported By</th>
              <th>Action Taken</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($violations as $violation)
              <tr class="violation-row" data-title="{{ strtolower($violation->title) }}" data-violation="true" data-created-at="{{ $violation->created_at }}">
                <td>{{ $violation->title }}</td>
                <td>
                  <span class="badge" style="background:#198754;color:#fff;">{{ ucfirst($violation->effective_severity) }}</span>
                </td>
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
                    @if ($violation->effective_severity === 'major')
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
                    @endif
                    
                    {{-- Show attachment button for violations with approved disciplinary actions --}}
                    @if($violation->disciplinary_action)
                      @if($violation->student_attachment_path)
                        {{-- If attachment already exists, show download button --}}
                        <a href="{{ route('student.violations.download-attachment', $violation->id) }}" 
                           class="btn btn-sm btn-outline-success" 
                           title="Download Attachment" target="_blank">
                          <i class="ri-download-line"></i> Download
                        </a>
                      @else
                        {{-- If no attachment, show upload button --}}
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
</script>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

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
// Violation search filter
document.addEventListener('DOMContentLoaded', function() {
  var searchInput = document.getElementById('violationSearch');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      var searchTerm = searchInput.value.trim().toLowerCase();
      var rows = document.querySelectorAll('.violation-row');
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
});
document.addEventListener('DOMContentLoaded', function() {
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

      <!-- BEHAVIOR SUMMARY -->
      <h4 class="section-title">Behavior Summary</h4>
      <div class="card mb-5">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h6 class="fw-bold text-primary">Recent Trends</h6>
              <ul class="list-unstyled">
                <li class="mb-2">
                  <i class="ri-calendar-line me-2 text-primary"></i>
                  Last violation: {{ $violations->first()->violation_date->diffForHumans() }}
                </li>
                <li class="mb-2">
                  @php
                    $improving = $violations->where('violation_date', '>=', now()->subMonth())->count()
                                <= $violations->where('violation_date', '>=', now()->subMonths(2))
                                              ->where('violation_date', '<', now()->subMonth())->count();
                  @endphp
                  <i class="ri-arrow-{{ $improving ? 'down' : 'up' }}-line me-2 text-{{ $improving ? 'success' : 'warning' }}"></i>
                  Trend: {{ $improving ? 'Improving' : 'Needs Attention' }}
                </li>
              </ul>
            </div>
            <div class="col-md-6">
              <h6 class="fw-bold text-primary">Recommendations</h6>
              <ul class="list-unstyled">
                @if($violations->where('effective_severity', 'major')->count() > 0)
                  <li class="mb-2"><i class="ri-lightbulb-line me-2 text-warning"></i> Consider scheduling a guidance counseling session</li>
                @endif
                @if($escalatedCount > 0)
                  <li class="mb-2"><i class="ri-alert-line me-2 text-danger"></i> Multiple minor violations escalated</li>
                @endif
                <li class="mb-2"><i class="ri-heart-line me-2 text-primary"></i> Focus on positive behavior choices</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    @else
      <!-- NO VIOLATIONS -->
      <div class="text-center py-5">
        <div class="mb-4">
          <i class="ri-shield-check-line display-1 text-success"></i>
        </div>
  <h4 class="text-success mb-3">Excellent Behavior!</h4>
  <p class="text-success mb-4">You have no violations on record. Keep up the great work!</p>
      </div>
    @endif

    <!-- CONTACT INFO -->
    <div class="card" style="background:#fff; border:2px solid #198754;">
      <div class="card-body text-center">
        <h6 class="fw-bold mb-3" style="color:#198754;">Need Help or Have Questions?</h6>
        <p class="mb-3" style="color:#198754;">If you have questions about your violations or need guidance, reach out to the Guidance Office.</p>
        <div class="row text-center">
          <div class="col-md-6">
            <i class="ri-phone-line me-2" style="color:#198754;"></i>
            <strong style="color:#198754;">Guidance Office:</strong> (02) 123-4567
          </div>
          <div class="col-md-6">
            <i class="ri-mail-line me-2" style="color:#198754;"></i>
            <strong style="color:#198754;">Email:</strong> guidance@nicolites.edu.ph
          </div>
        </div>
      </div>
    </div>
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
