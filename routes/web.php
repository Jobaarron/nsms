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
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\PaymentScheduleController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\DataChangeRequestController;
use App\Http\Controllers\PdfController;




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

// AJAX file upload routes for enrollment
Route::post('/enroll/upload-temp-file', [EnrollmentController::class, 'uploadTempFile'])
     ->name('enroll.upload.temp');
Route::delete('/enroll/delete-temp-file/{fileId}', [EnrollmentController::class, 'deleteTempFile'])
     ->name('enroll.delete.temp');
Route::get('/enroll/get-temp-files', [EnrollmentController::class, 'getTempFiles'])
     ->name('enroll.get.temp');

// Contact form routes
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');



// Admin Generator (accessible without login for initial setup) Removed


// Admin routes
Route::prefix('admin')->group(function () {
    // Admin login routes (public)
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.submit');
    
    // Protected admin routes - use auth middleware
    Route::middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
        
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [AdminController::class, 'getStats'])->name('dashboard.stats');
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

        // Forwarded Case Meetings for President (Admin)
        Route::get('/forwarded-cases', [AdminController::class, 'forwardedCases'])->name('forwarded.cases');

        // Sanction actions for forwarded cases
        Route::post('/sanctions/{sanction}/approve', [AdminController::class, 'approveSanction'])->name('sanctions.approve');
        Route::post('/sanctions/{sanction}/reject', [AdminController::class, 'rejectSanction'])->name('sanctions.reject');
        Route::post('/sanctions/{sanction}/revise', [AdminController::class, 'reviseSanction'])->name('sanctions.revise');

        // View summary report for case meeting
        Route::get('/case-meetings/{caseMeeting}/summary', [AdminController::class, 'viewSummaryReport'])->name('case-meetings.summary');

    // Route to download Disciplinary Conference Report PDF for a specific case meeting
    Route::get('/case-meetings/{caseMeeting}/disciplinary-conference-report/pdf', [PdfController::class, 'DisciplinaryConReports'])->name('case-meetings.disciplinary-conference-report.pdf');
        
    
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

            
        // Contact Messages Management
        Route::get('/contact-messages', [ContactController::class, 'adminIndex'])->name('contact.messages');
        Route::get('/contact-messages/{message}', [ContactController::class, 'show'])->name('contact.show');
        Route::post('/contact-messages/{message}/status', [ContactController::class, 'updateStatus'])->name('contact.status');
        Route::delete('/contact-messages/{message}', [ContactController::class, 'destroy'])->name('contact.destroy');
        Route::post('/contact-messages/bulk-action', [ContactController::class, 'bulkAction'])->name('contact.bulk');
            
        });
    });
});

   


// First version of routes, keep it here and do not delete. Route::put('/enrollments/{id}', [AdminController::class, 'updateEnrollment'])->name('enrollments.update');


