// Vanilla JavaScript User Management System
document.addEventListener('DOMContentLoaded', function() {
    // Global variables
    let currentUserId = null;
    let usersTable;
    let selectedUsers = [];

    // Initialize the page
    function initializePage() {
        initializeDataTable();
        initializeEventHandlers();
        initializeFormValidation();
        initializeTooltips();
        updateStats();
        exposeGlobalFunctions();
    }

    // Initialize Bootstrap tooltips
    function initializeTooltips() {
        // Check if Bootstrap is available
        if (typeof bootstrap === 'undefined') {
            console.warn('Bootstrap is not loaded. Tooltips will not be initialized.');
            return;
        }
        
        // Initialize tooltips for existing elements
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        
        // Re-initialize tooltips when DataTable redraws
        if (usersTable) {
            usersTable.on('draw.dt', function() {
                setTimeout(function() {
                    const newTooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                    newTooltipTriggerList.forEach(function(tooltipTriggerEl) {
                        if (!tooltipTriggerEl._tooltip) {
                            new bootstrap.Tooltip(tooltipTriggerEl);
                        }
                    });
                }, 100);
            });
        }
    }

    // Expose functions globally
    function exposeGlobalFunctions() {
        window.viewUser = viewUser;
        window.editUser = editUser;
        window.deleteUser = deleteUser;
        window.bulkAction = bulkAction;
        window.editUserFromView = editUserFromView;
        window.exportUsers = exportUsers;
        window.importUsers = importUsers;
        window.generateUserReport = generateUserReport;
    }

    // Initialize DataTable
    function initializeDataTable() {
        const tableElement = document.getElementById('usersTable');
        if (tableElement && typeof DataTable !== 'undefined') {
            usersTable = new DataTable('#usersTable', {
                responsive: true,
                pageLength: 10,
                order: [[1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [0, -1] },
                    { searchable: false, targets: [0, -1] }
                ],
                language: {
                    search: "Search users:",
                    lengthMenu: "Show _MENU_ users per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ users",
                    emptyTable: "No users found"
                }
            });
        }
    }

    // Initialize event handlers
    function initializeEventHandlers() {
        // User type selection
        const userTypeCards = document.querySelectorAll('.user-type-card');
        userTypeCards.forEach(card => {
            card.addEventListener('click', function() {
                selectUserType(this.dataset.type);
            });
        });

        // Form submissions
        const createForm = document.getElementById('createUserForm');
        if (createForm) {
            createForm.addEventListener('submit', handleCreateUser);
        }

        const editForm = document.getElementById('editUserForm');
        if (editForm) {
            editForm.addEventListener('submit', handleEditUser);
        }

        // Password visibility toggles
        document.querySelectorAll('[id^="toggleCreate"], [id^="toggleEdit"]').forEach(button => {
            button.addEventListener('click', function() {
                togglePasswordVisibility(this);
            });
        });

        // Select all checkbox
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', toggleSelectAll);
        }

        // Individual checkboxes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('user-checkbox')) {
                updateBulkActionsState();
            }
        });

        // Bulk actions button
        const bulkActionsBtn = document.getElementById('bulkActionsBtn');
        if (bulkActionsBtn) {
            bulkActionsBtn.addEventListener('click', showBulkActions);
        }
    }

    // Initialize form validation
    function initializeFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    }

    // Select user type
    function selectUserType(type) {
        // Remove selection from all cards
        document.querySelectorAll('.user-type-card').forEach(card => {
            card.classList.remove('selected');
        });

        // Select current card
        const selectedCard = document.querySelector(`[data-type="${type}"]`);
        if (selectedCard) {
            selectedCard.classList.add('selected');
        }

        // Show/hide additional fields
        const additionalSection = document.getElementById('additionalInfoSection');
        const typeFields = document.querySelectorAll('.user-type-fields');
        
        // Hide all type-specific fields
        typeFields.forEach(field => {
            field.style.display = 'none';
        });

        // Show fields for selected type
        const selectedFields = document.getElementById(`${type}Fields`);
        if (selectedFields) {
            selectedFields.style.display = 'block';
            additionalSection.style.display = 'block';
        } else {
            additionalSection.style.display = 'none';
        }
    }

    // Handle create user form submission
    function handleCreateUser(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const submitBtn = event.target.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');
        
        // Show loading state
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        
        // Clear previous validation errors
        clearValidationErrors(event.target);
        
        fetch('/admin/manage-users', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                const modal = document.getElementById('createUserModal');
                if (typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getInstance(modal).hide();
                } else {
                    modal.style.display = 'none';
                }
                resetCreateForm();
                location.reload(); // Refresh to show new user
            } else {
                if (data.errors) {
                    displayValidationErrors(event.target, data.errors);
                } else {
                    showAlert('error', data.message || 'Failed to create user');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred while creating the user');
        })
        .finally(() => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        });
    }

    // Handle edit user form submission
    function handleEditUser(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        formData.append('_method', 'POST');
        
        const submitBtn = event.target.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');
        
        // Show loading state
        submitBtn.disabled = true;
        if (spinner) spinner.classList.remove('d-none');
        
        // Clear previous validation errors
        clearValidationErrors(event.target);
        
        fetch(`/admin/manage-users/${currentUserId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                const modal = document.getElementById('editUserModal');
                if (typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getInstance(modal).hide();
                } else {
                    modal.style.display = 'none';
                }
                location.reload(); // Refresh to show updated user
            } else {
                if (data.errors) {
                    displayValidationErrors(event.target, data.errors);
                } else {
                    showAlert('error', data.message || 'Failed to update user');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred while updating the user');
        })
        .finally(() => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        });
    }

    // View user function
    function viewUser(userId) {
        fetch(`/admin/manage-users/${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateViewModal(data.user);
                    const modal = document.getElementById('viewUserModal');
                    if (typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modal).show();
                    } else {
                        modal.style.display = 'block';
                        modal.classList.add('show');
                    }
                } else {
                    showAlert('error', 'Failed to load user details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred while loading user details');
            });
    }

    // Edit user function
    function editUser(userId) {
        fetch(`/admin/manage-users/${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateEditModal(data.user);
                    const modal = document.getElementById('editUserModal');
                    if (typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modal).show();
                    } else {
                        modal.style.display = 'block';
                        modal.classList.add('show');
                    }
                } else {
                    showAlert('error', 'Failed to load user details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred while loading user details');
            });
    }

    // Edit user from view modal
    function editUserFromView() {
        if (currentUserId) {
            bootstrap.Modal.getInstance(document.getElementById('viewUserModal')).hide();
            editUser(currentUserId);
        }
    }

    // Delete user function
    function deleteUser(userId, userName) {
        if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
            fetch(`/admin/manage-users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    location.reload(); // Refresh to remove deleted user
                } else {
                    showAlert('error', data.message || 'Failed to delete user');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred while deleting the user');
            });
        }
    }

    // Populate view modal
    function populateViewModal(user) {
        currentUserId = user.id;
        
        // Basic information
        document.getElementById('view_user_id').textContent = user.id;
        document.getElementById('view_user_name').textContent = user.name;
        document.getElementById('view_user_email').textContent = user.email;
        document.getElementById('view_user_name_display').textContent = user.name;
        document.getElementById('view_user_email_display').textContent = user.email;
        
        // Avatar
        const avatar = document.getElementById('view_user_avatar');
        avatar.textContent = user.name.charAt(0).toUpperCase();
        
        // Status
        const statusBadge = getStatusBadge(user.status || 'active');
        document.getElementById('view_user_status').innerHTML = statusBadge;
        document.getElementById('view_user_status_badge').innerHTML = statusBadge;
        
        // Dates
        document.getElementById('view_user_created').textContent = formatDate(user.created_at);
        document.getElementById('view_user_updated').textContent = formatDate(user.updated_at);
        document.getElementById('view_user_last_login').textContent = user.last_login_at ? formatDate(user.last_login_at) : 'Never';
        document.getElementById('view_account_created').textContent = formatDate(user.created_at);
        document.getElementById('view_account_status').innerHTML = statusBadge;
        
        // Email verification
        document.getElementById('view_user_verified').textContent = user.email_verified_at ? 'Yes' : 'No';
        
        // Roles
        if (user.roles && user.roles.length > 0) {
            const rolesBadges = user.roles.map(role => {
                const badgeClass = getRoleBadgeClass(role.name);
                return `<span class="badge ${badgeClass} me-1">${capitalizeFirst(role.name)}</span>`;
            }).join('');
            document.getElementById('view_user_roles').innerHTML = rolesBadges;
        } else {
            document.getElementById('view_user_roles').innerHTML = '<span class="badge bg-secondary">No Role</span>';
        }
        
        // Permissions
        if (user.permissions && user.permissions.length > 0) {
            const permissionsList = user.permissions.map(permission => 
                `<span class="badge bg-light text-dark me-1">${permission.name}</span>`
            ).join('');
            document.getElementById('view_user_permissions').innerHTML = permissionsList;
        } else {
            document.getElementById('view_user_permissions').innerHTML = '<span class="text-muted">No direct permissions</span>';
        }
        
        // Show/hide tabs based on user roles
        const tabElements = ['admin-tab-li', 'student-tab-li', 'teacher-tab-li', 'guidance-tab-li'];
        tabElements.forEach(tabId => {
            const tab = document.getElementById(tabId);
            if (tab) tab.style.display = 'none';
        });
        
        if (user.roles) {
            user.roles.forEach(role => {
                if (role.name === 'admin') {
                    const adminTab = document.getElementById('admin-tab-li');
                    if (adminTab) adminTab.style.display = 'block';
                    populateAdminInfo(user.admin);
                } else if (role.name === 'student') {
                    const studentTab = document.getElementById('student-tab-li');
                    if (studentTab) studentTab.style.display = 'block';
                    populateStudentInfo(user.student);
                } else if (role.name === 'teacher') {
                    const teacherTab = document.getElementById('teacher-tab-li');
                    if (teacherTab) teacherTab.style.display = 'block';
                    populateTeacherInfo(user.teacher);
                } else if (role.name === 'guidance_discipline') {
                    const guidanceTab = document.getElementById('guidance-tab-li');
                    if (guidanceTab) guidanceTab.style.display = 'block';
                    populateGuidanceInfo(user.guidance_discipline);
                }
            });
        }
    }

    // Populate admin info tab
    function populateAdminInfo(adminInfo) {
        if (adminInfo) {
            document.getElementById('view_admin_employee_id').textContent = adminInfo.employee_id || '-';
            document.getElementById('view_admin_department').textContent = adminInfo.department || '-';
            document.getElementById('view_admin_position').textContent = adminInfo.position || '-';
            document.getElementById('view_admin_level').textContent = capitalizeFirst(adminInfo.admin_level || '-');
        } else {
            const fields = ['view_admin_employee_id', 'view_admin_department', 'view_admin_position', 'view_admin_level'];
            fields.forEach(fieldId => {
                const element = document.getElementById(fieldId);
                if (element) element.textContent = '-';
            });
        }
    }

    // Populate student info tab
    function populateStudentInfo(studentInfo) {
        if (studentInfo) {
            document.getElementById('view_student_id').textContent = studentInfo.student_id || '-';
            document.getElementById('view_student_lrn').textContent = studentInfo.lrn || '-';
            document.getElementById('view_student_grade').textContent = studentInfo.grade_level || '-';
            document.getElementById('view_student_section').textContent = studentInfo.section || '-';
            document.getElementById('view_student_enrollment').innerHTML = getStatusBadge(studentInfo.enrollment_status || 'pending');
            document.getElementById('view_student_academic_year').textContent = studentInfo.academic_year || '-';
        } else {
            const fields = ['view_student_id', 'view_student_lrn', 'view_student_grade', 'view_student_section', 'view_student_academic_year'];
            fields.forEach(fieldId => {
                const element = document.getElementById(fieldId);
                if (element) element.textContent = '-';
            });
            const enrollmentElement = document.getElementById('view_student_enrollment');
            if (enrollmentElement) enrollmentElement.textContent = '-';
        }
    }

    // Populate teacher info tab
    function populateTeacherInfo(teacherInfo) {
        if (teacherInfo) {
            document.getElementById('view_teacher_employee_id').textContent = teacherInfo.employee_id || '-';
            document.getElementById('view_teacher_department').textContent = teacherInfo.department || '-';
            document.getElementById('view_teacher_position').textContent = teacherInfo.position || '-';
            document.getElementById('view_teacher_hire_date').textContent = teacherInfo.hire_date ? formatDate(teacherInfo.hire_date) : '-';
            document.getElementById('view_teacher_specialization').textContent = teacherInfo.specialization || '-';
            document.getElementById('view_teacher_employment_status').innerHTML = getStatusBadge(teacherInfo.employment_status || 'active');
        } else {
            const fields = ['view_teacher_employee_id', 'view_teacher_department', 'view_teacher_position', 'view_teacher_hire_date', 'view_teacher_specialization'];
            fields.forEach(fieldId => {
                const element = document.getElementById(fieldId);
                if (element) element.textContent = '-';
            });
            const statusElement = document.getElementById('view_teacher_employment_status');
            if (statusElement) statusElement.textContent = '-';
        }
    }

    // Populate guidance info tab
    function populateGuidanceInfo(guidanceInfo) {
        if (guidanceInfo) {
            document.getElementById('view_guidance_employee_id').textContent = guidanceInfo.employee_id || '-';
            document.getElementById('view_guidance_department').textContent = guidanceInfo.department || '-';
            document.getElementById('view_guidance_position').textContent = guidanceInfo.position || '-';
            document.getElementById('view_guidance_hire_date').textContent = guidanceInfo.hire_date ? formatDate(guidanceInfo.hire_date) : '-';
            document.getElementById('view_guidance_specialization').textContent = guidanceInfo.specialization || '-';
            document.getElementById('view_guidance_employment_status').innerHTML = getStatusBadge(guidanceInfo.employment_status || 'active');
        } else {
            const fields = ['view_guidance_employee_id', 'view_guidance_department', 'view_guidance_position', 'view_guidance_hire_date', 'view_guidance_specialization'];
            fields.forEach(fieldId => {
                const element = document.getElementById(fieldId);
                if (element) element.textContent = '-';
            });
            const statusElement = document.getElementById('view_guidance_employment_status');
            if (statusElement) statusElement.textContent = '-';
        }
    }

    // Populate edit modal
    function populateEditModal(user) {
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_status').value = user.status || 'active';
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_password_confirmation').value = '';
        
        // Clear all role checkboxes
        const roleCheckboxes = document.querySelectorAll('#edit_roles_container input[type="checkbox"]');
        roleCheckboxes.forEach(checkbox => checkbox.checked = false);
        
        // Check user's current roles
        if (user.roles) {
            user.roles.forEach(role => {
                const checkbox = document.querySelector(`#edit_roles_container input[value="${role.name}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }
        
        // Populate additional fields based on user type
        const additionalFields = document.getElementById('editAdditionalFields');
        additionalFields.innerHTML = '';
        
        if (user.roles) {
            user.roles.forEach(role => {
                if (role.name === 'admin' && user.admin) {
                    populateEditAdminFields(user.admin);
                } else if (role.name === 'teacher' && user.teacher) {
                    populateEditTeacherFields(user.teacher);
                } else if (role.name === 'student' && user.student) {
                    populateEditStudentFields(user.student);
                } else if (role.name === 'guidance_discipline' && user.guidance_discipline) {
                    populateEditGuidanceFields(user.guidance_discipline);
                }
            });
        }
    }

    // Utility functions
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 
                          type === 'info' ? 'alert-info' : 'alert-danger';
        const iconClass = type === 'success' ? 'ri-check-circle-line' : 
                         type === 'warning' ? 'ri-alert-line' : 
                         type === 'info' ? 'ri-information-line' : 'ri-error-warning-line';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Add new alert at the top of the page
        const pageHeader = document.querySelector('.page-header');
        if (pageHeader) {
            pageHeader.insertAdjacentHTML('afterend', alertHtml);
        }
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
        
        // Scroll to top to show alert
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function displayValidationErrors(form, errors) {
        for (let field in errors) {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                const feedback = input.parentNode.querySelector('.invalid-feedback');
                input.classList.add('is-invalid');
                if (feedback) {
                    feedback.textContent = errors[field][0];
                }
            }
        }
    }

    function clearValidationErrors(form) {
        const invalidInputs = form.querySelectorAll('.is-invalid');
        invalidInputs.forEach(input => input.classList.remove('is-invalid'));
        
        const feedbacks = form.querySelectorAll('.invalid-feedback');
        feedbacks.forEach(feedback => feedback.textContent = '');
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function getRoleBadgeClass(roleName) {
        switch(roleName) {
            case 'admin': return 'role-admin';
            case 'teacher': return 'role-teacher';
            case 'student': return 'role-student';
            default: return 'bg-secondary';
        }
    }

    function getStatusBadge(status) {
        const statusClasses = {
            'active': 'status-active',
            'inactive': 'status-inactive',
            'pending': 'status-pending',
            'suspended': 'status-suspended'
        };
        
        const statusClass = statusClasses[status] || 'status-inactive';
        return `<span class="status-badge ${statusClass}">${capitalizeFirst(status)}</span>`;
    }

    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function togglePasswordVisibility(button) {
        const input = button.previousElementSibling;
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('ri-eye-line');
            icon.classList.add('ri-eye-off-line');
        } else {
            input.type = 'password';
            icon.classList.remove('ri-eye-off-line');
            icon.classList.add('ri-eye-line');
        }
    }

    function resetCreateForm() {
        const form = document.getElementById('createUserForm');
        if (form) {
            form.reset();
            document.querySelectorAll('.user-type-card').forEach(card => {
                card.classList.remove('selected');
            });
            const additionalSection = document.getElementById('additionalInfoSection');
            if (additionalSection) additionalSection.style.display = 'none';
            document.querySelectorAll('.user-type-fields').forEach(field => {
                field.style.display = 'none';
            });
            clearValidationErrors(form);
        }
    }

    function updateStats() {
        fetch('/admin/manage-users/stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update stats cards
                    const statsElements = {
                        'total-users': data.stats.total_users,
                        'total-admins': data.stats.total_admins,
                        'total-teachers': data.stats.total_teachers,
                        'total-students': data.stats.total_students,
                        'total-guidance': data.stats.total_guidance
                    };
                    
                    Object.entries(statsElements).forEach(([elementClass, value]) => {
                        const element = document.querySelector(`.${elementClass}`);
                        if (element) {
                            element.textContent = value;
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error updating stats:', error);
            });
    }

    // Bulk action function
    function bulkAction(action) {
        const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
        if (selectedUsers.length === 0) {
            showAlert('warning', 'Please select at least one user to perform bulk actions.');
            return;
        }

        const userIds = Array.from(selectedUsers).map(checkbox => checkbox.value);
        const confirmMessage = `Are you sure you want to ${action} ${userIds.length} selected user(s)?`;
        
        if (!confirm(confirmMessage)) {
            return;
        }

        const formData = new FormData();
        formData.append('action', action);
        formData.append('user_ids', JSON.stringify(userIds));

        fetch('/admin/manage-users/bulk-action', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.message || 'Bulk action failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred during bulk action');
        });
    }

    function exportUsers(format) {
        const url = `/admin/manage-users/export?format=${format}`;
        window.open(url, '_blank');
    }

    function importUsers() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.csv,.xlsx,.xls';
        input.onchange = function(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('import_file', file);

            showAlert('info', 'Importing users, please wait...');

            fetch('/admin/manage-users/import', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', `Successfully imported ${data.imported_count} users`);
                    location.reload();
                } else {
                    showAlert('error', data.message || 'Import failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred during import');
            });
        };
        input.click();
    }

    function generateUserReport() {
        const url = '/admin/manage-users/report';
        window.open(url, '_blank');
    }

    function showBulkActions() {
        const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
        if (selectedUsers.length === 0) {
            showAlert('warning', 'Please select users first to see bulk actions.');
            return;
        }

        const actions = [
            { label: 'Activate Selected', action: 'activate', icon: 'ri-check-line', class: 'btn-success' },
            { label: 'Deactivate Selected', action: 'deactivate', icon: 'ri-close-line', class: 'btn-warning' },
            { label: 'Delete Selected', action: 'delete', icon: 'ri-delete-bin-line', class: 'btn-danger' }
        ];

        let actionsHtml = '<div class="bulk-actions-menu">';
        actions.forEach(actionItem => {
            actionsHtml += `<button class="btn ${actionItem.class} me-2 mb-2" onclick="bulkAction('${actionItem.action}')">
                <i class="${actionItem.icon} me-1"></i>${actionItem.label}
            </button>`;
        });
        actionsHtml += '</div>';

        showAlert('info', `Selected ${selectedUsers.length} users. Choose an action:<br>${actionsHtml}`);
    }

    function toggleSelectAll() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        
        updateBulkActionsState();
    }

    function updateBulkActionsState() {
        const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
        const bulkActionsBtn = document.getElementById('bulkActionsBtn');
        const selectAllCheckbox = document.getElementById('selectAll');
        const allUserCheckboxes = document.querySelectorAll('.user-checkbox');
        
        if (bulkActionsBtn) {
            bulkActionsBtn.disabled = selectedUsers.length === 0;
            bulkActionsBtn.textContent = selectedUsers.length > 0 
                ? `Bulk Actions (${selectedUsers.length})` 
                : 'Bulk Actions';
        }
        
        // Update select all checkbox state
        if (selectAllCheckbox && allUserCheckboxes.length > 0) {
            const allChecked = selectedUsers.length === allUserCheckboxes.length;
            const someChecked = selectedUsers.length > 0;
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }
    }

    // Populate edit form fields for admin users
    function populateEditAdminFields(adminInfo) {
        if (!adminInfo) return;
        
        const fields = {
            'edit_employee_id': adminInfo.employee_id,
            'edit_department': adminInfo.department,
            'edit_position': adminInfo.position,
            'edit_admin_level': adminInfo.admin_level
        };
        
        Object.entries(fields).forEach(([fieldId, value]) => {
            const field = document.getElementById(fieldId);
            if (field && value !== null && value !== undefined) {
                field.value = value;
            }
        });
    }

    function populateEditTeacherFields(teacherInfo) {
        if (!teacherInfo) return;
        
        const fields = {
            'edit_teacher_employee_id': teacherInfo.employee_id,
            'edit_teacher_department': teacherInfo.department,
            'edit_specialization': teacherInfo.specialization,
            'edit_hire_date': teacherInfo.hire_date,
            'edit_employment_status': teacherInfo.employment_status,
            'edit_qualification': teacherInfo.qualification,
            'edit_experience_years': teacherInfo.experience_years
        };
        
        Object.entries(fields).forEach(([fieldId, value]) => {
            const field = document.getElementById(fieldId);
            if (field && value !== null && value !== undefined) {
                field.value = value;
            }
        });
    }

    function populateEditStudentFields(studentInfo) {
        if (!studentInfo) return;
        
        const fields = {
            'edit_student_id': studentInfo.student_id,
            'edit_lrn': studentInfo.lrn,
            'edit_grade_level': studentInfo.grade_level,
            'edit_section': studentInfo.section,
            'edit_academic_year': studentInfo.academic_year,
            'edit_enrollment_date': studentInfo.enrollment_date,
            'edit_enrollment_status': studentInfo.enrollment_status,
            'edit_guardian_name': studentInfo.guardian_name,
            'edit_guardian_contact': studentInfo.guardian_contact,
            'edit_guardian_relationship': studentInfo.guardian_relationship
        };
        
        Object.entries(fields).forEach(([fieldId, value]) => {
            const field = document.getElementById(fieldId);
            if (field && value !== null && value !== undefined) {
                field.value = value;
            }
        });
    }

    function populateEditGuidanceFields(guidanceInfo) {
        if (!guidanceInfo) return;
        
        const fields = {
            'edit_guidance_employee_id': guidanceInfo.employee_id,
            'edit_guidance_department': guidanceInfo.department,
            'edit_specialization': guidanceInfo.specialization,
            'edit_license_number': guidanceInfo.license_number,
            'edit_certification': guidanceInfo.certification,
            'edit_hire_date': guidanceInfo.hire_date,
            'edit_employment_status': guidanceInfo.employment_status
        };
        
        Object.entries(fields).forEach(([fieldId, value]) => {
            const field = document.getElementById(fieldId);
            if (field && value !== null && value !== undefined) {
                field.value = value;
            }
        });
    }

    // Initialize the page
    initializePage();
});
