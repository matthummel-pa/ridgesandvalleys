# Deploy log

A running record of what shipped to **ridgesandvalleys.com**, when, and whether
it worked. Deploys are automated — this file is the human-readable trail on top
of them.

## How deploys actually happen

Code flows **up**: local → GitHub → live. Content flows **down**: live → local.
Nothing gets edited directly on the server.

1. You push to `main`.
2. If the push touched `web/app/themes/ridgesandvalleys-theme/**` (or the
   workflow file itself), GitHub Actions runs `.github/workflows/deploy-theme.yml`.
3. The runner installs Node deps, runs the Vite production build, then `rsync`s
   the built theme to SiteGround **over SSH** using the `SSH_PRIVATE_KEY` secret.
4. It then runs, over the same SSH connection:
   `wp acorn view:clear; wp acorn optimize:clear; wp cache flush; wp sg purge`.
5. You verify on the live, uncached URL.

You can also fire it by hand from the Actions tab (`workflow_dispatch`) without
pushing anything.

## Why the manifest is the source of truth

Vite fingerprints every built asset, so the fastest honest check that a deploy
landed is to compare hashes:

- Local: `web/app/themes/ridgesandvalleys-theme/public/build/manifest.json`
- Live: `https://ridgesandvalleys.com/wp-content/themes/ridgesandvalleys-theme/public/build/manifest.json`

Same hashes = the live site is serving your build. Different hashes = it is not,
no matter what the Actions tab says. Note the live path is `/wp-content/...`,
not `/app/...` — the live install is managed WordPress, not Bedrock.

## Verification checklist (run this every time)

- [ ] `git status` is clean and `git log origin/main..main` is empty
- [ ] The Actions run for **your** commit SHA finished green — not just the
      newest run, the one matching your SHA
- [ ] Live manifest hashes match your local `manifest.json`
- [ ] The changed page renders correctly at ~390px wide and on desktop
- [ ] Accessibility pass on the changed page (WCAG 2.2 AA)

## Log

Newest first. "Run" is the GitHub Actions run ID.

### 2026-08-17 — Journal filters forced to scroll with the page — Run 31989328213 — success — `23d40e3`

- **Shipped:** `.rv-work-filters` is `position: static !important` (Journal + Work),
  so topic chips cannot pin under the header.
- **Verified:** Actions run `31989328213` for merge SHA `23d40e3` finished green
  (~1m23s). The deployed theme `manifest.json` is `app-C2gT-I9L.css` /
  `app-ClM6M_wh.js` and that CSS includes `position:static!important` on
  `.rv-work-filters`.
- **Note:** live HTML is currently loading stylesheet URLs from
  `ridgesandvalleys-theme-wpvibe-backup` (`app-CpBTynwu.css`). Until WordPress
  is using the deployed `ridgesandvalleys-theme` folder, Journal may still
  show the older copy.

### 2026-08-17 — Homepage proof photos equal height — Run 31985271108 — success — `e69e96f`

- **Shipped:** homepage project-card photos share a 16:10 frame and fill it
  (`object-fit: cover`, top-aligned). Pine-green letterbox under the screenshots
  is gone.
- **Verified:** Actions run `31985271108` for merge SHA `e69e96f` finished green
  (~1m18s). Live `manifest.json` is `app-CpBTynwu.css` / `app-CKdy6D6D.js`. Live
  CSS includes `.rv-proof-visual{…aspect-ratio:16/10…}` and
  `.rv-proof-visual.rv-media-photo>img{object-fit:cover…}`.

### 2026-08-17 — Homepage proof grid (3 columns, max 3) — Run 31984277317 — success — `14fa47d`

- **Shipped:** homepage featured work is a filterable three-column project grid.
  At most three cards show at once. Screenshot on top, story underneath. Industry
  chips swap which projects appear.
- **Verified:** Actions run `31984277317` for merge SHA `14fa47d` finished green
  (~1m22s). Live `manifest.json` is `app-CrAnTunf.css` / `app-CgiHLrX6.js`. Live
  JS includes `data-rv-home-proof` filter logic.

### 2026-08-17 — Homepage concept screenshots uncropped — Run 31982374759 — success — `608c0df`

- **Shipped:** homepage concept-section images use the full attachment (`size-full`)
  instead of the cropped `rv-hero` size. CSS shows those photos at natural height
  (`object-fit: contain`) with no 16/10 mobile crop. Work grid thumbs unchanged.
- **Verified:** Actions run `31982374759` for merge SHA `608c0df` finished green.
  Live `manifest.json` is `app-DqLwjtMd.css` / `app-BvzXGmcA.js`. Homepage markup
  includes `attachment-full size-full`.

### 2026-08-17 — Homepage hero matches interior measure — Run 31981360829 — success — `3bf10cc`

- **Shipped:** homepage H1/lede use the same full-width measure and nav gap as
  Free Tools and the other interior heroes.
- **Verified:** Actions run `31981360829` for merge SHA `3bf10cc` finished green.
  Live `manifest.json` is `app-DiaM2rpk.css` / `app-DH8r0nrR.js`. Live CSS
  includes `.home .rv-hero-title{…max-width:none…}`.

