if (typeof $ === 'undefined') {
    console.error('jQuery is not loaded. Please include jQuery before this script.');
}

$(document).ready(function() {
    // Global variables
    let currentUserId = null;
    let usersTable;
    let selectedUsers = [];

    // Initialize the page
    initializePage();

    function initializePage() {
        initializeDataTable();
        setupEventListeners();
        setupCSRFToken();
        setupFormValidation();
        exposeGlobalFunctions(); // Add this line
    }

    // Expose functions globally - ADD THIS FUNCTION
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
        usersTable = $('#usersTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[1, 'desc']],
            columnDefs: [
                { orderable: false, targets: [0, 8] }, // Checkbox and Actions columns
                { searchable: false, targets: [0, 8] }
            ],
            language: {
                search: "Search users:",
                lengthMenu: "Show _MENU_ users per page",
                info: "Showing _START_ to _END_ of _TOTAL_ users",
                infoEmpty: "No users found",
                emptyTable: "No users available",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            drawCallback: function() {
                // Reinitialize checkboxes after table redraw
                updateBulkActionsButton();
            }
        });
    }

    // Setup CSRF Token
    function setupCSRFToken() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }

    // Setup Event Listeners
    function setupEventListeners() {
        // User type selection
        $('.user-type-card').on('click', function() {
            $('.user-type-card').removeClass('selected');
            $(this).addClass('selected');
            const userType = $(this).data('type');
            $('#create_user_type').val(userType);
            toggleAdditionalFields(userType);
            validateUserTypeSelection();
        });

        // Password toggle buttons
        $('#toggleCreatePassword').on('click', function() {
            togglePasswordVisibility('#create_password', this);
        });

        $('#toggleCreatePasswordConfirm').on('click', function() {
            togglePasswordVisibility('#create_password_confirmation', this);
        });

        $('#toggleEditPassword').on('click', function() {
            togglePasswordVisibility('#edit_password', this);
        });

        $('#toggleEditPasswordConfirm').on('click', function() {
            togglePasswordVisibility('#edit_password_confirmation', this);
        });

        // Select all checkbox
        $('#selectAll').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.user-checkbox').prop('checked', isChecked);
            updateSelectedUsers();
        });

        // Individual checkboxes
        $(document).on('change', '.user-checkbox', function() {
            updateSelectedUsers();
            updateSelectAllCheckbox();
        });

        // Bulk actions button
        $('#bulkActionsBtn').on('click', function() {
            if (selectedUsers.length > 0) {
                $('#selectedCount').text(selectedUsers.length);
                $('#bulkActionsModal').modal('show');
            }
        });

        // Filters
        $('#roleFilter, #statusFilter').on('change', function() {
            applyFilters();
        });

        $('#customSearch').on('keyup', function() {
            usersTable.search($(this).val()).draw();
        });

        $('#resetFilters').on('click', function() {
            resetFilters();
        });

        // Form submissions
        $('#createUserForm').on('submit', handleCreateUser);
        $('#editUserForm').on('submit', handleEditUser);

        // Modal events
        $('#createUserModal').on('hidden.bs.modal', resetCreateForm);
        $('#editUserModal').on('hidden.bs.modal', resetEditForm);

        // Real-time validation
        setupRealTimeValidation();
    }

    // Toggle password visibility
    function togglePasswordVisibility(inputSelector, button) {
        const input = $(inputSelector);
        const icon = $(button).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    }

    // Toggle additional fields based on user type
    function toggleAdditionalFields(userType) {
        $('#additionalInfoSection').show();
        $('.user-type-fields').hide();
        
        if (userType === 'admin') {
            $('#adminFields').show();
        } else if (userType === 'student') {
            $('#studentFields').show();
        } else {
            $('#additionalInfoSection').hide();
        }
    }

    // Update selected users array
    function updateSelectedUsers() {
        selectedUsers = [];
        $('.user-checkbox:checked').each(function() {
            selectedUsers.push(parseInt($(this).val()));
        });
        updateBulkActionsButton();
    }

    // Update bulk actions button state
    function updateBulkActionsButton() {
        const button = $('#bulkActionsBtn');
        if (selectedUsers.length > 0) {
            button.prop('disabled', false).text(`Bulk Actions (${selectedUsers.length})`);
        } else {
            button.prop('disabled', true).text('Bulk Actions');
        }
    }

    // Update select all checkbox
    function updateSelectAllCheckbox() {
        const totalCheckboxes = $('.user-checkbox').length;
        const checkedCheckboxes = $('.user-checkbox:checked').length;
        
        if (checkedCheckboxes === 0) {
            $('#selectAll').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#selectAll').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#selectAll').prop('indeterminate', true);
        }
    }

    // Apply filters
    function applyFilters() {
        const roleFilter = $('#roleFilter').val();
        const statusFilter = $('#statusFilter').val();
        
        usersTable.rows().every(function() {
            const row = $(this.node());
            const userRoles = row.data('user-roles') || '';
            const userStatus = row.data('user-status') || 'active';
            
            let showRow = true;
            
            if (roleFilter && !userRoles.includes(roleFilter)) {
                showRow = false;
            }
            
            if (statusFilter && userStatus !== statusFilter) {
                showRow = false;
            }
            
            if (showRow) {
                row.show();
            } else {
                row.hide();
            }
        });
        
        usersTable.draw();
    }

    // Reset filters
    function resetFilters() {
        $('#roleFilter, #statusFilter').val('');
        $('#customSearch').val('');
        usersTable.search('').draw();
        usersTable.rows().every(function() {
            $(this.node()).show();
        });
        usersTable.draw();
    }

    // Validate user type selection
    function validateUserTypeSelection() {
        const userType = $('#create_user_type').val();
        if (userType) {
            $('#create_user_type').removeClass('is-invalid');
            $('.user-type-selection').next('.invalid-feedback').text('');
        }
    }

    // Setup real-time validation
    function setupRealTimeValidation() {
        // Name validation
        $('#create_name, #edit_name').on('input', function() {
            validateName(this);
        });

        // Email validation
        $('#create_email, #edit_email').on('blur', function() {
            validateEmail(this);
        });

        // Password validation
        $('#create_password, #edit_password').on('input', function() {
            validatePassword(this);
        });

        // Password confirmation validation
        $('#create_password_confirmation, #edit_password_confirmation').on('input', function() {
            validatePasswordConfirmation(this);
        });

        // Clear validation on input
        $('.form-control, .form-select').on('input change', function() {
            if ($(this).hasClass('is-invalid')) {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').text('');
            }
        });
    }

    // Validation functions
    function validateName(input) {
        const name = $(input).val().trim();
        if (name.length < 2) {
            setFieldError(input, 'Name must be at least 2 characters long');
            return false;
        }
        clearFieldError(input);
        return true;
    }

    function validateEmail(input) {
        const email = $(input).val().trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            setFieldError(input, 'Please enter a valid email address');
            return false;
        }
        clearFieldError(input);
        return true;
    }

    function validatePassword(input) {
        const password = $(input).val();
        const isRequired = $(input).prop('required');
        
        if (isRequired && password.length < 8) {
            setFieldError(input, 'Password must be at least 8 characters long');
            return false;
        }
        clearFieldError(input);
        return true;
    }

    function validatePasswordConfirmation(input) {
        const form = $(input).closest('form');
        const password = form.find('[name="password"]').val();
        const confirmation = $(input).val();
        
        if (confirmation && password !== confirmation) {
            setFieldError(input, 'Passwords do not match');
            return false;
        }
        clearFieldError(input);
        return true;
    }

    function setFieldError(input, message) {
        $(input).addClass('is-invalid');
        $(input).siblings('.invalid-feedback').text(message);
    }

    function clearFieldError(input) {
        $(input).removeClass('is-invalid');
        $(input).siblings('.invalid-feedback').text('');
    }

    // Form validation
    function setupFormValidation() {
        // Add custom validation methods if needed
    }

    // Handle create user form submission
    function handleCreateUser(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const spinner = submitBtn.find('.spinner-border');
        
        // Validate user type selection
        if (!$('#create_user_type').val()) {
            setFieldError($('#create_user_type')[0], 'Please select a user type');
            return;
        }
        
        // Show loading state
        setLoadingState(submitBtn, spinner, true);
        clearValidationErrors(form);
        
        $.ajax({
            url: '/admin/manage-users',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#createUserModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    displayValidationErrors(form, xhr.responseJSON.errors);
                } else {
                    showAlert('error', xhr.responseJSON?.message || 'An error occurred while creating the user.');
                }
            },
            complete: function() {
                setLoadingState(submitBtn, spinner, false);
            }
        });
    }

    // Handle edit user form submission
    function handleEditUser(e) {
        e.preventDefault();
        
        const form = $(this);
        const userId = $('#edit_user_id').val();
        const submitBtn = form.find('button[type="submit"]');
        const spinner = submitBtn.find('.spinner-border');
        
        setLoadingState(submitBtn, spinner, true);
        clearValidationErrors(form);
        
        $.ajax({
            url: `/admin/manage-users/${userId}`,
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editUserModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    displayValidationErrors(form, xhr.responseJSON.errors);
                } else {
                    showAlert('error', xhr.responseJSON?.message || 'An error occurred while updating the user.');
                }
            },
            complete: function() {
                setLoadingState(submitBtn, spinner, false);
            }
        });
    }

    // Set loading state for buttons
    function setLoadingState(button, spinner, loading) {
        if (loading) {
            button.prop('disabled', true);
            spinner.removeClass('d-none');
        } else {
            button.prop('disabled', false);
            spinner.addClass('d-none');
        }
    }

    // Global functions for onclick events
    function viewUser(userId) {
        $.ajax({
            url: `/admin/manage-users/${userId}`,
            method: 'GET',
            success: function(user) {
                currentUserId = user.id;
                populateViewModal(user);
                $('#viewUserModal').modal('show');
            },
            error: function() {
                showAlert('error', 'Failed to load user data.');
            }
        });
    }

    function editUser(userId) {
        $.ajax({
            url: `/admin/manage-users/${userId}`,
            method: 'GET',
            success: function(user) {
                populateEditModal(user);
                $('#editUserModal').modal('show');
            },
            error: function() {
                showAlert('error', 'Failed to load user data.');
            }
        });
    }

    function deleteUser(userId, userName) {
        if (confirm(`Are you sure you want to delete user "${userName}"?\n\nThis action cannot be undone and will permanently remove all user data.`)) {
            $.ajax({
                url: `/admin/manage-users/${userId}`,
                method: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'Failed to delete user.');
                }
            });
        }
    }

    window.bulkAction = function(action) {
        if (selectedUsers.length === 0) {
            showAlert('warning', 'No users selected.');
            return;
        }

        let confirmMessage = '';
        let url = '';
        
        switch (action) {
            case 'activate':
                confirmMessage = `Are you sure you want to activate ${selectedUsers.length} user(s)?`;
                url = '/admin/manage-users/bulk-activate';
                break;
            case 'deactivate':
                confirmMessage = `Are you sure you want to deactivate ${selectedUsers.length} user(s)?`;
                url = '/admin/manage-users/bulk-deactivate';
                break;
            case 'suspend':
                confirmMessage = `Are you sure you want to suspend ${selectedUsers.length} user(s)?`;
                url = '/admin/manage-users/bulk-suspend';
                break;
            case 'delete':
                confirmMessage = `Are you sure you want to delete ${selectedUsers.length} user(s)?\n\nThis action cannot be undone.`;
                url = '/admin/manage-users/bulk-delete';
                break;
        }

        if (confirm(confirmMessage)) {
            $.ajax({
                url: url,
                method: 'POST',
                data: { user_ids: selectedUsers },
                success: function(response) {
                    if (response.success) {
                        $('#bulkActionsModal').modal('hide');
                        showAlert('success', response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'Bulk action failed.');
                }
            });
        }
    };

     // Populate view modal
     function populateViewModal(user) {
        // Set avatar
        $('#view_user_avatar').text(user.name.charAt(0).toUpperCase());
        
        // Set basic info
        $('#view_user_id').text(user.id);
        $('#view_user_name, #view_user_name_display').text(user.name);
        $('#view_user_email, #view_user_email_display').text(user.email);
        
        // Set status badge
        const statusBadge = getStatusBadge(user.status || 'active');
        $('#view_user_status_badge').html(statusBadge);
        $('#view_user_status').html(statusBadge);
        
        // Set dates
        $('#view_user_created, #view_account_created').text(formatDate(user.created_at));
        $('#view_user_updated').text(formatDate(user.updated_at));
        $('#view_user_last_login').text(user.last_login_at ? formatDate(user.last_login_at) : 'Never');
        
        // Set email verification
        $('#view_user_verified').html(user.email_verified_at ? 
            '<span class="badge bg-success">Verified</span>' : 
            '<span class="badge bg-warning">Not Verified</span>'
        );
        
        // Set roles
        if (user.roles && user.roles.length > 0) {
            let rolesBadges = user.roles.map(role => {
                let badgeClass = getRoleBadgeClass(role.name);
                return `<span class="badge ${badgeClass} me-1">${capitalizeFirst(role.name)}</span>`;
            }).join('');
            $('#view_user_roles').html(rolesBadges);
        } else {
            $('#view_user_roles').html('<span class="badge bg-secondary">No Role</span>');
        }
        
        // Set permissions
        if (user.permissions && user.permissions.length > 0) {
            let permissionsList = user.permissions.map(permission => 
                `<span class="badge bg-light text-dark me-1">${permission.name}</span>`
            ).join('');
            $('#view_user_permissions').html(permissionsList);
        } else {
            $('#view_user_permissions').html('<span class="text-muted">No direct permissions</span>');
        }
        
        // Show/hide tabs based on user roles
        $('#admin-tab-li, #student-tab-li').hide();
        
        if (user.roles) {
            user.roles.forEach(role => {
                if (role.name === 'admin') {
                    $('#admin-tab-li').show();
                    populateAdminInfo(user.admin_info);
                } else if (role.name === 'student') {
                    $('#student-tab-li').show();
                    populateStudentInfo(user.student_info);
                }
            });
        }
        
        // Set account status
        $('#view_account_status').html(statusBadge);
    }

    // Populate admin info tab
    function populateAdminInfo(adminInfo) {
        if (adminInfo) {
            $('#view_admin_employee_id').text(adminInfo.employee_id || '-');
            $('#view_admin_department').text(adminInfo.department || '-');
            $('#view_admin_position').text(adminInfo.position || '-');
            $('#view_admin_level').text(capitalizeFirst(adminInfo.admin_level || '-'));
        } else {
            $('#view_admin_employee_id, #view_admin_department, #view_admin_position, #view_admin_level').text('-');
        }
    }

    // Populate student info tab
    function populateStudentInfo(studentInfo) {
        if (studentInfo) {
            $('#view_student_id').text(studentInfo.student_id || '-');
            $('#view_student_lrn').text(studentInfo.lrn || '-');
            $('#view_student_grade').text(studentInfo.grade_level || '-');
            $('#view_student_section').text(studentInfo.section || '-');
            $('#view_student_enrollment').html(getStatusBadge(studentInfo.enrollment_status || 'pending'));
            $('#view_student_academic_year').text(studentInfo.academic_year || '-');
        } else {
            $('#view_student_id, #view_student_lrn, #view_student_grade, #view_student_section, #view_student_academic_year').text('-');
            $('#view_student_enrollment').text('-');
        }
    }

    // Populate edit modal
    function populateEditModal(user) {
        $('#edit_user_id').val(user.id);
        $('#edit_name').val(user.name);
        $('#edit_email').val(user.email);
        $('#edit_status').val(user.status || 'active');
        $('#edit_password, #edit_password_confirmation').val('');
        
        // Clear all role checkboxes
        $('#edit_roles_container input[type="checkbox"]').prop('checked', false);
        
        // Check user's current roles
        if (user.roles) {
            user.roles.forEach(function(role) {
                $(`#edit_role_${role.id}`).prop('checked', true);
            });
        }
        
        // Populate additional fields based on roles
        populateEditAdditionalFields(user);
        
        // Clear validation errors
        clearValidationErrors($('#editUserForm'));
    }

    // Populate additional fields in edit modal
    function populateEditAdditionalFields(user) {
        let additionalFieldsHtml = '';
        
        if (user.roles) {
            user.roles.forEach(role => {
                if (role.name === 'admin' && user.admin_info) {
                    additionalFieldsHtml += generateAdminEditFields(user.admin_info);
                } else if (role.name === 'student' && user.student_info) {
                    additionalFieldsHtml += generateStudentEditFields(user.student_info);
                }
            });
        }
        
        $('#editAdditionalFields').html(additionalFieldsHtml);
    }

    // Generate admin edit fields
    function generateAdminEditFields(adminInfo) {
        return `
            <div class="admin-edit-fields">
                <h6 class="section-title">Admin Information</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="edit_employee_id" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="edit_employee_id" name="employee_id" value="${adminInfo.employee_id || ''}">
                    </div>
                    <div class="col-md-6">
                        <label for="edit_department" class="form-label">Department</label>
                        <select class="form-select" id="edit_department" name="department">
                            <option value="">Select Department</option>
                            <option value="Administration" ${adminInfo.department === 'Administration' ? 'selected' : ''}>Administration</option>
                            <option value="Academic Affairs" ${adminInfo.department === 'Academic Affairs' ? 'selected' : ''}>Academic Affairs</option>
                            <option value="Student Affairs" ${adminInfo.department === 'Student Affairs' ? 'selected' : ''}>Student Affairs</option>
                            <option value="Finance" ${adminInfo.department === 'Finance' ? 'selected' : ''}>Finance</option>
                            <option value="IT" ${adminInfo.department === 'IT' ? 'selected' : ''}>Information Technology</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="edit_position" class="form-label">Position</label>
                        <input type="text" class="form-control" id="edit_position" name="position" value="${adminInfo.position || 'Administrator'}">
                    </div>
                    <div class="col-md-6">
                        <label for="edit_admin_level" class="form-label">Admin Level</label>
                        <select class="form-select" id="edit_admin_level" name="admin_level">
                            <option value="admin" ${adminInfo.admin_level === 'admin' ? 'selected' : ''}>Admin</option>
                            <option value="super_admin" ${adminInfo.admin_level === 'super_admin' ? 'selected' : ''}>Super Admin</option>
                            <option value="moderator" ${adminInfo.admin_level === 'moderator' ? 'selected' : ''}>Moderator</option>
                        </select>
                    </div>
                </div>
            </div>
        `;
    }

    // Generate student edit fields
    function generateStudentEditFields(studentInfo) {
        return `
            <div class="student-edit-fields">
                <h6 class="section-title">Student Information</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="edit_student_id" class="form-label">Student ID</label>
                        <input type="text" class="form-control" id="edit_student_id" name="student_id" value="${studentInfo.student_id || ''}">
                    </div>
                    <div class="col-md-6">
                        <label for="edit_lrn" class="form-label">LRN</label>
                        <input type="text" class="form-control" id="edit_lrn" name="lrn" value="${studentInfo.lrn || ''}">
                    </div>
                    <div class="col-md-4">
                        <label for="edit_grade_level" class="form-label">Grade Level</label>
                        <select class="form-select" id="edit_grade_level" name="grade_level">
                            <option value="">Select Grade</option>
                            ${generateGradeOptions(studentInfo.grade_level)}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="edit_section" class="form-label">Section</label>
                        <input type="text" class="form-control" id="edit_section" name="section" value="${studentInfo.section || ''}">
                    </div>
                    <div class="col-md-4">
                        <label for="edit_academic_year" class="form-label">Academic Year</label>
                        <input type="text" class="form-control" id="edit_academic_year" name="academic_year" value="${studentInfo.academic_year || '2024-2025'}">
                    </div>
                </div>
            </div>
        `;
    }

    // Generate grade options
    function generateGradeOptions(selectedGrade) {
        const grades = [
            'Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
            'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
        ];
        
        return grades.map(grade => 
            `<option value="${grade}" ${grade === selectedGrade ? 'selected' : ''}>${grade}</option>`
        ).join('');
    }

    // Reset create form
    function resetCreateForm() {
        $('#createUserForm')[0].reset();
        $('.user-type-card').removeClass('selected');
        $('#additionalInfoSection').hide();
        $('.user-type-fields').hide();
        clearValidationErrors($('#createUserForm'));
    }

    // Reset edit form
    function resetEditForm() {
        $('#editUserForm')[0].reset();
        $('#editAdditionalFields').empty();
        clearValidationErrors($('#editUserForm'));
    }

    // Utility Functions
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 
                          type === 'info' ? 'alert-info' : 'alert-danger';
        const iconClass = type === 'success' ? 'fas fa-check-circle' : 
                         type === 'warning' ? 'fas fa-exclamation-triangle' : 
                         type === 'info' ? 'fas fa-info-circle' : 'fas fa-exclamation-triangle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert at the top of the page
        $('.page-header').after(alertHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
        
        // Scroll to top to show alert
        $('html, body').animate({ scrollTop: 0 }, 300);
    }

    function displayValidationErrors(form, errors) {
        for (let field in errors) {
            const input = form.find(`[name="${field}"]`);
            const feedback = input.siblings('.invalid-feedback');
            
            input.addClass('is-invalid');
            feedback.text(errors[field][0]);
        }
    }

    function clearValidationErrors(form) {
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');
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

    // Export functions for global access
    window.userManagement = {
        viewUser,
        editUser,
        deleteUser,
        bulkAction,
        editUserFromView
    };
});

