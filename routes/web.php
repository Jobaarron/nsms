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
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EnrolleeController;
// use Spatie\Permission\Middlewares\RoleMiddleware;
// use Spatie\Permission\Middlewares\PermissionMiddleware;
// use App\Http\Controllers\AdminGeneratorController;
// use App\Http\Controllers\AuthController;


Route::get('/', function () {
    return view('welcome');
});

// Enrollment side
Route::get('/enroll', function () {
    return view('enroll');
});


// Enrollment Create & Store
Route::get('/enroll', [EnrollmentController::class, 'create'])
     ->name('enroll.create');
Route::post('/enroll', [EnrollmentController::class, 'store'])
     ->name('enroll.store');

// API route for fee calculation
Route::get('/api/fees/calculate/{gradeLevel}', [EnrollmentController::class, 'calculateFees'])
     ->name('api.fees.calculate');

// Contact form routes
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');



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
        
        // Users management - Updated to use UserManagementController
        Route::get('/manage-users', [App\Http\Controllers\UserManagementController::class, 'index'])->name('admin.manage.users');
        Route::post('/manage-users', [App\Http\Controllers\UserManagementController::class, 'store'])->name('admin.manage.store');
        Route::get('/manage-users/{user}', [App\Http\Controllers\UserManagementController::class, 'show'])->name('admin.manage.show');
        Route::post('/manage-users/{user}', [App\Http\Controllers\UserManagementController::class, 'update'])->name('admin.manage.update');
        Route::delete('/manage-users/{user}', [App\Http\Controllers\UserManagementController::class, 'destroy'])->name('admin.manage.destroy');
        
        // Additional user management endpoints
        Route::get('/manage-users/stats', [App\Http\Controllers\UserManagementController::class, 'getStats'])->name('admin.manage.stats');
        Route::post('/manage-users/bulk-action', [App\Http\Controllers\UserManagementController::class, 'bulkAction'])->name('admin.manage.bulk');
        Route::get('/manage-users/export', [App\Http\Controllers\UserManagementController::class, 'export'])->name('admin.manage.export');
        Route::post('/manage-users/import', [App\Http\Controllers\UserManagementController::class, 'import'])->name('admin.manage.import');
        Route::get('/manage-users/report', [App\Http\Controllers\UserManagementController::class, 'generateReport'])->name('admin.manage.report');
        
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
    
    
        
        // Individual enrollment actions
        Route::post('/enrollments/{id}/approve', [AdminController::class, 'approveEnrollment'])->name('enrollments.approve');
        Route::post('/enrollments/{id}/reject', [AdminController::class, 'rejectEnrollment'])->name('enrollments.reject');
        Route::delete('/enrollments/{id}', [AdminController::class, 'deleteEnrollment'])->name('enrollments.delete');
        Route::put('/enrollments/{id}', [AdminController::class, 'updateEnrollment'])->name('enrollments.update');
        Route::post('/enrollments/{id}/status', [AdminController::class, 'updateEnrollmentStatus'])->name('enrollments.status');
        Route::get('/enrollments/{id}/view', [AdminController::class, 'viewEnrollment'])->name('enrollments.view');
        Route::get('/enrollments/{id}/edit', [AdminController::class, 'editEnrollment'])->name('enrollments.edit');
        
        // Bulk operations
        Route::post('/enrollments/bulk/approve', [AdminController::class, 'bulkApprove'])->name('enrollments.bulk.approve');
        Route::post('/enrollments/bulk/reject', [AdminController::class, 'bulkReject'])->name('enrollments.bulk.reject');
        Route::post('/enrollments/bulk/delete', [AdminController::class, 'bulkDelete'])->name('enrollments.bulk.delete');

        Route::post('/enrollments/export', [AdminController::class, 'exportSelected'])->name('enrollments.export');
        Route::post('/enrollments/export-all', [AdminController::class, 'exportAll'])->name('enrollments.export.all');
        Route::post('/enrollments/send-notification', [AdminController::class, 'sendBulkNotification'])->name('enrollments.notification');
        Route::post('/enrollments/print', [AdminController::class, 'printEnrollments'])->name('enrollments.print');
        
        // Contact Messages Management
        Route::get('/contact-messages', [ContactController::class, 'adminIndex'])->name('admin.contact.messages');
        Route::get('/contact-messages/{message}', [ContactController::class, 'show'])->name('admin.contact.show');
        Route::post('/contact-messages/{message}/status', [ContactController::class, 'updateStatus'])->name('admin.contact.status');
        Route::delete('/contact-messages/{message}', [ContactController::class, 'destroy'])->name('admin.contact.destroy');
        Route::post('/contact-messages/bulk-action', [ContactController::class, 'bulkAction'])->name('admin.contact.bulk');
    });

   


