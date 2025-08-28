<x-admin-layout>
    <meta name="base-url" content="{{ url('') }}">
    @vite(['resources/css/roles_access.css'])
    @vite(['resources/js/role-modals.js'])
    @vite(['resources/js/admin-role-access.js'])

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
                            Assign Roles to Users (Admin)
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
                                            @if(!in_array($permission->name, ['Dashboard', 'Manage Users', 'Manage Enrollments', 'Manage Students', 'View Reports', 'Roles & Access', 'System Settings', 'Manage Roles']))
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
</x-admin-layout>