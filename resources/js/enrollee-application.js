document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle function
    window.togglePassword = function(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '_icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'ri-eye-off-line';
        } else {
            field.type = 'password';
            icon.className = 'ri-eye-line';
        }
    };

    // Change password form validation and submission
    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function(e) {
            // Clear previous validation errors
            const inputs = this.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('is-invalid');
            });
            
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('new_password_confirmation').value;
            
            // Client-side validation for password confirmation
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                
                const confirmField = document.getElementById('new_password_confirmation');
                confirmField.classList.add('is-invalid');
                
                // Create or update error message
                let errorDiv = confirmField.parentNode.parentNode.querySelector('.invalid-feedback');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    confirmField.parentNode.parentNode.appendChild(errorDiv);
                }
                errorDiv.textContent = 'Passwords do not match.';
                errorDiv.style.display = 'block';
                
                return false;
            }
            
            // If validation passes, the form will submit normally with proper method override
        });
    }

    // Auto-show change password modal if there are validation errors
    if (document.querySelector('#changePasswordModal .is-invalid')) {
        const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
        modal.show();
    }

    // Auto-show change password modal if there are success/error messages related to password
    const successAlert = document.querySelector('#changePasswordModal .alert-success');
    const errorAlert = document.querySelector('#changePasswordModal .alert-danger');
    
    if (successAlert || errorAlert) {
        const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
        modal.show();
        
        // Auto-close modal after 3 seconds if it's a success message
        if (successAlert) {
            setTimeout(() => {
                modal.hide();
                // Clear the form
                if (changePasswordForm) {
                    changePasswordForm.reset();
                }
            }, 3000);
        }
    }
});