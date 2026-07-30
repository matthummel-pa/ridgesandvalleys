# Ridges & Valleys Studio — Sage 11 theme

Bespoke WordPress theme for **Ridges & Valleys Studio** (Gettysburg & South Central PA
web design), built on **Sage 11 (Roots)**: Blade templating, **Tailwind CSS v4**,
**TypeScript**, Vite, and Acorn — with a **custom-block development** workflow.

Earthy, editorial, accessibility-first. Historic, not antique. Fast, not flimsy.

## Stack
- **Sage 11 / Acorn** — Laravel-style app container, Blade views (`resources/views`)
- **Tailwind CSS v4** — design tokens as `@theme` in `resources/css/app.css`, which also
  generate the block-editor `theme.json` palette via `@roots/vite-plugin`
- **TypeScript** — `resources/js/*.ts` (front-end + block editor), bundled by Vite
- **Custom blocks** — dynamic, server-rendered in `app/blocks.php`; editor UI in
  `resources/js/editor.ts`. Example: **Ridgeline CTA** (`rv/ridgeline-cta`)

## Included
- Full Blade template set (front-page, index, single, page, archive, search, 404,
  contact, project single/archive, partials)
- Projects CPT (`/work/`) + Project Type taxonomy + details meta box (`app/projects.php`)
- Plugin-free contact form (`app/contact.php`) — nonce + honeypot + rate limit + wp_mail
- Customizer → Theme Options (`app/customizer.php`) — colors, layout, CTA, footer, social
- Accessible off-canvas menu (TypeScript, focus-trapped)

See **DEV.md** for how to run and build locally.

MIT © 2026 Matt Hummel — Ridges & Valleys Studio. Built on Sage (roots.io), MIT.
