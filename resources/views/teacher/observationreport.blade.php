
<x-teacher-layout>
@vite(['resources/css/student_violations.css', 'resources/js/teacher-observation-reports.js'])

<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
      <div class="mb-3 mb-md-0">
        <h1 class="section-title mb-1">
          <i class="ri-file-list-3-line me-2 text-success"></i>
          Observation Reports
        </h1>
        <p class="text-muted mb-0">Review and respond to student observation reports from the guidance department</p>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-success">
          <i class="ri-arrow-left-line me-2"></i>Back to Dashboard
        </a>
      </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri-check-circle-line me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search Reports -->
    <div class="mb-4">
        <label for="reportSearch" class="form-label fw-semibold">
          <i class="ri-search-line me-1"></i>Search Reports
          <small class="text-muted">({{ $reports->count() }} report{{ $reports->count() != 1 ? 's' : '' }} total)</small>
        </label>
        <div class="position-relative">
          <div class="input-group">
            <span class="input-group-text bg-light">
              <i class="ri-filter-line text-muted"></i>
            </span>
            <input 
                type="text" 
                id="reportSearch" 
                class="form-control" 
                placeholder="Type student name, violation, or counselor name to filter reports..."
                autocomplete="off"
            >
          </div>
        </div>
    </div>

    @if($reports->count() > 0)
        <!-- Main Reports Section -->
        <div class="row">
          <div class="col-12">
            <div class="card shadow-sm border-0">
              <div class="card-header bg-success text-white">
                <div class="d-flex align-items-center">
                  <i class="ri-clipboard-line me-2"></i>
                  <h5 class="mb-0">Observation Records</h5>
                </div>
              </div>
              <div class="card-body p-0">
                <!-- Desktop Table View -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table align-middle mb-0">
                        <thead style="background:#f8f9fa;">
                            <tr>
                                <th class="fw-semibold text-dark border-0">Student Name</th>
                                <th class="fw-semibold text-dark border-0">Violation</th>
                                <th class="fw-semibold text-dark border-0">Date & Time of Meeting</th>
                                <th class="fw-semibold text-dark border-0">Reported By</th>
                                <th class="fw-semibold text-dark border-0">Actions</th>
                            </tr>
                        </thead>
                <tbody>
                    @foreach($reports as $report)
                        <tr 
                            class="report-row" 
                            data-student="{{ strtolower($report->student->name ?? '') }}" 
                            data-violation="{{ strtolower($report->violation->title ?? '') }}"
                        >
                            <td>{{ $report->student?->full_name ?? $report->student?->name ?? '-' }}</td>
                            <td>{{ $report->violation?->title ?? '-' }}</td>
                            <td>
                                @php
                                    $date = null;
                                    $time = null;

                                    if ($report->scheduled_date) {
                                        $date = is_string($report->scheduled_date)
                                                ? substr($report->scheduled_date, 0, 10)
                                                : $report->scheduled_date?->format('Y-m-d');
                                    }

                                    if ($report->scheduled_time instanceof \Carbon\Carbon) {
                                        $time = $report->scheduled_time->format('g:i A');
                                    } elseif (is_string($report->scheduled_time)) {
                                        $parts = preg_split('/\s+/', $report->scheduled_time);
                                        $timePart = count($parts) > 1 ? $parts[1] : $parts[0];
                                        $time = date('H:i:s', strtotime($timePart));
                                    }
                                @endphp

                                @if($date && $time)
                                    {{ \Carbon\Carbon::parse("$date $time")->format('M d, Y h:i A') }}
                                @elseif($date)
                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($report->counselor)
                                    @if($report->counselor->first_name || $report->counselor->last_name)
                                        {{ trim($report->counselor->first_name . ' ' . $report->counselor->last_name) }}
                                    @elseif($report->counselor->user)
                                        {{ $report->counselor->user->name }}
                                    @else
                                        -
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-primary btn-sm view-pdf-btn" 
                                        data-pdf-url="{{ url('/teacher/observationreport/pdf/' . $report->id) }}" 
                                        title="View Report"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#pdfModal"
                                    >
                                        <i class="ri-eye-line me-1"></i>View
                                    </button>
                                    @if($report->teacher_statement && $report->action_plan)
                                        <button 
                                            type="button" 
                                            class="btn btn-outline-success btn-sm" 
                                            title="Reply submitted"
                                            disabled
                                        >
                                            <i class="ri-check-line me-1"></i>Replied
                                        </button>
                                    @else
                                        <button 
                                            type="button" 
                                            class="btn btn-outline-warning btn-sm reply-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#replyModal"
                                            data-id="{{ $report->id }}" 
                                            data-teacher_statement="{{ $report->teacher_statement ?? '' }}"
                                            data-action_plan="{{ $report->action_plan ?? '' }}"
                                            title="Submit your reply"
                                        >
                                            <i class="ri-chat-1-line me-1"></i>Reply
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>

                <!-- Mobile Card View -->
                <div class="d-md-none">
                    @foreach($reports as $report)
                    <div class="border-bottom report-row p-3" 
                         data-student="{{ strtolower($report->student->name ?? '') }}" 
                         data-violation="{{ strtolower($report->violation->title ?? '') }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <h6 class="card-title mb-1">{{ $report->student?->full_name ?? $report->student?->name ?? '-' }}</h6>
                            <div class="small text-muted mb-1">
                                <strong>Violation:</strong> {{ $report->violation?->title ?? '-' }}
                            </div>
                            <div class="small text-muted mb-1">
                                @php
                                    $date = null;
                                    $time = null;

                                    if ($report->scheduled_date) {
                                        $date = is_string($report->scheduled_date)
                                                ? substr($report->scheduled_date, 0, 10)
                                                : $report->scheduled_date?->format('Y-m-d');
                                    }

                                    if ($report->scheduled_time instanceof \Carbon\Carbon) {
                                        $time = $report->scheduled_time->format('g:i A');
                                    } elseif (is_string($report->scheduled_time)) {
                                        $parts = preg_split('/\s+/', $report->scheduled_time);
                                        $timePart = count($parts) > 1 ? $parts[1] : $parts[0];
                                        $time = date('H:i:s', strtotime($timePart));
                                    }
                                @endphp
                                <strong>Meeting:</strong> 
                                @if($date && $time)
                                    {{ \Carbon\Carbon::parse("$date $time")->format('M d, Y h:i A') }}
                                @elseif($date)
                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                                @else
                                    -
                                @endif
                            </div>
                            <div class="small text-muted">
                                <strong>Reported by:</strong> 
                                @if($report->counselor)
                                    @if($report->counselor->first_name || $report->counselor->last_name)
                                        {{ trim($report->counselor->first_name . ' ' . $report->counselor->last_name) }}
                                    @elseif($report->counselor->user)
                                        {{ $report->counselor->user->name }}
                                    @else
                                        -
                                    @endif
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                    
                        <div class="d-flex gap-2 mt-3">
                            <button 
                                type="button" 
                                class="btn btn-outline-primary btn-sm flex-fill view-pdf-btn" 
                                data-pdf-url="{{ url('/teacher/observationreport/pdf/' . $report->id) }}" 
                                data-bs-toggle="modal" 
                                data-bs-target="#pdfModal"
                            >
                                <i class="ri-eye-line me-1"></i>View Report
                            </button>
                            @if($report->teacher_statement && $report->action_plan)
                                <button 
                                    type="button" 
                                    class="btn btn-outline-success btn-sm flex-fill" 
                                    title="Reply submitted"
                                    disabled
                                >
                                    <i class="ri-check-line me-1"></i>Replied
                                </button>
                            @else
                                <button 
                                    type="button" 
                                    class="btn btn-outline-warning btn-sm flex-fill reply-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#replyModal"
                                    data-id="{{ $report->id }}" 
                                    data-teacher_statement="{{ $report->teacher_statement ?? '' }}"
                                    data-action_plan="{{ $report->action_plan ?? '' }}"
                                >
                                    <i class="ri-chat-1-line me-1"></i>Reply
                                </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="row justify-content-center">
          <div class="col-lg-6">
            <div class="card shadow-sm border-0">
              <div class="card-body text-center py-5">
                <div class="mb-4">
                  <i class="ri-shield-check-line display-1 text-success"></i>
                </div>
                <h4 class="text-success mb-3">No Observation Reports</h4>
                <p class="text-muted mb-4">
                  You currently have no observation reports requiring your attention. 
                  Reports will appear here when the guidance department creates them for your students.
                </p>
                <div class="alert alert-info" role="alert">
                  <h6 class="alert-heading">
                    <i class="ri-information-line me-2"></i>About Observation Reports
                  </h6>
                  <p class="mb-0">
                    Observation reports are created by the guidance department to notify you about 
                    student behavioral concerns or incidents. When you receive reports, you'll be able to 
                    view details and submit your teacher response and action plan.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
    @endif
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="replyForm" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="replyModalLabel">
                        <i class="ri-chat-1-line me-2"></i>Teacher Reply
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Information Alert -->
                    <div class="alert alert-info border-0 bg-info bg-opacity-10 mb-4">
                      <div class="d-flex align-items-start">
                        <i class="ri-information-line me-2 text-info fs-5 mt-1"></i>
                        <div>
                          <strong class="text-info">Response Guidelines:</strong>
                          <p class="mb-0 mt-1">
                            Please provide your professional observations and specific action steps you will take 
                            to address the concerns mentioned in the observation report.
                          </p>
                        </div>
                      </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="teacher_statement" class="form-label fw-semibold">
                            <i class="ri-file-text-line me-1"></i>Teacher Statement <span class="text-danger">*</span>
                        </label>
                        <textarea 
                            class="form-control" 
                            id="teacher_statement" 
                            name="teacher_statement" 
                            rows="4" 
                            required 
                            placeholder="Describe your observations about the student's behavior or the situation mentioned in the report..."
                        ></textarea>
                        <div class="form-text">
                          <i class="ri-lightbulb-line me-1"></i>
                          Share your professional perspective and any relevant context.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="action_plan" class="form-label fw-semibold">
                            <i class="ri-task-line me-1"></i>Action Plan <span class="text-danger">*</span>
                        </label>
                        <textarea 
                            class="form-control" 
                            id="action_plan" 
                            name="action_plan" 
                            rows="4" 
                            required 
                            placeholder="Outline specific steps you will take to support the student or address the concerns..."
                        ></textarea>
                        <div class="form-text">
                          <i class="ri-lightbulb-line me-1"></i>
                          Be specific about your intervention strategies and timeline.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-2"></i>Cancel
                    </button>
                    <button type="button" id="submitReplyBtn" class="btn btn-success">
                        <i class="ri-send-plane-line me-2"></i>Submit Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="ri-alert-triangle-line me-2"></i>
                    <strong>Please Review Your Response</strong>
                </div>
                <p>Are you sure you want to submit your observation report reply? Please review the information below:</p>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Teacher Statement:</label>
                    <div class="border p-2 rounded bg-light" id="confirmTeacherStatement" style="min-height: 60px; white-space: pre-wrap;"></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Action Plan:</label>
                    <div class="border p-2 rounded bg-light" id="confirmActionPlan" style="min-height: 60px; white-space: pre-wrap;"></div>
                </div>
                
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    Once submitted, this reply cannot be edited.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmSubmitBtn" class="btn btn-success">
                    <span id="submitBtnText">Yes, Submit Reply</span>
                    <span id="submitSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- PDF Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfModalLabel">
                    <i class="ri-file-pdf-line me-2"></i>Teacher Observation Report PDF
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="height:80vh;">
                <iframe id="pdfFrame" src="" width="100%" height="100%" style="border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript functionality moved to separate file: resources/js/teacher-observation-reports.js -->

<!-- Alert marking functionality moved to teacher-observation-reports.js -->
</x-teacher-layout>
