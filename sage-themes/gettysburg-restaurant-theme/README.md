# Field & Musket Tavern

Sage 11 (Roots) WordPress theme converted from the Ridges & Valleys Studio
concept in [`ridgesandvalleys`](https://github.com/matthummel-pa/ridgesandvalleys)
(`web/app/themes/ridgesandvalleys-theme/concept/gettysburg-restaurant/`).

Farm-to-table tavern concept for Gettysburg, PA.

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

Drop this folder into `wp-content/themes/gettysburg-restaurant-theme` (or Bedrock
`web/app/themes/gettysburg-restaurant-theme`). Activate the theme, then seed pages:

```bash
bash dev/seed-pages.sh
```

Bedrock Vite base:

```bash
VITE_BASE=/app/themes/gettysburg-restaurant-theme/public/build/ npm run dev
```

## Pages

| Path | Title | Template |
| --- | --- | --- |
| `/` | Home | `front-page.blade.php` |
| `menu/` | Menu | `template-menu.blade.php` |
| `reserve/` | Reservations | `template-reserve.blade.php` |
| `story/` | Our Story & Sourcing | `template-story.blade.php` |
| `visit/` | Visit Us in Downtown Gettysburg, PA | `template-visit.blade.php` |

## Note

This is a **design concept**, not a live business. Forms and checkout flows
are interactive demos.

MIT © Ridges & Valleys Studio. Sage starter is MIT © Roots.
