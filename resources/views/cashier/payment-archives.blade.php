<x-cashier-layout>
    @push('styles')
        @vite('resources/css/index_student.css')
    @endpush
    @vite(['resources/js/cashier-payment-archives.js'])
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

    <!-- Unified Payment Archives -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-archive-line me-2"></i>All Payment Archives
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filters Row -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select class="form-select payment-filter" name="payment_method">
                                <option value="">All Payment Methods</option>
                                <option value="full">Full Payment</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="payment-search" placeholder="Search by Student ID, Name, Transaction ID...">
                        </div>
                    </div>

                    <!-- Unified Payment Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="payment-archives-table">
                            <thead>
                                <tr>
                                    <th>Priority</th>
                                    <th>Transaction ID</th>
                                    <th>Student Info</th>
                                    <th>Amount</th>
                                    <th>Date Confirmed</th>
                                    <th>Processed By</th>
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
                    <!-- <button type="button" class="btn btn-success" id="printReceiptBtn" onclick="printReceiptFromModal()">
                        <i class="ri-printer-line me-2"></i>Print Receipt
                    </button> -->
                </div>
            </div>
        </div>
    </div>
</x-cashier-layout>
