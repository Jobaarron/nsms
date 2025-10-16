<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\EnrolleeController;
use App\Http\Controllers\GuidanceController;
use App\Http\Controllers\RegistrarController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AdminEnrollmentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\PaymentScheduleController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\DataChangeRequestController;




Route::get('/', function () {
    return view('welcome');
});

// Enrollment side
Route::get('/apply', function () {
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



// Admin Generator (accessible without login for initial setup) Removed


// Admin routes
Route::prefix('admin')->group(function () {
    // Admin login routes (public)
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.submit');
    
    // Protected admin routes - use auth middleware
    Route::middleware(['auth'])->group(function () {
        // Dashboard
        Route::middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
        
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [AdminController::class, 'getStats'])->name('dashboard.stats');

        // Forwarded Case Meetings for President (Admin)
        Route::get('/forwarded-cases', [AdminController::class, 'forwardedCases'])->name('forwarded.cases');

        // Sanction actions for forwarded cases
        Route::post('/sanctions/{sanction}/approve', [AdminController::class, 'approveSanction'])->name('sanctions.approve');
        Route::post('/sanctions/{sanction}/reject', [AdminController::class, 'rejectSanction'])->name('sanctions.reject');
        Route::post('/sanctions/{sanction}/revise', [AdminController::class, 'reviseSanction'])->name('sanctions.revise');

        // View summary report for case meeting
        Route::get('/case-meetings/{caseMeeting}/summary', [AdminController::class, 'viewSummaryReport'])->name('case-meetings.summary');
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
        
       
     
        
        // User Management - Use your custom permission middleware
        Route::middleware(['permission:Manage Users'])->group(function () {
            Route::get('/manage-users', [UserManagementController::class, 'index'])->name('manage.users');
            Route::post('/assign-role', [AdminController::class, 'assignRole'])->name('assign.role');
            Route::post('/remove-role', [AdminController::class, 'removeRole'])->name('remove.role');
            Route::post('/create-role', [AdminController::class, 'createRole'])->name('create.role');
            Route::put('/roles/{id}', [AdminController::class, 'updateRole'])->name('update.role');
            Route::delete('/roles/{id}', [AdminController::class, 'deleteRole'])->name('delete.role');
            Route::post('/create-permission', [AdminController::class, 'createPermission'])->name('create.permission');
            Route::put('/permissions/{id}', [AdminController::class, 'updatePermission'])->name('update.permission');
            Route::delete('/permissions/{id}', [AdminController::class, 'deletePermission'])->name('delete.permission');
            Route::get('/users/{user}/roles', [AdminController::class, 'getUserRoles'])->name('user.roles');

            // User Management
            Route::get('/user-management', [UserManagementController::class, 'index'])->name('user.management');

            // User CRUD operations
            Route::get('/users/{id}', [UserManagementController::class, 'show'])->name('users.show');
            Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');
            Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('users.destroy');
            Route::post('/users/admin', [UserManagementController::class, 'storeAdmin'])->name('users.store.admin');
            Route::post('/users/teacher', [UserManagementController::class, 'storeTeacher'])->name('users.store.teacher');
            Route::post('/users/guidance', [UserManagementController::class, 'storeGuidance'])->name('users.store.guidance');
            Route::post('/users/discipline', [UserManagementController::class, 'storeDiscipline'])->name('users.store.discipline');
            Route::post('/users/guidance-counselor', [UserManagementController::class, 'createGuidanceCounselor'])->name('users.store.guidance_counselor');
            Route::post('/users/discipline-head', [UserManagementController::class, 'createDisciplineHead'])->name('users.store.discipline_head');
            Route::post('/users/discipline-officer', [UserManagementController::class, 'createDisciplineOfficer'])->name('users.store.discipline_officer');
            Route::post('/users/cashier', [UserManagementController::class, 'createCashier'])->name('users.store.cashier');
            Route::post('/users/faculty-head', [UserManagementController::class, 'createFacultyHead'])->name('users.store.faculty_head');
            Route::get('/users/stats', [UserManagementController::class, 'getStats'])->name('users.stats');
            
            // Enrollments management 
            Route::get('/enrollments', [AdminEnrollmentController::class, 'index'])->name('admin.enrollments');
            
            // Enrollment API routes
            Route::prefix('enrollments')->name('admin.enrollments.')->group(function () {
                Route::get('/applications', [AdminEnrollmentController::class, 'getApplications'])->name('applications');
                Route::get('/applications/{id}', [AdminEnrollmentController::class, 'getApplication'])->name('application');
                Route::post('/applications/{id}/status', [AdminEnrollmentController::class, 'updateApplicationStatus'])->name('application.status');
                
                // Application actions
                Route::post('/applications/{id}/approve', [AdminEnrollmentController::class, 'approveApplication'])->name('application.approve');
                Route::post('/applications/{id}/decline', [AdminEnrollmentController::class, 'declineApplication'])->name('application.decline');
                Route::delete('/applications/{id}', [AdminEnrollmentController::class, 'deleteApplication'])->name('application.delete');
                
                // Bulk actions
                Route::post('/applications/bulk-approve', [AdminEnrollmentController::class, 'bulkApprove'])->name('applications.bulk-approve');
                Route::post('/applications/bulk-decline', [AdminEnrollmentController::class, 'bulkDecline'])->name('applications.bulk-decline');
                Route::post('/applications/bulk-delete', [AdminEnrollmentController::class, 'bulkDelete'])->name('applications.bulk-delete');
                
                Route::get('/documents', [AdminEnrollmentController::class, 'getDocuments'])->name('documents');
                Route::get('/applications/{applicationId}/documents', [AdminEnrollmentController::class, 'getApplicationDocuments'])->name('application.documents');
                Route::get('/documents/{enrolleeId}/{documentIndex}', [AdminEnrollmentController::class, 'getDocument'])->name('document');
                Route::get('/documents/{enrolleeId}/{documentIndex}/view', [AdminEnrollmentController::class, 'viewDocument'])->name('document.view');
                Route::get('/documents/{enrolleeId}/{documentIndex}/download', [AdminEnrollmentController::class, 'downloadDocument'])->name('document.download');
                Route::post('/documents/{enrolleeId}/{documentIndex}/status', [AdminEnrollmentController::class, 'updateDocumentStatus'])->name('document.status');
                
                Route::get('/appointments', [AdminEnrollmentController::class, 'getAppointments'])->name('appointments');
                Route::post('/applications/{applicationId}/appointment', [AdminEnrollmentController::class, 'updateAppointment'])->name('application.appointment');
        
                Route::get('/notices', [AdminEnrollmentController::class, 'getNotices'])->name('notices');
                Route::post('/notices', [AdminEnrollmentController::class, 'createNotice'])->name('notices.create');
                Route::post('/notices/bulk', [AdminEnrollmentController::class, 'sendBulkNotices'])->name('notices.bulk');
                Route::delete('/notices/{noticeId}', [AdminEnrollmentController::class, 'deleteNotice'])->name('notices.delete');
                
                Route::get('/export', [AdminEnrollmentController::class, 'export'])->name('export');
            });
            
            // Contact Messages Management
            Route::get('/contact-messages', [ContactController::class, 'adminIndex'])->name('admin.contact.messages');
            Route::get('/contact-messages/{message}', [ContactController::class, 'show'])->name('admin.contact.show');
            Route::post('/contact-messages/{message}/status', [ContactController::class, 'updateStatus'])->name('admin.contact.status');
            Route::delete('/contact-messages/{message}', [ContactController::class, 'destroy'])->name('admin.contact.destroy');
            Route::post('/contact-messages/bulk-action', [ContactController::class, 'bulkAction'])->name('admin.contact.bulk');
        });
    });
});
});

   