// Inside the auth middleware group
Route::middleware(['auth'])->group(function () {
    // Dashboard - accessible to all authenticated users (duplicate removed)
    // Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    // Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    
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

// Teacher Dashboard AJAX Routes
Route::get('/teacher/dashboard/stats', [TeacherController::class, 'getDashboardStats'])
    ->name('teacher.dashboard.stats')
    ->middleware(['auth', 'role:teacher']);


Route::get('/teacher/grades', [App\Http\Controllers\TeacherGradeController::class, 'index'])
    ->name('teacher.grades')
    ->middleware(['auth', 'role:teacher|faculty_head']);

// Teacher Counseling Recommendation Routes
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/recommend-counseling', [TeacherController::class, 'showRecommendForm'])
        ->name('recommend-counseling.form');
    Route::post('/recommend-counseling', [TeacherController::class, 'recommendToCounseling'])
        ->name('recommend-counseling');


    // Route for the Teacher Observation Report page
    Route::get('/observationreport', [TeacherController::class, 'showObservationReport'])
        ->name('observationreport');

    // Route to serve the dynamic teacher observation report PDF
    Route::get('/observationreport/pdf/{caseMeeting}', [App\Http\Controllers\PdfController::class, 'teacherObservationReportPdf'])
        ->name('observationreport.pdf');

    // Teacher Observation Report: Teacher Reply (update case meeting)
    Route::post('/observationreport/reply/{caseMeeting}', [App\Http\Controllers\TeacherController::class, 'submitObservationReply'])
        ->name('observationreport.reply');

    // Teacher Advisory Routes
    Route::get('/advisory', [App\Http\Controllers\TeacherAdvisoryController::class, 'advisory'])
        ->name('advisory');
        
    // Teacher Advisory AJAX Routes
    Route::get('/advisory/student/{student}/grades', [App\Http\Controllers\TeacherAdvisoryController::class, 'getStudentGrades'])
        ->name('advisory.student.grades');
    Route::get('/advisory/all-grades', [App\Http\Controllers\TeacherAdvisoryController::class, 'getAllAdvisoryGrades'])
        ->name('advisory.all-grades');
    Route::get('/advisory/student/{student}/report-card', [App\Http\Controllers\TeacherAdvisoryController::class, 'generateStudentReportCard'])
        ->name('advisory.student.report-card');
    Route::get('/advisory/all-report-cards', [App\Http\Controllers\TeacherAdvisoryController::class, 'generateAllReportCards'])
        ->name('advisory.all-report-cards');
});


// Teacher Grade Routes (Extended functionality)
Route::middleware(['auth', 'role:teacher|faculty_head'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/grades/create', [App\Http\Controllers\TeacherGradeController::class, 'create'])
        ->name('grades.create');
    Route::post('/grades', [App\Http\Controllers\TeacherGradeController::class, 'store'])
        ->name('grades.store');
    Route::get('/grades/{submission}/edit', [App\Http\Controllers\TeacherGradeController::class, 'edit'])
        ->name('grades.edit');
    Route::put('/grades/{submission}', [App\Http\Controllers\TeacherGradeController::class, 'update'])
        ->name('grades.update');
    Route::delete('/grades/{submission}', [App\Http\Controllers\TeacherGradeController::class, 'destroy'])
        ->name('grades.destroy');
    
    // Check grade submission status
    Route::get('/check-submission-status', [App\Http\Controllers\TeacherController::class, 'checkSubmissionStatus'])
        ->name('check-submission-status');
    
    // Grade entry form for specific assignment
    Route::get('/grades/submit/{assignment}', [App\Http\Controllers\TeacherGradeController::class, 'showGradeEntry'])
        ->name('grades.submit');
    Route::post('/grades/submit/{assignment}', [App\Http\Controllers\TeacherGradeController::class, 'submitGrades'])
        ->name('grades.submit.store');
    Route::get('/grades/{submission}/data', [App\Http\Controllers\TeacherGradeController::class, 'getSubmissionData'])
        ->name('grades.data');
    Route::get('/grades/stats', [App\Http\Controllers\TeacherGradeController::class, 'getSubmissionStats'])
        ->name('grades.stats');
});

// Student Schedule Routes (requires payment)
Route::middleware(['auth:student', 'student.payment'])->prefix('student')->name('student.')->group(function () {
    Route::get('/schedule', [App\Http\Controllers\StudentScheduleController::class, 'index'])
        ->name('schedule.index');
    Route::get('/schedule/calendar', [App\Http\Controllers\StudentScheduleController::class, 'weeklyCalendar'])
        ->name('schedule.calendar');
    Route::get('/schedule/data', [App\Http\Controllers\StudentScheduleController::class, 'getScheduleData'])
        ->name('schedule.data');
});

// Student Grade Routes (requires payment)
Route::middleware(['auth:student', 'student.payment'])->prefix('student')->name('student.')->group(function () {
    Route::get('/grades', [App\Http\Controllers\StudentGradeController::class, 'index'])
        ->name('grades.index');
    Route::get('/grades/{quarter}', [App\Http\Controllers\StudentGradeController::class, 'quarter'])
        ->name('grades.quarter');
    Route::get('/grades/{quarter}/details', [App\Http\Controllers\StudentGradeController::class, 'details'])
        ->name('grades.details');
    Route::get('/grades/report/{quarter?}', [App\Http\Controllers\StudentGradeController::class, 'report'])
        ->name('grades.report');
});

