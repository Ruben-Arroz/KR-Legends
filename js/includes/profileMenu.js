document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const profileMenu = document.querySelector('.profile-menu-container');
    const avatarTrigger = document.querySelector('.profile-avatar-trigger');
    
    if (!profileMenu) return;
    
    // Improve avatar interaction
    if (avatarTrigger) {
        avatarTrigger.addEventListener('click', function(e) {
            this.classList.toggle('active');
        });
        
        // Close dropdown when clicking elsewhere
        document.addEventListener('click', function(e) {
            if (!avatarTrigger.contains(e.target) && !e.target.closest('.dropdown-menu')) {
                avatarTrigger.classList.remove('active');
            }
        });
    }
    
    // Add responsive behavior
    function handleResponsiveLayout() {
        if (window.innerWidth <= 992) {
            // Mobile optimizations
            if (profileMenu) {
                profileMenu.classList.add('mobile-layout');
            }
        } else {
            // Desktop layout
            if (profileMenu) {
                profileMenu.classList.remove('mobile-layout');
            }
        }
    }
    
    // Initial call and listen for resize
    handleResponsiveLayout();
    window.addEventListener('resize', handleResponsiveLayout);
});