// First version of routes, keep it here and do not delete. Route::put('/enrollments/{id}', [AdminController::class, 'updateEnrollment'])->name('enrollments.update');


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
// Route::get('/test', function () {
//     if (Auth::check()) {
//         $user = Auth::user();
//         $roles = $user->getRoleNames()->toArray();
//         $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        
//         return 'Logged in as: ' . $user->name . 
//                ' (Roles: ' . implode(', ', $roles) . ')' .
//                ' (Permissions: ' . implode(', ', $permissions) . ')';
//     } else {
//         return 'Not logged in';
//     }
// })->name('test');


// Public Teacher Account Generator (no authentication required)
Route::get('/teacher-generator', [TeacherController::class, 'showGeneratorForm'])
    ->name('teacher.generator');

Route::post('/teacher-generator', [TeacherController::class, 'generateTeacher'])
    ->name('generate.teacher');

// Teacher Authentication Routes
Route::get('/teacher/login', [TeacherController::class, 'showLoginForm'])
    ->name('teacher.login');

Route::post('/teacher/login', [TeacherController::class, 'login'])
    ->name('teacher.login.submit');

Route::post('/teacher/logout', [TeacherController::class, 'logout'])
    ->name('teacher.logout');

// Teacher Dashboard Route (protected)
Route::get('/teacher', [TeacherController::class, 'index'])
    ->name('teacher.dashboard')
    ->middleware(['auth', 'role:teacher']);

// Public Guidance Account Generator (no authentication required)
Route::get('/guidance-generator', [GuidanceDisciplineController::class, 'showPublicGenerator'])
    ->name('guidance.generator');

Route::post('/guidance-generator', [GuidanceDisciplineController::class, 'createPublicAccount'])
    ->name('guidance.generator.submit');
    
// Guidance & Discipline Routes
Route::prefix('guidance')->name('guidance.')->group(function () {
    
    // Authentication Routes
    Route::get('/login', [GuidanceDisciplineController::class, 'showLogin'])
        ->name('login');
    
    Route::post('/login', [GuidanceDisciplineController::class, 'login'])
        ->name('login.submit');
    
    Route::post('/logout', [GuidanceDisciplineController::class, 'logout'])
        ->name('logout');
    
    // Protected Routes (require authentication and guidance staff role)
    Route::middleware(['auth'])->group(function () {
        
        // Dashboard
        Route::get('/', [GuidanceDisciplineController::class, 'dashboard'])
            ->name('dashboard');
        
        // Account Management Routes
        Route::get('/create-account', [GuidanceDisciplineController::class, 'showCreateAccount'])
            ->name('create-account');
        
        Route::post('/create-account', [GuidanceDisciplineController::class, 'createAccount'])
            ->name('create-account.submit');
        
        // Student Management Routes
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [GuidanceDisciplineController::class, 'studentsIndex'])
                ->name('index');
            
            Route::get('/{student}', [GuidanceDisciplineController::class, 'showStudent'])
                ->name('show');
                
            Route::get('/{student}/info', [GuidanceDisciplineController::class, 'getStudentInfo'])
                ->name('info');
        });
        
        // Violations Management Routes
        Route::prefix('violations')->name('violations.')->group(function () {
            Route::get('/', [GuidanceDisciplineController::class, 'violationsIndex'])
                ->name('index');
            
            Route::post('/', [GuidanceDisciplineController::class, 'storeViolation'])
                ->name('store');
            
            Route::get('/{violation}', [GuidanceDisciplineController::class, 'showViolation'])
                ->name('show');
            
            Route::get('/{violation}/edit', [GuidanceDisciplineController::class, 'editViolation'])
                ->name('edit');
            
            Route::put('/{violation}', [GuidanceDisciplineController::class, 'updateViolation'])
                ->name('update');
            
            Route::delete('/{violation}', [GuidanceDisciplineController::class, 'destroyViolation'])
                ->name('destroy');
        });
        
        // Counseling Records Routes (to be implemented)
        Route::prefix('counseling')->name('counseling.')->group(function () {
            Route::get('/', function () {
                return view('guidancediscipline.counseling.index');
            })->name('index');
            
            Route::get('/create', function () {
                return view('guidancediscipline.counseling.create');
            })->name('create');
            
            Route::post('/', function () {
                // Store counseling record logic
            })->name('store');
            
            Route::get('/{record}/edit', function () {
                return view('guidancediscipline.counseling.edit');
            })->name('edit');
        });
        
        // Facial Recognition Routes (to be implemented)
        Route::prefix('facial-recognition')->name('facial-recognition.')->group(function () {
            Route::get('/', function () {
                return view('guidancediscipline.facial-recognition.index');
            })->name('index');
            
            Route::post('/enroll', function () {
                // Enroll face logic
            })->name('enroll');
            
            Route::post('/recognize', function () {
                // Face recognition logic
            })->name('recognize');
        });
        
        // Disciplinary Actions Routes (to be implemented)
        Route::prefix('disciplinary-actions')->name('disciplinary-actions.')->group(function () {
            Route::get('/', function () {
                return view('guidancediscipline.disciplinary-actions.index');
            })->name('index');
            
            Route::get('/create', function () {
                return view('guidancediscipline.disciplinary-actions.create');
            })->name('create');
            
            Route::post('/', function () {
                // Store disciplinary action logic
            })->name('store');
        });
        
        // Analytics & Reports Routes (to be implemented)
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', function () {
                return view('guidancediscipline.reports.index');
            })->name('index');
            
            Route::get('/violations', function () {
                return view('guidancediscipline.reports.violations');
            })->name('violations');
            
            Route::get('/counseling', function () {
                return view('guidancediscipline.reports.counseling');
            })->name('counseling');
        });
        
        // Settings Routes (to be implemented)
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', function () {
                return view('guidancediscipline.settings.index');
            })->name('index');
            
            Route::get('/profile', function () {
                return view('guidancediscipline.settings.profile');
            })->name('profile');
            
            Route::put('/profile', function () {
                // Update profile logic
            })->name('profile.update');
        });
    });
});


