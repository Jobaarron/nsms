<x-admin-layout>
<div class="container mt-4">
    <h1>Forwarded Case Meetings</h1>

    @if($caseMeetings->count() > 0)
    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>Student</th>
                <th>Offense</th>
                <th>Scheduled Date</th>
                <th>Scheduled Time</th>

                <th>Status</th>
                <th>Sanctions</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($caseMeetings as $meeting)
            <tr>
                <td>{{ $meeting->student ? $meeting->student->full_name : 'Unknown' }}</td>
                <td>
                    @if($meeting->sanctions->count() > 0 && $meeting->sanctions->first()->violation)
                        {{ $meeting->sanctions->first()->violation->title ?: $meeting->sanctions->first()->violation->description }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>{{ $meeting->scheduled_date->format('Y-m-d') }}</td>
                <td>{{ $meeting->scheduled_time->format('H:i') }}</td>

                <td><span class="badge bg-warning">{{ ucfirst($meeting->status) }}</span></td>
                <td>
                    <ul>
                        @foreach($meeting->sanctions as $sanction)
                        <li>
                            {{ $sanction->sanction }}
                            @if($sanction->is_approved)
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    <div class="btn-group-vertical btn-group-sm">
                        <!-- View Summary Button - Always Available -->
                        <button class="btn btn-outline-info btn-sm view-summary-btn" data-meeting-id="{{ $meeting->id }}" title="View Summary Report" data-bs-toggle="modal" data-bs-target="#viewSummaryModal">
                            <i class="ri-file-text-line"></i> View Summary
                        </button>

                        @foreach($meeting->sanctions as $sanction)
                            @if(!$sanction->is_approved)
                                <!-- Approve Button -->
                                <button class="btn btn-outline-success btn-sm approve-sanction-btn" data-sanction-id="{{ $sanction->id }}" title="Approve Sanction">
                                    <i class="ri-check-line"></i> Approve
                                </button>

                                <!-- Reject Button -->
                                <button class="btn btn-outline-danger btn-sm reject-sanction-btn" data-sanction-id="{{ $sanction->id }}" title="Reject Sanction">
                                    <i class="ri-close-line"></i> Reject
                                </button>

                                <!-- Revise Button -->
                                <button class="btn btn-outline-warning btn-sm revise-sanction-btn" data-sanction-id="{{ $sanction->id }}" title="Revise Sanction" data-bs-toggle="modal" data-bs-target="#reviseSanctionModal" data-sanction="{{ $sanction->sanction }}" data-notes="{{ $sanction->notes }}">
                                    <i class="ri-edit-line"></i> Revise
                                </button>
                            @else
                                <!-- Approved Status -->
                                <span class="badge bg-success">Approved</span>
                            @endif
                        @endforeach
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $caseMeetings->links() }}

    @else
    <p>No forwarded case meetings found.</p>
    @endif

    <!-- View Summary Modal -->
    <div class="modal fade" id="viewSummaryModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Case Meeting Summary Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="summaryModalBody">
                    <!-- Content will be loaded dynamically -->
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Revise Sanction Modal -->
    <div class="modal fade" id="reviseSanctionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Revise Sanction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="reviseSanctionForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="revise-sanction-text" class="form-label">Sanction <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="revise-sanction-text" name="sanction" rows="4" required placeholder="Enter the revised sanction..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="revise-sanction-notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="revise-sanction-notes" name="notes" rows="3" placeholder="Any additional notes for this revision..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="ri-edit-line me-2"></i>Revise Sanction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // View summary buttons
    const viewSummaryButtons = document.querySelectorAll('.view-summary-btn');
    viewSummaryButtons.forEach(button => {
        button.addEventListener('click', function () {
            const meetingId = this.getAttribute('data-meeting-id');
            loadSummaryReport(meetingId);
        });
    });

    // Approve sanction buttons
    const approveButtons = document.querySelectorAll('.approve-sanction-btn');
    approveButtons.forEach(button => {
        button.addEventListener('click', function () {
            const sanctionId = this.getAttribute('data-sanction-id');
            if (confirm('Are you sure you want to approve this sanction? This will mark the case meeting as completed.')) {
                fetch(`/admin/sanctions/${sanctionId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('An error occurred while approving the sanction.');
                    console.error(error);
                });
            }
        });
    });

    // Reject sanction buttons
    const rejectButtons = document.querySelectorAll('.reject-sanction-btn');
    rejectButtons.forEach(button => {
        button.addEventListener('click', function () {
            const sanctionId = this.getAttribute('data-sanction-id');
            if (confirm('Are you sure you want to reject this sanction? This action cannot be undone.')) {
                fetch(`/admin/sanctions/${sanctionId}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('An error occurred while rejecting the sanction.');
                    console.error(error);
                });
            }
        });
    });

    // Revise sanction buttons
    const reviseButtons = document.querySelectorAll('.revise-sanction-btn');
    reviseButtons.forEach(button => {
        button.addEventListener('click', function () {
            const sanctionId = this.getAttribute('data-sanction-id');
            const sanctionText = this.getAttribute('data-sanction');
            const notesText = this.getAttribute('data-notes');

            // Populate modal with existing data
            document.getElementById('revise-sanction-text').value = sanctionText || '';
            document.getElementById('revise-sanction-notes').value = notesText || '';

            // Store sanction ID for form submission
            document.getElementById('reviseSanctionForm').setAttribute('data-sanction-id', sanctionId);
        });
    });

    // Handle revise sanction form submission
    document.getElementById('reviseSanctionForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const sanctionId = this.getAttribute('data-sanction-id');
        const formData = new FormData(this);

        fetch(`/admin/sanctions/${sanctionId}/revise`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                // Close modal and reload page
                const modal = bootstrap.Modal.getInstance(document.getElementById('reviseSanctionModal'));
                modal.hide();
                location.reload();
            }
        })
        .catch(error => {
            alert('An error occurred while revising the sanction.');
            console.error(error);
        });
    });
});

