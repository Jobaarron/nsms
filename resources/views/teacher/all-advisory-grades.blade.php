<div class="all-advisory-grades">
    <style>
        .all-advisory-grades .table th,
        .all-advisory-grades .table td {
            font-size: 0.8rem;
            padding: 6px 4px;
            vertical-align: middle;
        }
        .all-advisory-grades .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .all-advisory-grades .table thead th {
            font-size: 0.7rem;
            line-height: 1.2;
            height: auto;
            max-height: 60px;
            overflow: hidden;
        }
        .all-advisory-grades .table tbody tr:hover {
            background-color: #f5f5f5;
        }
        .all-advisory-grades .table-responsive {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }
    </style>
    
    <div class="row mb-3">
        <div class="col-md-8">
            <h6 class="text-primary">Advisory Class: {{ $className }}</h6>
            <p class="mb-0">Academic Year: {{ $currentAcademicYear }} | 1st Quarter</p>
        </div>
        <div class="col-md-4 text-end">
            <span class="badge bg-info">{{ $totalStudents }} Students</span>
        </div>
    </div>
    
    @if(count($studentsData) > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-sm" style="min-width: 1600px;">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" style="min-width: 180px; white-space: nowrap;">Student Name</th>
                        <th rowspan="2" style="min-width: 100px; white-space: nowrap;">Student ID</th>
                        <th colspan="{{ count($subjects) }}" class="text-center">Subjects (1st Quarter)</th>
                        <th rowspan="2" style="min-width: 80px; white-space: nowrap;">Average</th>
                        <th rowspan="2" style="min-width: 80px; white-space: nowrap;">Ranking</th>
                    </tr>
                    <tr>
                        @foreach($subjects as $subject)
                            <th style="min-width: 120px; font-size: 0.7rem; text-align: center; padding: 6px 3px; line-height: 1.1; word-wrap: break-word; white-space: normal;">
                                {{ $subject->subject_name }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($studentsData as $studentData)
                        <tr>
                            <td>{{ $studentData['student']->last_name }}, {{ $studentData['student']->first_name }}</td>
                            <td>{{ $studentData['student']->student_id }}</td>
                            @foreach($subjects as $subject)
                                <td class="text-center">
                                    @if(isset($studentData['grades'][$subject->id]) && $studentData['grades'][$subject->id] !== null)
                                        {{ number_format($studentData['grades'][$subject->id], 0) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center">
                                @if($studentData['average'] !== null)
                                    <strong>{{ number_format($studentData['average'], 1) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($studentData['ranking'] !== null)
                                    {{ $studentData['ranking'] }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h6 class="text-success">Class Average</h6>
                        <h4 class="text-success">{{ number_format($classAverage, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <h6 class="text-info">Highest Grade</h6>
                        <h4 class="text-info">{{ number_format($highestGrade, 1) }}</h4>
                        @if($topStudent)
                            <small>{{ $topStudent['student']->first_name }} {{ $topStudent['student']->last_name }}</small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <h6 class="text-warning">Students Graded</h6>
                        <h4 class="text-warning">{{ $gradedStudents }}/{{ $totalStudents }}</h4>
                        <small>{{ $totalStudents > 0 ? round(($gradedStudents / $totalStudents) * 100) : 0 }}% Complete</small>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            No students found in your advisory class.
        </div>
    @endif
    
    <div class="mt-3">
        <small class="text-muted">
            <i class="ri-information-line me-1"></i>
            This shows only students with submitted grades for the current quarter.
        </small>
    </div>
</div>
