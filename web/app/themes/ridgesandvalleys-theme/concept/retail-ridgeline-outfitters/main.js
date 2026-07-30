/* ============================================================
   Ridgeline Outfitters — global site behaviors (every page)
   Mobile menu, scroll reveals, header shadow, current year,
   smooth in-page anchor scroll, newsletter demo (guarded).
   Vanilla JS only. No storage. Respects reduced motion.
   ============================================================ */
(function(){
  "use strict";

  function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
  function qsa(sel, ctx){ return Array.prototype.slice.call((ctx||document).querySelectorAll(sel)); }
  function prefersReducedMotion(){
    return window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  }

  /* ---------- Mobile menu ---------- */
  var mobileMenu = qs("#mobileMenu");
  var menuToggle = qs("#menuToggle");

  function openMobileMenu(){
    if (!mobileMenu) return;
    mobileMenu.hidden = false;
    requestAnimationFrame(function(){ mobileMenu.classList.add("open"); });
    menuToggle.setAttribute("aria-expanded","true");
    menuToggle.setAttribute("aria-label","Close menu");
  }
  function closeMobileMenu(){
    if (!mobileMenu) return;
    mobileMenu.classList.remove("open");
    menuToggle.setAttribute("aria-expanded","false");
    menuToggle.setAttribute("aria-label","Open menu");
    setTimeout(function(){ if (!mobileMenu.classList.contains("open")) mobileMenu.hidden = true; }, 280);
  }
  if (menuToggle && mobileMenu){
    menuToggle.addEventListener("click", function(){
      if (mobileMenu.classList.contains("open")) closeMobileMenu(); else openMobileMenu();
    });
    qsa("#mobileMenu a").forEach(function(a){ a.addEventListener("click", closeMobileMenu); });
    document.addEventListener("keydown", function(e){
      if (e.key === "Escape" && mobileMenu.classList.contains("open")) closeMobileMenu();
    });
  }

  /* ---------- Header shadow on scroll ---------- */
  var header = qs(".site-header");
  if (header){
    var onScroll = function(){
      header.classList.toggle("is-scrolled", window.scrollY > 8);
    };
    window.addEventListener("scroll", onScroll, { passive:true });
    onScroll();
  }

  /* ---------- Smooth in-page anchor scroll ---------- */
  qsa('a[href^="#"]').forEach(function(a){
    a.addEventListener("click", function(e){
      var href = a.getAttribute("href");
      if (!href || href === "#") return;
      var target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: prefersReducedMotion() ? "auto" : "smooth", block: "start" });
    });
  });

  /* ---------- Scroll reveals (graceful degrade) ---------- */
  (function initReveals(){
    var items = qsa(".reveal");
    if (!items.length) return;
    if (!("IntersectionObserver" in window)){
      items.forEach(function(el){ el.classList.add("in-view"); });
      return;
    }
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if (entry.isIntersecting){
          entry.target.classList.add("in-view");
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -40px 0px" });
    items.forEach(function(el){ io.observe(el); });
  })();

  /* ---------- Current year ---------- */
  qsa("[data-year]").forEach(function(el){ el.textContent = new Date().getFullYear(); });

  /* ---------- Newsletter demo (guarded — only if present) ---------- */
  var nf = qs("#newsletterForm");
  if (nf){
    nf.addEventListener("submit", function(e){
      e.preventDefault();
      var email = qs("#newsletterEmail");
      if (!email.value || !email.value.includes("@")){ email.focus(); return; }
      var ok = qs("#newsletterSuccess");
      if (ok) ok.classList.add("show");
      email.value = "";
    });
  }

  /* ---------- Contact form demo (guarded — only on contact page) ---------- */
  var cf = qs("#contactForm");
  if (cf){
    cf.addEventListener("submit", function(e){
      e.preventDefault();
      if (!cf.checkValidity()){ cf.reportValidity(); return; }
      var ok = qs("#contactSuccess");
      if (ok) ok.classList.add("show");
      cf.reset();
    });
  }

})();
