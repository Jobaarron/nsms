// User Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');
    
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;

    // Initialize event listeners
    initializeEventListeners();

    function initializeEventListeners() {
        // Create user form submissions
        document.getElementById('createAdminForm')?.addEventListener('submit', handleCreateAdmin);
        document.getElementById('createTeacherForm')?.addEventListener('submit', handleCreateTeacher);
        document.getElementById('createGuidanceForm')?.addEventListener('submit', handleCreateGuidance);
        document.getElementById('createDisciplineForm')?.addEventListener('submit', handleCreateDiscipline);
        document.getElementById('editUserForm')?.addEventListener('submit', handleEditUser);
    }

    // Global functions for button clicks
    window.openCreateUserModal = function(userType) {
        const modalMap = {
            'admin': 'createAdminModal',
            'teacher': 'createTeacherModal',
            'guidance': 'createGuidanceModal',
            'discipline': 'createDisciplineModal'
        };

        const modalId = modalMap[userType];
        if (modalId) {
            const modal = new bootstrap.Modal(document.getElementById(modalId));
            modal.show();
        }
    };

    window.viewUser = function(userId) {
        fetch(`${baseUrl}/admin/users/${userId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayUserDetails(data.user);
                const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
                modal.show();
            } else {
                showAlert('error', data.message || 'Error loading user details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error loading user details');
        });
    };

    window.editUser = function(userId) {
        fetch(`${baseUrl}/admin/users/${userId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateEditForm(data.user);
                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                modal.show();
            } else {
                showAlert('error', data.message || 'Error loading user details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error loading user details');
        });
    };

    window.deleteUser = function(userId, userName) {
        if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
            fetch(`${baseUrl}/admin/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('error', data.message || 'Error deleting user');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Error deleting user');
            });
        }
    };

    // Handle create admin form submission
    function handleCreateAdmin(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        submitUserForm('/admin/users/admin', formData, 'Admin user created successfully!');
    }

    // Handle create teacher form submission
    function handleCreateTeacher(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        submitUserForm('/admin/users/teacher', formData, 'Teacher user created successfully!');
    }

    // Handle create guidance form submission
    function handleCreateGuidance(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        submitUserForm('/admin/users/guidance', formData, 'Guidance user created successfully!');
    }

    // Handle create discipline form submission
    function handleCreateDiscipline(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        submitUserForm('/admin/users/discipline', formData, 'Discipline user created successfully!');
    }

    // Handle edit user form submission
    function handleEditUser(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const userId = formData.get('user_id');
        
        if (!userId) {
            showAlert('error', 'User ID is missing');
            return;
        }

        fetch(`${baseUrl}/admin/users/${userId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert('error', data.message || 'Error updating user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error updating user');
        });
    }

    // Generic form submission function
    function submitUserForm(endpoint, formData, successMessage) {
        fetch(`${baseUrl}${endpoint}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message || successMessage);
                // Close all modals
                document.querySelectorAll('.modal').forEach(modal => {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                });
                // Reset forms
                document.querySelectorAll('form').forEach(form => form.reset());
                // Reload page after short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert('error', data.message || 'Error creating user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error creating user');
        });
    }

    // Display user details in view modal
    function displayUserDetails(user) {
        let roleInfo = '';
        let specificInfo = '';

        // Determine user type and specific information
        if (user.roles && user.roles.length > 0) {
            const roleNames = user.roles.map(role => role.name).join(', ');
            roleInfo = `<span class="badge bg-primary">${roleNames}</span>`;

            // Add specific information based on role
            if (user.admin) {
                specificInfo = `
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Employee ID:</strong> ${user.admin.employee_id || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Department:</strong> ${user.admin.department || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Admin Level:</strong> ${user.admin.admin_level || 'N/A'}
                        </div>
                    </div>
                `;
            } else if (user.teacher) {
                specificInfo = `
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Employee ID:</strong> ${user.teacher.employee_id || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Department:</strong> ${user.teacher.department || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Position:</strong> ${user.teacher.position || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Specialization:</strong> ${user.teacher.specialization || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Hire Date:</strong> ${user.teacher.hire_date || 'N/A'}
                        </div>
                    </div>
                `;
            } else if (user.guidance_discipline) {
                specificInfo = `
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Employee ID:</strong> ${user.guidance_discipline.employee_id || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Position:</strong> ${user.guidance_discipline.position || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Specialization:</strong> ${user.guidance_discipline.specialization || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Type:</strong> ${user.guidance_discipline.type || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Hire Date:</strong> ${user.guidance_discipline.hire_date || 'N/A'}
                        </div>
                    </div>
                `;
            }
        }

        const content = `
            <div class="row">
                <div class="col-md-6">
                    <strong>Name:</strong> ${user.name}
                </div>
                <div class="col-md-6">
                    <strong>Email:</strong> ${user.email}
                </div>
                <div class="col-md-6">
                    <strong>Status:</strong> <span class="badge bg-${user.status === 'active' ? 'success' : 'secondary'}">${user.status || 'active'}</span>
                </div>
                <div class="col-md-6">
                    <strong>Roles:</strong> ${roleInfo}
                </div>
                <div class="col-md-6">
                    <strong>Created:</strong> ${new Date(user.created_at).toLocaleDateString()}
                </div>
                <div class="col-md-6">
                    <strong>Last Updated:</strong> ${new Date(user.updated_at).toLocaleDateString()}
                </div>
            </div>
            ${specificInfo}
        `;

        document.getElementById('viewUserContent').innerHTML = content;
    }

    // Populate edit form with user data
    function populateEditForm(user) {
        let formContent = `
            <input type="hidden" name="user_id" value="${user.id}">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" value="${user.name}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="edit_email" name="email" value="${user.email}" required>
                    </div>
                </div>
            </div>
        `;

        // Add specific fields based on user type
        if (user.admin) {
            formContent += `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit_employee_id" class="form-label">Employee ID</label>
                            <input type="text" class="form-control" id="edit_employee_id" name="employee_id" value="${user.admin.employee_id || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit_department" class="form-label">Department</label>
                            <select class="form-control" id="edit_department" name="department">
                                <option value="">Select Department</option>
                                <option value="Administration" ${user.admin.department === 'Administration' ? 'selected' : ''}>Administration</option>
                                <option value="Academic Affairs" ${user.admin.department === 'Academic Affairs' ? 'selected' : ''}>Academic Affairs</option>
                                <option value="Student Affairs" ${user.admin.department === 'Student Affairs' ? 'selected' : ''}>Student Affairs</option>
                                <option value="Finance" ${user.admin.department === 'Finance' ? 'selected' : ''}>Finance</option>
                                <option value="IT Department" ${user.admin.department === 'IT Department' ? 'selected' : ''}>IT Department</option>
                                <option value="Human Resources" ${user.admin.department === 'Human Resources' ? 'selected' : ''}>Human Resources</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="edit_admin_level" class="form-label">Admin Level</label>
                    <select class="form-control" id="edit_admin_level" name="admin_level">
                        <option value="">Select Admin Level</option>
                        <option value="super_admin" ${user.admin.admin_level === 'super_admin' ? 'selected' : ''}>Super Admin</option>
                        <option value="admin" ${user.admin.admin_level === 'admin' ? 'selected' : ''}>Admin</option>
                        <option value="moderator" ${user.admin.admin_level === 'moderator' ? 'selected' : ''}>Moderator</option>
                    </select>
                </div>
            `;
        } else if (user.teacher) {
            formContent += `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit_employee_id" class="form-label">Employee ID</label>
                            <input type="text" class="form-control" id="edit_employee_id" name="employee_id" value="${user.teacher.employee_id || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit_department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="edit_department" name="department" value="${user.teacher.department || ''}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit_position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="edit_position" name="position" value="${user.teacher.position || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit_specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="edit_specialization" name="specialization" value="${user.teacher.specialization || ''}">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="edit_hire_date" class="form-label">Hire Date</label>
                    <input type="date" class="form-control" id="edit_hire_date" name="hire_date" value="${user.teacher.hire_date ? new Date(user.teacher.hire_date).toISOString().split('T')[0] : ''}">
                </div>
            `;
        } else if (user.guidance_discipline) {
            formContent += `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit_employee_id" class="form-label">Employee ID</label>
                            <input type="text" class="form-control" id="edit_employee_id" name="employee_id" value="${user.guidance_discipline.employee_id || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit_position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="edit_position" name="position" value="${user.guidance_discipline.position || ''}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit_specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="edit_specialization" name="specialization" value="${user.guidance_discipline.specialization || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit_hire_date" class="form-label">Hire Date</label>
                            <input type="date" class="form-control" id="edit_hire_date" name="hire_date" value="${user.guidance_discipline.hire_date ? new Date(user.guidance_discipline.hire_date).toISOString().split('T')[0] : ''}">
                        </div>
                    </div>
                </div>
            `;
        }

        document.getElementById('editUserContent').innerHTML = formContent;
    }

    // Show alert messages
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alert-container');
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'ri-check-line' : 'ri-error-warning-line';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${iconClass} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        alertContainer.innerHTML = alertHtml;
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
});
