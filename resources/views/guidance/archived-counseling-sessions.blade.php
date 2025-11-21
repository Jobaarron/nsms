<x-guidance-layout>
    @vite('resources/css/index_guidance.css')
    
    <style>
        /* Archive Protection Styles - Minimal Green/White/Black Theme */
        .modal-content {
            border: 2px solid #28a745;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            background: white;
        }
        
        .modal-header {
            background: white;
            border-bottom: 1px solid #28a745;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
        }
        
        .form-control.is-invalid {
            animation: shake 0.5s ease-in-out;
            border-color: #dc3545;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .archive-locked-state {
            background: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-success, .btn-warning {
            background: #28a745;
            border: 1px solid #28a745;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-success:hover, .btn-warning:hover {
            background: #218838;
            border-color: #1e7e34;
            color: white;
            transform: translateY(-1px);
        }
        
        .text-guidance {
            color: #000;
            font-weight: 700;
        }
        
        .archive-session-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            z-index: 1000;
            border: 1px solid #218838;
        }
        
        .text-success {
            color: #28a745 !important;
        }
        
        .alert-warning {
            background: #f8f9fa;
            border: 1px solid #28a745;
            color: #000;
        }
        
        .ri-lock-2-line {
            color: #000 !important;
        }
        
        .btn-outline-danger {
            color: #000;
            border-color: #000;
        }
        
        .btn-outline-danger:hover {
            background: #000;
            color: white;
        }
        
        .password-strength {
            height: 3px;
            background: #f8f9fa;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 8px;
        }
        
        .password-strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            background: #28a745;
        }
    </style>

    <!-- Password Protection Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 text-center">
                    <div class="w-100">
                        <div class="mb-3">
                            <i class="ri-lock-2-line fs-1" style="color: #000;"></i>
                        </div>
                        <h5 class="modal-title" style="color: #000;">Archive Center Access</h5>
                        <p class="text-muted mb-0">Enter password to access archived records</p>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning border mb-3" style="background: #f8f9fa; border-color: #28a745 !important; color: #000;">
                        <div class="d-flex">
                            <i class="ri-shield-line me-3 fs-5" style="color: #28a745;"></i>
                            <div>
                                <strong>Security Notice</strong>
                                <p class="mb-0 small">Archive contains sensitive and confidential student records. Unauthorized access is prohibited.</p>
                            </div>
                        </div>
                    </div>

                    <form id="passwordForm">
                        <div class="mb-3">
                            <label for="archivePassword" class="form-label">
                                Password 
                                <small class="text-muted">
                                    (<span id="attemptCounter">3</span> attempts remaining)
                                </small>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="archivePassword" placeholder="Enter archive password" required autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                            <div id="passwordError" class="text-danger small mt-1" style="display: none;"></div>
                            
                            <!-- Password hint (optional) -->
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="ri-information-line me-1"></i>
                                    Hint: School archive password format - check with guidance office
                                </small>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="ri-unlock-line me-2"></i>Access Archive
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <small class="text-muted">
                        <i class="ri-information-line me-1"></i>
                        Contact system administrator if you need access
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content (Hidden by default) -->
    <div id="archiveContent" style="display: none;">
        <!-- Header -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-guidance">
                            <i class="ri-shield-check-line me-2 text-success"></i>Archive Center
                        </h1>
                        <p class="text-muted mb-0">View archived case meetings and completed counseling sessions</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('guidance.counseling-sessions.index') }}" class="btn btn-outline-secondary">
                            Back to Counseling Sessions
                        </a>
                        <button class="btn btn-outline-danger" onclick="lockArchive()">
                            <i class="ri-lock-line me-2"></i>Lock Archive
                        </button>
                        <button class="btn btn-outline-primary" onclick="location.reload()">
                            <i class="ri-refresh-line me-2"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                <input type="search" class="form-control" id="search-filter" placeholder="Search student name...">
                                <button class="btn btn-outline-secondary" onclick="clearFilters()">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Archive Date Range</label>
                            <input type="date" class="form-control" id="date-filter">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Archive Reason</label>
                            <select class="form-select" id="reason-filter">
                                <option value="">All Reasons</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="transferred">Transferred</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Archived Sessions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Archived Records</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="ri-more-line me-1"></i>Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportArchivedSessions()">
                                    <i class="ri-download-line me-2"></i>Export
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="printArchivedSessions()">
                                    <i class="ri-printer-line me-2"></i>Print
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-tabs mb-3" id="archiveTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active text-success" id="counseling-tab" data-bs-toggle="tab" data-bs-target="#counselingTabPane" type="button" role="tab" aria-controls="counselingTabPane" aria-selected="true">
                                <i class="ri-heart-pulse-line me-2"></i>Archived Counseling Sessions
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-success" id="meetings-tab" data-bs-toggle="tab" data-bs-target="#meetingsTabPane" type="button" role="tab" aria-controls="meetingsTabPane" aria-selected="false">
                                <i class="ri-team-line me-2"></i>Archived Case Meetings
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="archiveTabContent">
                        <!-- Counseling Sessions Tab -->
                        <div class="tab-pane fade show active" id="counselingTabPane" role="tabpanel" aria-labelledby="counseling-tab">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="archived-counseling-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student</th>
                                            <th>Recommended by</th>
                                            <th>Session Details</th>
                                            <th>Status</th>
                                            <th>Archive Info</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $counselingSessions = $archivedSessions->filter(function($record) {
                                                return strpos($record->type, 'counseling') !== false;
                                            });
                                        @endphp
                                        @forelse($counselingSessions as $session)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                                        <i class="ri-user-heart-line text-info"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $session->student_name ?? '-' }}</div>
                                                        <small class="text-muted">{{ $session->student_id_number ?? '-' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $session->recommended_by_name ?? '-' }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">Session #{{ $session->session_no ?? '-' }}</div>
                                                @if($session->start_date)
                                                    <small class="text-muted d-block">{{ $session->start_date->format('M d, Y') }}</small>
                                                @endif
                                                @if($session->time)
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($session->time)->format('h:i A') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($session->status ?? 'Unknown') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <small class="text-muted">Archived: {{ $session->archived_at ? $session->archived_at->format('M d, Y H:i') : '-' }}</small>
                                                </div>
                                                @if($session->archive_reason)
                                                    <div>
                                                        <small class="text-muted">Reason: {{ ucfirst($session->archive_reason) }}</small>
                                                    </div>
                                                @endif
                                                @if($session->archived_by)
                                                    <div>
                                                        <small class="text-muted">By: {{ $session->archived_by }}</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" onclick="viewArchivedRecord('{{ $session->type }}', {{ $session->id }})" title="View Details">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="ri-heart-pulse-line fs-1 d-block mb-2"></i>
                                                    <p>No archived counseling sessions found</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Case Meetings Tab -->
                        <div class="tab-pane fade" id="meetingsTabPane" role="tabpanel" aria-labelledby="meetings-tab">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="archived-meetings-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student</th>
                                            <th>Counselor</th>
                                            <th>Meeting Details</th>
                                            <th>Status</th>
                                            <th>Archive Info</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $caseMeetings = $archivedSessions->filter(function($record) {
                                                return strpos($record->type, 'meeting') !== false;
                                            });
                                        @endphp
                                        @forelse($caseMeetings as $meeting)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                                        <i class="ri-team-line text-warning"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $meeting->student_name ?? '-' }}</div>
                                                        <small class="text-muted">{{ $meeting->student_id_number ?? '-' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $meeting->counselor_name ?? '-' }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $meeting->meeting_type ?? 'Case Meeting' }}</div>
                                                @if($meeting->violation_description)
                                                    <small class="text-muted d-block">{{ Str::limit($meeting->violation_description, 50) }}</small>
                                                @endif
                                                @if($meeting->start_date)
                                                    <small class="text-muted d-block">{{ $meeting->start_date->format('M d, Y') }}</small>
                                                @endif
                                                @if($meeting->time)
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($meeting->time)->format('h:i A') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($meeting->status ?? 'Unknown') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <small class="text-muted">Archived: {{ $meeting->archived_at ? $meeting->archived_at->format('M d, Y H:i') : '-' }}</small>
                                                </div>
                                                @if($meeting->archive_reason)
                                                    <div>
                                                        <small class="text-muted">Reason: {{ ucfirst($meeting->archive_reason) }}</small>
                                                    </div>
                                                @endif
                                                @if($meeting->archived_by)
                                                    <div>
                                                        <small class="text-muted">By: {{ $meeting->archived_by }}</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" onclick="viewArchivedRecord('{{ $meeting->type }}', {{ $meeting->id }})" title="View Details">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="ri-team-line fs-1 d-block mb-2"></i>
                                                    <p>No archived case meetings found</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @if($archivedSessions->hasPages() || $archivedSessions->count() > 0)
                    <div class="card-footer bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Showing {{ $archivedSessions->firstItem() ?? 0 }} to {{ $archivedSessions->lastItem() ?? 0 }} of {{ $archivedSessions->total() }} {{ Str::plural('archived record', $archivedSessions->total()) }}
                            </div>
                            @if($archivedSessions->hasPages())
                                <div>
                                    {{ $archivedSessions->links('pagination.custom') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
    <!-- End Archive Content -->

    <!-- View Session Details Modal -->
    <div class="modal fade" id="viewSessionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Archived Record Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="sessionDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Bootstrap JS for modal functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Archive password configuration
        const ARCHIVE_PASSWORD = '{{ $archivePassword ?? "nsmsguidance" }}'; // Password from controller
        const PASSWORD_SESSION_KEY = 'archive_access_granted';
        const PASSWORD_TIMEOUT = 30 * 60 * 1000; // 30 minutes in milliseconds
        const MAX_FAILED_ATTEMPTS = 3;
        const LOCKOUT_TIME = 15 * 60 * 1000; // 15 minutes lockout after max failed attempts

        document.addEventListener('DOMContentLoaded', function() {
            checkArchiveAccess();
            initializePasswordProtection();
        });

        function checkArchiveAccess() {
            // Check if account is locked out
            const lockoutData = localStorage.getItem('archive_lockout');
            if (lockoutData) {
                const { timestamp } = JSON.parse(lockoutData);
                const now = new Date().getTime();
                
                if (now - timestamp < LOCKOUT_TIME) {
                    const timeLeft = Math.ceil((LOCKOUT_TIME - (now - timestamp)) / 60000);
                    showLockoutMessage(timeLeft);
                    return;
                }
                
                // Lockout expired, remove it
                localStorage.removeItem('archive_lockout');
                localStorage.removeItem('failed_attempts');
            }
            
            const accessData = sessionStorage.getItem(PASSWORD_SESSION_KEY);
            
            if (accessData) {
                const { timestamp } = JSON.parse(accessData);
                const now = new Date().getTime();
                
                // Check if access is still valid (within timeout period)
                if (now - timestamp < PASSWORD_TIMEOUT) {
                    showArchiveContent();
                    return;
                }
                
                // Access expired, remove from session storage
                sessionStorage.removeItem(PASSWORD_SESSION_KEY);
            }
            
            // Show password modal
            showPasswordModal();
        }

        function showLockoutMessage(minutesLeft) {
            document.body.innerHTML = `
                <div class="archive-locked-state">
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="ri-lock-2-line" style="font-size: 4rem; color: #000;"></i>
                        </div>
                        <h3 class="mb-3" style="color: #000;">Archive Access Temporarily Locked</h3>
                        <p class="text-muted mb-4">
                            Too many failed password attempts. Please try again in <strong>${minutesLeft} minutes</strong>.
                        </p>
                        <p class="small text-muted">
                            <i class="ri-information-line me-2" style="color: #28a745;"></i>
                            Contact your system administrator if you need immediate access.
                        </p>
                        <button class="btn btn-outline-dark mt-3" onclick="location.href='{{ route('guidance.counseling-sessions.index') }}'">
                            Back to Counseling Sessions
                        </button>
                    </div>
                </div>
            `;
        }

        function initializePasswordProtection() {
            // Update attempt counter on modal show
            updateAttemptCounter();
            
            // Password form submission
            document.getElementById('passwordForm').addEventListener('submit', function(e) {
                e.preventDefault();
                validatePassword();
            });

            // Toggle password visibility
            document.getElementById('togglePassword').addEventListener('click', function() {
                const passwordInput = document.getElementById('archivePassword');
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.className = 'ri-eye-off-line';
                } else {
                    passwordInput.type = 'password';
                    icon.className = 'ri-eye-line';
                }
            });

            // Clear error on input
            document.getElementById('archivePassword').addEventListener('input', function() {
                clearPasswordError();
            });

            // Add keyboard shortcuts
            document.getElementById('archivePassword').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    validatePassword();
                }
            });
        }

        function updateAttemptCounter() {
            const failedAttempts = parseInt(localStorage.getItem('failed_attempts') || '0');
            const remainingAttempts = MAX_FAILED_ATTEMPTS - failedAttempts;
            const counterElement = document.getElementById('attemptCounter');
            
            if (counterElement) {
                counterElement.textContent = Math.max(0, remainingAttempts);
                
                // Change color based on remaining attempts
                if (remainingAttempts <= 1) {
                    counterElement.style.color = '#000';
                    counterElement.style.fontWeight = 'bold';
                } else if (remainingAttempts <= 2) {
                    counterElement.style.color = '#000';
                } else {
                    counterElement.style.color = '#28a745';
                }
            }
        }

        function showPasswordModal() {
            const modal = new bootstrap.Modal(document.getElementById('passwordModal'));
            modal.show();
            
            // Focus on password input
            document.getElementById('archivePassword').focus();
        }

        function validatePassword() {
            const enteredPassword = document.getElementById('archivePassword').value;
            const submitButton = document.querySelector('#passwordForm button[type="submit"]');
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validating...';
            
            // Simulate validation delay for security
            setTimeout(() => {
                if (enteredPassword === ARCHIVE_PASSWORD) {
                    // Password correct - reset failed attempts
                    localStorage.removeItem('failed_attempts');
                    localStorage.removeItem('archive_lockout');
                    
                    const accessData = {
                        timestamp: new Date().getTime(),
                        user: '{{ Auth::user()->name ?? "Unknown" }}',
                        sessionId: generateSessionId()
                    };
                    
                    sessionStorage.setItem(PASSWORD_SESSION_KEY, JSON.stringify(accessData));
                    
                    // Hide modal and show content
                    const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal'));
                    modal.hide();
                    showArchiveContent();
                    
                    // Show success toast
                    showToast('Archive access granted successfully', 'success');
                    
                    // Log successful access
                    console.info('Archive access granted:', {
                        user: '{{ Auth::user()->name ?? "Unknown" }}',
                        timestamp: new Date().toISOString(),
                        sessionId: accessData.sessionId
                    });
                } else {
                    // Password incorrect - track failed attempts
                    handleFailedAttempt();
                }
                
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="ri-unlock-line me-2"></i>Access Archive';
                
                // Clear password field on failure
                if (enteredPassword !== ARCHIVE_PASSWORD) {
                    document.getElementById('archivePassword').value = '';
                    document.getElementById('archivePassword').focus();
                }
            }, 1000);
        }

        function handleFailedAttempt() {
            let failedAttempts = parseInt(localStorage.getItem('failed_attempts') || '0') + 1;
            localStorage.setItem('failed_attempts', failedAttempts.toString());
            
            const remainingAttempts = MAX_FAILED_ATTEMPTS - failedAttempts;
            
            if (failedAttempts >= MAX_FAILED_ATTEMPTS) {
                // Lock out the user
                const lockoutData = {
                    timestamp: new Date().getTime(),
                    attempts: failedAttempts
                };
                localStorage.setItem('archive_lockout', JSON.stringify(lockoutData));
                
                // Show lockout message
                showPasswordError(`Maximum attempts exceeded. Access locked for 15 minutes.`);
                
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                showPasswordError(`Incorrect password. ${remainingAttempts} attempt(s) remaining.`);
                updateAttemptCounter();
            }
            
            // Log failed attempt
            console.warn('Failed archive access attempt:', {
                user: '{{ Auth::user()->name ?? "Unknown" }}',
                timestamp: new Date().toISOString(),
                attempt: failedAttempts,
                ip: '{{ request()->ip() }}'
            });
        }

        function generateSessionId() {
            return 'arch_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        }

        function showPasswordError(message) {
            const errorDiv = document.getElementById('passwordError');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            
            // Add shake animation to password input
            const passwordInput = document.getElementById('archivePassword');
            passwordInput.classList.add('is-invalid');
            
            setTimeout(() => {
                passwordInput.classList.remove('is-invalid');
            }, 3000);
        }

        function clearPasswordError() {
            const errorDiv = document.getElementById('passwordError');
            errorDiv.style.display = 'none';
            
            const passwordInput = document.getElementById('archivePassword');
            passwordInput.classList.remove('is-invalid');
        }

        function showArchiveContent() {
            document.getElementById('archiveContent').style.display = 'block';
            
            // Add session indicator
            addSessionIndicator();
            
            // Start session timeout
            startSessionTimeout();
        }

        function addSessionIndicator() {
            // Remove existing indicator if any
            const existingIndicator = document.getElementById('sessionIndicator');
            if (existingIndicator) {
                existingIndicator.remove();
            }
            
            // Session indicator removed - no visual indicator needed
        }

        function startSessionTimer() {
            // Timer functionality removed - session timeout still active in background
        }

        function lockArchive() {
            if (confirm('Are you sure you want to lock the archive? You will need to enter the password again to access it.')) {
                sessionStorage.removeItem(PASSWORD_SESSION_KEY);
                location.reload();
            }
        }

        function startSessionTimeout() {
            // Set timeout to automatically lock archive after inactivity
            setTimeout(() => {
                const accessData = sessionStorage.getItem(PASSWORD_SESSION_KEY);
                if (accessData) {
                    sessionStorage.removeItem(PASSWORD_SESSION_KEY);
                    showToast('Archive session expired for security. Please enter password again.', 'warning');
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                }
            }, PASSWORD_TIMEOUT);
        }

        function showToast(message, type = 'info') {
            // Create toast container if it doesn't exist
            let toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toastContainer';
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }
            
            // Create toast element
            const toastId = 'toast_' + Date.now();
            const backgroundColor = type === 'success' ? '#28a745' : type === 'warning' ? '#f8f9fa' : '#000';
            const textColor = type === 'warning' ? '#000' : '#fff';
            const borderColor = type === 'warning' ? '#28a745' : backgroundColor;
            
            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center border" role="alert" style="background: ${backgroundColor}; color: ${textColor}; border-color: ${borderColor};">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="ri-${type === 'success' ? 'check-line' : type === 'warning' ? 'alert-line' : 'information-line'} me-2" style="color: ${type === 'warning' ? '#28a745' : textColor};"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" style="filter: ${type === 'warning' ? 'invert(1)' : 'none'};"></button>
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            // Show toast
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
            toast.show();
            
            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }

        function clearFilters() {
            document.getElementById('search-filter').value = '';
            document.getElementById('date-filter').value = '';
            document.getElementById('reason-filter').value = '';
            // Clear filters for both tables
            filterCounselingTable();
            filterMeetingsTable();
        }

        function exportArchivedSessions() {
            // Implementation for exporting archived sessions
            alert('Export functionality to be implemented');
        }

        function printArchivedSessions() {
            // Implementation for printing archived sessions
            window.print();
        }

        function viewArchivedRecord(type, recordId) {
            // Implementation for viewing record details using Bootstrap modal
            const modal = new bootstrap.Modal(document.getElementById('viewSessionModal'));
            modal.show();
            document.getElementById('sessionDetailsContent').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p>Loading...</p></div>';
            
            const isCounselingSession = type.includes('counseling');
            const recordType = isCounselingSession ? 'Counseling Session' : 'Case Meeting';
            
            // Use appropriate endpoint based on record type
            const endpoint = isCounselingSession 
                ? `/guidance/api/counseling-sessions/${recordId}`
                : `/guidance/case-meetings/${recordId}/summary`;
            
            // Fetch detailed record information
            fetch(endpoint, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'  // Include session cookies for Laravel auth
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const content = isCounselingSession 
                        ? generateCounselingSessionHTML(data.session)
                        : generateSummaryHTML(data.meeting);
                    document.getElementById('sessionDetailsContent').innerHTML = content;
                } else {
                    document.getElementById('sessionDetailsContent').innerHTML = `<div class="alert alert-danger">Failed to load ${recordType.toLowerCase()} details.</div>`;
                }
            })
            .catch(error => {
                document.getElementById('sessionDetailsContent').innerHTML = `<div class="alert alert-danger">An error occurred while loading the ${recordType.toLowerCase()} details.</div>`;
                console.error(error);
            });
        }



        // Function to generate HTML for counseling session details
        function generateCounselingSessionHTML(session) {
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
                                <p><strong>Name:</strong> ${session.student_full_name || 'Unknown'}</p>
                                <p><strong>Student ID:</strong> ${session.student ? (session.student.student_id || 'N/A') : 'N/A'}</p>
                                <p><strong>LRN:</strong> ${session.student_lrn || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Grade Level:</strong> ${session.student ? (session.student.grade_level || 'N/A') : 'N/A'}</p>
                                <p><strong>Age:</strong> ${session.student_age || 'N/A'}</p>
                                <p><strong>Gender:</strong> ${session.student_gender || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Counseling Details
            html += `
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Counseling Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Scheduled Date:</strong> ${session.scheduled_date ? new Date(session.scheduled_date).toLocaleDateString() : 'TBD'}</p>
                                <p><strong>Scheduled Time:</strong> ${session.scheduled_time ? new Date('1970-01-01T' + session.scheduled_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', hour12: true}) : 'TBD'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong> ${session.status_display || session.status || 'N/A'}</p>
                                <p><strong>Type:</strong> ${session.type || 'Individual Counseling'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Session Summary/Notes (if available)
            if (session.summary || session.notes || session.counseling_summary) {
                html += `
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Counseling Summary</h6>
                        </div>
                        <div class="card-body">
                            <p>${(session.summary || session.notes || session.counseling_summary || '').replace(/\n/g, '<br>')}</p>
                        </div>
                    </div>
                `;
            }

            // Recommendations (if available)
            if (session.recommendations) {
                html += `
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Recommendations</h6>
                        </div>
                        <div class="card-body">
                            <p>${session.recommendations.replace(/\n/g, '<br>')}</p>
                        </div>
                    </div>
                `;
            }

            // Reports/Documents
            html += `
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Reports</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            ${session.documents_html || '<span class="text-muted">No documents available</span>'}
                        </div>
                    </div>
                </div>
            `;

            // Additional session information
            if (session.scheduled_dates && session.scheduled_dates.length > 1) {
                html += `
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Session Schedule</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                `;

                session.scheduled_dates.forEach(date => {
                    html += `
                        <tr>
                            <td>${date}</td>
                            <td>${session.scheduled_time ? new Date('1970-01-01T' + session.scheduled_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', hour12: true}) : '-'}</td>
                            <td><span class="badge bg-success">${session.status_display || session.status || 'Scheduled'}</span></td>
                        </tr>
                    `;
                });

                html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
            }

            return html;
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
                                <p><strong>Meeting Type:</strong> ${meeting.meeting_type ? meeting.meeting_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Case Meeting'}</p>
                                <p><strong>Scheduled Date:</strong> ${meeting.scheduled_date ? new Date(meeting.scheduled_date).toLocaleDateString() : 'TBD'}</p>
                                <p><strong>Scheduled Time:</strong> ${meeting.scheduled_time ? new Date(meeting.scheduled_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', hour12: true}) : 'TBD'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Reports Card (PDF Attachments)
            let hasNarrative = meeting.student && meeting.violation_id && (meeting.student_statement || meeting.incident_feelings || meeting.action_plan);
            let hasTeacherObservation = meeting.id && (meeting.teacher_statement || meeting.action_plan);
            html += `
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Reports</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            <a href="/guidance/case-meetings/${meeting.id}/disciplinary-conference-report/pdf" target="_blank" class="btn btn-outline-primary btn-sm"><i class="ri-download-2-line me-2"></i> Disciplinary Conference Reports PDF</a>
                            ${hasNarrative
                                ? `<a href="/narrative-report/view/${meeting.student.id}/${meeting.violation_id}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="ri-attachment-2 me-2"></i> Student Narrative PDF</a>`
                                : ''}
                            ${hasTeacherObservation
                                ? `<a href="/guidance/observationreport/pdf/${meeting.id}" target="_blank" class="btn btn-outline-success btn-sm"><i class="ri-file-pdf-line me-2"></i> Teacher Observation Report PDF</a>`
                                : ''}
                            ${meeting.violation && meeting.violation.student_attachment_path
                                ? `<a href="/discipline/violations/${meeting.violation_id}/download-student-attachment" target="_blank" class="btn btn-outline-info btn-sm"><i class="ri-attachment-line me-2"></i> Student Attachment</a>`
                                : ''}
                            ${!hasNarrative && !hasTeacherObservation && (!meeting.violation || !meeting.violation.student_attachment_path)
                                ? '<span class="text-muted small">No Attachment</span>'
                                : ''}
                        </div>
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
                                    ${sanction.approved_at ? `<p class="small text-muted mt-1">Approved on ${new Date(sanction.approved_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true })}</p>` : ''}
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

        // Simple filtering functionality
        document.getElementById('search-filter')?.addEventListener('input', function() {
            filterCounselingTable();
            filterMeetingsTable();
        });

        document.getElementById('date-filter')?.addEventListener('change', function() {
            filterCounselingTable();
            filterMeetingsTable();
        });

        document.getElementById('reason-filter')?.addEventListener('change', function() {
            filterCounselingTable();
            filterMeetingsTable();
        });

        function filterCounselingTable() {
            const searchTerm = document.getElementById('search-filter')?.value.toLowerCase() || '';
            const dateFilter = document.getElementById('date-filter')?.value || '';
            const reasonFilter = document.getElementById('reason-filter')?.value || '';
            
            const rows = document.querySelectorAll('#archived-counseling-table tbody tr');
            
            rows.forEach(row => {
                if (row.querySelector('td')) { // Skip empty state row
                    const studentName = row.cells[0].textContent.toLowerCase();
                    const archiveDate = row.cells[4].textContent;
                    const archiveReason = row.cells[4].textContent.toLowerCase();
                    
                    let showRow = true;
                    
                    if (searchTerm && !studentName.includes(searchTerm)) {
                        showRow = false;
                    }
                    
                    if (dateFilter && !archiveDate.includes(dateFilter)) {
                        showRow = false;
                    }
                    
                    if (reasonFilter && !archiveReason.includes(reasonFilter.toLowerCase())) {
                        showRow = false;
                    }
                    
                    row.style.display = showRow ? '' : 'none';
                }
            });
        }

        function filterMeetingsTable() {
            const searchTerm = document.getElementById('search-filter')?.value.toLowerCase() || '';
            const dateFilter = document.getElementById('date-filter')?.value || '';
            const reasonFilter = document.getElementById('reason-filter')?.value || '';
            
            const rows = document.querySelectorAll('#archived-meetings-table tbody tr');
            
            rows.forEach(row => {
                if (row.querySelector('td')) { // Skip empty state row
                    const studentName = row.cells[0].textContent.toLowerCase();
                    const archiveDate = row.cells[4].textContent;
                    const archiveReason = row.cells[4].textContent.toLowerCase();
                    
                    let showRow = true;
                    
                    if (searchTerm && !studentName.includes(searchTerm)) {
                        showRow = false;
                    }
                    
                    if (dateFilter && !archiveDate.includes(dateFilter)) {
                        showRow = false;
                    }
                    
                    if (reasonFilter && !archiveReason.includes(reasonFilter.toLowerCase())) {
                        showRow = false;
                    }
                    
                    row.style.display = showRow ? '' : 'none';
                }
            });
        }
    </script>
</x-guidance-layout>
