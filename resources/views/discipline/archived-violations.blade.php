<x-discipline-layout>

  @vite(['resources/js/app.js', 'resources/css/index_discipline.css', 'resources/js/discipline_violations.js'])

  <!-- MAIN CONTENT -->
  <main class="col-12 col-md-10 px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="section-title mb-0">Archived Violations</h1>
        <small class="text-muted">Previously deleted violations for record keeping</small>
      </div>
      <div class="d-flex align-items-center gap-3">
        <a href="{{ route('discipline.violations.index') }}" class="btn btn-outline-primary">
          <i class="ri-arrow-left-line me-2"></i>Back to Active Violations
        </a>
        <div class="text-muted">
          <i class="ri-calendar-line me-1"></i>{{ now()->format('F j, Y') }}
        </div>
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

    <!-- STATISTICS CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-4">
        <div class="card card-summary stats-card h-100" style="background-color: #6c757d; color: white;">
          <div class="card-body text-center">
            <i class="ri-archive-line display-6 mb-2"></i>
            <div>Total Archived</div>
            <h3>{{ $stats['total_archived'] ?? 0 }}</h3>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-4">
        <div class="card card-summary stats-card h-100" style="background-color: #dc3545; color: white;">
          <div class="card-body text-center">
            <i class="ri-calendar-line display-6 mb-2"></i>
            <div>Archived This Month</div>
            <h3>{{ $stats['archived_this_month'] ?? 0 }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- SEARCH AND FILTER SECTION -->
    <div class="search-filter-section">
      <div class="row align-items-end">
        <div class="col-md-4">
          <label for="searchInput" class="form-label fw-bold">Search Archived Violations</label>
          <div class="input-group">
            <span class="input-group-text"><i class="ri-search-line"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Search archived violations...">
          </div>
        </div>
        <div class="col-md-4">
          <label for="dateFilter" class="form-label fw-bold">Archive Date Filter</label>
          <input type="date" class="form-control" id="dateFilter" title="Filter by archive date">
        </div>
      </div>
    </div>

    <!-- ARCHIVED VIOLATIONS TABLE -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Archived Violations List</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="archivedViolationsTable">
            <thead>
              <tr>
                <th>Student</th>
                <th>Violation</th>
                <th>Violation Date</th>
                <th>Archive Date</th>
                <th>Archive Reason</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($archivedViolations as $violation)
                <tr>
                  <td>
                    <div>
                      <strong>{{ $violation->student->first_name ?? 'Unknown' }} {{ $violation->student->last_name ?? '' }}</strong>
                      <br><small class="text-muted">{{ $violation->student->student_id ?? 'N/A' }}</small>
                    </div>
                  </td>
                  <td>
                    <strong>{{ $violation->title }}</strong>
                    <br><small class="text-muted">{{ Str::limit($violation->description, 50) }}</small>
                  </td>
                  <td>
                    {{ $violation->violation_date ? $violation->violation_date->format('M d, Y') : 'N/A' }}
                    @if($violation->violation_time)
                      <br><small class="text-muted">{{ date('h:i A', strtotime($violation->violation_time)) }}</small>
                    @endif
                  </td>
                  <td>
                    {{ $violation->archived_at ? $violation->archived_at->format('M d, Y') : 'N/A' }}
                    @if($violation->archived_at)
                      <br><small class="text-muted">{{ $violation->archived_at->format('h:i A') }}</small>
                    @endif
                  </td>
                  <td>
                    @if($violation->archive_reason === 'manual_deletion')
                      <span class="badge bg-secondary">Manual Deletion</span>
                    @elseif($violation->archive_reason === 'escalation_to_major')
                      <span class="badge bg-warning text-dark">Escalated to Major</span>
                    @else
                      <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $violation->archive_reason ?? 'Unknown')) }}</span>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      <button type="button" class="btn btn-sm btn-outline-primary"
                              onclick="viewArchivedViolation({{ $violation->id }})"
                              title="View Details">
                        <i class="ri-eye-line"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center py-5">
                  <i class="ri-archive-line display-4 text-muted"></i>
                  <p class="text-muted mt-2">No archived violations found</p>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        @if($archivedViolations->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
          <div>
            <small class="text-muted">
              Showing {{ $archivedViolations->firstItem() ?: 0 }} to {{ $archivedViolations->lastItem() ?: 0 }}
              of {{ $archivedViolations->total() }} archived violations
            </small>
          </div>
          {{ $archivedViolations->links() }}
        </div>
        @endif
      </div>
    </div>

  </main>

  <!-- VIEW ARCHIVED VIOLATION MODAL -->
  <div class="modal fade" id="viewArchivedViolationModal" tabindex="-1" aria-labelledby="viewArchivedViolationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewArchivedViolationModalLabel">Archived Violation Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="viewArchivedViolationModalBody">
          <!-- Content will be loaded dynamically -->
        </div>
      </div>
    </div>
  </div>

</x-discipline-layout>
