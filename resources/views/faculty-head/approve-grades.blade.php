<x-faculty-head-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Approve/Reject Submitted Grades</h1>
      <div class="text-muted">
        <i class="ri-checkbox-circle-line me-1"></i>Pending Review
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <!-- PENDING SUBMISSIONS -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-file-check-line me-2"></i>Pending Grade Submissions
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Teacher</th>
                <th>Subject</th>
                <th>Class</th>
                <th>Quarter</th>
                <th>Students</th>
                <th>Submitted</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($pendingSubmissions as $submission)
              <tr>
                <td>
                  <strong>{{ $submission->teacher->name }}</strong>
                  <br><small class="text-muted">{{ $submission->teacher->email }}</small>
                </td>
                <td>
                  <strong>{{ $submission->subject->subject_name }}</strong>
                  <br><small class="text-muted">{{ $submission->subject->subject_code }}</small>
                </td>
                <td>{{ $submission->grade_level }} - {{ $submission->section }}</td>
                <td>{{ $submission->quarter }}</td>
                <td>
                  <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: {{ $submission->completion_percentage }}%"
                         aria-valuenow="{{ $submission->completion_percentage }}" 
                         aria-valuemin="0" aria-valuemax="100">
                      {{ $submission->completion_percentage }}%
                    </div>
                  </div>
                  <small class="text-muted">{{ $submission->grades_entered }}/{{ $submission->total_students }} students</small>
                </td>
                <td>{{ $submission->submitted_at->format('M d, Y H:i') }}</td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewSubmission({{ $submission->id }})">
                      <i class="ri-eye-line me-1"></i>Review
                    </button>
                    <button class="btn btn-outline-success" onclick="approveSubmission({{ $submission->id }})">
                      <i class="ri-check-line me-1"></i>Approve
                    </button>
                    <button class="btn btn-outline-danger" onclick="rejectSubmission({{ $submission->id }})">
                      <i class="ri-close-line me-1"></i>Reject
                    </button>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">
                  <div class="py-4">
                    <i class="ri-file-check-line display-1 text-muted mb-3"></i>
                    <h5>No Pending Submissions</h5>
                    <p class="text-muted">All grade submissions have been reviewed.</p>
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- REVIEW MODAL -->
  <div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Review Grade Submission</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="reviewModalBody">
          <!-- Content will be loaded via AJAX -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-success" id="approveBtn">
            <i class="ri-check-line me-2"></i>Approve
          </button>
          <button type="button" class="btn btn-danger" id="rejectBtn">
            <i class="ri-close-line me-2"></i>Reject
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- APPROVAL MODAL -->
  <div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Approve Grade Submission</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="approvalForm" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Review Notes (Optional)</label>
              <textarea name="review_notes" class="form-control" rows="3" placeholder="Add any comments about this approval..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">
              <i class="ri-check-line me-2"></i>Approve Submission
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- REJECTION MODAL -->
  <div class="modal fade" id="rejectionModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Reject Grade Submission</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="rejectionForm" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
              <textarea name="review_notes" class="form-control" rows="4" placeholder="Please provide a clear reason for rejecting this submission..." required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">
              <i class="ri-close-line me-2"></i>Reject Submission
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</x-faculty-head-layout>

@push('scripts')
<script src="{{ asset('js/faculty-head-approve-grades.js') }}"></script>
@endpush
