document.addEventListener('DOMContentLoaded', function() {
    // CSRF token setup for AJAX
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Set up AJAX headers
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    
    // Update the existing functions to use AJAX
    window.approveStudent = function(id) {
        if (confirm('Are you sure you want to approve this student?')) {
            fetch(`/admin/enrollments/${id}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    location.reload();
                } else {
                    showAlert('error', 'Failed to approve student');
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred');
                console.error('Error:', error);
            });
        }
    };

    window.rejectStudent = function(id) {
        if (confirm('Are you sure you want to reject this student?')) {
            fetch(`/admin/enrollments/${id}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('warning', data.message);
                    location.reload();
                } else {
                    showAlert('error', 'Failed to reject student');
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred');
                console.error('Error:', error);
            });
        }
    };

    window.deleteStudent = function(id) {
        if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
            fetch(`/admin/enrollments/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    location.reload();
                } else {
                    showAlert('error', 'Failed to delete student');
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred');
                console.error('Error:', error);
            });
        }
    };

    // Bulk actions with AJAX
    window.bulkApprove = function() {
        const selected = getSelectedStudents();
        if (selected.length > 0 && confirm(`Approve ${selected.length} students?`)) {
            fetch('/admin/enrollments/bulk/approve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ student_ids: selected })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    location.reload();
                } else {
                    showAlert('error', 'Failed to approve students');
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred');
                console.error('Error:', error);
            });
        }
    };

    window.bulkReject = function() {
        const selected = getSelectedStudents();
        if (selected.length > 0 && confirm(`Reject ${selected.length} students?`)) {
            fetch('/admin/enrollments/bulk/reject', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ student_ids: selected })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('warning', data.message);
                    location.reload();
                } else {
                    showAlert('error', 'Failed to reject students');
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred');
                console.error('Error:', error);
            });
        }
    };

    window.bulkDelete = function() {
        const selected = getSelectedStudents();
        if (selected.length > 0 && confirm(`Delete ${selected.length} students? This action cannot be undone.`)) {
            fetch('/admin/enrollments/bulk/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ student_ids: selected })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    location.reload();
                } else {
                    showAlert('error', 'Failed to delete students');
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred');
                console.error('Error:', error);
            });
        }
    };

    // Alert function
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }
});