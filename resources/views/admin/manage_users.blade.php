<x-admin-layout>
    @vite(['resources/js/manage_users.js'])
    @vite(['resources/css/manage_users.css'])
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-header">
                    <h1 class="page-title">
                        <i class="fas fa-users me-2">Manage User</i>
                    </h1>
                    <p class="page-subtitle">Manage system users, roles, and permissions</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number">{{ $users->count() }}</h3>
                        <p class="stats-label">Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon bg-danger">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number">{{ $users->filter(fn($u) => $u->hasRole('admin'))->count() }}</h3>
                        <p class="stats-label">Administrators</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon bg-success">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number">{{ $users->filter(fn($u) => $u->hasRole('teacher'))->count() }}</h3>
                        <p class="stats-label">Teachers</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon bg-info">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number">{{ $users->filter(fn($u) => $u->hasRole('student'))->count() }}</h3>
                        <p class="stats-label">Students</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="row">
            <div class="col-12">
                <div class="content-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-list me-2"></i>Users List
                            </h3>
                            <div class="header-actions">
                                <button type="button" class="btn btn-outline-secondary me-2" id="bulkActionsBtn" disabled>
                                    <i class="fas fa-tasks me-1"></i>Bulk Actions
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                                    <i class="fas fa-plus me-1"></i>Create New User
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="filters-section mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <select class="form-select" id="roleFilter">
                                        <option value="">All Roles</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="statusFilter">
                                        <option value="">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" class="form-control" id="customSearch" placeholder="Search users...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-secondary w-100" id="resetFilters">
                                        <i class="fas fa-undo me-1"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="table-responsive">
                            <table class="table table-hover" id="usersTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="40">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Last Login</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr data-user-id="{{ $user->id }}" data-user-roles="{{ $user->roles->pluck('name')->join(',') }}" data-user-status="{{ $user->status ?? 'active' }}">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input user-checkbox" type="checkbox" value="{{ $user->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <span class="user-id">#{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div class="user-details">
                                                    <div class="user-name">{{ $user->name }}</div>
                                                    @if($user->student)
                                                        <small class="text-muted">Student ID: {{ $user->student->student_id ?? 'N/A' }}</small>
                                                    @elseif($user->admin)
                                                        <small class="text-muted">Employee ID: {{ $user->admin->employee_id ?? 'N/A' }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="user-email">{{ $user->email }}</span>
                                            @if($user->email_verified_at)
                                                <i class="fas fa-check-circle text-success ms-1" title="Email Verified"></i>
                                            @else
                                                <i class="fas fa-exclamation-circle text-warning ms-1" title="Email Not Verified"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="roles-badges">
                                                @forelse($user->roles as $role)
                                                    <span class="badge role-badge role-{{ $role->name }}">
                                                        {{ ucfirst($role->name) }}
                                                    </span>
                                                @empty
                                                    <span class="badge bg-secondary">No Role</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ $user->status ?? 'active' }}">
                                                {{ ucfirst($user->status ?? 'active') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="date-text">{{ $user->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted d-block">{{ $user->created_at->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            @if($user->last_login_at ?? false)
                                                <span class="date-text">{{ $user->last_login_at->format('M d, Y') }}</span>
                                                <small class="text-muted d-block">{{ $user->last_login_at->diffForHumans() }}</small>
                                            @else
                                                <span class="text-muted">Never</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        onclick="viewUser({{ $user->id }})" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        onclick="editUser({{ $user->id }})" title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if(!($user->hasRole('admin') && \App\Models\User::role('admin')->count() <= 1) && $user->id !== auth()->id())
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')" title="Delete User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @else
                                                <button type="button" class="btn btn-sm btn-secondary" disabled 
                                                        title="{{ $user->id === auth()->id() ? 'Cannot delete yourself' : 'Cannot delete last admin' }}">
                                                    <i class="fas fa-shield-alt"></i>
                                                </button>
                                                @endif
                                            </div>                                            
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

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Create New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createUserForm">
                    <div class="modal-body">
                        <!-- User Type Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">User Type *</label>
                                <div class="user-type-selection">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="user-type-card" data-type="admin">
                                                <i class="fas fa-user-shield"></i>
                                                <h6>Administrator</h6>
                                                <p>Full system access</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="user-type-card" data-type="teacher">
                                                <i class="fas fa-chalkboard-teacher"></i>
                                                <h6>Teacher</h6>
                                                <p>Teaching staff member</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="user-type-card" data-type="student">
                                                <i class="fas fa-user-graduate"></i>
                                                <h6>Student</h6>
                                                <p>Student account</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="user_type" id="create_user_type" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="form-section">
                            <h6 class="section-title">Basic Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="create_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="create_name" name="name" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="create_email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="create_email" name="email" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="create_password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="create_password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleCreatePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="create_password_confirmation" class="form-label">Confirm Password *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="create_password_confirmation" name="password_confirmation" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleCreatePasswordConfirm">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="create_status" class="form-label">Status</label>
                                <select class="form-select" id="create_status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="pending">Pending</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Role Assignment -->
                    <div class="form-section">
                        <h6 class="section-title">Role Assignment</h6>
                        <div class="row">
                            @foreach($roles as $role)
                            <div class="col-md-4 mb-3">
                                <div class="role-checkbox-card">
                                    <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}" id="create_role_{{ $role->id }}">
                                    <label class="form-check-label" for="create_role_{{ $role->id }}">
                                        <div class="role-info">
                                            <h6 class="role-name">{{ ucfirst($role->name) }}</h6>
                                            <p class="role-description">
                                                @switch($role->name)
                                                    @case('admin')
                                                        Full system administration access
                                                        @break
                                                    @case('teacher')
                                                        Teaching and classroom management
                                                        @break
                                                    @case('student')
                                                        Student portal access
                                                        @break
                                                    @default
                                                        {{ $role->name }} role permissions
                                                @endswitch
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Additional Information (Dynamic based on user type) -->
                    <div class="form-section" id="additionalInfoSection" style="display: none;">
                        <h6 class="section-title">Additional Information</h6>
                        
                        <!-- Admin specific fields -->
                        <div id="adminFields" class="user-type-fields" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="create_employee_id" class="form-label">Employee ID</label>
                                    <input type="text" class="form-control" id="create_employee_id" name="employee_id">
                                </div>
                                <div class="col-md-6">
                                    <label for="create_department" class="form-label">Department</label>
                                    <select class="form-select" id="create_department" name="department">
                                        <option value="">Select Department</option>
                                        <option value="Administration">Administration</option>
                                        <option value="Academic Affairs">Academic Affairs</option>
                                        <option value="Student Affairs">Student Affairs</option>
                                        <option value="Finance">Finance</option>
                                        <option value="IT">Information Technology</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="create_position" class="form-label">Position</label>
                                    <input type="text" class="form-control" id="create_position" name="position" value="Administrator">
                                </div>
                                <div class="col-md-6">
                                    <label for="create_admin_level" class="form-label">Admin Level</label>
                                    <select class="form-select" id="create_admin_level" name="admin_level">
                                        <option value="admin">Admin</option>
                                        <option value="super_admin">Super Admin</option>
                                        <option value="moderator">Moderator</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Student specific fields -->
                        <div id="studentFields" class="user-type-fields" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="create_student_id" class="form-label">Student ID</label>
                                    <input type="text" class="form-control" id="create_student_id" name="student_id">
                                </div>
                                <div class="col-md-6">
                                    <label for="create_lrn" class="form-label">LRN (Learner Reference Number)</label>
                                    <input type="text" class="form-control" id="create_lrn" name="lrn">
                                </div>
                                <div class="col-md-4">
                                    <label for="create_grade_level" class="form-label">Grade Level</label>
                                    <select class="form-select" id="create_grade_level" name="grade_level">
                                        <option value="">Select Grade</option>
                                        <option value="Kindergarten">Kindergarten</option>
                                        <option value="Grade 1">Grade 1</option>
                                        <option value="Grade 2">Grade 2</option>
                                        <option value="Grade 3">Grade 3</option>
                                        <option value="Grade 4">Grade 4</option>
                                        <option value="Grade 5">Grade 5</option>
                                        <option value="Grade 6">Grade 6</option>
                                        <option value="Grade 7">Grade 7</option>
                                        <option value="Grade 8">Grade 8</option>
                                        <option value="Grade 9">Grade 9</option>
                                        <option value="Grade 10">Grade 10</option>
                                        <option value="Grade 11">Grade 11</option>
                                        <option value="Grade 12">Grade 12</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="create_section" class="form-label">Section</label>
                                    <input type="text" class="form-control" id="create_section" name="section">
                                </div>
                                <div class="col-md-4">
                                    <label for="create_academic_year" class="form-label">Academic Year</label>
                                    <input type="text" class="form-control" id="create_academic_year" name="academic_year" value="2024-2025">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <i class="fas fa-save me-1"></i>Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">
                    <i class="fas fa-user-edit me-2"></i>Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm">
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="modal-body">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h6 class="section-title">Basic Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_password" class="form-label">New Password (leave blank to keep current)</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="edit_password" name="password">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleEditPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_password_confirmation" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleEditPasswordConfirm">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="pending">Pending</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Role Assignment -->
                    <div class="form-section">
                        <h6 class="section-title">Role Assignment</h6>
                        <div class="row" id="edit_roles_container">
                            @foreach($roles as $role)
                            <div class="col-md-4 mb-3">
                                <div class="role-checkbox-card">
                                    <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}" id="edit_role_{{ $role->id }}">
                                    <label class="form-check-label" for="edit_role_{{ $role->id }}">
                                        <div class="role-info">
                                            <h6 class="role-name">{{ ucfirst($role->name) }}</h6>
                                            <p class="role-description">
                                                @switch($role->name)
                                                    @case('admin')
                                                        Full system administration access
                                                        @break
                                                    @case('teacher')
                                                        Teaching and classroom management
                                                        @break
                                                    @case('student')
                                                        Student portal access
                                                        @break
                                                    @default
                                                        {{ $role->name }} role permissions
                                                @endswitch
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="form-section" id="editAdditionalInfoSection">
                        <h6 class="section-title">Additional Information</h6>
                        <div id="editAdditionalFields">
                            <!-- Dynamic content will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <i class="fas fa-save me-1"></i>Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewUserModalLabel">
                    <i class="fas fa-user me-2"></i>User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="user-profile-section">
                            <div class="user-avatar-large" id="view_user_avatar">A</div>
                            <h5 class="mt-3 mb-1" id="view_user_name_display">-</h5>
                            <p class="text-muted" id="view_user_email_display">-</p>
                            <div id="view_user_status_badge"></div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="user-details-table">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="35%">User ID:</th>
                                    <td id="view_user_id">-</td>
                                </tr>
                                <tr>
                                    <th>Full Name:</th>
                                    <td id="view_user_name">-</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td id="view_user_email">-</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td id="view_user_status">-</td>
                                </tr>
                                <tr>
                                    <th>Roles:</th>
                                    <td id="view_user_roles">-</td>
                                </tr>
                                <tr>
                                    <th>Email Verified:</th>
                                    <td id="view_user_verified">-</td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td id="view_user_created">-</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td id="view_user_updated">-</td>
                                </tr>
                                <tr>
                                    <th>Last Login:</th>
                                    <td id="view_user_last_login">-</td>
                                </tr>
                                <tr>
                                    <th>Permissions:</th>
                                    <td id="view_user_permissions">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Information Tabs -->
                <div class="mt-4">
                    <ul class="nav nav-tabs" id="userDetailsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                <i class="fas fa-info-circle me-1"></i>General
                            </button>
                        </li>
                        <li class="nav-item" role="presentation" id="admin-tab-li" style="display: none;">
                            <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-info" type="button" role="tab">
                                <i class="fas fa-user-shield me-1"></i>Admin Info
                            </button>
                        </li>
                        <li class="nav-item" role="presentation" id="student-tab-li" style="display: none;">
                            <button class="nav-link" id="student-tab" data-bs-toggle="tab" data-bs-target="#student-info" type="button" role="tab">
                                <i class="fas fa-user-graduate me-1"></i>Student Info
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="userDetailsTabContent">
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <strong>Account Created:</strong>
                                    <p id="view_account_created">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Account Status:</strong>
                                    <p id="view_account_status">-</p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="admin-info" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <strong>Employee ID:</strong>
                                    <p id="view_admin_employee_id">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Department:</strong>
                                    <p id="view_admin_department">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Position:</strong>
                                    <p id="view_admin_position">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Admin Level:</strong>
                                    <p id="view_admin_level">-</p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="student-info" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <strong>Student ID:</strong>
                                    <p id="view_student_id">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>LRN:</strong>
                                    <p id="view_student_lrn">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Grade Level:</strong>
                                    <p id="view_student_grade">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Section:</strong>
                                    <p id="view_student_section">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Enrollment Status:</strong>
                                    <p id="view_student_enrollment">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Academic Year:</strong>
                                    <p id="view_student_academic_year">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button type="button" class="btn btn-warning" onclick="editUserFromView()">
                    <i class="fas fa-edit me-1"></i>Edit User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal Soon to be use -->
{{-- <div class="modal fade" id="bulkActionsModal" tabindex="-1" aria-labelledby="bulkActionsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionsModalLabel">
                    <i class="fas fa-tasks me-2"></i>Bulk Actions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selected <span id="selectedCount">0</span> user(s). Choose an action:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success" onclick="bulkAction('activate')">
                        <i class="fas fa-check me-1"></i>Activate Users
                    </button>
                    <button type="button" class="btn btn-warning" onclick="bulkAction('deactivate')">
                        <i class="fas fa-pause me-1"></i>Deactivate Users
                    </button>
                    <button type="button" class="btn btn-info" onclick="bulkAction('suspend')">
                        <i class="fas fa-ban me-1"></i>Suspend Users
                    </button>
                    <button type="button" class="btn btn-danger" onclick="bulkAction('delete')">
                        <i class="fas fa-trash me-1"></i>Delete Users
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div> --}}
</x-admin-layout>