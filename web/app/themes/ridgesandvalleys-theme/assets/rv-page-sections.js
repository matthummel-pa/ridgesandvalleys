/**
 * Ridges & Valleys — interior page content enhancement.
 *
 * Turns the flat, editor-authored H3 sub-sections inside
 * `body.page:not(.home) .rv-reading.rv-prose` into a responsive grid of
 * icon cards. Everything before the first H3 (the intro H2, lead paragraph,
 * and any figure) is left untouched as the section intro.
 *
 * Progressive enhancement: with JavaScript off the content is untouched and
 * fully readable (the CSS gives the inline H3s a clay top-rule instead).
 *
 * Static theme asset — ships with the theme, independent of the Vite build.
 */
(function () {
	'use strict';

	// A small set of on-brand line icons (stroke = currentColor).
	var I = {
		ridge: '<path d="M2 18l6-8 4 5 3-4 5 7" /><circle cx="8" cy="10" r="0" />',
		key: '<circle cx="8" cy="15" r="4" /><path d="M11 12l9-9M17 6l2 2M14 9l2 2" />',
		tag: '<path d="M3 12l8-8h8v8l-8 8z" /><circle cx="15.5" cy="8.5" r="1.4" />',
		bolt: '<path d="M13 2L4 14h6l-1 8 9-12h-6z" />',
		shield: '<path d="M12 3l7 3v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V6z" /><path d="M9 12l2 2 4-4" />',
		pin: '<path d="M12 22s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z" /><circle cx="12" cy="10" r="2.6" />',
		refresh: '<path d="M20 11a8 8 0 10-1.8 6M20 5v6h-6" />',
		globe: '<circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18" />',
		wrench: '<path d="M14.7 6.3a4 4 0 00-5.4 5.1L3 17.7 6.3 21l6.3-6.3a4 4 0 005.1-5.4l-2.6 2.6-2.4-.6-.6-2.4z" />',
		chat: '<path d="M21 12a8 8 0 01-11.5 7.2L4 21l1.8-5.5A8 8 0 1121 12z" />',
		clock: '<circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />',
		doc: '<path d="M7 3h7l5 5v13H7z" /><path d="M14 3v5h5M10 13h6M10 17h6" />'
	};

	// Keyword → icon. First match wins; falls back to the ridge mark.
	var MAP = [
		[/\b(own|owns?|yours|keys?|locked|lock-?in)\b/, 'key'],
		[/\b(pay|payment|price|pricing|cost|deposit|invoice|fee|rate|\$)\b/, 'tag'],
		[/\b(fast|speed|quick|launch|days?|timeline|deadline)\b/, 'bolt'],
		[/\b(access|accessib|wcag|508|inclusive)\b/, 'shield'],
		[/\b(secure|security|safe|backup|warranty|guarantee|protect)\b/, 'shield'],
		[/\b(contact|ask|talk|question|questions|answers?|faq|help|support|call|email)\b/, 'chat'],
		[/\b(area|areas|serve|serving|local|town|towns|near|gettysburg|county|region)\b/, 'pin'],
		[/\b(change|changes|revision|revise|edit|update|updates|maintain|care)\b/, 'refresh'],
		[/\b(host|hosting|domain|domains|online|web|search|seo|google|found)\b/, 'globe'],
		[/\b(fix|fixes|rescue|repair|broken|cleanup|clean up|audit)\b/, 'wrench'],
		[/\b(process|step|steps|how|work|works|start|begin|first)\b/, 'clock'],
		[/\b(include|includes|included|what|scope|deliver|plan|plans)\b/, 'doc']
	];

	function svg(name) {
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" ' +
			'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' +
			(I[name] || I.ridge) + '</svg>';
	}

	function pickIcon(text) {
		var t = (text || '').toLowerCase();
		for (var i = 0; i < MAP.length; i++) {
			if (MAP[i][0].test(t)) { return MAP[i][1]; }
		}
		return 'ridge';
	}

	function firstIndex(nodes, tag) {
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].tagName === tag) { return i; }
		}
		return -1;
	}

	function enhance(sec) {
		var kids = Array.prototype.slice.call(sec.children);
		var start = firstIndex(kids, 'H3');
		if (start < 0) { return; }
		if (sec.querySelector('.rv-subgrid')) { return; }

		var grid = document.createElement('div');
		grid.className = 'rv-subgrid';

		var card = null;
		for (var i = start; i < kids.length; i++) {
			var node = kids[i];
			if (node.tagName === 'H3') {
				card = document.createElement('div');
				card.className = 'rv-subcard';
				var chip = document.createElement('span');
				chip.className = 'rv-subcard-ic';
				chip.setAttribute('aria-hidden', 'true');
				chip.innerHTML = svg(pickIcon(node.textContent));
				card.appendChild(chip);
				card.appendChild(node);
				grid.appendChild(card);
			} else if (card) {
				card.appendChild(node);
			}
		}

		if (grid.children.length) { sec.appendChild(grid); }
	}

	var sections = document.querySelectorAll('body.page:not(.home) .rv-reading.rv-prose');
	Array.prototype.forEach.call(sections, enhance);
})();
