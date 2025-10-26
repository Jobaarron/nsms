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
            <select name="grade_level" id="grade_level_adviser" class="form-select" required>
              <option value="">Select Grade</option>
              @php
                $gradeOrder = [
                  'Nursery', 'Junior Casa', 'Senior Casa', 'Kinder',
                  'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
                  'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
                ];
                $availableGrades = $sections->pluck('grade_level')->unique();
                $orderedGrades = collect($gradeOrder)->filter(function($grade) use ($availableGrades) {
                  return $availableGrades->contains($grade);
                });
              @endphp
              @foreach($orderedGrades as $grade)
                <option value="{{ $grade }}">{{ $grade }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Section</label>
            <select name="section" id="section_adviser" class="form-select" required>
              <option value="">Select Section</option>
              @foreach($sections->pluck('section_name')->unique()->sort() as $section)
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
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Teacher</th>
                <th>Class</th>
                <th>Assigned Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($advisers as $adviser)
              <tr>
                <td>
                  <div class="fw-medium">{{ $adviser->teacher->user->name }}</div>
                  <div class="small text-muted">Class Adviser</div>
                </td>
                <td>
                  <span class="badge bg-primary">{{ $adviser->grade_level }} - {{ $adviser->section }}</span>
                </td>
                <td>
                  <div class="small">{{ $adviser->assigned_date->format('M d, Y') }}</div>
                </td>
                <td>
                  @if($adviser->status === 'active')
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-secondary">Inactive</span>
                  @endif
                </td>
                <td>
                  @if($adviser->status === 'active')
                    <button class="btn btn-sm btn-outline-danger" onclick="removeAssignment({{ $adviser->id }})" title="Remove Class Adviser">
                      <i class="ri-user-unfollow-line"></i>
                    </button>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  <i class="ri-user-star-line display-6 mb-2 d-block"></i>
                  No class advisers assigned yet
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</x-faculty-head-layout>

