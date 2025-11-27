<x-guidance-layout>
    @vite(['resources/css/index_guidance.css', 'resources/js/archive-center.js'])
    
    <!-- Meta tags for JavaScript configuration -->
    <meta name="archive-password" content="{{ $archivePassword ?? 'nsmsguidance' }}">
    <meta name="user-name" content="{{ Auth::user()->name ?? 'Unknown' }}">
    <meta name="user-ip" content="{{ request()->ip() }}">
    <meta name="back-url" content="{{ route('guidance.counseling-sessions.index') }}">
    
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
                <div>
                    <h1 class="h3 mb-0 text-guidance">
                        <i class="ri-shield-check-line me-2 text-success"></i>Archive Center
                    </h1>
                    <p class="text-muted mb-0">View archived case meetings and completed counseling sessions</p>
                </div>
            </div>
        </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                <input type="search" class="form-control" id="search-filter" placeholder="Search student name...">
                                <button class="btn btn-outline-secondary" onclick="clearFilters()">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Archive Date Range</label>
                            <input type="date" class="form-control" id="date-filter">
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
</x-guidance-layout>