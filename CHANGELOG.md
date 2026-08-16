# Changelog

All notable changes to the Ridges & Valleys Studio website live here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this
project uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html). Dates are the
date the change landed on `main`.

**What counts as a release here:** every push to `main` that touches
`web/app/themes/ridgesandvalleys-theme/**` builds and deploys automatically. Version
numbers below are cut by hand when a meaningful group of work ships — they mark
milestones, not individual deploys. For the deploy-by-deploy record (run IDs, timings,
failures), see [`docs/deploy-log.md`](docs/deploy-log.md). For problems hit and how they
were fixed, see [`docs/error-log.md`](docs/error-log.md).

**How to add an entry:** put your change under `## [Unreleased]` in the right category
as part of the same commit that makes the change. When you cut a version, move the
`Unreleased` items down under a new heading with today's date.

Categories: `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security`.

---

## [Unreleased]

### Added

- **Free Tools hub: chooser, filters, limits, privacy, and next steps** — all
  copy lives in Page content fields (no Gutenberg). New sections help people
  pick a checker, read the score colors, see what a machine cannot judge, and
  move from a report to Rescue / Local Launch / a quote.
- **About page: proof strip in the hero** — 15+ yrs, ~7 days to first draft,
  WCAG 2.1 AA, You own it. Every item is a claim we can back up in under a minute.
- **About page: "Try us before you hire us" band** — links all six free tools
  (Website Grader, SEO Checker, Accessibility Checker, Security Checker, Email
  Deliverability Checker, Local SEO Scorecard) with a plain-English "what it does"
  line each, plus a link to `/free-tools/`.
- **About page: pricing + contact band** — the four fixed-price offers stated openly
  (Website Rescue, Local Launch, Growth Site, Care & Grow) next to a NAP block
  (studio name, phone, email, based-in, service area) that matches the footer exactly.
  Phone and email pull from the same Customizer settings the footer uses, so there is
  one place to change them.
- **`CHANGELOG.md`** (this file), **`docs/deploy-log.md`**, and **`docs/error-log.md`**.
- **`docs/` directory** for operational records that do not belong in the README.

### Changed

- **Free Tools page design** — featured Website Grader card, filter chips,
  numbered how-to, proof ribbon in the hero, and on-page calculators kept as
  built-in tools (not blocks). Hero H1/lede use the full content width, with
  extra space between the eyebrow and the main navigation.
- **README expanded** — four new sections: "Editing page copy without a deploy" (the
  `\App\field()` / `field_lines()` / `field_rows()` helpers, and why routine copy
  changes should never need a deploy), "Verifying a deploy actually landed" (match the
  Actions run to the SHA, then compare Vite manifest hashes — with the two caveats that
  have burned us), "Accessibility & brand standards" (WCAG 2.1 AA, bespoke-styles-only,
  NAP, verifiable claims), and "Logs & changelog" (which of the three files to write in,
  and when). The pre-commit checks and the four cache-clearing commands the deploy runs
  are now spelled out, and the troubleshooting table gained four entries.
- **`.gitignore`** now ignores `_to_delete/`, the local scratch folder used to stage
  files for manual deletion.
- **Deploy workflows now purge the SiteGround cache too** (`wp sg purge`) after the
  WordPress object cache flush, on both the production and staging pipelines. Without
  it, SG Optimizer's page cache could keep serving the old markup for minutes after a
  successful deploy, which looked like a failed deploy.

### Removed

- **Gutenberg on the Free Tools template** — the block editor is hidden, and
  leftover block markup is cleared on save so the page cannot drift back to
  editor content.

### Fixed

- **Header and footer email links were dead on every page.** The `href` used
  `{{ antispambot($rvEmail) }}`; Blade escaped the ampersand of each HTML entity
  `antispambot()` returns, so the link resolved to literal `&amp;#109;&amp;#97;tt…`
  text instead of an address. Switched both to `{!! … !!}`, matching the adjacent
  `<span>` that was already correct. This was live on a lead-generation site, so
  anyone clicking the contact link in the header or footer got a broken compose window.
- **Social row email icon pointed at `mailtohello@ridgesandvalleys.com`.** Two faults
  in one link: a doubled `mailto` prefix and the wrong address. The Customizer field
  sanitizes with `sanitize_email()`, which strips the colon out of a pasted
  `mailto:hello@…` and stores `mailtohello@…`; `social_links()` then prepended
  `mailto:` again. Added `\App\social_email_address()` to strip any leading mailto
  prefix and validate the result, used it both when rendering the link and as the
  Customizer's sanitize callback, and made the link skip itself entirely rather than
  render if no valid address survives.
  **Still needs a human:** the stored address is `hello@`, but the studio address is
  `matt@ridgesandvalleys.com`. That is content, not code — fix it in
  Customizer → Social Links → Email.

### Corrected

- **`docs/deploy-log.md`** claimed commit `3fc3371` had been pushed but never deployed,
  leaving live one commit stale. It had not. `3fc3371` is an empty commit —
  `git diff --stat 3fc3371^ 3fc3371` returns nothing — and the newsletter work it was
  named for actually shipped in `48105d5`, which deployed normally. The entry now
  records the wrong conclusion alongside the right one, and the "Pushed to origin but
  never deployed" runbook in `docs/error-log.md` now tells you to check the commit is
  non-empty before concluding anything.
