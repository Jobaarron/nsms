<x-enrollee-layout>
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="section-title">Enrollment Schedule</h1>
            <div>
                @if($enrollee->preferred_schedule)
                <span class="badge bg-info">
                    Preferred: {{ $enrollee->preferred_schedule->format('M d, Y') }}
                </span>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- ENROLLMENT SCHEDULE STATUS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-calendar-check-line me-2"></i>
                            Schedule Status
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($enrollee->enrollment_status === 'enrolled')
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="ri-checkbox-circle-line me-2"></i>
                                <div>
                                    <strong>Enrollment Complete!</strong>
                                    <br>You have been successfully enrolled. Your class schedule will be available soon.
                                </div>
                            </div>
                        @elseif($enrollee->enrollment_status === 'approved' && $enrollee->is_paid)
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="ri-time-line me-2"></i>
                                <div>
                                    <strong>Ready for Enrollment!</strong>
                                    <br>Your payment has been confirmed. Please wait for the enrollment schedule to be finalized.
                                </div>
                            </div>
                        @elseif($enrollee->enrollment_status === 'approved' && !$enrollee->is_paid)
                            <div class="alert alert-warning d-flex align-items-center">
                                <i class="ri-money-dollar-circle-line me-2"></i>
                                <div>
                                    <strong>Payment Required!</strong>
                                    <br>Please complete your payment to proceed with enrollment scheduling.
                                </div>
                            </div>
                        @elseif($enrollee->enrollment_status === 'pending')
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="ri-hourglass-line me-2"></i>
                                <div>
                                    <strong>Application Under Review</strong>
                                    <br>Schedule information will be available once your application is approved.
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary d-flex align-items-center">
                                <i class="ri-information-line me-2"></i>
                                <div>
                                    <strong>Schedule Not Available</strong>
                                    <br>Enrollment schedule is not available for your current application status.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- PREFERRED SCHEDULE -->
                @if($enrollee->preferred_schedule)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-calendar-line me-2"></i>
                            Your Preferred Schedule
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Preferred Enrollment Date</h6>
                                <p class="fs-5">
                                    <i class="ri-calendar-event-line me-2 text-primary"></i>
                                    {{ $enrollee->preferred_schedule->format('l, F d, Y') }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Status</h6>
                                <p>
                                    @if($enrollee->enrollment_date)
                                        <span class="badge bg-success">
                                            <i class="ri-check-line me-1"></i>
                                            Confirmed for {{ $enrollee->enrollment_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="ri-time-line me-1"></i>
                                            Pending Confirmation
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        @if($enrollee->enrollment_status === 'pending' && !$enrollee->enrollment_date)
                        <div class="mt-3">
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                                <i class="ri-calendar-event-line me-1"></i>
                                Request Reschedule
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- ENROLLMENT TIMELINE -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-time-line me-2"></i>
                            Enrollment Timeline
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item completed">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">Application Submitted</h6>
                                        <p class="text-muted mb-0">Your enrollment application was successfully submitted.</p>
                                    </div>
                                    <small class="text-muted">{{ $enrollee->application_date->format('M d, Y g:i A') }}</small>
                                </div>
                            </div>
                            
                            @if($enrollee->approved_at)
                            <div class="timeline-item completed">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">Application Approved</h6>
                                        <p class="text-muted mb-0">Your application has been reviewed and approved.</p>
                                    </div>
                                    <small class="text-muted">{{ $enrollee->approved_at->format('M d, Y g:i A') }}</small>
                                </div>
                            </div>
                            @endif
                            
                            @if($enrollee->payment_date)
                            <div class="timeline-item completed">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">Payment Completed</h6>
                                        <p class="text-muted mb-0">Enrollment fee payment has been processed.</p>
                                    </div>
                                    <small class="text-muted">{{ $enrollee->payment_date->format('M d, Y g:i A') }}</small>
                                </div>
                            </div>
                            @endif
                            
                            @if($enrollee->enrollment_date)
                            <div class="timeline-item {{ $enrollee->enrolled_at ? 'completed' : '' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">Enrollment Scheduled</h6>
                                        <p class="text-muted mb-0">Your enrollment is scheduled for this date.</p>
                                    </div>
                                    <small class="text-muted">{{ $enrollee->enrollment_date->format('M d, Y') }}</small>
                                </div>
                            </div>
                            @endif
                            
                            @if($enrollee->enrolled_at)
                            <div class="timeline-item completed">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">Successfully Enrolled</h6>
                                        <p class="text-muted mb-0">Welcome to NSMS! Your enrollment is now complete.</p>
                                    </div>
                                    <small class="text-muted">{{ $enrollee->enrolled_at->format('M d, Y g:i A') }}</small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- APPOINTMENT SCHEDULING -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-calendar-schedule-line me-2"></i>
                            Schedule an Appointment
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($enrollee->enrollment_status === 'pending')
                            <div class="alert alert-info mb-3">
                                <i class="ri-information-line me-2"></i>
                                <strong>Schedule a consultation</strong> with our admission counselor to discuss your application and requirements.
                            </div>
                        @elseif($enrollee->enrollment_status === 'approved')
                            <div class="alert alert-success mb-3">
                                <i class="ri-information-line me-2"></i>
                                <strong>Schedule an enrollment appointment</strong> to complete your registration and receive your class schedule.
                            </div>
                        @else
                            <div class="alert alert-secondary mb-3">
                                <i class="ri-information-line me-2"></i>
                                <strong>Appointment scheduling</strong> is available based on your enrollment status.
                            </div>
                        @endif

                        <form id="appointmentForm" method="POST" action="{{ route('enrollee.appointment.request') }}">
                            @csrf
                            <div class="row">
                                {{-- <div class="col-md-6 mb-3">
                                    <label for="appointment_type" class="form-label">Appointment Type</label>
                                    <select class="form-select @error('appointment_type') is-invalid @enderror" id="appointment_type" name="appointment_type" required>
                                        <option value="">Select appointment type</option>
                                        @if($enrollee->enrollment_status === 'pending')
                                            <option value="consultation">Admission Consultation</option>
                                            <option value="document_review">Document Review</option>
                                            <option value="requirements_clarification">Requirements Clarification</option>
                                        @elseif($enrollee->enrollment_status === 'approved')
                                            <option value="enrollment">Enrollment Appointment</option>
                                            <option value="payment_assistance">Payment Assistance</option>
                                            <option value="schedule_planning">Schedule Planning</option>
                                        @else
                                            <option value="general_inquiry">General Inquiry</option>
                                        @endif
                                    </select>
                                    @error('appointment_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div> --}}
                                <div class="col-md-6 mb-3">
                                    <label for="preferred_date" class="form-label">Preferred Date</label>
                                    <input type="date" class="form-control @error('preferred_date') is-invalid @enderror" 
                                           id="preferred_date" name="preferred_date" 
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                           max="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                                    @error('preferred_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="preferred_time" class="form-label">Preferred Time</label>
                                    <input type="time" class="form-control @error('preferred_time') is-invalid @enderror" 
                                           id="preferred_time" name="preferred_time" 
                                           min="08:00" max="17:00" required>
                                    <div class="form-text">Office hours: 8:00 AM - 5:00 PM</div>
                                    @error('preferred_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- <div class="col-md-6 mb-3">
                                    <label for="contact_method" class="form-label">Preferred Contact Method</label>
                                    <select class="form-select @error('contact_method') is-invalid @enderror" id="contact_method" name="contact_method" required>
                                        <option value="">Select contact method</option>
                                        <option value="phone">Phone Call</option>
                                        <option value="email">Email</option>
                                        <option value="in_person">In-Person Visit</option>
                                    </select>
                                    @error('contact_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div> --}}
                            </div>
                            <div class="mb-3">
                                <label for="appointment_notes" class="form-label">Additional Notes (Optional)</label>
                                <textarea class="form-control @error('appointment_notes') is-invalid @enderror" 
                                          id="appointment_notes" name="appointment_notes" rows="3" 
                                          placeholder="Please provide any specific topics you'd like to discuss or questions you have..."></textarea>
                                @error('appointment_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="ri-time-line me-1"></i>
                                    Appointments are typically scheduled within 24-48 hours
                                </small>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-calendar-check-line me-1"></i>
                                    Request Appointment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- IMPORTANT DATES -->
                {{-- <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-calendar-todo-line me-2"></i>
                            Important Dates for {{ $enrollee->academic_year }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <i class="ri-file-text-line me-2"></i>
                                            Enrollment Period Opens
                                        </td>
                                        <td>June 1, 2024</td>
                                        <td><span class="badge bg-success">Completed</span></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="ri-file-text-line me-2"></i>
                                            Enrollment Period Closes
                                        </td>
                                        <td>July 31, 2024</td>
                                        <td><span class="badge bg-warning">Ongoing</span></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="ri-calendar-check-line me-2"></i>
                                            Class Orientation
                                        </td>
                                        <td>August 15, 2024</td>
                                        <td><span class="badge bg-secondary">Upcoming</span></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="ri-book-open-line me-2"></i>
                                            First Day of Classes
                                        </td>
                                        <td>August 19, 2024</td>
                                        <td><span class="badge bg-secondary">Upcoming</span></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="ri-calendar-event-line me-2"></i>
                                            First Quarter Ends
                                        </td>
                                        <td>October 18, 2024</td>
                                        <td><span class="badge bg-secondary">Upcoming</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> --}}
            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-4">
                <!-- QUICK INFO -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-information-line me-2"></i>
                            Quick Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-6">Application ID</dt>
                            <dd class="col-6">{{ $enrollee->application_id }}</dd>
                            <dt class="col-6">Grade Level</dt>
                            <dd class="col-6">{{ $enrollee->grade_level_applied }}</dd>
                            @if($enrollee->strand_applied)
                            <dt class="col-6">Strand</dt>
                            <dd class="col-6">{{ $enrollee->strand_applied }}</dd>
                            @endif
                            <dt class="col-6">Academic Year</dt>
                            <dd class="col-6">{{ $enrollee->academic_year }}</dd>
                            <dt class="col-6">Student Type</dt>
                            <dd class="col-6">{{ ucfirst($enrollee->student_type) }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- ENROLLMENT REQUIREMENTS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-task-line me-2"></i>
                            Enrollment Checklist
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>
                                    <i class="ri-{{ $enrollee->enrollment_status !== 'pending' ? 'check' : 'time' }}-line me-2 text-{{ $enrollee->enrollment_status !== 'pending' ? 'success' : 'warning' }}"></i>
                                    Application Review
                                </span>
                                <span class="badge bg-{{ $enrollee->enrollment_status !== 'pending' ? 'success' : 'warning' }}">
                                    {{ $enrollee->enrollment_status !== 'pending' ? 'Done' : 'Pending' }}
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>
                                    <i class="ri-{{ $enrollee->is_paid ? 'check' : 'time' }}-line me-2 text-{{ $enrollee->is_paid ? 'success' : 'warning' }}"></i>
                                    Payment
                                </span>
                                <span class="badge bg-{{ $enrollee->is_paid ? 'success' : 'warning' }}">
                                    {{ $enrollee->is_paid ? 'Done' : 'Pending' }}
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>
                                    <i class="ri-{{ (is_array($enrollee->documents) && count($enrollee->documents) > 0) ? 'check' : 'time' }}-line me-2 text-{{ (is_array($enrollee->documents) && count($enrollee->documents) > 0) ? 'success' : 'warning' }}"></i>
                                    Documents
                                </span>
                                <span class="badge bg-{{ (is_array($enrollee->documents) && count($enrollee->documents) > 0) ? 'success' : 'warning' }}">
                                    {{ (is_array($enrollee->documents) && count($enrollee->documents) > 0) ? 'Submitted' : 'Pending' }}
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>
                                    <i class="ri-{{ $enrollee->enrollment_status === 'enrolled' ? 'check' : 'time' }}-line me-2 text-{{ $enrollee->enrollment_status === 'enrolled' ? 'success' : 'warning' }}"></i>
                                    Pre-Registration
                                </span>
                                <span class="badge bg-{{ $enrollee->enrollment_status === 'enrolled' ? 'success' : 'warning' }}">
                                    {{ $enrollee->enrollment_status === 'enrolled' ? 'Done' : 'Pending' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTACT INFORMATION -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-customer-service-line me-2"></i>
                            Need Assistance?
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">For questions about your enrollment schedule:</p>
                        <div class="d-grid gap-2">
                            <a href="tel:+1234567890" class="btn btn-outline-primary btn-sm disabled">
                                <i class="ri-phone-line me-1"></i>
                                Call Registrar
                            </a>
                            <a href="mailto:registrar@nsms.edu" class="btn btn-outline-primary btn-sm disabled">
                                <i class="ri-mail-line me-1"></i>
                                Email Registrar
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm disabled">
                                <i class="ri-map-pin-line me-1"></i>
                                Visit Office
                            </a>
                        </div>
                        
                        <hr>
                        <small class="text-muted">
                            <strong>Office Hours:</strong><br>
                            Monday - Friday: 8:00 AM - 5:00 PM<br>
                            Saturday: 8:00 AM - 12:00 PM
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reschedule Modal -->
    @if($enrollee->enrollment_status === 'pending' && !$enrollee->enrollment_date)
    <div class="modal fade" id="rescheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-calendar-event-line me-2"></i>
                        Request Schedule Change
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="rescheduleForm" method="POST" action="{{ route('enrollee.schedule.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            You can request to change your preferred enrollment schedule. The new schedule is subject to availability and approval.
                        </div>

                        <div class="mb-3">
                            <label for="current_schedule" class="form-label">Current Preferred Schedule</label>
                            <input type="text" class="form-control" id="current_schedule" value="{{ $enrollee->preferred_schedule?->format('F d, Y') ?? 'Not set' }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="new_preferred_schedule" class="form-label">New Preferred Schedule</label>
                            <input type="date" class="form-control" id="new_preferred_schedule" name="preferred_schedule" min="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="reschedule_reason" class="form-label">Reason for Change</label>
                            <textarea class="form-control" id="reschedule_reason" name="reason" rows="3" placeholder="Please explain why you need to change your schedule" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-calendar-event-line me-1"></i>
                            Request Change
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</x-enrollee-layout>