// First version of routes, keep it here and do not delete. Route::put('/enrollments/{id}', [AdminController::class, 'updateEnrollment'])->name('enrollments.update');


// Inside the auth middleware group
Route::middleware(['auth'])->group(function () {
    // Dashboard - accessible to all authenticated users
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    
    // User management - requires 'Manage users' permission
    Route::middleware(['permission:Manage users'])->group(function () {
      
        // Other user routes...
    });
    
    // Role management - requires 'Roles & Access' permission
    Route::middleware(['permission:Roles & Access'])->group(function () {
     
        // Other role routes...
    });
});



// Public Teacher Account Generator (no authentication required) Removed




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

// Teacher Counseling Recommendation Routes
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/recommend-counseling', [TeacherController::class, 'showRecommendForm'])
        ->name('recommend-counseling.form');
    Route::post('/recommend-counseling', [TeacherController::class, 'recommendToCounseling'])
        ->name('recommend-counseling');
});


// Discipline Portal Routes
Route::prefix('discipline')->name('discipline.')->group(function () {
    // Public routes
    Route::get('/login', [App\Http\Controllers\DisciplineController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\DisciplineController::class, 'login'])->name('login.submit');
    
    // Protected routes
    Route::middleware(['web'])->group(function () {
        // Dashboard
        Route::get('/', [App\Http\Controllers\DisciplineController::class, 'dashboard'])->name('dashboard');
        
        // Logout
        Route::post('/logout', [App\Http\Controllers\DisciplineController::class, 'logout'])->name('logout');
        
        // Student Management Routes
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [App\Http\Controllers\DisciplineController::class, 'studentsIndex'])
                ->name('index');

            Route::get('/search', [App\Http\Controllers\DisciplineController::class, 'searchStudents'])
                ->name('search');

            Route::get('/{student}', [App\Http\Controllers\DisciplineController::class, 'showStudent'])
                ->name('show');

            Route::get('/{student}/info', [App\Http\Controllers\DisciplineController::class, 'getStudentInfo'])
                ->name('info');
        });
        
        // Violations Management Routes
        Route::prefix('violations')->name('violations.')->group(function () {
            Route::get('/', [App\Http\Controllers\DisciplineController::class, 'violationsIndex'])
                ->name('index');

            Route::get('/summary', [App\Http\Controllers\DisciplineController::class, 'violationsSummary'])
                ->name('summary');

            Route::post('/', [App\Http\Controllers\DisciplineController::class, 'storeViolation'])
                ->name('store');

            Route::get('/{violation}', [App\Http\Controllers\DisciplineController::class, 'showViolation'])
                ->name('show');

            Route::get('/{violation}/edit', [App\Http\Controllers\DisciplineController::class, 'editViolation'])
                ->name('edit');

            Route::put('/{violation}', [App\Http\Controllers\DisciplineController::class, 'updateViolation'])
                ->name('update');

        Route::delete('/{violation}', [App\Http\Controllers\DisciplineController::class, 'destroyViolation'])
            ->name('destroy');

        // Forward violation to case meeting
        Route::post('/{violation}/forward', [App\Http\Controllers\DisciplineController::class, 'forwardViolation'])
            ->name('forward');
        });
    });
});

