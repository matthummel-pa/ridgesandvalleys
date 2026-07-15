# Ridges & Valleys Studio — website

The WordPress theme + site for **[ridgesandvalleys.com](https://ridgesandvalleys.com)** — a
Gettysburg & South Central PA web design and local growth studio.

Built on **[Pressroot](https://github.com/matthummel-pa/pressroot)** (a Sage 11 / Roots theme
framework) and customized into the studio's own brand: earthy, bold, editorial. *Historic, not
antique. Local, not touristy. Fast, not flimsy.*

> This repository is its own independent entity — the main business site — cloned from Pressroot
> at v1.7.0 and rebranded. It does **not** track Pressroot as an upstream fork; pull framework
> updates in manually when you choose.

## What's customized vs. Pressroot
- **`style.css`** — Ridges & Valleys theme header.
- **`theme.json`** — earthy palette (Ridge Pine, Gettysburg Clay, Harvest Wheat, Valley Sage,
  Fieldstone, Cream Paper, Bark Ink, Dusk Slate) mapped onto Pressroot's existing color slugs, so
  every framework component inherits the new brand. Fonts (Outfit / Instrument Serif / JetBrains
  Mono) already match the brand and are unchanged.
- **`.playground/blueprint.json`, `package.json`** — theme folder renamed to `ridgesandvalleys`.
- **`extras/mu-plugins/ridges-valleys-seo.php`** — SEO/perf/a11y drop-in (see docs).
- **`design/`** — the source design: HTML mockups (`design/mockups/`) and brand board + guidelines
  (`design/brand/`). This is the design backup requested.
- **`docs/SEO-PERFORMANCE-ACCESSIBILITY.md`** — the launch playbook.

Text domain is kept as `pressroot` so the framework's translation strings keep resolving.

## Local development (from Pressroot's flow)
Requires PHP 8.3+, Composer, Node 20.19+/22.12+.

```bash
composer install
npm install
npm run dev          # Vite dev server
# or a full local WordPress in the browser (WP Playground):
npm run wp           # serves the theme at http://localhost:8881
```

See `DEV-LOCAL.md` for the full Pressroot dev notes.

## Build for production / import to the live site
```bash
composer install --no-dev -o
npm ci
npm run build        # compiles assets to public/build
```
Then package the theme (excludes dev files via `.distignore`) and upload:
- Zip the theme folder as `ridgesandvalleys` and install via **Appearance → Themes → Add New → Upload**, **or**
- Drop the folder into `wp-content/themes/ridgesandvalleys/` on the host.
- Copy `extras/mu-plugins/ridges-valleys-seo.php` into `wp-content/mu-plugins/`.
- Follow `docs/SEO-PERFORMANCE-ACCESSIBILITY.md` before go-live.

An import-ready **source package** is provided alongside this repo. A production package still
needs the `composer install` + `npm run build` step above (standard for any Sage theme).

## Credits
Framework: Pressroot © Matt Hummel (MIT). This site © 2026 Ridges & Valleys Studio.
