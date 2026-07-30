/* =========================================================================
   Keystone Homes & Land — LISTINGS page tools
   - Listing data, filters, sort, grid render
   - Grid / map toggle + pins
   - Save hearts, detail modal
   - In-modal land-loan / mortgage estimate
   - Reads ?type=&price=&acreage=&township= from the home hero search
   ========================================================================= */
(function(){
  "use strict";

  /* ============================= DATA ============================= */
  var LISTINGS = [
    {
      id:1, type:"historic", typeLabel:"Historic Home", status:"active",
      title:"Baltimore Pike Brick Farmhouse (c.1890)",
      address:"1755 Baltimore Pike, Cumberland Township, PA 17325",
      township:"Cumberland", price:525000,
      beds:4, baths:2, sqft:2400, acres:8.2,
      grad:"linear-gradient(135deg,#9a3324,#c14a34)",
      desc:"A handsome 1890s brick farmhouse with original hardwood floors, a wraparound porch, and a restored bank barn. Eight-plus acres of gently rolling pasture bordered by mature hedgerow, minutes from downtown Gettysburg.",
      lat:28, lng:22
    },
    {
      id:2, type:"land", typeLabel:"Land / Acreage", status:"active",
      title:"Marsh Creek Land Parcel",
      address:"62 Marsh Creek Rd, Straban Township, PA 17325",
      township:"Straban", price:215000,
      beds:0, baths:0, sqft:0, acres:38,
      grad:"linear-gradient(135deg,#5c6f3e,#8fae5c)",
      desc:"Thirty-eight acres of mostly tillable ground along Marsh Creek, currently leased for row crops. Road frontage, public water available at the road, and a soil map on file showing strong Class II farmland.",
      lat:52, lng:62
    },
    {
      id:3, type:"farm", typeLabel:"Working Farm", status:"active",
      title:"Wheatland Farmhouse & Outbuildings",
      address:"1420 Fairfield Rd, Cumberland Township, PA 17325",
      township:"Cumberland", price:649000,
      beds:4, baths:2.5, sqft:2850, acres:12.4,
      grad:"linear-gradient(135deg,#d7a340,#a97a1f)",
      desc:"A well-kept 1970s farmhouse updated top to bottom, with a 40x60 pole barn, fenced pasture, and a spring-fed pond. Long been run as a small cattle operation; equally suited to a hobby farm or horse property.",
      lat:20, lng:38
    },
    {
      id:4, type:"home", typeLabel:"Home", status:"active",
      title:"Seminary Ridge Cottage",
      address:"980 Chambersburg Pike, Cumberland Township, PA 17325",
      township:"Cumberland", price:349900,
      beds:3, baths:2, sqft:1680, acres:0.6,
      grad:"linear-gradient(135deg,#4d5a5e,#7c8b8f)",
      desc:"A move-in-ready cottage a short walk from Seminary Ridge, with an updated kitchen, screened porch, and a level, fenced back yard. Ideal starter home or in-town pied-a-terre.",
      lat:34, lng:18
    },
    {
      id:5, type:"land", typeLabel:"Land / Acreage", status:"active",
      title:"Rock Creek Grazing Land",
      address:"215 Rock Creek Church Rd, Straban Township, PA 17325",
      township:"Straban", price:180000,
      beds:0, baths:0, sqft:0, acres:45,
      grad:"linear-gradient(135deg,#8fae5c,#5c6f3e)",
      desc:"Forty-five acres of fenced pasture along Rock Creek with a run-in shed and gravity-fed water. Long history as grazing ground for a beef cattle herd; also suitable for hay or a future homesite.",
      lat:58, lng:74
    },
    {
      id:6, type:"farm", typeLabel:"Working Farm", status:"pending",
      title:"Oak Ridge Orchard Farm",
      address:"4110 York Rd, Franklin Township, PA 17325",
      township:"Franklin", price:875000,
      beds:4, baths:3, sqft:3200, acres:60,
      grad:"linear-gradient(135deg,#a97a1f,#d7a340)",
      desc:"A sixty-acre producing apple and peach orchard with a renovated farmhouse, cold storage building, and roadside stand. A rare turn-key opportunity to continue an established Adams County orchard operation.",
      lat:70, lng:30
    },
    {
      id:7, type:"historic", typeLabel:"Historic Home", status:"active",
      title:"The Herr Homestead (c.1852)",
      address:"310 Herr's Ridge Rd, Franklin Township, PA 17325",
      township:"Franklin", price:795000,
      beds:5, baths:3, sqft:3600, acres:22,
      grad:"linear-gradient(135deg,#6f2015,#9a3324)",
      desc:"A stone-and-frame homestead dating to 1852, lovingly maintained through six generations. Original summer kitchen, restored bank barn, twenty-two acres, and documented Civil War-era provenance.",
      lat:78, lng:44
    },
    {
      id:8, type:"land", typeLabel:"Land / Acreage", status:"new",
      title:"Table Rock View Lot",
      address:"0 Table Rock Rd, Franklin Township, PA 17325",
      township:"Franklin", price:129000,
      beds:0, baths:0, sqft:0, acres:5.5,
      grad:"linear-gradient(135deg,#d7a340,#f2d98f)",
      desc:"A wooded 5.5-acre building lot with long-range views toward South Mountain. Perc-approved for a conventional septic system; electric at the road. A quiet, buildable spot minutes from Gettysburg.",
      lat:86, lng:56
    }
  ];

  if(!document.getElementById("listingGrid")) return; /* not the listings page */

  var savedListings = {};
  var pinnedId = null;

  var currency = function(n){
    return "$" + Math.round(n).toLocaleString("en-US");
  };

  /* ============================= ICONS ============================= */
  var ICONS = {
    bed:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a2 2 0 012-2h14a2 2 0 012 2v6"/><path d="M3 18v2M21 18v2"/><path d="M5 12V8a2 2 0 012-2h3v6"/></svg>',
    bath:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h16v3a4 4 0 01-4 4H8a4 4 0 01-4-4z"/><path d="M4 12V6a2 2 0 012-2 2 2 0 012 2"/><line x1="2" y1="19" x2="22" y2="19"/></svg>',
    sqft:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="1"/><path d="M9 3v18M3 9h6"/></svg>',
    acres:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l7 7M4 4h5M4 4v5"/><path d="M20 20l-7-7M20 20h-5M20 20v-5"/><path d="M14 4l-4 4M4 14l4 4"/></svg>'
  };

  var TYPE_COLOR = { home:"#9a3324", farm:"#5c6f3e", land:"#d7a340", historic:"#4d5a5e" };

  function specsHTML(l){
    var parts = [];
    if(l.type !== "land"){
      parts.push('<span>'+ICONS.bed+' '+l.beds+' bd</span>');
      parts.push('<span>'+ICONS.bath+' '+l.baths+' ba</span>');
      parts.push('<span>'+ICONS.sqft+' '+l.sqft.toLocaleString()+' sqft</span>');
    }
    parts.push('<span>'+ICONS.acres+' '+l.acres+' ac</span>');
    return parts.join("");
  }

  function statusLabel(s){
    return s === "active" ? "Active" : s === "pending" ? "Pending" : "New";
  }

  /* ============================= RENDER LISTINGS ============================= */
  var gridEl = document.getElementById("listingGrid");
  var emptyEl = document.getElementById("emptyState");
  var countEl = document.getElementById("resultCount");
  var pinsEl = document.getElementById("mapPins");

  function cardTemplate(l){
    var saved = !!savedListings[l.id];
    return (
      '<article class="card" id="card-'+l.id+'" data-id="'+l.id+'">' +
        '<div class="card-photo" style="background:'+l.grad+';">' +
          '<span class="status-tag status-'+l.status+'">'+statusLabel(l.status)+'</span>' +
          '<span class="card-tag">'+l.typeLabel+'</span>' +
          '<button type="button" class="save-heart" aria-label="Save '+l.title+'" aria-pressed="'+saved+'" data-save="'+l.id+'">' +
            '<svg viewBox="0 0 24 24"><path d="M12 21s-7.5-4.6-10-9.3C.5 8 2.4 4.5 6 4c2.1-.3 4 .8 6 3.1C14 4.8 15.9 3.7 18 4c3.6.5 5.5 4 4 7.7-2.5 4.7-10 9.3-10 9.3z"/></svg>' +
          '</button>' +
        '</div>' +
        '<div class="card-body">' +
          '<span class="card-price">'+currency(l.price)+'</span>' +
          '<h3 class="card-title">'+l.title+'</h3>' +
          '<p class="card-address"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg><span>'+l.address+'</span></p>' +
          '<div class="card-specs">'+specsHTML(l)+'</div>' +
          '<div class="card-actions">' +
            '<button type="button" class="btn btn-primary btn-sm" data-view="'+l.id+'">View details</button>' +
          '</div>' +
        '</div>' +
      '</article>'
    );
  }

  function getFilters(prefix){
    return {
      type: document.getElementById(prefix+"Type").value,
      price: document.getElementById(prefix+"Price").value,
      acreage: document.getElementById(prefix+"Acreage").value,
      township: document.getElementById(prefix+"Township").value
    };
  }

  function applyFilters(f){
    return LISTINGS.filter(function(l){
      if(f.type !== "all" && l.type !== f.type) return false;
      if(f.township !== "all" && l.township !== f.township) return false;
      if(f.price !== "all"){
        var pr = f.price.split("-").map(Number);
        if(l.price < pr[0] || l.price > pr[1]) return false;
      }
      if(f.acreage !== "all"){
        var ar = f.acreage.split("-").map(Number);
        if(l.acres < ar[0] || l.acres > ar[1]) return false;
      }
      return true;
    });
  }

  function sortListings(list, sortVal){
    var copy = list.slice();
    if(sortVal === "price-asc") copy.sort(function(a,b){return a.price-b.price;});
    else if(sortVal === "price-desc") copy.sort(function(a,b){return b.price-a.price;});
    else if(sortVal === "acreage-desc") copy.sort(function(a,b){return b.acres-a.acres;});
    return copy;
  }

  function renderPins(list){
    pinsEl.innerHTML = list.map(function(l){
      var color = TYPE_COLOR[l.type];
      return (
        '<button type="button" class="map-pin" style="left:'+l.lng+'%;top:'+l.lat+'%;" data-pin="'+l.id+'" aria-label="'+l.title+', '+currency(l.price)+'">' +
          '<span class="pin-price">'+currency(l.price)+'</span>' +
          '<svg viewBox="0 0 24 24"><path fill="'+color+'" stroke="#fffdf8" stroke-width="1.5" d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8z"/><circle cx="12" cy="10" r="3" fill="#fffdf8"/></svg>' +
        '</button>'
      );
    }).join("");
  }

  function render(){
    var f = getFilters("f");
    var sortVal = document.getElementById("fSort").value;
    var filtered = sortListings(applyFilters(f), sortVal);

    countEl.innerHTML = "<strong>"+filtered.length+"</strong> " + (filtered.length === 1 ? "property" : "properties") + " found";

    if(filtered.length === 0){
      gridEl.style.display = "none";
      emptyEl.style.display = "block";
    } else {
      gridEl.style.display = "grid";
      emptyEl.style.display = "none";
      gridEl.innerHTML = filtered.map(cardTemplate).join("");
    }
    renderPins(filtered);
    if(pinnedId){
      var c = document.getElementById("card-"+pinnedId);
      if(c) c.classList.add("pinned");
    }
  }

  /* filter listeners */
  ["fType","fPrice","fAcreage","fTownship","fSort"].forEach(function(id){
    document.getElementById(id).addEventListener("change", render);
  });
  document.getElementById("filterForm").addEventListener("submit", function(e){ e.preventDefault(); });

  document.getElementById("resetFilters").addEventListener("click", resetFilters);
  var emptyResetBtn = document.getElementById("emptyResetBtn");
  if(emptyResetBtn) emptyResetBtn.addEventListener("click", resetFilters);
  function resetFilters(){
    ["fType","fPrice","fAcreage","fTownship","fSort"].forEach(function(id){
      document.getElementById(id).selectedIndex = 0;
    });
    render();
  }

  /* ============================= HERO-SEARCH URL PARAMS ============================= */
  function applyUrlParams(){
    var params = new URLSearchParams(window.location.search);
    if(![...params.keys()].length) return;
    var map = {type:"fType", price:"fPrice", acreage:"fAcreage", township:"fTownship"};
    Object.keys(map).forEach(function(k){
      var val = params.get(k);
      if(val){
        var sel = document.getElementById(map[k]);
        var ok = Array.prototype.some.call(sel.options, function(o){ return o.value === val; });
        if(ok) sel.value = val;
      }
    });
  }
  applyUrlParams();

  /* card click delegation: view details, save heart */
  gridEl.addEventListener("click", function(e){
    var viewBtn = e.target.closest("[data-view]");
    var saveBtn = e.target.closest("[data-save]");
    if(viewBtn){
      openModal(Number(viewBtn.getAttribute("data-view")));
    } else if(saveBtn){
      toggleSave(saveBtn);
    }
  });

  function toggleSave(btn){
    var id = Number(btn.getAttribute("data-save"));
    savedListings[id] = !savedListings[id];
    btn.setAttribute("aria-pressed", !!savedListings[id]);
  }

  /* ============================= GRID / MAP TOGGLE ============================= */
  var gridBtn = document.getElementById("gridViewBtn");
  var mapBtn = document.getElementById("mapViewBtn");
  var mapView = document.getElementById("mapView");

  gridBtn.addEventListener("click", function(){
    gridBtn.classList.add("active"); gridBtn.setAttribute("aria-pressed","true");
    mapBtn.classList.remove("active"); mapBtn.setAttribute("aria-pressed","false");
    mapView.classList.remove("active");
    gridEl.style.display = LISTINGS.length ? "grid" : "none";
  });
  mapBtn.addEventListener("click", function(){
    mapBtn.classList.add("active"); mapBtn.setAttribute("aria-pressed","true");
    gridBtn.classList.remove("active"); gridBtn.setAttribute("aria-pressed","false");
    mapView.classList.add("active");
    gridEl.style.display = "none";
  });

  pinsEl.addEventListener("click", function(e){
    var pin = e.target.closest("[data-pin]");
    if(!pin) return;
    var id = Number(pin.getAttribute("data-pin"));
    pinnedId = id;
    document.querySelectorAll(".map-pin").forEach(function(p){p.classList.remove("pin-active");});
    pin.classList.add("pin-active");
    gridBtn.click();
    requestAnimationFrame(function(){
      var card = document.getElementById("card-"+id);
      if(card){
        card.scrollIntoView({behavior:"smooth", block:"center"});
        document.querySelectorAll(".card").forEach(function(c){c.classList.remove("pinned");});
        card.classList.add("pinned");
      }
    });
  });

  /* ============================= MODAL ============================= */
  var overlay = document.getElementById("modalOverlay");
  var lastFocused = null;

  function galleryTiles(l){
    var shades = [l.grad,
      "linear-gradient(135deg,#e5d5ab,#d7a340)",
      "linear-gradient(135deg,#7c8b8f,#4d5a5e)",
      "linear-gradient(135deg,#f2d98f,#a97a1f)"];
    return (
      '<div class="modal-gallery-main" style="background:'+shades[0]+';"></div>' +
      '<div style="background:'+shades[1]+';"></div>' +
      '<div style="background:'+shades[2]+';"></div>'
    );
  }

  function openModal(id){
    var l = LISTINGS.filter(function(x){return x.id === id;})[0];
    if(!l) return;
    lastFocused = document.activeElement;

    document.getElementById("modalGallery").innerHTML = galleryTiles(l);
    document.getElementById("modalTag").textContent = l.typeLabel + " · " + l.township + " Township";
    document.getElementById("modalTitle").textContent = l.title;
    document.getElementById("modalAddress").querySelector("span").textContent = l.address;
    document.getElementById("modalPrice").textContent = currency(l.price);
    var statusEl = document.getElementById("modalStatus");
    statusEl.textContent = statusLabel(l.status);
    statusEl.className = "status-tag status-"+l.status;
    document.getElementById("modalDesc").textContent = l.desc;

    var specs = [];
    if(l.type !== "land"){
      specs.push({v:l.beds,k:"Beds"});
      specs.push({v:l.baths,k:"Baths"});
      specs.push({v:l.sqft.toLocaleString(),k:"Sq Ft"});
    }
    specs.push({v:l.acres,k:"Acres"});
    document.getElementById("modalSpecs").innerHTML = specs.map(function(s){
      return '<div><strong>'+s.v+'</strong><span>'+s.k+'</span></div>';
    }).join("");

    document.getElementById("calcPrice").value = l.price;
    recalcMortgage();

    overlay.classList.add("open");
    document.body.style.overflow = "hidden";
    document.getElementById("modalCloseBtn").focus();
  }

  function closeModal(){
    overlay.classList.remove("open");
    document.body.style.overflow = "";
    if(lastFocused) lastFocused.focus();
  }

  document.getElementById("modalCloseBtn").addEventListener("click", closeModal);
  overlay.addEventListener("click", function(e){
    if(e.target === overlay) closeModal();
  });
  document.addEventListener("keydown", function(e){
    if(e.key === "Escape" && overlay.classList.contains("open")) closeModal();
  });
  document.getElementById("modalScheduleBtn").addEventListener("click", closeModal);
  document.getElementById("modalSaveBtn").addEventListener("click", function(){
    this.textContent = this.textContent === "Save Listing" ? "Saved ✓" : "Save Listing";
  });

  /* land-loan / mortgage calculator inside modal */
  function recalcMortgage(){
    var price = Number(document.getElementById("calcPrice").value) || 0;
    var downPct = Number(document.getElementById("calcDown").value) || 0;
    var rate = Number(document.getElementById("calcRate").value) || 0;
    var years = Number(document.getElementById("calcTerm").value) || 30;

    var loan = price * (1 - downPct/100);
    var monthlyRate = (rate/100)/12;
    var n = years*12;
    var payment = 0;
    if(loan > 0){
      if(monthlyRate === 0){
        payment = loan/n;
      } else {
        payment = loan * (monthlyRate * Math.pow(1+monthlyRate, n)) / (Math.pow(1+monthlyRate, n) - 1);
      }
    }
    document.getElementById("calcMonthly").textContent = currency(payment) + " /mo";
  }
  ["calcPrice","calcDown","calcRate","calcTerm"].forEach(function(id){
    document.getElementById(id).addEventListener("input", recalcMortgage);
    document.getElementById(id).addEventListener("change", recalcMortgage);
  });

  /* ============================= INIT ============================= */
  render();

})();
