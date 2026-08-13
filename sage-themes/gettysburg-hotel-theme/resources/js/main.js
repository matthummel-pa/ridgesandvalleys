/* ============================================================
   The Lantern & Laurel Inn — global site behaviors
   Ridges & Valleys Studio concept
   Loaded on every page with <script src="main.js" defer>.
   Page-specific tools (the booking bar) live inline on their page.
   ============================================================ */
(function () {
  "use strict";

  // --- Mobile menu toggle ---
  var hamburger = document.getElementById('hamburgerBtn');
  var panel = document.getElementById('mobilePanel');

  if (hamburger && panel) {
    hamburger.addEventListener('click', function () {
      var isOpen = panel.classList.toggle('open');
      hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      hamburger.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
    });

    panel.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        panel.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.setAttribute('aria-label', 'Open menu');
      });
    });
  }

  // --- Header shadow on scroll ---
  var header = document.querySelector('header.site');
  if (header) {
    var onScroll = function () {
      if (window.scrollY > 8) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  // --- Scroll reveal (degrades gracefully) ---
  var revealEls = document.querySelectorAll('.reveal');
  var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if ('IntersectionObserver' in window && !prefersReduced) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('in');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    revealEls.forEach(function (el) { observer.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('in'); });
  }

  // --- Current year in footer ---
  document.querySelectorAll('.js-year').forEach(function (el) {
    el.textContent = new Date().getFullYear();
  });
})();
