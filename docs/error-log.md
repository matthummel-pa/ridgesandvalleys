# Error log

Things that have broken, what the symptom looked like, and what actually fixed
it. Check here before debugging from scratch — most failures on this project
are repeats.

Add a new entry every time you lose more than fifteen minutes to something.

---

## Composer install fails on the runner: PHP platform mismatch

**Symptom.** `composer install` in CI aborts with a message about the package
requiring a PHP version the runner does not have, even though it installs fine
on the Mac.

**Cause.** The Mac and the GitHub runner were on different PHP minors, and the
lock file was resolved against the local one.

**Fix.** Pin the platform PHP in `composer.json` so the resolver targets the
same version everywhere:

```json
"config": { "platform": { "php": "8.3" } }
```

Then re-run `composer update --lock` locally and commit the lock file.

---

## npm ci fails: lock file out of sync with peer deps

**Symptom.** `npm ci` fails on the runner with an `ERESOLVE` peer-dependency
error, or complains the lock file does not match `package.json`. `npm install`
works locally.

**Cause.** The lock file was generated locally with `--legacy-peer-deps`, which
`npm ci` does not assume. The two ran different resolution rules.

**Fix.** Use the same flag in both places, and regenerate the lock file with it
so CI and local agree:

```bash
npm install --legacy-peer-deps
git add package-lock.json && git commit -m "Regenerate lock with legacy peer deps"
```

**Why it matters.** If local and CI resolve dependencies differently, a build
that passes on your machine tells you nothing about the deploy.

---

## rsync step fails: malformed SSH_PRIVATE_KEY secret

**Symptom.** The build succeeds, then the deploy step dies immediately at the
SSH connection with a permission or invalid-format error. Repeated
`workflow_dispatch` reruns fail identically.

**Cause.** The private key was pasted into the GitHub secret without its
trailing newline, or with the header/footer lines mangled by copy-paste.

**Fix.** Re-paste the key into the repository secret including the
`-----BEGIN ...-----` and `-----END ...-----` lines **and** a trailing newline.
Verify the key you hold locally is valid without ever printing it:

```bash
ssh-keygen -y -f ~/.ssh/rv_siteground
```

That prints only the public half. If it errors, the private key file itself is
the problem, not GitHub.

**Note.** Adding or rotating SSH keys inside SiteGround is a host security
setting — do that yourself in the SiteGround panel.

---

## Deploy succeeded but the page is unchanged: Acorn view cache

**Symptom.** The Actions run is green, the manifest hashes on the live site
match your local build, but a Blade template change does not show up.

**Cause.** Acorn compiles Blade to PHP and caches the result. Rsyncing a new
`.blade.php` does not invalidate the compiled copy.

**Fix.** This is why the workflow runs, over SSH, after every rsync:

```bash
wp acorn view:clear
wp acorn optimize:clear
wp cache flush
```

If you ever deploy by hand, run those three yourself or you will chase a ghost.

---

## Deploy succeeded but the page is unchanged: SiteGround page cache

**Symptom.** Same as above, but clearing the Acorn views does not help either.
The page updates in a private window or with a cache-busting query string.

**Cause.** SG Optimizer serves a full-page cache in front of WordPress. It is a
separate layer from the WordPress object cache — `wp cache flush` does not
touch it.

**Fix.** Purge it explicitly:

```bash
wp sg purge
```

As of 2026-08-06 this is part of both `deploy-theme.yml` and
`deploy-staging.yml`, so it now happens automatically. Before that date it had
to be done by hand, which is the single most common reason an old deploy
"looked broken."

**Verify on the live, uncached URL.** A hard refresh in your own browser is not
proof — your browser has its own cache and so does Cloudflare-style edge
caching. Fetch the manifest URL directly.

---

## Wrong live path: 404 on the manifest

**Symptom.** Fetching
`https://ridgesandvalleys.com/app/themes/ridgesandvalleys-theme/public/build/manifest.json`
returns 404, which looks like a failed deploy.

**Cause.** That is the **Bedrock** path. The local repo is Bedrock; the live
install is managed WordPress with a conventional layout.

**Fix.** Use `/wp-content/themes/ridgesandvalleys-theme/public/build/manifest.json`
on live. Nothing was broken.

---

## Local `main` silently behind `origin/main`

**Symptom.** You push, the deploy runs, and changes someone else (or you, from
the other worktree) made come back or disappear.

**Cause.** Two git worktrees share one `.git`. It is easy to commit in one and
forget the other is stale. Note `.git` in the bedrock directory is a small
**file**, not a directory — that is normal for a worktree.

**Check.**

```bash
git fetch origin
git rev-list --left-right --count origin/main...main
```

`3	0` means you are three commits behind and zero ahead.

**Fix.** Stash anything uncommitted, rebase, restore:

```bash
git stash push -m wip -- <paths>
git pull --rebase origin main
git stash pop
```

---

## Pushed to origin but never deployed

**Symptom.** `git log` shows your commit on `origin/main`, but the live site
does not have it.

**Cause.** Either the workflow's path filter did not match (the commit touched
nothing under the theme directory) or the run failed and nobody looked.

**Check.** Match the run to the SHA, not to the clock:

```bash
gh run list --limit 10
```

If the newest run's SHA is older than `origin/main`'s HEAD, you have an
undeployed commit. This exact situation happened with `3fc3371` on 2026-08-06 —
see `docs/deploy-log.md`.

**Fix.** Push any commit that touches the theme path, or fire the workflow
manually from the Actions tab.

---

## Ghost button renders white-on-cream

**Symptom.** A `.rv-btn-ghost` link is invisible on a light background.

**Cause.** `resources/css/app.css` scopes a white-text override to
`.rv-hero-actions .rv-btn-ghost`, intended for the dark hero. Reusing the
`.rv-hero-actions` wrapper on a light band inherits that override.

**Fix.** Do not reuse `.rv-hero-actions` outside a dark hero. Give the light-band
CTA its own wrapper class and style the spacing there.

---

## `border: 4px solid var(--ridgeline)` does nothing

**Symptom.** An accent stripe built as a border simply does not appear.

**Cause.** `--ridgeline` is a `linear-gradient`. Gradients are images, and CSS
borders cannot take an image through `border-color`.

**Fix.** Use a positioned `::before` with `background: var(--ridgeline)` — the
pattern `.rv-about-hl::before` already uses.

---

## `python3 -c "import yaml"` → ModuleNotFoundError

**Symptom.** Cannot lint workflow YAML locally before pushing.

**Cause.** The system Python on the Mac has no PyYAML.

**Fix.** Either `pip3 install pyyaml --break-system-packages`, or skip it and
mirror the structure of a known-good workflow file. A malformed workflow fails
fast and loudly in Actions, so the cost of finding out there is low.

---

## Local links 301 to `-2` slugs that do not exist on live

**Symptom.** A link that is correct in the template — `/free-tools/`,
`/local-seo/` — returns a 301 to `/free-tools-2/` or `/local-seo-2/` on
`localhost:8080`. It looks like the template has the wrong URL.

**Cause.** The local database was imported more than once, so WordPress
resolved the slug collision by appending `-2`. The live database is clean.

**Fix.** Nothing in the code. Verify against live before "fixing" a link:

```bash
curl -s -o /dev/null -w '%{http_code}\n' https://ridgesandvalleys.com/free-tools/
```

If live returns 200, the template is right and your local content is the
problem. Re-import the live database cleanly (drop and recreate first) if the
duplicates get in your way.

**Do not** change a template URL to match a local `-2` slug. That ships a
broken link.
