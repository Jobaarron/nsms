
<x-teacher-layout>
@vite(['resources/css/student_violations.css'])

<div class="container-fluid py-4">
    <h2 class="mb-4" style="color:#198754;">Observation Reports</h2>

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
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Reply button click to populate modal and set form action
    document.querySelectorAll('.reply-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const caseMeetingId = btn.getAttribute('data-id');
            const teacherStatement = btn.getAttribute('data-teacher_statement') || '';
            const actionPlan = btn.getAttribute('data-action_plan') || '';
            document.getElementById('teacher_statement').value = teacherStatement;
            document.getElementById('action_plan').value = actionPlan;
            document.getElementById('replyForm').action = '/teacher/observationreport/reply/' + caseMeetingId;
        });
    });

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
});
</script>
// ...existing code...
</script>
</x-teacher-layout>
