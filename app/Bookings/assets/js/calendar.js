/**
 * Pressroots Reserve — admin calendar.
 *
 * A dependency-free calendar with four views (Month / Week / Day / List) over
 * the REST /calendar feed. Times are read as the site's wall-clock from the
 * feed's ISO strings, so what an owner sees matches the site timezone
 * regardless of their browser's zone.
 */
(function () {
	'use strict';

	var I = window.PRT_CAL_I18N || {};
	var HOUR = 46; // px per hour in time-grid views

	function h(tag, attrs, kids) {
		var el = document.createElement(tag);
		attrs = attrs || {};
		Object.keys(attrs).forEach(function (k) {
			if (k === 'class') el.className = attrs[k];
			else if (k === 'html') el.innerHTML = attrs[k];
			else if (k === 'text') el.textContent = attrs[k];
			else if (k === 'style') el.style.cssText = attrs[k];
			else if (k.indexOf('on') === 0 && typeof attrs[k] === 'function') el.addEventListener(k.slice(2), attrs[k]);
			else if (attrs[k] != null && attrs[k] !== false) el.setAttribute(k, attrs[k]);
		});
		(kids || []).forEach(function (c) { if (c != null) el.appendChild(typeof c === 'string' ? document.createTextNode(c) : c); });
		return el;
	}
	function pad(n) { return n < 10 ? '0' + n : '' + n; }
	function ymd(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
	function fmt(str, v) { return String(str).replace('%d', v).replace('%s', v); }
	function addDays(d, n) { var x = new Date(d); x.setDate(x.getDate() + n); return x; }
	function startOfWeek(d, sow) { var x = new Date(d); var diff = (x.getDay() - sow + 7) % 7; x.setDate(x.getDate() - diff); x.setHours(0, 0, 0, 0); return x; }
	// Wall-clock minutes from an ISO string like 2026-07-13T14:30:00-04:00 (ignores offset on purpose).
	function isoMinutes(iso) { var m = /T(\d{2}):(\d{2})/.exec(iso || ''); return m ? (parseInt(m[1], 10) * 60 + parseInt(m[2], 10)) : 0; }

	function Calendar(root) {
		var restUrl = root.getAttribute('data-rest');
		var nonce = root.getAttribute('data-nonce');
		var sow = parseInt(root.getAttribute('data-start-of-week'), 10) || 0;
		var todayStr = root.getAttribute('data-today');
		var cursor = new Date(todayStr + 'T00:00:00');
		var view = 'month';
		var cache = {}; // rangeKey -> events

		function rangeFor() {
			var start, end;
			if (view === 'month') {
				var first = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
				start = startOfWeek(first, sow);
				end = addDays(start, 42);
			} else if (view === 'week') {
				start = startOfWeek(cursor, sow); end = addDays(start, 7);
			} else if (view === 'day') {
				start = new Date(cursor); start.setHours(0, 0, 0, 0); end = addDays(start, 1);
			} else { // list — a 60-day forward window from the month start
				start = new Date(cursor.getFullYear(), cursor.getMonth(), 1); end = addDays(start, 62);
			}
			return { start: addDays(start, -1), end: addDays(end, 1) };
		}

		function load() {
			var r = rangeFor();
			var key = ymd(r.start) + '_' + ymd(r.end);
			if (cache[key]) { render(cache[key]); return; }
			root.querySelector('.prt-cal-body') && (root.querySelector('.prt-cal-body').innerHTML = '<p class="prt-cal-loading">' + (I.today ? '…' : '…') + '</p>');
			var startTs = Math.floor(new Date(r.start).getTime() / 1000);
			var endTs = Math.floor(new Date(r.end).getTime() / 1000);
			fetch(restUrl + '?start=' + startTs + '&end=' + endTs, { headers: { 'X-WP-Nonce': nonce } })
				.then(function (res) { return res.json(); })
				.then(function (j) { cache[key] = (j && j.events) || []; render(cache[key]); })
				.catch(function () { cache[key] = []; render([]); });
		}

		function eventsOn(events, dateStr) {
			return events.filter(function (e) { return e.date === dateStr; })
				.sort(function (a, b) { return a.start - b.start; });
		}

		/* ── Chrome ─────────────────────────────────────────────────────── */
		function title() {
			if (view === 'day') {
				var d = cursor;
				return I.days[d.getDay()] + ', ' + I.months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
			}
			if (view === 'week') {
				var s = startOfWeek(cursor, sow), e = addDays(s, 6);
				if (s.getMonth() === e.getMonth()) return I.months[s.getMonth()] + ' ' + s.getDate() + '–' + e.getDate() + ', ' + s.getFullYear();
				return I.months[s.getMonth()] + ' ' + s.getDate() + ' – ' + I.months[e.getMonth()] + ' ' + e.getDate() + ', ' + e.getFullYear();
			}
			return I.months[cursor.getMonth()] + ' ' + cursor.getFullYear();
		}

		function step(dir) {
			if (view === 'month' || view === 'list') cursor = new Date(cursor.getFullYear(), cursor.getMonth() + dir, 1);
			else if (view === 'week') cursor = addDays(cursor, 7 * dir);
			else cursor = addDays(cursor, dir);
			load();
		}

		function toolbar() {
			var nav = h('div', { class: 'prt-cal-nav' }, [
				h('button', { class: 'button prt-cal-btn', 'aria-label': I.prev, onclick: function () { step(-1); }, html: '&#8249;' }),
				h('button', { class: 'button prt-cal-today', onclick: function () { cursor = new Date(todayStr + 'T00:00:00'); load(); }, text: I.today }),
				h('button', { class: 'button prt-cal-btn', 'aria-label': I.next, onclick: function () { step(1); }, html: '&#8250;' })
			]);
			var views = h('div', { class: 'prt-cal-views' }, ['month', 'week', 'day', 'list'].map(function (v) {
				return h('button', {
					class: 'button prt-cal-view' + (view === v ? ' is-active' : ''),
					onclick: function () { view = v; load(); }, text: I.views[v]
				});
			}));
			return h('div', { class: 'prt-cal-toolbar' }, [nav, h('h2', { class: 'prt-cal-title', text: title() }), views]);
		}

		function eventEl(e, opts) {
			opts = opts || {};
			var cls = 'prt-cal-ev prt-cal-ev--' + e.status;
			var label = (opts.time !== false ? e.time + ' ' : '') + e.title + ' — ' + e.name;
			return h('a', {
				class: cls, href: e.edit || '#', title: label + (e.party > 1 ? ' (' + fmt(I.party, e.party) + ')' : ''),
				style: opts.style || ''
			}, [
				opts.time !== false ? h('span', { class: 'prt-cal-ev__t', text: e.time }) : null,
				h('span', { class: 'prt-cal-ev__x', text: e.title + ' · ' + e.name + (e.party > 1 ? ' (' + e.party + ')' : '') })
			]);
		}

		/* ── Views ──────────────────────────────────────────────────────── */
		function renderMonth(events, body) {
			var first = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
			var gridStart = startOfWeek(first, sow);
			var head = h('div', { class: 'prt-cal-dow' });
			for (var i = 0; i < 7; i++) head.appendChild(h('div', { class: 'prt-cal-dow__c', text: I.days[(sow + i) % 7] }));
			body.appendChild(head);

			var grid = h('div', { class: 'prt-cal-month' });
			for (var d = 0; d < 42; d++) {
				var day = addDays(gridStart, d);
				var ds = ymd(day);
				var inMonth = day.getMonth() === cursor.getMonth();
				var isToday = ds === todayStr;
				var cell = h('div', { class: 'prt-cal-cell' + (inMonth ? '' : ' is-out') + (isToday ? ' is-today' : '') });
				cell.appendChild(h('button', {
					class: 'prt-cal-cell__n', text: String(day.getDate()),
					onclick: (function (dd) { return function () { cursor = new Date(dd); view = 'day'; load(); }; })(day)
				}));
				var dayEvents = eventsOn(events, ds);
				var shown = dayEvents.slice(0, 3);
				shown.forEach(function (e) { cell.appendChild(eventEl(e, {})); });
				if (dayEvents.length > 3) {
					cell.appendChild(h('button', {
						class: 'prt-cal-more', text: fmt(I.more, dayEvents.length - 3),
						onclick: (function (dd) { return function () { cursor = new Date(dd); view = 'day'; load(); }; })(day)
					}));
				}
				grid.appendChild(cell);
			}
			body.appendChild(grid);
		}

		function timeGrid(events, body, days) {
			// header — column template matches the body grid below exactly.
			var cols = '56px repeat(' + days.length + ',1fr)';
			var head = h('div', { class: 'prt-cal-tg-head', style: 'display:grid;grid-template-columns:' + cols });
			head.appendChild(h('div', { class: 'prt-cal-tg-gutter' }));
			days.forEach(function (day) {
				var ds = ymd(day);
				head.appendChild(h('div', { class: 'prt-cal-tg-daylabel' + (ds === todayStr ? ' is-today' : '') }, [
					h('span', { class: 'prt-cal-tg-dow', text: I.days[day.getDay()] }),
					h('span', { class: 'prt-cal-tg-date', text: String(day.getDate()) })
				]));
			});
			body.appendChild(head);

			var scroll = h('div', { class: 'prt-cal-tg-scroll' });
			var grid = h('div', { class: 'prt-cal-tg', style: 'grid-template-columns:56px repeat(' + days.length + ',1fr)' });
			// hour gutter
			var gutter = h('div', { class: 'prt-cal-tg-hours' });
			for (var hr = 0; hr < 24; hr++) {
				gutter.appendChild(h('div', { class: 'prt-cal-tg-hour', style: 'height:' + HOUR + 'px' },
					[h('span', { text: (hr === 0 ? '12a' : hr < 12 ? hr + 'a' : hr === 12 ? '12p' : (hr - 12) + 'p') })]));
			}
			grid.appendChild(gutter);

			days.forEach(function (day) {
				var col = h('div', { class: 'prt-cal-tg-col', style: 'height:' + (HOUR * 24) + 'px' });
				// hour lines
				for (var l = 0; l < 24; l++) col.appendChild(h('div', { class: 'prt-cal-tg-line', style: 'top:' + (l * HOUR) + 'px' }));
				// events with lane layout
				var evs = eventsOn(events, ymd(day)).map(function (e) {
					return { e: e, s: isoMinutes(e.startISO), en: Math.max(isoMinutes(e.endISO), isoMinutes(e.startISO) + 20) };
				});
				var lanes = [];
				evs.forEach(function (item) {
					var placed = false;
					for (var li = 0; li < lanes.length; li++) {
						if (lanes[li] <= item.s) { item.lane = li; lanes[li] = item.en; placed = true; break; }
					}
					if (!placed) { item.lane = lanes.length; lanes.push(item.en); }
				});
				var laneCount = Math.max(1, lanes.length);
				evs.forEach(function (item) {
					var top = (item.s / 60) * HOUR;
					var height = Math.max(22, ((item.en - item.s) / 60) * HOUR - 2);
					var w = 100 / laneCount;
					col.appendChild(eventEl(item.e, {
						time: true,
						style: 'position:absolute;top:' + top + 'px;height:' + height + 'px;left:calc(' + (item.lane * w) + '% + 2px);width:calc(' + w + '% - 4px);'
					}));
				});
				grid.appendChild(col);
			});
			scroll.appendChild(grid);
			body.appendChild(scroll);
			// scroll to 8am
			setTimeout(function () { scroll.scrollTop = 8 * HOUR; }, 0);
		}

		function renderList(events, body) {
			var upcoming = events.slice().sort(function (a, b) { return a.start - b.start; });
			if (!upcoming.length) { body.appendChild(h('p', { class: 'prt-cal-empty', text: I.noEvents })); return; }
			var wrap = h('div', { class: 'prt-cal-list' });
			var lastDate = null;
			upcoming.forEach(function (e) {
				if (e.date !== lastDate) {
					lastDate = e.date;
					var d = new Date(e.date + 'T00:00:00');
					wrap.appendChild(h('div', { class: 'prt-cal-list__day' + (e.date === todayStr ? ' is-today' : ''), text: I.days[d.getDay()] + ', ' + I.months[d.getMonth()] + ' ' + d.getDate() }));
				}
				wrap.appendChild(h('a', { class: 'prt-cal-list__row', href: e.edit || '#' }, [
					h('span', { class: 'prt-cal-list__time', text: e.time }),
					h('span', { class: 'prt-cal-list__title', text: e.title + ' — ' + e.name + (e.party > 1 ? ' · ' + fmt(I.party, e.party) : '') }),
					h('span', { class: 'prt-cal-pill prt-cal-pill--' + e.status, text: e.status === 'pending' ? I.pending : I.confirmed })
				]));
			});
			body.appendChild(wrap);
		}

		function render(events) {
			root.innerHTML = '';
			root.appendChild(toolbar());
			var body = h('div', { class: 'prt-cal-body prt-cal-body--' + view });
			root.appendChild(body);
			if (view === 'month') renderMonth(events, body);
			else if (view === 'week') timeGrid(events, body, (function () { var s = startOfWeek(cursor, sow), a = []; for (var i = 0; i < 7; i++) a.push(addDays(s, i)); return a; })());
			else if (view === 'day') timeGrid(events, body, [new Date(cursor)]);
			else renderList(events, body);
		}

		load();
	}

	function init() {
		var node = document.getElementById('prt-calendar');
		if (node) Calendar(node);
	}
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
	else init();
})();
