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

<!-- Create Cashier Modal -->
<div class="modal fade" id="createCashierModal" tabindex="-1" aria-labelledby="createCashierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createCashierModalLabel">
                    <i class="ri-money-dollar-circle-line me-2"></i>Create Cashier
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createCashierForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cashier_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="cashier_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cashier_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="cashier_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cashier_employee_id" class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="cashier_employee_id" name="employee_id" placeholder="e.g., CSH001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cashier_position" class="form-label">Position</label>
                                <select class="form-control" id="cashier_position" name="position">
                                    <option value="">Select Position</option>
                                    <option value="Cashier">Cashier</option>
                                    <option value="Senior Cashier">Senior Cashier</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="cashier_hire_date" class="form-label">Hire Date</label>
                        <input type="date" class="form-control" id="cashier_hire_date" name="hire_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-add-line me-1"></i>Create Cashier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Faculty Head Modal -->
<div class="modal fade" id="createFacultyHeadModal" tabindex="-1" aria-labelledby="createFacultyHeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createFacultyHeadModalLabel">
                    <i class="ri-user-star-line me-2"></i>Create Faculty Head
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createFacultyHeadForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="faculty_head_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="faculty_head_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="faculty_head_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="faculty_head_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="faculty_head_employee_id" class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="faculty_head_employee_id" name="employee_id" placeholder="e.g., FH001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="faculty_head_department" class="form-label">Department</label>
                                <select class="form-control" id="faculty_head_department" name="department">
                                    <option value="">Select Department</option>
                                    <option value="Elementary">Elementary</option>
                                    <option value="Junior High School">Junior High School</option>
                                    <option value="Senior High School">Senior High School</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="faculty_head_position" class="form-label">Position</label>
                                <select class="form-control" id="faculty_head_position" name="position">
                                    <option value="">Select Position</option>
                                    <option value="Faculty Head">Faculty Head</option>
                                    <option value="Department Head">Department Head</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="faculty_head_specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="faculty_head_specialization" name="specialization" placeholder="e.g., Educational Leadership">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="faculty_head_hire_date" class="form-label">Hire Date</label>
                        <input type="date" class="form-control" id="faculty_head_hire_date" name="hire_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i>Create Faculty Head
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
