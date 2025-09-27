<!-- Create Admin Modal -->
<div class="modal fade" id="createAdminModal" tabindex="-1" aria-labelledby="createAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="createAdminModalLabel">
                    <i class="ri-admin-line me-2"></i>Create Admin User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createAdminForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="admin_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="admin_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="admin_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="admin_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="admin_employee_id" class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="admin_employee_id" name="employee_id" placeholder="e.g., ADM001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="admin_department" class="form-label">Department</label>
                                <select class="form-control" id="admin_department" name="department">
                                    <option value="">Select Department</option>
                                    <option value="Administration">Administration</option>
                                    <option value="Academic Affairs">Academic Affairs</option>
                                    <option value="Student Affairs">Student Affairs</option>
                                    <option value="Finance">Finance</option>
                                    <option value="IT Department">IT Department</option>
                                    <option value="Human Resources">Human Resources</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="admin_level" class="form-label">Admin Level *</label>
                        <select class="form-control" id="admin_level" name="admin_level" required>
                            <option value="">Select Admin Level</option>
                            <option value="super_admin">Super Admin (Full System Access)</option>
                            <option value="admin">Admin (Standard Admin Access)</option>
                            <option value="moderator">Moderator (Limited Admin Access)</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Default password will be set to <strong>admin123</strong>. User should change it on first login.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ri-add-line me-1"></i>Create Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Teacher Modal -->
<div class="modal fade" id="createTeacherModal" tabindex="-1" aria-labelledby="createTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createTeacherModalLabel">
                    <i class="ri-user-star-line me-2"></i>Create Teacher User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createTeacherForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="teacher_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="teacher_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="teacher_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="teacher_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="teacher_employee_id" class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="teacher_employee_id" name="employee_id" placeholder="e.g., TCH001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="teacher_department" class="form-label">Department</label>
                                <select class="form-control" id="teacher_department" name="department">
                                    <option value="">Select Department</option>
                                    <option value="Elementary">Elementary</option>
                                    <option value="Junior High School">Junior High School</option>
                                    <option value="Senior High School">Senior High School</option>
                                    <option value="Mathematics">Mathematics</option>
                                    <option value="Science">Science</option>
                                    <option value="English">English</option>
                                    <option value="Filipino">Filipino</option>
                                    <option value="Social Studies">Social Studies</option>
                                    <option value="Physical Education">Physical Education</option>
                                    <option value="Arts">Arts</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="teacher_position" class="form-label">Position</label>
                                <select class="form-control" id="teacher_position" name="position">
                                    <option value="">Select Position</option>
                                    <option value="Teacher I">Teacher I</option>
                                    <option value="Teacher II">Teacher II</option>
                                    <option value="Teacher III">Teacher III</option>
                                    <option value="Master Teacher I">Master Teacher I</option>
                                    <option value="Master Teacher II">Master Teacher II</option>
                                    <option value="Head Teacher">Head Teacher</option>
                                    <option value="Department Head">Department Head</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="teacher_specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="teacher_specialization" name="specialization" placeholder="e.g., Mathematics, English Literature">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="teacher_hire_date" class="form-label">Hire Date</label>
                        <input type="date" class="form-control" id="teacher_hire_date" name="hire_date">
                    </div>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Default password will be set to <strong>teacher123</strong>. User should change it on first login.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-add-line me-1"></i>Create Teacher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Guidance Modal -->
<div class="modal fade" id="createGuidanceModal" tabindex="-1" aria-labelledby="createGuidanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="createGuidanceModalLabel">
                    <i class="ri-heart-pulse-line me-2"></i>Create Guidance User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createGuidanceForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guidance_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="guidance_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guidance_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="guidance_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guidance_employee_id" class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="guidance_employee_id" name="employee_id" placeholder="e.g., GDN001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guidance_position" class="form-label">Position</label>
                                <select class="form-control" id="guidance_position" name="position">
                                    <option value="">Select Position</option>
                                    <option value="Guidance Counselor">Guidance Counselor</option>
                                    <option value="Senior Guidance Counselor">Senior Guidance Counselor</option>
                                    <option value="Guidance Coordinator">Guidance Coordinator</option>
                                    <option value="School Psychologist">School Psychologist</option>
                                    <option value="Career Counselor">Career Counselor</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guidance_specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="guidance_specialization" name="specialization" placeholder="e.g., Educational Psychology, Career Guidance">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guidance_hire_date" class="form-label">Hire Date</label>
                                <input type="date" class="form-control" id="guidance_hire_date" name="hire_date">
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Default password will be set to <strong>guidance123</strong>. User should change it on first login.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="ri-add-line me-1"></i>Create Guidance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Discipline Modal -->
<div class="modal fade" id="createDisciplineModal" tabindex="-1" aria-labelledby="createDisciplineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="createDisciplineModalLabel">
                    <i class="ri-shield-check-line me-2"></i>Create Discipline User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createDisciplineForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="discipline_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="discipline_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_employee_id" class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="discipline_employee_id" name="employee_id" placeholder="e.g., DSC001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_position" class="form-label">Position</label>
                                <select class="form-control" id="discipline_position" name="position">
                                    <option value="">Select Position</option>
                                    <option value="Discipline Officer">Discipline Officer</option>
                                    <option value="Senior Discipline Officer">Senior Discipline Officer</option>
                                    <option value="Discipline Coordinator">Discipline Coordinator</option>
                                    <option value="Student Affairs Officer">Student Affairs Officer</option>
                                    <option value="Behavior Specialist">Behavior Specialist</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="discipline_specialization" name="specialization" placeholder="e.g., Behavioral Management, Student Discipline">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_hire_date" class="form-label">Hire Date</label>
                                <input type="date" class="form-control" id="discipline_hire_date" name="hire_date">
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Default password will be set to <strong>discipline123</strong>. User should change it on first login.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="ri-add-line me-1"></i>Create Discipline
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewUserModalLabel">
                    <i class="ri-eye-line me-2"></i>User Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewUserContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editUserModalLabel">
                    <i class="ri-edit-line me-2"></i>Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm">
                <div class="modal-body" id="editUserContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="ri-save-line me-1"></i>Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Guidance Counselor Modal -->
