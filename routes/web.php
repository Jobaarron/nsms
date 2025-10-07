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
        });

        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

        // User Management - Use your custom permission middleware
        Route::middleware(['can:Manage Roles'])->group(function () {
            Route::get('/manage-users', [UserManagementController::class, 'index'])->name('admin.manage.users');
            Route::post('/assign-role', [AdminController::class, 'assignRole'])->name('admin.assign.role');
            Route::post('/remove-role', [AdminController::class, 'removeRole'])->name('admin.remove.role');
            Route::post('/create-role', [AdminController::class, 'createRole'])->name('admin.create.role');
            Route::put('/roles/{id}', [AdminController::class, 'updateRole'])->name('admin.update.role');
            Route::delete('/roles/{id}', [AdminController::class, 'deleteRole'])->name('admin.delete.role');
            Route::post('/create-permission', [AdminController::class, 'createPermission'])->name('admin.create.permission');
            Route::put('/permissions/{id}', [AdminController::class, 'updatePermission'])->name('admin.update.permission');
            Route::delete('/permissions/{id}', [AdminController::class, 'deletePermission'])->name('admin.delete.permission');
            Route::get('/users/{user}/roles', [AdminController::class, 'getUserRoles'])->name('admin.user.roles');

            // User Management
            Route::get('/user-management', [UserManagementController::class, 'index'])->name('admin.user.management');

            // User CRUD operations
            Route::get('/users/{id}', [UserManagementController::class, 'show'])->name('admin.users.show');
            Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('admin.users.update');
            Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
            Route::post('/users/admin', [UserManagementController::class, 'storeAdmin'])->name('admin.users.store.admin');
            Route::post('/users/teacher', [UserManagementController::class, 'storeTeacher'])->name('admin.users.store.teacher');
            Route::post('/users/guidance', [UserManagementController::class, 'storeGuidance'])->name('admin.users.store.guidance');
            Route::post('/users/discipline', [UserManagementController::class, 'storeDiscipline'])->name('admin.users.store.discipline');
            Route::post('/users/guidance-counselor', [UserManagementController::class, 'createGuidanceCounselor'])->name('admin.users.store.guidance_counselor');
            Route::post('/users/discipline-head', [UserManagementController::class, 'createDisciplineHead'])->name('admin.users.store.discipline_head');
            Route::post('/users/discipline-officer', [UserManagementController::class, 'createDisciplineOfficer'])->name('admin.users.store.discipline_officer');
            Route::post('/users/cashier', [UserManagementController::class, 'createCashier'])->name('admin.users.store.cashier');
            Route::post('/users/faculty-head', [UserManagementController::class, 'createFacultyHead'])->name('admin.users.store.faculty_head');
            Route::get('/users/stats', [UserManagementController::class, 'getStats'])->name('admin.users.stats');

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

// First version of routes, keep it here and do not delete. Route::put('/enrollments/{id}', [AdminController::class, 'updateEnrollment'])->name('enrollments.update');

// Inside the auth middleware group
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
});

// Public Teacher Account Generator
Route::get('/teacher-generator', [TeacherController::class, 'showGeneratorForm'])->name('teacher.generator');
Route::post('/teacher-generator', [TeacherController::class, 'generateTeacher'])->name('generate.teacher');

// Teacher Authentication Routes
Route::get('/teacher/login', [TeacherController::class, 'showLoginForm'])->name('teacher.login');
Route::post('/teacher/login', [TeacherController::class, 'login'])->name('teacher.login.submit');
Route::post('/teacher/logout', [TeacherController::class, 'logout'])->name('teacher.logout');

// Teacher Dashboard Route (protected)
Route::get('/teacher', [TeacherController::class, 'index'])
    ->name('teacher.dashboard')
    ->middleware(['auth', 'role:teacher']);

// Teacher Counseling Recommendation Routes
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/recommend-counseling', [TeacherController::class, 'showRecommendForm'])->name('recommend-counseling.form');
    Route::post('/recommend-counseling', [TeacherController::class, 'recommendToCounseling'])->name('recommend-counseling');
});

// Discipline Portal Routes
Route::prefix('discipline')->name('discipline.')->group(function () {
    Route::get('/login', [App\Http\Controllers\DisciplineController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\DisciplineController::class, 'login'])->name('login.submit');
    Route::middleware(['web'])->group(function () {
        Route::get('/', [App\Http\Controllers\DisciplineController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [App\Http\Controllers\DisciplineController::class, 'logout'])->name('logout');
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [App\Http\Controllers\DisciplineController::class, 'studentsIndex'])->name('index');
            Route::get('/search', [App\Http\Controllers\DisciplineController::class, 'searchStudents'])->name('search');
            Route::get('/{student}', [App\Http\Controllers\DisciplineController::class, 'showStudent'])->name('show');
            Route::get('/{student}/info', [App\Http\Controllers\DisciplineController::class, 'getStudentInfo'])->name('info');
        });
        Route::prefix('violations')->name('violations.')->group(function () {
            Route::get('/', [App\Http\Controllers\DisciplineController::class, 'violationsIndex'])->name('index');
            Route::get('/summary', [App\Http\Controllers\DisciplineController::class, 'violationsSummary'])->name('summary');
            Route::post('/', [App\Http\Controllers\DisciplineController::class, 'storeViolation'])->name('store');
            Route::get('/{violation}', [App\Http\Controllers\DisciplineController::class, 'showViolation'])->name('show');
            Route::get('/{violation}/edit', [App\Http\Controllers\DisciplineController::class, 'editViolation'])->name('edit');
            Route::put('/{violation}', [App\Http\Controllers\DisciplineController::class, 'updateViolation'])->name('update');
            Route::delete('/{violation}', [App\Http\Controllers\DisciplineController::class, 'destroyViolation'])->name('destroy');
            Route::post('/{violation}/forward', [App\Http\Controllers\DisciplineController::class, 'forwardViolation'])->name('forward');
        });
    });
});

