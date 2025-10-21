<x-registrar-layout>
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="section-title">Approved Applications</h1>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" onclick="refreshApproved()">
                    <i class="ri-refresh-line me-1"></i>Refresh
                </button>
                {{-- <button class="btn btn-outline-primary" onclick="exportApproved()">
                    <i class="ri-download-line me-1"></i>Export
                </button> --}}
            </div>
        </div>

        <!-- FILTERS -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Grade Level</label>
                        <select class="form-select" id="grade-filter">
                            <option value="">All Grades</option>
                            <option value="Grade 1">Grade 1</option>
                            <option value="Grade 2">Grade 2</option>
                            <option value="Grade 3">Grade 3</option>
                            <option value="Grade 4">Grade 4</option>
                            <option value="Grade 5">Grade 5</option>
                            <option value="Grade 6">Grade 6</option>
                            <option value="Grade 7">Grade 7</option>
                            <option value="Grade 8">Grade 8</option>
                            <option value="Grade 9">Grade 9</option>
                            <option value="Grade 10">Grade 10</option>
                            <option value="Grade 11">Grade 11</option>
                            <option value="Grade 12">Grade 12</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" id="search-input" placeholder="Search by name, email, or application ID">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button class="btn btn-registrar" onclick="applyApprovedFilters()">
                                <i class="ri-search-line me-1"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- APPROVED APPLICATIONS TABLE -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <h5 class="mb-0">
                    <i class="ri-check-line me-2"></i>
                    Approved Applications
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="approved-table">
                        <thead class="table-dark">
                            <tr>
                                <th>Application ID</th>
                                <th>Name</th>
                                <th>Grade Level</th>
                                <th>Email</th>
                                <th>Approved Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="approved-tbody">
                            @foreach($approved_applications as $application)
                            <tr data-id="{{ $application->id }}">
                                <td>{{ $application->application_id }}</td>
                                <td>{{ $application->first_name }} {{ $application->last_name }}</td>
                                <td>{{ $application->grade_level_applied }}</td>
                                <td>{{ $application->email }}</td>
                                <td>{{ $application->approved_at ? $application->approved_at->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewApprovedApplication({{ $application->id }})" title="View Details">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="generateStudentCredentials({{ $application->id }})" title="Generate Student Credentials">
                                            <i class="ri-key-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PAGINATION -->
        <div class="d-flex justify-content-center mt-4">
            {{ $approved_applications->links() }}
        </div>
    </div>

    <!-- VIEW APPROVED APPLICATION MODAL -->
    <div class="modal fade" id="viewApprovedModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approved Application Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="approved-details">
                        <!-- Application details will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="generate-credentials-btn">
                        <i class="ri-key-line me-1"></i>Generate Student Credentials
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- STUDENT CREDENTIALS MODAL -->
    <div class="modal fade" id="credentialsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Student Credentials Generated</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success">
                        <h6><i class="ri-check-circle-line me-2"></i>Credentials Successfully Sent!</h6>
                        <p class="mb-0">Student portal credentials have been sent to the applicant's email address.</p>
                    </div>
                    <div id="credentials-details">
                        <!-- Credentials details will be shown here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/registrar-approved.js'])
</x-registrar-layout>