// Additional utility functions
function exportUsers(format) {
    const url = `/admin/manage-users/export?format=${format}`;
    window.open(url, '_blank');
}

function importUsers() {
    $('#importUsersModal').modal('show');
}

function generateUserReport() {
    const url = '/admin/manage-users/report';
    window.open(url, '_blank');
}

// Advanced search functionality
function initializeAdvancedSearch() {
    $('#advancedSearchBtn').on('click', function() {
        $('#advancedSearchModal').modal('show');
    });
    
    $('#advancedSearchForm').on('submit', function(e) {
        e.preventDefault();
        performAdvancedSearch();
    });
}

function performAdvancedSearch() {
    const formData = $('#advancedSearchForm').serialize();
    
    $.ajax({
        url: '/admin/manage-users/advanced-search',
        method: 'GET',
        data: formData,
        success: function(response) {
            // Update table with search results
            updateTableWithResults(response.users);
            $('#advancedSearchModal').modal('hide');
            showAlert('info', `Found ${response.users.length} user(s) matching your criteria.`);
        },
        error: function() {
            showAlert('error', 'Advanced search failed. Please try again.');
        }
    });
}

function updateTableWithResults(users) {
    usersTable.clear();
    users.forEach(user => {
        usersTable.row.add(formatUserRowData(user));
    });
    usersTable.draw();
}

