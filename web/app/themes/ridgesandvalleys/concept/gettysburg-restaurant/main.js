/* ============================================================
   Field & Musket — global site behaviors (shared on every page)
   Ridges & Valleys Studio · vanilla JS, no dependencies,
   no localStorage/sessionStorage.
   Page-specific tools (e.g. the reservation engine) live inline
   on the page that hosts them.
============================================================ */
(function () {
  "use strict";

  // ---------- Mobile nav toggle ----------
  var header = document.getElementById('siteHeader');
  var hamburgerBtn = document.getElementById('hamburgerBtn');
  var hamburgerIcon = document.getElementById('hamburgerIcon');
  var mobileNav = document.getElementById('mobileNav');

  var iconMenu = '<path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>';
  var iconClose = '<path d="M5 5l14 14M19 5L5 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>';

  function setNavOpen(open) {
    if (!header) return;
    header.classList.toggle('nav-open', open);
    hamburgerBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    hamburgerBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    if (hamburgerIcon) hamburgerIcon.innerHTML = open ? iconClose : iconMenu;
  }

  if (hamburgerBtn && mobileNav && header) {
    hamburgerBtn.addEventListener('click', function () {
      setNavOpen(!header.classList.contains('nav-open'));
    });
    mobileNav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () { setNavOpen(false); });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && header.classList.contains('nav-open')) {
        setNavOpen(false);
        hamburgerBtn.focus();
      }
    });
  }

  // ---------- Header shadow on scroll ----------
  if (header) {
    var onScroll = function () {
      header.classList.toggle('scrolled', window.scrollY > 8);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  // ---------- Smooth scroll for same-page anchors ----------
  document.querySelectorAll('a[href^="#"]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      var targetId = link.getAttribute('href');
      if (!targetId || targetId === '#' || targetId.length < 2) return;
      var target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        target.scrollIntoView({ behavior: prefersReduced ? 'auto' : 'smooth', block: 'start' });
        target.setAttribute('tabindex', '-1');
        target.focus({ preventScroll: true });
      }
    });
  });

  // ---------- Scroll-reveal via IntersectionObserver (graceful degrade) ----------
  var revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('is-visible'); });
  }

  // ---------- Current year in footer ----------
  document.querySelectorAll('[data-year]').forEach(function (el) {
    el.textContent = new Date().getFullYear();
  });

})();
