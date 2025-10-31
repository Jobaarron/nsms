
<x-admin-layout>
<div class="container-fluid">
    <div class="card" style="margin-bottom:0;box-shadow:none;background:#fff;">
        <div class="card-header bg-success text-white d-flex align-items-center" style="border-radius: 1rem 1rem 0 0;">
            <i class="ri-file-text-line me-2" style="font-size:1.5rem;"></i>
            <h4 class="mb-0 fw-bold">Forwarded Case Meetings</h4>
        </div>
    <div class="card-body p-0" style="padding-bottom:0;">
            @if($caseMeetings->count() > 0)
            <div class="table-responsive">
                <table class="table mb-0" style="border-collapse:separate;border-spacing:0 0.5rem;">
                    <thead class="table-light">
                        <tr style="border-bottom:2px solid #43b36a;">
                            <th>Student</th>
                            <th>Offense</th>
                            <th>Date</th>
                            <th>Sanction</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($caseMeetings as $meeting)
                        <tr style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(67,179,106,0.07);">
                            <!-- Student cell: Name (bold), ID (muted, small, below) -->
                            <td style="min-width:180px;">
                                <div class="fw-bold">{{ $meeting->student ? $meeting->student->full_name : 'Unknown' }}</div>
                                <div class="text-muted small">{{ $meeting->student ? $meeting->student->student_id : '' }}</div>
                            </td>
                            <!-- Offense cell: Title (bold), desc (muted, small, below) -->
                            <td style="min-width:220px;">
                                @if($meeting->sanctions->count() > 0 && $meeting->sanctions->first()->violation)
                                    <div class="fw-bold">{{ $meeting->sanctions->first()->violation->title }}</div>
                                    <div class="text-muted small">{{ $meeting->sanctions->first()->violation->description }}</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <!-- Date cell: Date (bold), time (muted, small, below) -->
                            <td style="min-width:120px;">
                                <div class="fw-bold">{{ $meeting->scheduled_date->format('M d, Y') }}</div>
                                <div class="text-muted small">{{ $meeting->scheduled_time ? $meeting->scheduled_time->format('h:i A') : '' }}</div>
                            </td>
                            <!-- Sanction cell: Main sanction (bold), others (muted, small, below) -->
                            <td style="min-width:220px;">
                                @if($meeting->sanctions->count() > 0)
                                    <div class="fw-bold">{{ $meeting->sanctions->first()->sanction }}</div>
                                    @if($meeting->sanctions->count() > 1)
                                        <div class="text-muted small">
                                            @foreach($meeting->sanctions->slice(1) as $sanction)
                                                {{ $sanction->sanction }}@if(!$loop->last), @endif
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <!-- Status cell -->
                            <td style="min-width:120px;">
                                <span class="badge bg-warning text-white" style="font-weight:600;">{{ $meeting->status === 'forwarded' ? 'Submitted' : ucfirst($meeting->status) }}</span>
                            </td>
                            <!-- Actions cell: icon buttons, horizontally aligned -->
                            <td style="min-width:160px;">
                                <div class="d-flex gap-2 align-items-center">
                                    <button class="btn btn-outline-info btn-sm view-summary-btn" data-meeting-id="{{ $meeting->id }}" title="View Summary Report" data-bs-toggle="modal" data-bs-target="#viewSummaryModal">
                                        <i class="ri-file-text-line"></i>
                                    </button>
                                    @foreach($meeting->sanctions as $sanction)
                                        @if(!$sanction->is_approved)
                                            <button class="btn btn-outline-success btn-sm approve-sanction-btn" data-sanction-id="{{ $sanction->id }}" title="Approve Sanction">
                                                <i class="ri-check-line"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm reject-sanction-btn" data-sanction-id="{{ $sanction->id }}" title="Reject Sanction">
                                                <i class="ri-close-line"></i>
                                            </button>
                                            <button class="btn btn-outline-warning btn-sm revise-sanction-btn" data-sanction-id="{{ $sanction->id }}" title="Revise Sanction" data-bs-toggle="modal" data-bs-target="#reviseSanctionModal" data-sanction="{{ $sanction->sanction }}" data-notes="{{ $sanction->notes }}">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 mb-0" style="margin-bottom:0;">{{ $caseMeetings->links() }}</div>
            @else
            <p class="text-center text-muted my-4 mb-0" style="margin-bottom:0;">No forwarded case meetings found.</p>
            @endif
        </div>
    </div>

    <!-- View Summary Modal -->
    <!-- ...existing modal and script code... -->
    {{-- Filter/Search Bar removed --}}
