# Ridgeline Realty

Sage 11 (Roots) WordPress theme converted from the Ridges & Valleys Studio
concept in [`ridgesandvalleys`](https://github.com/matthummel-pa/ridgesandvalleys)
(`web/app/themes/ridgesandvalleys-theme/concept/realtor-ridgeline-realty/`).

Gettysburg homes and historic-property realty concept.

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

Drop this folder into `wp-content/themes/realtor-ridgeline-realty-theme` (or Bedrock
`web/app/themes/realtor-ridgeline-realty-theme`). Activate the theme, then seed pages:

```bash
bash dev/seed-pages.sh
```

Bedrock Vite base:

```bash
VITE_BASE=/app/themes/realtor-ridgeline-realty-theme/public/build/ npm run dev
```

## Pages

| Path | Title | Template |
| --- | --- | --- |
| `/` | Home | `front-page.blade.php` |
| `agents/` | Our Agents | `template-agents.blade.php` |
| `areas/` | Areas We Serve | `template-areas.blade.php` |
| `contact/` | Contact & Schedule a Showing | `template-contact.blade.php` |
| `listings/` | Homes for Sale in Gettysburg & Adams County | `template-listings.blade.php` |
| `sell/` | Sell Your Home in Gettysburg & Adams County | `template-sell.blade.php` |

## Note

This is a **design concept**, not a live business. Forms and checkout flows
are interactive demos.

MIT © Ridges & Valleys Studio. Sage starter is MIT © Roots.
