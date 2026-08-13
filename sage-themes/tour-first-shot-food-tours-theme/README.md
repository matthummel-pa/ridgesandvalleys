# First Shot Food & History Tours

Sage 11 (Roots) WordPress theme converted from the Ridges & Valleys Studio
concept in [`ridgesandvalleys`](https://github.com/matthummel-pa/ridgesandvalleys)
(`web/app/themes/ridgesandvalleys-theme/concept/tour-first-shot-food-tours/`).

Food and history walking-tour concept for Gettysburg, PA.

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

Drop this folder into `wp-content/themes/tour-first-shot-food-tours-theme` (or Bedrock
`web/app/themes/tour-first-shot-food-tours-theme`). Activate the theme, then seed pages:

```bash
bash dev/seed-pages.sh
```

Bedrock Vite base:

```bash
VITE_BASE=/app/themes/tour-first-shot-food-tours-theme/public/build/ npm run dev
```

## Pages

| Path | Title | Template |
| --- | --- | --- |
| `/` | Home | `front-page.blade.php` |
| `book/` | Book a Gettysburg Tour Date | `template-book.blade.php` |
| `contact/` | Contact First Shot Food & History Tours | `template-contact.blade.php` |
| `faq/` | FAQ | `template-faq.blade.php` |
| `route/` | The Gettysburg Walking Route & Local Eats | `template-route.blade.php` |
| `tours/` | Gettysburg Food & History Tours | `template-tours.blade.php` |

## Note

This is a **design concept**, not a live business. Forms and checkout flows
are interactive demos.

MIT © Ridges & Valleys Studio. Sage starter is MIT © Roots.
