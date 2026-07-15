# Build recipe — Restaurant / Café / Taproom

A copy-paste walkthrough for building a complete restaurant site with the
Pressroot generator. Worked example: **Four Score Brewery**, a homebrew
taproom in Gettysburg — swap in your own details. Total hands-on time:
about 10 minutes.

---

## Step 1 — Theme Settings (Appearance → Pressroot → Theme Settings)

This page is the core prompt for your entire site. Everything you enter
here compiles into the brief every AI call receives.

### Identity

| Field | Enter |
|---|---|
| Business / site name | `Four Score Brewery` |
| What you do, in one line | `Small-batch beers and wood-fired plates in a converted Gettysburg barn — walk-ins welcome, dogs on the patio.` |

The one-liner matters most: it becomes the tagline, the hero fallback, and
the seed for every AI draft. Say **what**, **where**, and **one thing that
makes you different**.

### Brand

| Question | Enter | Why |
|---|---|---|
| Light or dark? | **Dark** | Evening-service restaurants photograph and convert better on dark grounds; brunch cafés should pick Light |
| Personality | **Warm & inviting** | Steers kit deals toward Warm Sand / Coral Cream / Amber Toast and warm design trends |
| Industry | **Restaurant, café & food** | Locks industry context into every prompt |
| Who is it for? | `locals and Gettysburg weekenders looking for a relaxed night out, groups welcome` | |
| Three words for your voice | `warm, unpretentious, a little funny` | |
| Main goal | **Book appointments** | This sets the header CTA to "Book now" and points every AI-written call to action at reservations |
| Imagery style | **Photography** | Food sells with photos; AI image generation will use photographic prompts |
| Content density | **Minimal — short and punchy** | Menus and hours don't need essays |
| Design trend | **🎲 Surprise me** (or lock **Editorial serif** for a classic bistro feel) | |

### AI instructions (the highest-priority line the model always obeys)

```
Always mention we take walk-ins at the bar. Hours are Tue–Sun 4–10pm,
Sunday brunch 10–2. Never call the food "elevated" or "curated". Mention
the dog-friendly patio when it fits. Prices are casual: $8–$28.
```

Leave **Auto-build on save** checked, then **Save theme settings** — the
progress bar runs: settings → core brief → design deal → smart copy. The
header CTA becomes "Book now", the footer picks up your tagline on a dark
ground, and previews unlock.

---

## Step 2 — Site type (Site Types tab)

1. Pick **Restaurant / Café — Home, Menu, Reservations** → **Apply site type**.
   The build bar runs and you get: three core-block pages, the Pressroot
   Menu (Home · Home · Menu · Reservations) assigned to your header, and a
   dealt kit + trend filtered by your Warm/Dark answers.
2. Open **📋 Restaurant marketing questions** and fill the OpenTable-style
   logistics — these become hard facts in every AI draft:

| Question | Enter |
|---|---|
| Cuisine & price range | `wood-fired American with a homebrew taproom, $$` |
| Reservations link | your OpenTable/Resy/Tock URL |
| Hours, one line | `Tue–Sun 4–10pm · Sunday brunch 10–2` |
| Signature dish | `the 48-hour smoked brisket flatbread` |

3. **Save answers.**

---

## Step 3 — Generate everything

In this order, from the pages table:

1. **✨ AI-write all pages** — the model writes Home, Menu, and
   Reservations from your brief + the logistics above.
2. **🖼 AI image** per page — hero photography per page, auto-attached and
   set as featured image (check the "Generated media" column).
3. Don't love a layout? **🎲 New design** per page, or **🎲 Refresh all**
   for a whole new theme (kit + trend + layouts). Copy survives per-page
   deals only if you AI-write after settling on layouts — so **pick
   layouts first, write second**.

## Step 4 — Ten-minute polish (the marketer's pass)

- Open **Menu** → *Edit blocks*: paste your real menu into the Table /
  card blocks. Real prices beat generated ones — never let AI invent a price.
- Open **Reservations** → confirm the booking button uses your real
  OpenTable/Resy URL (AI never touches URLs, so paste it once).
- Suggested blocks (in the editor sidebar): Menu → Table for items/prices;
  Reservations → Buttons front-and-center + hours columns.
- Add 3–5 real photos to the Media Library and swap them in — generated
  images are placeholders; real food photos convert ~2× better.
- Settings → General: confirm timezone, then publish all three pages.

## SEO quick wins (restaurant-specific)

- Title your Home page `Four Score Brewery — Taproom & Kitchen in Gettysburg, PA`
  (business + category + city is the local-search formula).
- Put hours, address, and phone in the footer tagline area — Google reads it.
- Claim the Google Business Profile and use the same name/hours everywhere
  (consistency is the #1 local ranking factor).
- The theme outputs JSON-LD automatically; keep it on unless you run Rank Math/Yoast.

---

**Rule of thumb:** answers in Theme Settings are *strategy*, the Site Types
tab is *build*, the block editor is *truth* — anything factual (prices,
hours, links) gets one manual check there before publishing.