// Faculty Head Authentication Routes
Route::get('/faculty-head/login', [App\Http\Controllers\FacultyHeadController::class, 'showLoginForm'])
    ->name('faculty-head.login');
Route::post('/faculty-head/login', [App\Http\Controllers\FacultyHeadController::class, 'login'])
    ->name('faculty-head.login.submit');
Route::post('/faculty-head/logout', [App\Http\Controllers\FacultyHeadController::class, 'logout'])
    ->name('faculty-head.logout');

// Faculty Head Routes (Protected by web guard with role check)
Route::middleware(['auth', 'role:faculty_head'])->prefix('faculty-head')->name('faculty-head.')->group(function () {
    Route::get('/', [App\Http\Controllers\FacultyHeadController::class, 'index'])
        ->name('dashboard');
    
    // Unified Faculty Assignments
    Route::get('/assign-faculty', [App\Http\Controllers\FacultyHeadController::class, 'assignFaculty'])
        ->name('assign-faculty');
    
    // Assign adviser per class
    Route::get('/assign-adviser', [App\Http\Controllers\FacultyHeadController::class, 'assignAdviser'])
        ->name('assign-adviser');
    Route::post('/assign-adviser', [App\Http\Controllers\FacultyHeadController::class, 'storeAdviser'])
        ->name('assign-adviser.store');
    
    // Assign teacher per subject/section
    Route::get('/assign-teacher', [App\Http\Controllers\FacultyHeadController::class, 'assignTeacherForm'])
        ->name('assign-teacher');
    Route::post('/assign-teacher', [App\Http\Controllers\FacultyHeadController::class, 'storeTeacherAssignment'])
        ->name('assign-teacher.store');
    
    // Remove assignment (both teacher and adviser)
    Route::delete('/remove-assignment/{assignment}', [App\Http\Controllers\FacultyHeadController::class, 'removeAssignment'])
        ->name('remove-assignment');
    
    // Get subjects for AJAX filtering
    Route::get('/get-subjects', [App\Http\Controllers\FacultyHeadController::class, 'getSubjects'])
        ->name('get-subjects');
    
    // Get sections for AJAX filtering
    Route::get('/get-sections', [App\Http\Controllers\FacultyHeadController::class, 'getSections'])
        ->name('get-sections');
    
    // Get section details (students and adviser)
    Route::get('/get-section-details', [App\Http\Controllers\FacultyHeadController::class, 'getSectionDetails'])
        ->name('get-section-details');
    
    
    // View submitted grades from teachers
    Route::get('/view-grades', [App\Http\Controllers\FacultyHeadController::class, 'viewGrades'])
        ->name('view-grades');
    
    // Approve/reject submitted grades from teachers
    Route::get('/approve-grades', [App\Http\Controllers\FacultyHeadController::class, 'approveGrades'])
        ->name('approve-grades');
    Route::post('/approve-grades/{submission}', [App\Http\Controllers\FacultyHeadController::class, 'approveSubmission'])
        ->name('approve-grades.approve');
    Route::post('/reject-grades/{submission}', [App\Http\Controllers\FacultyHeadController::class, 'rejectSubmission'])
        ->name('approve-grades.reject');
    
    // Activate grade submission
    Route::get('/activate-submission', [App\Http\Controllers\FacultyHeadController::class, 'activateSubmission'])
        ->name('activate-submission');
    Route::post('/activate-submission/toggle', [App\Http\Controllers\FacultyHeadController::class, 'toggleGradeSubmissionStatus'])
        ->name('activate-submission.toggle');
    Route::post('/activate-submission/quarter', [App\Http\Controllers\FacultyHeadController::class, 'updateQuarterSettings'])
        ->name('activate-submission.quarter');
    
    // API endpoint for checking grade submission status (used by teacher views)
    Route::get('/api/grade-submission-status', [App\Http\Controllers\FacultyHeadController::class, 'getGradeSubmissionStatus'])
        ->name('api.grade-submission-status');
    
    // API endpoint for getting subjects by grade level (used by assign teacher form)
    Route::get('/api/subjects-by-grade', [App\Http\Controllers\FacultyHeadController::class, 'getSubjectsByGrade'])
        ->name('api.subjects-by-grade');
});


