<x-student-layout>
      <main class="col-12 col-md-10 px-4 py-4">
        <h1 class="section-title">Welcome, {{ $student->first_name ?? 'Student' }}</h1>
        @if(!$student->canAccessFeatures())
            <div class="alert alert-warning d-flex align-items-center mb-4">
                <i class="ri-alert-line me-2"></i>
                <div>
                    <strong>Payment Required!</strong> 
                    You need to complete your payment to access all features. Current status: <strong>{{ $student->paymentStatus }}</strong>
                </div>
            </div>
        @endif

        <!-- SUMMARY CARDS -->
        <div class="row g-3 mb-5">
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-paid h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-checkbox-circle-line display-6 me-3"></i>
                <div>
                  <div>Payment Status</div>
                  <h3>{{ $student->paymentStatus }}</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-credits h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-bar-chart-line display-6 me-3"></i>
                <div>
                  <div>To be use soon</div>
                  <h3>Value</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-subjects h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-book-2-line display-6 me-3"></i>
                <div>
                  <div>Active Subjects</div>
                  <h3>2</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-gpa h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-star-line display-6 me-3"></i>
                <div>
                  <div>To be use soon</div>
                  <h3>Value</h3>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- PAYMENT HISTORY -->
        <h4 class="section-title">Payment History</h4>
        <div class="table-responsive mb-5">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Jul 01, 2024</td>
                <td>$1,200.00</td>
                <td>Tuition</td>
                <td><span class="badge bg-success">Paid</span></td>
              </tr>
              <tr>
                <td>Jan 15, 2024</td>
                <td>$1,200.00</td>
                <td>Tuition</td>
                <td><span class="badge bg-success">Paid</span></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- ACTIVE SUBJECTS -->
        <h4 class="section-title">My Subjects</h4>
        <div class="table-responsive mb-5">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Code</th>
                <th>Subject</th>
                <th>Instructor</th>
                <th>Schedule</th>
                <th>Room</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>BIO101</td>
                <td>Biology 101</td>
                <td>Dr. Smith</td>
                <td>Mon/Wed 9:00–10:30</td>
                <td>Room 202</td>
              </tr>
              <tr>
                <td>ENG201</td>
                <td>English Lit</td>
                <td>Prof. Lee</td>
                <td>Tue/Thu 11:00–12:30</td>
                <td>Room 105</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- GUIDANCE & DISCIPLINE -->
        {{-- <h4 class="section-title">Guidance & Discipline</h4>
        <div class="row mb-5">
          <div class="col-md-6">
            <h6 class="fw-bold">Guidance Notes</h6>
            <ul class="list-group">
              <li class="list-group-item">
                <strong>Jun 20:</strong> Met with counselor regarding course load.
              </li>
              <li class="list-group-item">
                <strong>May 05:</strong> Guidance on academic planning.
              </li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6 class="fw-bold">Discipline Records</h6>
            <ul class="list-group">
              <li class="list-group-item">
                <strong>Feb 14:</strong> Late to class 
                <span class="badge bg-warning text-dark float-end">Minor</span>
              </li>
              <li class="list-group-item">
                <strong>Jan 22:</strong> Unexcused absence 
                <span class="badge bg-danger float-end">Major</span>
              </li>
            </ul>
          </div>
        </div> --}}

        <!-- PROFILE -->
        <h4 class="section-title">My Profile</h4>
        <div class="card mb-5">
          <div class="card-body">
            <dl class="row">
              <dt class="col-sm-3">LRN</dt>
              <dd class="col-sm-9">{{ $student->student_id ?? $student->lrn ?? 'STU-' . str_pad($student->id, 6, '0', STR_PAD_LEFT) }}</dd>
              <dt class="col-sm-3">Name</dt>
              <dd class="col-sm-9">{{ $student->full_name }}</dd>
              {{-- <dt class="col-sm-3">Student Type</dt>
              <dd class="col-sm-9">{{ ucfirst($student->student_type) }}</dd> --}}
              <dt class="col-sm-3">Grade Level</dt>
              <dd class="col-sm-9">{{ $student->grade_level }}{{ $student->strand ? ' - ' . $student->strand : '' }}</dd>
              <dt class="col-sm-3">Email</dt>
              <dd class="col-sm-9">{{ $student->email }}</dd>
              <dt class="col-sm-3">Contact Number</dt>
              <dd class="col-sm-9">{{ $student->contact_number ?? 'Not provided' }}</dd>
              <dt class="col-sm-3">Address</dt>
              <dd class="col-sm-9">{{ $student->address }}, {{ $student->city }}, {{ $student->province }} {{ $student->zip_code }}</dd>
              <dt class="col-sm-3">Guardian</dt>
              <dd class="col-sm-9">{{ $student->guardian_name }} ({{ $student->guardian_contact }})</dd>
              <dt class="col-sm-3">Enrollment Status</dt>
              <dd class="col-sm-9">
                <span class="badge bg-{{ $student->enrollment_status === 'enrolled' ? 'success' : ($student->enrollment_status === 'pending' ? 'warning' : 'secondary') }}">
                  {{ ucfirst($student->enrollment_status) }}
                </span>
              </dd>
              <dt class="col-sm-3">Payment Mode</dt>
              <dd class="col-sm-9">{{ ucfirst($student->payment_mode) }}</dd>
            </dl>
            @if($student->canAccessFeatures())
              <button class="btn btn-outline-primary">
                <i class="ri-edit-line me-1"></i>Edit Profile
              </button>
            @else
              <button class="btn btn-outline-secondary" disabled title="Payment required to edit profile">
                <i class="ri-edit-line me-1"></i>Edit Profile
                <span class="badge bg-warning text-dark ms-2">Pay First</span>
              </button>
            @endif
          </div>
        </div>

        <!-- QUICK ACTIONS -->
        <h4 class="section-title">Quick Actions</h4>
        <div class="row g-3">
          <div class="col-md-4">
            @if($student->canAccessFeatures())
              <button class="btn btn-outline-primary w-100 py-3">
                <i class="ri-wallet-line me-2"></i>Make Payment
              </button>
            @else
              <button class="btn btn-outline-secondary w-100 py-3" disabled title="Payment required to access this feature">
                <i class="ri-wallet-line me-2"></i>Make Payment
                <span class="badge bg-warning text-dark ms-2">Pay First</span>
              </button>
            @endif
          </div>
          <div class="col-md-4">
            @if($student->canAccessFeatures())
              <button class="btn btn-outline-primary w-100 py-3">
                <i class="ri-calendar-line me-2"></i>View Schedule
              </button>
            @else
              <button class="btn btn-outline-secondary w-100 py-3" disabled title="Payment required to access this feature">
                <i class="ri-calendar-line me-2"></i>View Schedule
                <span class="badge bg-warning text-dark ms-2">Pay First</span>
              </button>
            @endif
          </div>
          <div class="col-md-4">
            @if($student->canAccessFeatures())
              <button class="btn btn-outline-primary w-100 py-3">
                <i class="ri-chat-1-line me-2"></i>Message Counselor
              </button>
            @else
              <button class="btn btn-outline-secondary w-100 py-3" disabled title="Payment required to access this feature">
                <i class="ri-chat-1-line me-2"></i>Message Counselor
                <span class="badge bg-warning text-dark ms-2">Pay First</span>
              </button>
            @endif
          </div>
        </div>
      </main>
    </div>
  </div>

</x-student-layout>