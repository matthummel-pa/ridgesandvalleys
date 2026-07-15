// Theme scripts
import './reading-progress.js';

// Dark mode is handled entirely by dark-mode.php (inline head + footer scripts).
// prt-theme key in localStorage, 'dark'/'light' values.
// Do NOT duplicate the toggle logic here — two listeners cancel each other out.

document.addEventListener('DOMContentLoaded', () => {
  // Popout menu — with real focus management (WCAG 2.4.3):
  // closed = inert + aria-hidden so its links leave the tab order / AT tree
  // (the panel is only visually hidden via transform in CSS); open = focus
  // moves into the panel, Tab is trapped inside it, Escape/close returns
  // focus to the toggle that opened it.
  const menuToggle  = document.querySelector('.menu-toggle');
  const popout      = document.getElementById('prt-popout');
  const overlay     = document.querySelector('.prt-popout-overlay');
  const closeBtn    = document.querySelector('.prt-popout-close');

  const setClosedState = (closed) => {
    if (!popout) return;
    // `inert` removes the subtree from tab order + accessibility tree in one
    // property (supported everywhere the theme targets); aria-hidden is the
    // belt to inert's braces for older AT.
    popout.inert = closed;
    popout.setAttribute('aria-hidden', closed ? 'true' : 'false');
  };

  const focusables = () =>
    popout
      ? Array.from(
          popout.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])')
        ).filter((el) => el.offsetParent !== null)
      : [];

  const isOpen = () => document.body.classList.contains('prt-popout-open');

  const openMenu = () => {
    document.body.classList.add('prt-popout-open');
    menuToggle && menuToggle.setAttribute('aria-expanded', 'true');
    setClosedState(false);
    if (popout) {
      if (!popout.hasAttribute('tabindex')) popout.setAttribute('tabindex', '-1');
      popout.focus();
    }
  };

  const closeMenu = () => {
    if (!isOpen()) return;
    document.body.classList.remove('prt-popout-open');
    menuToggle && menuToggle.setAttribute('aria-expanded', 'false');
    setClosedState(true);
    // Return focus to the control that opened the panel.
    menuToggle && menuToggle.focus();
  };

  // Start closed: hidden from keyboard + AT until opened.
  setClosedState(true);

  menuToggle &&
    menuToggle.addEventListener('click', () => (isOpen() ? closeMenu() : openMenu()));
  closeBtn && closeBtn.addEventListener('click', closeMenu);
  overlay && overlay.addEventListener('click', closeMenu);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenu();

    // Focus trap while the popout is open.
    if (e.key === 'Tab' && isOpen() && popout) {
      const items = focusables();
      if (!items.length) return;
      const first = items[0];
      const last = items[items.length - 1];
      if (e.shiftKey && (document.activeElement === first || document.activeElement === popout)) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    }
  });
});
