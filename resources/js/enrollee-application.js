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

    // Data Change Request functionality
    const fieldSelect = document.getElementById('field_name');
    const currentValueInput = document.getElementById('current_value');
    const oldValueInput = document.getElementById('old_value');
    
    // Get all new value input elements
    const newValueInputs = {
        text: document.getElementById('new_value'),
        uppercase: document.getElementById('new_value_uppercase'),
        email: document.getElementById('new_value_email'),
        tel: document.getElementById('new_value_tel'),
        date: document.getElementById('new_value_date'),
        textarea: document.getElementById('new_value_textarea'),
        gender: document.getElementById('new_value_gender'),
        student_type: document.getElementById('new_value_student_type'),
        grade_level: document.getElementById('new_value_grade_level'),
        strand: document.getElementById('new_value_strand'),
        track: document.getElementById('new_value_track'),
        last_school_type: document.getElementById('new_value_last_school_type')
    };
    
    // Define field input type mappings
    const fieldInputTypes = {
        // Name fields - uppercase
        'first_name': 'uppercase',
        'middle_name': 'uppercase',
        'last_name': 'uppercase',
        'suffix': 'uppercase',
        'nationality': 'uppercase',
        'religion': 'uppercase',
        
        // Contact fields
        'email': 'email',
        'contact_number': 'tel',
        'father_contact': 'tel',
        'mother_contact': 'tel',
        'guardian_contact': 'tel',
        
        // Date fields
        'date_of_birth': 'date',
        
        // Address fields - uppercase
        'address': 'textarea',
        'city': 'uppercase',
        'province': 'uppercase',
        'zip_code': 'text',
        
        // Dropdown fields
        'gender': 'gender',
        'student_type': 'student_type',
        'grade_level_applied': 'grade_level',
        'strand_applied': 'strand',
        'track_applied': 'track',
        'last_school_type': 'last_school_type',
        
        // Parent/Guardian names - uppercase
        'father_name': 'uppercase',
        'father_occupation': 'uppercase',
        'mother_name': 'uppercase',
        'mother_occupation': 'uppercase',
        'guardian_name': 'uppercase',
        'last_school_name': 'uppercase',
        
        // Medical history - textarea uppercase
        'medical_history': 'textarea'
    };
    
    function hideAllNewValueInputs() {
        Object.values(newValueInputs).forEach(input => {
            if (input) {
                input.style.display = 'none';
                input.removeAttribute('name');
                input.required = false;
            }
        });
    }
    
    function showNewValueInput(inputType) {
        hideAllNewValueInputs();
        
        const input = newValueInputs[inputType];
        if (input) {
            input.style.display = 'block';
            input.setAttribute('name', 'new_value');
            input.required = true;
            
            // Clear previous value
            if (input.tagName === 'SELECT') {
                input.selectedIndex = 0;
            } else {
                input.value = '';
            }
        }
    }
    
    console.log('Field select element:', fieldSelect);
    console.log('Current value input:', currentValueInput);
    console.log('Old value input:', oldValueInput);
    console.log('Available enrollee data:', window.enrolleeData);
    
    if (fieldSelect && currentValueInput) {
        // Get enrollee data from the page
        const enrolleeData = window.enrolleeData || {};
        
        fieldSelect.addEventListener('change', function() {
            const selectedField = this.value;
            console.log('Selected field:', selectedField);
            console.log('Enrollee data keys:', Object.keys(enrolleeData));
            console.log('Field exists in data:', selectedField in enrolleeData);
            
            // Show appropriate input type
            const inputType = fieldInputTypes[selectedField] || 'text';
            showNewValueInput(inputType);
            
            if (selectedField && selectedField in enrolleeData) {
                let currentValue = enrolleeData[selectedField];
                console.log('Raw current value for', selectedField, ':', currentValue);
                
                // Handle different data types and null/empty values
                let displayValue = '';
                if (currentValue === null || currentValue === undefined || currentValue === '') {
                    displayValue = 'Not provided';
                } else {
                    // Handle special cases for display
                    if (selectedField === 'date_of_birth' && currentValue) {
                        // Format date for display
                        try {
                            const date = new Date(currentValue);
                            displayValue = date.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });
                        } catch (e) {
                            displayValue = String(currentValue);
                        }
                    } else {
                        displayValue = String(currentValue);
                    }
                }
                
                console.log('Display value:', displayValue);
                
                currentValueInput.value = displayValue;
                if (oldValueInput) {
                    oldValueInput.value = String(currentValue || ''); // Store raw value for form submission
                }
            } else {
                console.log('Field not found in enrollee data or no field selected');
                currentValueInput.value = 'Not provided';
                if (oldValueInput) {
                    oldValueInput.value = '';
                }
            }
        });
        
        // Initialize by hiding all inputs
        hideAllNewValueInputs();
    } else {
        console.error('Required elements not found for data change request functionality');
    }
});