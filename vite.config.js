import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/css/landingpage.css',
                'resources/css/enroll.css',
                'resources/css/login_admin.css',
                'resources/css/login_registrar.css',
                'resources/css/roles_access.css',
                'resources/css/index_admin.css',
                'resources/css/email.css',
                'resources/css/admin_generator.css',
                'resources/css/manage_users.css',
                'resources/css/index_student.css',
                'resources/css/index_cashier.css',
                'resources/css/index_enrollee.css',
                'resources/css/index_registrar.css',
                'resources/css/enrollee-documents.css',
                'resources/css/index_guidance.css',
                'resources/css/index_teacher.css',
                'resources/css/index_faculty_head.css',
                'resources/css/admin_contact_messages.css',
                'resources/css/password-field.css',
                'resources/js/guidance_student-profile.js',
                'resources/css/guidance_student-profile.css',
                'resources/js/guidance_student-violations.js',
                'resources/css/guidance_student-violations.css',
                'resources/css/student_violations.css',
                'resources/js/landingpage.js',
                'resources/js/enroll.js',
                'resources/js/enrollee-documents.js',
                'resources/js/enrollee-payment.js',
                'resources/js/enrollee-notices.js',
                'resources/js/enrollee-application.js',
                'resources/js/admin-role-access.js',
                'resources/js/role-modals.js',

                'resources/js/user-management.js',
                'resources/js/admin-enrollment-management.js',
                'resources/css/admin-enrollment-management.css',
                'resources/js/enrollee-index.js',
                'resources/js/registrar-applications.js',
                'resources/js/registrar-approved.js',
                'resources/js/registrar-reports.js',
                'resources/css/index_discipline.css',
                'resources/js/discipline_violations.js',
                'resources/js/discipline_student-profile.js',
                'resources/css/index_guidance.css',
                'resources/js/guidance_case-meetings.js',
                'resources/js/guidance_counseling-sessions.js',
                'resources/js/student-subjects.js',
                'resources/js/student-enrollment.js',
                'resources/js/cashier-payment-schedules.js',
                'resources/js/cashier-fees.js',
                'resources/js/enrollee-data-change-requests.js',
                'resources/js/registrar-data-change-requests.js',
                'resources/js/registrar-document-management.js',
                'resources/js/faculty-head-assign-teacher.js',
                'resources/js/faculty-head-activate-submission.js',
                'resources/js/faculty-head-dashboard.js',
                'resources/js/faculty-head-view-grades.js',
                'resources/js/faculty-head-approve-grades.js',
                'resources/js/grade-submission-checker.js',
                'resources/js/teacher-grades.js',
                'resources/js/teacher-dashboard.js',
                'resources/js/teacher-schedule.js',
                'resources/js/teacher-recommend-counseling.js',
                'resources/js/student-dashboard.js',
                'resources/js/student-grades.js',
                'resources/js/student-schedule.js',
                'resources/js/student-violation.js',
                'resources/js/face-registration.js',
                'resources/js/forwarded-cases.js',
                'resources/js/guidance-dashboard.js',
                'resources/js/cashier-dashboard.js',
                'resources/js/registrar-class-lists.js'
                
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            external: [],
            output: {
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name && assetInfo.name.endsWith('.png')) {
                        return 'assets/images/[name].[hash][extname]';
                    }
                    return 'assets/[name].[hash][extname]';
                }
            }
        }
    }
});
