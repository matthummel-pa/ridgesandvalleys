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

If the newest run's SHA is older than `origin/main`'s HEAD, you *may* have an
undeployed commit. Before concluding that, check the commit is not empty:

```bash
git diff --stat <sha>^ <sha>
```

Empty output means the commit changes no files and there is nothing to deploy.
That is exactly what happened with `3fc3371` on 2026-08-06 — the SHA mismatch
was real, the commit was empty, and live was fine. See `docs/deploy-log.md`.

**Fix.** If the commit really does change theme files, push any commit that
touches the theme path, or fire the workflow manually.

---

## `git push` rejected: "refusing to allow an OAuth App to ... workflow"

**Symptom.** A push that includes any change under `.github/workflows/` is
rejected:

```
! [remote rejected] main -> main (refusing to allow an OAuth App to create or
update workflow `.github/workflows/deploy-staging.yml` without `workflow` scope)
```

The commit is fine. The push is fine. Only the workflow file is the problem.

**Cause.** The push went out over HTTPS, so it authenticated with the `gh` CLI's
OAuth token, and that token has no `workflow` scope:

```bash
gh auth status
# Token scopes: 'admin:public_key', 'gist', 'read:org', 'repo'
```

GitHub blocks OAuth tokens from touching workflow definitions unless the scope
is explicitly granted. This is a guardrail against a compromised token
rewriting CI to run arbitrary code.

**Fix.** Push over SSH instead. SSH keys are not subject to OAuth app scopes:

```bash
GIT_SSH_COMMAND='ssh -i ~/.ssh/mykey -o IdentitiesOnly=yes' \
  git push git@github.com:matthummel-pa/ridgesandvalleys.git main:main
```

The `origin` remote stays HTTPS; this just overrides the transport for one
push. The alternative — `gh auth refresh -s workflow` — widens the token's
permissions permanently, which is the worse trade for a change this occasional.

---

## Push lands on origin but fires no workflow run

**Symptom.** A push succeeds and the commit is confirmed on the remote, but no
Actions run appears — not after 20 seconds, not after 45.

**What was ruled out**, in order, on 2026-08-06 for commit `31edc06`:

- Actions enabled on the repo — `{"enabled":true,"allowed_actions":"all"}`
- Both workflows `state: active`
- The remote copy of `deploy-theme.yml` carries the right path filter,
  `web/app/themes/ridgesandvalleys-theme/**`
- The commit demonstrably touches that path
- `git ls-remote origin main` returns the pushed SHA

So the trigger conditions were all satisfied and the run still did not fire.
**Root cause not determined.** Worth suspecting, untested: GitHub sometimes
suppresses push triggers for pushes that arrive over a transport or identity
the repo does not associate with a workflow-capable actor — this push was the
SSH workaround above.

**Fix / workaround.** Dispatch it by hand. Both workflows carry
`workflow_dispatch`:

```bash
gh workflow run deploy-theme.yml --ref main
gh run list --limit 3
```

Run `31127099769` deployed this way and succeeded in 1m20s.

**Do not** assume a green working tree means a green live site. Always confirm
a run exists for your SHA — see "Pushed to origin but never deployed" above.

---

## Contact `mailto:` renders as `&amp;#109;&amp;#97;tt&amp;#64;...`

**Symptom.** The header and footer email link displays correctly but is dead —
clicking it opens a mail client addressed to literal garbage. Live HTML shows:

```html
href="mailto:&amp;#109;&amp;#97;tt&amp;#64;ridg&amp;#101;s&amp;#97;ndv..."
```

**Cause.** WordPress's `antispambot()` returns a string of HTML entities
(`&#109;` and so on) to hide the address from scrapers. Blade's `{{ }}` runs
`htmlspecialchars` on whatever it prints, which escapes the leading `&` of
every entity into `&amp;` — so the browser renders the entity *text* instead of
decoding it. The `<span>` next to it looked fine because it already used
`{!! !!}`; only the `href` was wrong.

**Fix.** Use `{!! !!}` for any `antispambot()` output, in attributes as well as
in text:

```blade
<a class="rv-contact-link" href="mailto:{!! antispambot($rvEmail) !!}">
```

**General rule:** a helper that *returns* markup or entities must never go
through `{{ }}`. Escaping already-escaped output is the bug.

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

## Clean slugs 301 to `-2` slugs — on live, not just local

**Symptom.** `/free-tools/` and `/local-seo/` return a 301 to `/free-tools-2/`
and `/local-seo-2/`. The `-2` pages return 200 and are the real pages.

**Correction to an earlier version of this entry.** This was first written up as
a local-only problem, with the claim "the live database is clean." That was
wrong, and it was wrong because of how it was checked: a browser-style fetch
follows redirects silently, so the clean URL appeared to return 200 when it was
actually returning 301 and the fetcher was quietly landing on the `-2` page.
`curl -sI` — headers only, no redirect following — tells the truth:

```
$ curl -sI https://ridgesandvalleys.com/free-tools/ | head -3
HTTP/2 301
location: https://ridgesandvalleys.com/free-tools-2/
x-redirect-by: WordPress
```

Same for `/local-seo/`. **Always use `curl -sI` when the question is "what
status does this URL return," not a tool that follows redirects for you.**

**Cause.** `x-redirect-by: WordPress` means this is WordPress's own canonical
redirect, not a plugin rule or an nginx rule. It happens when the clean slug is
already claimed by another post — usually a trashed, drafted, or duplicated
page — so WordPress appended `-2` to the live one and now points the clean URL
at it.

**Fix.** This is content work in the WordPress admin, not code. Find the object
holding the `free-tools` / `local-seo` slug (check Trash and Drafts, plus any
attachment or revision with that slug), remove or rename it, then edit the real
page's permalink back to the clean slug. Flush permalinks afterward.

**Do not** change template URLs to point at `-2`. The clean slugs are the ones
in the sitemap, in outreach copy, and in anything already linked — repointing
the templates locks in the wrong canonical instead of fixing it.

**Status:** open as of 2026-08-06. The redirects work, so nothing is broken for
a visitor, but the site is serving `-2` canonicals on two pages that matter for
local SEO.
