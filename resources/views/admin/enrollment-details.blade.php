<x-admin-layout>
    <x-slot name="title">Student Details - {{ $student->first_name }} {{ $student->last_name }}</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Student Enrollment Details</h1>
            <a href="{{ route('admin.enrollments') }}" class="btn btn-secondary">
                <i class="ri-arrow-left-line me-1"></i>Back to Enrollments
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Student Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> {{ $student->first_name }} {{ $student->last_name }}</p>
                                <p><strong>Email:</strong> {{ $student->email }}</p>
                                <p><strong>Grade Level:</strong> {{ $student->grade_level }}</p>
                                @if($student->strand)
                                    <p><strong>Strand:</strong> {{ $student->strand }}</p>
                                @endif
                                @if($student->lrn)
                                    <p><strong>LRN:</strong> {{ $student->lrn }}</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p><strong>Guardian:</strong> {{ $student->guardian_name }}</p>
                                <p><strong>Contact:</strong> {{ $student->guardian_contact }}</p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-{{ $student->enrollment_status === 'enrolled' ? 'success' : ($student->enrollment_status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($student->enrollment_status) }}
                                    </span>
                                </p>
                                <p><strong>Applied:</strong> {{ $student->created_at->format('M d, Y H:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                @if($student->id_photo)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Student Photo</h5>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ asset('storage/' . $student->id_photo) }}" alt="Student Photo" class="img-fluid rounded">
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
