<x-student-layout>
  @vite(['resources/css/student_violations.css', 'resources/js/student-violation.js'])

  <div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">My Violations</h1>
      <div class="text-muted">
        <i class="ri-user-line me-1"></i>{{ $student->first_name }} {{ $student->last_name }}
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
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
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
        <div class="card h-100" style="background:#43b36a;border-radius:12px;">
          <div class="card-body d-flex align-items-center">
            <i class="ri-information-line display-6 me-3 text-white"></i>
            <div>
              <div class="text-white">Minor</div>
              <h3 class="text-white">{{ $violations->where('effective_severity', 'minor')->count() }}</h3>
              @if($escalatedCount > 0)
                <small class="text-white-50">({{ $violations->where('severity', 'minor')->where('escalated', false)->count() }} original)</small>
              @endif
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card h-100" style="background:#145c36;border-radius:12px;">
          <div class="card-body d-flex align-items-center">
            <i class="ri-alert-line display-6 me-3 text-white"></i>
            <div>
              <div class="text-white">Major</div>
              <h3 class="text-white">{{ $violations->where('effective_severity', 'major')->count() }}</h3>
              @if($escalatedCount > 0)
                <small class="text-white-50">({{ $escalatedCount }} escalated)</small>
              @endif
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card h-100 border-2 border-success">
          <div class="card-body d-flex align-items-center">
            <i class="ri-time-line display-6 me-3 text-success"></i>
            <div>
              <div class="text-success">This Month</div>
              <h3 class="text-success">{{ $violations->where('violation_date', '>=', now()->startOfMonth())->count() }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    @if($violations->count() > 0)
      <!-- VIOLATIONS LIST TABLE -->
      <h4 class="section-title">Violation Records</h4>
      <div class="table-responsive mb-5">
        <table class="table align-middle" style="background:#fff;border-radius:10px;overflow:hidden;">
          <thead style="background:#198754;color:#fff;">
            <tr>
              <th>Title</th>
              <th>Severity</th>
              <th>Date</th>
              <th>Reported By</th>
              <th>Action Taken</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($violations as $violation)
              <tr style="color:#198754;">
                <td>{{ $violation->title }}</td>
                <td>
                  <span class="badge bg-success">{{ ucfirst($violation->effective_severity) }}</span>
                </td>
                <td>{{ $violation->violation_date->format('M d, Y') }}</td>
                <td>{{ $violation->reported_by_name ?? '-' }}</td>
                <td>{{ $violation->action_taken ?? '-' }}</td>
                <td>
                  <!-- View PDF -->
                  <a href="#" data-bs-toggle="modal" 
                     data-bs-target="#pdfModal" 
                     data-pdf="{{ asset('storage/Student-narrative-report/' . ($violation->pdf_path ?? 'Student.pdf')) }}"
                     title="View PDF">
                    <i class="ri-eye-line" style="font-size:1.5em;color:#198754;margin-right:10px;"></i>
                  </a>

                  <!-- Reply -->
                  <a href="#" data-bs-toggle="modal" data-bs-target="#replyModal"
                     data-id="{{ $violation->id }}" title="Reply to Narrative Report">
                    <i class="ri-chat-1-line" style="font-size:1.5em;color:#198754;"></i>
                  </a>
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
              <form method="POST" action="#">
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
        <p class="text-muted mb-4">You have no violations on record. Keep up the great work!</p>
      </div>
    @endif

    <!-- CONTACT INFO -->
    <div class="card bg-light">
      <div class="card-body text-center">
        <h6 class="fw-bold text-primary mb-3">Need Help or Have Questions?</h6>
        <p class="text-muted mb-3">If you have questions about your violations or need guidance, reach out to the Guidance Office.</p>
        <div class="row text-center">
          <div class="col-md-6">
            <i class="ri-phone-line text-primary me-2"></i>
            <strong>Guidance Office:</strong> (02) 123-4567
          </div>
          <div class="col-md-6">
            <i class="ri-mail-line text-primary me-2"></i>
            <strong>Email:</strong> guidance@nicolites.edu.ph
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS (must come before your JS file) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</x-student-layout>
