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
						<p><strong>Scheduled Time:</strong> ${meeting.scheduled_time ? new Date(meeting.scheduled_time).toLocaleTimeString() : 'TBD'}</p>
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
				${meeting.student && meeting.violation_id
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
