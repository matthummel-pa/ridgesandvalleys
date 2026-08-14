/**
 * Ridges & Valleys Studio — front-end entry (TypeScript).
 *
 * Accessible off-canvas menu: toggle, ESC to close, focus trap while open,
 * focus returned to the toggle on close, click-outside to dismiss.
 */
import '@styles/app.css';

type Focusable = HTMLElement;

function initOffcanvas(): void {
  const toggle = document.querySelector<HTMLButtonElement>('.rv-menu-toggle');
  const panel = document.getElementById('rv-offcanvas');
  if (!toggle || !panel) return;

  const closeBtn = panel.querySelector<HTMLButtonElement>('.rv-offcanvas-close');
  let lastFocused: HTMLElement | null = null;

  const focusable = (): Focusable[] =>
    Array.from(
      panel.querySelectorAll<Focusable>(
        'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])',
      ),
    );

  const open = (): void => {
    lastFocused = document.activeElement as HTMLElement | null;
    panel.hidden = false;
    requestAnimationFrame(() => panel.classList.add('is-open'));
    toggle.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
    document.addEventListener('keydown', onKeydown);
    focusable()[0]?.focus();
  };

  const close = (): void => {
    panel.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    document.removeEventListener('keydown', onKeydown);
    window.setTimeout(() => {
      panel.hidden = true;
    }, 250);
    lastFocused?.focus();
  };

  const onKeydown = (e: KeyboardEvent): void => {
    if (e.key === 'Escape') {
      close();
      return;
    }
    if (e.key !== 'Tab') return;
    const items = focusable();
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  };

  toggle.addEventListener('click', () => {
    toggle.getAttribute('aria-expanded') === 'true' ? close() : open();
  });
  closeBtn?.addEventListener('click', close);
  panel.addEventListener('click', (e) => {
    if (e.target === panel) close();
    else if ((e.target as HTMLElement).closest('a')) close();
  });
}

/**
 * Light / dark theme toggle. The initial theme is set pre-paint by an inline
 * script in the document head (reads localStorage 'rv-theme', else the OS
 * preference). This wires the header button to flip and persist the choice.
 */
function initThemeToggle(): void {
  const btn = document.querySelector<HTMLButtonElement>('.rv-theme-toggle');
  if (!btn) return;

  const root = document.documentElement;
  const sync = (): void => {
    const isDark = root.getAttribute('data-theme') === 'dark';
    btn.setAttribute('aria-pressed', String(isDark));
  };
  sync();

  btn.addEventListener('click', () => {
    const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    try {
      localStorage.setItem('rv-theme', next);
    } catch {
      /* storage unavailable — the choice just won't persist */
    }
    sync();
  });

  // Follow the OS preference until the visitor makes an explicit choice.
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    let saved: string | null = null;
    try {
      saved = localStorage.getItem('rv-theme');
    } catch {
      /* ignore */
    }
    if (!saved) {
      root.setAttribute('data-theme', e.matches ? 'dark' : 'light');
      sync();
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initOffcanvas();
  initThemeToggle();
});
