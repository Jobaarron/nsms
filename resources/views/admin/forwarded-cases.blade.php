
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
                        
                        <div class="alert alert-warning border-0 rounded-4 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="ri-information-line text-white me-2 fs-5"></i>
                                <div class="text-white">
                                    <strong>Instructions:</strong><br>
                                    <small class="text-white">• The current sanction is disabled and marked as "Current"<br>
                                    • Select a DIFFERENT sanction to revise the case meeting<br>
                                    • Only ONE sanction can be applied per case</small>
                                </div>
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
                                        <input class="form-check-input sanction-radio" type="radio" id="written_reflection" name="selected_sanction" value="written_reflection">
                                        <label class="form-check-label fw-medium" for="written_reflection">
                                            <i class="ri-file-edit-line text-success me-2"></i>Written Reflection
                                        </label>
                                        <div class="conditional-field" data-target="written_reflection_fields">
                                            <label class="form-label small text-muted">Due Date:</label>
                                            <input type="date" name="written_reflection_due" class="form-control form-control-sm">
                                        </div>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input sanction-radio" type="radio" id="mentorship_counseling" name="selected_sanction" value="mentorship_counseling">
                                        <label class="form-check-label fw-medium" for="mentorship_counseling">
                                            <i class="ri-user-heart-line text-success me-2"></i>Mentorship/Counseling
                                        </label>
                                        <div class="conditional-field" data-target="mentorship_fields">
                                            <label class="form-label small text-muted">Mentor Name:</label>
                                            <input type="text" name="mentor_name" class="form-control form-control-sm" placeholder="Enter mentor name">
                                        </div>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input sanction-radio" type="radio" id="parent_teacher_communication" name="selected_sanction" value="parent_teacher_communication">
                                        <label class="form-check-label fw-medium" for="parent_teacher_communication">
                                            <i class="ri-parent-line text-warning me-2"></i>Parent-Teacher Communication
                                        </label>
                                        <div class="conditional-field" data-target="parent_teacher_fields">
                                            <label class="form-label small text-muted">Communication Method:</label>
                                            <input type="text" name="communication_method" class="form-control form-control-sm" placeholder="Enter communication method">
                                        </div>
                                    </div>

                                    <div class="form-check mb-0">
                                        <input class="form-check-input sanction-radio" type="radio" id="restorative_justice_activity" name="selected_sanction" value="restorative_justice_activity">
                                        <label class="form-check-label fw-medium" for="restorative_justice_activity">
                                            <i class="ri-hand-heart-line text-success me-2"></i>Restorative Justice Activity
                                        </label>
                                        <div class="conditional-field" data-target="restorative_justice_fields">
                                            <label class="form-label small text-muted">Activity Details:</label>
                                            <textarea name="activity_details" class="form-control form-control-sm" rows="2" placeholder="Enter activity details"></textarea>
                                        </div>
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
                                        <input class="form-check-input sanction-radio" type="radio" id="follow_up_meeting" name="selected_sanction" value="follow_up_meeting">
                                        <label class="form-check-label fw-medium" for="follow_up_meeting">
                                            <i class="ri-calendar-check-line text-success me-2"></i>Follow-up Meeting
                                        </label>
                                        <div class="conditional-field" data-target="follow_up_fields">
                                            <label class="form-label small text-muted">Meeting Date:</label>
                                            <input type="date" name="follow_up_date" class="form-control form-control-sm">
                                        </div>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input sanction-radio" type="radio" id="community_service" name="selected_sanction" value="community_service">
                                        <label class="form-check-label fw-medium" for="community_service">
                                            <i class="ri-community-line text-success me-2"></i>Community Service
                                        </label>
                                        <div class="conditional-field" data-target="community_service_fields">
                                            <label class="form-label small text-muted">Assigned Area:</label>
                                            <input type="text" name="service_area" class="form-control form-control-sm" placeholder="Enter assigned area">
                                        </div>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input sanction-radio" type="radio" id="suspension" name="selected_sanction" value="suspension">
                                        <label class="form-check-label fw-medium" for="suspension">
                                            <i class="ri-pause-circle-line text-danger me-2"></i>Suspension
                                        </label>
                                        <div class="conditional-field" data-target="suspension_fields">
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-2">Suspension Duration (days):</label>
                                                <input type="number" name="suspension_days" class="form-control form-control-sm mb-2" placeholder="Enter number of days" min="1" max="30">
                                            </div>
                                            
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">Start Date:</label>
                                                    <input type="date" name="suspension_start_date" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">End Date:</label>
                                                    <input type="date" name="suspension_end_date" class="form-control form-control-sm">
                                                </div>
                                            </div>
                                            
                                            <small class="text-muted mt-2 d-block">
                                                <i class="ri-information-line me-1"></i>
                                                Student must accomplish activity sheets missed during suspension period.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="form-check mb-0">
                                        <input class="form-check-input sanction-radio" type="radio" id="expulsion" name="selected_sanction" value="expulsion">
                                        <label class="form-check-label fw-medium" for="expulsion">
                                            <i class="ri-close-circle-line text-danger me-2"></i>Expulsion
                                        </label>
                                        <div class="conditional-field" data-target="expulsion_fields">
                                            <label class="form-label small text-muted">Expulsion Date:</label>
                                            <input type="date" name="expulsion_date" class="form-control form-control-sm">
                                            <small class="text-muted mt-1 d-block">Note: Certificate of eligibility may be affected per RMPS Sec. 146</small>
                                        </div>
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

@vite('resources/js/forwarded-cases.js')
</x-admin-layout>
