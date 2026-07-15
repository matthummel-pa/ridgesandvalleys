# Push to GitHub + import to ridgesandvalleys.com

This folder is a **ready git repository** (already committed) — a Sage 11 theme cloned from
Pressroot v1.7.0 and rebranded for Ridges & Valleys Studio. I couldn't push it from the assistant
side (the connected GitHub app is read-only and scoped to your existing repos), so here are the
exact steps. Takes ~2 minutes.

## 1. Create the repo and push (its own independent entity)

Using the GitHub CLI:
```bash
cd ridgesandvalleys
gh repo create ridgesandvalleys --private --source=. --remote=origin --push
```

Or manually (create an empty **private** repo named `ridgesandvalleys` on github.com first, no README), then:
```bash
cd ridgesandvalleys
git branch -M main
git remote add origin https://github.com/matthummel-pa/ridgesandvalleys.git
git push -u origin main
```

This repo has **no fork link** to Pressroot — it's your standalone main-business repo, exactly as asked.

## 2. Update the Pressroot repo (I couldn't write to it — add this yourself)

In `matthummel-pa/pressroot`, add `docs/reference-sites.md` with:

```markdown
# Reference sites built on Pressroot

| Site | What it is | Rebrand approach |
| --- | --- | --- |
| **Ridges & Valleys Studio** — ridgesandvalleys.com | The studio's own main business site. | Cloned from Pressroot v1.7.0 into an independent repo; rebranded via `theme.json` by mapping the earthy palette onto Pressroot's existing color slugs, so every component inherited the brand with zero component edits. Added an SEO/perf/a11y mu-plugin + launch playbook. |
```
(Or just `git commit` it from your machine — you have write access; the assistant's GitHub app did not.)

## 3. Build for production
```bash
composer install --no-dev -o
npm ci
npm run build
```

## 4. Import to the live site (ridgesandvalleys.com)
- Zip the built theme folder as `ridgesandvalleys.zip` and install via
  **Appearance → Themes → Add New → Upload**, or drop it into `wp-content/themes/ridgesandvalleys/`.
- Copy `extras/mu-plugins/ridges-valleys-seo.php` → `wp-content/mu-plugins/`.
- Activate the theme; set Permalinks to **Post name**.
- Work through `docs/SEO-PERFORMANCE-ACCESSIBILITY.md` before go-live.

## 5. Dev / preview locally (no MySQL needed)
```bash
composer install && npm install
npm run wp     # full WordPress in the browser at http://localhost:8881 (WP Playground)
```
