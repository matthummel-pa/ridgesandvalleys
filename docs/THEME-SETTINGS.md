---
title: Settings reference
---

# matthummel theme â€” settings reference

Every setting the theme exposes, where to find it, and what it does. The theme is
built on **Sage 11 / Acorn**; all options write WordPress **theme mods** (or options,
where noted) and render with no build step.

Two places hold settings:

1. **Customize â†’ Theme Options** â€” the live, preview-as-you-edit panel.
2. **Appearance â†’ Pressroot** â€” one consolidated settings page (Setup wizard,
   AI Models, Theme Settings, Site Types, GitHub, Support) for everything that
   isn't a live visual preview, plus **Appearance â†’ Local Fonts**, which
   stayed separate. New installs land on the **Setup** tab â€” a six-step
   guided wizard (see [SETUP-WIZARD.md](SETUP-WIZARD.md)).

---

## Customizer â†’ Theme Options

### Colors
- **Brand / buttons** â€” primary green used for buttons, links, accents (`--color-green`).
- **Page background** â€” site background (`--color-khaki`).
- **Headings** â€” heading text color (`--color-ink`).
- **Body text** â€” body copy color (`--color-body`).

### Typography
- **Heading font** / **Body font** â€” pick from Geist, Bricolage Grotesque, Schibsted Grotesk, Space Grotesk, Sora, Inter Tight, Fraunces, Inter, Work Sans, or System.
- **Base font size** â€” root body size (15â€“19px).
- **Body line height** â€” 1.5 to 2.0.
- **Heading line height** â€” 1.0 to 1.3.
- **Heading letter spacing** â€” tighter â†’ wide.

### Typography (advanced)
- **Navigation font** / **Button font** â€” assign different families to the nav and buttons (loads only when set).
- **Heading / Body / Nav / Button weight** â€” per-element font weights.
- **Nav letter case** â€” normal / UPPERCASE / lowercase.
- **Body letter spacing** â€” tight / normal / loose.
- **Base font on tablet / mobile** â€” responsive overrides at â‰¤1024px and â‰¤600px.

### Extras
- **Underline content links** â€” underline links inside post/page content.
- **Button corner radius** â€” square â†’ pill.
- **Card corner radius** â€” 6â€“20px.
- **Text selection color** â€” `::selection` background.
- **Scroll-to-top button** â€” floating back-to-top control.

### Layout
- **Default content width** â€” global content max-width (standard presets).
- **Per type (Pages / Posts / Projects / Archives)** â€” width preset, custom width, and show-sidebar toggle for each content type.

### Header Layout
- **Full-width menu** â€” stretch the nav across the header.
- **Header width / height / gap** â€” sizing of the header row.
- **Element position** â€” order of logo, menu, social links, and button.
- **Header button** â€” show/hide, text, and URL of the header CTA.
- **Sticky header** â€” pin the header on scroll.
- **Shrink on scroll** â€” reduce header padding once scrolled (needs sticky).
- **Transparent overlay header** â€” off / front page only / all pages.

### Top Bar
- **Enable top bar**, **contact text**, **show social links**, **button text/URL**, **background** and **text color** (palette).

### Navigation
- Full flexbox control of the primary menu: **direction, justify, align, align-content, wrap, gap**.
- Menu item box/type: **padding, min-height, font-size, weight, transform, letter-spacing, radius, color, hover color**.

### Menu & Popout
- **Use menu icon on desktop / tablet / mobile** â€” where the hamburger replaces the inline nav.
- **Panel background** â€” solid or gradient (start, end, angle), **text / icon color**.
- **Popout width**, **menu columns** and **block columns** (desktop), plus **popout item styling** (align, padding, font, weight, transform, gap).

### Social Icons
- **Show social icons in navigation bar** + **position** (left / center / right).
- **Display** (text or icons), **size, shape, color, chip background, hover color**.
- **Social URLs** â€” LinkedIn, GitHub, Dev.to, X, Bluesky, YouTube, Instagram, Facebook, Mastodon, Email, RSS (each shows its Blade icon).

### Announcement Bar
- **Show bar**, **message**, **link text/URL**, **background/text color**, **dismissible** (remembered per visitor), **hide on mobile**, and optional **start/end dates** for scheduling.

