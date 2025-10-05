<x-teacher-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Recommend Student for Counseling</h1>
      <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary">
        <i class="ri-arrow-left-line me-2"></i>Back to Dashboard
      </a>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Counseling Recommendation Form</h5>
          </div>
          <div class="card-body">
            <form action="{{ route('teacher.recommend-counseling') }}" method="POST">
              @csrf

              <div class="mb-3">
                <label for="student_id" class="form-label">Select Student <span class="text-danger">*</span></label>
                <select class="form-select @error('student_id') is-invalid @enderror" id="student_id" name="student_id" required>
                  <option value="">Choose a student...</option>
                  @foreach($students as $student)
                    <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                      {{ $student->full_name }} ({{ $student->student_id }})
                    </option>
                  @endforeach
                </select>
                @error('student_id')
                  <div class="invalid-feedback">{{ $error }}</div>
                @enderror
              </div>

              <div class="mb-3">
                <label for="reason" class="form-label">Reason for Recommendation <span class="text-danger">*</span></label>
                <textarea
                  class="form-control @error('reason') is-invalid @enderror"
                  id="reason"
                  name="reason"
                  rows="4"
                  placeholder="Please describe the reason for recommending this student for counseling. Include any observations, concerns, or specific issues you've noticed..."
                  required
                >{{ old('reason') }}</textarea>
                @error('reason')
                  <div class="invalid-feedback">{{ $error }}</div>
                @enderror
                <div class="form-text">
                  This information will help the guidance counselor understand the student's needs and provide appropriate support.
                </div>
              </div>

              <div class="mb-3">
                <label for="notes" class="form-label">Additional Notes (Optional)</label>
                <textarea
                  class="form-control @error('notes') is-invalid @enderror"
                  id="notes"
                  name="notes"
                  rows="3"
                  placeholder="Any additional observations or context that might be helpful..."
                >{{ old('notes') }}</textarea>
                @error('notes')
                  <div class="invalid-feedback">{{ $error }}</div>
                @enderror
              </div>

              <div class="alert alert-info">
                <i class="ri-information-line me-2"></i>
                <strong>Note:</strong> This recommendation will be forwarded to the guidance department for review.
                A guidance counselor will assess the situation and may schedule a counseling session if appropriate.
              </div>

              <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary">
                  <i class="ri-close-line me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-warning">
                  <i class="ri-heart-pulse-line me-2"></i>Submit Recommendation
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
</x-teacher-layout>
