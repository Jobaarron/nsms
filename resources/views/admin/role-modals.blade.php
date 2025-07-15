<!-- Assign Role Modal -->
<head>
    @vite('resources/js/role-modals.js')
</head>
<div class="modal fade" id="assignRoleModal" tabindex="-1" aria-labelledby="assignRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignRoleModalLabel">
                    <i class="ri-user-add-line me-2"></i>
                    Assign Role to User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Assign a role to: <strong id="assignUserName"></strong></p>
                <div class="mb-3">
                    <label for="assignRoleSelect" class="form-label">Select Role</label>
                    <select class="form-select" id="assignRoleSelect" required>
                        <option value="">Choose a role...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" id="assignUserId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="assignRole()">
                    <i class="ri-check-line me-1"></i>Assign Role
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Role Modal -->
<div class="modal fade" id="removeRoleModal" tabindex="-1" aria-labelledby="removeRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeRoleModalLabel">
                    <i class="ri-user-unfollow-line me-2"></i>
                    Remove Role from User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Remove role from: <strong id="removeUserName"></strong></p>
                <div class="mb-3">
                    <label for="removeRoleSelect" class="form-label">Select Role to Remove</label>
                    <select class="form-select" id="removeRoleSelect" required>
                        <option value="">Choose a role...</option>
                    </select>
                </div>
                <input type="hidden" id="removeUserId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="removeRole()">
                    <i class="ri-delete-bin-line me-1"></i>Remove Role
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRoleModalLabel">
                    <i class="ri-add-line me-2"></i>
                    Create New Role
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createRoleForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="createRoleName" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="createRoleName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="row">
                            @foreach($permissions as $permission)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions" value="{{ $permission->name }}" id="create_perm_{{ $permission->id }}">
                                        <label class="form-check-label" for="create_perm_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="createRole()">
                        <i class="ri-save-line me-1"></i>Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRoleModalLabel">
                    <i class="ri-edit-line me-2"></i>
                    Edit Role
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRoleForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editRoleName" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="editRoleName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="row" id="editRolePermissions">
                            @foreach($permissions as $permission)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions" value="{{ $permission->name }}" id="edit_perm_{{ $permission->id }}">
                                        <label class="form-check-label" for="edit_perm_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" id="editRoleId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateRole()">
                        <i class="ri-save-line me-1"></i>Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Permission Modal -->
<div class="modal fade" id="createPermissionModal" tabindex="-1" aria-labelledby="createPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPermissionModalLabel">
                    <i class="ri-add-line me-2"></i>
                    Create New Permission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createPermissionForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="createPermissionName" class="form-label">Permission Name</label>
                        <input type="text" class="form-control" id="createPermissionName" name="name" required>
                        <div class="form-text">
                            Examples: "view reports", "manage students", "edit settings"
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="createPermission()">
                        <i class="ri-save-line me-1"></i>Create Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Permission Modal -->
<div class="modal fade" id="editPermissionModal" tabindex="-1" aria-labelledby="editPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPermissionModalLabel">
                    <i class="ri-edit-line me-2"></i>
                    Edit Permission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPermissionForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editPermissionName" class="form-label">Permission Name</label>
                        <input type="text" class="form-control" id="editPermissionName" name="name" required>
                    </div>
                    <input type="hidden" id="editPermissionId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updatePermission()">
                        <i class="ri-save-line me-1"></i>Update Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