// Guidance Portal Routes
Route::prefix('guidance')->name('guidance.')->group(function () {
    Route::get('/login', [App\Http\Controllers\GuidanceController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\GuidanceController::class, 'login'])->name('login.submit');
    Route::middleware(['web', 'auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\GuidanceController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [App\Http\Controllers\GuidanceController::class, 'logout'])->name('logout');
        Route::prefix('case-meetings')->name('case-meetings.')->group(function () {
            Route::get('/', [App\Http\Controllers\GuidanceController::class, 'caseMeetingsIndex'])->name('index');
            Route::get('/{caseMeeting}', [App\Http\Controllers\GuidanceController::class, 'showCaseMeeting'])->name('show');
            Route::get('/{caseMeeting}/edit', [App\Http\Controllers\GuidanceController::class, 'editCaseMeeting'])->name('edit');
            Route::put('/{caseMeeting}', [App\Http\Controllers\GuidanceController::class, 'updateCaseMeeting'])->name('update');
            Route::get('/export', [App\Http\Controllers\GuidanceController::class, 'exportCaseMeetings'])->name('export');
            Route::post('/', [App\Http\Controllers\GuidanceController::class, 'scheduleCaseMeeting'])->name('schedule');
            Route::post('/{caseMeeting}/complete', [App\Http\Controllers\GuidanceController::class, 'completeCaseMeeting'])->name('complete');
            Route::post('/{caseMeeting}/summary', [App\Http\Controllers\GuidanceController::class, 'createCaseSummary'])->name('summary');
            Route::post('/{caseMeeting}/forward', [App\Http\Controllers\GuidanceController::class, 'forwardToPresident'])->name('forward');
        });
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
        });
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/counselors', [App\Http\Controllers\GuidanceController::class, 'getCounselors'])->name('counselors');
        });
    });
});

// Student routes
Route::get('/student', [StudentController::class, 'index']);
Route::prefix('student')->name('student.')->group(function () {
    Route::get('/login', [StudentController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [StudentController::class, 'login'])->name('login.submit');
    Route::middleware('auth:student')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'index'])->name('dashboard');
        Route::get('/violations', [StudentController::class, 'violations'])->name('violations');
        Route::get('/enrollment', [StudentController::class, 'enrollment'])->name('enrollment');
        Route::post('/enrollment', [StudentController::class, 'submitEnrollment'])->name('enrollment.submit');
        Route::get('/subjects', [StudentController::class, 'subjects'])->name('subjects');
        Route::get('/payments', [StudentController::class, 'payments'])->name('payments');
        Route::post('/payment/mode/update', [StudentController::class, 'updatePaymentMode'])->name('payment.mode.update');
        Route::get('/face-registration', [StudentController::class, 'faceRegistration'])->name('face-registration');
        Route::post('/face-registration/save', [StudentController::class, 'saveFaceRegistration'])->name('face-registration.save');
        Route::delete('/face-registration/delete', [StudentController::class, 'deleteFaceRegistration'])->name('face-registration.delete');
        Route::post('/logout', [StudentController::class, 'logout'])->name('logout');
    });
});

// Enrollee routes
Route::prefix('enrollee')->name('enrollee.')->group(function () {
    Route::get('/login', [EnrolleeController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [EnrolleeController::class, 'login'])->name('login.submit');
    Route::middleware('auth:enrollee')->group(function () {
        Route::get('/dashboard', [EnrolleeController::class, 'index'])->name('dashboard');
        Route::post('/logout', [EnrolleeController::class, 'logout'])->name('logout');
        Route::get('/requirements', [EnrolleeController::class, 'requirements'])->name('requirements');
        Route::post('/requirements', [EnrolleeController::class, 'submitRequirements'])->name('requirements.submit');
        Route::get('/enrollment-status', [EnrolleeController::class, 'enrollmentStatus'])->name('enrollment-status');
        Route::get('/notices', [EnrolleeController::class, 'notices'])->name('notices');
        Route::get('/schedule', [EnrolleeController::class, 'schedule'])->name('schedule');
        Route::get('/appointment', [EnrolleeController::class, 'appointment'])->name('appointment');
    });
});

// Registrar routes
Route::prefix('registrar')->name('registrar.')->group(function () {
    Route::get('/login', [RegistrarController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [RegistrarController::class, 'login'])->name('login.submit');
    Route::middleware(['auth:registrar'])->group(function () {
        Route::get('/dashboard', [RegistrarController::class, 'index'])->name('dashboard');
        Route::get('/students', [RegistrarController::class, 'students'])->name('students');
        Route::get('/student/{id}', [RegistrarController::class, 'viewStudent'])->name('student.view');
        Route::get('/documents', [RegistrarController::class, 'documents'])->name('documents');
        Route::get('/api/students', [RegistrarController::class, 'getStudents'])->name('api.students');
        Route::post('/logout', [RegistrarController::class, 'logout'])->name('logout');
    });
});

// ===== PAYMENT SCHEDULING ROUTES =====
Route::prefix('payment-schedule')->name('payment-schedule.')->group(function () {
    Route::get('/', [PaymentScheduleController::class, 'index'])->name('index');
    Route::post('/', [PaymentScheduleController::class, 'store'])->name('store');
    Route::get('/{id}', [PaymentScheduleController::class, 'show'])->name('show');
    Route::put('/{id}', [PaymentScheduleController::class, 'update'])->name('update');
    Route::delete('/{id}', [PaymentScheduleController::class, 'destroy'])->name('destroy');
});

