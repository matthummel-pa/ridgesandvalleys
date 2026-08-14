/**
 * Free tools suite — front-end behaviour (TypeScript).
 *
 * Loaded only on pages that render tool widgets (Free Tools template or an
 * rv/* tool block). Server-rendered shells live in app/tools.php; the URL
 * tools call the rv-tools/v1 REST endpoints.
 */

/* ------------------------------------------------------------------ helpers */
const esc = (s: string): string =>
  s.replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c] as string));

function money(n: number): string {
  return '$' + Math.round(n).toLocaleString('en-US');
}

/* --------------------------------------------------------- contrast checker */
function hexToRgb(hex: string): [number, number, number] | null {
  const m = /^#?([0-9a-f]{6})$/i.exec(hex.trim());
  if (!m) return null;
  const int = parseInt(m[1], 16);
  return [(int >> 16) & 255, (int >> 8) & 255, int & 255];
}
function luminance([r, g, b]: [number, number, number]): number {
  const f = (c: number) => {
    const s = c / 255;
    return s <= 0.03928 ? s / 12.92 : Math.pow((s + 0.055) / 1.055, 2.4);
  };
  return 0.2126 * f(r) + 0.7152 * f(g) + 0.0722 * f(b);
}
function contrastRatio(a: string, b: string): number | null {
  const ra = hexToRgb(a);
  const rb = hexToRgb(b);
  if (!ra || !rb) return null;
  const la = luminance(ra);
  const lb = luminance(rb);
  return (Math.max(la, lb) + 0.05) / (Math.min(la, lb) + 0.05);
}

function initContrast(root: HTMLElement): void {
  const fg = root.querySelector<HTMLInputElement>('#rv-c-fg');
  const fgHex = root.querySelector<HTMLInputElement>('#rv-c-fg-hex');
  const bg = root.querySelector<HTMLInputElement>('#rv-c-bg');
  const bgHex = root.querySelector<HTMLInputElement>('#rv-c-bg-hex');
  const out = root.querySelector<HTMLElement>('.rv-tool-result');
  if (!fg || !fgHex || !bg || !bgHex || !out) return;

  const badge = (ok: boolean, label: string) =>
    `<span class="rv-badge ${ok ? 'is-pass' : 'is-fail'}">${ok ? '✓' : '✗'} ${label}</span>`;

  const render = () => {
    const ratio = contrastRatio(fgHex.value, bgHex.value);
    if (ratio === null) {
      out.innerHTML = '<p class="rv-tool-hint">Enter two valid hex colors.</p>';
      return;
    }
    const r = Math.round(ratio * 100) / 100;
    out.innerHTML =
      `<div class="rv-contrast-preview" style="color:${esc(fgHex.value)};background:${esc(bgHex.value)}">` +
      `<span class="rv-contrast-big">Aa</span><span class="rv-contrast-small">The quick brown fox</span></div>` +
      `<p class="rv-contrast-ratio"><strong>${r}:1</strong> contrast ratio</p>` +
      `<div class="rv-badges">${badge(ratio >= 4.5, 'AA normal')}${badge(ratio >= 3, 'AA large')}` +
      `${badge(ratio >= 7, 'AAA normal')}${badge(ratio >= 4.5, 'AAA large')}</div>`;
  };
  const sync = (color: HTMLInputElement, hex: HTMLInputElement) => {
    color.addEventListener('input', () => { hex.value = color.value.toUpperCase(); render(); });
    hex.addEventListener('input', () => { if (hexToRgb(hex.value)) color.value = hex.value; render(); });
  };
  sync(fg, fgHex);
  sync(bg, bgHex);
  render();
}

/* --------------------------------------------------------- quote estimator */
function initEstimator(root: HTMLElement): void {
  const type = root.querySelector<HTMLSelectElement>('#rv-e-type');
  const pages = root.querySelector<HTMLSelectElement>('#rv-e-pages');
  const adds = root.querySelectorAll<HTMLInputElement>('.rv-e-add');
  const out = root.querySelector<HTMLElement>('.rv-tool-result');
  if (!type || !pages || !out) return;

  const render = () => {
    let base = parseFloat(pages.value) * parseFloat(type.value);
    adds.forEach((a) => { if (a.checked) base += parseFloat(a.value); });
    const low = base;
    const high = base * 1.35;
    out.innerHTML =
      `<p class="rv-estimate-range"><strong>${money(low)}–${money(high)}</strong></p>` +
      `<p class="rv-tool-hint">A ballpark for planning — every project gets a fixed, written quote before we start.</p>`;
  };
  [type, pages].forEach((el) => el.addEventListener('change', render));
  adds.forEach((a) => a.addEventListener('change', render));
  render();
}

