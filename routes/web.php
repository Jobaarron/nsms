<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EnrollmentController;
// use Illuminate\Support\Facades\Mail;
// use App\Mail\StudentWelcomeMail;
// use App\Models\Student;
use App\Models\User;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GuidanceDisciplineController;
// use Spatie\Permission\Middlewares\RoleMiddleware;
// use Spatie\Permission\Middlewares\PermissionMiddleware;
// use App\Http\Controllers\AdminGeneratorController;
// use App\Http\Controllers\AuthController;


Route::get('/', function () {
    return view('welcome');
});

// Route::get('/login', function () {
//     return view('login');
// }); // Excluded in guest side

// Enrollment side
Route::get('/enroll', function () {
    return view('enroll');
});


// Enrollment Create & Store
Route::get('/enroll', [EnrollmentController::class, 'create'])
     ->name('enroll.create');
Route::post('/enroll', [EnrollmentController::class, 'store'])
     ->name('enroll.store');

    //  Route::get('/mailtrap-test', function () {
        
    //     $student = new Student([
    //         'first_name' => 'Job Aarron',
    //         'email'      => 'jobaarronmisenas26@gmail.com',
    //     ]);
    
    //     Mail::to(env('MAIL_TEST_RECIPIENT'))
    //     ->send(new StudentWelcomeMail($student, 'TestPwd123'));
       
    //     return 'Check your Mailtrap inbox!';
    // }); // Mailtrap Testing Purposes Do No Touch

    // Route::get('/test-email/student-welcome', function () {
    //     $student = (object) [
    //         'first_name' => 'Jane',
    //         'email' => 'jane.smith@example.com'
    //     ];
        
    //     $rawPassword = 'TestPass456';
        
    //     return view('emails.student_welcome', compact('student', 'rawPassword'));
    // }); Email form sample, it does not send to the mailtrap.

   // Admin Generator Routes
//    Route::get('/generate-admin', [AdminGeneratorController::class, 'showForm'])->name('show.admin.generator');
//    Route::post('/generate-admin', [AdminGeneratorController::class, 'generateAdmin'])->name('generate.admin');
//    Route::post('/admin/login', [AdminController::class, 'adminLogin'])->name('admin.login.submit');
//    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// Admin Generator (accessible without login for initial setup)
Route::get('/generate-admin', [AdminController::class, 'showGeneratorForm'])->name('show.admin.generator');
Route::post('/generate-admin', [AdminController::class, 'generateAdmin'])->name('generate.admin');

// Admin routes
Route::prefix('admin')->group(function () {
    // Admin login routes (public)
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.submit');
    
    // Protected admin routes - use auth middleware
    Route::middleware(['auth'])->group(function () {
        // Dashboard
        
        Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
            Route::get('/dashboard/stats', [AdminController::class, 'getStats'])->name('dashboard.stats');
        });
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
        
        // Users management
        Route::get('/manage-users', [AdminController::class, 'manageUsers'])->name('admin.manage.users');
        
        // Roles & Access Management - Use your custom permission middleware
        Route::middleware(['can:Manage Roles'])->group(function () {
            Route::get('/roles-access', [AdminController::class, 'rolesAccess'])->name('admin.roles.access');
            
            // AJAX endpoints for roles & access management
            Route::post('/roles-access/assign-role', [AdminController::class, 'assignRole'])->name('admin.assign.role');
            Route::post('/roles-access/remove-role', [AdminController::class, 'removeRole'])->name('admin.remove.role');
            Route::get('/users/{user}/roles', [AdminController::class, 'getUserRoles'])->name('admin.user.roles');
            
            Route::post('/roles-access/create-role', [AdminController::class, 'createRole'])->name('admin.create.role');
            Route::put('/roles-access/update-role/{id}', [AdminController::class, 'updateRole'])->name('admin.update.role');
            Route::delete('/roles-access/delete-role/{id}', [AdminController::class, 'deleteRole'])->name('admin.delete.role');
            
            Route::post('/roles-access/create-permission', [AdminController::class, 'createPermission'])->name('admin.create.permission');
            Route::put('/roles-access/update-permission/{id}', [AdminController::class, 'updatePermission'])->name('admin.update.permission');
            Route::delete('/roles-access/delete-permission/{id}', [AdminController::class, 'deletePermission'])->name('admin.delete.permission');
        });
    });

    // Enrollments management 
    Route::get('/enrollments', [AdminController::class, 'enrollments'])->name('admin.enrollments');
});


// Inside the auth middleware group
Route::middleware(['auth'])->group(function () {
    // Dashboard - accessible to all authenticated users
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    
    // User management - requires 'Manage users' permission
    Route::middleware(['permissions:Manage users'])->group(function () {
      
        // Other user routes...
    });
    
    // Role management - requires 'Roles & Access' permission
    Route::middleware(['permissions:Roles & Access'])->group(function () {
     
        // Other role routes...
    });
});

// Test route to check authentication, roles, and permissions (Spatie)
Route::get('/test', function () {
    if (Auth::check()) {
        $user = Auth::user();
        $roles = $user->getRoleNames()->toArray();
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        
        return 'Logged in as: ' . $user->name . 
               ' (Roles: ' . implode(', ', $roles) . ')' .
               ' (Permissions: ' . implode(', ', $permissions) . ')';
    } else {
        return 'Not logged in';
    }
})->name('test');







Route::get('/teacher', [TeacherController::class, 'index']);
// Route::get('/admin', [adminController::class, 'adminindex']);
// Route::get('/admin/login', [adminController::class, 'adminlogin']);
Route::get('/student', [StudentController::class, 'index']);
Route::get('/guidance', [GuidanceDisciplineController::class, 'index']);

// Student routes
Route::prefix('student')->name('student.')->group(function () {
    // Student login routes (public)
    Route::get('/login', [StudentController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [StudentController::class, 'login'])->name('login.submit');
    
    // Protected student routes
    Route::middleware(['auth:student'])->group(function () {
        Route::get('/dashboard', [StudentController::class, 'index'])->name('dashboard');
        Route::post('/logout', [StudentController::class, 'logout'])->name('logout');
    });
});
