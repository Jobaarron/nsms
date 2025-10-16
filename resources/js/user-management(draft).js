// User Management JavaScript Functions

// Function to open create user modals for new roles
function openCreateUserModal(userType) {
    // No need for event handling since we're using buttons instead of dropdown links
    
    // Close any open modals first
    $('.modal').modal('hide');
    
    // Open the appropriate modal based on user type
    switch(userType) {
        case 'admin':
            $('#createAdminModal').modal('show');
            break;
        case 'teacher':
            $('#createTeacherModal').modal('show');
            break;
        case 'guidance':
            $('#createGuidanceModal').modal('show');
            break;
        case 'discipline':
            $('#createDisciplineModal').modal('show');
            break;
        case 'guidance_counselor':
            $('#createGuidanceCounselorModal').modal('show');
            break;
        case 'discipline_head':
            $('#createDisciplineHeadModal').modal('show');
            break;
        case 'discipline_officer':
            $('#createDisciplineOfficerModal').modal('show');
            break;
        case 'cashier':
            $('#createCashierModal').modal('show');
            break;
        case 'faculty_head':
            $('#createFacultyHeadModal').modal('show');
            break;
        default:
            console.error('Unknown user type:', userType);
    }
}

// Handle form submissions for new user types
$(document).ready(function() {
    
    // Admin Form
    $('#createAdminForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line spin me-1"></i>Creating...');
        
        $.ajax({
            url: '/admin/users/admin',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#createAdminModal').modal('hide');
                    showAlert('success', response.message);
                    location.reload();
                } else {
                    showAlert('error', response.message || 'Error creating admin');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error creating admin';
                showAlert('error', errorMsg);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="ri-add-line me-1"></i>Create Admin');
            }
        });
    });
    
    // Teacher Form
    $('#createTeacherForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line spin me-1"></i>Creating...');
        
        $.ajax({
            url: '/admin/users/teacher',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#createTeacherModal').modal('hide');
                    showAlert('success', response.message);
                    location.reload();
                } else {
                    showAlert('error', response.message || 'Error creating teacher');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error creating teacher';
                showAlert('error', errorMsg);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="ri-add-line me-1"></i>Create Teacher');
            }
        });
    });
    
    // Guidance Counselor Form
    $('#createGuidanceCounselorForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line spin me-1"></i>Creating...');
        
        $.ajax({
            url: '/admin/users/guidance-counselor',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#createGuidanceCounselorModal').modal('hide');
                    showAlert('success', response.message);
                    location.reload();
                } else {
                    showAlert('error', response.message || 'Error creating guidance counselor');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error creating guidance counselor';
                showAlert('error', errorMsg);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="ri-add-line me-1"></i>Create Guidance Counselor');
            }
        });
    });
    
    // Discipline Head Form
    $('#createDisciplineHeadForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line spin me-1"></i>Creating...');
        
        $.ajax({
            url: '/admin/users/discipline-head',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#createDisciplineHeadModal').modal('hide');
                    showAlert('success', response.message);
                    location.reload();
                } else {
                    showAlert('error', response.message || 'Error creating discipline head');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error creating discipline head';
                showAlert('error', errorMsg);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="ri-add-line me-1"></i>Create Discipline Head');
            }
        });
    });
    
    // Discipline Officer Form
    $('#createDisciplineOfficerForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line spin me-1"></i>Creating...');
        
        $.ajax({
            url: '/admin/users/discipline-officer',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#createDisciplineOfficerModal').modal('hide');
                    showAlert('success', response.message);
                    location.reload();
                } else {
                    showAlert('error', response.message || 'Error creating discipline officer');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error creating discipline officer';
                showAlert('error', errorMsg);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="ri-add-line me-1"></i>Create Discipline Officer');
            }
        });
    });
    
    // Cashier Form
    $('#createCashierForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line spin me-1"></i>Creating...');
        
        $.ajax({
            url: '/admin/users/cashier',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#createCashierModal').modal('hide');
                    showAlert('success', response.message);
                    location.reload();
                } else {
                    showAlert('error', response.message || 'Error creating cashier');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error creating cashier';
                showAlert('error', errorMsg);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="ri-add-line me-1"></i>Create Cashier');
            }
        });
    });
    
    // Faculty Head Form
    $('#createFacultyHeadForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line spin me-1"></i>Creating...');
        
        $.ajax({
            url: '/admin/users/faculty-head',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#createFacultyHeadModal').modal('hide');
                    showAlert('success', response.message);
                    location.reload();
                } else {
                    showAlert('error', response.message || 'Error creating faculty head');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error creating faculty head';
                showAlert('error', errorMsg);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="ri-add-line me-1"></i>Create Faculty Head');
            }
        });
    });
    
});

// Helper function to show alerts
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'ri-check-circle-line' : 'ri-error-warning-line';
    
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
    $('main .container-fluid').prepend(alertHtml);
    
    // Auto-hide success alerts after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            $('.alert-success').fadeOut();
        }, 5000);
    }
}

