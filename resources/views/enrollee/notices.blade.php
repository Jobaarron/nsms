@php
    use App\Models\Notice;
    
    // Get notifications for current enrollee (excluding guidance-specific notifications)
    $enrollee = Auth::guard('enrollee')->user();
    $notifications = Notice::getForEnrollee($enrollee->id);
    
    // Calculate statistics
    $totalNotifications = $notifications->count();
    $unreadNotifications = $notifications->where('is_read', false)->count();
    $urgentNotifications = $notifications->where('priority', 'urgent')->count();
@endphp

<x-enrollee-layout>
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            {{-- <h1 class="section-title">Notices & Announcements</h1> Old --}}
            <h1 class="section-title">Notifications</h1>
            <div>
                <span class="badge bg-info">
                    <i class="ri-notification-line me-1"></i>
                    {{ $unreadNotifications }} Unread
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
                            Notifications
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 25%;">Title</th>
                                        <th style="width: 50%;">Message</th>
                                        <th style="width: 15%;">Date</th>
                                        <th style="width: 10%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($notifications->count() > 0)
                                        @foreach($notifications as $notice)
                                        <tr class="{{ !$notice->is_read ? 'table-warning' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if(!$notice->is_read)
                                                        <i class="ri-notification-2-fill text-primary me-2" title="Unread"></i>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0">{{ $notice->title }}</h6>
                                                        @if($notice->is_global)
                                                            <small class="text-muted">
                                                                <i class="ri-global-line me-1"></i>General Notice
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-0">{{ $notice->preview_message }}</p>
                                                @if($notice->created_by)
                                                    <small class="text-muted">
                                                        <i class="ri-user-line me-1"></i>
                                                        From: {{ $notice->creator_name ?? 'Registrar' }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $notice->formatted_date }}<br>
                                                    <span class="text-primary">{{ $notice->time_ago }}</span>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewNotification({{ $notice->id }})" 
                                                            title="View Full Notification">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    @if(!$notice->is_read)
                                                        <button class="btn btn-sm btn-outline-success" 
                                                                onclick="markAsRead({{ $notice->id }})" 
                                                                title="Mark as Read">
                                                            <i class="ri-check-line"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                        </tr>
                                        @endforeach
                                    @else
                                        <!-- EMPTY STATE -->
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <div class="text-center">
                                                    <i class="ri-notification-off-line" style="font-size: 4rem; color: #dee2e6;"></i>
                                                    <h5 class="text-muted mt-3 mb-2">No notifications yet</h5>
                                                    <p class="text-muted mb-3">
                                                        You haven't received any notifications from the registrar or admission office yet.<br>
                                                        Notifications will appear here once they are sent by the school administration.
                                                    </p>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button class="btn btn-outline-primary btn-sm" onclick="refreshNotifications()">
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
                        @if($notifications->count() > 0)
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">Showing {{ $notifications->count() }} notification(s)</small>
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
                <!-- NOTIFICATION STATISTICS -->
                <!-- <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="ri-bar-chart-line me-2"></i>
                            Summary
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <h4 class="text-primary mb-1">{{ $totalNotifications }}</h4>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-warning mb-1">{{ $unreadNotifications }}</h4>
                            <small class="text-muted">Unread</small>
                        </div>
                        <div class="mb-0">
                            <h4 class="text-danger mb-1">{{ $urgentNotifications }}</h4>
                            <small class="text-muted">Urgent</small>
                        </div>
                    </div>
                </div> -->

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
                            <button class="btn btn-outline-secondary btn-sm" onclick="refreshNotifications()">
                                <i class="ri-refresh-line me-1"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification View Modal -->
    <div class="modal fade" id="notificationViewModal" tabindex="-1" aria-labelledby="notificationViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationViewModalLabel">
                        <i class="ri-notification-line me-2"></i>
                        <span id="notification-modal-title">Notification Details</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Message:</h6>
                                <div id="notification-modal-message" class="border rounded p-3 bg-light">
                                    <!-- Notification message content -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Notification Information</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Date:</strong> <span id="notice-modal-date"></span></p>
                                    <p><strong>From:</strong> <span id="notice-modal-from"></span></p>
                                    <p><strong>Status:</strong> <span id="notice-modal-status" class="badge"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="mark-read-btn" onclick="markAsReadFromModal()">
                        <i class="ri-check-line me-1"></i>Mark as Read
                    </button>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/enrollee-notices.js'])
</x-enrollee-layout>
