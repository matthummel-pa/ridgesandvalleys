/* Global behaviors — Hallowed Ground Battlefield Tours
   (hamburger menu, scroll reveal, header shadow, current year, FAQ accordion) */
(function(){
  "use strict";

  /* Current year */
  var yr = String(new Date().getFullYear());
  document.querySelectorAll("[data-year]").forEach(function(el){ el.textContent = yr; });

  /* Header shadow on scroll */
  var header = document.querySelector(".site-header");
  if(header){
    var onScroll = function(){
      if(window.scrollY > 8){ header.classList.add("is-scrolled"); }
      else { header.classList.remove("is-scrolled"); }
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive:true });
  }

  /* Mobile nav */
  var hamburgerBtn = document.getElementById("hamburgerBtn");
  var mobileNav = document.getElementById("mobileNav");
  if(hamburgerBtn && mobileNav){
    var closeMobileNav = function(){
      mobileNav.classList.remove("is-open");
      hamburgerBtn.setAttribute("aria-expanded","false");
    };
    hamburgerBtn.addEventListener("click", function(){
      var open = mobileNav.classList.toggle("is-open");
      hamburgerBtn.setAttribute("aria-expanded", open ? "true" : "false");
    });
    mobileNav.querySelectorAll("a").forEach(function(a){ a.addEventListener("click", closeMobileNav); });
    document.addEventListener("keydown", function(e){ if(e.key === "Escape") closeMobileNav(); });
  }

  /* Reveal on scroll */
  var revealEls = document.querySelectorAll(".reveal");
  if("IntersectionObserver" in window){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          entry.target.classList.add("is-visible");
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -40px 0px" });
    revealEls.forEach(function(el){ io.observe(el); });
  } else {
    revealEls.forEach(function(el){ el.classList.add("is-visible"); });
  }

  /* FAQ accordion */
  document.querySelectorAll(".faq-item").forEach(function(item){
    var btn = item.querySelector(".faq-q");
    var panel = item.querySelector(".faq-a");
    if(!btn || !panel) return;
    btn.addEventListener("click", function(){
      var isOpen = item.getAttribute("data-open") === "true";
      document.querySelectorAll(".faq-item").forEach(function(other){
        if(other !== item){
          other.setAttribute("data-open","false");
          var ob = other.querySelector(".faq-q");
          var op = other.querySelector(".faq-a");
          if(ob) ob.setAttribute("aria-expanded","false");
          if(op) op.style.maxHeight = null;
        }
      });
      if(isOpen){
        item.setAttribute("data-open","false");
        btn.setAttribute("aria-expanded","false");
        panel.style.maxHeight = null;
      } else {
        item.setAttribute("data-open","true");
        btn.setAttribute("aria-expanded","true");
        panel.style.maxHeight = panel.scrollHeight + "px";
      }
    });
  });

})();