// View user function
function viewUser(userId) {
    $.ajax({
        url: `/admin/users/${userId}`,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                const user = response.user;
                
                // Populate view modal with user data
                $('#viewUserModalLabel').text(`View User: ${user.name}`);
                
                let userDetails = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Basic Information</h6>
                            <p><strong>Name:</strong> ${user.name}</p>
                            <p><strong>Email:</strong> ${user.email}</p>
                            <p><strong>Status:</strong> <span class="badge bg-${user.status === 'active' ? 'success' : 'secondary'}">${user.status || 'Active'}</span></p>
                            <p><strong>Roles:</strong> ${user.roles.map(role => `<span class="badge bg-info me-1">${role.name}</span>`).join('')}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Account Details</h6>
                            <p><strong>Created:</strong> ${new Date(user.created_at).toLocaleDateString()}</p>
                            <p><strong>Last Updated:</strong> ${new Date(user.updated_at).toLocaleDateString()}</p>
                `;
                
                // Add role-specific details
                if (user.admin) {
                    userDetails += `
                            <p><strong>Employee ID:</strong> ${user.admin.employee_id || 'N/A'}</p>
                            <p><strong>Department:</strong> ${user.admin.department || 'N/A'}</p>
                            <p><strong>Admin Level:</strong> ${user.admin.admin_level || 'N/A'}</p>
                    `;
                } else if (user.teacher) {
                    userDetails += `
                            <p><strong>Employee ID:</strong> ${user.teacher.employee_id || 'N/A'}</p>
                            <p><strong>Department:</strong> ${user.teacher.department || 'N/A'}</p>
                            <p><strong>Position:</strong> ${user.teacher.position || 'N/A'}</p>
                    `;
                } else if (user.guidance_discipline) {
                    userDetails += `
                            <p><strong>Employee ID:</strong> ${user.guidance_discipline.employee_id || 'N/A'}</p>
                            <p><strong>Position:</strong> ${user.guidance_discipline.position || 'N/A'}</p>
                            <p><strong>Department:</strong> ${user.guidance_discipline.department || 'N/A'}</p>
                    `;
                }
                
                userDetails += `
                        </div>
                    </div>
                `;
                
                $('#viewUserContent').html(userDetails);
                $('#viewUserModal').modal('show');
            } else {
                showAlert('error', response.message || 'Error loading user details');
            }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Error loading user details';
            showAlert('error', errorMsg);
        }
    });
}

// Edit user function
function editUser(userId) {
    $.ajax({
        url: `/admin/users/${userId}`,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                const user = response.user;
                
                // Populate edit modal with user data
                $('#editUserModalLabel').text(`Edit User: ${user.name}`);
                
                // Store user ID for form submission
                $('#editUserForm').data('user-id', userId);
                
                // Populate basic fields
                $('#edit_name').val(user.name);
                $('#edit_email').val(user.email);
                $('#edit_status').val(user.status || 'active');
                
                // Determine user type and show appropriate fields
                let userType = 'user';
                if (user.admin) {
                    userType = 'admin';
                    populateEditAdminFields(user.admin);
                } else if (user.teacher) {
                    userType = 'teacher';
                    populateEditTeacherFields(user.teacher);
                } else if (user.guidance_discipline) {
                    userType = 'guidance';
                    populateEditGuidanceFields(user.guidance_discipline);
                }
                
                // Show/hide appropriate field sections
                $('.edit-user-fields').hide();
                $(`.edit-${userType}-fields`).show();
                
                $('#editUserModal').modal('show');
            } else {
                showAlert('error', response.message || 'Error loading user details');
            }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Error loading user details';
            showAlert('error', errorMsg);
        }
    });
}

// Helper functions to populate edit fields
function populateEditAdminFields(admin) {
    $('#edit_admin_employee_id').val(admin.employee_id || '');
    $('#edit_admin_department').val(admin.department || '');
    $('#edit_admin_level').val(admin.admin_level || '');
}

function populateEditTeacherFields(teacher) {
    $('#edit_teacher_employee_id').val(teacher.employee_id || '');
    $('#edit_teacher_department').val(teacher.department || '');
    $('#edit_teacher_position').val(teacher.position || '');
    $('#edit_teacher_specialization').val(teacher.specialization || '');
    $('#edit_teacher_hire_date').val(teacher.hire_date || '');
}

function populateEditGuidanceFields(guidance) {
    $('#edit_guidance_employee_id').val(guidance.employee_id || '');
    $('#edit_guidance_position').val(guidance.position || '');
    $('#edit_guidance_department').val(guidance.department || '');
    $('#edit_guidance_specialization').val(guidance.specialization || '');
    $('#edit_guidance_hire_date').val(guidance.hire_date || '');
}

// Delete user function
function deleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete user: ${userName}?\n\nThis action cannot be undone and will permanently remove the user and all associated data.`)) {
        $.ajax({
            url: `/admin/users/${userId}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message || `User "${userName}" has been successfully deleted.`);
                    // Reload the page to refresh the user list
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert('error', response.message || 'Error deleting user');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error deleting user';
                showAlert('error', errorMsg);
            }
        });
    }
}

// Handle edit user form submission
$(document).ready(function() {
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        
        const userId = $(this).data('user-id');
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line spin me-1"></i>Updating...');
        
        $.ajax({
            url: `/admin/users/${userId}`,
            method: 'PUT',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#editUserModal').modal('hide');
                    showAlert('success', response.message || 'User updated successfully!');
                    // Reload the page to refresh the user list
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert('error', response.message || 'Error updating user');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error updating user';
                showAlert('error', errorMsg);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="ri-save-line me-1"></i>Update User');
            }
        });
    });
});
