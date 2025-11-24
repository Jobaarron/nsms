// Run this in browser console to clear all old sidebar states
// This will reset all sidebar states to default (expanded)

console.log('Clearing old sidebar states...');

// Clear old global sidebar state
sessionStorage.removeItem('sidebarState');
localStorage.removeItem('sidebarState');

// Clear all portal-specific states
const portalTypes = ['enrollee', 'student', 'admin', 'teacher', 'registrar', 'guidance', 'faculty_head', 'discipline', 'cashier'];
portalTypes.forEach(portal => {
    sessionStorage.removeItem(`sidebarState_${portal}`);
    localStorage.removeItem(`sidebarState_${portal}`);
});

console.log('All sidebar states cleared! Refresh the page to see the default expanded sidebar.');