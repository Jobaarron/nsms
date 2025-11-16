
<x-teacher-layout>
@vite(['resources/css/student_violations.css'])

<div class="container-fluid py-4">
    <h2 class="mb-4" style="color:#198754;">Observation Reports</h2>

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
        <input 
            type="text" 
            id="reportSearch" 
            class="form-control" 
            placeholder="Search by student name or violation..."
        >
    </div>

    @if($reports->count() > 0)
        <h4 class="section-title">Observation Records</h4>
        <div class="table-responsive mb-5">
            <table class="table align-middle" style="background:#fff;border-radius:10px;overflow:hidden;">
                <thead style="background:#198754;color:#fff;">
                    <tr>
                        <th>Student Name</th>
                        <th>Violation</th>
                        <th>Date & Time of Meeting</th>
                        <th>Reported By</th>
                        <th>Actions</th>
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
                                        $time = $report->scheduled_time->format('H:i:s');
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
                                <div class="btn-group" role="group">
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-outline-primary view-pdf-btn" 
                                        data-pdf-url="{{ url('/teacher/observationreport/pdf/' . $report->id) }}" 
                                        title="View Report"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#pdfModal"
                                    >
                                        <i class="ri-eye-line"></i>
                                    </button>
<!-- PDF Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfModalLabel">Teacher Observation Report PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="height:80vh;">
                <iframe id="pdfFrame" src="" width="100%" height="100%" style="border:none;"></iframe>
            </div>
        </div>
    </div>
</div>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-outline-info reply-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#replyModal" 
                                        data-id="{{ $report->id }}" 
                                        data-teacher_statement="{{ $report->teacher_statement ?? '' }}"
                                        data-action_plan="{{ $report->action_plan ?? '' }}"
                                        title="Reply"
                                    >
                                        <i class="ri-chat-1-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="ri-shield-check-line display-1 text-success"></i>
            </div>
            <h4 class="text-success mb-3">No Observation Reports</h4>
            <p class="text-success mb-4">There are currently no observation reports to display.</p>
        </div>
    @endif
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="replyForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">Teacher Reply</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="teacher_statement" class="form-label">Teacher Statement</label>
                        <textarea class="form-control" id="teacher_statement" name="teacher_statement" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="action_plan" class="form-label">Action Plan</label>
                        <textarea class="form-control" id="action_plan" name="action_plan" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="submitReplyBtn" class="btn btn-success">Submit Reply</button>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentCaseMeetingId = null;
    

    
    // Handle Reply button click to populate modal and set form action
    document.querySelectorAll('.reply-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            currentCaseMeetingId = btn.getAttribute('data-id');
            const teacherStatement = btn.getAttribute('data-teacher_statement') || '';
            const actionPlan = btn.getAttribute('data-action_plan') || '';
            
            // Populate form fields
            document.getElementById('teacher_statement').value = teacherStatement;
            document.getElementById('action_plan').value = actionPlan;
            
            // Reset button states
            resetButtonStates();
            
            // Check if already replied (has existing data)
            if (teacherStatement || actionPlan) {
                document.getElementById('submitReplyBtn').textContent = 'Update Reply';
            } else {
                document.getElementById('submitReplyBtn').textContent = 'Submit Reply';
            }
        });
    });

    // Handle Submit Reply button click - show confirmation
    document.getElementById('submitReplyBtn').addEventListener('click', function() {
        const teacherStatement = document.getElementById('teacher_statement').value.trim();
        const actionPlan = document.getElementById('action_plan').value.trim();
        
        // Validate required fields
        if (!teacherStatement || !actionPlan) {
            alert('Please fill in both Teacher Statement and Action Plan before submitting.');
            return;
        }
        
        // Populate confirmation modal
        document.getElementById('confirmTeacherStatement').textContent = teacherStatement;
        document.getElementById('confirmActionPlan').textContent = actionPlan;
        
        // Hide reply modal and show confirmation
        const replyModal = bootstrap.Modal.getInstance(document.getElementById('replyModal'));
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        
        replyModal.hide();
        setTimeout(() => {
            confirmationModal.show();
        }, 300);
    });

    // Handle final confirmation submit
    document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
        if (!currentCaseMeetingId) {
            alert('Error: No case meeting ID found. Please try again.');
            return;
        }
        
        // Disable button and show spinner
        const submitBtn = document.getElementById('confirmSubmitBtn');
        const submitBtnText = document.getElementById('submitBtnText');
        const submitSpinner = document.getElementById('submitSpinner');
        
        submitBtn.disabled = true;
        submitBtnText.textContent = 'Submitting...';
        submitSpinner.style.display = 'inline-block';
        
        // Create and submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/teacher/observationreport/reply/' + currentCaseMeetingId;
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        if (csrfToken) {
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrfToken;
            form.appendChild(tokenInput);
        }
        
        // Add form data
        const teacherStatementInput = document.createElement('input');
        teacherStatementInput.type = 'hidden';
        teacherStatementInput.name = 'teacher_statement';
        teacherStatementInput.value = document.getElementById('teacher_statement').value;
        form.appendChild(teacherStatementInput);
        
        const actionPlanInput = document.createElement('input');
        actionPlanInput.type = 'hidden';
        actionPlanInput.name = 'action_plan';
        actionPlanInput.value = document.getElementById('action_plan').value;
        form.appendChild(actionPlanInput);
        
        // Submit form with success handling
        document.body.appendChild(form);
        
        form.submit();
    });

    // Reset button states when modals are hidden
    document.getElementById('confirmationModal').addEventListener('hidden.bs.modal', function() {
        resetButtonStates();
    });
    
    document.getElementById('replyModal').addEventListener('hidden.bs.modal', function() {
        resetButtonStates();
    });
    
    function resetButtonStates() {
        const submitBtn = document.getElementById('confirmSubmitBtn');
        const submitBtnText = document.getElementById('submitBtnText');
        const submitSpinner = document.getElementById('submitSpinner');
        
        submitBtn.disabled = false;
        submitBtnText.textContent = 'Yes, Submit Reply';
        submitSpinner.style.display = 'none';
    }

    // Handle View Report button to show PDF in modal
    document.querySelectorAll('.view-pdf-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const pdfUrl = btn.getAttribute('data-pdf-url');
            document.getElementById('pdfFrame').src = pdfUrl;
        });
    });

    // Clear PDF src when modal is closed (optional, for cleanup)
    const pdfModal = document.getElementById('pdfModal');
    if (pdfModal) {
        pdfModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('pdfFrame').src = '';
        });
    }
    
    // Add search functionality
    const searchInput = document.getElementById('reportSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.report-row');
            
            rows.forEach(function(row) {
                const studentName = row.getAttribute('data-student');
                const violationTitle = row.getAttribute('data-violation');
                
                if (studentName.includes(searchTerm) || violationTitle.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
// ...existing code...
</script>
</x-teacher-layout>
