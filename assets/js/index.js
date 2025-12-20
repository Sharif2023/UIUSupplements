/**
 * UIU Supplements - Global JavaScript
 * Centralized JS for common functionality across all pages
 */

/**
 * Footer Scroll Handler
 * Adjusts sidebar navigation position when scrolling near footer
 * to prevent sidebar from overlapping the footer
 */
window.addEventListener("scroll", function() {
    const nav = document.querySelector("nav");
    const footer = document.querySelector(".footer");
    
    if (!nav || !footer) return;
    
    const footerRect = footer.getBoundingClientRect();

    if (footerRect.top <= window.innerHeight) {
        nav.style.position = "absolute";
        nav.style.top = (window.scrollY + footerRect.top - nav.offsetHeight) + "px";
    } else {
        nav.style.position = "fixed";
        nav.style.top = "0";
    }
});

/**
 * DOM Ready Handler
 * Runs initialization code when DOM is fully loaded
 */
document.addEventListener("DOMContentLoaded", function() {
    // Add active class to current nav item based on URL
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('nav ul li a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
});