<div class="modal fade" id="createGuidanceCounselorModal" tabindex="-1" aria-labelledby="createGuidanceCounselorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="createGuidanceCounselorModalLabel">
                    <i class="ri-heart-pulse-line me-2"></i>Create Guidance Counselor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createGuidanceCounselorForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guidance_counselor_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="guidance_counselor_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guidance_counselor_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="guidance_counselor_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guidance_counselor_employee_id" class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="guidance_counselor_employee_id" name="employee_id" placeholder="e.g., GC001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guidance_counselor_position" class="form-label">Position</label>
                                <select class="form-control" id="guidance_counselor_position" name="position">
                                    <option value="">Select Position</option>
                                    <option value="Guidance Counselor">Guidance Counselor</option>
                                    <option value="Senior Guidance Counselor">Senior Guidance Counselor</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guidance_counselor_specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="guidance_counselor_specialization" name="specialization" placeholder="e.g., Educational Psychology">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guidance_counselor_hire_date" class="form-label">Hire Date</label>
                                <input type="date" class="form-control" id="guidance_counselor_hire_date" name="hire_date">
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Default password will be set to <strong>password123</strong>. User should change it on first login.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="ri-add-line me-1"></i>Create Guidance Counselor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Discipline Head Modal -->
<div class="modal fade" id="createDisciplineHeadModal" tabindex="-1" aria-labelledby="createDisciplineHeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="createDisciplineHeadModalLabel">
                    <i class="ri-shield-star-line me-2"></i>Create Discipline Head
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createDisciplineHeadForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_head_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="discipline_head_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_head_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="discipline_head_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_head_employee_id" class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="discipline_head_employee_id" name="employee_id" placeholder="e.g., DH001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_head_position" class="form-label">Position</label>
                                <select class="form-control" id="discipline_head_position" name="position">
                                    <option value="">Select Position</option>
                                    <option value="Discipline Head">Discipline Head</option>
                                    <option value="Chief Discipline Officer">Chief Discipline Officer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_head_specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="discipline_head_specialization" name="specialization" placeholder="e.g., Behavioral Management">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_head_hire_date" class="form-label">Hire Date</label>
                                <input type="date" class="form-control" id="discipline_head_hire_date" name="hire_date">
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Default password will be set to <strong>password123</strong>. User should change it on first login.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="ri-add-line me-1"></i>Create Discipline Head
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Discipline Officer Modal -->
<div class="modal fade" id="createDisciplineOfficerModal" tabindex="-1" aria-labelledby="createDisciplineOfficerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="createDisciplineOfficerModalLabel">
                    <i class="ri-shield-check-line me-2"></i>Create Discipline Officer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createDisciplineOfficerForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_officer_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="discipline_officer_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_officer_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="discipline_officer_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_officer_employee_id" class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="discipline_officer_employee_id" name="employee_id" placeholder="e.g., DO001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_officer_position" class="form-label">Position</label>
                                <select class="form-control" id="discipline_officer_position" name="position">
                                    <option value="">Select Position</option>
                                    <option value="Discipline Officer">Discipline Officer</option>
                                    <option value="Student Affairs Officer">Student Affairs Officer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_officer_specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="discipline_officer_specialization" name="specialization" placeholder="e.g., Student Discipline">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discipline_officer_hire_date" class="form-label">Hire Date</label>
                                <input type="date" class="form-control" id="discipline_officer_hire_date" name="hire_date">
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Default password will be set to <strong>password123</strong>. User should change it on first login.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary">
                        <i class="ri-add-line me-1"></i>Create Discipline Officer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
