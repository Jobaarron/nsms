<x-faculty-head-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Assign Teacher per Subject/Section</h1>
      <div class="text-muted">
        <i class="ri-user-settings-line me-1"></i>{{ $currentAcademicYear }}
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <!-- ASSIGN NEW TEACHER -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-add-circle-line me-2"></i>Assign Teacher to Subject/Section
        </h5>
      </div>
      <div class="card-body">
        <form action="{{ route('faculty-head.assign-teacher.store') }}" method="POST" class="row g-3">
          @csrf
          <div class="col-md-3">
            <label class="form-label">Teacher</label>
            <select name="teacher_id" class="form-select" required>
              <option value="">Select Teacher</option>
              @foreach($teachers as $teacher)
                <option value="{{ $teacher->teacher->id ?? '' }}">{{ $teacher->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Subject</label>
            <select name="subject_id" class="form-select" required>
              <option value="">Select Subject</option>
              @foreach($subjects as $subject)
                <option value="{{ $subject->id }}">{{ $subject->subject_name }} ({{ $subject->grade_level }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Grade Level</label>
            <select name="grade_level" class="form-select" required>
              <option value="">Select Grade</option>
              @foreach($subjects->pluck('grade_level')->unique() as $grade)
                <option value="{{ $grade }}">{{ $grade }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Section</label>
            <input type="text" name="section" class="form-control" placeholder="e.g., A, B, C" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Effective Date</label>
            <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-d') }}" required>
          </div>
          <div class="col-12">
            <label class="form-label">Notes (Optional)</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes about this assignment"></textarea>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">
              <i class="ri-user-add-line me-2"></i>Assign Teacher
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- CURRENT TEACHER ASSIGNMENTS -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-user-settings-line me-2"></i>Current Teacher Assignments
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Teacher</th>
                <th>Subject</th>
                <th>Class</th>
                <th>Type</th>
                <th>Assigned Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($assignments as $assignment)
              <tr>
                <td>{{ $assignment->teacher->name }}</td>
                <td>{{ $assignment->subject->subject_name ?? 'N/A' }}</td>
                <td>{{ $assignment->grade_level }} - {{ $assignment->section }}</td>
                <td>
                  @if($assignment->isClassAdviser())
                    <span class="badge bg-primary">Class Adviser</span>
                  @else
                    <span class="badge bg-secondary">Subject Teacher</span>
                  @endif
                </td>
                <td>{{ $assignment->assigned_date->format('M d, Y') }}</td>
                <td>
                  @if($assignment->status === 'active')
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-secondary">Inactive</span>
                  @endif
                </td>
                <td>
                  @if($assignment->status === 'active')
                    <button class="btn btn-sm btn-outline-danger" onclick="removeAssignment({{ $assignment->id }})">
                      <i class="ri-user-unfollow-line me-1"></i>Remove
                    </button>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No teacher assignments found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</x-faculty-head-layout>

@push('scripts')
<script src="{{ asset('js/faculty-head-assign-teacher.js') }}"></script>
@endpush
