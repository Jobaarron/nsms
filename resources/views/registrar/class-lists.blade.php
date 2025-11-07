<x-registrar-layout>
  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">
        <i class="ri-group-line me-2 text-primary"></i>Student List
      </h1>
      <div class="text-muted">
        <i class="ri-calendar-line me-1"></i>{{ $currentAcademicYear }}
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

    <!-- GRADE LEVELS ACCORDION -->
    <div class="accordion" id="gradeAccordion">
      @php
        $gradeGroups = [
          'Elementary' => ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
          'Junior High' => ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'],
          'Senior High' => ['Grade 11', 'Grade 12']
        ];
      @endphp
      
      @foreach($gradeGroups as $groupName => $grades)
        @foreach($grades as $grade)
          <div class="accordion-item mb-3">
            <h2 class="accordion-header" id="heading{{ str_replace(' ', '', $grade) }}">
              <button class="accordion-button collapsed fw-bold" type="button" 
                      data-bs-toggle="collapse" 
                      data-bs-target="#collapse{{ str_replace(' ', '', $grade) }}" 
                      aria-expanded="false" 
                      aria-controls="collapse{{ str_replace(' ', '', $grade) }}"
                      onclick="loadGradeSections('{{ $grade }}')">
                <i class="ri-graduation-cap-line me-2 text-primary"></i>
                {{ $grade }}
                <span class="badge bg-secondary ms-auto me-3" id="badge{{ str_replace(' ', '', $grade) }}">Loading...</span>
              </button>
            </h2>
            <div id="collapse{{ str_replace(' ', '', $grade) }}" 
                 class="accordion-collapse collapse" 
                 aria-labelledby="heading{{ str_replace(' ', '', $grade) }}" 
                 data-bs-parent="#gradeAccordion">
              <div class="accordion-body p-0">
                <div id="sections{{ str_replace(' ', '', $grade) }}" class="loading-sections">
                  <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading sections...</span>
                    </div>
                    <p class="text-muted mt-2">Loading sections for {{ $grade }}...</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      @endforeach
    </div>

    <!-- Student Details Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Student Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="studentModalBody">
            <!-- Student details will be loaded here -->
          </div>
        </div>
      </div>
    </div>

  </main>

  <!-- Hidden Templates for JavaScript -->
  <div id="templates" style="display: none;">
    <!-- Strand Card Template -->
    <div id="strand-card-template" class="col-md-6">
      <div class="strand-card border rounded p-3 text-center" style="cursor: pointer;">
        <div class="strand-badge bg-primary text-white rounded px-3 py-2 mb-2">
          <span class="strand-name"></span>
        </div>
        <h6 class="mb-1 strand-full-name"></h6>
        <small class="text-muted">Click to view sections</small>
      </div>
    </div>

    <!-- Track Card Template -->
    <div id="track-card-template" class="col-md-6">
      <div class="track-card border rounded p-3 text-center" style="cursor: pointer;">
        <div class="track-badge bg-success text-white rounded px-3 py-2 mb-2">
          <span class="track-name"></span>
        </div>
        <h6 class="mb-1 track-full-name"></h6>
        <small class="text-muted">Click to view sections</small>
      </div>
    </div>

    <!-- Section Item Template -->
    <div id="section-item-template" class="section-item border-bottom" style="cursor: pointer;">
      <div class="d-flex justify-content-between align-items-center p-3 hover-bg-light">
        <div class="d-flex align-items-center">
          <div class="section-badge bg-warning text-white rounded px-3 py-2 me-3">
            Section <span class="section-name"></span>
          </div>
          <div>
            <h6 class="mb-0">Section <span class="section-name-text"></span></h6>
            <small class="text-muted">Click to view students</small>
          </div>
        </div>
        <div class="text-end">
          <span class="badge bg-secondary student-count"></span>
        </div>
      </div>
    </div>

    <!-- Student Item Template -->
    <div id="student-item-template" class="student-item d-flex justify-content-between align-items-center py-2 border-bottom">
      <div class="d-flex align-items-center">
        <span class="student-number me-3 text-muted"></span>
        <div>
          <h6 class="mb-0 student-name"></h6>
          <small class="text-muted student-id"></small>
        </div>
      </div>
      <div class="student-actions">
        <button class="btn btn-sm btn-outline-primary" type="button">
          <i class="ri-eye-line"></i>
        </button>
      </div>
    </div>
  </div>

  @vite('resources/js/registrar-class-lists.js')
</x-registrar-layout>
