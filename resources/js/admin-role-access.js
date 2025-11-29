// Admin Role Access JavaScript - Main controller

// Get CSRF token and base URLs from page
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';

// Configuration for API endpoints
const endpoints = {
    userRoles: (userId) => `${baseUrl}/admin/users/${userId}/roles`,
    assignRole: `${baseUrl}/admin/assign-role`,
    removeRole: `${baseUrl}/admin/remove-role`,
    createRole: `${baseUrl}/admin/create-role`,
    updateRole: (id) => `${baseUrl}/admin/roles/${id}`,
    deleteRole: (id) => `${baseUrl}/admin/roles/${id}`,
    createPermission: `${baseUrl}/admin/create-permission`,
    updatePermission: (id) => `${baseUrl}/admin/permissions/${id}`,
    deletePermission: (id) => `${baseUrl}/admin/permissions/${id}`
};

// Check if role-modals.js utilities are available
function waitForUtils(callback) {
    if (window.roleModalsReady && typeof window.showAlert === 'function') {
        callback();
    } else {
        setTimeout(() => waitForUtils(callback), 100);
    }
}

// Initialize when ready
document.addEventListener('DOMContentLoaded', function() {
    
    waitForUtils(function() {
        initializeRoleAccess();
    });
});

function initializeRoleAccess() {

    // User Role Management Functions
    window.openAssignRoleModal = function(userId, userName) {
        document.getElementById('assignUserId').value = userId;
        document.getElementById('assignUserName').textContent = userName;
        window.showModal('assignRoleModal');
    };

    window.openRemoveRoleModal = function(userId, userName) {
        document.getElementById('removeUserId').value = userId;
        document.getElementById('removeUserName').textContent = userName;
        
        // Populate user's current roles
        fetch(endpoints.userRoles(userId))
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('removeRoleSelect');
                select.innerHTML = '';
                data.roles.forEach(role => {
                    select.innerHTML += `<option value="${role.name}">${role.name}</option>`;
                });
            })
            .catch(error => {
                window.showAlert('Error loading user roles', 'danger');
            });
        
        window.showModal('removeRoleModal');
    };

    window.assignRole = function() {
        const userId = document.getElementById('assignUserId').value;
        const role = document.getElementById('assignRoleSelect').value;
        
        if (!role) {
            window.showAlert('Please select a role', 'warning');
            return;
        }
        
        fetch(endpoints.assignRole, {
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
                window.showAlert(data.message, 'success');
                window.hideModal('assignRoleModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            window.showAlert('An error occurred while assigning the role.', 'danger');
        });
    };

    window.removeRole = function() {
        const userId = document.getElementById('removeUserId').value;
        const role = document.getElementById('removeRoleSelect').value;
        
        if (!role) {
            window.showAlert('Please select a role to remove', 'warning');
            return;
        }
        
        fetch(endpoints.removeRole, {
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
                window.showAlert(data.message, 'success');
                window.hideModal('removeRoleModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            window.showAlert('An error occurred while removing the role.', 'danger');
        });
    };

    // Role Management Functions
    window.openCreateRoleModal = function() {
        document.getElementById('createRoleForm').reset();
        window.showModal('createRoleModal');
    };

    window.openEditRoleModal = function(roleId, roleName, permissions) {
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
        
        window.showModal('editRoleModal');
    };

    window.createRole = function() {
        const formData = new FormData(document.getElementById('createRoleForm'));
        const data = {
            name: formData.get('name'),
            permissions: formData.getAll('permissions')
        };
        
        if (!data.name) {
            window.showAlert('Please enter a role name', 'warning');
            return;
        }
        
        fetch(endpoints.createRole, {
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
                window.showAlert(data.message, 'success');
                window.hideModal('createRoleModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            window.showAlert('An error occurred while creating the role.', 'danger');
        });
    };

    window.updateRole = function() {
        const roleId = document.getElementById('editRoleId').value;
        const formData = new FormData(document.getElementById('editRoleForm'));
        const data = {
            name: formData.get('name'),
            permissions: formData.getAll('permissions')
        };
        
        if (!data.name) {
            window.showAlert('Please enter a role name', 'warning');
            return;
        }
        
        fetch(endpoints.updateRole(roleId), {
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
                window.showAlert(data.message, 'success');
                window.hideModal('editRoleModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            window.showAlert('An error occurred while updating the role.', 'danger');
        });
    };

    window.deleteRole = function(roleId, roleName) {
        if (confirm(`Are you sure you want to delete the role "${roleName}"? This action cannot be undone.`)) {
            fetch(endpoints.deleteRole(roleId), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    window.showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                window.showAlert('An error occurred while deleting the role.', 'danger');
            });
        }
    };

    // Permission Management Functions
    window.openCreatePermissionModal = function() {
        document.getElementById('createPermissionForm').reset();
        window.showModal('createPermissionModal');
    };

    window.openEditPermissionModal = function(permissionId, permissionName) {
        document.getElementById('editPermissionId').value = permissionId;
        document.getElementById('editPermissionName').value = permissionName;
        window.showModal('editPermissionModal');
    };

    window.createPermission = function() {
        const formData = new FormData(document.getElementById('createPermissionForm'));
        const data = {
            name: formData.get('name')
        };
        
        if (!data.name) {
            window.showAlert('Please enter a permission name', 'warning');
            return;
        }
        
        fetch(endpoints.createPermission, {
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
                window.showAlert(data.message, 'success');
                window.hideModal('createPermissionModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            window.showAlert('An error occurred while creating the permission.', 'danger');
        });
    };

    window.updatePermission = function() {
        const permissionId = document.getElementById('editPermissionId').value;
        const formData = new FormData(document.getElementById('editPermissionForm'));
        const data = {
            name: formData.get('name')
        };
        
        if (!data.name) {
            window.showAlert('Please enter a permission name', 'warning');
            return;
        }
        
        fetch(endpoints.updatePermission(permissionId), {
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
                window.showAlert(data.message, 'success');
                window.hideModal('editPermissionModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            window.showAlert('An error occurred while updating the permission.', 'danger');
        });
    };

    window.deletePermission = function(permissionId, permissionName) {
        if (confirm(`Are you sure you want to delete the permission "${permissionName}"? This action cannot be undone.`)) {
            fetch(endpoints.deletePermission(permissionId), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    window.showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                window.showAlert('An error occurred while deleting the permission.', 'danger');
            });
        }
    };

    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

}