# TODO: Fix Laravel Errors

## Database Connection
- [ ] Start MySQL service (net start mysql)
- [ ] Run php artisan migrate
- [ ] If MySQL not available, consider switching to SQLite

## Vite Assets
- [ ] Run npm run build

## Cache and Config
- [ ] Run php artisan config:clear
- [ ] Run php artisan cache:clear

## Check Imports
- [ ] Verify no duplicate Str imports in routes/api.php
- [ ] Check AppServiceProvider for Route facade usage

## Test Application
- [ ] Run php artisan serve
- [ ] Check logs for remaining errors

# TODO: Fix Case Meeting Actions

## Update Blade View
- [x] Update resources/views/guidance/case-meetings.blade.php to show complete and forward buttons for 'scheduled' and 'in_progress' statuses

## Update JavaScript
- [x] Update resources/js/guidance_case-meetings.js to show complete and forward buttons in modal for 'scheduled' and 'in_progress' statuses

## Test Changes
- [ ] Verify that in_progress meetings now show 5 actions instead of 3
