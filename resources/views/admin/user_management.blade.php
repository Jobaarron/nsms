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

    <!-- Modals -->
    @include('admin.role-modals')

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
                <button class="nav-link" id="accounts-tab" data-bs-toggle="tab" data-bs-target="#accounts" type="button" role="tab">
                    <i class="ri-account-box-line me-2"></i>Accounts
                </button>
            </li>
 
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="rolesAccessTabContent">
            
            <!-- Accounts Tab -->
            <div class="tab-pane fade" id="accounts" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-account-box-line me-2"></i>
                            System Accounts
                        </h5>
                        <button class="btn btn-light btn-sm" onclick="openAddAccountModal()">
                            <i class="ri-add-line me-1"></i>Add Account
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Accounts by Role (System Accounts Only) -->
                        <div class="row">
                            @php
                                $accountRoles = ['admin', 'teacher', 'faculty_head', 'registrar', 'cashier', 'guidance_counselor', 'discipline_officer'];
                                $accountRoleLabels = [
                                    'admin' => 'Administrators',
                                    'teacher' => 'Teachers',
                                    'faculty_head' => 'Faculty Heads',
                                    'registrar' => 'Registrars',
                                    'cashier' => 'Cashiers',
                                    'guidance_counselor' => 'Guidance Counselors',
                                    'discipline_officer' => 'Discipline Officers'
                                ];
                                $accountRoleBadges = [
                                    'admin' => 'danger',
                                    'teacher' => 'success',
                                    'faculty_head' => 'primary',
                                    'registrar' => 'info',
                                    'cashier' => 'warning',
                                    'guidance_counselor' => 'info',
                                    'discipline_officer' => 'secondary'
                                ];
                            @endphp
                            
                            @foreach($accountRoles as $accountRole)
                                @php
                                    $accountsWithRole = $users->filter(function($user) use ($accountRole) {
                                        return $user->hasRole($accountRole);
                                    });
                                @endphp
                                <div class="col-12 col-lg-6 mb-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-light border-bottom">
                                            <h6 class="mb-0">
                                                <span class="badge bg-{{ $accountRoleBadges[$accountRole] }} me-2">{{ count($accountsWithRole) }}</span>
                                                {{ $accountRoleLabels[$accountRole] }}
                                            </h6>
                                        </div>
                                        <div class="card-body p-0">
                                            @if($accountsWithRole->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover mb-0">
                                                        <tbody>
                                                            @foreach($accountsWithRole as $staff)
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="avatar-sm bg-{{ $accountRoleBadges[$accountRole] }} rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                                            <span class="text-white fw-bold" style="font-size: 0.75rem;">{{ strtoupper(substr($staff->name, 0, 1)) }}</span>
                                                                        </div>
                                                                        <div>
                                                                            <strong class="d-block">{{ $staff->name }}</strong>
                                                                            <small class="text-muted">{{ $staff->email }}</small>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="text-end" style="width: 100px;">
                                                                    <button class="btn btn-xs btn-outline-primary" onclick="openEditAccountModal({{ $staff->id }}, '{{ $staff->name }}', '{{ $staff->email }}', '{{ $accountRole }}')" title="Edit">
                                                                        <i class="ri-edit-line"></i>
                                                                    </button>
                                                                    <button class="btn btn-xs btn-outline-danger" onclick="deleteAccount({{ $staff->id }}, '{{ $staff->name }}')" title="Delete">
                                                                        <i class="ri-delete-bin-line"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="p-3 text-center text-muted">
                                                    <i class="ri-inbox-line fs-4 d-block mb-2"></i>
                                                    <small>No accounts yet</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
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
                                        <td class="user-email-cell">{{ $user->email }}</td>
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
                                                    <small class="d-block text-muted">Applicant</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="user-email-cell">{{ $enrollee->email }}</td>
                                        <td>
                                            <span class="badge bg-warning">applicant</span>
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
                                            @if($role->name === 'applicant')
                                                <span class="badge bg-info">{{ $role->enrollee_count ?? 0 }} users</span>
                                            @else
                                                <span class="badge bg-info">{{ $role->users->count() }} users</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="openEditRoleModal({{ $role->id }}, '{{ $role->name }}', {{ $role->permissions->pluck('name') }})">
                                                <i class="ri-edit-line me-1"></i>Edit
                                            </button>
                                            @if(!in_array($role->name, ['admin', 'super_admin', 'teacher', 'student', 'guidance', 'discipline', 'applicant']))
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

   
    <!-- Add/Edit Account Modal -->
    <div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="addAccountModalLabel">
                        <i class="ri-account-box-line me-2"></i>
                        <span id="accountFormTitle">Add New Account</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="accountForm">
                        <input type="hidden" id="accountId" value="">
                        <input type="hidden" id="passwordTouched" value="false">
                        
                        <div class="mb-3">
                            <label for="accountName" class="form-label">
                                <i class="ri-user-line me-2"></i>Full Name
                            </label>
                            <input type="text" class="form-control" id="accountName" placeholder="Enter full name" required>
                        </div>

                        <div class="mb-3">
                            <label for="accountEmail" class="form-label">
                                <i class="ri-mail-line me-2"></i>Email Address
                            </label>
                            <input type="email" class="form-control" id="accountEmail" placeholder="Enter email address" required>
                        </div>

                        <div class="mb-3">
                            <label for="accountPassword" class="form-label">
                                <i class="ri-lock-line me-2"></i>Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="accountPassword" placeholder="Enter password" required onchange="trackPasswordChange()" oninput="trackPasswordChange()">
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility()">
                                    <i class="ri-eye-line" id="passwordEyeIcon"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2" id="passwordHint" style="font-size: 0.85rem;">Leave empty to auto-generate password</small>
                        </div>

                        <div class="mb-3">
                            <label for="accountRole" class="form-label">
                                <i class="ri-shield-line me-2"></i>Role
                            </label>
                            <select class="form-select" id="accountRole" required>
                                <option value="">-- Select Role --</option>
                                <option value="admin">Administrator</option>
                                <option value="teacher">Teacher</option>
                                <option value="faculty_head">Faculty Head</option>
                                <option value="registrar">Registrar</option>
                                <option value="cashier">Cashier</option>
                                <option value="guidance_counselor">Guidance Counselor</option>
                                <option value="discipline_officer">Discipline Officer</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-info" id="submitAccountBtn" onclick="submitAccountForm()">
                        <i class="ri-save-line me-1"></i>Add Account
                    </button>
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>