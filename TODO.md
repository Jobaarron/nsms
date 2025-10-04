# Fix Controllers to Give Right Data

## Tasks

## Completed
- [x] Analyze the issue: Controllers filtering violations incorrectly, causing wrong counts in views
- [x] Create plan and get approval
- [x] Modify StudentController::violations() to pass all violations with effective_severity and escalated set instead of filtered
- [x] Modify DisciplineController::violationsIndex() to calculate stats from the filtered major violations collection