// Guidance Portal Routes (GuidanceController - for general guidance functionality)
Route::prefix('guidance')->name('guidance.')->group(function () {
    // Public routes
    Route::get('/login', [App\Http\Controllers\GuidanceController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\GuidanceController::class, 'login'])->name('login.submit');
    
    // Protected routes
    Route::middleware(['web'])->group(function () {
        // Dashboard
        Route::get('/', [App\Http\Controllers\GuidanceController::class, 'dashboard'])->name('dashboard');
        
        // Logout
        Route::post('/logout', [App\Http\Controllers\GuidanceController::class, 'logout'])->name('logout');
        
        // Case Meeting Routes
        Route::prefix('case-meetings')->name('case-meetings.')->group(function () {
            Route::get('/', [App\Http\Controllers\GuidanceController::class, 'caseMeetingsIndex'])
                ->name('index');

            Route::get('/{caseMeeting}', [App\Http\Controllers\GuidanceController::class, 'showCaseMeeting'])
                ->name('show');

            Route::get('/{caseMeeting}/edit', [App\Http\Controllers\GuidanceController::class, 'editCaseMeeting'])
                ->name('edit');

            Route::put('/{caseMeeting}', [App\Http\Controllers\GuidanceController::class, 'updateCaseMeeting'])
                ->name('update');

            Route::get('/export', [App\Http\Controllers\GuidanceController::class, 'exportCaseMeetings'])
                ->name('export');

            Route::post('/', [App\Http\Controllers\GuidanceController::class, 'scheduleCaseMeeting'])
                ->name('schedule');

            Route::post('/{caseMeeting}/complete', [App\Http\Controllers\GuidanceController::class, 'completeCaseMeeting'])
                ->name('complete');

            Route::post('/{caseMeeting}/summary', [App\Http\Controllers\GuidanceController::class, 'createCaseSummary'])
                ->name('summary');

            Route::post('/{caseMeeting}/forward', [App\Http\Controllers\GuidanceController::class, 'forwardToPresident'])
                ->name('forward');
        });
        
        // Counseling Session Routes
        Route::prefix('counseling-sessions')->name('counseling-sessions.')->group(function () {
            Route::get('/', [App\Http\Controllers\GuidanceController::class, 'counselingSessionsIndex'])->name('index');
            Route::post('/', [App\Http\Controllers\GuidanceController::class, 'scheduleCounselingSession'])->name('schedule');
            Route::get('/{counselingSession}', [App\Http\Controllers\GuidanceController::class, 'showCounselingSession'])->name('show');
            Route::get('/{counselingSession}/edit', [App\Http\Controllers\GuidanceController::class, 'editCounselingSession'])->name('edit');
            Route::put('/{counselingSession}', [App\Http\Controllers\GuidanceController::class, 'updateCounselingSession'])->name('update');
            Route::post('/{counselingSession}/complete', [App\Http\Controllers\GuidanceController::class, 'completeCounselingSession'])->name('complete');
            Route::post('/{counselingSession}/reschedule', [App\Http\Controllers\GuidanceController::class, 'rescheduleCounselingSession'])->name('reschedule');
 Route::post('/{id}/schedule-inline', [App\Http\Controllers\GuidanceController::class, 'scheduleInline'])
            ->name('schedule-inline');            Route::post('/{counselingSession}/schedule-recommended', [App\Http\Controllers\GuidanceController::class, 'scheduleRecommendedSession'])->name('schedule-recommended');
            Route::post('/{counselingSession}/summary', [App\Http\Controllers\GuidanceController::class, 'createCounselingSummary'])->name('summary');
            Route::get('/export', [App\Http\Controllers\GuidanceController::class, 'exportCounselingSessions'])->name('export');
            Route::get('/', [App\Http\Controllers\GuidanceController::class, 'counselingSessionsIndex'])
                ->name('index');

            Route::post('/', [App\Http\Controllers\GuidanceController::class, 'scheduleCounselingSession'])
                ->name('schedule');

            Route::post('/{counselingSession}/schedule-recommended', [App\Http\Controllers\GuidanceController::class, 'scheduleRecommendedSession'])
                ->name('schedule-recommended');

            Route::post('/{counselingSession}/summary', [App\Http\Controllers\GuidanceController::class, 'createCounselingSummary'])
                ->name('summary');
        });

        // API Routes
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/counselors', [App\Http\Controllers\GuidanceController::class, 'getCounselors'])
                ->name('counselors');
        });
    });
});

