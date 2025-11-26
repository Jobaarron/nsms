<x-guidance-layout>
    @vite('resources/css/index_guidance.css')

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('guidance.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Notifications</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-guidance">
                        <i class="ri-notification-line me-2"></i>Notifications
                    </h1>
                    <p class="text-muted mb-0">Guidance-related notifications and updates</p>
                </div>
                <div>
                    <span class="badge bg-info">
                        <i class="ri-notification-line me-1"></i>
                        {{ $unreadCount }} Unread
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <small class="text-muted">
                                Showing {{ $notifications->count() }} of {{ $notifications->total() }} notifications
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-success" onclick="markAllAsRead()">
                                <i class="ri-check-double-line me-1"></i>Mark All Read
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshPage()">
                                <i class="ri-refresh-line me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">All Notifications</h5>
                </div>
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                @php
                                    $title = strtolower($notification->title);
                                    $icon = 'ri-notification-line';
                                    $badgeColor = 'bg-info';
                                    $typeText = '';
                                    
                                    if (str_contains($title, 'case meeting')) {
                                        $icon = 'ri-calendar-event-line';
                                        $badgeColor = 'bg-primary';
                                        $typeText = 'Case Meeting';
                                    } elseif (str_contains($title, 'counseling session')) {
                                        $icon = 'ri-heart-pulse-line';
                                        $badgeColor = 'bg-success';
                                        $typeText = 'Counseling';
                                    } elseif (str_contains($title, 'teacher reply')) {
                                        $icon = 'ri-reply-line';
                                        $badgeColor = 'bg-warning';
                                        $typeText = 'Teacher Reply';
                                    } elseif (str_contains($title, 'forwarded')) {
                                        $icon = 'ri-share-forward-line';
                                        $badgeColor = 'bg-danger';
                                        $typeText = 'Forwarded Case';
                                    } elseif (str_contains($title, 'recommended')) {
                                        $icon = 'ri-user-heart-line';
                                        $badgeColor = 'bg-info';
                                        $typeText = 'Recommendation';
                                    }
                                @endphp

                                <div class="list-group-item notification-item {{ !$notification->is_read ? 'notification-unread' : '' }}" 
                                     onclick="handleNotificationClick({{ $notification->id }}, '{{ addslashes($notification->title) }}')"
                                     style="cursor: pointer; transition: all 0.2s ease;">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3 mt-1">
                                            <i class="{{ $icon }} text-primary fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-1 notification-title">{{ $notification->title }}</h6>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($typeText)
                                                        <span class="badge {{ $badgeColor }}">{{ $typeText }}</span>
                                                    @endif
                                                    @if(!$notification->is_read)
                                                        <span class="badge bg-warning">New</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <p class="mb-2 text-muted notification-message">
                                                {{ $notification->preview_message ?? Str::limit($notification->message, 120) }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="ri-time-line me-1"></i>{{ $notification->formatted_date }}
                                                    <span class="ms-2 text-primary">{{ $notification->time_ago }}</span>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="ri-arrow-right-line"></i>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <i class="ri-notification-off-line" style="font-size: 4rem; color: #dee2e6;"></i>
                            <h5 class="text-muted mt-3 mb-2">No notifications yet</h5>
                            <p class="text-muted mb-3">
                                Notifications for case meetings, counseling sessions, teacher replies,<br>
                                forwarded cases, and recommendations will appear here.
                            </p>
                            <a href="{{ route('guidance.dashboard') }}" class="btn btn-outline-primary">
                                <i class="ri-dashboard-line me-1"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Pagination -->
                @if($notifications->hasPages())
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-center">
                        {{ $notifications->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Notification Detail Modal -->
    <div class="modal fade" id="notificationDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-notification-line me-2"></i>
                        <span id="modal-notification-title">Notification Details</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Message:</h6>
                                <div id="modal-notification-message" class="border rounded p-3 bg-light">
                                    <!-- Notification message content -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Information</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Date:</strong> <span id="modal-notification-date"></span></p>
                                    <p><strong>From:</strong> <span id="modal-notification-from"></span></p>
                                    <p><strong>Status:</strong> <span id="modal-notification-status" class="badge"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="modal-navigate-btn">
                        <i class="ri-arrow-right-line me-1"></i>Go to Page
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .notification-item:hover {
            background-color: #f8f9fa;
            transform: translateX(2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .notification-unread {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }

        .notification-title {
            font-weight: 600;
            color: #333;
        }

        .notification-message {
            font-size: 0.95rem;
            line-height: 1.4;
        }
    </style>

    <script>
        let currentNotificationId = null;
        let currentNotificationTitle = null;

        function handleNotificationClick(notificationId, title) {
            currentNotificationId = notificationId;
            currentNotificationTitle = title.toLowerCase();
            
            // Mark as read and show modal
            fetch(`/guidance/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI to show as read
                    updateNotificationAsRead(notificationId);
                    // Show modal with notification details
                    showNotificationModal(data.notification);
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }

        function updateNotificationAsRead(notificationId) {
            const notificationElement = document.querySelector(`[onclick*="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.classList.remove('notification-unread');
                const newBadge = notificationElement.querySelector('.badge.bg-warning');
                if (newBadge && newBadge.textContent === 'New') {
                    newBadge.remove();
                }
            }
        }

        function showNotificationModal(notification) {
            document.getElementById('modal-notification-title').textContent = notification.title;
            document.getElementById('modal-notification-message').textContent = notification.message;
            document.getElementById('modal-notification-date').textContent = notification.formatted_date;
            document.getElementById('modal-notification-from').textContent = notification.creator_name || 'System';
            
            const statusElement = document.getElementById('modal-notification-status');
            statusElement.textContent = notification.is_read ? 'Read' : 'Unread';
            statusElement.className = `badge ${notification.is_read ? 'bg-success' : 'bg-warning'}`;

            // Set up navigation button
            const navigateBtn = document.getElementById('modal-navigate-btn');
            navigateBtn.onclick = function() {
                navigateToRelevantPage();
            };

            const modal = new bootstrap.Modal(document.getElementById('notificationDetailModal'));
            modal.show();
        }

        function navigateToRelevantPage() {
            if (currentNotificationTitle.includes('case meeting') || currentNotificationTitle.includes('forwarded')) {
                window.location.href = '/guidance/case-meetings';
            } else if (currentNotificationTitle.includes('counseling session') || currentNotificationTitle.includes('recommended')) {
                window.location.href = '/guidance/counseling-sessions';
            } else if (currentNotificationTitle.includes('teacher reply')) {
                window.location.href = '/guidance/case-meetings';
            } else {
                window.location.href = '/guidance/dashboard';
            }
        }

        function markAllAsRead() {
            fetch('/guidance/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error marking all as read:', error);
            });
        }

        function refreshPage() {
            location.reload();
        }
    </script>
</x-guidance-layout>