function formatUserRowData(user) {
    return [
        `<input type="checkbox" class="user-checkbox" value="${user.id}">`,
        user.id,
        formatUserInfo(user),
        user.email,
        formatUserRoles(user.roles),
        getStatusBadge(user.status),
        formatDate(user.created_at),
        formatDate(user.updated_at),
        formatActionButtons(user)
    ];
}

function formatUserInfo(user) {
    return `
        <div class="user-info">
            <div class="user-avatar">${user.name.charAt(0).toUpperCase()}</div>
            <div class="user-details">
                <div class="user-name">${user.name}</div>
                <div class="user-id">ID: ${user.id}</div>
            </div>
        </div>
    `;
}

function formatUserRoles(roles) {
    if (!roles || roles.length === 0) {
        return '<span class="badge bg-secondary">No Role</span>';
    }
    
    return roles.map(role => {
        const badgeClass = getRoleBadgeClass(role.name);
        return `<span class="badge ${badgeClass} me-1">${capitalizeFirst(role.name)}</span>`;
    }).join('');
}

function formatActionButtons(user) {
    const canDelete = !(user.roles?.some(r => r.name === 'admin') && 
                       user.roles.filter(r => r.name === 'admin').length <= 1) && 
                      user.id !== window.currentUserId;
    
    return `
        <div class="action-buttons">
            <button type="button" class="btn btn-sm btn-outline-info" 
                    onclick="viewUser(${user.id})" title="View User">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning" 
                    onclick="editUser(${user.id})" title="Edit User">
                <i class="fas fa-edit"></i>
            </button>
            ${canDelete ? 
                `<button type="button" class="btn btn-sm btn-outline-danger" 
                        onclick="deleteUser(${user.id}, '${user.name.replace(/'/g, "\\\'")}')" title="Delete User">
                    <i class="fas fa-trash"></i>
                </button>` :
                `<button type="button" class="btn btn-sm btn-outline-secondary" disabled 
                        title="${user.id === window.currentUserId ? 'Cannot delete yourself' : 'Cannot delete last admin'}">
                    <i class="fas fa-shield-alt"></i>
                </button>`
            }
        </div>
    `;
}

