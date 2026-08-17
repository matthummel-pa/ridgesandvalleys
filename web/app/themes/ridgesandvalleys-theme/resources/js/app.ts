/**
 * Ridges & Valleys Studio — front-end entry (TypeScript).
 *
 * Accessible off-canvas menu: toggle, ESC to close, focus trap while open,
 * focus returned to the toggle on close, click-outside to dismiss.
 */
import '@styles/app.css';
import '../../assets/rv-enhancements.css';

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

/**
 * Footer nav columns use <details> as a mobile accordion. Keep them open on
 * desktop so the lists stay visible without a click.
 */
function initFooterAccordion(): void {
  const items = document.querySelectorAll<HTMLDetailsElement>('.rv-footer-acc');
  if (!items.length) return;

  const mq = window.matchMedia('(min-width: 768px)');
  const sync = (): void => {
    items.forEach((el) => {
      el.open = mq.matches;
    });
  };

  mq.addEventListener('change', sync);
  sync();
}

/**
 * Footer newsletter → HubSpot Forms API. Portal + form GUID live on the form
 * as data attributes so this stays out of the Blade template.
 */
function initFooterNewsletter(): void {
  const form = document.getElementById('rv-fnews-form') as HTMLFormElement | null;
  if (!form) return;

  const emailEl = document.getElementById('rv-fnews-email') as HTMLInputElement | null;
  const statusEl = document.getElementById('rv-fnews-status');
  const btn = form.querySelector<HTMLButtonElement>('.rv-fnews-btn');
  const portal = form.dataset.hsPortal;
  const guid = form.dataset.hsForm;
  if (!emailEl || !statusEl || !btn || !portal || !guid) return;

  const endpoint = `https://api.hsforms.com/submissions/v3/integration/submit/${portal}/${guid}`;
  const show = (msg: string, state: string): void => {
    statusEl.hidden = false;
    statusEl.textContent = msg;
    statusEl.setAttribute('data-state', state);
  };

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = (emailEl.value || '').trim();
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
      show('Please enter a valid email address.', 'err');
      emailEl.focus();
      return;
    }

    btn.disabled = true;
    show('Signing you up…', 'pending');

    fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        fields: [{ name: 'email', value: email }],
        context: { pageUri: location.href, pageName: document.title },
      }),
    })
      .then(async (r) => {
        const j = (await r.json().catch(() => ({}))) as {
          inlineMessage?: string;
          errors?: { message?: string }[];
        };
        return { ok: r.ok, j };
      })
      .then((res) => {
        btn.disabled = false;
        if (res.ok) {
          form.reset();
          const raw = res.j.inlineMessage;
          show(raw ? raw.replace(/<[^>]*>/g, '') : 'Thanks — you are on the list.', 'ok');
          return;
        }
        show(res.j.errors?.[0]?.message || 'Something went wrong. Please try again.', 'err');
      })
      .catch(() => {
        btn.disabled = false;
        show('Network error. Please try again.', 'err');
      });
  });
}

function initHomeProofFilters(): void {
  const root = document.querySelector<HTMLElement>('[data-rv-home-proof]');
  if (!root) return;

  const bar = root.querySelector<HTMLElement>('[data-rv-home-proof-filters]');
  const grid = root.querySelector<HTMLElement>('[data-rv-home-proof-grid]');
  if (!bar || !grid) return;

  const cards = Array.from(grid.querySelectorAll<HTMLElement>('.rv-proof-item'));
  const buttons = Array.from(bar.querySelectorAll<HTMLButtonElement>('.rv-filter'));
  const countEl = root.querySelector<HTMLElement>('[data-rv-home-proof-count]');
  const emptyEl = root.querySelector<HTMLElement>('[data-rv-home-proof-empty]');
  const emptyAll = emptyEl?.querySelector<HTMLButtonElement>('.rv-work-empty-all');
  const limit = Math.max(1, parseInt(grid.dataset.limit || '3', 10) || 3);
  if (!cards.length || !buttons.length) return;

  const noun = (n: number): string => (n === 1 ? 'project' : 'projects');

  const apply = (filter: string): void => {
    let shown = 0;
    cards.forEach((card) => {
      const match = filter === 'all' || card.getAttribute('data-cat') === filter;
      const visible = match && shown < limit;
      if (match && visible) shown += 1;
      card.classList.toggle('is-hidden', !visible);
    });
    if (countEl) {
      countEl.textContent = `Showing ${shown} ${noun(shown)}`;
    }
    if (emptyEl) emptyEl.hidden = shown !== 0;
  };

  const run = (btn: HTMLButtonElement): void => {
    if (btn.getAttribute('aria-pressed') === 'true') return;
    buttons.forEach((b) => {
      b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
    });
    apply(btn.getAttribute('data-filter') || 'all');
  };

  buttons.forEach((btn) => {
    btn.addEventListener('click', () => run(btn));
  });
  emptyAll?.addEventListener('click', () => {
    const all = bar.querySelector<HTMLButtonElement>('[data-filter="all"]');
    if (all) run(all);
  });
}

function initToolFilters(): void {
  const bar = document.querySelector<HTMLElement>('[data-rv-tool-filters]');
  const hub = document.querySelector<HTMLElement>('[data-rv-toolhub]');
  if (!bar || !hub) return;

  const chips = Array.from(bar.querySelectorAll<HTMLButtonElement>('[data-filter]'));
  const cards = Array.from(hub.querySelectorAll<HTMLElement>('[data-group]'));
  if (!chips.length || !cards.length) return;

  const apply = (key: string): void => {
    chips.forEach((chip) => {
      const on = chip.dataset.filter === key;
      chip.classList.toggle('is-on', on);
      chip.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
    cards.forEach((card) => {
      const group = card.dataset.group || '';
      const show = key === 'all' || group === key;
      card.classList.toggle('is-hidden', !show);
    });
  };

  bar.addEventListener('click', (e) => {
    const btn = (e.target as HTMLElement).closest<HTMLButtonElement>('[data-filter]');
    if (!btn || !bar.contains(btn)) return;
    apply(btn.dataset.filter || 'all');
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initOffcanvas();
  initThemeToggle();
  initFooterAccordion();
  initFooterNewsletter();
  initToolFilters();
  initHomeProofFilters();
});
