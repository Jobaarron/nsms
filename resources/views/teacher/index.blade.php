<x-teacher-layout>
  <!-- MAIN CONTENT -->
     <main class="col-12 col-md-10 px-4 py-4">
       <div class="d-flex justify-content-between align-items-center mb-4">
         <h1 class="section-title mb-0">Teacher Dashboard</h1>
         <div class="text-muted">
           <i class="ri-calendar-line me-1"></i>{{ now()->format('F j, Y') }}
         </div>
       </div>

       @if(session('success'))
         <div class="alert alert-success alert-dismissible fade show" role="alert">
           {{ session('success') }}
           <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
         </div>
       @endif

       <!-- USER INFO -->
       {{-- <div class="card mb-4">
         <div class="card-body">
           <div class="d-flex align-items-center">
             <div class="me-3">
               <i class="ri-user-3-line display-4 text-primary"></i>
             </div>
             <div>
               <h5 class="mb-1">{{ Auth::user()->name }}</h5>
               <p class="text-muted mb-0">Teacher</p>
               @if(Auth::user()->teacher)
                 <small class="text-muted">{{ Auth::user()->teacher->department ?? 'Department not specified' }}</small>
               @endif
             </div>
           </div>
         </div>
       </div> --}}

       <!-- SUMMARY CARDS -->
       <div class="row g-3 mb-5">
         <div class="col-6 col-lg-3">
           <div class="card card-summary card-classes h-100">
             <div class="card-body text-center">
               <i class="ri-book-2-line display-6 mb-2"></i>
               <div>Classes Teaching</div>
               <h3>{{ $stats['total_classes'] ?? 0 }}</h3>
             </div>
           </div>
         </div>
         <div class="col-6 col-lg-3">
           <div class="card card-summary card-students h-100">
             <div class="card-body text-center">
               <i class="ri-team-line display-6 mb-2"></i>
               <div>Total Students</div>
               <h3>{{ $stats['total_students'] ?? 0 }}</h3>
             </div>
           </div>
         </div>
         <div class="col-6 col-lg-3">
           <div class="card card-summary card-grades h-100">
             <div class="card-body text-center">
               <i class="ri-pencil-line display-6 mb-2"></i>
               <div>Pending Grades</div>
               <h3>{{ $stats['pending_grades'] ?? 0 }}</h3>
             </div>
           </div>
         </div>
         {{-- <div class="col-6 col-lg-3">
           <div class="card card-summary card-upcoming h-100">
             <div class="card-body text-center">
               <i class="ri-calendar-event-line display-6 mb-2"></i>
               <div>Upcoming Exams</div>
               <h3>{{ $stats['upcoming_exams'] ?? 0 }}</h3>
             </div>
           </div>
         </div> --}}
       </div>

       <!-- RECENT CLASSES TABLE -->
       <h4 class="section-title">Recent Classes</h4>
       <div class="table-responsive mb-5">
         <table class="table table-striped align-middle">
           <thead>
             <tr>
               <th>#</th>
               <th>Class</th>
               <th>Level</th>
               <th>Students</th>
               <th class="text-center">Actions</th>
             </tr>
           </thead>
           <tbody>
             <tr>
               <td>1</td>
               <td>Biology 101</td>
               <td>Grade School</td>
               <td>28</td>
               <td class="text-center">
                 <button class="btn btn-sm btn-outline-primary me-1">
                   <i class="ri-eye-line"></i>
                 </button>
                 <button class="btn btn-sm btn-outline-primary me-1">
                   <i class="ri-pencil-line"></i>
                 </button>
                 <button class="btn btn-sm btn-outline-primary">
                   <i class="ri-time-line"></i>
                 </button>
               </td>
             </tr>
           </tbody>
         </table>
       </div>

       <!-- QUICK ACTIONS -->
       <h4 class="section-title">Quick Actions</h4>
       <div class="row g-3 mb-5">
         <div class="col-md-4">
           <div class="card h-100">
             <div class="card-body text-center">
               <i class="ri-add-circle-line display-4 text-primary mb-3"></i>
               <h5>New Assignment</h5>
               <p class="text-muted">Create and assign new tasks to your students</p>
               <button class="btn btn-outline-primary" disabled>
                 <i class="ri-add-circle-line me-2"></i>Coming Soon
               </button>
             </div>
           </div>
         </div>
         <div class="col-md-4">
           <div class="card h-100">
             <div class="card-body text-center">
               <i class="ri-file-text-line display-4 text-secondary mb-3"></i>
               <h5>Submit Grades</h5>
               <p class="text-muted">Enter and submit student grades and assessments</p>
               <button class="btn btn-outline-secondary" disabled>
                 <i class="ri-file-text-line me-2"></i>Coming Soon
               </button>
             </div>
           </div>
         </div>
         <div class="col-md-4">
           <div class="card h-100">
             <div class="card-body text-center">
               <i class="ri-checkbox-multiple-line display-4 text-info mb-3"></i>
               <h5>Mark Attendance</h5>
               <p class="text-muted">Take attendance for your classes and track student presence</p>
               <button class="btn btn-outline-info" disabled>
                 <i class="ri-checkbox-multiple-line me-2"></i>Coming Soon
               </button>
             </div>
           </div>
         </div>
       </div>

     </main>
</x-teacher-layout>