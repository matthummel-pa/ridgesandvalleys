/* ============================================================
   Ridgeline Outfitters — Shop tools (shop.html only)
   Product grid + category filter + wishlist + quick-view modal
   + cart drawer with free-shipping progress bar.
   Vanilla JS, memory-only state (no storage).
   ============================================================ */
(function(){
  "use strict";

  /* ---------- Product data ---------- */
  var PRODUCTS = [
    {
      id:"p1", name:"Chestnut Ridge Flannel Overshirt", cat:"Apparel", price:68,
      sizes:["S","M","L","XL"], photo:"photo-1",
      desc:"Brushed cotton flannel in a deep pine plaid, built heavy enough to work as a light jacket on cool-morning battlefield walks.",
      icon: "shirt"
    },
    {
      id:"p2", name:"Blaze Point Insulated Vest", cat:"Apparel", price:89,
      sizes:["S","M","L","XL"], photo:"photo-2",
      desc:"600-fill insulation with a wind-resistant shell — our top seller for shoulder-season hikes when mornings run cold.",
      icon:"vest"
    },
    {
      id:"p3", name:"Seminary Ridge Trail Runner", cat:"Footwear", price:129,
      sizes:["8","9","10","11","12"], photo:"photo-3",
      desc:"Low-profile trail shoe with a sticky rubber outsole, tuned for the rocky, root-crossed paths around the battlefield trails.",
      icon:"boot"
    },
    {
      id:"p4", name:"Herr Ridge Waterproof Hiking Boot", cat:"Footwear", price:159,
      sizes:["8","9","10","11","12"], photo:"photo-4",
      desc:"Full-grain leather, waterproof membrane, and a stiffened shank for carrying weight comfortably on multi-day trips.",
      icon:"boot"
    },
    {
      id:"p5", name:"Cumberland 32L Daypack", cat:"Packs", price:98,
      sizes:["One Size"], photo:"photo-5",
      desc:"A do-everything 32-liter daypack with a padded hip belt and a dedicated hydration sleeve — our most-recommended pack.",
      icon:"pack"
    },
    {
      id:"p6", name:"Michaux 55L Overnight Pack", cat:"Packs", price:189,
      sizes:["S/M","M/L"], photo:"photo-6",
      desc:"Adjustable torso-length overnight pack built for two- to three-night trips into Michaux State Forest and beyond.",
      icon:"pack"
    },
    {
      id:"p7", name:"Round Top Two-Person Tent", cat:"Camp", price:229,
      sizes:["One Size"], photo:"photo-7",
      desc:"A freestanding 3-season tent that pitches in under five minutes — genuinely, we've timed it at the counter.",
      icon:"tent"
    },
    {
      id:"p8", name:"Cavalry Camp Percolator Kit", cat:"Camp", price:54,
      sizes:["One Size"], photo:"photo-8",
      desc:"Stainless percolator, two enamel mugs, and a mesh stuff sack — the whole reason your campsite smells like coffee at dawn.",
      icon:"pot"
    }
  ];

  var ICONS = {
    shirt: '<svg viewBox="0 0 64 64" fill="none" stroke="#f8f6f0" stroke-width="2.5" stroke-linejoin="round"><path d="M22 8L10 16l4 8 6-3v27a2 2 0 002 2h20a2 2 0 002-2V21l6 3 4-8-12-8-4 4h-12z"/></svg>',
    vest: '<svg viewBox="0 0 64 64" fill="none" stroke="#f8f6f0" stroke-width="2.5" stroke-linejoin="round"><path d="M24 8l-8 6v10l6-3v27a2 2 0 002 2h16a2 2 0 002-2V21l6 3v-10l-8-6-4 4h-12z"/><path d="M24 8l8 12 8-12" /></svg>',
    boot: '<svg viewBox="0 0 64 64" fill="none" stroke="#111a15" stroke-width="2.5" stroke-linejoin="round"><path d="M22 10v20l-14 8v6a2 2 0 002 2h34a4 4 0 004-4c0-6-6-8-10-10V10z"/><path d="M22 18h10"/></svg>',
    pack: '<svg viewBox="0 0 64 64" fill="none" stroke="#f8f6f0" stroke-width="2.5" stroke-linejoin="round"><rect x="16" y="18" width="32" height="34" rx="8"/><path d="M22 18v-4a10 10 0 0120 0v4"/><rect x="24" y="26" width="16" height="12" rx="2"/></svg>',
    tent: '<svg viewBox="0 0 64 64" fill="none" stroke="#f8f6f0" stroke-width="2.5" stroke-linejoin="round"><path d="M6 50L32 12l26 38z"/><path d="M32 12v38M20 50l12-18 12 18"/></svg>',
    pot: '<svg viewBox="0 0 64 64" fill="none" stroke="#111a15" stroke-width="2.5" stroke-linejoin="round"><path d="M16 28h32v18a6 6 0 01-6 6H22a6 6 0 01-6-6z"/><path d="M20 28V16a4 4 0 014-4h0a4 4 0 014 4v2M48 34h6v6a4 4 0 01-4 4h-2"/></svg>'
  };

  var NEW_ARRIVALS = [
    { name:"Powder Ridge Softshell Jacket", cat:"Apparel", price:142, photo:"photo-2" },
    { name:"Wheatfield Wool Beanie", cat:"Apparel", price:24, photo:"photo-1" },
    { name:"Devil's Den Approach Shoe", cat:"Footwear", price:118, photo:"photo-3" },
    { name:"Little Round Top Hip Pack", cat:"Packs", price:42, photo:"photo-6" },
    { name:"Codori Farm Camp Lantern", cat:"Camp", price:36, photo:"photo-7" },
    { name:"Trostle Trail Gaiters", cat:"Apparel", price:32, photo:"photo-4" }
  ];

  var FREE_SHIP_THRESHOLD = 75;

  /* ---------- State (memory only, no storage) ---------- */
  var cart = [];        // {id, size, qty}
  var wishlist = {};     // id -> true

  /* ---------- Helpers ---------- */
  function money(n){ return "$" + n.toFixed(2); }
  function findProduct(id){ for (var i=0;i<PRODUCTS.length;i++){ if (PRODUCTS[i].id===id) return PRODUCTS[i]; } return null; }
  function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
  function qsa(sel, ctx){ return Array.prototype.slice.call((ctx||document).querySelectorAll(sel)); }
  function prefersReducedMotion(){
    return window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  }

  var productGrid = qs("#productGrid");
  if (!productGrid) return; // not on the shop page

  /* ---------- Render product grid ---------- */
  var selectedSizes = {}; // productId -> size chosen on card

  function renderGrid(){
    productGrid.innerHTML = PRODUCTS.map(function(p){
      var sizesHTML = p.sizes.map(function(s){
        var pressed = (selectedSizes[p.id] === s);
        return '<button type="button" class="size-chip" data-pid="'+p.id+'" data-size="'+s+'" aria-pressed="'+pressed+'">'+s+'</button>';
      }).join("");

      return (
        '<article class="product-card" data-cat="'+p.cat+'" data-pid="'+p.id+'">' +
          '<div class="product-photo-wrap" style="position:relative;">' +
            '<div class="product-photo ' + p.photo + '">' +
              '<span class="cat-flag">'+p.cat+'</span>' +
              ICONS[p.icon] +
              '<button type="button" class="wishlist-btn" data-pid="'+p.id+'" aria-label="Add '+p.name+' to wishlist" aria-pressed="'+(!!wishlist[p.id])+'">' +
                '<svg viewBox="0 0 24 24"><path d="M12 21s-7.5-4.6-10-9.3C.4 8 2 4.5 5.6 4.1c2-.2 3.7.8 4.9 2.5 1.2-1.7 2.9-2.7 4.9-2.5C19 4.5 20.6 8 20 11.7 17.5 16.4 12 21 12 21z"/></svg>' +
              '</button>' +
              '<button type="button" class="quick-view-btn" data-pid="'+p.id+'">Quick View</button>' +
            '</div>' +
          '</div>' +
          '<div class="product-body">' +
            '<span class="product-cat">'+p.cat+'</span>' +
            '<h3 class="product-name">'+p.name+'</h3>' +
            '<p class="product-price mono">'+money(p.price)+'</p>' +
            '<div class="size-row" aria-label="Select a size">'+sizesHTML+'</div>' +
            '<button type="button" class="add-cart-btn" data-pid="'+p.id+'">' +
              '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 13h10l3-8H5.4M7 13L5.4 5M7 13l-1.7 6.4A1 1 0 006.3 20H18"/></svg>' +
              'Add to Cart' +
            '</button>' +
          '</div>' +
        '</article>'
      );
    }).join("");
  }

  function renderArrivals(){
    var track = qs("#arrivalsTrack");
    if (!track) return;
    track.innerHTML = NEW_ARRIVALS.map(function(p){
      return (
        '<div class="arrival-card">' +
          '<div class="arrival-photo product-photo ' + p.photo + '"></div>' +
          '<div class="arrival-body">' +
            '<span class="new-tag">New</span>' +
            '<div class="product-cat">'+p.cat+'</div>' +
            '<div class="product-name">'+p.name+'</div>' +
            '<div class="product-price mono">'+money(p.price)+'</div>' +
          '</div>' +
        '</div>'
      );
    }).join("");
  }

  /* ---------- Filter tabs ---------- */
  var filterTabs = qsa(".filter-tab");
  filterTabs.forEach(function(tab){
    tab.addEventListener("click", function(){
      filterTabs.forEach(function(t){ t.setAttribute("aria-pressed","false"); });
      tab.setAttribute("aria-pressed","true");
      applyFilter(tab.getAttribute("data-filter"));
    });
  });

  function applyFilter(cat){
    qsa(".product-card").forEach(function(card){
      var match = (cat === "All" || card.getAttribute("data-cat") === cat);
      card.classList.toggle("is-hidden", !match);
    });
  }

  /* Collection tiles jump to shop + filter */
  qsa(".collection-tile").forEach(function(tile){
    tile.addEventListener("click", function(){
      var cat = tile.getAttribute("data-jump");
      filterTabs.forEach(function(t){
        t.setAttribute("aria-pressed", t.getAttribute("data-filter") === cat ? "true" : "false");
      });
      applyFilter(cat);
      var shop = document.getElementById("shop");
      if (shop) shop.scrollIntoView({behavior: prefersReducedMotion() ? "auto" : "smooth", block:"start"});
    });
  });

  /* ---------- Delegate: size chips, wishlist, add-to-cart, quick view ---------- */
  productGrid.addEventListener("click", function(e){
    var sizeBtn = e.target.closest(".size-chip");
    if (sizeBtn){
      var pid = sizeBtn.getAttribute("data-pid");
      selectedSizes[pid] = sizeBtn.getAttribute("data-size");
      qsa('.size-chip[data-pid="'+pid+'"]', productGrid).forEach(function(b){
        b.setAttribute("aria-pressed", b === sizeBtn ? "true" : "false");
      });
      return;
    }
    var wishBtn = e.target.closest(".wishlist-btn");
    if (wishBtn){
      toggleWishlist(wishBtn.getAttribute("data-pid"), wishBtn);
      return;
    }
    var qvBtn = e.target.closest(".quick-view-btn");
    if (qvBtn){
      openQuickView(qvBtn.getAttribute("data-pid"));
      return;
    }
    var addBtn = e.target.closest(".add-cart-btn");
    if (addBtn){
      var pid2 = addBtn.getAttribute("data-pid");
      var product = findProduct(pid2);
      var size = selectedSizes[pid2] || product.sizes[0];
      addToCart(pid2, size, 1);
      return;
    }
  });

  function toggleWishlist(pid){
    wishlist[pid] = !wishlist[pid];
    var pressed = !!wishlist[pid];
    qsa('.wishlist-btn[data-pid="'+pid+'"]').forEach(function(b){
      b.setAttribute("aria-pressed", pressed);
    });
    updateWishlistCount();
  }

  function updateWishlistCount(){
    var n = Object.keys(wishlist).filter(function(k){ return wishlist[k]; }).length;
    var c = qs("#wishlistCount"); if (c) c.textContent = n;
    var b = qs("#wishlistHeaderBtn"); if (b) b.setAttribute("aria-label", "View wishlist ("+n+" items)");
  }

  var wlBtn = qs("#wishlistHeaderBtn");
  if (wlBtn){
    wlBtn.addEventListener("click", function(){
      var n = Object.keys(wishlist).filter(function(k){ return wishlist[k]; }).length;
      showToast(n === 0 ? "Your wishlist is empty — tap the heart on any item." : "You have " + n + " item" + (n===1?"":"s") + " saved to your wishlist.");
    });
  }

  /* ---------- Quick view modal ---------- */
  var qvModal = qs("#quickViewModal");
  var qvCurrentId = null;
  var qvSelectedSize = null;
  var lastFocused = null;

  function openQuickView(pid){
    var p = findProduct(pid);
    if (!p) return;
    qvCurrentId = pid;
    qvSelectedSize = selectedSizes[pid] || p.sizes[0];

    qs("#qvPhoto").innerHTML = '<div class="product-photo ' + p.photo + '" style="position:absolute;inset:0;">' + ICONS[p.icon] + '</div>';
    qs("#qvCat").textContent = p.cat;
    qs("#qvName").textContent = p.name;
    qs("#qvPrice").textContent = money(p.price);
    qs("#qvDesc").textContent = p.desc;
    qs("#qvSizeRow").innerHTML = p.sizes.map(function(s){
      return '<button type="button" class="size-chip" data-size="'+s+'" aria-pressed="'+(s===qvSelectedSize)+'">'+s+'</button>';
    }).join("");

    qvModal.classList.add("open");
    qvModal.setAttribute("aria-hidden","false");
    document.body.style.overflow = "hidden";
    lastFocused = document.activeElement;
    qs("#qvCloseBtn").focus();
  }

  function closeQuickView(){
    qvModal.classList.remove("open");
    qvModal.setAttribute("aria-hidden","true");
    document.body.style.overflow = "";
    if (lastFocused) lastFocused.focus();
  }

  qs("#qvSizeRow").addEventListener("click", function(e){
    var btn = e.target.closest(".size-chip");
    if (!btn) return;
    qvSelectedSize = btn.getAttribute("data-size");
    qsa(".size-chip", qs("#qvSizeRow")).forEach(function(b){
      b.setAttribute("aria-pressed", b === btn ? "true" : "false");
    });
  });

  qs("#qvAddBtn").addEventListener("click", function(){
    if (!qvCurrentId) return;
    addToCart(qvCurrentId, qvSelectedSize, 1);
    closeQuickView();
  });
  qs("#qvCloseBtn").addEventListener("click", closeQuickView);
  qvModal.addEventListener("click", function(e){
    if (e.target === qvModal) closeQuickView();
  });

  /* ---------- Cart logic ---------- */
  function addToCart(pid, size, qty){
    var existing = cart.find(function(item){ return item.id === pid && item.size === size; });
    if (existing){ existing.qty += qty; }
    else { cart.push({ id: pid, size: size, qty: qty }); }
    renderCart();
    showToast(findProduct(pid).name + " added to cart");
    openCart();
  }

  function changeQty(pid, size, delta){
    var item = cart.find(function(i){ return i.id === pid && i.size === size; });
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0){ cart = cart.filter(function(i){ return i !== item; }); }
    renderCart();
  }

  function removeItem(pid, size){
    cart = cart.filter(function(i){ return !(i.id === pid && i.size === size); });
    renderCart();
  }

  function cartSubtotal(){
    return cart.reduce(function(sum, item){
      var p = findProduct(item.id);
      return sum + (p ? p.price * item.qty : 0);
    }, 0);
  }

  function cartCount(){
    return cart.reduce(function(sum, item){ return sum + item.qty; }, 0);
  }

  function renderCart(){
    var body = qs("#cartBody");
    var foot = qs("#cartFoot");
    var count = cartCount();
    var subtotal = cartSubtotal();

    qs("#cartCount").textContent = count;
    qs("#cartOpenBtn").setAttribute("aria-label", "Open cart, "+count+" items");
    qs("#cartAnnounce").textContent = count + (count === 1 ? " item" : " items") + " in cart, subtotal " + money(subtotal);

    if (cart.length === 0){
      body.innerHTML =
        '<div class="cart-empty">' +
          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 13h10l3-8H5.4M7 13L5.4 5M7 13l-1.7 6.4A1 1 0 006.3 20H18"/></svg>' +
          '<p>Your cart is empty.<br>Add some gear to get started.</p>' +
        '</div>';
      foot.hidden = true;
      return;
    }

    var remaining = Math.max(0, FREE_SHIP_THRESHOLD - subtotal);
    var pct = Math.min(100, (subtotal / FREE_SHIP_THRESHOLD) * 100);
    var progressHTML =
      '<div class="ship-progress ' + (remaining <= 0 ? "done" : "") + '">' +
        '<p>' + (remaining <= 0 ? "You've unlocked free shipping!" : "You're " + money(remaining) + " away from free shipping") + '</p>' +
        '<div class="bar-track"><div class="bar-fill" style="width:' + pct + '%"></div></div>' +
      '</div>';

    var itemsHTML = cart.map(function(item){
      var p = findProduct(item.id);
      return (
        '<div class="cart-item">' +
          '<div class="cart-item-photo product-photo ' + p.photo + '"></div>' +
          '<div class="cart-item-info">' +
            '<div class="name">'+p.name+'</div>' +
            '<div class="meta">Size: '+item.size+'</div>' +
            '<div class="cart-item-controls">' +
              '<div class="qty-stepper">' +
                '<button type="button" aria-label="Decrease quantity of '+p.name+'" data-pid="'+p.id+'" data-size="'+item.size+'" data-delta="-1">−</button>' +
                '<span>'+item.qty+'</span>' +
                '<button type="button" aria-label="Increase quantity of '+p.name+'" data-pid="'+p.id+'" data-size="'+item.size+'" data-delta="1">+</button>' +
              '</div>' +
              '<span class="cart-item-price mono">'+money(p.price * item.qty)+'</span>' +
            '</div>' +
            '<button type="button" class="remove-link" data-pid="'+p.id+'" data-size="'+item.size+'">Remove</button>' +
          '</div>' +
        '</div>'
      );
    }).join("");

    body.innerHTML = progressHTML + itemsHTML;
    foot.hidden = false;
    qs("#cartSubtotal").textContent = money(subtotal);
  }

  qs("#cartBody").addEventListener("click", function(e){
    var stepBtn = e.target.closest("[data-delta]");
    if (stepBtn){
      changeQty(stepBtn.getAttribute("data-pid"), stepBtn.getAttribute("data-size"), parseInt(stepBtn.getAttribute("data-delta"),10));
      return;
    }
    var rmBtn = e.target.closest(".remove-link");
    if (rmBtn){
      removeItem(rmBtn.getAttribute("data-pid"), rmBtn.getAttribute("data-size"));
      return;
    }
  });

  /* ---------- Cart drawer open/close ---------- */
  var cartDrawer = qs("#cartDrawer");
  var overlay = qs("#overlay");

  function openCart(){
    cartDrawer.classList.add("open");
    cartDrawer.setAttribute("aria-hidden","false");
    overlay.classList.add("show");
    document.body.style.overflow = "hidden";
  }
  function closeCart(){
    cartDrawer.classList.remove("open");
    cartDrawer.setAttribute("aria-hidden","true");
    overlay.classList.remove("show");
    if (!qvModal.classList.contains("open")) document.body.style.overflow = "";
  }

  qs("#cartOpenBtn").addEventListener("click", openCart);
  qs("#cartCloseBtn").addEventListener("click", closeCart);
  overlay.addEventListener("click", function(){ closeCart(); closeQuickView(); });

  document.addEventListener("keydown", function(e){
    if (e.key === "Escape"){
      if (qvModal.classList.contains("open")) closeQuickView();
      else if (cartDrawer.classList.contains("open")) closeCart();
    }
  });

  qs("#checkoutBtn").addEventListener("click", function(){
    if (cart.length === 0) return;
    var total = money(cartSubtotal());
    cart = [];
    renderCart();
    closeCart();
    showToast("Thanks! Demo checkout complete — total " + total + " (nothing was charged).", 4200);
  });

  /* ---------- Toast ---------- */
  var toastTimer = null;
  function showToast(msg, duration){
    var toast = qs("#toast");
    if (!toast) return;
    qs("#toastMsg").textContent = msg;
    toast.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function(){ toast.classList.remove("show"); }, duration || 2600);
  }

  /* ---------- Init ---------- */
  renderGrid();
  renderArrivals();
  applyFilter("All");
  renderCart();
  updateWishlistCount();

})();