// User activity tracking
function trackUserActivity(userId, action) {
    $.ajax({
        url: '/admin/manage-users/track-activity',
        method: 'POST',
        data: {
            user_id: userId,
            action: action,
            timestamp: new Date().toISOString()
        },
        success: function(response) {
            console.log('Activity tracked:', response);
        },
        error: function() {
            console.warn('Failed to track user activity');
        }
    });
}

// Real-time notifications
function initializeRealTimeNotifications() {
    if (typeof Echo !== 'undefined') {
        Echo.channel('user-management')
            .listen('UserCreated', (e) => {
                showAlert('info', `New user "${e.user.name}" has been created.`);
                refreshTable();
            })
            .listen('UserUpdated', (e) => {
                showAlert('info', `User "${e.user.name}" has been updated.`);
                refreshTable();
            })
            .listen('UserDeleted', (e) => {
                showAlert('info', `User "${e.user.name}" has been deleted.`);
                refreshTable();
            });
    }
}

function refreshTable() {
    setTimeout(() => {
        location.reload();
    }, 2000);
}

// Keyboard shortcuts
function initializeKeyboardShortcuts() {
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + N - New User
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            $('#createUserModal').modal('show');
        }
        
        // Ctrl/Cmd + F - Focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            $('#customSearch').focus();
        }
        
        // Escape - Close modals
        if (e.key === 'Escape') {
            $('.modal').modal('hide');
        }
        
        // Ctrl/Cmd + A - Select all (when table is focused)
        if ((e.ctrlKey || e.metaKey) && e.key === 'a' && $('#usersTable').is(':focus-within')) {
            e.preventDefault();
            $('#selectAll').prop('checked', true).trigger('change');
        }
    });
}

