<x-cashier-layout>
    <x-slot name="title">Create New Fee</x-slot>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Create New Fee</h2>
                    <p class="text-muted mb-0">Add a new fee to the school fee structure</p>
                </div>
                <a href="{{ route('cashier.fees') }}" class="btn btn-outline-secondary">
                    <i class="ri-arrow-left-line me-2"></i>Back to Fees
                </a>
            </div>

            <!-- Create Fee Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-add-circle-line me-2"></i>Fee Information
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('cashier.fees.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- Fee Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fee Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name') }}" 
                                       placeholder="e.g., Entrance Fee, Tuition Fee">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount (₱) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" 
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       name="amount" value="{{ old('amount') }}" 
                                       placeholder="0.00">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Educational Level -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Educational Level <span class="text-danger">*</span></label>
                                <select class="form-select @error('educational_level') is-invalid @enderror" 
                                        name="educational_level" id="educational_level">
                                    <option value="">Select Educational Level</option>
                                    <option value="preschool" {{ old('educational_level') === 'preschool' ? 'selected' : '' }}>Preschool</option>
                                    <option value="elementary" {{ old('educational_level') === 'elementary' ? 'selected' : '' }}>Elementary</option>
                                    <option value="junior_high" {{ old('educational_level') === 'junior_high' ? 'selected' : '' }}>Junior High School</option>
                                    <option value="senior_high" {{ old('educational_level') === 'senior_high' ? 'selected' : '' }}>Senior High School</option>
                                </select>
                                @error('educational_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fee Category -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fee Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('fee_category') is-invalid @enderror" 
                                        name="fee_category">
                                    <option value="">Select Category</option>
                                    <option value="entrance" {{ old('fee_category') === 'entrance' ? 'selected' : '' }}>Entrance</option>
                                    <option value="tuition" {{ old('fee_category') === 'tuition' ? 'selected' : '' }}>Tuition</option>
                                    <option value="miscellaneous" {{ old('fee_category') === 'miscellaneous' ? 'selected' : '' }}>Miscellaneous</option>
                                    <option value="laboratory" {{ old('fee_category') === 'laboratory' ? 'selected' : '' }}>Laboratory</option>
                                    <option value="library" {{ old('fee_category') === 'library' ? 'selected' : '' }}>Library</option>
                                    <option value="other" {{ old('fee_category') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('fee_category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Grade Levels -->
                        <div class="mb-3">
                            <label class="form-label">Applicable Grade Levels <span class="text-danger">*</span></label>
                            <div id="grade-levels-container">
                                <!-- Grade levels will be populated by JavaScript -->
                            </div>
                            @error('grade_levels')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Payment Schedule -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Schedule</label>
                                <select class="form-select" name="payment_schedule">
                                    <option value="full_payment" {{ old('payment_schedule') === 'full_payment' ? 'selected' : '' }}>Full Payment</option>
                                    <option value="pay_separate" {{ old('payment_schedule') === 'pay_separate' ? 'selected' : '' }}>Pay Separately</option>
                                    <option value="pay_before_exam" {{ old('payment_schedule') === 'pay_before_exam' ? 'selected' : '' }}>Pay Before Exam</option>
                                    <option value="monthly" {{ old('payment_schedule') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="quarterly" {{ old('payment_schedule') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                </select>
                            </div>

                            <!-- Academic Year -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                @php
                                    $currentYear = now()->year;
                                    $currentMonth = now()->month;
                                    if ($currentMonth >= 1 && $currentMonth <= 5) {
                                        $academicYear = ($currentYear - 1) . '-' . $currentYear;
                                    } else {
                                        $academicYear = $currentYear . '-' . ($currentYear + 1);
                                    }
                                @endphp
                                <input type="text" class="form-control @error('academic_year') is-invalid @enderror" 
                                       name="academic_year" value="{{ old('academic_year', $academicYear) }}" 
                                       placeholder="2025-2026">
                                @error('academic_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      name="description" rows="3" 
                                      placeholder="Optional description of the fee">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" 
                                       id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active (Fee is available for use)
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-2"></i>Create Fee
                            </button>
                            <a href="{{ route('cashier.fees') }}" class="btn btn-outline-secondary">
                                <i class="ri-close-line me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Grade levels by educational level
        const gradeLevels = {
            'preschool': ['Toddler', 'Nursery', 'Junior Casa', 'Kindergarten'],
            'elementary': ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
            'junior_high': ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'],
            'senior_high': ['Grade 11', 'Grade 12']
        };

        // Update grade levels when educational level changes
        document.getElementById('educational_level').addEventListener('change', function() {
            const level = this.value;
            const container = document.getElementById('grade-levels-container');
            
            if (level && gradeLevels[level]) {
                let html = '<div class="row">';
                gradeLevels[level].forEach((grade, index) => {
                    const isChecked = @json(old('grade_levels', [])).includes(grade) ? 'checked' : '';
                    html += `
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="grade_levels[]" 
                                       value="${grade}" id="grade_${index}" ${isChecked}>
                                <label class="form-check-label" for="grade_${index}">
                                    ${grade}
                                </label>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-muted">Please select an educational level first.</p>';
            }
        });

        // Trigger change event on page load if there's an old value
        document.addEventListener('DOMContentLoaded', function() {
            const educationalLevel = document.getElementById('educational_level');
            if (educationalLevel.value) {
                educationalLevel.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endpush
</x-cashier-layout>
