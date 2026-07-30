/* ==========================================================================
   Cannon & Crumb — ordering tools (loaded on menu.html and order.html)
   Menu category tabs, dietary filters, quantity steppers, and the full
   order cart: add/remove items, pickup/delivery toggle, running total,
   and a demo checkout. All in-memory (no storage). Every DOM lookup is
   guarded so the file is safe to load on any page.
   ========================================================================== */
(function(){
  "use strict";

  /* ---------------- Menu tabs ---------------- */
  var tabs = document.querySelectorAll('.menu-tab');
  if(tabs.length){
    tabs.forEach(function(tab){
      tab.addEventListener('click', function(){
        tabs.forEach(function(t){ t.setAttribute('aria-selected','false'); });
        tab.setAttribute('aria-selected','true');
        var target = tab.getAttribute('aria-controls');
        document.querySelectorAll('.menu-panel').forEach(function(panel){
          var isActive = panel.id === target;
          panel.hidden = !isActive;
          panel.classList.toggle('active', isActive);
        });
        applyFilters();
      });
    });
  }

  /* ---------------- Dietary filters ---------------- */
  var activeFilters = new Set();
  var chips = document.querySelectorAll('.filter-chip');
  chips.forEach(function(chip){
    chip.addEventListener('click', function(){
      var f = chip.getAttribute('data-filter');
      var pressed = chip.getAttribute('aria-pressed') === 'true';
      chip.setAttribute('aria-pressed', pressed ? 'false' : 'true');
      if(pressed){ activeFilters.delete(f); } else { activeFilters.add(f); }
      applyFilters();
    });
  });

  function applyFilters(){
    var visiblePanel = document.querySelector('.menu-panel.active');
    if(!visiblePanel) return;
    var cards = visiblePanel.querySelectorAll('.menu-card');
    var anyVisible = false;
    cards.forEach(function(card){
      var tagsAttr = (card.getAttribute('data-tags') || '').split(' ').filter(Boolean);
      var matches = true;
      activeFilters.forEach(function(f){
        if(tagsAttr.indexOf(f) === -1){ matches = false; }
      });
      card.classList.toggle('hide', !matches);
      if(matches) anyVisible = true;
    });
    var emptyNote = visiblePanel.querySelector('.empty-note');
    if(emptyNote){ emptyNote.classList.toggle('show', !anyVisible); }
  }

  /* ---------------- Quantity steppers ---------------- */
  var quantities = {};
  document.querySelectorAll('.menu-card .stepper').forEach(function(stepper){
    var id = stepper.getAttribute('data-stepper');
    if(!id) return;
    quantities[id] = 1;
    var display = stepper.querySelector('span');
    var minus = stepper.querySelector('.step-minus');
    var plus = stepper.querySelector('.step-plus');
    if(minus) minus.addEventListener('click', function(){
      quantities[id] = Math.max(1, quantities[id] - 1);
      display.textContent = quantities[id];
    });
    if(plus) plus.addEventListener('click', function(){
      quantities[id] = Math.min(20, quantities[id] + 1);
      display.textContent = quantities[id];
    });
  });

  /* ---------------- Cart state ---------------- */
  var cart = {}; // id -> {name, price, qty}
  var fulfillmentMode = 'pickup';
  var TAX_RATE = 0.06;
  var DELIVERY_FEE = 3.50;

  var cartBtn = document.getElementById('cart-btn');
  var cartCount = document.getElementById('cart-count');
  var orderPanel = document.getElementById('order-panel');
  var orderOverlay = document.getElementById('order-overlay');
  var orderClose = document.getElementById('order-close');
  var orderEmpty = document.getElementById('order-empty');
  var orderItemsEl = document.getElementById('order-items');
  var orderSummary = document.getElementById('order-summary');
  var fulfillmentToggle = document.getElementById('fulfillment-toggle');
  var cartAnnouncer = document.getElementById('cart-announcer');
  var checkoutBtn = document.getElementById('checkout-btn');

  // If the order panel isn't on this page, there's nothing more to wire up.
  if(!orderPanel){ return; }

  document.querySelectorAll('.add-btn').forEach(function(btn){
    btn.addEventListener('click', function(){
      var id = btn.getAttribute('data-add');
      var card = btn.closest('.menu-card');
      var name = card.getAttribute('data-name');
      var price = parseFloat(card.getAttribute('data-price'));
      var qty = quantities[id] || 1;

      if(cart[id]){
        cart[id].qty += qty;
      } else {
        cart[id] = {name:name, price:price, qty:qty};
      }

      quantities[id] = 1;
      var stepperSpan = card.querySelector('.stepper span');
      if(stepperSpan) stepperSpan.textContent = '1';

      btn.classList.add('added');
      var originalText = btn.textContent;
      btn.textContent = 'Added ✓';
      setTimeout(function(){
        btn.classList.remove('added');
        btn.textContent = originalText;
      }, 1200);

      renderCart();
      announce(qty + ' × ' + name + ' added to your order.');
      openPanel();
    });
  });

  function announce(msg){
    if(cartAnnouncer) cartAnnouncer.textContent = msg;
  }

  function getTotals(){
    var subtotal = 0, count = 0;
    Object.keys(cart).forEach(function(id){
      subtotal += cart[id].price * cart[id].qty;
      count += cart[id].qty;
    });
    var tax = subtotal * TAX_RATE;
    var deliveryFee = (fulfillmentMode === 'delivery' && count > 0) ? DELIVERY_FEE : 0;
    var total = subtotal + tax + deliveryFee;
    return {subtotal:subtotal, tax:tax, deliveryFee:deliveryFee, total:total, count:count};
  }

  function fmt(n){ return '$' + n.toFixed(2); }

  function renderCart(){
    var ids = Object.keys(cart);
    var totals = getTotals();

    if(cartCount) cartCount.textContent = totals.count;
    if(cartBtn) cartBtn.setAttribute('aria-label', 'Open your order, ' + totals.count + ' item' + (totals.count === 1 ? '' : 's'));

    if(ids.length === 0){
      if(orderEmpty) orderEmpty.style.display = 'block';
      if(orderSummary) orderSummary.style.display = 'none';
      if(fulfillmentToggle) fulfillmentToggle.style.display = 'none';
      if(orderItemsEl) orderItemsEl.innerHTML = '';
      return;
    }

    if(orderEmpty) orderEmpty.style.display = 'none';
    if(orderSummary) orderSummary.style.display = 'block';
    if(fulfillmentToggle) fulfillmentToggle.style.display = 'flex';

    orderItemsEl.innerHTML = '';
    ids.forEach(function(id){
      var item = cart[id];
      var row = document.createElement('div');
      row.className = 'order-item';
      row.innerHTML =
        '<div class="order-item-info">' +
          '<h4>' + escapeHtml(item.name) + '</h4>' +
          '<div class="order-item-price mono">' + fmt(item.price) + ' each</div>' +
          '<button type="button" class="order-item-remove" data-remove="' + id + '">Remove</button>' +
        '</div>' +
        '<div class="order-item-controls">' +
          '<div class="stepper">' +
            '<button type="button" class="cart-minus" data-cid="' + id + '" aria-label="Decrease ' + escapeHtml(item.name) + ' quantity">−</button>' +
            '<span aria-hidden="true">' + item.qty + '</span>' +
            '<button type="button" class="cart-plus" data-cid="' + id + '" aria-label="Increase ' + escapeHtml(item.name) + ' quantity">+</button>' +
          '</div>' +
        '</div>';
      orderItemsEl.appendChild(row);
    });

    orderItemsEl.querySelectorAll('.cart-minus').forEach(function(b){
      b.addEventListener('click', function(){
        var id = b.getAttribute('data-cid');
        cart[id].qty -= 1;
        if(cart[id].qty <= 0){ delete cart[id]; }
        renderCart();
      });
    });
    orderItemsEl.querySelectorAll('.cart-plus').forEach(function(b){
      b.addEventListener('click', function(){
        var id = b.getAttribute('data-cid');
        cart[id].qty += 1;
        renderCart();
      });
    });
    orderItemsEl.querySelectorAll('[data-remove]').forEach(function(b){
      b.addEventListener('click', function(){
        var id = b.getAttribute('data-remove');
        var name = cart[id] ? cart[id].name : '';
        delete cart[id];
        renderCart();
        announce(name + ' removed from your order.');
      });
    });

    setText('sum-subtotal', fmt(totals.subtotal));
    setText('sum-tax', fmt(totals.tax));
    setText('sum-delivery', fmt(totals.deliveryFee));
    var delRow = document.getElementById('sum-delivery-row');
    if(delRow) delRow.style.display = (fulfillmentMode === 'delivery') ? 'flex' : 'none';
    setText('sum-total', fmt(totals.total));
  }

  function setText(id, val){
    var el = document.getElementById(id);
    if(el) el.textContent = val;
  }

  function escapeHtml(str){
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  /* Fulfillment toggle */
  function wireFulfillment(){
    if(!fulfillmentToggle) return;
    fulfillmentToggle.querySelectorAll('button').forEach(function(b){
      b.addEventListener('click', function(){
        fulfillmentToggle.querySelectorAll('button').forEach(function(x){ x.setAttribute('aria-pressed','false'); });
        b.setAttribute('aria-pressed','true');
        fulfillmentMode = b.getAttribute('data-mode');
        renderCart();
      });
    });
  }
  wireFulfillment();

  /* ---------------- Open/close order panel ---------------- */
  var lastFocusedEl = null;

  function openPanel(){
    lastFocusedEl = document.activeElement;
    orderPanel.classList.add('open');
    if(orderOverlay) orderOverlay.classList.add('open');
    orderPanel.setAttribute('aria-hidden', 'false');
    if(orderClose) orderClose.focus();
    document.body.style.overflow = 'hidden';
  }
  function closePanel(){
    orderPanel.classList.remove('open');
    if(orderOverlay) orderOverlay.classList.remove('open');
    orderPanel.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if(lastFocusedEl) lastFocusedEl.focus();
  }

  if(cartBtn){
    cartBtn.addEventListener('click', function(e){
      e.preventDefault();  // header cart is an <a href="order.html"> fallback
      openPanel();
    });
  }
  var heroOrderBtn = document.getElementById('hero-order-btn');
  if(heroOrderBtn){
    heroOrderBtn.addEventListener('click', function(e){ e.preventDefault(); openPanel(); });
  }
  if(orderClose) orderClose.addEventListener('click', closePanel);
  if(orderOverlay) orderOverlay.addEventListener('click', closePanel);
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape' && orderPanel.classList.contains('open')){ closePanel(); }
  });

  /* ---------------- Checkout demo ---------------- */
  if(checkoutBtn){
    checkoutBtn.addEventListener('click', function(e){
      e.preventDefault();
      var totals = getTotals();
      if(totals.count === 0) return;

      var orderNum = 'CC-' + Math.floor(1000 + Math.random() * 9000);
      var minsOut = fulfillmentMode === 'delivery' ? (35 + Math.floor(Math.random()*15)) : (12 + Math.floor(Math.random()*10));
      var now = new Date();
      var readyTime = new Date(now.getTime() + minsOut * 60000);
      var timeStr = readyTime.toLocaleTimeString([], {hour:'numeric', minute:'2-digit'});

      var body = document.getElementById('order-body');
      body.innerHTML =
        '<div class="confirmation">' +
          '<div class="check-circle"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>' +
          '<h3>Order placed — thank you!</h3>' +
          '<p class="order-number mono">' + orderNum + '</p>' +
          '<p>' + (fulfillmentMode === 'delivery' ? 'Estimated delivery' : 'Ready for pickup') + ' around <strong>' + timeStr + '</strong>.</p>' +
          '<p style="font-size:0.85rem;color:var(--espresso-soft);margin-top:1rem;">This is a demo checkout — no real payment was processed and no order was actually sent.</p>' +
          '<button class="btn btn-outline btn-sm" id="confirm-close" style="margin-top:1.5rem;">Close</button>' +
        '</div>';
      if(orderSummary) orderSummary.style.display = 'none';

      document.getElementById('confirm-close').addEventListener('click', function(){
        cart = {};
        fulfillmentMode = 'pickup';
        closePanel();
        setTimeout(function(){
          body.innerHTML =
            '<div class="order-empty" id="order-empty">' +
              '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>' +
              '<p>Your order is empty. Add something delicious from the menu.</p>' +
            '</div>' +
            '<div class="fulfillment-toggle" id="fulfillment-toggle" style="display:none;">' +
              '<button type="button" data-mode="pickup" aria-pressed="true">Pickup</button>' +
              '<button type="button" data-mode="delivery" aria-pressed="false">Delivery</button>' +
            '</div>' +
            '<div id="order-items" aria-live="polite"></div>';
          orderEmpty = document.getElementById('order-empty');
          orderItemsEl = document.getElementById('order-items');
          fulfillmentToggle = document.getElementById('fulfillment-toggle');
          wireFulfillment();
          if(orderSummary) orderSummary.style.display = 'none';
          renderCart();
        }, 350);
      });
    });
  }

  /* ---------------- init ---------------- */
  renderCart();
})();
