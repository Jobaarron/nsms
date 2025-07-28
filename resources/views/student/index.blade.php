<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>

  <title>Student Dashboard • NSMS</title>

  <!-- Remix Icons -->
  <link 
    href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" 
    rel="stylesheet"
  />

  <!-- Google Font -->
  <link 
    href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" 
    rel="stylesheet"
  />

  <!-- App CSS (includes Bootstrap 5 via Vite) -->
  @vite('resources/sass/app.scss')

  <style>
    /* Color Palette */
    :root {
      --primary-color: #014421;
      --secondary-color: #D0D8C3;
      --accent-color: #2d6a3e;
      --light-green: #e8f5e8;
      --dark-green: #012d17;
    }
    body {
      font-family: 'Nunito', sans-serif;
      background-color: var(--light-green);
    }
    /* Sidebar */
    .sidebar {
      background-color: var(--secondary-color);
      min-height: 100vh;
    }
    .sidebar .nav-link {
      color: var(--primary-color);
      font-weight: 600;
      padding: 0.75rem 1rem;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background-color: var(--accent-color);
      color: #fff;
      border-radius: 0.25rem;
    }
    /* Section Titles */
    .section-title {
      color: var(--primary-color);
      font-weight: 700;
      margin-bottom: 1rem;
    }
    /* Summary Cards */
    .card-summary { color: #fff; }
    .card-paid     { background-color: var(--primary-color); }
    .card-credits  { background-color: var(--accent-color); }
    .card-subjects { background-color: var(--dark-green); }
    .card-gpa      { background-color: var(--secondary-color); color: var(--dark-green); }
    /* Table Head */
    .table thead {
      background-color: var(--primary-color);
      color: #fff;
    }
    /* Buttons */
    .btn-outline-primary {
      color: var(--primary-color);
      border-color: var(--primary-color);
    }
    .btn-outline-primary:hover {
      background-color: var(--primary-color);
      color: #fff;
    }

    .sidebar .nav-link.disabled {
  color: var(--secondary-color) !important;
  opacity: 0.6;
  cursor: not-allowed;
  pointer-events: none;
}

.sidebar .nav-link.disabled:hover {
  background-color: transparent !important;
  color: var(--secondary-color) !important;
}

.sidebar .nav-link.disabled i {
  opacity: 0.5;
}
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">

      <!-- SIDEBAR -->
      <nav class="col-12 col-md-2 sidebar d-none d-md-block py-4">
        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link active" href="#">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          {{-- <li class="nav-item mb-2">
            @if($student->canAccessFeatures())
              <a class="nav-link" href="#">
                <i class="ri-cash-line me-2"></i>Payments
              </a>
            @else
              <span class="nav-link disabled d-flex justify-content-between align-items-center">
                <span><i class="ri-cash-line me-2"></i>Payments</span>
                <small class="badge bg-warning text-dark">Pay First</small>
              </span>
            @endif
          </li> --}}
          {{-- <li class="nav-item mb-2">
            @if($student->canAccessFeatures())
              <a class="nav-link" href="#">
                <i class="ri-book-open-line me-2"></i>My Subjects
              </a>
            @else
              <span class="nav-link disabled d-flex justify-content-between align-items-center">
                <span><i class="ri-book-open-line me-2"></i>My Subjects</span>
                <small class="badge bg-warning text-dark">Pay First</small>
              </span>
            @endif
          </li> --}}
          {{-- <li class="nav-item mb-2">
            @if($student->canAccessFeatures())
              <a class="nav-link" href="#">
                <i class="ri-file-paper-line me-2"></i>Guidance Notes
              </a>
            @else
              <span class="nav-link disabled d-flex justify-content-between align-items-center">
                <span><i class="ri-file-paper-line me-2"></i>Guidance Notes</span>
                <small class="badge bg-warning text-dark">Pay First</small>
              </span>
            @endif
          </li> --}}
          <li class="nav-item mb-2">
            {{-- @if($student->canAccessFeatures()) --}}
              <a class="nav-link" href="#">
                <i class="ri-flag-line me-2"></i>Violations
              </a>
            {{-- @else --}}
              {{-- <span class="nav-link disabled d-flex justify-content-between align-items-center">
                <span><i class="ri-flag-line me-2"></i>Discipline</span>
                <small class="badge bg-warning text-dark">Pay First</small>
              </span> --}}
            {{-- @endif --}}
          </li>
          {{-- <li class="nav-item">
            @if($student->canAccessFeatures())
              <a class="nav-link" href="#">
                <i class="ri-user-line me-2"></i>Profile
              </a>
            @else
              <span class="nav-link disabled d-flex justify-content-between align-items-center">
                <span><i class="ri-user-line me-2"></i>Profile</span>
                <small class="badge bg-warning text-dark">Pay First</small>
              </span>
            @endif
          </li> --}}
        </ul>
        
        <!-- LOGOUT SECTION -->
        <div class="mt-auto pt-3">
          <form action="{{ route('student.logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start d-flex align-items-center" style="font-weight: 600;">
              <i class="ri-logout-circle-line me-2"></i>Logout
            </button>
          </form>
        </div>
      </nav>

      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 px-4 py-4">
        <h1 class="section-title">Welcome, {{ $student->first_name ?? 'Student' }}</h1>
        
        {{-- @if(!$student->canAccessFeatures())
            <div class="alert alert-warning d-flex align-items-center mb-4">
                <i class="ri-alert-line me-2"></i>
                <div>
                    <strong>Payment Required!</strong> 
                    You need to complete your payment to access all features. Current status: <strong>{{ $student->paymentStatus }}</strong>
                </div>
            </div>
        @endif --}}

        <!-- SUMMARY CARDS -->
        {{-- <div class="row g-3 mb-5"> --}}
          {{-- <div class="col-6 col-lg-3">
            <div class="card card-summary card-paid h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-checkbox-circle-line display-6 me-3"></i>
                <div>
                  <div>Payment Status</div>
                  <h3>{{ $student->paymentStatus }}</h3>
                </div>
              </div>
            </div>
          </div> --}}
          {{-- <div class="col-6 col-lg-3">
            <div class="card card-summary card-credits h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-bar-chart-line display-6 me-3"></i>
                <div>
                  <div>Credits Earned</div>
                  <h3>18</h3>
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
                  <h3>5</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-gpa h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-star-line display-6 me-3"></i>
                <div>
                  <div>Average</div>
                  <h3>85</h3>
                </div>
              </div>
            </div>
          </div> --}}
        {{-- </div> --}}

        <!-- PAYMENT HISTORY -->
        {{-- <h4 class="section-title">Payment History</h4>
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
        </div> --}}

        <!-- ACTIVE SUBJECTS -->
        {{-- <h4 class="section-title">My Subjects</h4>
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
        </div> --}}

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
              <dt class="col-sm-3">Student ID</dt>
              <dd class="col-sm-9">{{ $student->student_id ?? $student->lrn ?? 'STU-' . str_pad($student->id, 6, '0', STR_PAD_LEFT) }}</dd>
              <dt class="col-sm-3">Name</dt>
              <dd class="col-sm-9">{{ $student->full_name }}</dd>
              <dt class="col-sm-3">Student Type</dt>
              <dd class="col-sm-9">{{ ucfirst($student->student_type) }}</dd>
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
            {{-- @if($student->canAccessFeatures())
              <button class="btn btn-outline-primary">
                <i class="ri-edit-line me-1"></i>Edit Profile
              </button>
            @else
              <button class="btn btn-outline-secondary" disabled title="Payment required to edit profile">
                <i class="ri-edit-line me-1"></i>Edit Profile
                <span class="badge bg-warning text-dark ms-2">Pay First</span>
              </button>
            @endif --}}
          </div>
        </div>

        <!-- QUICK ACTIONS -->
        {{-- <h4 class="section-title">Quick Actions</h4>
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
        </div> --}}
      </main>
    </div>
  </div>
</body>
</html>