// Function to load summary report
function loadSummaryReport(meetingId) {
    const modalBody = document.getElementById('summaryModalBody');

    // Show loading spinner
    modalBody.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    fetch(`/admin/case-meetings/${meetingId}/summary`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const meeting = data.meeting;
            modalBody.innerHTML = generateSummaryHTML(meeting);
        } else {
            modalBody.innerHTML = '<div class="alert alert-danger">Failed to load summary report.</div>';
        }
    })
    .catch(error => {
        modalBody.innerHTML = '<div class="alert alert-danger">An error occurred while loading the summary report.</div>';
        console.error(error);
    });
}

// Function to generate HTML for summary report
function generateSummaryHTML(meeting) {
    let html = '';

    // Student Information
    html += `
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Student Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> ${meeting.student ? meeting.student.full_name : 'Unknown'}</p>
                        <p><strong>Student ID:</strong> ${meeting.student ? meeting.student.student_id : 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Grade Level:</strong> ${meeting.student ? meeting.student.grade_level : 'N/A'}</p>
                        <p><strong>Section:</strong> ${meeting.student ? meeting.student.section : 'N/A'}</p>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Meeting Details
    html += `
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Meeting Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Meeting Type:</strong> ${meeting.meeting_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</p>
                        <p><strong>Scheduled Date:</strong> ${meeting.scheduled_date ? new Date(meeting.scheduled_date).toLocaleDateString() : 'TBD'}</p>
                        <p><strong>Scheduled Time:</strong> ${meeting.scheduled_time ? new Date(meeting.scheduled_time).toLocaleTimeString() : 'TBD'}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Location:</strong> ${meeting.location || 'TBD'}</p>
                        <p><strong>Counselor:</strong> ${meeting.counselor ? meeting.counselor.full_name : 'Unassigned'}</p>

                    </div>
                </div>
            </div>
        </div>
    `;

    // Case Summary
    if (meeting.summary) {
        html += `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Case Summary</h6>
                </div>
                <div class="card-body">
                    <p>${meeting.summary.replace(/\n/g, '<br>')}</p>
                </div>
            </div>
        `;
    }

    // Recommendations
    if (meeting.recommendations) {
        html += `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Recommendations</h6>
                </div>
                <div class="card-body">
                    <p>${meeting.recommendations.replace(/\n/g, '<br>')}</p>
                </div>
            </div>
        `;
    }

    // Sanctions
    if (meeting.sanctions && meeting.sanctions.length > 0) {
        html += `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Sanctions</h6>
                </div>
                <div class="card-body">
        `;

        meeting.sanctions.forEach(sanction => {
            html += `
                <div class="border rounded p-3 mb-3">
                    <div class="row">
                        <div class="col-md-8">
                            <h6>Sanction Details</h6>
                            <p><strong>Sanction:</strong> ${sanction.sanction}</p>
                            ${sanction.deportment_grade_action ? `<p><strong>Deportment Grade Action:</strong> ${sanction.deportment_grade_action}</p>` : ''}
                            ${sanction.suspension ? `<p><strong>Suspension:</strong> ${sanction.suspension}</p>` : ''}
                            ${sanction.notes ? `<p><strong>Notes:</strong> ${sanction.notes.replace(/\n/g, '<br>')}</p>` : ''}
                        </div>
                        <div class="col-md-4">
                            <h6>Status</h6>
                            ${sanction.is_approved ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-warning">Pending</span>'}
                            ${sanction.approved_at ? `<p class="small text-muted mt-1">Approved on ${new Date(sanction.approved_at).toLocaleString()}</p>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;
    }

    // Reason for Meeting
    if (meeting.reason) {
        html += `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Reason for Meeting</h6>
                </div>
                <div class="card-body">
                    <p>${meeting.reason.replace(/\n/g, '<br>')}</p>
                </div>
            </div>
        `;
    }

    // Additional Notes
    if (meeting.notes) {
        html += `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Additional Notes</h6>
                </div>
                <div class="card-body">
                    <p>${meeting.notes.replace(/\n/g, '<br>')}</p>
                </div>
            </div>
        `;
    }

    // President Notes
    if (meeting.president_notes) {
        html += `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">President Notes</h6>
                </div>
                <div class="card-body">
                    <p>${meeting.president_notes.replace(/\n/g, '<br>')}</p>
                </div>
            </div>
        `;
    }

    return html;
}
</script>
</x-admin-layout>
