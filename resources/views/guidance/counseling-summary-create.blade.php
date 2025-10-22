@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Create Counseling Summary for {{ $counselingSession->student->name ?? 'Student' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('counseling-sessions.summary', $counselingSession->id) }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="session_summary">Session Summary</label>
                            <textarea name="session_summary" id="session_summary" class="form-control" required>{{ old('session_summary') }}</textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label for="student_progress">Student Progress</label>
                            <textarea name="student_progress" id="student_progress" class="form-control">{{ old('student_progress') }}</textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label for="goals_achieved">Goals Achieved</label>
                            <textarea name="goals_achieved" id="goals_achieved" class="form-control">{{ old('goals_achieved') }}</textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label for="next_steps">Next Steps</label>
                            <textarea name="next_steps" id="next_steps" class="form-control">{{ old('next_steps') }}</textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label for="follow_up_required">
                                <input type="checkbox" name="follow_up_required" id="follow_up_required" value="1" {{ old('follow_up_required') ? 'checked' : '' }}>
                                Follow-up Required
                            </label>
                        </div>
                        <div class="form-group mb-3">
                            <label for="follow_up_date">Follow-up Date</label>
                            <input type="date" name="follow_up_date" id="follow_up_date" class="form-control" value="{{ old('follow_up_date') }}">
                        </div>
                        <button type="submit" class="btn btn-success">Submit Summary</button>
                        <a href="{{ route('counseling-sessions.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
