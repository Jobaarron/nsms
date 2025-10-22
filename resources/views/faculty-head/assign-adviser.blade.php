<x-faculty-head-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Assign Adviser per Class</h1>
      <div class="text-muted">
        <i class="ri-user-star-line me-1"></i>{{ $currentAcademicYear }}
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

    <!-- ASSIGN NEW ADVISER -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-add-circle-line me-2"></i>Assign New Class Adviser
        </h5>
      </div>
      <div class="card-body">
        <form action="{{ route('faculty-head.assign-adviser.store') }}" method="POST" class="row g-3">
          @csrf
          <div class="col-md-4">
            <label class="form-label">Teacher</label>
            <select name="teacher_id" class="form-select" required>
              <option value="">Select Teacher</option>
              @foreach($teachers as $teacher)
                <option value="{{ $teacher->teacher->id ?? '' }}">{{ $teacher->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Grade Level</label>
            <select name="grade_level" class="form-select" required>
              <option value="">Select Grade</option>
              @foreach($classes->pluck('grade_level')->unique() as $grade)
                <option value="{{ $grade }}">{{ $grade }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Section</label>
            <select name="section" class="form-select" required>
              <option value="">Select Section</option>
              @foreach($classes->pluck('section')->unique() as $section)
                <option value="{{ $section }}">{{ $section }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Effective Date</label>
            <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-d') }}" required>
          </div>
          <div class="col-12">
            <label class="form-label">Notes (Optional)</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes about this assignment"></textarea>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">
              <i class="ri-user-add-line me-2"></i>Assign Adviser
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- CURRENT CLASS ADVISERS -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="ri-user-star-line me-2"></i>Current Class Advisers
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Class</th>
                <th>Adviser</th>
                <th>Assigned Date</th>
                <th>Effective Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($advisers as $adviser)
              <tr>
                <td>{{ $adviser->grade_level }} - {{ $adviser->section }}</td>
                <td>{{ $adviser->teacher->name }}</td>
                <td>{{ $adviser->assigned_date->format('M d, Y') }}</td>
                <td>{{ $adviser->effective_date->format('M d, Y') }}</td>
                <td>
                  @if($adviser->status === 'active')
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-secondary">Inactive</span>
                  @endif
                </td>
                <td>
                  @if($adviser->status === 'active')
                    <button class="btn btn-sm btn-outline-danger" onclick="removeAssignment({{ $adviser->id }})">
                      <i class="ri-user-unfollow-line me-1"></i>Remove
                    </button>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No class advisers assigned yet</td>
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
<script src="{{ asset('js/faculty-head-assign-adviser.js') }}"></script>
@endpush
