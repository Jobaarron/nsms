<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Teacher Dashboard • NSMS</title>

  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet"/>

  <!-- App CSS & JS (includes Bootstrap 5 via Vite) -->
  @vite(['resources/sass/app.scss','resources/js/app.js'])

  <style>
    /* Reuse color palette */
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
    }
    .sidebar .nav-link {
      color: var(--primary-color);
      font-weight: 600;
      padding: .75rem 1rem;
    }
    .sidebar .nav-link.active,
    .sidebar .nav-link:hover {
      background-color: var(--accent-color);
      color: #fff;
      border-radius: .25rem;
    }
    /* Section title */
    .section-title {
      color: var(--primary-color);
      font-weight: 700;
      margin-bottom: 1rem;
    }
    /* Cards */
    .card-summary { color: #fff; }
    .card-classes  { background-color: var(--primary-color); }
    .card-students { background-color: var(--accent-color); }
    .card-grades   { background-color: var(--dark-green); }
    .card-upcoming { background-color: var(--secondary-color); color: var(--dark-green); }
    /* Table */
    .table thead { background-color: var(--primary-color); color: #fff; }
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
      <nav class="col-12 col-md-2 sidebar d-md-block py-4 vh-100">
        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link active" href="#">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-book-3-line me-2"></i>My Classes</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-team-line me-2"></i>Students</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-pencil-ruler-2-line me-2"></i>Grade Book</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-time-line me-2"></i>Attendance</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-chat-1-line me-2"></i>Messages</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
        </ul>
      </nav>

      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 px-4 py-4">
        <h1 class="section-title">Teacher Dashboard</h1>

        <!-- SUMMARY CARDS -->
        <div class="row g-3 mb-5">
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-classes h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-book-2-line display-6 me-3"></i>
                <div>
                  <div>Classes Teaching</div>
                  <h3>--</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-students h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-team-line display-6 me-3"></i>
                <div>
                  <div>Students Enrolled</div>
                  <h3>--</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-grades h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-pencil-line display-6 me-3"></i>
                <div>
                  <div>Pending Grades</div>
                  <h3>--</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-upcoming h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-calendar-event-line display-6 me-3"></i>
                <div>
                  <div>Upcoming Exams</div>
                  <h3>--</h3>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RECENT CLASSES TABLE -->
        <h4 class="section-title">Recent Classes</h4>
        <div class="table-responsive mb-5">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Class</th>
                <th>Level</th>
                <th>Students</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>Biology 101</td>
                <td>Grade School</td>
                <td>28</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-primary me-1">
                    <i class="ri-eye-line"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-primary me-1">
                    <i class="ri-pencil-line"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-primary">
                    <i class="ri-time-line"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- QUICK ACTIONS -->
        <h4 class="section-title">Quick Actions</h4>
        <div class="row g-3">
          <div class="col-md-4">
            <button class="btn btn-outline-primary w-100 py-3">
              <i class="ri-add-circle-line me-2"></i>New Assignment
            </button>
          </div>
          <div class="col-md-4">
            <button class="btn btn-outline-primary w-100 py-3">
              <i class="ri-file-text-line me-2"></i>Submit Grades
            </button>
          </div>
          <div class="col-md-4">
            <button class="btn btn-outline-primary w-100 py-3">
              <i class="ri-checkbox-multiple-line me-2"></i>Mark Attendance
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</body>
</html>
