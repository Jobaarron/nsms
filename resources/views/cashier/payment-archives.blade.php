<x-cashier-layout>
    @push('styles')
        @vite('resources/css/index_student.css')
    @endpush
    @vite(['resources/js/cashier-payment-schedules.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="section-title mb-1">Payment Archives</h2>
                    <p class="text-muted mb-0">Complete history of all confirmed and completed payments</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-success fs-6" id="total-count">0 Total</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <ul class="nav nav-tabs card-header-tabs" id="archiveTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-payments" type="button" role="tab">
                                <i class="ri-file-list-line me-2"></i>All Payments
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed-payments" type="button" role="tab">
                                <i class="ri-check-double-line me-2"></i>Completed
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#payment-history" type="button" role="tab">
                                <i class="ri-history-line me-2"></i>History
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <!-- Filters Row -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select payment-filter" name="payment_method">
                                <option value="">All Payment Methods</option>
                                <option value="full">Full Payment</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select payment-filter" name="status">
                                <option value="">All Status</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="payment-search" placeholder="Search by Student ID, Name, Transaction ID...">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-success w-100" onclick="exportArchives()">
                                <i class="ri-download-line me-2"></i>Export
                            </button>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content" id="archiveTabContent">
                        <!-- All Payments Tab -->
                        <div class="tab-pane fade show active" id="all-payments" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover" id="all-payments-table">
                                    <thead>
                                        <tr>
                                            <th>Priority</th>
                                            <th>Transaction ID</th>
                                            <th>Student Info</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="text-muted mt-2">Loading payment archives...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Completed Payments Tab -->
                        <div class="tab-pane fade" id="completed-payments" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover" id="completed-payments-table">
                                    <thead>
                                        <tr>
                                            <th>Priority</th>
                                            <th>Transaction ID</th>
                                            <th>Student Info</th>
                                            <th>Amount</th>
                                            <th>Confirmed Date</th>
                                            <th>Processed By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Content loaded via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Payment History Tab -->
                        <div class="tab-pane fade" id="payment-history" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover" id="payment-history-table">
                                    <thead>
                                        <tr>
                                            <th>Priority</th>
                                            <th>Transaction ID</th>
                                            <th>Student Info</th>
                                            <th>Amount</th>
                                            <th>Confirmed Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Content loaded via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination-container" class="d-flex justify-content-center mt-4"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Details Modal -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="paymentDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="printReceiptBtn" onclick="printReceiptFromModal()">
                        <i class="ri-printer-line me-2"></i>Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-cashier-layout>
