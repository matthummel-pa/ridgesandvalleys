/* First Shot Food & History Tours — global site behaviors
   Shared across every page. Page-specific tools (booking engine, FAQ
   accordion) live inline on their own pages. */
(function(){
  "use strict";

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Mobile menu ---------- */
  var hamburgerBtn = document.getElementById('hamburgerBtn');
  var mobileMenu = document.getElementById('mobileMenu');

  function closeMenu(){
    if(!hamburgerBtn) return;
    hamburgerBtn.setAttribute('aria-expanded', 'false');
    hamburgerBtn.setAttribute('aria-label', 'Open menu');
    mobileMenu.classList.remove('open');
  }
  function openMenu(){
    hamburgerBtn.setAttribute('aria-expanded', 'true');
    hamburgerBtn.setAttribute('aria-label', 'Close menu');
    mobileMenu.classList.add('open');
  }
  if(hamburgerBtn && mobileMenu){
    hamburgerBtn.addEventListener('click', function(){
      var expanded = hamburgerBtn.getAttribute('aria-expanded') === 'true';
      if(expanded){ closeMenu(); } else { openMenu(); }
    });
    mobileMenu.querySelectorAll('a').forEach(function(a){
      a.addEventListener('click', closeMenu);
    });
    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape'){ closeMenu(); }
    });
  }

  /* ---------- Smooth anchor scroll (same-page # links only) ---------- */
  document.querySelectorAll('a[href^="#"]').forEach(function(link){
    link.addEventListener('click', function(e){
      var id = link.getAttribute('href');
      if(id.length < 2) return;
      var target = document.querySelector(id);
      if(target){
        e.preventDefault();
        target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
        target.setAttribute('tabindex', '-1');
        target.focus({ preventScroll: true });
      }
    });
  });

  /* ---------- Reveal on scroll ---------- */
  var revealEls = document.querySelectorAll('.reveal');
  if('IntersectionObserver' in window && !reduceMotion){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          entry.target.classList.add('in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach(function(el){ io.observe(el); });
  } else {
    revealEls.forEach(function(el){ el.classList.add('in'); });
  }

  /* ---------- Footer year ---------- */
  var yearEl = document.getElementById('footerYear');
  if(yearEl){ yearEl.textContent = new Date().getFullYear(); }

  /* ---------- Generic FAQ accordion (any .faq-item with .faq-q markup) ---------- */
  document.querySelectorAll('.faq-item').forEach(function(wrap){
    var btn = wrap.querySelector('.faq-q');
    if(!btn) return;
    btn.addEventListener('click', function(){
      var isOpen = wrap.dataset.open === 'true';
      wrap.dataset.open = isOpen ? 'false' : 'true';
      btn.setAttribute('aria-expanded', String(!isOpen));
    });
  });

})();