### 2026-08-17 — Unstick Journal/Work filters — Run 31981126711 — success — `65d71d4`

- **Shipped:** Journal and Work category chips scroll with the page (no
  `position: sticky`).
- **First attempt failed:** run `31980238423` for merge `1e8ee6e` timed out on
  SiteGround SSH (`rsync` code 255, six retries). Retry `65d71d4` ran green.
- **Verified:** live `/journal/` and `/work/` CSS is
  `.rv-work-filters{padding:.7rem 0}` / `{margin-top:1.5rem;padding:.7rem 0}`
  with no sticky.

### 2026-08-16 — Interior heroes match Free Tools — Run 31979027202 — success — `50ffa06`

- **Shipped:** interior page and journal-post heroes use the same full-width H1/lede
  and top padding as Free Tools. Homepage hero unchanged.
- **Verified:** Actions run `31979027202` for merge SHA `50ffa06` finished green.
  Live `manifest.json` matches (`app-DGhFVETg.css`, `app-JTdqm83a.js`). About
  serves that CSS bundle.

### 2026-08-16 — Free Tools hub redesign — Run 31978415604 — success — `f6b12b3`

- **Shipped:** field-driven `/free-tools/` hub (no Gutenberg), chooser + filters +
  FAQ/privacy/next-step sections, wider hero H1/lede, more space under the nav.
- **Verified:** Actions run `31978415604` for merge SHA `f6b12b3` finished green.
  Live `manifest.json` matches local (`app-DDqUIA0y.css`, `app-DwnLZxif.js`).
  Live HTML includes `rv-hero--tools`.


### 2026-08-06 — About page rebuild + docs + SiteGround cache purge

- **Shipped:** About page proof strip, six-free-tools band, pricing + NAP band;
  new `CHANGELOG.md`, `docs/deploy-log.md`, `docs/error-log.md`; rewritten
  `README.md`; `wp sg purge` added to both the production and staging workflows.
- **Correction — live was not stale.** An earlier version of this entry said
  commit `3fc3371` ("Move newsletter into footer as native HubSpot form") had
  been pushed to `origin/main` but never deployed, because the newest Actions
  run at the time was `31126770067` and that run was for `48105d5`. The SHA
  mismatch was real; the conclusion was wrong. `3fc3371` is an **empty commit** —
  `git diff --stat 3fc3371^ 3fc3371` returns nothing, and `git show --stat`
  lists no files. The newsletter work actually shipped in `48105d5`, which did
  deploy. So live was never missing anything. **Lesson: a SHA newer than the
  last deployed run does not by itself mean live is stale — check that the
  commit changes files before concluding anything.**
- **Correction worth remembering.** Mid-investigation the local `manifest.json`
  read `app-smLHJxVW.css` / `app-Cch2uB98.js` against live's
  `app-Dhb0iuw-.css` / `app-VukVg13C.js`, which looked like proof of drift. It
  was not. That local manifest was a stale build artifact from before the
  rebase; rebuilding after `git pull --rebase` produced hashes identical to
  live. **Always rebuild before comparing manifests, and remember the manifest
  only fingerprints Vite output** — a Blade-only change rsyncs straight across
  and never moves a hash, so matching hashes do not prove a template change
  landed. Between the two checks: the manifest tells you whether the CSS/JS
  bundle is current, and the SHA-to-run check tells you whether the latest
  commit actually ran. Neither is sufficient alone.
- **Also fixed locally:** `main` was three commits behind `origin/main`
  (`d7b9743`, `48105d5`, `3fc3371`). Recovered with
  `git stash push -- .github/workflows/` → `git pull --rebase origin main` →
  `git stash pop`. Clean fast-forward, no conflicts.

### 2026-08-06 19:17 — Run 31126770067 — success — `48105d5`

HubSpot newsletter form integrated in the footer.

### 2026-08-06 18:48 — Runs 31126392214 + 31126391314 — failure — `workflow_dispatch`

Two manual dispatches fired seconds apart against `48105d5`; both failed. The
push-triggered run for the same SHA succeeded 29 minutes later. Lesson: do not
double-click Run workflow, and prefer pushing over dispatching.

### 2026-08-06 12:19 — Run 31100921112 — success — `79cbfcc`

Comment form restyled: mono eyebrow, gradient accent stripe, pine focus ring.

### 2026-08-06 06:20 — Run 31076934650 — failure — `21977a1`

Rank Math title + meta description pass for all pages. Build-stage failure.

### 2026-08-06 06:01 — Run 31075902658 — failure — `647921c` — `workflow_dispatch`

### 2026-08-06 05:06 — Run 31073117428 — cancelled — `6b42f35` — `workflow_dispatch`

### 2026-08-04 20:36 — Run 30948629785 — failure — `26aa1c4` — `workflow_dispatch`

Part of the run of failures that led to the three CI fixes recorded in
`CHANGELOG.md` under `[1.0.0]` — see `docs/error-log.md` for what each one was
and how it was solved.

## Rollback

There is no "undo deploy" button. To roll back, revert the commit and push:

```bash
git revert <sha>
git push origin main
```

That fires a normal deploy of the reverted state. Confirm with the manifest
check above.
