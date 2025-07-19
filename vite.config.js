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
                'resources/js/landingpage.js',
                'resources/js/enroll.js',
                'resources/js/admin-enrollments.js',
                
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
