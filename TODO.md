# TODO: Implement Case Summary Creation After Case Meeting

## Tasks
- [x] Add "Add Summary" button in case-meetings.blade.php for completed meetings without summary
- [x] Add modal form for case summary creation in case-meetings.blade.php
- [x] Add createCaseSummary JavaScript function in guidance_case-meetings.js
- [x] Test the summary creation flow
- [x] Verify summary displays in details modal

## Additional Tasks: Set Status to Pre-Completed on Summary Submission
- [x] Create migration to add 'pre_completed' to case_meetings status enum
- [x] Update CaseMeeting model to include 'pre_completed' in status display
- [x] Modify createCaseSummary method to set status to 'pre_completed'
- [x] Update case-meetings view to include 'pre_completed' in status filter and statistics
- [x] Run migration to update database
- [x] Fix action buttons to remain available for 'pre_completed' status

## Status Workflow: Discipline Forward -> In Progress, Guidance Schedule -> Scheduled
- [x] Update DisciplineController::forwardViolation to set CaseMeeting status to 'in_progress' and Violation status to 'in_progress'
- [x] Update Violation model getStatusColorAttribute to include 'in_progress' -> 'info'
- [x] Update discipline violations view to show 'in_progress' status as "In Progress" badge
- [x] Update DisciplineController validations to include 'in_progress' status
- [x] Update DisciplineController stats to count 'in_progress' in investigating stats