// Discipline Portal Routes
Route::prefix('discipline')->name('discipline.')->group(function () {
    // Public routes
    Route::get('/login', [App\Http\Controllers\DisciplineController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\DisciplineController::class, 'login'])->name('login.submit');


    // AJAX: Minor/Major violation stats for pie chart
    Route::get('/minor-major-violation-stats', [App\Http\Controllers\DisciplineController::class, 'getMinorMajorViolationStats'])->name('minor-major-violation-stats');
    // AJAX: Monthly bar chart stats
    Route::get('/violation-bar-stats', [App\Http\Controllers\DisciplineController::class, 'getViolationBarStats'])->name('violation-bar-stats');
    // AJAX: Case status pie chart (pending, ongoing, completed)
    Route::get('/case-status-stats', [App\Http\Controllers\DisciplineController::class, 'getCaseStatusStats'])->name('case-status-stats');

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
    // API: Minor/Major violation stats for pie chart
    Route::get('/minor-major-violation-stats', [App\Http\Controllers\GuidanceController::class, 'getMinorMajorViolationStats'])->name('minor-major-violation-stats');
    
    // Protected routes
    
    Route::middleware(['web'])->group(function () {
        // Dashboard
        Route::get('/', [App\Http\Controllers\GuidanceController::class, 'dashboard'])->name('dashboard');
            // API: Case status stats for dashboard pie chart
            Route::get('/case-status-stats', [App\Http\Controllers\GuidanceController::class, 'getCaseStatusStats'])->name('case-status-stats');
            // API: Closed cases per month for bar chart
            Route::get('/closed-cases-stats', [App\Http\Controllers\GuidanceController::class, 'getClosedCasesStats'])->name('closed-cases-stats');
            // API: Counseling sessions per month for bar chart
            Route::get('/counseling-sessions-stats', [App\Http\Controllers\GuidanceController::class, 'getCounselingSessionsStats'])->name('counseling-sessions-stats');
            // API: Discipline vs total students for histogram
            Route::get('/discipline-vs-total-stats', [App\Http\Controllers\GuidanceController::class, 'getDisciplineVsTotalStats'])->name('discipline-vs-total-stats');
            // API: Weekly violation list for dashboard
            Route::get('/weekly-violations', [App\Http\Controllers\GuidanceController::class, 'getWeeklyViolations'])->name('weekly-violations');

            Route::get('/top-cases', [App\Http\Controllers\GuidanceController::class, 'getTopCases']);        // Logout
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
        // API route to fetch all unique sanctions for dropdown (Guidance)
      Route::get('/api/sanctions/list', [App\Http\Controllers\GuidanceController::class, 'sanctionList'])->name('api.sanctions.list');
        // Overall Disciplinary Conference Summary Report (all students)
        Route::get('/conference-summary-report/pdf', [PdfController::class, 'conferenceSummaryReportAllPdf'])->name('pdf.conference-summary-report.all');

    Route::get('/observationreport/pdf/{caseMeeting}', [App\Http\Controllers\PdfController::class, 'teacherObservationReportPdf']);
        // PDF route for case meeting attachment (moved outside case-meetings group)
        Route::get('/pdf/case-meeting/{caseMeetingId}', [PdfController::class, 'caseMeetingAttachmentPdf'])->name('pdf.case-meeting.attachment');
        
        // Counseling Session Routes
    Route::prefix('counseling-sessions')->name('counseling-sessions.')->group(function () {

    // AJAX: Store counseling summary report
    Route::post('/{counselingSession}/summary-report', [App\Http\Controllers\GuidanceController::class, 'createCounselingSummaryReport'])->name('summary-report');

    // Counseling session detail API for modal (now inside counseling-sessions group)
    Route::get('/api/counseling-sessions/{id}', [App\Http\Controllers\GuidanceController::class, 'apiShowCounselingSession'])->middleware(['auth'])->name('api.show');
        
            // AJAX: Reject counseling session with feedback and archive
            Route::post('/{counselingSession}/reject-with-feedback', [App\Http\Controllers\GuidanceController::class, 'rejectCounselingSessionWithFeedback'])->name('reject-with-feedback');
            // Show create summary form
            Route::get('/{counselingSession}/summary/create', [App\Http\Controllers\GuidanceController::class, 'createCounselingSummaryForm'])->name('summary.create');
              // Approve counseling session (AJAX)
              Route::post('/approve', [App\Http\Controllers\GuidanceController::class, 'approveCounselingSession'])->name('approve');
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
                
// AJAX route for counseling session approval
Route::post('/guidance/counseling-sessions/approve', [GuidanceController::class, 'approveCounselingSession'])->name('guidance.counseling-sessions.approve');


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
        
        // Enrollment routes (always accessible for completing enrollment)
        Route::get('/enrollment', [StudentController::class, 'enrollment'])->name('enrollment');
        Route::post('/enrollment', [StudentController::class, 'submitEnrollment'])->name('enrollment.submit');
        
        Route::post('/logout', [StudentController::class, 'logout'])->name('logout');
        
        // Payments routes (requires payment settlement - same as other features)
        Route::middleware('student.payment')->group(function () {
            Route::get('/payments', [StudentController::class, 'payments'])->name('payments');
            Route::post('/payment/mode/update', [StudentController::class, 'updatePaymentMode'])->name('payment.mode.update');
        });
        
        // Routes that require payment settlement and enrollment completion
        Route::middleware('student.payment')->group(function () {
            // Subjects routes (requires payment)
            Route::get('/subjects', [StudentController::class, 'subjects'])->name('subjects');
            
            // Violations routes (requires payment)
            Route::match(['get', 'post'], '/violations', [StudentController::class, 'violations'])->name('violations');
            Route::post('/violations/reply/{violation}', [StudentController::class, 'submitViolationReply'])->name('violations.reply');

            // Student Narrative Report - Reply Form (requires payment)
            Route::get('/narrative-report/reply', function() {
                return view('student.narrative_report');
            })->name('narrative.reply');
            
            // Face registration routes (requires payment)
            Route::get('/face-registration', [StudentController::class, 'faceRegistration'])->name('face-registration');
            Route::post('/face-registration/save', [StudentController::class, 'saveFaceRegistration'])->name('face-registration.save');
            Route::delete('/face-registration/delete', [StudentController::class, 'deleteFaceRegistration'])->name('face-registration.delete');
        });
    });
});

// Student Narrative Report - View PDF Only (always available)
Route::get('/narrative-report/view/{studentId}/{violationId}', [PdfController::class, 'studentNarrativePdf'])->name('student.pdf.studentNarrative');


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
        
        
        // Notice management
        Route::post('/applications/{id}/notice', [RegistrarController::class, 'sendNotice'])->name('applications.notice');
        
        // Bulk operations
        Route::post('/applications/bulk-approve', [RegistrarController::class, 'bulkApprove'])->name('applications.bulk-approve');
        Route::post('/applications/bulk-decline', [RegistrarController::class, 'bulkDecline'])->name('applications.bulk-decline');
        
        // Approved applications
        Route::get('/approved', [RegistrarController::class, 'approved'])->name('approved');
        Route::post('/applications/{id}/generate-credentials', [RegistrarController::class, 'generateStudentCredentials'])->name('applications.generate-credentials');
        
        // Notices and Documents data
        Route::get('/notices', [RegistrarController::class, 'getNotices'])->name('notices.get');
        Route::get('/documents', [RegistrarController::class, 'getAllDocuments'])->name('documents.get');
        
        
        // Notice management
        Route::post('/notices/create', [RegistrarController::class, 'createNotice'])->name('notices.create');
        Route::put('/notices/{id}/update', [RegistrarController::class, 'updateNotice'])->name('notices.update');
        Route::post('/notices/bulk', [RegistrarController::class, 'sendBulkNotice'])->name('notices.bulk');
        Route::get('/notices/{id}', [RegistrarController::class, 'getNotice'])->name('notices.get.single');
        Route::get('/recipients/preview', [RegistrarController::class, 'previewRecipients'])->name('recipients.preview');
        
        
        // Class Lists Management
        Route::get('/class-lists', [RegistrarController::class, 'classLists'])->name('class-lists');
        Route::get('/class-lists/get-strands', [RegistrarController::class, 'getClassListStrands'])->name('class-lists.get-strands');
        Route::get('/class-lists/get-tracks', [RegistrarController::class, 'getClassListTracks'])->name('class-lists.get-tracks');
        Route::get('/class-lists/get-sections', [RegistrarController::class, 'getClassListSections'])->name('class-lists.get-sections');
        
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
            // Clear all authentication guards to prevent session conflicts
            Auth::guard('registrar')->logout();
            Auth::guard('web')->logout();
            Auth::guard('enrollee')->logout();
            Auth::guard('student')->logout();
            
            // Completely clear the session
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->flush();
            
            return redirect()->route('registrar.login')->with('success', 'Logged out successfully');
        })->name('logout');
    });
});

