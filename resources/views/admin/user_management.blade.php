<x-admin-layout>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    @vite(['resources/css/roles_access.css'])
    @vite(['resources/js/role-modals.js'])
    @vite(['resources/js/admin-role-access.js'])
    @vite(['resources/js/user-management.js'])
    <!-- Modals -->
    @include('admin.role-modals')
    @include('admin.user-management-modals')

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="ri-shield-user-line me-2"></i>
                User Management
            </h1>
        </div>

        <!-- Alert Container -->
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
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="manage-admins-tab" data-bs-toggle="tab" data-bs-target="#manage-admins" type="button" role="tab">
                    <i class="ri-admin-line me-2"></i>Admins
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="manage-teachers-tab" data-bs-toggle="tab" data-bs-target="#manage-teachers" type="button" role="tab">
                    <i class="ri-user-star-line me-2"></i>Teachers
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="manage-guidance-discipline-tab" data-bs-toggle="tab" data-bs-target="#manage-guidance-discipline" type="button" role="tab">
                    <i class="ri-heart-pulse-line me-2"></i>Guidance & Discipline
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
                            Users
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
                                    {{-- Regular Users (Admin, Teacher, Student, Guidance & Discipline) --}}
                                    @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @php
                                                    $userType = 'user';
                                                    $bgColor = 'bg-primary';
                                                    if($user->hasRole('admin') || $user->hasRole('super_admin')) {
                                                        $userType = 'admin';
                                                        $bgColor = 'bg-danger';
                                                    } elseif($user->hasRole('teacher')) {
                                                        $userType = 'teacher';
                                                        $bgColor = 'bg-success';
                                                    } elseif($user->hasRole('student')) {
                                                        $userType = 'student';
                                                        $bgColor = 'bg-primary';
                                                    } elseif($user->hasRole('guidance_counselor') || $user->hasRole('discipline') || $user->hasRole('discipline_head') || $user->hasRole('discipline_officer')) {
                                                        $userType = 'guidance';
                                                        $bgColor = 'bg-info';
                                                    }
                                                @endphp
                                                <div class="avatar-sm {{ $bgColor }} rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-white fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                </div>
                                                <div>
                                                    <strong>{{ $user->name }}</strong>
                                                    <small class="d-block text-muted">{{ ucfirst($userType) }}</small>
                                                </div>
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

                                    {{-- Enrollees (Separate Auth Model) --}}
                                    @foreach($enrollees as $enrollee)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-white fw-bold">{{ strtoupper(substr($enrollee->first_name ?? 'E', 0, 1)) }}</span>
                                                </div>
                                                <div>
                                                    <strong>{{ $enrollee->first_name }} {{ $enrollee->last_name }}</strong>
                                                    <small class="d-block text-muted">Enrollee</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $enrollee->email }}</td>
                                        <td>
                                            <span class="badge bg-warning">enrollee</span>
                                        </td>
                                        <td>
                                            <span class="text-muted small">Separate auth system</span>
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

            <!-- Manage Admins Tab -->
            <div class="tab-pane fade" id="manage-admins" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-admin-line me-2"></i>
                            Manage Admin Users
                        </h5>
                        <button class="btn btn-light btn-sm" onclick="openCreateUserModal('admin')">
                            <i class="ri-add-line me-1"></i>Create Admin
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Admin</th>
                                        <th>Email</th>
                                        <th>Employee ID</th>
                                        <th>Department</th>
                                        <th>Admin Level</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users->filter(function($user) { return $user->hasRole('admin') || $user->hasRole('super_admin'); }) as $admin)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-danger rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-white fw-bold">{{ strtoupper(substr($admin->name, 0, 1)) }}</span>
                                                </div>
                                                <strong>{{ $admin->name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $admin->email }}</td>
                                        <td>{{ $admin->admin->employee_id ?? 'N/A' }}</td>
                                        <td>{{ $admin->admin->department ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-danger">{{ $admin->admin->admin_level ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $admin->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($admin->status ?? 'active') }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewUser({{ $admin->id }})">
                                                <i class="ri-eye-line me-1"></i>View
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="editUser({{ $admin->id }})">
                                                <i class="ri-edit-line me-1"></i>Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteUser({{ $admin->id }}, '{{ $admin->name }}')">
                                                <i class="ri-delete-bin-line me-1"></i>Delete
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manage Teachers Tab -->
            <div class="tab-pane fade" id="manage-teachers" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-user-star-line me-2"></i>
                            Manage Teacher Users
                        </h5>
                        <button class="btn btn-light btn-sm" onclick="openCreateUserModal('teacher')">
                            <i class="ri-add-line me-1"></i>Create Teacher
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Teacher</th>
                                        <th>Email</th>
                                        <th>Employee ID</th>
                                        <th>Department</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users->filter(function($user) { return $user->hasRole('teacher'); }) as $teacher)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-white fw-bold">{{ strtoupper(substr($teacher->name, 0, 1)) }}</span>
                                                </div>
                                                <strong>{{ $teacher->name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $teacher->email }}</td>
                                        <td>{{ $teacher->teacher->employee_id ?? 'N/A' }}</td>
                                        <td>{{ $teacher->teacher->department ?? 'N/A' }}</td>
                                        <td>{{ $teacher->teacher->position ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $teacher->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($teacher->status ?? 'active') }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewUser({{ $teacher->id }})">
                                                <i class="ri-eye-line me-1"></i>View
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="editUser({{ $teacher->id }})">
                                                <i class="ri-edit-line me-1"></i>Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteUser({{ $teacher->id }}, '{{ $teacher->name }}')">
                                                <i class="ri-delete-bin-line me-1"></i>Delete
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manage Guidance & Discipline Tab -->
            <div class="tab-pane fade" id="manage-guidance-discipline" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-heart-pulse-line me-2"></i>
                            Manage Guidance & Discipline Users
                        </h5>
                        <button class="btn btn-light btn-sm" onclick="openCreateUserModal('guidance')">
                            <i class="ri-add-line me-1"></i>Create Guidance & Discipline
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Staff</th>
                                        <th>Email</th>
                                        <th>Employee ID</th>
                                        <th>Role Type</th>
                                        <th>Position</th>
                                        <th>Specialization</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users->filter(function($user) { return $user->hasRole('guidance_counselor') || $user->hasRole('discipline') || $user->hasRole('discipline_head') || $user->hasRole('discipline_officer'); }) as $staff)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @php
                                                    $isGuidance = $staff->hasRole('guidance_counselor');
                                                    $avatarColor = $isGuidance ? 'info' : 'warning';
                                                    $textColor = $isGuidance ? 'white' : 'dark';
                                                @endphp
                                                <div class="avatar-sm bg-{{ $avatarColor }} rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-{{ $textColor }} fw-bold">{{ strtoupper(substr($staff->name, 0, 1)) }}</span>
                                                </div>
                                                <strong>{{ $staff->name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $staff->email }}</td>
                                        <td>{{ $staff->guidanceDiscipline->employee_id ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $roleType = 'Discipline';
                                                $badgeColor = 'warning';
                                                if ($staff->hasRole('guidance_counselor')) {
                                                    $roleType = 'Guidance Counselor';
                                                    $badgeColor = 'info';
                                                } elseif ($staff->hasRole('discipline_head')) {
                                                    $roleType = 'Discipline Head';
                                                    $badgeColor = 'danger';
                                                } elseif ($staff->hasRole('discipline_officer')) {
                                                    $roleType = 'Discipline Officer';
                                                    $badgeColor = 'warning';
                                                } elseif ($staff->hasRole('discipline')) {
                                                    $roleType = 'Discipline';
                                                    $badgeColor = 'warning';
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }}">{{ $roleType }}</span>
                                        </td>
                                        <td>{{ $staff->guidanceDiscipline->position ?? 'N/A' }}</td>
                                        <td>{{ $staff->guidanceDiscipline->specialization ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $staff->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($staff->status ?? 'active') }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewUser({{ $staff->id }})">
                                                <i class="ri-eye-line me-1"></i>View
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="editUser({{ $staff->id }})">
                                                <i class="ri-edit-line me-1"></i>Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteUser({{ $staff->id }}, '{{ $staff->name }}')">
                                                <i class="ri-delete-bin-line me-1"></i>Delete
                                            </button>
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

   
</x-admin-layout>