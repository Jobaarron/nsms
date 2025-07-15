<x-admin-layout>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="ri-shield-user-line me-2"></i>
                Roles & Access Management
            </h1>
        </div>

        <!-- Alert Messages -->
        <div id="alert-container"></div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="rolesAccessTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="user-roles-tab" data-bs-toggle="tab" data-bs-target="#user-roles" type="button" role="tab">
                    <i class="ri-user-settings-line me-2"></i>User Roles
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="manage-roles-tab" data-bs-toggle="tab" data-bs-target="#manage-roles" type="button" role="tab">
                    <i class="ri-shield-line me-2"></i>Manage Roles
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="manage-permissions-tab" data-bs-toggle="tab" data-bs-target="#manage-permissions" type="button" role="tab">
                    <i class="ri-key-line me-2"></i>Manage Permissions
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="rolesAccessTabContent">
            
            <!-- User Roles Tab -->
            <div class="tab-pane fade show active" id="user-roles" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="ri-user-settings-line me-2"></i>
                            Assign Roles to Users
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Current Roles</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-white fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                </div>
                                                <strong>{{ $user->name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @forelse($user->roles as $role)
                                                <span class="badge bg-info me-1">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-muted">No roles assigned</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="openAssignRoleModal({{ $user->id }}, '{{ $user->name }}')">
                                                <i class="ri-user-add-line me-1"></i>Assign Role
                                            </button>
                                            @if($user->roles->count() > 0)
                                                <button class="btn btn-sm btn-outline-danger" onclick="openRemoveRoleModal({{ $user->id }}, '{{ $user->name }}')">
                                                    <i class="ri-user-unfollow-line me-1"></i>Remove Role
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manage Roles Tab -->
            <div class="tab-pane fade" id="manage-roles" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-shield-line me-2"></i>
                            Manage Roles
                        </h5>
                        <button class="btn btn-light btn-sm" onclick="openCreateRoleModal()">
                            <i class="ri-add-line me-1"></i>Create Role
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Role Name</th>
                                        <th>Permissions</th>
                                        <th>Users Count</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roles as $role)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary fs-6">{{ $role->name }}</span>
                                        </td>
                                        <td>
                                            @forelse($role->permissions as $permission)
                                                <span class="badge bg-secondary me-1">{{ $permission->name }}</span>
                                            @empty
                                                <span class="text-muted">No permissions</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $role->users->count() }} users</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="openEditRoleModal({{ $role->id }}, '{{ $role->name }}', {{ $role->permissions->pluck('name') }})">
                                                <i class="ri-edit-line me-1"></i>Edit
                                            </button>
                                            @if(!in_array($role->name, ['admin', 'super_admin', 'teacher', 'student', 'guidance', 'discipline']))
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteRole({{ $role->id }}, '{{ $role->name }}')">
                                                    <i class="ri-delete-bin-line me-1"></i>Delete
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manage Permissions Tab -->
            <div class="tab-pane fade" id="manage-permissions" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-key-line me-2"></i>
                            Manage Permissions
                        </h5>
                        <button class="btn btn-dark btn-sm" onclick="openCreatePermissionModal()">
                            <i class="ri-add-line me-1"></i>Create Permission
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Permission Name</th>
                                        <th>Roles with this Permission</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permissions as $permission)
                                    <tr>
                                        <td>
                                            <span class="badge bg-warning text-dark fs-6">{{ $permission->name }}</span>
                                        </td>
                                        <td>
                                            @forelse($permission->roles as $role)
                                                <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-muted">No roles assigned</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="openEditPermissionModal({{ $permission->id }}, '{{ $permission->name }}')">
                                                <i class="ri-edit-line me-1"></i>Edit
                                            </button>
                                            @if(!in_array($permission->name, ['Dashboard', 'Manage Users', 'Manage Enrollments', 'Manage Students', 'View Reports', 'Roles & Access', 'System Settings', 'manage roles']))
                                                <button class="btn btn-sm btn-outline-danger" onclick="deletePermission({{ $permission->id }}, '{{ $permission->name }}')">
                                                    <i class="ri-delete-bin-line me-1"></i>Delete
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('admin.role-modals')

    <!-- JavaScript -->
    <script>
        // CSRF Token - with fallback
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    
        // Show Alert Function
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alert-container');
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="ri-${type === 'success' ? 'check-line' : 'error-warning-line'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            alertContainer.innerHTML = alertHtml;
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
    
        // Modal helper functions
        function showModal(modalId) {
            const modalElement = document.getElementById(modalId);
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                new bootstrap.Modal(modalElement).show();
            } else {
                // Fallback for when Bootstrap JS is not available
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
                document.body.classList.add('modal-open');
            }
        }
    
        function hideModal(modalId) {
            const modalElement = document.getElementById(modalId);
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }
            } else {
                // Fallback for when Bootstrap JS is not available
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                document.body.classList.remove('modal-open');
            }
        }
    
        // User Role Management
        function openAssignRoleModal(userId, userName) {
            document.getElementById('assignUserId').value = userId;
            document.getElementById('assignUserName').textContent = userName;
            showModal('assignRoleModal');
        }
    
        function openRemoveRoleModal(userId, userName) {
            document.getElementById('removeUserId').value = userId;
            document.getElementById('removeUserName').textContent = userName;
            
            // Populate user's current roles
            fetch(`{{ route('admin.user.roles', ':userId') }}`.replace(':userId', userId))
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('removeRoleSelect');
                    select.innerHTML = '';
                    data.roles.forEach(role => {
                        select.innerHTML += `<option value="${role.name}">${role.name}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error fetching user roles:', error);
                    showAlert('Error loading user roles', 'danger');
                });
            
            showModal('removeRoleModal');
        }
    
        function assignRole() {
            const userId = document.getElementById('assignUserId').value;
            const role = document.getElementById('assignRoleSelect').value;
            
            if (!role) {
                showAlert('Please select a role', 'warning');
                return;
            }
            
            fetch('{{ route("admin.assign.role") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ user_id: userId, role: role })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    hideModal('assignRoleModal');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while assigning the role.', 'danger');
            });
        }
    
        function removeRole() {
            const userId = document.getElementById('removeUserId').value;
            const role = document.getElementById('removeRoleSelect').value;
            
            if (!role) {
                showAlert('Please select a role to remove', 'warning');
                return;
            }
            
            fetch('{{ route("admin.remove.role") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ user_id: userId, role: role })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    hideModal('removeRoleModal');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while removing the role.', 'danger');
            });
        }
    
        // Role Management
        function openCreateRoleModal() {
            document.getElementById('createRoleForm').reset();
            showModal('createRoleModal');
        }
    
        function openEditRoleModal(roleId, roleName, permissions) {
            document.getElementById('editRoleId').value = roleId;
            document.getElementById('editRoleName').value = roleName;
            
            // Clear all checkboxes first
            document.querySelectorAll('#editRolePermissions input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Check permissions that this role has
            permissions.forEach(permission => {
                const checkbox = document.querySelector(`#editRolePermissions input[value="${permission}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
            
            showModal('editRoleModal');
        }
    
        function createRole() {
            const formData = new FormData(document.getElementById('createRoleForm'));
            const data = {
                name: formData.get('name'),
                permissions: formData.getAll('permissions')
            };
            
            if (!data.name) {
                showAlert('Please enter a role name', 'warning');
                return;
            }
            
            fetch('{{ route("admin.create.role") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    hideModal('createRoleModal');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while creating the role.', 'danger');
            });
        }
    
        function updateRole() {
            const roleId = document.getElementById('editRoleId').value;
            const formData = new FormData(document.getElementById('editRoleForm'));
            const data = {
                name: formData.get('name'),
                permissions: formData.getAll('permissions')
            };
            
            if (!data.name) {
                showAlert('Please enter a role name', 'warning');
                return;
            }
            
            fetch(`{{ route("admin.update.role", ":id") }}`.replace(':id', roleId), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    hideModal('editRoleModal');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while updating the role.', 'danger');
            });
        }
    
        function deleteRole(roleId, roleName) {
            if (confirm(`Are you sure you want to delete the role "${roleName}"? This action cannot be undone.`)) {
                fetch(`{{ route("admin.delete.role", ":id") }}`.replace(':id', roleId), {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while deleting the role.', 'danger');
                });
            }
        }
    
        // Permission Management
        function openCreatePermissionModal() {
            document.getElementById('createPermissionForm').reset();
            showModal('createPermissionModal');
        }
    
        function openEditPermissionModal(permissionId, permissionName) {
            document.getElementById('editPermissionId').value = permissionId;
            document.getElementById('editPermissionName').value = permissionName;
            showModal('editPermissionModal');
        }
    
        function createPermission() {
            const formData = new FormData(document.getElementById('createPermissionForm'));
            const data = {
                name: formData.get('name')
            };
            
            if (!data.name) {
                showAlert('Please enter a permission name', 'warning');
                return;
            }
            
            fetch('{{ route("admin.create.permission") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    hideModal('createPermissionModal');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while creating the permission.', 'danger');
            });
        }
    
        function updatePermission() {
            const permissionId = document.getElementById('editPermissionId').value;
            const formData = new FormData(document.getElementById('editPermissionForm'));
            const data = {
                name: formData.get('name')
            };
            
            if (!data.name) {
                showAlert('Please enter a permission name', 'warning');
                return;
            }
            
            fetch(`{{ route("admin.update.permission", ":id") }}`.replace(':id', permissionId), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    hideModal('editPermissionModal');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while updating the permission.', 'danger');
            });
        }
    
        function deletePermission(permissionId, permissionName) {
            if (confirm(`Are you sure you want to delete the permission "${permissionName}"? This action cannot be undone.`)) {
                fetch(`{{ route("admin.delete.permission", ":id") }}`.replace(':id', permissionId), {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while deleting the permission.', 'danger');
                });
            }
        }
    
        // Initialize tooltips when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Only initialize tooltips if Bootstrap is available
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
    </script>

    <style>
        .avatar-sm {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }
        
        .nav-tabs .nav-link {
            color: #6c757d;
            border: 1px solid transparent;
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
        }
        
        .nav-tabs .nav-link:hover {
            border-color: #e9ecef #e9ecef #dee2e6;
            isolation: isolate;
        }
        
        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .badge {
            font-size: 0.75em;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .alert {
            border: none;
            border-radius: 0.5rem;
        }
        
        .modal-header {
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
        }
        
        .modal-footer {
            border-top: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
        }
        
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        .table-responsive {
            border-radius: 0.375rem;
        }
        
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,.125);
            padding: 1rem 1.5rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
    </style>
</x-admin-layout>