// ===== PAYMENT SCHEDULING ROUTES ===== 


// Student Payment Scheduling Routes (requires payment settlement - same as other features)
Route::middleware(['auth:student', 'student.payment'])->prefix('student')->name('student.')->group(function () {
    Route::post('/payment-schedule', [PaymentScheduleController::class, 'createPaymentSchedule'])->name('payment-schedule.create');
    Route::get('/payment-schedule/{studentId}', [PaymentScheduleController::class, 'getPaymentSchedule'])->name('payment-schedule.get');
});

// Cashier Payment Management Routes (AJAX/API style)
Route::middleware(['auth:cashier'])->prefix('cashier/api')->name('cashier.api.')->group(function () {
    Route::get('/payment-schedules', [PaymentScheduleController::class, 'getAllPaymentSchedules'])->name('payment-schedules.all');
    Route::get('/payment-schedules/{paymentId}', [PaymentScheduleController::class, 'getPaymentDetails'])->name('payment-schedules.details');
    Route::get('/payment-schedules/student/{studentId}/{paymentMethod}', [PaymentScheduleController::class, 'getStudentPaymentSchedule'])->name('payment-schedules.student');
    Route::post('/payment-schedules/student/{studentId}/{paymentMethod}/process', [PaymentScheduleController::class, 'processStudentPaymentSchedule'])->name('payment-schedules.student.process');
    Route::post('/payment-schedules/individual/{paymentId}/process', [PaymentScheduleController::class, 'processIndividualPayment'])->name('payment-schedules.individual.process');
    Route::get('/payment-statistics', [PaymentScheduleController::class, 'getPaymentStatistics'])->name('payment-statistics');
    Route::get('/payment-history', [PaymentScheduleController::class, 'getPaymentHistory'])->name('payment-history');
    Route::get('/pending-payment-schedules', [PaymentScheduleController::class, 'getPendingPaymentSchedules'])->name('pending-payment-schedules');
    Route::get('/due-payment-schedules', [PaymentScheduleController::class, 'getDuePaymentSchedules'])->name('due-payment-schedules');
    Route::get('/pdf/cashier-receipt', [PdfController::class, 'showCashierReceipt'])->name('pdf.cashier-receipt');
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
        Route::get('/payments', [CashierController::class, 'payments'])->name('payments');
        Route::get('/payment-archives', [CashierController::class, 'paymentArchives'])->name('payment-archives');
        Route::get('/api/payment-archives', [CashierController::class, 'getPaymentArchivesData'])->name('api.payment-archives');
        
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
        
        
        // Logout
        Route::post('/logout', [CashierController::class, 'logout'])->name('logout');
    });
});

// PDF route for counseling session
Route::get('/pdf/counseling-session', [PdfController::class, 'show']);

// PDF Receipt (dynamic overlay)
Route::get('/pdf/receipt', [PdfController::class, 'showReceipt'])->name('pdf.receipt');
