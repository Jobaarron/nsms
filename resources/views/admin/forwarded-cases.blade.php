
<x-admin-layout>
<div class="container-fluid px-4">
    <!-- Modern Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div class="d-flex align-items-center gap-3">
            <div class="p-3 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                <i class="ri-file-list-3-line fs-2 text-white"></i>
            </div>
            <div>
                <h1 class="fw-bold mb-1 text-dark">Case Management</h1>
                <p class="text-muted mb-0 fs-6">Review and manage disciplinary case meetings</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="text-end">
                <small class="text-muted d-block">Last updated</small>
                <span class="fw-semibold text-dark">{{ date('M d, Y') }}</span>
            </div>
        </div>
    </div>

    <style>
        /* Modern Card Design */
        .modern-card {
            border-radius: 24px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            background: #fff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        
        .modern-card:hover {
            box-shadow: 0 12px 50px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .modern-card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid rgba(25, 135, 84, 0.1);
            padding: 1.75rem 2rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: #2d3748;
            letter-spacing: -0.025em;
        }
        
        .forwarded-header {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
        }
        
        .history-header {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
        }

        /* Modern Table Design */
        .modern-table {
            border: none;
            background: transparent;
        }
        
        .modern-table thead th {
            background: #f8f9fa;
            color: #495057;
            font-weight: 700;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: none;
            padding: 1.25rem 1.5rem;
            border-top: 3px solid #198754;
        }
        
        .modern-table tbody tr {
            background: #fff;
            border: none;
            transition: all 0.2s ease;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }
        
        .modern-table tbody tr:hover {
            background: #f8fff9;
            transform: translateX(4px);
            box-shadow: 4px 0 12px rgba(25, 135, 84, 0.1);
        }
        
        .modern-table tbody tr:last-child {
            border-bottom: none;
        }
        
        .modern-table td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            border: none;
            font-size: 0.925rem;
        }

        /* Modern Badges */
        .modern-badge {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            border: 2px solid transparent;
        }
        
        .badge-warning-modern {
            background: linear-gradient(135deg, #ffc107 0%, #ffca28 100%);
            color: #664d03;
            border-color: rgba(255, 193, 7, 0.3);
        }
        
        .badge-success-modern {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
        }
        
        .badge-info-modern {
            background: linear-gradient(135deg, #0dcaf0 0%, #31d2f2 100%);
            color: white;
        }
        
        .badge-secondary-modern {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
        }

        /* Modern Action Buttons */
        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: currentColor;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .action-btn:hover::before {
            opacity: 0.1;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .btn-info-modern {
            background: #e3f2fd;
            color: #1976d2;
            border-color: rgba(25, 118, 210, 0.2);
        }
        
        .btn-success-modern {
            background: #e8f5e8;
            color: #2e7d32;
            border-color: rgba(46, 125, 50, 0.2);
        }
        
        .btn-warning-modern {
            background: #fff3e0;
            color: #f57c00;
            border-color: rgba(245, 124, 0, 0.2);
        }

        /* Modern Tabs */
        .nav-tabs {
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 2rem;
        }
        
        .nav-tabs .nav-link {
            border: none;
            border-radius: 12px 12px 0 0;
            padding: 1rem 2rem;
            font-weight: 600;
            color: #6c757d;
            background: transparent;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
            border-bottom: 3px solid #198754;
        }
        
        .nav-tabs .nav-link:hover:not(.active) {
            background: #e8f5e8;
            color: #198754;
        }

        /* Cell Content Styling */
        .cell-primary {
            font-weight: 600;
            color: #2d3748;
            line-height: 1.4;
        }
        
        .cell-secondary {
            font-size: 0.85rem;
            color: #718096;
            margin-top: 0.25rem;
            line-height: 1.3;
        }
        
        .cell-highlight {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 8px;
            padding: 0.5rem;
            margin: -0.5rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #718096;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        /* Modern Pagination - 10 rows per page */
        .modern-pagination {
            background: #f8f9fa;
            border-radius: 0 0 24px 24px;
            padding: 1.5rem 2rem;
            position: relative;
        }
        
        .modern-pagination::before {
            content: '10 rows per page';
            position: absolute;
            top: 0.5rem;
            right: 1rem;
            font-size: 0.75rem;
            color: #6c757d;
            background: #fff;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }
        
        .modern-pagination .pagination {
            margin: 0;
        }
        
        .modern-pagination .page-link {
            border: none;
            border-radius: 12px;
            color: #198754;
            font-weight: 600;
            margin: 0 0.25rem;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }
        
        .modern-pagination .page-link:hover {
            background: #e8f5e8;
            color: #198754;
            transform: translateY(-1px);
        }
        
        .modern-pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            border-color: #198754;
            color: white;
        }
        
        .modern-pagination .page-item.disabled .page-link {
            color: #6c757d;
            background: transparent;
        }
        
        .pagination-info {
            font-size: 0.9rem;
            color: #198754;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .pagination-wrapper {
            border-top: 1px solid rgba(0, 0, 0, 0.06);
        }
        
        .modern-pagination .page-link:focus {
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.2);
        }
        
        .table-footer {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 0 0 24px 24px;
            border-top: 1px solid rgba(25, 135, 84, 0.1);
        }

        /* Modern Search */
        .search-container {
            background: #f8f9fa;
            border-radius: 24px 24px 0 0;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(25, 135, 84, 0.1);
        }
        
        .search-input {
            border: 2px solid transparent;
            border-radius: 50px;
            padding: 0.75rem 1.25rem 0.75rem 3rem;
            font-size: 0.95rem;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            border-color: #198754;
            box-shadow: 0 4px 16px rgba(25, 135, 84, 0.15);
            outline: none;
        }
        
        .search-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #198754;
            font-size: 1.1rem;
        }
        
        .search-wrapper {
            position: relative;
            max-width: 400px;
        }
        
        .clear-search {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        
        .clear-search:hover {
            background: #e9ecef;
            color: #198754;
        }
    </style>

    <!-- Modern Tab Navigation -->
    <ul class="nav nav-tabs" id="caseMeetingTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active d-flex align-items-center gap-2" id="forwarded-tab" data-bs-toggle="tab" data-bs-target="#forwarded" type="button" role="tab" aria-controls="forwarded" aria-selected="true">
                <i class="ri-file-forward-line"></i>
                <span>Active Cases</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-2" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="false">
                <i class="ri-history-line"></i>
                <span>Case History</span>
            </button>
        </li>
    </ul>
    <div class="tab-content" id="caseMeetingTabsContent">
        <!-- Active Cases Tab -->
        <div class="tab-pane fade show active" id="forwarded" role="tabpanel" aria-labelledby="forwarded-tab">
            <div class="modern-card">
                <div class="modern-card-header forwarded-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <i class="ri-file-forward-line" style="font-size: 1.5rem;"></i>
                        <div>
                            <div class="fw-bold">Active Case Meetings</div>
                            <small style="opacity: 0.9;">Cases requiring administrative review</small>
                        </div>
                    </div>
                    @php
                        $forwardedMeetings = $caseMeetings->filter(function($meeting) { return $meeting->status !== 'case_closed'; });
                    @endphp
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white text-success fw-bold fs-6 px-3 py-2 rounded-pill">{{ $forwardedMeetings->count() }} Cases</span>
                        <small class="text-white-50">{{ $caseMeetings->total() ?? $forwardedMeetings->count() }} total</small>
                    </div>
                </div>
                
                <!-- Search Container for Active Cases -->
                <div class="search-container">
                    <div class="d-flex align-items-center gap-3">
                        <div class="search-wrapper flex-grow-1">
                            <i class="ri-search-line search-icon"></i>
                            <input type="text" class="form-control search-input" id="searchActive" placeholder="Search by student name, ID, or violation...">
                            <button type="button" class="clear-search d-none" id="clearSearchActive">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <select class="form-select" id="statusFilter" style="width: auto; border-radius: 12px;">
                                <option value="">All Status</option>
                                <option value="submitted">Submitted</option>
                                <option value="forwarded">Forwarded</option>
                            </select>
                            <button type="button" class="btn btn-outline-success rounded-pill px-3" id="resetFiltersActive" title="Reset all filters">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-0">
                    @if($forwardedMeetings->count() > 0)
                    <div class="table-responsive">
                        <table class="table modern-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th><i class="ri-calendar-line me-2"></i>Date & Time</th>
                                    <th><i class="ri-user-line me-2"></i>Student</th>
                                    <th><i class="ri-shield-line me-2"></i>Violation</th>
                                    <th><i class="ri-gavel-line me-2"></i>Sanctions</th>
                                    <th><i class="ri-flag-line me-2"></i>Status</th>
                                    <th><i class="ri-settings-line me-2"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($forwardedMeetings as $meeting)
                                <tr>
                                    <!-- Date cell -->
                                    <td>
                                        <div class="cell-primary">{{ $meeting->scheduled_date->format('M d, Y') }}</div>
                                        <div class="cell-secondary">
                                            <i class="ri-time-line me-1"></i>
                                            {{ $meeting->scheduled_time ? $meeting->scheduled_time->format('h:i A') : 'TBD' }}
                                        </div>
                                    </td>
                                    <!-- Student cell -->
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="ri-user-line text-muted"></i>
                                            </div>
                                            <div>
                                                <div class="cell-primary">{{ $meeting->student ? $meeting->student->full_name : 'Unknown' }}</div>
                                                <div class="cell-secondary">
                                                    <i class="ri-hashtag me-1"></i>
                                                    {{ $meeting->student ? $meeting->student->student_id : 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Violation cell -->
                                    <td>
                                        @if($meeting->violation)
                                            <div class="cell-highlight">
                                                <div class="cell-primary">{{ $meeting->violation->title }}</div>
                                            </div>
                                        @elseif($meeting->sanctions->count() > 0 && $meeting->sanctions->first()->violation)
                                            <div class="cell-highlight">
                                                <div class="cell-primary">{{ $meeting->sanctions->first()->violation->title }}</div>
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">No violation specified</span>
                                        @endif
                                    </td>
                                    <!-- Sanctions cell -->
                                    <td>
                                        @php
                                            $sanctions = [];
                                            if ($meeting->written_reflection) $sanctions[] = ['icon' => 'ri-file-edit-line', 'text' => 'Written Reflection', 'color' => 'text-primary'];
                                            if ($meeting->mentorship_counseling) $sanctions[] = ['icon' => 'ri-user-heart-line', 'text' => 'Mentorship/Counseling', 'color' => 'text-info'];
                                            if ($meeting->parent_teacher_communication) $sanctions[] = ['icon' => 'ri-parent-line', 'text' => 'Parent Communication', 'color' => 'text-warning'];
                                            if ($meeting->restorative_justice_activity) $sanctions[] = ['icon' => 'ri-hand-heart-line', 'text' => 'Restorative Justice', 'color' => 'text-success'];
                                            if ($meeting->follow_up_meeting) $sanctions[] = ['icon' => 'ri-calendar-check-line', 'text' => 'Follow-up Meeting', 'color' => 'text-secondary'];
                                            if ($meeting->community_service) $sanctions[] = ['icon' => 'ri-community-line', 'text' => 'Community Service', 'color' => 'text-primary'];
                                            if ($meeting->suspension) $sanctions[] = ['icon' => 'ri-pause-circle-line', 'text' => 'Suspension', 'color' => 'text-danger'];
                                            if ($meeting->expulsion) $sanctions[] = ['icon' => 'ri-close-circle-line', 'text' => 'Expulsion', 'color' => 'text-danger'];
                                        @endphp
                                        @if(count($sanctions) > 0)
                                            <div class="d-flex flex-column gap-1">
                                                @foreach(array_slice($sanctions, 0, 2) as $sanction)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="{{ $sanction['icon'] }} {{ $sanction['color'] }}"></i>
                                                        <span class="cell-primary small">{{ $sanction['text'] }}</span>
                                                    </div>
                                                @endforeach
                                                @if(count($sanctions) > 2)
                                                    <small class="text-muted">+{{ count($sanctions) - 2 }} more</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">No sanctions assigned</span>
                                        @endif
                                    </td>
                                    <!-- Status cell -->
                                    <td>
                                        <span class="modern-badge badge-warning-modern">
                                            {{ $meeting->status === 'forwarded' ? 'Submitted' : ucfirst($meeting->status) }}
                                        </span>
                                    </td>
                                    <!-- Actions cell -->
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            <!-- View Summary Report Button -->
                                            <button class="action-btn btn-info-modern view-summary-btn" data-meeting-id="{{ $meeting->id }}" title="View Summary Report" data-bs-toggle="modal" data-bs-target="#viewSummaryModal">
                                                <i class="ri-file-text-line"></i>
                                            </button>
                                            
                                            @if($meeting->status === 'submitted')
                                                <!-- Approve Button -->
                                                <button class="action-btn btn-success-modern approve-case-meeting-btn" data-meeting-id="{{ $meeting->id }}" title="Approve Case">
                                                    <i class="ri-check-line"></i>
                                                </button>
                                                
                                                <!-- Edit Sanction Button -->
                                                <button class="action-btn btn-warning-modern revise-sanction-btn" data-meeting-id="{{ $meeting->id }}" title="Edit Sanction" data-bs-toggle="modal" data-bs-target="#reviseSanctionModal">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination for active cases -->
                    @if(isset($caseMeetings) && method_exists($caseMeetings, 'hasPages') && $caseMeetings->hasPages())
                        <div class="modern-pagination">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="pagination-info">
                                    <i class="ri-file-list-line me-2 text-success"></i>
                                    Showing {{ ($caseMeetings->currentPage() - 1) * 10 + 1 }} 
                                    to {{ min($caseMeetings->currentPage() * 10, $caseMeetings->total()) }} 
                                    of {{ $caseMeetings->total() }} active cases
                                </div>
                                <div>
                                    {{ $caseMeetings->appends(request()->query())->links('pagination.custom') }}
                                </div>
                            </div>
                        </div>
                    @elseif($forwardedMeetings->count() > 10)
                        <div class="modern-pagination">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="pagination-info">
                                    <i class="ri-file-list-line me-2 text-success"></i>
                                    Showing {{ $forwardedMeetings->count() }} active cases (10 per page)
                                </div>
                            </div>
                        </div>
                    @elseif($forwardedMeetings->count() > 0)
                        <div class="modern-pagination">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="pagination-info">
                                    <i class="ri-file-list-line me-2 text-success"></i>
                                    Showing all {{ $forwardedMeetings->count() }} active cases
                                </div>
                            </div>
                        </div>
                    @endif
                    @else
                    <div class="empty-state">
                        <i class="ri-folder-open-line"></i>
                        <h4 class="mb-2">No Active Cases</h4>
                        <p class="mb-0">All case meetings have been reviewed and processed.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- Case History Tab -->
        <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
            <div class="modern-card">
                <div class="modern-card-header history-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <i class="ri-history-line" style="font-size: 1.5rem;"></i>
                        <div>
                            <div class="fw-bold">Case Meeting History</div>
                            <small style="opacity: 0.9;">Archived and completed case meetings</small>
                        </div>
                    </div>
                    @if(isset($archivedMeetings))
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-white text-success fw-bold fs-6 px-3 py-2 rounded-pill">{{ $archivedMeetings->count() }} Archived</span>
                            <small class="text-white-50">{{ $archivedMeetings->total() ?? $archivedMeetings->count() }} total</small>
                        </div>
                    @endif
                </div>
                
                <!-- Search Container for History -->
                <div class="search-container">
                    <div class="d-flex align-items-center gap-3">
                        <div class="search-wrapper flex-grow-1">
                            <i class="ri-search-line search-icon"></i>
                            <input type="text" class="form-control search-input" id="searchHistory" placeholder="Search by student name, ID, or violation...">
                            <button type="button" class="clear-search d-none" id="clearSearchHistory">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <select class="form-select" id="archiveReasonFilter" style="width: auto; border-radius: 12px;">
                                <option value="">All Reasons</option>
                                <option value="approved">Approved</option>
                                <option value="closed">Closed</option>
                                <option value="completed">Completed</option>
                            </select>
                            <button type="button" class="btn btn-outline-success rounded-pill px-3" id="resetFiltersHistory" title="Reset all filters">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-0">
                    @if(isset($archivedMeetings) && $archivedMeetings->count() > 0)
                    <div class="table-responsive">
                        <table class="table modern-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th><i class="ri-calendar-line me-2"></i>Date & Time</th>
                                    <th><i class="ri-user-line me-2"></i>Student</th>
                                    <th><i class="ri-shield-line me-2"></i>Violation</th>
                                    <th><i class="ri-gavel-line me-2"></i>Sanctions</th>
                                    <th><i class="ri-flag-line me-2"></i>Status</th>
                                    <th><i class="ri-settings-line me-2"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($archivedMeetings as $meeting)
                                <tr>
                                    <!-- Date cell -->
                                    <td>
                                        <div class="cell-primary">{{ $meeting->scheduled_date ? $meeting->scheduled_date->format('M d, Y') : 'N/A' }}</div>
                                        <div class="cell-secondary">
                                            <i class="ri-archive-line me-1"></i>
                                            Archived: {{ $meeting->archived_at->format('M d, Y') }}
                                        </div>
                                    </td>
                                    <!-- Student cell -->
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="ri-user-line text-muted"></i>
                                            </div>
                                            <div>
                                                <div class="cell-primary">{{ $meeting->student ? $meeting->student->full_name : 'Unknown' }}</div>
                                                <div class="cell-secondary">
                                                    <i class="ri-hashtag me-1"></i>
                                                    {{ $meeting->student ? $meeting->student->student_id : 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Violation cell -->
                                    <td>
                                        @if($meeting->violation)
                                            <div class="cell-highlight">
                                                <div class="cell-primary">{{ $meeting->violation->title }}</div>
                                            </div>
                                        @elseif($meeting->sanctions->count() > 0 && $meeting->sanctions->first()->violation)
                                            <div class="cell-highlight">
                                                <div class="cell-primary">{{ $meeting->sanctions->first()->violation->title }}</div>
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">No violation specified</span>
                                        @endif
                                    </td>
                                    <!-- Sanctions cell -->
                                    <td>
                                        @php
                                            $sanctions = [];
                                            if ($meeting->written_reflection) $sanctions[] = ['icon' => 'ri-file-edit-line', 'text' => 'Written Reflection', 'color' => 'text-primary'];
                                            if ($meeting->mentorship_counseling) $sanctions[] = ['icon' => 'ri-user-heart-line', 'text' => 'Mentorship/Counseling', 'color' => 'text-info'];
                                            if ($meeting->parent_teacher_communication) $sanctions[] = ['icon' => 'ri-parent-line', 'text' => 'Parent Communication', 'color' => 'text-warning'];
                                            if ($meeting->restorative_justice_activity) $sanctions[] = ['icon' => 'ri-hand-heart-line', 'text' => 'Restorative Justice', 'color' => 'text-success'];
                                            if ($meeting->follow_up_meeting) $sanctions[] = ['icon' => 'ri-calendar-check-line', 'text' => 'Follow-up Meeting', 'color' => 'text-secondary'];
                                            if ($meeting->community_service) $sanctions[] = ['icon' => 'ri-community-line', 'text' => 'Community Service', 'color' => 'text-primary'];
                                            if ($meeting->suspension) $sanctions[] = ['icon' => 'ri-pause-circle-line', 'text' => 'Suspension', 'color' => 'text-danger'];
                                            if ($meeting->expulsion) $sanctions[] = ['icon' => 'ri-close-circle-line', 'text' => 'Expulsion', 'color' => 'text-danger'];
                                        @endphp
                                        @if(count($sanctions) > 0)
                                            <div class="d-flex flex-column gap-1">
                                                @foreach(array_slice($sanctions, 0, 2) as $sanction)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="{{ $sanction['icon'] }} {{ $sanction['color'] }}"></i>
                                                        <span class="cell-primary small">{{ $sanction['text'] }}</span>
                                                    </div>
                                                @endforeach
                                                @if(count($sanctions) > 2)
                                                    <small class="text-muted">+{{ count($sanctions) - 2 }} more</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">No sanctions assigned</span>
                                        @endif
                                    </td>
                                    <!-- Status cell -->
                                    <td>
                                        @if($meeting->archive_reason === 'approved')
                                            <span class="modern-badge badge-success-modern">Approved</span>
                                        @elseif($meeting->archive_reason === 'closed')
                                            <span class="modern-badge badge-secondary-modern">Closed</span>
                                        @elseif($meeting->archive_reason === 'completed')
                                            <span class="modern-badge badge-info-modern">Completed</span>
                                        @else
                                            <span class="modern-badge badge-info-modern">{{ ucfirst($meeting->archive_reason) }}</span>
                                        @endif
                                    </td>
                                    <!-- Actions cell -->
                                    <td>
                                        <button class="action-btn btn-info-modern view-summary-btn" data-meeting-id="{{ $meeting->id }}" title="View Summary Report" data-bs-toggle="modal" data-bs-target="#viewSummaryModal">
                                            <i class="ri-file-text-line"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination for archived meetings -->
                    @if(isset($archivedMeetings) && $archivedMeetings->hasPages())
                        <div class="modern-pagination">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="pagination-info">
                                    <i class="ri-archive-line me-2 text-success"></i>
                                    Showing {{ ($archivedMeetings->currentPage() - 1) * 10 + 1 }} 
                                    to {{ min($archivedMeetings->currentPage() * 10, $archivedMeetings->total()) }} 
                                    of {{ $archivedMeetings->total() }} archived cases
                                </div>
                                <div>
                                    {{ $archivedMeetings->appends(request()->query())->links('pagination.custom') }}
                                </div>
                            </div>
                        </div>
                    @elseif(isset($archivedMeetings) && $archivedMeetings->count() > 10)
                        <div class="modern-pagination">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="pagination-info">
                                    <i class="ri-archive-line me-2 text-success"></i>
                                    Showing {{ $archivedMeetings->count() }} archived cases (10 per page)
                                </div>
                            </div>
                        </div>
                    @elseif(isset($archivedMeetings) && $archivedMeetings->count() > 0)
                        <div class="modern-pagination">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="pagination-info">
                                    <i class="ri-archive-line me-2 text-success"></i>
                                    Showing all {{ $archivedMeetings->count() }} archived cases
                                </div>
                            </div>
                        </div>
                    @endif
                    @else
                    <div class="empty-state">
                        <i class="ri-archive-line"></i>
                        <h4 class="mb-2">No Archived Cases</h4>
                        <p class="mb-0">No case meetings have been archived yet.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- View Summary Modal -->
    <!-- ...existing modal and script code... -->
    {{-- Filter/Search Bar removed --}}
</div>


    <!-- Modern View Summary Modal -->
    <div class="modal fade" id="viewSummaryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modern-card border-0">
                <div class="modal-header bg-gradient border-0" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%); border-radius: 24px 24px 0 0;">
                    <div class="d-flex align-items-center gap-3 text-white">
                        <i class="ri-file-text-line fs-4"></i>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Case Meeting Summary</h5>
                            <small style="opacity: 0.9;">Comprehensive case review report</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="summaryModalBody">
                    <!-- Content will be loaded dynamically -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-success">Loading case summary...</p>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light" style="border-radius: 0 0 24px 24px;">
                    <button type="button" class="btn btn-outline-success rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="ri-close-line me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Incident Form PDF Preview Modal (optional, for future use) -->
    <div class="modal fade" id="incidentFormPdfPreviewModal" tabindex="-1" aria-labelledby="incidentFormPdfPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content minimalist-modal">
                <div class="modal-header border-0 pb-0" style="background:transparent;">
                    <h5 class="modal-title fw-semibold" id="incidentFormPdfPreviewModalLabel" style="letter-spacing:0.5px;">Incident Form PDF Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="background: #fff; min-height: 600px;">
                    <iframe id="incidentFormPdfIframe" src="" width="100%" height="600px" style="border: none;"></iframe>
                </div>
                <div class="modal-footer border-0 pt-0" style="background:transparent;">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius:6px;box-shadow:none;">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Revise Sanction Modal -->
    <div class="modal fade" id="reviseSanctionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modern-card border-0">
                <div class="modal-header bg-gradient border-0" style="background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%); border-radius: 24px 24px 0 0;">
                    <div class="d-flex align-items-center gap-3 text-white">
                        <i class="ri-edit-line fs-4"></i>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Revise Sanctions</h5>
                            <small style="opacity: 0.9;">Modify case meeting sanctions</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="reviseSanctionForm">
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark mb-3">Current Sanctions from Guidance</label>
                            <div id="current-sanctions-display" class="alert alert-info border-0 rounded-4 bg-light">
                                <!-- Current sanctions will be loaded here -->
                            </div>
                        </div>
                        
                        <div class="row g-4">
                            <!-- Primary Interventions -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4">
                                    <h6 class="fw-bold text-success mb-3 d-flex align-items-center">
                                        <i class="ri-heart-pulse-line me-2"></i>
                                        Primary Interventions
                                    </h6>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="written_reflection" name="written_reflection">
                                        <label class="form-check-label fw-medium" for="written_reflection">
                                            <i class="ri-file-edit-line text-success me-2"></i>Written Reflection
                                        </label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="mentorship_counseling" name="mentorship_counseling">
                                        <label class="form-check-label fw-medium" for="mentorship_counseling">
                                            <i class="ri-user-heart-line text-success me-2"></i>Mentorship/Counseling
                                        </label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="parent_teacher_communication" name="parent_teacher_communication">
                                        <label class="form-check-label fw-medium" for="parent_teacher_communication">
                                            <i class="ri-parent-line text-warning me-2"></i>Parent-Teacher Communication
                                        </label>
                                    </div>

                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="restorative_justice_activity" name="restorative_justice_activity">
                                        <label class="form-check-label fw-medium" for="restorative_justice_activity">
                                            <i class="ri-hand-heart-line text-success me-2"></i>Restorative Justice Activity
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4">
                                    <h6 class="fw-bold text-warning mb-3 d-flex align-items-center">
                                        <i class="ri-alert-line me-2"></i>
                                        Additional Interventions
                                    </h6>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="follow_up_meeting" name="follow_up_meeting">
                                        <label class="form-check-label fw-medium" for="follow_up_meeting">
                                            <i class="ri-calendar-check-line text-success me-2"></i>Follow-up Meeting
                                        </label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="community_service" name="community_service">
                                        <label class="form-check-label fw-medium" for="community_service">
                                            <i class="ri-community-line text-success me-2"></i>Community Service
                                        </label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="suspension" name="suspension">
                                        <label class="form-check-label fw-medium" for="suspension">
                                            <i class="ri-pause-circle-line text-danger me-2"></i>Suspension
                                        </label>
                                    </div>

                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="expulsion" name="expulsion">
                                        <label class="form-check-label fw-medium" for="expulsion">
                                            <i class="ri-close-circle-line text-danger me-2"></i>Expulsion
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light gap-2" style="border-radius: 0 0 24px 24px;">
                        <button type="button" class="btn btn-outline-success rounded-pill px-4" data-bs-dismiss="modal">
                            <i class="ri-close-line me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4">
                            <i class="ri-edit-line me-2"></i>Update Sanctions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize search functionality
    initializeSearch();
    
    // Minimalist modal/card hover CSS
    const style = document.createElement('style');
    style.innerHTML = `
        /* Modern Modal Card Styles */
        .modal-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        .modal-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        .modal-card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid rgba(25, 135, 84, 0.1);
            font-weight: 700;
            font-size: 1.1rem;
            padding: 1.25rem 1.5rem;
            color: #2d3748;
        }
        .modal-card-body {
            padding: 1.5rem;
        }
        .modal-attachment-btn {
            border-radius: 12px;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .modal-attachment-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }
        .modal-attachment-btn.btn-outline-primary {
            background: #e8f5e8;
            color: #198754;
            border-color: rgba(25, 135, 84, 0.3);
        }
        .modal-attachment-btn.btn-outline-success {
            background: #e8f5e8;
            color: #2e7d32;
            border-color: rgba(46, 125, 50, 0.3);
        }
        .modal-attachment-btn.btn-outline-info {
            background: #e0f7fa;
            color: #00695c;
            border-color: rgba(0, 105, 92, 0.3);
        }
        .sanction-item {
            background: #f8f9fa;
            border-left: 4px solid #198754;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }
        .sanction-item:hover {
            background: #e8fff0;
            transform: translateX(4px);
        }
        .status-badge-modern {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
    `;
    document.head.appendChild(style);
    // View summary buttons
    const viewSummaryButtons = document.querySelectorAll('.view-summary-btn');
    viewSummaryButtons.forEach(button => {
        button.addEventListener('click', function () {
            const meetingId = this.getAttribute('data-meeting-id');
            loadSummaryReport(meetingId);
        });
    });

    // Approve sanction buttons
    const approveButtons = document.querySelectorAll('.approve-sanction-btn');
    approveButtons.forEach(button => {
        button.addEventListener('click', function () {
            const sanctionId = this.getAttribute('data-sanction-id');
            if (confirm('Are you sure you want to approve this sanction? This will mark the case meeting as completed.')) {
                fetch(`/admin/sanctions/${sanctionId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        // Reload page to update button states
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('An error occurred while approving the sanction.');
                    console.error(error);
                });
            }
        });
    });

    // Reject sanction buttons
    const rejectButtons = document.querySelectorAll('.reject-sanction-btn');
    rejectButtons.forEach(button => {
        button.addEventListener('click', function () {
            const sanctionId = this.getAttribute('data-sanction-id');
            if (confirm('Are you sure you want to reject this sanction? This action cannot be undone.')) {
                fetch(`/admin/sanctions/${sanctionId}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('An error occurred while rejecting the sanction.');
                    console.error(error);
                });
            }
        });
    });

    // Revise sanction buttons
    const reviseButtons = document.querySelectorAll('.revise-sanction-btn');
    reviseButtons.forEach(button => {
        button.addEventListener('click', function () {
            const meetingId = this.getAttribute('data-meeting-id');

            // Reset modal
            resetReviseSanctionModal();
            
            // Load current sanctions
            loadCurrentSanctions(meetingId);

            // Store meeting ID for form submission
            document.getElementById('reviseSanctionForm').setAttribute('data-meeting-id', meetingId);
        });
    });

    // Handle revise sanction form submission
    document.getElementById('reviseSanctionForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const meetingId = this.getAttribute('data-meeting-id');
        const formData = new FormData(this);

        fetch(`/admin/case-meetings/${meetingId}/sanctions`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                // Close modal and reload page
                const modal = bootstrap.Modal.getInstance(document.getElementById('reviseSanctionModal'));
                modal.hide();
                location.reload();
            }
        })
        .catch(error => {
            alert('An error occurred while revising the sanction.');
            console.error(error);
        });
    });
});

