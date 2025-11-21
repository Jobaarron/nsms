// Collapsible Sidebar JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const mainContent = document.querySelector('.main-content-wrapper');
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    
    // Check if elements exist
    if (!sidebar || !sidebarToggle || !mainContent) {
        console.warn('Sidebar elements not found');
        return;
    }
    
    // Get stored sidebar state or default to expanded
    const sidebarState = localStorage.getItem('sidebarState') || 'expanded';
    
    // Apply initial state
    if (sidebarState === 'collapsed') {
        collapseSidebar();
    }
    
    // Toggle sidebar on button click (handled in handleMobileToggle function)
    
    function collapseSidebar() {
        sidebar.classList.add('collapsed');
        sidebarToggle.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
        
        // Add tooltips to nav links when collapsed
        navLinks.forEach(link => {
            const spanText = link.querySelector('span');
            if (spanText) {
                link.setAttribute('data-tooltip', spanText.textContent.trim());
                link.setAttribute('title', spanText.textContent.trim());
            }
        });
        
        localStorage.setItem('sidebarState', 'collapsed');
    }
    
    function expandSidebar() {
        sidebar.classList.remove('collapsed');
        sidebarToggle.classList.remove('collapsed');
        mainContent.classList.remove('sidebar-collapsed');
        
        // Remove tooltips when expanded
        navLinks.forEach(link => {
            link.removeAttribute('data-tooltip');
            link.removeAttribute('title');
        });
        
        localStorage.setItem('sidebarState', 'expanded');
    }
    
    // Handle window resize
    function handleResize() {
        if (window.innerWidth <= 767.98) {
            // Mobile view - always show toggle button in default position
            sidebarToggle.classList.remove('collapsed');
        } else {
            // Desktop view - maintain current state
            if (sidebar.classList.contains('collapsed')) {
                sidebarToggle.classList.add('collapsed');
            }
        }
    }
    
    // Listen for window resize
    window.addEventListener('resize', function() {
        handleResize();
        handleMobileToggle();
    });
    
    // Initial check
    handleResize();
    
    // Handle mobile sidebar toggle
    function handleMobileToggle() {
        if (window.innerWidth <= 767.98) {
            // Override desktop behavior for mobile
            sidebarToggle.removeEventListener('click', toggleSidebar);
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                sidebar.classList.toggle('show');
            });
        } else {
            // Desktop behavior
            sidebarToggle.addEventListener('click', toggleSidebar);
        }
    }
    
    function toggleSidebar() {
        if (sidebar.classList.contains('collapsed')) {
            expandSidebar();
        } else {
            collapseSidebar();
        }
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 767.98 && 
            !sidebar.contains(e.target) && 
            !sidebarToggle.contains(e.target) &&
            sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
        }
    });
    
    // Initial mobile setup
    handleMobileToggle();
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