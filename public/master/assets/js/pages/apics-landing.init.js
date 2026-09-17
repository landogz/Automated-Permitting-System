/**
 * APICS homepage (Velzon landing) — sticky navbar + back-to-top.
 * Safe subset of master/assets/js/pages/landing.init.js (no plan/review deps).
 */
(function () {
    'use strict';

    function windowScroll() {
        var navbar = document.getElementById('navbar');
        if (!navbar) {
            return;
        }
        if (document.body.scrollTop >= 50 || document.documentElement.scrollTop >= 50) {
            navbar.classList.add('is-sticky');
        } else {
            navbar.classList.remove('is-sticky');
        }
    }

    window.addEventListener('scroll', windowScroll);
    windowScroll();

    var navLinks = document.querySelectorAll('#navbar-example .nav-item .nav-link');
    var menuToggle = document.getElementById('navbarSupportedContent');
    var bsCollapse = null;

    function closeMobileMenu() {
        if (!menuToggle || !window.bootstrap) {
            return;
        }
        if (document.documentElement.clientWidth >= 980) {
            return;
        }
        bsCollapse = bootstrap.Collapse.getOrCreateInstance(menuToggle, { toggle: false });
        bsCollapse.hide();
    }

    if (navLinks.length && menuToggle) {
        Array.prototype.forEach.call(navLinks, function (link) {
            link.addEventListener('click', closeMobileMenu);
        });
    }

    var myButton = document.getElementById('back-to-top');

    function scrollFunction() {
        if (!myButton) {
            return;
        }
        if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
            myButton.style.display = 'block';
        } else {
            myButton.style.display = 'none';
        }
    }

    window.topFunction = function topFunction() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    };

    window.addEventListener('scroll', scrollFunction);
    scrollFunction();
})();
