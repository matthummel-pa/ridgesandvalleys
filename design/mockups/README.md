# Ridges & Valleys Studio — Website (static build)

A real, clickable multi-page site built to the brand guide (dark, bold, earthy, editorial).
Open `index.html` in a browser. All pages share `assets/site.css`.

## Pages
- `index.html` — Home
- `services.html` — Services + Founding offer
- `work.html` — Work (case study index)
- `process.html` — Groundwork to Launch
- `about.html` — About Matt
- `start.html` — Start a Project (working demo form)
- `blog.html` — Journal (index)
- `journal-post.html` — Single blog post template
- `project.html` — Single project / case study template

## Imagery — what's wired in

**Local (Wikimedia Commons, via Special:FilePath):**
- Cumberland Valley — hero on Home, Services; article/after images
- Wentz farm near Gettysburg (Adams County) — Work hero, featured case, Work card
- Emmitsburg Road battlefield fields, Gettysburg NMP — Process hero, Project hero, blog card
- Carbaugh Run Natural Area, **Michaux State Forest** — Home "rooted" panel, Work card, blog card
- **South Mountain** aerial — About panel

**Pexels (free commercial, no attribution):** ridge/valley/farm/fence/wheat/fog fills the rest.

> Every image sits on a dark pine fallback, so if a Wikimedia file is slow or renamed, that slot
> shows an intentional dark panel instead of breaking.

## Swap-map — drop in your hand-picked local shots

I couldn't verify a few specific shots from here (Commons is cache-only to me), so these slots use
placeholders — swap them when you've picked exact files:
- **Downtown Gettysburg / Lincoln Square** → Home hero or About panel.
  Source: https://commons.wikimedia.org/wiki/Category:Buildings_in_Gettysburg,_Pennsylvania
- **Adams County aerial farmland / orchards** → Home "rooted" panel or Work cards.
  Source: https://commons.wikimedia.org/wiki/Category:Adams_County,_Pennsylvania and NPS/USDA (public domain)
- **Caledonia State Park** → any nature slot. Source: search Commons "Caledonia State Park".

To swap: find each `background-image:url('…')` and replace the URL. For a local Commons file use
`https://commons.wikimedia.org/wiki/Special:FilePath/FILE_NAME.jpg?width=1600`.

## Licenses
- **Pexels** images: Pexels License — free commercial use, no attribution required.
- **Wikimedia Commons** images: per-file — public domain OR CC BY-SA (needs a credit + link).
  Open each file's page and check the license box before a client-facing launch.

## Notes
- Fonts (Outfit, Instrument Serif, JetBrains Mono) load from Google Fonts.
- The Start-a-Project form is a front-end demo — wire it to your inbox / Pressroot on launch.
- This is a static prototype: hand it to Pressroot/WordPress as the design reference, or host as-is.
