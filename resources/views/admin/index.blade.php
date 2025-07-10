<x-admin-layout>
  <x-slot:title>Admin Dashboard</x-slot:title>
  
  <h1 class="section-title">Admin Dashboard</h1>

  <!-- SUMMARY CARDS -->
  <div class="row g-3 mb-5">
    <div class="col-6 col-lg-3">
      <div class="card card-summary card-primary h-100">
        <div class="card-body d-flex align-items-center">
          <i class="ri-user-3-line display-6 me-3"></i>
          <div>
            <div>Total Users</div>
            <h3>--</h3>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card card-summary card-secondary h-100">
        <div class="card-body d-flex align-items-center">
          <i class="ri-shield-user-line display-6 me-3"></i>
          <div>
            <div>Roles Defined</div>
            <h3>--</h3>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card card-summary card-success h-100">
        <div class="card-body d-flex align-items-center">
          <i class="ri-book-open-line display-6 me-3"></i>
          <div>
            <div>Enrolled This Year</div>
            <h3>--</h3>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card card-summary card-success h-100">
        <div class="card-body d-flex align-items-center">
          <i class="ri-book-mark-line display-6 me-3"></i>
          <div>
            <div>Enrolled Last Year</div>
            <h3>--</h3>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- RECENT ENROLLMENTS TABLE -->
  <h4 class="section-title">Latest Enrollments</h4>
  <div class="table-responsive mb-5">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Student</th>
          <th>Program</th>
          <th>Date</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Jane Doe</td>
          <td>Junior High</td>
          <td>2024-07-15</td>
          <td class="text-center">
            <button class="btn btn-sm btn-outline-primary me-1">
              <i class="ri-eye-line"></i>
            </button>
            <button class="btn btn-sm btn-outline-warning me-1">
              <i class="ri-edit-line"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger">
              <i class="ri-delete-bin-line"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</x-admin-layout>
