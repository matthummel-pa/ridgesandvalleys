# Ridges & Valleys Studio — WordPress site (Bedrock)

Bedrock-managed WordPress. The theme is a Sage 11 theme in
`web/app/themes/ridgesandvalleys/`. WordPress core + plugins are managed by Composer.

## Structure
- `web/` — document root (point the server here)
- `web/wp/` — WordPress core (Composer-installed, gitignored)
- `web/app/` — wp-content (themes, plugins, uploads)
- `config/` — Bedrock config + per-environment overrides
- `.env` — DB creds, URLs, salts (gitignored; copy from `.env.example`)

## Local dev (Phase 2)
Runs on DDEV with MySQL. See the migration plan.

## Deploy (Phase 3)
Push to GitHub -> GitHub Actions builds the theme and deploys to SiteGround.

## Workflow
- Code flows UP (local -> GitHub -> live). Deploys never touch the database.
- Content flows DOWN (live -> local) when you need real data for dev.