// First version of routes, keep it here and do not delete.
// Route::get('/teacher', [TeacherController::class, 'index']);
// Route::get('/admin', [adminController::class, 'adminindex']);
// Route::get('/admin/login', [adminController::class, 'adminlogin']);


// Route::get('/guidance', [GuidanceDisciplineController::class, 'index']);
// Route::get('/guidance/login', [GuidanceDisciplineController::class, 'loginform']);

// Student routes
Route::get('/student', [StudentController::class, 'index']);
Route::prefix('student')->name('student.')->group(function () {
    // Student login routes (public)
    Route::get('/login', [StudentController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [StudentController::class, 'login'])->name('login.submit');
    
    // Protected student routes
    Route::middleware(['auth:student'])->group(function () {
        Route::get('/student', [StudentController::class, 'index'])->name('dashboard');
        Route::get('/violations', [StudentController::class, 'violations'])->name('violations');
        Route::post('/logout', [StudentController::class, 'logout'])->name('logout');
    });
});

// Enrollee routes
Route::prefix('enrollee')->name('enrollee.')->group(function () {
    // Enrollee login routes (public)
    Route::get('/login', [EnrolleeController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [EnrolleeController::class, 'login'])->name('login.submit');
    
    // Protected enrollee routes
    Route::middleware(['auth:enrollee'])->group(function () {
        // Dashboard
        Route::get('/', [EnrolleeController::class, 'index'])->name('dashboard');
        
        // Application management
        Route::get('/application', [EnrolleeController::class, 'application'])->name('application');
        
        // Document management
        Route::get('/documents', [EnrolleeController::class, 'documents'])->name('documents');
        Route::post('/documents/upload', [EnrolleeController::class, 'uploadDocument'])->name('documents.upload');
        Route::delete('/documents/delete', [EnrolleeController::class, 'deleteDocument'])->name('documents.delete');
        
        // Payment management
        Route::get('/payment', [EnrolleeController::class, 'payment'])->name('payment');
        Route::post('/payment/process', [EnrolleeController::class, 'processPayment'])->name('payment.process');
        
        // Schedule management
        Route::get('/schedule', [EnrolleeController::class, 'schedule'])->name('schedule');
        Route::put('/schedule', [EnrolleeController::class, 'updateSchedule'])->name('schedule.update');
        
        // Profile management (redirects to application page since they're merged)
        Route::get('/profile', function() {
            return redirect()->route('enrollee.application');
        })->name('profile');
        Route::put('/profile', [EnrolleeController::class, 'updateProfile'])->name('profile.update');
        
        // Logout
        Route::post('/logout', [EnrolleeController::class, 'logout'])->name('logout');
    });
});
