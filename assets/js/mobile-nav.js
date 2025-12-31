/**
 * UIU Supplements - Mobile Navigation Handler
 * Manages mobile menu toggle, overlay, and touch interactions
 */

(function () {
    'use strict';

    // Initialize mobile navigation on DOM ready
    document.addEventListener('DOMContentLoaded', function () {
        initMobileNavigation();
    });

    /**
     * Initialize mobile navigation system
     */
    function initMobileNavigation() {
        // Create mobile menu toggle button if it doesn't exist
        createMobileToggle();

        // Create overlay for mobile menu
        createMobileOverlay();

        // Set up event listeners
        setupEventListeners();

        // Handle window resize
        handleWindowResize();
    }

    /**
     * Create mobile menu toggle button
     */
    function createMobileToggle() {
        // Check if toggle already exists
        if (document.querySelector('.mobile-menu-toggle')) {
            return;
        }

        const toggle = document.createElement('button');
        toggle.className = 'mobile-menu-toggle';
        toggle.setAttribute('aria-label', 'Toggle mobile menu');
        toggle.innerHTML = '<i class="fas fa-bars"></i>';

        document.body.appendChild(toggle);
    }

    /**
     * Create overlay for mobile menu
     */
    function createMobileOverlay() {
        // Check if overlay already exists
        if (document.querySelector('.mobile-overlay')) {
            return;
        }

        const overlay = document.createElement('div');
        overlay.className = 'mobile-overlay';
        document.body.appendChild(overlay);
    }

    /**
     * Set up all event listeners
     */
    function setupEventListeners() {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const overlay = document.querySelector('.mobile-overlay');
        const nav = document.querySelector('nav');
        const sidebar = document.querySelector('.sidebar');

        if (!toggle) return;

        // Toggle button click
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleMobileMenu();
        });

        // Overlay click
        if (overlay) {
            overlay.addEventListener('click', function () {
                closeMobileMenu();
            });
        }

        // Close menu when clicking nav links (except on admin panel)
        if (nav && !sidebar) {
            const navLinks = nav.querySelectorAll('a:not(.logo):not(.logout-btn)');
            navLinks.forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth <= 768) {
                        closeMobileMenu();
                    }
                });
            });
        }

        // Handle escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });

        // Prevent body scroll when menu is open
        overlay?.addEventListener('touchmove', function (e) {
            e.preventDefault();
        }, { passive: false });
    }

    /**
     * Toggle mobile menu open/closed
     */
    function toggleMobileMenu() {
        const nav = document.querySelector('nav');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.mobile-overlay');
        const toggle = document.querySelector('.mobile-menu-toggle');
        const menuElement = sidebar || nav;

        if (!menuElement) return;

        const isOpen = menuElement.classList.contains('mobile-active');

        if (isOpen) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    }

    /**
     * Open mobile menu
     */
    function openMobileMenu() {
        const nav = document.querySelector('nav');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.mobile-overlay');
        const toggle = document.querySelector('.mobile-menu-toggle');
        const menuElement = sidebar || nav;

        if (!menuElement) return;

        menuElement.classList.add('mobile-active');
        overlay?.classList.add('active');

        // Change icon to close
        if (toggle) {
            toggle.innerHTML = '<i class="fas fa-times"></i>';
        }

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Accessibility
        menuElement.setAttribute('aria-hidden', 'false');
    }

    /**
     * Close mobile menu
     */
    function closeMobileMenu() {
        const nav = document.querySelector('nav');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.mobile-overlay');
        const toggle = document.querySelector('.mobile-menu-toggle');
        const menuElement = sidebar || nav;

        if (!menuElement) return;

        menuElement.classList.remove('mobile-active');
        overlay?.classList.remove('active');

        // Change icon back to bars
        if (toggle) {
            toggle.innerHTML = '<i class="fas fa-bars"></i>';
        }

        // Restore body scroll
        document.body.style.overflow = '';

        // Accessibility
        menuElement.setAttribute('aria-hidden', 'true');
    }

    /**
     * Handle window resize
     */
    function handleWindowResize() {
        let resizeTimer;

        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                // Close mobile menu if screen becomes larger
                if (window.innerWidth > 768) {
                    closeMobileMenu();
                }
            }, 250);
        });
    }

    /**
     * Add swipe gesture support for closing menu
     */
    function addSwipeSupport() {
        const nav = document.querySelector('nav');
        const sidebar = document.querySelector('.sidebar');
        const menuElement = sidebar || nav;

        if (!menuElement) return;

        let touchStartX = 0;
        let touchEndX = 0;

        menuElement.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        menuElement.addEventListener('touchend', function (e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });

        function handleSwipe() {
            const swipeDistance = touchEndX - touchStartX;

            // If swiped left more than 50px, close menu
            if (swipeDistance < -50) {
                closeMobileMenu();
            }
        }
    }

    // Initialize swipe support
    addSwipeSupport();

    // Export functions for global use if needed
    window.MobileNav = {
        open: openMobileMenu,
        close: closeMobileMenu,
        toggle: toggleMobileMenu
    };

})();
