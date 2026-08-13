# Sage concept themes

Each folder is a **Sage 11 (Roots)** WordPress theme converted from
`web/app/themes/ridgesandvalleys-theme/concept/`.

This Cursor token cannot create GitHub repositories. After you create
empty repos (or run `publish-to-github.sh` with a token that can),
connect each repo in Cursor so follow-up work can land there.

| Local folder | Suggested GitHub repo | Concept |
| --- | --- | --- |
| `gettysburg-hotel-theme/` | `matthummel-pa/gettysburg-hotel-theme` | Lantern & Laurel Inn |
| `hotel-cupola-field-theme/` | `matthummel-pa/hotel-cupola-field-theme` | Cupola & Field Hotel |
| `gettysburg-restaurant-theme/` | `matthummel-pa/gettysburg-restaurant-theme` | Field & Musket Tavern |
| `restaurant-cannon-and-crumb-theme/` | `matthummel-pa/restaurant-cannon-and-crumb-theme` | Cannon & Crumb |
| `gettysburg-retail-theme/` | `matthummel-pa/gettysburg-retail-theme` | Diamond & Ridge Mercantile |
| `retail-ridgeline-outfitters-theme/` | `matthummel-pa/retail-ridgeline-outfitters-theme` | Ridgeline Outfitters |
| `tour-hallowed-ground-tours-theme/` | `matthummel-pa/tour-hallowed-ground-tours-theme` | Hallowed Ground Battlefield Tours |
| `tour-first-shot-food-tours-theme/` | `matthummel-pa/tour-first-shot-food-tours-theme` | First Shot Food & History Tours |
| `realtor-ridgeline-realty-theme/` | `matthummel-pa/realtor-ridgeline-realty-theme` | Ridgeline Realty |
| `realtor-keystone-homes-and-land-theme/` | `matthummel-pa/realtor-keystone-homes-and-land-theme` | Keystone Homes & Land |

## Publish

From a machine whose `gh` login can create repositories:

```bash
bash sage-themes/publish-to-github.sh
```

Regenerate from the HTML concepts:

```bash
python3 sage-themes/generate.py
```