</div>


    <!-- View Summary Modal (restored for summary JS compatibility) -->
    <div class="modal fade" id="viewSummaryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content minimalist-modal">
                <div class="modal-header border-0 pb-0" style="background:transparent;">
                    <h5 class="modal-title fw-semibold" style="letter-spacing:0.5px;">Case Meeting Summary Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="summaryModalBody" style="background:transparent;">
                    <!-- Content will be loaded dynamically -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0" style="background:transparent;">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius:6px;box-shadow:none;">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Incident Form PDF Preview Modal (optional, for future use) -->
    <div class="modal fade" id="incidentFormPdfPreviewModal" tabindex="-1" aria-labelledby="incidentFormPdfPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content minimalist-modal">
                <div class="modal-header border-0 pb-0" style="background:transparent;">
                    <h5 class="modal-title fw-semibold" id="incidentFormPdfPreviewModalLabel" style="letter-spacing:0.5px;">Incident Form PDF Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="background: #fff; min-height: 600px;">
                    <iframe id="incidentFormPdfIframe" src="" width="100%" height="600px" style="border: none;"></iframe>
                </div>
                <div class="modal-footer border-0 pt-0" style="background:transparent;">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius:6px;box-shadow:none;">Close</button>
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
    // Minimalist modal/card hover CSS
    const style = document.createElement('style');
    style.innerHTML = `
        .minimalist-modal {
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,60,60,0.10);
            border: none;
            background: #fff;
            transition: box-shadow 0.2s;
        }
        .minimalist-modal .card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 12px 0 rgba(67,179,106,0.06);
            margin-bottom: 1.2rem;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .minimalist-modal .card:hover {
            box-shadow: 0 6px 24px 0 rgba(67,179,106,0.13);
            transform: translateY(-2px) scale(1.01);
        }
        .minimalist-modal .card-header {
            background: transparent;
            border-bottom: none;
            font-weight: 600;
            font-size: 1.08rem;
            padding-bottom: 0.5rem;
        }
        .minimalist-modal .card-body {
            padding-top: 0.5rem;
        }
        .minimalist-modal .btn-outline-primary.btn-sm {
            border-radius: 6px;
            padding: 0.25rem 0.75rem;
            background: #fff;
            color: #198754;
            border-color: #198754;
            transition: background 0.15s, color 0.15s, border 0.15s;
        }
        .minimalist-modal .btn-outline-primary.btn-sm:hover,
        .minimalist-modal .btn-outline-primary.btn-sm:focus,
        .minimalist-modal .btn-outline-primary.btn-sm:active {
            background: #198754;
            color: #fff;
            border-color: #198754;
        }
    `;
    document.head.appendChild(style);
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
                        <p><strong>Scheduled Time:</strong> ${meeting.scheduled_time ? meeting.scheduled_time : 'TBD'}</p>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Reports Card (PDF Attachment)
    html += `
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Reports</h6>
            </div>
            <div class="card-body">
                ${meeting.student && meeting.violation_id && (meeting.student_statement || meeting.incident_feelings || meeting.action_plan)
                    ? `<a href="/narrative-report/view/${meeting.student.id}/${meeting.violation_id}" target="_blank" class="btn btn-outline-primary btn-sm minimalist-attachment-btn"><i class="ri-attachment-2"></i> Student Narrative PDF</a>`
                    : '<span class="text-muted small">No Attachment</span>'}
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
