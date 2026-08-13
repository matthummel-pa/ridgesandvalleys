# Ridgeline Outfitters

Sage 11 (Roots) WordPress theme converted from the Ridges & Valleys Studio
concept in [`ridgesandvalleys`](https://github.com/matthummel-pa/ridgesandvalleys)
(`web/app/themes/ridgesandvalleys-theme/concept/retail-ridgeline-outfitters/`).

Outdoor gear shop concept for Gettysburg, PA.

## Stack

- [Sage 11](https://roots.io/sage/) / [Acorn](https://roots.io/acorn/)
- Blade templates
- Vite 8 + Tailwind CSS v4 (editor) + the original concept CSS
- PHP 8.3+

## Local

```bash
composer install
npm install
npm run build
```

Drop this folder into `wp-content/themes/retail-ridgeline-outfitters-theme` (or Bedrock
`web/app/themes/retail-ridgeline-outfitters-theme`). Activate the theme, then seed pages:

```bash
bash dev/seed-pages.sh
```

Bedrock Vite base:

```bash
VITE_BASE=/app/themes/retail-ridgeline-outfitters-theme/public/build/ npm run dev
```

## Pages

| Path | Title | Template |
| --- | --- | --- |
| `/` | Home | `front-page.blade.php` |
| `about/` | About Ridgeline Outfitters | `template-about.blade.php` |
| `contact/` | Contact Ridgeline Outfitters | `template-contact.blade.php` |
| `guides/` | Gettysburg Trail & Battlefield Gear Guides | `template-guides.blade.php` |
| `shop/` | Shop Hiking & Camp Gear | `template-shop.blade.php` |
| `visit/` | Visit Us in Gettysburg, PA | `template-visit.blade.php` |

## Note

This is a **design concept**, not a live business. Forms and checkout flows
are interactive demos.

MIT © Ridges & Valleys Studio. Sage starter is MIT © Roots.
