# Diamond & Ridge Mercantile

Sage 11 (Roots) WordPress theme converted from the Ridges & Valleys Studio
concept in [`ridgesandvalleys`](https://github.com/matthummel-pa/ridgesandvalleys)
(`web/app/themes/ridgesandvalleys-theme/concept/gettysburg-retail/`).

Downtown retail and gifts concept for Gettysburg, PA.

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

Drop this folder into `wp-content/themes/gettysburg-retail-theme` (or Bedrock
`web/app/themes/gettysburg-retail-theme`). Activate the theme, then seed pages:

```bash
bash dev/seed-pages.sh
```

Bedrock Vite base:

```bash
VITE_BASE=/app/themes/gettysburg-retail-theme/public/build/ npm run dev
```

## Pages

| Path | Title | Template |
| --- | --- | --- |
| `/` | Home | `front-page.blade.php` |
| `about/` | Local Makers & Our Story | `template-about.blade.php` |
| `collections/` | Collections | `template-collections.blade.php` |
| `contact/` | Contact Us | `template-contact.blade.php` |
| `shop/` | Shop Locally Made Gifts Online | `template-shop.blade.php` |
| `visit/` | Visit Us & The Area | `template-visit.blade.php` |

## Note

This is a **design concept**, not a live business. Forms and checkout flows
are interactive demos.

MIT © Ridges & Valleys Studio. Sage starter is MIT © Roots.
