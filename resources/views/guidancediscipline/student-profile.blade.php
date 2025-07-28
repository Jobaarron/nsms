<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>

  <title>Student Profiles • NSMS</title>

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

    /* Search and filter styling */
    .search-filter-section {
      background: white;
      border-radius: 0.5rem;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Student card styling */
    .student-card {
      transition: transform 0.2s ease;
    }

    .student-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    /* Camera modal styling */
    video {
      border: 2px solid var(--secondary-color);
      border-radius: 0.5rem;
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
            <a class="nav-link active d-flex justify-content-between align-items-center" href="{{ route('guidance.students.index') }}">
              <span><i class="ri-user-line me-2"></i>Student Profiles</span>
              {{-- <small class="badge bg-success text-white">Active</small> --}}
            </a>
          </li>
          <li class="nav-item mb-2">
            <a class="nav-link d-flex justify-content-between align-items-center" href="{{ route('guidance.violations.index') }}">
              <span><i class="ri-alert-line me-2"></i>Violations</span>
              {{-- <small class="badge bg-success text-white">Active</small> --}}
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
          <h1 class="section-title mb-0">Student Profiles</h1>
          <div class="text-muted">
            <i class="ri-calendar-line me-1"></i>{{ now()->format('F j, Y') }}
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

        <!-- SEARCH AND FILTER SECTION -->
        <div class="search-filter-section">
          <div class="row align-items-end">
            <div class="col-md-4">
              <label for="searchInput" class="form-label fw-bold">Search Students</label>
              <div class="input-group">
                <span class="input-group-text"><i class="ri-search-line"></i></span>
                <input type="text" class="form-control" id="searchInput" placeholder="Search by name, student ID, or LRN...">
              </div>
            </div>
            <div class="col-md-2">
              <label for="gradeFilter" class="form-label fw-bold">Grade Level</label>
              <select class="form-select" id="gradeFilter">
                <option value="">All Grades</option>
                <option value="Grade 7">Grade 7</option>
                <option value="Grade 8">Grade 8</option>
                <option value="Grade 9">Grade 9</option>
                <option value="Grade 10">Grade 10</option>
                <option value="Grade 11">Grade 11</option>
                <option value="Grade 12">Grade 12</option>
              </select>
            </div>
            {{-- COMMENTED OUT FOR FUTURE USE: Status Filter
            <div class="col-md-2">
              <label for="statusFilter" class="form-label fw-bold">Status</label>
              <select class="form-select" id="statusFilter">
                <option value="">All Statuses</option>
                <option value="enrolled">Enrolled</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
              </select>
            </div>
            --}}
            <div class="col-md-2">
              <label for="faceFilter" class="form-label fw-bold">Face Registration</label>
              <select class="form-select" id="faceFilter">
                <option value="">All</option>
                <option value="registered">Registered</option>
                <option value="not_registered">Not Registered</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-bold">Actions</label>
              <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#facialRecognitionModal">
                <i class="ri-camera-line me-2"></i>Face Scanner
              </button>
            </div>
          </div>
        </div>

        <!-- STUDENTS TABLE -->
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Students List</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle" id="studentsTable">
                <thead>
                  <tr>
                    <th>Photo</th>
                    <th>Student Info</th>
                    <th>Grade</th>
                    {{-- <th>Grade & Section</th> --}}
                    {{-- <th>Status</th> --}}
                    <th>Face Registration</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($students as $student)
                  <tr>
                    <td>
                      @if($student->hasIdPhoto())
                      <img src="{{ $student->id_photo_data_url }}" 
                             alt="Student Photo" 
                             class="rounded-circle" 
                             width="50" height="50">
                      @else
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 50px; height: 50px;">
                          <i class="ri-user-line text-white"></i>
                        </div>
                      @endif
                    </td>
                    <td>
                      <div>
                        <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                        <br><small class="text-muted">ID: {{ $student->student_id ?: ($student->lrn ? 'LRN: '.$student->lrn : 'No ID') }}</small>
                        @if($student->lrn)
                          <br><small class="text-muted">LRN: {{ $student->lrn }}</small>
                        @endif
                      </div>
                    </td>
                    <td>
                      <span class="fw-bold">{{ $student->grade_level }}</span>
                      @if($student->section)
                        {{-- <br><small class="text-muted">Section: {{ $student->section }}</small> --}}
                      @else
                        {{-- <br><small class="text-muted">No section assigned</small> --}}
                      @endif
                    </td>
                    {{-- COMMENTED OUT FOR FUTURE USE: Status Column
                    <td>
                      <span class="badge bg-{{ $student->enrollment_status === 'enrolled' ? 'success' : ($student->enrollment_status === 'pending' ? 'warning' : 'info') }}">
                        {{ ucfirst($student->enrollment_status) }}
                      </span>
                    </td>
                    --}}
                    <td>
                      <span class="badge bg-warning">
                        <i class="ri-close-line me-1"></i>Not Registered
                      </span>
                    </td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="viewStudent({{ $student->id }})"
                                title="View Profile">
                          <i class="ri-eye-line"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" 
                                onclick="registerFace({{ $student->id }})"
                                title="Register Face">
                          <i class="ri-camera-line"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="viewViolations({{ $student->id }})"
                                title="View Violations">
                          <i class="ri-alert-line"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="5" class="text-center py-5">
                      <i class="ri-user-line display-4 text-muted"></i>
                      <p class="text-muted mt-2">No students found</p>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            @if($students->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
              <div>
                <small class="text-muted">
                  Showing {{ $students->firstItem() ?: 0 }} to {{ $students->lastItem() ?: 0 }} 
                  of {{ $students->total() }} students
                </small>
              </div>
              {{ $students->links() }}
            </div>
            @endif
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- Student Profile Modal -->
  <div class="modal fade" id="studentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Student Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="studentModalBody">
          <!-- Student details will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="hideModal('studentModal')">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Facial Recognition Scanner Modal -->
  <div class="modal fade" id="facialRecognitionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-camera-line me-2"></i>Facial Recognition Scanner
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <div class="text-center">
                <video id="video" width="100%" height="300" autoplay style="display: none;"></video>
                <div id="cameraPlaceholder" class="border rounded p-5 text-center" style="height: 300px; background: #f8f9fa;">
                  <i class="ri-camera-line display-1 text-muted"></i>
                  <p class="text-muted">Click "Start Camera" to begin facial recognition</p>
                </div>
                <canvas id="canvas" style="display: none;"></canvas>
              </div>
              <div class="text-center mt-3">
                <button type="button" class="btn btn-primary" id="startCamera">
                  <i class="ri-camera-line me-2"></i>Start Camera
                </button>
                <button type="button" class="btn btn-success" id="capturePhoto" style="display: none;">
                  <i class="ri-camera-3-line me-2"></i>Capture Photo
                </button>
                <button type="button" class="btn btn-danger" id="stopCamera" style="display: none;">
                  <i class="ri-stop-line me-2"></i>Stop Camera
                </button>
              </div>
            </div>
            <div class="col-md-4">
              <h6>Instructions:</h6>
              <ul class="list-unstyled">
                <li><i class="ri-check-line text-success me-2"></i>Look directly at the camera</li>
                <li><i class="ri-check-line text-success me-2"></i>Ensure good lighting</li>
                <li><i class="ri-check-line text-success me-2"></i>Remove glasses if possible</li>
                <li><i class="ri-check-line text-success me-2"></i>Keep face within frame</li>
              </ul>
              
              <div id="recognitionResult" style="display: none;">
                <h6>Recognition Result:</h6>
                <div id="resultContent"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Face Registration Modal -->
  <div class="modal fade" id="faceRegistrationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-user-add-line me-2"></i>Register Student Face
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <h6>Student Information:</h6>
              <div id="studentInfoForRegistration"></div>
            </div>
            <div class="col-md-6">
              <h6>Face Registration:</h6>
              <div class="text-center">
                <video id="registrationVideo" width="100%" height="200" autoplay style="display: none;"></video>
                <div id="registrationPlaceholder" class="border rounded p-3 text-center" style="height: 200px; background: #f8f9fa;">
                  <i class="ri-camera-line display-4 text-muted"></i>
                  <p class="text-muted">Camera for registration</p>
                </div>
                <canvas id="registrationCanvas" style="display: none;"></canvas>
              </div>
              <div class="text-center mt-3">
                <button type="button" class="btn btn-primary" id="startRegistrationCamera">
                  <i class="ri-camera-line me-2"></i>Start Camera
                </button>
                <button type="button" class="btn btn-success" id="captureRegistrationPhoto" style="display: none;">
                  <i class="ri-camera-3-line me-2"></i>Capture & Register
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Wait for both DOM and Bootstrap to be ready
    document.addEventListener('DOMContentLoaded', function() {
      // Wait a bit for Bootstrap to initialize
      // setTimeout(function() {
      //   console.log('Page loaded, Bootstrap status:', typeof window.bootstrap !== 'undefined' ? 'Available' : 'Not Available');
      //   initializeModalEventListeners();
      // }, 100);

      // Initialize modal event listeners
      function initializeModalEventListeners() {
        // Add close button functionality to all modals
        document.querySelectorAll('.modal').forEach(modal => {
          const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
          closeButtons.forEach(button => {
            button.addEventListener('click', function() {
              hideModal(modal.id);
            });
          });
          
          // Close on backdrop click
          modal.addEventListener('click', function(e) {
            if (e.target === modal) {
              hideModal(modal.id);
            }
          });
        });
        
        // Global ESC key listener
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') {
            window.ModalManager.hideAll();
          }
        });
      }

      // Cleanup function for camera streams
      function cleanupCameraStreams() {
        if (stream) {
          stream.getTracks().forEach(track => track.stop());
          stream = null;
        }
        if (registrationStream) {
          registrationStream.getTracks().forEach(track => track.stop());
          registrationStream = null;
        }
      }

      // Cleanup when page is unloaded
      window.addEventListener('beforeunload', cleanupCameraStreams);
      // Search functionality
      const searchInput = document.getElementById('searchInput');
      const gradeFilter = document.getElementById('gradeFilter');
      // const statusFilter = document.getElementById('statusFilter'); // COMMENTED OUT FOR FUTURE USE
      const faceFilter = document.getElementById('faceFilter');
      
      function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const gradeValue = gradeFilter.value;
        // const statusValue = statusFilter.value; // COMMENTED OUT FOR FUTURE USE
        const faceValue = faceFilter.value;
        const rows = document.querySelectorAll('#studentsTable tbody tr');
        
        rows.forEach(row => {
          if (row.cells.length < 5) return; // Skip empty rows (updated since status column removed)
          
          const studentInfo = row.cells[1].textContent.toLowerCase();
          const grade = row.cells[2].textContent;
          // const status = row.cells[3].textContent.toLowerCase(); // COMMENTED OUT FOR FUTURE USE
          const faceStatus = row.cells[3].textContent.toLowerCase(); // Updated index since status column removed
          
          const matchesSearch = studentInfo.includes(searchTerm);
          const matchesGrade = !gradeValue || grade.includes(gradeValue);
          // const matchesStatus = !statusValue || status.includes(statusValue); // COMMENTED OUT FOR FUTURE USE
          const matchesStatus = true; // Always true since status filter is disabled
          const matchesFace = !faceValue || 
            (faceValue === 'registered' && faceStatus.includes('registered')) ||
            (faceValue === 'not_registered' && faceStatus.includes('not registered'));
          
          row.style.display = matchesSearch && matchesGrade && matchesStatus && matchesFace ? '' : 'none';
        });
      }
      
      [searchInput, gradeFilter, faceFilter].forEach(element => { // Removed statusFilter
        element.addEventListener('input', filterTable);
        element.addEventListener('change', filterTable);
      });

      // Camera functionality
      let stream = null;
      let registrationStream = null;

      // Main camera controls
      const video = document.getElementById('video');
      const cameraPlaceholder = document.getElementById('cameraPlaceholder');
      const startCameraBtn = document.getElementById('startCamera');
      const captureBtn = document.getElementById('capturePhoto');
      const stopCameraBtn = document.getElementById('stopCamera');

      startCameraBtn.addEventListener('click', async function() {
        try {
          stream = await navigator.mediaDevices.getUserMedia({ video: true });
          video.srcObject = stream;
          video.style.display = 'block';
          cameraPlaceholder.style.display = 'none';
          startCameraBtn.style.display = 'none';
          captureBtn.style.display = 'inline-block';
          stopCameraBtn.style.display = 'inline-block';
        } catch (err) {
          alert('Error accessing camera: ' + err.message);
        }
      });

      stopCameraBtn.addEventListener('click', function() {
        if (stream) {
          stream.getTracks().forEach(track => track.stop());
          video.srcObject = null;
          video.style.display = 'none';
          cameraPlaceholder.style.display = 'block';
          startCameraBtn.style.display = 'inline-block';
          captureBtn.style.display = 'none';
          stopCameraBtn.style.display = 'none';
        }
      });

      captureBtn.addEventListener('click', function() {
        document.getElementById('recognitionResult').style.display = 'block';
        document.getElementById('resultContent').innerHTML = 
          '<div class="alert alert-info">Facial recognition feature coming soon...</div>';
      });

      // Registration camera controls
      const registrationVideo = document.getElementById('registrationVideo');
      const registrationPlaceholder = document.getElementById('registrationPlaceholder');
      const startRegistrationBtn = document.getElementById('startRegistrationCamera');
      const captureRegistrationBtn = document.getElementById('captureRegistrationPhoto');

      startRegistrationBtn.addEventListener('click', async function() {
        try {
          registrationStream = await navigator.mediaDevices.getUserMedia({ video: true });
          registrationVideo.srcObject = registrationStream;
          registrationVideo.style.display = 'block';
          registrationPlaceholder.style.display = 'none';
          startRegistrationBtn.style.display = 'none';
          captureRegistrationBtn.style.display = 'inline-block';
        } catch (err) {
          alert('Error accessing camera: ' + err.message);
        }
      });

      captureRegistrationBtn.addEventListener('click', function() {
        alert('Face registration feature coming soon...');
        
        if (registrationStream) {
          registrationStream.getTracks().forEach(track => track.stop());
          registrationVideo.srcObject = null;
          registrationVideo.style.display = 'none';
          registrationPlaceholder.style.display = 'block';
          startRegistrationBtn.style.display = 'inline-block';
          captureRegistrationBtn.style.display = 'none';
        }
        
        hideModal('faceRegistrationModal');
      });

      // Modal cleanup
      document.getElementById('facialRecognitionModal').addEventListener('hidden.bs.modal', function() {
        if (stream) {
          stream.getTracks().forEach(track => track.stop());
          video.srcObject = null;
          video.style.display = 'none';
          cameraPlaceholder.style.display = 'block';
          startCameraBtn.style.display = 'inline-block';
          captureBtn.style.display = 'none';
          stopCameraBtn.style.display = 'none';
        }
      });

      document.getElementById('faceRegistrationModal').addEventListener('hidden.bs.modal', function() {
        if (registrationStream) {
          registrationStream.getTracks().forEach(track => track.stop());
          registrationVideo.srcObject = null;
          registrationVideo.style.display = 'none';
          registrationPlaceholder.style.display = 'block';
          startRegistrationBtn.style.display = 'inline-block';
          captureRegistrationBtn.style.display = 'none';
        }
      });
    });

    // Comprehensive modal management system
    window.ModalManager = {
      activeModals: new Set(),
      
      show: function(modalId) {
        try {
          const modalElement = document.getElementById(modalId);
          if (!modalElement) {
            console.error('Modal not found:', modalId);
            return false;
          }

          // Try Bootstrap first
          if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
            const modal = new window.bootstrap.Modal(modalElement, {
              backdrop: true,
              keyboard: true,
              focus: true
            });
            modal.show();
            this.activeModals.add(modalId);
            
            // Add event listeners for proper cleanup
            modalElement.addEventListener('hidden.bs.modal', () => {
              this.activeModals.delete(modalId);
            }, { once: true });
            
            return true;
          }
          
          // Fallback implementation
          return this.showFallback(modalId);
          
        } catch (error) {
          console.error('Error showing modal:', error);
          return this.showFallback(modalId);
        }
      },
      
      hide: function(modalId) {
        try {
          const modalElement = document.getElementById(modalId);
          if (!modalElement) return false;

          // Try Bootstrap first
          if (typeof window.bootstrap !== 'undefined') {
            const modal = window.bootstrap.Modal.getInstance(modalElement);
            if (modal) {
              modal.hide();
              return true;
            }
          }
          
          // Fallback implementation
          return this.hideFallback(modalId);
          
        } catch (error) {
          console.error('Error hiding modal:', error);
          return this.hideFallback(modalId);
        }
      },
      
      showFallback: function(modalId) {
        const modalElement = document.getElementById(modalId);
        const backdrop = this.createBackdrop(modalId);
        
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        modalElement.setAttribute('aria-hidden', 'false');
        modalElement.setAttribute('aria-modal', 'true');
        modalElement.setAttribute('role', 'dialog');
        
        document.body.classList.add('modal-open');
        document.body.appendChild(backdrop);
        
        this.activeModals.add(modalId);
        this.addFallbackEventListeners(modalId);
        
        return true;
      },
      
      hideFallback: function(modalId) {
        const modalElement = document.getElementById(modalId);
        const backdrop = document.getElementById(modalId + '-backdrop');
        
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');
        modalElement.removeAttribute('aria-modal');
        modalElement.removeAttribute('role');
        
        if (backdrop) backdrop.remove();
        
        if (this.activeModals.size <= 1) {
          document.body.classList.remove('modal-open');
        }
        
        this.activeModals.delete(modalId);
        return true;
      },
      
      createBackdrop: function(modalId) {
        // Remove existing backdrop
        const existingBackdrop = document.getElementById(modalId + '-backdrop');
        if (existingBackdrop) existingBackdrop.remove();
        
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = modalId + '-backdrop';
        backdrop.style.zIndex = '1040';
        
        // Click to close
        backdrop.addEventListener('click', () => this.hide(modalId));
        
        return backdrop;
      },
      
      addFallbackEventListeners: function(modalId) {
        const modalElement = document.getElementById(modalId);
        
        // ESC key to close
        const escHandler = (e) => {
          if (e.key === 'Escape' && this.activeModals.has(modalId)) {
            this.hide(modalId);
            document.removeEventListener('keydown', escHandler);
          }
        };
        document.addEventListener('keydown', escHandler);
        
        // Close buttons
        const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
        closeButtons.forEach(button => {
          button.addEventListener('click', () => this.hide(modalId));
        });
        
        // Click outside to close
        modalElement.addEventListener('click', (e) => {
          if (e.target === modalElement) {
            this.hide(modalId);
          }
        });
      },
      
      hideAll: function() {
        this.activeModals.forEach(modalId => this.hide(modalId));
      }
    };

    // Convenient wrapper functions
    function showModal(modalId) {
      return window.ModalManager.show(modalId);
    }

    function hideModal(modalId) {
      return window.ModalManager.hide(modalId);
    }


    // <tr><td><strong>Date of Birth:</strong></td><td>${data.date_of_birth || 'N/A'}</td></tr> Keep this here and do not remove
    // Global functions for button actions
    function viewStudent(studentId) {
      // Fetch student data from server
      fetch(`/guidance/students/${studentId}`)
        .then(response => response.json())
        .then(data => {
          document.getElementById('studentModalBody').innerHTML = `
            <div class="row">
              <div class="col-md-4 text-center">
                ${data.id_photo_data_url && data.id_photo_data_url !== null ? 
                  `<img src="${data.id_photo_data_url}" alt="Student Photo" class="img-fluid rounded-circle mb-3" style="max-width: 150px;">` :
                  `<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 150px; height: 150px;">
                     <i class="ri-user-line text-white display-4"></i>
                   </div>`
                }
                <h5>${data.first_name} ${data.last_name}</h5>
                <p class="text-muted">${data.grade_level}${data.section ? ' - ' + data.section : ''}</p>
              </div>
              <div class="col-md-8">
                <h6>Student Information</h6>
                <table class="table table-sm">
                  <tbody>
                    <!-- <tr><td><strong>Student ID:</strong></td><td>${data.student_id || 'N/A'}</td></tr> -->
                    <tr><td><strong>LRN:</strong></td><td>${data.lrn || 'N/A'}</td></tr>
                    <tr><td><strong>Gender:</strong></td><td>${data.gender || 'N/A'}</td></tr>
                    
                    <tr><td><strong>Contact:</strong></td><td>${data.contact_number || 'N/A'}</td></tr>
                    <tr><td><strong>Email:</strong></td><td>${data.email || 'N/A'}</td></tr>
                    <tr><td><strong>Address:</strong></td><td>${data.address || 'N/A'}</td></tr>
                    <!-- COMMENTED OUT FOR FUTURE USE: Status Row
                    <tr><td><strong>Status:</strong></td><td>
                      <span class="badge bg-${data.enrollment_status === 'enrolled' ? 'success' : (data.enrollment_status === 'pending' ? 'warning' : 'info')}">
                        ${data.enrollment_status ? data.enrollment_status.charAt(0).toUpperCase() + data.enrollment_status.slice(1) : 'N/A'}
                      </span>
                    </td></tr>
                    -->
                  </tbody>
                </table>
                
                <h6 class="mt-3">Parent/Guardian Information</h6>
                <table class="table table-sm">
                  <tbody>
                    <tr><td><strong>Father:</strong></td><td>${data.father_name || 'N/A'}</td></tr>
                    <tr><td><strong>Father Contact:</strong></td><td>${data.father_contact || 'N/A'}</td></tr>
                    <tr><td><strong>Mother:</strong></td><td>${data.mother_name || 'N/A'}</td></tr>
                    <tr><td><strong>Mother Contact:</strong></td><td>${data.mother_contact || 'N/A'}</td></tr>
                    <tr><td><strong>Guardian:</strong></td><td>${data.guardian_name || 'N/A'}</td></tr>
                    <tr><td><strong>Guardian Contact:</strong></td><td>${data.guardian_contact || 'N/A'}</td></tr>
                  </tbody>
                </table>
                
                ${data.violations && data.violations.length > 0 ? `
                  <h6 class="mt-3">Recent Violations (${data.violations.length})</h6>
                  <div class="list-group">
                    ${data.violations.slice(0, 3).map(violation => `
                      <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                          <h6 class="mb-1">${violation.title}</h6>
                          <small class="text-muted">${new Date(violation.violation_date).toLocaleDateString()}</small>
                        </div>
                        <p class="mb-1">${violation.description}</p>
                        <small class="badge bg-${violation.severity === 'minor' ? 'success' : (violation.severity === 'major' ? 'warning' : 'danger')}">${violation.severity}</small>
                      </div>
                    `).join('')}
                    ${data.violations.length > 3 ? `<small class="text-muted">... and ${data.violations.length - 3} more</small>` : ''}
                  </div>
                ` : '<p class="text-muted mt-3">No violations recorded</p>'}
              </div>
            </div>
          `;
          showModal('studentModal');
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error loading student information');
        });
    }

    function registerFace(studentId) {
      // Fetch student data first
      fetch(`/guidance/students/${studentId}/info`)
        .then(response => response.json())
        .then(data => {
          document.getElementById('studentInfoForRegistration').innerHTML = `
            <div class="card">
              <div class="card-body">
                <div class="text-center mb-3">
                  ${data.id_photo_data_url && data.id_photo_data_url !== null ? 
                    `<img src="${data.id_photo_data_url}" alt="Student Photo" class="img-fluid rounded-circle" style="max-width: 100px;">` :
                    `<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px;">
                       <i class="ri-user-line text-white"></i>
                     </div>`
                  }
                </div>
                <h6 class="text-center">${data.first_name} ${data.last_name}</h6>
                <p class="text-center text-muted mb-1">ID: ${data.student_id || 'N/A'}</p>
                <p class="text-center text-muted">${data.grade_level}${data.section ? ' - ' + data.section : ''}</p>
              </div>
            </div>
          `;
          showModal('faceRegistrationModal');
        })
        .catch(error => {
          console.error('Error:', error);
          document.getElementById('studentInfoForRegistration').innerHTML = `
            <div class="card">
              <div class="card-body">
                <h6>Student ID: ${studentId}</h6>
                <p class="mb-0">Face registration will be implemented soon.</p>
              </div>
            </div>
          `;
          showModal('faceRegistrationModal');
        });
    }

    function viewViolations(studentId) {
      window.location.href = `/guidance/violations?student_id=${studentId}`;
    }
  </script>
</body>
</html>
