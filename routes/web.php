<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EnrollmentController;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentWelcomeMail;
use App\Models\Student;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GuidanceDisciplineController;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use App\Http\Controllers\AdminGeneratorController;
use App\Http\Controllers\AuthController;

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
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
        
        // Users management
        Route::middleware(['auth'])->group(function () {
            Route::get('/manage-users', [AdminController::class, 'manageUsers'])->name('admin.manage.users');
            // Other user routes...
        });
        
        // Roles management
        Route::middleware(['auth'])->group(function () {
            // Dashboard - accessible to all authenticated users
            Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
            Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
            
            // Roles & Access page
            Route::get('/roles-access', [AdminController::class, 'rolesAccess'])->name('admin.roles.access');
        });
        
         // Enrollments management
        Route::get('/admin/enrollments', [AdminController::class, 'enrollments'])->name('admin.enrollments');
        

        // Add all other admin routes here
    });
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
Route::get('/guidance', [GuidanceDisciplineController::class, 'index']);;
