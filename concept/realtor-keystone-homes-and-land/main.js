/* =========================================================================
   Keystone Homes & Land — GLOBAL behaviors (shared on every page)
   - Mobile nav toggle
   - Sticky header shadow on scroll
   - Scroll-reveal IntersectionObserver
   - Concept chat widget
   - Current year in footer
   ========================================================================= */
(function(){
  "use strict";

  /* ============================= MOBILE NAV ============================= */
  var hamburgerBtn = document.getElementById("hamburgerBtn");
  var mobileNav = document.getElementById("mobileNav");
  if(hamburgerBtn && mobileNav){
    hamburgerBtn.addEventListener("click", function(){
      var open = mobileNav.classList.toggle("open");
      hamburgerBtn.setAttribute("aria-expanded", open);
      hamburgerBtn.setAttribute("aria-label", open ? "Close menu" : "Open menu");
    });
    mobileNav.querySelectorAll("a").forEach(function(a){
      a.addEventListener("click", function(){
        mobileNav.classList.remove("open");
        hamburgerBtn.setAttribute("aria-expanded","false");
        hamburgerBtn.setAttribute("aria-label","Open menu");
      });
    });
  }

  /* ============================= HEADER SHADOW ON SCROLL ============================= */
  var header = document.querySelector(".site-header");
  if(header){
    var onScroll = function(){
      if(window.scrollY > 8) header.classList.add("scrolled");
      else header.classList.remove("scrolled");
    };
    window.addEventListener("scroll", onScroll, {passive:true});
    onScroll();
  }

  /* ============================= REVEAL ON SCROLL ============================= */
  var revealEls = document.querySelectorAll(".reveal");
  if("IntersectionObserver" in window){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          entry.target.classList.add("in-view");
          io.unobserve(entry.target);
        }
      });
    }, {threshold:.12});
    revealEls.forEach(function(el){ io.observe(el); });
  } else {
    revealEls.forEach(function(el){ el.classList.add("in-view"); });
  }

  /* ============================= CONCEPT CHAT WIDGET ============================= */
  var chatFab = document.getElementById("chatFab");
  var chatWidget = document.getElementById("chatWidget");
  var chatCloseBtn = document.getElementById("chatCloseBtn");
  var chatBody = document.getElementById("chatBody");
  var chatQuick = document.getElementById("chatQuick");

  var REPLIES = {
    land: {label:"Land prices?", reply:"Right now our land parcels range from about $129,000 for a 5.5-acre wooded lot near Table Rock up to $215,000 for 38 tillable acres near Marsh Creek. Head to the Listings page and filter by 'Land' to see them all."},
    historic: {label:"Historic homes?", reply:"We currently have two historic properties listed — an 1852 stone homestead in Franklin Township and an 1890s brick farmhouse in Cumberland Township. Both come with a bit of Adams County history attached."},
    tour: {label:"Book a tour", reply:"You can book a call or tour anytime using the scheduler on our Land Buyer's Guide page — just pick a date and time and a Keystone agent will confirm."},
    financing: {label:"Financing help?", reply:"Our Land Buyer's Guide page has a land-loan estimate and a quick pre-qualification tool — plug in your numbers and it'll give you a friendly ballpark to start with."}
  };

  function openChat(){
    if(!chatWidget) return;
    chatWidget.classList.add("open");
    chatWidget.setAttribute("aria-hidden","false");
    chatFab.setAttribute("aria-expanded","true");
  }
  function closeChat(){
    if(!chatWidget) return;
    chatWidget.classList.remove("open");
    chatWidget.setAttribute("aria-hidden","true");
    chatFab.setAttribute("aria-expanded","false");
    chatFab.focus();
  }
  if(chatFab && chatWidget){
    chatFab.addEventListener("click", function(){
      chatWidget.classList.contains("open") ? closeChat() : openChat();
    });
    if(chatCloseBtn) chatCloseBtn.addEventListener("click", closeChat);
    if(chatQuick){
      chatQuick.addEventListener("click", function(e){
        var btn = e.target.closest("button[data-q]");
        if(!btn) return;
        var key = btn.getAttribute("data-q");
        var r = REPLIES[key];
        if(!r) return;
        appendChat(r.label, "user");
        setTimeout(function(){ appendChat(r.reply, "bot"); }, 400);
      });
    }
    document.addEventListener("keydown", function(e){
      if(e.key === "Escape" && chatWidget.classList.contains("open")) closeChat();
    });
  }
  function appendChat(text, who){
    if(!chatBody) return;
    var div = document.createElement("div");
    div.className = "chat-msg " + who;
    div.textContent = text;
    chatBody.appendChild(div);
    chatBody.scrollTop = chatBody.scrollHeight;
  }

  /* ============================= CURRENT YEAR ============================= */
  var yearEls = document.querySelectorAll("[data-year]");
  var yr = new Date().getFullYear();
  yearEls.forEach(function(el){ el.textContent = yr; });

})();
