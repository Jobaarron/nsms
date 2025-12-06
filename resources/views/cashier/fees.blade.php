<x-cashier-layout>
    <x-slot name="title">Fee Management</x-slot>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Fee Management</h2>
                    <p class="text-muted mb-0">Manage school fees and pricing for different grade levels</p>
                </div>
                <a href="{{ route('cashier.fees.create') }}" class="btn btn-primary">
                    <i class="ri-add-line me-2"></i>Create New Fee
                </a>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-check-circle-line me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i>{{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Fee Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                                        <i class="ri-money-dollar-circle-line fs-4 text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="fw-bold fs-4 mb-0">{{ $fees->total() }}</h3>
                                    <small class="text-muted">Total Fees</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-success bg-opacity-10 p-3 rounded">
                                        <i class="ri-check-double-line fs-4 text-success"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="fw-bold fs-4 mb-0">{{ $fees->where('is_active', true)->count() }}</h3>
                                    <small class="text-muted">Active Fees</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                                        <i class="ri-pause-circle-line fs-4 text-warning"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="fw-bold fs-4 mb-0">{{ $fees->where('is_active', false)->count() }}</h3>
                                    <small class="text-muted">Inactive Fees</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-info bg-opacity-10 p-3 rounded">
                                        <i class="ri-calendar-line fs-4 text-info"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    @php
                                        $currentYear = now()->year;
                                        $currentMonth = now()->month;
                                        if ($currentMonth >= 1 && $currentMonth <= 5) {
                                            $academicYear = ($currentYear - 1) . '-' . $currentYear;
                                        } else {
                                            $academicYear = $currentYear . '-' . ($currentYear + 1);
                                        }
                                    @endphp
                                    <h3 class="fw-bold fs-4 mb-0">{{ $academicYear }}</h3>
                                    <small class="text-muted">Current Academic Year</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fees Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-list-check-line me-2"></i>School Fees
                    </h5>
                </div>
                <div class="card-body">
                    @if($fees->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fee Name</th>
                                        <th>Grade Levels</th>
                                        <th>Amount</th>
                                        <th>Category</th>
                                        <th>Academic Year</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fees as $fee)
                                        <tr>
                                            <td class="fw-semibold">
                                                {{ $fee->name }}
                                                @if($fee->name === 'PCA Fee')
                                                    <span class="badge bg-warning text-dark ms-1">SHS Only</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($fee->applicable_grades)
                                                    @php
                                                        $grades = is_array($fee->applicable_grades) ? $fee->applicable_grades : json_decode($fee->applicable_grades, true);
                                                    @endphp
                                                    @if($grades && count($grades) > 0)
                                                        @foreach($grades as $grade)
                                                            <span class="badge bg-light text-dark me-1">{{ $grade }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">All Grades</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">All Grades</span>
                                                @endif
                                            </td>
                                            <td class="fw-bold {{ $fee->amount < 0 ? 'text-success' : 'text-primary' }}">
                                                {{ $fee->amount < 0 ? '-' : '' }}₱{{ number_format(abs($fee->amount), 2) }}
                                                @if($fee->amount < 0)
                                                    <small class="text-muted d-block">Discount</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $fee->fee_category === 'entrance' ? 'primary' : ($fee->fee_category === 'tuition' ? 'success' : ($fee->fee_category === 'miscellaneous' ? 'info' : 'secondary')) }}">
                                                    {{ ucfirst($fee->fee_category) }}
                                                </span>
                                                <br>
                                                <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $fee->educational_level)) }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $fee->academic_year }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $fee->is_active ? 'success' : 'secondary' }}">
                                                    {{ $fee->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('cashier.fees.edit', $fee) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Edit Fee">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-{{ $fee->is_active ? 'warning' : 'success' }}" 
                                                            onclick="toggleFeeStatus({{ $fee->id }})"
                                                            title="{{ $fee->is_active ? 'Deactivate' : 'Activate' }} Fee">
                                                        <i class="ri-{{ $fee->is_active ? 'pause' : 'play' }}-circle-line"></i>
                                                    </button>
                                                    <form action="{{ route('cashier.fees.destroy', $fee) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this fee? This action cannot be undone.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="Delete Fee">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($fees->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $fees->links('pagination.custom') }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="ri-money-dollar-circle-line fs-1 text-muted mb-3"></i>
                            <h6 class="text-muted">No fees found</h6>
                            <p class="text-muted small">Create your first fee to get started with fee management.</p>
                            <a href="{{ route('cashier.fees.create') }}" class="btn btn-primary">
                                <i class="ri-add-line me-2"></i>Create First Fee
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/cashier-fees.js'])
@endpush
</x-cashier-layout>
