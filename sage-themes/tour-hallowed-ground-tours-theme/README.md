# Hallowed Ground Battlefield Tours

Sage 11 (Roots) WordPress theme converted from the Ridges & Valleys Studio
concept in [`ridgesandvalleys`](https://github.com/matthummel-pa/ridgesandvalleys)
(`web/app/themes/ridgesandvalleys-theme/concept/tour-hallowed-ground-tours/`).

Guided battlefield tour concept for Gettysburg, PA.

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

Drop this folder into `wp-content/themes/tour-hallowed-ground-tours-theme` (or Bedrock
`web/app/themes/tour-hallowed-ground-tours-theme`). Activate the theme, then seed pages:

```bash
bash dev/seed-pages.sh
```

Bedrock Vite base:

```bash
VITE_BASE=/app/themes/tour-hallowed-ground-tours-theme/public/build/ npm run dev
```

## Pages

| Path | Title | Template |
| --- | --- | --- |
| `/` | Home | `front-page.blade.php` |
| `area/` | About Gettysburg & the Battlefield | `template-area.blade.php` |
| `book/` | Book & Pay | `template-book.blade.php` |
| `contact/` | Contact & FAQ | `template-contact.blade.php` |
| `guides/` | Your Gettysburg Battlefield Guides | `template-guides.blade.php` |
| `tours/` | Gettysburg Battlefield Tours & Tickets | `template-tours.blade.php` |

## Note

This is a **design concept**, not a live business. Forms and checkout flows
are interactive demos.

MIT © Ridges & Valleys Studio. Sage starter is MIT © Roots.