// Function to reset the revise sanction modal
function resetReviseSanctionModal() {
    const dropdown = document.getElementById('revise-sanction-dropdown');
    const customField = document.getElementById('custom-sanction-field');
    const customTextarea = document.getElementById('revise-sanction-custom');
    
    dropdown.value = '';
    customField.style.display = 'none';
    customTextarea.removeAttribute('required');
    customTextarea.value = '';
}



// Function to load summary report
function loadSummaryReport(meetingId) {
    const modalBody = document.getElementById('summaryModalBody');

    // Show loading spinner
    modalBody.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    fetch(`/admin/case-meetings/${meetingId}/summary`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const meeting = data.meeting;
            modalBody.innerHTML = generateSummaryHTML(meeting);
        } else {
            modalBody.innerHTML = '<div class="alert alert-danger">Failed to load summary report.</div>';
        }
    })
    .catch(error => {
        modalBody.innerHTML = '<div class="alert alert-danger">An error occurred while loading the summary report.</div>';
        console.error(error);
    });
}

// Function to generate HTML for summary report
function generateSummaryHTML(meeting) {
    let html = '';

    // Student Information
    html += `
        <div class="modal-card">
            <div class="modal-card-header d-flex align-items-center gap-2">
                <i class="ri-user-line text-success"></i>
                <span>Student Information</span>
            </div>
            <div class="modal-card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-success-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ri-user-line text-success fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">${meeting.student ? meeting.student.full_name : 'Unknown'}</h6>
                                <small class="text-muted">Full Name</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ri-hashtag text-success fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">${meeting.student ? meeting.student.student_id : 'N/A'}</h6>
                                <small class="text-muted">Student ID</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ri-graduation-cap-line text-warning fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">${meeting.student ? meeting.student.grade_level : 'N/A'}</h6>
                                <small class="text-muted">Grade Level</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Meeting Details
    html += `
        <div class="modal-card">
            <div class="modal-card-header d-flex align-items-center gap-2">
                <i class="ri-calendar-event-line text-success"></i>
                <span>Meeting Details</span>
            </div>
            <div class="modal-card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ri-file-list-line text-success fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">${meeting.meeting_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</h6>
                                <small class="text-muted">Meeting Type</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ri-calendar-line text-success fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">${meeting.scheduled_date ? new Date(meeting.scheduled_date).toLocaleDateString() : 'TBD'}</h6>
                                <small class="text-muted">Scheduled Date</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ri-time-line text-warning fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">${meeting.scheduled_time ? meeting.scheduled_time : 'TBD'}</h6>
                                <small class="text-muted">Scheduled Time</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Reports Card (PDF Attachments)
    let hasNarrative = meeting.student && meeting.violation_id && (meeting.student_statement || meeting.incident_feelings || meeting.action_plan);
    let hasTeacherObservation = meeting.id && (meeting.teacher_statement || meeting.action_plan);
    html += `
        <div class="modal-card">
            <div class="modal-card-header d-flex align-items-center gap-2">
                <i class="ri-file-text-line text-success"></i>
                <span>Case Reports & Documents</span>
            </div>
            <div class="modal-card-body">
                <div class="d-flex flex-column gap-3">
                    <a href="/admin/case-meetings/${meeting.id}/disciplinary-conference-report/pdf" target="_blank" class="modal-attachment-btn btn-outline-success">
                        <i class="ri-download-2-line"></i>
                        <span>Disciplinary Conference Report</span>
                    </a>
                    ${hasNarrative
                        ? `<a href="/narrative-report/view/${meeting.student.id}/${meeting.violation_id}" target="_blank" class="modal-attachment-btn btn-outline-success">
                            <i class="ri-file-edit-line"></i>
                            <span>Student Narrative Report</span>
                        </a>`
                        : ''}
                    ${hasTeacherObservation
                        ? `<a href="/guidance/observationreport/pdf/${meeting.id}" target="_blank" class="modal-attachment-btn btn-outline-info">
                            <i class="ri-eye-line"></i>
                            <span>Teacher Observation Report</span>
                        </a>`
                        : ''}
                    ${meeting.violation && meeting.violation.student_attachment_path
                        ? `<a href="/discipline/violations/${meeting.violation_id}/download-student-attachment" target="_blank" class="modal-attachment-btn btn-outline-info">
                            <i class="ri-attachment-line"></i>
                            <span>Student Attachment</span>
                        </a>`
                        : ''}
                    ${!hasNarrative && !hasTeacherObservation && (!meeting.violation || !meeting.violation.student_attachment_path)
                        ? '<div class="text-center py-4"><i class="ri-file-line text-muted fs-3 mb-2 d-block"></i><span class="text-muted fst-italic">No documents available</span></div>'
                        : ''}
                </div>
            </div>
        </div>
    `;

    // Case Summary
    if (meeting.summary) {
        html += `
            <div class="modal-card">
                <div class="modal-card-header d-flex align-items-center gap-2">
                    <i class="ri-file-list-3-line text-primary"></i>
                    <span>Case Summary</span>
                </div>
                <div class="modal-card-body">
                    <div class="bg-light rounded-4 p-3">
                        <p class="mb-0 lh-lg">${meeting.summary.replace(/\n/g, '<br>')}</p>
                    </div>
                </div>
            </div>
        `;
    }

    // Recommendations
    if (meeting.recommendations) {
        html += `
            <div class="modal-card">
                <div class="modal-card-header d-flex align-items-center gap-2">
                    <i class="ri-lightbulb-line text-success"></i>
                    <span>Recommendations</span>
                </div>
                <div class="modal-card-body">
                    <div class="bg-success-subtle rounded-4 p-3 border-start border-success border-4">
                        <p class="mb-0 lh-lg">${meeting.recommendations.replace(/\n/g, '<br>')}</p>
                    </div>
                </div>
            </div>
        `;
    }

    // Sanctions
    if (meeting.sanctions && meeting.sanctions.length > 0) {
        html += `
            <div class="modal-card">
                <div class="modal-card-header d-flex align-items-center gap-2">
                    <i class="ri-gavel-line text-warning"></i>
                    <span>Applied Sanctions</span>
                </div>
                <div class="modal-card-body">
        `;

        meeting.sanctions.forEach((sanction, index) => {
            html += `
                <div class="sanction-item">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <span class="fw-bold text-warning small">${index + 1}</span>
                                </div>
                                <h6 class="mb-0 fw-bold">${sanction.sanction}</h6>
                            </div>
                            ${sanction.deportment_grade_action ? `<p class="mb-2"><i class="ri-star-line me-2 text-info"></i><strong>Deportment Action:</strong> ${sanction.deportment_grade_action}</p>` : ''}
                            ${sanction.suspension ? `<p class="mb-2"><i class="ri-pause-circle-line me-2 text-danger"></i><strong>Suspension:</strong> ${sanction.suspension}</p>` : ''}
                            ${sanction.notes ? `<p class="mb-0"><i class="ri-file-text-line me-2 text-success"></i><strong>Notes:</strong> ${sanction.notes.replace(/\n/g, '<br>')}</p>` : ''}
                        </div>
                        <div class="col-md-4 text-end">
                            ${sanction.is_approved 
                                ? '<span class="status-badge-modern bg-success text-white">Approved</span>' 
                                : '<span class="status-badge-modern bg-warning text-dark">Pending</span>'}
                            ${sanction.approved_at ? `<p class="small text-muted mt-2 mb-0"><i class="ri-time-line me-1"></i>Approved on ${new Date(sanction.approved_at).toLocaleString()}</p>` : ''}
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
            <div class="modal-card">
                <div class="modal-card-header d-flex align-items-center gap-2">
                    <i class="ri-sticky-note-line text-success"></i>
                    <span>Additional Notes</span>
                </div>
                <div class="modal-card-body">
                    <div class="bg-success-subtle rounded-4 p-3 border-start border-success border-4">
                        <p class="mb-0 lh-lg">${meeting.notes.replace(/\n/g, '<br>')}</p>
                    </div>
                </div>
            </div>
        `;
    }

    // President Notes
    if (meeting.president_notes) {
        html += `
            <div class="modal-card">
                <div class="modal-card-header d-flex align-items-center gap-2">
                    <i class="ri-vip-crown-line text-warning"></i>
                    <span>President Notes</span>
                </div>
                <div class="modal-card-body">
                    <div class="bg-warning-subtle rounded-4 p-3 border-start border-warning border-4">
                        <p class="mb-0 lh-lg">${meeting.president_notes.replace(/\n/g, '<br>')}</p>
                    </div>
                </div>
            </div>
        `;
    }

    return html;
}

// Load current sanctions for a case meeting
function loadCurrentSanctions(meetingId) {
    fetch(`/admin/case-meetings/${meetingId}/sanctions`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Display current sanctions
            const sanctionsDisplay = document.getElementById('current-sanctions-display');
            let currentSanctions = [];
            if (data.sanctions.written_reflection) currentSanctions.push('Written Reflection');
            if (data.sanctions.mentorship_counseling) currentSanctions.push('Mentorship/Counseling');
            if (data.sanctions.parent_teacher_communication) currentSanctions.push('Parent-Teacher Communication');
            if (data.sanctions.restorative_justice_activity) currentSanctions.push('Restorative Justice Activity');
            if (data.sanctions.follow_up_meeting) currentSanctions.push('Follow-up Meeting');
            if (data.sanctions.community_service) currentSanctions.push('Community Service');
            if (data.sanctions.suspension) currentSanctions.push('Suspension');
            if (data.sanctions.expulsion) currentSanctions.push('Expulsion');
            
            sanctionsDisplay.innerHTML = currentSanctions.length > 0 ? 
                currentSanctions.join(', ') : 'No sanctions currently set';
            
            // Set checkboxes based on current values
            document.getElementById('written_reflection').checked = data.sanctions.written_reflection;
            document.getElementById('mentorship_counseling').checked = data.sanctions.mentorship_counseling;
            document.getElementById('parent_teacher_communication').checked = data.sanctions.parent_teacher_communication;
            document.getElementById('restorative_justice_activity').checked = data.sanctions.restorative_justice_activity;
            document.getElementById('follow_up_meeting').checked = data.sanctions.follow_up_meeting;
            document.getElementById('community_service').checked = data.sanctions.community_service;
            document.getElementById('suspension').checked = data.sanctions.suspension;
            document.getElementById('expulsion').checked = data.sanctions.expulsion;
        }
    })
    .catch(error => {
        console.error('Error loading sanctions:', error);
        document.getElementById('current-sanctions-display').innerHTML = 'Error loading current sanctions';
    });
}

// Reset the revise sanction modal
function resetReviseSanctionModal() {
    // Uncheck all checkboxes
    const checkboxes = document.querySelectorAll('#reviseSanctionModal input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    
    // Clear the sanctions display
    const sanctionsDisplay = document.getElementById('current-sanctions-display');
    if (sanctionsDisplay) {
        sanctionsDisplay.innerHTML = 'Loading...';
    }
}

// Approve case meeting buttons
document.addEventListener('DOMContentLoaded', function() {
    const approveButtons = document.querySelectorAll('.approve-case-meeting-btn');
    console.log('Found approve buttons:', approveButtons.length);
    
    approveButtons.forEach(button => {
        button.addEventListener('click', function () {
            const meetingId = this.getAttribute('data-meeting-id');
            console.log('Approve button clicked for meeting:', meetingId);
            
            if (confirm('Are you sure you want to approve this case? It will be archived after approval.')) {
                console.log('Sending approve request to:', `/admin/case-meetings/${meetingId}/approve`);
                
                fetch(`/admin/case-meetings/${meetingId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    alert(data.message);
                    if (data.success) {
                        // Reload page to update view
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error details:', error);
                    alert('An error occurred while approving the case.');
                });
            }
        });
    });

    // Close case buttons
    const closeButtons = document.querySelectorAll('.close-case-btn');
    closeButtons.forEach(button => {
        button.addEventListener('click', function () {
            const meetingId = this.getAttribute('data-meeting-id');
            const notes = prompt('Add president notes (optional):');
            
            if (confirm('Are you sure you want to close this case? It will be archived after closure.')) {
                fetch(`/admin/cases/${meetingId}/close`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        president_notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        // Remove the row from the table or reload page
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('An error occurred while closing the case.');
                    console.error(error);
                });
            }
        });
    });
});

// Search functionality
function initializeSearch() {
    // Active cases search
    const searchActive = document.getElementById('searchActive');
    const clearSearchActive = document.getElementById('clearSearchActive');
    const statusFilter = document.getElementById('statusFilter');
    
    // History search
    const searchHistory = document.getElementById('searchHistory');
    const clearSearchHistory = document.getElementById('clearSearchHistory');
    const archiveReasonFilter = document.getElementById('archiveReasonFilter');
    
    // Active cases search handler
    if (searchActive) {
        searchActive.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            filterTable('forwarded', query, statusFilter?.value || '');
            toggleClearButton(clearSearchActive, query);
        });
        
        clearSearchActive?.addEventListener('click', function() {
            searchActive.value = '';
            filterTable('forwarded', '', statusFilter?.value || '');
            toggleClearButton(clearSearchActive, '');
        });
    }
    
    // Status filter handler
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const query = searchActive?.value.toLowerCase() || '';
            filterTable('forwarded', query, this.value);
        });
    }
    
    // History search handler
    if (searchHistory) {
        searchHistory.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            filterTable('history', query, archiveReasonFilter?.value || '');
            toggleClearButton(clearSearchHistory, query);
        });
        
        clearSearchHistory?.addEventListener('click', function() {
            searchHistory.value = '';
            filterTable('history', '', archiveReasonFilter?.value || '');
            toggleClearButton(clearSearchHistory, '');
        });
    }
    
    // Archive reason filter handler
    if (archiveReasonFilter) {
        archiveReasonFilter.addEventListener('change', function() {
            const query = searchHistory?.value.toLowerCase() || '';
            filterTable('history', query, this.value);
        });
    }
    
    // Reset filters handlers
    const resetFiltersActive = document.getElementById('resetFiltersActive');
    const resetFiltersHistory = document.getElementById('resetFiltersHistory');
    
    if (resetFiltersActive) {
        resetFiltersActive.addEventListener('click', function() {
            if (searchActive) searchActive.value = '';
            if (statusFilter) statusFilter.value = '';
            if (clearSearchActive) clearSearchActive.classList.add('d-none');
            filterTable('forwarded', '', '');
        });
    }
    
    if (resetFiltersHistory) {
        resetFiltersHistory.addEventListener('click', function() {
            if (searchHistory) searchHistory.value = '';
            if (archiveReasonFilter) archiveReasonFilter.value = '';
            if (clearSearchHistory) clearSearchHistory.classList.add('d-none');
            filterTable('history', '', '');
        });
    }
}

function filterTable(tabType, searchQuery, filterValue) {
    const tabId = tabType === 'forwarded' ? 'forwarded' : 'history';
    const tableSelector = `#${tabId} .modern-table tbody tr`;
    const rows = document.querySelectorAll(tableSelector);
    let visibleCount = 0;
    
    rows.forEach(row => {
        const studentName = row.querySelector('.cell-primary')?.textContent.toLowerCase() || '';
        const studentId = row.querySelector('.cell-secondary')?.textContent.toLowerCase() || '';
        const violationCell = row.children[2];
        const violation = violationCell?.textContent.toLowerCase() || '';
        const statusCell = row.children[4];
        const status = statusCell?.textContent.toLowerCase() || '';
        
        // Check search query match
        const matchesSearch = !searchQuery || 
            studentName.includes(searchQuery) || 
            studentId.includes(searchQuery) || 
            violation.includes(searchQuery);
            
        // Check filter match
        let matchesFilter = true;
        if (filterValue) {
            if (tabType === 'forwarded') {
                matchesFilter = status.includes(filterValue.toLowerCase());
            } else {
                matchesFilter = status.includes(filterValue.toLowerCase());
            }
        }
        
        const shouldShow = matchesSearch && matchesFilter;
        row.style.display = shouldShow ? '' : 'none';
        
        if (shouldShow) {
            visibleCount++;
        }
    });
    
    // Update results count
    updateResultsCount(tabType, visibleCount, rows.length);
    
    // Show/hide no results message
    showNoResultsMessage(tabType, visibleCount);
}

function toggleClearButton(clearButton, query) {
    if (clearButton) {
        if (query) {
            clearButton.classList.remove('d-none');
        } else {
            clearButton.classList.add('d-none');
        }
    }
}

function updateResultsCount(tabType, visibleCount, totalCount) {
    const tabId = tabType === 'forwarded' ? 'forwarded' : 'history';
    const badge = document.querySelector(`#${tabId} .badge`);
    
    if (badge) {
        const originalText = badge.textContent.split(' ')[1]; // Get "Cases" or "Archived"
        badge.textContent = `${visibleCount} ${originalText}`;
        
        if (visibleCount !== totalCount) {
            badge.classList.add('bg-warning', 'text-dark');
            badge.classList.remove('bg-white', 'text-success');
        } else {
            badge.classList.remove('bg-warning', 'text-dark');
            badge.classList.add('bg-white', 'text-success');
        }
    }
}

function showNoResultsMessage(tabType, visibleCount) {
    const tabId = tabType === 'forwarded' ? 'forwarded' : 'history';
    const tableContainer = document.querySelector(`#${tabId} .table-responsive`);
    let noResultsMsg = document.querySelector(`#${tabId} .no-results-message`);
    
    if (visibleCount === 0) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results-message text-center py-5';
            noResultsMsg.innerHTML = `
                <i class="ri-search-line fs-1 text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No results found</h5>
                <p class="text-muted mb-0">Try adjusting your search criteria or filters.</p>
            `;
            tableContainer?.parentNode.insertBefore(noResultsMsg, tableContainer.nextSibling);
        }
        noResultsMsg.style.display = 'block';
        if (tableContainer) tableContainer.style.display = 'none';
    } else {
        if (noResultsMsg) noResultsMsg.style.display = 'none';
        if (tableContainer) tableContainer.style.display = 'block';
    }
}
</script>
</x-admin-layout>
