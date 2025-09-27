<x-admin-layout>
  {{-- <x-slot:title>Admin Dashboard</x-slot:title> --}}
  
  <h1 class="section-title">Admin Dashboard</h1>

  <!-- SUMMARY CARDS -->
  <div class="row g-3 mb-5">
    <div class="col-6 col-lg-3">
      <div class="card card-summary card-primary h-100">
        <div class="card-body d-flex align-items-center">
          <i class="ri-user-3-line display-6 me-3 text-white"></i>
          <div>
            <div class="text-white small">Total Users</div>
            <h3 id="total-users" class="mb-0 text-white">{{ $totalUsers ?? 0 }}</h3>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card card-summary card-secondary h-100">
        <div class="card-body d-flex align-items-center">
          <i class="ri-shield-user-line display-6 me-3 text-white"></i>
          <div>
            <div class="text-white small">Roles Defined</div>
            <h3 id="total-roles" class="mb-0 text-white">{{ $totalRoles ?? 0 }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>


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
