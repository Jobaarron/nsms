<x-student-layout>
  @vite(['resources/css/student_violations.css', 'resources/js/student-violation.js'])

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
              <tr class="violation-row" data-title="{{ strtolower($violation->title) }}">
                <td>{{ $violation->title }}</td>
                <td>
                  <span class="badge" style="background:#198754;color:#fff;">{{ ucfirst($violation->effective_severity) }}</span>
                </td>
                <td>{{ $violation->violation_date->format('M d, Y') }}</td>
                <td>{{ $violation->reported_by_name ?? '-' }}</td>
                <td>{{ $violation->action_taken ?? '-' }}</td>
                <td>
                  @if ($violation->effective_severity === 'major')
                    <!-- View PDF -->
                    <a href="#" data-bs-toggle="modal" 
                       data-bs-target="#pdfModal" 
                       data-pdf="{{ route('student.pdf.studentNarrative', ['studentId' => $student->id, 'violationId' => $violation->id]) }}"
                       title="View PDF">
                      <i class="ri-eye-line"></i>
                    </a>

                    <!-- Reply -->
                    <a href="#" data-bs-toggle="modal" data-bs-target="#replyModal"
                       data-id="{{ $violation->id }}" title="Reply to Narrative Report">
                      <i class="ri-chat-1-line"></i>
                    </a>
                  @endif
                </td>
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
</x-student-layout>
