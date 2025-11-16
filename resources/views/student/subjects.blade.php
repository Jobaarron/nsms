<x-student-layout>
    @vite(['resources/js/student-subjects.js'])
    @push('styles')
        @vite('resources/css/index_student.css')
    @endpush

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="section-title mb-1">My Subjects</h2>
                    <p class="text-muted mb-0">View your subjects and academic information</p>
                </div>
                <div class="text-end">
                    <small class="text-muted">Student ID: <strong>{{ $student->student_id }}</strong></small><br>
                    <small class="text-muted">Grade Level: <strong>{{ $student->grade_level }}</strong></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Academic Info Summary -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm card-summary card-subjects h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="ri-book-line fs-2"></i>
                    </div>
                    <div class="flex-grow-1">
                        @php
                            // Use subjects filtered by controller (includes semester filtering for SHS)
                            $subjects = $currentSubjects;
                        @endphp
                        <h3 class="fw-bold fs-4 mb-0 text-white">{{ $subjects->count() }}</h3>
                        <small class="text-white">Total Subjects</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm card-summary card-paid h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="ri-star-line fs-2"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="fw-bold fs-4 mb-0 text-white">{{ $subjects->where('category', 'core')->count() }}</h3>
                        <small class="text-white">Core Subjects</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm card-summary card-credits h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="ri-focus-line fs-2"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="fw-bold fs-4 mb-0 text-white">{{ $subjects->where('category', 'specialized')->count() }}</h3>
                        <small class="text-white">Specialized</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm card-summary card-gpa h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="ri-graduation-cap-line fs-2"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="fw-bold fs-4 mb-0 text-white">{{ $student->grade_level }}</h3>
                        <small class="text-white">Grade Level</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Subjects List -->
        <div class="col-lg-8">
                <!-- Subjects Table -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-book-line me-2"></i>My Subjects
                                @if(in_array($student->grade_level, ['Grade 11', 'Grade 12']))
                                    <small class="text-muted d-block">{{ $currentSemester }} - {{ $student->strand }}{{ $student->track ? ' (' . $student->track . ')' : '' }}</small>
                                @else
                                    <small class="text-muted d-block">{{ $currentGradingPeriod }}</small>
                                @endif
                            </h5>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm active" data-filter="all">All</button>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-filter="core">Core</button>
                                @if($student->strand)
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-filter="specialized">Specialized</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($subjects->count() > 0)
                            <div class="alert alert-info mb-3">
                                <i class="ri-information-line me-2"></i>
                                <strong>Academic Year {{ $student->academic_year ?? '2024-2025' }}</strong>
                                @if(in_array($student->grade_level, ['Grade 11', 'Grade 12']))
                                    <br><small>Showing only {{ $currentSemester }} subjects that you will take this academic year</small>
                                @else
                                    <br><small>Showing all subjects you will take this academic year</small>
                                @endif
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="subjectsTable">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Subject Name</th>
                                            @if(in_array($student->grade_level, ['Grade 11', 'Grade 12']))
                                                <th>Semester</th>
                                                <th>Type</th>
                                            @endif
                                            <th>Academic Year</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subjects as $subject)
                                            <tr class="subject-row" data-type="{{ $subject->category ?? 'core' }}">
                                                <td class="fw-semibold">
                                                    <span class="badge bg-{{ ($subject->category ?? 'core') === 'core' ? 'secondary' : 'primary' }}">
                                                        {{ ucfirst($subject->category ?? 'core') }}
                                                    </span>
                                                </td>
                                                <td class="fw-medium">{{ $subject->subject_name }}</td>
                                                @if(in_array($student->grade_level, ['Grade 11', 'Grade 12']))
                                                    <td>
                                                        <span class="badge bg-success">{{ $subject->semester ?? 'All Year' }}</span>
                                                    </td>
                                                    <td>
                                                        @if($subject->strand)
                                                            <span class="badge bg-primary">{{ $subject->strand }}</span>
                                                            @if($subject->track)
                                                                <span class="badge bg-info">{{ $subject->track }}</span>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-secondary">Core Subject</span>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td>{{ $subject->academic_year ?? '2024-2025' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="ri-book-line fs-1 text-muted mb-3"></i>
                                <h6 class="text-muted">No subjects found</h6>
                                <p class="text-muted small">No subjects are configured for your grade level. Please contact the registrar for assistance.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Subject Categories -->
                @if($subjects->count() > 0)
                    <div class="row">
                        <!-- Core Subjects -->
                        @if($subjects->where('category', 'core')->count() > 0)
                            <div class="col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-light border-0">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-star-line me-2 text-warning"></i>Core Subjects
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="list-group list-group-flush">
                                            @foreach($subjects->where('category', 'core') as $subject)
                                                <div class="list-group-item border-0 px-0">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-1">{{ $subject->subject_name }}</h6>
                                                            <small class="text-muted">{{ $subject->semester ?? 'All Year' }}</small>
                                                        </div>
                                                        <span class="badge bg-secondary">Core</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Specialized Subjects -->
                        @if($subjects->where('category', 'specialized')->count() > 0)
                            <div class="col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-light border-0">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-focus-line me-2 text-primary"></i>Specialized Subjects
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="list-group list-group-flush">
                                            @foreach($subjects->where('category', 'specialized') as $subject)
                                                <div class="list-group-item border-0 px-0">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-1">{{ $subject->subject_name }}</h6>
                                                            <small class="text-muted">{{ $subject->semester ?? 'All Year' }}</small>
                                                        </div>
                                                        <div>
                                                            <span class="badge bg-primary">{{ $subject->strand }}</span>
                                                            @if($subject->track)
                                                                <span class="badge bg-info">{{ $subject->track }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Right Column - Academic Information -->
            <div class="col-lg-4">
                <!-- Academic Details -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h6 class="card-title mb-0">Academic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Student Name</label>
                            <div class="fw-semibold">{{ $student->full_name ?? ($student->first_name . ' ' . $student->last_name) }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Student ID</label>
                            <div class="fw-semibold">{{ $student->student_id }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Grade Level</label>
                            <div class="fw-semibold">{{ $student->grade_level }}</div>
                        </div>
                        @if($student->strand)
                            <div class="mb-3">
                                <label class="form-label text-muted small">Strand</label>
                                <div class="fw-semibold">{{ $student->strand }}</div>
                            </div>
                        @endif
                        @if($student->track)
                            <div class="mb-3">
                                <label class="form-label text-muted small">Track</label>
                                <div class="fw-semibold">{{ $student->track }}</div>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label text-muted small">Class</label>
                            <div class="fw-semibold">
                                @if($student->section)
                                    @php
                                        $classInfo = $student->grade_level . ' - ' . $student->section;
                                        if ($student->strand) {
                                            $classInfo = $student->grade_level . ' - ' . $student->section . ' - ' . $student->strand;
                                            if ($student->track) {
                                                $classInfo = $student->grade_level . ' - ' . $student->section . ' - ' . $student->strand . ' - ' . $student->track;
                                            }
                                        }
                                    @endphp
                                    {{ $classInfo }}
                                @else
                                    To be assigned
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Academic Year</label>
                            <div class="fw-semibold">{{ $student->academic_year ?? '2024-2025' }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Student Type</label>
                            <div class="fw-semibold text-capitalize">{{ $student->student_type ?? 'New' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Curriculum Guide -->
                {{-- @if($student->grade_level === 'Grade 11' || $student->grade_level === 'Grade 12')
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="card-title mb-0">
                                <i class="ri-information-line me-2"></i>Curriculum Guide
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($student->strand === 'ABM')
                                <h6 class="text-primary">Accountancy, Business & Management</h6>
                                <p class="small text-muted mb-2">This strand prepares students for business and accounting careers.</p>
                                <ul class="small text-muted">
                                    <li>Business Finance</li>
                                    <li>Fundamentals of ABM</li>
                                    <li>Applied Economics</li>
                                    <li>Business Ethics</li>
                                </ul>
                            @elseif($student->strand === 'STEM')
                                <h6 class="text-primary">Science, Technology, Engineering & Mathematics</h6>
                                <p class="small text-muted mb-2">This strand focuses on science and mathematics for future STEM careers.</p>
                                <ul class="small text-muted">
                                    <li>Pre-Calculus & Calculus</li>
                                    <li>General Chemistry & Physics</li>
                                    <li>General Biology</li>
                                    <li>Research Projects</li>
                                </ul>
                            @elseif($student->strand === 'HUMSS')
                                <h6 class="text-primary">Humanities & Social Sciences</h6>
                                <p class="small text-muted mb-2">This strand develops critical thinking and communication skills.</p>
                                <ul class="small text-muted">
                                    <li>Creative Writing</li>
                                    <li>Philippine Politics</li>
                                    <li>World Religions</li>
                                    <li>Social Sciences</li>
                                </ul>
                            @elseif($student->strand === 'TVL')
                                <h6 class="text-primary">Technical-Vocational-Livelihood</h6>
                                <p class="small text-muted mb-2">This strand provides technical and vocational skills.</p>
                                @if($student->track === 'HE')
                                    <ul class="small text-muted">
                                        <li>Food & Beverage Services</li>
                                        <li>Bread & Pastry Production</li>
                                        <li>Cookery NC II</li>
                                        <li>Work Immersion</li>
                                    </ul>
                                @elseif($student->track === 'ICT')
                                    <ul class="small text-muted">
                                        <li>Computer Systems Servicing</li>
                                        <li>Programming</li>
                                        <li>Web Development</li>
                                        <li>Work Immersion</li>
                                    </ul>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif --}}

                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h6 class="card-title mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary">
                                <i class="ri-dashboard-line me-2"></i>Back to Dashboard
                            </a>
                            @if($student->enrollment_status === 'pre_registered')
                                <a href="{{ route('student.enrollment') }}" class="btn btn-primary">
                                    <i class="ri-user-add-line me-2"></i>Complete Enrollment
                                </a>
                            @endif
                            <a href="{{ route('student.payments') }}" class="btn btn-outline-success">
                                <i class="ri-bill-line me-2"></i>View Payments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Filter functionality
                const filterButtons = document.querySelectorAll('[data-filter]');
                const subjectRows = document.querySelectorAll('.subject-row');
                
                filterButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const filter = this.getAttribute('data-filter');
                        
                        // Update active button
                        filterButtons.forEach(btn => btn.classList.remove('active'));
                        this.classList.add('active');
                        
                        // Filter rows
                        subjectRows.forEach(row => {
                            const rowType = row.getAttribute('data-type');
                            
                            if (filter === 'all' || filter === rowType) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
</x-student-layout>