// Auto-save draft functionality
function initializeAutoSave() {
    let autoSaveTimer;
    
    $('#createUserForm input, #createUserForm select, #createUserForm textarea').on('input change', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            saveDraft('create');
        }, 2000);
    });
    
    $('#editUserForm input, #editUserForm select, #editUserForm textarea').on('input change', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            saveDraft('edit');
        }, 2000);
    });
}

function saveDraft(formType) {
    const formData = $(`#${formType}UserForm`).serialize();
    localStorage.setItem(`userForm_${formType}_draft`, formData);
    
    // Show subtle indication that draft is saved
    const indicator = $(`#${formType}UserModal .modal-header`).find('.draft-indicator');
    if (indicator.length === 0) {
        $(`#${formType}UserModal .modal-header .modal-title`).after(
            '<small class="draft-indicator text-light ms-2 opacity-75">Draft saved</small>'
        );
    }
    
    setTimeout(() => {
        $('.draft-indicator').fadeOut(1000, function() {
            $(this).remove();
        });
    }, 2000);
}

function loadDraft(formType) {
    const draft = localStorage.getItem(`userForm_${formType}_draft`);
    if (draft && confirm('A draft was found. Would you like to restore it?')) {
        const params = new URLSearchParams(draft);
        params.forEach((value, key) => {
            const field = $(`#${formType}UserForm [name="${key}"]`);
            if (field.is(':checkbox')) {
                field.prop('checked', value === 'on');
            } else {
                field.val(value);
            }
        });
    }
}

