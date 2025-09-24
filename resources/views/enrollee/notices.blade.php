@php
    // Get notices from database (when implemented)
    // For now, keep empty until registrar/admin sends notices
    $notices = [];
    
    // Calculate statistics
    $totalNotices = count($notices);
    $unreadNotices = collect($notices)->where('is_read', false)->count();
    $urgentNotices = collect($notices)->where('priority', 'urgent')->count();
@endphp

<x-enrollee-layout>
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            {{-- <h1 class="section-title">Notices & Announcements</h1> Old --}}
            <h1 class="section-title">Notices</h1>
            <div>
                <span class="badge bg-info">
                    <i class="ri-notification-line me-1"></i>
                    {{ $unreadNotices }} Unread
                </span>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-10">
                <!-- NOTICES TABLE -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-notification-line me-2"></i>
                            All Notices & Announcements
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 15%;">Timestamp</th>
                                        <th style="width: 15%;">User</th>
                                        <th style="width: 65%;">Details</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @if(count($notices) > 0)
                                        @foreach($notices as $notice)
                                        <tr class="{{ !$notice['is_read'] ? 'table-warning' : '' }}">
                                            <td>
                                                <span class="fw-bold">{{ $notice['id'] }}</span>
                                                @if(!$notice['is_read'])
                                                    <i class="ri-notification-2-fill text-primary ms-1" title="Unread"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $notice['timestamp']->format('M d, Y') }}<br>
                                                    {{ $notice['timestamp']->format('g:i A') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($notice['user_type'] === 'super_admin')
                                                        <i class="ri-shield-star-line text-danger me-2"></i>
                                                    @elseif($notice['user_type'] === 'admin')
                                                        <i class="ri-admin-line text-primary me-2"></i>
                                                    @elseif($notice['user_type'] === 'registrar')
                                                        <i class="ri-file-list-line text-success me-2"></i>
                                                    @endif
                                                    <div>
                                                        <small class="fw-semibold">{{ $notice['user'] }}</small><br>
                                                        <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $notice['user_type'])) }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <h6 class="mb-0 me-2">{{ $notice['title'] }}</h6>
                                                            @if($notice['priority'] === 'urgent')
                                                                <span class="badge bg-danger">Urgent</span>
                                                            @elseif($notice['priority'] === 'high')
                                                                <span class="badge bg-warning">Important</span>
                                                            @else
                                                                <span class="badge bg-info">Info</span>
                                                            @endif
                                                        </div>
                                                        <p class="text-muted mb-0 small">{{ $notice['details'] }}</p>
                                                    </div>
                                                    <div class="ms-2">
                                                        <button class="btn btn-sm btn-outline-primary" onclick="markAsRead({{ $notice['id'] }})" title="Mark as Read">
                                                            <i class="ri-check-line"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <!-- EMPTY STATE -->
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <div class="text-center">
                                                    <i class="ri-notification-off-line" style="font-size: 4rem; color: #dee2e6;"></i>
                                                    <h5 class="text-muted mt-3 mb-2">No notices yet</h5>
                                                    <p class="text-muted mb-3">
                                                        You haven't received any notices from the registrar or admission office yet.<br>
                                                        Notices will appear here once they are sent by the school administration.
                                                    </p>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button class="btn btn-outline-primary btn-sm" onclick="refreshNotices()">
                                                            <i class="ri-refresh-line me-1"></i>
                                                            Check for Updates
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <!-- PAGINATION -->
                        @if(count($notices) > 0)
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">Showing {{ count($notices) }} notice(s)</small>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item disabled">
                                        <span class="page-link">Previous</span>
                                    </li>
                                    <li class="page-item active">
                                        <span class="page-link">1</span>
                                    </li>
                                    <li class="page-item disabled">
                                        <span class="page-link">Next</span>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-2">
                <!-- NOTICE STATISTICS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="ri-bar-chart-line me-2"></i>
                            Summary
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <h4 class="text-primary mb-1">{{ $totalNotices }}</h4>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-warning mb-1">{{ $unreadNotices }}</h4>
                            <small class="text-muted">Unread</small>
                        </div>
                        <div class="mb-0">
                            <h4 class="text-danger mb-1">{{ $urgentNotices }}</h4>
                            <small class="text-muted">Urgent</small>
                        </div>
                    </div>
                </div>

                <!-- QUICK ACTIONS -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="ri-settings-line me-2"></i>
                            Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                                <i class="ri-check-double-line me-1"></i>
                                Mark All Read
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="refreshNotices()">
                                <i class="ri-refresh-line me-1"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/enrollee-notices.js'])
</x-enrollee-layout>
