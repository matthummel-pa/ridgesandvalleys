# Pressroot v1.4.0 — Pressroot AI, Site Types & a consolidated settings page

This release reshapes how a new Pressroot site gets set up. What used to be a one-off "generate some hero copy" tool is now **Pressroot AI**, built around **Site Types** — pick a business category and get a matching design plus real starter pages in one click. Four separate admin pages also became one, with a new **Support** tab for live repo status and docs.

## ✨ New

- **Site Types** — pick Agency/Studio, Freelance/Portfolio, SaaS/Startup, Blog/Content site, or Marketing/Landing page to apply a matching Style Kit and create starter pages together, each pre-filled with one of two hand-built pattern variants (not generic filler). Live, scaled-down previews render before you commit to a type.
- **Regenerate** — swap any starter page, or every page in a site type at once, to its other hand-built variant.
- **Starter hero copy generator** — a one-line business description in, a draft headline + subheadline out.
- **AI Connectors** — optional free API keys for Google Gemini, Groq, and OpenRouter, alongside the always-on, keyless Pollinations default, picked per generation from a model dropdown. Every key stays server-side.
- **AI in the block editor** — a "Generate with AI" toolbar button on paragraph, heading, and list blocks, for everyday writing rather than just initial setup.
- **Theme Addons** — a Customizer toggle to switch the entire Pressroot AI surface off if a site doesn't want it.
- **Appearance → Pressroot** — Theme Tools, Starter Sites, Pressroot AI, and GitHub (four separate admin pages) are now one page with a left-sidebar menu.
- **Support tab** — live status for this theme's own GitHub repo (stats, languages, latest releases, open issues), plus curated links to its documentation. Always available, independent of the Pressroot AI addon toggle.

## 🔄 Changed

- "Pressroot AI" is renamed **Site Types** — it's the primary tab now, not one of several equally-weighted options.
- The standalone **Style Kits** tab is gone — every Site Type already applies its own matching kit, so a manual picker was a redundant second path. Export/Import/Reset moved into an Advanced section on the Site Types tab instead of disappearing.
- The old **Starter Sites** demo importer is retired in favor of Site Types (more personas, regenerate, live previews, dedicated patterns per type); its dashboard "Create starter pages" button (blank pages, no design) went with it.
- Repository links across the code and docs now correctly point at `matthummel-pa/pressroot`, not the old `matthummel-theme` repo it was cloned from.
- Leftover "Matt Hummel" branding in a few user-facing strings (pattern category names, the dashboard widget title, Pattern Library help text) now reads "Pressroot".
- README and the settings reference document previously-missing features — Pressroot AI, the consolidated settings page, WP-CLI, Dev Mode, Reading UX, Pattern Library, Google Fonts Collection — and the stale "17 starter patterns" line is corrected to the real count (22 general-purpose + 26 Site Type patterns).
- README, the Support doc, and the in-admin Support tab now tell Pressroot's origin story: built from the author's own portfolio, generalized into a framework other developers can rebrand.

## 🐛 Fixed

- A dev-seed script that collided with Pressroot AI's page slugs, corrupting preview content on some installs.
- Switching site types now cleanly force-deletes the previous type's pages instead of letting them pile up.
- A pattern attribute mismatch that silently fell back to placeholder card content on `prt/skills-grid`.
- Block-editor previews that depend on a REST round-trip now show an instant preview skeleton instead of staying blank inside pattern thumbnails and the pattern-picker modal.
- A documentation claim that the old Starter Sites patterns were mostly "dead" — they weren't; corrected, with the actual (still valid) reasoning for retiring that feature kept intact.

## 📝 Notes

- No breaking changes to stored content: site-type page meta, style-kit slugs, and AJAX/admin-post action names are all unchanged internally — only labels and admin-page structure moved.
- If Pressroot AI is switched off via Theme Addons, the settings page falls back to the first visible tab automatically.

**Full Changelog**: see `CHANGELOG.md` in this repo.