### Hero
- **Copy** â€” editable **eyebrow**, **H1 title**, and **sub-paragraph** (clear a field to hide that element).
- **Layout** â€” **columns** (1â€“3: content + side image + 2nd image), **content position** (horizontal & vertical), **content max-width** + **spacing**, with **tablet/mobile** max-width overrides.
- **Flexbox (advanced)** â€” direction, justify, align, wrap, gap on the hero container.
- **Media** â€” **side image / illustration** (+ 2nd for 3-column), **image side**, or a **background cover image** with **overlay %** and **min-height**.
- **Image finder** (per image control) â€” search **Openverse** (no key), **Unsplash**/**Pexels** (optional keys â†’ fields in this section), or generate a free **AI** image; the pick is imported to the Media Library and set.
- **Entrance animation** â€” fade-up/in, zoom, pop, blur, slide.

### Animations
- **Enable on-scroll animations** (site-wide), **effect** (fade-up/in, zoom, pop, blur, slide), and **speed** (fast/normal/slow). Honours `prefers-reduced-motion`.

### Responsive (mobile & tablet)
- **Hide on mobile/tablet** â€” navigation social (mobile/desktop), top-bar social, top-bar button, navbar button (mobile + tablet), the "Menu" label, and shrink the logo on mobile.
- **Keep top bar on one line** (tablet), and **per-breakpoint inner widths** for the top bar, navbar, and message bar (tablet + mobile). Mobile = â‰¤640px, tablet = 641â€“1024px.

### Dark Mode
- **Enable dark mode toggle** and **default mode** (light / dark / auto by system).

### Footer & Header
- **Show social icons in footer**. *(Sticky header lives in Header Layout.)*
- **Footer background / text** â€” palette choice or custom hex.
- **Footer columns** â€” 1â€“4 (each maps to a block widget area under Appearance â†’ Widgets).
- **Footer tagline**.

### CTA & Intros
- Global **project CTA** plus intro text for the Projects and Contact templates.

### SEO & Schema
- **Output meta + schema** (auto-disables if Rank Math/Yoast is active).
- **Entity** â€” Person or Organization, **name**, **logo**, **default share image**, **Twitter/X handle**.
- Emits Open Graph, Twitter cards, and JSON-LD (Person/Org, WebSite, Article, BreadcrumbList).

### Performance
- Toggles: **disable emojis, oEmbed/wp-embed, jQuery Migrate, XML-RPC/pingbacks, dashicons (logged-out), wp_head cleanup, defer scripts**.
- **Preconnect domains** â€” comma-separated origins.

### Custom Code
- **Head / Body / Footer code** injection (analytics, pixels, verification) and a **Custom CSS** box.

### Newsletter
- **Form action URL** (Mailchimp), **heading**, **sub-note**, **button text** â€” rendered by `[prt_newsletter]`.

### Cookie Notice
- **Show notice**, **message**, **accept button**, **policy link** (URL + text). Dismissal remembered locally.

### White Label
- **Login logo**, **login background**, **admin footer text**, and **"Get started" dashboard widget** toggle.

---

## Appearance â†’ Pressroot

Everything above (General, Design, Layout, Header, Footer, Social Links, etc.)
used to also be mirrored on a tabbed "Theme Settings" admin page. That page was
removed â€” it was pure duplication of the Customizer controls above. What
remained was four separate Appearance submenu pages (Theme Tools, Starter
Sites, Pressroot AI, GitHub); those are now one page, **Appearance â†’
Pressroot**, navigated with a left-sidebar menu (one section per area) and
the active section's content on the right:

### Setup (tab)
The six-step guided onboarding wizard (`app/setup-wizard.php`) — the first
tab, and the default landing tab until it has been completed once. Progress
is saved per step (option `prt_wizard_progress`), the tab resumes where the
owner left off, and every step stays revisitable afterwards.

1. **Business info** — identity, business/website type dropdown, industry,
   brand & voice, colors/fonts, logo + photo/video uploads, and the
   business-fact fields (`prt_biz_mission`, `prt_biz_about`, `prt_biz_email`,
   `prt_biz_phone`, `prt_biz_address`, `prt_biz_hours`) that compile into the
   CORE SITE BRIEF so the AI states real facts.
2. **Connections** — AI provider keys, an SEO plugin selector (built-in /
   Yoast / Rank Math / All in One SEO, with one-click install/activate and a
   beginner's SEO primer; stored as `prt_seo_choice`), a validated **GA4
   Measurement ID** (`prt_ga4_id`, auto-injects gtag.js) with Google
   Analytics + Google Business Profile walkthroughs, and addon toggles.
3. **WordPress settings** — timezone, pretty permalinks, site icon, search
   visibility (hidden while building), and comment defaults, applied in one
   click with plain-English explanations.
4. **Generate your website** — runs the site-type generator, the "AI-write
   all pages" pass, and the brand-image generator, in labeled stages.
5. **Review** — homepage preview, per-page preview/edit table, and a
   "where to change what" map.
6. **Launch** — pre-flight checklist, publish all generated drafts, set the
   static front page, and re-open the site to search engines.

Full detail: [SETUP-WIZARD.md](SETUP-WIZARD.md).

### Site Types (tab)
The default tab, and the one-click way to set the site's whole look and starter
content in one step (replaces the old standalone "Style Kits" tab — every
site type below already applies its own matching palette/font/radius preset,
so a separate manual kit picker was a redundant second way to do the same
thing).
- **Choose a site type** â€” Agency, Freelance/Portfolio, SaaS, Blog, or
  Marketing landing, each with live design previews and a matching Style Kit.
- **Your starter pages** â€” regenerate any page to its other hand-built
  variant, or regenerate a whole site type at once.
- **Generate starter hero copy** â€” a headline + subheadline from a one-line
  description, using whichever AI model is selected.
- **Advanced: Connect more AI models** â€” optional free API keys for Google
  Gemini, Groq, and OpenRouter, alongside the always-available, keyless
  Pollinations default.
- **Advanced: Backup & restore settings** â€” Export all theme settings as
  JSON, Import a previous export, or Reset everything to defaults (moved here
  from the old Style Kits tab â€” same tools, same behavior).
- Can be switched off entirely via Customize â†’ Theme Options â†’ Theme Addons
  ("Enable Pressroot AI") â€” the tab disappears when disabled.

### GitHub (tab)
- **Default GitHub owner**, **API token**, **data cache (hours)**, **OAuth Client ID**, and **Connect with GitHub** (device-flow login) â€” raises the API rate limit for the live repo data shown on project pages.

### Support (tab)
Always visible, regardless of the Pressroot AI addon toggle.
- **Repository status** â€” live stats, topics, language breakdown, and recent
  releases/changelog for "this theme's repository," pulled through the same
  live GitHub data engine (`App\Github`) that powers project pages.
- **Open issues** â€” the most recently updated open issues, with links to view
  all issues or open a new one.
- **Documentation** â€” a curated list of links straight to the theme's own
  docs (Architecture, Settings reference, Development, Build log, Brand &
  design system).
- **Edit repository** â€” which owner/repo counts as "this theme," separate
  from the GitHub tab's default owner (that one's just a fallback for
  individual Projects).

---

## Appearance â†’ Local Fonts
- **Download fonts now** â€” fetch the active families' woff2 into `uploads/prt-fonts/`.
- **Serve fonts locally** â€” use the local stylesheet and remove every Google Fonts request + preconnect.
- Separately, the full **Google Fonts library (1,500+ families)** is registered as a Font Collection in the native block editor Font Library (Appearance â†’ Editor â†’ Styles â†’ Typography â†’ "Manage fonts") â€” browsable/filterable by category, downloaded and self-hosted on install.

## Appearance â†’ Pattern Library
A read-only admin page listing every pattern registered in the "Pressroot" category (the general-purpose ones below, not the Site Type patterns, which live under their own "AI Site Types" category and are reached through Site Types instead), plus a link to the Synced Patterns editor and a short "how to insert a pattern" walkthrough.

---

## Reading experience (single posts)
Always on, no settings â€” automatic on every single post:
- **Table of contents** â€” auto-built from the post's headings.
- **Reading progress bar** and **estimated reading time**.
- **Copy buttons** on code blocks (pairs with the Prism syntax highlighting below).

---

## Blocks (in the inserter, "Pressroot" category)

- **Social Icons** â€” inline Blade SVG social icons; pulls from site social links by default, fully styleable (size, shape, brand/mono, colors, hover, alignment).
- **Icon (Blade)** â€” drop in any Blade icon by name (`si-â€¦`, `heroicon-o-â€¦`, `lucide-â€¦`, `prt-â€¦`), with size, color, alignment.
- **Post Grid** â€” query posts/projects/pages into a responsive card grid (columns, count, order, image/excerpt/date/category toggles).
- **GitHub blocks** â€” repo card, repo grid, user/org stats, and releases, all backed by the same live/cached `App\Github` data used by the Support tab and project pages.
- **Patterns** â€” 22 general-purpose patterns (Hero, Pricing, Testimonials, Logo cloud, Feature grid, Callout, CTA band, Stat strip, FAQ, and more) â€” browse the full, current list on **Appearance â†’ Pattern Library** rather than here, since it's kept in sync with what's actually registered. Site Type patterns (26 more, two hand-built variants per starter page) are separate â€” see the Site Types tab above.

## Shortcodes
- `[prt_newsletter]` â€” newsletter signup form (Customizer â†’ Newsletter).
- `[prt_breadcrumbs]` â€” breadcrumb trail with BreadcrumbList schema.

## Per-project (Projects â†’ edit â†’ "Project Details")
- **GitHub owner / repo**, **eyebrow**, **demo URL** â€” drive the live GitHub data section on each project.

---

## Appearance → Pressroot → Brand (v1.5)

The non-technical control panel — every answer steers the design generator and the AI:

| Question | Stored as | Drives |
|---|---|---|
| Business name | `prt_brand_name` | Hero eyebrow default, AI prompts, optional site title |
| One-line description | `prt_brand_desc` | Hero subtext default, AI prompts, optional tagline |
| Brand color | `prt_brand_color` | Overrides every dealt kit's accent (`prt_color_action`) |
| Light or dark? | `prt_brand_mode` | Filters which Style Kits can be dealt |
| Personality | `prt_brand_vibe` | Filters kits **and** design trends |
| Who is it for? | `prt_brand_audience` | AI copy targeting |
| Industry | `prt_brand_industry` | AI copy + AI image prompts |
| Three voice words | `prt_brand_tone` | AI copy voice + image mood |
| Main goal | `prt_brand_goal` | Every AI-written call-to-action |
| Imagery style | `prt_brand_imagery` | AI image generation style |
| Content density | `prt_brand_density` | AI copy length |
| Design trend | `prt_brand_trend` | Lock one trend, or 🎲 rotate per refresh |
| Site title & tagline sync | checkbox | Copies name/description to Settings → General |
| **Powered by AI — or not** | `prt_ai_features_on` | Master switch for every AI-calling feature |

## Design generator (Site Types tab, v1.5)

- **8 site types**: agency, freelance, saas, blog, marketing, affiliate, restaurant, realty. Picker cards preview **all four designs per page** (A/B hand-built, C/D generated by `app/site-type-remix.php`), each rendered in the type's own kit via `?prt_preview_kit=`.
- **Every 🎲 deal**: random variant per page (never the current one) + random kit from the type's pool + random design trend — then `prt_refresh_branding()` re-asserts the brand color and `prt_flush_design_caches()` clears critical CSS.
- **Design trends** (`prt_design_trends()`, body class `prt-trend-*`): bento, glass, brutalist, editorial, minimal, retro_pop — pure CSS layers in `resources/css/app.css`.
- **Kits**: 13 total; `core_marketing` is reserved — only the Marketing type's pool contains it.
- **✨ AI Builder** (`app/ai-builder.php`): "Write with AI" per page / per type replaces text segments only (block markup is never AI-generated); "Generate brand image" sideloads a Pollinations image into the Media Library and sets the hero. All gated by `prt_ai_features_enabled()`.
- **Gutenberg is the page builder**: every generated page is plain blocks — "Edit blocks" opens the block editor.

## Homepage hero (generic, v1.5)

All Customizer → Theme Options → Hero. Two-tone headline in four editable pieces: `prt_hero_title` ("Your brand in.") + `prt_hero_accent` (gradient, "Your site") + `prt_hero_serif` (italic serif, "out.") + `prt_hero_suffix` (empty by default). Also: `prt_hero_subtext`, `prt_hero_btn1_text/_url`, `prt_hero_btn2_text/_url`, `prt_hero_chip1/2` (blank hides). A saved Brand profile supplies smarter defaults for the eyebrow and subtext.

## Notes for developers
- All front-end overrides are emitted via the `prt_head_end` action, which fires after the
  built stylesheet, so settings win without `!important` gymnastics.
- Icons use **Blade Icons** (Simple Icons `si-`, Heroicons `heroicon-o-`/`-s-`, Lucide `lucide-`,
  and a local `prt-` set in `resources/svg/`).
- Module files live in `app/*.php` and are registered in `functions.php`.