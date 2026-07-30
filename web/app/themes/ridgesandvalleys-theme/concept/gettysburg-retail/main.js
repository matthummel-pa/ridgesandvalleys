(function () {
  'use strict';

  /* ============ CURRENT YEAR ============ */
  document.querySelectorAll('.js-year').forEach(function (el) {
    el.textContent = String(new Date().getFullYear());
  });

  /* ============ HEADER SHADOW ON SCROLL ============ */
  var header = document.querySelector('.site-header');
  if (header) {
    var onScroll = function () {
      if (window.scrollY > 8) header.classList.add('scrolled');
      else header.classList.remove('scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  var scrim = document.getElementById('scrim');

  /* ============ MOBILE MENU ============ */
  var menuToggle = document.getElementById('menuToggle');
  var menuClose  = document.getElementById('menuClose');
  var mobileMenu = document.getElementById('mobileMenu');

  function openMenu() {
    if (!mobileMenu) return;
    mobileMenu.classList.add('open');
    mobileMenu.setAttribute('aria-hidden', 'false');
    if (menuToggle) menuToggle.setAttribute('aria-expanded', 'true');
    if (scrim) scrim.classList.add('show');
    if (menuClose) menuClose.focus();
  }
  function closeMenu() {
    if (!mobileMenu) return;
    mobileMenu.classList.remove('open');
    mobileMenu.setAttribute('aria-hidden', 'true');
    if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
    if (scrim && !(cartDrawer && cartDrawer.classList.contains('open'))) scrim.classList.remove('show');
    if (menuToggle) menuToggle.focus();
  }
  if (menuToggle) menuToggle.addEventListener('click', openMenu);
  if (menuClose)  menuClose.addEventListener('click', closeMenu);
  document.querySelectorAll('.mobile-link').forEach(function (a) {
    a.addEventListener('click', closeMenu);
  });

  /* ============ CART (in-memory only, no storage) ============ */
  var cart = []; // { name, price, qty }

  var cartBadge   = document.getElementById('cartBadge');
  var cartToggle  = document.getElementById('cartToggle');
  var cartDrawer  = document.getElementById('cartDrawer');
  var cartClose   = document.getElementById('cartClose');
  var cartItemsEl = document.getElementById('cartItems');
  var cartTotalEl = document.getElementById('cartTotal');
  var checkoutBtn = document.getElementById('checkoutBtn');
  var checkoutMsg = document.getElementById('checkoutMsg');
  var toast       = document.getElementById('toast');
  var toastText   = document.getElementById('toastText');

  var pmClasses = ['pm-1','pm-2','pm-3','pm-4','pm-5','pm-6','pm-7','pm-8'];

  function formatMoney(n) { return '$' + n.toFixed(2); }
  function totalCount() { return cart.reduce(function (s, i) { return s + i.qty; }, 0); }
  function totalPrice() { return cart.reduce(function (s, i) { return s + i.qty * i.price; }, 0); }

  function renderCart() {
    if (!cartBadge) return;
    var count = totalCount();
    cartBadge.textContent = String(count);
    if (cartToggle) cartToggle.setAttribute('aria-label', 'Open cart, ' + count + ' item' + (count === 1 ? '' : 's'));

    if (!cartItemsEl) return;
    cartItemsEl.innerHTML = '';
    if (cart.length === 0) {
      var empty = document.createElement('p');
      empty.className = 'cart-empty';
      empty.textContent = 'Your bag is empty — browse the shop to add something local & lovely.';
      cartItemsEl.appendChild(empty);
    } else {
      cart.forEach(function (item, idx) {
        var row = document.createElement('div');
        row.className = 'cart-item';

        var media = document.createElement('div');
        media.className = 'cart-item-media product-media ' + pmClasses[idx % pmClasses.length];

        var info = document.createElement('div');
        info.className = 'cart-item-info';
        var strong = document.createElement('strong');
        strong.textContent = item.name;
        var span = document.createElement('span');
        span.textContent = formatMoney(item.price) + ' · qty ' + item.qty;
        info.appendChild(strong);
        info.appendChild(span);

        var qtyWrap = document.createElement('div');
        qtyWrap.className = 'cart-item-qty';

        var minus = document.createElement('button');
        minus.className = 'qty-btn';
        minus.type = 'button';
        minus.setAttribute('aria-label', 'Decrease quantity of ' + item.name);
        minus.textContent = '−';
        minus.addEventListener('click', function () { changeQty(idx, -1); });

        var qtyLabel = document.createElement('span');
        qtyLabel.style.fontFamily = 'var(--font-mono)';
        qtyLabel.style.fontSize = '0.82rem';
        qtyLabel.style.minWidth = '1.2em';
        qtyLabel.style.textAlign = 'center';
        qtyLabel.textContent = item.qty;

        var plus = document.createElement('button');
        plus.className = 'qty-btn';
        plus.type = 'button';
        plus.setAttribute('aria-label', 'Increase quantity of ' + item.name);
        plus.textContent = '+';
        plus.addEventListener('click', function () { changeQty(idx, 1); });

        qtyWrap.appendChild(minus);
        qtyWrap.appendChild(qtyLabel);
        qtyWrap.appendChild(plus);

        row.appendChild(media);
        row.appendChild(info);
        row.appendChild(qtyWrap);
        cartItemsEl.appendChild(row);
      });
    }
    if (cartTotalEl) cartTotalEl.textContent = formatMoney(totalPrice());
  }

  function changeQty(idx, delta) {
    if (!cart[idx]) return;
    cart[idx].qty += delta;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
    renderCart();
  }

  function addToCart(name, price, btn) {
    var existing = cart.find(function (i) { return i.name === name; });
    if (existing) existing.qty += 1;
    else cart.push({ name: name, price: price, qty: 1 });
    renderCart();
    bumpBadge();
    showToast('Added "' + name + '" to cart');
    if (btn) {
      var originalHTML = btn.innerHTML;
      btn.classList.add('added');
      btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg> Added';
      window.setTimeout(function () {
        btn.classList.remove('added');
        btn.innerHTML = originalHTML;
      }, 1400);
    }
  }

  function bumpBadge() {
    if (!cartBadge) return;
    cartBadge.classList.remove('bump');
    void cartBadge.offsetWidth;
    cartBadge.classList.add('bump');
  }

  var toastTimer = null;
  function showToast(message) {
    if (!toast) return;
    if (toastText) toastText.textContent = message || 'Added to cart';
    toast.classList.add('show');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(function () { toast.classList.remove('show'); }, 2200);
  }

  document.querySelectorAll('.add-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var name = btn.getAttribute('data-name');
      var price = parseFloat(btn.getAttribute('data-price'));
      addToCart(name, price, btn);
    });
  });

  function openCart() {
    if (!cartDrawer) return;
    cartDrawer.classList.add('open');
    cartDrawer.setAttribute('aria-hidden', 'false');
    if (scrim) scrim.classList.add('show');
    if (cartClose) cartClose.focus();
  }
  function closeCart() {
    if (!cartDrawer) return;
    cartDrawer.classList.remove('open');
    cartDrawer.setAttribute('aria-hidden', 'true');
    if (scrim && !(mobileMenu && mobileMenu.classList.contains('open'))) scrim.classList.remove('show');
    if (cartToggle) cartToggle.focus();
  }
  if (cartToggle) cartToggle.addEventListener('click', openCart);
  if (cartClose)  cartClose.addEventListener('click', closeCart);

  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', function () {
      if (!checkoutMsg) return;
      if (cart.length === 0) {
        checkoutMsg.textContent = 'Your bag is empty — add something first!';
      } else {
        checkoutMsg.textContent = 'This is a concept — checkout isn’t connected yet. Total would be ' + formatMoney(totalPrice()) + '.';
      }
      window.setTimeout(function () { checkoutMsg.textContent = ''; }, 5000);
    });
  }

  /* Shared scrim closes whichever panel is open */
  if (scrim) {
    scrim.addEventListener('click', function () {
      if (mobileMenu && mobileMenu.classList.contains('open')) closeMenu();
      if (cartDrawer && cartDrawer.classList.contains('open')) closeCart();
    });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      if (mobileMenu && mobileMenu.classList.contains('open')) closeMenu();
      if (cartDrawer && cartDrawer.classList.contains('open')) closeCart();
    }
  });

  /* ============ NEWSLETTER FORM ============ */
  var newsletterForm = document.getElementById('newsletterForm');
  var newsletterMsg  = document.getElementById('newsletterMsg');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var emailInput = document.getElementById('newsletterEmail');
      if (!emailInput || emailInput.value.trim() === '') {
        if (newsletterMsg) newsletterMsg.textContent = 'Please enter an email address.';
        return;
      }
      if (newsletterMsg) newsletterMsg.textContent = 'You’re on the list! Watch your inbox for new batches and restocks.';
      newsletterForm.reset();
    });
  }

  /* ============ CONTACT FORM ============ */
  var contactForm = document.getElementById('contactForm');
  var contactMsg  = document.getElementById('contactMsg');
  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = document.getElementById('cf-name');
      var email = document.getElementById('cf-email');
      if (!name || name.value.trim() === '' || !email || email.value.trim() === '') {
        if (contactMsg) contactMsg.textContent = 'Please add your name and email so we can reply.';
        return;
      }
      if (contactMsg) contactMsg.textContent = 'Thanks! This is a concept form — in a live store your message would reach shop@diamondandridge.test.';
      contactForm.reset();
    });
  }

  /* ============ SCROLL REVEAL ============ */
  if ('IntersectionObserver' in window) {
    document.documentElement.classList.add('js-reveal-ready');
    var revealObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal').forEach(function (el) { revealObserver.observe(el); });
  }

  /* Initial render */
  renderCart();
})();
