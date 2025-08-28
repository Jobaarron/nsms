<!DOCTYPE html>
<html>
<head>
    <title>Student Enrollments Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        .status-pending { color: #856404; }
        .status-enrolled { color: #155724; }
        .status-rejected { color: #721c24; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Enrollments Report</h1>
        <p>Generated on: {{ date('F d, Y H:i A') }}</p>
        <p>Total Records: {{ count($students) }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Grade Level</th>
                <th>Guardian</th>
                <th>Contact</th>
                <th>Status</th>
                <th>Applied Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                <tr>
                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->grade_level }}</td>
                    <td>{{ $student->guardian_name }}</td>
                    <td>{{ $student->guardian_contact }}</td>
                    <td class="status-{{ $student->enrollment_status }}">
                        {{ ucfirst($student->enrollment_status) }}
                    </td>
                    <td>{{ $student->created_at->format('M d, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Print Report</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
