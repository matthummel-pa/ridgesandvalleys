# Cannon & Crumb

Sage 11 (Roots) WordPress theme converted from the Ridges & Valleys Studio
concept in [`ridgesandvalleys`](https://github.com/matthummel-pa/ridgesandvalleys)
(`web/app/themes/ridgesandvalleys-theme/concept/restaurant-cannon-and-crumb/`).

All-day cafe and bakery concept for Gettysburg, PA.

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

Drop this folder into `wp-content/themes/restaurant-cannon-and-crumb-theme` (or Bedrock
`web/app/themes/restaurant-cannon-and-crumb-theme`). Activate the theme, then seed pages:

```bash
bash dev/seed-pages.sh
```

Bedrock Vite base:

```bash
VITE_BASE=/app/themes/restaurant-cannon-and-crumb-theme/public/build/ npm run dev
```

## Pages

| Path | Title | Template |
| --- | --- | --- |
| `/` | Home | `front-page.blade.php` |
| `catering/` | Catering & Events | `template-catering.blade.php` |
| `contact/` | Contact | `template-contact.blade.php` |
| `menu/` | Menu | `template-menu.blade.php` |
| `order/` | Order Online | `template-order.blade.php` |
| `visit/` | Visit Us on Lincoln Square | `template-visit.blade.php` |

## Note

This is a **design concept**, not a live business. Forms and checkout flows
are interactive demos.

MIT © Ridges & Valleys Studio. Sage starter is MIT © Roots.
