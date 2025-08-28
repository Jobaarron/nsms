<x-student-layout>
  @vite(['resources/css/student_violations.css'])
      <!-- MAIN CONTENT -->
      <main class="col-12 col-md-10 px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="section-title mb-0">My Violations</h1>
          <div class="text-muted">
            <i class="ri-user-line me-1"></i>{{ $student->first_name }} {{ $student->last_name }}
          </div>
        </div>

        @if(session('info'))
          <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        <!-- SUMMARY CARDS -->
        <div class="row g-3 mb-5">
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-total h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-flag-line display-6 me-3"></i>
                <div>
                  <div>Total Violations</div>
                  <h3>{{ $violations->count() }}</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-minor h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-information-line display-6 me-3"></i>
                <div>
                  <div>Minor</div>
                  <h3>{{ $violations->where('severity', 'minor')->count() }}</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-major h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-alert-line display-6 me-3"></i>
                <div>
                  <div>Major</div>
                  <h3>{{ $violations->where('severity', 'major')->count() }}</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="card card-summary card-recent h-100">
              <div class="card-body d-flex align-items-center">
                <i class="ri-time-line display-6 me-3"></i>
                <div>
                  <div>This Month</div>
                  <h3>{{ $violations->where('violation_date', '>=', now()->startOfMonth())->count() }}</h3>
                </div>
              </div>
            </div>
          </div>
        </div>

        @if($violations->count() > 0)
          <!-- VIOLATIONS LIST -->
          <h4 class="section-title">Violation Records</h4>
          <div class="row g-3 mb-5">
            @foreach($violations as $violation)
              <div class="col-12">
                <div class="card violation-card violation-{{ $violation->severity }}">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                          <h6 class="card-title mb-1 fw-bold">{{ $violation->title }}</h6>
                          <span class="badge badge-violation bg-{{ $violation->severity === 'minor' ? 'success' : ($violation->severity === 'major' ? 'warning' : 'danger') }}">
                            {{ ucfirst($violation->severity) }}
                          </span>
                        </div>
                        <p class="card-text text-muted mb-2">{{ $violation->description }}</p>
                        @if($violation->action_taken)
                          <p class="card-text mb-0">
                            <strong>Action Taken:</strong> {{ $violation->action_taken }}
                          </p>
                        @endif
                      </div>
                      <div class="col-md-4 text-md-end">
                        <div class="mb-2">
                          <small class="text-muted">
                            <i class="ri-calendar-line me-1"></i>
                            {{ $violation->violation_date->format('M d, Y') }}
                          </small>
                        </div>
                        @if($violation->reported_by_name)
                          <div class="mb-2">
                            <small class="text-muted">
                              <i class="ri-user-line me-1"></i>
                              Reported by: {{ $violation->reported_by_name }}
                            </small>
                          </div>
                        @endif
                        @if($violation->handled_by_name)
                          <div>
                            <small class="text-muted">
                              <i class="ri-shield-user-line me-1"></i>
                              Handled by: {{ $violation->handled_by_name }}
                            </small>
                          </div>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          <!-- BEHAVIOR SUMMARY -->
          <h4 class="section-title">Behavior Summary</h4>
          <div class="card mb-5">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <h6 class="fw-bold text-primary">Recent Trends</h6>
                  <ul class="list-unstyled">
                    <li class="mb-2">
                      <i class="ri-calendar-line me-2 text-primary"></i>
                      Last violation: {{ $violations->first()->violation_date->diffForHumans() }}
                    </li>
                    <li class="mb-2">
                      <i class="ri-arrow-{{ $violations->where('violation_date', '>=', now()->subMonth())->count() <= $violations->where('violation_date', '>=', now()->subMonths(2))->where('violation_date', '<', now()->subMonth())->count() ? 'down' : 'up' }}-line me-2 text-{{ $violations->where('violation_date', '>=', now()->subMonth())->count() <= $violations->where('violation_date', '>=', now()->subMonths(2))->where('violation_date', '<', now()->subMonth())->count() ? 'success' : 'warning' }}"></i>
                      Trend: {{ $violations->where('violation_date', '>=', now()->subMonth())->count() <= $violations->where('violation_date', '>=', now()->subMonths(2))->where('violation_date', '<', now()->subMonth())->count() ? 'Improving' : 'Needs Attention' }}
                    </li>
                  </ul>
                </div>
                <div class="col-md-6">
                  <h6 class="fw-bold text-primary">Recommendations</h6>
                  <ul class="list-unstyled">
                    @if($violations->where('severity', 'major')->count() > 0)
                      <li class="mb-2">
                        <i class="ri-lightbulb-line me-2 text-warning"></i>
                        Consider scheduling a guidance counseling session
                      </li>
                    @endif
                    @if($violations->count() === 0)
                      <li class="mb-2">
                        <i class="ri-star-line me-2 text-success"></i>
                        Excellent behavior record!
                      </li>
                    @else
                      <li class="mb-2">
                        <i class="ri-heart-line me-2 text-primary"></i>
                        Focus on positive behavior choices moving forward
                      </li>
                    @endif
                  </ul>
                </div>
              </div>
            </div>
          </div>
        @else
          <!-- NO VIOLATIONS -->
          <div class="text-center py-5">
            <div class="mb-4">
              <i class="ri-shield-check-line display-1 text-success"></i>
            </div>
            <h4 class="text-success mb-3">Excellent Behavior!</h4>
            <p class="text-muted mb-4">You have no violations on record. Keep up the great work!</p>
            <div class="card mx-auto" style="max-width: 400px;">
              <div class="card-body">
                <h6 class="fw-bold text-primary">Tips for Continued Success:</h6>
                <ul class="list-unstyled text-start mt-3">
                  <li class="mb-2">
                    <i class="ri-check-line me-2 text-success"></i>
                    Follow school rules and regulations
                  </li>
                  <li class="mb-2">
                    <i class="ri-check-line me-2 text-success"></i>
                    Treat fellow students with respect
                  </li>
                  <li class="mb-2">
                    <i class="ri-check-line me-2 text-success"></i>
                    Attend classes regularly and on time
                  </li>
                  <li class="mb-0">
                    <i class="ri-check-line me-2 text-success"></i>
                    Communicate openly with teachers and staff
                  </li>
                </ul>
              </div>
            </div>
          </div>
        @endif

        <!-- CONTACT INFO -->
        <div class="card bg-light">
          <div class="card-body text-center">
            <h6 class="fw-bold text-primary mb-3">Need Help or Have Questions?</h6>
            <p class="text-muted mb-3">If you have any questions about these violations or need guidance on improving your behavior, don't hesitate to reach out to the Guidance Office.</p>
            <div class="row text-center">
              <div class="col-md-6">
                <i class="ri-phone-line text-primary me-2"></i>
                <strong>Guidance Office:</strong> (02) 123-4567
              </div>
              <div class="col-md-6">
                <i class="ri-mail-line text-primary me-2"></i>
                <strong>Email:</strong> guidance@nicolites.edu.ph
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</x-student-layout>
