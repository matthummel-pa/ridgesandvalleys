/* ============================================================
   Ridgeline Realty — global site behaviors (loaded on every page)
   Mobile nav, scroll reveal, header shadow, current year, chat widget.
   Page-specific tools (listings/filter/modal/calc/forms) live inline
   on their own pages.
   ============================================================ */
(function(){
  "use strict";

  /* ---------- Mobile nav ---------- */
  var hamburgerBtn = document.getElementById('hamburgerBtn');
  var mobileNav = document.getElementById('mobileNav');
  if(hamburgerBtn && mobileNav){
    hamburgerBtn.addEventListener('click', function(){
      var open = mobileNav.classList.toggle('open');
      hamburgerBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      hamburgerBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    });
    mobileNav.querySelectorAll('a').forEach(function(a){
      a.addEventListener('click', function(){
        mobileNav.classList.remove('open');
        hamburgerBtn.setAttribute('aria-expanded','false');
        hamburgerBtn.setAttribute('aria-label','Open menu');
      });
    });
  }

  /* ---------- Header shadow on scroll ---------- */
  var header = document.querySelector('header.site-header');
  if(header){
    var onScroll = function(){
      if(window.scrollY > 10){ header.classList.add('scrolled'); }
      else { header.classList.remove('scrolled'); }
    };
    window.addEventListener('scroll', onScroll, {passive:true});
    onScroll();
  }

  /* ---------- Reveal on scroll ---------- */
  var revealEls = document.querySelectorAll('.reveal');
  if('IntersectionObserver' in window){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          entry.target.classList.add('in');
          io.unobserve(entry.target);
        }
      });
    }, {threshold:0.12});
    revealEls.forEach(function(el){ io.observe(el); });
  } else {
    revealEls.forEach(function(el){ el.classList.add('in'); });
  }

  /* ---------- Current year ---------- */
  document.querySelectorAll('[data-current-year]').forEach(function(el){
    el.textContent = new Date().getFullYear();
  });

  /* ---------- Chat widget ---------- */
  var chatToggleBtn = document.getElementById('chatToggleBtn');
  var chatPanel = document.getElementById('chatPanel');
  var chatCloseBtn = document.getElementById('chatCloseBtn');
  var chatBody = document.getElementById('chatBody');

  if(chatToggleBtn && chatPanel && chatBody){
    var openChat = function(){
      chatPanel.classList.add('open');
      chatToggleBtn.setAttribute('aria-expanded','true');
    };
    var closeChat = function(){
      chatPanel.classList.remove('open');
      chatToggleBtn.setAttribute('aria-expanded','false');
    };
    chatToggleBtn.addEventListener('click', function(){
      if(chatPanel.classList.contains('open')){ closeChat(); } else { openChat(); }
    });
    if(chatCloseBtn){ chatCloseBtn.addEventListener('click', closeChat); }
    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape' && chatPanel.classList.contains('open')){ closeChat(); }
    });

    var quickReplies = {
      worth: {
        user: "What's my home worth?",
        bot: 'Great question! Head to our <a href="sell.html">Sell Your Home</a> page and drop in your address for a free, no-obligation estimate from an agent who knows your street.'
      },
      showing: {
        user: 'Schedule a showing',
        bot: 'Happy to help — visit the <a href="contact.html#schedule">Contact</a> page and use the "Schedule a showing" form to pick a property, date, and time.'
      },
      agent: {
        user: 'Talk to an agent',
        bot: 'You can reach our team directly at (717) 555-0210, or use the "Contact an agent" form on the <a href="contact.html">Contact</a> page and someone will follow up shortly.'
      },
      areas: {
        user: 'Areas you serve?',
        bot: 'We work throughout Gettysburg plus Biglerville, Littlestown, New Oxford, McSherrystown, Fairfield, Cashtown, and the surrounding Adams County townships. See our <a href="areas.html">Areas We Serve</a> page.'
      }
    };

    document.querySelectorAll('.chat-quick button').forEach(function(btn){
      btn.addEventListener('click', function(){
        var key = btn.getAttribute('data-reply');
        var reply = quickReplies[key];
        if(!reply) return;
        var userMsg = document.createElement('div');
        userMsg.className = 'chat-msg from-user';
        userMsg.textContent = reply.user;
        chatBody.appendChild(userMsg);

        var botMsg = document.createElement('div');
        botMsg.className = 'chat-msg';
        botMsg.innerHTML = reply.bot;
        chatBody.appendChild(botMsg);

        chatBody.scrollTop = chatBody.scrollHeight;
      });
    });
  }

})();
