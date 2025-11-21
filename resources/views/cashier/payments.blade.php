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
                    <h2 class="section-title mb-1">Payments</h2>
                    <p class="text-muted mb-0">Review and confirm pending payment submissions</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-warning fs-6" id="pending-count">{{ $payments->total() }} Pending</span>
                    <span class="badge bg-danger fs-6 ms-2" id="due-count" style="display: none;">0 Due</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Payment Schedules -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-calendar-schedule-line me-2"></i>Student Payment Schedules
                        </h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm payment-filter" name="status">
                                <option value="">All Status</option>
                                <option value="pending">Not yet paid</option>
                                <option value="confirmed">Paid</option>
                            </select>
                            <select class="form-select form-select-sm payment-filter" name="due_status">
                                <option value="">All Payments</option>
                                <option value="due">Due Payments</option>
                                <option value="not_due">Not Due</option>
                            </select>
                            <input type="text" class="form-control form-control-sm" id="payment-search" placeholder="Student ID">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="payment-schedules-table">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Student ID</th>
                                    <th>Amount</th>
                                    <th>Scheduled Date</th>
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
                                        <p class="text-muted mt-2">Loading payment schedules...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="pagination-container"></div>
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
                    {{-- <button type="button" class="btn btn-success" onclick="confirmFromModal()">
                        <i class="ri-check-line me-2"></i>Confirm Payment
                    </button>
                    <button type="button" class="btn btn-danger" onclick="rejectFromModal()">
                        <i class="ri-close-line me-2"></i>Reject Payment
                    </button> --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Payment Modal -->
    <div class="modal fade" id="confirmPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to confirm this payment?</p>
                    <div class="mb-3">
                        <label for="confirmNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="confirmNotes" rows="3" 
                                  placeholder="Add any notes about this confirmation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="processConfirmationModal()">
                        <i class="ri-check-line me-2"></i>Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-cashier-layout>
