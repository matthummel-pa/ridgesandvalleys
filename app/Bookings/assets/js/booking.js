/**
 * Pressroots Reserve — front-end booking widget.
 *
 * Progressive flow: service → date → time → details → confirmation. All
 * availability comes from the REST engine; this script never decides what's
 * bookable, it only renders the engine's answers and posts the final choice.
 * No dependencies, no build step — plain ES5-ish DOM work.
 */
(function () {
	'use strict';

	function h(tag, attrs, children) {
		var el = document.createElement(tag);
		attrs = attrs || {};
		Object.keys(attrs).forEach(function (k) {
			if (k === 'class') { el.className = attrs[k]; }
			else if (k === 'html') { el.innerHTML = attrs[k]; }
			else if (k === 'text') { el.textContent = attrs[k]; }
			else if (k.indexOf('on') === 0 && typeof attrs[k] === 'function') { el.addEventListener(k.slice(2), attrs[k]); }
			else if (attrs[k] !== null && attrs[k] !== false) { el.setAttribute(k, attrs[k]); }
		});
		(children || []).forEach(function (c) {
			if (c == null) { return; }
			el.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
		});
		return el;
	}

	function sprintf(str, val) { return String(str).replace('%d', val).replace('%s', val); }

	function Widget(root) {
		var cfg;
		try { cfg = JSON.parse(root.getAttribute('data-prt-booking')); } catch (e) { return; }
		var S = cfg.strings;
		var stage = root.querySelector('.prt-bk-stage');
		var state = { service: null, date: null, slot: null, days: [] };

		function api(path, opts) {
			opts = opts || {};
			var url = cfg.rest + path;
			var init = { method: opts.method || 'GET', headers: { 'Content-Type': 'application/json' } };
			if (opts.body) { init.headers['X-PRT-Nonce'] = cfg.nonce; init.body = JSON.stringify(opts.body); }
			return fetch(url, init).then(function (r) {
				return r.json().then(function (j) { return { ok: r.ok, status: r.status, data: j }; });
			});
		}

		function loading() { stage.innerHTML = ''; stage.appendChild(h('p', { class: 'prt-bk-loading', text: S.loading })); }

		function crumbs(step) {
			var items = [S.choose, S.date, S.time, S.details];
			var wrap = h('ol', { class: 'prt-bk-crumbs' });
			items.forEach(function (label, i) {
				wrap.appendChild(h('li', { class: 'prt-bk-crumb' + (i === step ? ' is-active' : (i < step ? ' is-done' : '')) }, [label]));
			});
			return wrap;
		}

		function priceLabel(svc) { return svc.price ? svc.price : ''; }

		/* Step 1 — service picker */
		function stepService() {
			state.date = null; state.slot = null;
			stage.innerHTML = '';
			stage.appendChild(crumbs(0));
			var list = h('div', { class: 'prt-bk-services' });
			cfg.services.forEach(function (svc) {
				list.appendChild(h('button', {
					type: 'button', class: 'prt-bk-service', onclick: function () { chooseService(svc); }
				}, [
					h('span', { class: 'prt-bk-service__title', text: svc.title }),
					svc.desc ? h('span', { class: 'prt-bk-service__desc', text: svc.desc }) : null,
					h('span', { class: 'prt-bk-service__meta' }, [
						h('span', { class: 'prt-bk-chip', text: sprintf('%d min', svc.duration) }),
						svc.capacity > 1 ? h('span', { class: 'prt-bk-chip', text: sprintf('%d seats', svc.capacity) }) : null,
						priceLabel(svc) ? h('span', { class: 'prt-bk-chip prt-bk-chip--price', text: priceLabel(svc) }) : null
					])
				]));
			});
			stage.appendChild(list);
		}

		function chooseService(svc) {
			state.service = svc;
			loading();
			api('/days?service=' + svc.id).then(function (res) {
				state.days = (res.ok && res.data.open) ? res.data.open : [];
				stepDate();
			});
		}

		/* Step 2 — date strip */
		function stepDate() {
			state.slot = null;
			stage.innerHTML = '';
			stage.appendChild(crumbs(1));
			stage.appendChild(header(state.service.title, function () { stepService(); }));

			if (!state.days.length) {
				stage.appendChild(h('p', { class: 'prt-bk-empty', text: S.nodays }));
				return;
			}
			var strip = h('div', { class: 'prt-bk-dates' });
			state.days.forEach(function (d) {
				var dt = new Date(d + 'T00:00:00');
				strip.appendChild(h('button', {
					type: 'button', class: 'prt-bk-date' + (state.date === d ? ' is-active' : ''),
					onclick: function () { chooseDate(d); }
				}, [
					h('span', { class: 'prt-bk-date__dow', text: dt.toLocaleDateString(undefined, { weekday: 'short' }) }),
					h('span', { class: 'prt-bk-date__num', text: dt.getDate() }),
					h('span', { class: 'prt-bk-date__mon', text: dt.toLocaleDateString(undefined, { month: 'short' }) })
				]));
			});
			stage.appendChild(strip);
			stage.appendChild(h('div', { class: 'prt-bk-slots' }));
		}

		function chooseDate(d) {
			state.date = d;
			stepDate();
			var slotWrap = stage.querySelector('.prt-bk-slots');
			slotWrap.innerHTML = '';
			slotWrap.appendChild(h('p', { class: 'prt-bk-loading', text: S.loading }));
			api('/slots?service=' + state.service.id + '&date=' + encodeURIComponent(d)).then(function (res) {
				renderSlots((res.ok && res.data.slots) ? res.data.slots : []);
			});
		}

		function renderSlots(slots) {
			var wrap = stage.querySelector('.prt-bk-slots');
			if (!wrap) { return; }
			wrap.innerHTML = '';
			if (!slots.length) { wrap.appendChild(h('p', { class: 'prt-bk-empty', text: S.noslots })); return; }
			var grid = h('div', { class: 'prt-bk-slotgrid' });
			slots.forEach(function (slot) {
				grid.appendChild(h('button', {
					type: 'button', class: 'prt-bk-slot', onclick: function () { chooseSlot(slot); }
				}, [
					h('span', { class: 'prt-bk-slot__time', text: slot.label }),
					state.service.capacity > 1 ? h('span', { class: 'prt-bk-slot__left', text: sprintf(S.seatsleft, slot.left) }) : null
				]));
			});
			wrap.appendChild(grid);
		}

		function chooseSlot(slot) { state.slot = slot; stepDetails(); }

		/* Step 3 — details form */
		function stepDetails() {
			stage.innerHTML = '';
			stage.appendChild(crumbs(3));
			var dt = new Date(state.date + 'T00:00:00');
			var summary = state.service.title + ' · ' + dt.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' }) + ' · ' + state.slot.label;
			stage.appendChild(header(summary, function () { stepDate(); }));

			var seats = state.service.capacity > 1;
			var partyOpts = [];
			var max = Math.min(state.slot.left, state.service.capacity);
			for (var i = 1; i <= max; i++) { partyOpts.push(h('option', { value: i, text: String(i) })); }

			var form = h('form', { class: 'prt-bk-form', novalidate: 'novalidate' });
			var err = h('p', { class: 'prt-bk-formerror', role: 'alert' });
			err.style.display = 'none';

			var fName = field(S.name + ' *', h('input', { type: 'text', name: 'name', required: 'required', autocomplete: 'name' }));
			var fEmail = field(S.email + ' *', h('input', { type: 'email', name: 'email', required: 'required', autocomplete: 'email' }));
			var fPhone = field(S.phone, h('input', { type: 'tel', name: 'phone', autocomplete: 'tel' }));
			var partySel = h('select', { name: 'party' }, partyOpts);
			var fParty = seats ? field(S.party, partySel) : null;
			var fNotes = field(S.notes, h('textarea', { name: 'notes', rows: '2' }));
			// Honeypot.
			var hp = h('input', { type: 'text', name: 'company', tabindex: '-1', autocomplete: 'off' });
			var hpWrap = h('div', { 'aria-hidden': 'true' }, [hp]);
			hpWrap.style.position = 'absolute'; hpWrap.style.left = '-5000px';

			var submit = h('button', { type: 'submit', class: 'prt-bk-submit', text: S.book });

			[err, fName, fEmail, fPhone, fParty, fNotes, hpWrap, submit].forEach(function (n) { if (n) { form.appendChild(n); } });

			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var name = form.name.value.trim();
				var email = form.email.value.trim();
				if (!name || !email) { err.textContent = S.required; err.style.display = ''; return; }
				submit.disabled = true; submit.textContent = S.booking; err.style.display = 'none';
				api('/book', {
					method: 'POST',
					body: {
						service: state.service.id, start: state.slot.start,
						name: name, email: email, phone: form.phone.value.trim(),
						party: seats ? parseInt(partySel.value, 10) : 1,
						notes: form.notes.value.trim(), company: hp.value
					}
				}).then(function (res) {
					if (res.ok && res.data.ok) { stepDone(res.data); }
					else {
						err.textContent = (res.data && res.data.message) ? res.data.message : S.error;
						err.style.display = ''; submit.disabled = false; submit.textContent = S.book;
						if (res.status === 409) { setTimeout(function () { chooseDate(state.date); }, 1400); }
					}
				}).catch(function () {
					err.textContent = S.error; err.style.display = ''; submit.disabled = false; submit.textContent = S.book;
				});
			});

			stage.appendChild(form);
		}

		/* Step 4 — confirmation */
		function stepDone(data) {
			stage.innerHTML = '';
			var pending = data.status === 'pending';
			var card = h('div', { class: 'prt-bk-done' + (pending ? ' is-pending' : '') }, [
				h('div', { class: 'prt-bk-done__mark', html: pending ? '&#9200;' : '&#10003;' }),
				h('h3', { class: 'prt-bk-done__title', text: pending ? S.pending : S.confirmed }),
				h('p', { class: 'prt-bk-done__when', text: data.service + ' · ' + data.when }),
				pending ? h('p', { class: 'prt-bk-done__msg', text: S.pendingmsg }) : null,
				data.success_text ? h('p', { class: 'prt-bk-done__note', text: data.success_text }) : null,
				h('button', {
					type: 'button', class: 'prt-bk-again', text: S.another,
					onclick: function () { state = { service: null, date: null, slot: null, days: [] }; start(); }
				})
			]);
			stage.appendChild(card);
		}

		/* helpers */
		function header(title, onBack) {
			return h('div', { class: 'prt-bk-head' }, [
				h('button', { type: 'button', class: 'prt-bk-back', text: S.back, onclick: onBack }),
				h('span', { class: 'prt-bk-head__title', text: title })
			]);
		}
		function field(label, control) {
			var id = 'prt-bk-' + Math.random().toString(36).slice(2, 8);
			control.id = id;
			return h('label', { class: 'prt-bk-field', for: id }, [h('span', { class: 'prt-bk-field__label', text: label }), control]);
		}

		function start() {
			if (cfg.preset) {
				var found = cfg.services.filter(function (s) { return s.id === cfg.preset; })[0];
				if (found) { chooseService(found); return; }
			}
			if (cfg.services.length === 1) { chooseService(cfg.services[0]); return; }
			stepService();
		}

		start();
	}

	function init() {
		var nodes = document.querySelectorAll('.prt-booking[data-prt-booking]');
		Array.prototype.forEach.call(nodes, function (n) {
			if (!n.getAttribute('data-prt-init')) { n.setAttribute('data-prt-init', '1'); Widget(n); }
		});
	}

	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); }
	else { init(); }
})();
