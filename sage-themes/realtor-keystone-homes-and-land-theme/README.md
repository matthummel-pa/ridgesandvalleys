# Keystone Homes & Land

Sage 11 (Roots) WordPress theme converted from the Ridges & Valleys Studio
concept in [`ridgesandvalleys`](https://github.com/matthummel-pa/ridgesandvalleys)
(`web/app/themes/ridgesandvalleys-theme/concept/realtor-keystone-homes-and-land/`).

Adams County farms, land, and acreage realty concept.

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

Drop this folder into `wp-content/themes/realtor-keystone-homes-and-land-theme` (or Bedrock
`web/app/themes/realtor-keystone-homes-and-land-theme`). Activate the theme, then seed pages:

```bash
bash dev/seed-pages.sh
```

Bedrock Vite base:

```bash
VITE_BASE=/app/themes/realtor-keystone-homes-and-land-theme/public/build/ npm run dev
```

## Pages

| Path | Title | Template |
| --- | --- | --- |
| `/` | Home | `front-page.blade.php` |
| `agents/` | Our Agents | `template-agents.blade.php` |
| `areas/` | Areas We Serve | `template-areas.blade.php` |
| `contact/` | Contact Keystone Homes & Land | `template-contact.blade.php` |
| `guide/` | Land Buyer's Guide | `template-guide.blade.php` |
| `listings/` | Farm, Land & Home Listings in Gettysburg, PA | `template-listings.blade.php` |

## Note

This is a **design concept**, not a live business. Forms and checkout flows
are interactive demos.

MIT © Ridges & Valleys Studio. Sage starter is MIT © Roots.
