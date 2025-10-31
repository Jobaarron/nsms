
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
                            <td>{{ $report->student?->name ?? '-' }}</td>
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
                            <td>{{ $report->reported_by ?? '-' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewModal" 
                                        data-id="{{ $report->id }}" 
                                        title="View Report"
                                    >
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-outline-info" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#replyModal" 
                                        data-id="{{ $report->id }}" 
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('reportSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = searchInput.value.trim().toLowerCase();
            document.querySelectorAll('.report-row').forEach(row => {
                const student = row.dataset.student || '';
                const violation = row.dataset.violation || '';
                row.style.display = (!searchTerm || student.includes(searchTerm) || violation.includes(searchTerm))
                    ? ''
                    : 'none';
            });
        });
    }
});
</script>
</x-teacher-layout>
