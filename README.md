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
- [Deploying changes to the live site](#deploying-changes-to-the-live-site)
- [Pulling live content down to local](#pulling-live-content-down-to-local)
- [Plugins & WordPress core](#plugins--wordpress-core)
- [Build resources & documentation](#build-resources--documentation)
- [Troubleshooting / error log](#troubleshooting--error-log)
- [Changelog](#changelog)

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
| `.github/workflows/deploy-theme.yml` | Production deploy (build theme → rsync to live over SSH) |
| `.github/workflows/deploy-staging.yml` | Full-Bedrock staging deploy — **idle**, dispatch-only |
| `wp-cli.yml` | Points WP-CLI at `web/wp` with docroot `web` |

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

That's the entire deploy. What happens next, automatically (workflow: `.github/workflows/deploy-theme.yml`):

1. `composer install --no-dev` + `npm ci` + `npm run build` on a clean runner (PHP 8.3, Node 20).
2. `rsync` the built theme to the live theme directory over SSH — **`--delete` scoped to the theme dir
   only**, with dev/build sources excluded (`node_modules`, `resources/css|js`, `dev`, `concept`, `docs`,
   Vite/TS config, etc.).
3. `wp cache flush` on the server.

A run takes ~1–2 minutes. **Watch it:**

```bash
gh run watch $(gh run list --workflow=deploy-theme.yml -L1 --json databaseId -q '.[0].databaseId')
```

or GitHub → **Actions** tab. To redeploy the current `main` without a code change, trigger it manually:

```bash
gh workflow run deploy-theme.yml --ref main
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

Real issues hit while standing this up, and their fixes — check here first.

| Symptom | Cause | Fix |
|---|---|---|
| CI build fails: Symfony components require PHP 8.4+ | Local PHP 8.5 locked newer deps than the runner (8.3) | Theme `composer.json` pins `config.platform.php = "8.3"`; re-lock with `composer update --lock`. Root `composer.json` is pinned the same way. |
| CI `npm ci` fails on peer-dependency conflicts | `@wordpress/*` peer ranges | `web/app/themes/ridgesandvalleys-theme/.npmrc` sets `legacy-peer-deps=true`; keep `package-lock.json` regenerated against it. |
| Deploy step: SSH auth fails / permission denied | Malformed `SSH_PRIVATE_KEY` secret (line endings) | Re-set from the key file: `gh secret set SSH_PRIVATE_KEY --repo matthummel-pa/ridgesandvalleys < ~/.ssh/rv_siteground`. |
| Local WP can't connect: **mysqli error 2002** | `DB_HOST=localhost` tries a socket | Set `DB_HOST='127.0.0.1'` in `.env`. |
| `composer install` won't place WP/plugins correctly | Missing installer path config | Root `composer.json` `extra.installer-paths` + `wordpress-install-dir: web/wp` handle this — run from repo root. |
| Deployed CSS/JS doesn't update on live | Server or SG Optimizer cache | The deploy ends with `wp cache flush`; also purge SG Optimizer / Dynamic Cache in Site Tools if needed. |
| Local site shows live URLs / mixed content | DB imported without URL rewrite | Re-run the `wp search-replace` step above. |

**Where to read live logs:** `WP_DEBUG_LOG` can point at a debug log via `.env` (see `.env.example`).
On the server, WordPress/PHP error logs are under Site Tools → Statistics → **Error Log**, and the deploy's
own logs are in the GitHub **Actions** run.

---

## Changelog

**2026-07-31 — Documentation**
- Rewrote this README with full dev/setup, deploy, build-resource, troubleshooting, and changelog sections.

**2026-07-30 — Bedrock migration complete (chosen scope)**
- **Phase 1:** Converted the repo from a bare Sage theme to a full Bedrock project. Theme moved to
  `web/app/themes/ridgesandvalleys-theme/` (git history preserved), rebranded off `roots/sage`. Composer
  now manages WordPress core `7.0.2` + plugins (Rank Math, AI Provider for OpenAI, SG Security), version-matched to live.
- **Phase 2:** Local dev switched from wp-now/SQLite to a Docker-free MariaDB + PHP 8.5 + WP-CLI stack;
  local DB seeded from a live content pull-down. Site runs at `http://localhost:8080`.
- **Phase 3:** Added `deploy-theme.yml` — GitHub Actions builds the theme and rsyncs it to the live theme
  folder over SSH (code only, never DB/uploads). First live deploy succeeded and was verified against the
  live asset manifest.
- **Decided against** restructuring the live document root to Bedrock's `web/` on SiteGround shared hosting
  (high friction, fights managed tooling). Live stays standard WordPress; the repo owns the theme. A full
  Bedrock **staging** pipeline (`deploy-staging.yml`) was built but left idle in case it's ever wanted.
- **Build fixes** that got CI green: pinned Composer platform to PHP 8.3 and re-locked; added `.npmrc`
  (`legacy-peer-deps=true`) and regenerated `package-lock.json`; re-set the `SSH_PRIVATE_KEY` secret.

_For the reasoning behind each decision, see the project docs listed above._
