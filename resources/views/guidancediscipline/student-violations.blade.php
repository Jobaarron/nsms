<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>

  <title>Violations Management • NSMS</title>

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

    /* User info in sidebar */
    .user-info {
      background-color: var(--primary-color);
      color: white;
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: 0.5rem;
    }

    .user-info .user-name {
      font-weight: 700;
      margin-bottom: 0.25rem;
    }

    .user-info .user-role {
      font-size: 0.875rem;
      opacity: 0.9;
    }

    /* Logout button */
    .btn-logout {
      background-color: #dc3545;
      border-color: #dc3545;
      color: white;
    }

    .btn-logout:hover {
      background-color: #c82333;
      border-color: #bd2130;
      color: white;
    }

    /* Statistics cards specific styling */
    .stats-card {
      transition: transform 0.2s ease;
    }

    .stats-card:hover {
      transform: translateY(-2px);
    }

    /* Search and filter styling */
    .search-filter-section {
      background: white;
      border-radius: 0.5rem;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">

      <!-- SIDEBAR -->
      <nav class="col-12 col-md-2 sidebar d-none d-md-block py-4">
        <!-- User Info -->
        <div class="user-info">
          <div class="user-name">{{ Auth::user()->name }}</div>
          <div class="user-role">{{ ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) }}</div>
        </div>

        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <a class="nav-link" href="{{ route('guidance.dashboard') }}">
              <i class="ri-dashboard-line me-2"></i>Dashboard
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link d-flex justify-content-between align-items-center" href="{{ route('guidance.students.index') }}">
              <span><i class="ri-user-line me-2"></i>Student Profiles</span>
              <small class="badge bg-success text-white">Active</small>
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link active d-flex justify-content-between align-items-center" href="{{ route('guidance.violations.index') }}">
              <span><i class="ri-alert-line me-2"></i>Violations</span>
              <small class="badge bg-success text-white">Active</small>
            </a>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-scan-2-line me-2"></i>Facial Recognition</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-chat-quote-line me-2"></i>Counseling</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-briefcase-line me-2"></i>Career Advice</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-bar-chart-line me-2"></i>Analytics & Reports</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>
          <li class="nav-item mb-2">
            <span class="nav-link disabled d-flex justify-content-between align-items-center">
              <span><i class="ri-settings-3-line me-2"></i>Settings</span>
              <small class="badge bg-light text-dark">Soon</small>
            </span>
          </li>

          {{-- @can('create_guidance_accounts')
          <li class="nav-item mb-2">
            <a class="nav-link" href="{{ route('guidance.create-account') }}">
              <i class="ri-user-add-line me-2"></i>Create Account
            </a>
          </li>
          @endcan --}}

          <li class="nav-item mt-3">
            <form method="POST" action="{{ route('guidance.logout') }}">
              @csrf
              <button type="submit" class="btn btn-logout w-100">
                <i class="ri-logout-circle-line me-2"></i>Logout
              </button>
            </form>
          </li>
        </ul>
      </nav>

      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="section-title mb-0">Violations Management</h1>
          <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createViolationModal">
              <i class="ri-add-line me-2"></i>Report New Violation
            </button>
            <div class="text-muted">
              <i class="ri-calendar-line me-1"></i>{{ now()->format('F j, Y') }}
            </div>
          </div>
        </div>

        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        <!-- STATISTICS CARDS -->
        <div class="row g-3 mb-4">
          <div class="col-6 col-lg-3">
            <div class="card card-summary stats-card h-100" style="background-color: #ffc107;">
              <div class="card-body text-center">
                <i class="ri-time-line display-6 mb-2"></i>
                <div>Pending</div>
                <h3>{{ $stats['pending'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary stats-card h-100" style="background-color: #17a2b8;">
              <div class="card-body text-center">
                <i class="ri-search-line display-6 mb-2"></i>
                <div>Investigating</div>
                <h3>{{ $stats['investigating'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary stats-card h-100" style="background-color: #28a745;">
              <div class="card-body text-center">
                <i class="ri-check-line display-6 mb-2"></i>
                <div>Resolved</div>
                <h3>{{ $stats['resolved'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary stats-card h-100" style="background-color: #dc3545;">
              <div class="card-body text-center">
                <i class="ri-error-warning-line display-6 mb-2"></i>
                <div>Severe Cases</div>
                <h3>{{ $stats['severe'] ?? 0 }}</h3>
              </div>
            </div>
          </div>
        </div>

        <!-- SEARCH AND FILTER SECTION -->
        <div class="search-filter-section">
          <div class="row align-items-end">
            <div class="col-md-3">
              <label for="searchInput" class="form-label fw-bold">Search Violations</label>
              <div class="input-group">
                <span class="input-group-text"><i class="ri-search-line"></i></span>
                <input type="text" class="form-control" id="searchInput" placeholder="Search violations...">
              </div>
            </div>
            <div class="col-md-2">
              <label for="statusFilter" class="form-label fw-bold">Status</label>
              <select class="form-select" id="statusFilter">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="investigating">Investigating</option>
                <option value="resolved">Resolved</option>
                <option value="dismissed">Dismissed</option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="severityFilter" class="form-label fw-bold">Severity</label>
              <select class="form-select" id="severityFilter">
                <option value="">All Severities</option>
                <option value="minor">Minor</option>
                <option value="major">Major</option>
                <option value="severe">Severe</option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="typeFilter" class="form-label fw-bold">Type</label>
              <select class="form-select" id="typeFilter">
                <option value="">All Types</option>
                <option value="late">Late Arrival</option>
                <option value="uniform">Uniform</option>
                <option value="misconduct">Misconduct</option>
                <option value="academic">Academic</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="dateFilter" class="form-label fw-bold">Date Filter</label>
              <input type="date" class="form-control" id="dateFilter" title="Filter by date">
            </div>
          </div>
        </div>

        <!-- VIOLATIONS TABLE -->
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Violations List</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle" id="violationsTable">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Violation</th>
                    <th>Type</th>
                    <th>Severity</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($violations as $violation)
                  <tr>
                    <td>#{{ $violation->id }}</td>
                    <td>
                      <div>
                        <strong>{{ $violation->student->first_name }} {{ $violation->student->last_name }}</strong>
                        <br><small class="text-muted">{{ $violation->student->student_id }}</small>
                      </div>
                    </td>
                    <td>
                      <strong>{{ $violation->title }}</strong>
                      <br><small class="text-muted">{{ Str::limit($violation->description, 50) }}</small>
                    </td>
                    <td>
                      <span class="badge bg-secondary">{{ ucfirst($violation->violation_type) }}</span>
                    </td>
                    <td>
                      <span class="badge bg-{{ $violation->severity === 'minor' ? 'success' : ($violation->severity === 'major' ? 'warning' : 'danger') }}">
                        {{ ucfirst($violation->severity) }}
                      </span>
                    </td>
                    <td>
                      {{ $violation->violation_date->format('M d, Y') }}
                      @if($violation->violation_time)
                        <br><small class="text-muted">{{ date('h:i A', strtotime($violation->violation_time)) }}</small>
                      @endif
                    </td>
                    <td>
                      <span class="badge bg-{{ $violation->status === 'pending' ? 'warning' : ($violation->status === 'resolved' ? 'success' : 'info') }}">
                        {{ ucfirst($violation->status) }}
                      </span>
                    </td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="viewViolation({{ $violation->id }})"
                                title="View Details">
                          <i class="ri-eye-line"></i>
                        </button>
                        @if($violation->status !== 'resolved')
                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="editViolation({{ $violation->id }})"
                                title="Edit">
                          <i class="ri-edit-line"></i>
                        </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="deleteViolation({{ $violation->id }})"
                                title="Delete">
                          <i class="ri-delete-bin-line"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="8" class="text-center py-5">
                      <i class="ri-alert-line display-4 text-muted"></i>
                      <p class="text-muted mt-2">No violations found</p>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            @if($violations->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
              <div>
                <small class="text-muted">
                  Showing {{ $violations->firstItem() ?: 0 }} to {{ $violations->lastItem() ?: 0 }} 
                  of {{ $violations->total() }} violations
                </small>
              </div>
              {{ $violations->links() }}
            </div>
            @endif
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- Create Violation Modal -->
  <div class="modal fade" id="createViolationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-add-line me-2"></i>Report New Violation
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('guidance.violations.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="student_id" class="form-label">Student <span class="text-danger">*</span></label>
                  <select class="form-select" id="student_id" name="student_id" required>
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                      <option value="{{ $student->id }}">
                        {{ $student->first_name }} {{ $student->last_name }} 
                        ({{ $student->student_id ?: 'No ID' }})
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="violation_type" class="form-label">Violation Type <span class="text-danger">*</span></label>
                  <select class="form-select" id="violation_type" name="violation_type" required>
                    <option value="">Select Type</option>
                    <option value="late">Late Arrival</option>
                    <option value="uniform">Uniform Violation</option>
                    <option value="misconduct">Misconduct</option>
                    <option value="academic">Academic Dishonesty</option>
                    <option value="other">Other</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="title" name="title" required 
                     placeholder="Brief description of the violation">
            </div>
            
            <div class="mb-3">
              <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
              <textarea class="form-control" id="description" name="description" rows="3" required
                        placeholder="Detailed description of what happened"></textarea>
            </div>
            
            <div class="row">
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="severity" class="form-label">Severity <span class="text-danger">*</span></label>
                  <select class="form-select" id="severity" name="severity" required>
                    <option value="minor">Minor</option>
                    <option value="major">Major</option>
                    <option value="severe">Severe</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="violation_date" class="form-label">Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="violation_date" name="violation_date" 
                         value="{{ date('Y-m-d') }}" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="violation_time" class="form-label">Time</label>
                  <input type="time" class="form-control" id="violation_time" name="violation_time">
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="location" class="form-label">Location</label>
              <input type="text" class="form-control" id="location" name="location" 
                     placeholder="Where did this happen?">
            </div>
            
            <div class="mb-3">
              <label for="witnesses" class="form-label">Witnesses</label>
              <textarea class="form-control" id="witnesses" name="witnesses" rows="2"
                        placeholder="Names of witnesses (one per line)"></textarea>
            </div>
            
            <div class="mb-3">
              <label for="evidence" class="form-label">Evidence Description</label>
              <textarea class="form-control" id="evidence" name="evidence" rows="2"
                        placeholder="Describe any evidence"></textarea>
            </div>
            
            <div class="mb-3">
              <label for="attachments" class="form-label">Attachments</label>
              <input type="file" class="form-control" id="attachments" name="attachments[]" multiple
                     accept="image/*,.pdf,.doc,.docx">
              <div class="form-text">Upload images or documents as evidence</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="ri-save-line me-2"></i>Report Violation
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Search and filter functionality
      const searchInput = document.getElementById('searchInput');
      const statusFilter = document.getElementById('statusFilter');
      const severityFilter = document.getElementById('severityFilter');
      const typeFilter = document.getElementById('typeFilter');
      const dateFilter = document.getElementById('dateFilter');
      
      function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const severityValue = severityFilter.value;
        const typeValue = typeFilter.value;
        const dateValue = dateFilter.value;
        const rows = document.querySelectorAll('#violationsTable tbody tr');
        
        rows.forEach(row => {
          if (row.cells.length < 8) return; // Skip empty rows
          
          const student = row.cells[1].textContent.toLowerCase();
          const violation = row.cells[2].textContent.toLowerCase();
          const type = row.cells[3].textContent.toLowerCase();
          const severity = row.cells[4].textContent.toLowerCase();
          const date = row.cells[5].textContent;
          const status = row.cells[6].textContent.toLowerCase();
          
          const matchesSearch = student.includes(searchTerm) || violation.includes(searchTerm);
          const matchesStatus = !statusValue || status.includes(statusValue);
          const matchesSeverity = !severityValue || severity.includes(severityValue);
          const matchesType = !typeValue || type.includes(typeValue);
          const matchesDate = !dateValue || date.includes(new Date(dateValue).toLocaleDateString());
          
          row.style.display = matchesSearch && matchesStatus && matchesSeverity && matchesType && matchesDate ? '' : 'none';
        });
      }
      
      [searchInput, statusFilter, severityFilter, typeFilter, dateFilter].forEach(element => {
        element.addEventListener('input', filterTable);
        element.addEventListener('change', filterTable);
      });
    });

    // Global functions for CRUD operations
    function viewViolation(violationId) {
      alert('Violation details view coming soon for violation ID: ' + violationId);
    }

    function editViolation(violationId) {
      alert('Edit violation form coming soon for violation ID: ' + violationId);
    }

    function deleteViolation(violationId) {
      if (confirm('Are you sure you want to delete this violation? This action cannot be undone.')) {
        // Create form and submit for deletion
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/guidance/violations/${violationId}`;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
      }
    }
  </script>
</body>
</html>
