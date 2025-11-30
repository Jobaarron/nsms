// Collapsible Sidebar JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const mainContent = document.querySelector('.main-content-wrapper');
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    
    // Check if elements exist - silently return if not (page may not have sidebar)
    if (!sidebar || !sidebarToggle || !mainContent) {
        return;
    }
    
    // Detect portal type from page title or URL
    function getPortalType() {
        const title = document.title.toLowerCase();
        if (title.includes('applicant') || title.includes('enrollee')) return 'enrollee';
        if (title.includes('student')) return 'student';
        if (title.includes('admin')) return 'admin';
        if (title.includes('teacher')) return 'teacher';
        if (title.includes('registrar')) return 'registrar';
        if (title.includes('guidance')) return 'guidance';
        if (title.includes('faculty') || title.includes('head')) return 'faculty_head';
        if (title.includes('discipline')) return 'discipline';
        if (title.includes('cashier')) return 'cashier';
        return 'default';
    }
    
    const portalType = getPortalType();
    const sidebarStateKey = `sidebarState_${portalType}`;
    
    // Get stored sidebar state or default to expanded (desktop only)
    const sidebarState = sessionStorage.getItem(sidebarStateKey) || 'expanded';
    
    // Apply initial state only on desktop
    if (window.innerWidth > 767.98 && sidebarState === 'collapsed') {
        collapseSidebar();
    }
    
    // Remove the initial class after applying the state
    setTimeout(() => {
        document.documentElement.classList.remove('sidebar-collapsed-initial');
    }, 100);
    
    // Main toggle event listener
    sidebarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (window.innerWidth <= 767.98) {
            // Mobile behavior - toggle show/hide
            sidebar.classList.toggle('show');
            updateToggleIcon();
        } else {
            // Desktop behavior - toggle collapse/expand
            if (sidebar.classList.contains('collapsed')) {
                expandSidebar();
            } else {
                collapseSidebar();
            }
        }
    });
    
    // Update toggle button icon based on state
    function updateToggleIcon() {
        const icon = sidebarToggle.querySelector('i');
        if (!icon) return;
        
        if (window.innerWidth <= 767.98) {
            // Mobile: hamburger/close icon
            if (sidebar.classList.contains('show')) {
                icon.className = 'ri-close-line';
            } else {
                icon.className = 'ri-menu-line';
            }
        } else {
            // Desktop: fold/unfold icon
            if (sidebar.classList.contains('collapsed')) {
                icon.className = 'ri-menu-unfold-line';
            } else {
                icon.className = 'ri-menu-fold-line';
            }
        }
    }
    
    function collapseSidebar() {
        sidebar.classList.add('collapsed');
        sidebarToggle.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
        document.documentElement.style.setProperty('--sidebar-width', '70px');
        
        // Add tooltips to nav links when collapsed
        navLinks.forEach(link => {
            const spanText = link.querySelector('span');
            if (spanText) {
                link.setAttribute('data-tooltip', spanText.textContent.trim());
                link.setAttribute('title', spanText.textContent.trim());
            }
        });
        
        // Add tooltips to logout buttons when collapsed
        const logoutButtons = document.querySelectorAll('.sidebar .logout-form button, .sidebar .logout-btn');
        logoutButtons.forEach(button => {
            const spanText = button.querySelector('span');
            if (spanText) {
                button.setAttribute('data-tooltip', spanText.textContent.trim());
                button.setAttribute('title', spanText.textContent.trim());
            } else {
                button.setAttribute('title', 'Logout');
            }
        });
        
        updateToggleIcon();
        sessionStorage.setItem(sidebarStateKey, 'collapsed');
    }
    
    function expandSidebar() {
        sidebar.classList.remove('collapsed');
        sidebarToggle.classList.remove('collapsed');
        mainContent.classList.remove('sidebar-collapsed');
        document.documentElement.style.setProperty('--sidebar-width', '250px');
        
        // Remove tooltips when expanded
        navLinks.forEach(link => {
            link.removeAttribute('data-tooltip');
            link.removeAttribute('title');
        });
        
        // Remove tooltips from logout buttons when expanded
        const logoutButtons = document.querySelectorAll('.sidebar .logout-form button, .sidebar .logout-btn');
        logoutButtons.forEach(button => {
            button.removeAttribute('data-tooltip');
            button.removeAttribute('title');
        });
        
        updateToggleIcon();
        sessionStorage.setItem(sidebarStateKey, 'expanded');
    }
    
    // Handle window resize
    function handleResize() {
        if (window.innerWidth <= 767.98) {
            // Mobile view - reset states
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('show');
            sidebarToggle.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
        } else {
            // Desktop view - restore saved state
            sidebar.classList.remove('show');
            const savedState = sessionStorage.getItem(sidebarStateKey);
            if (savedState === 'collapsed') {
                collapseSidebar();
            } else {
                expandSidebar();
            }
        }
        updateToggleIcon();
    }
    
    // Listen for window resize
    window.addEventListener('resize', handleResize);
    
    // Initial check
    handleResize();
    
    // Get mobile overlay
    const mobileOverlay = document.querySelector('.mobile-overlay');
    
    // Close sidebar when clicking overlay on mobile
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', function() {
            if (window.innerWidth <= 767.98 && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                updateToggleIcon();
            }
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 767.98 && 
            !sidebar.contains(e.target) && 
            !sidebarToggle.contains(e.target) &&
            (!mobileOverlay || !mobileOverlay.contains(e.target)) &&
            sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            updateToggleIcon();
        }
    });
});

// Export functions for potential external use
window.sidebarControls = {
    collapse: function() {
        const event = new Event('click');
        const toggleBtn = document.querySelector('.sidebar-toggle');
        if (toggleBtn && !document.querySelector('.sidebar').classList.contains('collapsed')) {
            toggleBtn.dispatchEvent(event);
        }
    },
    expand: function() {
        const event = new Event('click');
        const toggleBtn = document.querySelector('.sidebar-toggle');
        if (toggleBtn && document.querySelector('.sidebar').classList.contains('collapsed')) {
            toggleBtn.dispatchEvent(event);
        }
    },
    toggle: function() {
        const event = new Event('click');
        const toggleBtn = document.querySelector('.sidebar-toggle');
        if (toggleBtn) {
            toggleBtn.dispatchEvent(event);
        }
    }
};