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
- [ ] Accessibility pass on the changed page (WCAG 2.1 AA)

## Log

Newest first. "Run" is the GitHub Actions run ID.

### 2026-08-06 — About page rebuild + docs + SiteGround cache purge

- **Shipped:** About page proof strip, six-free-tools band, pricing + NAP band;
  new `CHANGELOG.md`, `docs/deploy-log.md`, `docs/error-log.md`; rewritten
  `README.md`; `wp sg purge` added to both the production and staging workflows.
- **Why it mattered:** commit `3fc3371` (newsletter block removed from the home
  template) had been pushed to `origin/main` but **never deployed** — the newest
  Actions run at the time was `31126770067`, which was for `48105d5`. So the
  live site was one commit stale. This deploy cleared that.
- **Correction worth remembering.** Mid-investigation the local `manifest.json`
  read `app-smLHJxVW.css` / `app-Cch2uB98.js` against live's
  `app-Dhb0iuw-.css` / `app-VukVg13C.js`, which looked like proof of drift. It
  was not. That local manifest was a stale build artifact from before the
  rebase; rebuilding after `git pull --rebase` produced hashes identical to
  live. **Always rebuild before comparing manifests, and remember the manifest
  only fingerprints Vite output** — a Blade-only change like `3fc3371` rsyncs
  straight across and never moves a hash. The SHA-to-run check is what caught
  this one.
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
