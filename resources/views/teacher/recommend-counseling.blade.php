<x-teacher-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="section-title mb-1">
          <i class="ri-user-heart-line me-2 text-success"></i>
          Recommend Student for Counseling
        </h1>
        <p class="text-muted mb-0">Submit a counseling recommendation for student support</p>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-success">
          <i class="ri-arrow-left-line me-2"></i>Back to Dashboard
        </a>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if(isset($message))
      <div class="alert {{ $students->count() > 0 ? 'alert-info' : 'alert-warning' }} alert-dismissible fade show" role="alert">
        <i class="ri-information-line me-2"></i>{{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <!-- Main Form Section -->
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-success text-white">
            <div class="d-flex align-items-center">
              <i class="ri-file-text-line me-2"></i>
              <h5 class="mb-0">Counseling Recommendation Form</h5>
            </div>
          </div>
          <div class="card-body p-4">
            @if($students->count() > 0)
            <form action="{{ route('teacher.recommend-counseling') }}" method="POST">
              @csrf

              <!-- Student Search Section -->
              <div class="mb-4">
                <label for="studentSearch" class="form-label fw-semibold">
                  <i class="ri-search-line me-1"></i>Search Student <span class="text-danger">*</span>
                  <small class="text-muted">({{ $students->count() }} student{{ $students->count() != 1 ? 's' : '' }} in your advisory)</small>
                </label>
                <div class="position-relative">
                  <div class="input-group">
                    <span class="input-group-text bg-light">
                      <i class="ri-user-line text-muted"></i>
                    </span>
                    <input type="text" class="form-control @error('student_id') is-invalid @enderror" 
                           id="studentSearch" name="studentSearch" 
                           placeholder="{{ $students->count() > 0 ? 'Type student name or ID...' : 'No students available' }}" 
                           autocomplete="off" {{ $students->count() > 0 ? 'required' : 'disabled' }}>
                  </div>
                  <input type="hidden" id="student_id" name="student_id" value="{{ old('student_id') }}">
                  <div id="studentSuggestions" class="suggestions-list shadow-sm" 
                       style="display: none; position: absolute; z-index: 10; width: 100%; 
                              background: #fff; border: 1px solid #dee2e6; border-radius: 0.375rem; 
                              max-height: 200px; overflow-y: auto; margin-top: 2px;"></div>
                </div>
                @error('student_id')
                  <div class="invalid-feedback">{{ $error }}</div>
                @enderror
              </div>
                <!-- Advisory students data for JavaScript -->
                <script>
                window.advisoryStudentsData = [
                  @foreach($students as $student)
                    {
                      id: {{ $student->id }},
                      name: "{{ $student->first_name }} {{ $student->last_name }}",
                      student_id: "{{ addslashes($student->student_id) }}"
                    }@if(!$loop->last),@endif
                  @endforeach
                ];
                </script>
                
                <!-- Include JavaScript for student search functionality -->
                @vite(['resources/js/teacher-recommend-counseling.js'])
                
                <!-- Reason for referral checklist -->
                <div class="mt-4">
                  <label class="form-label fw-semibold">
                    <i class="ri-checkbox-multiple-line me-1"></i>Reason for referral 
                    <small class="text-muted">(check all that apply)</small>
                  </label>
                  
                  <div class="row g-4">
                    <div class="col-md-6">
                      <div class="card h-100 border-success border-opacity-25">
                        <div class="card-header bg-success bg-opacity-10 py-2">
                          <h6 class="mb-0 text-success">
                            <i class="ri-book-line me-1"></i>Academic
                          </h6>
                        </div>
                        <div class="card-body py-3">
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_academic[]" value="Attendance">
                            <label class="form-check-label">Attendance (excessive absenteeism)</label>
                          </div>
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_academic[]" value="Activity Sheets/Assignments">
                            <label class="form-check-label">Activity Sheets/Assignments</label>
                          </div>
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_academic[]" value="Exams">
                            <label class="form-check-label">Exams</label>
                          </div>
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_academic[]" value="Quiz">
                            <label class="form-check-label">Quiz</label>
                          </div>
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_academic[]" value="Others" id="referral_academic_others">
                            <label class="form-check-label" for="referral_academic_others">Others</label>
                          </div>
                          <div class="ms-4">
                            <input type="text" name="referral_academic_other" class="form-control form-control-sm" placeholder="Specify (optional)" style="max-width: 200px;">
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="card h-100 border-info border-opacity-25">
                        <div class="card-header bg-info bg-opacity-10 py-2">
                          <h6 class="mb-0 text-info">
                            <i class="ri-user-heart-line me-1"></i>Personal/Social
                          </h6>
                        </div>
                        <div class="card-body py-3">
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_social[]" value="Anger Management">
                            <label class="form-check-label">Anger Management</label>
                          </div>
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_social[]" value="Bullying">
                            <label class="form-check-label">Bullying</label>
                          </div>
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_social[]" value="Social Skills/Friends">
                            <label class="form-check-label">Social Skills/Friends</label>
                          </div>
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_social[]" value="Negative Attitude">
                            <label class="form-check-label">Negative Attitude</label>
                          </div>
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_social[]" value="Honesty">
                            <label class="form-check-label">Honesty</label>
                          </div>
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_social[]" value="Self-esteem">
                            <label class="form-check-label">Self-esteem</label>
                          </div>
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_social[]" value="Personal Hygiene">
                            <label class="form-check-label">Personal Hygiene</label>
                          </div>
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_social[]" value="Adjustment">
                            <label class="form-check-label">Adjustment</label>
                          </div>
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_social[]" value="Family Conflict">
                            <label class="form-check-label">Family Conflict</label>
                          </div>
                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="referral_social[]" value="Others" id="referral_social_others">
                            <label class="form-check-label" for="referral_social_others">Others</label>
                          </div>
                          <div class="ms-4">
                            <input type="text" name="referral_social_other" class="form-control form-control-sm" placeholder="Specify (optional)" style="max-width: 200px;">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Incident Details Section -->
                  <div class="mt-4">
                    <label class="form-label fw-semibold">
                      <i class="ri-file-text-line me-1"></i>Incident Details
                    </label>
                    <textarea class="form-control" id="incident_description" name="incident_description" 
                              rows="4" placeholder="Describe the incident or concern in detail..." 
                              style="resize: vertical;"></textarea>
                    <div class="form-text">
                      <i class="ri-information-line me-1"></i>
                      Please provide specific details about the situation that led to this recommendation.
                    </div>
                  </div>
                </div>
              </div>

             
              <!-- Information Alert -->
              <div class="alert alert-info border-0 bg-info bg-opacity-10 mt-4">
                <div class="d-flex align-items-start">
                  <i class="ri-information-line me-2 text-info fs-5 mt-1"></i>
                  <div>
                    <strong class="text-info">Important Note:</strong>
                    <p class="mb-0 mt-1">
                      This recommendation will be forwarded to the guidance department for review. 
                      A guidance counselor will assess the situation and may schedule a counseling session if appropriate.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="d-flex justify-content-end gap-3 mt-4">

                <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary px-4">
                  <i class="ri-close-line me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-success px-4">
                  <i class="ri-heart-pulse-line me-2"></i>Submit Recommendation
                </button>
              </div>
            </form>
            @else
            <div class="text-center py-5">
              <div class="mb-4">
                <i class="ri-user-unfollow-line display-4 text-muted"></i>
              </div>
              <h5 class="text-muted">No Advisory Students Available</h5>
              <p class="text-muted mb-4">
                You can only recommend students from your advisory class for counseling. 
                Please contact the administrator if you believe this is an error.
              </p>
              <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-primary">
                <i class="ri-arrow-left-line me-2"></i>Back to Dashboard
              </a>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Scheduled Counseling Sessions Modal -->
  @if(isset($scheduledSessions) && $scheduledSessions->count() > 0)
  <div class="modal fade" id="scheduledCounselingModal" tabindex="-1" aria-labelledby="scheduledCounselingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="scheduledCounselingModalLabel">
            <i class="ri-calendar-check-line me-2"></i>Scheduled Counseling Sessions
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info border-0 bg-info bg-opacity-10">
            <div class="d-flex align-items-start">
              <i class="ri-information-line me-2 text-info fs-5 mt-1"></i>
              <div>
                <strong class="text-info">Guidance has scheduled your counseling recommendations</strong>
                <p class="mb-0 mt-1">
                  The following sessions have been approved and scheduled by the Guidance Department.
                </p>
              </div>
            </div>
          </div>
          
          <div class="list-group">
            @foreach($scheduledSessions as $session)
            <div class="list-group-item border-start border-success border-3">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <h6 class="mb-1 text-success">
                    <i class="ri-user-heart-line me-1"></i>
                    {{ $session->student->first_name ?? '' }} {{ $session->student->last_name ?? '' }}
                  </h6>
                  @if($session->student && $session->student->student_id)
                    <small class="text-muted">Student ID: {{ $session->student->student_id }}</small>
                  @endif
                </div>
                <span class="badge bg-success">Scheduled</span>
              </div>
              
              <div class="row g-2 mt-2">
                @if($session->start_date)
                  <div class="col-md-6">
                    <small class="text-muted d-block"><strong>Start Date:</strong></small>
                    <span class="badge bg-primary">{{ $session->start_date->format('M j, Y') }}</span>
                  </div>
                @endif
                
                @if($session->end_date)
                  <div class="col-md-6">
                    <small class="text-muted d-block"><strong>End Date:</strong></small>
                    <span class="badge bg-primary">{{ $session->end_date->format('M j, Y') }}</span>
                  </div>
                @endif
                
                @if($session->time)
                  <div class="col-md-6">
                    <small class="text-muted d-block"><strong>Time:</strong></small>
                    <span class="badge bg-info">{{ $session->time instanceof \Carbon\Carbon ? $session->time->format('g:i A') : $session->time }}</span>
                  </div>
                @endif
                
                @if($session->frequency)
                  <div class="col-md-6">
                    <small class="text-muted d-block"><strong>Frequency:</strong></small>
                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $session->frequency)) }}</span>
                  </div>
                @endif
                
                @if($session->time_limit)
                  <div class="col-md-6">
                    <small class="text-muted d-block"><strong>Duration:</strong></small>
                    <span class="badge bg-warning text-dark">{{ $session->time_limit }} minutes</span>
                  </div>
                @endif
                
                @if($session->session_no)
                  <div class="col-md-6">
                    <small class="text-muted d-block"><strong>Session #:</strong></small>
                    <span class="badge bg-success">{{ $session->session_no }}</span>
                  </div>
                @endif
              </div>
              
              @if($session->incident_description)
                <div class="mt-2">
                  <small class="text-muted d-block"><strong>Your Original Recommendation:</strong></small>
                  <p class="small text-secondary mt-1 mb-0" style="font-style: italic;">
                    {{ Str::limit($session->incident_description, 150) }}
                  </p>
                </div>
              @endif
            </div>
            @endforeach
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="markAsViewedBtn">
            <i class="ri-check-line me-1"></i>Mark as Viewed
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Auto-show modal on page load if there are scheduled sessions
    document.addEventListener('DOMContentLoaded', function() {
      @if(isset($scheduledSessions) && $scheduledSessions->count() > 0)
        var scheduledModal = new bootstrap.Modal(document.getElementById('scheduledCounselingModal'));
        scheduledModal.show();
        
        // Mark as viewed button handler
        document.getElementById('markAsViewedBtn').addEventListener('click', function() {
          const csrfToken = document.querySelector('meta[name="csrf-token"]');
          if (!csrfToken) {
            return;
          }
          
          const btn = this;
          btn.disabled = true;
          btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Marking...';
          
          fetch('{{ route("teacher.mark-alert-viewed") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            },
            body: JSON.stringify({
              alert_type: 'counseling'
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              btn.innerHTML = '<i class="ri-check-double-line me-1"></i>Viewed';
              btn.classList.remove('btn-success');
              btn.classList.add('btn-outline-success');
              
              // Hide badge from sidebar by reloading the layout component
              const badge = document.querySelector('#counseling-link .badge');
              if (badge) {
                badge.style.display = 'none';
              }
              
              // Remove red background from sidebar item
              const counselingLink = document.getElementById('counseling-link');
              if (counselingLink) {
                counselingLink.style.backgroundColor = '';
                counselingLink.style.borderLeft = '';
                counselingLink.style.paddingLeft = '';
              }
              
              setTimeout(() => {
                scheduledModal.hide();
              }, 1000);
            }
          })
          .catch(error => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-check-line me-1"></i>Mark as Viewed';
            alert('Failed to mark as viewed. Please try again.');
          });
        });
      @endif
    });
  </script>
  @endif

  <!-- Counseling Notifications Modal - Removed (sidebar badge handles notifications) -->
  <div class="modal fade d-none" id="counselingNotificationsModal" tabindex="-1" aria-labelledby="counselingNotificationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="counselingNotificationsModalLabel">
            <i class="ri-notification-3-line me-2"></i>Counseling Session Notifications
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @if(isset($scheduledSessions) && $scheduledSessions->count() > 0)
            <div class="alert alert-info border-0 bg-info bg-opacity-10">
              <div class="d-flex align-items-start">
                <i class="ri-information-line me-2 text-info fs-5 mt-1"></i>
                <div>
                  <strong class="text-info">Scheduled Sessions</strong>
                  <p class="mb-0 mt-1">
                    The following counseling sessions you recommended have been scheduled by the Guidance Department.
                  </p>
                </div>
              </div>
            </div>
            
            <div class="list-group">
              @foreach($scheduledSessions as $session)
                <div class="list-group-item border-start border-success border-3">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                      <h6 class="mb-2 text-success">
                        <i class="ri-user-heart-line me-1"></i>
                        {{ $session->student->first_name ?? '' }} {{ $session->student->last_name ?? '' }}
                      </h6>
                      
                      @if($session->student && $session->student->student_id)
                        <p class="text-muted small mb-2">
                          <strong>Student ID:</strong> {{ $session->student->student_id }}
                        </p>
                      @endif
                      
                      <div class="row g-2">
                        @if($session->start_date)
                          <div class="col-md-6">
                            <small class="text-muted d-block"><strong>Start Date:</strong></small>
                            <span class="badge bg-primary">{{ $session->start_date->format('M j, Y') }}</span>
                          </div>
                        @endif
                        
                        @if($session->end_date)
                          <div class="col-md-6">
                            <small class="text-muted d-block"><strong>End Date:</strong></small>
                            <span class="badge bg-primary">{{ $session->end_date->format('M j, Y') }}</span>
                          </div>
                        @endif
                        
                        @if($session->time)
                          <div class="col-md-6">
                            <small class="text-muted d-block"><strong>Time:</strong></small>
                            <span class="badge bg-info">{{ $session->time instanceof \Carbon\Carbon ? $session->time->format('g:i A') : $session->time }}</span>
                          </div>
                        @endif
                        
                        @if($session->frequency)
                          <div class="col-md-6">
                            <small class="text-muted d-block"><strong>Frequency:</strong></small>
                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $session->frequency)) }}</span>
                          </div>
                        @endif
                        
                        @if($session->time_limit)
                          <div class="col-md-6">
                            <small class="text-muted d-block"><strong>Duration:</strong></small>
                            <span class="badge bg-warning text-dark">{{ $session->time_limit }} minutes</span>
                          </div>
                        @endif
                        
                        @if($session->session_no)
                          <div class="col-md-6">
                            <small class="text-muted d-block"><strong>Session #:</strong></small>
                            <span class="badge bg-success">{{ $session->session_no }}</span>
                          </div>
                        @endif
                      </div>
                      
                      @if($session->incident_description)
                        <div class="mt-3">
                          <small class="text-muted d-block"><strong>Original Recommendation:</strong></small>
                          <p class="small text-secondary mt-1 mb-0" style="font-style: italic;">
                            {{ Str::limit($session->incident_description, 150) }}
                          </p>
                        </div>
                      @endif
                    </div>
                    
                    <div class="text-end">
                      <span class="badge bg-success">{{ ucfirst($session->status) }}</span>
                      @if($session->counselor)
                        <p class="text-muted small mt-2 mb-0">
                          <strong>Counselor:</strong><br>
                          {{ $session->counselor->name ?? 'Guidance Department' }}
                        </p>
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-4">
              <div class="mb-3">
                <i class="ri-notification-off-line display-4 text-muted"></i>
              </div>
              <h5 class="text-muted mb-2">No Scheduled Sessions</h5>
              <p class="text-muted mb-0">
                You don't have any scheduled counseling sessions at the moment.<br>
                Sessions you recommend will appear here once they're scheduled by the Guidance Department.
              </p>
            </div>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>


</x-teacher-layout>
