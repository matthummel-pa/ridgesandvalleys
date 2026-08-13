# Cupola & Field Hotel

Sage 11 (Roots) WordPress theme converted from the Ridges & Valleys Studio
concept in [`ridgesandvalleys`](https://github.com/matthummel-pa/ridgesandvalleys)
(`web/app/themes/ridgesandvalleys-theme/concept/hotel-cupola-field/`).

Modern boutique hotel concept for Gettysburg, PA.

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

Drop this folder into `wp-content/themes/hotel-cupola-field-theme` (or Bedrock
`web/app/themes/hotel-cupola-field-theme`). Activate the theme, then seed pages:

```bash
bash dev/seed-pages.sh
```

Bedrock Vite base:

```bash
VITE_BASE=/app/themes/hotel-cupola-field-theme/public/build/ npm run dev
```

## Pages

| Path | Title | Template |
| --- | --- | --- |
| `/` | Home | `front-page.blade.php` |
| `amenities/` | Amenities & Concierge | `template-amenities.blade.php` |
| `area/` | The Area | `template-area.blade.php` |
| `contact/` | Contact | `template-contact.blade.php` |
| `reservations/` | Reservations | `template-reservations.blade.php` |
| `rooms/` | Rooms & Suites | `template-rooms.blade.php` |

## Note

This is a **design concept**, not a live business. Forms and checkout flows
are interactive demos.

MIT © Ridges & Valleys Studio. Sage starter is MIT © Roots.