// Guidance & Discipline Portal Routes (GuidanceDisciplineController - for unified guidance/discipline system) Removed - Early version (sentimental)


// API Routes for Violations (ViolationController - for React Native/API access)
Route::prefix('api/violations')->name('api.violations.')->group(function () {
    Route::get('/', [App\Http\Controllers\ViolationController::class, 'index'])->name('index');
    Route::post('/check-duplicate', [App\Http\Controllers\ViolationController::class, 'checkDuplicate'])->name('check-duplicate');
    Route::post('/', [App\Http\Controllers\ViolationController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\ViolationController::class, 'show'])->name('show');
    Route::put('/{id}', [App\Http\Controllers\ViolationController::class, 'update'])->name('update');
    Route::get('/statistics/all', [App\Http\Controllers\ViolationController::class, 'statistics'])->name('statistics');
    Route::get('/student/{studentId}', [App\Http\Controllers\ViolationController::class, 'studentViolations'])->name('student');
});


// First version of routes, keep it here and do not delete. This is for early version and sentimental value purposes xD //
// Route::get('/teacher', [TeacherController::class, 'index']);
// Route::get('/admin', [adminController::class, 'adminindex']);
// Route::get('/admin/login', [adminController::class, 'adminlogin']);



// Student routes
Route::get('/student', [StudentController::class, 'index']);
Route::prefix('student')->name('student.')->group(function () {
    // Student login routes (public)
    Route::get('/login', [StudentController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [StudentController::class, 'login'])->name('login.submit');
 
    
    // Protected student routes
    Route::middleware('auth:student')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'index'])->name('dashboard');
        Route::get('/violations', [StudentController::class, 'violations'])->name('violations');
        
        // Enrollment routes
        Route::get('/enrollment', [StudentController::class, 'enrollment'])->name('enrollment');
        Route::post('/enrollment', [StudentController::class, 'submitEnrollment'])->name('enrollment.submit');
        
        // Subjects routes
        Route::get('/subjects', [StudentController::class, 'subjects'])->name('subjects');
        
        // Payments routes
        Route::get('/payments', [StudentController::class, 'payments'])->name('payments');
        Route::post('/payment/mode/update', [StudentController::class, 'updatePaymentMode'])->name('payment.mode.update');
        
        // Face registration routes
        Route::get('/face-registration', [StudentController::class, 'faceRegistration'])->name('face-registration');
        Route::post('/face-registration/save', [StudentController::class, 'saveFaceRegistration'])->name('face-registration.save');
        Route::delete('/face-registration/delete', [StudentController::class, 'deleteFaceRegistration'])->name('face-registration.delete');
        
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
        
        // Payment management
        Route::get('/payment', [EnrolleeController::class, 'payment'])->name('payment');
        
        // Document management
        Route::get('/documents', [EnrolleeController::class, 'documents'])->name('documents');
        Route::post('/documents/upload', [EnrolleeController::class, 'uploadDocument'])->name('documents.upload');
        Route::delete('/documents/delete', [EnrolleeController::class, 'deleteDocument'])->name('documents.delete');
        Route::get('/documents/view/{index}', [EnrolleeController::class, 'viewDocument'])->name('documents.view');
        Route::get('/documents/download/{index}', [EnrolleeController::class, 'downloadDocument'])->name('documents.download');
        
        // Schedule management
        Route::get('/schedule', [EnrolleeController::class, 'schedule'])->name('schedule');
        Route::put('/schedule', [EnrolleeController::class, 'updateSchedule'])->name('schedule.update');
        
        // Appointment management
        Route::post('/appointment/request', [EnrolleeController::class, 'requestAppointment'])->name('appointment.request');
        
        // Notices management
        Route::get('/notices', [EnrolleeController::class, 'notices'])->name('notices');
        Route::get('/notices/{id}', [EnrolleeController::class, 'getNotice'])->name('notices.get');
        Route::post('/notices/{id}/mark-read', [EnrolleeController::class, 'markNoticeAsRead'])->name('notices.mark-read');
        Route::post('/notices/mark-all-read', [EnrolleeController::class, 'markAllNoticesAsRead'])->name('notices.mark-all-read');
        
        // Profile management (redirects to application page since they're merged)
        Route::get('/profile', function() {
            return redirect()->route('enrollee.application');
        })->name('profile');
        Route::put('/profile', [EnrolleeController::class, 'updateProfile'])->name('profile.update');
        
        // Password management
        Route::put('/password/update', [EnrolleeController::class, 'updatePassword'])->name('password.update');
        
        // Pre-registration
        Route::post('/pre-register', [EnrolleeController::class, 'preRegister'])->name('pre-register');
        
        // Data Change Requests
        Route::post('/data-change-requests', [EnrolleeController::class, 'storeDataChangeRequest'])->name('data-change-requests.store');
        Route::get('/data-change-requests/{id}', [EnrolleeController::class, 'showDataChangeRequest'])->name('data-change-requests.show');
        Route::put('/data-change-requests/{id}', [EnrolleeController::class, 'updateDataChangeRequest'])->name('data-change-requests.update');
        Route::delete('/data-change-requests/{id}', [EnrolleeController::class, 'destroyDataChangeRequest'])->name('data-change-requests.destroy');
        
        // Logout
        Route::post('/logout', [EnrolleeController::class, 'logout'])->name('logout');
    });
});

