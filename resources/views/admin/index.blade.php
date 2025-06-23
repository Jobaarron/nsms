<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Admin Dashboard • NSMS</title>

  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet"/>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"/>

  <!-- App CSS & JS (includes Bootstrap 5 via Vite) -->
  @vite(['resources/sass/app.scss','resources/js/app.js'])

  <style>
    /* Color Palette from layout.blade.php */
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

    /* Summary Cards */
    .card-summary {
      color: #fff;
    }
    .card-primary   { background-color: var(--primary-color); }
    .card-secondary { background-color: var(--secondary-color); color: var(--dark-green); }
    .card-success   { background-color: var(--accent-color); }
    .card-dark      { background-color: var(--dark-green); }

    /* Section headers */
    .section-title {
      color: var(--primary-color);
      margin-bottom: 1rem;
      font-weight: 700;
    }

    /* Table */
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
    .btn-outline-success {
      color: var(--accent-color);
      border-color: var(--accent-color);
    }
    .btn-outline-success:hover {
      background-color: var(--accent-color);
      color: #fff;
    }
    .btn-outline-dark {
      color: var(--dark-green);
      border-color: var(--dark-green);
    }
    .btn-outline-dark:hover {
      background-color: var(--dark-green);
      color: #fff;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">

      <!-- SIDEBAR -->
      <nav class="col-12 col-md-2 sidebar d-md-block py-4">
        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link active" href="#">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link" href="#">
              <i class="ri-user-line me-2"></i>Manage Users
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link" href="#">
              <i class="ri-shield-user-line me-2"></i>Roles & Access
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link" href="#">
              <i class="ri-book-open-line me-2"></i>Enrollments
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link" href="#">
              <i class="ri-file-list-3-line me-2"></i>Reports
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">
              <i class="ri-cash-line me-2"></i>Cashier
            </a>
          </li>
        </ul>
      </nav>

      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 px-4 py-4">
        <h1 class="section-title">Admin Dashboard</h1>

        <!-- SUMMARY CARDS -->
        <div class="row g-3 mb-5">
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-primary h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-user-3-line display-6 me-3"></i>
                <div>
                  <div>Total Users</div>
                  <h3>--</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-secondary h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-shield-user-line display-6 me-3"></i>
                <div>
                  <div>Roles Defined</div>
                  <h3>--</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-success h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-book-open-line display-6 me-3"></i>
                <div>
                  <div>Enrolled This Year</div>
                  <h3>--</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-dark h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-book-mark-line display-6 me-3"></i>
                <div>
                  <div>Enrolled Last Year</div>
                  <h3>--</h3>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RECENT ENROLLMENTS TABLE -->
        <h4 class="section-title">Latest Enrollments</h4>
        <div class="table-responsive mb-5">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Student</th>
                <th>Program</th>
                <th>Date</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>Jane Doe</td>
                <td>Junior High</td>
                <td>2024-07-15</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-primary me-1">
                    <i class="ri-eye-line"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-warning me-1">
                    <i class="ri-edit-line"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger">
                    <i class="ri-delete-bin-line"></i>
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
              <i class="ri-user-add-line me-2"></i>Add User
            </button>
          </div>
          <div class="col-md-4">
            <button class="btn btn-outline-success w-100 py-3">
              <i class="ri-book-add-line me-2"></i>New Enrollment
            </button>
          </div>
          <div class="col-md-4">
            <button class="btn btn-outline-dark w-100 py-3">
              <i class="ri-money-dollar-box-line me-2"></i>Cashier Dashboard
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</body>
</html>
