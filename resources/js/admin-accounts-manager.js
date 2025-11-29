// Admin Accounts Manager - Properly exposed for Vite

window.togglePasswordVisibility = function() {
    const passwordInput = document.getElementById('accountPassword');
    const eyeIcon = document.getElementById('passwordEyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.className = 'ri-eye-off-line';
    } else {
        passwordInput.type = 'password';
        eyeIcon.className = 'ri-eye-line';
    }
};

window.openAddAccountModal = function() {
    const modal = new bootstrap.Modal(document.getElementById('addAccountModal'));
    document.getElementById('accountForm').reset();
    document.getElementById('accountFormTitle').textContent = 'Add New Account';
    document.getElementById('submitAccountBtn').textContent = 'Add Account';
    document.getElementById('accountId').value = '';
    document.getElementById('accountPassword').type = 'password';
    document.getElementById('accountPassword').value = '';
    document.getElementById('passwordEyeIcon').className = 'ri-eye-line';
    document.getElementById('passwordHint').textContent = 'Leave empty to auto-generate password';
    document.getElementById('passwordTouched').value = 'false';
    modal.show();
};

window.openEditAccountModal = function(userId, userName, userEmail, userRole) {
    const modal = new bootstrap.Modal(document.getElementById('addAccountModal'));
    document.getElementById('accountFormTitle').textContent = 'Edit Account';
    document.getElementById('submitAccountBtn').textContent = 'Update Account';
    document.getElementById('accountId').value = userId;
    document.getElementById('accountName').value = userName;
    document.getElementById('accountEmail').value = userEmail;
    document.getElementById('accountRole').value = userRole;
    document.getElementById('accountPassword').type = 'password';
    document.getElementById('passwordEyeIcon').className = 'ri-eye-line';
    document.getElementById('passwordHint').textContent = 'Leave empty to keep current password';
    document.getElementById('passwordTouched').value = 'false';
    
    // Fetch current password from server
    fetch(`/admin/accounts/${userId}/password`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        }
        throw new Error('Failed to fetch password');
    })
    .then(data => {
        if (data.success) {
            if (data.password) {
                // Display the real plain password
                document.getElementById('accountPassword').value = data.password;
                // Update hint to show current password is displayed
                document.getElementById('passwordHint').innerHTML = '<i class="ri-information-line me-1"></i><strong>Current Password:</strong> Displayed above. Clear and enter new password to change it.';
            } else {
                // Password not available in cache
                document.getElementById('accountPassword').value = '';
                document.getElementById('passwordHint').innerHTML = '<i class="ri-alert-line me-1"></i><strong>Password not available.</strong> Please set a new password.';
            }
        }
    })
    .catch(error => {
        document.getElementById('accountPassword').value = '';
        document.getElementById('passwordHint').textContent = 'Leave empty to keep current password';
    });
    
    modal.show();
};

window.deleteAccount = function(userId, userName) {
    if (!confirm(`Are you sure you want to delete the account for ${userName}?`)) {
        return;
    }

    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Deleting...';

    fetch(`/admin/accounts/${userId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to delete account');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (typeof showAlert === 'function') {
                showAlert(`Account for ${userName} deleted successfully`, 'success', 3000);
            }
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Failed to delete account');
        }
    })
    .catch(error => {
        if (typeof showAlert === 'function') {
            showAlert(error.message || 'Error deleting account', 'danger', 3000);
        }
        btn.disabled = false;
        btn.innerHTML = originalContent;
    });
};

window.trackPasswordChange = function() {
    document.getElementById('passwordTouched').value = 'true';
};

window.submitAccountForm = function() {
    const accountId = document.getElementById('accountId').value;
    const name = document.getElementById('accountName').value.trim();
    const email = document.getElementById('accountEmail').value.trim();
    const password = document.getElementById('accountPassword').value.trim();
    const passwordTouched = document.getElementById('passwordTouched').value === 'true';
    const role = document.getElementById('accountRole').value;

    if (!name || !email || !role) {
        if (typeof showAlert === 'function') {
            showAlert('Please fill in all required fields', 'warning', 3000);
        }
        return;
    }

    // For new accounts, password is required
    if (!accountId && !password) {
        if (typeof showAlert === 'function') {
            showAlert('Password is required for new accounts', 'warning', 3000);
        }
        return;
    }

    // For edit accounts, if password field was touched, it's required
    if (accountId && passwordTouched && !password) {
        if (typeof showAlert === 'function') {
            showAlert('Password is required when changing it', 'warning', 3000);
        }
        return;
    }

    if (!isValidEmail(email)) {
        if (typeof showAlert === 'function') {
            showAlert('Please enter a valid email address', 'warning', 3000);
        }
        return;
    }

    // Validate password length if provided
    if (password && password.length < 6) {
        if (typeof showAlert === 'function') {
            showAlert('Password must be at least 6 characters', 'warning', 3000);
        }
        return;
    }

    const btn = document.getElementById('submitAccountBtn');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Processing...';

    const url = accountId ? `/admin/accounts/${accountId}` : '/admin/accounts';
    const method = accountId ? 'PUT' : 'POST';

    const formData = {
        name: name,
        email: email,
        role: role
    };

    // Include password if provided or if touched in edit mode
    if (password && (passwordTouched || !accountId)) {
        formData.password = password;
    }

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Failed to save account');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (typeof showAlert === 'function') {
                showAlert(data.message || 'Account saved successfully', 'success', 5000);
            }
            setTimeout(() => location.reload(), 2000);
        } else {
            throw new Error(data.message || 'Failed to save account');
        }
    })
    .catch(error => {
        if (typeof showAlert === 'function') {
            showAlert(error.message || 'Error saving account', 'danger', 3000);
        }
        btn.disabled = false;
        btn.innerHTML = originalContent;
    });
};

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