// Registrar Authentication Routes
Route::prefix('registrar')->name('registrar.')->group(function () {
    // Test route (temporary)
    Route::get('/test', function() {
        return 'Registrar routes are working!';
    });
    
    // Login routes (guest only)
    Route::get('/login', function() {
        // Check if user is already authenticated as registrar
        if (Auth::guard('registrar')->check()) {
            return redirect()->route('registrar.dashboard');
        }
        return view('registrar.login');
    })->name('login')->middleware('guest:registrar');
        
    Route::post('/login', function(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);
        $remember = $request->boolean('remember');

        if (Auth::guard('registrar')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('registrar.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    })->middleware('guest:registrar');
    
    // Protected routes (authenticated registrar only)
    Route::middleware(['auth:registrar'])->group(function () {
        // Dashboard
        Route::get('/', [RegistrarController::class, 'dashboard'])->name('dashboard');
        
        // Applications management
        Route::get('/applications', [RegistrarController::class, 'applications'])->name('applications');
        Route::get('/applications/data', [RegistrarController::class, 'getApplicationsData'])->name('applications.data');
        Route::get('/applications/{id}', [RegistrarController::class, 'getApplication'])->name('applications.get');
        Route::post('/applications/{id}/approve', [RegistrarController::class, 'approveApplication'])->name('applications.approve');
        Route::post('/applications/{id}/decline', [RegistrarController::class, 'declineApplication'])->name('applications.decline');
        
        // Document management
        Route::get('/applications/{id}/documents', [RegistrarController::class, 'getApplicationDocuments'])->name('applications.documents');
        Route::post('/applications/{id}/documents/status', [RegistrarController::class, 'updateDocumentStatus'])->name('applications.documents.status');
        Route::get('/documents/view/{path}', [RegistrarController::class, 'serveDocument'])->name('documents.serve')->where('path', '.*');
        
        // Appointment management
        Route::post('/applications/{id}/appointment', [RegistrarController::class, 'scheduleAppointment'])->name('applications.appointment');
        Route::post('/applications/{id}/schedule', [RegistrarController::class, 'scheduleAppointment'])->name('applications.schedule');
        
        // Notice management
        Route::post('/applications/{id}/notice', [RegistrarController::class, 'sendNotice'])->name('applications.notice');
        
        // Bulk operations
        Route::post('/applications/bulk-approve', [RegistrarController::class, 'bulkApprove'])->name('applications.bulk-approve');
        Route::post('/applications/bulk-decline', [RegistrarController::class, 'bulkDecline'])->name('applications.bulk-decline');
        
        // Approved applications
        Route::get('/approved', [RegistrarController::class, 'approved'])->name('approved');
        Route::post('/applications/{id}/generate-credentials', [RegistrarController::class, 'generateStudentCredentials'])->name('applications.generate-credentials');
        
        // Appointments, Notices, and Documents data
        Route::get('/appointments', [RegistrarController::class, 'getAppointments'])->name('appointments.get');
        Route::get('/notices', [RegistrarController::class, 'getNotices'])->name('notices.get');
        Route::get('/documents', [RegistrarController::class, 'getAllDocuments'])->name('documents.get');
        
        // Appointment management
        Route::post('/appointments/{id}/approve', [RegistrarController::class, 'approveAppointment'])->name('appointments.approve');
        Route::post('/appointments/{id}/reject', [RegistrarController::class, 'rejectAppointment'])->name('appointments.reject');
        Route::post('/appointments/{id}/schedule', [RegistrarController::class, 'updateAppointmentSchedule'])->name('appointments.schedule');
        
        // Notice management
        Route::post('/notices/create', [RegistrarController::class, 'createNotice'])->name('notices.create');
        Route::put('/notices/{id}/update', [RegistrarController::class, 'updateNotice'])->name('notices.update');
        Route::post('/notices/bulk', [RegistrarController::class, 'sendBulkNotice'])->name('notices.bulk');
        Route::get('/notices/{id}', [RegistrarController::class, 'getNotice'])->name('notices.get.single');
        Route::get('/recipients/preview', [RegistrarController::class, 'previewRecipients'])->name('recipients.preview');
        
        // Reports
        Route::get('/reports', [RegistrarController::class, 'reports'])->name('reports');
        
        // Data Change Requests
        Route::get('/data-change-requests', [DataChangeRequestController::class, 'getDataChangeRequests'])->name('data-change-requests.index');
        Route::get('/data-change-requests/{id}', [DataChangeRequestController::class, 'getDataChangeRequest'])->name('data-change-requests.show');
        Route::post('/data-change-requests/{id}/process', [DataChangeRequestController::class, 'processDataChangeRequest'])->name('data-change-requests.process');
        
        // Test route for debugging
        Route::get('/test-data-change-requests', function() {
            $count = \App\Models\DataChangeRequest::count();
            $enrolleeCount = \App\Models\Enrollee::count();
            
            // Create a test data change request if none exist and there are enrollees
            if ($count === 0 && $enrolleeCount > 0) {
                $enrollee = \App\Models\Enrollee::first();
                \App\Models\DataChangeRequest::create([
                    'enrollee_id' => $enrollee->id,
                    'field_name' => 'first_name',
                    'old_value' => $enrollee->first_name,
                    'new_value' => 'Updated Name',
                    'reason' => 'Test data change request',
                    'status' => 'pending'
                ]);
                $count = 1;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Test route working',
                'total_requests' => $count,
                'total_enrollees' => $enrolleeCount,
                'auth_user' => auth()->guard('registrar')->user() ? auth()->guard('registrar')->user()->name : 'Not authenticated'
            ]);
        })->name('test-data-change-requests');
        
        // Logout
        Route::post('/logout', function(Request $request) {
            Auth::guard('registrar')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('registrar.login');
        })->name('logout');
    });
});

// ===== PAYMENT SCHEDULING ROUTES ===== 


// Student Payment Scheduling Routes
Route::middleware(['auth:student'])->prefix('student')->name('student.')->group(function () {
    Route::post('/payment-schedule', [PaymentScheduleController::class, 'createPaymentSchedule'])->name('payment-schedule.create');
    Route::get('/payment-schedule/{studentId}', [PaymentScheduleController::class, 'getPaymentSchedule'])->name('payment-schedule.get');
});

// Cashier Payment Management Routes (AJAX/API style)
Route::middleware(['auth:cashier'])->prefix('cashier/api')->name('cashier.api.')->group(function () {
    Route::get('/payment-schedules', [PaymentScheduleController::class, 'getAllPaymentSchedules'])->name('payment-schedules.all');
    Route::get('/payment-schedules/{paymentId}', [PaymentScheduleController::class, 'getPaymentDetails'])->name('payment-schedules.details');
    Route::get('/payment-schedules/student/{studentId}/{paymentMethod}', [PaymentScheduleController::class, 'getStudentPaymentSchedule'])->name('payment-schedules.student');
    Route::post('/payment-schedules/student/{studentId}/{paymentMethod}/process', [PaymentScheduleController::class, 'processStudentPaymentSchedule'])->name('payment-schedules.student.process');
    Route::get('/payment-schedules/pending', [PaymentScheduleController::class, 'getPendingPaymentSchedules'])->name('payment-schedules.pending');
    Route::get('/payment-schedules/due', [PaymentScheduleController::class, 'getDuePaymentSchedules'])->name('payment-schedules.due');
    Route::post('/payment-schedules/{paymentId}/process', [PaymentScheduleController::class, 'processPayment'])->name('payment-schedules.process');
    Route::get('/payment-statistics', [PaymentScheduleController::class, 'getPaymentStatistics'])->name('payment-statistics');
    Route::get('/payment-history', [PaymentScheduleController::class, 'getPaymentHistory'])->name('payment-history');
});

// ===== CASHIER ROUTES =====

// Cashier Authentication Routes
Route::prefix('cashier')->name('cashier.')->group(function () {
    // Guest routes (login form and process)
    Route::middleware('guest:cashier')->group(function () {
        Route::get('/login', [CashierController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [CashierController::class, 'login'])->name('login.submit');
    });

    // Protected routes (require cashier authentication)
    Route::middleware(['auth:cashier'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [CashierController::class, 'index'])->name('dashboard');
        
        // Payment Management
        Route::get('/pending-payments', [CashierController::class, 'pendingPayments'])->name('pending-payments');
        Route::get('/due-payments', [CashierController::class, 'duePayments'])->name('due-payments');
        Route::get('/completed-payments', [CashierController::class, 'completedPayments'])->name('completed-payments');
        Route::get('/payment-history', [CashierController::class, 'paymentHistory'])->name('payment-history');
        
        // Payment Actions
        Route::post('/payments/{payment}/confirm', [CashierController::class, 'confirmPayment'])->name('payments.confirm');
        Route::post('/payments/{payment}/reject', [CashierController::class, 'rejectPayment'])->name('payments.reject');
        Route::get('/payments/{payment}/details', [CashierController::class, 'getPaymentDetails'])->name('payments.details');
        
        // Fee Management
        Route::get('/fees', [CashierController::class, 'fees'])->name('fees');
        Route::get('/fees/create', [CashierController::class, 'createFee'])->name('fees.create');
        Route::post('/fees', [CashierController::class, 'storeFee'])->name('fees.store');
        Route::get('/fees/{fee}/edit', [CashierController::class, 'editFee'])->name('fees.edit');
        Route::put('/fees/{fee}', [CashierController::class, 'updateFee'])->name('fees.update');
        Route::delete('/fees/{fee}', [CashierController::class, 'destroyFee'])->name('fees.destroy');
        Route::post('/fees/{fee}/toggle', [CashierController::class, 'toggleFeeStatus'])->name('fees.toggle');
        
        // Reports
        Route::get('/reports', [CashierController::class, 'reports'])->name('reports');
        
        // Logout
        Route::post('/logout', [CashierController::class, 'logout'])->name('logout');
    });
});