- **`docs/error-log.md`** claimed the duplicate `-2` slugs were a local-only problem and
  that "the live database is clean." Production has the same problem:
  `curl -sI https://ridgesandvalleys.com/free-tools/` returns `301` to `/free-tools-2/`,
  same for `/local-seo/`, both with `x-redirect-by: WordPress`. The earlier check used a
  fetcher that followed redirects silently, so a 301 looked like a 200. Entry rewritten,
  including the method error that caused it, and flagged as open.

### Notes

- The About page copy lives in the Blade template as translatable defaults. Every
  string is overridable per-page through the theme's page-fields system
  (`\App\field()` / `\App\field_rows()`), so content can be edited in WordPress
  without a deploy.

---

## [1.2.0] — 2026-08-06

### Added

- Site-wide custom code injection — header, below-body, and footer slots.
- Editable footer contact fields (phone, email, location) with Gettysburg, PA defaults.
- Bespoke local-SEO sidebar on single posts, always on.
- HubSpot newsletter signup as a native form in the footer.

### Changed

- Previous/Next post navigation redesigned as cards.
- Comment form restyled — mono eyebrow, gradient accent stripe, pine focus ring.
- Newsletter moved out of the blog index and into the footer, so it appears
  site-wide instead of on one template.
- SEO titles and meta descriptions optimized for all core pages and wired to Yoast,
  with Rank Math kept as a fallback.
- Footer email set back to `matt@ridgesandvalleys.com`.

### Removed

- Editor-content block from bespoke page templates — the templates own their layout,
  so the duplicate block was producing a second, unstyled copy of the content.
- Editor-content width/alignment control, superseded by the page-level layout controls.
- Local-SEO content block from the journal (blog index).

---

## [1.1.0] — 2026-08-05

### Added

- Dynamic layout controls — per-entry hero, content width, and sidebar toggles.
- Per-page hero typography plus hero button label and style controls.
- Hero copy width and font-size Customizer controls.
- Hero image credit / caption with show-hide and Customizer styling, extended to
  in-content photos.
- Open Images — search and import openly licensed photos from Openverse.
- Reorderable homepage sections with per-section image fields.
- Contour brand system — Young Serif and Geist Mono, topographic SVG motifs.
- Logo wordmark with Customizer logo-size and hero alignment controls.

### Changed

- Uniform interior hero sizing, tighter About padding, per-page Featured Image heroes.
- Interior page section styling and reveal-on-scroll treatment.
- Contact info added to the footer's first column and to the mobile menu.

---

## [1.0.0] — 2026-07-30

First Bedrock release. The repo became the source of truth for the theme, and the
first automated deploy reached the live site.

### Added

- **Bedrock structure** — `web/` docroot, `web/wp/` core, `web/app/` for themes,
  plugins, and uploads, `config/` for environment config, `.env` kept out of git.
- **Sage 11 bespoke theme** at `web/app/themes/ridgesandvalleys-theme/`
  (Blade + Tailwind CSS v4 + TypeScript + Vite + Acorn).
- **DDEV config** and a pinned `composer.lock` (WordPress 7.0.2 and plugins).
- **GitHub Actions deploy pipeline** — builds the theme, rsyncs it to SiteGround over
  SSH, then clears the Acorn view cache and flushes the WordPress cache.
- **Staging deploy pipeline** (`workflow_dispatch` only, currently idle) that can push
  the full Bedrock tree to a staging path if we ever move off managed shared hosting.

### Changed

- Theme folder renamed to `ridgesandvalleys-theme` to match the live active-theme slug,
  so a deploy overwrites the running theme instead of installing a second one.
- All Pressroot references removed from code, comments, and CI.

### Fixed

- **Composer platform mismatch.** The lock file had been generated under local PHP 8.5,
  so CI (PHP 8.3) rejected Symfony 8.x. Pinned `config.platform.php` to `8.3` in both
  the root and theme `composer.json` and re-locked; Symfony resolved back to 7.4.
- **`npm ci` lock mismatch.** A global `legacy-peer-deps=true` in `~/.npmrc` meant the
  local lock file could never be reproduced in a clean CI runner. Added a repo-level
  `.npmrc` and regenerated `package-lock.json`.
- **Deploy cache flush** — switched to `cd` + `wp` so the remote path resolves reliably,
  and added an explicit Acorn view-cache clear, since Blade compiles views outside the
  theme directory and rsyncing new `.blade.php` files alone does not update the site.

---

## [0.1.0] — 2026-07-15

### Added

- Initial Ridges & Valleys Studio site on the Pressroot framework, rebranded with the
  earthy palette in `theme.json`, plus an SEO / performance / accessibility mu-plugin
  and playbook. Superseded by the Bedrock rebuild in 1.0.0.

[Unreleased]: https://github.com/matthummel-pa/ridgesandvalleys/compare/main...HEAD
