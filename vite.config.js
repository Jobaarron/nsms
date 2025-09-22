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
                'resources/css/roles_access.css',
                'resources/css/index_admin.css',
                'resources/css/email.css',
                'resources/css/admin_generator.css',
                'resources/css/admin_enrollments.css',
                'resources/css/manage_users.css',
                'resources/css/index_student.css',
                'resources/css/index_enrollee.css',
                'resources/css/index_guidance.css',
                'resources/css/index_teacher.css',
                'resources/js/guidance_student-profile.js',
                'resources/css/guidance_student-profile.css',
                'resources/js/guidance_student-violations.js',
                'resources/css/guidance_student-violations.css',
                'resources/css/student_violations.css',
                'resources/js/landingpage.js',
                'resources/js/enroll.js',
                'resources/js/admin-enrollments.js',
                'resources/js/admin-role-access.js',
                'resources/js/role-modals.js',
                'resources/js/manage_users.js',
                
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
