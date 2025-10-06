<x-admin-layout>
<div class="container mt-4">
    <h1>Forwarded Case Meetings</h1>

    @if($caseMeetings->count() > 0)
    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>Student</th>
                <th>Meeting Type</th>
                <th>Scheduled Date</th>
                <th>Scheduled Time</th>
                <th>Location</th>
                <th>Status</th>
                <th>Sanctions</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($caseMeetings as $meeting)
            <tr>
                <td>{{ $meeting->student ? $meeting->student->full_name : 'Unknown' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $meeting->meeting_type)) }}</td>
                <td>{{ $meeting->scheduled_date->format('Y-m-d') }}</td>
                <td>{{ $meeting->scheduled_time->format('H:i') }}</td>
                <td>{{ $meeting->location }}</td>
                <td><span class="badge bg-warning">{{ ucfirst($meeting->status) }}</span></td>
                <td>
                    <ul>
                        @foreach($meeting->sanctions as $sanction)
                        <li>
                            {{ $sanction->sanction }}
                            @if($sanction->is_approved)
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    @foreach($meeting->sanctions as $sanction)
                        @if(!$sanction->is_approved)
                        <button class="btn btn-sm btn-success approve-sanction-btn" data-sanction-id="{{ $sanction->id }}">
                            Approve Sanction
                        </button>
                        @endif
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $caseMeetings->links() }}

    @else
    <p>No forwarded case meetings found.</p>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.approve-sanction-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function () {
            const sanctionId = this.getAttribute('data-sanction-id');
            if (confirm('Are you sure you want to approve this sanction? This will mark the case meeting as completed.')) {
                fetch(`/admin/sanctions/${sanctionId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('An error occurred while approving the sanction.');
                    console.error(error);
                });
            }
        });
    });
});
</script>
</x-admin-layout>
