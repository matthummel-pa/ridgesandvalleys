# Concept demos — Ridges & Valleys Studio

Static, multi-page website concepts for Gettysburg & Adams County businesses.
These are design concepts, not live businesses.

**Live gallery:** https://matthummel-pa.github.io/ridgesandvalleys/

See a concept you like? [Get a quote](https://ridgesandvalleys.com/contact/).

## How updates go live

This folder is the source. It is **not** copied to WordPress hosting (the theme
deploy skips `concept/`). Pushing changes on `main` runs
`.github/workflows/deploy-concepts.yml`, which publishes this folder to
GitHub Pages.

The WordPress Work grid then links to:

`https://matthummel-pa.github.io/ridgesandvalleys/{folder}/`

## Concepts

| Folder | Concept |
| --- | --- |
| `gettysburg-hotel/` | The Lantern & Laurel Inn |
| `hotel-cupola-field/` | The Cupola & Field Hotel |
| `gettysburg-restaurant/` | Field & Musket Tavern |
| `restaurant-cannon-and-crumb/` | Cannon & Crumb |
| `gettysburg-retail/` | Diamond & Ridge Mercantile |
| `retail-ridgeline-outfitters/` | Ridgeline Outfitters |
| `tour-hallowed-ground-tours/` | Hallowed Ground Battlefield Tours |
| `tour-first-shot-food-tours/` | First Shot Food & History Tours |
| `realtor-ridgeline-realty/` | Ridgeline Realty |
| `realtor-keystone-homes-and-land/` | Keystone Homes & Land |

## Local preview

```bash
cd web/app/themes/ridgesandvalleys-theme/concept
python3 -m http.server 4173
```

Open http://127.0.0.1:4173/
