<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>

  <title>Guidance & Discipline • NSMS</title>

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
  @vite(['resources/sass/app.scss','resources/js/app.js'])

  <style>
    /* Palette from layout */
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
      padding: .75rem 1rem;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background-color: var(--accent-color);
      color: #fff;
      border-radius: .25rem;
    }
    /* Section Title */
    .section-title {
      color: var(--primary-color);
      font-weight: 700;
      margin-bottom: 1rem;
    }
    /* Summary Cards */
    .card-summary { color: #fff; }
    .card-students    { background-color: var(--primary-color); }
    .card-facerec     { background-color: var(--accent-color); }
    .card-violations  { background-color: var(--dark-green); }
    .card-counsel     { background-color: var(--secondary-color); color: var(--dark-green); }
    .card-reports     { background-color: #4a4a4a; }
    /* Tables */
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
          <li class="nav-item mb-2">
            <a class="nav-link" href="#">
              <i class="ri-user-line me-2"></i>Student Profiles
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link" href="#">
              <i class="ri-scan-2-line me-2"></i>Facial Recognition
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link" href="#">
              <i class="ri-alert-line me-2"></i>Violations
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link" href="#">
              <i class="ri-chat-quote-line me-2"></i>Counseling
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link" href="#">
              <i class="ri-briefcase-line me-2"></i>Career Advice
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link" href="#">
              <i class="ri-bar-chart-line me-2"></i>Analytics & Reports
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">
              <i class="ri-settings-3-line me-2"></i>Settings
            </a>
          </li>
        </ul>
      </nav>

      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 px-4 py-4">
        <h1 class="section-title">Guidance & Discipline</h1>

        <!-- SUMMARY CARDS -->
        <div class="row g-3 mb-5">
          <div class="col-6 col-lg-2">
            <div class="card card-summary card-students h-100">
              <div class="card-body text-center">
                <i class="ri-team-line display-6 mb-2"></i>
                <div>Total Students</div>
                <h3>120</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-2">
            <div class="card card-summary card-facerec h-100">
              <div class="card-body text-center">
                <i class="ri-scan-2-line display-6 mb-2"></i>
                <div>Faces Registered</div>
                <h3>95</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-2">
            <div class="card card-summary card-violations h-100">
              <div class="card-body text-center">
                <i class="ri-alert-line display-6 mb-2"></i>
                <div>Violations This Month</div>
                <h3>12</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-2">
            <div class="card card-summary card-counsel h-100">
              <div class="card-body text-center">
                <i class="ri-chat-quote-line display-6 mb-2"></i>
                <div>Counsel Sessions</div>
                <h3>8</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-2">
            <div class="card card-summary card-counsel h-100">
              <div class="card-body text-center">
                <i class="ri-briefcase-line display-6 mb-2"></i>
                <div>Career Advisories</div>
                <h3>5</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-2">
            <div class="card card-summary card-reports h-100">
              <div class="card-body text-center">
                <i class="ri-bar-chart-line display-6 mb-2"></i>
                <div>Reports Generated</div>
                <h3>20</h3>
              </div>
            </div>
          </div>
        </div>

        <!-- FACIAL RECOGNITION PANEL -->
        <h4 class="section-title">Facial Recognition</h4>
        <div class="card mb-5">
          <div class="card-body">
            <div class="row">
              <div class="col-md-8">
                <div class="border rounded p-3 text-center" style="height:300px; background:#fff;">
                  <i class="ri-camera-line display-1 text-secondary"></i>
                  <p class="text-muted">Camera feed placeholder</p>
                </div>
              </div>
              <div class="col-md-4">
                <button class="btn btn-outline-primary w-100 mb-3">
                  <i class="ri-user-add-line me-2"></i>Enroll New Face
                </button>
                <button class="btn btn-outline-primary w-100">
                  <i class="ri-file-upload-line me-2"></i>Import Face Data
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- VIOLATIONS TABLE -->
        <h4 class="section-title">Recent Violations</h4>
        <div class="table-responsive mb-5">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Student</th>
                <th>Violation</th>
                <th>Date</th>
                <th>Severity</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>John Smith</td>
                <td>Late to class</td>
                <td>Jul 12, 2024</td>
                <td><span class="badge bg-warning text-dark">Minor</span></td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-primary me-1">
                    <i class="ri-eye-line"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-primary">
                    <i class="ri-edit-line"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- COUNSELING & CAREER ADVICE -->
        <div class="row mb-5">
          <div class="col-md-6">
            <h4 class="section-title">Upcoming Counseling</h4>
            <ul class="list-group">
              <li class="list-group-item">
                <i class="ri-calendar-event-line me-2"></i> Jul 20: Academic Planning
              </li>
              <li class="list-group-item">
                <i class="ri-calendar-event-line me-2"></i> Jul 25: Peer Counseling
              </li>
            </ul>
          </div>
          <div class="col-md-6">
            <h4 class="section-title">Career Advice Slots</h4>
            <ul class="list-group">
              <li class="list-group-item">
                <i class="ri-calendar-event-line me-2"></i> Jul 22: Resume Workshop
              </li>
              <li class="list-group-item">
                <i class="ri-calendar-event-line me-2"></i> Jul 30: Interview Prep
              </li>
            </ul>
          </div>
        </div>

        <!-- ANALYTICS PLACEHOLDER -->
        <h4 class="section-title">Analytics & Reports</h4>
        <div class="border rounded p-4 text-center mb-5" style="background:#fff; height:250px;">
          <i class="ri-chart-pie-line display-1 text-secondary"></i>
          <p class="text-muted">Charts and export tools go here</p>
        </div>

      </main>
    </div>
  </div>
</body>
</html>
