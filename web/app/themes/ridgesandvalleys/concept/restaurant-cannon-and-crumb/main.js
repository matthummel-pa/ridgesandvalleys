/* ==========================================================================
   Cannon & Crumb — global behaviors (loaded on every page)
   Hamburger / mobile nav, scroll-reveal, header shadow on scroll,
   current year, concept-badge no-op. Page-specific tools (menu tabs +
   dietary filters + ordering cart) live in order.js.
   ========================================================================== */
(function(){
  "use strict";

  /* ---------------- Mobile nav ---------------- */
  var hamburger = document.getElementById('hamburger');
  var mainNav = document.getElementById('main-nav');
  if(hamburger && mainNav){
    hamburger.addEventListener('click', function(){
      var open = mainNav.classList.toggle('open');
      hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    mainNav.querySelectorAll('a').forEach(function(a){
      a.addEventListener('click', function(){
        mainNav.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* ---------------- Reveal on scroll ---------------- */
  var revealEls = document.querySelectorAll('.reveal');
  if('IntersectionObserver' in window){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          entry.target.classList.add('in-view');
          io.unobserve(entry.target);
        }
      });
    }, {threshold:0.12});
    revealEls.forEach(function(el){ io.observe(el); });
  } else {
    revealEls.forEach(function(el){ el.classList.add('in-view'); });
  }

  /* ---------------- Header shadow on scroll ---------------- */
  var header = document.querySelector('header.site-header');
  if(header){
    var onScroll = function(){
      if(window.scrollY > 8){
        header.style.boxShadow = '0 8px 24px -18px rgba(58,38,32,0.5)';
      } else {
        header.style.boxShadow = 'none';
      }
    };
    onScroll();
    window.addEventListener('scroll', onScroll, {passive:true});
  }

  /* ---------------- Current year ---------------- */
  document.querySelectorAll('[data-year]').forEach(function(el){
    el.textContent = new Date().getFullYear();
  });

  /* ---------------- Concept badge (no-op link) ---------------- */
  var badge = document.getElementById('concept-badge');
  if(badge){
    badge.addEventListener('click', function(e){ e.preventDefault(); });
  }
})();