function clearDraft(formType) {
    localStorage.removeItem(`userForm_${formType}_draft`);
}

// Initialize all features when document is ready
$(document).ready(function() {
    initializeAdvancedSearch();
    initializeRealTimeNotifications();
    initializeKeyboardShortcuts();
    initializeAutoSave();
    
    // Load drafts when modals are shown
    $('#createUserModal').on('shown.bs.modal', function() {
        loadDraft('create');
    });
    
    $('#editUserModal').on('shown.bs.modal', function() {
        loadDraft('edit');
    });
    
    // Clear drafts when forms are successfully submitted
    $('#createUserForm').on('submit', function() {
        clearDraft('create');
    });
    
    $('#editUserForm').on('submit', function() {
        clearDraft('edit');
    });
    
    // Enhanced search placeholder
    $('#usersTable_filter input').attr('placeholder', 'Search by name, email, role, or ID...');
    
    // Add tooltips to action buttons
    $('[title]').tooltip();
    
    // Initialize progress indicators
    initializeProgressIndicators();
});

// Progress indicators for long operations
function initializeProgressIndicators() {
    // Show progress for bulk operations
    window.showBulkProgress = function(action, total) {
        const progressHtml = `
            <div class="bulk-progress-modal modal fade" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-body text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-tasks fa-2x text-primary"></i>
                            </div>
                            <h6>Processing ${action}...</h6>
                            <div class="progress mb-3">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted">
                                <span class="current">0</span> of <span class="total">${total}</span> completed
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(progressHtml);
        $('.bulk-progress-modal').modal('show');
    };
    
    window.updateBulkProgress = function(current, total) {
        const percentage = (current / total) * 100;
        $('.bulk-progress-modal .progress-bar').css('width', percentage + '%');
        $('.bulk-progress-modal .current').text(current);
    };
    
    window.hideBulkProgress = function() {
        $('.bulk-progress-modal').modal('hide');
        setTimeout(() => {
            $('.bulk-progress-modal').remove();
        }, 300);
    };
}

// Performance optimization
function optimizeTablePerformance() {
    // Implement virtual scrolling for large datasets
    if (usersTable.rows().count() > 1000) {
        usersTable.page.len(50).draw();
        
        // Add infinite scroll
        $('#usersTable_wrapper .dataTables_scroll').on('scroll', function() {
            const scrollTop = $(this).scrollTop();
            const scrollHeight = $(this)[0].scrollHeight;
            const clientHeight = $(this)[0].clientHeight;
            
            if (scrollTop + clientHeight >= scrollHeight - 100) {
                loadMoreUsers();
            }
        });
    }
}

function loadMoreUsers() {
    // Implementation for loading more users
    console.log('Loading more users...');
}

// Error handling and retry mechanism
function handleAjaxError(xhr, textStatus, errorThrown) {
    console.error('AJAX Error:', textStatus, errorThrown);
    
    if (xhr.status === 419) {
        // CSRF token expired
        showAlert('warning', 'Session expired. Please refresh the page.');
        setTimeout(() => {
            location.reload();
        }, 3000);
    } else if (xhr.status === 403) {
        showAlert('error', 'You do not have permission to perform this action.');
    } else if (xhr.status >= 500) {
        showAlert('error', 'Server error occurred. Please try again later.');
    } else {
        showAlert('error', 'An unexpected error occurred. Please try again.');
    }
}

// Setup global AJAX error handler
$(document).ajaxError(handleAjaxError);

// Cleanup function
function cleanup() {
    // Clear timers
    if (window.autoSaveTimer) {
        clearTimeout(window.autoSaveTimer);
    }
    
    // Remove event listeners
    $(document).off('keydown');
    
    // Clear localStorage drafts older than 24 hours
    Object.keys(localStorage).forEach(key => {
        if (key.startsWith('userForm_') && key.endsWith('_draft')) {
            // In a real implementation, you'd check the timestamp
            // For now, just clear all drafts on cleanup
            localStorage.removeItem(key);
        }
    });
}

// Call cleanup when page is unloaded
$(window).on('beforeunload', cleanup);

// Export for testing purposes
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        formatUserInfo,
        formatUserRoles,
        getRoleBadgeClass,
        getStatusBadge,
        capitalizeFirst,
        formatDate
    };
}