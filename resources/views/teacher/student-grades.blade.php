<div class="student-grades">
    <div class="row mb-3">
        <div class="col-md-6">
            <h6 class="text-primary">Student Information</h6>
            <p class="mb-1"><strong>Name:</strong> {{ $student->first_name }} {{ $student->last_name }}</p>
            <p class="mb-1"><strong>Student ID:</strong> {{ $student->student_id }}</p>
            <p class="mb-1"><strong>Grade Level:</strong> {{ $student->grade_level }}
                @if($student->section) - {{ $student->section }} @endif
                @if($student->strand) - {{ $student->strand }} @endif
                @if($student->track) - {{ $student->track }} @endif
            </p>
        </div>
        <div class="col-md-6">
            <h6 class="text-info">Academic Year</h6>
            <p class="mb-1"><strong>Year:</strong> {{ $currentAcademicYear }}</p>
            <p class="mb-1"><strong>Quarter:</strong> All Quarters</p>
        </div>
    </div>
    
    @if(count($gradesData) > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Subject</th>
                        <th>1st Quarter</th>
                        <th>2nd Quarter</th>
                        <th>3rd Quarter</th>
                        <th>4th Quarter</th>
                        <th>Final Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gradesData as $subjectData)
                        <tr>
                            <td>{{ $subjectData['subject_name'] }}</td>
                            @foreach(['1st', '2nd', '3rd', '4th'] as $quarter)
                                <td class="text-center">
                                    @if($subjectData['quarters'][$quarter] !== null)
                                        {{ number_format($subjectData['quarters'][$quarter], 0) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center text-muted">-</td>
                            <td class="text-center">-</td>
                        </tr>
                    @endforeach
                    <tr class="table-info">
                        <td><strong>Average</strong></td>
                        @foreach(['1st', '2nd', '3rd', '4th'] as $quarter)
                            <td class="text-center">
                                @if($quarterAverages[$quarter] !== null)
                                    <strong>{{ number_format($quarterAverages[$quarter], 1) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-center text-muted">-</td>
                        <td class="text-center">-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            No grades found for this student.
        </div>
    @endif
    
    <div class="mt-3">
        <small class="text-muted">
            <i class="ri-information-line me-1"></i>
            Grades are updated in real-time as teachers submit them.
        </small>
    </div>
</div>
