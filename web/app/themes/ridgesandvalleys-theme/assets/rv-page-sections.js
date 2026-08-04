/**
 * Ridges & Valleys — reveal-on-scroll for interior page content sections.
 *
 * Progressive enhancement: the `.rv-js` class (set in <head> by setup.php) is
 * what allows the CSS to hide these sections, so with JavaScript disabled the
 * content is always visible. Users who prefer reduced motion get the content
 * shown immediately with no animation.
 *
 * Static theme asset — ships with the theme, independent of the Vite build.
 */
(function () {
	var SELECTOR = 'body.page:not(.home) .rv-reading.rv-prose';
	var nodes = Array.prototype.slice.call(document.querySelectorAll(SELECTOR));
	if (!nodes.length) {
		return;
	}

	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	// No IntersectionObserver or reduced motion: just show everything.
	if (reduce || !('IntersectionObserver' in window)) {
		nodes.forEach(function (node) { node.classList.add('is-in'); });
		return;
	}

	var observer = new IntersectionObserver(function (entries) {
		entries.forEach(function (entry) {
			if (entry.isIntersecting) {
				entry.target.classList.add('is-in');
				observer.unobserve(entry.target);
			}
		});
	}, { rootMargin: '0px 0px -6% 0px', threshold: 0.06 });

	nodes.forEach(function (node) { observer.observe(node); });
})();
