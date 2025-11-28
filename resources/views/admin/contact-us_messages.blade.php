<x-admin-layout title="Contact Messages">
    @push('styles')
        @vite(['resources/css/admin_contact_messages.css'])
    @endpush
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Contact Messages</h1>
            <p class="text-muted">Manage inquiries from website visitors</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ri-mail-line fs-2 text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold fs-4">{{ $stats['total'] }}</div>
                            <div class="text-muted small">Active Messages</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ri-mail-unread-line fs-2 text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold fs-4">{{ $stats['unread'] }}</div>
                            <div class="text-muted small">Unread</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ri-mail-open-line fs-2 text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold fs-4">{{ $stats['read'] }}</div>
                            <div class="text-muted small">Read</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" onchange="window.applyFilters()">
                        <option value="">All Status</option>
                        <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="subject" class="form-label">Subject</label>
                    <select name="subject" id="subject" class="form-select" onchange="window.applyFilters()">
                        <option value="">All Subjects</option>
                        <option value="enrollment" {{ request('subject') == 'enrollment' ? 'selected' : '' }}>Enrollment Inquiry</option>
                        <option value="academic" {{ request('subject') == 'academic' ? 'selected' : '' }}>Academic Information</option>
                        <option value="admission" {{ request('subject') == 'admission' ? 'selected' : '' }}>Admission Requirements</option>
                        <option value="facilities" {{ request('subject') == 'facilities' ? 'selected' : '' }}>School Facilities</option>
                        <option value="other" {{ request('subject') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Search by name, email, or message..." value="{{ request('search') }}"
                           oninput="window.debounceSearch()">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.contact.messages') }}" class="btn btn-outline-secondary" title="Refresh">
                            <i class="ri-refresh-line"></i> Refresh
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="card border-0 shadow-sm mb-4" id="bulk-actions" style="display: none;">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold">Bulk Actions:</span>
                <button type="button" class="btn btn-primary" onclick="window.bulkAction('read')">Mark as Read</button>
                <button type="button" class="btn btn-danger" onclick="window.bulkAction('delete')">Delete Selected</button>
                <button type="button" class="btn btn-secondary" onclick="window.clearSelection()">Clear Selection</button>
            </div>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($messages->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </th>
                                <th>Sender</th>
                                <th>Subject</th>
                                <th>Message Preview</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $message)
                                <tr data-message-id="{{ $message->id }}" class="{{ $message->status === 'unread' ? 'table-warning' : '' }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input message-checkbox" 
                                               value="{{ $message->id }}">
                                    </td>
                                    <td>{{ $message->name }}</td>
                                    <td>{{ $message->email }}</td>
                                    <td>{{ $message->subject_display }}</td>
                                    <td>
                                        <span class="{{ $message->status_badge_class }} status-badge">
                                            {{ ucfirst($message->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>{{ $message->created_at->format('M d, Y') }}</div>
                                        <div class="text-muted small">{{ $message->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="window.viewMessage({{ $message->id }})" title="View">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="window.deleteMessage({{ $message->id }})" title="Delete">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer bg-transparent">
                    {{ $messages->appends(request()->query())->links('pagination.custom') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="ri-mail-line fs-1 text-muted"></i>
                    <h5 class="mt-3">No messages found</h5>
                    <p class="text-muted">No contact messages match your current filters.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- View Message Modal -->
    <div class="modal fade" id="viewMessageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Message Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="message-details">
                    <!-- Message details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Message Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="update-status-form">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="message-status" class="form-label">Status</label>
                            <select class="form-select" id="message-status" name="status" required>
                                <option value="unread">Unread</option>
                                <option value="read">Read</option>
                                <option value="replied">Replied</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="admin-notes" class="form-label">Admin Notes (Optional)</label>
                            <textarea class="form-control" id="admin-notes" name="admin_notes" rows="3" 
                                      placeholder="Add any internal notes about this message..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentMessageId = null;
        let searchTimeout = null;

        // Define functions globally first
        window.applyFilters = function() {
            const status = document.getElementById('status').value;
            const subject = document.getElementById('subject').value;
            const search = document.getElementById('search').value;
            
            const params = new URLSearchParams();
            if (status) params.append('status', status);
            if (subject) params.append('subject', subject);
            if (search) params.append('search', search);
            
            const url = '{{ route("admin.contact.messages") }}' + (params.toString() ? '?' + params.toString() : '');
            window.location.href = url;
        };

        window.debounceSearch = function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(window.applyFilters, 500);
        };

        // View message function
        window.viewMessage = function(messageId) {
            fetch(`/admin/contact-messages/${messageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const message = data.message;
                        document.getElementById('message-details').innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Name:</strong> ${message.name}
                                </div>
                                <div class="col-md-6">
                                    <strong>Email:</strong> ${message.email}
                                </div>
                                <div class="col-md-6">
                                    <strong>Subject:</strong> ${message.subject_display}
                                </div>
                                <div class="col-md-6">
                                    <strong>Status:</strong> <span class="${message.status_badge_class}">${message.status}</span>
                                </div>
                                <div class="col-12 mt-3">
                                    <strong>Message:</strong>
                                    <div class="border rounded p-3 mt-2 bg-light">
                                        ${message.message.replace(/\n/g, '<br>')}
                                    </div>
                                </div>
                                ${message.admin_notes ? `
                                <div class="col-12 mt-3">
                                    <strong>Admin Notes:</strong>
                                    <div class="border rounded p-3 mt-2 bg-warning bg-opacity-10">
                                        ${message.admin_notes.replace(/\n/g, '<br>')}
                                    </div>
                                </div>
                                ` : ''}
                                <div class="col-12 mt-3">
                                    <small class="text-muted">
                                        Received: ${new Date(message.created_at).toLocaleString()}
                                        ${message.read_at ? `<br>Read: ${new Date(message.read_at).toLocaleString()}` : ''}
                                    </small>
                                </div>
                            </div>
                        `;
                        
                        currentMessageId = messageId;
                        new bootstrap.Modal(document.getElementById('viewMessageModal')).show();
                        // Update the status badge in the table without reloading
                        const statusCell = document.querySelector(`tr[data-message-id="${messageId}"] .status-badge`);
                        if (statusCell) {
                            statusCell.className = 'badge bg-info';
                            statusCell.textContent = 'Read';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading message details');
                });
        }

        // Delete message function
        window.deleteMessage = function(messageId) {
            if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
                fetch(`/admin/contact-messages/${messageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting message');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting message');
                });
            }
        }

        // Select all functionality
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.message-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleBulkActions();
        });

        // Individual checkbox functionality
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('message-checkbox')) {
                toggleBulkActions();
            }
        });

        function toggleBulkActions() {
            const checkedBoxes = document.querySelectorAll('.message-checkbox:checked');
            const bulkActions = document.getElementById('bulk-actions');
            
            if (checkedBoxes.length > 0) {
                bulkActions.style.display = 'block';
            } else {
                bulkActions.style.display = 'none';
            }
        }

        window.clearSelection = function() {
            document.querySelectorAll('.message-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('select-all').checked = false;
            toggleBulkActions();
        }

        window.updateStatus = function(messageId, status) {
            fetch(`/admin/contact-messages/${messageId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating status');
            });
        }

        window.bulkAction = function(action) {
            const checkedBoxes = document.querySelectorAll('.message-checkbox:checked');
            const messageIds = Array.from(checkedBoxes).map(cb => cb.value);
            
            if (messageIds.length === 0) {
                alert('Please select at least one message');
                return;
            }

            let confirmMessage = '';
            switch(action) {
                case 'mark_read':
                    confirmMessage = `Mark ${messageIds.length} message(s) as read?`;
                    break;
                case 'delete':
                    confirmMessage = `Delete ${messageIds.length} message(s)? This action cannot be undone.`;
                    break;
            }

            if (confirm(confirmMessage)) {
                fetch('/admin/contact-messages/bulk-action', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        action: action,
                        message_ids: messageIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error performing bulk action');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error performing bulk action');
                });
            }
        }

    </script>

    @push('scripts')
    <script>
        // Mark contact messages alert as viewed when admin visits this page
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    console.warn('CSRF token not found');
                    return;
                }
                
                fetch('{{ route("admin.mark-alert-viewed") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                    },
                    body: JSON.stringify({
                        alert_type: 'contact_messages'
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        console.error('Failed to mark contact messages alert as viewed:', response.status);
                    }
                })
                .catch(error => console.error('Error marking contact messages alert as viewed:', error));
            } catch(error) {
                console.error('Error in admin contact messages alert script:', error);
            }
        });
    </script>
    @endpush
</x-admin-layout>