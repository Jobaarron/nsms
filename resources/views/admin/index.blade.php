<x-admin-layout>
  {{-- <x-slot:title>Admin Dashboard</x-slot:title> --}}
  
  <h1 class="section-title">Admin Dashboard</h1>

  <!-- SUMMARY CARDS -->
  <!-- Comprehensive User Statistics -->
  <div class="row g-3 mb-5">
    <div class="col-6 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <i class="ri-user-3-line fs-2 admin-green-icon"></i>
            </div>
            <div class="flex-grow-1 ms-3">
              <div class="fw-bold fs-4">{{ $totalUsers ?? 0 }}</div>
              <div class="text-muted small">Total Users</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <i class="ri-user-add-line fs-2 admin-green-light-icon"></i>
            </div>
            <div class="flex-grow-1 ms-3">
              <div class="fw-bold fs-4">{{ $userStats['total_enrollees'] ?? 0 }}</div>
              <div class="text-muted small">Enrollees/Applicants</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <i class="ri-graduation-cap-line fs-2 admin-green-dark-icon"></i>
            </div>
            <div class="flex-grow-1 ms-3">
              <div class="fw-bold fs-4">{{ $userStats['total_students'] ?? 0 }}</div>
              <div class="text-muted small">Students</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <i class="ri-shield-user-line fs-2 admin-green-accent-icon"></i>
            </div>
            <div class="flex-grow-1 ms-3">
              <div class="fw-bold fs-4">{{ $userStats['total_roles'] ?? 0 }}</div>
              <div class="text-muted small">Roles Defined</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Detailed Statistics Row -->
  {{-- <div class="row g-3 mb-5">
    <div class="col-6 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <i class="ri-user-star-line fs-2 text-danger"></i>
            </div>
            <div class="flex-grow-1 ms-3">
              <div class="fw-bold fs-4">{{ $userStats['total_teachers'] ?? 0 }}</div>
              <div class="text-muted small">Teachers</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <i class="ri-admin-line fs-2 text-dark"></i>
            </div>
            <div class="flex-grow-1 ms-3">
              <div class="fw-bold fs-4">{{ $userStats['total_admins'] ?? 0 }}</div>
              <div class="text-muted small">Administrators</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <i class="ri-heart-line fs-2 text-secondary"></i>
            </div>
            <div class="flex-grow-1 ms-3">
              <div class="fw-bold fs-4">{{ $userStats['total_guidance'] ?? 0 }}</div>
              <div class="text-muted small">Guidance Staff</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <i class="ri-time-line fs-2 text-warning"></i>
            </div>
            <div class="flex-grow-1 ms-3">
              <div class="fw-bold fs-4">{{ $userStatusStats['pending_enrollees'] ?? 0 }}</div>
              <div class="text-muted small">Pending Applications</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div> --}}

  <!-- Data Visualization Section -->
  <div class="row g-3 mb-5">
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm">
  <div class="card-header admin-card-header">
          <h5 class="card-title mb-0">
            <i class="ri-pie-chart-line me-2 admin-green-icon"></i><span class="user-distribution-black">User Distribution</span>
          </h5>
        </div>
        <div class="card-body">
          <div class="chart-container" style="position: relative; height: 250px;">
            <canvas id="userTypesChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    {{-- <div class="col-lg-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
          <h5 class="card-title mb-0">
            <i class="ri-bar-chart-line me-2"></i>Enrollment Status
          </h5>
        </div>
        <div class="card-body">
          <canvas id="enrollmentStatusChart" width="400" height="200"></canvas>
        </div>
      </div>
    </div> --}}
  </div>

  <!-- Monthly Applications Chart -->
  {{-- <div class="row g-3 mb-5">
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
          <h5 class="card-title mb-0">
            <i class="ri-line-chart-line me-2"></i>Monthly Applications Trend (2025)
          </h5>
        </div>
        <div class="card-body">
          <canvas id="monthlyApplicationsChart" width="800" height="300"></canvas>
        </div>
      </div>
    </div>
  </div> --}}

  <!-- Chart.js Script -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // User Types Pie Chart
    const userTypesCtx = document.getElementById('userTypesChart').getContext('2d');
    new Chart(userTypesCtx, {
      type: 'doughnut',
      data: {
        labels: {!! json_encode($chartData['user_types']['labels']) !!},
        datasets: [{
          data: {!! json_encode($chartData['user_types']['data']) !!},
          backgroundColor: [
            '#43b36a', '#2b7a3b', '#7be495', '#198754', '#20c997', '#a3e635'
          ],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });

    // Enrollment Status Bar Chart
    const enrollmentStatusCtx = document.getElementById('enrollmentStatusChart').getContext('2d');
    new Chart(enrollmentStatusCtx, {
      type: 'bar',
      data: {
        labels: {!! json_encode($chartData['enrollment_status']['labels']) !!},
        datasets: [{
          label: 'Applications',
          data: {!! json_encode($chartData['enrollment_status']['data']) !!},
          backgroundColor: ['#43b36a', '#7be495', '#198754'],
          borderColor: ['#2b7a3b', '#43b36a', '#198754'],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true
          }
        },
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });

    // Monthly Applications Line Chart
    const monthlyApplicationsCtx = document.getElementById('monthlyApplicationsChart').getContext('2d');
    new Chart(monthlyApplicationsCtx, {
      type: 'line',
      data: {
        labels: {!! json_encode($chartData['monthly_applications']['labels']) !!},
        datasets: [{
          label: 'Applications',
          data: {!! json_encode($chartData['monthly_applications']['data']) !!},
          borderColor: '#43b36a',
          backgroundColor: 'rgba(67, 179, 106, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true
          }
        },
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  </script>


  {{-- internal js dahil ayaw gumana ng internal js via resources using vite syntax --}}
  <script>
    // Auto-refresh sa dashboard stats every 30 seconds
    setInterval(function() {
      refreshDashboardStats();
    }, 30000);

    function refreshDashboardStats() {
      fetch('{{ route("admin.dashboard.stats") }}')
        .then(response => response.json())
        .then(data => {
          document.getElementById('total-users').textContent = data.total_users;
          document.getElementById('total-roles').textContent = data.total_roles;
          document.getElementById('active-users').textContent = data.active_users;
          document.getElementById('recent-users').textContent = data.recent_users;
        })
        .catch(error => {
          console.error('Error fetching dashboard stats:', error);
        });
    }
  </script>

</x-admin-layout>
