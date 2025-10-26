<x-faculty-head-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Activate Grade Submission</h1>
      <div class="text-muted">
        <i class="ri-play-circle-line me-1"></i>System Control
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

    <!-- CURRENT STATUS -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-lg-6">
        <div class="card card-summary {{ $isActive ? 'card-payment' : 'card-schedule' }} h-100">
          <div class="card-body text-center">
            <i class="{{ $isActive ? 'ri-play-circle-fill' : 'ri-pause-circle-fill' }} display-1 mb-3"></i>
            <h4>Grade Submission is Currently</h4>
            <h2>{{ $isActive ? 'ACTIVE' : 'INACTIVE' }}</h2>
            <p class="mb-0">{{ $isActive ? 'Teachers can submit grades' : 'Grade submission is disabled' }}</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title">
              <i class="ri-information-line me-2"></i>Grade Submission Control
            </h5>
            <p class="card-text">
              Use this control to activate or deactivate the grade submission system for all teachers. 
              When activated, teachers can submit grades for review. When deactivated, the submission 
              feature will be disabled system-wide.
            </p>
            <div class="mt-4">
              <button type="button" class="btn btn-lg {{ $isActive ? 'btn-danger' : 'btn-success' }}" id="toggleSubmissionBtn" data-active="{{ $isActive ? '1' : '0' }}">
                <i class="{{ $isActive ? 'ri-pause-circle-line' : 'ri-play-circle-line' }} me-2" id="toggleIcon"></i>
                <span id="toggleText">{{ $isActive ? 'Deactivate' : 'Activate' }} Grade Submission</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SUBMISSION PERIODS -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-calendar-check-line me-2"></i>Grade Submission Periods
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-6 col-lg-3">
            <div class="card border">
              <div class="card-body text-center">
                <h6>1st Quarter</h6>
                <div class="form-check form-switch d-flex justify-content-center">
                  <input class="form-check-input quarter-switch" type="checkbox" id="q1Switch" data-quarter="q1" {{ $quarterSettings['q1_active'] ? 'checked' : '' }}>
                </div>
                <small class="text-muted">Aug - Oct</small>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card border">
              <div class="card-body text-center">
                <h6>2nd Quarter</h6>
                <div class="form-check form-switch d-flex justify-content-center">
                  <input class="form-check-input quarter-switch" type="checkbox" id="q2Switch" data-quarter="q2" {{ $quarterSettings['q2_active'] ? 'checked' : '' }}>
                </div>
                <small class="text-muted">Nov - Jan</small>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card border">
              <div class="card-body text-center">
                <h6>3rd Quarter</h6>
                <div class="form-check form-switch d-flex justify-content-center">
                  <input class="form-check-input quarter-switch" type="checkbox" id="q3Switch" data-quarter="q3" {{ $quarterSettings['q3_active'] ? 'checked' : '' }}>
                </div>
                <small class="text-muted">Feb - Apr</small>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card border">
              <div class="card-body text-center">
                <h6>4th Quarter</h6>
                <div class="form-check form-switch d-flex justify-content-center">
                  <input class="form-check-input quarter-switch" type="checkbox" id="q4Switch" data-quarter="q4" {{ $quarterSettings['q4_active'] ? 'checked' : '' }}>
                </div>
                <small class="text-muted">May - Jul</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SYSTEM NOTIFICATIONS -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-notification-line me-2"></i>System Notifications
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          <h6><i class="ri-information-line me-2"></i>Important Notes:</h6>
          <ul class="mb-0">
            <li>When grade submission is deactivated, teachers will not be able to submit new grades or edit existing drafts.</li>
            <li>Already submitted grades will remain in the system for review.</li>
            <li>Teachers will be notified automatically when the system status changes.</li>
            <li>This setting affects all teachers and subjects system-wide.</li>
          </ul>
        </div>
      </div>
    </div>
  </main>

  <!-- CONFIRMATION MODAL -->
  <div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Action</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="confirmMessage"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="confirmBtn">Confirm</button>
        </div>
      </div>
    </div>
  </div>
</x-faculty-head-layout>

