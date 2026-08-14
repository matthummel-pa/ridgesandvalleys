# Ridges & Valleys Studio — Website (Bedrock + Sage)

Source of truth for the **ridgesandvalleys.com** website. This repo is a
[Bedrock](https://roots.io/bedrock/) WordPress project; the front end is a bespoke
[Sage 11](https://roots.io/sage/) theme (Blade + Tailwind CSS v4 + TypeScript + Vite + Acorn).

You develop the theme locally against a real copy of the live content, push to `main`, and a
GitHub Actions pipeline **builds the theme and deploys it to SiteGround over SSH** — code only, never
the database or uploads. The live server stays a normal SiteGround-managed WordPress; **this repo is
the source of truth for the theme.**

- **Live:** https://ridgesandvalleys.com
- **Repo:** https://github.com/matthummel-pa/ridgesandvalleys (default branch `main`)
- **Theme:** `web/app/themes/ridgesandvalleys-theme/` (Sage 11, v1.0.0, PHP 8.3+, WP 6.6+)

> **The two golden rules**
> 1. **Code flows UP** (local → GitHub → live). **Content flows DOWN** (live → local). Never push a
>    local database to live.
> 2. Deploys are **theme files only** — they never touch the live database, uploads, plugins, or core.

---

## Table of contents

- [Architecture](#architecture)
- [Repository layout](#repository-layout)
- [Prerequisites](#prerequisites)
- [Local development setup](#local-development-setup)
- [Everyday theme development](#everyday-theme-development)
- [Editing page copy without a deploy](#editing-page-copy-without-a-deploy)
- [Deploying changes to the live site](#deploying-changes-to-the-live-site)
- [Verifying a deploy actually landed](#verifying-a-deploy-actually-landed)
- [Pulling live content down to local](#pulling-live-content-down-to-local)
- [Plugins & WordPress core](#plugins--wordpress-core)
- [Accessibility & brand standards](#accessibility--brand-standards)
- [Build resources & documentation](#build-resources--documentation)
- [Troubleshooting / error log](#troubleshooting--error-log)
- [Logs & changelog](#logs--changelog)

---

## Architecture

```
Local dev (Mac)                 GitHub                         SiteGround (live)
──────────────                  ──────                         ─────────────────
Bedrock + Sage theme   ── push main ──►  Actions: build   ── rsync/SSH ──►  public_html theme dir
MariaDB (real content)                   theme + deploy                     normal managed WordPress
localhost:8080                           (code only)                        real DB + uploads (untouched)
```

- **Bedrock** gives a Composer-managed WordPress (core + plugins are pinned, reproducible) with config
  split into per-environment files and secrets kept in a gitignored `.env`.
- **Sage 11** is the theme framework: Blade templates, Tailwind CSS v4, TypeScript, and a Vite build,
  booted by [Acorn](https://roots.io/acorn/) (Laravel components inside WordPress).
- On **live**, we deliberately did *not* restructure SiteGround's document root to Bedrock's `web/`
  (high friction, low benefit on shared hosting). Live runs standard WordPress; only the **built theme**
  is deployed to it. See `claude/bedrock-migration-status.md` in the project for the full rationale.

---

## Repository layout

| Path | What it is |
|---|---|
| `composer.json` / `composer.lock` | Bedrock root — Composer manages WP core (`roots/wordpress`) + plugins |
| `config/` | Bedrock config (`application.php` + `environments/` overrides) |
| `.env` | DB creds, URLs, salts — **gitignored**, copied from `.env.example` |
| `.env.example` | Committed template for `.env` |
| `web/` | Document root |
| `web/wp/` | WordPress core — Composer-installed, **gitignored** |
| `web/app/` | `wp-content` (themes, plugins, mu-plugins, uploads) |
| `web/app/themes/ridgesandvalleys-theme/` | **The Sage theme — what you edit and what gets deployed** |
| `web/app/uploads/` | Media — lives on the server, **gitignored** |
| `.github/workflows/deploy-sage-theme.yml` | Production deploy (build theme → rsync to live over SSH) |
| `.github/workflows/deploy-staging.yml` | Full-Bedrock staging deploy — **idle**, dispatch-only |
| `wp-cli.yml` | Points WP-CLI at `web/wp` with docroot `web` |
| `CHANGELOG.md` | Human-readable release history ([Keep a Changelog](https://keepachangelog.com/en/1.1.0/) + SemVer) |
| `docs/deploy-log.md` | What shipped to live, when, and whether it worked |
| `docs/error-log.md` | Every failure hit on this project and the fix that ended it |

Composer-managed and build-generated paths are gitignored: `vendor/`, `web/wp/`, plugins,
`web/app/uploads/`, and the theme's `node_modules/`, `vendor/`, and `public/build/`.

---

## Prerequisites

Install once (macOS / Homebrew shown):

- **PHP 8.3+** and **Composer** — `brew install php composer`
- **MariaDB** (or MySQL) as a background service — `brew install mariadb && brew services start mariadb`
- **WP-CLI** — https://wp-cli.org (`brew install wp-cli`)
- **Node 20.19+ (or 22.12+)** and npm — for the Vite theme build

> The old wp-now/SQLite dev setup has been retired. Local now runs real MySQL/MariaDB so it matches live.
> If you'd rather containerize, [DDEV](https://ddev.readthedocs.io/) is a clean Bedrock fit — but the
> brew-service stack below is what this project currently uses.

---

## Local development setup

**1. Clone and install PHP dependencies**

```bash
git clone https://github.com/matthummel-pa/ridgesandvalleys.git
cd ridgesandvalleys
composer install                 # installs WP core + plugins into web/
```

**2. Create the local database** (values this project uses)

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS ridgesandvalleys;
  CREATE USER IF NOT EXISTS 'rv'@'127.0.0.1' IDENTIFIED BY 'rvpass';
  GRANT ALL ON ridgesandvalleys.* TO 'rv'@'127.0.0.1'; FLUSH PRIVILEGES;"
```

**3. Configure `.env`** — copy the template and fill it in

```bash
cp .env.example .env
```

Set at minimum:

```dotenv
DB_NAME='ridgesandvalleys'
DB_USER='rv'
DB_PASSWORD='rvpass'
DB_HOST='127.0.0.1'          # use 127.0.0.1 (not localhost) to avoid mysqli 2002 socket errors
DB_PREFIX='ssb_'             # this project's table prefix
WP_ENV='development'
WP_HOME='http://localhost:8080'
WP_SITEURL="${WP_HOME}/wp"
```

Generate the eight `AUTH_KEY … NONCE_SALT` values from https://roots.io/salts.html and paste them in.

**4. Build the theme**

```bash
cd web/app/themes/ridgesandvalleys-theme
composer install
npm ci
npm run build          # production build → public/build/
```

**5. Load real content** — see [Pulling live content down to local](#pulling-live-content-down-to-local).

**6. Run the site**

```bash
# from the repo root
wp server --docroot=web --host=127.0.0.1 --port=8080 --path=web/wp
```

Open **http://localhost:8080**. MariaDB runs as a brew service and persists across reboots.

---

## Everyday theme development

Edit the theme in `web/app/themes/ridgesandvalleys-theme/` — Blade views in `resources/views/`,
styles/JS in `resources/`, block/Customizer PHP in `app/`. From the theme directory:

| Command | What it does |
|---|---|
| `npm run dev` | Vite dev server / HMR watch while you edit |
| `npm run build` | Production asset build (what deploy runs) |
| `npm run lint:ts` | TypeScript type-check (`tsc --noEmit`) |
| `composer install` | Install/refresh Acorn + PHP deps |
| `./vendor/bin/pint` | Format PHP to the repo's Pint style (`pint.json`) |
| `npm run translate:pot` | Regenerate the translation template |

Preview at `http://localhost:8080`, then commit and push (below).

**Before you commit any template change**, run the checks that catch the things this project has
actually broken before:

```bash
npm run lint:ts        # TypeScript type-check
npm run build          # must succeed — this is exactly what CI runs
./vendor/bin/pint      # PHP formatting
```

Then look at the page at **~390px wide and at desktop width**, and confirm one primary call to
action per view. Fluid responsive is a requirement, not a nice-to-have.

---

## Editing page copy without a deploy

Every user-facing string in the page templates goes through the theme's page-fields helpers, defined
in `web/app/themes/ridgesandvalleys-theme/app/page-fields.php`:

| Helper | Returns | Use for |
|---|---|---|
| `\App\field('key', $default)` | A single string | Headings, intros, button labels |
| `\App\field_lines('key', $defaults)` | An array of lines | Bullet lists, short line-by-line content |
| `\App\field_rows('key', $defaults)` | An array of rows (assoc arrays) | Repeating cards, priced items, link lists |

The template supplies a sensible default; WordPress can override it per page. **This means routine
copy edits do not require a code change or a deploy** — they happen in the admin and take effect
immediately. Reserve deploys for structure, styling, and new sections.

When you add a new string to a template, always wire it through one of these helpers with the copy
you want as the default. Never hard-code a string that a client might reasonably want to change.

---

## Deploying changes to the live site

**This is how a theme change reaches ridgesandvalleys.com.** The moment you push to `main`, GitHub
Actions builds the theme and rsyncs it to the live theme folder over SSH — the same SSH deploy from the
Bedrock migration guide, run by CI so no keys ever leave GitHub.

```bash
git add -A
git commit -m "describe your theme change"
git push origin main
```

That's the entire deploy. What happens next, automatically (workflow: `.github/workflows/deploy-sage-theme.yml`):

1. `composer install --no-dev` + `npm ci` + `npm run build` on a clean runner (PHP 8.3, Node 20).
2. `rsync` the built theme to the live theme directory over SSH — **`--delete` scoped to the theme dir
   only**, with dev/build sources excluded (`node_modules`, `resources/css|js`, `dev`, `concept`, `docs`,
   Vite/TS config, etc.).
3. Over the same SSH connection, on the server:
   `wp acorn view:clear; wp acorn optimize:clear; wp cache flush; wp sg purge`.

Those four commands clear four different caches, and skipping any one of them produces the same
symptom — a green deploy and an unchanged page. `acorn view:clear` throws away compiled Blade
templates, `acorn optimize:clear` drops the framework's cached config, `cache flush` empties the
WordPress object cache, and `sg purge` clears SG Optimizer's full-page cache in front of WordPress.

A run takes ~1–2 minutes. **Watch it:**

```bash
gh run watch $(gh run list --workflow=deploy-sage-theme.yml -L1 --json databaseId -q '.[0].databaseId')
```

or GitHub → **Actions** tab. To redeploy the current `main` without a code change, trigger it manually:

```bash
gh workflow run deploy-sage-theme.yml --ref main
```

**Triggers:** a push to `main` that touches `web/app/themes/ridgesandvalleys-theme/**` or the workflow
file, or a manual `workflow_dispatch`. A commit that only changes root docs (like this README) does **not**
deploy — nothing in it reaches the theme.

**Deploy authentication** uses GitHub repo secrets only (Settings → Secrets and variables → Actions),
never anything in the repo: `SSH_HOST`, `SSH_USER`, `SSH_PORT`, `DEPLOY_PATH` (live theme folder), and
`SSH_PRIVATE_KEY` (the deploy key; its public half is on SiteGround under SSH Keys Manager).
`STAGING_PATH` is only used by the idle staging workflow.

**If a deploy breaks the live theme:** the blast radius is the theme only. Revert to a known-good commit
(`git revert …` → push), and the pipeline redeploys it. SiteGround also keeps automatic daily backups
(Site Tools → Security → Backups) for a full restore.

---

## Verifying a deploy actually landed

A green checkmark in the Actions tab means the workflow finished. It does **not** prove the live site
is serving your build. Two checks do.

**1. Match the run to your commit SHA, not to the clock.**

```bash
git rev-parse --short main
gh run list --limit 5
```

If the newest run's SHA is older than `origin/main`'s HEAD, you have a commit that was pushed but
never deployed — usually because it touched nothing under the theme path, so the workflow's path
filter skipped it. This happened with `3fc3371` on 2026-08-06. Fix it by pushing any theme-touching
commit, or with `gh workflow run deploy-sage-theme.yml --ref main`.

**2. Compare the Vite manifest hashes.** Vite fingerprints every built asset, so this is the
fastest honest signal:

```bash
# local
cat web/app/themes/ridgesandvalleys-theme/public/build/manifest.json

# live
curl -s https://ridgesandvalleys.com/wp-content/themes/ridgesandvalleys-theme/public/build/manifest.json
```

Same hashes = live is serving your build. Different hashes = it is not, whatever the Actions tab
says. Note the live path is `/wp-content/...`, not `/app/...` — live is managed WordPress, not
Bedrock, and using the Bedrock path returns a 404 that looks alarmingly like a failed deploy.

Two caveats, both of which have burned this project already. **Run `npm run build` before you
compare** — an old local manifest looks exactly like a stale live site. And the manifest only
fingerprints **Vite output**: a Blade-only change ships as a plain file and never moves a hash, so
matching hashes do not prove a template change landed. For those, check the SHA and look at the
page.

Then run the accessibility pass on the changed page and check it at ~390px. Record the outcome in
`docs/deploy-log.md`.

---

## Pulling live content down to local

Refresh your local database with real content whenever you need it. Content only ever flows **down** —
live is never at risk.

1. SiteGround Site Tools → **phpMyAdmin** → export the live database (SQL).
2. Import into the local `ridgesandvalleys` database (drop/recreate first for a clean load).
3. Rewrite the URLs to local:
   ```bash
   wp search-replace 'https://ridgesandvalleys.com' 'http://localhost:8080' \
     --path=web/wp --all-tables
   ```
4. *(Optional)* copy live `wp-content/uploads` down if you need media locally.

---

## Plugins & WordPress core

- **On live:** leave SiteGround's WordPress Autoupdate **on**. SiteGround updates and hardens core and
  auto-updates plugins. You do nothing, and the deploy never overwrites them.
- **In the repo:** Composer pins the same plugins so local dev matches live — currently
  **SEO by Rank Math**, **AI Provider for OpenAI**, and **SG Security**, plus WordPress core `roots/wordpress 7.0.2`.
- **Realign local with live** occasionally by bumping the version in the root `composer.json`
  (or `composer update wpackagist-plugin/<name>`), then `composer install`.
- **Never deploy plugins or core from the repo to live** — that would fight SiteGround's managed updates.
  The theme is the only thing the pipeline pushes.

*Adding repo-driven custom PHP to live later:* the clean path is a single committed mu-plugin file
(`web/app/mu-plugins/rv-site.php`) plus one additive (never `--delete`) rsync step in the workflow.
Not wired up yet — there's no custom code to deploy today. See `claude/operations-runbook.md`.

---

## Accessibility & brand standards

These are build requirements, not preferences. Anything that ships has to clear them.

**Accessibility — WCAG 2.2 AA on every page**, built in rather than bolted on. In practice that
means real semantic headings in order, a visible focus state on every interactive element, text
contrast that passes at AA, an accessible name on every icon-only control and every landmark list,
alt text on meaningful images and empty alt on decorative ones (the contour SVGs are decorative),
and a layout that survives 200% zoom. Check the changed page before you push, not after.

**Design authorship.** Styles are bespoke and hand-authored — Tailwind v4 tokens plus Blade. No
AI-generated design, no presets, no style kits. AI is used for content and planning only. We tell
clients this, so it has to stay true.

**Brand facts must be identical everywhere** (name, address, phone — "NAP"):

```
Ridges & Valleys Studio
(223) 340-8098   ·  schema: +1-223-340-8098
matt@ridgesandvalleys.com
https://ridgesandvalleys.com
Gettysburg, PA — serving Adams County & South Central PA
```

Ridges & Valleys is a **service-area business**: never publish a street address. In templates, pull
these from the Customizer values (`rv_contact_phone`, `rv_contact_email`, `rv_contact_location`)
rather than typing them in, so one change updates every appearance.

**Claims must be verifiable in 60 seconds.** The proof strip — 15+ yrs · ~7 days to first draft ·
WCAG 2.2 AA · You own it — is the standard. No invented stats, ratings, or awards. No implied local
tenure. If you cannot point at the evidence, it does not go on the page.

---

## Build resources & documentation

**Roots stack (theme + Bedrock)**
- Bedrock — https://roots.io/bedrock/ · docs: https://docs.roots.io/bedrock/master/installation/
- Sage 11 — https://roots.io/sage/ · docs: https://docs.roots.io/sage/master/
- Acorn (Laravel in WordPress) — https://roots.io/acorn/ · docs: https://docs.roots.io/acorn/master/
- Sage Blade views — https://docs.roots.io/sage/master/blade-templates/
- WordPress salts generator — https://roots.io/salts.html

**Front-end build tooling**
- Blade templating — https://laravel.com/docs/blade
- Tailwind CSS v4 — https://tailwindcss.com/docs
- Vite — https://vite.dev · Roots Vite plugin — https://github.com/roots/vite-plugin
- TypeScript — https://www.typescriptlang.org/docs/

**WordPress / infra**
- Composer WordPress packages — https://wpackagist.org
- WP-CLI — https://wp-cli.org
- DDEV (optional containerized local) — https://ddev.readthedocs.io/
- SiteGround SSH & Git — https://www.siteground.com/tutorials/ssh/
- GitHub Actions — https://docs.github.com/actions

**Project docs (in the Claude project, not this repo)**
- `bedrock-github-siteground-migration-plan.md` — the original migration plan
- `bedrock-migration-status.md` — what was actually built and the decisions made
- `operations-runbook.md` — day-to-day operating guide

---

## Troubleshooting / error log

The full, running list lives in **[`docs/error-log.md`](docs/error-log.md)** — check there first, and
add an entry any time you lose more than fifteen minutes to something. The table below is the short
version of the issues hit while standing this up.

| Symptom | Cause | Fix |
|---|---|---|
| CI build fails: Symfony components require PHP 8.4+ | Local PHP 8.5 locked newer deps than the runner (8.3) | Theme `composer.json` pins `config.platform.php = "8.3"`; re-lock with `composer update --lock`. Root `composer.json` is pinned the same way. |
| CI `npm ci` fails on peer-dependency conflicts | `@wordpress/*` peer ranges | `web/app/themes/ridgesandvalleys-theme/.npmrc` sets `legacy-peer-deps=true`; keep `package-lock.json` regenerated against it. |
| Deploy step: SSH auth fails / permission denied | Malformed `SSH_PRIVATE_KEY` secret (line endings) | Re-set from the key file: `gh secret set SSH_PRIVATE_KEY --repo matthummel-pa/ridgesandvalleys < ~/.ssh/rv_siteground`. |
| Local WP can't connect: **mysqli error 2002** | `DB_HOST=localhost` tries a socket | Set `DB_HOST='127.0.0.1'` in `.env`. |
| `composer install` won't place WP/plugins correctly | Missing installer path config | Root `composer.json` `extra.installer-paths` + `wordpress-install-dir: web/wp` handle this — run from repo root. |
| Deployed CSS/JS doesn't update on live | Acorn view cache, WP object cache, or SG Optimizer page cache | The deploy now ends with `wp acorn view:clear; wp acorn optimize:clear; wp cache flush; wp sg purge`. If you deployed by hand, run all four. |
| Live manifest URL returns 404 | Used the Bedrock path on a managed-WordPress install | Live is `/wp-content/themes/...`, not `/app/themes/...`. |
| Pushed to `origin/main` but live is stale | The commit touched nothing under the theme path, so the workflow's path filter skipped it | Match the run to the SHA (`gh run list`), then push a theme-touching commit or `gh workflow run deploy-sage-theme.yml --ref main`. |
| Local `main` silently behind `origin/main` | Two worktrees share one `.git` | `git rev-list --left-right --count origin/main...main`, then stash → `git pull --rebase origin main` → stash pop. |
| Local site shows live URLs / mixed content | DB imported without URL rewrite | Re-run the `wp search-replace` step above. |

**Where to read live logs:** `WP_DEBUG_LOG` can point at a debug log via `.env` (see `.env.example`).
On the server, WordPress/PHP error logs are under Site Tools → Statistics → **Error Log**, and the deploy's
own logs are in the GitHub **Actions** run.

---

## Logs & changelog

Three files, three jobs. Keep them current — they are the only reason anyone can pick this project up
cold, including you in six months.

| File | What goes in it | When to write |
|---|---|---|
| [`CHANGELOG.md`](CHANGELOG.md) | What changed in the product, by version | Every meaningful change, under `[Unreleased]` |
| [`docs/deploy-log.md`](docs/deploy-log.md) | What shipped to live, when, whether it worked | After every deploy you verified |
| [`docs/error-log.md`](docs/error-log.md) | Failures and the fix that ended them | Any time you lose 15+ minutes |

`CHANGELOG.md` follows [Keep a Changelog 1.1.0](https://keepachangelog.com/en/1.1.0/) and
[Semantic Versioning](https://semver.org/). Add your entry under `[Unreleased]` as you work; cut a
version number by hand at a milestone. Deploys are continuous and do not bump the version — the
deploy log tracks those.

_For the reasoning behind the architectural decisions, see the project docs listed above._
