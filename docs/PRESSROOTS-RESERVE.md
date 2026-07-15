# Pressroots Reserve — bookings &amp; reservations

Appointments and table/seat reservations for Pressroot, built for restaurants, hotels, and meetings. Ships **inside the theme** as an optional Theme Addon and **standalone** as the [pressroots-reserve](https://github.com/matthummel-pa/pressroots-reserve) WordPress plugin — the include files and assets are identical, only the bootstrap differs.

It aims for the worthwhile core of the established booking apps (Calendly / Acuity / OpenTable) without the bloat or per-booking fees: everything lives in your own WordPress, no third-party service.

## Turning it on

- **Theme addon:** Customizer → Theme Options → **Theme Addons** → *Pressroots Reserve (bookings & reservations)*. On by default. Also offered as a checkbox in the **Setup wizard** Connections step (writes the `prt_addon_bookings_enabled` theme mod).
- **Standalone plugin:** activate **Pressroots Reserve**. If both are present, the plugin defines `PRT_BOOKINGS_VERSION` at load and the bundled theme addon steps aside — the same dual-mode arrangement Repofolio uses, so you can migrate either direction without duplicate post types, blocks, or settings. Data is shared: option key `prt_bookings_options`, post types `prt_service` / `prt_booking`.

Once on: add a service under **Bookings → Services**, set your hours on the **Bookings** tab (Appearance → Pressroot), and drop the **Booking form** block (or the `[prt_booking]` shortcode) on any page. Bookings arrive under **Bookings**; the visual overview is **Bookings → Calendar**.

## How it fits the theme

Follows the Repofolio addon pattern exactly: gated on the Add-ons toggle, classes under `app/Bookings/includes/` loaded with plain `require_once`, booted through a `PrtBookings\Plugin` object on `after_setup_theme`, and settings surfaced as the **Bookings** tab on Appearance → Pressroot via the `pressroot/settings_tabs` filter.

**Brand vs. internals.** The product name is *Pressroots Reserve* (shown in the addon toggle, settings heading, block title, `.ics` PRODID, and calendar page). The internals are stable and unbranded — namespace `PrtBookings`, addon slug `bookings`, option `prt_bookings_options`, CPTs `prt_service` / `prt_booking`, REST namespace `prt-bookings/v1`, constants `PRT_BOOKINGS_*` — so the display name can change without touching code.

> **Boot order note.** `app/bookings-addon.php` checks `prt_addon_enabled('bookings')` at include time, so the `bookings` default is declared directly in `prt_addon_defaults()` (`app/theme-addons.php`), not via a `pressroot/addon_defaults` filter lower in the addon file — a filter registered after the check would be too late and the addon would never boot. `'bookings-addon'` is registered in the `functions.php` module list.

## Files

```
app/bookings-addon.php              Bootstrap: constants, require_once, boot, addon toggle, settings tab
app/Bookings/includes/
  class-settings.php                Options array + the Bookings settings screen
  class-services.php                prt_service CPT (duration, buffer, capacity, price)
  class-engine.php                  prt_booking CPT, slot calculator, create/cancel, no-double-book guard
  class-emails.php                  Confirmation / notification emails + .ics
  class-rest.php                    REST routes for the widget + the admin calendar feed
  class-block.php                   prt/booking block + [prt_booking] shortcode
  class-admin.php                   List columns, quick actions, detail box, multi-view calendar
  class-plugin.php                  Boot orchestrator + tokenized cancel-confirmation page
app/Bookings/assets/
  js/booking.js                     Front-end widget (service → date → time → details)
  js/block.js                       Editor block registration (ServerSideRender preview)
  js/calendar.js                    Admin Month/Week/Day/List calendar
  css/booking.css                   Front-end widget styles
  css/admin.css                     Admin list pills + calendar styles
```

All JS is hand-written vanilla (no build step), matching the theme's admin-asset convention.

## Services

A **service** is a bookable thing (`prt_service` CPT, edited under Bookings → Services). Its "Booking details" meta box holds:

| Field | Meaning |
| --- | --- |
| Duration (minutes) | Length of one booking. |
| Buffer after (minutes) | Cleanup/travel time before the next slot. |
| Capacity per slot | `1` = one-on-one **appointment** (Calendly-style). `>1` = **seats per slot** (OpenTable-style); the visitor picks a party size that consumes seats. |
| Price label | Display only — no payment is collected. |

## Availability engine

`class-engine.php` computes availability from the settings and existing bookings.

- **Time model.** Everything is computed in the **site timezone** (`wp_timezone()`) and stored as UTC unix timestamps in post meta, so DST transitions and timezone edits can't corrupt stored bookings. A slot is identified by its start timestamp.
- **Rules** (Bookings settings): a weekly open/close schedule per weekday, blackout dates, a minimum-notice window (how soon before a slot it locks), a booking window (how far ahead visitors can book), and a slot interval (`0` = back-to-back at duration + buffer).
- **No double-booking.** Slot lists subtract booked seats (pending + confirmed both hold a seat) from capacity. `Engine::create()` re-validates the posted timestamp against the freshly computed slot list, then re-counts seats immediately after insert; if a concurrent request oversold the slot, the later (highest-ID) booking withdraws itself. This is the optimistic strategy the big booking apps use — a true row lock isn't available on vanilla WP storage.

## Front-end booking form

A `prt/booking` **block** and a `[prt_booking]` **shortcode** resolve to the same server-rendered container; `assets/js/booking.js` drives the flow (service → date strip → slot grid → details form) by calling the REST routes. Nothing about availability is decided in the browser — the JS only renders what the engine returns.

```
[prt_booking]                 visitor chooses the service
[prt_booking service="12"]    pin the form to service #12
[prt_booking accent="#0aa"]   override the accent color
```

Hardened like `app/contact.php`: a page-fresh nonce, a honeypot field, and a per-IP rate limit on the booking endpoint.

## Emails

Plain-text `wp_mail`, mirroring `app/contact.php` conventions (site-name subject prefix, Reply-To). The customer confirmation carries an **`.ics`** attachment so "add to calendar" works in any client, plus a **tokenized cancel link**. That link opens a **confirm screen** on GET and only cancels on the POST it submits — so mail scanners and link-preview bots can't auto-cancel. Owners are notified on booking and on customer cancellation.

## Admin

- **Bookings list** (`prt_booking`): When / Service / Guest / Party / Status columns, a status pill, and one-click **Confirm** / **Cancel** row actions (admin-post + nonce, reusing `Engine::set_status()` so the right emails fire). A per-booking detail panel with a status selector is the one place to edit a booking by hand.
- **Calendar** (Bookings → Calendar): a dependency-free **Month / Week / Day / List** calendar over the REST feed (`edit_theme_options`-gated). Times render in the site timezone; the Week/Day time-grid lays overlapping bookings into lanes.

## REST API (`prt-bookings/v1`)

| Route | Method | Access | Purpose |
| --- | --- | --- | --- |
| `/services` | GET | public | Published services for the widget. |
| `/days?service=` | GET | public | Open days in the booking window. |
| `/slots?service=&date=` | GET | public | Available slots for a day. |
| `/book` | POST | public* | Create a booking. *Guarded by nonce + honeypot + per-IP rate limit; the engine re-validates the slot. |
| `/calendar?start=&end=` | GET | `edit_theme_options` | Booking feed for the admin calendar. |

## Settings (Bookings tab)

Stored as one option array (`prt_bookings_options`), saved via admin-post with capability + nonce checks: the weekly schedule, slot interval, minimum notice, booking window, blackout dates, auto-confirm vs. pending, notification email, and an extra confirmation note.

## Hooks

- `pressroot/booking_created` (`$booking`, `$service`) — fires after a booking is stored; emails hook here.
- `pressroot/booking_status_changed` (`$booking`, `$old`, `$new`) — fires on every status transition (pending → confirmed → cancelled).

## Standalone plugin

The plugin bootstrap (`pressroots-reserve.php`) defines the `PRT_BOOKINGS_*` constants from the plugin path, `require_once`s the same `includes/`, boots the `Plugin` on `plugins_loaded`, and adds its own **Settings** submenu (the theme edition renders the same screen as the Bookings tab instead). Repo: <https://github.com/matthummel-pa/pressroots-reserve>.
