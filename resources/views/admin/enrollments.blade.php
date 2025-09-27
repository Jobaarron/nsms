<x-admin-layout>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @vite(['resources/js/admin-enrollment-management.js'])
    @vite(['resources/css/admin-enrollment-management.css'])
    
    @include('admin.enrollment-modals')
    
    <x-slot name="title">Enrollment Management</x-slot>

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="ri-graduation-cap-line me-2"></i>
                Enrollment Management
            </h1>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="refreshData()">
                    <i class="ri-refresh-line me-1"></i>Refresh
                </button>
                <button class="btn btn-success" onclick="exportData()">
                    <i class="ri-download-line me-1"></i>Export
                </button>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alert-container"></div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ri-file-list-line fs-2 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4" id="total-count">0</div>
                                <div class="text-muted small">Total Applications</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ri-time-line fs-2 text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4" id="pending-count">0</div>
                                <div class="text-muted small">Pending Review</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ri-check-line fs-2 text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4" id="approved-count">0</div>
                                <div class="text-muted small">Approved</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ri-calendar-check-line fs-2 text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4" id="appointments-count">0</div>
                                <div class="text-muted small">Pending Appointments</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="enrollmentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="applications-tab" data-bs-toggle="tab" data-bs-target="#applications" type="button" role="tab">
                    <i class="ri-file-list-line me-2"></i>Applications
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                    <i class="ri-folder-line me-2"></i>Document Review
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab">
                    <i class="ri-calendar-line me-2"></i>Appointments
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="notices-tab" data-bs-toggle="tab" data-bs-target="#notices" type="button" role="tab">
                    <i class="ri-notification-line me-2"></i>Notices
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="enrollmentTabContent">
            
            <!-- Applications Tab -->
            <div class="tab-pane fade show active" id="applications" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="ri-file-list-line me-2"></i>
                            Enrollment Applications
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select class="form-select" id="status-filter">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="enrolled">Enrolled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="grade-filter">
                                    <option value="">All Grades</option>
                                    <option value="Nursery">Nursery</option>
                                    <option value="Junior Casa">Junior Casa</option>
                                    <option value="Senior Casa">Senior Casa</option>
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
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="search-input" placeholder="Search by name, email, or application ID...">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                    <i class="ri-close-line me-1"></i>Clear
                                </button>
                            </div>
                        </div>

                        <!-- Applications Table -->
                        <div class="table-responsive">
                            <table class="table table-hover" id="applications-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" id="select-all" class="form-check-input">
                                        </th>
                                        <th>Application ID</th>
                                        <th>Student Name</th>
                                        <th>Grade Level</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Applied Date</th>
                                        <th style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Bulk Actions Panel -->
                        <div class="card mt-3" id="bulk-actions-panel" style="display: none;">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-0">
                                            <i class="ri-checkbox-multiple-line me-2"></i>
                                            <span id="selectedCount">0</span> applications selected
                                        </h6>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <div class="btn-group flex-wrap" role="group">
                                            <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()">
                                                <i class="ri-check-line me-1"></i>Approve
                                            </button>
                                            
                                            <button type="button" class="btn btn-warning btn-sm" onclick="bulkDecline()">
                                                <i class="ri-close-line me-1"></i>Decline
                                            </button>
                                            
                                            <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                                                <i class="ri-delete-bin-line me-1"></i>Delete
                                            </button>
                                            
                                            <button type="button" class="btn btn-info btn-sm" onclick="exportSelected()">
                                                <i class="ri-download-line me-1"></i>Export
                                            </button>
                                            
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="clearAllSelections()">
                                                <i class="ri-close-circle-line me-1"></i>Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Review Tab -->
            <div class="tab-pane fade" id="documents" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="ri-folder-line me-2"></i>
                            Document Review & Verification
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select class="form-select" id="doc-status-filter">
                                    <option value="">All Documents</option>
                                    <option value="pending">Pending Review</option>
                                    <option value="verified">Verified</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="doc-type-filter">
                                    <option value="">All Types</option>
                                    <option value="birth_certificate">Birth Certificate</option>
                                    <option value="form_137">Form 137</option>
                                    <option value="good_moral">Good Moral</option>
                                    <option value="id_photo">ID Photo</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover" id="documents-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Application ID</th>
                                        <th>Student Name</th>
                                        <th>Document Type</th>
                                        <th>Upload Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointments Tab -->
            <div class="tab-pane fade" id="appointments" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="ri-calendar-line me-2"></i>
                            Schedule Appointments
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select class="form-select" id="appointment-status-filter">
                                    <option value="">All Appointments</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" class="form-control" id="appointment-date-filter">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover" id="appointments-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Application ID</th>
                                        <th>Student Name</th>
                                        <th>Requested Date</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notices Tab -->
            <div class="tab-pane fade" id="notices" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="ri-notification-line me-2"></i>
                            Send Notices to Applicants
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <button class="btn btn-primary" onclick="openCreateNoticeModal()">
                                    <i class="ri-add-line me-1"></i>Create Notice
                                </button>
                                <button class="btn btn-outline-info ms-2" onclick="openBulkNoticeModal()">
                                    <i class="ri-mail-send-line me-1"></i>Bulk Notice
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover" id="notices-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Notice Title</th>
                                        <th>Recipients</th>
                                        <th>Priority</th>
                                        <th>Sent Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Modals -->
    @include('admin.enrollment-modals')
</x-admin-layout>