/* --------------------------------------------------------- cost calculator */
function initCalculator(root: HTMLElement): void {
  const tier = root.querySelector<HTMLSelectElement>('#rv-k-tier');
  const annual = root.querySelector<HTMLInputElement>('#rv-k-annual');
  const out = root.querySelector<HTMLElement>('.rv-tool-result');
  if (!tier || !annual || !out) return;

  const render = () => {
    const monthly = parseFloat(tier.value);
    if (annual.checked) {
      const yr = monthly * 12 * 0.85;
      out.innerHTML =
        `<p class="rv-estimate-range"><strong>${money(yr / 12)}/mo</strong> <span class="rv-muted">billed ${money(yr)}/yr</span></p>` +
        `<p class="rv-tool-hint">Annual billing saves about 15%.</p>`;
    } else {
      out.innerHTML =
        `<p class="rv-estimate-range"><strong>${money(monthly)}/mo</strong></p>` +
        `<p class="rv-tool-hint">Cancel anytime — you always own your site.</p>`;
    }
  };
  tier.addEventListener('change', render);
  annual.addEventListener('change', render);
  render();
}

/* ---------------------------------------------------------- URL-based tools */
type Check = { label: string; pass: boolean; hint?: string };
type Finding = { label: string; status: 'pass' | 'warn' | 'fail'; detail?: string };

function urlTool(root: HTMLElement, kind: 'grader' | 'a11y'): void {
  const form = root.querySelector<HTMLFormElement>('.rv-tool-form');
  const input = root.querySelector<HTMLInputElement>('input[name="url"]');
  const out = root.querySelector<HTMLElement>('.rv-tool-result');
  const endpoint = form?.dataset.endpoint;
  if (!form || !input || !out || !endpoint) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    out.hidden = false;
    out.innerHTML = '<p class="rv-tool-loading">Fetching and analysing…</p>';
    try {
      const res = await fetch(`${endpoint}?url=${encodeURIComponent(input.value)}`);
      const data = await res.json();
      if (!data.ok) {
        out.innerHTML = `<p class="rv-tool-error">${esc(data.error || 'Could not analyse that URL.')}</p>`;
        return;
      }
      out.innerHTML = kind === 'grader' ? renderGrade(data) : renderA11y(data);
    } catch {
      out.innerHTML = '<p class="rv-tool-error">Network error — try again.</p>';
    }
  });
}

function renderGrade(d: { score: number; grade: string; checks: Check[]; meta: Record<string, unknown> }): string {
  const rows = d.checks
    .map(
      (c) =>
        `<li class="${c.pass ? 'is-pass' : 'is-fail'}"><span class="rv-tick">${c.pass ? '✓' : '✗'}</span>` +
        `<span>${esc(c.label)}${!c.pass && c.hint ? ` <em>${esc(c.hint)}</em>` : ''}</span></li>`,
    )
    .join('');
  return (
    `<div class="rv-score" data-grade="${esc(d.grade)}"><span class="rv-score-num">${d.score}</span>` +
    `<span class="rv-score-grade">${esc(d.grade)}</span></div>` +
    `<p class="rv-tool-hint">${d.meta.status} · ${d.meta.ms}ms · ${d.meta.kb}KB · ${d.meta.images} images</p>` +
    `<ul class="rv-checklist">${rows}</ul>`
  );
}

function renderA11y(d: { summary: { fail: number; warn: number; pass: number }; findings: Finding[] }): string {
  const icon = { pass: '✓', warn: '!', fail: '✗' };
  const rows = d.findings
    .map(
      (f) =>
        `<li class="is-${f.status}"><span class="rv-tick">${icon[f.status]}</span>` +
        `<span>${esc(f.label)}${f.detail ? ` <em>${esc(f.detail)}</em>` : ''}</span></li>`,
    )
    .join('');
  return (
    `<div class="rv-badges rv-a11y-summary">` +
    `<span class="rv-badge is-fail">${d.summary.fail} fail</span>` +
    `<span class="rv-badge is-warn">${d.summary.warn} warn</span>` +
    `<span class="rv-badge is-pass">${d.summary.pass} pass</span></div>` +
    `<ul class="rv-checklist">${rows}</ul>`
  );
}

/* ------------------------------------------------------------------- boot */
export function initTools(): void {
  document.querySelectorAll<HTMLElement>('[data-rv-tool]').forEach((el) => {
    switch (el.dataset.rvTool) {
      case 'contrast': initContrast(el); break;
      case 'estimator': initEstimator(el); break;
      case 'calculator': initCalculator(el); break;
      case 'grader': urlTool(el, 'grader'); break;
      case 'a11y': urlTool(el, 'a11y'); break;
    }
  });
}

document.addEventListener('DOMContentLoaded', initTools);
